<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Policy;
use App\Models\User;

class PolicyPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->hasPermission('manage_policies');
    }

    public function view(User $user, Policy $policy): bool
    {
        return $user->isAdmin() || $user->hasPermission('manage_policies') || $policy->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->hasPermission('manage_policies');
    }

    public function update(User $user, Policy $policy): bool
    {
        return $user->isAdmin() || $user->hasPermission('manage_policies');
    }

    public function delete(User $user, Policy $policy): bool
    {
        return $user->isAdmin() || $user->hasPermission('delete_users');
    }

    public function restore(User $user, Policy $policy): bool
    {
        return $user->isAdmin() || $user->hasPermission('delete_users');
    }

    public function forceDelete(User $user, Policy $policy): bool
    {
        return $user->isAdmin();
    }
}
