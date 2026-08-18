<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\Drivers\Imagick\Driver as ImagickDriver;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class ImageOptimizer
{
    /**
     * Process, optimize and save an uploaded image file.
     *
     * @param UploadedFile|TemporaryUploadedFile|string $file
     * @param string $type The context of the image ('hero' or 'thumbnail')
     * @param string $directory The storage directory
     * @param string $disk The storage disk (default: 'public')
     * @return string The path to the saved WebP image
     */
    public function optimizeAndSave($file, string $type = 'hero', string $directory = 'uploads', string $disk = 'public'): string
    {
        // 1. Initialize Intervention Image Manager
        // Auto-detect Imagick if available, otherwise fallback to standard GD
        $driver = extension_loaded('imagick') ? new ImagickDriver() : new GdDriver();
        $manager = new ImageManager($driver);

        // 2. Read the image
        $imagePath = is_string($file) ? Storage::disk($disk)->path($file) : $file->getRealPath();
        $image = $manager->read($imagePath);

        // 3. Metadata (EXIF) is typically stripped automatically on WebP conversion

        // 4. Resize based on type
        // Use scaleDown to prevent upscaling small images, maintaining aspect ratio
        $maxWidth = ($type === 'thumbnail') ? 800 : 1920;
        $image->scaleDown(width: $maxWidth);

        // 5. Convert to WebP format
        $encodedImage = $image->toWebp(quality: 80);

        // 6. Generate a unique filename and path
        // Ensure the directory structure exists
        $filename = Str::uuid() . '.webp';
        $path = trim($directory, '/') . '/' . $filename;

        // 7. Save to disk
        Storage::disk($disk)->put($path, (string) $encodedImage);

        return $path;
    }
}
