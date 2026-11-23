<?php

namespace Tests\Feature;

use App\Models\Destination;
use App\Models\Hotel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class ImportTripjackHotelsTest extends TestCase
{
    use RefreshDatabase;

    protected function hotel(string $id, string $name, string $city, string $country): array
    {
        return [
            'tjHotelId' => $id, 'name' => $name, 'star_rating' => 5,
            'locale' => ['address' => ['city' => $city, 'countryname' => $country, 'fulladdr' => "$name, $city"]],
        ];
    }

    public function test_hotels_are_added_by_id_into_their_own_city(): void
    {
        Queue::fake();
        $dubai = Destination::create(['name' => 'Dubai', 'slug' => 'dubai', 'country' => 'United Arab Emirates', 'type' => 'city', 'for' => ['hotel'], 'is_active' => true]);
        Http::fake(['*/content/fetch-hotel-content' => Http::response(['status' => ['success' => true], 'hotels' => [
            $this->hotel('100000000036', 'The Oberoi, New Delhi', 'New Delhi', 'India'),
            $this->hotel('100000002784', 'Taj Dubai', 'Dubai', 'United Arab Emirates'),
        ]])]);

        $this->artisan('tripjack:import-hotels', ['ids' => ['100000000036', '100000002784', '999']])
            ->expectsOutputToContain('2 of 3 hotels added or updated.')
            ->expectsOutputToContain('999  not found at TripJack')
            ->assertSuccessful();

        $delhi = Destination::where('slug', 'new-delhi')->firstOrFail();
        $this->assertSame('India', $delhi->country);
        $this->assertSame($delhi->id, Hotel::where('tripjack_hotel_id', '100000000036')->value('destination_id'));
        $this->assertSame($dubai->id, Hotel::where('tripjack_hotel_id', '100000002784')->value('destination_id'));
    }
}
