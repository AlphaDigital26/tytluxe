<?php

namespace App\Filament\Resources\FlightBookings\Pages;

use App\Filament\Actions\RecordManualRefundAction;
use App\Filament\Resources\FlightBookings\FlightBookingActions;
use App\Filament\Resources\FlightBookings\FlightBookingResource;
use App\Models\Booking;
use Filament\Actions\ActionGroup;
use Filament\Resources\Pages\ViewRecord;

/**
 * A flight booking in plain words. Reads only what is saved on the booking
 * — no live TripJack call on page load, so the page always opens quickly
 * even when TripJack is slow; "Check status with airline" fetches fresh
 * details on demand.
 */
class ViewFlightBooking extends ViewRecord
{
    protected static string $resource = FlightBookingResource::class;

    protected string $view = 'filament.pages.flight-booking-view';

    public function getTitle(): string
    {
        return 'Flight booking '.$this->record->reference;
    }

    protected function resolveRecord(int|string $key): Booking
    {
        return parent::resolveRecord($key)->load('payments', 'user');
    }

    protected function getHeaderActions(): array
    {
        return [
            FlightBookingActions::checkStatus(),
            FlightBookingActions::downloadInvoice(),
            FlightBookingActions::addNote(),
            RecordManualRefundAction::make(),
            ActionGroup::make([
                FlightBookingActions::releaseReservation(),
                FlightBookingActions::cancelAndRefund(),
            ])->label('Cancel')->icon('heroicon-o-x-circle')->button()->color('danger')
                ->visible(fn () => in_array($this->record->status, ['confirmed', 'on_hold'], true)),
        ];
    }
}
