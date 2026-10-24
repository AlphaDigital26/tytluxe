<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/** When TripJack refuses or fails a search, don't tell the guest there are no flights. */
class FlightSearchFailureTest extends TestCase
{
    use RefreshDatabase;

    protected function search()
    {
        return $this->get(route('flights.search', [
            'from' => 'DEL', 'to' => 'BOM', 'depart_date' => now()->addDays(10)->toDateString(),
            'return_date' => now()->addDays(12)->toDateString(), 'adults' => 1, 'trip_type' => 'return', 'fare_type' => 'REGULAR',
        ]));
    }

    public function test_a_refused_search_says_try_again_not_no_flights(): void
    {
        Http::fake(['*/air-search-all' => Http::response(['status' => ['success' => false, 'httpStatus' => 403], 'errors' => [['errCode' => '408', 'message' => 'Access Denied']]], 403)]);

        $this->search()->assertOk()
            ->assertSee('load flights just now. Please search again in a minute.')
            ->assertDontSee('No flights found for this route and date');
    }

    public function test_a_search_with_no_flights_still_says_so(): void
    {
        Http::fake(['*/air-search-all' => Http::response(['searchResult' => ['tripInfos' => []], 'status' => ['success' => true]])]);

        $this->search()->assertOk()->assertSee('No flights found for this route and date');
    }
}
