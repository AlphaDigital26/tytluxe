<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\PackageDeparture;

$packageId = 8;
$year = 2025;

$dates = [
    // July
    ['04 Jul', '08 Jul'],
    ['11 Jul', '15 Jul'],
    ['18 Jul', '22 Jul'],
    ['25 Jul', '29 Jul'],
    // August
    ['01 Aug', '05 Aug'],
    ['08 Aug', '12 Aug'],
    ['14 Aug', '18 Aug'],
    ['15 Aug', '19 Aug'],
    ['22 Aug', '26 Aug'],
    ['29 Aug', '02 Sep'],
    // September
    ['05 Sep', '09 Sep'],
    ['12 Sep', '16 Sep'],
    ['19 Sep', '23 Sep'],
    ['26 Sep', '30 Sep'],
    // October
    ['03 Oct', '07 Oct'],
    ['10 Oct', '14 Oct'],
    ['17 Oct', '21 Oct'],
    ['24 Oct', '28 Oct'],
    ['31 Oct', '04 Nov'],
    // November
    ['07 Nov', '11 Nov'],
    ['14 Nov', '18 Nov'],
    ['21 Nov', '25 Nov'],
    ['28 Nov', '02 Dec'],
    // December
    ['05 Dec', '09 Dec'], // Fixed from PDF typo "05 Nov" under December
    ['12 Dec', '16 Dec'],
    ['19 Dec', '23 Dec'],
    ['25 Dec', '29 Dec'],
    ['26 Dec', '30 Dec'],
];

// Clear existing to avoid duplicates
PackageDeparture::where('package_id', $packageId)->delete();

foreach ($dates as $range) {
    $startDateStr = $range[0] . " $year";
    
    // Check if end date crosses into next year (e.g., 28 Nov - 02 Dec is fine, but Dec to Jan needs next year)
    $endYear = $year;
    if (str_contains($range[1], 'Jan') && str_contains($range[0], 'Dec')) {
        $endYear = $year + 1;
    }
    $endDateStr = $range[1] . " $endYear";

    PackageDeparture::create([
        'package_id' => $packageId,
        'start_date' => \Carbon\Carbon::parse($startDateStr)->format('Y-m-d'),
        'end_date'   => \Carbon\Carbon::parse($endDateStr)->format('Y-m-d'),
    ]);
}

echo "Added " . count($dates) . " dates for package ID $packageId\n";
