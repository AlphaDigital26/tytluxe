<?php

namespace App\Policies;

/** Support and Content moderate and reply to guest reviews. */
class ReviewPolicy extends RoleBasedPolicy
{
    protected array $editRoles = ['Super Admin', 'Operations', 'Support', 'Content'];

    protected array $deleteRoles = ['Super Admin', 'Content'];
}
