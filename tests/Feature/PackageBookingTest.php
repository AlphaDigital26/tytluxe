<?php

namespace Tests\Feature;

use App\Mail\BookingUpdateMail;
use App\Models\Admin;
use App\Models\Booking;
use App\Models\Package;
use App\Models\PackageDeparture;
use App\Models\User;
use App\Services\PackageBookingService;
use App\Services\PackagePricingService;
use App\Services\Payment\RazorpayService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\Doubles\FakeRazorpayService;
use Tests\TestCase;

class PackageBookingTest extends TestCase
{
    use RefreshDatabase;

    protected FakeRazorpayService $razorpay;

    protected function setUp(): void
    {
        parent::setUp();

        $this->razorpay = new FakeRazorpayService();
        $this->app->instance(RazorpayService::class, $this->razorpay);
        Mail::fake();
    }

    protected function package(array $attributes = []): Package
    {
        return Package::create($attributes + [
            'title' => 'Spiti Valley Group Tour',
            'slug' => 'spiti-valley',
            'description' => 'High-altitude desert road trip.',
            'tour_type' => 'group',
            'duration_nights' => 6,
            'price_from' => 20000,
            'booking_amount' => 5000,
            'online_booking' => true,
            'sharing_prices' => [
                ['label' => 'Triple sharing', 'price' => 20000],
                ['label' => 'Double sharing', 'price' => 23000],
            ],
            'is_active' => true,
        ]);
    }

    protected function departure(Package $package, int $daysAhead = 30, ?int $seats = 10, float $extra = 0): PackageDeparture
    {
        return $package->departures()->create([
            'start_date' => today()->addDays($daysAhead),
            'end_date' => today()->addDays($daysAhead + 6),
            'total_seats' => $seats,
            'extra_price' => $extra,
        ]);
    }

    protected function bookingForm(PackageDeparture $departure, array $overrides = [], int $travellers = 2): array
    {
        return $overrides + [
            'departure_id' => $departure->id,
            'option' => 'Triple sharing',
            'plan' => 'part',
            'travellers' => array_map(fn ($i) => ['full_name' => 'Traveller '.chr(64 + $i), 'gender' => 'female', 'age' => 30], range(1, $travellers)),
            'email' => 'guest@gmail.com',
            'phone_code' => '91',
            'phone' => '9876543210',
            'terms' => '1',
        ];
    }

    protected function pay(Booking $booking, string $path): void
    {
        $this->get($path)->assertOk();
        $payment = $booking->payments()->where('status', 'created')->latest('id')->firstOrFail();

        $this->post('/payment/razorpay/callback', [
            'razorpay_payment_id' => 'pay_'.$payment->id,
            'razorpay_order_id' => $payment->razorpay_order_id,
            'razorpay_signature' => 'sig',
        ])->assertRedirect(route('hotel.booking.confirmation', $booking->reference));
    }

    public function test_quote_adds_gst_and_gateway_fee_per_payment(): void
    {
        $package = $this->package();
        $departure = $this->departure($package, extra: 1000);

        $quote = PackagePricingService::quote($package, $departure, 20000, 2, 'part');

        $this->assertSame(21000.0, $quote['per_person']);
        $this->assertSame(42000.0, $quote['subtotal']);
        $this->assertSame(2100.0, $quote['gst']);
        $this->assertSame('part', $quote['plan']);
        // Booking amount ₹5,000 × 2 + 5% GST, grossed up for Razorpay's 2.36%.
        $this->assertSame(10000.0, $quote['now']['base']);
        $this->assertSame(500.0, $quote['now']['gst']);
        $this->assertSame(round(10500 / (1 - 0.0236), 2), $quote['now']['total']);
        $this->assertSame(32000.0, $quote['balance']['base']);

        $full = PackagePricingService::quote($package, $departure, 20000, 2, 'full');
        $this->assertSame(round(44100 / (1 - 0.0236), 2), $full['now']['total']);
        $this->assertSame(0.0, $full['balance']['total']);
    }

