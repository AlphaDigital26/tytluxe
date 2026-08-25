<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$package = \App\Models\Package::first();
$daysCount = $package->itineraryDays->count();
$incCount = $package->inclusions->count();
$excCount = $package->exclusions->count();
$textLength = strlen(strip_tags($package->description ?? ''));
$aboutHeight = max(80, ($textLength / 100) * 20);
$baseHeight = 1050;
$daysHeight = $daysCount * 230;
$incExcHeight = ($incCount + $excCount) * 26;
$calculatedHeight = ($baseHeight + $aboutHeight + $daysHeight + $incExcHeight) * 1.25;

$pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.sample-itinerary', compact('package'))
    ->setPaper([0, 0, 595.28, $calculatedHeight]);

$pdf->save(public_path('test_a4_itinerary.pdf'));
echo "PDF Generated!\n";
