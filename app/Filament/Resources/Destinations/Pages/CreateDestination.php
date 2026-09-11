<?php

namespace App\Filament\Resources\Destinations\Pages;

use App\Filament\Resources\Destinations\DestinationResource;
use App\Jobs\SyncDestinationHotels;
use App\Models\Destination;
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
            $data['for'] = [$tab];
        }

        return $data;
    }

    /**
     * Adding a destination alone pulls in zero hotels — TripJack's mapping
     * sync only tracks incremental changes for cities we already know about.
     * So a new hotel destination needs its own one-time catalogue fetch,
     * fired here automatically so non-technical admins never need the CLI.
     */
    protected function afterCreate(): void
    {
        /** @var Destination $destination */
        $destination = $this->record;

        if (in_array('hotel', (array) ($destination->for ?? []), true)) {
            $destination->update(['hotel_sync_status' => 'pending']);
            SyncDestinationHotels::dispatch($destination->id);
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
