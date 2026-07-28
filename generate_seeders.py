import os
import re
import json

base_dir = r"e:\TYTluxe\tyt\tytluxe\resources\views\pages"
seeders_dir = r"e:\TYTluxe\tyt\tytluxe\database\seeders"

files_to_parse = {
    'hotels.blade.php': 'HotelSeeder.php',
    'cruises.blade.php': 'CruiseSeeder.php',
    'staycation.blade.php': 'StaycationSeeder.php'
}

def escape_string(s):
    if s is None:
        return ""
    return s.replace("'", "\\'").strip()

def extract_cards(html, item_type):
    cards = []
    # Find all divs with class htl-card (or crs-card, sty-card)
    pattern = re.compile(rf'<div[^>]*class="[^"]*{item_type}-card[^"]*"([^>]*)>', re.IGNORECASE)
    for match in pattern.finditer(html):
        attr_string = match.group(1)
        
        card_data = {}
        # extract data-* attributes
        attr_pattern = re.compile(r'data-([a-zA-Z0-9-]+)="([^"]*)"')
        for attr_match in attr_pattern.finditer(attr_string):
            key = attr_match.group(1)
            val = attr_match.group(2)
            card_data[key] = val
        
        if card_data:
            cards.append(card_data)
            
    return cards

def generate_hotel_seeder(cards):
    seeder_content = """<?php

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
"""
    for card in cards:
        name = escape_string(card.get('name', ''))
        location = escape_string(card.get('location', ''))
        desc = escape_string(card.get('desc', ''))
        badge = escape_string(card.get('badge', ''))
        features = escape_string(card.get('features', ''))
        rooms = escape_string(card.get('rooms', ''))
        
        seeder_content += f"""
        $dest = Destination::firstOrCreate(['name' => '{location}'], ['slug' => Str::slug('{location}'), 'country' => 'India']);
        $hotel = Hotel::create([
            'destination_id' => $dest->id,
            'title' => '{name}',
            'slug' => Str::slug('{name}'),
            'description' => '{desc}',
            'category' => 'city_luxury', // Default category
            'address' => '{location}, India',
            'star_rating' => 5,
            'price_from' => 10000,
            'is_featured' => true,
        ]);
        
        $features = explode(',', '{features}');
        foreach($features as $feature) {{
            if(trim($feature) != '') {{
                $amenity = Amenity::firstOrCreate(['name' => trim($feature)]);
                $hotel->amenities()->attach($amenity->id);
            }}
        }}
"""
    seeder_content += """
    }
}
"""
    return seeder_content

def generate_cruise_seeder(cards):
    seeder_content = """<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Cruise;
use App\Models\Destination;
use App\Models\Amenity;

class CruiseSeeder extends Seeder
{
    public function run()
    {
"""
    for card in cards:
        name = escape_string(card.get('name', ''))
        location = escape_string(card.get('location', ''))
        desc = escape_string(card.get('desc', ''))
        badge = escape_string(card.get('badge', ''))
        
        seeder_content += f"""
        $dest = Destination::firstOrCreate(['name' => '{location}']);
        $cruise = Cruise::create([
            'destination_id' => $dest->id,
            'name' => '{name}',
            'description' => '{desc}',
            'badge' => '{badge}',
            'is_featured' => true,
        ]);
"""
    seeder_content += """
    }
}
"""
    return seeder_content
    
def generate_staycation_seeder(cards):
    seeder_content = """<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Staycation;
use App\Models\Destination;
use App\Models\Amenity;

class StaycationSeeder extends Seeder
{
    public function run()
    {
"""
    for card in cards:
        name = escape_string(card.get('name', ''))
        location = escape_string(card.get('location', ''))
        desc = escape_string(card.get('desc', ''))
        badge = escape_string(card.get('badge', ''))
        
        seeder_content += f"""
        $dest = Destination::firstOrCreate(['name' => '{location}']);
        $staycation = Staycation::create([
            'destination_id' => $dest->id,
            'name' => '{name}',
            'description' => '{desc}',
            'badge' => '{badge}',
            'is_featured' => true,
        ]);
"""
    seeder_content += """
    }
}
"""
    return seeder_content

for blade_file, seeder_file in files_to_parse.items():
    file_path = os.path.join(base_dir, blade_file)
    if os.path.exists(file_path):
        with open(file_path, 'r', encoding='utf-8') as f:
            html = f.read()
            
        if blade_file == 'hotels.blade.php':
            cards = extract_cards(html, 'htl')
            seeder_content = generate_hotel_seeder(cards)
        elif blade_file == 'cruises.blade.php':
            cards = extract_cards(html, 'crs')
            seeder_content = generate_cruise_seeder(cards)
        elif blade_file == 'staycation.blade.php':
            cards = extract_cards(html, 'sty')
            seeder_content = generate_staycation_seeder(cards)
            
        out_path = os.path.join(seeders_dir, seeder_file)
        with open(out_path, 'w', encoding='utf-8') as f:
            f.write(seeder_content)
        print(f"Generated {seeder_file} with {len(cards)} items.")
    else:
        print(f"File not found: {file_path}")
