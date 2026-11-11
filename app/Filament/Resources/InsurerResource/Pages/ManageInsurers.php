<?php

declare(strict_types=1);

namespace App\Filament\Resources\InsurerResource\Pages;

use App\Casts\JalaliDateCast;
use App\Filament\Resources\InsurerResource;
use App\Jobs\SendBaleMessageJob;
use App\Models\Installment;
use App\Models\NotificationLog;
use App\Models\NotificationSetting;
use App\Models\Policy;
use App\Services\NotificationDispatcherService;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ManageRecords;

class ManageInsurers extends ManageRecords
{
    protected static string $resource = InsurerResource::class;

    protected static string $view = 'filament.resources.insurer-resource.pages.manage-insurers';

    public string $serviceTab = 'smart_dispatch';

    public string $installmentTemplate = '';

    public string $overdueTemplate = '';

    public string $renewalTemplate = '';

    public int $renewalDaysBefore = 15;

    public function mount(): void
    {
        parent::mount();
        $settings = NotificationSetting::current();
        $this->installmentTemplate = (string) $settings->installment_template;
        $this->overdueTemplate = (string) $settings->overdue_template;
        $this->renewalTemplate = (string) $settings->renewal_template;
        $this->renewalDaysBefore = (int) $settings->renewal_days_before;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('افزودن شرکت و سرویس')
                ->icon('heroicon-o-plus')
                ->modalHeading('ثبت شرکت بیمه و نوع بیمه‌نامه'),
        ];
    }

    public function saveBaleSettings(): void
    {
        $settings = NotificationSetting::current();
        $settings->update([
            'installment_template' => $this->installmentTemplate,
            'overdue_template'     => $this->overdueTemplate,
            'renewal_template'     => $this->renewalTemplate,
            'renewal_days_before'  => $this->renewalDaysBefore,
        ]);

        Notification::make()
            ->title('تنظیمات بات بله و قالب پیام‌ها ذخیره شد.')
            ->success()
            ->send();
    }

    public function triggerAutoOverdueMessages(): void
    {
        $overdueInstallments = Installment::with(['policy.user'])
            ->where('status', 'overdue')
            ->get();

        $settings = NotificationSetting::current();
        $dispatcher = app(NotificationDispatcherService::class);
        $sentCount = 0;

        foreach ($overdueInstallments as $inst) {
            $user = $inst->policy?->user;
            if ($user === null) {
                continue;
            }

            $msg = str_replace(
                ['{name}', '{amount}', '{due_date}', '{policy_number}'],
                [
                    $user->name ?: $user->mobile,
                    number_format((int) $inst->amount),
                    JalaliDateCast::toJalali($inst->due_date),
                    (string) $inst->policy->policy_number,
                ],
                (string) $settings->overdue_template
            );

            $dispatcher->dispatch(
                user: $user,
                type: 'overdue_auto_blast',
                relatedId: (int) $inst->id,
                offsetDay: (int) now()->format('YmdHi'),
                message: $msg,
                signedPaymentUrl: $inst->generateSignedPaymentUrl(),
                channels: ['bale', 'database', 'webpush']
            );
            $sentCount++;
        }

        Notification::make()
            ->title("پیام اخطار معوقات برای {$sentCount} قسط معوق به صورت خودکار ارسال شد.")
            ->success()
            ->send();
    }

    public function triggerAutoExpirationMessages(): void
    {
        $settings = NotificationSetting::current();
        $today = now('Asia/Tehran')->toDateString();
        $limit = now('Asia/Tehran')->addDays((int) $settings->renewal_days_before)->toDateString();

        $expiringPolicies = Policy::with('user')
            ->whereNotNull('expires_at')
            ->whereBetween('expires_at', [$today, $limit])
            ->get();

        $dispatcher = app(NotificationDispatcherService::class);
        $sentCount = 0;

        foreach ($expiringPolicies as $policy) {
            $user = $policy->user;
            if ($user === null) {
                continue;
            }

            $daysLeft = max(0, (int) now('Asia/Tehran')->startOfDay()->diffInDays($policy->expires_at, false));
            $msg = str_replace(
                ['{name}', '{policy_number}', '{title}', '{expires_at}', '{days_left}'],
                [
                    $user->name ?: $user->mobile,
                    (string) $policy->policy_number,
                    (string) ($policy->vehicle_name ?: $policy->title),
                    JalaliDateCast::toJalali($policy->expires_at),
                    (string) $daysLeft,
                ],
                (string) $settings->renewal_template
            );

            $dispatcher->dispatch(
                user: $user,
                type: 'renewal_auto_blast',
                relatedId: (int) $policy->id,
                offsetDay: (int) now()->format('YmdHi'),
                message: $msg,
                signedPaymentUrl: route('customer.dashboard'),
                channels: ['bale', 'database', 'webpush']
            );
            $sentCount++;
        }

        Notification::make()
            ->title("پیام یادآور انقضا برای {$sentCount} بیمه‌نامه به صورت خودکار ارسال شد.")
            ->success()
            ->send();
    }

    public function retryBaleLog(int $logId): void
    {
        $log = NotificationLog::find($logId);
        if ($log instanceof NotificationLog) {
            $log->update(['status' => 'queued', 'error_message' => null]);
            SendBaleMessageJob::dispatch($log->id);
            Notification::make()->title('پیام مجدداً در صف ارسال بات بله قرار گرفت.')->success()->send();
        }
    }

    public function getBaleLogsProperty()
    {
        return NotificationLog::with('user')
            ->where('channel', 'bale')
            ->latest()
            ->limit(30)
            ->get();
    }

    public function getWebAppLogsProperty()
    {
        return NotificationLog::with('user')
            ->whereIn('channel', ['database', 'webpush'])
            ->latest()
            ->limit(30)
            ->get();
    }
}