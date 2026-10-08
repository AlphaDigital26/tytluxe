<?php

namespace App\Filament\Resources\Bookings\Pages;

use App\Filament\Resources\Bookings\BookingResource;
use Filament\Resources\Pages\EditRecord;

class EditBooking extends EditRecord
{
    protected static string $resource = BookingResource::class;

    public function getTitle(): string
    {
        return 'Edit booking '.$this->record->reference;
    }

    /**
     * Contact-detail and note edits aren't booking activity — updated_at
     * drives the "hotel confirmation taking too long" warning, which a typo
     * fix must not reset.
     */
    protected function handleRecordUpdate(\Illuminate\Database\Eloquent\Model $record, array $data): \Illuminate\Database\Eloquent\Model
    {
        \App\Models\Booking::withoutTimestamps(fn () => $record->update($data));

        return $record;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }
}
