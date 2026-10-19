<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Admin;

use App\Models\Enquiry;

class EnquiryPolicy
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
    public function view(Admin $user, Enquiry $model): bool
    {
        return in_array($user->role, ['Super Admin', 'Operations', 'Support', 'Finance', 'Content', 'Analyst']); // Shared queue
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(Admin $user): bool
    {
        // Support and Operations take the phone and WhatsApp calls.
        return in_array($user->role, ['Super Admin', 'Operations', 'Support'], true);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(Admin $user, Enquiry $model): bool
    {
        if (in_array($user->role, ['Super Admin', 'Operations'], true)) return true;
        // Support picks up new, unassigned enquiries as well as their own.
        if ($user->role === 'Support' && $model->assigned_agent_id === null) return true;
        return $model->assigned_agent_id === $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(Admin $user, Enquiry $model): bool
    {
        return $user->role === 'Super Admin';
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(Admin $user, Enquiry $model): bool
    {
        return $user->role === 'Super Admin';
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(Admin $user, Enquiry $model): bool
    {
        return false; // Never permanently delete from CMS
    }
}
