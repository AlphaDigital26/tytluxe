<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Hotel;
use App\Models\Destination;
use App\Models\Amenity;
use App\Models\HotelImage;
use App\Models\RoomType;
use Illuminate\Support\Str;

class HotelSeeder extends Seeder
{
    public function run()
    {

        $dest = Destination::firstOrCreate(['name' => 'Shimla'], ['slug' => Str::slug('Shimla'), 'country' => 'India']);
        $hotel = Hotel::create([
            'destination_id' => $dest->id,
            'title' => 'Snow Valley Resort',
            'slug' => Str::slug('Snow Valley Resort'),
            'description' => 'Snow Valley Resort is a comfortable Shimla mountain stay suited for families, couples and leisure travellers who want resort-style facilities with convenient access to the hill city. It works well for guests looking for warm hospitality, restaurant access, room service and a practical base for sightseeing around Shimla. Check-in: 1:00 PM; Check-out: 11:00 AM.',
            'category' => 'city_luxury', // Default category
            'address' => 'Shimla, India',
            'star_rating' => 5,
            'price_from' => 10000,
            'is_featured' => true,
        ]);
        
        $features = explode(',', 'Shimla Mountain Resort,Family Friendly,Restaurant,Room Service,Sightseeing Base,Official Hotel');
        foreach($features as $feature) {
            if(trim($feature) != '') {
                $amenity = Amenity::firstOrCreate(['name' => trim($feature)]);
                $hotel->amenities()->attach($amenity->id);
            }
        }

        $dest = Destination::firstOrCreate(['name' => 'Shimla'], ['slug' => Str::slug('Shimla'), 'country' => 'India']);
        $hotel = Hotel::create([
            'destination_id' => $dest->id,
            'title' => 'Snow Valley Heights',
            'slug' => Str::slug('Snow Valley Heights'),
            'description' => 'Snow Valley Heights is an elevated Snow Valley property in Shimla, positioned for travellers who prefer valley-facing surroundings and polished hill-station comfort. The hotel is suitable for relaxed leisure stays, family trips and guests who want a quieter mountain setting with essential hotel services. Check-in: 1:00 PM; Check-out: 11:00 AM.',
            'category' => 'city_luxury', // Default category
            'address' => 'Shimla, India',
            'star_rating' => 5,
            'price_from' => 10000,
            'is_featured' => true,
        ]);
        
        $features = explode(',', 'Valley Views,Premium Rooms,Shimla Stay,Restaurant,Room Service,Official Hotel');
        foreach($features as $feature) {
            if(trim($feature) != '') {
                $amenity = Amenity::firstOrCreate(['name' => trim($feature)]);
                $hotel->amenities()->attach($amenity->id);
            }
        }

        $dest = Destination::firstOrCreate(['name' => 'Shimla'], ['slug' => Str::slug('Shimla'), 'country' => 'India']);
        $hotel = Hotel::create([
            'destination_id' => $dest->id,
            'title' => '8Fold by Larisa',
            'slug' => Str::slug('8Fold by Larisa'),
            'description' => '8Fold by Larisa in Shimla is a boutique mountain stay from the Larisa collection, designed for guests who want a compact, stylish and calm base in the hills. It is best suited for couples, families and small groups looking for contemporary comfort in a curated Shimla property. Check-in: 2:00 PM; Check-out: 12:00 PM.',
            'category' => 'city_luxury', // Default category
            'address' => 'Shimla, India',
            'star_rating' => 5,
            'price_from' => 10000,
            'is_featured' => true,
        ]);
        
        $features = explode(',', 'Boutique Stay,Larisa Collection,Mountain Setting,Curated Rooms,Shimla,Official Hotel');
        foreach($features as $feature) {
            if(trim($feature) != '') {
                $amenity = Amenity::firstOrCreate(['name' => trim($feature)]);
                $hotel->amenities()->attach($amenity->id);
            }
        }

        $dest = Destination::firstOrCreate(['name' => 'Manali'], ['slug' => Str::slug('Manali'), 'country' => 'India']);
        $hotel = Hotel::create([
            'destination_id' => $dest->id,
            'title' => 'Kanishka - AM Hotel Kollection',
            'slug' => Str::slug('Kanishka - AM Hotel Kollection'),
            'description' => 'Kanishka by AM Hotel Kollection is a Manali stay for travellers who want a comfortable hotel experience close to the hill-town atmosphere. It is suitable for family holidays, couples and leisure guests looking for a practical Manali base with hospitality support and easy access to local experiences.',
            'category' => 'city_luxury', // Default category
            'address' => 'Manali, India',
            'star_rating' => 5,
            'price_from' => 10000,
            'is_featured' => true,
        ]);
        
        $features = explode(',', 'AM Hotel Kollection,Manali Stay,Family Rooms,Restaurant,Hill Views,Official Hotel');
        foreach($features as $feature) {
            if(trim($feature) != '') {
                $amenity = Amenity::firstOrCreate(['name' => trim($feature)]);
                $hotel->amenities()->attach($amenity->id);
            }
        }

        $dest = Destination::firstOrCreate(['name' => 'Manali'], ['slug' => Str::slug('Manali'), 'country' => 'India']);
        $hotel = Hotel::create([
            'destination_id' => $dest->id,
            'title' => 'Larisa Resort - Manali',
            'slug' => Str::slug('Larisa Resort - Manali'),
            'description' => 'Larisa Resort Manali is a luxury mountain resort with a refined, nature-led setting and a quieter resort atmosphere. It is ideal for travellers who want upgraded comfort, scenic surroundings and Larisa hospitality while staying in Manali.',
            'category' => 'city_luxury', // Default category
            'address' => 'Manali, India',
            'star_rating' => 5,
            'price_from' => 10000,
            'is_featured' => true,
        ]);
        
        $features = explode(',', 'Deluxe Garden View,Luxury Suite,Larisa Collection,Fine Dining,Manali Resort,Official Hotel');
        foreach($features as $feature) {
            if(trim($feature) != '') {
                $amenity = Amenity::firstOrCreate(['name' => trim($feature)]);
                $hotel->amenities()->attach($amenity->id);
            }
        }

        $dest = Destination::firstOrCreate(['name' => 'Manali'], ['slug' => Str::slug('Manali'), 'country' => 'India']);
        $hotel = Hotel::create([
            'destination_id' => $dest->id,
            'title' => 'Renest River Country Resort',
            'slug' => Str::slug('Renest River Country Resort'),
            'description' => 'Renest River Country Resort is a Manali resort designed for relaxed holidays, family breaks and guests who want resort facilities in a mountain destination. It is a good fit for travellers who prefer an easy stay with restaurant access, hospitality services and a comfortable base for Manali sightseeing.',
            'category' => 'city_luxury', // Default category
            'address' => 'Manali, India',
            'star_rating' => 5,
            'price_from' => 10000,
            'is_featured' => true,
        ]);
        
        $features = explode(',', 'Renest Hotels,Mountain Resort,Family Friendly,Restaurant,Manali Stay,Official Hotel');
        foreach($features as $feature) {
            if(trim($feature) != '') {
                $amenity = Amenity::firstOrCreate(['name' => trim($feature)]);
                $hotel->amenities()->attach($amenity->id);
            }
        }

        $dest = Destination::firstOrCreate(['name' => 'Manali'], ['slug' => Str::slug('Manali'), 'country' => 'India']);
        $hotel = Hotel::create([
            'destination_id' => $dest->id,
            'title' => 'Tiaraa Hotel &amp; Resort',
            'slug' => Str::slug('Tiaraa Hotel &amp; Resort'),
            'description' => 'Tiaraa Hotel &amp; Resort Manali is a premium resort-style stay for travellers looking for polished hospitality, mountain scenery and a comfortable holiday base. It suits families, couples and leisure groups who want a more refined Manali resort experience.',
            'category' => 'city_luxury', // Default category
            'address' => 'Manali, India',
            'star_rating' => 5,
            'price_from' => 10000,
            'is_featured' => true,
        ]);
        
        $features = explode(',', 'Premium Resort,Mountain Scenery,Restaurant,Family Friendly,Manali,Official Hotel');
        foreach($features as $feature) {
            if(trim($feature) != '') {
                $amenity = Amenity::firstOrCreate(['name' => trim($feature)]);
                $hotel->amenities()->attach($amenity->id);
            }
        }

        $dest = Destination::firstOrCreate(['name' => 'Manali'], ['slug' => Str::slug('Manali'), 'country' => 'India']);
        $hotel = Hotel::create([
            'destination_id' => $dest->id,
            'title' => 'Moustache Select, Manali',
            'slug' => Str::slug('Moustache Select, Manali'),
            'description' => 'Moustache Select Manali is a modern stay for travellers who want style, comfort and easy access to the mountains. It works well for couples, friend groups and young leisure travellers who prefer a hotel with contemporary rooms and social energy.',
            'category' => 'city_luxury', // Default category
            'address' => 'Manali, India',
            'star_rating' => 5,
            'price_from' => 10000,
            'is_featured' => true,
        ]);
        
        $features = explode(',', 'Moustache Select,Stylish Rooms,Manali,Travel Desk,Social Spaces,Official Hotel');
        foreach($features as $feature) {
            if(trim($feature) != '') {
                $amenity = Amenity::firstOrCreate(['name' => trim($feature)]);
                $hotel->amenities()->attach($amenity->id);
            }
        }

        $dest = Destination::firstOrCreate(['name' => 'Mussoorie'], ['slug' => Str::slug('Mussoorie'), 'country' => 'India']);
        $hotel = Hotel::create([
            'destination_id' => $dest->id,
            'title' => 'Moustache Select, Mussoorie',
            'slug' => Str::slug('Moustache Select, Mussoorie'),
            'description' => 'Moustache Select Mussoorie is a contemporary hotel option for travellers visiting the Queen of Hills. It is suited for couples, families and friend groups who want a stylish Mussoorie stay with convenient access to the hill-station experience.',
            'category' => 'city_luxury', // Default category
            'address' => 'Mussoorie, India',
            'star_rating' => 5,
            'price_from' => 10000,
            'is_featured' => true,
        ]);
        
        $features = explode(',', 'Moustache Select,Mussoorie,Stylish Rooms,Valley Setting,Social Spaces,Official Hotel');
        foreach($features as $feature) {
            if(trim($feature) != '') {
                $amenity = Amenity::firstOrCreate(['name' => trim($feature)]);
                $hotel->amenities()->attach($amenity->id);
            }
        }

        $dest = Destination::firstOrCreate(['name' => 'Mussoorie'], ['slug' => Str::slug('Mussoorie'), 'country' => 'India']);
        $hotel = Hotel::create([
            'destination_id' => $dest->id,
            'title' => 'Everest Base Camp',
            'slug' => Str::slug('Everest Base Camp'),
            'description' => 'Everest Base Camp Mussoorie is a themed forest-and-mountain retreat inspired by Himalayan expedition living. The property is best for guests who want a distinctive stay experience, outdoor character and boutique accommodation rather than a standard city hotel. Check-in: 2:00 PM; Check-out: 10:30 AM.',
            'category' => 'city_luxury', // Default category
            'address' => 'Mussoorie, India',
            'star_rating' => 5,
            'price_from' => 10000,
            'is_featured' => true,
        ]);
        
        $features = explode(',', 'Themed Stay,Mussoorie,Forest Setting,Glamping Style,Room Categories,Official Hotel');
        foreach($features as $feature) {
            if(trim($feature) != '') {
                $amenity = Amenity::firstOrCreate(['name' => trim($feature)]);
                $hotel->amenities()->attach($amenity->id);
            }
        }

        $dest = Destination::firstOrCreate(['name' => 'Jaipur'], ['slug' => Str::slug('Jaipur'), 'country' => 'India']);
        $hotel = Hotel::create([
            'destination_id' => $dest->id,
            'title' => 'Buena Vista',
            'slug' => Str::slug('Buena Vista'),
            'description' => 'Buena Vista Jaipur is a luxury resort retreat with a palatial design language, garden-led spaces and a refined leisure atmosphere. It is suited for couples, luxury travellers and families looking for a polished Jaipur stay away from the regular city-hotel format. Check-in: 2:00 PM; Check-out: 11:00 AM.',
            'category' => 'city_luxury', // Default category
            'address' => 'Jaipur, India',
            'star_rating' => 5,
            'price_from' => 10000,
            'is_featured' => true,
        ]);
        
        $features = explode(',', 'Palatial Garden Tents,Jaipur,Heritage Feel,Luxury Resort,Private Villas,Official Hotel');
        foreach($features as $feature) {
            if(trim($feature) != '') {
                $amenity = Amenity::firstOrCreate(['name' => trim($feature)]);
                $hotel->amenities()->attach($amenity->id);
            }
        }

        $dest = Destination::firstOrCreate(['name' => 'Udaipur'], ['slug' => Str::slug('Udaipur'), 'country' => 'India']);
        $hotel = Hotel::create([
            'destination_id' => $dest->id,
            'title' => 'Hotel Lakend',
            'slug' => Str::slug('Hotel Lakend'),
            'description' => 'Hotel Lakend is a lakeside Udaipur hotel for guests who want scenic water views, city access and a resort-like setting. It works well for couples, families and leisure travellers planning a relaxed Udaipur stay close to the lake experience. Check-in: 1:00 PM; Check-out: 10:00 AM.',
            'category' => 'city_luxury', // Default category
            'address' => 'Udaipur, India',
            'star_rating' => 5,
            'price_from' => 10000,
            'is_featured' => true,
        ]);
        
        $features = explode(',', 'Lakeside,Udaipur,Room Categories,Restaurant,City of Lakes,Official Hotel');
        foreach($features as $feature) {
            if(trim($feature) != '') {
                $amenity = Amenity::firstOrCreate(['name' => trim($feature)]);
                $hotel->amenities()->attach($amenity->id);
            }
        }

        $dest = Destination::firstOrCreate(['name' => 'Udaipur'], ['slug' => Str::slug('Udaipur'), 'country' => 'India']);
        $hotel = Hotel::create([
            'destination_id' => $dest->id,
            'title' => 'Anandam - A Jungle Retreat',
            'slug' => Str::slug('Anandam - A Jungle Retreat'),
            'description' => 'Anandam - A Jungle Retreat is a nature-focused resort near Udaipur for travellers who want greenery, quiet surroundings and a retreat-style break. It is suitable for families, couples and groups who prefer open spaces and a slower stay experience outside the usual city setting. Check-in: 2:00 PM; Check-out: 11:00 AM.',
            'category' => 'city_luxury', // Default category
            'address' => 'Udaipur, India',
            'star_rating' => 5,
            'price_from' => 10000,
            'is_featured' => true,
        ]);
        
        $features = explode(',', 'Jungle Retreat,Udaipur,Nature Stay,Resort Experience,Room Categories,Official Hotel');
        foreach($features as $feature) {
            if(trim($feature) != '') {
                $amenity = Amenity::firstOrCreate(['name' => trim($feature)]);
                $hotel->amenities()->attach($amenity->id);
            }
        }

        $dest = Destination::firstOrCreate(['name' => 'Udaipur'], ['slug' => Str::slug('Udaipur'), 'country' => 'India']);
        $hotel = Hotel::create([
            'destination_id' => $dest->id,
            'title' => 'Moustache Verandah, Udaipur',
            'slug' => Str::slug('Moustache Verandah, Udaipur'),
            'description' => 'Moustache Verandah Udaipur is a boutique stay for travellers who want a stylish city base with the Moustache hospitality style. It suits couples, friend groups and leisure guests planning to explore Udaipur while staying in a character-led property.',
            'category' => 'city_luxury', // Default category
            'address' => 'Udaipur, India',
            'star_rating' => 5,
            'price_from' => 10000,
            'is_featured' => true,
        ]);
        
        $features = explode(',', 'Moustache,Udaipur,Boutique Stay,City Stay,Room Categories,Official Hotel');
        foreach($features as $feature) {
            if(trim($feature) != '') {
                $amenity = Amenity::firstOrCreate(['name' => trim($feature)]);
                $hotel->amenities()->attach($amenity->id);
            }
        }

        $dest = Destination::firstOrCreate(['name' => 'Udaipur'], ['slug' => Str::slug('Udaipur'), 'country' => 'India']);
        $hotel = Hotel::create([
            'destination_id' => $dest->id,
            'title' => 'Moustache Luxuria, Udaipur',
            'slug' => Str::slug('Moustache Luxuria, Udaipur'),
            'description' => 'Moustache Luxuria Udaipur is a premium boutique-luxury option within the Moustache portfolio. It is suited for travellers who want upgraded comfort, polished interiors and an Udaipur base with a more refined stay experience.',
            'category' => 'city_luxury', // Default category
            'address' => 'Udaipur, India',
            'star_rating' => 5,
            'price_from' => 10000,
            'is_featured' => true,
        ]);
        
        $features = explode(',', 'Moustache Luxuria,Udaipur,Premium Rooms,Boutique Luxury,Room Categories,Official Hotel');
        foreach($features as $feature) {
            if(trim($feature) != '') {
                $amenity = Amenity::firstOrCreate(['name' => trim($feature)]);
                $hotel->amenities()->attach($amenity->id);
            }
        }

        $dest = Destination::firstOrCreate(['name' => 'Rishikesh'], ['slug' => Str::slug('Rishikesh'), 'country' => 'India']);
        $hotel = Hotel::create([
            'destination_id' => $dest->id,
            'title' => 'Yog Niketan by Sanskriti',
            'slug' => Str::slug('Yog Niketan by Sanskriti'),
            'description' => 'Yog Niketan by Sanskriti is a calm Rishikesh stay built around wellness, spirituality and a peaceful riverside-town experience. It is ideal for guests interested in yoga, quiet hospitality, temple-town access and a slower retreat atmosphere.',
            'category' => 'city_luxury', // Default category
            'address' => 'Rishikesh, India',
            'star_rating' => 5,
            'price_from' => 10000,
            'is_featured' => true,
        ]);
        
        $features = explode(',', 'Wellness Stay,Rishikesh,Yoga Setting,Peaceful Retreat,Room Categories,Official Hotel');
        foreach($features as $feature) {
            if(trim($feature) != '') {
                $amenity = Amenity::firstOrCreate(['name' => trim($feature)]);
                $hotel->amenities()->attach($amenity->id);
            }
        }

        $dest = Destination::firstOrCreate(['name' => 'Rishikesh'], ['slug' => Str::slug('Rishikesh'), 'country' => 'India']);
        $hotel = Hotel::create([
            'destination_id' => $dest->id,
            'title' => 'Moustache Select Riverside Resort',
            'slug' => Str::slug('Moustache Select Riverside Resort'),
            'description' => 'Moustache Select Riverside Resort Rishikesh is a resort-style stay for travellers who want river proximity, outdoor activities and a relaxed setting. It works well for friend groups, couples and adventure travellers looking for rafting access and nature-led downtime.',
            'category' => 'city_luxury', // Default category
            'address' => 'Rishikesh, India',
            'star_rating' => 5,
            'price_from' => 10000,
            'is_featured' => true,
        ]);
        
        $features = explode(',', 'Riverside Resort,Rishikesh,Moustache Select,Adventure Access,Room Categories,Official Hotel');
        foreach($features as $feature) {
            if(trim($feature) != '') {
                $amenity = Amenity::firstOrCreate(['name' => trim($feature)]);
                $hotel->amenities()->attach($amenity->id);
            }
        }

        $dest = Destination::firstOrCreate(['name' => 'Bhimtal'], ['slug' => Str::slug('Bhimtal'), 'country' => 'India']);
        $hotel = Hotel::create([
            'destination_id' => $dest->id,
            'title' => '8Fold - Pinecrest by Larisa',
            'slug' => Str::slug('8Fold - Pinecrest by Larisa'),
            'description' => '8Fold - Pinecrest by Larisa is a boutique Bhimtal escape with a calm Kumaon setting. It is suited for guests who want a quieter hill stay, boutique hospitality and easy access to the lake-region atmosphere.',
            'category' => 'city_luxury', // Default category
            'address' => 'Bhimtal, India',
            'star_rating' => 5,
            'price_from' => 10000,
            'is_featured' => true,
        ]);
        
        $features = explode(',', 'Pinecrest,Larisa Collection,Bhimtal,Boutique Stay,Room Categories,Official Hotel');
        foreach($features as $feature) {
            if(trim($feature) != '') {
                $amenity = Amenity::firstOrCreate(['name' => trim($feature)]);
                $hotel->amenities()->attach($amenity->id);
            }
        }

        $dest = Destination::firstOrCreate(['name' => 'Bhimtal'], ['slug' => Str::slug('Bhimtal'), 'country' => 'India']);
        $hotel = Hotel::create([
            'destination_id' => $dest->id,
            'title' => 'Moustache Luxuria, Bhimtal',
            'slug' => Str::slug('Moustache Luxuria, Bhimtal'),
            'description' => 'Moustache Luxuria Bhimtal is a premium boutique stay for travellers who want upgraded comfort in the lake region. It works well for couples, families and groups looking for a scenic Bhimtal base with polished rooms.',
            'category' => 'city_luxury', // Default category
            'address' => 'Bhimtal, India',
            'star_rating' => 5,
            'price_from' => 10000,
            'is_featured' => true,
        ]);
        
        $features = explode(',', 'Moustache Luxuria,Bhimtal,Premium Rooms,Lake Region,Room Categories,Official Hotel');
        foreach($features as $feature) {
            if(trim($feature) != '') {
                $amenity = Amenity::firstOrCreate(['name' => trim($feature)]);
                $hotel->amenities()->attach($amenity->id);
            }
        }

        $dest = Destination::firstOrCreate(['name' => 'Goa'], ['slug' => Str::slug('Goa'), 'country' => 'India']);
        $hotel = Hotel::create([
            'destination_id' => $dest->id,
            'title' => 'Renest Calangute',
            'slug' => Str::slug('Renest Calangute'),
            'description' => 'Renest Calangute is a Goa hotel positioned for beach holidays, casual leisure trips and easy access to the Calangute area. It is a practical option for couples, families and groups wanting a coastal stay with hotel services.',
            'category' => 'city_luxury', // Default category
            'address' => 'Goa, India',
            'star_rating' => 5,
            'price_from' => 10000,
            'is_featured' => true,
        ]);
        
        $features = explode(',', 'Renest Hotels,Calangute,Goa Stay,Beach Access,Room Categories,Official Hotel');
        foreach($features as $feature) {
            if(trim($feature) != '') {
                $amenity = Amenity::firstOrCreate(['name' => trim($feature)]);
                $hotel->amenities()->attach($amenity->id);
            }
        }

        $dest = Destination::firstOrCreate(['name' => 'Goa'], ['slug' => Str::slug('Goa'), 'country' => 'India']);
        $hotel = Hotel::create([
            'destination_id' => $dest->id,
            'title' => 'Larisa Resort &amp; Spa - Ashwem Goa',
            'slug' => Str::slug('Larisa Resort &amp; Spa - Ashwem Goa'),
            'description' => 'Larisa Resort &amp; Spa Ashwem Goa is a beach-resort stay for travellers who want a quieter North Goa setting with cottage-style accommodation. It is well suited for couples, families and leisure guests looking for Larisa hospitality close to Ashwem Beach.',
            'category' => 'city_luxury', // Default category
            'address' => 'Goa, India',
            'star_rating' => 5,
            'price_from' => 10000,
            'is_featured' => true,
        ]);
        
        $features = explode(',', '2 Bedroom Cottage,Cottage Suite,Luxury Cottage,Ashwem Beach,Larisa Collection,Official Hotel');
        foreach($features as $feature) {
            if(trim($feature) != '') {
                $amenity = Amenity::firstOrCreate(['name' => trim($feature)]);
                $hotel->amenities()->attach($amenity->id);
            }
        }

        $dest = Destination::firstOrCreate(['name' => 'Goa'], ['slug' => Str::slug('Goa'), 'country' => 'India']);
        $hotel = Hotel::create([
            'destination_id' => $dest->id,
            'title' => '8Fold by Larisa - Siolim Goa',
            'slug' => Str::slug('8Fold by Larisa - Siolim Goa'),
            'description' => '8Fold by Larisa Siolim Goa is a boutique stay in a calmer North Goa neighbourhood, suited for travellers who want comfort, design-led hospitality and access to nearby beaches without staying in the busiest beach belts.',
            'category' => 'city_luxury', // Default category
            'address' => 'Goa, India',
            'star_rating' => 5,
            'price_from' => 10000,
            'is_featured' => true,
        ]);
        
        $features = explode(',', 'Standard Room,Deluxe Room,Premium Room,Siolim,Larisa Collection,Official Hotel');
        foreach($features as $feature) {
            if(trim($feature) != '') {
                $amenity = Amenity::firstOrCreate(['name' => trim($feature)]);
                $hotel->amenities()->attach($amenity->id);
            }
        }

        $dest = Destination::firstOrCreate(['name' => 'Jibhi'], ['slug' => Str::slug('Jibhi'), 'country' => 'India']);
        $hotel = Hotel::create([
            'destination_id' => $dest->id,
            'title' => 'Bradhi Resorts',
            'slug' => Str::slug('Bradhi Resorts'),
            'description' => 'Bradhi Resorts is a Jibhi stay for guests who want valley scenery, nature access and the quieter side of Himachal. It is suited for couples, families and small groups looking for a relaxed mountain break in the Tirthan-Jibhi region.',
            'category' => 'city_luxury', // Default category
            'address' => 'Jibhi, India',
            'star_rating' => 5,
            'price_from' => 10000,
            'is_featured' => true,
        ]);
        
        $features = explode(',', 'Jibhi,Tirthan Valley,Nature Stay,Forest Setting,Room Categories,Official Hotel');
        foreach($features as $feature) {
            if(trim($feature) != '') {
                $amenity = Amenity::firstOrCreate(['name' => trim($feature)]);
                $hotel->amenities()->attach($amenity->id);
            }
        }

        $dest = Destination::firstOrCreate(['name' => 'Jibhi'], ['slug' => Str::slug('Jibhi'), 'country' => 'India']);
        $hotel = Hotel::create([
            'destination_id' => $dest->id,
            'title' => '8Fold by Larisa - Jibhi',
            'slug' => Str::slug('8Fold by Larisa - Jibhi'),
            'description' => '8Fold by Larisa Jibhi is a boutique mountain property for travellers who want Larisa-style comfort in a scenic Himachal setting. It is suitable for peaceful holidays, nature-led trips and travellers who prefer small-format boutique stays.',
            'category' => 'city_luxury', // Default category
            'address' => 'Jibhi, India',
            'star_rating' => 5,
            'price_from' => 10000,
            'is_featured' => true,
        ]);
        
        $features = explode(',', '8Fold,Larisa Collection,Jibhi,Boutique Stay,Room Categories,Official Hotel');
        foreach($features as $feature) {
            if(trim($feature) != '') {
                $amenity = Amenity::firstOrCreate(['name' => trim($feature)]);
                $hotel->amenities()->attach($amenity->id);
            }
        }

        $dest = Destination::firstOrCreate(['name' => 'Jaipur'], ['slug' => Str::slug('Jaipur'), 'country' => 'India']);
        $hotel = Hotel::create([
            'destination_id' => $dest->id,
            'title' => 'Brij Baggecha, Kukas Jaipur',
            'slug' => Str::slug('Brij Baggecha, Kukas Jaipur'),
            'description' => 'Brij Baggecha Kukas Jaipur is a refined retreat-style stay near Jaipur, suitable for guests who want Brij hospitality, calm surroundings and a polished leisure experience outside the dense city core.',
            'category' => 'city_luxury', // Default category
            'address' => 'Jaipur, India',
            'star_rating' => 5,
            'price_from' => 10000,
            'is_featured' => true,
        ]);
        
        $features = explode(',', 'Brij Hotels,Kukas Jaipur,Luxury Retreat,Jaipur Stay,Room Categories,Official Hotel');
        foreach($features as $feature) {
            if(trim($feature) != '') {
                $amenity = Amenity::firstOrCreate(['name' => trim($feature)]);
                $hotel->amenities()->attach($amenity->id);
            }
        }

        $dest = Destination::firstOrCreate(['name' => 'Kasol'], ['slug' => Str::slug('Kasol'), 'country' => 'India']);
        $hotel = Hotel::create([
            'destination_id' => $dest->id,
            'title' => 'Kailasha - The Himalayan Village Resort',
            'slug' => Str::slug('Kailasha - The Himalayan Village Resort'),
            'description' => 'Kailasha - The Himalayan Village Resort is a Kasol mountain stay for travellers looking for a Himalayan village-resort atmosphere, nature views and a relaxed base in the Parvati Valley region.',
            'category' => 'city_luxury', // Default category
            'address' => 'Kasol, India',
            'star_rating' => 5,
            'price_from' => 10000,
            'is_featured' => true,
        ]);
        
        $features = explode(',', 'Kasol,Himalayan Resort,Mountain Stay,Nature Escape,Room Categories,Approved Hotel');
        foreach($features as $feature) {
            if(trim($feature) != '') {
                $amenity = Amenity::firstOrCreate(['name' => trim($feature)]);
                $hotel->amenities()->attach($amenity->id);
            }
        }

        $dest = Destination::firstOrCreate(['name' => 'Kasol'], ['slug' => Str::slug('Kasol'), 'country' => 'India']);
        $hotel = Hotel::create([
            'destination_id' => $dest->id,
            'title' => 'Itsy Bitsy Cabin',
            'slug' => Str::slug('Itsy Bitsy Cabin'),
            'description' => 'Itsy Bitsy Cabin is a distinctive Kasol cabin stay with multiple themed accommodation types, suited for couples, friends and small groups who want a memorable mountain stay rather than a standard hotel room.',
            'category' => 'city_luxury', // Default category
            'address' => 'Kasol, India',
            'star_rating' => 5,
            'price_from' => 10000,
            'is_featured' => true,
        ]);
        
        $features = explode(',', 'Dome Cabin - up to 3 adults,Moonlight Chalet - up to 3 adults,Luar Chalet - up to 3 adults,A-Frame Cabin - up to 4 adults,Starlight Dome - up to 4 adults,Official Hotel');
        foreach($features as $feature) {
            if(trim($feature) != '') {
                $amenity = Amenity::firstOrCreate(['name' => trim($feature)]);
                $hotel->amenities()->attach($amenity->id);
            }
        }

    }
}
