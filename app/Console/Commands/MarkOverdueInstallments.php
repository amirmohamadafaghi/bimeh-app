<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Filament\Widgets\RecentChangesWidget;
use App\Models\Installment;
use App\Models\SystemActivity;
use Carbon\Carbon;
use Illuminate\Console\Command;

class MarkOverdueInstallments extends Command
{
    protected $signature = 'bimeh:mark-overdue';

    protected $description = 'بروزرسانی خودکار وضعیت اقساط سررسید گذشته به معوق (overdue)';

    public function handle(): int
    {
        $today = Carbon::now('Asia/Tehran')->toDateString();

        $updatedCount = Installment::where('status', 'pending')
            ->whereDate('due_date', '<', $today)
            ->update([
                'status'     => 'overdue',
                'updated_at' => now(),
            ]);

        if ($updatedCount > 0) {
            SystemActivity::create([
                'event_type' => 'cron_job_executed',
                'title'      => "تغییر خودکار وضعیت {$updatedCount} قسط سررسید گذشته به معوق",
                'icon'       => 'app_installments',
                'color'      => 'rose',
                'details'    => [
                    'overdue_marked_count' => $updatedCount,
                    'date'                 => $today,
                ],
            ]);

            if (class_exists(RecentChangesWidget::class)) {
                RecentChangesWidget::clearCache();
            }
        }

        $this->info("تعداد {$updatedCount} قسط به وضعیت معوق (overdue) تغییر یافت.");

        return self::SUCCESS;
    }
}