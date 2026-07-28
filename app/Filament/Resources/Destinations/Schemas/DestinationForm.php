<?php

namespace App\Filament\Resources\Destinations\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class DestinationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('slug')
                    ->required(),
                TextInput::make('country')
                    ->required(),
                Select::make('type')
                    ->options(['city' => 'City', 'region' => 'Region', 'island' => 'Island', 'other' => 'Other'])
                    ->default('city')
                    ->required(),
                TextInput::make('lat')
                    ->numeric()
                    ->default(null),
                TextInput::make('lng')
                    ->numeric()
                    ->default(null),
                Toggle::make('is_active')
                    ->required(),
            ]);
    }
}
