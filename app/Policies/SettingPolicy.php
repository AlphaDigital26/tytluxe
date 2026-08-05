<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Admin;
use App\Models\Setting;

class SettingPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(Admin $user): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(Admin $user, Setting $model): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(Admin $user): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(Admin $user, Setting $model): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(Admin $user, Setting $model): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(Admin $user, Setting $model): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(Admin $user, Setting $model): bool
    {
        return false; // Never permanently delete from CMS
    }
}
