<?php

namespace App\Filament\Resources\Bookings\Pages;

use App\Filament\Actions\RecordManualRefundAction;
use App\Filament\Resources\Bookings\BookingResource;
use App\Filament\Resources\Bookings\HotelBookingActions;
use App\Filament\Resources\Bookings\Tables\BookingsTable;
use App\Models\Booking;
use App\Services\TripJack\TripJackClient;
use Filament\Actions\ActionGroup;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

class ViewBooking extends ViewRecord
{
    protected static string $resource = BookingResource::class;

    protected string $view = 'filament.pages.booking-view';

    /** @var array<int, array{from: string, to: string, amount: float}>|null */
    public ?array $penaltySchedule = null;

    public ?string $penaltyError = null;

    public ?string $penaltyFetchedAt = null;

    protected function getHeaderActions(): array
    {
        return [
            HotelBookingActions::checkStatus(fn (Booking $record) => $this->loadPenaltySchedule(fresh: true)),
            HotelBookingActions::downloadInvoice(),
            // Grouped so the row fits a laptop screen, as on the flight page.
            ActionGroup::make([
                HotelBookingActions::addNote(),
                EditAction::make()->label('Edit contact details'),
                RecordManualRefundAction::make(),
            ])->label('More')->icon('heroicon-m-ellipsis-vertical')->button()->color('gray'),
            // Same cancel as the list's "More" menu, so staff on a call can
            // cancel from the booking itself.
            BookingsTable::cancelAndRefundAction()->button(),
        ];
    }

    protected function resolveRecord(int|string $key): Booking
    {
        return parent::resolveRecord($key)->load('hotel', 'travelers', 'payments');
    }

    /** No schedule saved yet: fetch it just after the page opens (wire:init). */
    public bool $penaltyPending = false;

    /**
     * No TripJack call here: a slow TripJack used to hold this page for up
     * to its full timeout × retries and crash it. The last schedule fetched
     * is shown from cache; if there is none, loadPenaltyLater() fetches it
     * once the page is already on screen.
     */
    public function mount(int|string $record): void
    {
        parent::mount($record);

        $this->loadPenaltySchedule(fresh: false);
    }

    public function loadPenaltyLater(): void
    {
        $this->penaltyPending = false;
        $this->loadPenaltySchedule(fresh: true);
    }

    protected function cacheKey(): string
    {
        return 'admin_hotel_penalty_schedule:'.$this->record->id;
    }

    protected function loadPenaltySchedule(bool $fresh): void
    {
        $this->penaltyError = null;

        // The hotel cancellation schedule below doesn't apply to flights.
        if (blank($this->record->tripjack_booking_id) || $this->record->vertical === 'flight') {
            return;
        }

        $cached = Cache::get($this->cacheKey());
        if (! $fresh) {
            if ($cached) {
                [$this->penaltySchedule, $this->penaltyFetchedAt] = [$cached['slabs'], $cached['at']];
            } else {
                $this->penaltyPending = true;
            }

            return;
        }

        try {
            $details = app(TripJackClient::class)->bookingDetails($this->record->tripjack_booking_id);
        } catch (\Throwable $e) {
            $this->penaltyError = 'Couldn\'t reach TripJack right now to load the cancellation policy. Please try again in a few minutes.';

            return;
        }

        $slabs = $details['itemInfos']['HOTEL']['hInfo']['ops'][0]['cnp']['pd'] ?? [];
        if (empty($slabs)) {
            $this->penaltyError = 'TripJack did not return a cancellation schedule for this booking.';

            return;
        }

        $this->penaltySchedule = collect($slabs)->map(fn (array $slab) => [
            'from' => Carbon::parse($slab['fdt'])->format('d-M, g:i A'),
            'to' => Carbon::parse($slab['tdt'])->format('d-M, g:i A'),
            'amount' => (float) ($slab['am'] ?? 0),
        ])->all();
        $this->penaltyFetchedAt = now()->format('M j, Y g:i A');

        // The schedule doesn't change after booking — keep it until the stay is over.
        $until = $this->record->check_out ? Carbon::parse($this->record->check_out)->endOfDay() : null;
        Cache::put($this->cacheKey(), ['slabs' => $this->penaltySchedule, 'at' => $this->penaltyFetchedAt], $until && $until->isFuture() ? $until->addDays(7) : now()->addDays(7));
    }
}
