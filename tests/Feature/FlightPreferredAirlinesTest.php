<?php

namespace Tests\Feature;

use App\Http\Controllers\FlightController;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Guests can pick several preferred airlines (TripJack takes up to 10),
 * sent as one comma-separated preferred_airline value.
 */
class FlightPreferredAirlinesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Http::fake(['*/air-search-all' => Http::response(['searchResult' => ['tripInfos' => []], 'status' => ['success' => true]])]);
    }

    protected function search(string $airlines): void
    {
        $this->get(route('flights.search', [
            'from' => 'DEL', 'to' => 'BOM', 'depart_date' => now()->addDays(30)->toDateString(),
            'adults' => 1, 'trip_type' => 'oneway', 'preferred_airline' => $airlines,
        ]))->assertOk();
    }

    public function test_several_airlines_are_sent_to_tripjack(): void
    {
        $this->search('6E,ai');

        Http::assertSent(fn (Request $r) => $r['searchQuery']['preferredAirline'] === [['code' => '6E'], ['code' => 'AI']]);
    }

    public function test_one_airline_still_works_and_none_sends_nothing(): void
    {
        $this->search('6E');
        Http::assertSent(fn (Request $r) => $r['searchQuery']['preferredAirline'] === [['code' => '6E']]);

        $this->search('');
        Http::assertSent(fn (Request $r) => ! isset($r['searchQuery']['preferredAirline']));
    }

    public function test_junk_and_repeats_are_dropped_and_at_most_ten_are_sent(): void
    {
        $this->assertSame(['6E', 'AI'], FlightController::preferredAirlines('6E, 6e,<script>,AI,ABC,'));

        $this->assertCount(10, FlightController::preferredAirlines('6E,AI,IX,QP,SG,EK,QR,EY,SQ,LH,BA,CX'));
    }

    public function test_fare_calendar_uses_the_same_airlines(): void
    {
        $this->getJson(route('flights.fare-calendar.ajax', [
            'from' => 'DEL', 'to' => 'BOM', 'depart_date' => now()->addDays(30)->toDateString(), 'adults' => 1, 'preferred_airline' => '6E,QP',
        ]))->assertOk();

        Http::assertSent(fn (Request $r) => $r['searchQuery']['preferredAirline'] === [['code' => '6E'], ['code' => 'QP']]);
    }

    public function test_results_page_keeps_the_choice_ticked_and_names_it(): void
    {
        $html = $this->get(route('flights.search', [
            'from' => 'DEL', 'to' => 'BOM', 'depart_date' => now()->addDays(30)->toDateString(),
            'adults' => 1, 'trip_type' => 'oneway', 'preferred_airline' => '6E,AI',
        ]))->assertOk()->getContent();

        $this->assertStringContainsString('value="6E,AI"', $html);
        $this->assertMatchesRegularExpression('/value="6E" data-name="IndiGo"\s+checked/', $html);
        $this->assertMatchesRegularExpression('/value="QP" data-name="Akasa Air"\s+>/', $html);
    }
}
