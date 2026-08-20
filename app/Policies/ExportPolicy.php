<?php

namespace App\Policies;

use Filament\Actions\Exports\Models\Export;
use Illuminate\Contracts\Auth\Authenticatable;

class ExportPolicy
{
    /**
     * Determine whether the user can view the export.
     */
    public function view(Authenticatable $user, Export $export): bool
    {
        // Allow if the export belongs to the authenticated user (admin or otherwise)
        return (int) $export->user_id === (int) $user->getAuthIdentifier();
    }
}
