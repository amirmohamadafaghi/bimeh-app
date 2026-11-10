<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Installment;
use App\Models\User;

class InstallmentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin()
            || $user->hasPermission('manage_installments')
            || $user->hasPermission('approve_installments');
    }

    public function view(User $user, Installment $installment): bool
    {
        return $this->viewAny($user) || $installment->policy?->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->hasPermission('manage_installments');
    }

    public function update(User $user, Installment $installment): bool
    {
        return $user->isAdmin()
            || $user->hasPermission('manage_installments')
            || $user->hasPermission('approve_installments');
    }

    public function delete(User $user, Installment $installment): bool
    {
        return $user->isAdmin() || $user->hasPermission('delete_users');
    }

    public function restore(User $user, Installment $installment): bool
    {
        return $user->isAdmin() || $user->hasPermission('delete_users');
    }

    public function forceDelete(User $user, Installment $installment): bool
    {
        return $user->isAdmin();
    }
}
