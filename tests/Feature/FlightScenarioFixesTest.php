<?php

namespace Tests\Feature;

use App\Http\Controllers\FlightController;
use App\Models\User;
use App\Services\TripJack\TripJackFlightErrorCatalog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/** Fixes from the flight scenario test (24 Oct 2026). */
class FlightScenarioFixesTest extends TestCase
{
    use RefreshDatabase;

    private function day(int $n): string
    {
        return now('Asia/Kolkata')->addDays($n)->toDateString();
    }

    public function test_impossible_searches_are_explained_without_asking_tripjack(): void
    {
        $this->actingAs(User::factory()->create());
        Http::fake();

        $cases = [
            ['from=DEL&to=DEL&depart_date='.$this->day(10), 'departure and arrival cities are the same'],
            ['from=DEL&to=BOM&depart_date='.$this->day(-2), 'already passed'],
            ['from=DEL&to=BOM&depart_date='.$this->day(400), 'up to about a year ahead'],
            ['from=DEL&to=BOM&depart_date=2026-02-31', 'valid travel date'],
            ['trip_type=return&from=DEL&to=BOM&depart_date='.$this->day(10).'&return_date='.$this->day(8), 'return date is before the departure date'],
            ['trip_type=multi&legs[0][from]=DEL&legs[0][to]=BOM&legs[0][date]='.$this->day(10).'&legs[1][from]=BOM&legs[1][to]=GOI&legs[1][date]='.$this->day(7), 'Flight 2: This flight is before the previous one'],
        ];
        foreach ($cases as [$query, $message]) {
            $this->get('/flights/search?adults=1&'.$query)->assertOk()->assertSee($message, false);
        }

        Http::assertNothingSent();
    }

    public function test_valid_searches_still_go_to_tripjack(): void
    {
        $this->assertNull(FlightController::searchInputProblem('return', 'DEL', 'BOM', $this->day(5), $this->day(5), []));
        $this->assertNull(FlightController::searchInputProblem('oneway', 'DEL', 'BOM', $this->day(0), '', []));
    }

    public function test_tripjack_search_errors_have_plain_messages(): void
    {
        foreach (['1003' => 'on or after the date', '1004' => 'year ahead', '1005' => 'cities are the same', '1045' => 'couldn’t find one of those airports', '808' => 'fares have expired', '2502' => 'already booked on this flight'] as $code => $text) {
            $this->assertStringContainsString($text, TripJackFlightErrorCatalog::describe($code)['message'], $code);
        }
    }
}
