<?php
require 'vendor/autoload.php';
$html = '<html><head><style>body{margin:0;padding:0;}</style></head><body><div style="height: 500px; background: red;">test</div><div style="height: 300px;">test2</div><div id="footer" style="height: 50px;">footer</div></body></html>';
$dompdf = new \Dompdf\Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper([0, 0, 500, 6000]);
$dompdf->render();

$tree = $dompdf->getTree();
$root = $tree->get_root();

$maxY = 0;
$allFrames = [];

function traverse($frame, $depth = 0) {
    global $maxY, $allFrames;
    if ($frame) {
        $pos = $frame->get_position();
        if (is_array($pos) && count($pos) >= 2) {
            $y = $pos[1];
            $h = 0;
            if (method_exists($frame, 'get_margin_height')) {
                $h = $frame->get_margin_height();
            }
            $bottom = $y + $h;
            if ($bottom > $maxY) {
                $maxY = $bottom;
            }
            $allFrames[] = str_repeat("  ", $depth) . get_class($frame) . " Y=$y H=$h Bottom=$bottom";
        }
        foreach ($frame->get_children() as $child) {
            traverse($child, $depth + 1);
        }
    }
}

traverse($root);
var_dump("Calculated Max Y:", $maxY);
file_put_contents('frames.log', implode("\n", $allFrames));
