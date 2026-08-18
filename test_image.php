<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use App\Services\ImageOptimizer;

$imgPath = __DIR__.'/storage/app/public/test.jpg';
if (!is_dir(dirname($imgPath))) {
    mkdir(dirname($imgPath), 0777, true);
}
// create a dummy image
$img = imagecreatetruecolor(3000, 2000);
$red = imagecolorallocate($img, 255, 0, 0);
imagefill($img, 0, 0, $red);
imagejpeg($img, $imgPath);

// Create an UploadedFile instance
$file = new UploadedFile($imgPath, 'test.jpg', 'image/jpeg', null, true);

$optimizer = app(ImageOptimizer::class);
try {
    $path = $optimizer->optimizeAndSave($file, 'hero', 'test');
    echo "Result Path: $path\n";
    // Check if webp was created
    if (file_exists(__DIR__ . '/storage/app/public/' . $path)) {
        echo "File size: " . filesize(__DIR__ . '/storage/app/public/' . $path) . " bytes\n";
        echo "Success!\n";
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
