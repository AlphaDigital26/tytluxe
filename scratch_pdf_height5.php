<?php
require 'vendor/autoload.php';
$html = '<html><head><style>body{margin:0;padding:0;}</style></head><body><div style="height: 500px; background: red;">test</div><div style="height: 300px;">test2</div>
<script type="text/php">
    file_put_contents("pdf_y.txt", $pdf->get_y());
</script>
</body></html>';

$options = new \Dompdf\Options();
$options->set('isPhpEnabled', true);
$dompdf = new \Dompdf\Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper([0, 0, 500, 6000]);
$dompdf->render();

var_dump(file_get_contents("pdf_y.txt"));
