<?php

$resources = [
    'Enquiries\EnquiryResource' => 10,
    'Bookings\BookingResource' => 20,
    'Packages\PackageResource' => 30,
    'Hotels\HotelResource' => 40,
    'Cruises\CruiseResource' => 50,
    'Destinations\DestinationResource' => 60,
    'Reviews\ReviewResource' => 70,
    'Amenities\AmenityResource' => 80,
    'Users\UserResource' => 90,
    'Admins\AdminResource' => 100,
    'Settings\SettingResource' => 110,
    'BlogCategories\BlogCategoryResource' => 120,
    'BlogPosts\BlogPostResource' => 130,
    'FeaturedBlogDestinations\FeaturedBlogDestinationResource' => 140,
];

foreach ($resources as $res => $sort) {
    $path = __DIR__ . '/app/Filament/Resources/' . $res . '.php';
    if (!file_exists($path)) {
        echo "Missing: $path\n";
        continue;
    }
    
    $content = file_get_contents($path);
    
    // Check if it already has navigationSort
    if (strpos($content, '$navigationSort') !== false) {
        $content = preg_replace('/protected static \?int \$navigationSort = \d+;/', 'protected static ?int $navigationSort = ' . $sort . ';', $content);
    } else {
        $content = preg_replace('/(.*\$navigationIcon = [^;]+;)/', "$1\n\n    protected static ?int \$navigationSort = $sort;", $content);
    }
    
    file_put_contents($path, $content);
    echo "Updated: $res\n";
}
