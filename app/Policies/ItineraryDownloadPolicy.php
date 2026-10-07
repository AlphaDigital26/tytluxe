<?php

namespace App\Policies;

/** Itinerary downloads are leads captured on the website — read-only, never added by hand. */
class ItineraryDownloadPolicy extends RoleBasedPolicy
{
    protected array $viewRoles = ['Super Admin', 'Operations', 'Support', 'Analyst'];

    protected array $editRoles = [];

    protected array $deleteRoles = ['Super Admin'];
}
