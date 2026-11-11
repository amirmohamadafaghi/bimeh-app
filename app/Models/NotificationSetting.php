<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class NotificationSetting extends Model
{
    public const CACHE_KEY = 'bimeh_notification_settings_current';

    protected $fillable = [
        'reminder_offsets',
        'renewal_days_before',
        'adaptive_timing_enabled',
        'birthday_greeting_enabled',
        'installment_template',
        'overdue_template',
        'renewal_template',
        'birthday_template',
    ];

    protected function casts(): array
    {
        return [
            'reminder_offsets'          => 'array',
            'renewal_days_before'       => 'integer',
            'adaptive_timing_enabled'   => 'boolean',
            'birthday_greeting_enabled' => 'boolean',
        ];
    }

    public static function defaultAttributes(): array
    {
        return [
            'reminder_offsets'          => [-3, 0, 2, 7],
            'renewal_days_before'       => 15,
            'adaptive_timing_enabled'   => true,
            'birthday_greeting_enabled' => true,
            'installment_template'      => 'همراه گرامی {name}، قسط بیمه‌نامه شماره {policy_number} به مبلغ {amount} ریال در تاریخ {due_date} سررسید می‌شود. لینک پرداخت: {payment_url}',
            'overdue_template'          => 'بیمه‌گذار گرامی {name}، قسط بیمه‌نامه شماره {policy_number} به مبلغ {amount} ریال با سررسید {due_date} معوق شده است. لطفاً جهت جلوگیری از تعلیق پوشش بیمه‌ای، از طریق لینک زیر تسویه فرمایید: {payment_url}',
            'renewal_template'          => 'همراه گرامی {name}، اعتبار بیمه‌نامه شماره {policy_number} در تاریخ {due_date} به پایان می‌رسد. جهت تمدید به موقع با نمایندگی آفاقی تماس بگیرید.',
            'birthday_template'         => 'زادروزتان خجسته باد {name} عزیز! آرزوی سلامتی، شادی و آرامش برای شما داریم. نمایندگی آفاقی (آفاق بیمه - کد ۳۱۱۶)',
        ];
    }

    public static function current(): self
    {
        if (Schema::hasTable('notification_settings') === false) {
            return new self(self::defaultAttributes());
        }

        return Cache::remember(self::CACHE_KEY, 3600, function (): self {
            $setting = self::first();
            if ($setting instanceof self) {
                return $setting;
            }

            return self::create(self::defaultAttributes());
        });
    }

    public static function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}