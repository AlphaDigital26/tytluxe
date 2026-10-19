<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Admin;

use App\Models\Offer;

class OfferPolicy
{
    /** Offers are website content: the Content team adds them, as with destinations. */
    public const EDIT_ROLES = ['Super Admin', 'Operations', 'Content'];

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
    public function view(Admin $user, Offer $model): bool
    {
        return in_array($user->role, ['Super Admin', 'Operations', 'Support', 'Finance', 'Content', 'Analyst']);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(Admin $user): bool
    {
        return in_array($user->role, self::EDIT_ROLES, true);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(Admin $user, Offer $model): bool
    {
        return in_array($user->role, self::EDIT_ROLES, true);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(Admin $user, Offer $model): bool
    {
        return $user->role === 'Super Admin';
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(Admin $user, Offer $model): bool
    {
        return $user->role === 'Super Admin';
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(Admin $user, Offer $model): bool
    {
        return false; // Never permanently delete from CMS
    }
}
