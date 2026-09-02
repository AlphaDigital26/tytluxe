<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Package;
use App\Http\Controllers\FrontendController;

class WarmItineraryPdfs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'packages:warm-pdf-cache {--force : Force regenerate existing cached PDFs}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pre-generate and cache all package itinerary PDFs for instantaneous downloads';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $packages = Package::with([
            'destination', 'images', 'inclusions', 'exclusions', 'itineraryDays', 'departures'
        ])->where('is_active', true)->get();

        $this->info("Found {$packages->count()} active packages to cache.");

        $controller = new FrontendController();
        $force = (bool) $this->option('force');

        foreach ($packages as $index => $package) {
            $num = $index + 1;
            $this->output->write("[{$num}/{$packages->count()}] Caching PDF for: {$package->title}... ");

            try {
                $pdf = $controller->getPdfForPackage($package, $force);
                $sizeKb = round(strlen($pdf) / 1024, 1);
                $this->info("DONE ({$sizeKb} KB)");
            } catch (\Throwable $e) {
                $this->error("FAILED: " . $e->getMessage());
            }
        }

        $this->info("All package PDFs warmed and cached successfully!");
        return Command::SUCCESS;
    }
}
