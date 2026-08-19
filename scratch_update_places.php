<?php
use App\Models\Hotel;
use Illuminate\Support\Str;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$hotel = Hotel::where('slug', Str::slug('Snow Valley Resorts'))->first();
if ($hotel) {
    $hotel->update([
        'nearby_attractions' => "Jakhu Temple (5 km)\nViceregal Lodge (3.5 km)\nHimachal State Museum (2.5 km)",
        'restaurants_cafes' => "Cafe Simla Times (4 km)\nWake and Bake (4.2 km)\nBaljees & Fascination (4 km)",
        'top_attractions' => "The Ridge (4 km)\nMall Road (3.8 km)\nChrist Church (4 km)"
    ]);
    echo "Places added successfully.";
} else {
    echo "Hotel not found.";
}
