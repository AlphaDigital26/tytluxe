<?php

namespace App\Filament\Resources\CabBookings\Tables;

use App\Filament\Actions\RecordManualRefundAction;
use App\Filament\Resources\CabBookings\CabBookingActions;
use App\Filament\Resources\FlightBookings\Tables\FlightBookingsTable;
use App\Models\Booking;
use App\Services\CabBookingService;
use App\Support\CabBookingStatus;
use Filament\Actions\ActionGroup;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CabBookingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with('payments'))
            ->columns([
                TextColumn::make('phone_summary')
                    ->label('Booking')
                    ->state(fn (Booking $record) => $record->lead_guest_name)
                    ->description(fn (Booking $record) => $record->reference.' · '.$record->guest_phone)
                    ->hiddenFrom('md'),
                TextColumn::make('reference')->visibleFrom('md')
                    ->label('Booking No.')
                    ->searchable()
                    ->copyable()
                    ->weight('bold'),
                TextColumn::make('lead_guest_name')->visibleFrom('md')
                    ->label('Passenger')
                    ->description(fn (Booking $record) => $record->guest_phone)
                    ->searchable(['lead_guest_name', 'guest_email', 'guest_phone']),
                TextColumn::make('flight_route')->visibleFrom('md')
                    ->label('Trip')
                    ->description(fn (Booking $record) => (CabBookingService::JOURNEY_TYPES[$record->cab_details['journeyType'] ?? ''] ?? 'Cab')
                        .' · '.(! empty($record->cab_details['roundTrip']) ? 'Round trip' : 'One way'))
                    ->searchable(),
                TextColumn::make('cab_pickup_at')->visibleFrom('md')
                    ->label('Pickup')
                    ->dateTime('D, j M Y, g:i A')
                    ->sortable(),
                TextColumn::make('pax_adults')->visibleFrom('md')
                    ->label('Travellers'),
                TextColumn::make('total_amount')
                    ->label('Guest paid')
                    ->money('INR')
                    ->description(fn (Booking $record) => FlightBookingsTable::paidNote($record))
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (Booking $record) => CabBookingStatus::for($record)['label'])
                    ->color(fn (Booking $record) => CabBookingStatus::for($record)['color'])
                    ->tooltip(fn (Booking $record) => CabBookingStatus::for($record)['help']),
                TextColumn::make('created_at')->visibleFrom('md')
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
            ])
            ->defaultSort('created_at', 'desc')
            ->searchPlaceholder('Search by booking no., name, phone, email or place')
            ->filters([
                SelectFilter::make('status')->label('Status')->options(CabBookingStatus::filterOptions()),
                Filter::make('pickup')
                    ->label('Pickup date')
                    ->schema([
                        DatePicker::make('from')->label('Pickup from'),
                        DatePicker::make('until')->label('Pickup until'),
                    ])
                    ->query(fn (Builder $query, array $data) => $query
                        ->when($data['from'] ?? null, fn (Builder $q, $date) => $q->whereDate('cab_pickup_at', '>=', $date))
                        ->when($data['until'] ?? null, fn (Builder $q, $date) => $q->whereDate('cab_pickup_at', '<=', $date))),
            ])
            ->recordActionsPosition(\Filament\Tables\Enums\RecordActionsPosition::BeforeColumns)
            ->recordActions([
                ViewAction::make()->label('Open')->extraAttributes(['class' => 'tyt-hide-on-phone']),
                ActionGroup::make([
                    CabBookingActions::checkStatus(),
                    CabBookingActions::downloadInvoice(),
                    CabBookingActions::addNote(),
                    RecordManualRefundAction::make(),
                    CabBookingActions::cancelAndRefund(),
                ])->label('More')->button()->color('gray')->extraAttributes(['class' => 'tyt-hide-on-phone']),
            ])
            ->emptyStateHeading('No cab bookings here')
            ->emptyStateDescription('Cab bookings made by guests on the website will appear here.')
            ->emptyStateIcon('heroicon-o-truck');
    }
}
