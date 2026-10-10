<?php

namespace App\Filament\Resources\FlightBookings\Tables;

use App\Filament\Actions\RecordManualRefundAction;
use App\Filament\Resources\FlightBookings\FlightBookingActions;
use App\Models\Booking;
use App\Support\FlightBookingStatus;
use Filament\Actions\ActionGroup;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class FlightBookingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('reference')
                    ->label('Booking No.')
                    ->searchable()
                    ->copyable()
                    ->weight('bold'),
                TextColumn::make('lead_guest_name')
                    ->label('Passenger')
                    ->description(fn (Booking $record) => $record->guest_phone)
                    ->searchable(['lead_guest_name', 'guest_email', 'guest_phone']),
                TextColumn::make('flight_route')
                    ->label('Journey')
                    ->formatStateUsing(fn (?string $state, Booking $record) => self::journey($record))
                    ->description(fn (Booking $record) => $record->flight_journey_type === 'RETURN' ? 'Round trip' : 'One way')
                    ->searchable(),
                TextColumn::make('flight_departure_date')
                    ->label('Travel date')
                    ->date('D, j M Y')
                    ->description(fn (Booking $record) => $record->flight_return_date ? 'Return '.$record->flight_return_date->format('j M') : null)
                    ->sortable(),
                TextColumn::make('pax')
                    ->label('Travellers')
                    ->state(fn (Booking $record) => (int) $record->pax_adults + (int) $record->pax_children + (int) $record->pax_infants),
                TextColumn::make('tripjack_flight_pnr')
                    ->label('PNR')
                    ->state(fn (Booking $record) => collect($record->tripjack_flight_pnr ?? [])->unique()->implode(', ') ?: '—')
                    ->searchable(query: fn (Builder $query, string $search) => $query->where('tripjack_flight_pnr', 'like', '%'.$search.'%'))
                    ->copyable(),
                TextColumn::make('total_amount')
                    ->label('Guest paid')
                    ->money('INR')
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (Booking $record) => FlightBookingStatus::for($record)['label'])
                    ->color(fn (Booking $record) => FlightBookingStatus::for($record)['color'])
                    ->tooltip(fn (Booking $record) => FlightBookingStatus::for($record)['help']),
                TextColumn::make('created_at')
                    ->label('Booked on')
                    ->dateTime('j M Y, g:i A')
                    ->sortable(),
                TextColumn::make('margin_amount')
                    ->label('Your earning')
                    ->money('INR')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('tripjack_booking_id')
                    ->label('TripJack ID')
                    ->searchable()
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('guest_email')
                    ->label('Email')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->searchPlaceholder('Search by booking no., name, phone, email, route or PNR')
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(FlightBookingStatus::filterOptions()),
                SelectFilter::make('flight_journey_type')
                    ->label('Trip type')
                    ->options(['ONEWAY' => 'One way', 'RETURN' => 'Round trip']),
                Filter::make('travel_date')
                    ->label('Travel date')
                    ->schema([
                        DatePicker::make('from')->label('Travelling from'),
                        DatePicker::make('until')->label('Travelling until'),
                    ])
                    ->query(fn (Builder $query, array $data) => $query
                        ->when($data['from'] ?? null, fn (Builder $q, $date) => $q->whereDate('flight_departure_date', '>=', $date))
                        ->when($data['until'] ?? null, fn (Builder $q, $date) => $q->whereDate('flight_departure_date', '<=', $date))),
                Filter::make('booked_on')
                    ->label('Booked on')
                    ->schema([
                        DatePicker::make('from')->label('Booked from'),
                        DatePicker::make('until')->label('Booked until'),
                    ])
                    ->query(fn (Builder $query, array $data) => $query
                        ->when($data['from'] ?? null, fn (Builder $q, $date) => $q->whereDate('created_at', '>=', $date))
                        ->when($data['until'] ?? null, fn (Builder $q, $date) => $q->whereDate('created_at', '<=', $date))),
            ])
            ->recordActions([
                ViewAction::make()->label('Open'),
                ActionGroup::make([
                    FlightBookingActions::checkStatus(),
                    FlightBookingActions::downloadETicket(),
                    FlightBookingActions::downloadInvoice(),
                    FlightBookingActions::addNote(),
                    RecordManualRefundAction::make(onlyWhenDue: true),
                    FlightBookingActions::releaseReservation(),
                    FlightBookingActions::cancelAndRefund(),
                ])->label('More')->button()->color('gray'),
            ])
            ->emptyStateHeading('No flight bookings here')
            ->emptyStateDescription('Flight bookings made by guests on the website will appear here.')
            ->emptyStateIcon('heroicon-o-paper-airplane');
    }

    /** "BOM → DEL" from the stored legs, falling back to the saved route text. */
    public static function journey(Booking $record): string
    {
        $legs = $record->flight_legs ?? [];
        if ($legs) {
            $first = $legs[0];
            $text = ($first['src'] ?? '?').' → '.($first['dest'] ?? '?');

            return $record->flight_journey_type === 'RETURN' ? str_replace('→', '⇄', $text) : $text;
        }

        return str_replace('-', ' → ', (string) $record->flight_route);
    }
}
