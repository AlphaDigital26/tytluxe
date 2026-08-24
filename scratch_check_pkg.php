<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Fix transport_info that was mistakenly set to meals text
$p = App\Models\Package::find(1);
echo "Before transport_info: " . $p->transport_info . "\n";
$p->transport_info = 'Volvo / TT';
$p->save();
echo "After transport_info: " . $p->transport_info . "\n";
echo "Done!\n";
