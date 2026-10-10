<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * tripjack:burst-test — growing bursts of read-only requests, stopping at
 * the first 429 and reporting its Retry-After.
 */
class TripjackBurstTestCommandTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake();
    }

    public function test_stops_at_the_first_429_and_reports_retry_after(): void
    {
        Http::fake(['*/nationality-info' => Http::sequence()
            ->push(['status' => ['success' => true]])
            ->push(['status' => ['success' => true]])
            ->push(['status' => ['success' => true]])
            ->push(['errors' => [['message' => 'Too many requests']]], 429, ['Retry-After' => '30'])
            ->push(['errors' => [['message' => 'Too many requests']]], 429, ['Retry-After' => '30']),
        ]);

        $this->artisan('tripjack:burst-test', ['--sizes' => '2,3,10', '--pause' => 0, '--force' => true])
            ->expectsOutputToContain('Rate limited at a burst of 3 simultaneous requests (Retry-After 30s)')
            ->assertSuccessful();

        // The 10-request burst never ran.
        Http::assertSentCount(5);

        $csv = Storage::get(Storage::files('tripjack-burst-tests')[0]);
        $this->assertStringContainsString('3,2,429', $csv);
    }

    public function test_reports_when_no_limit_was_reached(): void
    {
        Http::fake(['*/content/fetch-countries' => Http::response(['hotelCountries' => []])]);

        $this->artisan('tripjack:burst-test', ['--sizes' => '2,4', '--pause' => 0, '--endpoint' => 'countries', '--force' => true])
            ->expectsOutputToContain('No 429 up to a burst of 4')
            ->assertSuccessful();

        Http::assertSentCount(6);
    }

    public function test_asks_before_using_the_live_key(): void
    {
        Http::fake();

        $this->artisan('tripjack:burst-test', ['--sizes' => '2'])
            ->expectsConfirmation('This uses the live TripJack API key. Run it now, at a quiet time?', 'no')
            ->assertSuccessful();

        Http::assertNothingSent();
    }
}
