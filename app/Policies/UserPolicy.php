<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Admin;


class UserPolicy
{
    /**
     * Customers' names, emails and phone numbers — the same roles that can
     * see bookings (BookingPolicy); Content and Analyst have no use for them.
     */
    public function viewAny(Admin $user): bool
    {
        return in_array($user->role, BookingPolicy::VIEW_ROLES, true);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(Admin $user, User $model): bool
    {
        if ($user->role === 'Super Admin') return true;
        return $this->viewAny($user) && $model->role === 'customer';
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(Admin $user): bool
    {
        return $user->role === 'Super Admin';
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(Admin $user, User $model): bool
    {
        return $user->role === 'Super Admin';
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(Admin $user, User $model): bool
    {
        return $user->role === 'Super Admin';
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(Admin $user, User $model): bool
    {
        return $user->role === 'Super Admin';
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(Admin $user, User $model): bool
    {
        return false; // Never permanently delete from CMS
    }
}
