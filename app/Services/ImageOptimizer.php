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
        try {
            // 1. Initialize Intervention Image Manager
            $driver = extension_loaded('imagick') ? new ImagickDriver() : new GdDriver();
            $manager = new ImageManager($driver);

            // 2. Read the image
            if (is_string($file)) {
                $imagePath = file_exists($file) ? $file : Storage::disk($disk)->path($file);
            } else {
                $imagePath = $file->getRealPath();
            }
            $image = $manager->read($imagePath);

            // 3. Auto-orient based on EXIF (crucial for phone camera uploads)
            $image->orient();

            // 4. Intelligently process and resize based on context
            if ($type === 'hero') {
                $this->processHeroImage($image);
                $quality = 85;
            } else {
                $this->processThumbnailImage($image);
                $quality = 82;
            }

            // 5. Convert to modern WebP format
            $encodedImage = $image->toWebp(quality: $quality);

            // 6. Generate unique filename and path
            $filename = Str::uuid() . '.webp';
            $path = trim($directory, '/') . '/' . $filename;

            // 7. Save to disk
            Storage::disk($disk)->put($path, (string) $encodedImage);

            return $path;
        } catch (\Throwable $e) {
            report($e);

            // Graceful fallback for non-tech uploads (e.g. SVG or unreadable raw files)
            if ($file instanceof UploadedFile || $file instanceof TemporaryUploadedFile) {
                return $file->store($directory, $disk);
            }

            return is_string($file) ? $file : '';
        }
    }

    /**
     * Automatically handles hero banner images regardless of what the user uploads:
     * - Upscales small images (like 600x600) with bicubic interpolation so they don't pixelate on 1080p/4K screens
     * - Scales down massive camera/phone photos (3000px+) to save bandwidth
     * - Applies subtle sharpening to preserve crisp texture and eliminate upscaling fuzziness
     */
    protected function processHeroImage($image): void
    {
        $targetWidth = 1920;
        $targetHeight = 1080;

        $width = $image->width();
        $height = $image->height();

        if ($width <= 0 || $height <= 0) {
            return;
        }

        $aspectRatio = $width / $height;
        $targetRatio = $targetWidth / $targetHeight; // ~1.778

        $wasUpscaled = false;

        // If the uploaded image is smaller than standard full-HD coverage
        if ($width < $targetWidth || $height < $targetHeight) {
            if ($aspectRatio >= $targetRatio) {
                // Wide landscape: ensure height is at least 1080
                if ($height < $targetHeight) {
                    $image->scale(height: $targetHeight);
                    $wasUpscaled = true;
                }
            } else {
                // Square or portrait: ensure width is at least 1920
                if ($width < $targetWidth) {
                    $image->scale(width: $targetWidth);
                    $wasUpscaled = true;
                }
            }
        } else {
            // Image is large: scale down gracefully without going below 1920x1080
            if ($aspectRatio >= $targetRatio) {
                if ($image->height() > 1440) {
                    $image->scaleDown(height: 1440);
                }
                if ($image->width() > 2560) {
                    $image->scaleDown(width: 2560);
                }
            } else {
                if ($image->width() > $targetWidth) {
                    $image->scaleDown(width: $targetWidth);
                }
            }
        }

        // If the image was upscaled, apply sharpening to enhance edge contrast and eliminate blur
        if ($wasUpscaled) {
            $image->sharpen(12);
        }
    }

    /**
     * Process gallery/card thumbnail images
     */
    protected function processThumbnailImage($image): void
    {
        $maxWidth = 800;
        if ($image->width() > $maxWidth) {
            $image->scaleDown(width: $maxWidth);
        } elseif ($image->width() < 400) {
            $image->scale(width: 600);
            $image->sharpen(8);
        }
    }
}
