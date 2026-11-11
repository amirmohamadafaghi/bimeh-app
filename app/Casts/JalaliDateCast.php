<?php

declare(strict_types=1);

namespace App\Casts;

use App\Support\PersianNormalizer;
use Carbon\Carbon;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Morilog\Jalali\Jalalian;
use Throwable;

class JalaliDateCast implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): ?Carbon
    {
        if (empty($value)) {
            return null;
        }

        return Carbon::parse($value)->timezone('Asia/Tehran');
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        return self::toGregorian($value);
    }

    public static function toGregorian(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if ($value instanceof Carbon || $value instanceof \DateTimeInterface) {
            return Carbon::instance($value)->format('Y-m-d');
        }

        $normalized = PersianNormalizer::normalizeText((string) $value);
        $normalized = str_replace('-', '/', $normalized);

        if (preg_match('/^1[34]\d{2}\/\d{1,2}\/\d{1,2}$/', $normalized)) {
            $parts = array_map('intval', explode('/', $normalized));

            return (new Jalalian($parts[0], $parts[1], $parts[2]))->toCarbon()->format('Y-m-d');
        }

        try {
            return Carbon::parse($normalized)->format('Y-m-d');
        } catch (Throwable) {
            return null;
        }
    }

    public static function toJalali(mixed $date, string $format = 'Y/m/d'): string
    {
        if (empty($date)) {
            return '—';
        }

        try {
            if (is_string($date)) {
                $normalized = str_replace('-', '/', PersianNormalizer::normalizeText($date));
                if (preg_match('/^1[34]\d{2}\/\d{1,2}\/\d{1,2}$/', $normalized)) {
                    return $normalized;
                }
            }

            return Jalalian::fromCarbon(Carbon::parse($date)->timezone('Asia/Tehran'))->format($format);
        } catch (Throwable) {
            return (string) $date;
        }
    }
}