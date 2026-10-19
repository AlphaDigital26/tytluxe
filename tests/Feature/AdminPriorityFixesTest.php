<?php

namespace Tests\Feature;

use App\Filament\Resources\PackageBookings\PackageBookingResource;
use App\Filament\Resources\Packages\Pages\CreatePackage;
use App\Filament\Resources\Packages\Pages\EditPackage;
use App\Models\Admin;
use App\Models\Booking;
use App\Models\Enquiry;
use App\Models\Package;
use App\Models\PackageDeparture;
use App\Models\User;
use App\Services\PackageBookingService;
use App\Services\Payment\RazorpayService;
use App\Support\DashboardData;
use App\Support\PackageBookingStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\Doubles\FakeRazorpayService;
use Tests\TestCase;

/** Priority-1 fixes from the admin review (17 Oct 2026). */
class AdminPriorityFixesTest extends TestCase
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

    protected function admin(string $role = 'Super Admin'): Admin
    {
        return Admin::create(['name' => $role, 'email' => uniqid('staff').'@example.com', 'password' => 'secret-pass', 'role' => $role, 'status' => 'Active']);
    }

    protected function package(): Package
    {
        return Package::create([
            'title' => 'Spiti Valley Group Tour', 'slug' => 'spiti-valley', 'description' => 'Road trip.',
            'tour_type' => 'group', 'duration_nights' => 6, 'price_from' => 20000, 'booking_amount' => 5000,
            'online_booking' => true, 'sharing_prices' => [['label' => 'Triple sharing', 'price' => 20000]], 'is_active' => true,
        ]);
    }

    protected function departure(Package $package, int $daysAhead): PackageDeparture
    {
        return $package->departures()->create(['start_date' => today()->addDays($daysAhead), 'end_date' => today()->addDays($daysAhead + 6), 'total_seats' => 10, 'extra_price' => 0]);
    }

    /** A confirmed, part-paid booking for 2 travellers. */
    protected function partPaidBooking(int $daysAhead = 40): Booking
    {
        $package = $this->package();
        $departure = $this->departure($package, $daysAhead);
        $this->actingAs(User::factory()->create());
        $this->post(route('package.book', $package->slug), [
            'departure_id' => $departure->id, 'option' => 'Triple sharing', 'plan' => 'part',
            'travellers' => [['full_name' => 'Traveller A', 'gender' => 'female', 'age' => 30], ['full_name' => 'Traveller B', 'gender' => 'male', 'age' => 31]],
            'email' => 'guest@gmail.com', 'phone_code' => '91', 'phone' => '9876543210', 'terms' => '1',
        ])->assertRedirect();
        $booking = Booking::where('vertical', 'package')->firstOrFail();
        $this->get(route('hotel.payment.show', $booking->reference))->assertOk();
        $payment = $booking->payments()->where('status', 'created')->firstOrFail();
        $this->post('/payment/razorpay/callback', ['razorpay_payment_id' => 'pay_'.$payment->id, 'razorpay_order_id' => $payment->razorpay_order_id, 'razorpay_signature' => 'sig']);

        return $booking->fresh();
    }

    public function test_offline_balance_counts_as_paid_and_is_never_refunded_through_razorpay(): void
    {
        $booking = $this->partPaidBooking();
        $online = (float) $booking->total_amount;
        $balance = PackageBookingResource::balanceWithGst($booking); // ₹30,000 + 5% GST

        app(PackageBookingService::class)->markBalancePaidOffline($booking, 'Staff', 'UPI ref 4521', $balance);
        $booking->refresh()->load('payments');

        $this->assertFalse($booking->packageBalanceDue());
        $this->assertSame(31500.0, $balance);
        $this->assertSame(round($online + $balance, 2), (float) $booking->total_amount);
        $this->assertSame(round($online + $balance, 2), PackageBookingResource::paidSoFar($booking), '"Guest has paid" includes the offline balance');
        $this->assertSame($online, PackageBookingService::refundableAmount($booking), 'only the online payment can go back through Razorpay');
        $this->assertSame($balance, PackageBookingService::offlinePaidAmount($booking));

        $result = app(PackageBookingService::class)->cancelAndRefund($booking, $online, 'Staff');

        $this->assertTrue($result['success']);
        $this->assertCount(1, $this->razorpay->refunds);
        $onlinePayment = $booking->payments->first(fn ($p) => ! $p->isOffline());
        $this->assertSame($onlinePayment->razorpay_payment_id, $this->razorpay->refunds[0]['payment_id'], 'the refund went to the online payment');
        $this->assertSame('cancelled', $booking->fresh()->status);
    }

    public function test_suggested_refund_follows_the_cancellation_policy(): void
    {
        $booking = $this->partPaidBooking(40);
        $paid = (float) $booking->total_amount;

        $s = PackageBookingService::suggestedRefund($booking);
        $this->assertSame([40, 10], [$s['days'], $s['charge_percent']]);
        $this->assertSame(round($paid * 0.9, 2), $s['refund']);

        $this->travel(17)->days(); // 23 days before departure
        $this->assertSame(25, PackageBookingService::suggestedRefund($booking->fresh())['charge_percent']);

        // Counted from the day the guest asked, not from when staff act.
        $booking->update(['cancellation_requested_at' => now()->subDays(10)]); // asked 33 days before
        $this->assertSame(10, PackageBookingService::suggestedRefund($booking->fresh())['charge_percent']);

        $this->travel(20)->days(); // 3 days before
        $booking->update(['cancellation_requested_at' => null]);
        $s = PackageBookingService::suggestedRefund($booking->fresh());
        $this->assertSame([100, 0.0], [$s['charge_percent'], $s['refund']]);
    }

    public function test_editing_a_title_keeps_the_web_address_but_a_new_package_gets_one(): void
    {
        $this->actingAs($this->admin(), 'admin');
        $package = $this->package();

        Livewire::test(EditPackage::class, ['record' => $package->getRouteKey()])
            ->set('data.title', 'Spiti Valley Group Tour — 6 Nights')
            ->assertSet('data.slug', 'spiti-valley');

        Livewire::test(CreatePackage::class)
            ->set('data.title', 'Goa Getaway 3 Nights')
            ->assertSet('data.slug', 'goa-getaway-3-nights');
    }

    public function test_balance_due_today_or_tomorrow_needs_attention_and_shows_on_the_dashboard(): void
    {
        $booking = $this->partPaidBooking(40);
        $this->actingAs($this->admin(), 'admin');
        $attention = fn () => PackageBookingStatus::applyNeedsAttention(Booking::query()->where('vertical', 'package'))->count();

        $this->assertSame(0, $attention(), 'balance due in 38 days is not urgent');

        $booking->update(['package_balance_due_on' => today()->addDay()]);
        $this->assertSame(1, $attention());

        $item = collect(DashboardData::todo())->firstWhere('icon', '🧳');
        $this->assertNotNull($item, 'the dashboard lists package bookings that need a look');
        $this->assertSame(1, $item['count']);

        $this->get('/tyt-console')->assertOk()->assertSee('Package bookings')->assertSee('Balances due in the next 7 days');
    }

    public function test_support_can_log_and_pick_up_enquiries_and_open_customers(): void
    {
        $support = $this->admin('Support');
        $content = $this->admin('Content');
        $customer = User::factory()->create();
        $unassigned = Enquiry::factory()->create(['assigned_agent_id' => null]);
        $someoneElses = Enquiry::factory()->create(['assigned_agent_id' => $this->admin('Support')->id]);

        $this->assertTrue($support->can('create', Enquiry::class));
        $this->assertTrue($support->can('update', $unassigned));
        $this->assertFalse($support->can('update', $someoneElses));
        $this->assertTrue($support->can('view', $customer));
        $this->assertFalse($content->can('create', Enquiry::class));
        $this->assertFalse($content->can('view', $customer));

        $this->actingAs($support, 'admin')->get('/tyt-console/enquiries/create')->assertOk();
        $this->get('/tyt-console/users/'.$customer->id)->assertOk();
    }
}
