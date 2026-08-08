<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Hotel;
use App\Models\Cruise;
use App\Models\Setting;
use App\Models\Staycation;
use App\Models\Offer;
use App\Models\Package;
use Illuminate\Support\Facades\Storage;

class FrontendController extends Controller
{
    public function index()
    {
        return view('pages.home');
    }

    public function hotels()
    {
        $hotels = Hotel::with(['destination', 'amenities'])->where('is_active', true)->get();

        return view('pages.hotels', compact('hotels'));
    }

    public function hotelDetails($id)
    {
        $tripjack   = new \App\Services\TripjackService();
        $apiData    = $tripjack->getHotelDetail($id);

        if (isset($apiData['error'])) {
            abort(404, 'Hotel details could not be retrieved.');
        }

        $hotel = $apiData['hotel'] ?? [];

        return view('pages.hotel-details', compact('hotel'));
    }

    public function cruises()
    {
        // ── Page-level text settings ───────────────────────────────────────
        $s = function (string $key, mixed $default = '') {
            return Setting::get($key, $default);
        };
        $j = function (string $key, mixed $default = []) {
            return Setting::getJson($key, $default);
        };

        // Hero
        $heroEyebrow  = $s('cruise_page.hero_eyebrow',  "Cordelia Cruises · India's Premium Cruise Line");
        $heroTitle    = $s('cruise_page.hero_title',    'Destination of <br><em>Your Dreams</em>');
        $heroSubtitle = $s('cruise_page.hero_subtitle', 'Mumbai &bull; Goa &bull; Kochi &bull; Lakshadweep &bull; Chennai &bull; Sri Lanka');
        $heroCtaText  = $s('cruise_page.hero_cta_text', 'Enquire Now');

        // Ship stats
        $shipStats = $j('cruise_page.ship_stats', [
            ['value' => 'All-Inclusive', 'label' => 'Dining & Entertainment'],
            ['value' => '48,563 GT',     'label' => 'Gross Tonnage'],
            ['value' => '6 Ports',       'label' => 'Mumbai to Sri Lanka'],
            ['value' => '24/7',          'label' => 'Onboard Support'],
        ]);

        // Destinations
        $destinationsLabel   = $s('cruise_page.destinations_label',   'Where We Sail');
        $destinationsHeading = $s('cruise_page.destinations_heading', 'Six Stunning Destinations');
        $destinationCards    = $j('cruise_page.destination_cards', []);

        // Resolve destination card images (uploaded file takes priority)
        $destinationCards = array_map(function ($card) {
            $card['resolved_image'] = (!empty($card['image_path']) && Storage::disk('public')->exists($card['image_path']))
                ? Storage::disk('public')->url($card['image_path'])
                : ($card['image_url'] ?? null);
            return $card;
        }, $destinationCards);

        // Experience Tabs
        $diningIntro        = $s('cruise_page.dining_intro');
        $diningItems        = $j('cruise_page.dining_items', []);
        $entertainmentIntro = $s('cruise_page.entertainment_intro');
        $entertainmentItems = $j('cruise_page.entertainment_items', []);
        $barsIntro          = $s('cruise_page.bars_intro');
        $barsItems          = $j('cruise_page.bars_items', []);
        $indulgenceIntro    = $s('cruise_page.indulgence_intro');
        $indulgenceItems    = $j('cruise_page.indulgence_items', []);
        $eventsItems        = $j('cruise_page.events_items', []);

        // Resolve dining item images
        $diningItems = array_map(function ($item) {
            $item['resolved_image'] = (!empty($item['image_path']) && Storage::disk('public')->exists($item['image_path']))
                ? Storage::disk('public')->url($item['image_path'])
                : ($item['image_url'] ?? null);
            return $item;
        }, $diningItems);

        // Trust strip
        $trustItems = $j('cruise_page.trust_items', []);

        // Booking form options
        $bookingPorts        = array_filter(array_map('trim', explode("\n", $s('cruise_page.booking_ports', "Mumbai\nChennai\nKochi"))));
        $bookingDestinations = array_filter(array_map('trim', explode("\n", $s('cruise_page.booking_destinations', "Goa\nLakshadweep\nSri Lanka"))));

        // ── Cruise record — cabin types from the featured active cruise ───
        $cruise    = Cruise::where('is_active', true)->with(['cabinTypes', 'images'])->first();
        $cabinTypes = $cruise ? $cruise->cabinTypes->map(function ($c) { $c->resolved_image = $c->resolved_image; return $c; }) : collect([]);

        // Hero carousel images from the cruise's images relation
        $heroImages = [];
        if ($cruise && $cruise->images->isNotEmpty()) {
            $heroImages = $cruise->images->sortBy('sort_order')->map(fn($img) => $img->resolved_image)->filter()->values()->toArray();
        }
        // Fallback to hardcoded Unsplash images if none set in DB
        if (empty($heroImages)) {
            $heroImages = [
                'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=1800&q=80',
                'https://images.unsplash.com/photo-1512100356356-de1b84283e18?auto=format&fit=crop&w=1800&q=80',
                'https://images.unsplash.com/photo-1500375592092-40eb2168fd21?auto=format&fit=crop&w=1800&q=80',
                'https://images.unsplash.com/photo-1520250497591-112f2f40a3f4?auto=format&fit=crop&w=1800&q=80',
            ];
        }

        return view('pages.cruises', compact(
            'heroEyebrow', 'heroTitle', 'heroSubtitle', 'heroCtaText', 'heroImages',
            'shipStats',
            'destinationsLabel', 'destinationsHeading', 'destinationCards',
            'diningIntro', 'diningItems',
            'entertainmentIntro', 'entertainmentItems',
            'barsIntro', 'barsItems',
            'indulgenceIntro', 'indulgenceItems',
            'eventsItems',
            'trustItems',
            'bookingPorts', 'bookingDestinations',
            'cabinTypes'
        ));
    }

