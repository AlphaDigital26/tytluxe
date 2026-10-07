<?php

namespace App\Filament\Resources\Bookings\Pages;

use App\Filament\Resources\Bookings\BookingResource;
use App\Models\Booking;
use App\Services\TripJack\TripJackClient;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Carbon;

class ViewBooking extends ViewRecord
{
    protected static string $resource = BookingResource::class;

    protected string $view = 'filament.pages.booking-view';

    /** @var array<int, array{from: string, to: string, amount: float}>|null */
    public ?array $penaltySchedule = null;

    public ?string $penaltyError = null;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }

    protected function resolveRecord(int|string $key): Booking
    {
        return parent::resolveRecord($key)->load('hotel', 'travelers');
    }

    public function mount(int|string $record): void
    {
        parent::mount($record);

        // The hotel cancellation schedule below doesn't apply to flights.
        if (blank($this->record->tripjack_booking_id) || $this->record->vertical === 'flight') {
            return;
        }

        try {
            $details = app(TripJackClient::class)->bookingDetails($this->record->tripjack_booking_id);
        } catch (\Throwable $e) {
            $this->penaltyError = "Couldn't reach TripJack right now to fetch the live cancellation policy: {$e->getMessage()}";

            return;
        }

        $op = $details['itemInfos']['HOTEL']['hInfo']['ops'][0] ?? null;
        $slabs = $op['cnp']['pd'] ?? [];

        if (empty($slabs)) {
            $this->penaltyError = 'TripJack did not return a cancellation schedule for this booking.';

            return;
        }

        $this->penaltySchedule = collect($slabs)->map(fn (array $slab) => [
            'from' => Carbon::parse($slab['fdt'])->format('d-M, g:i A'),
            'to' => Carbon::parse($slab['tdt'])->format('d-M, g:i A'),
            'amount' => (float) ($slab['am'] ?? 0),
        ])->all();
    }
}
