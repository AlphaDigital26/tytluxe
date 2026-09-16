<?php

namespace App\Console\Commands;

use App\Services\TripJack\Exceptions\TripJackException;
use App\Services\TripJack\TripJackClient;
use Illuminate\Console\Command;

class ListTripjackBookings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tripjack:bookings
        {--start= : Range start (Y-m-d or Y-m-d\TH:i:s), defaults to 7 days ago}
        {--end= : Range end (Y-m-d or Y-m-d\TH:i:s), defaults to now}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List TripJack hotel bookings created within a date range (oms/v1/hotel/bookings)';

    public function handle(TripJackClient $client): int
    {
        $start = $this->option('start')
            ? \Carbon\Carbon::parse($this->option('start'))
            : now()->subDays(7)->startOfDay();
        $end = $this->option('end')
            ? \Carbon\Carbon::parse($this->option('end'))
            : now();

        try {
            $response = $client->bookingList($start->format('Y-m-d\TH:i:s'), $end->format('Y-m-d\TH:i:s'));
        } catch (TripJackException $e) {
            $this->error("TripJack bookings list failed: {$e->getMessage()}");

            return self::FAILURE;
        }

        $bookings = $response['bookings'] ?? [];

        if (empty($bookings)) {
            $this->info("No TripJack bookings found between {$start} and {$end}.");

            return self::SUCCESS;
        }

        $rows = collect($bookings)->map(fn ($b) => [
            $b['bookingId'] ?? '—',
            $b['status'] ?? '—',
            isset($b['totalPrice']) ? number_format((float) $b['totalPrice'], 2) : '—',
            collect($b['options'][0]['rooms'] ?? [])->pluck('checkInDate')->first() ?? '—',
            collect($b['options'][0]['rooms'] ?? [])->pluck('checkOutDate')->first() ?? '—',
        ])->all();

        $this->table(['Booking ID', 'Status', 'Total Price', 'Check-in', 'Check-out'], $rows);
        $this->info(count($bookings).' booking(s) between '.$start->toDateString().' and '.$end->toDateString().'.');

        return self::SUCCESS;
    }
}
