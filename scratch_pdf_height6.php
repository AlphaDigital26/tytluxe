<?php
require 'vendor/autoload.php';
$html = '<html><head><style>body{margin:0;padding:0;}</style></head><body><div style="height: 500px; background: red;">test</div><div style="height: 300px;">test2</div></body></html>';
$dompdf = new \Dompdf\Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper([0, 0, 500, 6000]);
$dompdf->render();

$tree = $dompdf->getTree();
$root = $tree->get_root();

function getMaxY($frame) {
    $maxY = 0;
    if ($frame) {
        $pos = $frame->get_position();
        if (isset($pos[1])) {
            $h = 0;
            if (method_exists($frame, 'get_margin_height')) {
                $h = $frame->get_margin_height();
            }
            $maxY = max($maxY, $pos[1] + $h);
        }
        foreach ($frame->get_children() as $child) {
            $maxY = max($maxY, getMaxY($child));
        }
    }
    return $maxY;
}

$contentHeight = getMaxY($root);
var_dump("Content Height calculated:", $contentHeight);
