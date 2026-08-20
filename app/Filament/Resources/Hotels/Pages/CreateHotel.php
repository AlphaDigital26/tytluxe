<?php

namespace App\Filament\Resources\Hotels\Pages;

use App\Filament\Resources\Hotels\HotelResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateHotel extends CreateRecord
{
    protected static string $resource = HotelResource::class;

    // After saving the hotel, take the admin directly to the Edit page
    // where the Room Types tab is visible — so they can add rooms immediately.
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('edit', ['record' => $this->getRecord()]);
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Hotel Created! 🏨')
            ->body('Now scroll down to the \'Room Types\' section below to add the rooms for this hotel.');
    }
}
