<?php

namespace App\Filament\Resources\Destinations\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Illuminate\Support\Str;
use Filament\Schemas\Schema;

class DestinationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                Section::make('Destination Details')
                    ->description('Enter the basic information about this destination.')
                    ->schema([
                        Grid::make(2)->schema([

                            TextInput::make('name')
                                ->label('Destination Name')
                                ->helperText('E.g. Goa, Maldives, Kerala')
                                ->required()
                                ->live(onBlur: true)
                                ->afterStateUpdated(fn ($set, ?string $state) => $set('slug', Str::slug($state)))
                                ->columnSpan(2),

                            TextInput::make('slug')
                                ->label('URL Slug')
                                ->helperText('This forms the web address. Auto-generated from the name above.')
                                ->required()
                                ->unique(ignoreRecord: true)
                                ->columnSpan(2),

                            TextInput::make('country')
                                ->label('Country')
                                ->helperText('E.g. India, Maldives, Thailand')
                                ->required(),

                            Select::make('type')
                                ->label('Location Type')
                                ->helperText('What kind of location is this?')
                                ->options([
                                    'city'   => '🏙️  City',
                                    'region' => '🗺️  Region',
                                    'island' => '🏝️  Island',
                                    'other'  => '📍  Other',
                                ])
                                ->default('city')
                                ->required()
                                ->native(false),

                        ]),
                    ]),

                Section::make('Usage')
                    ->description('Which section of the website is this destination for?')
                    ->schema([
                        Select::make('for')
                            ->label('Used For')
                            ->helperText('Select which sections this destination belongs to.')
                            ->options([
                                'hotel'   => '🏨  Hotels',
                                'cruise'  => '🚢  Cruises',
                                'package' => '📦  Packages',
                            ])
                            ->multiple()
                            ->default(['hotel'])
                            ->required(),
                    ]),

                Section::make('Visibility & Coordinates')
                    ->schema([
                        Grid::make(2)->schema([
                            Toggle::make('is_active')
                                ->label('Visible on Website')
                                ->helperText('Turn off to hide this destination from dropdowns and the website.')
                                ->default(true)
                                ->required(),
                        ]),

                        Grid::make(2)->schema([
                            TextInput::make('lat')
                                ->label('Latitude (optional)')
                                ->helperText('Used for map display if needed.')
                                ->numeric()
                                ->default(null),

                            TextInput::make('lng')
                                ->label('Longitude (optional)')
                                ->helperText('Used for map display if needed.')
                                ->numeric()
                                ->default(null),
                        ]),
                    ]),

            ]);
    }
}
