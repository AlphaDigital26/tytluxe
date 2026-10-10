<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\User;
use App\Services\FlightBookingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The flight e-ticket: PNR, flights with terminals and baggage, and every
 * passenger's ticket number — downloadable once the airline has ticketed.
 */
class FlightETicketTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        \Illuminate\Support\Facades\Mail::fake();
        \Illuminate\Support\Facades\Http::fake();
    }

    protected function details(): array
    {
        return [
            'order' => ['status' => 'SUCCESS'],
            'itemInfos' => ['AIR' => [
                'tripInfos' => [[
                    'sI' => [[
                        'fD' => ['aI' => ['code' => '6E', 'name' => 'IndiGo'], 'fN' => '2134', 'eT' => '321'],
                        'da' => ['code' => 'BOM', 'city' => 'Mumbai', 'name' => 'Chhatrapati Shivaji International Airport', 'terminal' => 'Terminal 2', 'country' => 'India'],
                        'aa' => ['code' => 'DEL', 'city' => 'Delhi', 'name' => 'Indira Gandhi International Airport', 'terminal' => '1', 'country' => 'India'],
                        'dt' => now()->addDays(20)->format('Y-m-d').'T06:00', 'at' => now()->addDays(20)->format('Y-m-d').'T08:10', 'duration' => 130,
                    ]],
                    'totalPriceList' => [['fd' => [
                        'ADULT' => ['cc' => 'ECONOMY', 'bI' => ['iB' => '15 Kg', 'cB' => '7 Kg']],
                        'INFANT' => ['cc' => 'ECONOMY', 'bI' => ['iB' => '0 Kg', 'cB' => '0 Kg']],
                    ]]],
                ]],
                'travellerInfos' => [
                    ['ti' => 'Ms', 'fN' => 'Asha', 'lN' => 'Rao', 'pnrDetails' => ['BOM-DEL' => 'XYZ789'], 'ticketNumberDetails' => ['BOM-DEL' => '0981234567890']],
                    ['ti' => 'Mr', 'fN' => 'Ravi', 'lN' => 'Rao', 'pnrDetails' => ['BOM-DEL' => 'XYZ789'], 'ticketNumberDetails' => ['BOM-DEL' => '0981234567891']],
                ],
            ]],
        ];
    }

    protected function ticketed(array $extra = []): Booking
    {
        $booking = Booking::forceCreate(array_merge([
            'reference' => 'TYT'.strtoupper(substr(md5(uniqid()), 0, 8)),
            'user_id' => User::factory()->create()->id,
            'vertical' => 'flight',
            'status' => 'confirmed',
            'guest_email' => 'guest@example.com',
            'guest_phone' => '9876543210',
            'tripjack_booking_id' => 'TJS'.random_int(100000000, 999999999),
            'flight_route' => 'BOM-DEL',
            'flight_departure_date' => now()->addDays(20)->toDateString(),
            'pax_adults' => 2, 'pax_children' => 0, 'pax_infants' => 0,
            'lead_guest_name' => 'Asha Rao',
            'base_amount' => 5000, 'total_amount' => 5600, 'tripjack_total_price' => 5000,
            'currency' => 'INR',
            'flight_segments_payload' => ['travellerInfo' => [
                ['ti' => 'Ms', 'pt' => 'ADULT', 'fN' => 'Asha', 'lN' => 'Rao'],
                ['ti' => 'Mr', 'pt' => 'ADULT', 'fN' => 'Ravi', 'lN' => 'Rao'],
            ]],
        ], $extra));

        $booking->update([
            'tripjack_flight_pnr' => ['BOM-DEL' => 'XYZ789'],
            'tripjack_flight_ticket_numbers' => FlightBookingService::ticketsByTraveller($this->details()),
            'flight_itinerary' => FlightBookingService::itinerarySummary($this->details()),
        ]);

        return $booking->fresh();
    }

    public function test_itinerary_keeps_terminals_and_baggage_for_the_e_ticket(): void
    {
        $segment = FlightBookingService::itinerarySummary($this->details())['segments'][0];

        $this->assertSame('Terminal 2', $segment['fromTerminal']);
        $this->assertSame('Indira Gandhi International Airport', $segment['toAirport']);
        $this->assertSame(130, $segment['duration']);
        $this->assertSame('ECONOMY', $segment['cabin']);
        $this->assertSame(['checkin' => '15 Kg', 'cabin' => '7 Kg'], $segment['baggage']['ADULT']);
    }

    public function test_e_ticket_shows_pnr_flight_terminals_baggage_and_every_ticket_number(): void
    {
        $booking = $this->ticketed();

        $html = view('pdf.e-ticket', ['booking' => $booking])->render();

        foreach (['XYZ789', 'IndiGo', '6E-2134', 'Terminal 2', 'Terminal 1', '06:00', '08:10', '2h 10m', 'Check-in 15 Kg, Cabin 7 Kg',
            'Ms Asha Rao', '0981234567890', 'Mr Ravi Rao', '0981234567891'] as $expected) {
            $this->assertStringContainsString($expected, $html);
        }
        // No infant on this booking, so no infant baggage line.
        $this->assertStringNotContainsString('Infant', $html);

        $this->actingAs($booking->user)
            ->get(route('flight.booking.eticket', $booking->reference))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_only_the_owner_of_a_ticketed_standing_booking_can_download_it(): void
    {
        $booking = $this->ticketed();
        $this->actingAs(User::factory()->create())->get(route('flight.booking.eticket', $booking->reference))->assertForbidden();

        // Not ticketed yet, cancelled, or a hotel: no e-ticket.
        $notTicketed = $this->ticketed();
        $notTicketed->update(['tripjack_flight_pnr' => null]);
        $this->actingAs($notTicketed->user)->get(route('flight.booking.eticket', $notTicketed->reference))->assertNotFound();

        $cancelled = $this->ticketed();
        $cancelled->update(['status' => 'cancelled']);
        $this->actingAs($cancelled->user)->get(route('flight.booking.eticket', $cancelled->reference))->assertNotFound();
    }

    public function test_confirmation_page_offers_the_download(): void
    {
        $booking = $this->ticketed();

        $this->actingAs($booking->user)
            ->get(route('hotel.booking.confirmation', $booking->reference))
            ->assertOk()
            ->assertSee(route('flight.booking.eticket', $booking->reference));
    }

    public function test_older_bookings_without_saved_flight_details_still_get_a_ticket(): void
    {
        $booking = $this->ticketed(['flight_legs' => [['src' => 'BOM', 'dest' => 'DEL', 'departureDate' => now()->addDays(20)->toDateString()]]]);
        $booking->update(['flight_itinerary' => null]);

        $html = view('pdf.e-ticket', ['booking' => $booking->fresh()])->render();

        $this->assertStringContainsString('XYZ789', $html);
        $this->assertStringContainsString('BOM', $html);
        $this->assertStringContainsString('0981234567891', $html);
    }
}
