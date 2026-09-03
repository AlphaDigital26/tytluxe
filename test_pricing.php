<?php
require __DIR__."/vendor/autoload.php";
$app = require_once __DIR__."/bootstrap/app.php";
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$dest = App\Models\Destination::where("name", "like", "%Dubai%")->first();
if (!$dest) die("no dest\n");
echo "Dest ID: " . $dest->id . "\n";

$hids = App\Models\Hotel::where("destination_id", $dest->id)
    ->where("source", "tripjack")
    ->where("is_active", true)
    ->whereNotNull("tripjack_hotel_id")
    ->count();
echo "Hotels to price: " . $hids . "\n";

$search = app(App\Services\TripJack\TripJackListingSearch::class);
try {
    $res = $search->search($dest, "2026-09-07", "2026-09-10", [["adults"=>2]]);
    echo "Priced options: " . count($res["options"]) . "\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

