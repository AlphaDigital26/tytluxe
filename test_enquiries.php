<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Total Enquiries: " . \App\Models\Enquiry::count() . "\n";
echo "Verticals: " . json_encode(\App\Models\Enquiry::pluck('vertical')->unique()) . "\n";
