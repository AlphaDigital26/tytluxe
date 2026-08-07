<?php

namespace App\Filament\Resources\Packages\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;

class PackageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->required(),
                TextInput::make('slug')
                    ->required(),
                Select::make('destination_id')
                    ->relationship('destination', 'name')
                    ->required(),
                Select::make('region_type')
                    ->options([
                        'domestic' => 'Domestic',
                        'international' => 'International',
                    ])
                    ->default('domestic')
                    ->required(),
                Select::make('tour_type')
                    ->options([
                        'group' => 'Group Tour',
                        'custom' => 'Custom Package',
                    ])
                    ->default('group')
                    ->required(),
                Textarea::make('description')
                    ->required()
                    ->columnSpanFull(),
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
