<?php

namespace App\Filament\Resources\CabBookings;

use App\Filament\Resources\CabBookings\Pages\ListCabBookings;
use App\Filament\Resources\CabBookings\Pages\ViewCabBooking;
use App\Filament\Resources\CabBookings\Tables\CabBookingsTable;
use App\Filament\Resources\Concerns\SearchesBookingsGlobally;
use App\Models\Booking;
use App\Support\CabBookingStatus;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/**
 * Cab bookings (TripJack Cabs), in plain words for non-technical staff.
 * Guests make bookings on the website; staff act through CabBookingActions.
 */
class CabBookingResource extends Resource
{
    use SearchesBookingsGlobally;

    protected static ?string $model = Booking::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Bookings & Leads';

    protected static ?int $navigationSort = 5;

    protected static ?string $navigationLabel = 'Cab Bookings';

    protected static ?string $slug = 'cab-bookings';

    protected static ?string $modelLabel = 'cab booking';

    protected static ?string $pluralModelLabel = 'cab bookings';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-truck';

    protected static ?string $recordTitleAttribute = 'reference';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('vertical', 'cab');
    }

    protected static function globalSearchTrip(Booking $record): ?string
    {
        return $record->flight_route;
    }

    protected static function globalSearchStatus(Booking $record): string
    {
        return CabBookingStatus::for($record)['label'];
    }

    public static function getNavigationBadge(): ?string
    {
        $count = CabBookingStatus::applyNeedsAttention(static::getEloquentQuery())->count();

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
        return CabBookingsTable::configure($table);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCabBookings::route('/'),
            'view' => ViewCabBooking::route('/{record}'),
        ];
    }
}
