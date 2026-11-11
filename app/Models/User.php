<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\UserRole;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements FilamentUser
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'national_code',
        'mobile',
        'phone',
        'birth_date',
        'gender',
        'address',
        'policyholder_type',
        'profile_title',
        'email',
        'password',
        'role',
        'permissions',
        'bale_chat_id',
        'push_subscription',
        'payment_badge',
        'suggested_badge',
        'average_delay_days',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password'           => 'hashed',
            'permissions'        => 'array',
            'push_subscription'  => 'array',
            'average_delay_days' => 'integer',
        ];
    }

    public static function availableManagerPermissions(): array
    {
        return [
            'manage_users'         => 'مشاهده و ویرایش بیمه‌گذاران',
            'delete_users'         => 'حذف بیمه‌گذاران',
            'import_excel'         => 'آپلود و همگام‌سازی فایل اکسل',
            'manage_policies'      => 'مدیریت و ثبت بیمه‌نامه‌ها',
            'manage_installments'  => 'مدیریت و تایید وصول اقساط',
            'manage_notifications' => 'مدیریت اعلان‌ها و ارسال پیام بله',
            'manage_insurers'      => 'مدیریت شرکت‌های بیمه',
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        $roleValue = $this->role instanceof UserRole ? $this->role->value : (string) $this->role;

        return in_array($roleValue, ['admin', 'manager', 'staff'], true);
    }

    public function isAdmin(): bool
    {
        $roleValue = $this->role instanceof UserRole ? $this->role->value : (string) $this->role;

        return $roleValue === 'admin';
    }

    public function isManager(): bool
    {
        $roleValue = $this->role instanceof UserRole ? $this->role->value : (string) $this->role;

        return in_array($roleValue, ['manager', 'staff'], true);
    }

public function hasPermission(string $permissionKey): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        if ($this->isManager()) {
            $perms = is_array($this->permissions) ? $this->permissions : [];
            if (empty($perms)) {
                return $permissionKey === 'delete_users' ? false : true;
            }

            return in_array($permissionKey, $perms, true);
        }

        return false;
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
}