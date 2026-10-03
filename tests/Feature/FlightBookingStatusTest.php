<?php

namespace Tests\Feature;

use App\Jobs\PollFlightBookingStatusJob;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\User;
use App\Services\FlightBookingService;
use App\Services\Payment\RazorpayService;
use App\Services\TripJack\TripJackFlightClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Tests\Doubles\FakeRazorpayService;
use Tests\TestCase;

/**
 * Book → Booking Details after the doc's 5-second wait, the itemInfos.AIR
 * path for PNR/tickets, and never resending or blind-refunding a Book whose
 * outcome is unknown (timeout / 5xx).
 */
class FlightBookingStatusTest extends TestCase
{
    use RefreshDatabase;

    protected FakeRazorpayService $razorpay;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->razorpay = new FakeRazorpayService();
        $this->app->instance(RazorpayService::class, $this->razorpay);
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
        config(['services.tripjack.retry_times' => 3]);
    }

    protected function pendingBooking(array $extra = []): Booking
    {
        return Booking::forceCreate(array_merge([
            'reference' => 'TYT'.strtoupper(substr(md5(uniqid()), 0, 8)),
            'user_id' => $this->user->id,
            'vertical' => 'flight',
            'status' => 'pending_payment',
            'guest_email' => 'guest@example.com',
            'guest_phone' => '9876543210',
            'tripjack_hold_id' => 'TJS100000000001',
            'flight_route' => 'DEL-BOM',
            'flight_departure_date' => now()->addDays(20)->toDateString(),
            'pax_adults' => 1,
            'pax_children' => 0,
            'pax_infants' => 0,
            'lead_guest_name' => 'Rahul Probe',
            'base_amount' => 5000,
            'total_amount' => 5600,
            'tripjack_total_price' => 5000,
            'flight_segments_payload' => ['travellerInfo' => [['ti' => 'Mr', 'pt' => 'ADULT', 'fN' => 'Rahul', 'lN' => 'Probe']]],
        ], $extra));
    }

    protected function paymentFor(Booking $booking): Payment
    {
        return Payment::create([
            'booking_id' => $booking->id, 'razorpay_order_id' => 'order_1', 'razorpay_payment_id' => 'pay_1',
            'amount' => $booking->total_amount, 'currency' => 'INR', 'status' => 'created',
        ]);
    }

    protected function details(string $status): array
    {
        return [
            'order' => ['bookingId' => 'TJS100000000001', 'status' => $status],
            'itemInfos' => ['AIR' => ['travellerInfos' => [[
                'fN' => 'Rahul', 'lN' => 'Probe',
                'pnrDetails' => ['DEL-BOM' => 'ABC123'],
                'ticketNumberDetails' => ['DEL-BOM' => '0981234567890'],
            ]]]],
            'status' => ['success' => true, 'httpStatus' => 200],
        ];
    }

    public function test_instant_book_waits_five_seconds_then_job_saves_pnr_from_item_infos(): void
    {
        Queue::fake();
        Http::fake(['*/air/book' => Http::response(['bookingId' => 'TJS100000000001', 'status' => ['success' => true, 'httpStatus' => 200]])]);
        $booking = $this->pendingBooking();

        app(FlightBookingService::class)->confirmAfterPayment($this->paymentFor($booking), $this->razorpay);

        $booking->refresh();
        $this->assertSame('confirmed', $booking->status);
        $this->assertSame('TJS100000000001', $booking->tripjack_booking_id);
        Http::assertNotSent(fn (Request $r) => str_contains($r->url(), 'booking-details'));
        Queue::assertPushed(PollFlightBookingStatusJob::class, fn ($job) => $job->delay !== null);

        Http::fake(['*/booking-details' => Http::response($this->details('SUCCESS'))]);
        (new PollFlightBookingStatusJob($booking->id))->handle(app(TripJackFlightClient::class), app(FlightBookingService::class), $this->razorpay);

        $booking->refresh();
        $this->assertSame(['DEL-BOM' => 'ABC123'], $booking->tripjack_flight_pnr);
        $this->assertSame(['DEL-BOM' => '0981234567890'], $booking->tripjack_flight_ticket_numbers);
    }

    public function test_job_keeps_polling_while_pending_and_refunds_when_failed(): void
    {
        Queue::fake();
        $booking = $this->pendingBooking(['status' => 'confirmed', 'tripjack_booking_id' => 'TJS100000000001']);
        $this->paymentFor($booking)->update(['status' => 'captured']);

        Http::fake(['*/booking-details' => Http::sequence()
            ->push($this->details('PENDING'))
            ->push($this->details('FAILED'))]);

        (new PollFlightBookingStatusJob($booking->id))->handle(app(TripJackFlightClient::class), app(FlightBookingService::class), $this->razorpay);
        $this->assertSame('confirmed', $booking->fresh()->status);
        Queue::assertPushed(PollFlightBookingStatusJob::class);

        (new PollFlightBookingStatusJob($booking->id, 2))->handle(app(TripJackFlightClient::class), app(FlightBookingService::class), $this->razorpay);
        $this->assertSame('refunded', $booking->fresh()->status);
        $this->assertCount(1, $this->razorpay->refunds);
    }

    public function test_book_timeout_is_sent_once_and_not_refunded_until_tripjack_answers(): void
    {
        Queue::fake();
        Http::fake(['*/air/book' => Http::failedConnection()]);
        $booking = $this->pendingBooking();
        $payment = $this->paymentFor($booking);

        app(FlightBookingService::class)->confirmAfterPayment($payment, $this->razorpay);

        Http::assertSentCount(1); // no automatic resend of Book
        $booking->refresh();
        $this->assertSame('failed_needs_review', $booking->status);
        $this->assertSame('TJS100000000001', $booking->tripjack_booking_id);
        $this->assertSame('captured', $payment->fresh()->status);
        $this->assertCount(0, $this->razorpay->refunds);
        Queue::assertPushed(PollFlightBookingStatusJob::class);

        // TripJack did ticket it — the booking is confirmed, not refunded.
        Http::fake(['*/booking-details' => Http::response($this->details('SUCCESS'))]);
        (new PollFlightBookingStatusJob($booking->id, 1, verifyUnconfirmed: true))->handle(app(TripJackFlightClient::class), app(FlightBookingService::class), $this->razorpay);

        $booking->refresh();
        $this->assertSame('confirmed', $booking->status);
        $this->assertSame(['DEL-BOM' => 'ABC123'], $booking->tripjack_flight_pnr);
        $this->assertCount(0, $this->razorpay->refunds);
    }

    public function test_timed_out_book_that_tripjack_never_received_is_refunded_after_fast_polls(): void
    {
        Queue::fake();
        $booking = $this->pendingBooking(['status' => 'failed_needs_review', 'tripjack_booking_id' => 'TJS100000000001']);
        $this->paymentFor($booking)->update(['status' => 'captured']);
        Http::fake(['*/booking-details' => Http::response(['status' => ['success' => false, 'httpStatus' => 400], 'errors' => [['errCode' => '1057', 'message' => 'Booking not found']]], 400)]);

        // Early "not found" isn't trusted yet...
        (new PollFlightBookingStatusJob($booking->id, 1, verifyUnconfirmed: true))->handle(app(TripJackFlightClient::class), app(FlightBookingService::class), $this->razorpay);
        $this->assertSame('failed_needs_review', $booking->fresh()->status);
        $this->assertCount(0, $this->razorpay->refunds);

        // ...but still not found after the fast polls means it was never booked.
        (new PollFlightBookingStatusJob($booking->id, 12, verifyUnconfirmed: true))->handle(app(TripJackFlightClient::class), app(FlightBookingService::class), $this->razorpay);
        $this->assertSame('refunded', $booking->fresh()->status);
        $this->assertCount(1, $this->razorpay->refunds);
    }

    public function test_definite_book_rejection_is_still_refunded_immediately(): void
    {
        Queue::fake();
        Http::fake(['*/air/book' => Http::response(['status' => ['success' => false, 'httpStatus' => 400], 'errors' => [['errCode' => '1071', 'message' => 'Fare no longer available']]], 400)]);
        $booking = $this->pendingBooking();

        app(FlightBookingService::class)->confirmAfterPayment($this->paymentFor($booking), $this->razorpay);

        $this->assertSame('refunded', $booking->fresh()->status);
        $this->assertCount(1, $this->razorpay->refunds);
        Queue::assertNotPushed(PollFlightBookingStatusJob::class);
    }

    public function test_confirmation_page_skips_booking_details_within_five_seconds_of_book(): void
    {
        $booking = $this->pendingBooking(['status' => 'confirmed', 'tripjack_booking_id' => 'TJS100000000001', 'tripjack_confirm_attempted_at' => now()]);
        Http::fake(['*/booking-details' => Http::response($this->details('SUCCESS'))]);

        $this->get(route('hotel.booking.confirmation', $booking->reference))->assertOk();
        Http::assertNothingSent();

        $this->travel(6)->seconds();
        $this->get(route('hotel.booking.confirmation', $booking->reference))->assertOk();
        Http::assertSentCount(1);
        $this->assertSame(['DEL-BOM' => 'ABC123'], $booking->fresh()->tripjack_flight_pnr);
    }
}
