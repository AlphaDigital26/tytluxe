<?php

namespace Tests\Feature;

use App\Models\Destination;
use App\Models\Hotel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/** Fixes from the 40-scenario hotel test (22 Oct 2026). */
class HotelScenarioFixesTest extends TestCase
{
    use RefreshDatabase;

    private Hotel $hotel;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create());
        $destination = Destination::create(['name' => 'Goa', 'slug' => 'goa', 'country' => 'India', 'type' => 'city', 'for' => ['hotel'], 'is_active' => true]);
        $this->hotel = Hotel::create([
            'destination_id' => $destination->id, 'title' => 'Scenario Hotel', 'slug' => 'scenario-hotel-1',
            'description' => 'x', 'category' => 'city_luxury', 'address' => 'Goa', 'star_rating' => 4,
            'price_from' => 0, 'source' => 'tripjack', 'tripjack_hotel_id' => '900000002', 'is_active' => true,
        ]);
    }

    public function test_typing_in_the_search_box_does_not_use_up_the_book_limit(): void
    {
        for ($i = 0; $i < 15; $i++) {
            $this->get('/hotels/search-suggestions?q=goa'.$i)->assertOk();
        }

        // The Book step has its own count: not refused after the typing above.
        $this->post("/hotels/{$this->hotel->slug}/review", ['option_id' => 'x'])->assertStatus(302);
    }

    public function test_the_same_action_is_still_limited(): void
    {
        for ($i = 0; $i < 10; $i++) {
            $this->post("/hotels/{$this->hotel->slug}/review", ['option_id' => 'x']);
        }

        $this->post("/hotels/{$this->hotel->slug}/review", ['option_id' => 'x'])
            ->assertStatus(429)
            ->assertSee('Please wait a moment');
    }

    public function test_search_explains_long_stays_reversed_dates_and_adult_age_children(): void
    {
        Http::fake();
        $in = now()->addDays(20);
        $url = fn (string $q) => "/hotels/{$this->hotel->slug}?check_in={$in->toDateString()}&adults=2&rooms=1&".$q;

        $this->get($url('check_out='.$in->copy()->addDays(31)->toDateString()))->assertSee('You can book up to 30 nights at a time');
        $this->get($url('check_out='.$in->copy()->addDays(30)->toDateString()))->assertDontSee('You can book up to 30 nights');
        $this->get($url('check_out='.$in->copy()->subDay()->toDateString()))->assertSee('Check-out must be at least one day after check-in');
        $this->get($url('check_out='.$in->toDateString()))->assertSee('Check-out must be at least one day after check-in');
        $this->get($url('check_out='.$in->copy()->addDays(2)->toDateString().'&children=1&child_ages=25'))
            ->assertSee('Children must be 0 to 17 years old');

        // None of these reached TripJack as a search.
        Http::assertNotSent(fn ($r) => str_contains($r->url(), '/hotel/pricing') && (
            ($r->data()['checkOut'] ?? '') === $in->copy()->addDays(31)->toDateString()
            || ($r->data()['checkOut'] ?? 'x') <= ($r->data()['checkIn'] ?? '')
            || in_array(25, $r->data()['rooms'][0]['childAge'] ?? [], true)));
    }

    public function test_guest_form_errors_name_the_room_and_guest_in_plain_words(): void
    {
        $option = [
            'optionId' => 'o-1', 'optionType' => 'SRSM', 'roomInfo' => [['id' => 'r1', 'name' => 'Sea View Room', 'adults' => 2]],
            'mealBasis' => 'Room Only', 'pricing' => ['totalPrice' => 9000, 'basePrice' => 8000, 'currency' => 'INR'],
            'compliance' => ['gstType' => 'NA', 'panRequired' => false, 'passportRequired' => false],
            'cancellation' => ['isRefundable' => false, 'penalties' => []],
        ];
        Http::fake([
            '*/hotel/pricing' => Http::response(['options' => [$option], 'reviewHash' => 'h', 'status' => ['success' => true]]),
            '*/hotel/review' => Http::response(['bookingId' => 'TGS-1', 'option' => $option, 'status' => ['success' => true]]),
        ]);
        $in = now()->addDays(20)->toDateString();
        $out = now()->addDays(22)->toDateString();
        $this->get("/hotels/{$this->hotel->slug}?check_in={$in}&check_out={$out}&adults=2&rooms=1");
        $this->post("/hotels/{$this->hotel->slug}/review", ['option_id' => 'o-1']);

        $this->post("/hotels/{$this->hotel->slug}/book", [
            'contact_email' => 'guest@gmail.com', 'contact_phone' => '9876543210', 'special_requests' => str_repeat('x', 600),
            'rooms' => [['travelers' => [['title' => 'Master', 'first_name' => 'R0han', 'last_name' => 'Kumar'], ['title' => 'Mrs', 'first_name' => '', 'last_name' => 'Kumar']]]],
        ])->assertSessionHasErrors([
            'rooms.0.travelers.0.title' => 'Room 1, guest 1: title must be Mr, Mrs or Ms for an adult, and Master or Miss for a child.',
            'rooms.0.travelers.0.first_name' => 'Room 1, guest 1: first name can only have letters, spaces, dots, apostrophes and hyphens.',
            'rooms.0.travelers.1.first_name' => 'Room 1, guest 2: first name is required.',
            'special_requests',
        ]);
        $this->assertStringNotContainsString('rooms.0', implode(' ', session('errors')->all()));
    }
}
