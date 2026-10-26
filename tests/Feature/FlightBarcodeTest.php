<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\User;
use App\Support\FlightBarcode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** TripJack's "Flights 2D Barcode User Guide": barcodes on every itinerary. */
class FlightBarcodeTest extends TestCase
{
    use RefreshDatabase;

    public function test_barcode_text_follows_the_guide_field_by_field(): void
    {
        // 1 May 2023 is day 121, as in the guide's Julian date example.
        $text = FlightBarcode::text('Srivastava', 'Pankaj', 'ABC123', 'DEL', 'BOM', '6E', '112', '2023-05-01');

        $this->assertSame('M1SRIVASTAVA/PANKAJ    ABC123 DELBOM6E 0112 121Y000000000000', $text);
        $this->assertSame(60, strlen($text)); // 1+1+20+1+7+3+3+3+5+3+1+4+5+1+2
        $this->assertSame('0001 ', substr(FlightBarcode::text('A', 'B', 'X', 'DEL', 'BOM', 'AI', '1', '2023-01-01'), 39, 5));
        $this->assertSame('11111', substr(FlightBarcode::text('A', 'B', 'X', 'DEL', 'BOM', 'AI', '11111', '2023-01-01'), 39, 5));
        // Long names are cut to 20 characters; middle names join the first name.
        $this->assertSame('VENKATARAGHAVAN/SURE', substr(FlightBarcode::text('Venkataraghavan', 'Suresh Kumar', 'X', 'MAA', 'DEL', 'AI', '440', '2026-12-01'), 2, 20));
        $this->assertSame('RAO/ANANYAPRIYA      ', substr(FlightBarcode::text('Rao', 'Ananya Priya', 'X', 'MAA', 'DEL', 'AI', '440', '2026-12-01'), 2, 21));
    }

    public function test_airlines_tripjack_cannot_change_online_hide_self_service(): void
    {
        $booking = fn (array $codes) => new Booking(['vertical' => 'flight', 'flight_itinerary' => ['segments' => array_map(fn ($c) => ['airlineCode' => $c], $codes)]]);

        foreach (['cancel', 'reschedule', 'extras'] as $what) {
            $this->assertTrue($booking(['6E'])->flightSupportsOnline($what), "6E $what");
            $this->assertFalse($booking(['FZ'])->flightSupportsOnline($what), "FZ $what");
            $this->assertFalse($booking(['6E', 'G9'])->flightSupportsOnline($what), "6E+G9 $what"); // any offline airline on the trip
        }
        // Star Air: cancel online, but no date change or add-ons.
        $this->assertTrue($booking(['S5'])->flightSupportsOnline('cancel'));
        $this->assertFalse($booking(['S5'])->flightSupportsOnline('reschedule'));
        $this->assertFalse($booking(['S5'])->flightSupportsOnline('extras'));
        // Not in the matrix: TripJack's own answer decides.
        $this->assertTrue($booking(['8M'])->flightSupportsOnline('reschedule'));
    }

    public function test_amendment_errors_from_the_reference_have_plain_messages(): void
    {
        foreach (['1152' => 'close to departure', '2603' => 'checked in', '2512' => 'still being processed', '1078' => 'infant', '1079' => 'without an adult',
            '1171' => 'seats, meals or baggage', '2621' => 'Same-day free cancellation', '8113' => 'No flights are available'] as $code => $text) {
            $this->assertStringContainsString($text, \App\Services\TripJack\TripJackFlightErrorCatalog::describe($code)['message'], $code);
        }
    }

    public function test_e_ticket_has_one_barcode_per_passenger_per_flight_but_none_for_infants(): void
    {
        $user = User::factory()->create();
        $booking = Booking::create([
            'reference' => 'TYTBARCODE1', 'user_id' => $user->id, 'vertical' => 'flight', 'status' => 'confirmed',
            'guest_email' => 'a@gmail.com', 'lead_guest_name' => 'Karan Mehra', 'tripjack_booking_id' => 'TJS1', 'base_amount' => 9000, 'tax_amount' => 1000, 'total_amount' => 10000, 'currency' => 'INR',
            'pax_adults' => 2, 'pax_children' => 0, 'pax_infants' => 1,
            'tripjack_flight_pnr' => ['DEL-BOM' => 'ABC123', 'BOM-DEL' => 'XYZ789'],
            'flight_segments_payload' => ['travellerInfo' => [
                ['ti' => 'Mr', 'pt' => 'ADULT', 'fN' => 'Karan', 'lN' => 'Mehra'],
                ['ti' => 'Mrs', 'pt' => 'ADULT', 'fN' => 'Simran', 'lN' => 'Mehra'],
                ['ti' => 'Ms', 'pt' => 'INFANT', 'fN' => 'Kiara', 'lN' => 'Mehra', 'dob' => '2026-02-02'],
            ]],
            'flight_itinerary' => ['segments' => [
                ['from' => 'DEL', 'to' => 'BOM', 'airline' => 'IndiGo', 'airlineCode' => '6E', 'flightNo' => '2112', 'departs' => '2026-12-09T06:00'],
                ['from' => 'BOM', 'to' => 'DEL', 'airline' => 'IndiGo', 'airlineCode' => '6E', 'flightNo' => '515', 'departs' => '2026-12-12T20:00'],
            ]],
        ]);

        $codes = FlightBarcode::forBooking($booking);
        $this->assertCount(4, $codes); // 2 adults x 2 flights; the infant gets none
        $this->assertSame(['DEL-BOM', 'DEL-BOM', 'BOM-DEL', 'BOM-DEL'], array_column($codes, 'sector'));
        $this->assertSame('XYZ789', $codes[2]['pnr']);
        $this->assertStringStartsWith('M1MEHRA/KARAN', $codes[0]['text']);
        $this->assertStringContainsString('ABC123 DELBOM6E 2112 343Y', $codes[0]['text']);

        $html = view('pdf.e-ticket', ['booking' => $booking])->render();
        $this->assertSame(4, substr_count($html, 'Your Airlines Reference:'));
        $this->assertSame(4, preg_match_all('/<img src="data:image\/png;base64,[^"]+" alt="Barcode /', $html));

        $this->actingAs($user)->get(route('flight.booking.eticket', $booking->reference))
            ->assertOk()->assertHeader('content-type', 'application/pdf');
    }
}
