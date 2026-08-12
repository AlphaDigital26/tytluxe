<?php

namespace App\Filament\Resources\Hotels\Schemas;

use App\Models\Amenity;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class HotelForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                // ── Basic Info ────────────────────────────────────────────
                Select::make('destination_id')
                    ->label('Destination')
                    ->helperText('City / region where this hotel is located')
                    ->relationship('destination', 'name', fn ($query) => $query->orderBy('name'))
                    ->required()
                    ->searchable()
                    ->preload(),

                TextInput::make('title')
                    ->label('Hotel Name')
                    ->required(),

                TextInput::make('slug')
                    ->label('URL Slug')
                    ->helperText('Auto-filled — only change if needed')
                    ->required(),

                Textarea::make('description')
                    ->label('Description')
                    ->helperText('A brief description that appears on the hotel detail page')
                    ->required()
                    ->columnSpanFull(),

                // ── Classification ────────────────────────────────────────
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

                TextInput::make('address')
                    ->label('Address')
                    ->required(),

                TextInput::make('star_rating')
                    ->label('Star Rating')
                    ->helperText('Enter a number from 1 to 5')
                    ->numeric()
                    ->minValue(1)
                    ->maxValue(5)
                    ->required(),

                TextInput::make('price_from')
                    ->label('Starting Price (₹ per night)')
                    ->helperText('Leave 0 if price is on request')
                    ->numeric()
                    ->required(),

                // ── Status ────────────────────────────────────────────────
                Toggle::make('is_active')
                    ->label('Visible on Website')
                    ->helperText('Turn on to show this hotel to visitors')
                    ->required(),

                Toggle::make('is_featured')
                    ->label('Featured Hotel')
                    ->helperText('Featured hotels appear highlighted on the listing')
                    ->required(),

                // ── Check-in / Check-out ──────────────────────────────────
                TextInput::make('check_in_time')
                    ->label('Check-in Time')
                    ->default('2:00 PM')
                    ->required(),

                TextInput::make('check_out_time')
                    ->label('Check-out Time')
                    ->default('11:00 AM')
                    ->required(),

                // ── Amenities (filtered to hotel type only) ───────────────
                Select::make('amenities')
                    ->label('Hotel Amenities')
                    ->helperText('Select amenities this hotel offers. Add new ones under Content → Amenities.')
                    ->relationship('amenities', 'name', fn ($query) => $query->where('type', 'hotel')->orderBy('name'))
                    ->multiple()
                    ->preload()
                    ->searchable()
                    ->columnSpanFull(),

                // ── Extra Details ─────────────────────────────────────────
                Textarea::make('room_categories')
                    ->label('Room Types')
                    ->helperText('Enter one room type per line — e.g. Deluxe Room, Suite, Penthouse')
                    ->columnSpanFull()
                    ->nullable(),

                Textarea::make('nearby_attractions')
                    ->label('Nearby Attractions')
                    ->helperText('Enter one attraction per line — e.g. Shimla Mall Road (1 km)')
                    ->columnSpanFull()
                    ->nullable(),

                // ── Location co-ordinates (optional) ─────────────────────
                TextInput::make('lat')
                    ->label('Latitude (optional)')
                    ->numeric()
                    ->default(null),

                TextInput::make('lng')
                    ->label('Longitude (optional)')
                    ->numeric()
                    ->default(null),

                // ── Source (internal) ─────────────────────────────────────
                Select::make('source')
                    ->label('Data Source')
                    ->helperText('Leave as Manual unless you know what this means')
                    ->options(['tripjack' => 'Tripjack API', 'manual' => 'Manual Entry'])
                    ->default('manual')
                    ->required()
                    ->native(false),

                TextInput::make('tripjack_hotel_id')
                    ->label('Tripjack Hotel ID (optional)')
                    ->default(null),

                // ── Images ────────────────────────────────────────────────
                Repeater::make('images')
                    ->label('Hotel Photos')
                    ->helperText('Upload photos of the hotel. The first photo appears as the main image.')
                    ->relationship('images')
                    ->schema([
                        FileUpload::make('path')
                            ->label('Photo')
                            ->image()
                            ->directory('hotels')
                            ->required(),
                        TextInput::make('alt_text')
                            ->label('Photo Caption (optional)')
                            ->nullable(),
                    ])
                    ->columnSpanFull()
                    ->defaultItems(0)
                    ->reorderableWithDragAndDrop(true)
                    ->addActionLabel('Add Photo'),

            ]);
    }
}
