<?php

namespace App\Filament\Resources\Amenities\Schemas;

use App\Models\Amenity;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class AmenityForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([

                Select::make('type')
                    ->label('Section / Category')
                    ->helperText('Which section does this amenity belong to?')
                    ->options([
                        'hotel'   => '🏨  Hotels',
                        'cruise'  => '🚢  Cruises',
                        'package' => '📦  Packages',
                    ])
                    ->default('hotel')
                    ->required()
                    ->native(false),

                TextInput::make('name')
                    ->label('Amenity Name')
                    ->helperText('e.g. Free WiFi, Swimming Pool, Breakfast Included')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(100),

                TextInput::make('icon')
                    ->label('Icon (optional)')
                    ->helperText('Paste an emoji or icon code — e.g. 🏊 or wifi')
                    ->default(null)
                    ->maxLength(50),

            ]);
    }
}
