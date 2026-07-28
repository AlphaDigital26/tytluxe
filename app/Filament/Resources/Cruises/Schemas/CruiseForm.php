<?php

namespace App\Filament\Resources\Cruises\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CruiseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->required(),
                TextInput::make('slug')
                    ->required(),
                Textarea::make('description')
                    ->required()
                    ->columnSpanFull(),
                TextInput::make('cruise_line')
                    ->required(),
                Select::make('category')
                    ->options(['scenic_getaway' => 'Scenic getaway', 'luxury' => 'Luxury', 'international' => 'International'])
                    ->required(),
                TextInput::make('duration_nights')
                    ->required()
                    ->numeric(),
                TextInput::make('price_from')
                    ->required()
                    ->numeric(),
                Toggle::make('is_active')
                    ->required(),
            ]);
    }
}
