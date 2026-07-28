<?php

namespace App\Filament\Resources\Enquiries\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class EnquiryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('user_id')
                    ->numeric()
                    ->default(null),
                Select::make('vertical')
                    ->options([
            'hotel' => 'Hotel',
            'flight' => 'Flight',
            'cruise' => 'Cruise',
            'staycation' => 'Staycation',
            'package' => 'Package',
            'general' => 'General',
        ])
                    ->required(),
                TextInput::make('reference_id')
                    ->numeric()
                    ->default(null),
                TextInput::make('name')
                    ->required(),
                TextInput::make('phone')
                    ->tel()
                    ->required(),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->required(),
                DatePicker::make('travel_date_from'),
                DatePicker::make('travel_date_to'),
                TextInput::make('pax_adults')
                    ->required()
                    ->numeric()
                    ->default(1),
                TextInput::make('pax_children')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('notes')
                    ->default(null),
                Select::make('status')
                    ->options([
            'new' => 'New',
            'contacted' => 'Contacted',
            'quoted' => 'Quoted',
            'converted' => 'Converted',
            'closed' => 'Closed',
        ])
                    ->default('new')
                    ->required(),
                TextInput::make('assigned_agent_id')
                    ->numeric()
                    ->default(null),
                Select::make('source')
                    ->options(['web' => 'Web', 'whatsapp' => 'Whatsapp', 'phone' => 'Phone'])
                    ->default('web')
                    ->required(),
            ]);
    }
}
