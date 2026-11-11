<?php

declare(strict_types=1);

namespace App\Observers;

use App\Filament\Widgets\RecentChangesWidget;
use App\Models\Installment;
use App\Models\InstallmentAudit;
use App\Models\Policy;
use App\Models\User;
use App\Services\ActivityLoggerService;
use Illuminate\Support\Facades\Auth;

class InstallmentObserver
{
    public function created(Installment $installment): void
    {
        $description = $installment->status === 'paid'
            ? 'ثبت و تسویه اولیه قسط (مبلغ مستند: ' . number_format((int) ($installment->documented_amount ?: $installment->amount)) . ' ریال)'
            : 'ایجاد اولیه ردیف قسط';

        $this->logAudit(
            $installment,
            $installment->status === 'paid' ? 'paid' : 'created',
            null,
            $installment->status,
            null,
            (int) $installment->amount,
            $description
        );

        if ($installment->status === 'paid' || $installment->status === 'overdue') {
            $this->evaluateCustomerBadge($installment);
        }

        RecentChangesWidget::clearCache();
    }

    public function updated(Installment $installment): void
    {
        if ($installment->wasChanged(['status', 'amount', 'documented_amount'])) {
            $action = $installment->wasChanged('status') && $installment->status === 'paid'
                ? 'paid'
                : 'status_changed';

            $this->logAudit(
                $installment,
                $action,
                (string) $installment->getOriginal('status'),
                (string) $installment->status,
                (int) $installment->getOriginal('amount'),
                (int) $installment->amount,
                'بروزرسانی وضعیت یا مبلغ قسط'
            );

            $this->evaluateCustomerBadge($installment);

            $policy = $installment->policy()->with('user')->first();
            if ($policy instanceof Policy) {
                $customerName = $policy->user?->name ?: ($policy->user?->mobile ?: 'بیمه‌گذار');
                $statusLabel = match ($installment->status) {
                    'paid'              => 'وصول نهایی شد',
                    'awaiting_approval' => 'ثبت پرداخت توسط مشتری (در انتظار تایید ادمین)',
                    'overdue'           => 'تغییر وضعیت به معوق',
                    default             => 'بروزرسانی قسط',
                };

                ActivityLoggerService::logPolicyOrInstallmentUpdate(
                    title: "قسط شماره {$installment->installment_number} ({$customerName}) — {$statusLabel}",
                    customerName: (string) $customerName,
                    policyNumber: (string) $policy->policy_number,
                    statusText: $statusLabel,
                    amountFormatted: number_format((int) $installment->amount) . ' ریال'
                );
            }

            RecentChangesWidget::clearCache();
        }
    }

    protected function logAudit(
        Installment $installment,
        string $action,
        ?string $oldStatus,
        ?string $newStatus,
        ?int $oldAmount,
        ?int $newAmount,
        string $description
    ): void {
        $user = Auth::user();
        $actorType = app()->runningInConsole()
            ? 'command'
            : (($user instanceof User && $user->canAccessPanel(filament()->getDefaultPanel())) ? 'admin' : 'customer');

        InstallmentAudit::create([
            'installment_id' => $installment->id,
            'actor_id'       => $user?->id,
            'actor_type'     => $actorType,
            'action'         => $action,
            'old_status'     => $oldStatus,
            'new_status'     => $newStatus,
            'old_amount'     => $oldAmount,
            'new_amount'     => $newAmount,
            'description'    => $description,
        ]);
    }

    protected function evaluateCustomerBadge(Installment $installment): void
    {
        $policy = $installment->policy()->with('user')->first();
        if ($policy === null || $policy->user === null) {
            return;
        }

        $user = $policy->user;

        $overdueCount = $user->installments()
            ->where('installments.status', 'overdue')
            ->count();

        $onTimePaidCount = $user->installments()
            ->where('installments.status', 'paid')
            ->whereRaw('DATE(installments.paid_at) <= DATE(installments.due_date)')
            ->count();

        if ($overdueCount >= 2 || ($overdueCount === 1 && ($user->average_delay_days ?? 0) > 7)) {
            $user->update([
                'payment_badge'   => 'bad_payer',
                'suggested_badge' => 'bad_payer',
            ]);
        } elseif ($overdueCount === 0 && $onTimePaidCount >= 2) {
            $user->update([
                'payment_badge'   => 'good_payer',
                'suggested_badge' => 'good_payer',
            ]);
        }
    }
}