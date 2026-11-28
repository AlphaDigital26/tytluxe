<?php

namespace App\Filament\Resources\Packages;

use App\Models\Package;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;

/**
 * Move-to-Bin / restore buttons for packages, in plain words. Nothing is
 * deleted for good from the admin (PackagePolicy::forceDelete): only the
 * daily packages:empty-bin erases packages after Package::BIN_DAYS days,
 * and never one with bookings.
 */
class PackageBinActions
{
    public static function moveToBin(): DeleteAction
    {
        return DeleteAction::make()
            ->label('Move to Bin')
            ->modalHeading('Move this package to the Bin?')
            ->modalDescription('It disappears from the website straight away. You can restore it from the Bin for '.Package::BIN_DAYS.' days; after that it is deleted for good.')
            ->modalSubmitActionLabel('Move to Bin')
            ->successNotificationTitle('Moved to the Bin — you can restore it for '.Package::BIN_DAYS.' days');
    }

    public static function restore(): RestoreAction
    {
        return RestoreAction::make()
            ->modalHeading('Restore this package?')
            ->modalDescription('It comes back exactly as it was. It stays hidden on the website until it is switched to Active.')
            ->successNotificationTitle('Package restored');
    }

    public static function moveToBinBulk(): DeleteBulkAction
    {
        return DeleteBulkAction::make()
            ->label('Move to Bin')
            ->modalHeading('Move the selected packages to the Bin?')
            ->modalDescription('They disappear from the website straight away and can be restored for '.Package::BIN_DAYS.' days.')
            ->modalSubmitActionLabel('Move to Bin');
    }

    public static function restoreBulk(): RestoreBulkAction
    {
        return RestoreBulkAction::make();
    }
}
