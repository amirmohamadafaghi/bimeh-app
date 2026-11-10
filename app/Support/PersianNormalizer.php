<?php

declare(strict_types=1);

namespace App\Support;

final class PersianNormalizer
{
    /**
     * نام مستعار برای سازگاری کامل با تمامی صفحات و سرویس‌ها
     */
    public static function normalize(?string $value): string
    {
        return self::normalizeText($value);
    }

    /**
     * تبدیل اعداد فارسی و عربی به انگلیسی و یکسان‌سازی حروف ی و ک
     */
    public static function normalizeText(?string $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        $replaced = str_replace(
            ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹', '٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩', 'ي', 'ك', '‌'],
            ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'ی', 'ک', ' '],
            $value
        );

        return trim(preg_replace('/\s+/', ' ', $replaced) ?? $replaced);
    }

    /**
     * استخراج عدد خالص (حذف کاما، فاصله و تبدیل مقادیر علمی اکسل)
     */
    public static function cleanNumber(mixed $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        $str = self::normalizeText((string) $value);
        $str = str_replace([',', ' ', '٬', '_'], '', $str);

        if (is_numeric($str)) {
            return number_format((float) $str, 0, '', '');
        }

        return $str;
    }

    /**
     * استانداردسازی کد ملی (بازگردانی صفر ابتدایی حذف‌شده توسط اکسل)
     */
    public static function cleanNationalCode(mixed $value): string
    {
        $code = self::cleanNumber($value);
        if ($code === '') {
            return '';
        }

        if (in_array(strlen($code), [8, 9], true)) {
            return str_pad($code, 10, '0', STR_PAD_LEFT);
        }

        return $code;
    }

    /**
     * استانداردسازی شماره موبایل (تبدیل 915xxxxxxx به 0915xxxxxxx)
     */
    public static function cleanMobile(mixed $value): string
    {
        $mobile = self::cleanNumber($value);
        if ($mobile === '') {
            return '';
        }

        if (strlen($mobile) === 10 && str_starts_with($mobile, '9')) {
            return '0' . $mobile;
        }

        return $mobile;
    }
}