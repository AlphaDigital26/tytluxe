<?php
require 'vendor/autoload.php';
$html = '<html><head><style>body{margin:0;padding:0;}</style></head><body><div style="height: 500px; background: red;">test</div><div style="height: 300px;">test2</div></body></html>';
$dompdf = new \Dompdf\Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper([0, 0, 500, 6000]);
$dompdf->render();
$cpdf = $dompdf->getCanvas()->get_cpdf();
$rc = new ReflectionClass($cpdf);
$props = $rc->getProperties();
$output = [];
foreach($props as $p) {
    $output[] = $p->getName();
}
file_put_contents('cpdf_props.txt', implode(", ", $output));
