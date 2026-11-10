<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\UserRole;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements FilamentUser, HasAvatar
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'national_code',
        'mobile',
        'phone',
        'email',
        'gender',
        'password',
        'role',
        'permissions',
        'policyholder_type',
        'payment_badge',
        'suggested_badge',
        'average_delay_days',
        'profile_title',
        'birth_date',
        'address',
        'bale_chat_id',
        'preferred_notification_hour',
        'push_subscription',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at'           => 'datetime',
            'password'                    => 'hashed',
            'preferred_notification_hour' => 'integer',
            'average_delay_days'          => 'integer',
            'permissions'                 => 'array',
            'push_subscription'           => 'array',
        ];
    }

    public static function availableManagerPermissions(): array
    {
        return [
            'manage_users'         => 'مدیریت و ویرایش بیمه‌گذاران',
            'delete_users'         => 'حذف بیمه‌گذاران (انتقال به سطل زباله)',
            'manage_policies'      => 'مدیریت بیمه‌نامه‌ها',
            'manage_installments'  => 'مدیریت و ویرایش اقساط',
            'approve_installments' => 'تایید وصولی و تغییر وضعیت مالی اقساط',
            'import_excel'         => 'آپلود و همگام‌سازی فایل اکسل',
            'manage_notifications' => 'مدیریت اعلان‌ها و ارسال پیام بله',
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return in_array($this->role, [
            UserRole::Admin->value,
            UserRole::SuperAdmin->value,
            UserRole::Manager->value,
        ], true);
    }

    public function isAdmin(): bool
    {
        return in_array($this->role, [UserRole::Admin->value, UserRole::SuperAdmin->value], true);
    }

    public function isManager(): bool
    {
        return $this->role === UserRole::Manager->value;
    }

    public function isCustomer(): bool
    {
        return $this->role === UserRole::Customer->value || empty($this->role);
    }

    public function hasPermission(string $permission): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        if ($this->isManager()) {
            $perms = is_array($this->permissions) ? $this->permissions : [];

            return in_array($permission, $perms, true);
        }

        return false;
    }

    public function hasFinancialAccess(): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        return $this->isManager() && (
            $this->hasPermission('approve_installments') ||
            $this->hasPermission('manage_installments') ||
            $this->hasPermission('delete_users')
        );
    }

    public function getGenderLabelAttribute(): string
    {
        return match ($this->gender) {
            'male', 'مرد', 'آقا'   => 'مرد',
            'female', 'زن', 'خانم' => 'زن',
            default                => $this->policyholder_type === 'حقوقی' ? 'شخص حقوقی' : 'ثبت نشده',
        };
    }

    public function getAdaptiveFirstReminderOffset(int $defaultOffset = -3): int
    {
        if ($this->payment_badge === 'bad_payer' || ($this->average_delay_days ?? 0) > 5) {
            return min($defaultOffset, -5);
        }

        if ($this->payment_badge === 'good_payer') {
            return max($defaultOffset, -2);
        }

        return $defaultOffset;
    }

    public function getFilamentAvatarUrl(): ?string
    {
        return asset('images/icons/app_admin.svg');
    }

    public function policies(): HasMany
    {
        return $this->hasMany(Policy::class);
    }

    public function installments(): HasManyThrough
    {
        return $this->hasManyThrough(Installment::class, Policy::class);
    }

    public function notificationLogs(): HasMany
    {
        return $this->hasMany(NotificationLog::class);
    }

    public function systemActivities(): HasMany
    {
        return $this->hasMany(SystemActivity::class, 'actor_id');
    }
}