<?php
require 'vendor/autoload.php';
$html = '<html><head><style>body{margin:0;padding:0;}</style></head><body><div style="height: 500px; background: red;">test</div><div style="height: 300px;">test2</div></body></html>';

$dompdf = new \Dompdf\Dompdf();
$dompdf->loadHtml($html);
// Render on tiny pages
$dompdf->setPaper([0, 0, 500, 100]);
$dompdf->render();
$pages = $dompdf->getCanvas()->get_page_count();
var_dump("Pages:", $pages, "Est height:", $pages * 100);
