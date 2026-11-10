<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\SystemActivity;
use Illuminate\Support\Facades\Auth;

class ActivityLoggerService
{
    public static function log(
        string $eventType,
        string $title,
        array $details = [],
        string $icon = 'app_reports',
        string $color = 'indigo'
    ): SystemActivity {
        return SystemActivity::create([
            'event_type' => $eventType,
            'title' => $title,
            'icon' => $icon,
            'color' => $color,
            'actor_id' => Auth::id(),
            'details' => $details,
        ]);
    }

    public static function logNotificationSent(
        string $categoryTitle,
        array $recipients = [],
        array $breakdown = []
    ): SystemActivity {
        $count = count($recipients);

        $stats = array_merge([
            'total_users' => $breakdown['total_users'] ?? $count,
            'today_count' => $breakdown['today_count'] ?? 0,
            'two_days_count' => $breakdown['two_days_count'] ?? 0,
            'seven_days_count' => $breakdown['seven_days_count'] ?? 0,
            'overdue_count' => $breakdown['overdue_count'] ?? 0,
            'expiring_10_days_count' => $breakdown['expiring_10_days_count'] ?? 0,
        ], $breakdown);

        return self::log(
            eventType: 'notification_sent',
            title: "ارسال اعلان و پیام سیستمی برای {$stats['total_users']} بیمه‌گذار ({$categoryTitle})",
            details: [
                'category' => $categoryTitle,
                'count' => $stats['total_users'],
                'stats' => $stats,
                'recipients' => $recipients,
            ],
            icon: 'app_notifications',
            color: 'indigo'
        );
    }

    public static function logPolicyOrInstallmentUpdate(
        string $title,
        string $customerName,
        string $policyNumber,
        string $statusText,
        string $amountFormatted
    ): SystemActivity {
        return self::log(
            eventType: 'installment_updated',
            title: $title,
            details: [
                'customer_name' => $customerName,
                'policy_number' => $policyNumber,
                'status_text' => $statusText,
                'amount_formatted' => $amountFormatted,
                'description' => "بیمه‌نامه {$policyNumber} ({$customerName}) - {$statusText} - مبلغ: {$amountFormatted}",
            ],
            icon: 'app_installments',
            color: 'amber'
        );
    }

    public static function logExcelImported(
        string $fileName,
        int $createdCount = 0,
        int $updatedCount = 0,
        array $records = [],
        array $breakdown = []
    ): SystemActivity {
        $stats = [
            'file_name' => $fileName,
            'new_users_count' => $breakdown['new_users_count'] ?? $createdCount,
            'updated_users_count' => $breakdown['updated_users_count'] ?? $updatedCount,
            'updated_policies_or_installments_count' => $breakdown['updated_policies_or_installments_count'] ?? 0,
            'status_changed_installments_count' => $breakdown['status_changed_installments_count'] ?? 0,
            'other_info_updated_count' => $breakdown['other_info_updated_count'] ?? 0,
        ];

        return self::log(
            eventType: 'excel_imported',
            title: "پردازش و همگام‌سازی فایل اکسل: «{$fileName}»",
            details: [
                'file_name' => $fileName,
                'created_count' => $stats['new_users_count'],
                'updated_count' => $stats['updated_users_count'],
                'stats' => $stats,
                'records' => $records,
            ],
            icon: 'app_upload_file',
            color: 'emerald'
        );
    }
}
