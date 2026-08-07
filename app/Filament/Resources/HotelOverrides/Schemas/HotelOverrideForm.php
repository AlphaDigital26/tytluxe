<?php

namespace App\Filament\Resources\HotelOverrides\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class HotelOverrideForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('hotel_id')
                    ->label('Tripjack Hotel ID (e.g. TBOM000000)')
                    ->required(),
                TextInput::make('override_name')
                    ->label('Custom Hotel Name')
                    ->default(null),
                Textarea::make('override_description')
                    ->label('Custom Description')
                    ->default(null)
                    ->columnSpanFull(),
                FileUpload::make('override_image')
                    ->label('Custom Cover Image')
                    ->image(),
                \Filament\Forms\Components\TagsInput::make('override_amenities')
                    ->label('Custom Amenities (Press enter to add)')
                    ->default(null)
                    ->columnSpanFull(),
            ]);
    }
}
