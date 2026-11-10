<?php

declare(strict_types=1);

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case SuperAdmin = 'super_admin';
    case Manager = 'manager';
    case Customer = 'customer';

    public function label(): string
    {
        return match ($this) {
            self::Admin, self::SuperAdmin => 'ادمین (مدیر ارشد)',
            self::Manager => 'منجر (مدیر با دسترسی انتخابی)',
            self::Customer => 'بیمه‌گذار (مشتری)',
        };
    }
}
