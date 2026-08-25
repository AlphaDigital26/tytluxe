<?php
require 'vendor/autoload.php';
$html = '<html><head><style>body{margin:0;padding:0;}</style></head><body><div style="height: 500px; background: red;">test</div><div style="height: 300px;">test2</div></body></html>';

$browsershot = \Spatie\Browsershot\Browsershot::html($html);
// evaluate javascript to get document height
$height = $browsershot->evaluate('document.documentElement.scrollHeight');
var_dump("Browsershot height:", $height);
