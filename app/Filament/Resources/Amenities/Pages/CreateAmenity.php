<?php

namespace App\Filament\Resources\Amenities\Pages;

use App\Filament\Resources\Amenities\AmenityResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAmenity extends CreateRecord
{
    protected static string $resource = AmenityResource::class;

    /**
     * Pre-fill the "type" field based on the active tab the admin was on.
     * e.g. if they were on the Hotels tab, the form opens with "Hotels" pre-selected.
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // If type wasn't manually set, try to read it from the URL query param (tab)
        if (empty($data['type'])) {
            $tab = request()->query('activeTab', 'hotel');
            $validTypes = ['hotel', 'cruise', 'package'];
            $data['type'] = in_array($tab, $validTypes) ? $tab : 'hotel';
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