    public function staycations()
    {
        $s = fn (string $key, mixed $default = '') => Setting::get($key, $default);
        $j = fn (string $key, mixed $default = []) => Setting::getJson($key, $default);

        // Hero
        $heroEyebrow  = $s('staycation_page.hero_eyebrow',  'Curated Staycations');
        $heroTitle    = $s('staycation_page.hero_title',    'Escape the Ordinary. <em>Stay Extraordinary.</em>');
        $heroSubtitle = $s('staycation_page.hero_subtitle', 'Handpicked resort stays near Mumbai & Pune - perfect for weekends, honeymoons & family getaways.');

        // Hero carousel images
        $heroImages = array_values(array_filter(array_map(function ($img) {
            if (!empty($img['image_path']) && Storage::disk('public')->exists($img['image_path'])) {
                return Storage::disk('public')->url($img['image_path']);
            }
            return $img['image_url'] ?? null;
        }, $j('staycation_page.hero_images', []))));

        // Fallback hero slides
        if (empty($heroImages)) {
            $heroImages = [
                'https://meritashotels.com/wp-content/uploads/2023/06/Deluxe-Room.jpg',
                'https://meritashotels.com/wp-content/uploads/2023/03/DSC_9452-HDR-copy.jpg',
                'https://meritashotels.com/wp-content/uploads/2023/03/Standard-Room-with-Sit-Out3.png',
                'https://meritashotels.com/wp-content/uploads/2023/03/Suite-Bed-Room-%40-Picaddle.jpg',
                'https://meritashotels.com/wp-content/uploads/2023/03/DSC_9476-HDR-copy.jpg',
            ];
        }

        // Resorts — resolve room images
        $resorts = array_map(function ($resort) {
            $resort['rooms'] = array_map(function ($room) {
                if (!empty($room['image_path']) && Storage::disk('public')->exists($room['image_path'])) {
                    $room['resolved_image'] = Storage::disk('public')->url($room['image_path']);
                } else {
                    $room['resolved_image'] = $room['image_url'] ?? null;
                }
                // Parse amenities string to array
                $room['amenity_list'] = array_map('trim', explode(',', $room['amenities'] ?? ''));
                return $room;
            }, $resort['rooms'] ?? []);
            return $resort;
        }, $j('staycation_page.resorts', []));

        // Bottom CTA
        $ctaTag      = $s('staycation_page.cta_tag',      'Book Your Staycation');
        $ctaHeading  = $s('staycation_page.cta_heading',  'Ready for Your <em>Perfect Escape?</em>');
        $ctaBody     = $s('staycation_page.cta_body',     "WhatsApp us with your dates and preferences - we'll get you the best rates on all Meritas properties instantly.");
        $ctaWhatsapp = $s('staycation_page.cta_whatsapp', 'https://wa.me/919875073788');

        return view('pages.staycation', compact(
            'heroEyebrow', 'heroTitle', 'heroSubtitle', 'heroImages',
            'resorts',
            'ctaTag', 'ctaHeading', 'ctaBody', 'ctaWhatsapp'
        ));
    }

