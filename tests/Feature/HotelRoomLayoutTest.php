<?php

namespace Tests\Feature;

use App\Http\Controllers\FrontendController;
use App\Models\Destination;
use App\Models\Hotel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/** The guest's own room-by-room layout reaches TripJack exactly (certification cases 4, 7, 10). */
class HotelRoomLayoutTest extends TestCase
{
    use RefreshDatabase;

    private Hotel $hotel;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create());
        $destination = Destination::create(['name' => 'Goa', 'slug' => 'goa', 'country' => 'India', 'type' => 'city', 'for' => ['hotel'], 'is_active' => true]);
        $this->hotel = Hotel::create([
            'destination_id' => $destination->id, 'title' => 'Layout Hotel', 'slug' => 'layout-hotel-1',
            'description' => 'x', 'category' => 'city_luxury', 'address' => 'Goa', 'star_rating' => 4,
            'price_from' => 0, 'source' => 'tripjack', 'tripjack_hotel_id' => '900000003', 'is_active' => true,
        ]);
        Http::fake(['*/hotel/pricing' => Http::response(['options' => [], 'reviewHash' => 'h', 'status' => ['success' => true]])]);
    }

    private function dates(): string
    {
        return 'check_in='.now()->addDays(20)->toDateString().'&check_out='.now()->addDays(22)->toDateString();
    }

    public function test_room_layout_is_parsed_and_checked(): void
    {
        $this->assertSame([
            ['adults' => 1, 'children' => 2, 'childAge' => [5, 8]],
            ['adults' => 2, 'children' => 1, 'childAge' => [7]],
            ['adults' => 1],
        ], FrontendController::parseRoomConfig('1-5.8|2-7|1'));

        foreach (['', '0', '7', '1-18', '1-1.2.3.4.5', '1|1|1|1|1|1|1|1|1|1', '2-x', "1-5'--"] as $bad) {
            $this->assertNull(FrontendController::parseRoomConfig($bad), $bad);
        }
    }

    public function test_tripjack_gets_exactly_the_rooms_the_guest_chose(): void
    {
        // Certification case 4: 1+2C / 2+1C / 1+1C / 1 / 1 (an even split would differ).
        $this->get("/hotels/{$this->hotel->slug}?{$this->dates()}&adults=6&children=4&rooms=5&child_ages=4,6,8,10&room_config=1-4.6|2-8|1-10|1|1")
            ->assertOk()
            ->assertSee('value="1-4.6|2-8|1-10|1|1"', false);

        Http::assertSent(fn ($r) => str_contains($r->url(), '/hotel/pricing') && $r->data()['rooms'] === [
            ['adults' => 1, 'children' => 2, 'childAge' => [4, 6]],
            ['adults' => 2, 'children' => 1, 'childAge' => [8]],
            ['adults' => 1, 'children' => 1, 'childAge' => [10]],
            ['adults' => 1],
            ['adults' => 1],
        ]);
    }

    public function test_links_without_a_layout_still_split_evenly(): void
    {
        $this->get("/hotels/{$this->hotel->slug}?{$this->dates()}&adults=3&children=0&rooms=2")->assertOk();

        Http::assertSent(fn ($r) => str_contains($r->url(), '/hotel/pricing') && $r->data()['rooms'] === [['adults' => 2], ['adults' => 1]]);
    }

    public function test_a_broken_layout_shows_a_message_and_prices_nothing(): void
    {
        $this->get("/hotels/{$this->hotel->slug}?{$this->dates()}&room_config=9-1")
            ->assertOk()
            ->assertSee('Please check the guests in each room');

        // (A background room-photo sync may price the hotel; never with this layout.)
        Http::assertNotSent(fn ($r) => str_contains($r->url(), '/hotel/pricing') && ($r->data()['rooms'][0]['adults'] ?? 0) === 9);
    }
}
