<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

function estimateHeight($packageId) {
    $package = App\Models\Package::find($packageId);
    if (!$package) return;
    
    $daysCount = $package->itineraryDays->count();
    $incCount = $package->inclusions->count();
    $excCount = $package->exclusions->count();
    
    $textLength = strlen(strip_tags($package->about_destination ?? ''));
    $aboutHeight = max(80, ($textLength / 100) * 20); // min 80px
    
    $baseHeight = 1000; // Hero(440) + Meta(60) + Titles(100) + Pricing(140) + Contact(60) + Margins(200)
    
    // Each day: brief (~40px) + detailed (~180px)
    $daysHeight = $daysCount * 220;
    
    // Inclusions/exclusions (~25px per item)
    $incExcHeight = ($incCount + $excCount) * 25;
    
    $totalHeight = $baseHeight + $aboutHeight + $daysHeight + $incExcHeight;
    
    // Add a safety buffer of 15%
    $safeHeight = $totalHeight * 1.15;
    
    echo "Package $packageId ({$package->name}):\n";
    echo "  Days: $daysCount, Inc: $incCount, Exc: $excCount, AboutLen: $textLength\n";
    echo "  Est: $totalHeight pt, Safe: $safeHeight pt\n";
    
    return $safeHeight;
}

estimateHeight(1);
estimateHeight(2); // Assuming 2 is Manali
