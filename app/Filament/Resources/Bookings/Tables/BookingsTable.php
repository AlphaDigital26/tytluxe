<?php

namespace App\Filament\Resources\Bookings\Tables;

use App\Services\FlightBookingService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BookingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('reference')
                    ->searchable(),
                TextColumn::make('user_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('guest_email')
                    ->searchable(),
                TextColumn::make('guest_phone')
                    ->searchable(),
                TextColumn::make('vertical')
                    ->badge(),
                TextColumn::make('hotel_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('room_type_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('tripjack_booking_id')
                    ->searchable(),
                TextColumn::make('tripjack_hold_id')
                    ->searchable(),
                TextColumn::make('check_in')
                    ->date()
                    ->sortable(),
                TextColumn::make('check_out')
                    ->date()
                    ->sortable(),
                TextColumn::make('flight_route')
                    ->searchable(),
                TextColumn::make('flight_journey_type')
                    ->label('Journey Type')
                    ->badge()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('flight_departure_date')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('tripjack_flight_pnr')
                    ->label('PNR')
                    ->state(fn ($record) => collect($record->tripjack_flight_pnr ?? [])->implode(', ') ?: null)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('flight_ssr_status')
                    ->label('Extras Status')
                    ->badge()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('flight_reissued_at')
                    ->label('Reissued')
                    ->dateTime('M j, Y h:i A')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('pax_adults')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('pax_children')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('lead_guest_name')
                    ->label('Primary Guest')
                    ->searchable(),
                TextColumn::make('special_requests')
                    ->searchable(),
                TextColumn::make('base_amount')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('tax_amount')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('discount_amount')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('total_amount')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('currency')
                    ->searchable(),
                TextColumn::make('offer_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('status')
                    ->badge(),
                TextColumn::make('cancellation_reason')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime('M j, Y h:i A')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime('M j, Y h:i A')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('processFullRefund')
                    ->label('Process Full Refund')
                    ->icon('heroicon-o-banknotes')
                    ->color('warning')
                    ->visible(fn ($record) => $record->vertical === 'flight' && $record->status === 'confirmed' && $record->tripjack_booking_id)
                    ->requiresConfirmation()
                    ->modalDescription('This uses TripJack\'s Auto Full Refund — only use it once you\'ve confirmed with the airline/TripJack that a full refund is owed (flight cancelled by airline, DGCA policy, etc.). This is NOT the same as a guest-requested cancellation, which they do themselves.')
                    ->form([
                        Select::make('remarks')
                            ->label('Reason (must match TripJack\'s exact checklist to auto-process)')
                            ->options([
                                'Flight Cancelled by Airline' => 'Flight Cancelled by Airline',
                                'Airline rescheduled flight, revised timings are not suitable' => 'Airline rescheduled flight, revised timings are not suitable',
                                'Already cancelled by directly contacting airline customer support team' => 'Already cancelled by directly contacting airline customer support team',
                                'Airline confirmed, refund is already processed' => 'Airline confirmed, refund is already processed',
                                'Refund under DGCA policy' => 'Refund under DGCA policy',
                                'Personal loss or bereavement' => 'Personal loss or bereavement',
                                'Passenger is medically unfit for travel' => 'Passenger is medically unfit for travel',
                                'Refund under empowerment policy' => 'Refund under empowerment policy',
                            ])
                            ->required()
                            ->native(false),
                    ])
                    ->action(function (array $data, $record): void {
                        $result = app(FlightBookingService::class)->submitFullRefund($record, $data['remarks']);

                        Notification::make()
                            ->title($result['success'] ? 'Full refund submitted' : 'Could not submit full refund')
                            ->body($result['success']
                                ? 'TripJack is processing this — the booking will update automatically once it resolves.'
                                : ($result['message'] ?? 'Unknown error.'))
                            ->color($result['success'] ? 'success' : 'danger')
                            ->send();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
