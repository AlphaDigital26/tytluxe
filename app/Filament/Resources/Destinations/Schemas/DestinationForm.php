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
                                ->afterStateUpdated(fn ($set, ?string $state, string $operation) => $operation === 'create' ? $set('slug', Str::slug($state)) : null) // only on create: a new slug breaks old links
                                ->columnSpan(2),

                            TextInput::make('slug')
                                ->label('Web address')
                                ->helperText(fn (string $operation) => $operation === 'create' ? 'This forms the web address. Auto-generated from the name above.' : 'This is the web address of the page. Changing it breaks links people already have (Google, WhatsApp shares) — only change it if you must.')
                                ->required()
                                ->unique(ignoreRecord: true)
                                ->columnSpan(2),

                            TextInput::make('country')
                                ->label('Country')
                                ->helperText('E.g. India, Maldives, Thailand')
                                ->required(),

                            TextInput::make('state')
                                ->label('State / Province (optional)')
                                ->helperText('E.g. Rajasthan, Goa, Himachal Pradesh — lets guests find this destination by typing the state name in search.'),

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
                            // Pre-selected from the Destinations tab the admin came from
                            // ("Add Destination" on the Packages tab → Packages). Only a
                            // starting choice: whatever the admin picks is what's saved.
                            ->default(fn () => in_array($tab = request()->query('tab'), ['hotel', 'cruise', 'package'], true) ? [$tab] : ['hotel'])
                            ->required(),
                    ]),

                Section::make('Visibility')
                    ->schema([
                        Grid::make(2)->schema([
                            Toggle::make('is_active')
                                ->label('Visible on Website')
                                ->helperText('Turn off to hide this destination from dropdowns and the website.')
                                ->default(true)
                                ->required(),
                        ]),
                    ]),

                // Rarely needed — tucked away so the form stays short.
                Section::make('Map position (optional)')
                    ->description('Only needed if this destination should appear on a map. You can copy the two numbers from Google Maps.')
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('lat')
                                ->label('Latitude')
                                ->numeric()
                                ->default(null),

                            TextInput::make('lng')
                                ->label('Longitude')
                                ->numeric()
                                ->default(null),
                        ]),
                    ]),

            ]);
    }
}
