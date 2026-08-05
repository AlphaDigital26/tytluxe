<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class CruisePageSettings extends Page
{
    protected string $view = 'filament.pages.cruise-page-settings';

    protected static string|\UnitEnum|null $navigationGroup = 'Cruise Catalog';

    protected static ?string $navigationLabel = 'Cruise Page Settings';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-adjustments-horizontal';

    protected static ?string $title = 'Cruise Page Settings';

    protected static ?int $navigationSort = 2;

    // -----------------------------------------------------------------
    // Form State
    // -----------------------------------------------------------------
    public array $data = [];

    public function mount(): void
    {
        $this->data = [
            // Hero
            'hero_eyebrow'  => Setting::get('cruise_page.hero_eyebrow', "Cordelia Cruises · India's Premium Cruise Line"),
            'hero_title'    => Setting::get('cruise_page.hero_title', 'Destination of <br><em>Your Dreams</em>'),
            'hero_subtitle' => Setting::get('cruise_page.hero_subtitle', 'Mumbai &bull; Goa &bull; Kochi &bull; Lakshadweep &bull; Chennai &bull; Sri Lanka'),
            'hero_cta_text' => Setting::get('cruise_page.hero_cta_text', 'Enquire Now'),

            // Ship Stats
            'ship_stats'    => Setting::getJson('cruise_page.ship_stats', [
                ['value' => 'All-Inclusive', 'label' => 'Dining & Entertainment'],
                ['value' => '48,563 GT',     'label' => 'Gross Tonnage'],
                ['value' => '6 Ports',       'label' => 'Mumbai to Sri Lanka'],
                ['value' => '24/7',          'label' => 'Onboard Support'],
            ]),

            // Destinations Section
            'destinations_label'   => Setting::get('cruise_page.destinations_label', 'Where We Sail'),
            'destinations_heading' => Setting::get('cruise_page.destinations_heading', 'Six Stunning Destinations'),
            'destination_cards'    => Setting::getJson('cruise_page.destination_cards', [
                ['city' => 'Mumbai',      'tag' => 'Enjoy Unlimited Experiences',  'image_url' => 'https://images.unsplash.com/photo-1595658658481-d53d3f999875?w=700&q=80', 'image_path' => null],
                ['city' => 'Goa',         'tag' => 'Party Capital of India',        'image_url' => 'https://images.unsplash.com/photo-1512343879784-a960bf40e7f2?w=700&q=80', 'image_path' => null],
                ['city' => 'Lakshadweep', 'tag' => "India's Best Kept Secret",      'image_url' => 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=700&q=80', 'image_path' => null],
                ['city' => 'Kochi',       'tag' => 'Queen of the Arabian Sea',      'image_url' => 'https://images.unsplash.com/photo-1602216056096-3b40cc0c9944?auto=format&fit=crop&w=700&q=80', 'image_path' => null],
                ['city' => 'Chennai',     'tag' => 'The Cultural Capital of India', 'image_url' => 'https://images.unsplash.com/photo-1582510003544-4d00b7f74220?auto=format&fit=crop&w=700&q=80', 'image_path' => null],
                ['city' => 'Sri Lanka',   'tag' => 'Island of Wonder',              'image_url' => 'https://images.unsplash.com/photo-1588411393236-d2524cca1196?auto=format&fit=crop&w=700&q=80', 'image_path' => null],
            ]),

            // Experience Tabs
            'dining_intro'   => Setting::get('cruise_page.dining_intro',   'From premium restaurants and world-class dining to street food favourites — all food preferences are taken care of onboard The Empress. Pure vegetarian & Jain options available throughout.'),
            'dining_items'   => Setting::getJson('cruise_page.dining_items', [
                ['name' => 'Starlight',      'description' => 'Experience waterfront dining at Starlight, a two-level restaurant onboard.', 'image_url' => 'https://images.unsplash.com/photo-1414235077428-338989a2e8c0?w=500&q=80', 'image_path' => null],
                ['name' => 'Chopstix',       'description' => 'A culinary tour of exotic Pan-Asian cuisines at this speciality restaurant.', 'image_url' => 'https://images.unsplash.com/photo-1579871494447-9811cf80d66c?w=500&q=80', 'image_path' => null],
                ['name' => "Chef's Table",   'description' => 'A global culinary pavilion with delectable delicacies from a specially curated menu.', 'image_url' => 'https://images.unsplash.com/photo-1559339352-11d035aa65de?w=500&q=80', 'image_path' => null],
                ['name' => 'Food Pavilions', 'description' => 'Essence of India · Far Eastern Kadhai · Hot Clay Tandoor · International Grill · Kettle & Bun · Street Food · Frozen desserts · The Cafe.', 'image_url' => 'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?w=500&q=80', 'image_path' => null],
            ]),

            'entertainment_intro' => Setting::get('cruise_page.entertainment_intro', "From India's most popular entertainment shows at the Marquee Theatre to live music, magic shows, outdoor movie nights and professional theatre performances."),
            'entertainment_items' => Setting::getJson('cruise_page.entertainment_items', [
                ['icon' => '🎭', 'name' => 'Balle Balle Show',        'description' => 'A modern Bollywood musical comedy exploring love, arranged marriages and weddings.'],
                ['icon' => '🎵', 'name' => 'Live Entertainment',       'description' => 'From yesteryear\'s hits to contemporary music — relax your senses with soothing live tunes.'],
                ['icon' => '🎬', 'name' => 'Movies Under the Stars',   'description' => 'Catch the latest Bollywood & Hollywood blockbusters under the open starry night sky on deck.'],
                ['icon' => '🎧', 'name' => 'DJ Parties',               'description' => 'Dance to the lively tunes of our resident DJ until the wee hours of the night.'],
                ['icon' => '✨', 'name' => 'The Burlesque Experience', 'description' => 'An adults-only bold & mesmerising performance on the high sea.'],
                ['icon' => '🎪', 'name' => 'All-Day Entertainment',    'description' => 'Entertainment options for everyone, wherever you go onboard.'],
            ]),

            'bars_intro' => Setting::get('cruise_page.bars_intro', 'Toast to the good life. Take your pick from our range of speciality creations, classic & premium beverages.'),
            'bars_items' => Setting::getJson('cruise_page.bars_items', [
                ['icon' => '🥂', 'name' => "The Chairman's Club", 'description' => 'Savour the finest premium and super-premium beverages in a modern chic setting.'],
                ['icon' => '🎶', 'name' => 'Connexions Bar',       'description' => 'Celebrate moments and life at the vibrant Connexions Bar.'],
                ['icon' => '🌅', 'name' => 'The Pool Bar',         'description' => 'Watch the sun melt into the waves as you relax by the Pool Bar on deck.'],
                ['icon' => '🌙', 'name' => 'The Dome',             'description' => 'Savour the night at our late-night bar in a private, exclusive space.'],
            ]),

            'indulgence_intro' => Setting::get('cruise_page.indulgence_intro', "Step aboard and discover a ship that has everything. From wellness retreats to adventure activities."),
            'indulgence_items' => Setting::getJson('cruise_page.indulgence_items', [
                ['icon' => '💆', 'name' => 'Spa & Salon',       'description' => 'Experience wellness with an unbeatable view of the sea.'],
                ['icon' => '💪', 'name' => 'Fitness Centre',    'description' => 'Power up with a 180-degree ocean view for an invigorating workout.'],
                ['icon' => '🧗', 'name' => 'Rock Climbing',     'description' => 'Elevate your day on the rock climbing wall in the middle of the ocean.'],
                ['icon' => '🛍️', 'name' => 'Shopping',         'description' => 'Experience blissful indulgence with exclusive luxury shopping on your cruise holiday.'],
                ['icon' => '🚤', 'name' => 'Shore Excursions',  'description' => 'Discover exciting new places through guided shore excursions at every port.'],
                ['icon' => '🎡', 'name' => 'Cordelia Academy',  'description' => 'A dedicated area for educational and fun activities for kids of all age groups.'],
            ]),

            'events_items' => Setting::getJson('cruise_page.events_items', [
                ['icon' => '💼', 'name' => 'Corporate Events',  'description' => 'Decorated venues, spacious lounges, high-end theatres — everything for a grand corporate event at sea.'],
                ['icon' => '💍', 'name' => 'Weddings at Sea',   'description' => "Say 'I Do' on a cruise. From vibrant pre-wedding festivities to solemn nuptials."],
            ]),

            // Trust Strip
            'trust_items' => Setting::getJson('cruise_page.trust_items', [
                ['icon' => '🚢', 'title' => "India's #1 Cruise",    'desc' => 'Cordelia — the premium cruise line built for Indians'],
                ['icon' => '🍽️', 'title' => 'All-Inclusive',        'desc' => 'Dining, entertainment & activities all included'],
                ['icon' => '🙏', 'title' => 'Jain & Veg Friendly',  'desc' => 'Dedicated pure veg & Jain counters onboard'],
                ['icon' => '📞', 'title' => 'Expert Support',       'desc' => 'Our team responds within 2 hours on WhatsApp'],
            ]),

            // Booking Form Options
            'booking_ports'        => Setting::get('cruise_page.booking_ports', "Mumbai\nChennai\nKochi"),
            'booking_destinations' => Setting::get('cruise_page.booking_destinations', "Goa\nLakshadweep\nKochi\nChennai\nSri Lanka\nMulti-Port Voyage"),
        ];
    }

    // -----------------------------------------------------------------
    // Form
    // -----------------------------------------------------------------
    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([

                // ── HERO ─────────────────────────────────────────────
                Section::make('🌊 Hero Section')
                    ->description('Controls the full-screen hero banner at the top of the cruise page.')
                    ->collapsible()
                    ->schema([
                        TextInput::make('hero_eyebrow')
                            ->label('Eyebrow Text')
                            ->required()
                            ->placeholder("Cordelia Cruises · India's Premium Cruise Line")
                            ->columnSpanFull(),
                        TextInput::make('hero_title')
                            ->label('Hero Title (HTML allowed)')
                            ->required()
                            ->placeholder('Destination of <br><em>Your Dreams</em>')
                            ->helperText('You can use basic HTML tags like <br>, <em>, <strong>.')
                            ->columnSpanFull(),
                        TextInput::make('hero_subtitle')
                            ->label('Hero Subtitle')
                            ->placeholder('Mumbai &bull; Goa &bull; Kochi &bull; ...')
                            ->columnSpanFull(),
                        TextInput::make('hero_cta_text')
                            ->label('CTA Button Text')
                            ->required()
                            ->placeholder('Enquire Now'),
                    ]),

                // ── SHIP STATS ────────────────────────────────────────
                Section::make('📊 Ship Stats Band')
                    ->description('The 4-column stats bar below the hero (e.g. "48,563 GT — Gross Tonnage").')
                    ->collapsible()
                    ->schema([
                        Repeater::make('ship_stats')
                            ->label('Stats')
                            ->schema([
                                TextInput::make('value')
                                    ->label('Big Value')
                                    ->required()
                                    ->placeholder('All-Inclusive'),
                                TextInput::make('label')
                                    ->label('Small Label Below')
                                    ->required()
                                    ->placeholder('Dining & Entertainment'),
                            ])
                            ->columns(2)
                            ->minItems(1)
                            ->maxItems(8)
                            ->reorderable()
                            ->columnSpanFull(),
                    ]),

                // ── DESTINATIONS ──────────────────────────────────────
                Section::make('🗺️ Destinations Section')
                    ->description('The "Where We Sail" grid of destination cards.')
                    ->collapsible()
                    ->schema([
                        TextInput::make('destinations_label')
                            ->label('Section Label (small caps above heading)')
                            ->placeholder('Where We Sail'),
                        TextInput::make('destinations_heading')
                            ->label('Section Heading')
                            ->placeholder('Six Stunning Destinations'),

                        Repeater::make('destination_cards')
                            ->label('Destination Cards')
                            ->schema([
                                TextInput::make('city')
                                    ->label('City / Destination Name')
                                    ->required(),
                                TextInput::make('tag')
                                    ->label('Tag Line')
                                    ->placeholder('Party Capital of India'),
                                Section::make('Card Image')
                                    ->description('Upload OR provide URL. Uploaded file takes priority.')
                                    ->columnSpanFull()
                                    ->schema([
                                        FileUpload::make('image_path')
                                            ->label('Upload Image')
                                            ->image()
                                            ->disk('public')
                                            ->directory('cruise-destinations')
                                            ->imagePreviewHeight('160')
                                            ->maxSize(4096),
                                        TextInput::make('image_url')
                                            ->label('Or: External Image URL')
                                            ->url()
                                            ->placeholder('https://images.unsplash.com/...'),
                                    ]),
                            ])
                            ->columns(2)
                            ->reorderable()
                            ->columnSpanFull(),
                    ]),

                // ── DINING TAB ────────────────────────────────────────
                Section::make('🍽️ Experience Tab: Dining')
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        Textarea::make('dining_intro')
                            ->label('Intro Paragraph')
                            ->rows(3)
                            ->columnSpanFull(),
                        Repeater::make('dining_items')
                            ->label('Dining Venues')
                            ->schema([
                                TextInput::make('name')
                                    ->required(),
                                Textarea::make('description')
                                    ->rows(2)
                                    ->columnSpanFull(),
                                Section::make('Image')
                                    ->columnSpanFull()
                                    ->schema([
                                        FileUpload::make('image_path')
                                            ->label('Upload Image')
                                            ->image()->disk('public')
                                            ->directory('cruise-dining')
                                            ->imagePreviewHeight('140')
                                            ->maxSize(4096),
                                        TextInput::make('image_url')
                                            ->label('Or: External URL')
                                            ->url(),
                                    ]),
                            ])
                            ->reorderable()
                            ->columnSpanFull(),
                    ]),

                // ── ENTERTAINMENT TAB ─────────────────────────────────
                Section::make('🎭 Experience Tab: Entertainment')
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        Textarea::make('entertainment_intro')
                            ->label('Intro Paragraph')
                            ->rows(3)
                            ->columnSpanFull(),
                        Repeater::make('entertainment_items')
                            ->label('Entertainment Shows & Activities')
                            ->schema([
                                TextInput::make('icon')->label('Emoji Icon')->placeholder('🎭'),
                                TextInput::make('name')->required(),
                                Textarea::make('description')->rows(2)->columnSpanFull(),
                            ])
                            ->columns(2)
                            ->reorderable()
                            ->columnSpanFull(),
                    ]),

                // ── BARS TAB ──────────────────────────────────────────
                Section::make('🥂 Experience Tab: Bars & Lounges')
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        Textarea::make('bars_intro')
                            ->label('Intro Paragraph')
                            ->rows(3)
                            ->columnSpanFull(),
                        Repeater::make('bars_items')
                            ->label('Bars & Lounges')
                            ->schema([
                                TextInput::make('icon')->label('Emoji Icon')->placeholder('🥂'),
                                TextInput::make('name')->required(),
                                Textarea::make('description')->rows(2)->columnSpanFull(),
                            ])
                            ->columns(2)
                            ->reorderable()
                            ->columnSpanFull(),
                    ]),

                // ── INDULGENCE TAB ────────────────────────────────────
                Section::make('💆 Experience Tab: Indulgence')
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        Textarea::make('indulgence_intro')
                            ->label('Intro Paragraph')
                            ->rows(3)
                            ->columnSpanFull(),
                        Repeater::make('indulgence_items')
                            ->label('Activities & Facilities')
                            ->schema([
                                TextInput::make('icon')->label('Emoji Icon')->placeholder('💆'),
                                TextInput::make('name')->required(),
                                Textarea::make('description')->rows(2)->columnSpanFull(),
                            ])
                            ->columns(2)
                            ->reorderable()
                            ->columnSpanFull(),
                    ]),

                // ── EVENTS TAB ────────────────────────────────────────
                Section::make('💼 Experience Tab: Events')
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        Repeater::make('events_items')
                            ->label('Event Types')
                            ->schema([
                                TextInput::make('icon')->label('Emoji Icon')->placeholder('💼'),
                                TextInput::make('name')->required(),
                                Textarea::make('description')->rows(2)->columnSpanFull(),
                            ])
                            ->columns(2)
                            ->reorderable()
                            ->columnSpanFull(),
                    ]),

                // ── TRUST STRIP ───────────────────────────────────────
                Section::make('✅ Trust Strip')
                    ->description('The dark strip at the bottom with USP badges.')
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        Repeater::make('trust_items')
                            ->label('Trust Items')
                            ->schema([
                                TextInput::make('icon')->label('Emoji Icon'),
                                TextInput::make('title')->required(),
                                TextInput::make('desc')->label('Description'),
                            ])
                            ->columns(3)
                            ->reorderable()
                            ->columnSpanFull(),
                    ]),

                // ── BOOKING FORM ──────────────────────────────────────
                Section::make('📋 Booking Form Options')
                    ->description('Lists shown in the booking enquiry form dropdowns.')
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        Textarea::make('booking_ports')
                            ->label('Departure Ports (one per line)')
                            ->rows(4)
                            ->helperText("Each line becomes one <option> in the Departure Port dropdown."),
                        Textarea::make('booking_destinations')
                            ->label('Destinations (one per line)')
                            ->rows(6)
                            ->helperText("Each line becomes one <option> in the Destination dropdown."),
                    ]),
            ]);
    }

    // -----------------------------------------------------------------
    // Save Action
    // -----------------------------------------------------------------
    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Save Cruise Page Settings')
                ->icon('heroicon-o-check-circle')
                ->color('primary')
                ->action('save'),
        ];
    }

    public function save(): void
    {
        $data = $this->data;

        // Simple string fields
        Setting::set('cruise_page.hero_eyebrow',  $data['hero_eyebrow']);
        Setting::set('cruise_page.hero_title',    $data['hero_title']);
        Setting::set('cruise_page.hero_subtitle', $data['hero_subtitle']);
        Setting::set('cruise_page.hero_cta_text', $data['hero_cta_text']);

        // JSON repeater fields
        Setting::setJson('cruise_page.ship_stats',           $data['ship_stats']           ?? []);
        Setting::set('cruise_page.destinations_label',       $data['destinations_label']);
        Setting::set('cruise_page.destinations_heading',     $data['destinations_heading']);
        Setting::setJson('cruise_page.destination_cards',    $data['destination_cards']    ?? []);
        Setting::set('cruise_page.dining_intro',             $data['dining_intro']);
        Setting::setJson('cruise_page.dining_items',         $data['dining_items']         ?? []);
        Setting::set('cruise_page.entertainment_intro',      $data['entertainment_intro']);
        Setting::setJson('cruise_page.entertainment_items',  $data['entertainment_items']  ?? []);
        Setting::set('cruise_page.bars_intro',               $data['bars_intro']);
        Setting::setJson('cruise_page.bars_items',           $data['bars_items']           ?? []);
        Setting::set('cruise_page.indulgence_intro',         $data['indulgence_intro']);
        Setting::setJson('cruise_page.indulgence_items',     $data['indulgence_items']     ?? []);
        Setting::setJson('cruise_page.events_items',         $data['events_items']         ?? []);
        Setting::setJson('cruise_page.trust_items',          $data['trust_items']          ?? []);
        Setting::set('cruise_page.booking_ports',            $data['booking_ports']);
        Setting::set('cruise_page.booking_destinations',     $data['booking_destinations']);

        Notification::make()
            ->title('Cruise page settings saved!')
            ->body('Changes will be reflected on the cruise page immediately.')
            ->success()
            ->send();
    }
}
