<?php

namespace Tests\Feature;

use App\Services\TripJack\TripJackFlightClient;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * TripJack Search returns some flights twice — once with the regular fares
 * and once with the NDC fares (confirmed live, IX-1056 DEL→BOM). They must
 * show as one card with every fare, like TripJack's own results page.
 */
class FlightResultsMergeTest extends TestCase
{
    protected function segment(string $flightNo, string $dt, string $from = 'DEL', string $to = 'BOM'): array
    {
        return [
            'id' => (string) random_int(1000, 9999),
            'fD' => ['aI' => ['code' => 'IX', 'name' => 'AI Express'], 'fN' => $flightNo],
            'da' => ['code' => $from], 'aa' => ['code' => $to],
            'dt' => $dt, 'at' => substr($dt, 0, 11).'20:10',
            'duration' => 140, 'stops' => 0, 'sN' => 0,
        ];
    }

    protected function fare(string $id, string $identifier, float $tf): array
    {
        return ['id' => $id, 'fareIdentifier' => $identifier, 'fd' => ['ADULT' => ['fC' => ['TF' => $tf, 'BF' => $tf - 500, 'TAF' => 500], 'rT' => 1, 'cc' => 'ECONOMY']]];
    }

    protected function itineraries(): array
    {
        return [
            ['sI' => [$this->segment('1056', '2026-11-06T17:50')], 'totalPriceList' => [
                $this->fare('P1', 'PUBLISHED', 5525.5), $this->fare('P2', 'SME', 6706.5), $this->fare('P3', 'FLEX', 6706.5),
            ]],
            ['sI' => [$this->segment('1235', '2026-11-06T23:25')], 'totalPriceList' => [$this->fare('Q1', 'PUBLISHED', 5525.5)]],
            ['sI' => [$this->segment('1056', '2026-11-06T17:50')], 'totalPriceList' => [
                $this->fare('N1', 'NDC_Xpress Value', 5692.5), $this->fare('N2', 'NDC_Corporate Value', 6915.5), $this->fare('N3', 'NDC_Corporate Flex', 7241.5),
            ]],
        ];
    }

    public function test_same_flight_itineraries_merge_and_keep_every_fare(): void
    {
        $merged = TripJackFlightClient::mergeSameFlights($this->itineraries());

        $this->assertCount(2, $merged);
        $this->assertSame(['P1', 'P2', 'P3', 'N1', 'N2', 'N3'], array_column($merged[0]['totalPriceList'], 'id'));
        $this->assertSame(['Q1'], array_column($merged[1]['totalPriceList'], 'id'));
    }

    public function test_same_flight_number_on_another_date_or_route_is_not_merged(): void
    {
        $merged = TripJackFlightClient::mergeSameFlights([
            ['sI' => [$this->segment('1056', '2026-11-06T17:50')], 'totalPriceList' => [$this->fare('A', 'PUBLISHED', 1)]],
            ['sI' => [$this->segment('1056', '2026-11-07T17:50')], 'totalPriceList' => [$this->fare('B', 'PUBLISHED', 1)]],
            ['sI' => [$this->segment('1056', '2026-11-06T17:50', 'DEL', 'BLR')], 'totalPriceList' => [$this->fare('C', 'PUBLISHED', 1)]],
            // Connecting journey sharing only its first flight.
            ['sI' => [$this->segment('1056', '2026-11-06T17:50'), $this->segment('2001', '2026-11-06T22:00', 'BOM', 'GOI')], 'totalPriceList' => [$this->fare('D', 'PUBLISHED', 1)]],
        ]);

        $this->assertCount(4, $merged);
    }

    public function test_direct_only_search_asks_tripjack_for_direct_flights(): void
    {
        // DEL→HYD→BOM: the second segment continues the same leg (sN 1).
        $connecting = ['sI' => [$this->segment('1056', '2026-11-06T06:00', 'DEL', 'HYD'), ['sN' => 1] + $this->segment('2001', '2026-11-06T10:00', 'HYD', 'BOM')], 'totalPriceList' => [$this->fare('C1', 'PUBLISHED', 4000)]];
        Http::fake(['*/air-search-all' => Http::response([
            'searchResult' => ['tripInfos' => ['ONWARD' => [...$this->itineraries(), $connecting]]],
            'status' => ['success' => true],
        ])]);
        $params = ['from' => 'DEL', 'to' => 'BOM', 'depart_date' => '2026-11-06', 'adults' => 1, 'trip_type' => 'oneway'];

        $html = $this->get(route('flights.search', $params + ['direct_flight' => 1]))->assertOk()->getContent();

        Http::assertSent(fn (Request $r) => $r['searchQuery']['searchModifiers'] === ['pfts' => ['REGULAR'], 'isDirectFlight' => true]);
        // Our own check still drops anything that isn't non-stop.
        $this->assertStringNotContainsString('value="C1"', $html);
        $this->assertStringContainsString('value="Q1"', $html);

        // A normal search doesn't send the modifier.
        $this->get(route('flights.search', $params))->assertOk();
        Http::assertSent(fn (Request $r) => $r['searchQuery']['searchModifiers'] === ['pfts' => ['REGULAR']]);
    }

    public function test_results_page_shows_one_card_per_flight_with_all_fares(): void
    {
        Http::fake(['*/air-search-all' => Http::response([
            'searchResult' => ['tripInfos' => ['ONWARD' => $this->itineraries()]],
            'status' => ['success' => true],
        ])]);

        $html = $this->get(route('flights.search', [
            'from' => 'DEL', 'to' => 'BOM', 'depart_date' => '2026-11-06', 'adults' => 1, 'trip_type' => 'oneway',
        ]))->assertOk()->getContent();

        $this->assertSame(2, substr_count($html, 'frx-book-btn" data-group'));
        foreach (['P1', 'P2', 'P3', 'N1', 'N2', 'N3', 'Q1'] as $priceId) {
            $this->assertStringContainsString('value="'.$priceId.'"', $html);
        }
    }
}
