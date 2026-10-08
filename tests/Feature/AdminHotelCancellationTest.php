<?php

namespace Tests\Feature;

use App\Filament\Resources\Bookings\Pages\ListBookings;
use App\Http\Controllers\FrontendController;
use App\Models\Admin;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\User;
use App\Services\Booking\BookingCancellationService;
use App\Services\Payment\RazorpayService;
use App\Services\TripJack\TripJackClient;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\Doubles\FakeRazorpayService;
use Tests\TestCase;

/**
 * Hotel "Cancel & Refund" in the admin: the amount staff choose survives a
 * slow TripJack, running it again finishes the cancellation instead of
 * being refused, the refund is capped at what was paid, and a cancellation
 * finished from two places is only refunded once.
 */
class AdminHotelCancellationTest extends TestCase
{
    use RefreshDatabase;

    protected FakeRazorpayService $razorpay;

    protected function setUp(): void
    {
        parent::setUp();
        $this->razorpay = new FakeRazorpayService();
        $this->app->instance(RazorpayService::class, $this->razorpay);
        $admin = Admin::forceCreate(['name' => 'Owner', 'email' => 'owner@example.com', 'password' => bcrypt('x'), 'role' => 'Super Admin', 'status' => 'Active']);
        $this->actingAs($admin, 'admin');
        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    protected function hotelBooking(array $extra = []): Booking
    {
        $booking = Booking::forceCreate(array_merge([
            'reference' => 'TYT'.strtoupper(substr(md5(uniqid()), 0, 8)),
            'user_id' => User::factory()->create()->id,
            'vertical' => 'hotel',
            'status' => 'confirmed',
            'guest_email' => 'guest@example.com',
            'guest_phone' => '9876543210',
            'tripjack_booking_id' => 'TGS'.random_int(100000000, 999999999),
            'check_in' => now()->addDays(30)->toDateString(),
            'check_out' => now()->addDays(32)->toDateString(),
            'pax_adults' => 1, 'pax_children' => 0, 'pax_infants' => 0,
            'lead_guest_name' => 'Asha Rao',
            'base_amount' => 25000, 'total_amount' => 30000, 'tripjack_total_price' => 25000,
            'currency' => 'INR',
        ], $extra));
        Payment::create([
            'booking_id' => $booking->id, 'razorpay_order_id' => 'order_'.uniqid(), 'razorpay_payment_id' => 'pay_'.uniqid(),
            'amount' => 30000, 'currency' => 'INR', 'status' => 'captured', 'purpose' => 'booking',
        ]);

        return $booking;
    }

    protected function details(string $status, float $penalty = 5000): array
    {
        return [
            'order' => ['status' => $status],
            'itemInfos' => ['HOTEL' => ['hInfo' => ['ops' => [['cnp' => ['ifra' => true, 'pd' => [[
                'fdt' => now()->subDay()->toIso8601String(), 'tdt' => now()->addDay()->toIso8601String(), 'am' => $penalty,
            ]]]]]]]],
            'status' => ['success' => true],
        ];
    }

    protected function cancel(Booking $booking, ?float $amount): array
    {
        return app(BookingCancellationService::class)->cancelAndResolve($booking, app(TripJackClient::class), $this->razorpay, $amount);
    }

    public function test_chosen_refund_survives_a_slow_tripjack_and_is_paid_by_the_scheduler(): void
    {
        $booking = $this->hotelBooking();
        Http::fake([
            '*/hotel/cancel-booking/*' => Http::response(['status' => ['success' => true]]),
            '*/hotel/booking-details' => Http::sequence()->push($this->details('CANCELLATION_PENDING'))->push($this->details('CANCELLED')),
        ]);

        $result = $this->cancel($booking, 12000);
        $this->assertTrue($result['success']);
        $this->assertSame('confirmed', $booking->fresh()->status);
        $this->assertEquals(12000, $booking->fresh()->admin_refund_amount);
        $this->assertCount(0, $this->razorpay->refunds);

        // Later, the 5-minute scheduler sees CANCELLED.
        app(FrontendController::class)->refreshHotelBookingStatus($booking->fresh(), app(TripJackClient::class), $this->razorpay);

        $this->assertSame('cancelled', $booking->fresh()->status);
        $this->assertCount(1, $this->razorpay->refunds);
        $this->assertSame(1200000, $this->razorpay->refunds[0]['amount']); // the staff's 12,000, not the automatic 24,000
    }

    public function test_running_it_again_finishes_instead_of_saying_already_in_progress(): void
    {
        $booking = $this->hotelBooking(['cancellation_requested_at' => now()->subHour(), 'admin_refund_amount' => 8000]);
        Http::fake(['*/hotel/booking-details' => Http::response($this->details('CANCELLED'))]);

        $result = $this->cancel($booking, null);

        $this->assertTrue($result['success']);
        $this->assertSame('cancelled', $booking->fresh()->status);
        Http::assertNotSent(fn (Request $r) => str_contains($r->url(), 'cancel-booking')); // not re-sent
        $this->assertSame(800000, $this->razorpay->refunds[0]['amount']);
    }

    public function test_refund_cannot_exceed_what_the_guest_paid(): void
    {
        $booking = $this->hotelBooking();
        Http::fake();

        $result = $this->cancel($booking, 45000);

        $this->assertFalse($result['success']);
        Http::assertNothingSent();
        $this->assertNull($booking->fresh()->cancellation_requested_at);

        Livewire::test(ListBookings::class)
            ->callTableAction('cancelAndRefund', $booking, ['refund_amount' => 45000])
            ->assertHasTableActionErrors(['refund_amount']);
    }

    public function test_a_cancellation_finished_twice_is_refunded_once(): void
    {
        $booking = $this->hotelBooking(['cancellation_requested_at' => now()->subHour()]);
        Http::fake(['*/hotel/booking-details' => Http::response($this->details('CANCELLED'))]);

        $controller = app(FrontendController::class);
        $controller->refreshHotelBookingStatus($booking, app(TripJackClient::class), $this->razorpay);
        $controller->refreshHotelBookingStatus($booking->fresh(), app(TripJackClient::class), $this->razorpay);
        app(BookingCancellationService::class)->finalize($booking, $this->details('CANCELLED'), $this->razorpay);

        $this->assertCount(1, $this->razorpay->refunds);
    }

    public function test_zero_means_no_refund(): void
    {
        $booking = $this->hotelBooking();
        Http::fake([
            '*/hotel/cancel-booking/*' => Http::response(['status' => ['success' => true]]),
            '*/hotel/booking-details' => Http::response($this->details('CANCELLED', 0)),
        ]);

        $this->cancel($booking, 0);

        $this->assertSame('cancelled', $booking->fresh()->status);
        $this->assertCount(0, $this->razorpay->refunds);
    }
}
