<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Installment;
use App\Models\SystemActivity;
use Illuminate\Console\Command;

class MarkOverdueInstallments extends Command
{
    protected $signature = 'bimeh:mark-overdue';

    protected $description = 'تغییر خودکار وضعیت اقساط سررسید گذشته به معوق (overdue) بر اساس Asia/Tehran';

    public function handle(): int
    {
        $todayTehran = now('Asia/Tehran')->toDateString();
        $updatedCount = 0;

        Installment::with('policy.user')
            ->where('status', 'pending')
            ->whereDate('due_date', '<', $todayTehran)
            ->chunkById(200, function ($installments) use (&$updatedCount): void {
                foreach ($installments as $installment) {
                    $installment->update(['status' => 'overdue']);
                    $updatedCount++;
                }
            });

        if ($updatedCount > 0) {
            SystemActivity::create([
                'event_type' => 'cron_job_executed',
                'title'      => "تغییر خودکار وضعیت {$updatedCount} قسط سررسید گذشته به «معوق»",
                'icon'       => 'app_installments',
                'color'      => 'rose',
                'details'    => [
                    'description' => "تعداد {$updatedCount} قسط در بررسی ساعت ۰۱:۳۰ بامداد به وضعیت معوق درآمد.",
                ],
            ]);
        }

        $this->info("تعداد {$updatedCount} قسط به وضعیت معوق تغییر یافت.");

        return self::SUCCESS;
    }
}