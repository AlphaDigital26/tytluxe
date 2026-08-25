<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$package = App\Models\Package::find(1);

// We inject a script at the very end of the HTML
$html = view('pdf.sample-itinerary', compact('package'))->render();
$html .= '<script type="text/php">file_put_contents(public_path("pdf_height.txt"), $pdf->get_y());</script>';

// Enable PHP execution
$options = new \Dompdf\Options();
$options->set('isPhpEnabled', true);
$dompdf = new \Dompdf\Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper([0, 0, 595.28, 10000]);
$dompdf->render();

echo "Height written to pdf_height.txt\n";
echo "Content: " . file_get_contents(public_path("pdf_height.txt")) . "\n";
