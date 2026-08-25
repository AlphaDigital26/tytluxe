<?php
require 'vendor/autoload.php';
$html = '<html><head><style>body{margin:0;padding:0;}</style></head><body><div style="height: 500px; background: red;">test</div><div style="height: 300px;">test2</div><div id="pdf-end-marker"></div></body></html>';
$dompdf = new \Dompdf\Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper([0, 0, 500, 6000]);
$dompdf->render();

$tree = $dompdf->getTree();
$marker = $tree->get_frame('pdf-end-marker');
if ($marker) {
    $box = $marker->get_padding_box();
    var_dump("Marker Y:", $box[1]);
} else {
    var_dump("Marker not found.");
}
