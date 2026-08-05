<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Admin;
use App\Models\Booking;

class BookingPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(Admin $user): bool
    {
        return in_array($user->role, ['admin', 'agent']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(Admin $user, Booking $model): bool
    {
        return in_array($user->role, ['admin', 'agent']);
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
    public function update(Admin $user, Booking $model): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(Admin $user, Booking $model): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(Admin $user, Booking $model): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(Admin $user, Booking $model): bool
    {
        return false; // Never permanently delete from CMS
    }
}
