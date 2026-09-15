<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Destination;
use App\Models\Hotel;
use App\Models\User;
use App\Services\Payment\RazorpayService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\Doubles\FakeRazorpayService;
use Tests\TestCase;

class BookingCancellationTest extends TestCase
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
    }

    /**
     * Builds a fully CONFIRMED booking (payment captured, TripJack Book
     * succeeded) — the only state cancellation is ever offered from. Reuses
     * the same draft/pricing shape as RazorpayPaymentFlowTest so
     * tripjack_total_price (25000, TripJack's raw price) vs. total_amount
     * (30136.21, the marked-up customer price) stay consistent with how
     * finalizeCancellation()'s penalty-ratio math is meant to be exercised.
     *
     * Doesn't register any Http::fake() itself — the /hotel/book and first
     * /hotel/booking-details response must be queued by the caller (as the
     * first entries of whatever sequence it sets up) before calling this,
     * since Http::fake() always appends and the first matching stub for a
     * URL wins — a fake registered here would permanently shadow anything
     * the test itself tries to register afterward for the same endpoint.
     */
    protected function confirmedBooking(string $slug, string $tjHotelId): Booking
    {
        $destination = Destination::create([
            'name' => 'Dubai', 'slug' => 'dubai-cancel-'.$tjHotelId, 'country' => 'UAE',
            'type' => 'city', 'for' => ['hotel'], 'is_active' => true,
        ]);
        $hotel = Hotel::create([
            'destination_id' => $destination->id,
            'title' => 'Cancellation Test Hotel', 'slug' => $slug,
            'description' => 'x', 'category' => 'city_luxury', 'address' => 'Dubai',
            'star_rating' => 4, 'price_from' => 0, 'source' => 'tripjack',
            'tripjack_hotel_id' => $tjHotelId, 'is_active' => true,
        ]);

        session(['tripjack_booking_draft' => [
            'hotel_id' => $hotel->id,
            'hid' => $tjHotelId,
            'bookingId' => 'TGS-CX-'.$tjHotelId,
            'option' => [
                'optionId' => 'opt-cx',
                'roomInfo' => [['id' => 'r1', 'name' => 'Deluxe Room']],
                'compliance' => ['panRequired' => false, 'passportRequired' => false],
                'pricing' => [
                    'totalPrice' => 25000, 'basePrice' => 20000, 'currency' => 'INR',
                    'customerPrice' => 30136.21,
                    'pricingBreakdown' => [
                        'tripjack_total_price' => 25000, 'gst_slab' => 0.18,
                        'margin_amount' => 3750, 'gst_on_margin' => 675, 'razorpay_recovery' => 711.21,
                    ],
                ],
            ],
            'correlationId' => 'cid-cx',
            'check_in' => now()->addDays(30)->toDateString(), 'check_out' => now()->addDays(32)->toDateString(),
            'adults' => 1, 'children' => 0, 'rooms' => 1,
        ]]);

        $this->post("/hotels/{$slug}/book", [
            'lead_name' => 'Cancel Guest', 'lead_email' => 'cancel@example.com', 'lead_phone' => '9876543210',
            'rooms' => [0 => ['travelers' => [0 => ['title' => 'Ms', 'first_name' => 'Cancel', 'last_name' => 'Guest']]]],
        ]);

        $booking = Booking::where('hotel_id', $hotel->id)->firstOrFail();
        $payment = $booking->payments()->first();
        $this->razorpay->nextSignatureValid = true;

        $this->post('/payment/razorpay/callback', [
            'razorpay_payment_id' => 'pay_cx_'.$tjHotelId,
            'razorpay_order_id' => $payment->razorpay_order_id,
            'razorpay_signature' => 'sig_fake',
        ]);

        $booking->refresh();
        $this->assertSame('confirmed', $booking->status, 'setup fixture must land confirmed');

        return $booking;
    }

    protected function cnpResponse(string $tjBookingId, string $orderStatus, float $penaltyAmount): array
    {
        return [
            'order' => ['bookingId' => $tjBookingId, 'status' => $orderStatus],
            'itemInfos' => ['HOTEL' => ['hInfo' => ['ops' => [[
                'cnp' => [
                    'ifra' => $penaltyAmount <= 0,
                    'pd' => [[
                        'fdt' => now()->subDay()->toIso8601String(),
                        'tdt' => now()->addDay()->toIso8601String(),
                        'am' => $penaltyAmount,
                    ]],
                ],
            ]]]]],
            'status' => ['success' => true],
        ];
    }

    public function test_free_cancellation_confirms_and_auto_refunds(): void
    {
        $tjId = 'TJ-CX-999000001';

        // Sequence for /hotel/booking-details: 1) confirmedBooking()'s own
        // "did Book succeed" check, 2) submitCancellation()'s post-cancel check.
        Http::fake([
            '*/hotel/book' => Http::response(['bookingId' => $tjId, 'status' => ['success' => true]], 200),
            '*/hotel/booking-details' => Http::sequence()
                ->push($this->cnpResponse($tjId, 'SUCCESS', 0.0))
                ->push($this->cnpResponse($tjId, 'CANCELLED', 0.0)),
            '*/hotel/cancel-booking/*' => Http::response(['status' => ['success' => true]], 200),
        ]);

        $booking = $this->confirmedBooking('free-cancel-hotel', '999000001');
        $payment = $booking->payments()->where('status', 'captured')->first();

        $response = $this->post("/booking/{$booking->reference}/cancel");
        $response->assertRedirect("/booking/{$booking->reference}");

        $booking->refresh();
        $payment->refresh();

        $this->assertSame('cancelled', $booking->status);
        $this->assertNotNull($booking->cancellation_requested_at);
        $this->assertSame('refunded', $payment->status);
        $this->assertSame((float) $payment->amount, (float) $payment->refund_amount);
        $this->assertCount(1, $this->razorpay->refunds);
    }

    public function test_cancellation_with_penalty_does_not_auto_refund(): void
    {
        $tjId = 'TJ-CX-999000002';

        Http::fake([
            '*/hotel/book' => Http::response(['bookingId' => $tjId, 'status' => ['success' => true]], 200),
            '*/hotel/booking-details' => Http::sequence()
                ->push($this->cnpResponse($tjId, 'SUCCESS', 0.0))
                ->push($this->cnpResponse($tjId, 'CANCELLED', 5000.0)),
            '*/hotel/cancel-booking/*' => Http::response(['status' => ['success' => true]], 200),
        ]);

        $booking = $this->confirmedBooking('penalty-cancel-hotel', '999000002');
        $payment = $booking->payments()->where('status', 'captured')->first();

        $this->post("/booking/{$booking->reference}/cancel");

        $booking->refresh();
        $payment->refresh();

        $this->assertSame('cancelled', $booking->status);
        $this->assertStringContainsString('penalty', strtolower($booking->cancellation_reason));
        // A penalty applies — this must NOT be auto-refunded, it needs a human
        // to decide the actual split, so the captured payment stays untouched.
        $this->assertSame('captured', $payment->status);
        $this->assertCount(0, $this->razorpay->refunds);
    }

    public function test_cancellation_pending_leaves_booking_confirmed_until_resolved(): void
    {
        $tjId = 'TJ-CX-999000003';

        Http::fake([
            '*/hotel/book' => Http::response(['bookingId' => $tjId, 'status' => ['success' => true]], 200),
            '*/hotel/booking-details' => Http::sequence()
                ->push($this->cnpResponse($tjId, 'SUCCESS', 0.0)) // confirmedBooking() setup
                ->push($this->cnpResponse($tjId, 'CANCELLATION_PENDING', 0.0)) // submitCancellation()'s check
                ->push($this->cnpResponse($tjId, 'CANCELLED', 0.0)), // later bookingConfirmation() poll
            '*/hotel/cancel-booking/*' => Http::response(['status' => ['success' => true]], 200),
        ]);

        $booking = $this->confirmedBooking('pending-cancel-hotel', '999000003');

        $this->post("/booking/{$booking->reference}/cancel");

        $booking->refresh();

        // Still 'confirmed' — TripJack hasn't resolved the cancellation yet —
        // but cancellation_requested_at is the guest-facing "in progress" signal.
        $this->assertSame('confirmed', $booking->status);
        $this->assertNotNull($booking->cancellation_requested_at);

        // A later visit to the confirmation page, once TripJack resolves it
        // offline to CANCELLED, must finish the job (status flip + refund).
        $this->get("/booking/{$booking->reference}");

        $booking->refresh();
        $this->assertSame('cancelled', $booking->status);
        $this->assertCount(1, $this->razorpay->refunds);
    }

    public function test_double_cancellation_request_is_idempotent(): void
    {
        $tjId = 'TJ-CX-999000004';
        $cancelCalls = 0;

        Http::fake([
            '*/hotel/book' => Http::response(['bookingId' => $tjId, 'status' => ['success' => true]], 200),
            '*/hotel/booking-details' => Http::sequence()
                ->push($this->cnpResponse($tjId, 'SUCCESS', 0.0))
                ->whenEmpty(Http::response($this->cnpResponse($tjId, 'CANCELLATION_PENDING', 0.0), 200)),
            '*/hotel/cancel-booking/*' => function () use (&$cancelCalls) {
                $cancelCalls++;

                return Http::response(['status' => ['success' => true]], 200);
            },
        ]);

        $booking = $this->confirmedBooking('double-cancel-hotel', '999000004');

        $this->post("/booking/{$booking->reference}/cancel");
        $this->assertSame(1, $cancelCalls);

        // A second submit (double form-submit, or a retry) must not fire a
        // second cancel-booking request against TripJack.
        $this->post("/booking/{$booking->reference}/cancel");

        $this->assertSame(1, $cancelCalls, 'the claim guard should redirect before ever calling TripJack again');
    }

    public function test_cancel_api_failure_releases_claim_for_retry(): void
    {
        $tjId = 'TJ-CX-999000005';

        Http::fake([
            '*/hotel/book' => Http::response(['bookingId' => $tjId, 'status' => ['success' => true]], 200),
            '*/hotel/booking-details' => Http::response($this->cnpResponse($tjId, 'SUCCESS', 0.0), 200),
            '*/hotel/cancel-booking/*' => Http::response([
                'status' => ['success' => false],
                'errors' => [['errCode' => '6512', 'message' => 'Cancellation not allowed']],
            ], 200),
        ]);

        $booking = $this->confirmedBooking('failed-cancel-hotel', '999000005');

        $response = $this->post("/booking/{$booking->reference}/cancel");
        $response->assertRedirect("/booking/{$booking->reference}/cancel");
        $response->assertSessionHas('booking_error');

        $booking->refresh();

        // The claim must be released so the guest isn't stuck seeing
        // "cancellation in progress" forever after a rejected request.
        $this->assertNull($booking->cancellation_requested_at);
        $this->assertSame('confirmed', $booking->status);
    }

    public function test_a_user_cannot_cancel_another_users_booking(): void
    {
        $tjId = 'TJ-CX-999000006';

        Http::fake([
            '*/hotel/book' => Http::response(['bookingId' => $tjId, 'status' => ['success' => true]], 200),
            '*/hotel/booking-details' => Http::response($this->cnpResponse($tjId, 'SUCCESS', 0.0), 200),
        ]);

        $booking = $this->confirmedBooking('owner-cancel-hotel', '999000006');

        $intruder = User::factory()->create();
        $this->actingAs($intruder);

        $this->get("/booking/{$booking->reference}/cancel")->assertForbidden();
        $this->post("/booking/{$booking->reference}/cancel")->assertForbidden();

        $this->assertNull($booking->fresh()->cancellation_requested_at);
    }
}
