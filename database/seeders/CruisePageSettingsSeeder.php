<?php

namespace Database\Seeders;

use App\Models\Cruise;
use App\Models\CruiseCabinType;
use App\Models\Setting;
use Illuminate\Database\Seeder;

class CruisePageSettingsSeeder extends Seeder
{
    /**
     * Seed default cruise page settings and a sample Cordelia cruise record
     * with cabin types that match the existing hardcoded blade page.
     */
    public function run(): void
    {
        // ──────────────────────────────────────────────────────────────
        // 1. SETTINGS TABLE — cruise page text/JSON content
        // ──────────────────────────────────────────────────────────────

        $settings = [
            'cruise_page.hero_eyebrow'  => "Cordelia Cruises · India's Premium Cruise Line",
            'cruise_page.hero_title'    => 'Destination of <br><em>Your Dreams</em>',
            'cruise_page.hero_subtitle' => 'Mumbai &bull; Goa &bull; Kochi &bull; Lakshadweep &bull; Chennai &bull; Sri Lanka',
            'cruise_page.hero_cta_text' => 'Enquire Now',

            'cruise_page.ship_stats' => json_encode([
                ['value' => 'All-Inclusive', 'label' => 'Dining & Entertainment'],
                ['value' => '48,563 GT',     'label' => 'Gross Tonnage'],
                ['value' => '6 Ports',       'label' => 'Mumbai to Sri Lanka'],
                ['value' => '24/7',          'label' => 'Onboard Support'],
            ]),

            'cruise_page.destinations_label'   => 'Where We Sail',
            'cruise_page.destinations_heading' => 'Six Stunning Destinations',
            'cruise_page.destination_cards'    => json_encode([
                ['city' => 'Mumbai',      'tag' => 'Enjoy Unlimited Experiences',  'image_url' => 'https://images.unsplash.com/photo-1595658658481-d53d3f999875?w=700&q=80', 'image_path' => null],
                ['city' => 'Goa',         'tag' => 'Party Capital of India',        'image_url' => 'https://images.unsplash.com/photo-1512343879784-a960bf40e7f2?w=700&q=80', 'image_path' => null],
                ['city' => 'Lakshadweep', 'tag' => "India's Best Kept Secret",      'image_url' => 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=700&q=80', 'image_path' => null],
                ['city' => 'Kochi',       'tag' => 'Queen of the Arabian Sea',      'image_url' => 'https://images.unsplash.com/photo-1602216056096-3b40cc0c9944?auto=format&fit=crop&w=700&q=80', 'image_path' => null],
                ['city' => 'Chennai',     'tag' => 'The Cultural Capital of India', 'image_url' => 'https://images.unsplash.com/photo-1582510003544-4d00b7f74220?auto=format&fit=crop&w=700&q=80', 'image_path' => null],
                ['city' => 'Sri Lanka',   'tag' => 'Island of Wonder',              'image_url' => 'https://images.unsplash.com/photo-1588411393236-d2524cca1196?auto=format&fit=crop&w=700&q=80', 'image_path' => null],
            ]),

            'cruise_page.dining_intro' => 'From premium restaurants and world-class dining to street food favourites — all food preferences are taken care of onboard The Empress. Pure vegetarian & Jain options available throughout.',
            'cruise_page.dining_items' => json_encode([
                ['name' => 'Starlight',      'description' => 'Experience waterfront dining at Starlight, a two-level restaurant onboard.', 'image_url' => 'https://images.unsplash.com/photo-1414235077428-338989a2e8c0?w=500&q=80', 'image_path' => null],
                ['name' => 'Chopstix',       'description' => 'A culinary tour of exotic Pan-Asian cuisines at this speciality restaurant.', 'image_url' => 'https://images.unsplash.com/photo-1579871494447-9811cf80d66c?w=500&q=80', 'image_path' => null],
                ['name' => "Chef's Table",   'description' => 'A global culinary pavilion with delectable delicacies from a specially curated menu.', 'image_url' => 'https://images.unsplash.com/photo-1559339352-11d035aa65de?w=500&q=80', 'image_path' => null],
                ['name' => 'Food Pavilions', 'description' => 'Essence of India · Far Eastern Kadhai · Hot Clay Tandoor · International Grill · Kettle & Bun · Street Food · Frozen desserts · The Cafe.', 'image_url' => 'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?w=500&q=80', 'image_path' => null],
            ]),

            'cruise_page.entertainment_intro' => "From India's most popular entertainment shows at the Marquee Theatre to live music, magic shows, outdoor movie nights and professional theatre performances.",
            'cruise_page.entertainment_items' => json_encode([
                ['icon' => '🎭', 'name' => 'Balle Balle Show',        'description' => 'A modern Bollywood musical comedy exploring love, arranged marriages and weddings. A heartwarming must-see for all fans of family-friendly musicals.'],
                ['icon' => '🎵', 'name' => 'Live Entertainment',       'description' => "From yesteryear's hits to contemporary music — relax your senses with soothing live tunes performed across the ship every evening."],
                ['icon' => '🎬', 'name' => 'Movies Under the Stars',   'description' => 'Catch the latest Bollywood & Hollywood blockbusters with your loved ones under the open starry night sky on deck.'],
                ['icon' => '🎧', 'name' => 'DJ Parties',               'description' => 'Dance to the lively tunes of our resident DJ until the wee hours of the night. Open for after-hours parties on the high seas.'],
                ['icon' => '✨', 'name' => 'The Burlesque Experience', 'description' => "An adults-only bold & mesmerising performance on the high sea — perfect for those seeking a little extra spice to their evening."],
                ['icon' => '🎪', 'name' => 'All-Day Entertainment',    'description' => 'Entertainment options for everyone, wherever you go onboard — from morning activities to late-night shows, every hour is filled.'],
            ]),

            'cruise_page.bars_intro' => 'Toast to the good life. Take your pick from our range of speciality creations, classic & premium beverages. Lounge in style as you raise a glass to your getaway on the high seas.',
            'cruise_page.bars_items' => json_encode([
                ['icon' => '🥂', 'name' => "The Chairman's Club", 'description' => 'Savour the finest premium and super-premium beverages served in a modern chic setting that truly sets itself apart.'],
                ['icon' => '🎶', 'name' => 'Connexions Bar',       'description' => 'Celebrate moments and life at the vibrant Connexions Bar. Get grooving to the music as you enjoy a selection of beverages served just the way you like it.'],
                ['icon' => '🌅', 'name' => 'The Pool Bar',         'description' => 'Watch the sun melt into the waves as you relax by the Pool Bar on deck and sip on a perfect sundowner.'],
                ['icon' => '🌙', 'name' => 'The Dome',             'description' => 'Savour the night at our late-night bar offering the finest selection of beverages in a private, exclusive space to enjoy your drink.'],
            ]),

            'cruise_page.indulgence_intro' => "Step aboard and discover a ship that has everything. From wellness retreats to adventure activities — Cordelia Cruises brings the 'ALL' in all-inclusive.",
            'cruise_page.indulgence_items' => json_encode([
                ['icon' => '💆', 'name' => 'Spa & Salon',      'description' => 'Experience wellness with an unbeatable view of the sea to refresh and rejuvenate your mind and body. Numerous beauty, hair & body treatments available.'],
                ['icon' => '💪', 'name' => 'Fitness Centre',   'description' => 'Power up with a 180-degree ocean view providing the perfect backdrop for an invigorating workout or a relaxing yoga session.'],
                ['icon' => '🧗', 'name' => 'Rock Climbing',    'description' => 'Choose to elevate your day on the rock climbing wall in the middle of the ocean. Challenge a friend or just enjoy the stunning view from the top.'],
                ['icon' => '🛍️','name' => 'Shopping',         'description' => 'Experience blissful indulgence with exclusive luxury shopping on your cruise holiday — retail therapy to make your vacation complete.'],
                ['icon' => '🚤', 'name' => 'Shore Excursions', 'description' => 'Discover exciting new places and enjoy water sports, shopping, and local cuisines through guided shore excursions at every port.'],
                ['icon' => '🎡', 'name' => 'Cordelia Academy', 'description' => 'A dedicated area for educational and fun activities for kids of all age groups. Child-care certified crew members take care of your little ones while you enjoy some me-time.'],
            ]),

            'cruise_page.events_items' => json_encode([
                ['icon' => '💼', 'name' => 'Corporate Events', 'description' => 'Decorated venues, spacious lounges, high-end theatres, sound technicians, catering services, live music and entertainment — everything for a grand corporate event at sea.'],
                ['icon' => '💍', 'name' => 'Weddings at Sea',  'description' => "Say 'I Do' on a cruise. From vibrant pre-wedding festivities to solemn nuptials, we offer indoor and on-deck venues with customised décor including Havan-Kund setup."],
            ]),

            'cruise_page.trust_items' => json_encode([
                ['icon' => '🚢', 'title' => "India's #1 Cruise",   'desc' => 'Cordelia — the premium cruise line built for Indians'],
                ['icon' => '🍽️','title' => 'All-Inclusive',        'desc' => 'Dining, entertainment & activities all included'],
                ['icon' => '🙏', 'title' => 'Jain & Veg Friendly', 'desc' => 'Dedicated pure veg & Jain counters onboard'],
                ['icon' => '📞', 'title' => 'Expert Support',      'desc' => 'Our team responds within 2 hours on WhatsApp'],
            ]),

            'cruise_page.booking_ports'        => "Mumbai\nChennai\nKochi",
            'cruise_page.booking_destinations' => "Goa\nLakshadweep\nKochi\nChennai\nSri Lanka\nMulti-Port Voyage",
        ];

        foreach ($settings as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        $this->command->info('✅ Cruise page settings seeded.');

        // ──────────────────────────────────────────────────────────────
        // 2. CRUISE RECORD — The Empress (Cordelia)
        // ──────────────────────────────────────────────────────────────
        $cruise = Cruise::firstOrCreate(
            ['slug' => 'the-empress'],
            [
                'title'           => 'The Empress — Cordelia Cruises',
                'description'     => "India's premier cruise ship offering all-inclusive voyages from Mumbai across six stunning coastal destinations.",
                'cruise_line'     => 'Cordelia Cruises',
                'category'        => 'luxury',
                'duration_nights' => 3,
                'price_from'      => 18999.00,
                'is_active'       => true,
            ]
        );

        // ──────────────────────────────────────────────────────────────
        // 3. CABIN TYPES — seed default staterooms
        // ──────────────────────────────────────────────────────────────
        $cabinTypes = [
            [
                'name'        => "The Chairman's Suite",
                'tier_label'  => 'Most Luxurious',
                'description' => 'Fine linen, plush settings, and spacious living arrangements — the pinnacle of luxury at sea.',
                'size_info'   => 'Cabin: 596 Sq. Ft | Balcony: 222 Sq. Ft',
                'price_from'  => 89999.00,
                'image_url'   => 'https://images.unsplash.com/photo-1590490360182-c33d57733427?w=600&q=80',
            ],
            [
                'name'        => 'Suite',
                'tier_label'  => 'Premium',
                'description' => 'Sail the high seas in the comfort of our luxury Suite with a private balcony overlooking the ocean.',
                'size_info'   => 'Cabin: 303 Sq. Ft | Balcony: 222 Sq. Ft',
                'price_from'  => 54999.00,
                'image_url'   => 'https://images.unsplash.com/photo-1631049307264-da0ec9d70304?w=600&q=80',
            ],
            [
                'name'        => 'Mini Suite',
                'tier_label'  => 'Balcony',
                'description' => 'Wake up to a private view of the sea. Your private screening of the ocean is worth a million words.',
                'size_info'   => 'Cabin: 194 Sq. Ft | Balcony: 25 Sq. Ft',
                'price_from'  => 34999.00,
                'image_url'   => 'https://images.unsplash.com/photo-1520250497591-112f2f40a3f4?w=600&q=80',
            ],
            [
                'name'        => 'Ocean View Stateroom',
                'tier_label'  => 'Ocean View',
                'description' => 'A private and cosy cabin of your own amidst the sea — exactly what our ocean view staterooms are all about.',
                'size_info'   => 'Cabin: 142 Sq. Ft',
                'price_from'  => 22999.00,
                'image_url'   => 'https://images.unsplash.com/photo-1584132967334-10e028bd69f7?w=600&q=80',
            ],
            [
                'name'        => 'Interior Stateroom',
                'tier_label'  => 'Value',
                'description' => 'Budget-friendly interior rooms that promise a homely, comfortable feeling at sea — a great value choice.',
                'size_info'   => 'Cabin: 117 Sq. Ft',
                'price_from'  => 18999.00,
                'image_url'   => 'https://images.unsplash.com/photo-1566073771259-6a8506099945?w=600&q=80',
            ],
        ];

        foreach ($cabinTypes as $cabin) {
            CruiseCabinType::firstOrCreate(
                ['cruise_id' => $cruise->id, 'name' => $cabin['name']],
                $cabin
            );
        }

        $this->command->info('✅ Cruise cabin types seeded.');
    }
}
