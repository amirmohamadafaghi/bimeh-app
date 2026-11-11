<?php

declare(strict_types=1);

namespace App\Enums;

enum InstallmentStatus: string
{
    case Pending = 'pending';
    case Paid = 'paid';
    case Overdue = 'overdue';
    case AwaitingApproval = 'awaiting_approval';

    public function label(): string
    {
        return match ($this) {
            self::Pending          => 'در انتظار پرداخت',
            self::Paid             => 'پرداخت شده',
            self::Overdue          => 'معوق',
            self::AwaitingApproval => 'در انتظار تایید ادمین',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending          => 'warning',
            self::Paid             => 'success',
            self::Overdue          => 'danger',
            self::AwaitingApproval => 'info',
        };
    }
}