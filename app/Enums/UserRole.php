<?php

declare(strict_types=1);

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case Manager = 'manager';
    case Staff = 'staff';
    case Customer = 'customer';

    public function label(): string
    {
        return match ($this) {
            self::Admin    => 'مدیر کل (Admin)',
            self::Manager  => 'منجر / مدیر داخلی',
            self::Staff    => 'کارشناس صدور',
            self::Customer => 'بیمه‌گذار (مشتری)',
        };
    }
}