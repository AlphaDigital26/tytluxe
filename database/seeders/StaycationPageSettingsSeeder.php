<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class StaycationPageSettingsSeeder extends Seeder
{
    public function run(): void
    {
        // ── Hero ──────────────────────────────────────────────────────────
        Setting::updateOrCreate(['key' => 'staycation_page.hero_eyebrow'],  ['value' => 'Curated Staycations']);
        Setting::updateOrCreate(['key' => 'staycation_page.hero_title'],    ['value' => 'Escape the Ordinary. <em>Stay Extraordinary.</em>']);
        Setting::updateOrCreate(['key' => 'staycation_page.hero_subtitle'], ['value' => 'Handpicked resort stays near Mumbai & Pune - perfect for weekends, honeymoons & family getaways.']);

        // ── Hero Carousel Images ──────────────────────────────────────────
        Setting::updateOrCreate(['key' => 'staycation_page.hero_images'], ['value' => json_encode([
            ['image_url' => 'https://meritashotels.com/wp-content/uploads/2023/06/Deluxe-Room.jpg',                         'image_path' => null],
            ['image_url' => 'https://meritashotels.com/wp-content/uploads/2023/03/DSC_9452-HDR-copy.jpg',                   'image_path' => null],
            ['image_url' => 'https://meritashotels.com/wp-content/uploads/2023/03/Standard-Room-with-Sit-Out3.png',         'image_path' => null],
            ['image_url' => 'https://meritashotels.com/wp-content/uploads/2023/03/Suite-Bed-Room-%40-Picaddle.jpg',         'image_path' => null],
            ['image_url' => 'https://meritashotels.com/wp-content/uploads/2023/03/DSC_9476-HDR-copy.jpg',                   'image_path' => null],
        ])]);

        // ── Bottom CTA ────────────────────────────────────────────────────
        Setting::updateOrCreate(['key' => 'staycation_page.cta_tag'],      ['value' => 'Book Your Staycation']);
        Setting::updateOrCreate(['key' => 'staycation_page.cta_heading'],   ['value' => 'Ready for Your <em>Perfect Escape?</em>']);
        Setting::updateOrCreate(['key' => 'staycation_page.cta_body'],      ['value' => "WhatsApp us with your dates and preferences - we'll get you the best rates on all Meritas properties instantly."]);
        Setting::updateOrCreate(['key' => 'staycation_page.cta_whatsapp'],  ['value' => 'https://wa.me/919875073788']);

        // ── Resorts with Rooms ────────────────────────────────────────────
        $resorts = [
            [
                'label'       => 'Lonavala - Resort 01',
                'name'        => 'Meritas Picaddle Resort, <em>Lonavala</em>',
                'description' => 'Known for its opulence and grandness, Meritas Picaddle Resort has firmly established itself as one of the top ranking luxury hotels in Lonavala. This 3 star resort has earned the recognition of being the best resort in Lonavala & within the vicinity of Mumbai and Pune.',
                'rooms'       => [
                    [
                        'name'        => 'Deluxe Room',
                        'description' => 'Pool-side view with elegant charm. Ideal for a luxurious and comforting retreat. Size: 280 Sq. Ft.',
                        'amenities'   => 'Up to 3 Guests, Queen Bed, AC, Flat-Screen TV, Free WiFi, Minibar, Kettle',
                        'image_url'   => 'https://meritashotels.com/wp-content/uploads/2023/06/Deluxe-Room.jpg',
                        'image_path'  => null,
                    ],
                    [
                        'name'        => 'Deluxe Room with Bathtub',
                        'description' => 'Pool-side view with a private bathtub for added luxury. Size: 280 Sq. Ft.',
                        'amenities'   => 'Up to 3 Guests, Queen Bed, AC, Flat-Screen TV, Free WiFi, Bathtub, Minibar, Kettle',
                        'image_url'   => 'https://meritashotels.com/wp-content/uploads/2023/06/1683639117_593_Bathtub.jpeg',
                        'image_path'  => null,
                    ],
                    [
                        'name'        => 'Executive Room',
                        'description' => 'City or pool views in lavish fully-equipped rooms. Size: 310-340 Sq. Ft.',
                        'amenities'   => 'Up to 3 Guests, Queen Bed, AC, Flat-Screen TV, Free WiFi, Minibar, Kettle',
                        'image_url'   => 'https://meritashotels.com/wp-content/uploads/2023/03/Executive-Room-Image-2.jpg',
                        'image_path'  => null,
                    ],
                    [
                        'name'        => 'Executive Room with Bathtub',
                        'description' => 'City or pool views with a private bathtub. Size: 310-340 Sq. Ft.',
                        'amenities'   => 'Up to 3 Guests, Queen Bed, AC, Flat-Screen TV, Free WiFi, Bathtub, Minibar, Kettle',
                        'image_url'   => 'https://meritashotels.com/wp-content/uploads/2023/03/Executive-Room-image-3.jpg',
                        'image_path'  => null,
                    ],
                    [
                        'name'        => 'Suite',
                        'description' => 'Spacious 550 Sq. Ft. suite with private living room, bedroom and exquisite furnishings.',
                        'amenities'   => 'Up to 3 Guests, Queen Bed, AC, Flat-Screen TV, Free WiFi, Bathtub, Minibar, Kettle',
                        'image_url'   => 'https://meritashotels.com/wp-content/uploads/2023/03/Suite-Bed-Room-%40-Picaddle.jpg',
                        'image_path'  => null,
                    ],
                ],
            ],
            [
                'label'       => 'Lonavala - Resort 02',
                'name'        => 'Meritas Aura Resort, <em>Lonavala</em>',
                'description' => "A magnificent resort settled within lush hills. Whether you're looking for a quiet romantic retreat, a rejuvenating time with friends, or some solitary respite - the resort will ensure your stay is truly remarkable.",
                'rooms'       => [
                    [
                        'name'        => 'Deluxe Room with Sit Out',
                        'description' => 'Comfortable deluxe rooms with a private sit-out balcony to soak in the lush surroundings.',
                        'amenities'   => 'Up to 3 Guests, Queen Bed, AC, LED TV, Free WiFi, Balcony',
                        'image_url'   => 'https://meritashotels.com/wp-content/uploads/2023/03/DSC_9452-HDR-copy.jpg',
                        'image_path'  => null,
                    ],
                    [
                        'name'        => 'Superior Room with Balcony',
                        'description' => 'Elevated comfort with a private balcony overlooking the hills of Lonavala.',
                        'amenities'   => 'Up to 3 Guests, Queen Bed, AC, LED TV, Free WiFi, Balcony, Kettle',
                        'image_url'   => 'https://meritashotels.com/wp-content/uploads/2023/03/DSC_9419-HDR-copy.jpg',
                        'image_path'  => null,
                    ],
                    [
                        'name'        => 'Suite Room with Sit Out',
                        'description' => 'Spacious suite experience with a private sit-out, perfect for families and couples.',
                        'amenities'   => 'Up to 4 Guests, Queen Bed, AC, LED TV, Free WiFi, Balcony, Kettle',
                        'image_url'   => 'https://meritashotels.com/wp-content/uploads/2023/03/DSC_9424-HDR-copy.jpg',
                        'image_path'  => null,
                    ],
                    [
                        'name'        => 'Suite Room with Plunge Pool',
                        'description' => 'Ultimate luxury - a private plunge pool suite with balcony views of the hills.',
                        'amenities'   => 'Up to 4 Guests, Queen Bed, AC, LED TV, Free WiFi, Balcony, Kettle',
                        'image_url'   => 'https://meritashotels.com/wp-content/uploads/2023/03/DSC_9476-HDR-copy.jpg',
                        'image_path'  => null,
                    ],
                    [
                        'name'        => 'Row House with Sit Out',
                        'description' => 'Ideal for large groups - a full row house with sit-out terrace and valley views.',
                        'amenities'   => 'Up to 10 Guests, Queen Bed, AC, LED TV, Free WiFi, Balcony, Kettle',
                        'image_url'   => 'https://meritashotels.com/wp-content/uploads/2023/03/DSC_9452-HDR-copy.jpg',
                        'image_path'  => null,
                    ],
                    [
                        'name'        => 'Row House with Large Plunge Pool',
                        'description' => 'Exclusive row house with a private large plunge pool - the ultimate group retreat.',
                        'amenities'   => 'Up to 10 Guests, Queen Bed, AC, LED TV, Free WiFi, Balcony, Kettle',
                        'image_url'   => 'https://meritashotels.com/wp-content/uploads/2023/03/DSC_9476-HDR-copy.jpg',
                        'image_path'  => null,
                    ],
                ],
            ],
            [
                'label'       => 'Lonavala - Resort 03',
                'name'        => 'Meritas Crystal Resort, <em>Lonavala</em>',
                'description' => 'Nestled in the majestic Lonavala valley, Meritas Crystal Resort is an extension of the trademark luxury offered across all Meritas properties. A great combination of accommodation, amenities and serenity - with state-of-the-art facilities for complete comfort.',
                'rooms'       => [
                    [
                        'name'        => 'Standard Room with Sit Out',
                        'description' => 'Comfortable well-appointed room with a private sit-out to enjoy the valley breeze.',
                        'amenities'   => 'Up to 2 Guests, Queen Bed, AC, LED TV, Free WiFi, Kettle',
                        'image_url'   => 'https://meritashotels.com/wp-content/uploads/2023/03/Standard-Room-with-Sit-Out3.png',
                        'image_path'  => null,
                    ],
                    [
                        'name'        => 'Deluxe Room',
                        'description' => 'Stylish deluxe rooms with modern amenities and the serene Lonavala atmosphere.',
                        'amenities'   => 'Up to 3 Guests, Queen Bed, AC, LED TV, Free WiFi, Kettle',
                        'image_url'   => 'https://meritashotels.com/wp-content/uploads/2023/03/Deluxe-Room.png',
                        'image_path'  => null,
                    ],
                    [
                        'name'        => 'Executive Room',
                        'description' => 'Indulgent executive accommodation with king bed and premium minibar service.',
                        'amenities'   => 'Up to 3 Guests, King Bed, AC, LED TV, Free WiFi, Minibar, Kettle',
                        'image_url'   => 'https://meritashotels.com/wp-content/uploads/2023/03/Executive-Room-1.png',
                        'image_path'  => null,
                    ],
                    [
                        'name'        => 'Superior Room with Loft',
                        'description' => 'Unique loft-style room ideal for families - king bed with ample space for up to 4 guests.',
                        'amenities'   => 'Up to 4 Guests, King Bed, AC, LED TV, Free WiFi, Minibar, Kettle',
                        'image_url'   => 'https://meritashotels.com/wp-content/uploads/2023/03/Superior-Room-with-Loft-3.png',
                        'image_path'  => null,
                    ],
                    [
                        'name'        => 'Super Deluxe Room with Bathtub & Balcony',
                        'description' => 'Premium room with king bed, private bathtub and a stunning balcony view.',
                        'amenities'   => 'Up to 3 Guests, King Bed, AC, LED TV, Free WiFi, Bathtub, Minibar, Kettle',
                        'image_url'   => 'https://meritashotels.com/wp-content/uploads/2023/03/Super-Deluxe-Room-with-Bathtub-and-Balcony1.png',
                        'image_path'  => null,
                    ],
                    [
                        'name'        => 'Premium Room with Jacuzzi & Terrace',
                        'description' => 'The pinnacle of luxury - king bed, sofa bed, private jacuzzi and a personal terrace.',
                        'amenities'   => 'Up to 4 Guests, King Bed, Sofa Bed, AC, LED TV, Free WiFi, Jacuzzi, Minibar, Kettle',
                        'image_url'   => 'https://meritashotels.com/wp-content/uploads/2023/03/337629365.jpg',
                        'image_path'  => null,
                    ],
                ],
            ],
        ];

        Setting::updateOrCreate(
            ['key' => 'staycation_page.resorts'],
            ['value' => json_encode($resorts)]
        );

        $this->command->info('✅ Staycation page settings seeded.');
    }
}
