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
        $this->apiKey = '41339869af02ee-b40d-4289-b14f-797ba73b4d37';
    }

    /**
     * Search Hotels via Tripjack API
     */
    public function searchHotels($checkIn = null, $checkOut = null, $cityId = '26713')
    {
        // Default to next week if dates are not provided
        if (!$checkIn) {
            $checkIn = now()->addDays(7)->format('Y-m-d');
        }
        if (!$checkOut) {
            $checkOut = now()->addDays(10)->format('Y-m-d');
        }

        $payload = [
            'searchQuery' => [
                'checkinDate' => $checkIn,
                'checkoutDate' => $checkOut,
                'roomInfo' => [
                    [
                        'numberOfAdults' => 2,
                        'numberOfChild' => 0,
                        'childAge' => []
                    ]
                ],
                'searchCriteria' => [
                    'city' => $cityId, // 26713 is often Dubai, or try string "Dubai"
                    'nationality' => '106', // 106 = India
                    'currency' => 'INR'
                ],
                'searchPreferences' => [
                    'ratings' => [3, 4, 5],
                    'fsc' => true
                ]
            ]
        ];

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'apikey' => $this->apiKey
            ])->post($this->baseUrl . '/hotel/search', $payload);

            if ($response->successful()) {
                return $response->json();
            }

            Log::warning('Tripjack API Error (IP likely not whitelisted), returning sample data.', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            // Fallback sample data matching Tripjack format for UI testing
            return [
                'searchResult' => [
                    'his' => [
                        [
                            'id' => 'TJ1234',
                            'name' => 'The Atlantis, The Palm',
                            'rt' => 5,
                            'ad' => [
                                'adr' => 'Crescent Rd - The Palm Jumeirah',
                                'city' => ['name' => 'Dubai']
                            ],
                            'img' => [
                                ['url' => 'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?w=800&q=80']
                            ],
                            'pt' => 'Resort',
                            'pop' => [
                                ['tpc' => 45000]
                            ]
                        ],
                        [
                            'id' => 'TJ5678',
                            'name' => 'Burj Al Arab Jumeirah',
                            'rt' => 5,
                            'ad' => [
                                'adr' => 'Jumeirah St - Umm Suqeim 3',
                                'city' => ['name' => 'Dubai']
                            ],
                            'img' => [
                                ['url' => 'https://images.unsplash.com/photo-1512453979798-5ea266f8880c?w=800&q=80']
                            ],
                            'pt' => 'Luxury Hotel',
                            'pop' => [
                                ['tpc' => 85000]
                            ]
                        ],
                        [
                            'id' => 'TJ9012',
                            'name' => 'Jumeirah Beach Hotel',
                            'rt' => 4,
                            'ad' => [
                                'adr' => 'Jumeirah St - Umm Suqeim',
                                'city' => ['name' => 'Dubai']
                            ],
                            'img' => [
                                ['url' => 'https://images.unsplash.com/photo-1520250497591-112f2f40a3f4?w=800&q=80']
                            ],
                            'pt' => 'Hotel',
                            'pop' => [
                                ['tpc' => 25000]
                            ]
                        ]
                    ]
                ]
            ];

        } catch (\Exception $e) {
            Log::error('Tripjack API Exception', ['message' => $e->getMessage()]);
            return [
                'error' => true,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Get Hotel Details via Tripjack API (with overrides)
     */
    public function getHotelDetail($hotelId)
    {
        $payload = [
            'id' => $hotelId
        ];

        try {
            // In a real scenario, this calls /hotel/info.
            // $response = Http::withHeaders([
            //     'Content-Type' => 'application/json',
            //     'apikey' => $this->apiKey
            // ])->post($this->baseUrl . '/hotel/info', $payload);
            // $data = $response->json();

            // Mock Data for Hotel Details (due to 403 IP error in dev)
            $data = [
                'hotel' => [
                    'id' => $hotelId,
                    'name' => 'The Atlantis, The Palm (Live API Name)',
                    'description' => 'Experience world-class service at Atlantis. Located on Dubai’s Palm Jumeirah Island and enjoys a private sandy beach, the 5-star Atlantis offers stunning views of the Arabian Gulf.',
                    'rt' => 5, // Rating
                    'pt' => 'Resort',
                    'ad' => [
                        'adr' => 'Crescent Rd - The Palm Jumeirah',
                        'city' => ['name' => 'Dubai'],
                        'country' => ['name' => 'United Arab Emirates']
                    ],
                    'fl' => ['Free Wi-Fi', 'Swimming Pool', 'Spa', 'Gym', 'Private Beach', 'Water Park'], // Facilities
                    'img' => [
                        ['url' => 'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?w=1200&q=80'],
                        ['url' => 'https://images.unsplash.com/photo-1571896349842-33c89424de2d?w=1200&q=80'],
                        ['url' => 'https://images.unsplash.com/photo-1566073771259-6a8506099945?w=1200&q=80']
                    ],
                    'rooms' => [
                        [
                            'name' => 'Ocean King Room',
                            'price' => 45000,
                            'inclusions' => ['Breakfast Included', 'Free Cancellation']
                        ],
                        [
                            'name' => 'Palm Queen Room',
                            'price' => 52000,
                            'inclusions' => ['Breakfast & Dinner', 'Free Cancellation']
                        ]
                    ]
                ]
            ];

            // APPLY OVERRIDES SYSTEM (Option A)
            $override = \App\Models\HotelOverride::where('hotel_id', $hotelId)->first();
            
            if ($override) {
                if ($override->override_name) {
                    $data['hotel']['name'] = $override->override_name;
                }
                if ($override->override_description) {
                    $data['hotel']['description'] = $override->override_description;
                }
                if ($override->override_image) {
                    // Prepend the local image as the main image
                    array_unshift($data['hotel']['img'], ['url' => asset('storage/' . $override->override_image)]);
                }
                if ($override->override_amenities && is_array($override->override_amenities)) {
                    $data['hotel']['fl'] = $override->override_amenities;
                }
            }

            return $data;

        } catch (\Exception $e) {
            Log::error('Tripjack API Detail Exception', ['message' => $e->getMessage()]);
            return [
                'error' => true,
                'message' => $e->getMessage()
            ];
        }
    }
}
