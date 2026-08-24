<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$package = App\Models\Package::find(1);
$html = view('pdf.sample-itinerary', compact('package'))->render();

$dompdf = new \Dompdf\Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper([0, 0, 595.28, 10000]);
$dompdf->render();

$tree = $dompdf->getTree();
$root = $tree->get_root();

function findMaxY($frame) {
    if (!$frame) return 0;
    
    $maxY = 0;
    foreach ($frame->get_children() as $child) {
        $box = $child->get_margin_box(); // Use margin box
        if ($box) {
            $y = $box['y'] + $box['h']; // use associative keys?
            if ($y > $maxY) $maxY = $y;
        }
        
        $childMax = findMaxY($child);
        if ($childMax > $maxY) $maxY = $childMax;
    }
    return $maxY;
}

$maxY = findMaxY($root);
echo "Max Y is: " . $maxY . "\n";
