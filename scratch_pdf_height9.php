<?php
require 'vendor/autoload.php';
$html = '<html><head><style>body{margin:0;padding:0;}</style></head><body><div style="height: 500px; background: red;">test</div><div style="height: 300px;">test2</div><div id="pdf-footer" style="height: 50px;">footer</div></body></html>';
$dompdf = new \Dompdf\Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper([0, 0, 500, 6000]);
$dompdf->render();

$canvas = $dompdf->getCanvas();
// CPDF adapter might have the max Y? Let's check objects
$cpdf = $canvas->get_cpdf();
if ($cpdf) {
    // try to see if there's any property indicating max y
    $rc = new ReflectionClass($cpdf);
    $props = $rc->getProperties();
    foreach($props as $p) {
        $p->setAccessible(true);
        if (in_array($p->getName(), ['y', 'currenty', 'maxy'])) {
             echo $p->getName() . " = " . $p->getValue($cpdf) . "\n";
        }
    }
}
