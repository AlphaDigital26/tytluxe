<?php

namespace App\Console\Commands;

use App\Http\Controllers\FrontendController;
use App\Models\Booking;
use App\Services\Payment\RazorpayService;
use App\Services\TripJack\TripJackClient;
use Illuminate\Console\Command;

/**
 * Background Booking Details polling for hotel bookings, so a booking's
 * status no longer depends on the guest revisiting the confirmation page:
 *
 *  - default: bookings still pending_confirmation (TripJack PENDING /
 *    IN_PROGRESS) and freshly requested cancellations — every few minutes.
 *  - --cancellations: confirmed bookings with a cancellation still pending,
 *    plus upcoming confirmed stays (catches a hotel-side cancellation) —
 *    TripJack's docs say once a day.
 */
class RefreshTripjackHotelBookings extends Command
{
    protected $signature = 'tripjack:refresh-hotel-bookings
        {--cancellations : Daily check of pending cancellations and upcoming confirmed stays}';

    protected $description = 'Poll TripJack Booking Details for hotel bookings that have not settled yet';

    public function handle(TripJackClient $client, RazorpayService $razorpay): int
    {
        $query = Booking::query()
            ->where('vertical', 'hotel')
            ->whereNotNull('tripjack_booking_id');

        if ($this->option('cancellations')) {
            $query->where('status', 'confirmed')
                ->where(fn ($q) => $q->whereNotNull('cancellation_requested_at')
                    ->orWhereDate('check_in', '>=', now()->toDateString()));
        } else {
            // Unconfirmed bookings (up to 3 days — beyond that a human
            // needs to look) and cancellations requested in the last day;
            // older pending cancellations fall to the daily run.
            $query->where(fn ($q) => $q
                ->where(fn ($q) => $q->where('status', 'pending_confirmation')->where('created_at', '>=', now()->subDays(3)))
                ->orWhere(fn ($q) => $q->where('status', 'confirmed')->where('cancellation_requested_at', '>=', now()->subDay())));
        }

        $controller = app(FrontendController::class);
        $count = 0;

        $query->orderBy('id')->each(function (Booking $booking) use ($controller, $client, $razorpay, &$count) {
            $before = $booking->status;
            $live = $controller->refreshHotelBookingStatus($booking, $client, $razorpay);
            $count++;
            $this->line("{$booking->reference}: TripJack ".($live ?? 'unreadable').", {$before} → {$booking->status}");
        });

        $this->info("Checked {$count} booking(s).");

        return self::SUCCESS;
    }
}
