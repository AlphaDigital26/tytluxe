<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Payment;
use App\Models\User;
use App\Services\FlightAncillaryService;
use App\Services\FlightBookingService;
use App\Services\Payment\RazorpayService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\Doubles\FakeRazorpayService;
use Tests\TestCase;

class FlightPostBookingTest extends TestCase
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

    protected function booking(array $extra = []): Booking
    {
        return Booking::forceCreate(array_merge([
            'reference' => 'TYT'.strtoupper(substr(md5(uniqid()), 0, 8)),
            'user_id' => $this->user->id,
            'vertical' => 'flight',
            'status' => 'confirmed',
            'tripjack_booking_id' => 'TJS'.random_int(100000, 999999),
            'flight_route' => 'DEL - COK',
            'flight_departure_date' => now()->addDays(20)->toDateString(),
            'pax_adults' => 2,
            'pax_children' => 0,
            'pax_infants' => 1,
            'lead_guest_name' => 'Rahul Probe',
            'base_amount' => 10000,
            'total_amount' => 11000,
            'tripjack_total_price' => 10000,
            'flight_legs' => [['src' => 'DEL', 'dest' => 'COK', 'departureDate' => '2026-10-21']],
            'flight_segments_payload' => ['travellerInfo' => [
                ['fN' => 'Rahul', 'lN' => 'Probe', 'pt' => 'ADULT'],
                ['fN' => 'Priya', 'lN' => 'Probe', 'pt' => 'ADULT'],
                ['fN' => 'Aarav', 'lN' => 'Probe', 'pt' => 'INFANT'],
            ]],
        ], $extra));
    }

    protected function payment(Booking $b, float $amount, string $purpose, string $payId): Payment
    {
        return Payment::create([
            'booking_id' => $b->id, 'razorpay_order_id' => 'order_'.$payId, 'razorpay_payment_id' => $payId,
            'amount' => $amount, 'currency' => 'INR', 'status' => 'captured', 'purpose' => $purpose,
        ]);
    }

    protected function fakeSsr(): void
    {
        $bag = [['code' => 'B15', 'amount' => 900, 'desc' => '15kg'], ['code' => 'B20', 'amount' => 1200, 'desc' => '20kg']];
        Http::fake([
            '*/ancillaries/fetch/ssr' => Http::response(['tripInfos' => [['sI' => [
                ['id' => 'S1', 'fD' => ['aI' => ['code' => '6E'], 'fN' => '101'], 'da' => ['code' => 'DEL'], 'aa' => ['code' => 'BLR'], 'dt' => '2026-10-21T06:00', 'at' => '2026-10-21T08:45',
                    'bI' => ['tI' => [
                        ['id' => 't1', 'fN' => 'Rahul', 'lN' => 'Probe', 'pt' => 'ADULT', 'ssrInfo' => ['BAGGAGE' => $bag, 'MEAL' => [['code' => 'VGML2', 'amount' => 400]]], 'ssrMealInfos' => [['code' => 'VGML', 'desc' => 'Veg meal']]],
                        ['id' => 't2', 'fN' => 'Priya', 'lN' => 'Probe', 'pt' => 'ADULT', 'ssrInfo' => ['BAGGAGE' => $bag, 'MEAL' => [['code' => 'VGML2', 'amount' => 400]]], 'ssrSeatInfos' => ['code' => 'X3C', 'seatNo' => '3C']],
                        ['id' => 't3', 'fN' => 'Aarav', 'lN' => 'Probe', 'pt' => 'INFANT', 'ssrInfo' => ['BAGGAGE' => $bag]],
                    ]]],
                ['id' => 'S2', 'fD' => ['aI' => ['code' => '6E'], 'fN' => '202'], 'da' => ['code' => 'BLR'], 'aa' => ['code' => 'COK'], 'dt' => '2026-10-21T10:00', 'at' => '2026-10-21T11:10',
                    'bI' => ['tI' => [
                        ['id' => 't1b', 'fN' => 'Rahul', 'lN' => 'Probe', 'pt' => 'ADULT', 'ssrInfo' => ['BAGGAGE' => [['code' => 'B15'], ['code' => 'B20']], 'MEAL' => [['code' => 'VGML2', 'amount' => 400]]]],
                        ['id' => 't2b', 'fN' => 'Priya', 'lN' => 'Probe', 'pt' => 'ADULT', 'ssrInfo' => ['BAGGAGE' => [['code' => 'B15'], ['code' => 'B20']]]],
                    ]]],
            ]]], 'status' => ['success' => true]]),
            '*/ancillaries/fetch/seat' => Http::response(['tripSeatMap' => ['tripSeat' => ['S1' => ['sData' => ['row' => 1, 'column' => 1], 'sInfo' => [
                ['seatNo' => '1A', 'code' => 'S1A', 'amount' => 300, 'isBooked' => false, 'seatPosition' => ['row' => 1, 'column' => 1]],
            ]]]], 'status' => ['success' => true]]),
        ]);
    }

    public function test_fetch_options_skips_infants_and_flags_already_added_meal_and_seat(): void
    {
        $this->fakeSsr();
        $groups = app(FlightAncillaryService::class)->fetchOptions($this->booking());

        $s1 = $groups[0]['segments'][0]['travellers'];
        $this->assertSame(['t1', 't2'], array_column($s1, 'id'), 'infant must be excluded');
        $this->assertCount(1, $s1[0]['alreadyMeal']);
        $this->assertSame('3C', $s1[1]['alreadySeat'][0]['seatNo'], 'object-shaped ssrSeatInfos normalised');
        $this->assertSame([], $s1[0]['alreadySeat']);
    }

    public function test_connecting_baggage_is_sent_per_traveller_on_every_segment_and_repeat_meal_seat_ignored(): void
    {
        $this->fakeSsr();
        $svc = app(FlightAncillaryService::class);
        $groups = $svc->fetchOptions($this->booking());

        $built = $svc->buildSsrPayload($groups, [
            'S1' => [
                't1' => ['baggage' => 'B15', 'meal' => 'VGML2'], // meal already added -> ignored
                't2' => ['baggage' => 'B20', 'seat' => 'S1A'],   // seat already added -> ignored
            ],
        ]);

        $this->assertSame(2100.0, $built['total']);
        $this->assertSame([
            ['id' => 'S1', 'bI' => ['tI' => [
                ['id' => 't1', 'sbi' => ['code' => 'B15', 'amount' => 900.0]],
                ['id' => 't2', 'sbi' => ['code' => 'B20', 'amount' => 1200.0]],
            ]]],
            ['id' => 'S2', 'bI' => ['tI' => [
                ['id' => 't1b', 'sbi' => ['code' => 'B15', 'amount' => 0]],
                ['id' => 't2b', 'sbi' => ['code' => 'B20', 'amount' => 0]],
            ]]],
        ], $built['segmentInfos']);
    }

    public function test_successful_earlier_addition_blocks_repeat_meal(): void
    {
        $this->fakeSsr();
        // Live SSRAmendmentDetails shape (2026-10-01): segment under `sI`,
        // travellers identified by name only.
        $booking = $this->booking(['flight_ssr_confirmed' => ['AM1' => ['status' => 'SUCCESS', 'details' => [
            ['sI' => 'S2', 'tI' => [['fN' => 'Rahul', 'lN' => 'Probe', 'ssrMealInfos' => [['code' => 'VGML2']]]]],
        ]]]]);
        $s2 = app(FlightAncillaryService::class)->fetchOptions($booking)[0]['segments'][1]['travellers'];
        $this->assertCount(1, $s2[0]['alreadyMeal']);
        $this->assertSame([], $s2[1]['alreadyMeal']);
    }

    public function test_seat_already_on_booking_details_blocks_a_second_seat(): void
    {
        $this->fakeSsr();
        // Live: Fetch SSR omits seats, Booking Details lists them by "FROM-TO".
        Http::fake(['*/booking-details' => Http::response(['itemInfos' => ['AIR' => ['travellerInfos' => [
            ['id' => 1, 'fN' => 'Rahul', 'lN' => 'Probe', 'ssrSeatInfos' => ['DEL-BLR' => ['code' => '4A', 'amount' => 800]]],
        ]]], 'status' => ['success' => true]])]);
        $svc = app(FlightAncillaryService::class);
        $groups = $svc->fetchOptions($this->booking());

        $this->assertSame('4A', $groups[0]['segments'][0]['travellers'][0]['alreadySeat'][0]['code']);
        $built = $svc->buildSsrPayload($groups, ['S1' => ['t1' => ['seat' => 'S1A']]]);
        $this->assertNull($built, 'second seat for the same traveller/segment is not sent');
    }

    public function test_infant_is_cancelled_with_its_adult_and_never_alone(): void
    {
        $booking = $this->booking();
        $svc = app(FlightBookingService::class);

        $this->assertSame([0 => 2], $svc->infantPartners($booking->flight_segments_payload['travellerInfo']));

        $rahul = $svc->cancellationScope($booking, 'travellers', [], [0]);
        $this->assertSame(['Rahul', 'Aarav'], array_column($rahul['trips'][0]['travellers'], 'fn'));
        $this->assertSame(['ADULT' => 1, 'INFANT' => 1], $rahul['paxCounts']);

        $priya = $svc->cancellationScope($booking, 'travellers', [], [1]);
        $this->assertSame(['Priya'], array_column($priya['trips'][0]['travellers'], 'fn'));

        $this->assertNull($svc->cancellationScope($booking, 'travellers', [], [2]), 'infant alone is not a valid scope');

        $html = $this->get("/booking/{$booking->reference}/cancel")->getContent();
        $this->assertStringContainsString('+ infant Aarav Probe', $html);
        $this->assertStringNotContainsString('value="2"', $html);
    }

    public function test_reschedule_breakdown_adds_up_when_rssr_is_negative_and_unsupported_airline_says_so(): void
    {
        $booking = $this->booking(['pax_infants' => 0]);
        Http::fake([
            '*/booking-details' => Http::response(['itemInfos' => ['AIR' => ['travellerInfos' => [['id' => 1, 'pnrDetails' => ['DEL-COK' => 'ABC123']]]]], 'status' => ['success' => true]]),
            '*/reissue/poll/searchquery-list' => Http::sequence()
                ->push(['searchIds' => ['5-R-1'], 'requestIds' => ['1'], 'status' => ['success' => true]])
                ->push(['status' => ['success' => false, 'httpStatus' => 400], 'errors' => [['errCode' => '1157', 'message' => 'No matching reissue configuration found for the given supplier']]], 400),
            // Live values (IX, 2026-10-01): RSSR arrives negative.
            '*/reissue/poll/search' => Http::response(['searchResult' => ['tripInfos' => ['ONWARD' => [[
                'sI' => [['fD' => ['aI' => ['code' => 'IX', 'name' => 'Air India Express'], 'fN' => '1692'], 'duration' => 175, 'da' => ['code' => 'DEL'], 'aa' => ['code' => 'COK'], 'dt' => '2026-10-26T10:25', 'at' => '2026-10-26T13:20']],
                'totalPriceList' => [['id' => 'P1', 'fd' => ['ADULT' => [
                    'fC' => ['BF' => 1800, 'TAF' => 83, 'AFS' => 3000, 'RSSR' => -2375, 'TF' => 2508],
                    'afC' => ['AFS' => ['ARF' => 3000]],
                ]]]],
            ]]]], 'status' => ['success' => true]]),
        ]);
        $svc = app(\App\Services\FlightReissueService::class);

        $bd = $svc->search($booking, 0, '2026-10-26')['options'][0]['breakdown'];
        $this->assertSame(['fareDifference' => 3766.0, 'airlineFee' => 6000.0, 'serviceFee' => 0.0, 'ancillaryRefund' => 4750.0, 'total' => 5016.0], $bd);
        $this->assertSame($bd['total'], $bd['fareDifference'] + $bd['airlineFee'] + $bd['serviceFee'] - $bd['ancillaryRefund']);

        $unsupported = $svc->search($booking, 0, '2026-10-26');
        $this->assertFalse($unsupported['success']);
        $this->assertStringContainsString('Online rescheduling isn\'t available for this airline', $unsupported['message']);
    }

    public function test_partly_rejected_extras_refund_the_rejected_part(): void
    {
        $booking = $this->booking(['flight_ssr_status' => 'pending', 'flight_ssr_amount_paid' => 2100]);
        $pay = $this->payment($booking, 2100, 'flight_ssr', 'pay_ssr_1');
        $svc = app(FlightAncillaryService::class);

        $svc->finalizeAmendment($booking, 'A1', ['amendmentStatus' => 'SUCCESS', 'totalAmount' => 1200], ['A1', 'A2'], $pay->id);
        $this->assertSame('pending', $booking->fresh()->flight_ssr_status);
        $svc->finalizeAmendment($booking, 'A2', ['amendmentStatus' => 'REJECTED', 'totalAmount' => 900], ['A1', 'A2'], $pay->id);
        $svc->finalizeAmendment($booking, 'A2', ['amendmentStatus' => 'REJECTED', 'totalAmount' => 900], ['A1', 'A2'], $pay->id); // duplicate job

        $this->assertCount(1, $this->razorpay->refunds);
        $this->assertSame(90000, $this->razorpay->refunds[0]['amount']);
        $this->assertSame('partially_confirmed', $booking->fresh()->flight_ssr_status);
        $this->assertEquals(1200, $booking->fresh()->flight_ssr_amount_paid);
        $this->assertSame('partially_refunded', $pay->fresh()->status);
    }

    public function test_fully_rejected_extras_refund_everything(): void
    {
        $booking = $this->booking(['flight_ssr_status' => 'pending', 'flight_ssr_amount_paid' => 2100]);
        $pay = $this->payment($booking, 2100, 'flight_ssr', 'pay_ssr_2');

        app(FlightAncillaryService::class)->finalizeAmendment($booking, 'A1', ['amendmentStatus' => 'REJECTED'], ['A1'], $pay->id);

        $this->assertSame(210000, $this->razorpay->refunds[0]['amount']);
        $this->assertSame('refunded', $booking->fresh()->flight_ssr_status);
        $this->assertSame('refunded', $pay->fresh()->status);
    }

    public function test_cancel_quote_endpoint_scales_per_pax_charges_to_what_guest_paid(): void
    {
        $booking = $this->booking();
        $this->payment($booking, 11000, 'booking', 'pay_fare_q');
        Http::fake(['*/amendment/amendment-charges' => Http::response(['bookingId' => 'x', 'trips' => [[
            'src' => 'DEL', 'dest' => 'COK', 'amendmentInfo' => ['ADULT' => ['amendmentCharges' => 1500, 'refundAmount' => 3500, 'totalFare' => 5000]],
        ]], 'status' => ['success' => true]])]);

        $this->postJson(route('flights.cancel.quote', $booking->reference), ['cancel_scope' => 'full'])
            ->assertOk()->assertJson(['quote' => ['charges' => 3300, 'refund' => 7700, 'currency' => 'INR']]);

        $this->postJson(route('flights.cancel.quote', $booking->reference), ['cancel_scope' => 'travellers', 'traveller_indexes' => [1]])
            ->assertOk()->assertJson(['quote' => ['refund' => 3850]]);

        $this->postJson(route('flights.cancel.quote', $booking->reference), ['cancel_scope' => 'travellers'])
            ->assertOk()->assertJson(['quote' => null, 'empty' => true]);

        Http::assertSent(fn ($r) => str_contains($r->url(), 'amendment-charges') && ($r['trips'][0]['travellers'][0]['fn'] ?? null) === 'Priya');
    }

    public function test_cancel_page_renders_refund_card_wired_to_quote_endpoint(): void
    {
        $booking = $this->booking();
        Http::fake(['*/amendment/amendment-charges' => Http::response(['status' => ['success' => false, 'httpStatus' => 400], 'errors' => [['errCode' => '2612']]], 400)]);

        $this->get("/booking/{$booking->reference}/cancel")
            ->assertOk()
            ->assertSee('id="cxQuoteBody"', false)
            ->assertSee(json_encode(route('flights.cancel.quote', $booking->reference)), false)
            ->assertDontSee('Free Same-Day Cancellation Available');
    }

    public function test_cancel_quote_is_private_to_the_booking_owner(): void
    {
        $booking = $this->booking();
        $this->actingAs(User::factory()->create());
        $this->postJson(route('flights.cancel.quote', $booking->reference))->assertForbidden();
    }

    public function test_cancellation_refunds_the_ticket_payment_not_a_later_extras_payment(): void
    {
        $booking = $this->booking(['cancellation_requested_at' => now()]);
        $this->payment($booking, 11000, 'booking', 'pay_fare_c');
        $this->travel(1)->minutes();
        $this->payment($booking, 500, 'flight_ssr', 'pay_ssr_c');

        app(FlightBookingService::class)->finalizeCancellation($booking, ['amendmentStatus' => 'SUCCESS', 'refundableAmount' => 7000]);

        $this->assertSame('pay_fare_c', $this->razorpay->refunds[0]['payment_id']);
        $this->assertSame(770000, $this->razorpay->refunds[0]['amount']);
        $this->assertSame('cancelled', $booking->fresh()->status);
    }

    public function test_reschedule_options_show_full_journey_and_all_traveller_breakdown(): void
    {
        $booking = $this->booking(['pax_infants' => 0]);
        $fd = fn ($tf, $bf) => ['ADULT' => [
            'fC' => ['BF' => $bf, 'TAF' => 12, 'AFS' => 2950, 'RSSR' => 0, 'OTF' => 3509, 'TF' => $tf],
            'afC' => ['AFS' => ['ARF' => 2850, 'CRF' => 100]],
        ]];
        $seg = fn ($from, $to, $dt, $at, $fn, $sN, $cT = null) => array_filter([
            'fD' => ['aI' => ['code' => 'SG', 'name' => 'SpiceJet'], 'fN' => $fn], 'duration' => 100, 'cT' => $cT,
            'da' => ['code' => $from], 'aa' => ['code' => $to], 'dt' => $dt, 'at' => $at, 'sN' => $sN,
        ], fn ($v) => $v !== null);

        Http::fake([
            '*/booking-details' => Http::response(['itemInfos' => ['AIR' => ['travellerInfos' => [
                ['id' => 11, 'pnrDetails' => ['DEL-COK' => 'ABC123']], ['id' => 12, 'pnrDetails' => ['DEL-COK' => 'ABC123']],
            ]]], 'status' => ['success' => true]]),
            '*/reissue/poll/searchquery-list' => Http::response(['searchIds' => ['5-R-1'], 'requestIds' => ['R1'], 'status' => ['success' => true]]), // live shape
            '*/reissue/poll/search' => Http::response(['searchResult' => ['tripInfos' => ['ONWARD' => [
                ['sI' => [$seg('DEL', 'BLR', '2026-10-22T21:00', '2026-10-22T23:40', '101', 0, 60), $seg('BLR', 'COK', '2026-10-23T00:40', '2026-10-23T01:50', '202', 1)],
                    'totalPriceList' => [['id' => 'P-conn', 'fd' => $fd(5364, 2300)]]],
                ['sI' => [$seg('DEL', 'COK', '2026-10-22T16:30', '2026-10-22T19:40', '476', 0)],
                    'totalPriceList' => [['id' => 'P-direct', 'fd' => $fd(3212, 250)]]],
            ]]], 'status' => ['success' => true]]),
        ]);

        $result = app(\App\Services\FlightReissueService::class)->search($booking, 0, '2026-10-22');

        $this->assertSame(['P-direct', 'P-conn'], array_column($result['options'], 'id'), 'sorted cheapest first');
        [$direct, $conn] = $result['options'];
        $this->assertSame(6424.0, $direct['amountPayable'], 'TF x 2 adults');
        $this->assertSame(['fareDifference' => 524.0, 'airlineFee' => 5700.0, 'serviceFee' => 200.0, 'ancillaryRefund' => 0.0, 'total' => 6424.0], $direct['breakdown']);
        $this->assertSame(1, $conn['stops']);
        $this->assertSame('BLR', $conn['via']);
        $this->assertSame('COK', $conn['to']);
        $this->assertSame('2026-10-23T01:50', $conn['arrTime'], 'arrival of the final segment');
        $this->assertSame(260, $conn['duration']);
        $this->assertSame('SG 101, SG 202', $conn['flightNos']);

        $this->post(route('flights.reissue.search', $booking->reference), ['leg_index' => 0, 'new_date' => now()->addDays(21)->toDateString()])
            ->assertOk()
            ->assertSee('1 stop via BLR')
            ->assertSee('<sup>+1</sup>', false)
            ->assertSee('Airline reschedule fee')
            ->assertDontSee('pass this exact amount')
            ->assertDontSee("TripJack's reissue fee");
    }

    public function test_reschedule_with_no_flights_on_date_is_not_an_error(): void
    {
        $booking = $this->booking();
        Http::fake([
            '*/booking-details' => Http::response(['itemInfos' => ['AIR' => ['travellerInfos' => [['id' => 1, 'pnrDetails' => ['DEL-COK' => 'ABC123']]]]], 'status' => ['success' => true]]),
            '*/reissue/poll/searchquery-list' => Http::response(['searchIds' => ['5-R-9'], 'requestIds' => ['9'], 'status' => ['success' => true]]),
            // Live response, 2026-10-01:
            '*/reissue/poll/search' => Http::response(['status' => ['success' => false, 'httpStatus' => 400], 'errors' => [['errCode' => '1148', 'message' => 'No reissue results found for the given booking']]], 400),
        ]);

        $this->post(route('flights.reissue.search', $booking->reference), ['leg_index' => 0, 'new_date' => now()->addDays(21)->toDateString()])
            ->assertOk()
            ->assertSee('No flights found for this date')
            ->assertDontSee('couldn&#039;t fetch', false);
    }

    public function test_same_partial_cancellation_cannot_be_submitted_twice(): void
    {
        \Illuminate\Support\Facades\Queue::fake();
        Http::fake(['*/submit-amendment' => Http::sequence()
            ->push(['status' => ['success' => false, 'httpStatus' => 400], 'errors' => [['errCode' => '2540', 'message' => 'x']]], 400)
            ->push(['amendmentId' => 'AM1', 'status' => ['success' => true]])
            ->push(['amendmentId' => 'AM2', 'status' => ['success' => true]])]);
        $booking = $this->booking();
        $svc = app(FlightBookingService::class);
        $trips = $svc->cancellationScope($booking, 'travellers', [], [1])['trips'];

        $this->assertFalse($svc->submitCancellation($booking, $trips)['success'], 'TripJack refused');
        $this->assertTrue($svc->submitCancellation($booking, $trips)['success'], 'claim released after a refusal, so a retry works');
        $second = $svc->submitCancellation($booking, $trips);
        $this->assertFalse($second['success']);
        $this->assertStringContainsString('already being processed', $second['message']);
        Http::assertSentCount(2);

        $other = $svc->cancellationScope($booking, 'travellers', [], [0])['trips'];
        $this->assertTrue($svc->submitCancellation($booking, $other)['success'], 'a different traveller is a different request');
    }

    public function test_unresolved_cancellation_keeps_checking_slowly_then_stops(): void
    {
        \Illuminate\Support\Facades\Queue::fake();
        Http::fake(['*/amendment-details' => Http::response(['amendmentStatus' => 'REQUESTED', 'status' => ['success' => true]])]);
        $booking = $this->booking(['cancellation_requested_at' => now()]);
        $run = fn ($attempt) => app()->call([new \App\Jobs\PollFlightAmendmentJob($booking->id, 'AM9', $attempt), 'handle']);

        $run(3);
        \Illuminate\Support\Facades\Queue::assertPushed(\App\Jobs\PollFlightAmendmentJob::class, fn ($j) => $j->delay->lt(now()->addMinute()));
        $this->assertNull($booking->fresh()->admin_note);

        $run(6);
        $this->assertStringContainsString('follow up with TripJack support', $booking->fresh()->admin_note);
        \Illuminate\Support\Facades\Queue::assertPushed(\App\Jobs\PollFlightAmendmentJob::class, fn ($j) => $j->delay->gt(now()->addMinutes(29)));

        $pushedBefore = count(\Illuminate\Support\Facades\Queue::pushed(\App\Jobs\PollFlightAmendmentJob::class));
        $run(54);
        $this->assertCount($pushedBefore, \Illuminate\Support\Facades\Queue::pushed(\App\Jobs\PollFlightAmendmentJob::class), 'stops after 24h');
        $this->assertStringContainsString('automatic checking has stopped', $booking->fresh()->admin_note);

        $booking->update(['cancellation_requested_at' => now()->subMinutes(15)]);
        $this->get("/booking/{$booking->reference}")->assertSee('taking longer than usual');
    }

    public function test_second_partial_cancellation_still_refunds_against_partially_refunded_payment(): void
    {
        $booking = $this->booking();
        $pay = $this->payment($booking, 11000, 'booking', 'pay_fare_p');
        $pay->update(['status' => 'partially_refunded', 'refund_amount' => 3850]);

        app(FlightBookingService::class)->finalizeCancellation($booking, ['amendmentStatus' => 'SUCCESS', 'refundableAmount' => 3500], isFullBooking: false);

        $this->assertSame(385000, $this->razorpay->refunds[0]['amount']);
        $this->assertEquals(7700, $pay->fresh()->refund_amount);
    }
}
