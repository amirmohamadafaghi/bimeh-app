<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->hasPermission('manage_users');
    }

    public function view(User $user, User $model): bool
    {
        return $user->isAdmin() || $user->hasPermission('manage_users') || $user->id === $model->id;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->hasPermission('manage_users');
    }

    public function update(User $user, User $model): bool
    {
        if ($model->isAdmin()) {
            return $user->isAdmin();
        }

        return $user->isAdmin() || $user->hasPermission('manage_users');
    }

    public function delete(User $user, User $model): bool
    {
        if ($model->isAdmin() || $user->id === $model->id) {
            return false;
        }

        return $user->isAdmin() || $user->hasPermission('delete_users');
    }

    public function restore(User $user, User $model): bool
    {
        return $user->isAdmin() || $user->hasPermission('delete_users');
    }

    public function forceDelete(User $user, User $model): bool
    {
        if ($user->id === $model->id) {
            return false;
        }

        return $user->isAdmin();
    }
}