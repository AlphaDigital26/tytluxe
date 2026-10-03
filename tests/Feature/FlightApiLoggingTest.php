<?php

namespace Tests\Feature;

use App\Services\TripJack\TripJackFlightClient;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

/**
 * tripjack.log never holds passport/PAN/DOB/contact details, and the UAT
 * certification logs (doc "Log Format Rules") are full, separate, raw files.
 */
class FlightApiLoggingTest extends TestCase
{
    protected string $certRoot;

    protected string $certDir;

    protected function setUp(): void
    {
        parent::setUp();
        // Isolated folder — never touch real certification logs.
        $this->certRoot = sys_get_temp_dir().'/tytluxe-cert-test-'.uniqid();
        config(['services.tripjack.flight.cert_log_path' => $this->certRoot]);
        $this->certDir = $this->certRoot.'/'.now()->format('Y-m-d');
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->certRoot);
        parent::tearDown();
    }

    protected function book(): void
    {
        app(TripJackFlightClient::class)->book('TJS500000000001', 5000.0, [[
            'ti' => 'Mr', 'pt' => 'ADULT', 'fN' => 'Rahul', 'lN' => 'Probe', 'dob' => '1990-04-12',
            'pNum' => 'Z1234567', 'eD' => '2031-01-01', 'pan' => 'ABCDE1234F',
        ]], ['rahul@example.com'], ['+919876543210']);
    }

    public function test_tripjack_log_masks_traveller_and_contact_details(): void
    {
        $logged = [];
        Log::shouldReceive('channel')->with('tripjack')->andReturnSelf();
        Log::shouldReceive('info')->andReturnUsing(function ($event, $context) use (&$logged) {
            $logged[] = $context;
        });
        Http::fake(['*' => Http::response(['bookingId' => 'TJS500000000001', 'status' => ['success' => true]])]);

        $this->book();

        $json = json_encode($logged);
        foreach (['Z1234567', 'ABCDE1234F', '1990-04-12', 'rahul@example.com', '+919876543210'] as $secret) {
            $this->assertStringNotContainsString($secret, $json);
        }
        $traveller = $logged[0]['request']['travellerInfo'][0];
        $this->assertSame('****4567', $traveller['pNum']);
        $this->assertSame('******234F', $traveller['pan']);
        $this->assertSame('****-**-**', $traveller['dob']);
        $this->assertSame('Rahul', $traveller['fN']);
        $this->assertSame(['***@example.com'], $logged[0]['request']['deliveryInfo']['emails']);
    }

    public function test_certification_logs_are_separate_raw_request_and_response_files_on_uat(): void
    {
        config(['services.tripjack.env' => 'test', 'services.tripjack.flight.cert_logs' => true]);
        $raw = '{"bookingId":"TJS500000000001","status":{"success":true,"httpStatus":200}}';
        Http::fake(['*' => Http::response($raw, 200, ['Content-Type' => 'application/json'])]);

        $this->book();

        $requests = glob($this->certDir.'/*_oms-v1-air-book_TJS500000000001_request.json');
        $responses = glob($this->certDir.'/*_oms-v1-air-book_TJS500000000001_response.json');
        $this->assertCount(1, $requests);
        $this->assertCount(1, $responses);
        $this->assertSame($raw, file_get_contents($responses[0])); // unmodified
        $request = json_decode(file_get_contents($requests[0]), true);
        $this->assertSame('Z1234567', $request['body']['travellerInfo'][0]['pNum']); // full, unmasked evidence
    }

    public function test_certification_logs_are_never_written_in_production(): void
    {
        config(['services.tripjack.env' => 'production', 'services.tripjack.flight.cert_logs' => true]);
        Http::fake(['*' => Http::response(['bookingId' => 'TJS500000000001', 'status' => ['success' => true]])]);

        $this->book();

        $this->assertDirectoryDoesNotExist($this->certDir);
    }
}
