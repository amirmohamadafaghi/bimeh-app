<?php

declare(strict_types=1);

namespace App\Models;

use App\Casts\JalaliDateCast;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SystemActivity extends Model
{
    protected $fillable = [
        'event_type',
        'title',
        'icon',
        'color',
        'actor_id',
        'details',
    ];

    protected function casts(): array
    {
        return [
            'details' => 'array',
        ];
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    public function getCreatedAtJalaliAttribute(): string
    {
        return JalaliDateCast::toJalali($this->created_at, 'Y/m/d H:i');
    }

    public function getRelativeTimeAttribute(): string
    {
        if ($this->created_at === null) {
            return 'لحظاتی پیش';
        }

        $diffInSeconds = (int) $this->created_at->diffInSeconds(now());

        if ($diffInSeconds < 60) {
            return 'لحظاتی پیش';
        }

        $diffInMinutes = (int) floor($diffInSeconds / 60);
        if ($diffInMinutes < 60) {
            return "{$diffInMinutes} دقیقه پیش";
        }

        $diffInHours = (int) floor($diffInMinutes / 60);
        if ($diffInHours < 24) {
            return "{$diffInHours} ساعت پیش";
        }

        $diffInDays = (int) floor($diffInHours / 24);

        return "{$diffInDays} روز پیش";
    }
}