<?php

namespace App\Filament\Resources\FlightBookings;

use App\Filament\Resources\FlightBookings\Pages\ListFlightBookings;
use App\Filament\Resources\FlightBookings\Pages\ViewFlightBooking;
use App\Filament\Resources\FlightBookings\Tables\FlightBookingsTable;
use App\Models\Booking;
use App\Support\FlightBookingStatus;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/**
 * Flight bookings only, in plain words, for non-technical staff. Bookings
 * are made by guests on the website, so there is no create/edit form here —
 * staff act on a booking through the buttons in FlightBookingActions.
 */
class FlightBookingResource extends Resource
{
    protected static ?string $model = Booking::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Bookings & Leads';

    protected static ?int $navigationSort = 4;

    protected static ?string $navigationLabel = 'Flight Bookings';

    protected static ?string $slug = 'flight-bookings';

    protected static ?string $modelLabel = 'flight booking';

    protected static ?string $pluralModelLabel = 'flight bookings';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-paper-airplane';

    protected static ?string $recordTitleAttribute = 'reference';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('vertical', 'flight');
    }

    /** Red badge on the menu item when something needs a person to look. */
    public static function getNavigationBadge(): ?string
    {
        $count = FlightBookingStatus::applyNeedsAttention(static::getEloquentQuery())->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Bookings that need your attention';
    }

    public static function table(Table $table): Table
    {
        return FlightBookingsTable::configure($table);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListFlightBookings::route('/'),
            'view' => ViewFlightBooking::route('/{record}'),
        ];
    }
}