    public function packages()
    {
        // Dummy data for presentation
        $packages = collect([
            (object)[
                'id' => 1,
                'title' => 'Maldives Luxury Escape',
                'duration_nights' => 5,
                'price_from' => 125000,
                'destination' => (object)['name' => 'Maldives'],
                'images' => collect([(object)['image_path' => 'https://images.unsplash.com/photo-1514282401047-d79a71a590e8?w=700&q=80']]),
                'inclusions' => collect([(object)['name' => 'Overwater Villa'], (object)['name' => 'All-Inclusive'], (object)['name' => 'Seaplane Transfer']])
            ],
            (object)[
                'id' => 2,
                'title' => 'Swiss Alps Adventure',
                'duration_nights' => 7,
                'price_from' => 185000,
                'destination' => (object)['name' => 'Switzerland'],
                'images' => collect([(object)['image_path' => 'https://images.unsplash.com/photo-1530122037265-a5f1f91d3b99?w=700&q=80']]),
                'inclusions' => collect([(object)['name' => 'Ski Pass'], (object)['name' => 'Premium Chalet'], (object)['name' => 'Breakfast']])
            ],
            (object)[
                'id' => 3,
                'title' => 'Bali Wellness Retreat',
                'duration_nights' => 6,
                'price_from' => 95000,
                'destination' => (object)['name' => 'Indonesia'],
                'images' => collect([(object)['image_path' => 'https://images.unsplash.com/photo-1537996194471-e657df975ab4?w=700&q=80']]),
                'inclusions' => collect([(object)['name' => 'Spa Treatments'], (object)['name' => 'Yoga Classes'], (object)['name' => 'Organic Meals']])
            ],
            (object)[
                'id' => 4,
                'title' => 'Dubai City Breaks',
                'duration_nights' => 4,
                'price_from' => 85000,
                'destination' => (object)['name' => 'UAE'],
                'images' => collect([(object)['image_path' => 'https://images.unsplash.com/photo-1512453979798-5ea266f8880c?w=700&q=80']]),
                'inclusions' => collect([(object)['name' => 'Burj Khalifa Ticket'], (object)['name' => 'Desert Safari'], (object)['name' => '5-Star Hotel']])
            ],
            (object)[
                'id' => 5,
                'title' => 'Paris Romance Tour',
                'duration_nights' => 5,
                'price_from' => 150000,
                'destination' => (object)['name' => 'France'],
                'images' => collect([(object)['image_path' => 'https://images.unsplash.com/photo-1502602898657-3e90760b2401?w=700&q=80']]),
                'inclusions' => collect([(object)['name' => 'Eiffel Tower Tour'], (object)['name' => 'Seine Cruise'], (object)['name' => 'Wine Tasting']])
            ],
            (object)[
                'id' => 6,
                'title' => 'Santorini Getaway',
                'duration_nights' => 6,
                'price_from' => 145000,
                'destination' => (object)['name' => 'Greece'],
                'images' => collect([(object)['image_path' => 'https://images.unsplash.com/photo-1469854523086-cc02fe5d8800?w=700&q=80']]),
                'inclusions' => collect([(object)['name' => 'Sunset Cruise'], (object)['name' => 'Cliffside Suite'], (object)['name' => 'Daily Breakfast']])
            ],
            // ── NEW: Jibhi Tirthan Valley 2N3D ───────────────────────────────────
            (object)[
                'id' => 7,
                'title' => 'Jibhi Tirthan Valley',
                'duration_nights' => 2,
                'price_from' => 6999,
                'destination' => (object)['name' => 'Himachal Pradesh'],
                'images' => collect([(object)['image_path' => 'https://images.unsplash.com/photo-1585409677983-0f6c41ca9c3b?w=700&q=80']]),
                'inclusions' => collect([
                    (object)['name' => 'Delhi–Delhi Transport'],
                    (object)['name' => '2 Breakfast + 2 Dinner'],
                    (object)['name' => 'Hotel Stay'],
                ])
            ],
            // ── NEW: Manali Sisu Kasol 3N4D ──────────────────────────────────────
            (object)[
                'id' => 8,
                'title' => 'Manali Sisu Kasol',
                'duration_nights' => 3,
                'price_from' => 9999,
                'destination' => (object)['name' => 'Himachal Pradesh'],
                'images' => collect([(object)['image_path' => 'https://images.unsplash.com/photo-1626621341517-bbf3d9990a23?w=700&q=80']]),
                'inclusions' => collect([
                    (object)['name' => 'AC Coach Delhi–Delhi'],
                    (object)['name' => '3 Breakfast + 3 Dinner'],
                    (object)['name' => 'Hotel + Camp Stay'],
                ])
            ],
        ]);
        
        return view('pages.packages', compact('packages'));
    }

