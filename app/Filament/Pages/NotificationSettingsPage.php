<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Jobs\SendBaleMessageJob;
use App\Models\Installment;
use App\Models\NotificationLog;
use App\Models\NotificationSetting;
use App\Models\SystemActivity;
use App\Models\User;
use App\Services\NotificationDispatcherService;
use App\Support\PersianNormalizer;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class NotificationSettingsPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'app_notifications';

    protected static ?string $navigationLabel = 'مدیریت اعلان';

    protected static ?string $title = 'مدیریت هوشمند اعلان‌ها و ربات بله';

    protected static ?int $navigationSort = 3;

    protected static string $view = 'filament.pages.notification-settings-page';

    public ?array $data = [];

    public array $custom_user_ids = [];

    public string $custom_message = '';

    public string $customerSearch = '';

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return $user instanceof User ? ($user->isAdmin() || $user->hasPermission('manage_notifications')) : false;
    }

    public function mount(): void
    {
        $settings = NotificationSetting::current();
        $this->form->fill($settings->toArray());

        $selectedIdsInput = (string) (request()->query('users') ?: request()->query('selected_users', ''));
        if (strlen($selectedIdsInput) > 0) {
            $ids = array_values(array_unique(array_filter(array_map('intval', explode(',', $selectedIdsInput)))));
            if (count($ids) > 0) {
                $this->custom_user_ids = $ids;
            }
        }
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('۱. تنظیمات زمان‌بندی خودکار اعلان‌ها و ربات بله (ذخیره خودکار)')
                    ->description('هرگونه تغییر در اعداد یا گزینه‌های زیر به صورت خودکار و آنی در سیستم ذخیره می‌شود.')
                    ->schema([
                        Forms\Components\TagsInput::make('reminder_offsets')
                            ->label('آفست‌های یادآوری قسط (به روز، جداشده با Enter)')
                            ->placeholder('افزودن روز')
                            ->required()
                            ->live()
                            ->afterStateUpdated(fn () => $this->autoSaveSettings())
                            ->helperText('مثال استاندارد: 7, 2, 0, -3 (۳ روز قبل، روز سررسید، ۲ روز بعد و ۷ روز بعد)'),

                        Forms\Components\TextInput::make('renewal_days_before')
                            ->label('آستانه یادآور تمدید بیمه‌نامه (روز قبل از انقضا)')
                            ->numeric()
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn () => $this->autoSaveSettings()),

                        Forms\Components\Toggle::make('adaptive_timing_enabled')
                            ->label('فعال‌سازی اعلان تطبیقی هوشمند (تنظیم یادآور اول بر اساس خوش‌حسابی یا بدحسابی مشتری)')
                            ->default(true)
                            ->live()
                            ->afterStateUpdated(fn () => $this->autoSaveSettings()),

                        Forms\Components\Toggle::make('birthday_greeting_enabled')
                            ->label('ارسال کاملاً خودکار پیام تبریک تولد به متولدین هر روز (ساعت ۰۷:۳۰ صبح)')
                            ->default(true)
                            ->live()
                            ->afterStateUpdated(fn () => $this->autoSaveSettings()),
                    ])->columns(2),

                Forms\Components\Section::make('قالب پیام‌های خودکار (ذخیره خودکار پس از ویرایش)')
                    ->description('متغیرهای قابل استفاده: {name} ، {policy_number} ، {amount} ، {due_date} ، {payment_url}')
                    ->schema([
                        Forms\Components\Textarea::make('installment_template')
                            ->label('قالب پیام یادآوری سررسید قسط')
                            ->rows(3)
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn () => $this->autoSaveSettings()),

                        Forms\Components\Textarea::make('overdue_template')
                            ->label('قالب پیام اخطار دیرکرد قسط')
                            ->rows(3)
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn () => $this->autoSaveSettings()),

                        Forms\Components\Textarea::make('renewal_template')
                            ->label('قالب پیام یادآور تمدید بیمه‌نامه')
                            ->rows(3)
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn () => $this->autoSaveSettings()),

                        Forms\Components\Textarea::make('birthday_template')
                            ->label('قالب پیام تبریک تولد خودکار')
                            ->rows(3)
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn () => $this->autoSaveSettings()),
                    ])->columns(2),
            ])
            ->statePath('data');
    }

    public function autoSaveSettings(): void
    {
        $settings = NotificationSetting::first() ?? NotificationSetting::current();
        $state = $this->form->getRawState();

        $offsets = array_map('intval', (array) ($state['reminder_offsets'] ?? [-3, 0, 2, 7]));
        $state['reminder_offsets'] = array_values(array_unique($offsets));

        $settings->update([
            'reminder_offsets'          => $state['reminder_offsets'],
            'renewal_days_before'       => (int) ($state['renewal_days_before'] ?? 15),
            'adaptive_timing_enabled'   => (bool) ($state['adaptive_timing_enabled'] ?? true),
            'birthday_greeting_enabled' => (bool) ($state['birthday_greeting_enabled'] ?? true),
            'installment_template'      => (string) ($state['installment_template'] ?? $settings->installment_template),
            'overdue_template'          => (string) ($state['overdue_template'] ?? $settings->overdue_template),
            'renewal_template'          => (string) ($state['renewal_template'] ?? $settings->renewal_template),
            'birthday_template'         => (string) ($state['birthday_template'] ?? $settings->birthday_template),
        ]);

        NotificationSetting::clearCache();

        Notification::make()
            ->title('تغییرات به صورت خودکار ذخیره شد.')
            ->success()
            ->duration(2000)
            ->send();
    }

    public function selectAllVisibleCustomers(): void
    {
        $visibleIds = $this->getFilteredCustomers()->pluck('id')->map(fn ($id): int => (int) $id)->all();
        $this->custom_user_ids = array_values(array_unique(array_merge(
            array_map('intval', $this->custom_user_ids),
            $visibleIds
        )));
    }

    public function selectOverdueCustomers(): void
    {
        $overdueUserIds = Installment::where('installments.status', 'overdue')
            ->join('policies', 'installments.policy_id', '=', 'policies.id')
            ->pluck('policies.user_id')
            ->filter()
            ->map(fn ($id): int => (int) $id)
            ->unique()
            ->values()
            ->all();

        $this->custom_user_ids = array_values(array_unique(array_merge(
            array_map('intval', $this->custom_user_ids),
            $overdueUserIds
        )));

        Notification::make()
            ->title(count($overdueUserIds) . ' بیمه‌گذار دارای قسط معوق به لیست انتخاب اضافه شدند.')
            ->info()
            ->send();
    }

    public function clearSelectedCustomers(): void
    {
        $this->custom_user_ids = [];
    }

    public function retryFailedLog(int $logId): void
    {
        $log = NotificationLog::find($logId);
        if ($log instanceof NotificationLog) {
            $log->update(['status' => 'queued', 'error_message' => null]);
            SendBaleMessageJob::dispatch($log->id);

            Notification::make()
                ->title('پیام مجدداً در صف ارسال ربات بله قرار گرفت.')
                ->success()
                ->send();
        }
    }

    public function sendCustomMessage(NotificationDispatcherService $dispatcher): void
    {
        $this->custom_user_ids = array_values(array_unique(array_filter(array_map('intval', $this->custom_user_ids))));

        $this->validate([
            'custom_user_ids'   => ['required', 'array', 'min:1'],
            'custom_user_ids.*' => ['integer', 'exists:users,id'],
            'custom_message'    => ['required', 'string', 'min:3'],
        ], [
            'custom_user_ids.required' => 'لطفاً حداقل یک بیمه‌گذار را از لیست انتخاب کنید.',
            'custom_message.required'  => 'لطفاً متن پیام اختصاصی را وارد کنید.',
        ]);

        $successCount = 0;

        User::whereIn('id', $this->custom_user_ids)
            ->chunkById(100, function ($users) use ($dispatcher, &$successCount): void {
                foreach ($users as $user) {
                    $dispatcher->dispatch(
                        $user,
                        'custom_message',
                        (int) $user->id,
                        random_int(1000, 999999),
                        trim($this->custom_message),
                        route('customer.dashboard')
                    );
                    $successCount++;
                }
            });

        if ($successCount === 0) {
            Notification::make()->title('هیچ کاربری یافت نشد.')->warning()->send();

            return;
        }

        SystemActivity::create([
            'actor_id'   => auth()->id(),
            'event_type' => 'notification_sent',
            'title'      => "ارسال پیام اختصاصی به {$successCount} بیمه‌گذار",
            'icon'       => 'app_notifications',
            'color'      => 'indigo',
            'details'    => [
                'description' => mb_substr(trim($this->custom_message), 0, 120),
            ],
        ]);

        $this->reset(['custom_user_ids', 'custom_message', 'customerSearch']);

        Notification::make()
            ->title("پیام اختصاصی با موفقیت برای {$successCount} بیمه‌گذار ارسال شد.")
            ->success()
            ->send();
    }

    protected function getFilteredCustomers()
    {
        $search = PersianNormalizer::normalizeText(trim($this->customerSearch));

        $customers = User::query()
            ->select(['id', 'name', 'mobile', 'national_code'])
            ->when(strlen($search) > 0, function ($q) use ($search): void {
                $q->where(function ($sub) use ($search): void {
                    $sub->where('name', 'like', "%{$search}%")
                        ->orWhere('mobile', 'like', "%{$search}%")
                        ->orWhere('national_code', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->limit(120)
            ->get();

        if (count($this->custom_user_ids) > 0) {
            $selectedUsers = User::whereIn('id', $this->custom_user_ids)
                ->get(['id', 'name', 'mobile', 'national_code']);
            $customers = $selectedUsers->merge($customers)->unique('id')->values();
        }

        return $customers;
    }

    protected function getViewData(): array
    {
        $logs = NotificationLog::with('user:id,name,mobile')
            ->latest()
            ->limit(50)
            ->get();

        $activities = SystemActivity::whereIn('event_type', ['notification_sent', 'birthday_greeting', 'cron_job_executed'])
            ->latest()
            ->limit(5)
            ->get();

        return [
            'logs'       => $logs,
            'activities' => $activities,
            'customers'  => $this->getFilteredCustomers(),
        ];
    }
}