<?php

namespace App\Filament\Resources\Offers\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class OfferForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->required(),
                Textarea::make('description')
                    ->required()
                    ->columnSpanFull(),
                Select::make('discount_type')
                    ->options(['percentage' => 'Percentage', 'fixed' => 'Fixed'])
                    ->required(),
                TextInput::make('discount_value')
                    ->required()
                    ->numeric(),
                TextInput::make('promo_code')
                    ->default(null),
                Select::make('applies_to_vertical')
                    ->options([
            'hotel' => 'Hotel',
            'flight' => 'Flight',
            'cruise' => 'Cruise',
            'staycation' => 'Staycation',
            'package' => 'Package',
            'all' => 'All',
        ])
                    ->required(),
                TextInput::make('applies_to_reference_id')
                    ->numeric()
                    ->default(null),
                DatePicker::make('valid_from')
                    ->required(),
                DatePicker::make('valid_to')
                    ->required(),
                Toggle::make('is_active')
                    ->required(),
            ]);
    }
}