    public function test_guest_books_with_booking_amount_then_pays_balance(): void
    {
        $package = $this->package();
        $departure = $this->departure($package);
        $user = User::factory()->create();

        $this->get(route('package.details', $package->slug))->assertOk()->assertSee('Book Now')->assertSee('Triple sharing');
        $this->actingAs($user)->get(route('package.checkout', ['slug' => $package->slug, 'departure' => $departure->id]))
            ->assertOk()->assertSee('Choose your travel date')->assertSee('10 seats left');

        $this->post(route('package.book', $package->slug), $this->bookingForm($departure))
            ->assertRedirect();

        $booking = Booking::where('vertical', 'package')->firstOrFail();
        $this->assertSame('pending_payment', $booking->status);
        $this->assertSame($departure->id, $booking->package_departure_id);
        $this->assertSame(2, $booking->travelers()->count());
        $this->assertSame(30000.0, (float) $booking->package_balance_amount);
        $this->assertSame($departure->start_date->copy()->subDays(2)->toDateString(), $booking->package_balance_due_on->toDateString());
        $this->assertSame(8, $departure->fresh()->seatsLeft(), 'seats are held while paying');

        $this->pay($booking, route('hotel.payment.show', $booking->reference));

        $booking->refresh();
        $this->assertSame('confirmed', $booking->status);
        $this->assertTrue($booking->packageBalanceDue());
        Mail::assertQueued(BookingUpdateMail::class, fn ($m) => $m->kind === BookingUpdateMail::CONFIRMED);

        $this->get(route('hotel.booking.confirmation', $booking->reference))
            ->assertOk()->assertSee('Your seats are booked!')->assertSee('Pay Balance Now');

        $this->pay($booking, route('package.balance', $booking->reference));

        $booking->refresh();
        $this->assertSame(0.0, (float) $booking->package_balance_amount);
        $this->assertFalse($booking->packageBalanceDue());
        $this->assertSame(2, $booking->payments()->where('status', 'captured')->count());
        $this->assertSame(
            round(PackagePricingService::charge(10000)['total'] + PackagePricingService::charge(30000)['total'], 2),
            round((float) $booking->total_amount, 2),
        );
        Mail::assertQueued(BookingUpdateMail::class, fn ($m) => $m->kind === BookingUpdateMail::BALANCE_PAID);
        $this->get(route('hotel.booking.invoice', $booking->reference))->assertOk();
    }

    public function test_full_payment_leaves_nothing_due(): void
    {
        $package = $this->package();
        $departure = $this->departure($package);
        $this->actingAs(User::factory()->create());

        $this->post(route('package.book', $package->slug), $this->bookingForm($departure, ['plan' => 'full', 'option' => 'Double sharing'], 1));
        $booking = Booking::where('vertical', 'package')->firstOrFail();
        $this->assertSame(round(24150 / (1 - 0.0236), 2), (float) $booking->total_amount);

        $this->pay($booking, route('hotel.payment.show', $booking->reference));

        $booking->refresh();
        $this->assertSame('confirmed', $booking->status);
        $this->assertFalse($booking->packageBalanceDue());
        $this->get(route('package.balance', $booking->reference))->assertRedirect(route('hotel.booking.confirmation', $booking->reference));
    }

