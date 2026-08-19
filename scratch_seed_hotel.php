<?php

use App\Models\Destination;
use App\Models\Hotel;
use App\Models\Amenity;
use App\Models\RoomType;
use Illuminate\Support\Str;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// 1. Create or Find Destination
$destination = Destination::firstOrCreate(
    ['slug' => 'shimla'],
    [
        'name' => 'Shimla',
        'country' => 'India',
        'type' => 'city',
        'for' => ['hotel', 'package'],
        'is_active' => true,
    ]
);

// 2. Create Amenities
$amenitiesList = [
    'Gym', 'Pool', 'Kids Play', 'Spa', 'Hall', 'Fire Service', 'Fast wifi', 'Parking',
    'Air Conditioning', 'In room safe', 'Seating Area', 'Wadrobe', '1 Bathroom'
];
$amenityIds = [];
foreach ($amenitiesList as $name) {
    $amenity = Amenity::firstOrCreate(
        ['name' => $name],
        ['type' => 'hotel']
    );
    $amenityIds[] = $amenity->id;
}

// 3. Create Hotel
$hotelTitle = 'Snow Valley Resorts';
$hotel = Hotel::updateOrCreate(
    ['slug' => Str::slug($hotelTitle)],
    [
        'destination_id' => $destination->id,
        'title' => $hotelTitle,
        'description' => 'Snow Valley Resorts introduces itself as one of the biggest Centrally Air conditioned Hotel of Shimla having 72 rooms, with all modern facilities and surrounded by panoramic view of the majestic mountains, age old pine trees and overlooking the town of Shimla. The hotel offers unique blend of fine hospitality and unparalleled services. Snow Valley Resorts is located in one of the prime locations of Shimla on NH-22 at Ghora Chowki.',
        'category' => 'family_friendly', // closest to a resort with kids play
        'address' => 'NH-22 at Ghora Chowki, Shimla',
        'star_rating' => 5, // Image shows 5 stars
        'price_from' => 5000, // Placeholder price
        'source' => 'manual',
        'check_in_time' => '2:00 PM',
        'check_out_time' => '11:00 AM',
        'is_active' => true,
        'is_featured' => true,
        'room_categories' => "Standard Rooms (No View)\nExecutive Rooms (Mountain View)\nPremium Rooms (Valley View)",
    ]
);

// Sync Hotel Amenities (Facilities from Image 1)
$hotelFacilities = ['Gym', 'Pool', 'Kids Play', 'Spa', 'Hall', 'Fire Service', 'Fast wifi', 'Parking'];
$hotelAmenityIds = Amenity::whereIn('name', $hotelFacilities)->pluck('id')->toArray();
$hotel->amenities()->sync($hotelAmenityIds);

// 4. Create Room Types
// Standard
RoomType::updateOrCreate(
    [
        'hotel_id' => $hotel->id,
        'name' => 'Standard Rooms (No View)',
    ],
    [
        'occupancy_adults' => 3, // "3 Guests"
        'occupancy_children' => 0,
        'room_size' => '140 sq.ft',
        'bed_type' => '1 Double Bed',
        'inclusions' => ['Air Conditioning', '1 Bathroom', 'Wadrobe'],
        'price_per_night' => 5000,
        'cancellation_policy' => 'free_cancellation',
        'is_active' => true,
    ]
);

// Executive
RoomType::updateOrCreate(
    [
        'hotel_id' => $hotel->id,
        'name' => 'Executive Rooms (Mountain View)',
    ],
    [
        'occupancy_adults' => 4, // "4 Guests"
        'occupancy_children' => 0,
        'room_size' => '200 sq.ft',
        'bed_type' => '1 Double Bed',
        'inclusions' => ['1 Bathroom', 'In room safe', 'Seating Area'],
        'price_per_night' => 7500,
        'cancellation_policy' => 'free_cancellation',
        'is_active' => true,
    ]
);

// Premium
RoomType::updateOrCreate(
    [
        'hotel_id' => $hotel->id,
        'name' => 'Premium Rooms (Valley View)',
    ],
    [
        'occupancy_adults' => 4, // "4 Guests"
        'occupancy_children' => 0,
        'room_size' => '180 sq.ft',
        'bed_type' => '1 Queen Bed',
        'inclusions' => ['Air Conditioning', '1 Bathroom', 'Wadrobe'],
        'price_per_night' => 9000,
        'cancellation_policy' => 'free_cancellation',
        'is_active' => true,
    ]
);

echo "Hotel successfully seeded!\n";
