<?php

namespace App\Policies;

/** Destinations feed hotels, packages and cruises — Operations and Content keep them up to date. */
class DestinationPolicy extends RoleBasedPolicy
{
    protected array $editRoles = ['Super Admin', 'Operations', 'Content'];

    protected array $deleteRoles = ['Super Admin'];
}
