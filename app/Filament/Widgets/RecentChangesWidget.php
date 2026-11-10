<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Installment;
use App\Models\NotificationLog;
use App\Models\Policy;
use App\Models\SystemActivity;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Cache;
use Morilog\Jalali\Jalalian;

class RecentChangesWidget extends Widget
{
    public const CACHE_KEY = 'bimeh_dashboard_recent_changes';

    protected static string $view = 'filament.widgets.recent-changes-widget';

    protected int | string | array $columnSpan = 'full';

    public static function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    protected function getViewData(): array
    {
        $cachedMetrics = Cache::remember(self::CACHE_KEY, now()->addMinutes(5), function (): array {
            $today = now('Asia/Tehran')->startOfDay();

            $logsQuery = NotificationLog::query()->whereDate('created_at', $today);
            if ((clone $logsQuery)->count() === 0) {
                $logsQuery = NotificationLog::query();
            }

            $totalLogsCount = (clone $logsQuery)->count();

            if ($totalLogsCount > 0) {
                $totalNotifiedUsers = (clone $logsQuery)->distinct('user_id')->count('user_id');

                $todayDueNotified = (clone $logsQuery)
                    ->where('type', 'installment_reminder')
                    ->where('offset_day', 0)
                    ->count();

                $twoDaysNotified = (clone $logsQuery)
                    ->where('type', 'installment_reminder')
                    ->whereIn('offset_day', [-2, 2])
                    ->count();

                $sevenDaysNotified = (clone $logsQuery)
                    ->where('type', 'installment_reminder')
                    ->whereIn('offset_day', [-7, 7])
                    ->count();

                $overdueNotified = (clone $logsQuery)
                    ->where('type', 'installment_reminder')
                    ->where('offset_day', '>', 0)
                    ->count();

                $expiring10DaysNotified = (clone $logsQuery)
                    ->where('type', 'policy_renewal')
                    ->count();
            } else {
                $todayDueNotified = Installment::whereDate('due_date', $today)->count();
                $twoDaysNotified = Installment::whereDate('due_date', $today->copy()->addDays(2))->count();
                $sevenDaysNotified = Installment::whereDate('due_date', $today->copy()->addDays(7))->count();

                $overdueNotified = Installment::where(function ($q) use ($today): void {
                    $q->where('status', 'overdue')
                        ->orWhere(function ($q2) use ($today): void {
                            $q2->whereIn('status', ['pending', 'awaiting_approval'])
                                ->whereDate('due_date', '<', $today);
                        });
                })->count();

                $expiring10DaysNotified = Policy::whereBetween('expires_at', [
                    $today->toDateString(),
                    $today->copy()->addDays(10)->toDateString(),
                ])->count();

                $totalNotifiedUsers = $todayDueNotified + $twoDaysNotified + $sevenDaysNotified + $overdueNotified + $expiring10DaysNotified;
            }

            $notificationReport = [
                'total_users'            => $totalNotifiedUsers,
                'today_count'            => $todayDueNotified,
                'two_days_count'         => $twoDaysNotified,
                'seven_days_count'       => $sevenDaysNotified,
                'overdue_count'          => $overdueNotified,
                'expiring_10_days_count' => $expiring10DaysNotified,
            ];

            $lastExcelActivity = SystemActivity::where('event_type', 'excel_imported')
                ->latest()
                ->first();

            $excelReport = null;

            if ($lastExcelActivity) {
                $details = $lastExcelActivity->details ?? [];
                $stats = $details['stats'] ?? [];

                $newUsers = (int) ($stats['new_users_count'] ?? $details['created_count'] ?? 0);
                $updatedUsers = (int) ($stats['updated_users_count'] ?? $details['updated_count'] ?? 0);
                $updatedPolicies = (int) ($stats['updated_policies_count'] ?? 0);
                $updatedInstallments = (int) ($stats['updated_installments_count'] ?? 0);

                $excelReport = [
                    'id'                                      => $lastExcelActivity->id,
                    'file_name'                               => $details['file_type'] ?? 'فایل اکسل',
                    'time_jalali'                             => Jalalian::fromCarbon($lastExcelActivity->created_at->timezone('Asia/Tehran'))->format('Y/m/d H:i'),
                    'new_users_count'                         => $newUsers,
                    'updated_users_count'                     => $updatedUsers,
                    'updated_policies_count'                  => $updatedPolicies,
                    'updated_installments_count'              => $updatedInstallments,
                    'updated_policies_or_installments_count'  => $updatedPolicies + $updatedInstallments,
                    'status_changed_installments_count'       => $updatedInstallments,
                ];
            }

            return [
                'notificationReport' => $notificationReport,
                'excelReport'        => $excelReport,
                'excelReports'       => $excelReport ? [$excelReport] : [],
            ];
        });

        return array_merge($cachedMetrics, [
            'todayJalaliText'    => Jalalian::now()->format('l j F Y'),
            'todayJalaliNumeric' => Jalalian::now()->format('Y/m/d'),
        ]);
    }
}