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

    public function test_flights_add_a_fixed_markup_with_5_percent_gst(): void
    {
        // ₹5,000 domestic fare, 1 passenger, 1 flight: ₹250 markup, ₹12.50
        // GST (5%), then the payment-gateway gross-up so Razorpay's 2.36%
        // comes out of the total and the rest is ours in full.
        $domestic = FlightPricingService::price(5000, 250);
        $this->assertSame(0.05, $domestic['gst_slab']);
        $this->assertSame(250.0, $domestic['margin_amount']);
        $this->assertSame(12.5, $domestic['gst_on_margin']);
        // Plus our ₹100 + ₹18 GST management fee, once per booking.
        $this->assertSame(118.0, $domestic['management_fee'] + $domestic['management_fee_gst']);
        $this->assertEqualsWithDelta(5262.5 / (1 - 0.0236) + 118, $domestic['customer_price'], 0.01);
        $this->assertEqualsWithDelta(5389.70, FlightPricingService::price(5000, 250, false)['customer_price'], 0.01);

        // International: ₹700 + ₹35 GST.
        $this->assertSame(35.0, FlightPricingService::price(25000, 700)['gst_on_margin']);
    }

    public function test_markup_is_per_passenger_per_flight_and_infants_pay_their_own(): void
    {
        $seg = fn ($from, $to, $sN = 0) => ['da' => ['code' => $from], 'aa' => ['code' => $to], 'sN' => $sN];

        // Domestic return: 2 flights × (2 adults + 1 child) × ₹250, plus
        // 2 flights × 1 infant × ₹100.
        $domesticReturn = [['sI' => [$seg('DEL', 'BOM')]], ['sI' => [$seg('BOM', 'DEL')]]];
        $this->assertSame(1500.0, FlightPricingService::bookingMarkup($domesticReturn, 2, 1));
        $this->assertSame(1700.0, FlightPricingService::bookingMarkup($domesticReturn, 2, 1, 1));

        // The infant amount is the same on an international flight, and on
        // a COMBO itinerary it counts each of its flights.
        $comboTrip = [['sI' => [$seg('BOM', 'DXB'), $seg('DXB', 'BOM')]]];
        $this->assertSame(1600.0, FlightPricingService::bookingMarkup($comboTrip, 1, 0, 1));

        FlightSettings::save(['infant_markup' => 150]);
        $this->assertSame(1800.0, FlightPricingService::bookingMarkup($domesticReturn, 2, 1, 1));

        // A connection inside one flight doesn't count again: DEL→BOM→GOI is one flight.
        $this->assertSame(250.0, FlightPricingService::markupPerPassenger([$seg('DEL', 'BOM'), $seg('BOM', 'GOI', 1)]));

        // International return in one COMBO itinerary: two flights at ₹700.
        $combo = [$seg('BOM', 'DXB'), $seg('DXB', 'BOM')];
        $this->assertSame(1400.0, FlightPricingService::markupPerPassenger($combo));

        // Multi-City mixing both: DEL→BOM domestic, BOM→DXB international.
        $multi = [['sI' => [$seg('DEL', 'BOM')]], ['sI' => [$seg('BOM', 'DXB')]]];
        $this->assertSame(950.0, FlightPricingService::bookingMarkup($multi, 1, 0));

        // TripJack's own country name wins over the airport list.
        $this->assertSame(700.0, FlightPricingService::markupPerPassenger([['da' => ['code' => 'XXX', 'country' => 'India'], 'aa' => ['code' => 'YYY', 'country' => 'Nepal'], 'sN' => 0]]));
    }

    public function test_results_show_the_marked_up_fare(): void
    {
        $this->fakeSearch(5000);

        $html = $this->get(route('flights.search', ['from' => 'DEL', 'to' => 'BOM', 'depart_date' => now()->addDays(30)->toDateString(), 'adults' => 1, 'trip_type' => 'oneway']))
            ->assertOk()->getContent();

        $marked = FlightPricingService::price(5000, 250, false)['customer_price']; // per person, no booking fee
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
        $this->assertStringContainsString('data-amount="'.FlightPricingService::price(4000, 250, false)['customer_price'].'"', $html);
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

    public function test_domestic_multi_city_shows_one_tab_per_flight_with_a_booking_bar(): void
    {
        $first = now()->addDays(30)->toDateString();
        $second = now()->addDays(31)->toDateString();
        $leg = fn ($id, $from, $to, $date, $no, $tf) => ['sI' => [['id' => $id, 'fD' => ['aI' => ['code' => '6E', 'name' => 'IndiGo'], 'fN' => $no], 'da' => ['code' => $from], 'aa' => ['code' => $to],
            'dt' => $date.'T06:00', 'at' => $date.'T08:10', 'duration' => 130, 'stops' => 0, 'sN' => 0]],
            'totalPriceList' => [['id' => 'P'.$id, 'fareIdentifier' => 'PUBLISHED', 'fd' => ['ADULT' => ['fC' => ['TF' => $tf, 'BF' => $tf - 800, 'TAF' => 800], 'rT' => 1, 'cc' => 'ECONOMY']]]]];
        Http::fake(['*/air-search-all' => Http::response([
            'searchResult' => ['tripInfos' => [
                0 => [$leg('1', 'DEL', 'BOM', $first, '101', 5000)],
                1 => [$leg('2', 'BOM', 'BLR', $second, '202', 4000)],
            ]],
            'status' => ['success' => true],
        ])]);

        $html = $this->get(route('flights.search', ['trip_type' => 'multi', 'adults' => 1, 'legs' => [
            ['from' => 'DEL', 'to' => 'BOM', 'date' => $first],
            ['from' => 'BOM', 'to' => 'BLR', 'date' => $second],
        ]]))->assertOk()->getContent();

        $this->assertStringContainsString('id="frxMcTabs"', $html);
        $this->assertSame(2, substr_count($html, 'class="frx-mc-tab '));
        $this->assertStringContainsString('class="frx-mc-tab active" data-leg="0"', $html);
        // Only the first flight's list is on screen to start with.
        $this->assertStringContainsString('<section class="frx-group" data-leg="0" >', $html);
        $this->assertStringContainsString('<section class="frx-group" data-leg="1"  hidden >', $html);
        $this->assertStringContainsString('id="frxRtBar" style="--frx-bar-legs: 2;"', $html);
        $this->assertStringContainsString('data-group="price_ids[0]"', $html);
        $this->assertStringContainsString('data-group="price_ids[1]"', $html);
        // Same form and fare radios as before: one price per flight goes to Review.
        $this->assertStringContainsString('name="price_ids[1]" value="P2"', $html);
        $this->assertStringContainsString('class="frx-groups"', $html);
    }

    public function test_fare_calendar_shows_the_marked_up_fare(): void
    {
        FlightSettings::save(['domestic_markup' => 300]);
        $this->fakeSearch(5000);

        $this->getJson(route('flights.fare-calendar.ajax', ['from' => 'DEL', 'to' => 'BOM', 'depart_date' => now()->addDays(30)->toDateString(), 'adults' => 1]))
            ->assertOk()
            ->assertJson(['success' => true, 'minPrice' => (int) round(FlightPricingService::price(5000, 300, false)['customer_price'])]);
    }
}
