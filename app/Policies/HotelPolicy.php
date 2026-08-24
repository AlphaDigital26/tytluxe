<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Admin;

use App\Models\Hotel;

class HotelPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(Admin $user): bool
    {
        return in_array($user->role, ['Super Admin', 'Operations', 'Support', 'Finance', 'Content', 'Analyst']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(Admin $user, Hotel $model): bool
    {
        return in_array($user->role, ['Super Admin', 'Operations', 'Support', 'Finance', 'Content', 'Analyst']);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(Admin $user): bool
    {
        return in_array($user->role, ['Super Admin', 'Operations', 'Support', 'Finance', 'Content', 'Analyst']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(Admin $user, Hotel $model): bool
    {
        return in_array($user->role, ['Super Admin', 'Operations', 'Support', 'Finance', 'Content', 'Analyst']);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(Admin $user, Hotel $model): bool
    {
        return $user->role === 'Super Admin';
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(Admin $user, Hotel $model): bool
    {
        return $user->role === 'Super Admin';
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(Admin $user, Hotel $model): bool
    {
        return false; // Never permanently delete from CMS
    }
}
