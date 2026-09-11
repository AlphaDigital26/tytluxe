<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Destination;
use App\Models\Hotel;
use App\Services\Payment\RazorpayService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\Doubles\FakeRazorpayService;
use Tests\TestCase;

class RazorpayPaymentFlowTest extends TestCase
{
    use RefreshDatabase;

    protected FakeRazorpayService $razorpay;

    protected function setUp(): void
    {
        parent::setUp();
        $this->razorpay = new FakeRazorpayService();
        $this->app->instance(RazorpayService::class, $this->razorpay);
    }

    protected function createPendingBooking(string $slug, string $tjHotelId): Booking
    {
        $destination = Destination::create([
            'name' => 'Dubai', 'slug' => 'dubai-rzp-'.$tjHotelId, 'country' => 'UAE',
            'type' => 'city', 'for' => ['hotel'], 'is_active' => true,
        ]);
        $hotel = Hotel::create([
            'destination_id' => $destination->id,
            'title' => 'Razorpay Test Hotel', 'slug' => $slug,
            'description' => 'x', 'category' => 'city_luxury', 'address' => 'Dubai',
            'star_rating' => 4, 'price_from' => 0, 'source' => 'tripjack',
            'tripjack_hotel_id' => $tjHotelId, 'is_active' => true,
        ]);

        session(['tripjack_booking_draft' => [
            'hotel_id' => $hotel->id,
            'hid' => $tjHotelId,
            'bookingId' => 'TGS-RZP-'.$tjHotelId,
            'option' => [
                'optionId' => 'opt-rzp',
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
            'correlationId' => 'cid-rzp',
            'check_in' => '2026-09-15', 'check_out' => '2026-09-18',
            'adults' => 1, 'children' => 0, 'rooms' => 1,
        ]]);

        $response = $this->post("/hotels/{$slug}/book", [
            'lead_name' => 'Jane Payer', 'lead_email' => 'jane@example.com', 'lead_phone' => '9876543210',
            'rooms' => [0 => ['travelers' => [0 => ['title' => 'Ms', 'first_name' => 'Jane', 'last_name' => 'Payer']]]],
        ]);

        $booking = Booking::where('hotel_id', $hotel->id)->firstOrFail();
        $response->assertRedirect("/booking/{$booking->reference}/pay");

        return $booking->fresh();
    }

    public function test_submitting_guest_details_creates_booking_and_order_without_calling_book(): void
    {
        Http::fake(); // any TripJack call here would be a bug

        $booking = $this->createPendingBooking('order-hotel', '888888001');

        $this->assertSame('pending_payment', $booking->status);
        $this->assertSame(1, $booking->payments()->count());

        $payment = $booking->payments()->first();
        $this->assertSame('created', $payment->status);
        $this->assertNotEmpty($payment->razorpay_order_id);
        $this->assertCount(1, $this->razorpay->orders);

        Http::assertNothingSent();
    }

    public function test_valid_callback_signature_confirms_booking_via_book_with_payment_infos(): void
    {
        Http::fake([
            '*/hotel/book' => Http::response(['bookingId' => 'TJ-BOOK-RZP', 'status' => ['success' => true]], 200),
            '*/hotel/booking-details' => Http::response([
                'order' => ['bookingId' => 'TJ-BOOK-RZP', 'status' => 'SUCCESS'],
                'status' => ['success' => true],
            ], 200),
        ]);

        $booking = $this->createPendingBooking('valid-cb-hotel', '888888002');
        $payment = $booking->payments()->first();
        $this->razorpay->nextSignatureValid = true;

        $response = $this->post('/payment/razorpay/callback', [
            'razorpay_payment_id' => 'pay_fake_1',
            'razorpay_order_id' => $payment->razorpay_order_id,
            'razorpay_signature' => 'sig_fake',
        ]);

        $response->assertRedirect("/booking/{$booking->reference}");

        $booking->refresh();
        $payment->refresh();

        $this->assertSame('confirmed', $booking->status);
        $this->assertSame('TJ-BOOK-RZP', $booking->tripjack_booking_id);
        $this->assertSame('captured', $payment->status);

        Http::assertSent(function ($request) use ($booking) {
            if (! str_contains($request->url(), '/hotel/book')) {
                return true;
            }

            // paymentInfos must carry TripJack's raw price (25000), never
            // the marked-up customer price (30,136.21) — that's what
            // actually gets deducted from the TripJack wallet.
            return ($request->data()['paymentInfos'][0]['amount'] ?? null) == 25000
                && ($request->data()['bookingId'] ?? null) === $booking->tripjack_hold_id;
        });
    }

    public function test_invalid_callback_signature_does_not_call_book(): void
    {
        Http::fake();

        $booking = $this->createPendingBooking('invalid-cb-hotel', '888888003');
        $payment = $booking->payments()->first();
        $this->razorpay->nextSignatureValid = false;

        $response = $this->post('/payment/razorpay/callback', [
            'razorpay_payment_id' => 'pay_fake_2',
            'razorpay_order_id' => $payment->razorpay_order_id,
            'razorpay_signature' => 'bad_sig',
        ]);

        $response->assertRedirect("/booking/{$booking->reference}/pay");
        $response->assertSessionHas('booking_error');

        $booking->refresh();
        $payment->refresh();
        $this->assertSame('pending_payment', $booking->status);
        $this->assertSame('created', $payment->status);

        Http::assertNothingSent();
    }

    public function test_book_failure_after_payment_captured_triggers_automatic_refund(): void
    {
        Http::fake([
            '*/hotel/book' => Http::response([
                'status' => ['success' => false],
                'errors' => [['errCode' => '6034', 'message' => 'Hotel Review Failed']],
            ], 200),
        ]);

        $booking = $this->createPendingBooking('refund-hotel', '888888004');
        $payment = $booking->payments()->first();
        $this->razorpay->nextSignatureValid = true;

        $this->post('/payment/razorpay/callback', [
            'razorpay_payment_id' => 'pay_fake_3',
            'razorpay_order_id' => $payment->razorpay_order_id,
            'razorpay_signature' => 'sig_fake',
        ]);

        $booking->refresh();
        $payment->refresh();

        $this->assertSame('refunded', $booking->status);
        $this->assertSame('refunded', $payment->status);
        $this->assertSame((float) $payment->amount, (float) $payment->refund_amount);
        $this->assertCount(1, $this->razorpay->refunds);
    }

    public function test_webhook_and_callback_racing_for_same_payment_do_not_double_book(): void
    {
        Http::fake([
            '*/hotel/book' => Http::response(['bookingId' => 'TJ-BOOK-RACE', 'status' => ['success' => true]], 200),
            '*/hotel/booking-details' => Http::response([
                'order' => ['bookingId' => 'TJ-BOOK-RACE', 'status' => 'SUCCESS'],
                'status' => ['success' => true],
            ], 200),
        ]);

        $booking = $this->createPendingBooking('race-hotel', '888888005');
        $payment = $booking->payments()->first();
        $this->razorpay->nextSignatureValid = true;
        $this->razorpay->nextWebhookSignatureValid = true;

        // The client-side callback fires first...
        $this->post('/payment/razorpay/callback', [
            'razorpay_payment_id' => 'pay_fake_4',
            'razorpay_order_id' => $payment->razorpay_order_id,
            'razorpay_signature' => 'sig_fake',
        ]);

        // ...then Razorpay's webhook fires for the same payment (both do, in production).
        $webhookBody = json_encode([
            'event' => 'payment.captured',
            'payload' => ['payment' => ['entity' => ['id' => 'pay_fake_4', 'order_id' => $payment->razorpay_order_id]]],
        ]);
        $this->call('POST', '/payment/razorpay/webhook', [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X-Razorpay-Signature' => 'webhook_sig_fake',
        ], $webhookBody);

        // One /hotel/book + one /hotel/booking-details — not four — proves
        // the webhook no-op'd against the already-processed payment.
        Http::assertSentCount(2);
        $this->assertSame('confirmed', $booking->fresh()->status);
    }

    /**
     * Instant Booking (paymentInfos included) is not a guarantee — TripJack
     * can still come back ON_HOLD, which requires a separate confirm-book
     * call before the option's deadline or it auto-cancels.
     */
    public function test_on_hold_response_triggers_confirm_book_and_lands_confirmed(): void
    {
        Http::fake([
            '*/hotel/book' => Http::response(['bookingId' => 'TJ-BOOK-HOLD', 'status' => ['success' => true]], 200),
            '*/hotel/booking-details' => Http::response([
                'order' => ['bookingId' => 'TJ-BOOK-HOLD', 'status' => 'ON_HOLD'],
                'status' => ['success' => true],
            ], 200),
            '*/hotel/confirm-book' => Http::response(['bookingId' => 'TJ-BOOK-HOLD', 'status' => ['success' => true]], 200),
        ]);

        $booking = $this->createPendingBooking('onhold-hotel', '888888006');
        $payment = $booking->payments()->first();
        $this->razorpay->nextSignatureValid = true;

        $this->post('/payment/razorpay/callback', [
            'razorpay_payment_id' => 'pay_fake_5',
            'razorpay_order_id' => $payment->razorpay_order_id,
            'razorpay_signature' => 'sig_fake',
        ]);

        $booking->refresh();

        $this->assertSame('confirmed', $booking->status);
        $this->assertNotNull($booking->tripjack_confirm_attempted_at);

        Http::assertSent(function ($request) use ($booking) {
            if (! str_contains($request->url(), '/hotel/confirm-book')) {
                return true;
            }

            // confirm-book must use the Book response's bookingId (TJ-BOOK-HOLD),
            // not the original Review bookingId, and TripJack's raw price again.
            return ($request->data()['bookingId'] ?? null) === $booking->tripjack_booking_id
                && ($request->data()['paymentInfos'][0]['amount'] ?? null) == 25000;
        });
    }

    public function test_on_hold_confirm_book_failure_triggers_automatic_refund(): void
    {
        Http::fake([
            '*/hotel/book' => Http::response(['bookingId' => 'TJ-BOOK-HOLD2', 'status' => ['success' => true]], 200),
            '*/hotel/booking-details' => Http::response([
                'order' => ['bookingId' => 'TJ-BOOK-HOLD2', 'status' => 'ON_HOLD'],
                'status' => ['success' => true],
            ], 200),
            '*/hotel/confirm-book' => Http::response([
                'status' => ['success' => false],
                'errors' => [['errCode' => '6537', 'message' => 'Hold not allowed']],
            ], 200),
        ]);

        $booking = $this->createPendingBooking('onhold-fail-hotel', '888888007');
        $payment = $booking->payments()->first();
        $this->razorpay->nextSignatureValid = true;

        $this->post('/payment/razorpay/callback', [
            'razorpay_payment_id' => 'pay_fake_6',
            'razorpay_order_id' => $payment->razorpay_order_id,
            'razorpay_signature' => 'sig_fake',
        ]);

        $booking->refresh();
        $payment->refresh();

        $this->assertSame('refunded', $booking->status);
        $this->assertSame('refunded', $payment->status);
        $this->assertCount(1, $this->razorpay->refunds);
    }

    public function test_confirm_book_is_never_called_twice_for_the_same_booking(): void
    {
        Http::fake([
            '*/hotel/book' => Http::response(['bookingId' => 'TJ-BOOK-HOLD3', 'status' => ['success' => true]], 200),
            '*/hotel/booking-details' => Http::response([
                'order' => ['bookingId' => 'TJ-BOOK-HOLD3', 'status' => 'ON_HOLD'],
                'status' => ['success' => true],
            ], 200),
            '*/hotel/confirm-book' => Http::response(['bookingId' => 'TJ-BOOK-HOLD3', 'status' => ['success' => true]], 200),
        ]);

        $booking = $this->createPendingBooking('onhold-race-hotel', '888888008');
        $payment = $booking->payments()->first();
        $this->razorpay->nextSignatureValid = true;
        $this->razorpay->nextWebhookSignatureValid = true;

        $this->post('/payment/razorpay/callback', [
            'razorpay_payment_id' => 'pay_fake_7',
            'razorpay_order_id' => $payment->razorpay_order_id,
            'razorpay_signature' => 'sig_fake',
        ]);

        $webhookBody = json_encode([
            'event' => 'payment.captured',
            'payload' => ['payment' => ['entity' => ['id' => 'pay_fake_7', 'order_id' => $payment->razorpay_order_id]]],
        ]);
        $this->call('POST', '/payment/razorpay/webhook', [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X-Razorpay-Signature' => 'webhook_sig_fake',
        ], $webhookBody);

        $confirmBookCalls = 0;
        Http::assertSent(function ($request) use (&$confirmBookCalls) {
            if (str_contains($request->url(), '/hotel/confirm-book')) {
                $confirmBookCalls++;
            }

            return true;
        });

        $this->assertSame(1, $confirmBookCalls, 'confirm-book must never be called twice — it could double-deduct the TripJack wallet');
        $this->assertSame('confirmed', $booking->fresh()->status);
    }
}
