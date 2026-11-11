<?php

declare(strict_types=1);

namespace App\Support;

class PersianNormalizer
{
    private const PERSIAN_DIGITS = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];

    private const ARABIC_DIGITS  = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];

    private const ENGLISH_DIGITS = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];

    public static function toEnglishDigits(mixed $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        $str = (string) $value;
        $str = str_replace(self::PERSIAN_DIGITS, self::ENGLISH_DIGITS, $str);
        $str = str_replace(self::ARABIC_DIGITS, self::ENGLISH_DIGITS, $str);

        return trim($str);
    }

    public static function normalizeText(mixed $value): string
    {
        $str = self::toEnglishDigits($value);

        $str = str_replace(
            ['ي', 'ى', 'ئ', 'ك', 'ة', 'ؤ', 'أ', 'إ', "\u{200F}", "\u{200E}"],
            ['ی', 'ی', 'ی', 'ک', 'ه', 'و', 'ا', 'ا', '', ''],
            $str
        );

        return trim($str);
    }

    public static function normalize(mixed $value): string
    {
        return self::normalizeText($value);
    }

    public static function cleanNumber(mixed $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        if (is_float($value) || (is_string($value) && stripos($value, 'E') !== false && is_numeric($value))) {
            $value = number_format((float) $value, 0, '', '');
        }

        $str = self::toEnglishDigits($value);

        if (preg_match('/^(\d+)\.0+$/', $str, $matches)) {
            $str = $matches[1];
        }

        return preg_replace('/[^0-9]/', '', $str) ?? '';
    }

    public static function cleanNationalCode(mixed $value): string
    {
        $digits = self::cleanNumber($value);
        if ($digits === '') {
            return '';
        }

        if (strlen($digits) >= 8 && strlen($digits) < 10) {
            return str_pad($digits, 10, '0', STR_PAD_LEFT);
        }

        return $digits;
    }

    public static function cleanMobile(mixed $value): string
    {
        $digits = self::cleanNumber($value);
        if ($digits === '') {
            return '';
        }

        if (str_starts_with($digits, '989') && strlen($digits) === 12) {
            return '0' . substr($digits, 2);
        }

        if (str_starts_with($digits, '00989') && strlen($digits) === 14) {
            return '0' . substr($digits, 4);
        }

        if (strlen($digits) === 10 && str_starts_with($digits, '9')) {
            return '0' . $digits;
        }

        return $digits;
    }
}