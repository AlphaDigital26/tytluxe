<?php

namespace App\Filament\Resources\CabBookings\Pages;

use App\Filament\Actions\RecordManualRefundAction;
use App\Filament\Resources\CabBookings\CabBookingActions;
use App\Filament\Resources\CabBookings\CabBookingResource;
use App\Models\Booking;
use Filament\Actions\ActionGroup;
use Filament\Resources\Pages\ViewRecord;

/**
 * A cab booking in plain words. Shows only what is saved on the booking (no
 * TripJack call on page load); "Check status with TripJack" fetches fresh
 * details on demand.
 */
class ViewCabBooking extends ViewRecord
{
    protected static string $resource = CabBookingResource::class;

    protected string $view = 'filament.pages.cab-booking-view';

    public function getTitle(): string
    {
        return 'Cab booking '.$this->record->reference;
    }

    protected function resolveRecord(int|string $key): Booking
    {
        return parent::resolveRecord($key)->load('payments', 'user');
    }

    protected function getHeaderActions(): array
    {
        return [
            CabBookingActions::checkStatus(),
            RecordManualRefundAction::make()->button()->color('danger'),
            ActionGroup::make([
                CabBookingActions::downloadInvoice(),
                CabBookingActions::addNote(),
            ])->label('More')->icon('heroicon-m-ellipsis-vertical')->button()->color('gray'),
            CabBookingActions::cancelAndRefund()->button(),
        ];
    }
}
