<?php

namespace App\Filament\Resources\Cruises\Schemas;

use App\Models\Amenity;
use App\Models\Destination;
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
                    ->label('Cruise Name')
                    ->required(),

                Select::make('destination_id')
                    ->label('Destination')
                    ->helperText('Select the main destination for this cruise. Add new ones under Content → Destinations → Cruises tab.')
                    ->relationship('destination', 'name', fn ($query) => $query->where('for', 'cruise')->where('is_active', true)->orderBy('name'))
                    ->searchable()
                    ->preload()
                    ->nullable(),

                TextInput::make('slug')
                    ->label('URL Slug')
                    ->helperText('Auto-filled — only change if needed')
                    ->required(),

                Textarea::make('description')
                    ->label('Description')
                    ->required()
                    ->columnSpanFull(),

                TextInput::make('cruise_line')
                    ->label('Cruise Line')
                    ->helperText('e.g. Cordelia Cruises, Royal Caribbean')
                    ->required(),

                Select::make('category')
                    ->label('Category')
                    ->options([
                        'scenic_getaway' => '🌊  Scenic Getaway',
                        'luxury'         => '✨  Luxury',
                        'international'  => '🌏  International',
                    ])
                    ->required()
                    ->native(false),

                TextInput::make('duration_nights')
                    ->label('Duration (Nights)')
                    ->numeric()
                    ->required(),

                TextInput::make('price_from')
                    ->label('Starting Price (₹ per person)')
                    ->numeric()
                    ->required(),

                Toggle::make('is_active')
                    ->label('Visible on Website')
                    ->helperText('Turn on to show this cruise to visitors')
                    ->required(),

                // ── Amenities (cruise type only) ──────────────────────────
                Select::make('amenities')
                    ->label('Cruise Amenities / Onboard Features')
                    ->helperText('Select what this cruise offers. Add new ones under Content → Amenities.')
                    ->relationship('amenities', 'name', fn ($query) => $query->where('type', 'cruise')->orderBy('name'))
                    ->multiple()
                    ->preload()
                    ->searchable()
                    ->columnSpanFull(),

            ]);
    }
}
