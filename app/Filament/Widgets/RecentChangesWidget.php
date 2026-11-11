<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Installment;
use App\Models\Policy;
use App\Models\SystemActivity;
use App\Models\User;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class RecentChangesWidget extends Widget
{
    public const CACHE_KEY = 'bimeh_recent_changes_widget_v2';

    protected static string $view = 'filament.widgets.recent-changes-widget';

    protected int|string|array $columnSpan = 'full';

    public static function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    protected function getViewData(): array
    {
        return Cache::remember(self::CACHE_KEY, 60, function (): array {
            $activities = Schema::hasTable('system_activities') === false
                ? collect()
                : SystemActivity::with('actor:id,name')
                    ->latest()
                    ->limit(8)
                    ->get();

            $totalUsers = User::where('role', 'customer')->count();
            $totalPolicies = Policy::count();
            $overdueCount = Installment::where('status', 'overdue')->count();
            $awaitingApprovalCount = Installment::where('status', 'awaiting_approval')->count();

            return [
                'activities'            => $activities,
                'totalUsers'            => $totalUsers,
                'totalPolicies'         => $totalPolicies,
                'overdueCount'          => $overdueCount,
                'awaitingApprovalCount' => $awaitingApprovalCount,
            ];
        });
    }
}