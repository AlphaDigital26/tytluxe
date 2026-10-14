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

    public function test_flights_charge_a_flat_18_percent_gst_on_our_markup_at_any_fare(): void
    {
        FlightSettings::save(['markup_percent' => 10]);

        // ₹5,000 fare: markup ₹500, GST ₹90 (18%, not the hotels' 5% below
        // ₹7,500), then the payment-gateway gross-up.
        $cheap = FlightPricingService::price(5000);
        $this->assertSame(0.18, $cheap['gst_slab']);
        $this->assertSame(90.0, $cheap['gst_on_margin']);
        // Plus our ₹100 + ₹18 GST management fee, once per booking.
        $this->assertSame(118.0, $cheap['management_fee'] + $cheap['management_fee_gst']);
        $this->assertEqualsWithDelta(5590 / (1 - 0.0236) + 118, $cheap['customer_price'], 0.01);
        $this->assertEqualsWithDelta(5590 / (1 - 0.0236), FlightPricingService::price(5000, null, false)['customer_price'], 0.01);
        $this->assertSame(0.18, FlightPricingService::price(20000)['gst_slab']);

        // One rate at every fare: a domestic return's two legs priced on
        // their own add up to the two together, so results match review.
        $this->assertEqualsWithDelta(
            FlightPricingService::price(8000, null, false)['customer_price'],
            FlightPricingService::price(4000, null, false)['customer_price'] * 2,
            0.02,
        );
    }

    public function test_results_show_the_marked_up_fare(): void
    {
        FlightSettings::save(['markup_percent' => 10]);
        $this->fakeSearch(5000);

        $html = $this->get(route('flights.search', ['from' => 'DEL', 'to' => 'BOM', 'depart_date' => now()->addDays(30)->toDateString(), 'adults' => 1, 'trip_type' => 'oneway']))
            ->assertOk()->getContent();

        $marked = FlightPricingService::price(5000, null, false)['customer_price']; // per person, no booking fee
        $this->assertStringContainsString('&#8377;'.number_format($marked, 2), $html);
        $this->assertStringContainsString('data-price="'.(int) $marked.'"', $html);
        $this->assertStringNotContainsString('&#8377;5,000.00', $html);
    }

    public function test_flex_fare_shows_its_non_refundable_flex_charge_when_tripjack_sends_it(): void
    {
        $date = now()->addDays(30)->toDateString();
        $segment = ['id' => '1', 'fD' => ['aI' => ['code' => '6E', 'name' => 'IndiGo'], 'fN' => '101'], 'da' => ['code' => 'DEL'], 'aa' => ['code' => 'BOM'],
            'dt' => $date.'T06:00', 'at' => $date.'T08:10', 'duration' => 130, 'stops' => 0, 'sN' => 0];
        $fare = fn ($id, $identifier, $fc) => ['id' => $id, 'fareIdentifier' => $identifier, 'fd' => ['ADULT' => ['fC' => $fc, 'rT' => 1, 'cc' => 'ECONOMY']]];
        Http::fake(['*/air-search-all' => Http::response([
            'searchResult' => ['tripInfos' => ['ONWARD' => [['sI' => [$segment], 'totalPriceList' => [
                $fare('F1', 'TJ_FLEX', ['TF' => 5400, 'BF' => 4200, 'TAF' => 1200, 'FTC' => 350]),
                $fare('P1', 'PUBLISHED', ['TF' => 5000, 'BF' => 4200, 'TAF' => 800]),
            ]]]]],
            'status' => ['success' => true],
        ])]);

        $html = $this->get(route('flights.search', ['from' => 'DEL', 'to' => 'BOM', 'depart_date' => $date, 'adults' => 1, 'trip_type' => 'oneway']))
            ->assertOk()->getContent();

        $this->assertStringContainsString('Zero Cancellation Fee &middot; 24h+ · ₹350 flex charge non-refundable', $html);
        $this->assertSame(1, substr_count($html, 'flex charge non-refundable'), 'Only the flex fare carries it');
    }

    public function test_domestic_round_trip_shows_both_lists_side_by_side_with_a_booking_bar(): void
    {
        $out = now()->addDays(30)->toDateString();
        $back = now()->addDays(33)->toDateString();
        $leg = fn ($id, $from, $to, $date, $no, $tf) => ['sI' => [['id' => $id, 'fD' => ['aI' => ['code' => '6E', 'name' => 'IndiGo'], 'fN' => $no], 'da' => ['code' => $from], 'aa' => ['code' => $to],
            'dt' => $date.'T06:00', 'at' => $date.'T08:10', 'duration' => 130, 'stops' => 0, 'sN' => 0]],
            'totalPriceList' => [['id' => 'P'.$id, 'fareIdentifier' => 'PUBLISHED', 'fd' => ['ADULT' => ['fC' => ['TF' => $tf, 'BF' => $tf - 800, 'TAF' => 800], 'rT' => 1, 'cc' => 'ECONOMY']]]]];
        Http::fake(['*/air-search-all' => Http::response([
            'searchResult' => ['tripInfos' => [
                'ONWARD' => [$leg('1', 'DEL', 'BOM', $out, '101', 5000)],
                'RETURN' => [$leg('2', 'BOM', 'DEL', $back, '202', 4000)],
            ]],
            'status' => ['success' => true],
        ])]);
        $params = ['from' => 'DEL', 'to' => 'BOM', 'depart_date' => $out, 'adults' => 1, 'trip_type' => 'return', 'return_date' => $back];

        $html = $this->get(route('flights.search', $params))->assertOk()->getContent();

        $this->assertStringContainsString('class="frx-groups frx-rt-grid"', $html);
        $this->assertStringContainsString('id="frxRtBar"', $html);
        $this->assertStringContainsString('data-group="price_ids[ONWARD]"', $html);
        $this->assertStringContainsString('data-group="price_ids[RETURN]"', $html);
        $this->assertStringContainsString('data-amount="'.FlightPricingService::price(4000, null, false)['customer_price'].'"', $html);
        $this->assertStringContainsString('data-times="06:00 BOM → 08:10 DEL"', $html);
        // TripJack's placement: each column has its own Cheapest / Fastest and sort row.
        $this->assertSame(2, substr_count($html, 'class="frx-leg-sort"'));
        $this->assertStringContainsString('class="frx-leg-pick active" data-group="RETURN" data-sort="price"', $html);
        $this->assertStringNotContainsString('id="frxSortbar"', $html);

        // One way keeps the single full-width list, no bar.
        $oneway = $this->get(route('flights.search', ['trip_type' => 'oneway'] + $params))->assertOk()->getContent();
        $this->assertStringNotContainsString('id="frxRtBar"', $oneway);
        $this->assertStringContainsString('class="frx-groups"', $oneway);
    }

    public function test_fare_calendar_shows_the_marked_up_fare(): void
    {
        FlightSettings::save(['markup_percent' => 10]);
        $this->fakeSearch(5000);

        $this->getJson(route('flights.fare-calendar.ajax', ['from' => 'DEL', 'to' => 'BOM', 'depart_date' => now()->addDays(30)->toDateString(), 'adults' => 1]))
            ->assertOk()
            ->assertJson(['success' => true, 'minPrice' => (int) round(FlightPricingService::price(5000, null, false)['customer_price'])]);
    }
}
