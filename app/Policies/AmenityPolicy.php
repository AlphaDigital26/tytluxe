<?php

namespace App\Policies;

/** Amenities are shared by every hotel and cruise. */
class AmenityPolicy extends RoleBasedPolicy
{
    protected array $editRoles = ['Super Admin', 'Operations', 'Content'];

    protected array $deleteRoles = ['Super Admin'];
}
