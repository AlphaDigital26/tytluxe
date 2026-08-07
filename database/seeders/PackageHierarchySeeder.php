<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\Destination;
use App\Models\Package;

class PackageHierarchySeeder extends Seeder
{
    public function run(): void
    {
        $himachal = Destination::firstOrCreate(['name' => 'Himachal Pradesh', 'slug' => 'himachal-pradesh']);
        $jaipur = Destination::firstOrCreate(['name' => 'Jaipur', 'slug' => 'jaipur']);
        $dubai = Destination::firstOrCreate(['name' => 'Dubai', 'slug' => 'dubai']);
        $maldives = Destination::firstOrCreate(['name' => 'Maldives', 'slug' => 'maldives']);

        // Group Domestic (Himachal)
        Package::updateOrCreate(['id' => 7], [
            'title' => 'Jibhi Tirthan Valley',
            'slug' => 'jibhi-tirthan-valley',
            'description' => 'Group tour to Jibhi.',
            'duration_nights' => 2,
            'price_from' => 6999,
            'is_active' => true,
            'destination_id' => $himachal->id,
            'region_type' => 'domestic',
            'tour_type' => 'group',
        ]);
        
        Package::updateOrCreate(['id' => 8], [
            'title' => 'Manali Sisu Kasol',
            'slug' => 'manali-sisu-kasol',
            'description' => 'Group tour to Manali.',
            'duration_nights' => 3,
            'price_from' => 9999,
            'is_active' => true,
            'destination_id' => $himachal->id,
            'region_type' => 'domestic',
            'tour_type' => 'group',
        ]);

        // Custom Domestic (Himachal)
        Package::updateOrCreate(['id' => 9], [
            'title' => 'Shimla Private Getaway',
            'slug' => 'shimla-private-getaway',
            'description' => 'Custom private tour for couples.',
            'duration_nights' => 3,
            'price_from' => 15999,
            'is_active' => true,
            'destination_id' => $himachal->id,
            'region_type' => 'domestic',
            'tour_type' => 'custom',
        ]);

        // Group Domestic (Jaipur)
        Package::updateOrCreate(['id' => 10], [
            'title' => 'Jaipur Heritage Walk',
            'slug' => 'jaipur-heritage-walk',
            'description' => 'Group heritage tour.',
            'duration_nights' => 2,
            'price_from' => 5999,
            'is_active' => true,
            'destination_id' => $jaipur->id,
            'region_type' => 'domestic',
            'tour_type' => 'group',
        ]);

        // International (Dubai)
        Package::updateOrCreate(['id' => 4], [
            'title' => 'Dubai City Breaks',
            'slug' => 'dubai-city-breaks',
            'description' => 'Explore the luxurious city of Dubai.',
            'duration_nights' => 4,
            'price_from' => 85000,
            'is_active' => true,
            'destination_id' => $dubai->id,
            'region_type' => 'international',
            'tour_type' => 'group',
        ]);

        // International (Maldives)
        Package::updateOrCreate(['id' => 1], [
            'title' => 'Maldives Luxury Escape',
            'slug' => 'maldives-luxury-escape',
            'description' => 'Overwater villas in the Maldives.',
            'duration_nights' => 5,
            'price_from' => 125000,
            'is_active' => true,
            'destination_id' => $maldives->id,
            'region_type' => 'international',
            'tour_type' => 'custom',
        ]);
    }
}
