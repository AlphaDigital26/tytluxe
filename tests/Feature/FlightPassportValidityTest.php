<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Passport rules on the passenger form:
 * - blocked if it expires before the trip ends (the guest couldn't finish
 *   the journey);
 * - warned, not blocked, if under 6 months are left from departure (the real
 *   rule is the destination's; the airline enforces it at check-in);
 * - TripJack (Shivam Rawat, 2026-10-08): passport details only when pm is
 *   true — never when it's false, even with pped/pid/dobe true — and then
 *   for every passenger including infants.
 */
class FlightPassportValidityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create());
        Http::fake();
    }

    protected function draft(array $pcs = ['pm' => true], array $pax = []): array
    {
        $seg = fn ($id, $from, $to, $dt) => ['id' => $id, 'da' => ['code' => $from], 'aa' => ['code' => $to], 'fD' => ['aI' => ['code' => 'EK'], 'fN' => '501'], 'dt' => $dt, 'at' => substr($dt, 0, 11).'12:00'];

        return [
            'bookingId' => 'TJS200000000001',
            'resultsUrl' => route('flights.search'),
            'expiresAt' => now()->addMinutes(10)->timestamp,
            'context' => ['tripType' => 'return', 'from' => 'DEL', 'to' => 'DXB', 'departDate' => '2026-12-01', 'returnDate' => '2026-12-20',
                'cabinClass' => 'ECONOMY', 'fareType' => 'REGULAR', 'legs' => []] + $pax + ['adults' => 1, 'children' => 0, 'infants' => 0],
            'response' => [
                'bookingId' => 'TJS200000000001',
                'tripInfos' => [['sI' => [$seg('1', 'DEL', 'DXB', '2026-12-01T09:00')]], ['sI' => [$seg('2', 'DXB', 'DEL', '2026-12-20T09:00')]]],
                'conditions' => ['pcs' => $pcs],
                'totalPriceInfo' => ['totalFareDetail' => ['fC' => ['TF' => 30000, 'BF' => 25000, 'TAF' => 5000]]],
            ],
        ];
    }

    protected function traveller(string $expiry, string $name = 'Rahul', string $title = 'Mr'): array
    {
        return ['title' => $title, 'first_name' => $name, 'last_name' => 'Probe', 'dob' => '2025-06-01',
            'passport_number' => 'Z1234567', 'passport_expiry' => $expiry, 'passport_nationality' => 'IN', 'passport_issue_date' => '2020-01-01'];
    }

    protected function submit(array $travellers, array $pcs = ['pm' => true], array $pax = [])
    {
        session(['flight_booking_draft' => $this->draft($pcs, $pax)]);

        return $this->post(route('flights.book'), [
            'review_booking_id' => 'TJS200000000001', 'intent' => 'review',
            'contact_email' => 'guest@example.com', 'contact_phone' => '9876543210',
            'travellers' => $travellers,
        ]);
    }

    public function test_passport_expiring_before_the_trip_ends_is_refused(): void
    {
        // Valid on departure (1 Dec) but expires before the 20 Dec return.
        $this->submit([$this->traveller('2026-12-10')])
            ->assertSessionHasErrors(['travellers.0.passport_expiry' => 'This passport expires before your trip ends. Please use a passport that is valid for the whole journey.']);
    }

    public function test_under_six_months_from_departure_is_only_a_warning(): void
    {
        $this->submit([$this->traveller('2027-03-15')])->assertRedirect(route('flights.confirm.show'));

        $this->get(route('flights.confirm.show'))
            ->assertOk()
            ->assertSee('less than 6 months left from your departure date');
    }

    public function test_six_months_or_more_shows_no_warning(): void
    {
        $this->submit([$this->traveller('2027-06-05')])->assertRedirect(route('flights.confirm.show'));

        $this->get(route('flights.confirm.show'))->assertOk()->assertDontSee('less than 6 months left');
    }

    public function test_no_passport_is_sent_when_pm_is_false_even_if_other_flags_are_true(): void
    {
        $this->submit([$this->traveller('2026-12-10')], ['pm' => false, 'pped' => true, 'pid' => true, 'dobe' => true])
            ->assertRedirect(route('flights.confirm.show'));

        $info = session('flight_booking_draft.passengerReview.travellerInfo')[0];
        foreach (['pNum', 'eD', 'pNat', 'pid'] as $field) {
            $this->assertArrayNotHasKey($field, $info);
        }
    }

    public function test_infants_need_passport_details_too_when_pm_is_true(): void
    {
        $adult = $this->traveller('2027-06-05');
        $adult['dob'] = '1990-01-01';
        $infant = ['title' => 'Master', 'first_name' => 'Kabir', 'last_name' => 'Probe', 'dob' => '2025-06-01'];

        $this->submit([$adult, $infant], pax: ['infants' => 1])
            ->assertSessionHasErrors(['travellers.1.passport_number', 'travellers.1.passport_expiry']);
    }

    public function test_form_limits_the_date_picker_and_carries_the_warning_date(): void
    {
        session(['flight_booking_draft' => $this->draft()]);

        $this->get(route('flights.passengers.show', ['booking' => 'TJS200000000001']))
            ->assertOk()
            ->assertSee('min="2026-12-20"', false)
            ->assertSee('data-warn-before="2027-06-01"', false);
    }
}
