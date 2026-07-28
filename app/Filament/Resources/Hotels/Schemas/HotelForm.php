<?php

namespace App\Filament\Resources\Hotels\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class HotelForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('destination_id')
                    ->required()
                    ->numeric(),
                TextInput::make('title')
                    ->required(),
                TextInput::make('slug')
                    ->required(),
                Textarea::make('description')
                    ->required()
                    ->columnSpanFull(),
                Select::make('category')
                    ->options([
            'beach_resort' => 'Beach resort',
            'city_luxury' => 'City luxury',
            'honeymoon' => 'Honeymoon',
            'family_friendly' => 'Family friendly',
        ])
                    ->required(),
                TextInput::make('address')
                    ->required(),
                TextInput::make('lat')
                    ->numeric()
                    ->default(null),
                TextInput::make('lng')
                    ->numeric()
                    ->default(null),
                TextInput::make('star_rating')
                    ->required()
                    ->numeric(),
                TextInput::make('price_from')
                    ->required()
                    ->numeric(),
                Select::make('source')
                    ->options(['tripjack' => 'Tripjack', 'manual' => 'Manual'])
                    ->default('manual')
                    ->required(),
                TextInput::make('tripjack_hotel_id')
                    ->tel()
                    ->default(null),
                Toggle::make('is_active')
                    ->required(),
                Toggle::make('is_featured')
                    ->required(),
            ]);
    }
}
