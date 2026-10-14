<?php

namespace App\Filament\Resources\Bookings;

use App\Filament\Resources\Bookings\Pages\EditBooking;
use App\Filament\Resources\Bookings\Pages\ListBookings;
use App\Filament\Resources\Bookings\Pages\ViewBooking;
use App\Filament\Resources\Bookings\Schemas\BookingForm;
use App\Filament\Resources\Bookings\Tables\BookingsTable;
use App\Models\Booking;
use App\Support\HotelBookingStatus;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class BookingResource extends Resource
{
    protected static ?string $model = Booking::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Bookings & Leads';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Hotel Bookings';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $recordTitleAttribute = 'reference';

    protected static ?string $modelLabel = 'hotel booking';

    /** Flights and packages have their own screens under Bookings & Leads. */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where(fn ($q) => $q->whereNotIn('vertical', ['flight', 'package'])->orWhereNull('vertical'));
    }

    /** Bookings come from guests on the website — never created by hand. */
    public static function canCreate(): bool
    {
        return false;
    }

    /** Red badge on the menu item when a booking needs a person to look. */
    public static function getNavigationBadge(): ?string
    {
        $count = HotelBookingStatus::applyNeedsAttention(static::getEloquentQuery())->count();

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

    public static function form(Schema $schema): Schema
    {
        return BookingForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BookingsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBookings::route('/'),
            'view' => ViewBooking::route('/{record}'),
            'edit' => EditBooking::route('/{record}/edit'),
        ];
    }
}
