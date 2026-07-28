<?php

namespace App\Filament\Resources\Bookings\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class BookingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('reference')
                    ->required(),
                TextInput::make('user_id')
                    ->numeric()
                    ->default(null),
                TextInput::make('guest_email')
                    ->email()
                    ->default(null),
                TextInput::make('guest_phone')
                    ->tel()
                    ->default(null),
                Select::make('vertical')
                    ->options(['hotel' => 'Hotel', 'flight' => 'Flight'])
                    ->required(),
                TextInput::make('hotel_id')
                    ->tel()
                    ->numeric()
                    ->default(null),
                TextInput::make('room_type_id')
                    ->numeric()
                    ->default(null),
                TextInput::make('tripjack_booking_id')
                    ->default(null),
                TextInput::make('tripjack_hold_id')
                    ->default(null),
                DatePicker::make('check_in'),
                DatePicker::make('check_out'),
                TextInput::make('flight_route')
                    ->default(null),
                TextInput::make('pax_adults')
                    ->required()
                    ->numeric()
                    ->default(1),
                TextInput::make('pax_children')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('lead_guest_name')
                    ->required(),
                TextInput::make('special_requests')
                    ->default(null),
                TextInput::make('base_amount')
                    ->required()
                    ->numeric(),
                TextInput::make('tax_amount')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('discount_amount')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('total_amount')
                    ->required()
                    ->numeric(),
                TextInput::make('currency')
                    ->required()
                    ->default('INR'),
                TextInput::make('offer_id')
                    ->numeric()
                    ->default(null),
                Select::make('status')
                    ->options([
            'pending_payment' => 'Pending payment',
            'confirmed' => 'Confirmed',
            'cancelled' => 'Cancelled',
            'failed_needs_review' => 'Failed needs review',
        ])
                    ->default('pending_payment')
                    ->required(),
                TextInput::make('cancellation_reason')
                    ->default(null),
                Textarea::make('admin_note')
                    ->default(null)
                    ->columnSpanFull(),
            ]);
    }
}
