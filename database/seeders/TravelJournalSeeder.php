<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\FeaturedBlogDestination;
use Illuminate\Support\Carbon;

class TravelJournalSeeder extends Seeder
{
    public function run(): void
    {
        // ── 1. Blog Categories ────────────────────────────────────────────
        $categories = [
            ['name' => 'Destinations',    'slug' => 'destinations',    'sort_order' => 1],
            ['name' => 'Luxury Hotels',   'slug' => 'luxury-hotels',   'sort_order' => 2],
            ['name' => 'Cruises',         'slug' => 'cruises',         'sort_order' => 3],
            ['name' => 'Travel Tips',     'slug' => 'travel-tips',     'sort_order' => 5],
            ['name' => 'Wellness',        'slug' => 'wellness',        'sort_order' => 6],
            ['name' => 'Food & Culture',  'slug' => 'food-culture',    'sort_order' => 7],
            ['name' => 'Adventure',       'slug' => 'adventure',       'sort_order' => 8],
            ['name' => 'Cultural Journeys','slug' => 'cultural-journeys','sort_order' => 9],
        ];

        foreach ($categories as $cat) {
            BlogCategory::updateOrCreate(['slug' => $cat['slug']], array_merge($cat, ['is_active' => true]));
        }

        $catId = fn(string $slug) => BlogCategory::where('slug', $slug)->value('id');

        // ── 2. Blog Posts ─────────────────────────────────────────────────
        $posts = [
            // ── TRENDING (hero carousel) ──
            [
                'title'              => 'Secret Spots in the Pink City: A Complete Guide to Jaipur\'s Hidden Wonders',
                'slug'               => 'secret-spots-jaipur-pink-city',
                'excerpt'            => 'Explore the serene stepwells, hidden artisan quarters, and majestic havelis that lie just beyond the typical tourist path in Jaipur.',
                'body'               => '<p>Jaipur, known as the Pink City, is famous for its palaces and forts — but the real magic lies in its quieter corners. The Panna Meena ka Kund stepwell, the bazaars of Johari Gali, and the forgotten temples of Galta Ji await the curious traveller.</p><p>Start your day early at the Amber Fort before the crowds arrive. Then head to the lesser-known Nahargarh Fort for panoramic views of the city. In the afternoon, lose yourself in the labyrinthine lanes of the old city where artisans craft blue pottery and block-print textiles by hand.</p><p>End your evening at a rooftop restaurant overlooking Hawa Mahal as the Pink City glows gold in the sunset light.</p>',
                'cover_image_url'    => 'https://images.unsplash.com/photo-1605806616949-1e87b487cb2a?auto=format&fit=crop&w=1920&q=80',
                'blog_category_id'   => $catId('cultural-journeys'),
                'read_time_minutes'  => 10,
                'published_at'       => Carbon::parse('2026-08-15'),
                'is_trending'        => true,
                'is_active'          => true,
                'sort_order'         => 1,
            ],
            [
                'title'              => '10 Hidden Gems in the Maldives for Your Next Luxury Escape',
                'slug'               => 'hidden-gems-maldives-luxury-escape',
                'excerpt'            => 'Beyond the overwater villas lies an archipelago of untouched atolls, vibrant house reefs, and secluded sandbanks waiting to be discovered.',
                'body'               => '<p>The Maldives conjures images of infinity pools merging with the turquoise Indian Ocean — but this island nation holds far more than its postcard-perfect resorts suggest. Venture beyond the tourist hotspots to discover local inhabited islands where Maldivian culture thrives in its authentic form.</p><p>Visit Fuvahmulah, the only single-island atoll in the Maldives, home to the rare thila ecosystem where tiger sharks and manta rays congregate year-round. Or sail to the uninhabited sandbank of Sandbank Island near Baa Atoll, a UNESCO Biosphere Reserve, for a private sunrise picnic prepared by your resort chef.</p><p>The Maldives rewards the explorer who digs deeper. Book a traditional dhoni sailing trip at dusk and watch bioluminescent plankton light up the sea like scattered stars.</p>',
                'cover_image_url'    => 'https://images.unsplash.com/photo-1476514525535-07fb3b4ae5f1?auto=format&fit=crop&w=1920&q=80',
                'blog_category_id'   => $catId('destinations'),
                'read_time_minutes'  => 8,
                'published_at'       => Carbon::parse('2026-08-06'),
                'is_trending'        => true,
                'is_active'          => true,
                'sort_order'         => 2,
            ],
            [
                'title'              => 'A Taste of Elegance: Dining Through the Streets of Paris',
                'slug'               => 'dining-streets-of-paris-elegance',
                'excerpt'            => 'From hidden Michelin-starred bistros to the finest patisseries in Montmartre — a curated guide to the culinary soul of the French capital.',
                'body'               => '<p>Paris is not merely a city; it is an experience of the senses — and nowhere is this more evident than at the dining table. The French capital offers a culinary journey unlike any other, from morning croissants at a corner boulangerie to late-night cheese plates at a candlelit cave à manger.</p><p>Begin your day at Du Pain et des Idées in the 10th arrondissement — their escargot pastry is a revelation. For lunch, navigate to Le Comptoir du Relais in Saint-Germain where chef Yves Camdeborde serves a traditional bistro menu that changes daily. As evening falls, secure a reservation at Septime in the 11th — consistently ranked among Europe\'s finest contemporary restaurants.</p><p>Do not overlook the markets: Marché d\'Aligre on a Sunday morning is where Parisians themselves shop, and the vendors are generous with tastings of aged cheeses and charcuterie.</p>',
                'cover_image_url'    => 'https://images.unsplash.com/photo-1502602898657-3e91760cbb34?auto=format&fit=crop&w=1920&q=80',
                'blog_category_id'   => $catId('food-culture'),
                'read_time_minutes'  => 7,
                'published_at'       => Carbon::parse('2026-07-28'),
                'is_trending'        => true,
                'is_active'          => true,
                'sort_order'         => 3,
            ],

            // ── NON-TRENDING posts ──
            [
                'title'              => 'The Ultimate Packing List for a Luxury Cruise Voyage',
                'slug'               => 'ultimate-packing-list-luxury-cruise',
                'excerpt'            => 'Everything you need for a spectacular voyage — from formal evening wear to effortless daytime excursion outfits and must-have tech gadgets.',
                'body'               => '<p>Packing for a luxury cruise requires a different mindset than a standard holiday. With formal dinners, pool days, shore excursions, and spa appointments all on the agenda, your wardrobe must work harder than ever.</p><p>Start with the essentials: a tuxedo or cocktail gown for formal nights, linen shirts and sundresses for port days, and a quality swimsuit cover-up for the pool deck. A compact steamer is indispensable — most ships provide irons but steamers travel lighter.</p><p>Do not forget reef-safe sunscreen (many cruise lines now enforce this in protected marine areas), a waterproof phone case for snorkelling excursions, and a smart crossbody bag for shore trips. Pack a power strip — cabin outlets are notoriously sparse — and a universal adapter if sailing internationally.</p>',
                'cover_image_url'    => 'https://images.unsplash.com/photo-1599640842225-85d111c60e6b?auto=format&fit=crop&w=800&q=80',
                'blog_category_id'   => $catId('cruises'),
                'read_time_minutes'  => 6,
                'published_at'       => Carbon::parse('2026-07-20'),
                'is_trending'        => false,
                'is_active'          => true,
                'sort_order'         => 4,
            ],
            [
                'title'              => 'Top 5 Wellness Retreats in Bali That Will Rejuvenate Your Soul',
                'slug'               => 'top-5-wellness-retreats-bali',
                'excerpt'            => 'Find inner peace in these exclusive lush jungle sanctuaries located in the spiritual heart of Bali, from Ubud to Seminyak.',
                'body'               => '<p>Bali has long been the world\'s wellness capital, and for good reason. The island\'s deep spiritual roots, lush tropical landscapes, and extraordinary hospitality create the perfect conditions for genuine restoration.</p><p><strong>1. Fivelements Puri Ahimsa (Ubud)</strong> — A riverside eco-retreat offering sacred Balinese healing ceremonies, raw food cuisine, and yoga pavilions suspended over the Ayung River.</p><p><strong>2. COMO Shambhala Estate</strong> — Perhaps Bali\'s most celebrated wellness destination, nestled in a jungle ravine with world-class Ayurvedic practitioners and a stunning spring-fed pool.</p><p><strong>3. Bambu Indah</strong> — John Hardy\'s antique Javanese house collection set around organic gardens and a river pool, with a strong focus on sustainable luxury and earth-centred wellness.</p><p><strong>4. Alaya Resort Ubud</strong> — A design-forward retreat combining contemporary Balinese aesthetics with a comprehensive spa menu and daily sunrise yoga sessions.</p><p><strong>5. Katamama, Seminyak</strong> — For those seeking wellness with a dose of culture, Katamama\'s in-house apothecary crafts personalised herbal treatments using centuries-old Balinese recipes.</p>',
                'cover_image_url'    => 'https://images.unsplash.com/photo-1537996194471-e657df975ab4?auto=format&fit=crop&w=800&q=80',
                'blog_category_id'   => $catId('wellness'),
                'read_time_minutes'  => 9,
                'published_at'       => Carbon::parse('2026-07-15'),
                'is_trending'        => false,
                'is_active'          => true,
                'sort_order'         => 5,
            ],
            [
                'title'              => 'Exploring the Swiss Alps: A Complete Winter Luxury Guide',
                'slug'               => 'swiss-alps-winter-luxury-guide',
                'excerpt'            => 'Discover the most exclusive ski resorts, cozy mountain chalets, and breathtaking alpine experiences that Switzerland does better than anywhere else.',
                'body'               => '<p>Switzerland in winter is an exercise in perfection — crisp mountain air, impeccably groomed pistes, and village squares that look lifted from a fairy tale. The Alps attract the world\'s most discerning travellers, and the infrastructure matches the expectation.</p><p>Verbier remains the choice for serious skiers — its four-valleys ski area encompasses over 400km of pistes, and its après-ski scene at Farm Club is legendary. Gstaad, meanwhile, is where old European money winters quietly, shopping at Hermès and dining at Chesery. St Moritz offers the most glamour: frozen lake horse racing, polo on snow, and the historic Badrutt\'s Palace Hotel.</p><p>For the ultimate alpine luxury, book a private helicopter ski guiding day — your pilot and mountain guide will take you to untracked powder fields inaccessible by any lift system.</p>',
                'cover_image_url'    => 'https://images.unsplash.com/photo-1491555103944-7c647fd857e6?auto=format&fit=crop&w=800&q=80',
                'blog_category_id'   => $catId('adventure'),
                'read_time_minutes'  => 8,
                'published_at'       => Carbon::parse('2026-07-10'),
                'is_trending'        => false,
                'is_active'          => true,
                'sort_order'         => 6,
            ],
            [
                'title'              => '12 Secluded Beaches Around the World That Only Insiders Know',
                'slug'               => '12-secluded-beaches-world-insiders',
                'excerpt'            => 'Leave the crowds behind and discover pristine shores — from the Philippines to Sardinia — that still feel untouched by mass tourism.',
                'body'               => '<p>The world\'s most beautiful beaches are rarely its most famous. El Nido\'s Secret Lagoon in the Philippines, accessible only by kayak through a narrow limestone crevice, is a turquoise marvel that most visitors to Palawan never find. In Sardinia, the Cala Brandinchi near San Teodoro is nicknamed "Tahiti" by locals for its Caribbean-calibre clarity.</p><p>Brazil\'s Praia do Sancho on Fernando de Noronha — an archipelago accessible only by small aircraft — is consistently rated among the world\'s finest but visited by fewer than 70,000 people per year due to strict conservation limits. In Greece, bypass Mykonos and Santorini for the beaches of Milos: Sarakiniko\'s lunar white volcanic rock formations create a surreal landscape unlike anything else in the Mediterranean.</p><p>The thread connecting these places: none require anything more than curiosity and willingness to look beyond the obvious. The reward is a beach experience that feels entirely your own.</p>',
                'cover_image_url'    => 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=800&q=80',
                'blog_category_id'   => $catId('destinations'),
                'read_time_minutes'  => 7,
                'published_at'       => Carbon::parse('2026-06-28'),
                'is_trending'        => false,
                'is_active'          => true,
                'sort_order'         => 8,
            ],
            [
                'title'              => 'Inside Tokyo\'s Most Exclusive Ryokans: Where Tradition Meets Luxury',
                'slug'               => 'tokyo-exclusive-ryokans-tradition-luxury',
                'excerpt'            => 'Discover the art of Japanese hospitality at its finest — where multi-course kaiseki dinners, cedar-scented onsen baths, and tatami suites await.',
                'body'               => '<p>Japan\'s ryokan tradition is one of the world\'s great hospitality experiences — and in Tokyo, a handful of establishments have evolved it into something transcendent. The Hoshinoya Tokyo in Otemachi stands 17 floors tall yet feels completely removed from the metropolis below, with tatami-floored rooms, morning yoga on the rooftop, and an in-house onsen drawing water from 1,500 metres underground.</p><p>Elsewhere, the Hoshino Resorts KAI properties — particularly KAI Nikko, a 90-minute drive north of the capital — offer a more traditional setting: low-slung wooden buildings around a landscaped garden, private onsen in each room, and multi-course kaiseki dinners that change with the season.</p><p>Ryokan etiquette adds to the ritual: shoes removed at the entrance, cotton yukata worn throughout the stay, and meals served in your room by a dedicated attendant who memorises your name from the first greeting.</p>',
                'cover_image_url'    => 'https://images.unsplash.com/photo-1493976040374-85c8e12f0c0e?auto=format&fit=crop&w=800&q=80',
                'blog_category_id'   => $catId('luxury-hotels'),
                'read_time_minutes'  => 9,
                'published_at'       => Carbon::parse('2026-06-20'),
                'is_trending'        => false,
                'is_active'          => true,
                'sort_order'         => 9,
            ],
            [
                'title'              => 'The Slow Travel Guide to Kerala\'s Backwaters',
                'slug'               => 'slow-travel-guide-kerala-backwaters',
                'excerpt'            => 'Trade the rush for a houseboat journey through emerald canals, spice plantations, and fishing villages where time moves at a gentler pace.',
                'body'               => '<p>Kerala\'s backwaters are a world unto themselves — a 900-kilometre network of interconnected rivers, lakes, and lagoons that stretch along India\'s south-western coast. The best way to experience them is slowly, aboard a converted rice barge called a kettuvallam.</p><p>A premium houseboat journey typically runs from Alleppey to Kumarakom, threading through narrow canals lined with coconut palms and past villages where children wave from the banks and fishermen cast their Chinese nets at dawn. The better operators include a private chef who cooks Keralan meals — karimeen pollichathu (pearl spot fish), avial (mixed vegetable curry), and payasam for dessert — from produce bought at village markets that morning.</p><p>Beyond the backwaters, Kerala rewards the curious: hill stations at Munnar where tea plantations climb improbably steep slopes; the spice gardens of Thekkady where cardamom, pepper, and cloves perfume the air; and Varkala\'s dramatic cliff-top beach, where red laterite cliffs drop into the Arabian Sea.</p>',
                'cover_image_url'    => 'https://images.unsplash.com/photo-1602216056096-3b40cc0c9944?auto=format&fit=crop&w=800&q=80',
                'blog_category_id'   => $catId('destinations'),
                'read_time_minutes'  => 11,
                'published_at'       => Carbon::parse('2026-06-12'),
                'is_trending'        => false,
                'is_active'          => true,
                'sort_order'         => 10,
            ],
            [
                'title'              => '7 Essential Tips for First-Time Luxury Cruise Passengers',
                'slug'               => '7-tips-first-time-luxury-cruise',
                'excerpt'            => 'From choosing the right cabin category to mastering ship etiquette — everything a first-timer needs to know to make the most of a luxury voyage.',
                'body'               => '<p>A luxury cruise is one of the most effortlessly pleasurable travel experiences — but a little preparation goes a long way. Here are seven things experienced cruisers wish they had known before their first voyage.</p><p><strong>1. Book early for the best cabin selection</strong> — Suites and penthouse staterooms on ships like Silversea\'s Silver Nova sell out 12–18 months in advance. Do not leave it late.</p><p><strong>2. The butler is not just for luggage</strong> — On ultra-luxury lines, your butler can book shore excursions, arrange private transfers, organise in-suite meals at any hour, and handle laundry within the day.</p><p><strong>3. All-inclusive means truly all-inclusive</strong> — On lines like Regent Seven Seas, every shore excursion, wine and spirits, gratuities, and wifi are included. Factor this in when comparing costs.</p><p><strong>4. Pack a formal outfit even if you think you will not use it</strong> — Formal nights on Cunard and Crystal are genuinely special occasions. You will regret not participating.</p><p><strong>5. Go ashore early</strong> — The crowds thin dramatically after 10am as ship passengers disembark. The solution is simple: be among the first off at every port.</p>',
                'cover_image_url'    => 'https://images.unsplash.com/photo-1548574505-5e239809ee19?auto=format&fit=crop&w=800&q=80',
                'blog_category_id'   => $catId('travel-tips'),
                'read_time_minutes'  => 7,
                'published_at'       => Carbon::parse('2026-06-05'),
                'is_trending'        => false,
                'is_active'          => true,
                'sort_order'         => 11,
            ],
        ];

        foreach ($posts as $post) {
            BlogPost::updateOrCreate(['slug' => $post['slug']], $post);
        }

        // ── 3. Featured Destinations ──────────────────────────────────────
        $destinations = [
            [
                'name'        => 'Dubai',
                'slug'        => 'dubai',
                'image_url'   => 'https://images.unsplash.com/photo-1512453979798-5ea266f8880c?auto=format&fit=crop&w=600&q=80',
                'story_count' => 8,
                'is_featured' => true,
                'sort_order'  => 1,
            ],
            [
                'name'        => 'Maldives',
                'slug'        => 'maldives',
                'image_url'   => 'https://images.unsplash.com/photo-1499793983690-e29da59ef1c2?auto=format&fit=crop&w=600&q=80',
                'story_count' => 11,
                'is_featured' => true,
                'sort_order'  => 2,
            ],
            [
                'name'        => 'Paris',
                'slug'        => 'paris',
                'image_url'   => 'https://images.unsplash.com/photo-1502602898657-3e91760cbb34?auto=format&fit=crop&w=600&q=80',
                'story_count' => 6,
                'is_featured' => true,
                'sort_order'  => 3,
            ],
            [
                'name'        => 'Bali',
                'slug'        => 'bali',
                'image_url'   => 'https://images.unsplash.com/photo-1537996194471-e657df975ab4?auto=format&fit=crop&w=600&q=80',
                'story_count' => 9,
                'is_featured' => true,
                'sort_order'  => 4,
            ],
            [
                'name'        => 'Tokyo',
                'slug'        => 'tokyo',
                'image_url'   => 'https://images.unsplash.com/photo-1540959733332-eab4deabeeaf?auto=format&fit=crop&w=600&q=80',
                'story_count' => 7,
                'is_featured' => true,
                'sort_order'  => 5,
            ],
            [
                'name'        => 'Kerala',
                'slug'        => 'kerala',
                'image_url'   => 'https://images.unsplash.com/photo-1602216056096-3b40cc0c9944?auto=format&fit=crop&w=600&q=80',
                'story_count' => 5,
                'is_featured' => true,
                'sort_order'  => 6,
            ],
        ];

        foreach ($destinations as $dest) {
            FeaturedBlogDestination::updateOrCreate(['slug' => $dest['slug']], $dest);
        }

        $this->command->info('✅  Travel Journal seeded: ' . count($categories) . ' categories, ' . count($posts) . ' posts, ' . count($destinations) . ' destinations.');
    }
}
