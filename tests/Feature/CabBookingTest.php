<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\User;
use App\Services\CabPricingService;
use App\Services\Payment\RazorpayService;
use App\Support\CabSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\Doubles\FakeRazorpayService;
use Tests\TestCase;

class CabBookingTest extends TestCase
{
    use RefreshDatabase;

    protected FakeRazorpayService $razorpay;

    /** @var array<string, mixed> per-endpoint responses for the fake TripJack cab API */
    protected array $tj = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->razorpay = new FakeRazorpayService();
        $this->app->instance(RazorpayService::class, $this->razorpay);
        Mail::fake();
        config([
            'services.tripjack.cab.base_url' => 'https://cabs.test',
            'services.tripjack.cab.agent_id' => '617306',
            'services.tripjack.cab.agent_email' => 'ops@tyt.test',
            'services.tripjack.cab.agent_phone' => '+919999999999',
            'services.tripjack.retry_times' => 1,
        ]);
        CabSettings::save(['markup' => 200]);

        $this->tj = [
            'get-lat-long' => ['success' => true, 'data' => ['location' => ['lat' => 28.55, 'lng' => 77.08], 'address' => ['city' => 'New Delhi', 'country' => 'India', 'postalCode' => '110037']]],
            'quotes' => $this->quotesResponse(),
            'booking' => ['success' => true, 'data' => ['id' => 'TJS8084864058222', 'status' => 'PAYMENT_PENDING', 'totalPrice' => 1463]],
            'payment/create' => ['success' => true, 'message' => 'ok'],
            'booking/details' => ['success' => true, 'data' => [['order' => ['bookingId' => 'TJS8084864058222', 'status' => 'PAYMENT_SUCCESS', 'paymentStatus' => 'SUCCESS', 'rideStatus' => 'NOT_STARTED', 'trackingLink' => 'https://tripassist.in/cabs/track?bookingId=abc']]]],
            'amendment:GET' => ['success' => true, 'data' => ['amendment' => ['refundAmount' => 1463, 'amountPaid' => 1463, 'description' => 'Upto 24 hours before departure']]],
            'amendment:POST' => ['success' => true, 'data' => ['id' => 'TJSA1', 'amendStatus' => 'SUCCESS', 'refundAmount' => 1463, 'amountPaid' => 1463]],
        ];
        $this->fakeTripJack();
    }

    protected function fakeTripJack(): void
    {
        Http::fake(function (Request $request) {
            $path = parse_url($request->url(), PHP_URL_PATH);
            $key = match (true) {
                str_contains($path, 'google-places') => 'google-places',
                str_contains($path, 'get-lat-long') => 'get-lat-long',
                str_contains($path, 'quotes') => 'quotes',
                str_contains($path, 'payment/create') => 'payment/create',
                str_contains($path, 'booking/details') => 'booking/details',
                str_ends_with($path, '/booking') => 'booking',
                str_contains($path, 'amendment') => 'amendment:'.$request->method(),
                default => null,
            };
            $response = $this->tj[$key] ?? ['success' => true, 'data' => ['places' => [['id' => 'P1', 'displayLabel' => 'IGI Airport Terminal 3, Delhi']]]];
            if ($response === 'timeout') {
                throw new \Illuminate\Http\Client\ConnectionException('timed out');
            }

            return Http::response($response, $response['success'] ?? true ? 200 : 400);
        });
    }

    protected function quotesResponse(): array
    {
        $policies = ['cancellationPolicy' => [
            ['minHours' => 24, 'refundPercentage' => 100, 'description' => 'Upto 24 hours before departure'],
            ['minHours' => 0, 'refundPercentage' => 0, 'description' => 'Within 24 hours before departure'],
        ], 'inclusions' => ['All inclusive pricing'], 'waitingTime' => '60 Min Free Waiting'];

        return ['success' => true, 'data' => [
            'journeyInfo' => ['journeyType' => 'AIRPORT_TRANSFER', 'tripType' => 'ONEWAY', 'pickupDateTime' => '2026-12-01T09:00:00', 'distance' => '19 Km', 'duration' => 38],
            'routeDetails' => ['isDomestic' => true, 'origin' => ['displayAddress' => 'IGI Airport T3'], 'destination' => ['displayAddress' => 'CP']],
            'quotesInfo' => [
                ['vehicleType' => 'Minivan', 'vehicleCategory' => 'Executive', 'label' => 'Executive Minivan', 'paxCapacity' => '8', 'similarType' => 'Innova Crysta or similar',
                    'quotes' => [['vendorId' => 1, 'quotationId' => 'Q-2', 'quoteChildId' => '486443', 'fareBreakup' => ['totalFare' => 2508, 'totalTax' => 50], 'policies' => $policies, 'paxCount' => 5, 'luggageCount' => 5]]],
                ['vehicleType' => 'Sedan', 'vehicleCategory' => 'Standard', 'label' => 'Standard Sedan', 'paxCapacity' => '3', 'similarType' => 'Toyota Etios or similar', 'vehicleImages' => ['https://s3.test/sedan.png'],
                    'quotes' => [['vendorId' => 1, 'quotationId' => 'Q-1', 'quoteChildId' => '361642', 'fareBreakup' => ['totalFare' => 1463, 'totalTax' => 33], 'policies' => $policies, 'paxCount' => 3, 'luggageCount' => 3]]],
            ],
        ]];
    }

    protected function searchQuery(array $overrides = []): array
    {
        return $overrides + [
            'journey' => 'airport_transfer',
            'trip' => 'oneway',
            'from' => 'IGI Airport Terminal 3, Delhi',
            'from_id' => 'P1',
            'to' => 'Connaught Place, New Delhi',
            'to_id' => 'P2',
            'pickup' => now()->addDays(2)->setTime(9, 0)->format('Y-m-d\TH:i'),
            'passengers' => 3,
        ];
    }

    /** Search, pick the Standard Sedan, fill the passenger form: the booking awaiting payment. */
    protected function bookSedan(User $user): Booking
    {
        $this->actingAs($user);
        $page = $this->get(route('cabs.search', $this->searchQuery()))->assertOk();
        preg_match('/cabs\/review\?search=(\w+)&amp;cab=361642/', $page->getContent(), $m);
        $this->assertNotEmpty($m, 'Book link for the sedan not found on the results page');

        $this->get(route('cabs.review', ['search' => $m[1], 'cab' => '361642']))->assertOk()->assertSee('Standard Sedan');

        $this->post(route('cabs.book'), [
            'search' => $m[1], 'cab' => '361642',
            'first_name' => 'Rahul', 'last_name' => 'Sharma', 'email' => 'rahul@gmail.com',
            'phone_code' => '91', 'phone' => '98765 43210', 'flight_number' => '6E 2134', 'accept' => '1',
        ])->assertRedirect();

        return Booking::where('vertical', 'cab')->latest('id')->firstOrFail();
    }

    protected function pay(Booking $booking): void
    {
        $this->get(route('hotel.payment.show', $booking->reference))->assertOk()->assertSee('Complete Your Booking');
        $payment = $booking->payments()->where('status', 'created')->latest('id')->firstOrFail();

        $this->post('/payment/razorpay/callback', [
            'razorpay_payment_id' => 'pay_'.$payment->id,
            'razorpay_order_id' => $payment->razorpay_order_id,
            'razorpay_signature' => 'sig',
        ])->assertRedirect(route('hotel.booking.confirmation', $booking->reference));
    }

    public function test_price_adds_markup_gst_and_gateway_fee(): void
    {
        $p = CabPricingService::price(1463, 200);

        $this->assertSame(10.0, $p['gst']);
        $this->assertEqualsWithDelta((1463 + 200 + 10) / (1 - 0.0236), $p['total'], 0.01);
        $this->assertEqualsWithDelta($p['total'], $p['supplier'] + $p['markup'] + $p['gst'] + $p['gateway'], 0.011);
    }

    public function test_search_shows_cabs_cheapest_first_with_our_price(): void
    {
        $page = $this->get(route('cabs.search', $this->searchQuery()))->assertOk();

        $price = CabPricingService::price(1463, 200)['total'];
        $page->assertSeeInOrder(['Standard Sedan', 'Executive Minivan'])
            ->assertSee(number_format($price))
            ->assertSee('Free cancellation up to 24 hours before pickup');

        Http::assertSent(fn (Request $r) => str_contains($r->url(), '/cabs/v2/quotes')
            && $r['journeyType'] === 'airport_transfer' && $r['tripType'] === 'oneway' && $r['passengers'] === 3
            && $r['origin']['lat'] === '28.55' && $r['origin']['displayAddress'] === 'IGI Airport Terminal 3, Delhi');
    }

    public function test_round_trip_search_sends_return_date_and_doubles_markup(): void
    {
        $return = now()->addDays(4)->setTime(18, 0)->format('Y-m-d\TH:i');
        $this->get(route('cabs.search', $this->searchQuery(['trip' => 'roundtrip', 'return' => $return])))->assertOk()
            ->assertSee(number_format(CabPricingService::price(1463, 400)['total']));

        Http::assertSent(fn (Request $r) => str_contains($r->url(), '/quotes') && $r['tripType'] === 'roundtrip' && isset($r['returnDate']));
    }

    public function test_pickup_too_soon_is_refused_before_calling_tripjack(): void
    {
        $this->get(route('cabs.search', $this->searchQuery(['pickup' => now()->addHour()->format('Y-m-d\TH:i')])))
            ->assertRedirect()->assertSessionHas('booking_error');

        Http::assertNothingSent();
    }

    public function test_guest_pays_then_cab_is_booked_and_paid_from_wallet(): void
    {
        $booking = $this->bookSedan(User::factory()->create());

        $expected = CabPricingService::price(1463, 200);
        $this->assertSame('pending_payment', $booking->status);
        $this->assertEquals($expected['total'], (float) $booking->total_amount);
        $this->assertEquals(200, (float) $booking->margin_amount);

        $this->pay($booking);
        $booking->refresh();

        $this->assertSame('confirmed', $booking->status);
        $this->assertSame('TJS8084864058222', $booking->tripjack_booking_id);
        $this->assertSame('NOT_STARTED', $booking->cab_details['live']['rideStatus']);
        $this->assertEquals($expected['total'] * 100, $this->razorpay->orders[0]['amount']);

        Http::assertSent(fn (Request $r) => str_ends_with($r->url(), '/cabs/v2/booking')
            && $r['quotationInfo']['quoteId'] === 'Q-1' && $r['quotationInfo']['childQuoteId'] === '361642'
            && $r['pricingInfo']['grossAmount'] === '1463.00' && $r['pricingInfo']['netAmount'] === '1430.00'
            && $r['passengerDetail']['phone'] === '+919876543210' && $r['passengerDetail']['flightDetails']['number'] === '6E2134'
            && $r['agentId'] === 617306 && $r['consent'] === 'yes' && $r['routeDetail']['isDomestic'] === true);
        Http::assertSent(fn (Request $r) => str_contains($r->url(), '/payment/create')
            && $r['bookingId'] === 'TJS8084864058222' && $r['amount'] == 1463 && $r['payUserId'] === '617306' && $r['paymentMedium'] === 'WALLET');

        $this->get(route('hotel.booking.confirmation', $booking->reference))->assertOk()
            ->assertSee('Your cab is booked')->assertSee('Track Driver')->assertSee('Cancel Booking');
    }

    public function test_tripjack_refusing_the_booking_refunds_the_guest(): void
    {
        $this->tj['booking'] = ['success' => false, 'error' => ['code' => '400', 'message' => 'Quote expired']];
        $booking = $this->bookSedan(User::factory()->create());

        $this->pay($booking);
        $booking->refresh();

        $this->assertSame('refunded', $booking->status);
        $this->assertCount(1, $this->razorpay->refunds);
        $this->assertStringContainsString('Quote expired', $booking->admin_note);
        Http::assertNotSent(fn (Request $r) => str_contains($r->url(), '/payment/create'));
    }

    public function test_higher_tripjack_price_is_not_paid_and_guest_is_refunded(): void
    {
        $this->tj['booking']['data']['totalPrice'] = 1900;
        $booking = $this->bookSedan(User::factory()->create());

        $this->pay($booking);

        $this->assertSame('refunded', $booking->fresh()->status);
        Http::assertNotSent(fn (Request $r) => str_contains($r->url(), '/payment/create'));
    }

    public function test_wallet_payment_timeout_goes_to_staff_without_refund(): void
    {
        $this->tj['payment/create'] = 'timeout';
        $booking = $this->bookSedan(User::factory()->create());

        $this->pay($booking);
        $booking->refresh();

        $this->assertSame('failed_needs_review', $booking->status);
        $this->assertSame([], $this->razorpay->refunds);

        // TripJack still shows it unpaid: the guest is told we're on it.
        $this->tj['booking/details']['data'][0]['order']['status'] = 'PAYMENT_PENDING';
        $this->tj['booking/details']['data'][0]['order']['paymentStatus'] = 'PENDING';
        $this->get(route('hotel.booking.confirmation', $booking->reference))->assertSee('We\'re confirming your cab');
        $this->assertSame('failed_needs_review', $booking->fresh()->status);

        // Staff pay it in TripJack's dashboard; the next check confirms it.
        $this->tj['booking/details']['data'][0]['order']['paymentStatus'] = 'SUCCESS';
        $this->travel(3)->minutes();
        $this->get(route('hotel.booking.confirmation', $booking->reference))->assertSee('Your cab is booked');
        $this->assertSame('confirmed', $booking->fresh()->status);
    }

    public function test_second_payment_callback_books_only_once(): void
    {
        $booking = $this->bookSedan(User::factory()->create());
        $this->pay($booking);
        $payment = $booking->payments()->first();

        app(\App\Services\CabBookingService::class)->confirmAfterPayment($payment);

        $this->assertCount(1, Http::recorded(fn (Request $r) => str_ends_with($r->url(), '/cabs/v2/booking')));
        $this->assertCount(1, Http::recorded(fn (Request $r) => str_contains($r->url(), '/payment/create')));
        $this->assertSame([], $this->razorpay->refunds);
    }

    public function test_guest_cancels_and_gets_refund_without_gateway_fee(): void
    {
        $booking = $this->bookSedan(User::factory()->create());
        $this->pay($booking);
        $booking->refresh();

        $this->get(route('hotel.booking.cancel.show', $booking->reference))->assertOk()->assertSee('Cancel your cab?');
        $this->post(route('hotel.booking.cancel', $booking->reference), ['confirm' => '1'])
            ->assertRedirect(route('hotel.booking.confirmation', $booking->reference));

        $booking->refresh();
        $expected = round((float) $booking->total_amount - (float) $booking->razorpay_recovery, 2);
        $this->assertSame('cancelled', $booking->status);
        $this->assertSame((int) round($expected * 100), $this->razorpay->refunds[0]['amount']);
        $this->assertEquals($expected, (float) $booking->payments()->first()->refund_amount);
    }

    public function test_no_refund_within_24_hours_policy(): void
    {
        $this->tj['amendment:POST']['data']['refundAmount'] = 0;
        $booking = $this->bookSedan(User::factory()->create());
        $this->pay($booking);

        $this->post(route('hotel.booking.cancel', $booking->reference), ['confirm' => '1']);

        $booking->refresh();
        $this->assertSame('cancelled', $booking->status);
        $this->assertSame([], $this->razorpay->refunds);
        $this->assertStringContainsString('no refund', $booking->cancellation_reason);
    }

    public function test_guest_cannot_cancel_when_switched_off(): void
    {
        $booking = $this->bookSedan(User::factory()->create());
        $this->pay($booking);
        CabSettings::save(['allow_guest_cancel' => false]);

        $this->post(route('hotel.booking.cancel', $booking->reference), ['confirm' => '1']);

        $this->assertSame('confirmed', $booking->fresh()->status);
        Http::assertNotSent(fn (Request $r) => str_contains($r->url(), '/amendment') && $r->method() === 'POST');
    }

    public function test_other_users_cannot_see_the_booking(): void
    {
        $booking = $this->bookSedan(User::factory()->create());

        $this->actingAs(User::factory()->create())
            ->get(route('hotel.booking.confirmation', $booking->reference))->assertForbidden();
    }

    public function test_switched_off_booking_hides_search(): void
    {
        CabSettings::save(['booking_enabled' => false]);

        $this->get(route('cabs'))->assertOk()->assertSee(CabSettings::DEFAULT_DISABLED_MESSAGE)->assertDontSee('Search Cabs');
        $this->get(route('cabs.search', $this->searchQuery()))->assertRedirect(route('cabs'));
        Http::assertNothingSent();
    }

    public function test_admin_sees_cab_bookings_settings_and_can_cancel(): void
    {
        $booking = $this->bookSedan(User::factory()->create());
        $this->pay($booking);

        $admin = \App\Models\Admin::create(['name' => 'Owner', 'email' => 'o@example.com', 'password' => 'secret-pass', 'role' => 'Super Admin', 'status' => 'Active']);
        $this->actingAs($admin, 'admin');

        $this->get('/tyt-console/cab-bookings')->assertOk()->assertSee($booking->reference)->assertSee('Confirmed');
        $this->get("/tyt-console/cab-bookings/{$booking->id}")->assertOk()
            ->assertSee('Standard Sedan')->assertSee('Your earning')->assertSee('Check status with TripJack');
        $this->get('/tyt-console/cab-settings')->assertOk()->assertSee('Your earning per cab trip');
        // Cab bookings stay out of the hotel bookings list.
        $this->get('/tyt-console/bookings')->assertOk()->assertDontSee($booking->reference);

        \Livewire\Livewire::test(\App\Filament\Resources\CabBookings\Pages\ViewCabBooking::class, ['record' => $booking->id])
            ->callAction('cancelCab');
        $this->assertSame('cancelled', $booking->fresh()->status);
    }

    public function test_confirmation_email_describes_the_cab(): void
    {
        $booking = $this->bookSedan(User::factory()->create());
        $this->pay($booking);

        $html = (new \App\Mail\BookingUpdateMail($booking->fresh(), \App\Mail\BookingUpdateMail::CONFIRMED))->render();
        $this->assertStringContainsString('Your cab is booked', $html);
        $this->assertStringContainsString('Standard Sedan', $html);
        $this->assertStringContainsString('IGI Airport Terminal 3, Delhi', $html);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.invoice', ['booking' => $booking->fresh()->load('travelers')])->output();
        $this->assertNotEmpty($pdf);
    }

    public function test_airport_pickup_needs_flight_number_and_can_save_traveller(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $page = $this->get(route('cabs.search', $this->searchQuery()))->assertOk();
        preg_match('/cabs\/review\?search=(\w+)&amp;cab=361642/', $page->getContent(), $m);
        $form = ['search' => $m[1], 'cab' => '361642', 'first_name' => 'Rahul', 'last_name' => 'Sharma',
            'email' => 'rahul@gmail.com', 'phone_code' => '91', 'phone' => '9876543210', 'accept' => '1'];

        $this->get(route('cabs.review', ['search' => $m[1], 'cab' => '361642']))->assertOk()
            ->assertSee('Primary Traveller Details')->assertSee('Price Breakup')->assertSee('Pay Now', false);

        $this->post(route('cabs.book'), $form)->assertSessionHasErrors('flight_number');
        $this->assertSame(0, Booking::where('vertical', 'cab')->count());

        $this->post(route('cabs.book'), $form + ['flight_number' => '6E 2134', 'save_traveller' => '1'])->assertRedirect();
        $this->assertSame(1, Booking::where('vertical', 'cab')->count());
        $this->assertDatabaseHas('user_travellers', ['user_id' => $user->id, 'first_name' => 'Rahul', 'last_name' => 'Sharma', 'phone' => '+919876543210']);
    }

    public function test_bags_hide_cabs_too_small_for_the_luggage(): void
    {
        $this->get(route('cabs.search', $this->searchQuery(['bags' => 4])))->assertOk()
            ->assertSee('Executive Minivan')->assertDontSee('Toyota Etios or similar');
    }

    public function test_upcoming_flight_offers_an_airport_pickup_after_landing(): void
    {
        $user = User::factory()->create();
        $arrives = now()->addDays(3)->setTime(10, 50);
        Booking::create([
            'reference' => 'TYTFLIGHT01', 'user_id' => $user->id, 'vertical' => 'flight', 'status' => 'confirmed',
            'lead_guest_name' => 'Rahul Sharma', 'pax_adults' => 2, 'flight_route' => 'DEL-BOM',
            'flight_departure_date' => $arrives->toDateString(), 'base_amount' => 9000, 'tax_amount' => 0, 'total_amount' => 9000, 'currency' => 'INR',
            'check_in' => $arrives->toDateString(), 'check_out' => $arrives->toDateString(),
            'flight_legs' => [['src' => 'DEL', 'dest' => 'BOM']],
            'flight_itinerary' => ['segments' => [['from' => 'DEL', 'to' => 'BOM', 'toCity' => 'Mumbai', 'toAirport' => 'Chhatrapati Shivaji', 'arrives' => $arrives->format('Y-m-d\TH:i')]]],
        ]);

        $page = $this->actingAs($user)->get(route('cabs'))->assertOk()
            ->assertSee('Your upcoming flights')->assertSee('TYTFLIGHT01')->assertSee('Add Cab');

        // Pickup at the arrival airport, 30 minutes after landing (10:50 → 11:30).
        $html = urldecode(html_entity_decode($page->getContent()));
        $this->assertStringContainsString('from=Chhatrapati Shivaji Airport, Mumbai', $html);
        $this->assertStringContainsString('pickup='.$arrives->format('Y-m-d').'T11:30', $html);
        $this->assertStringContainsString('passengers=2', $html);

        $this->get(route('cabs'))->assertOk(); // guests see no flight table
        auth()->logout();
        $this->get(route('cabs'))->assertDontSee('Your upcoming flights');
    }

    public function test_place_suggestions(): void
    {
        $this->getJson(route('cabs.places', ['q' => 'igi']))->assertOk()
            ->assertJsonPath('places.0.label', 'IGI Airport Terminal 3, Delhi');
    }
}
