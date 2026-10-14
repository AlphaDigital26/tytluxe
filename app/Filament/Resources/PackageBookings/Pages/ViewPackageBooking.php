<?php

namespace App\Filament\Resources\PackageBookings\Pages;

use App\Filament\Actions\RecordManualRefundAction;
use App\Filament\Resources\Bookings\HotelBookingActions;
use App\Filament\Resources\PackageBookings\PackageBookingActions;
use App\Filament\Resources\PackageBookings\PackageBookingResource;
use App\Models\Booking;
use Filament\Resources\Pages\ViewRecord;

class ViewPackageBooking extends ViewRecord
{
    protected static string $resource = PackageBookingResource::class;

    public function getTitle(): string
    {
        return 'Package booking '.$this->record->reference;
    }

    protected function resolveRecord(int|string $key): Booking
    {
        return parent::resolveRecord($key)->load('package', 'travelers', 'payments.booking');
    }

    protected function getHeaderActions(): array
    {
        return [
            PackageBookingActions::markBalanceReceived(),
            HotelBookingActions::downloadInvoice(),
            HotelBookingActions::addNote(),
            RecordManualRefundAction::make(),
            PackageBookingActions::cancelAndRefund(),
        ];
    }
}
