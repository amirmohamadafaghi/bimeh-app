<?php

declare(strict_types=1);

namespace App\Models;

use App\Casts\JalaliDateCast;
use App\Observers\InstallmentObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\URL;

#[ObservedBy([InstallmentObserver::class])]
class Installment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'policy_id',
        'installment_number',
        'amount',
        'paid_amount',
        'documented_amount',
        'documented_date',
        'payment_identifier',
        'payment_type',
        'profile_title',
        'due_date',
        'due_date_jalali',
        'status',
        'paid_at',
        'tracking_code',
    ];

    protected function casts(): array
    {
        return [
            'due_date'           => JalaliDateCast::class,
            'paid_at'            => 'datetime',
            'installment_number' => 'integer',
            'amount'             => 'integer',
            'paid_amount'        => 'integer',
            'documented_amount'  => 'integer',
        ];
    }

    public function policy(): BelongsTo
    {
        return $this->belongsTo(Policy::class);
    }

    public function audits(): HasMany
    {
        return $this->hasMany(InstallmentAudit::class)->latest();
    }

    public function getDueDateJalaliAttribute(): string
    {
        if (empty($this->attributes['due_date_jalali'])) {
            return JalaliDateCast::toJalali($this->due_date);
        }

        return (string) $this->attributes['due_date_jalali'];
    }

    public function getRemainingAmountAttribute(): int
    {
        if ($this->status === 'paid') {
            return 0;
        }

        $paid = max((int) $this->paid_amount, (int) $this->documented_amount);

        return max(0, (int) $this->amount - $paid);
    }

    public function generateSignedPaymentUrl(): string
    {
        $userId = $this->policy?->user_id;

        if (empty($userId)) {
            return route('customer.dashboard');
        }

        return URL::temporarySignedRoute(
            'customer.installment.single-pay',
            now()->addDays(7),
            [
                'installment' => $this->id,
                'user'        => $userId,
            ]
        );
    }
}