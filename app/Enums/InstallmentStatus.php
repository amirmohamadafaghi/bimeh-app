<?php

declare(strict_types=1);

namespace App\Enums;

enum InstallmentStatus: string
{
    case Pending = 'pending';
    case AwaitingApproval = 'awaiting_approval';
    case Paid = 'paid';
    case Overdue = 'overdue';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'در انتظار پرداخت',
            self::AwaitingApproval => 'در انتظار تایید ادمین',
            self::Paid => 'وصول شده',
            self::Overdue => 'معوق (سررسید گذشته)',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending => 'warning',
            self::AwaitingApproval => 'info',
            self::Paid => 'success',
            self::Overdue => 'danger',
        };
    }
}
