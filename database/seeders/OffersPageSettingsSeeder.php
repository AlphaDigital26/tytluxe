<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class OffersPageSettingsSeeder extends Seeder
{
    public function run(): void
    {
        // Hero
        Setting::updateOrCreate(['key' => 'offers_page.hero_eyebrow'],  ['value' => 'Limited Time Deals']);
        Setting::updateOrCreate(['key' => 'offers_page.hero_title'],    ['value' => 'Exclusive Deals. <em>Unforgettable</em> Experiences.']);
        Setting::updateOrCreate(['key' => 'offers_page.hero_subtitle'], ['value' => 'Handpicked offers on hotels, cruises & flights — updated regularly']);

        // Hero images
        Setting::updateOrCreate(['key' => 'offers_page.hero_images'], ['value' => json_encode([
            ['image_url' => 'https://images.unsplash.com/photo-1542314831-068cd1dbfeeb?w=1400&q=85', 'image_path' => null],
            ['image_url' => 'https://images.unsplash.com/photo-1548574505-5e239809ee19?w=1400&q=85', 'image_path' => null],
            ['image_url' => 'https://images.unsplash.com/photo-1436491865332-7a61a109cc05?w=1400&q=85', 'image_path' => null],
            ['image_url' => 'https://images.unsplash.com/photo-1512343879784-a960bf40e7f2?w=1400&q=85', 'image_path' => null],
            ['image_url' => 'https://images.unsplash.com/photo-1590490360182-c33d57733427?w=1400&q=85', 'image_path' => null],
        ])]);

        // Filter tabs
        Setting::updateOrCreate(['key' => 'offers_page.filter_tabs'], ['value' => json_encode([
            ['key' => 'all',       'label' => 'All Offers'],
            ['key' => 'hotels',    'label' => 'Hotels'],
            ['key' => 'cruises',   'label' => 'Cruises'],
            ['key' => 'flights',   'label' => 'Flights'],
            ['key' => 'honeymoon', 'label' => 'Honeymoon'],
            ['key' => 'family',    'label' => 'Family'],
        ])]);

        // Bottom CTA
        Setting::updateOrCreate(['key' => 'offers_page.cta_tag'],         ['value' => 'Stay Ahead']);
        Setting::updateOrCreate(['key' => 'offers_page.cta_heading'],      ['value' => 'Be the First to <em>Know</em>']);
        Setting::updateOrCreate(['key' => 'offers_page.cta_body'],         ['value' => "Drop your WhatsApp number and we'll notify you the moment a new deal goes live — no spam, ever."]);
        Setting::updateOrCreate(['key' => 'offers_page.cta_notify_note'],  ['value' => "WhatsApp only. We won't call unless you ask."]);
        Setting::updateOrCreate(['key' => 'offers_page.cta_whatsapp'],     ['value' => 'https://wa.me/9875073788']);
        Setting::updateOrCreate(['key' => 'offers_page.cta_wa_label'],     ['value' => 'Ask for Latest Deals on WhatsApp']);

        // Categories with their cards
        $categories = [
            [
                'category_key'  => 'hotels',
                'slider_label'  => 'Hotel Offers',
                'slider_title'  => 'Handpicked <em>Stays</em>',
                'cards' => [
                    ['name' => 'Beach Resort Getaway',    'subtitle' => 'Sun, sand & luxury — handpicked beachfront stays',       'price' => 'Contact for Price', 'badge_type' => 'badge-hot',  'badge_label' => 'Hot Deal',    'coming_soon' => true, 'enquire_link' => 'https://wa.me/9875073788', 'image_url' => 'https://images.unsplash.com/photo-1542314831-068cd1dbfeeb?w=700&q=80',   'image_path' => null],
                    ['name' => 'City Luxury Hotels',       'subtitle' => "5-star stays in the heart of India's finest cities",    'price' => 'Contact for Price', 'badge_type' => 'badge-gold', 'badge_label' => 'City Luxury', 'coming_soon' => true, 'enquire_link' => 'https://wa.me/9875073788', 'image_url' => 'https://images.unsplash.com/photo-1571003123894-1f0594d2b5d9?w=700&q=80',   'image_path' => null],
                    ['name' => 'Honeymoon Stays',          'subtitle' => 'Private villas & romantic escapes for couples',          'price' => 'Contact for Price', 'badge_type' => 'badge-new',  'badge_label' => 'Romantic',    'coming_soon' => true, 'enquire_link' => 'https://wa.me/9875073788', 'image_url' => 'https://images.unsplash.com/photo-1520250497591-112f2f40a3f4?w=700&q=80',   'image_path' => null],
                    ['name' => 'Family Friendly Resorts',  'subtitle' => 'Fun-filled stays the whole family will love',            'price' => 'Contact for Price', 'badge_type' => 'badge-gold', 'badge_label' => 'Family',      'coming_soon' => true, 'enquire_link' => 'https://wa.me/9875073788', 'image_url' => 'https://images.unsplash.com/photo-1596436889106-be35e843f974?w=700&q=80',   'image_path' => null],
                ],
            ],
            [
                'category_key'  => 'cruises',
                'slider_label'  => 'Cruise Offers',
                'slider_title'  => 'Sail <em>Beyond Ordinary</em>',
                'cards' => [
                    ['name' => 'Scenic Getaways',        'subtitle' => 'Breathtaking coastal routes — Mumbai to Sri Lanka',          'price' => 'Contact for Price', 'badge_type' => 'badge-gold', 'badge_label' => 'Scenic',        'coming_soon' => true, 'enquire_link' => 'https://wa.me/9875073788', 'image_url' => 'https://images.unsplash.com/photo-1548574505-5e239809ee19?w=700&q=80',   'image_path' => null],
                    ['name' => 'Luxury Cruises',          'subtitle' => 'Suite experiences, fine dining & concierge at sea',         'price' => 'Contact for Price', 'badge_type' => 'badge-hot',  'badge_label' => 'Luxury',        'coming_soon' => true, 'enquire_link' => 'https://wa.me/9875073788', 'image_url' => 'https://images.unsplash.com/photo-1590490360182-c33d57733427?w=700&q=80',   'image_path' => null],
                    ['name' => 'International Cruises',   'subtitle' => 'Global voyages — Mediterranean, Caribbean & beyond',        'price' => 'Contact for Price', 'badge_type' => 'badge-new',  'badge_label' => 'International', 'coming_soon' => true, 'enquire_link' => 'https://wa.me/9875073788', 'image_url' => 'https://images.unsplash.com/photo-1545579133-99bb5ad189be?w=700&q=80',   'image_path' => null],
                    ['name' => 'Lakshadweep Special',     'subtitle' => "India's best-kept secret — pristine islands by sea",        'price' => 'Contact for Price', 'badge_type' => 'badge-gold', 'badge_label' => 'Exclusive',     'coming_soon' => true, 'enquire_link' => 'https://wa.me/9875073788', 'image_url' => 'https://images.unsplash.com/photo-1582510003544-4d00b7f74220?w=700&q=80',   'image_path' => null],
                ],
            ],
            [
                'category_key'  => 'flights',
                'slider_label'  => 'Flight Offers',
                'slider_title'  => 'Fly the <em>Right Way</em>',
                'cards' => [
                    ['name' => 'Domestic Flights',      'subtitle' => 'Pan-India routes at the best available fares',             'price' => 'Contact for Price', 'badge_type' => 'badge-gold', 'badge_label' => 'Domestic',      'coming_soon' => true, 'enquire_link' => 'https://wa.me/9875073788', 'image_url' => 'https://images.unsplash.com/photo-1436491865332-7a61a109cc05?w=700&q=80',   'image_path' => null],
                    ['name' => 'International Flights', 'subtitle' => 'Global destinations — all major airlines covered',        'price' => 'Contact for Price', 'badge_type' => 'badge-hot',  'badge_label' => 'International', 'coming_soon' => true, 'enquire_link' => 'https://wa.me/9875073788', 'image_url' => 'https://images.unsplash.com/photo-1503221043305-f7498f8b7888?w=700&q=80',   'image_path' => null],
                    ['name' => 'Business Class',         'subtitle' => 'Lie-flat beds, premium lounges & priority boarding',     'price' => 'Contact for Price', 'badge_type' => 'badge-new',  'badge_label' => 'Business',      'coming_soon' => true, 'enquire_link' => 'https://wa.me/9875073788', 'image_url' => 'https://images.unsplash.com/photo-1540339832862-474599807836?w=700&q=80',   'image_path' => null],
                    ['name' => 'First Class',            'subtitle' => 'Suite experience, personal concierge & fine dining',     'price' => 'Contact for Price', 'badge_type' => 'badge-gold', 'badge_label' => 'First Class',   'coming_soon' => true, 'enquire_link' => 'https://wa.me/9875073788', 'image_url' => 'https://images.unsplash.com/photo-1464037866556-6812c9d1c72e?w=700&q=80',   'image_path' => null],
                ],
            ],
            [
                'category_key'  => 'honeymoon',
                'slider_label'  => 'Honeymoon Packages',
                'slider_title'  => 'Romance <em>Awaits</em>',
                'cards' => [
                    ['name' => 'Goa Honeymoon',         'subtitle' => "Beachside romance in India's party capital",              'price' => 'Contact for Price', 'badge_type' => 'badge-hot',  'badge_label' => 'Popular',       'coming_soon' => true, 'enquire_link' => 'https://wa.me/9875073788', 'image_url' => 'https://images.unsplash.com/photo-1512343879784-a960bf40e7f2?w=700&q=80',   'image_path' => null],
                    ['name' => 'Sri Lanka Escape',       'subtitle' => 'Island paradise — the perfect honeymoon destination',    'price' => 'Contact for Price', 'badge_type' => 'badge-gold', 'badge_label' => 'Exotic',        'coming_soon' => true, 'enquire_link' => 'https://wa.me/9875073788', 'image_url' => 'https://images.unsplash.com/photo-1546975490-a79e31768668?w=700&q=80',   'image_path' => null],
                    ['name' => 'Lakshadweep Retreat',    'subtitle' => 'Secluded island bliss for just the two of you',         'price' => 'Contact for Price', 'badge_type' => 'badge-new',  'badge_label' => 'Private',       'coming_soon' => true, 'enquire_link' => 'https://wa.me/9875073788', 'image_url' => 'https://images.unsplash.com/photo-1582510003544-4d00b7f74220?w=700&q=80',   'image_path' => null],
                    ['name' => 'Cruise + Hotel Bundle',  'subtitle' => 'Sail by day, luxury stay by night — the perfect combo', 'price' => 'Contact for Price', 'badge_type' => 'badge-gold', 'badge_label' => 'Cruise Bundle', 'coming_soon' => true, 'enquire_link' => 'https://wa.me/9875073788', 'image_url' => 'https://images.unsplash.com/photo-1631049307264-da0ec9d70304?w=700&q=80',   'image_path' => null],
                ],
            ],
            [
                'category_key'  => 'family',
                'slider_label'  => 'Family Packages',
                'slider_title'  => 'Holidays for <em>Everyone</em>',
                'cards' => [
                    ['name' => 'Family Resorts',          'subtitle' => 'Kid-friendly stays with pools, activities & more',       'price' => 'Contact for Price', 'badge_type' => 'badge-gold', 'badge_label' => 'Family',        'coming_soon' => true, 'enquire_link' => 'https://wa.me/9875073788', 'image_url' => 'https://images.unsplash.com/photo-1596436889106-be35e843f974?w=700&q=80',   'image_path' => null],
                    ['name' => 'South India Family Tour', 'subtitle' => 'Kochi, Munnar & Kerala backwaters — a cultural journey', 'price' => 'Contact for Price', 'badge_type' => 'badge-hot',  'badge_label' => 'Explore',       'coming_soon' => true, 'enquire_link' => 'https://wa.me/9875073788', 'image_url' => 'https://images.unsplash.com/photo-1609766857272-ec2b31534384?w=700&q=80',   'image_path' => null],
                    ['name' => 'Mumbai City Break',       'subtitle' => 'The city of dreams — sightseeing, food & fun for all',  'price' => 'Contact for Price', 'badge_type' => 'badge-new',  'badge_label' => 'City Break',    'coming_soon' => true, 'enquire_link' => 'https://wa.me/9875073788', 'image_url' => 'https://images.unsplash.com/photo-1595658658481-d53d3f999875?w=700&q=80',   'image_path' => null],
                    ['name' => 'All-Inclusive Packages',  'subtitle' => 'Flights + hotel + meals — everything taken care of',    'price' => 'Contact for Price', 'badge_type' => 'badge-gold', 'badge_label' => 'All-Inclusive', 'coming_soon' => true, 'enquire_link' => 'https://wa.me/9875073788', 'image_url' => 'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?w=700&q=80',   'image_path' => null],
                ],
            ],
        ];

        Setting::updateOrCreate(['key' => 'offers_page.categories'], ['value' => json_encode($categories)]);

        $this->command->info('✅ Offers page settings seeded.');
    }
}
