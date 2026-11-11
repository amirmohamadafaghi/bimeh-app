<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\NotificationLog;
use App\Models\NotificationSetting;
use App\Models\Policy;
use App\Models\SystemActivity;
use App\Models\User;
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
                    if ($policy->user instanceof User) {
                        $message = $dispatcher->formatTemplate((string) $settings->renewal_template, [
                            'name'          => $policy->user->name ?: $policy->user->mobile,
                            'policy_number' => (string) $policy->policy_number,
                            'title'         => (string) ($policy->vehicle_name ?: $policy->title),
                            'amount'        => number_format((int) $policy->total_amount),
                            'due_date'      => $policy->expires_at_jalali,
                            'expires_at'    => $policy->expires_at_jalali,
                            'days_left'     => (string) $daysBefore,
                        ]);

                        $log = $dispatcher->dispatch(
                            $policy->user,
                            'policy_renewal',
                            (int) $policy->id,
                            -$daysBefore,
                            $message,
                            route('customer.dashboard')
                        );

                        if ($log instanceof NotificationLog) {
                            $sentCount++;
                        }
                    }
                }
            });

        if ($sentCount > 0) {
            SystemActivity::create([
                'event_type' => 'cron_job_executed',
                'title'      => "ارسال خودکار یادآور تمدید برای {$sentCount} بیمه‌نامه",
                'icon'       => 'app_notifications',
                'color'      => 'amber',
                'details'    => [
                    'sent_count'  => $sentCount,
                    'target_date' => $targetDate,
                ],
            ]);
        }

        $this->info("تعداد {$sentCount} یادآور تمدید بیمه‌نامه در صف ارسال قرار گرفت.");

        return self::SUCCESS;
    }
}