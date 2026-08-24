<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$package = \App\Models\Package::first();
$pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.sample-itinerary', compact('package'))
    ->setPaper('a4', 'portrait');

$pdf->save(public_path('test_a4_itinerary.pdf'));
echo "PDF Generated!\n";
