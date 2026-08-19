<?php

namespace App\Filament\Resources\Offers\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class OfferForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([

            Section::make('Offer Details')
                ->schema([
                    Grid::make(2)->schema([

                        TextInput::make('title')
                            ->label('Offer Title')
                            ->required()
                            ->placeholder('Summer Hotel Discount')
                            ->columnSpan(2),

                        Textarea::make('description')
                            ->label('Description')
                            ->required()
                            ->rows(3)
                            ->placeholder('Valid on all room bookings above ₹5,000...')
                            ->columnSpan(2),

                        Select::make('discount_type')
                            ->label('Discount Type')
                            ->required()
                            ->options([
                                'percentage' => '% Percentage',
                                'fixed'      => '₹ Fixed Amount',
                            ])
                            ->native(false),

                        TextInput::make('discount_value')
                            ->label('Discount Value')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->placeholder('20'),

                        TextInput::make('promo_code')
                            ->label('Promo Code (optional)')
                            ->placeholder('SUMMER20')
                            ->helperText('Leave blank if no promo code is needed.')
                            ->nullable()
                            ->unique(ignoreRecord: true)
                            ->columnSpan(2),
                    ]),
                ]),

            Section::make('Applicability')
                ->schema([
                    Grid::make(2)->schema([

                        Select::make('applies_to_vertical')
                            ->label('Applies To')
                            ->required()
                            ->options([
                                'all'       => '🌍 All Categories',
                                'hotel'     => '🏨 Hotels',
                                'flight'    => '✈️ Flights',
                                'cruise'    => '🚢 Cruises',
                                'package'   => '📦 Packages',
                                'staycation'=> '🏡 Staycations',
                            ])
                            ->native(false),

                        TextInput::make('applies_to_reference_id')
                            ->label('Specific Record ID (optional)')
                            ->numeric()
                            ->helperText('Leave blank to apply to all records in the category above.')
                            ->nullable(),
                    ]),
                ]),

            Section::make('Validity')
                ->schema([
                    Grid::make(2)->schema([

                        DatePicker::make('valid_from')
                            ->label('Valid From')
                            ->required()
                            ->native(false),

                        DatePicker::make('valid_to')
                            ->label('Valid Until')
                            ->required()
                            ->native(false)
                            ->afterOrEqual('valid_from'),
                    ]),
                ]),

            Section::make('Status')
                ->schema([
                    Toggle::make('is_active')
                        ->label('Active (visible & usable)')
                        ->default(true),
                ])->columns(1),
        ]);
    }
}
