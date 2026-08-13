<?php

namespace App\Filament\Resources\Packages\Schemas;

use App\Models\Amenity;
use App\Models\Destination;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class PackageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([

            Tabs::make('Package Details')
                ->tabs([

                    // ──────────────────────────────────────────────────────────
                    // TAB 1 — Basic Information
                    // ──────────────────────────────────────────────────────────
                    Tab::make('Basic Info')
                        ->icon('heroicon-o-document-text')
                        ->schema([

                            TextInput::make('title')
                                ->label('Package Title')
                                ->placeholder('e.g.  Jibhi & Tirthan Valley — 2 Nights 3 Days')
                                ->helperText('The full name of this trip, exactly as it appears on the brochure or PDF.')
                                ->required()
                                ->live(onBlur: true)
                                ->afterStateUpdated(fn (string $state, callable $set) => $set('slug', Str::slug($state)))
                                ->columnSpanFull(),

                            TextInput::make('slug')
                                ->label('URL Slug  (auto-filled from title)')
                                ->helperText('Auto-generated from the title. You can edit it, but use hyphens instead of spaces.')
                                ->required()
                                ->unique(ignoreRecord: true),

                            TextInput::make('hero_eyebrow')
                                ->label('Location Tag')
                                ->placeholder('e.g.  Himachal Pradesh, India')
                                ->helperText('Short location line shown above the title on the website.'),

                            Textarea::make('description')
                                ->label('Package Description')
                                ->placeholder('Write 2–4 sentences describing the essence of this trip...')
                                ->helperText('Copy the main overview paragraph from the PDF. Appears under the title on the website.')
                                ->rows(5)
                                ->columnSpanFull(),

                            Select::make('region_type')
                                ->label('Region')
                                ->options([
                                    'domestic'      => 'Domestic  (within India)',
                                    'international' => 'International  (outside India)',
                                ])
                                ->helperText('Is this trip within India or abroad?')
                                ->required(),

                            Select::make('tour_type')
                                ->label('Tour Type')
                                ->options([
                                    'group'  => 'Group Tour  (fixed departure dates)',
                                    'custom' => 'Custom / Private Tour  (tailor-made)',
                                ])
                                ->helperText('Group tours go on scheduled dates. Custom tours are arranged per group.')
                                ->required(),

                            Select::make('destination_id')
                                ->label('Destination')
                                ->relationship('destination', 'name', fn ($query) => $query->where('for', 'package')->where('is_active', true)->orderBy('name'))
                                ->searchable()
                                ->preload()
                                ->nullable()
                                ->helperText('Choose the main destination for this package.'),

                        ])->columns(2),

                    // ──────────────────────────────────────────────────────────
                    // TAB 2 — Pricing & Logistics
                    // ──────────────────────────────────────────────────────────
                    Tab::make('Pricing')
                        ->icon('heroicon-o-banknotes')
                        ->schema([

                            TextInput::make('price_from')
                                ->label('Starting Price  (₹)')
                                ->placeholder('e.g.  6999')
                                ->helperText('The lowest per-person price shown on the PDF.')
                                ->required()
                                ->numeric()
                                ->prefix('₹'),

                            TextInput::make('duration_nights')
                                ->label('Duration  (number of nights)')
                                ->placeholder('e.g.  2')
                                ->helperText('A "2 Night 3 Day" trip = 2 nights.')
                                ->required()
                                ->numeric()
                                ->suffix('nights'),

                            TextInput::make('booking_amount')
                                ->label('Advance Booking Amount  (₹)')
                                ->placeholder('e.g.  2000')
                                ->helperText('The partial amount a customer pays to confirm their spot.')
                                ->numeric()
                                ->prefix('₹')
                                ->nullable(),

                            TextInput::make('departure_from')
                                ->label('Departure City')
                                ->placeholder('e.g.  Delhi')
                                ->helperText('The city from which the trip begins.'),

                            TextInput::make('meals_info')
                                ->label('Meals Included')
                                ->placeholder('e.g.  2 Breakfasts + 2 Dinners  (MAP AI)')
                                ->helperText('Copy directly from the PDF meals section.'),

                            TextInput::make('transport_info')
                                ->label('Transport')
                                ->placeholder('e.g.  Volvo Bus / Tempo Traveller')
                                ->helperText('The type of transport used during the trip.'),

                            TextInput::make('stay_info')
                                ->label('Accommodation / Stay')
                                ->placeholder('e.g.  Cosy riverside homestay in Jibhi')
                                ->helperText('Describe where guests will be staying.'),

                        ])->columns(2),

                    // ──────────────────────────────────────────────────────────
                    // TAB 3 — Trip Highlights
                    // ──────────────────────────────────────────────────────────
                    Tab::make('Highlights')
                        ->icon('heroicon-o-star')
                        ->schema([

                            Repeater::make('highlights')
                                ->relationship('highlights')
                                ->label('Trip Highlights')
                                ->helperText('Add the key selling points — usually the bold bullets or feature cards at the top of the PDF. Drag rows to reorder.')
                                ->schema([

                                    Select::make('icon')
                                        ->label('Icon')
                                        ->options(static::highlightIcons())
                                        ->helperText('Pick the closest matching icon.')
                                        ->required()
                                        ->default('fa-solid fa-star')
                                        ->searchable(),

                                    TextInput::make('title')
                                        ->label('Highlight Title')
                                        ->placeholder('e.g.  Jalori Pass Trek')
                                        ->helperText('A short, punchy title for this highlight.')
                                        ->required(),

                                    Textarea::make('description')
                                        ->label('Short Description  (optional)')
                                        ->placeholder('e.g.  Trek to 10,280 ft with panoramic Himalayan views')
                                        ->helperText('One sentence expanding on this highlight.')
                                        ->rows(2)
                                        ->columnSpanFull(),

                                ])
                                ->columns(2)
                                ->addActionLabel('+ Add a Highlight')
                                ->reorderable('sort_order')
                                ->orderColumn('sort_order')
                                ->collapsible()
                                ->collapsed(false)
                                ->itemLabel(fn (array $state): string => $state['title'] ?? 'New Highlight')
                                ->columnSpanFull(),

                        ]),

                    // ──────────────────────────────────────────────────────────
                    // TAB 4 — Day-by-Day Itinerary
                    // ──────────────────────────────────────────────────────────
                    Tab::make('Itinerary')
                        ->icon('heroicon-o-calendar-days')
                        ->schema([

                            Repeater::make('itineraryDays')
                                ->relationship('itineraryDays')
                                ->label('Day-by-Day Itinerary')
                                ->helperText('Copy each day directly from the "Itinerary" section of the PDF. Use the drag handle (⠿) to reorder days.')
                                ->schema([

                                    TextInput::make('day_number')
                                        ->label('Day No.')
                                        ->helperText('e.g. 1 for Day 1')
                                        ->numeric()
                                        ->default(1)
                                        ->required()
                                        ->minValue(1)
                                        ->columnSpan(1),

                                    TextInput::make('title')
                                        ->label('Day Title')
                                        ->placeholder('e.g.  Arrival in Jibhi | Waterfall Walk')
                                        ->helperText('A short heading for this day — copy from the PDF.')
                                        ->required()
                                        ->columnSpan(3),

                                    Textarea::make('description')
                                        ->label('Day Description')
                                        ->placeholder('Describe what guests do and experience on this day...')
                                        ->helperText('Copy the full paragraph for this day from the PDF.')
                                        ->rows(4)
                                        ->columnSpanFull(),

                                    TagsInput::make('chips')
                                        ->label('Activity Tags  (optional)')
                                        ->placeholder('Type an activity and press Enter  —  e.g.  Jibhi Waterfall')
                                        ->helperText('Quick-glance activity tags shown under the day title on the website.')
                                        ->columnSpanFull(),

                                ])
                                ->columns(4)
                                ->addActionLabel('+ Add a Day')
                                ->reorderable('sort_order')
                                ->orderColumn('sort_order')
                                ->collapsible()
                                ->collapsed(false)
                                ->itemLabel(fn (array $state): string =>
                                    (isset($state['day_number']) && isset($state['title']) && $state['title'] !== '')
                                        ? 'Day ' . $state['day_number'] . '  —  ' . $state['title']
                                        : 'New Day'
                                )
                                ->columnSpanFull(),

                        ]),

                    // ──────────────────────────────────────────────────────────
                    // TAB 5 — Departures
                    // ──────────────────────────────────────────────────────────
                    Tab::make('Departures')
                        ->icon('heroicon-o-calendar')
                        ->schema([

                            Repeater::make('departures')
                                ->relationship('departures')
                                ->label('Travel Dates / Batches')
                                ->helperText('Add fixed departure batches for this tour. They will be automatically grouped by month on the website.')
                                ->schema([
                                    DatePicker::make('start_date')
                                        ->label('Start Date')
                                        ->required()
                                        ->native(false)
                                        ->displayFormat('d M Y'),

                                    DatePicker::make('end_date')
                                        ->label('End Date')
                                        ->required()
                                        ->native(false)
                                        ->displayFormat('d M Y'),
                                ])
                                ->columns(2)
                                ->addActionLabel('+ Add a Batch')
                                ->collapsible()
                                ->collapsed(false)
                                ->itemLabel(fn (array $state): string =>
                                    (isset($state['start_date']) && isset($state['end_date']))
                                        ? \Carbon\Carbon::parse($state['start_date'])->format('d M') . ' — ' . \Carbon\Carbon::parse($state['end_date'])->format('d M')
                                        : 'New Batch'
                                )
                                ->columnSpanFull(),

                        ]),

                    // ──────────────────────────────────────────────────────────
                    // TAB 6 — Inclusions
                    // ──────────────────────────────────────────────────────────
                    Tab::make('Inclusions')
                        ->icon('heroicon-o-check-circle')
                        ->schema([

                            Repeater::make('inclusions')
                                ->relationship('inclusions')
                                ->label('What\'s Included')
                                ->helperText('List everything covered in the package price — from the "Inclusions" section of the PDF. Add one item per row.')
                                ->schema([

                                    Select::make('category')
                                        ->label('Category')
                                        ->options([
                                            'flight'   => '✈️  Flight',
                                            'hotel'    => '🏨  Hotel / Stay',
                                            'meal'     => '🍽️  Meal',
                                            'activity' => '🎯  Activity / Sightseeing',
                                            'transfer' => '🚌  Transport / Transfer',
                                            'other'    => '📌  Other',
                                        ])
                                        ->helperText('Choose the best category.')
                                        ->required()
                                        ->default('other'),

                                    TextInput::make('label')
                                        ->label('What\'s included?')
                                        ->placeholder('e.g.  Breakfast and dinner at the homestay')
                                        ->helperText('Copy the inclusion text from the PDF.')
                                        ->required(),

                                ])
                                ->columns(2)
                                ->addActionLabel('+ Add an Inclusion')
                                ->reorderable('sort_order')
                                ->orderColumn('sort_order')
                                ->collapsible()
                                ->collapsed(false)
                                ->itemLabel(fn (array $state): string => $state['label'] ?? 'New Inclusion')
                                ->columnSpanFull(),

                        ]),

                    // ──────────────────────────────────────────────────────────
                    // TAB 6 — Exclusions
                    // ──────────────────────────────────────────────────────────
                    Tab::make('Exclusions')
                        ->icon('heroicon-o-x-circle')
                        ->schema([

                            Repeater::make('exclusions')
                                ->relationship('exclusions')
                                ->label('What\'s NOT Included')
                                ->helperText('List things guests need to arrange or pay for themselves — from the "Exclusions" section of the PDF. Add one item per row.')
                                ->schema([

                                    TextInput::make('name')
                                        ->label('Not included item')
                                        ->placeholder('e.g.  Personal travel insurance')
                                        ->helperText('Copy each exclusion line from the PDF.')
                                        ->required()
                                        ->columnSpanFull(),

                                ])
                                ->addActionLabel('+ Add an Exclusion')
                                ->reorderable('sort_order')
                                ->orderColumn('sort_order')
                                ->collapsible()
                                ->collapsed(false)
                                ->itemLabel(fn (array $state): string => $state['name'] ?? 'New Exclusion')
                                ->columnSpanFull(),

                        ]),

                    // ──────────────────────────────────────────────────────────
                    // TAB 7 — Photos & PDF
                    // ──────────────────────────────────────────────────────────
                    Tab::make('Photos & PDF')
                        ->icon('heroicon-o-photo')
                        ->schema([

                            FileUpload::make('hero_bg_image')
                                ->label('Hero Banner Image')
                                ->helperText('The large background image at the top of the package page. Best size: 1920 × 1080 px (landscape/wide photo).')
                                ->image()
                                ->imagePreviewHeight('200')
                                ->directory('packages/heroes')
                                ->columnSpanFull(),

                            Repeater::make('images')
                                ->relationship('images')
                                ->label('Gallery Photos')
                                ->helperText('Upload all the trip photos. Drag rows to reorder them — the first photo appears first in the gallery.')
                                ->schema([

                                    FileUpload::make('path')
                                        ->label('Photo')
                                        ->helperText('Upload a photo from your computer.')
                                        ->image()
                                        ->imagePreviewHeight('130')
                                        ->directory('packages/gallery')
                                        ->required(),

                                    TextInput::make('alt_text')
                                        ->label('Photo Caption  (optional)')
                                        ->placeholder('e.g.  View from Jalori Pass at sunrise')
                                        ->helperText('A short description of this photo.'),

                                ])
                                ->columns(2)
                                ->addActionLabel('+ Add a Photo')
                                ->reorderable('sort_order')
                                ->orderColumn('sort_order')
                                ->collapsible()
                                ->collapsed(false)
                                ->itemLabel(fn (array $state): string =>
                                    ! empty($state['alt_text']) ? $state['alt_text'] : 'Photo'
                                )
                                ->columnSpanFull(),

                            FileUpload::make('itinerary_pdf')
                                ->label('Itinerary PDF')
                                ->helperText('Upload the full PDF brochure. Guests can click "Download Itinerary" on the package page to get this file.')
                                ->acceptedFileTypes(['application/pdf'])
                                ->directory('itineraries')
                                ->maxSize(15360)
                                ->validationMessages([
                                    'max' => 'The itinerary PDF must not be greater than 15 MB.',
                                ])
                                ->columnSpanFull(),

                        ]),

                    // ──────────────────────────────────────────────────────────
                    // TAB 8 — Amenities
                    // ──────────────────────────────────────────────────────────
                    Tab::make('Amenities')
                        ->icon('heroicon-o-check-badge')
                        ->schema([

                            Select::make('amenities')
                                ->label('Package Amenities / What\'s Included')
                                ->helperText('Pick all amenities that apply to this package. To add a new amenity, go to Content → Amenities in the sidebar.')
                                ->relationship('amenities', 'name', fn ($query) => $query->where('type', 'package')->orderBy('name'))
                                ->multiple()
                                ->preload()
                                ->searchable()
                                ->columnSpanFull(),

                        ]),

                    // ──────────────────────────────────────────────────────────
                    // TAB 9 — Publishing
                    // ──────────────────────────────────────────────────────────
                    Tab::make('Publish')
                        ->icon('heroicon-o-rocket-launch')
                        ->schema([

                            Toggle::make('is_active')
                                ->label('Published & Visible on Website')
                                ->helperText('Turn this ON when the package is ready for customers to see. Turn it OFF to hide it temporarily.')
                                ->required()
                                ->default(true),

                        ]),

                ])
                ->columnSpanFull(),

        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Curated icon options for Trip Highlights
    // ─────────────────────────────────────────────────────────────────────────
    private static function highlightIcons(): array
    {
        return [
            'fa-solid fa-mountain'        => '🏔️   Mountain / Trek',
            'fa-solid fa-person-hiking'   => '🥾   Trekking / Hiking',
            'fa-solid fa-water'           => '💧   Waterfall / River',
            'fa-solid fa-tree'            => '🌲   Forest / Nature Walk',
            'fa-solid fa-sun'             => '☀️   Sunrise / Sunset View',
            'fa-solid fa-snowflake'       => '❄️   Snow / Winter Experience',
            'fa-solid fa-campground'      => '⛺   Camping / Bonfire Night',
            'fa-solid fa-fish'            => '🎣   Fishing / Angling',
            'fa-solid fa-camera'          => '📷   Photography Spots',
            'fa-solid fa-binoculars'      => '🔭   Wildlife / Bird Watching',
            'fa-solid fa-spa'             => '🧘   Relaxation / Wellness',
            'fa-solid fa-utensils'        => '🍽️   Local Food / Cuisine',
            'fa-solid fa-hotel'           => '🏨   Luxury / Premium Stay',
            'fa-solid fa-house'           => '🏡   Homestay / Village Life',
            'fa-solid fa-car-side'        => '🚗   Scenic Road Trip',
            'fa-solid fa-bus'             => '🚌   Group Transport',
            'fa-solid fa-plane'           => '✈️   Flight Included',
            'fa-solid fa-umbrella-beach'  => '🏖️   Beach / Coastal',
            'fa-solid fa-landmark'        => '🏛️   Heritage / Culture / Temple',
            'fa-solid fa-map-marked-alt'  => '🗺️   Guided Sightseeing',
            'fa-solid fa-star'            => '⭐   General Highlight',
        ];
    }
}
