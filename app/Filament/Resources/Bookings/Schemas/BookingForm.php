<?php

namespace App\Filament\Resources\Bookings\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

/**
 * Only the parts of a booking staff may safely change by hand. Amounts,
 * status, dates and TripJack IDs are driven by TripJack and Razorpay —
 * editing them here used to be possible and could break refunds, so they
 * change only through the booking's action buttons.
 */
class BookingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Guest contact details')
                    ->description('Correct a typo in the guest\'s name, email or phone. This does not change anything with the hotel or the payment.')
                    ->columns(3)
                    ->schema([
                        TextInput::make('lead_guest_name')
                            ->label('Guest name')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('guest_email')
                            ->label('Email')
                            ->email()
                            ->required(),
                        TextInput::make('guest_phone')
                            ->label('Phone')
                            ->tel()
                            ->required(),
                    ]),
                Section::make('Internal notes')
                    ->schema([
                        Textarea::make('admin_note')
                            ->label('Notes (only your team sees these)')
                            ->rows(5)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