    public function packageDetails($id)
    {
        // ── Jibhi Tirthan Valley 2N3D ──────────────────────────────────────────
        if ($id == 7) {
            $package = (object)[
                'id'              => 7,
                'title'           => 'Jibhi Tirthan Valley',
                'duration_nights' => 2,
                'price_from'      => 6999,
                'description'     => 'Nestled in the tranquil Tirthan Valley of Himachal Pradesh, Jibhi is a captivating destination that offers a perfect mix of adventure, culture, and natural beauty. Surrounded by lush pine forests, sparkling rivers, and snow-capped Himalayan peaks, this offbeat village invites travelers seeking a peaceful retreat away from the chaos of city life. Explore scenic trekking trails, Serolsar Lake, the famous Jalori Pass, and the historic Chehni Kothi — all while staying at rustic homestays with authentic Himachali warmth.',
                'destination'     => (object)['name' => 'Himachal Pradesh'],
                'images'          => collect([
                    (object)['image_path' => 'https://images.unsplash.com/photo-1585409677983-0f6c41ca9c3b?w=1200&q=80'],
                    (object)['image_path' => 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=1200&q=80'],
                    (object)['image_path' => 'https://images.unsplash.com/photo-1501854140801-50d01698950b?w=1200&q=80'],
                ]),
                'inclusions'      => collect([
                    (object)['name' => 'Delhi to Delhi Transport (Volvo/Tempo Traveller)'],
                    (object)['name' => 'Surface Transfers for Sightseeing'],
                    (object)['name' => 'Hotel Accommodation'],
                    (object)['name' => '2 Breakfasts + 2 Dinners (MAP AI)'],
                    (object)['name' => 'Bonfire (subject to availability)'],
                    (object)['name' => 'Trip Captain'],
                    (object)['name' => 'Driver Allowance, Toll & State Taxes'],
                    (object)['name' => 'Parking Charges'],
                ]),
                'itinerary'       => collect([
                    (object)['day' => 0, 'title' => 'Departure from Delhi', 'description' => 'The group assembles at the pickup point. A small tour briefing session is provided. Departure from Delhi for an overnight comfortable journey to Jibhi — a tiny hamlet situated between lush green forests in the Tirthan Valley of Himachal Pradesh.'],
                    (object)['day' => 1, 'title' => 'Jibhi Waterfall & Mini Thailand', 'description' => 'Arrive at Jibhi and check into your rooms to relax. In the afternoon, head for a local sightseeing tour: visit the soul-stirring Jibhi Waterfall and trek 500 metres to "Mini Thailand" (Kulhi Katand) — a unique formation of two giant rocks with water flowing through, giving it a secluded beach vibe. Explore the local market and go café hopping. End the evening with a bonfire and dinner under the starry sky.'],
                    (object)['day' => 2, 'title' => 'Jalori Pass & Raghupur Fort / Serolsar Lake Trek', 'description' => 'After an early breakfast, head towards Jalori Pass (on own cost). Choose between two treks: Raghupur Fort Trek — scenic trails through the Tirthan Valley with panoramic views of the Himalayas and the ancient fort ruins; or Serolsar Lake Trek — dense pine forests leading to a sacred lake with the Buddhi Nagin Temple and a 360° Himalayan viewpoint. Dinner under the starry sky and overnight accommodation.'],
                    (object)['day' => 3, 'title' => 'Chhoie Waterfall & Departure', 'description' => 'After breakfast and check-out, visit Gushaini Village for a brief exploration. Trek to the spectacular Chhoie Waterfall, renowned for its scenic grandeur — on favourable days, a double rainbow appears beneath the falls. Board the vehicle for the return journey to Aut and onwards to Delhi overnight.'],
                    (object)['day' => 4, 'title' => 'Arrival in Delhi', 'description' => 'Arrive in Delhi the following morning, with memories of the Himalayas to cherish.'],
                ]),
            ];
            return view('pages.package-jibhi');
        }

        // ── Manali Sisu Kasol 3N4D ─────────────────────────────────────────────
        if ($id == 8) {
            $package = (object)[
                'id'              => 8,
                'title'           => 'Manali Sisu Kasol',
                'duration_nights' => 3,
                'price_from'      => 9999,
                'description'     => 'Nestled in the heart of Himachal Pradesh, Manali is steeped in mythology and natural beauty — often called the "Valley of the Gods." This 3-night, 4-day journey takes you from the ancient Hadimba Devi Temple and Mall Road in Manali to the breathtaking Solang Valley, through the famous Atal Tunnel to Sissu\'s mesmerising scenery, and finally to the bohemian riverside charm of Kasol and the sacred Manikaran Gurudwara.',
                'destination'     => (object)['name' => 'Himachal Pradesh'],
                'images'          => collect([
                    (object)['image_path' => 'https://images.unsplash.com/photo-1626621341517-bbf3d9990a23?w=1200&q=80'],
                    (object)['image_path' => 'https://images.unsplash.com/photo-1598346762291-aee88549193f?w=1200&q=80'],
                    (object)['image_path' => 'https://images.unsplash.com/photo-1532375810709-75b1da00537c?w=1200&q=80'],
                ]),
                'inclusions'      => collect([
                    (object)['name' => 'AC Coach / Tempo Traveller Delhi to Delhi'],
                    (object)['name' => '2 Nights Hotel in Manali + 1 Night Camp in Kasol'],
                    (object)['name' => '3 Breakfasts + 3 Dinners'],
                    (object)['name' => 'Trip Captain Present at All Times'],
                    (object)['name' => 'All Required Permits'],
                    (object)['name' => 'Driver Allowance, Toll & State Taxes'],
                ]),
                'itinerary'       => collect([
                    (object)['day' => 0, 'title' => 'Departure from Delhi / Chandigarh', 'description' => 'Board the AC coach/tempo traveller from Delhi or Chandigarh. Receive a briefing from the trip captain. Overnight journey to Manali with 2–3 halt stops for dinner and snacks en route.'],
                    (object)['day' => 1, 'title' => 'Manali Local Sightseeing', 'description' => 'Reach Manali in the morning and check into the hotel. Freshen up and relax. Leave for Manali local sightseeing — Hadimba Devi Temple (unique pagoda-style architecture from the Mahabharata era), Vashisht Temple with its healing hot springs, and the vibrant Mall Road. Return to the hotel. Dinner with bonfire and light music. Overnight stay in Manali.'],
                    (object)['day' => 2, 'title' => 'Solang Valley – Sissu – Atal Tunnel', 'description' => 'After breakfast, leave for Solang Valley — enjoy mesmerising views and optional adventure activities. Proceed through the iconic Atal Tunnel to Sissu; spend time enjoying the beautiful scenery. Optional visit to Rohtang (on personal expense). Return to hotel for dinner and overnight stay in Manali.'],
                    (object)['day' => 3, 'title' => 'Leave for Kasol', 'description' => 'Wake up early, have breakfast, and check out. Head for Kasol. En route, indulge in optional activities like river rafting and paragliding (personal cost). Reach Kasol camps/cottage. Enjoy dinner around a bonfire and overnight stay in Kasol.'],
                    (object)['day' => 4, 'title' => 'Kasol – Manikaran & Back to Delhi', 'description' => 'After breakfast and check-out, visit the sacred Manikaran Gurudwara and enjoy the langar. Explore Kasol\'s riverside views and bohemian cafés. In the late evening, board the vehicle for the overnight return journey to Delhi.'],
                ]),
            ];
            return view('pages.package-manali');
        }

        // ── Default (existing packages 1–6) ────────────────────────────────────
        $package = (object)[
            'id' => $id,
            'title' => 'Luxury Destination Escape',
            'duration_nights' => 7,
            'price_from' => 150000,
            'description' => 'Experience the ultimate luxury getaway with our exclusively curated travel package. Enjoy world-class amenities, stunning views, and unforgettable moments tailored just for you. This package includes premium accommodation, guided tours, and exquisite dining experiences.',
            'destination' => (object)['name' => 'Exotic Location'],
            'images' => collect([
                (object)['image_path' => 'https://images.unsplash.com/photo-1540202404-b71180fb78d1?w=1200&q=80'],
                (object)['image_path' => 'https://images.unsplash.com/photo-1571003123894-1f0594d2b5d9?w=1200&q=80'],
                (object)['image_path' => 'https://images.unsplash.com/photo-1499793983690-e29da59ef1c2?w=1200&q=80']
            ]),
            'inclusions' => collect([
                (object)['name' => '5-Star Accommodation'], 
                (object)['name' => 'Daily Gourmet Breakfast'], 
                (object)['name' => 'Private Airport Transfers'],
                (object)['name' => 'Exclusive Guided Tours'],
                (object)['name' => 'Spa & Wellness Access']
            ]),
            'itinerary' => collect([
                (object)['day' => 1, 'title' => 'Arrival & Welcome', 'description' => 'Arrive in style with a private transfer to your luxury resort. Enjoy a welcome dinner at the signature restaurant.'],
                (object)['day' => 2, 'title' => 'Guided Sightseeing', 'description' => 'Explore the local culture and landmarks with an expert private guide. Includes priority access to attractions.'],
                (object)['day' => 3, 'title' => 'Relaxation & Spa', 'description' => 'Spend the day unwinding at the world-class spa facility. Complimentary couples massage included.'],
                (object)['day' => 4, 'title' => 'Adventure & Excursion', 'description' => 'Embark on a thrilling outdoor adventure, followed by a sunset cruise in the evening.'],
                (object)['day' => 5, 'title' => 'Farewell & Departure', 'description' => 'Enjoy a final gourmet breakfast before your private transfer back to the airport.']
            ])
        ];

        return view('pages.package-details', compact('package'));
    }

    public function offers()
    {
        $s = fn (string $key, mixed $default = '') => Setting::get($key, $default);
        $j = fn (string $key, mixed $default = []) => Setting::getJson($key, $default);

        // Hero
        $heroEyebrow  = $s('offers_page.hero_eyebrow',  'Limited Time Deals');
        $heroTitle    = $s('offers_page.hero_title',    'Exclusive Deals. <em>Unforgettable</em> Experiences.');
        $heroSubtitle = $s('offers_page.hero_subtitle', 'Handpicked offers on hotels, cruises & flights — updated regularly');

        // Hero carousel images
        $heroImages = array_values(array_filter(array_map(function ($img) {
            if (!empty($img['image_path']) && Storage::disk('public')->exists($img['image_path'])) {
                return Storage::disk('public')->url($img['image_path']);
            }
            return $img['image_url'] ?? null;
        }, $j('offers_page.hero_images', []))));

        if (empty($heroImages)) {
            $heroImages = [
                'https://images.unsplash.com/photo-1542314831-068cd1dbfeeb?w=1400&q=85',
                'https://images.unsplash.com/photo-1548574505-5e239809ee19?w=1400&q=85',
                'https://images.unsplash.com/photo-1436491865332-7a61a109cc05?w=1400&q=85',
            ];
        }

        // Filter tabs
        $filterTabs = $j('offers_page.filter_tabs', [
            ['key' => 'all', 'label' => 'All Offers'],
            ['key' => 'hotels', 'label' => 'Hotels'],
            ['key' => 'cruises', 'label' => 'Cruises'],
            ['key' => 'flights', 'label' => 'Flights'],
            ['key' => 'honeymoon', 'label' => 'Honeymoon'],
            ['key' => 'family', 'label' => 'Family'],
        ]);

        // Categories — resolve card images
        $categories = array_map(function ($cat) {
            $cat['cards'] = array_map(function ($card) {
                if (!empty($card['image_path']) && Storage::disk('public')->exists($card['image_path'])) {
                    $card['resolved_image'] = Storage::disk('public')->url($card['image_path']);
                } else {
                    $card['resolved_image'] = $card['image_url'] ?? null;
                }
                return $card;
            }, $cat['cards'] ?? []);
            return $cat;
        }, $j('offers_page.categories', []));

        // Bottom CTA
        $ctaTag        = $s('offers_page.cta_tag',         'Stay Ahead');
        $ctaHeading    = $s('offers_page.cta_heading',     'Be the First to <em>Know</em>');
        $ctaBody       = $s('offers_page.cta_body',        "Drop your WhatsApp number and we'll notify you the moment a new deal goes live — no spam, ever.");
        $ctaNotifyNote = $s('offers_page.cta_notify_note', "WhatsApp only. We won't call unless you ask.");
        $ctaWhatsapp   = $s('offers_page.cta_whatsapp',    'https://wa.me/9875073788');
        $ctaWaLabel    = $s('offers_page.cta_wa_label',    'Ask for Latest Deals on WhatsApp');

        return view('pages.offers', compact(
            'heroEyebrow', 'heroTitle', 'heroSubtitle', 'heroImages',
            'filterTabs', 'categories',
            'ctaTag', 'ctaHeading', 'ctaBody', 'ctaNotifyNote', 'ctaWhatsapp', 'ctaWaLabel'
        ));
    }
}
