<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\LaravelPdf\Facades\Pdf;

class GenerateSamplePdf extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:sample-pdf';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a sample itinerary PDF';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $path = public_path('premium_itinerary.pdf');
        
        Pdf::view('pdf.premium-itinerary')
            ->format('a4')
            ->margins(0, 0, 0, 0)
            ->save($path);
        
        $this->info("Premium PDF generated at: {$path}");
    }
}
