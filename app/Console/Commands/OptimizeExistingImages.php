<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ImageOptimizer;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class OptimizeExistingImages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'images:optimize-existing {--force : Re-optimize even if already WebP (e.g. to upscale low-res hero banners)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Optimize existing images in the database by converting to WebP and upscaling low-res hero banners';

    /**
     * Execute the console command.
     */
    public function handle(ImageOptimizer $optimizer)
    {
        $this->info('Starting bulk image optimization...');

        $configs = [
            [
                'model' => \App\Models\Package::class,
                'columns' => [
                    ['name' => 'hero_bg_image', 'type' => 'string', 'context' => 'hero']
                ]
            ],
            [
                'model' => \App\Models\PackageImage::class,
                'columns' => [
                    ['name' => 'path', 'type' => 'string', 'context' => 'thumbnail']
                ]
            ],
            [
                'model' => \App\Models\HotelImage::class,
                'columns' => [
                    ['name' => 'path', 'type' => 'string', 'context' => 'thumbnail']
                ]
            ],
            [
                'model' => \App\Models\CruiseCabinType::class,
                'columns' => [
                    ['name' => 'image_path', 'type' => 'string', 'context' => 'thumbnail']
                ]
            ],
            [
                'model' => \App\Models\CruiseImage::class,
                'columns' => [
                    ['name' => 'path', 'type' => 'string', 'context' => 'hero']
                ]
            ],
            [
                'model' => \App\Models\RoomType::class,
                'columns' => [
                    ['name' => 'image_path', 'type' => 'string', 'context' => 'thumbnail'],
                    ['name' => 'images', 'type' => 'json', 'context' => 'thumbnail']
                ]
            ],
            [
                'model' => \App\Models\Review::class,
                'columns' => [
                    ['name' => 'avatar_path', 'type' => 'string', 'context' => 'thumbnail'],
                    ['name' => 'images', 'type' => 'json', 'context' => 'thumbnail']
                ]
            ],
        ];

        foreach ($configs as $config) {
            $modelClass = $config['model'];
            $columns = $config['columns'];

            $this->info("Processing {$modelClass}...");
            $records = $modelClass::all();
            
            $bar = $this->output->createProgressBar(count($records));
            $bar->start();

            foreach ($records as $record) {
                $needsSave = false;
                
                foreach ($columns as $column) {
                    $colName = $column['name'];
                    $colType = $column['type'];
                    $context = $column['context'];

                    $value = $record->{$colName};

                    if (empty($value)) {
                        continue;
                    }

                    if ($colType === 'string') {
                        $newPath = $this->processPath($value, $context, $optimizer);
                        if ($newPath !== $value) {
                            $record->{$colName} = $newPath;
                            $needsSave = true;
                        }
                    } elseif ($colType === 'json') {
                        $paths = is_string($value) ? json_decode($value, true) : $value;
                        if (is_array($paths)) {
                            $newPaths = [];
                            $pathsChanged = false;
                            
                            foreach ($paths as $path) {
                                $newPath = $this->processPath($path, $context, $optimizer);
                                $newPaths[] = $newPath;
                                if ($newPath !== $path) {
                                    $pathsChanged = true;
                                }
                            }

                            if ($pathsChanged) {
                                $record->{$colName} = $newPaths;
                                $needsSave = true;
                            }
                        }
                    }
                }

                if ($needsSave) {
                    $record->save();
                }

                $bar->advance();
            }
            
            $bar->finish();
            $this->newLine();
        }

        $this->info('Bulk image optimization completed successfully.');
    }

    private function processPath(string $path, string $context, ImageOptimizer $optimizer): string
    {
        // Check if it's an external URL
        if (Str::startsWith($path, ['http://', 'https://'])) {
            return $path;
        }

        // Check if the file exists on the disk
        if (!Storage::disk('public')->exists($path)) {
            return $path;
        }

        $isWebp = Str::endsWith($path, '.webp');
        $force = $this->option('force');

        if ($isWebp && !$force) {
            // If it's already WebP, check if it's a hero image that was saved at low resolution (< 1600px wide or < 900px high)
            if ($context === 'hero') {
                $fullPath = Storage::disk('public')->path($path);
                $sz = @getimagesize($fullPath);
                if ($sz && ($sz[0] < 1600 || $sz[1] < 900)) {
                    // Re-optimize low-res hero banner to upscale and sharpen it!
                } else {
                    return $path;
                }
            } else {
                return $path;
            }
        }

        try {
            // Calculate directory from the current path
            $directory = dirname($path);
            if ($directory === '.') {
                $directory = 'optimized';
            }

            // Optimize and generate webp.
            $newPath = $optimizer->optimizeAndSave($path, $context, $directory, 'public');

            // If an existing WebP was re-optimized and a new filename was generated, remove the old low-res WebP
            if ($isWebp && $newPath !== $path && Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }

            return $newPath;
        } catch (\Exception $e) {
            $this->error("\nFailed to optimize {$path}: " . $e->getMessage());
            return $path;
        }
    }
}
