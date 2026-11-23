<?php

namespace Tests\Feature;

use App\Models\Destination;
use App\Models\Hotel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Searching one property, picked from the suggestions or typed in the destination box. */
class HotelPropertySearchTest extends TestCase
{
    use RefreshDatabase;

    protected function makeHotel(Destination $d, string $title, string $slug, string $hid): Hotel
    {
        return Hotel::create([
            'destination_id' => $d->id, 'title' => $title, 'slug' => $slug, 'description' => 'x',
            'category' => 'city_luxury', 'address' => 'Dubai', 'star_rating' => 4, 'price_from' => 0,
            'source' => 'tripjack', 'tripjack_hotel_id' => $hid, 'is_active' => true,
        ]);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $dubai = Destination::create(['name' => 'Dubai', 'slug' => 'dubai', 'country' => 'United Arab Emirates', 'type' => 'city', 'for' => ['hotel'], 'is_active' => true]);
        $this->makeHotel($dubai, 'Taj Dubai', 'taj-dubai-100000002784', '100000002784');
        $this->makeHotel($dubai, 'Grand Hyatt Dubai', 'grand-hyatt-dubai-100000208246', '100000208246');
    }

    public function test_a_picked_property_lists_only_that_hotel(): void
    {
        $this->get(route('hotels', ['hotel' => 'taj-dubai-100000002784']))
            ->assertOk()
            ->assertDontSee('Please choose a destination')
            ->assertSee('hotels/taj-dubai-100000002784', false)
            ->assertDontSee('hotels/grand-hyatt-dubai-100000208246', false);
    }

    public function test_a_hotel_name_typed_in_the_destination_box_finds_that_hotel(): void
    {
        $this->get(route('hotels', ['destination' => 'Grand Hyatt Dubai']))
            ->assertOk()
            ->assertDontSee('We don\'t have hotels in', false)
            ->assertSee('hotels/grand-hyatt-dubai-100000208246', false)
            ->assertDontSee('hotels/taj-dubai-100000002784', false);
    }

    public function test_a_city_search_still_lists_every_hotel(): void
    {
        $this->get(route('hotels', ['destination' => 'Dubai']))
            ->assertOk()
            ->assertSee('hotels/taj-dubai-100000002784', false)
            ->assertSee('hotels/grand-hyatt-dubai-100000208246', false);
    }
}
