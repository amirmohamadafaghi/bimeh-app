<?php

declare(strict_types=1);

namespace App\Models;

use App\Casts\JalaliDateCast;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Policy extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'insurer_id',
        'policy_number',
        'title',
        'insurance_category',
        'policyholder_type',
        'vehicle_name',
        'plate_number',
        'vehicle_model',
        'no_claim_years',
        'driver_no_claim_years',
        'profile_title',
        'started_at',
        'started_at_jalali',
        'issued_at',
        'issued_at_jalali',
        'expires_at',
        'expires_at_jalali',
        'total_amount',
        'initial_payment',
        'paid_amount',
        'payment_type',
        'down_payment',
        'installments_count',
        'first_due_date',
        'status',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'started_at'         => JalaliDateCast::class,
            'issued_at'          => JalaliDateCast::class,
            'expires_at'         => JalaliDateCast::class,
            'first_due_date'     => JalaliDateCast::class,
            'total_amount'       => 'integer',
            'initial_payment'    => 'integer',
            'paid_amount'        => 'integer',
            'down_payment'       => 'integer',
            'installments_count' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function insurer(): BelongsTo
    {
        return $this->belongsTo(Insurer::class);
    }

    public function installments(): HasMany
    {
        return $this->hasMany(Installment::class)->orderBy('installment_number');
    }

    public function getStartedAtJalaliAttribute(): string
    {
        if (! empty($this->attributes['started_at_jalali'])) {
            return (string) $this->attributes['started_at_jalali'];
        }
        if ($this->started_at) {
            return JalaliDateCast::toJalali($this->started_at);
        }

        return $this->getIssuedAtJalaliAttribute();
    }

    public function getIssuedAtJalaliAttribute(): string
    {
        if (! empty($this->attributes['issued_at_jalali'])) {
            return (string) $this->attributes['issued_at_jalali'];
        }
        if (! empty($this->attributes['started_at_jalali'])) {
            return (string) $this->attributes['started_at_jalali'];
        }

        return JalaliDateCast::toJalali($this->issued_at ?? $this->started_at);
    }

    public function getExpiresAtJalaliAttribute(): string
    {
        return ! empty($this->attributes['expires_at_jalali'])
            ? (string) $this->attributes['expires_at_jalali']
            : JalaliDateCast::toJalali($this->expires_at);
    }

    public function getInsuranceTypeAttribute(): string
    {
        return (string) ($this->insurance_category ?: ($this->title ?: 'ثالث'));
    }
}