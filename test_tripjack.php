<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$service = new \App\Services\TripjackService();
$response = $service->searchHotels(date('Y-m-d', strtotime('+7 days')), date('Y-m-d', strtotime('+10 days')), '26713');

file_put_contents(__DIR__ . '/tripjack_response.json', json_encode($response, JSON_PRETTY_PRINT));
echo "Done";
