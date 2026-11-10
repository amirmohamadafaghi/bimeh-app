<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class NotificationSetting extends Model
{
    public const CACHE_KEY = 'bimeh_notification_settings_array_v2';

    protected static ?self $cachedInstance = null;

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

    protected static function booted(): void
    {
        static::saved(function (): void {
            static::clearCache();
        });

        static::deleted(function (): void {
            static::clearCache();
        });
    }

    public static function clearCache(): void
    {
        static::$cachedInstance = null;
        Cache::forget(self::CACHE_KEY);
    }

    public static function current(): self
    {
        if (static::$cachedInstance instanceof self) {
            return static::$cachedInstance;
        }

        // ذخیره به صورت آرایه خام برای جلوگیری از خطای سریالایز شدن مدل در کش
        $attributes = Cache::rememberForever(self::CACHE_KEY, function (): array {
            $record = static::firstOrCreate([], [
                'reminder_offsets'          => [-3, 0, 2, 7],
                'renewal_days_before'       => 15,
                'adaptive_timing_enabled'   => true,
                'birthday_greeting_enabled' => true,
                'installment_template'      => "مشتری گرامی {name}، سررسید قسط بیمه‌نامه شماره {policy_number} به مبلغ {amount} ریال در تاریخ {due_date} می‌باشد.\n{payment_url}",
                'overdue_template'          => "هشدار دیرکرد: مشتری گرامی {name}، قسط بیمه‌نامه {policy_number} به مبلغ {amount} ریال با سررسید {due_date} معوق شده است.\n{payment_url}",
                'renewal_template'          => "یادآور تمدید: مشتری گرامی {name}، اعتبار بیمه‌نامه شماره {policy_number} در تاریخ {due_date} به پایان می‌رسد.",
                'birthday_template'         => "زادروزتان مبارک! بیمه‌گذار گرامی {name}، آفاق بیمه سالروز تولد شما را تبریک می‌گوید.",
            ]);

            return $record->attributesToArray();
        });

        $instance = new static();
        $instance->forceFill($attributes);
        $instance->exists = true;

        return static::$cachedInstance = $instance;
    }
}