<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Hotel;
use App\Models\Cruise;
use App\Models\Staycation;
use App\Models\Offer;
use App\Models\Package;

class FrontendController extends Controller
{
    public function index()
    {
        return view('pages.home');
    }

    public function hotels()
    {
        $hotels = Hotel::with(['destination', 'amenities', 'images'])->where('is_active', true)->get();
        return view('pages.hotels', compact('hotels'));
    }

    public function cruises()
    {
        // $cruises = Cruise::with(['destination', 'images'])->where('is_active', true)->get();
        return view('pages.cruises');
    }

    public function staycations()
    {
        // $staycations = Staycation::with(['destination', 'amenities', 'images'])->where('is_active', true)->get();
        return view('pages.staycation');
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
        ]);
        
        return view('pages.packages', compact('packages'));
    }

    public function packageDetails($id)
    {
        // Dummy data for a single package details
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
        $offers = Offer::where('is_active', true)->get();
        return view('pages.offers', compact('offers'));
    }
}
