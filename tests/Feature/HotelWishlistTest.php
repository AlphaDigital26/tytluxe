<?php

namespace Tests\Feature;

use App\Models\Destination;
use App\Models\Hotel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HotelWishlistTest extends TestCase
{
    use RefreshDatabase;

    public function test_wishlist_page_loads_successfully(): void
    {
        $response = $this->get('/wishlist');

        $response->assertStatus(200);
        $response->assertSee('Your Curated');
        $response->assertSee('Wishlist');
        $response->assertSee('Saved Properties');
    }

    public function test_hotels_wishlist_redirects_to_wishlist(): void
    {
        $response = $this->get('/hotels/wishlist');

        $response->assertRedirect('/wishlist');
    }

    public function test_wishlist_lookup_returns_hotels_json(): void
    {
        $dest = Destination::factory()->create(['name' => 'Goa', 'slug' => 'goa', 'country' => 'India']);
        $hotel = Hotel::factory()->create([
            'title' => 'Taj Exotica Resort & Spa',
            'slug' => 'taj-exotica-goa',
            'destination_id' => $dest->id,
            'description' => 'Luxury beachfront paradise in Benaulim, Goa.',
            'is_active' => true,
            'price_from' => 25000,
        ]);

        $response = $this->postJson('/wishlist/lookup', [
            'slugs' => ['taj-exotica-goa'],
        ]);

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'hotels');
        $response->assertJsonPath('hotels.0.slug', 'taj-exotica-goa');
        $response->assertJsonPath('hotels.0.title', 'Taj Exotica Resort & Spa');
    }

    public function test_header_does_not_include_standalone_wishlist_button(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertDontSee('header-wishlist-cta');
    }

    public function test_profile_dropdown_and_profile_page_include_wishlist(): void
    {
        $user = \App\Models\User::factory()->create();

        $response = $this->actingAs($user)->get('/');
        $response->assertStatus(200);
        $response->assertSee('profile-dd-wishlist-item');
        $response->assertSee(route('wishlist'));

        $profileResponse = $this->actingAs($user)->get('/profile');
        $profileResponse->assertStatus(200);
        $profileResponse->assertSee('profileWishlistTabBtn');
        $profileResponse->assertSee('tab-wishlist');
    }

    public function test_wishlist_page_renders_category_tabs(): void
    {
        $response = $this->get('/wishlist');

        $response->assertStatus(200);
        $response->assertSee('wlTabHotels');
        $response->assertSee('wlTabPackages');
        $response->assertSee('wlTabFlights');
        $response->assertSee('wlTabBadgeHotels');
        $response->assertSee('wlTabBadgePackages');
        $response->assertSee('wlTabBadgeFlights');
    }

    public function test_flights_page_includes_wishlist_buttons(): void
    {
        $response = $this->get('/flights');

        $response->assertStatus(200);
        $response->assertSee('flt-heart-btn');
        $response->assertSee('data-type="flight"', false);
    }
}

