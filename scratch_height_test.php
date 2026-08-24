<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$package = App\Models\Package::find(1);

$pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.sample-itinerary', compact('package'))
    ->setPaper([0, 0, 595.28, 10000]);

$pdf->render();

$dompdf = $pdf->getDomPDF();
$callbacks = $dompdf->getCallbacks();
// Actually, let's use the root frame
$tree = $dompdf->getTree();
$root = $tree->get_root();

function getMaxY($frame) {
    $maxY = 0;
    if ($frame) {
        $box = $frame->get_padding_box();
        if ($box) {
            $y = $box[1] + $box[3]; // y + h
            if ($y > $maxY) $maxY = $y;
        }
        foreach ($frame->get_children() as $child) {
            $childMax = getMaxY($child);
            if ($childMax > $maxY) $maxY = $childMax;
        }
    }
    return $maxY;
}

echo "Max Y: " . getMaxY($root) . "\n";
