<?php

namespace Tests\Feature;

use App\Services\FlightPricingService;
use App\Support\FlightSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Search results and the date-strip fare calendar show our marked-up price,
 * not TripJack's raw fare, so the fare a guest clicks is the price they pay.
 */
class FlightResultsPricingTest extends TestCase
{
    use RefreshDatabase;

    protected function fakeSearch(float $tf): void
    {
        $date = now()->addDays(30)->toDateString();
        Http::fake(['*/air-search-all' => Http::response([
            'searchResult' => ['tripInfos' => ['ONWARD' => [[
                'sI' => [['id' => '1', 'fD' => ['aI' => ['code' => '6E', 'name' => 'IndiGo'], 'fN' => '101'], 'da' => ['code' => 'DEL'], 'aa' => ['code' => 'BOM'],
                    'dt' => $date.'T06:00', 'at' => $date.'T08:10', 'duration' => 130, 'stops' => 0, 'sN' => 0]],
                'totalPriceList' => [['id' => 'P1', 'fareIdentifier' => 'PUBLISHED', 'fd' => ['ADULT' => ['fC' => ['TF' => $tf, 'BF' => $tf - 800, 'TAF' => 800], 'rT' => 1, 'cc' => 'ECONOMY']]]],
            ]]]],
            'status' => ['success' => true],
        ])]);
    }

    public function test_per_adult_price_matches_what_the_review_page_charges(): void
    {
        FlightSettings::save(['markup_percent' => 10]);

        // One adult: exactly the booking total.
        $this->assertSame(FlightPricingService::price(5000)['customer_price'], FlightPricingService::perAdult(['ADULT' => 5000], ['ADULT' => 1]));

        // Two adults: ₹10,000 together crosses the GST slab, so each adult's
        // share is half of that total, not the price of ₹5,000 alone.
        $this->assertEqualsWithDelta(FlightPricingService::price(10000)['customer_price'] / 2, FlightPricingService::perAdult(['ADULT' => 5000], ['ADULT' => 2]), 0.01);
        $this->assertGreaterThan(FlightPricingService::price(5000)['customer_price'], FlightPricingService::perAdult(['ADULT' => 5000], ['ADULT' => 2]));
    }

    public function test_results_show_the_marked_up_fare(): void
    {
        FlightSettings::save(['markup_percent' => 10]);
        $this->fakeSearch(5000);

        $html = $this->get(route('flights.search', ['from' => 'DEL', 'to' => 'BOM', 'depart_date' => now()->addDays(30)->toDateString(), 'adults' => 1, 'trip_type' => 'oneway']))
            ->assertOk()->getContent();

        $marked = FlightPricingService::price(5000)['customer_price'];
        $this->assertStringContainsString('&#8377;'.number_format($marked, 2), $html);
        $this->assertStringContainsString('data-price="'.(int) $marked.'"', $html);
        $this->assertStringNotContainsString('&#8377;5,000.00', $html);
    }

    public function test_fare_calendar_shows_the_marked_up_fare(): void
    {
        FlightSettings::save(['markup_percent' => 10]);
        $this->fakeSearch(5000);

        $this->getJson(route('flights.fare-calendar.ajax', ['from' => 'DEL', 'to' => 'BOM', 'depart_date' => now()->addDays(30)->toDateString(), 'adults' => 1]))
            ->assertOk()
            ->assertJson(['success' => true, 'minPrice' => (int) round(FlightPricingService::price(5000)['customer_price'])]);
    }
}
