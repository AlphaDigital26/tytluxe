<?php

namespace App\Filament\Resources\FlightBookings\Pages;

use App\Filament\Actions\RecordManualRefundAction;
use App\Filament\Resources\FlightBookings\FlightBookingActions;
use App\Filament\Resources\FlightBookings\FlightBookingResource;
use App\Models\Booking;
use App\Services\FlightBookingService;
use App\Services\TripJack\TripJackFlightClient;
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

    public ?string $itineraryError = null;

    protected function resolveRecord(int|string $key): Booking
    {
        return parent::resolveRecord($key)->load('payments', 'user');
    }

    /**
     * Airline name and flight times for a booking that doesn't have them
     * saved yet, fetched just after the page has opened (wire:init) — the
     * page itself still never waits for TripJack. Read-only: unlike "Check
     * status" it never changes the booking's status or money.
     */
    public function loadItinerary(): void
    {
        if (filled($this->record->flight_itinerary['segments'] ?? null) || blank($this->record->tripjack_booking_id)) {
            return;
        }

        try {
            $details = app(TripJackFlightClient::class)->bookingDetails($this->record->tripjack_booking_id);
            app(FlightBookingService::class)->storeItinerary($this->record, $details);
            $this->record->refresh()->load('payments', 'user');
        } catch (\Throwable $e) {
            // fall through to the message below
        }

        if (blank($this->record->flight_itinerary['segments'] ?? null)) {
            $this->itineraryError = 'Couldn\'t load the airline name and flight times from TripJack just now. Click "Check status with airline" to try again.';
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            FlightBookingActions::checkStatus(),
            // Only shown when a refund is owed — then it's the job to do, so it
            // sits in the row the "What to do" box points at, not in a menu.
            RecordManualRefundAction::make()->button()->color('danger'),
            FlightBookingActions::downloadETicket(),
            // Grouped so the row fits a laptop screen — side by side, the
            // Cancel button ran off the right edge.
            ActionGroup::make([
                FlightBookingActions::downloadInvoice(),
                FlightBookingActions::addNote(),
            ])->label('More')->icon('heroicon-m-ellipsis-vertical')->button()->color('gray'),
            ActionGroup::make([
                FlightBookingActions::cancelAndRefund(),
            // "Cancel" alone reads as "close this page" — say what it does.
            ])->label('Cancel ticket')->icon('heroicon-o-x-circle')->button()->color('danger')
                ->visible(fn () => $this->record->status === 'confirmed'),
        ];
    }
}
