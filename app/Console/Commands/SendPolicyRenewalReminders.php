<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\NotificationSetting;
use App\Models\Policy;
use App\Services\NotificationDispatcherService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendPolicyRenewalReminders extends Command
{
    protected $signature = 'bimeh:send-renewal-reminders';

    protected $description = 'ارسال یادآور تمدید بیمه‌نامه قبل از پایان اعتبار با پردازش دسته‌ای';

    public function handle(NotificationDispatcherService $dispatcher): int
    {
        $settings = NotificationSetting::current();
        $daysBefore = (int) ($settings->renewal_days_before ?: 15);
        $targetDate = Carbon::now('Asia/Tehran')->addDays($daysBefore)->toDateString();

        $sentCount = 0;

        Policy::with('user')
            ->whereNotNull('expires_at')
            ->whereDate('expires_at', $targetDate)
            ->chunkById(200, function ($policies) use ($dispatcher, $settings, $daysBefore, &$sentCount): void {
                foreach ($policies as $policy) {
                    if (! $policy->user) {
                        continue;
                    }

                    $message = $dispatcher->formatTemplate($settings->renewal_template, [
                        'name'          => $policy->user->name ?: $policy->user->mobile,
                        'policy_number' => $policy->policy_number,
                        'amount'        => number_format((int) $policy->total_amount),
                        'due_date'      => $policy->expires_at_jalali,
                    ]);

                    $log = $dispatcher->dispatch(
                        $policy->user,
                        'policy_renewal',
                        $policy->id,
                        -$daysBefore,
                        $message,
                        route('customer.dashboard')
                    );

                    if ($log) {
                        $sentCount++;
                    }
                }
            });

        $this->info("تعداد {$sentCount} یادآور تمدید بیمه‌نامه ارسال شد.");

        return self::SUCCESS;
    }
}