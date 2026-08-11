<?php

namespace App\Filament\Resources\Packages\Schemas;

use App\Models\Destination;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class PackageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Tabs::make('Package Details')->tabs([

                // ── Tab 1: Core Details ────────────────────────────────────────
                Tab::make('Core Details')->schema([
                    TextInput::make('title')
                        ->required()
                        ->columnSpanFull(),
                    TextInput::make('slug')
                        ->required()
                        ->unique(ignoreRecord: true),
                    TextInput::make('hero_eyebrow')
                        ->label('Hero Eyebrow Text')
                        ->placeholder('e.g. Himachal Pradesh, India'),
                    Textarea::make('description')
                        ->required()
                        ->rows(4)
                        ->columnSpanFull(),
                    Select::make('region_type')
                        ->label('Region')
                        ->options(['domestic' => 'Domestic', 'international' => 'International'])
                        ->required(),
                    Select::make('tour_type')
                        ->label('Tour Type')
                        ->options(['group' => 'Group', 'custom' => 'Custom'])
                        ->required(),
                    Select::make('destination_id')
                        ->label('Destination')
                        ->options(fn () => Destination::orderBy('name')->pluck('name', 'id'))
                        ->searchable()
                        ->nullable(),
                    Toggle::make('is_active')
                        ->label('Active / Published')
                        ->required(),
                ])->columns(2),

                // ── Tab 2: Pricing & Booking ───────────────────────────────────
                Tab::make('Pricing & Booking')->schema([
                    TextInput::make('price_from')
                        ->label('Starting Price (₹)')
                        ->required()
                        ->numeric(),
                    TextInput::make('duration_nights')
                        ->label('Duration (Nights)')
                        ->required()
                        ->numeric(),
                    TextInput::make('booking_amount')
                        ->label('Booking Amount (₹)')
                        ->numeric()
                        ->nullable(),
                    TextInput::make('departure_from')
                        ->label('Departure City')
                        ->placeholder('e.g. Delhi'),
                    TextInput::make('meals_info')
                        ->label('Meals Info')
                        ->placeholder('e.g. 2 Breakfasts + 2 Dinners (MAP AI)'),
                    TextInput::make('transport_info')
                        ->label('Transport')
                        ->placeholder('e.g. Volvo / Tempo Traveller'),
                    TextInput::make('stay_info')
                        ->label('Stay')
                        ->placeholder('e.g. Hotel in Jibhi'),
                ])->columns(2),

                // ── Tab 3: Hero Image & PDF ────────────────────────────────────
                Tab::make('Hero & PDF')->schema([
                    FileUpload::make('hero_bg_image')
                        ->label('Hero Background Image')
                        ->image()
                        ->directory('packages/heroes')
                        ->columnSpanFull(),
                    FileUpload::make('itinerary_pdf')
                        ->label('Itinerary PDF')
                        ->acceptedFileTypes(['application/pdf'])
                        ->directory('itineraries')
                        ->columnSpanFull(),
                ])->columns(1),

            ])->columnSpanFull(),
        ]);
    }
}
