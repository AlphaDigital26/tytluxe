<?php

namespace App\Filament\Resources\Packages\Pages;

use App\Filament\Resources\Packages\PackageBinActions;
use App\Filament\Resources\Packages\PackageResource;
use Filament\Resources\Pages\EditRecord;

class EditPackage extends EditRecord
{
    protected static string $resource = PackageResource::class;

    // Long form with many tabs: keep Save on screen so a tab isn't left unsaved.
    public static bool $formActionsAreSticky = true;

    protected function getHeaderActions(): array
    {
        return [
            PackageBinActions::moveToBin(),
            PackageBinActions::restore(),
        ];
    }

    public function getSubheading(): ?string
    {
        if (! $this->record->trashed()) {
            return null;
        }

        return $this->record->canBeErased()
            ? 'This package is in the Bin. Restore it to bring it back — otherwise it is deleted for good on '.$this->record->erasesOn()->format('j M Y').'.'
            : 'This package is in the Bin. It has bookings, so it is never deleted for good. Restore it to bring it back.';
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
