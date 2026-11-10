<?php

namespace App\Models;

use App\Casts\JalaliDateCast;
use Illuminate\Database\Eloquent\Model;

class InstallmentAudit extends Model
{
    protected $fillable = [
        'installment_id',
        'actor_id',
        'actor_type',
        'action',
        'old_status',
        'new_status',
        'old_amount',
        'new_amount',
        'description',
    ];

    public function installment()
    {
        return $this->belongsTo(Installment::class);
    }

    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    public function getCreatedAtJalaliAttribute(): string
    {
        return JalaliDateCast::toJalali($this->created_at, 'Y/m/d H:i:s');
    }
}
