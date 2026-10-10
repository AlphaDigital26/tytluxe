<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Destination;
use App\Models\Hotel;
use App\Models\User;
use App\Services\HotelPricingService;
use App\Services\Payment\RazorpayService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Request as HttpRequest;
use Illuminate\Support\Facades\Http;
use Tests\Doubles\FakeRazorpayService;
use Tests\TestCase;

/**
 * End-to-end hotel booking scenarios through the real routes:
 * detail page → review → guest details → Razorpay → TripJack Book →
 * confirmation → cancellation. TripJack and Razorpay are faked so every
 * outcome (success, pending, failure, penalties) can be forced.
 */
class HotelBookingScenariosTest extends TestCase
{
    use RefreshDatabase;

    protected string $checkIn;

    protected string $checkOut;

    protected FakeRazorpayService $razorpay;

    protected User $user;

    protected const TJ_PRICE = 25000.0;

    protected function setUp(): void
    {
        parent::setUp();
        $this->checkIn = now()->addDays(20)->toDateString();
        $this->checkOut = now()->addDays(23)->toDateString();
        $this->razorpay = new FakeRazorpayService();
        $this->app->instance(RazorpayService::class, $this->razorpay);
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    // ── fixtures ────────────────────────────────────────────────────────

    protected function hotel(string $tjId, array $attrs = []): Hotel
    {
        $destination = Destination::firstOrCreate(
            ['slug' => 'dubai-scn'],
            ['name' => 'Dubai', 'country' => 'United Arab Emirates', 'type' => 'city', 'for' => ['hotel'], 'is_active' => true],
        );

        return Hotel::create(array_merge([
            'destination_id' => $destination->id,
            'title' => 'Scenario Hotel '.$tjId, 'slug' => 'scenario-hotel-'.$tjId,
            'description' => 'x', 'category' => 'city_luxury', 'address' => 'Dubai',
            'star_rating' => 4, 'price_from' => 0, 'source' => 'tripjack',
            'tripjack_hotel_id' => $tjId, 'is_active' => true,
        ], $attrs));
    }

    /** An option as Detail/Review return it. */
    protected function option(array $over = []): array
    {
        return array_replace_recursive([
            'optionId' => 'opt-1',
            'optionType' => 'SRSM',
            'roomInfo' => [['id' => 'r1', 'name' => 'Deluxe Room', 'adults' => 2, 'children' => 0]],
            'mealBasis' => 'Room Only',
            'pricing' => ['totalPrice' => self::TJ_PRICE, 'basePrice' => 21000, 'taxes' => 4000, 'currency' => 'INR'],
            'compliance' => ['gstType' => 'NA', 'panRequired' => false, 'passportRequired' => false],
            'cancellation' => ['isRefundable' => true, 'penalties' => [
                ['from' => now('Asia/Kolkata')->subDay()->format('Y-m-d\TH:i:s'), 'to' => now('Asia/Kolkata')->addDays(10)->format('Y-m-d\TH:i:s'), 'amount' => 0],
                ['from' => now('Asia/Kolkata')->addDays(10)->format('Y-m-d\TH:i:s'), 'to' => now('Asia/Kolkata')->addDays(20)->format('Y-m-d\TH:i:s'), 'amount' => self::TJ_PRICE],
            ]],
            'deadlineDateTime' => now()->addDays(10)->format('Y-m-d\TH:i:s'),
        ], $over);
    }

    /** Booking Details response; $penalty is the slab in force right now (IST, no offset). */
    protected function details(string $tjId, string $status, float $penalty = 0, array $extra = []): array
    {
        return array_replace_recursive([
            'order' => ['bookingId' => $tjId, 'status' => $status, 'amount' => self::TJ_PRICE],
            'itemInfos' => ['HOTEL' => ['hInfo' => ['ops' => [[
                'cnp' => ['ifra' => $penalty <= 0, 'pd' => [[
                    'fdt' => now('Asia/Kolkata')->subDay()->format('Y-m-d\TH:i:s'),
                    'tdt' => now('Asia/Kolkata')->addDay()->format('Y-m-d\TH:i:s'),
                    'am' => $penalty,
                ]]],
            ]]]]],
            'status' => ['success' => true],
        ], $extra);
    }

    /** Detail + Review fakes for one option; merged with caller's book/details fakes. */
    protected function fakeSearch(string $tjId, array $option, array $more = [], ?array $reviewOption = null): void
    {
        Http::fake(array_merge([
            '*/hotel/pricing' => Http::response(['tjHotelId' => $tjId, 'options' => [$option], 'reviewHash' => 'hash-'.$tjId, 'correlationId' => 'cid-'.$tjId, 'status' => ['success' => true]], 200),
            '*/hotel/review' => Http::response(['bookingId' => 'TGS-'.$tjId, 'option' => $reviewOption ?? $option, 'status' => ['success' => true]], 200),
            '*/nationality-info' => Http::response(['nationalityInfos' => [['countryId' => '106', 'countryName' => 'India']], 'status' => ['success' => true]], 200),
        ], $more));
    }

    /**
     * Runs detail → review → guest form → pay → callback. Returns the booking.
     *
     * @param  array  $query  extra detail-page query (adults/rooms/children/child_ages)
     * @param  array  $form  guest form payload override
     */
    protected function bookThrough(Hotel $hotel, array $query = [], array $form = [], bool $pay = true): ?Booking
    {
        $q = http_build_query(array_merge(['check_in' => $this->checkIn, 'check_out' => $this->checkOut, 'adults' => 2, 'rooms' => 1], $query));
        $this->get("/hotels/{$hotel->slug}?{$q}")->assertOk();

        $this->post("/hotels/{$hotel->slug}/review", ['option_id' => 'opt-1'])->assertRedirect("/hotels/{$hotel->slug}/review");
        $this->get("/hotels/{$hotel->slug}/review")->assertOk();

        $this->post("/hotels/{$hotel->slug}/book", $form ?: [
            'contact_email' => 'guest@example.com', 'contact_phone' => '9876543210',
            'rooms' => [0 => ['travelers' => [
                0 => ['title' => 'Mr', 'first_name' => 'Ravi', 'last_name' => 'Kumar'],
                1 => ['title' => 'Ms', 'first_name' => 'Asha', 'last_name' => 'Kumar'],
            ]]],
        ]);

        $booking = Booking::where('hotel_id', $hotel->id)->latest('id')->first();
        if (! $booking || ! $pay) {
            return $booking;
        }

        $payment = $booking->payments()->first();
        $this->razorpay->nextSignatureValid = true;
        $this->post('/payment/razorpay/callback', [
            'razorpay_payment_id' => 'pay_'.$booking->id,
            'razorpay_order_id' => $payment->razorpay_order_id,
            'razorpay_signature' => 'sig',
        ])->assertRedirect("/booking/{$booking->reference}");

        return $booking->fresh();
    }

    protected function sentTo(string $path): \Illuminate\Support\Collection
    {
        return collect(Http::recorded())->filter(fn ($pair) => str_contains($pair[0]->url(), $path))->map(fn ($pair) => $pair[0]);
    }

    // ── 1. Happy paths ──────────────────────────────────────────────────

    public function test_01_single_room_confirmed_with_hotel_confirmation_number(): void
    {
        $hotel = $this->hotel('700000001');
        $this->fakeSearch('700000001', $this->option(), [
            '*/hotel/book' => Http::response(['bookingId' => 'TJ-1', 'status' => ['success' => true]], 200),
            '*/hotel/booking-details' => Http::response($this->details('TJ-1', 'SUCCESS', 0, [
                'itemInfos' => ['HOTEL' => ['hInfo' => ['ops' => [['ris' => [['hotelConfirmationNumber' => 'HCN-998877']]]]]]],
            ]), 200),
        ]);

        $booking = $this->bookThrough($hotel);

        $this->assertSame('confirmed', $booking->status);
        $this->assertSame('TJ-1', $booking->tripjack_booking_id);
        $this->assertSame('HCN-998877', $booking->hotel_confirmation_number);
        $this->assertEqualsWithDelta(HotelPricingService::price(self::TJ_PRICE)['customer_price'], (float) $booking->total_amount, 0.01);

        // Book carried TripJack's raw price, not the customer price.
        $book = $this->sentTo('/hotel/book')->first();
        $this->assertEqualsWithDelta(self::TJ_PRICE, $book->data()['paymentInfos'][0]['amount'], 0.01);

        $before = $this->sentTo('/hotel/booking-details')->count();
        $this->get("/booking/{$booking->reference}")->assertOk()->assertSee('Booking Confirmed')->assertSee('HCN-998877');
        $this->assertSame($before, $this->sentTo('/hotel/booking-details')->count(), 'A finished booking must not call Booking Details again');
    }

    public function test_02_two_rooms_mixed_types_with_child_shows_per_room_names_and_sends_ages(): void
    {
        $hotel = $this->hotel('700000002');
        $mixed = $this->option([
            'optionType' => 'CRSM',
            'roomInfo' => [
                ['id' => 'r1', 'name' => 'Deluxe Room', 'adults' => 2, 'children' => 1, 'childAge' => [7]],
                ['id' => 'r2', 'name' => 'Standard Room', 'adults' => 1, 'children' => 0],
            ],
        ]);
        $this->fakeSearch('700000002', $mixed, [
            '*/hotel/book' => Http::response(['bookingId' => 'TJ-2', 'status' => ['success' => true]], 200),
            '*/hotel/booking-details' => Http::response($this->details('TJ-2', 'SUCCESS'), 200),
        ]);

        $q = http_build_query(['check_in' => $this->checkIn, 'check_out' => $this->checkOut, 'adults' => 3, 'children' => 1, 'rooms' => 2, 'child_ages' => '7']);
        $this->get("/hotels/{$hotel->slug}?{$q}")->assertOk();
        $this->post("/hotels/{$hotel->slug}/review", ['option_id' => 'opt-1']);
        $this->get("/hotels/{$hotel->slug}/review")->assertOk()->assertSee('Room 1: Deluxe Room | Room 2: Standard Room');

        $this->post("/hotels/{$hotel->slug}/book", [
            'contact_email' => 'fam@example.com', 'contact_phone' => '9876543210',
            'rooms' => [
                0 => ['travelers' => [
                    0 => ['title' => 'Mr', 'first_name' => 'Arun', 'last_name' => 'Shah'],
                    1 => ['title' => 'Ms', 'first_name' => 'Meera', 'last_name' => 'Shah'],
                    2 => ['title' => 'Master', 'first_name' => 'Kabir', 'last_name' => 'Shah'],
                ]],
                1 => ['travelers' => [0 => ['title' => 'Mr', 'first_name' => 'Vikram', 'last_name' => 'Rao']]],
            ],
        ]);
        $booking = Booking::where('hotel_id', $hotel->id)->firstOrFail();
        $this->razorpay->nextSignatureValid = true;
        $this->post('/payment/razorpay/callback', ['razorpay_payment_id' => 'pay_2', 'razorpay_order_id' => $booking->payments()->first()->razorpay_order_id, 'razorpay_signature' => 's']);

        $this->assertSame('confirmed', $booking->fresh()->status);
        // The guest's own search (the room-type background refresh also
        // prices this new hotel, with default guests, so take the last call).
        $searchRooms = $this->sentTo('/hotel/pricing')->last()->data()['rooms'];
        $this->assertCount(2, $searchRooms);
        $this->assertSame([7], collect($searchRooms)->pluck('childAge')->filter()->flatten()->all(), 'The real child age is sent');
        $roomTravellers = $this->sentTo('/hotel/book')->first()->data()['roomTravellerInfo'] ?? [];
        $this->assertCount(2, $roomTravellers);
        $this->assertCount(3, $roomTravellers[0]['travellerInfo']);
        $this->assertCount(1, $roomTravellers[1]['travellerInfo']);
    }

    public function test_03_pan_required_rejects_invalid_pan_and_accepts_valid_one(): void
    {
        $hotel = $this->hotel('700000003');
        $this->fakeSearch('700000003', $this->option(['compliance' => ['panRequired' => true]]), [
            '*/hotel/book' => Http::response(['bookingId' => 'TJ-3', 'status' => ['success' => true]], 200),
            '*/hotel/booking-details' => Http::response($this->details('TJ-3', 'SUCCESS'), 200),
        ]);

        $base = ['contact_email' => 'p@example.com', 'contact_phone' => '9876543210', 'pan_name' => 'Ravi Kumar',
            'rooms' => [0 => ['travelers' => [0 => ['title' => 'Mr', 'first_name' => 'Ravi', 'last_name' => 'Kumar'], 1 => ['title' => 'Ms', 'first_name' => 'Asha', 'last_name' => 'Kumar']]]]];

        // 4th letter D isn't a valid PAN holder type (TripJack error 1092).
        $bad = $this->bookThrough($hotel, form: $base + ['pan_number' => 'ABCDE1234F'], pay: false);
        $this->assertNull($bad, 'Invalid PAN must not create a booking');

        $this->post("/hotels/{$hotel->slug}/book", $base + ['pan_number' => 'ABCPE1234F']);
        $this->assertSame(1, Booking::where('hotel_id', $hotel->id)->count());
    }

    // ── 2. Hotel slow to confirm / failures after payment ─────────────────

    public function test_04_pending_at_tripjack_shows_awaiting_then_background_job_confirms(): void
    {
        $hotel = $this->hotel('700000004');
        $this->fakeSearch('700000004', $this->option(), [
            '*/hotel/book' => Http::response(['bookingId' => 'TJ-4', 'status' => ['success' => true]], 200),
            '*/hotel/booking-details' => Http::sequence()
                ->push($this->details('TJ-4', 'PENDING'))
                ->push($this->details('TJ-4', 'PENDING'))
                ->whenEmpty(Http::response($this->details('TJ-4', 'SUCCESS'))),
        ]);

        $booking = $this->bookThrough($hotel);
        $this->assertSame('pending_confirmation', $booking->status);
        $this->get("/booking/{$booking->reference}")->assertOk()->assertSee('Awaiting Hotel Confirmation');

        $this->artisan('tripjack:refresh-hotel-bookings')->assertSuccessful();
        $this->assertSame('confirmed', $booking->fresh()->status);
    }

    public function test_05_book_rejected_by_tripjack_refunds_guest_in_full(): void
    {
        $hotel = $this->hotel('700000005');
        $this->fakeSearch('700000005', $this->option(), [
            '*/hotel/book' => Http::response(['status' => ['success' => false], 'errors' => [['errCode' => '6503', 'message' => 'Sold out']]], 200),
        ]);

        $booking = $this->bookThrough($hotel);

        $this->assertSame('refunded', $booking->status);
        $this->assertCount(1, $this->razorpay->refunds);
        $this->get("/booking/{$booking->reference}")->assertSee('Refund Issued');
    }

    public function test_06_tripjack_aborts_after_accepting_book_refunds_guest(): void
    {
        $hotel = $this->hotel('700000006');
        $this->fakeSearch('700000006', $this->option(), [
            '*/hotel/book' => Http::response(['bookingId' => 'TJ-6', 'status' => ['success' => true]], 200),
            '*/hotel/booking-details' => Http::response($this->details('TJ-6', 'ABORTED'), 200),
        ]);

        $booking = $this->bookThrough($hotel);

        $this->assertSame('refunded', $booking->status);
        $this->assertCount(1, $this->razorpay->refunds);
    }

    public function test_07_book_server_error_is_never_retried(): void
    {
        $hotel = $this->hotel('700000007');
        config(['services.tripjack.retry_times' => 3]);
        $this->fakeSearch('700000007', $this->option(), [
            '*/hotel/book' => Http::response(['error' => 'boom'], 502),
        ]);

        $booking = $this->bookThrough($hotel);

        $this->assertCount(1, $this->sentTo('/hotel/book'), 'Book must be sent exactly once — a retry could double-book');
        // A 5xx means the outcome is unknown — no refund until TripJack says.
        $this->assertSame('pending_confirmation', $booking->status);
        $this->assertCount(0, $this->razorpay->refunds);
    }

    public function test_08_book_timeout_is_not_retried_and_outcome(): void
    {
        $hotel = $this->hotel('700000008');
        $bookCalls = 0;
        $this->fakeSearch('700000008', $this->option(), [
            '*/hotel/book' => function () use (&$bookCalls) {
                $bookCalls++;
                throw new ConnectionException('timed out');
            },
            '*/hotel/booking-details' => Http::response($this->details('TJ-8', 'SUCCESS'), 200),
        ]);

        $booking = $this->bookThrough($hotel);

        $this->assertSame(1, $bookCalls);
        // TripJack may have booked it: no refund, tracked under the Review id.
        $this->assertSame('pending_confirmation', $booking->status);
        $this->assertSame('TGS-700000008', $booking->tripjack_booking_id);
        $this->assertCount(0, $this->razorpay->refunds);

        // TripJack did book it → the background check confirms it.
        $this->artisan('tripjack:refresh-hotel-bookings')->assertSuccessful();
        $this->assertSame('confirmed', $booking->fresh()->status);
        $this->assertCount(0, $this->razorpay->refunds);
    }

    public function test_08b_book_timeout_that_tripjack_never_received_is_refunded_after_grace_period(): void
    {
        $hotel = $this->hotel('700000081');
        $this->fakeSearch('700000081', $this->option(), [
            '*/hotel/book' => fn () => throw new ConnectionException('timed out'),
            '*/hotel/booking-details' => Http::response(['status' => ['success' => false, 'httpStatus' => 400], 'errors' => [['errCode' => '6010', 'message' => 'Booking not found']]], 400),
        ]);

        $booking = $this->bookThrough($hotel);
        $this->assertSame('pending_confirmation', $booking->status);

        // Within the grace period: not found yet is not proof — keep waiting.
        $this->artisan('tripjack:refresh-hotel-bookings')->assertSuccessful();
        $this->assertSame('pending_confirmation', $booking->fresh()->status);
        $this->assertCount(0, $this->razorpay->refunds);

        // Still unknown to TripJack 45 minutes later → refunded in full.
        $this->travel(45)->minutes();
        $this->artisan('tripjack:refresh-hotel-bookings')->assertSuccessful();
        $this->assertSame('refunded', $booking->fresh()->status);
        $this->assertCount(1, $this->razorpay->refunds);
    }

    public function test_08c_tripjack_outage_during_status_checks_never_refunds(): void
    {
        $hotel = $this->hotel('700000082');
        $this->fakeSearch('700000082', $this->option(), [
            '*/hotel/book' => fn () => throw new ConnectionException('timed out'),
            '*/hotel/booking-details' => Http::response(['error' => 'down'], 503),
        ]);

        $booking = $this->bookThrough($hotel);
        $this->travel(2)->hours();
        $this->artisan('tripjack:refresh-hotel-bookings')->assertSuccessful();

        $this->assertSame('pending_confirmation', $booking->fresh()->status, 'An outage is not evidence the booking failed');
        $this->assertCount(0, $this->razorpay->refunds);
    }

    public function test_09_on_hold_after_book_is_confirmed_via_confirm_book(): void
    {
        $hotel = $this->hotel('700000009');
        $this->fakeSearch('700000009', $this->option(), [
            '*/hotel/book' => Http::response(['bookingId' => 'TJ-9', 'status' => ['success' => true]], 200),
            '*/hotel/booking-details' => Http::sequence()->push($this->details('TJ-9', 'ON_HOLD'))->whenEmpty(Http::response($this->details('TJ-9', 'SUCCESS'))),
            '*/hotel/confirm-book' => Http::response(['bookingId' => 'TJ-9', 'status' => ['success' => true]], 200),
        ]);

        $booking = $this->bookThrough($hotel);

        $this->assertSame('confirmed', $booking->status);
        $this->assertCount(1, $this->sentTo('/hotel/confirm-book'));
    }

    // ── 3. Price moves & errors before payment ────────────────────────────

    public function test_10_price_rise_at_review_is_shown_and_charged_at_new_price(): void
    {
        $hotel = $this->hotel('700000010');
        $this->fakeSearch('700000010', $this->option(), [
            '*/hotel/book' => Http::response(['bookingId' => 'TJ-10', 'status' => ['success' => true]], 200),
            '*/hotel/booking-details' => Http::response($this->details('TJ-10', 'SUCCESS'), 200),
        ], reviewOption: $this->option(['pricing' => ['totalPrice' => 27000]]));

        $q = http_build_query(['check_in' => $this->checkIn, 'check_out' => $this->checkOut, 'adults' => 2, 'rooms' => 1]);
        $this->get("/hotels/{$hotel->slug}?{$q}");
        $this->post("/hotels/{$hotel->slug}/review", ['option_id' => 'opt-1']);
        $newPrice = HotelPricingService::price(27000)['customer_price'];
        $this->get("/hotels/{$hotel->slug}/review")->assertOk()->assertSee(number_format($newPrice, 2));

        $this->post("/hotels/{$hotel->slug}/book", ['contact_email' => 'x@example.com', 'contact_phone' => '9876543210',
            'rooms' => [0 => ['travelers' => [0 => ['title' => 'Mr', 'first_name' => 'Ravi', 'last_name' => 'Kumar'], 1 => ['title' => 'Ms', 'first_name' => 'Asha', 'last_name' => 'Kumar']]]]]);
        $booking = Booking::where('hotel_id', $hotel->id)->firstOrFail();
        $this->assertEqualsWithDelta($newPrice, (float) $booking->total_amount, 0.01);
        $this->assertEqualsWithDelta(27000, (float) $booking->tripjack_total_price, 0.01);
    }

    public function test_11_sold_out_at_review_returns_guest_to_hotel_with_message(): void
    {
        $hotel = $this->hotel('700000011');
        $this->fakeSearch('700000011', $this->option(), [
            '*/hotel/review' => Http::response(['status' => ['success' => false], 'errors' => [['errCode' => '6503', 'message' => 'Option not available']]], 200),
        ]);

        $q = http_build_query(['check_in' => $this->checkIn, 'check_out' => $this->checkOut, 'adults' => 2, 'rooms' => 1]);
        $this->get("/hotels/{$hotel->slug}?{$q}");
        $r = $this->post("/hotels/{$hotel->slug}/review", ['option_id' => 'opt-1']);

        $r->assertRedirect();
        $this->assertStringContainsString("/hotels/{$hotel->slug}", $r->headers->get('Location'));
        $this->assertNotEmpty(session('booking_error'));
        $this->assertSame(0, Booking::count());
    }

    public function test_12_failed_payment_never_calls_book(): void
    {
        $hotel = $this->hotel('700000012');
        $this->fakeSearch('700000012', $this->option());

        $booking = $this->bookThrough($hotel, pay: false);
        $payment = $booking->payments()->first();
        $this->razorpay->nextSignatureValid = false;
        $this->post('/payment/razorpay/callback', ['razorpay_payment_id' => 'pay_x', 'razorpay_order_id' => $payment->razorpay_order_id, 'razorpay_signature' => 'bad']);

        $this->assertNotSame('confirmed', $booking->fresh()->status);
        $this->assertCount(0, $this->sentTo('/hotel/book'));
    }

    // ── 4. Cancellation ───────────────────────────────────────────────

    protected function confirmedForCancel(string $tjId, array $detailsSequence): Booking
    {
        $hotel = $this->hotel($tjId);
        $seq = Http::sequence()->push($this->details('TJ-'.$tjId, 'SUCCESS'));
        foreach ($detailsSequence as $d) {
            $seq->push($d);
        }
        $this->fakeSearch($tjId, $this->option(), [
            '*/hotel/book' => Http::response(['bookingId' => 'TJ-'.$tjId, 'status' => ['success' => true]], 200),
            '*/hotel/booking-details' => $seq,
            '*/hotel/cancel-booking/*' => Http::response(['status' => ['success' => true]], 200),
        ]);
        $booking = $this->bookThrough($hotel);
        $this->assertSame('confirmed', $booking->status);

        return $booking;
    }

    public function test_13_free_cancellation_refund_less_fee_and_cancel_sent_without_body(): void
    {
        $booking = $this->confirmedForCancel('700000013', [
            $this->details('TJ-700000013', 'SUCCESS', 0), // cancel page quote
            $this->details('TJ-700000013', 'CANCELLED', 0),
        ]);

        $this->get("/booking/{$booking->reference}/cancel")->assertOk();
        $this->post("/booking/{$booking->reference}/cancel")->assertRedirect("/booking/{$booking->reference}");

        $booking->refresh();
        $payment = $booking->payments()->first();
        $this->assertSame('cancelled', $booking->status);
        // Free cancellation: everything paid less the flat fee (RefundPolicy).
        $this->assertSame('partially_refunded', $payment->status);
        $this->assertEqualsWithDelta((float) $payment->amount - 100, (float) $payment->refund_amount, 0.01);

        $cancel = $this->sentTo('/hotel/cancel-booking/')->first();
        $this->assertSame('', $cancel->body(), 'Cancellation must be sent with no body');
        $this->assertFalse($cancel->hasHeader('Content-Type'), 'TripJack rejects cancel-booking (403) when Content-Type is sent');
    }

    public function test_14_cancellation_with_penalty_refunds_what_tripjack_refunds(): void
    {
        $booking = $this->confirmedForCancel('700000014', [
            $this->details('TJ-700000014', 'SUCCESS', 10000),
            $this->details('TJ-700000014', 'CANCELLED', 10000),
        ]);

        $this->get("/booking/{$booking->reference}/cancel")->assertOk();
        $this->post("/booking/{$booking->reference}/cancel");

        $payment = $booking->payments()->first();
        $this->assertSame('cancelled', $booking->fresh()->status);
        $this->assertSame('partially_refunded', $payment->status);
        // TripJack keeps 10000 of its 25000 and refunds us 15000 — exactly
        // what the guest gets back; our markup isn't refunded.
        $this->assertEqualsWithDelta(self::TJ_PRICE - 10000, (float) $payment->refund_amount, 0.01);
    }

    public function test_15_pending_cancellation_resolved_later_uses_penalty_at_request_time(): void
    {
        $tj = 'TJ-700000015';
        // Free until 2h from now (IST); full penalty after that.
        $slabs = ['itemInfos' => ['HOTEL' => ['hInfo' => ['ops' => [['cnp' => ['ifra' => true, 'pd' => [
            ['fdt' => now('Asia/Kolkata')->subDay()->format('Y-m-d\TH:i:s'), 'tdt' => now('Asia/Kolkata')->addHours(2)->format('Y-m-d\TH:i:s'), 'am' => 0],
            ['fdt' => now('Asia/Kolkata')->addHours(2)->format('Y-m-d\TH:i:s'), 'tdt' => now('Asia/Kolkata')->addDays(30)->format('Y-m-d\TH:i:s'), 'am' => self::TJ_PRICE],
        ]]]]]]]];
        $pending = array_replace_recursive($this->details($tj, 'CANCELLATION_PENDING'), $slabs);
        $cancelled = array_replace_recursive($this->details($tj, 'CANCELLED'), $slabs);
        // Still pending at submit and when the guest revisits; done afterwards.
        $booking = $this->confirmedForCancel('700000015', [$pending, $pending, $cancelled]);

        $this->post("/booking/{$booking->reference}/cancel");
        $this->assertSame('confirmed', $booking->fresh()->status);
        $this->get("/booking/{$booking->reference}")->assertSee('Cancellation In Progress');

        // TripJack finishes 5 hours later — now inside the full-penalty slab.
        $this->travel(5)->hours();
        $this->artisan('tripjack:refresh-hotel-bookings')->assertSuccessful();

        $payment = $booking->payments()->first();
        $this->assertSame('cancelled', $booking->fresh()->status);
        $this->assertEqualsWithDelta((float) $payment->amount - 100, (float) $payment->refund_amount, 0.01, 'Asked within the free window → free-cancellation refund, even though TripJack finished later');
    }

    // ── 5. Access, display, resilience ─────────────────────────────────

    public function test_16_other_users_cannot_view_or_cancel_a_booking(): void
    {
        $booking = $this->confirmedForCancel('700000016', []);

        $this->actingAs(User::factory()->create());
        $this->get("/booking/{$booking->reference}")->assertForbidden();
        $this->get("/booking/{$booking->reference}/cancel")->assertForbidden();
        $this->post("/booking/{$booking->reference}/cancel")->assertForbidden();
        $this->get("/booking/{$booking->reference}/invoice")->assertForbidden();
    }

    public function test_17_unrated_hotel_and_policy_fields_display(): void
    {
        $hotel = $this->hotel('700000017', ['star_rating' => null, 'checkin_min_age' => 21, 'checkout_till' => '1:00 PM']);
        $this->fakeSearch('700000017', $this->option());

        $q = http_build_query(['check_in' => $this->checkIn, 'check_out' => $this->checkOut, 'adults' => 2, 'rooms' => 1]);
        $this->get("/hotels/{$hotel->slug}?{$q}")->assertOk()
            ->assertSee('Unrated')->assertDontSee('-Star Hotel')
            ->assertSee('21 years')->assertSee('till 1:00 PM')
            ->assertSee('Free Cancellation before');
        $this->get('/hotels?destination=dubai-scn')->assertOk();
    }

    public function test_18_nationality_outage_is_not_retried_on_every_page_load(): void
    {
        $hotel = $this->hotel('700000018');
        Http::fake([
            '*/nationality-info' => Http::response(['error' => 'down'], 500),
            '*/hotel/pricing' => Http::response(['tjHotelId' => '700000018', 'options' => [$this->option()], 'reviewHash' => 'h', 'correlationId' => 'c', 'status' => ['success' => true]], 200),
        ]);

        $q = http_build_query(['check_in' => $this->checkIn, 'check_out' => $this->checkOut, 'adults' => 2, 'rooms' => 1, 'nationality' => '12']);
        $this->get("/hotels/{$hotel->slug}?{$q}")->assertOk();
        $first = $this->sentTo('/nationality-info')->count();
        $this->get("/hotels/{$hotel->slug}?{$q}")->assertOk();
        $this->get('/hotels')->assertOk();

        $this->assertGreaterThan(0, $first);
        $this->assertSame($first, $this->sentTo('/nationality-info')->count(), 'After a failure, the next hour must not call TripJack again');
    }
}
