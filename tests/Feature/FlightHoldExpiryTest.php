<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * flights:expire-holds — held fares past TripJack's hold deadline are
 * marked hold_expired in the database, not only shown as expired.
 */
class FlightHoldExpiryTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    protected function hold(array $extra = []): Booking
    {
        return Booking::forceCreate(array_merge([
            'reference' => 'TYT'.strtoupper(substr(md5(uniqid()), 0, 8)),
            'user_id' => $this->user->id,
            'vertical' => 'flight',
            'status' => 'on_hold',
            'tripjack_booking_id' => 'TJS4'.random_int(10000000000, 99999999999),
            'tripjack_hold_expires_at' => now()->subMinute(),
            'flight_route' => 'DEL-BOM',
            'flight_departure_date' => now()->addDays(20)->toDateString(),
            'pax_adults' => 1, 'pax_children' => 0, 'pax_infants' => 0,
            'lead_guest_name' => 'Rahul Probe',
            'base_amount' => 5000, 'total_amount' => 5600, 'tripjack_total_price' => 5000,
        ], $extra));
    }

    public function test_holds_past_their_deadline_are_marked_expired(): void
    {
        Http::fake();
        $expired = $this->hold();
        $live = $this->hold(['tripjack_hold_expires_at' => now()->addHour()]);
        $paying = $this->hold(['tripjack_confirm_attempted_at' => now()]);
        $hotel = $this->hold(['vertical' => 'hotel']);

        $this->artisan('flights:expire-holds')->assertSuccessful();

        $this->assertSame('hold_expired', $expired->fresh()->status);
        $this->assertSame('on_hold', $live->fresh()->status);
        $this->assertSame('on_hold', $paying->fresh()->status); // Confirm-Book settles it
        $this->assertSame('on_hold', $hotel->fresh()->status);
        Http::assertNothingSent();
    }

    public function test_hold_without_a_saved_deadline_is_looked_up_in_booking_details(): void
    {
        $lapsed = $this->hold(['tripjack_hold_expires_at' => null]);
        $released = $this->hold(['tripjack_hold_expires_at' => null]);
        $live = $this->hold(['tripjack_hold_expires_at' => null]);
        $limit = fn (string $time) => ['itemInfos' => ['AIR' => ['timeLimit' => $time]]];

        Http::fake(['*/booking-details' => Http::sequence()
            ->push(['order' => ['status' => 'ON_HOLD'], 'status' => ['success' => true]] + $limit(now()->subHour()->format('Y-m-d\TH:i')))
            ->push(['order' => ['status' => 'UNCONFIRMED'], 'status' => ['success' => true]])
            ->push(['order' => ['status' => 'ON_HOLD'], 'status' => ['success' => true]] + $limit(now()->addHours(3)->format('Y-m-d\TH:i')))]);

        $this->artisan('flights:expire-holds')->assertSuccessful();

        $this->assertSame('hold_expired', $lapsed->fresh()->status);
        $this->assertSame('hold_expired', $released->fresh()->status);
        $this->assertSame('on_hold', $live->fresh()->status);
        $this->assertNotNull($live->fresh()->tripjack_hold_expires_at); // saved, so it isn't looked up again
    }

    public function test_guest_page_shows_the_expired_hold(): void
    {
        Http::fake();
        $booking = $this->hold();
        $this->artisan('flights:expire-holds');

        $this->actingAs($this->user)
            ->get(route('hotel.booking.confirmation', $booking->reference))
            ->assertOk()
            ->assertSee('Held Fare Expired')
            ->assertDontSee('Confirm &amp; Pay', false);
    }
}
