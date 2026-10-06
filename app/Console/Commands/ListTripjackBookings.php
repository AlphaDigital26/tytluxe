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
        {--start= : Range start in IST (Y-m-d or Y-m-d\TH:i:s), defaults to 6 days ago; max 15 days back}
        {--end= : Range end in IST (Y-m-d or Y-m-d\TH:i:s), defaults to now; max 7 days after start}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List TripJack hotel bookings created within a date range (oms/v3/hotel/bookings)';

    public function handle(TripJackClient $client): int
    {
        // Dates are IST, as TripJack expects.
        $start = $this->option('start') ?: now('Asia/Kolkata')->subDays(6)->startOfDay();
        $end = $this->option('end') ?: now('Asia/Kolkata');

        try {
            [$start, $end] = TripJackClient::bookingListRange($start, $end);
            $response = $client->bookingList($start, $end);
        } catch (\InvalidArgumentException $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
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
        $this->info(count($bookings)." booking(s) between {$start} and {$end} IST.");

        return self::SUCCESS;
    }
}
