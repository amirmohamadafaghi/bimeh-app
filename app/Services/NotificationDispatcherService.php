<?php

namespace App\Services;

use App\Casts\JalaliDateCast;
use App\Jobs\SendBaleMessageJob;
use App\Models\NotificationLog;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class NotificationDispatcherService
{
    /**
     * ارسال هم‌زمان به کانال درون‌برنامه‌ای (پنل مشتری) و صف ربات بله با کلید یکتای Idempotency
     */
    public function dispatch(
        User $user,
        string $type,
        int $relatedId,
        int $offsetDay,
        string $message,
        ?string $signedPaymentUrl = null
    ): ?NotificationLog {
        // کلید یکتا طبق بند ۱۵: user_id + type + related_id + offset_day
        $idempotencyKey = "{$user->id}:{$type}:{$relatedId}:{$offsetDay}";

        // جلوگیری صددرصدی از ارسال تکراری حتی با اجرای چندباره Command
        $existing = NotificationLog::where('idempotency_key', $idempotencyKey)->first();
        if ($existing) {
            return null;
        }

        // ۱. ثبت اعلان درون‌برنامه‌ای (Web App Notification) برای پنل مشتری
        DB::table('notifications')->insert([
            'id' => (string) Str::uuid(),
            'type' => $type,
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
            'data' => json_encode([
                'title' => $this->resolveTitle($type, $offsetDay),
                'message' => $message,
                'related_id' => $relatedId,
                'offset_day' => $offsetDay,
                'action_url' => $signedPaymentUrl,
                'created_jalali' => JalaliDateCast::toJalali(now(), 'Y/m/d H:i'),
            ], JSON_UNESCAPED_UNICODE),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // ۲. ثبت لاگ و ارسال به صف ربات بله
        $log = NotificationLog::create([
            'user_id' => $user->id,
            'channel' => 'bale',
            'type' => $type,
            'related_id' => $relatedId,
            'offset_day' => $offsetDay,
            'idempotency_key' => $idempotencyKey,
            'message' => $message,
            'status' => 'queued',
        ]);

        SendBaleMessageJob::dispatch($log->id, $signedPaymentUrl);

        return $log;
    }

    public function formatTemplate(string $template, array $replacements): string
    {
        foreach ($replacements as $key => $value) {
            $template = str_replace('{'.$key.'}', (string) $value, $template);
        }

        return $template;
    }

    protected function resolveTitle(string $type, int $offsetDay): string
    {
        if ($type === 'policy_renewal') {
            return 'یادآور تمدید بیمه‌نامه';
        }
        if ($type === 'payment_confirmed') {
            return 'تأیید پرداخت قسط';
        }
        if ($offsetDay < 0) {
            return 'یادآور سررسید قسط';
        }
        if ($offsetDay === 0) {
            return 'سررسید قسط امروز';
        }

        return 'اخطار قسط معوق';
    }
}
