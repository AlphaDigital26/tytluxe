<?php

namespace Tests\Feature;

use App\Filament\Resources\Bookings\Pages\ListBookings;
use App\Filament\Resources\FlightBookings\Pages\ListFlightBookings;
use App\Models\Admin;
use App\Models\Booking;
use App\Models\User;
use App\Services\FlightBookingService;
use App\Services\FlightPricingService;
use App\Support\FlightSettings;
use App\Support\HotelBookingStatus;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Smaller fixes from the 2026-10-07 audit: sign-up links, the 9-passenger
 * limit, "Check status" permissions, notes not resetting the stuck timer,
 * who released a hold, and the markup fixed for a booking in progress.
 */
class AuditFollowUpTest extends TestCase
{
    use RefreshDatabase;

    protected function admin(string $role): Admin
    {
        return Admin::forceCreate(['name' => $role.' person', 'email' => uniqid('a').'@example.com', 'password' => bcrypt('x'), 'role' => $role, 'status' => 'Active']);
    }

    protected function booking(array $extra = []): Booking
    {
        return Booking::forceCreate(array_merge([
            'reference' => 'TYT'.strtoupper(substr(md5(uniqid()), 0, 8)),
            'user_id' => User::factory()->create()->id,
            'vertical' => 'flight',
            'status' => 'confirmed',
            'guest_email' => 'guest@example.com',
            'guest_phone' => '9876543210',
            'tripjack_booking_id' => 'TJS'.random_int(100000000, 999999999),
            'flight_route' => 'DEL-BOM',
            'flight_departure_date' => now()->addDays(20)->toDateString(),
            'pax_adults' => 1, 'pax_children' => 0, 'pax_infants' => 0,
            'lead_guest_name' => 'Rahul Probe',
            'base_amount' => 5000, 'total_amount' => 5600, 'tripjack_total_price' => 5000,
            'currency' => 'INR',
        ], $extra));
    }

    public function test_sign_up_links_open_the_real_terms_and_privacy_pages(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('href="'.route('terms').'"', false)
            ->assertSee('href="'.route('privacy').'"', false);
    }

    public function test_search_never_asks_tripjack_for_more_than_nine_seated_passengers(): void
    {
        Http::fake(['*/air-search-all' => Http::response(['searchResult' => ['tripInfos' => []], 'status' => ['success' => true]])]);

        $this->get(route('flights.search', ['from' => 'DEL', 'to' => 'BOM', 'depart_date' => now()->addDays(30)->toDateString(), 'adults' => 5, 'children' => 9, 'trip_type' => 'oneway']))->assertOk();

        Http::assertSent(fn (Request $r) => $r['searchQuery']['paxInfo']['ADULT'] === 5 && $r['searchQuery']['paxInfo']['CHILD'] === 4);
    }

    public function test_view_only_roles_cannot_press_check_status(): void
    {
        $flight = $this->booking();
        $hotel = $this->booking(['vertical' => 'hotel']);
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        $this->actingAs($this->admin('Analyst'), 'admin');
        Livewire::test(ListFlightBookings::class)->assertTableActionHidden('checkStatus', $flight);
        Livewire::test(ListBookings::class)->assertTableActionHidden('checkStatus', $hotel);

        $this->actingAs($this->admin('Super Admin'), 'admin');
        Livewire::test(ListFlightBookings::class)->assertTableActionVisible('checkStatus', $flight);
    }

    public function test_adding_a_note_does_not_reset_the_taking_too_long_timer(): void
    {
        $booking = $this->booking(['vertical' => 'hotel', 'status' => 'pending_confirmation']);
        Booking::withoutTimestamps(fn () => $booking->forceFill(['updated_at' => now()->subHours(3)])->save());
        $this->assertSame('confirmation_stuck', HotelBookingStatus::key($booking->fresh()));

        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $this->actingAs($this->admin('Super Admin'), 'admin');
        Livewire::test(ListBookings::class)->callTableAction('addNote', $booking, ['note' => 'Chased TripJack by phone.']);

        $this->assertStringContainsString('Chased TripJack by phone.', $booking->fresh()->admin_note);
        $this->assertSame('confirmation_stuck', HotelBookingStatus::key($booking->fresh()));
    }

    public function test_hold_released_by_staff_is_not_recorded_as_the_guest(): void
    {
        Http::fake([
            '*/air/unhold' => Http::response(['status' => ['success' => true]]),
            '*/booking-details' => Http::response(['order' => ['status' => 'UNCONFIRMED'], 'status' => ['success' => true]]),
        ]);
        $booking = $this->booking(['status' => 'on_hold']);

        app(FlightBookingService::class)->releaseHold($booking, 'Priya');

        $booking->refresh();
        $this->assertSame('Hold released by the TYT Luxe team (Priya) before payment.', $booking->cancellation_reason);
        $this->assertStringNotContainsString('You released', $booking->guestCancellationMessage());
    }

    public function test_markup_is_fixed_when_the_guest_picks_the_fare(): void
    {
        $this->actingAs(User::factory()->create());
        $segment = ['id' => '1', 'da' => ['code' => 'DEL'], 'aa' => ['code' => 'BOM'], 'fD' => ['aI' => ['code' => '6E'], 'fN' => '101'], 'dt' => now()->addDays(20)->format('Y-m-d').'T06:00', 'at' => now()->addDays(20)->format('Y-m-d').'T08:10'];
        session(['flight_booking_draft' => [
            'bookingId' => 'TJS200000000001',
            'expiresAt' => now()->addMinutes(10)->timestamp,
            'marginRate' => 0.10, // the markup when the fare was picked
            'context' => ['tripType' => 'oneway', 'from' => 'DEL', 'to' => 'BOM', 'departDate' => now()->addDays(20)->toDateString(), 'returnDate' => '',
                'adults' => 1, 'children' => 0, 'infants' => 0, 'cabinClass' => 'ECONOMY', 'fareType' => 'REGULAR', 'legs' => []],
            'response' => ['bookingId' => 'TJS200000000001', 'tripInfos' => [['sI' => [$segment]]], 'conditions' => [],
                'totalPriceInfo' => ['totalFareDetail' => ['fC' => ['TF' => 5000, 'BF' => 4200, 'TAF' => 800]]]],
        ]]);

        // The client raises the markup while the guest is mid-booking.
        FlightSettings::save(['markup_percent' => 20]);

        $this->post(route('flights.book'), [
            'review_booking_id' => 'TJS200000000001', 'intent' => 'review',
            'contact_email' => 'guest@example.com', 'contact_phone' => '9876543210',
            'travellers' => [['title' => 'Mr', 'first_name' => 'Rahul', 'last_name' => 'Probe']],
        ])->assertRedirect(route('flights.confirm.show'));

        $summary = $this->get(route('flights.confirm.show'))->assertOk()->viewData('summary');
        $this->assertSame(FlightPricingService::price(5000, 0.10)['customer_price'], $summary['total']);
        $this->assertNotSame(FlightPricingService::price(5000)['customer_price'], $summary['total']);
    }
}
