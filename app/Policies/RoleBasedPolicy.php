<?php

namespace App\Policies;

use App\Models\Admin;

/**
 * Admin-role rules for a CMS screen, declared as three role lists. Each
 * subclass is picked up by Laravel's policy auto-discovery by name
 * (App\Policies\{Model}Policy).
 */
abstract class RoleBasedPolicy
{
    public const ALL_ROLES = ['Super Admin', 'Operations', 'Support', 'Finance', 'Content', 'Analyst'];

    /** @var array<int, string> who can see the list and open records */
    protected array $viewRoles = self::ALL_ROLES;

    /** @var array<int, string> who can add and edit */
    protected array $editRoles = ['Super Admin'];

    /** @var array<int, string> who can delete */
    protected array $deleteRoles = ['Super Admin'];

    public function viewAny(Admin $user): bool
    {
        return in_array($user->role, $this->viewRoles, true);
    }

    public function view(Admin $user, $model): bool
    {
        return $this->viewAny($user);
    }

    public function create(Admin $user): bool
    {
        return in_array($user->role, $this->editRoles, true);
    }

    public function update(Admin $user, $model): bool
    {
        return in_array($user->role, $this->editRoles, true);
    }

    public function delete(Admin $user, $model): bool
    {
        return in_array($user->role, $this->deleteRoles, true);
    }

    public function deleteAny(Admin $user): bool
    {
        return in_array($user->role, $this->deleteRoles, true);
    }

    public function restore(Admin $user, $model): bool
    {
        return in_array($user->role, $this->deleteRoles, true);
    }

    public function forceDelete(Admin $user, $model): bool
    {
        return false; // Never permanently delete from CMS
    }
}
