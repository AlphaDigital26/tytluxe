<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TripjackService
{
    protected $baseUrl;
    protected $apiKey;

    public function __construct()
    {
        $this->baseUrl = 'https://apitest-hms.tripjack.com/hms/v3';
        $this->apiKey  = env('TRIPJACK_API_KEY', '41339895c5de9a-9c33-4558-a139-ccaa3fcf1440');
    }

    /**
     * Search Hotels via Tripjack API
     */
    public function searchHotels($checkIn = null, $checkOut = null, $cityId = '26713')
    {
        if (!$checkIn)  $checkIn  = now()->addDays(7)->format('Y-m-d');
        if (!$checkOut) $checkOut = now()->addDays(10)->format('Y-m-d');

        $payload = [
            'searchQuery' => [
                'checkinDate'  => $checkIn,
                'checkoutDate' => $checkOut,
                'roomInfo'     => [
                    [
                        'numberOfAdults' => 2,
                        'numberOfChild'  => 0,
                        'childAge'       => [],
                    ]
                ],
                'searchCriteria' => [
                    'city'        => $cityId,
                    'nationality' => '106',
                    'currency'    => 'INR',
                ],
                'searchPreferences' => [
                    'ratings' => [3, 4, 5],
                    'fsc'     => true,
                ],
            ]
        ];

        try {
            $response = Http::timeout(12)->withHeaders([
                'Content-Type' => 'application/json',
                'apikey'       => $this->apiKey,
            ])->post($this->baseUrl . '/hotel/search', $payload);

            if ($response->successful()) {
                return $response->json();
            }

            Log::warning('Tripjack search API error, returning fallback data.', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);

            // ── Fallback sample list ──────────────────────────────────────────
            return [
                'searchResult' => [
                    'his' => [
                        [
                            'id'   => 'TJ1234',
                            'name' => 'The Atlantis, The Palm',
                            'rt'   => 5,
                            'ad'   => ['adr' => 'Crescent Rd - The Palm Jumeirah', 'city' => ['name' => 'Dubai']],
                            'img'  => [['url' => 'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?w=800&q=80']],
                            'pt'   => 'Resort',
                            'pop'  => [['tpc' => 45000]],
                        ],
                        [
                            'id'   => 'TJ5678',
                            'name' => 'Burj Al Arab Jumeirah',
                            'rt'   => 5,
                            'ad'   => ['adr' => 'Jumeirah St - Umm Suqeim 3', 'city' => ['name' => 'Dubai']],
                            'img'  => [['url' => 'https://images.unsplash.com/photo-1512453979798-5ea266f8880c?w=800&q=80']],
                            'pt'   => 'Luxury Hotel',
                            'pop'  => [['tpc' => 85000]],
                        ],
                        [
                            'id'   => 'TJ9012',
                            'name' => 'Jumeirah Beach Hotel',
                            'rt'   => 4,
                            'ad'   => ['adr' => 'Jumeirah St - Umm Suqeim', 'city' => ['name' => 'Dubai']],
                            'img'  => [['url' => 'https://images.unsplash.com/photo-1520250497591-112f2f40a3f4?w=800&q=80']],
                            'pt'   => 'Hotel',
                            'pop'  => [['tpc' => 25000]],
                        ],
                    ]
                ]
            ];

        } catch (\Exception $e) {
            Log::error('Tripjack search exception', ['message' => $e->getMessage()]);
            return ['error' => true, 'message' => $e->getMessage()];
        }
    }

    /**
     * Get Hotel Details via Tripjack API
     */
    public function getHotelDetail($hotelId)
    {
        try {
            // Attempt the real Tripjack /hotel/info API call
            $response = Http::timeout(10)->withHeaders([
                'Content-Type' => 'application/json',
                'apikey'       => $this->apiKey,
            ])->post($this->baseUrl . '/hotel/info', ['id' => $hotelId]);

            if ($response->successful()) {
                $data      = $response->json();
                $hotelData = $data['hotel'] ?? $data['data'] ?? null;
                if ($hotelData) {
                    $hotelData = $this->applyOverrides($hotelId, $hotelData);
                    return ['hotel' => $hotelData];
                }
            }

            Log::warning('Tripjack hotel/info failed, using fallback.', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);

        } catch (\Exception $e) {
            Log::warning('Tripjack hotel/info exception, using fallback.', ['message' => $e->getMessage()]);
        }

        // ── Per-hotel rich fallback data ──────────────────────────────────────
        $fallbacks = [
            'TJ1234' => [
                'id'          => 'TJ1234',
                'name'        => 'The Atlantis, The Palm',
                'description' => 'Perched on the iconic Palm Jumeirah, Atlantis The Palm is a legendary resort that blends Arabian opulence with breathtaking ocean vistas. With over 1,500 rooms, the world-famous Aquaventure Waterpark, Dolphin Bay, and a private sandy beach, Atlantis offers an unrivalled family and luxury escape in the heart of Dubai.',
                'rt'          => 5,
                'pt'          => 'Resort',
                'checkIn'     => '14:00',
                'checkOut'    => '12:00',
                'policies'    => [
                    'Children under 12 stay free when using existing bedding.',
                    'Pets are not allowed on the property.',
                    'Early check-in subject to availability; fees may apply.',
                    'Valid photo ID required at check-in.',
                ],
                'ad' => [
                    'adr'     => 'Crescent Rd - The Palm Jumeirah',
                    'city'    => ['name' => 'Dubai'],
                    'country' => ['name' => 'United Arab Emirates'],
                    'pin'     => 'The Palm Jumeirah, Dubai, UAE',
                ],
                'fl' => [
                    'Free High-Speed Wi-Fi', 'Aquaventure Waterpark', 'Private Beach',
                    'Dolphin Bay', 'Outdoor & Indoor Pools', 'Spa & Wellness Centre',
                    'Fitness Centre', 'Concierge Service', '24hr Room Service',
                    'Valet Parking', 'Kids Club', 'Multiple Restaurants & Bars',
                ],
                'img' => [
                    ['url' => 'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?w=1600&q=85'],
                    ['url' => 'https://images.unsplash.com/photo-1571896349842-33c89424de2d?w=1200&q=80'],
                    ['url' => 'https://images.unsplash.com/photo-1566073771259-6a8506099945?w=1200&q=80'],
                    ['url' => 'https://images.unsplash.com/photo-1520250497591-112f2f40a3f4?w=1200&q=80'],
                    ['url' => 'https://images.unsplash.com/photo-1564501049412-61c2a3083791?w=1200&q=80'],
                ],
                'rooms' => [
                    [
                        'name'        => 'Ocean Deluxe King Room',
                        'price'       => 45000,
                        'maxOccupancy'=> 2,
                        'boardType'   => 'Room Only',
                        'inclusions'  => ['Free Cancellation', 'Free Wi-Fi'],
                        'cancellation'=> 'Free cancellation before 48 hrs',
                    ],
                    [
                        'name'        => 'Palm Beach Suite',
                        'price'       => 68000,
                        'maxOccupancy'=> 3,
                        'boardType'   => 'Breakfast Included',
                        'inclusions'  => ['Breakfast Included', 'Free Cancellation', 'Waterpark Access'],
                        'cancellation'=> 'Free cancellation before 72 hrs',
                    ],
                    [
                        'name'        => 'Royal Bridge Suite',
                        'price'       => 145000,
                        'maxOccupancy'=> 4,
                        'boardType'   => 'All Inclusive',
                        'inclusions'  => ['All Meals', 'Butler Service', 'Waterpark', 'Spa Credit'],
                        'cancellation'=> 'Non-refundable',
                    ],
                ],
            ],
            'TJ5678' => [
                'id'          => 'TJ5678',
                'name'        => 'Burj Al Arab Jumeirah',
                'description' => 'Standing on its own artificial island and shaped like a billowing sail, Burj Al Arab Jumeirah is the world\'s most recognisable hotel — and arguably the most luxurious. Every room is a duplex suite, every detail is bespoke. From private butler service to helicopter arrivals, this is the pinnacle of Arabian hospitality.',
                'rt'          => 5,
                'pt'          => 'Ultra-Luxury Hotel',
                'checkIn'     => '15:00',
                'checkOut'    => '12:00',
                'policies'    => [
                    'All guests enjoy complimentary Rolls-Royce airport transfers.',
                    'Dress code applies in all dining venues.',
                    'Children under 16 not permitted in some areas.',
                    'Reservation required for all dining experiences.',
                ],
                'ad' => [
                    'adr'     => 'Jumeirah St - Umm Suqeim 3',
                    'city'    => ['name' => 'Dubai'],
                    'country' => ['name' => 'United Arab Emirates'],
                    'pin'     => 'Jumeirah Beach, Dubai, UAE',
                ],
                'fl' => [
                    'Private Butler (24hr)', 'Rolls-Royce Airport Transfers', 'Helipad Arrivals',
                    'Private Beach Club', 'Infinity Pool', 'Talise Spa',
                    'In-Room Smart Technology', 'All-Suite Hotel', 'Michelin-Star Dining',
                    'Gold-Plated Amenities', 'Concierge', 'Personal Chef on Request',
                ],
                'img' => [
                    ['url' => 'https://images.unsplash.com/photo-1512453979798-5ea266f8880c?w=1600&q=85'],
                    ['url' => 'https://images.unsplash.com/photo-1590073844006-33379778ae09?w=1200&q=80'],
                    ['url' => 'https://images.unsplash.com/photo-1571003123894-1f0594d2b5d9?w=1200&q=80'],
                    ['url' => 'https://images.unsplash.com/photo-1549294413-26f195200c16?w=1200&q=80'],
                ],
                'rooms' => [
                    [
                        'name'        => 'Deluxe One Bedroom Suite',
                        'price'       => 85000,
                        'maxOccupancy'=> 2,
                        'boardType'   => 'Breakfast Included',
                        'inclusions'  => ['Butler Service', 'Airport Transfer', 'Breakfast'],
                        'cancellation'=> 'Free cancellation before 7 days',
                    ],
                    [
                        'name'        => 'Panoramic Suite',
                        'price'       => 145000,
                        'maxOccupancy'=> 3,
                        'boardType'   => 'Half Board',
                        'inclusions'  => ['Butler Service', 'Rolls-Royce Transfer', 'Breakfast & Dinner', 'Spa Access'],
                        'cancellation'=> 'Free cancellation before 14 days',
                    ],
                ],
            ],
            'TJ9012' => [
                'id'          => 'TJ9012',
                'name'        => 'Jumeirah Beach Hotel',
                'description' => 'Designed in the shape of a crashing wave to complement the iconic Burj Al Arab next door, Jumeirah Beach Hotel offers an unbeatable beachfront location, spectacular views, and a vibrant atmosphere perfect for families and couples alike. With over 20 restaurants and bars and direct access to a private beach, it\'s Dubai\'s most beloved resort.',
                'rt'          => 4,
                'pt'          => 'Beach Resort',
                'checkIn'     => '14:00',
                'checkOut'    => '12:00',
                'policies'    => [
                    'Children of all ages welcome.',
                    'Complimentary kids club for ages 3–12.',
                    'Pets are not permitted.',
                    'Photo ID required at check-in.',
                ],
                'ad' => [
                    'adr'     => 'Jumeirah St - Umm Suqeim',
                    'city'    => ['name' => 'Dubai'],
                    'country' => ['name' => 'United Arab Emirates'],
                    'pin'     => 'Jumeirah Beach, Dubai, UAE',
                ],
                'fl' => [
                    'Private Beach', 'Outdoor Pools', 'Waterpark (Wild Wadi)',
                    'Free Wi-Fi', '20+ Restaurants & Bars', 'Fitness Centre',
                    'Spa', 'Kids Club', 'Water Sports', 'Concierge', '24hr Room Service',
                ],
                'img' => [
                    ['url' => 'https://images.unsplash.com/photo-1520250497591-112f2f40a3f4?w=1600&q=85'],
                    ['url' => 'https://images.unsplash.com/photo-1542314831-068cd1dbfeeb?w=1200&q=80'],
                    ['url' => 'https://images.unsplash.com/photo-1564501049412-61c2a3083791?w=1200&q=80'],
                    ['url' => 'https://images.unsplash.com/photo-1571003123894-1f0594d2b5d9?w=1200&q=80'],
                ],
                'rooms' => [
                    [
                        'name'        => 'Deluxe Sea View Room',
                        'price'       => 25000,
                        'maxOccupancy'=> 2,
                        'boardType'   => 'Room Only',
                        'inclusions'  => ['Free Wi-Fi', 'Free Cancellation'],
                        'cancellation'=> 'Free cancellation before 48 hrs',
                    ],
                    [
                        'name'        => 'Ocean Suite',
                        'price'       => 42000,
                        'maxOccupancy'=> 3,
                        'boardType'   => 'Breakfast Included',
                        'inclusions'  => ['Breakfast', 'Wild Wadi Access', 'Free Cancellation'],
                        'cancellation'=> 'Free cancellation before 72 hrs',
                    ],
                ],
            ],
        ];

        // Generic fallback for any unknown hotel ID
        $hotel = $fallbacks[$hotelId] ?? [
            'id'          => $hotelId,
            'name'        => 'Luxury Hotel',
            'description' => 'A premium property offering exceptional hospitality, modern amenities and an unforgettable stay experience.',
            'rt'          => 4,
            'pt'          => 'Hotel',
            'checkIn'     => '14:00',
            'checkOut'    => '12:00',
            'policies'    => ['Please contact us for specific hotel policies.'],
            'ad'          => ['adr' => '', 'city' => ['name' => 'Dubai'], 'country' => ['name' => 'United Arab Emirates'], 'pin' => 'Dubai, UAE'],
            'fl'          => ['Free Wi-Fi', 'Swimming Pool', 'Gym', 'Restaurant', 'Room Service'],
            'img'         => [
                ['url' => 'https://images.unsplash.com/photo-1566073771259-6a8506099945?w=1600&q=85'],
                ['url' => 'https://images.unsplash.com/photo-1564501049412-61c2a3083791?w=1200&q=80'],
            ],
            'rooms' => [
                [
                    'name'        => 'Standard Room',
                    'price'       => 12000,
                    'maxOccupancy'=> 2,
                    'boardType'   => 'Room Only',
                    'inclusions'  => ['Free Wi-Fi', 'Free Cancellation'],
                    'cancellation'=> 'Free cancellation before 24 hrs',
                ],
            ],
        ];

        $hotel = $this->applyOverrides($hotelId, $hotel);
        return ['hotel' => $hotel];
    }

    /**
     * Apply admin overrides (from HotelOverride model) on top of API / fallback data
     */
    private function applyOverrides(string $hotelId, array $hotel): array
    {
        try {
            $override = \App\Models\HotelOverride::where('hotel_id', $hotelId)->first();
            if (!$override) return $hotel;

            if ($override->override_name)        $hotel['name']        = $override->override_name;
            if ($override->override_description) $hotel['description'] = $override->override_description;
            if ($override->override_image)       array_unshift($hotel['img'], ['url' => asset('storage/' . $override->override_image)]);
            if ($override->override_amenities && is_array($override->override_amenities)) $hotel['fl'] = $override->override_amenities;
        } catch (\Exception $e) {
            Log::warning('HotelOverride lookup failed', ['message' => $e->getMessage()]);
        }

        return $hotel;
    }
}
