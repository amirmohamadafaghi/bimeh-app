<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\NotificationLog;
use App\Services\BaleBotService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\RateLimited;
use Illuminate\Queue\SerializesModels;
use Throwable;

class SendBaleMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function backoff(): array
    {
        return [10, 30, 60];
    }

    public function __construct(
        public int $notificationLogId,
        public ?string $signedPaymentUrl = null
    ) {}

    public function middleware(): array
    {
        return [new RateLimited('bale-api')];
    }

    public function handle(BaleBotService $baleService): void
    {
        $log = NotificationLog::with('user')->find($this->notificationLogId);
        if ($log === null || $log->status === 'sent') {
            return;
        }

        try {
            $baleService->sendMessage(
                $log->user?->bale_chat_id,
                (string) $log->message,
                $this->signedPaymentUrl
            );

            $log->update([
                'status'        => 'sent',
                'error_message' => null,
                'sent_at'       => now(),
            ]);
        } catch (Throwable $e) {
            $log->update([
                'status'        => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}