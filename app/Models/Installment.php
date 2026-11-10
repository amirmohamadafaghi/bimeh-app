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
        'payment_type',
        'payment_identifier',
        'documented_amount',
        'documented_date',
        'profile_title',
        'due_date',
        'due_date_jalali',
        'status',
        'tracking_code',
        'paid_at',
        'followed_up_at',
    ];

    protected function casts(): array
    {
        return [
            'due_date' => JalaliDateCast::class,
            'paid_at' => 'datetime',
            'followed_up_at' => 'datetime',
            'amount' => 'integer',
            'paid_amount' => 'integer',
            'documented_amount' => 'integer',
            'installment_number' => 'integer',
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
        return $this->attributes['due_date_jalali'] ?? JalaliDateCast::toJalali($this->due_date);
    }

    public function getPaidAtJalaliAttribute(): string
    {
        return $this->paid_at ? JalaliDateCast::toJalali($this->paid_at, 'Y/m/d H:i') : '—';
    }

    public function generateSignedPaymentUrl(): string
    {
        return URL::temporarySignedRoute(
            'customer.installments.signed-pay',
            now('Asia/Tehran')->addDays(7),
            ['installment' => $this->id, 'user' => $this->policy?->user_id]
        );
    }
}
