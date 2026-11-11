<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Casts\JalaliDateCast;
use App\Models\Installment;
use App\Models\NotificationLog;
use App\Models\NotificationSetting;
use App\Models\SystemActivity;
use App\Models\User;
use App\Services\NotificationDispatcherService;
use App\Support\PersianNormalizer;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendInstallmentReminders extends Command
{
    protected $signature = 'bimeh:send-installment-reminders';

    protected $description = 'ارسال هوشمند یادآور اقساط (قبل از سررسید، روز سررسید، پس از سررسید) و تبریک تولد خودکار';

    public function handle(NotificationDispatcherService $dispatcher): int
    {
        $settings = NotificationSetting::current();
        $today = Carbon::now('Asia/Tehran')->startOfDay();

        $baseOffsets = (is_array($settings->reminder_offsets) && count($settings->reminder_offsets) > 0)
            ? array_map('intval', $settings->reminder_offsets)
            : [-3, 0, 2, 7];

        $sentReminders = 0;

        Installment::with(['policy.user'])
            ->whereIn('status', ['pending', 'overdue'])
            ->chunkById(200, function ($installments) use ($dispatcher, $settings, $today, $baseOffsets, &$sentReminders): void {
                foreach ($installments as $installment) {
                    $user = $installment->policy?->user;
                    if ($user === null || $installment->due_date === null) {
                        continue;
                    }

                    $dueDate = Carbon::parse($installment->due_date)->timezone('Asia/Tehran')->startOfDay();
                    $diffDays = (int) $dueDate->diffInDays($today, false);

                    $userOffsets = $baseOffsets;
                    if ($settings->adaptive_timing_enabled) {
                        $badge = (string) ($user->payment_badge ?? 'normal');
                        if ($badge === 'good_payer') {
                            $userOffsets = array_values(array_filter($userOffsets, fn (int $o): bool => $o >= -2));
                        } elseif ($badge === 'bad_payer') {
                            if (in_array(-5, $userOffsets, true)) {
                                // آفست ۵ روز قبل از قبل در آرایه موجود است
                            } else {
                                $userOffsets[] = -5;
                            }
                        }
                    }

                    if (in_array($diffDays, $userOffsets, true) === false) {
                        continue;
                    }

                    $template = $diffDays > 0
                        ? (string) $settings->overdue_template
                        : (string) $settings->installment_template;

                    $signedUrl = $installment->generateSignedPaymentUrl();

                    $message = $dispatcher->formatTemplate($template, [
                        'name'          => $user->name ?: $user->mobile,
                        'policy_number' => (string) $installment->policy->policy_number,
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

                    if ($log instanceof NotificationLog) {
                        $sentReminders++;
                    }
                }
            });

        $sentBirthdays = 0;
        if ($settings->birthday_greeting_enabled) {
            $sentBirthdays = $this->sendBirthdayGreetings($dispatcher, $settings, $today);
        }

        if ($sentReminders > 0 || $sentBirthdays > 0) {
            SystemActivity::create([
                'event_type' => 'cron_job_executed',
                'title'      => 'اجرای خودکار موتور یادآور اقساط و تبریک تولد',
                'icon'       => 'app_notifications',
                'color'      => 'emerald',
                'details'    => [
                    'sent_reminders' => $sentReminders,
                    'sent_birthdays' => $sentBirthdays,
                    'description'    => "یادآور قسط: {$sentReminders} | تبریک تولد: {$sentBirthdays}",
                ],
            ]);
        }

        $this->info("یادآورهای ارسال‌شده: {$sentReminders} | تبریک تولد: {$sentBirthdays}");

        return self::SUCCESS;
    }

    protected function sendBirthdayGreetings(
        NotificationDispatcherService $dispatcher,
        NotificationSetting $settings,
        Carbon $today
    ): int {
        $todayJalaliMonthDay = substr(PersianNormalizer::normalizeText(JalaliDateCast::toJalali($today, 'Y/m/d')), 5, 5);
        $todayGregorianMonthDay = $today->format('m-d');
        $jalaliYear = (int) substr(PersianNormalizer::normalizeText(JalaliDateCast::toJalali($today, 'Y/m/d')), 0, 4);

        $sentCount = 0;

        User::whereNotNull('birth_date')
            ->whereRaw('CHAR_LENGTH(birth_date) > 0')
            ->chunkById(200, function ($users) use (
                $dispatcher,
                $settings,
                $todayJalaliMonthDay,
                $todayGregorianMonthDay,
                $jalaliYear,
                &$sentCount
            ): void {
                foreach ($users as $user) {
                    $rawBirth = PersianNormalizer::normalizeText(trim((string) $user->birth_date));
                    if (strlen($rawBirth) < 5) {
                        continue;
                    }

                    $normalizedSlash = str_replace('-', '/', $rawBirth);
                    $birthJalaliStr = JalaliDateCast::toJalali($rawBirth, 'Y/m/d');

                    $isBirthday = str_ends_with($normalizedSlash, $todayJalaliMonthDay)
                        || str_ends_with($birthJalaliStr, $todayJalaliMonthDay)
                        || str_ends_with($rawBirth, $todayGregorianMonthDay);

                    if ($isBirthday === false) {
                        continue;
                    }

                    $template = (string) ($settings->birthday_template ?: 'زادروزتان خجسته باد {name} عزیز! آرزوی سلامتی و آرامش برای شما داریم. نمایندگی آفاقی (آفاق بیمه)');
                    $message = $dispatcher->formatTemplate($template, [
                        'name' => $user->name ?: 'بیمه‌گذار گرامی',
                    ]);

                    $log = $dispatcher->dispatch(
                        $user,
                        'birthday_greeting',
                        (int) $user->id,
                        $jalaliYear,
                        $message,
                        route('customer.dashboard')
                    );

                    if ($log instanceof NotificationLog) {
                        $sentCount++;
                    }
                }
            });

        return $sentCount;
    }
}