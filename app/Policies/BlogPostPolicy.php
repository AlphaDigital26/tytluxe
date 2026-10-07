<?php

namespace App\Policies;

/** Travel Journal is the Content team's area. */
class BlogPostPolicy extends RoleBasedPolicy
{
    protected array $editRoles = ['Super Admin', 'Content'];

    protected array $deleteRoles = ['Super Admin', 'Content'];
}
