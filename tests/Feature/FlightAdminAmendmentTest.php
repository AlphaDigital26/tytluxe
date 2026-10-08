<?php

namespace Tests\Feature;

use App\Filament\Resources\Bookings\Pages\ListBookings;
use App\Filament\Resources\FlightBookings\Pages\ListFlightBookings;
use App\Jobs\PollFlightAmendmentJob;
use App\Models\Admin;
use App\Models\Booking;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Flight Bookings → "Cancel & refund": a normal cancellation or TripJack's
 * Auto Full Refund (doc: Submit Amendment, type FULL_REFUND, checklist
 * remarks) — both through the flight API, never the hotel one.
 */
class FlightAdminAmendmentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $admin = Admin::forceCreate(['name' => 'Owner', 'email' => 'owner@example.com', 'password' => bcrypt('x'), 'role' => 'Super Admin', 'status' => 'Active']);
        $this->actingAs($admin, 'admin');
        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    protected function flightBooking(array $extra = []): Booking
    {
        return Booking::forceCreate(array_merge([
            'reference' => 'TYT'.strtoupper(substr(md5(uniqid()), 0, 8)),
            'user_id' => User::factory()->create()->id,
            'vertical' => 'flight',
            'status' => 'confirmed',
            'guest_email' => 'guest@example.com',
            'guest_phone' => '+919876543210',
            'tripjack_booking_id' => 'TJS100000000009',
            'flight_route' => 'DEL-BOM',
            'flight_departure_date' => now()->addDays(20)->toDateString(),
            'pax_adults' => 1,
            'pax_children' => 0,
            'pax_infants' => 0,
            'lead_guest_name' => 'Rahul Probe',
            'base_amount' => 5000,
            'total_amount' => 5600,
            'tripjack_total_price' => 5000,
        ], $extra));
    }

    protected function fakeTripJack(): void
    {
        Http::fake([
            '*/booking-details' => Http::response(['order' => ['status' => 'SUCCESS'], 'status' => ['success' => true]]),
            '*/submit-amendment' => Http::response(['bookingId' => 'TJS100000000009', 'amendmentId' => 'AMD1', 'status' => ['success' => true]]),
            '*' => Http::response(['status' => ['success' => false]], 400),
        ]);
    }

    public function test_full_refund_sends_full_refund_type_with_checklist_remarks(): void
    {
        Queue::fake();
        $this->fakeTripJack();
        $booking = $this->flightBooking();

        Livewire::test(ListFlightBookings::class)
            ->callTableAction('cancelFlight', $booking, ['kind' => 'full_refund', 'full_refund_reason' => 'Refund under DGCA policy'])
            ->assertHasNoTableActionErrors();

        Http::assertSent(fn (Request $r) => str_contains($r->url(), 'submit-amendment')
            && $r['type'] === 'FULL_REFUND'
            && $r['remarks'] === 'Refund under DGCA policy'
            && ! isset($r['trips']));
        Queue::assertPushed(PollFlightAmendmentJob::class);
        $this->assertNotNull($booking->fresh()->cancellation_requested_at);
    }

    public function test_full_refund_rejects_reasons_outside_the_checklist(): void
    {
        $this->fakeTripJack();
        $booking = $this->flightBooking();

        Livewire::test(ListFlightBookings::class)
            ->callTableAction('cancelFlight', $booking, ['kind' => 'full_refund', 'full_refund_reason' => 'Guest changed their mind'])
            ->assertHasTableActionErrors(['full_refund_reason']);

        Http::assertNotSent(fn (Request $r) => str_contains($r->url(), 'submit-amendment'));
    }

    public function test_normal_cancel_uses_the_flight_amendment_api(): void
    {
        Queue::fake();
        $this->fakeTripJack();
        $booking = $this->flightBooking();

        Livewire::test(ListFlightBookings::class)
            ->callTableAction('cancelFlight', $booking, ['kind' => 'normal'])
            ->assertHasNoTableActionErrors();

        Http::assertSent(fn (Request $r) => str_contains($r->url(), '/oms/v1/air/amendment/submit-amendment')
            && $r['type'] === 'CANCELLATION');
        Queue::assertPushed(PollFlightAmendmentJob::class);
    }

    public function test_hotel_screen_never_offers_its_cancel_on_a_flight(): void
    {
        $hotel = $this->flightBooking(['vertical' => 'hotel', 'reference' => 'TYTHOTEL01', 'tripjack_booking_id' => 'TJH1']);

        Livewire::test(ListBookings::class)
            ->assertTableActionVisible('cancelAndRefund', $hotel);
        $this->assertFalse(Booking::query()->whereKey($this->flightBooking()->id)->whereIn('id', \App\Filament\Resources\Bookings\BookingResource::getEloquentQuery()->select('id'))->exists());
    }

    public function test_cancel_is_hidden_while_a_cancellation_is_in_progress(): void
    {
        $booking = $this->flightBooking(['cancellation_requested_at' => now()]);

        Livewire::test(ListFlightBookings::class)
            ->assertTableActionHidden('cancelFlight', $booking);
    }
}