    public function test_infants_up_to_three_travel_free_without_a_seat(): void
    {
        $package = $this->package();
        $departure = $this->departure($package, seats: 2);
        $this->actingAs(User::factory()->create());

        $form = $this->bookingForm($departure, ['plan' => 'full', 'travellers' => [
            ['full_name' => 'Mother Rao', 'gender' => 'female', 'age' => 31],
            ['full_name' => 'Father Rao', 'gender' => 'male', 'age' => 34],
            ['full_name' => 'Baby Rao', 'gender' => 'female', 'age' => 0],
            ['full_name' => 'Toddler Rao', 'gender' => 'male', 'age' => 3],
        ]]);
        $this->post(route('package.book', $package->slug), $form);

        $booking = Booking::where('vertical', 'package')->firstOrFail();
        $this->assertSame(2, (int) $booking->pax_adults, 'only paying travellers take seats');
        $this->assertSame(2, (int) $booking->pax_infants);
        $this->assertSame(40000.0, (float) $booking->base_amount, '2 × ₹20,000 — infants free');
        $this->assertSame(2, $booking->travelers()->where('traveler_type', 'infant')->count());
        $this->assertSame('Mother Rao', $booking->lead_guest_name);
        $this->assertSame('2 travellers + 2 infants (free)', $booking->packageTravellersLabel());

        // A 4-year-old pays.
        $p2 = $this->package(['slug' => 'spiti-2']);
        $d2 = $this->departure($p2);
        $this->post(route('package.book', $p2->slug), $this->bookingForm($d2, ['plan' => 'full', 'travellers' => [
            ['full_name' => 'Mother Rao', 'gender' => 'female', 'age' => 31],
            ['full_name' => 'Kid Rao', 'gender' => 'male', 'age' => 4],
        ]]));
        $this->assertSame(2, (int) Booking::where('package_id', $p2->id)->value('pax_adults'));

        // Infants alone can't book.
        $this->post(route('package.book', $p2->slug), $this->bookingForm($d2, ['travellers' => [['full_name' => 'Baby Solo', 'gender' => 'male', 'age' => 1]]]))
            ->assertSessionHas('booking_error');
    }

    public function test_seat_limit_blocks_overbooking(): void
    {
        $package = $this->package();
        $departure = $this->departure($package, seats: 2);
        $this->actingAs(User::factory()->create());

        $this->post(route('package.book', $package->slug), $this->bookingForm($departure, [], 3))
            ->assertSessionHas('booking_error', 'Only 2 seats are left on this departure. Please reduce the number of travellers or choose another date.');
        $this->assertSame(0, Booking::count());

        $this->post(route('package.book', $package->slug), $this->bookingForm($departure, [], 2));
        $this->post(route('package.book', $package->slug), $this->bookingForm($departure, [], 1))
            ->assertSessionHas('booking_error', 'Sorry, this departure date is no longer open for booking. Please choose another date.');
    }

    public function test_payment_after_seats_sold_out_is_refunded(): void
    {
        $package = $this->package();
        $departure = $this->departure($package, seats: 2);
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->post(route('package.book', $package->slug), $this->bookingForm($departure, [], 2));
        $late = Booking::where('vertical', 'package')->firstOrFail();
        $this->get(route('hotel.payment.show', $late->reference))->assertOk();
        $payment = $late->payments()->firstOrFail();

        // The 30-minute hold runs out and someone else books the seats.
        Booking::whereKey($late->id)->update(['created_at' => now()->subHour()]);
        Booking::create([
            'reference' => 'TYTOTHER1', 'user_id' => User::factory()->create()->id, 'vertical' => 'package',
            'package_id' => $package->id, 'package_departure_id' => $departure->id, 'pax_adults' => 2,
            'lead_guest_name' => 'Other', 'base_amount' => 1, 'total_amount' => 1, 'status' => 'confirmed',
        ]);

        $this->post('/payment/razorpay/callback', [
            'razorpay_payment_id' => 'pay_late', 'razorpay_order_id' => $payment->razorpay_order_id, 'razorpay_signature' => 'sig',
        ]);

        $this->assertSame('refunded', $late->fresh()->status);
        $this->assertSame('refunded', $payment->fresh()->status);
        $this->assertCount(1, $this->razorpay->refunds);
    }

