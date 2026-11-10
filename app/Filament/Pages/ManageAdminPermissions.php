<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Models\User;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class ManageAdminPermissions extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'app_admin';

    // عدم نمایش در سایدبار / منوی اصلی؛ دسترسی فقط از طریق دکمه هدر پروفایل
    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $title = 'مدیریت اختیارات و دسترسی مدیران';

    protected static string $view = 'filament.pages.manage-admin-permissions';

    public ?int $selected_manager_id = null;

    public array $permissions = [];

    public static function canAccess(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    public function mount(): void
    {
        // انتخاب اولین منجر به صورت پیش‌فرض در صورت وجود
        $firstManager = User::where('role', 'manager')->first();
        if ($firstManager) {
            $this->selected_manager_id = $firstManager->id;
            $this->permissions = is_array($firstManager->permissions) ? $firstManager->permissions : [];
        }
    }

    public function updatedSelectedManagerId($value): void
    {
        if (! $value) {
            $this->permissions = [];
            return;
        }

        $manager = User::find($value);
        if ($manager && $manager->role === 'manager') {
            $this->permissions = is_array($manager->permissions) ? $manager->permissions : [];
        } else {
            $this->permissions = [];
        }
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('انتخاب مدیر / منجر')
                    ->description('مدیری را که می‌خواهید دسترسی‌های او را محدود یا آزاد کنید انتخاب نمایید.')
                    ->schema([
                        Forms\Components\Select::make('selected_manager_id')
                            ->label('حساب کاربری منجر')
                            ->options(User::where('role', 'manager')->pluck('name', 'id'))
                            ->searchable()
                            ->reactive()
                            ->afterStateUpdated(fn ($state) => $this->updatedSelectedManagerId($state))
                            ->placeholder('-- انتخاب کنید --')
                            ->required(),
                    ]),

                Forms\Components\Section::make('تعیین سطوح دسترسی و اختیارات')
                    ->description('تیک بخش‌هایی را که این منجر اجازه دسترسی به آن‌ها را دارد فعال یا غیرفعال کنید.')
                    ->schema([
                        Forms\Components\CheckboxList::make('permissions')
                            ->label('مجوزهای فعال سیستم')
                            ->options(User::availableManagerPermissions())
                            ->columns(2),
                    ]),
            ]);
    }

    public function save(): void
    {
        if (! $this->selected_manager_id) {
            Notification::make()->title('لطفاً ابتدا یک منجر را انتخاب کنید.')->warning()->send();
            return;
        }

        $manager = User::where('role', 'manager')->find($this->selected_manager_id);
        if ($manager) {
            $manager->update([
                'permissions' => array_values($this->permissions),
            ]);

            Notification::make()
                ->title('اختیارات و دسترسی‌های این مدیر با موفقیت بروزرسانی شد.')
                ->success()
                ->send();
        }
    }
}