<?php
require 'vendor/autoload.php';
$html = '<html><head><style>body{margin:0;padding:0;}</style></head><body><div style="height: 500px; background: red;">test</div><div style="height: 300px;">test2</div></body></html>';
$dompdf = new \Dompdf\Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper([0, 0, 500, 6000]);
$dompdf->render();

$tree = $dompdf->getTree();
$root = $tree->get_root();

function printFrames($frame, $depth = 0) {
    if (method_exists($frame, 'get_padding_box')) {
        $box = $frame->get_padding_box();
        echo str_repeat("  ", $depth) . get_class($frame) . " Y=" . $box[1] . " H=" . $box[3] . "\n";
    }
    foreach ($frame->get_children() as $child) {
        printFrames($child, $depth + 1);
    }
}

printFrames($root);