    public function test_guest_can_ask_to_cancel_until_seven_days_before(): void
    {
        $package = $this->package();
        $user = User::factory()->create();
        $this->actingAs($user);

        $soon = $this->departure($package, daysAhead: 6);
        $this->post(route('package.book', $package->slug), $this->bookingForm($soon, ['plan' => 'full'], 1));
        $late = Booking::where('package_departure_id', $soon->id)->firstOrFail();
        $this->pay($late, route('hotel.payment.show', $late->reference));
        $this->get(route('hotel.booking.cancel.show', $late->reference))
            ->assertRedirect(route('hotel.booking.confirmation', $late->reference));

        $later = $this->departure($package, daysAhead: 20);
        $this->post(route('package.book', $package->slug), $this->bookingForm($later, ['plan' => 'full'], 1));
        $booking = Booking::where('package_departure_id', $later->id)->firstOrFail();
        $this->pay($booking, route('hotel.payment.show', $booking->reference));

        $this->get(route('hotel.booking.cancel.show', $booking->reference))->assertOk()->assertSee('Cancel this booking?');
        $this->post(route('hotel.booking.cancel', $booking->reference), ['reason' => 'Plans changed', 'confirm' => '1'])
            ->assertSessionHas('booking_success');

        $booking->refresh();
        $this->assertSame('confirmed', $booking->status, 'seats stay booked until the team decides');
        $this->assertNotNull($booking->cancellation_requested_at);
        $this->assertSame([], $this->razorpay->refunds, 'nothing is refunded automatically');
    }

    public function test_admin_cancels_with_chosen_refund(): void
    {
        $package = $this->package();
        $departure = $this->departure($package);
        $this->actingAs(User::factory()->create());
        $this->post(route('package.book', $package->slug), $this->bookingForm($departure, ['plan' => 'full'], 1));
        $booking = Booking::where('vertical', 'package')->firstOrFail();
        $this->pay($booking, route('hotel.payment.show', $booking->reference));

        $result = app(PackageBookingService::class)->cancelAndRefund($booking->fresh(), 15000, 'Owner');

        $this->assertTrue($result['success']);
        $booking->refresh();
        $this->assertSame('cancelled', $booking->status);
        $this->assertSame(1500000, $this->razorpay->refunds[0]['amount']);
        $this->assertSame('partially_refunded', $booking->payments()->first()->status);
        $this->assertSame(10, $departure->fresh()->seatsLeft(), 'cancelled seats are free again');
        Mail::assertQueued(BookingUpdateMail::class, fn ($m) => $m->kind === BookingUpdateMail::CANCELLED);
        $this->assertStringContainsString('has been refunded', $booking->guestCancellationMessage());

        $this->assertFalse(app(PackageBookingService::class)->cancelAndRefund($booking, 1, 'Owner')['success']);
    }

    public function test_unpaid_balance_on_departure_day_is_forfeited(): void
    {
        $package = $this->package();
        $departure = $this->departure($package, daysAhead: 10);
        $this->actingAs(User::factory()->create());
        $this->post(route('package.book', $package->slug), $this->bookingForm($departure, [], 1));
        $booking = Booking::where('vertical', 'package')->firstOrFail();
        $this->pay($booking, route('hotel.payment.show', $booking->reference));

        $this->travel(7)->days(); // 3 days before departure: first reminder
        $this->artisan('packages:balance-check')->assertSuccessful();
        Mail::assertQueued(BookingUpdateMail::class, fn ($m) => $m->kind === BookingUpdateMail::BALANCE_REMINDER);
        $this->assertSame('confirmed', $booking->fresh()->status);

        $this->travel(3)->days(); // departure day, still unpaid
        $this->artisan('packages:balance-check')->assertSuccessful();

        $booking->refresh();
        $this->assertSame('cancelled', $booking->status);
        $this->assertSame([], $this->razorpay->refunds, 'the booking amount is kept');
        $this->assertStringContainsString('no refund is due', $booking->guestCancellationMessage());
    }

    public function test_package_without_online_booking_keeps_whatsapp_flow(): void
    {
        $package = $this->package(['online_booking' => false]);
        $this->departure($package);

        $this->get(route('package.details', $package->slug))->assertOk()->assertSee('wa.me', false)->assertDontSee('pdBookDeparture', false);
        $this->actingAs(User::factory()->create())
            ->get(route('package.checkout', $package->slug))->assertRedirect(route('package.details', $package->slug));
    }

