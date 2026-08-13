<?php

namespace App\Filament\Resources\Destinations\Pages;

use App\Filament\Resources\Destinations\DestinationResource;
use Filament\Resources\Pages\CreateRecord;

class CreateDestination extends CreateRecord
{
    protected static string $resource = DestinationResource::class;

    /**
     * Pre-fill the `for` field based on the active tab the admin was on.
     * This way, if they clicked "Add Destination" while on the Hotels tab,
     * the form already knows it's for Hotels — one less thing to worry about.
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $tab = request()->query('tab', 'hotel');

        // Only accept valid tab values
        if (in_array($tab, ['hotel', 'cruise', 'package'])) {
            $data['for'] = $tab;
        }

        return $data;
    }
}
