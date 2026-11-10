<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Casts\JalaliDateCast;
use App\Enums\InstallmentStatus;
use App\Models\Installment;
use App\Models\NotificationSetting;
use App\Models\SystemActivity;
use App\Models\User;
use App\Services\NotificationDispatcherService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendInstallmentReminders extends Command
{
    protected $signature = 'bimeh:send-installment-reminders';

    protected $description = 'ارسال خودکار اعلان‌های سررسید، دیرکرد اقساط و تبریک تولد بیمه‌گذاران (درون‌برنامه‌ای + ربات بله)';

    public function handle(NotificationDispatcherService $dispatcher): int
    {
        $settings = NotificationSetting::current();
        $today = Carbon::now('Asia/Tehran')->startOfDay();
        $offsets = array_map('intval', $settings->reminder_offsets ?: [-3, 0, 2, 7]);

        $sentCount = 0;
        $birthdayCount = 0;

        // ۱. ارسال هوشمند یادآور سررسید و اخطار دیرکرد اقساط
        Installment::with(['policy.user'])
            ->whereIn('status', [InstallmentStatus::Pending->value, InstallmentStatus::Overdue->value])
            ->whereNotNull('due_date')
            ->chunkById(200, function ($installments) use ($dispatcher, $settings, $today, $offsets, &$sentCount): void {
                foreach ($installments as $installment) {
                    $user = $installment->policy?->user;
                    if (! $user || ! $installment->due_date) {
                        continue;
                    }

                    $diffDays = (int) $installment->due_date->copy()->startOfDay()->diffInDays($today, false);

                    $effectiveOffsets = collect($offsets)->map(function (int $offset) use ($user, $settings): int {
                        if ($offset < 0 && $settings->adaptive_timing_enabled) {
                            return $user->getAdaptiveFirstReminderOffset($offset);
                        }

                        return $offset;
                    })->unique()->values()->all();

                    if (! in_array($diffDays, $effectiveOffsets, true)) {
                        continue;
                    }

                    $template = $diffDays > 0 ? $settings->overdue_template : $settings->installment_template;
                    $signedUrl = $installment->generateSignedPaymentUrl();

                    $message = $dispatcher->formatTemplate($template, [
                        'name'          => $user->name ?: $user->mobile,
                        'policy_number' => $installment->policy->policy_number,
                        'amount'        => number_format((int) $installment->amount),
                        'due_date'      => $installment->due_date_jalali,
                        'payment_url'   => $signedUrl,
                    ]);

                    $log = $dispatcher->dispatch(
                        $user,
                        'installment_reminder',
                        (int) $installment->id,
                        $diffDays,
                        $message,
                        $signedUrl
                    );

                    if ($log) {
                        $sentCount++;
                    }
                }
            });

        // ۲. ارسال خودکار تبریک تولد به متولدین امروز
        if ($settings->birthday_greeting_enabled && ! empty($settings->birthday_template)) {
            $todayJalaliMonthDay = JalaliDateCast::toJalali($today, 'm/d');
            $todayGregorianMonthDay = $today->format('m-d');
            $currentYear = (int) JalaliDateCast::toJalali($today, 'Y');

            User::whereNotNull('birth_date')
                ->where('birth_date', '!=', '')
                ->chunkById(200, function ($users) use ($dispatcher, $settings, $todayJalaliMonthDay, $todayGregorianMonthDay, $currentYear, &$birthdayCount): void {
                    foreach ($users as $user) {
                        $birthStr = str_replace('-', '/', (string) $user->birth_date);
                        $isBirthday = str_ends_with($birthStr, $todayJalaliMonthDay)
                            || str_ends_with((string) $user->birth_date, $todayGregorianMonthDay);

                        if (! $isBirthday) {
                            continue;
                        }

                        $msg = $dispatcher->formatTemplate($settings->birthday_template, [
                            'name' => $user->name ?: 'بیمه‌گذار گرامی',
                        ]);

                        $log = $dispatcher->dispatch(
                            $user,
                            'birthday_greeting',
                            (int) $user->id,
                            $currentYear,
                            $msg,
                            route('customer.dashboard')
                        );

                        if ($log) {
                            $birthdayCount++;
                        }
                    }
                });
        }

        if ($sentCount > 0 || $birthdayCount > 0) {
            SystemActivity::create([
                'event_type' => 'cron_job_executed',
                'title'      => "اجرای خودکار موتور اعلان: {$sentCount} یادآور قسط و {$birthdayCount} تبریک تولد",
                'icon'       => 'app_notifications',
                'color'      => 'indigo',
                'details'    => [
                    'description' => "یادآور قسط/معوقات: {$sentCount} مورد | تبریک تولد: {$birthdayCount} مورد",
                ],
            ]);
        }

        $this->info("تعداد {$sentCount} یادآور قسط و {$birthdayCount} تبریک تولد با موفقیت صف‌بندی شد.");

        return self::SUCCESS;
    }
}