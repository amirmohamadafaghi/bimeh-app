<?php

declare(strict_types=1);

namespace App\Services;

use App\Filament\Widgets\RecentChangesWidget;
use App\Models\SystemActivity;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class ActivityLoggerService
{
    public static function logPolicyOrInstallmentUpdate(
        string $title,
        string $customerName,
        string $policyNumber,
        string $statusText,
        string $amountFormatted = ''
    ): void {
        if (Schema::hasTable('system_activities') === false) {
            return;
        }

        SystemActivity::create([
            'actor_id'   => Auth::id(),
            'event_type' => 'installment_updated',
            'title'      => $title,
            'icon'       => 'app_installments',
            'color'      => str_contains($statusText, 'وصول') ? 'emerald' : (str_contains($statusText, 'معوق') ? 'rose' : 'amber'),
            'details'    => [
                'customer_name' => $customerName,
                'policy_number' => $policyNumber,
                'status'        => $statusText,
                'amount'        => $amountFormatted,
                'description'   => "بیمه‌نامه: {$policyNumber} | وضعیت: {$statusText}" . (strlen($amountFormatted) > 0 ? " | مبلغ: {$amountFormatted}" : ''),
            ],
        ]);

        if (class_exists(RecentChangesWidget::class)) {
            RecentChangesWidget::clearCache();
        }
    }
}