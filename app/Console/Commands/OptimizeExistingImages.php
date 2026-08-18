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
    protected $signature = 'images:optimize-existing';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Optimize existing images in the database by converting them to WebP format';

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
        // Check if it's a URL or already a WebP
        if (Str::startsWith($path, ['http://', 'https://']) || Str::endsWith($path, '.webp')) {
            return $path;
        }

        // Check if the file exists on the disk
        if (!Storage::disk('public')->exists($path)) {
            return $path;
        }

        try {
            // Calculate directory from the current path
            $directory = dirname($path);
            if ($directory === '.') {
                $directory = 'optimized';
            }

            // Optimize and generate webp. The old file is left untouched on the disk.
            $newPath = $optimizer->optimizeAndSave($path, $context, $directory, 'public');

            return $newPath;
        } catch (\Exception $e) {
            $this->error("\nFailed to optimize {$path}: " . $e->getMessage());
            return $path;
        }
    }
}
