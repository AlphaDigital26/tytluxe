<?php

namespace App\Filament\Resources\Concerns;

use App\Models\Booking;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * The search bar at the top of the admin finds a booking by what a calling
 * guest gives you — name, phone, email — not only by booking number.
 * Shared by the hotel, flight and package booking screens.
 */
trait SearchesBookingsGlobally
{
    public static function getGloballySearchableAttributes(): array
    {
        return ['reference', 'lead_guest_name', 'guest_phone', 'guest_email', 'tripjack_booking_id'];
    }

    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return static::getEloquentQuery();
    }

    public static function getGlobalSearchResultTitle(Model $record): string
    {
        return $record->reference.' — '.$record->lead_guest_name;
    }

    /** @param  Booking  $record */
    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return array_filter([
            'Trip' => static::globalSearchTrip($record),
            'Date' => $record->check_in ? Carbon::parse($record->check_in)->format('j M Y') : null,
            'Phone' => $record->guest_phone,
            'Status' => static::globalSearchStatus($record),
        ]);
    }

    abstract protected static function globalSearchTrip(Booking $record): ?string;

    abstract protected static function globalSearchStatus(Booking $record): string;
}
