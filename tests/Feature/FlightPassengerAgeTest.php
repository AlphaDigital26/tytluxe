<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Passenger ages are checked on our side against the travel dates (doc
 * Booking API errors: adult 12–100 at departure, child 2–12, infant 0–2,
 * senior citizen 60+) instead of waiting for TripJack to reject them.
 */
class FlightPassengerAgeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create());
        Http::fake();
    }

    protected function segment(string $id, string $from, string $to, string $dt): array
    {
        return ['id' => $id, 'da' => ['code' => $from], 'aa' => ['code' => $to], 'fD' => ['aI' => ['code' => '6E'], 'fN' => '101'], 'dt' => $dt, 'at' => substr($dt, 0, 11).'08:10'];
    }

    protected function draft(array $pax, string $fareType = 'REGULAR', bool $return = false): array
    {
        $trips = [['sI' => [$this->segment('1', 'DEL', 'BOM', '2026-11-01T06:00')]]];
        if ($return) {
            $trips[] = ['sI' => [$this->segment('2', 'BOM', 'DEL', '2026-11-20T06:00')]];
        }

        return [
            'bookingId' => 'TJS200000000001',
            'resultsUrl' => route('flights.search').'?from=DEL&to=BOM',
            'expiresAt' => now()->addMinutes(10)->timestamp,
            'context' => ['tripType' => $return ? 'return' : 'oneway', 'from' => 'DEL', 'to' => 'BOM', 'departDate' => '2026-11-01', 'returnDate' => $return ? '2026-11-20' : '',
                'cabinClass' => 'ECONOMY', 'fareType' => $fareType, 'legs' => []] + $pax + ['adults' => 1, 'children' => 0, 'infants' => 0],
            'response' => [
                'bookingId' => 'TJS200000000001',
                'tripInfos' => $trips,
                'conditions' => ['dob' => ['adobr' => true, 'cdobr' => true]],
                'totalPriceInfo' => ['totalFareDetail' => ['fC' => ['TF' => 5000, 'BF' => 4200, 'TAF' => 800]]],
            ],
        ];
    }

    protected function submit(array $travellers, array $pax = [], string $fareType = 'REGULAR', bool $return = false)
    {
        session(['flight_booking_draft' => $this->draft($pax, $fareType, $return)]);

        return $this->post(route('flights.book'), [
            'review_booking_id' => 'TJS200000000001',
            'intent' => 'review',
            'contact_email' => 'guest@example.com',
            'contact_phone' => '9876543210',
            'travellers' => $travellers,
        ]);
    }

    protected function adult(string $dob = '1990-05-10', string $name = 'Rahul'): array
    {
        return ['title' => 'Mr', 'first_name' => $name, 'last_name' => 'Probe', 'dob' => $dob];
    }

    public function test_adult_must_be_12_or_older_on_the_departure_date(): void
    {
        // Turns 12 the day after departure.
        $this->submit([$this->adult('2014-11-02')])
            ->assertSessionHasErrors(['travellers.0.dob' => 'Adults must be at least 12 years old on the travel date. Please book this traveller as a child.']);

        $this->submit([$this->adult('2014-11-01')])->assertRedirect(route('flights.confirm.show'));
    }

    public function test_child_must_be_2_to_11_on_the_departure_date(): void
    {
        $child = fn (string $dob) => ['title' => 'Master', 'first_name' => 'Aarav', 'last_name' => 'Probe', 'dob' => $dob];

        $this->submit([$this->adult(), $child('2024-11-02')], ['children' => 1])
            ->assertSessionHasErrors(['travellers.1.dob' => 'Children must be at least 2 years old on the travel date. Please book this traveller as an infant.']);

        $this->submit([$this->adult(), $child('2014-11-01')], ['children' => 1])
            ->assertSessionHasErrors(['travellers.1.dob' => 'Children must be under 12 years old on the travel date. Please book this traveller as an adult.']);

        $this->submit([$this->adult(), $child('2019-03-15')], ['children' => 1])->assertRedirect(route('flights.confirm.show'));
    }

    public function test_infant_must_still_be_under_2_on_the_last_flight(): void
    {
        // Under 2 on 1 Nov, but turns 2 on 10 Nov — before the 20 Nov return.
        $infant = ['title' => 'Master', 'first_name' => 'Kabir', 'last_name' => 'Probe', 'dob' => '2024-11-10'];

        $this->submit([$this->adult(), $infant], ['infants' => 1])->assertRedirect(route('flights.confirm.show'));

        $this->submit([$this->adult(), $infant], ['infants' => 1], return: true)
            ->assertSessionHasErrors(['travellers.1.dob' => 'Infants must be under 2 years old for the whole trip. Please book this traveller as a child.']);
    }

    public function test_senior_citizen_fare_needs_a_dob_showing_60_or_over(): void
    {
        $draft = $this->draft([], 'SENIOR_CITIZEN');
        $draft['response']['conditions'] = []; // DOB required even when the fare doesn't flag it
        session(['flight_booking_draft' => $draft]);
        $this->post(route('flights.book'), [
            'review_booking_id' => 'TJS200000000001', 'intent' => 'review',
            'contact_email' => 'guest@example.com', 'contact_phone' => '9876543210',
            'travellers' => [['title' => 'Mr', 'first_name' => 'Rahul', 'last_name' => 'Probe']],
        ])->assertSessionHasErrors('travellers.0.dob');

        $this->submit([$this->adult('1967-01-01')], fareType: 'SENIOR_CITIZEN')
            ->assertSessionHasErrors(['travellers.0.dob' => 'Senior citizen fares are only for travellers aged 60 or over on the travel date.']);

        $this->submit([$this->adult('1966-11-01')], fareType: 'SENIOR_CITIZEN')->assertRedirect(route('flights.confirm.show'));
    }

    public function test_date_picker_only_offers_valid_birth_dates(): void
    {
        session(['flight_booking_draft' => $this->draft(['children' => 1, 'infants' => 1], return: true)]);

        $this->get(route('flights.passengers.show', ['booking' => 'TJS200000000001']))
            ->assertOk()
            ->assertSee('id="flpDob0" value="" min="1925-11-02" max="2014-11-01"', false)
            ->assertSee('id="flpDob1" value="" min="2014-11-02" max="2024-11-01"', false)
            ->assertSee('id="flpDob2" value="" min="2024-11-21"', false);
    }
}