    public function test_admin_package_bookings_screens(): void
    {
        $package = $this->package();
        $departure = $this->departure($package);
        $guest = User::factory()->create();
        $this->actingAs($guest);
        $this->post(route('package.book', $package->slug), $this->bookingForm($departure));
        $booking = Booking::where('vertical', 'package')->firstOrFail();
        $this->pay($booking, route('hotel.payment.show', $booking->reference));

        $admin = Admin::create(['name' => 'Owner', 'email' => 'o@example.com', 'password' => 'secret-pass', 'role' => 'Super Admin', 'status' => 'Active']);
        $this->actingAs($admin, 'admin');

        $this->get('/tyt-console/package-bookings')->assertOk()->assertSee($booking->reference)->assertSee('Booking amount paid, balance due');
        $this->get("/tyt-console/package-bookings/{$booking->reference}")->assertOk()->assertSee('Traveller A')->assertSee('Mark balance as received');
        $this->get('/tyt-console/bookings')->assertOk()->assertDontSee($booking->reference);
        $this->get("/tyt-console/packages/{$package->id}/edit")->assertOk();

        \Livewire\Livewire::test(\App\Filament\Resources\PackageBookings\Pages\ViewPackageBooking::class, ['record' => $booking->reference])
            ->callAction('markBalanceReceived', ['note' => 'UPI ref 123'])
            ->assertHasNoActionErrors();
        $this->assertFalse($booking->fresh()->packageBalanceDue());
    }

    // ── Fixes from the 30-scenario package test (28 Oct 2026) ──────────────

    public function test_sending_the_same_booking_twice_makes_one_booking(): void
    {
        $this->actingAs(User::factory()->create());
        $departure = $this->departure($package = $this->package());

        $first = $this->post("/packages/{$package->slug}/book", $this->bookingForm($departure));
        $second = $this->post("/packages/{$package->slug}/book", $this->bookingForm($departure));

        $this->assertSame(1, Booking::count());
        $second->assertRedirect($first->headers->get('Location'));
        $this->assertSame(8, $departure->fresh()->seatsLeft()); // 2 held, not 4

        // A real change (another traveller) is a new booking.
        $this->post("/packages/{$package->slug}/book", $this->bookingForm($departure, [], 3));
        $this->assertSame(2, Booking::count());
    }

    public function test_a_baby_under_one_can_be_entered_as_age_0(): void
    {
        $this->actingAs(User::factory()->create());
        $departure = $this->departure($package = $this->package());

        $this->get("/packages/{$package->slug}/book")->assertOk()
            ->assertSee('type="number" min="0" max="99"', false)
            ->assertDontSee('min="1" max="99"', false);

        $form = $this->bookingForm($departure, [], 1);
        $form['travellers'][] = ['full_name' => 'Baby Rao', 'gender' => 'female', 'age' => 0];
        $this->post("/packages/{$package->slug}/book", $form)->assertRedirect();
        $this->assertSame(1, Booking::firstOrFail()->pax_infants);
    }

    public function test_a_mistyped_email_is_caught_with_a_suggestion(): void
    {
        $this->actingAs(User::factory()->create());
        $departure = $this->departure($package = $this->package());

        foreach (['ravi@gmial.com' => 'ravi@gmail.com', 'ravi@gmail.con' => 'ravi@gmail.com', 'ravi@yahooo.com' => 'ravi@yahoo.com'] as $typed => $meant) {
            $this->post("/packages/{$package->slug}/book", $this->bookingForm($departure, ['email' => $typed]))
                ->assertSessionHasErrors(['email' => "Please check your email address. Did you mean {$meant}?"]);
        }
        foreach (['ravi@mail.com', 'ravi@ymail.com', 'ravi@acme.co.in', 'ravi@zoho.com'] as $real) {
            $this->assertNull(\App\Rules\BookableEmail::typoFix(substr(strrchr($real, '@'), 1)), $real);
        }
        $this->assertSame(0, Booking::count());
    }

    public function test_too_many_travellers_is_explained_in_plain_words(): void
    {
        $this->actingAs(User::factory()->create());
        $departure = $this->departure($package = $this->package(), 30, 50);

        $this->post("/packages/{$package->slug}/book", $this->bookingForm($departure, [], 21))
            ->assertSessionHasErrors(['travellers' => 'You can book up to 20 travellers at a time. For a bigger group, please contact us.']);
    }
}
