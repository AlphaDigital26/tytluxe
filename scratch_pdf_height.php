<?php
require 'vendor/autoload.php';
$html = '<html><head><style>body{margin:0;padding:0;}</style></head><body><div style="height: 500px; background: red;">test</div></body></html>';
$dompdf = new \Dompdf\Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper([0, 0, 500, 6000]);
$dompdf->render();

// Try to get content height
$tree = $dompdf->getTree();
$root = $tree->get_root();
$height = $root->get_padding_box()[3]; // y + h? actually get_padding_box returns [x, y, w, h]
var_dump("Padding box height:", $height);

// Try another way
var_dump("Canvas height:", $dompdf->getCanvas()->get_height());
