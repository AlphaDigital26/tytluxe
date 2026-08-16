<?php

namespace App\Filament\Resources\Hotels\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\RichEditor;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Grid;
use Illuminate\Support\Str;
use Filament\Schemas\Schema;

class HotelForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Hotel Details')
                    ->tabs([
                        Tab::make('General Information')
                            ->icon('heroicon-o-information-circle')
                            ->schema([
                                Grid::make(2)->schema([
                                    Select::make('destination_id')
                                        ->label('Destination')
                                        ->helperText('City or region where this hotel is located')
                                        ->relationship('destination', 'name', fn ($query) => $query->whereJsonContains('for', 'hotel')->where('is_active', true)->orderBy('name'))
                                        ->required()
                                        ->searchable()
                                        ->preload(),

                                    Select::make('category')
                                        ->label('Hotel Category')
                                        ->options([
                                            'beach_resort'    => '🏖️  Beach Resort',
                                            'city_luxury'     => '🏙️  City Luxury',
                                            'honeymoon'       => '💑  Honeymoon',
                                            'family_friendly' => '👨‍👩‍👧  Family Friendly',
                                        ])
                                        ->required()
                                        ->native(false),

                                    TextInput::make('title')
                                        ->label('Hotel Name')
                                        ->required()
                                        ->live(onBlur: true)
                                        ->afterStateUpdated(fn ($set, ?string $state) => $set('slug', Str::slug($state)))
                                        ->columnSpan(2),

                                    RichEditor::make('description')
                                        ->label('Description')
                                        ->helperText('A brief description that appears on the hotel detail page. You can make text bold, add bullet points, etc.')
                                        ->required()
                                        ->columnSpan(2)
                                        ->toolbarButtons([
                                            'bold', 'italic', 'bulletList', 'orderedList', 'h2', 'h3', 'link', 'redo', 'undo',
                                        ]),

                                    TextInput::make('slug')
                                        ->label('URL Slug (Advanced)')
                                        ->helperText('Auto-generated. Only edit if you know what you are doing.')
                                        ->required()
                                        ->unique(ignoreRecord: true)
                                        ->columnSpan(2),
                                ]),
                            ]),

                        Tab::make('Pricing & Rating')
                            ->icon('heroicon-o-currency-rupee')
                            ->schema([
                                Grid::make(2)->schema([
                                    TextInput::make('price_from')
                                        ->label('Starting Price (Per Night)')
                                        ->helperText('Leave as 0 to show "Price on request"')
                                        ->numeric()
                                        ->required()
                                        ->prefix('₹'),

                                    Select::make('star_rating')
                                        ->label('Star Rating')
                                        ->helperText('Official classification of the hotel')
                                        ->options([
                                            1 => '⭐ 1 Star',
                                            2 => '⭐⭐ 2 Stars',
                                            3 => '⭐⭐⭐ 3 Stars',
                                            4 => '⭐⭐⭐⭐ 4 Stars',
                                            5 => '⭐⭐⭐⭐⭐ 5 Stars',
                                        ])
                                        ->required()
                                        ->native(false),
                                ]),
                            ]),

                        Tab::make('Location & Rules')
                            ->icon('heroicon-o-map-pin')
                            ->schema([
                                Grid::make(2)->schema([
                                    TextInput::make('address')
                                        ->label('Full Address')
                                        ->required()
                                        ->columnSpan(2),

                                    TextInput::make('check_in_time')
                                        ->label('Check-in Time')
                                        ->default('2:00 PM')
                                        ->required(),

                                    TextInput::make('check_out_time')
                                        ->label('Check-out Time')
                                        ->default('11:00 AM')
                                        ->required(),

                                    TextInput::make('lat')
                                        ->label('Latitude (Map location)')
                                        ->helperText('e.g. 31.1048. Find on Google Maps.')
                                        ->numeric()
                                        ->default(null),

                                    TextInput::make('lng')
                                        ->label('Longitude (Map location)')
                                        ->helperText('e.g. 77.1734. Find on Google Maps.')
                                        ->numeric()
                                        ->default(null),
                                ]),
                            ]),

                        Tab::make('Content & Amenities')
                            ->icon('heroicon-o-sparkles')
                            ->schema([
                                Select::make('amenities')
                                    ->label('Hotel Amenities')
                                    ->helperText('Select amenities this hotel offers.')
                                    ->relationship('amenities', 'name', fn ($query) => $query->where('type', 'hotel')->orderBy('name'))
                                    ->multiple()
                                    ->preload()
                                    ->searchable()
                                    ->columnSpanFull(),

                                Textarea::make('room_categories')
                                    ->label('Room Types')
                                    ->helperText('Enter one room type per line — e.g. Deluxe Room, Suite, Penthouse')
                                    ->columnSpanFull()
                                    ->rows(3)
                                    ->nullable(),

                                Textarea::make('nearby_attractions')
                                    ->label('Nearby Attractions')
                                    ->helperText('Enter one attraction per line — e.g. Shimla Mall Road (1 km)')
                                    ->columnSpanFull()
                                    ->rows(3)
                                    ->nullable(),
                            ]),

                        Tab::make('Photos')
                            ->icon('heroicon-o-photo')
                            ->schema([
                                Repeater::make('images')
                                    ->label('Hotel Photos')
                                    ->helperText('Upload photos of the hotel. The first photo is used as the cover image.')
                                    ->relationship('images')
                                    ->schema([
                                        FileUpload::make('path')
                                            ->label('Photo')
                                            ->image()
                                            ->directory('hotels')
                                            ->disk('public')
                                            ->required(),
                                        TextInput::make('alt_text')
                                            ->label('Photo Caption (optional)')
                                            ->nullable(),
                                    ])
                                    ->grid(2)
                                    ->columnSpanFull()
                                    ->defaultItems(0)
                                    ->reorderableWithDragAndDrop(true)
                                    ->addActionLabel('Add Photo'),
                            ]),
                    ])->columnSpanFull(),

                Section::make('Visibility Settings')
                    ->schema([
                        Toggle::make('is_active')
                            ->label('Visible on Website')
                            ->helperText('Turn on to show this hotel to visitors')
                            ->default(true)
                            ->required(),

                        Toggle::make('is_featured')
                            ->label('Featured Hotel')
                            ->helperText('Featured hotels appear highlighted on the listing')
                            ->default(false)
                            ->required(),
                    ])->columns(2),
            ]);
    }
}
