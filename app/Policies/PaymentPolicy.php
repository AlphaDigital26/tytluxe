<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Admin;

use App\Models\Payment;

class PaymentPolicy
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
    public function view(Admin $user, Payment $model): bool
    {
        return in_array($user->role, ['Super Admin', 'Operations', 'Support', 'Finance', 'Content', 'Analyst']);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(Admin $user): bool
    {
        return false; // Append-only via webhooks/system
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(Admin $user, Payment $model): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(Admin $user, Payment $model): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(Admin $user, Payment $model): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(Admin $user, Payment $model): bool
    {
        return false; // Never permanently delete from CMS
    }
}
