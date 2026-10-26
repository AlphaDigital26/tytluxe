<?php

namespace App\Support;

use chillerlan\QRCode\Output\QROutputInterface;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Illuminate\Support\Carbon;

/**
 * The 2D boarding barcode TripJack requires on every itinerary (Ministry of
 * Civil Aviation mandate): one per passenger per flight, no infants, text
 * in the IATA boarding-pass (BCBP) layout from TripJack's "Flights 2D
 * Barcode User Guide", e.g. "M1MEHRA/KARAN         ABC123 DELBOM6E 0112 226Y00000000000".
 */
class FlightBarcode
{
    /**
     * @param  string  $lastName  passenger last name
     * @param  string  $firstName  first (and middle) name
     */
    public static function text(string $lastName, string $firstName, string $pnr, string $from, string $to, string $carrier, string $flightNo, \DateTimeInterface|string $date): string
    {
        // "<Last Name>/<FirstName><MiddleName>" — first and middle run together.
        $name = self::clean($lastName).'/'.str_replace(' ', '', self::clean($firstName));
        $digits = preg_replace('/\D/', '', $flightNo);
        $suffix = preg_replace('/[^A-Z]/', '', strtoupper($flightNo)); // operational suffix letter, if any
        $flight = strlen($digits) >= 5 ? substr($digits, 0, 5) : str_pad(str_pad($digits, 4, '0', STR_PAD_LEFT).$suffix, 5);

        return 'M'                                         // format code
            .'1'                                           // legs encoded
            .str_pad(substr($name, 0, 20), 20)             // passenger name
            .' '                                           // e-ticket indicator: blank (guide)
            .str_pad(substr(strtoupper($pnr), 0, 7), 7)    // operating carrier PNR
            .strtoupper(substr($from, 0, 3))               // from airport
            .strtoupper(substr($to, 0, 3))                 // to airport
            .str_pad(strtoupper(substr($carrier, 0, 3)), 3) // operating carrier
            .$flight                                       // flight number, 5 chars
            .str_pad((string) Carbon::parse($date)->dayOfYear, 3, '0', STR_PAD_LEFT) // Julian day
            .'Y'                                           // compartment
            .'0000'                                        // seat
            .'00000'                                       // check-in sequence
            .'0'                                           // passenger status
            .'00';                                         // variable field size
    }

    /** PNG data URI of the barcode, for <img> in the PDF (dompdf). */
    public static function image(string $text): string
    {
        return (new QRCode(new QROptions([
            'outputType' => QROutputInterface::GDIMAGE_PNG,
            'outputBase64' => true,
            'scale' => 4,
            'quietzoneSize' => 1,
        ])))->render($text);
    }

    /**
     * One entry per non-infant passenger per flight, in itinerary order
     * (journey-wise), with the PNR for that flight's sector.
     *
     * @return array<int, array{passenger: string, sector: string, flight: string, pnr: string, text: string}>
     */
    public static function forBooking(\App\Models\Booking $booking): array
    {
        $segments = $booking->flight_itinerary['segments'] ?? [];
        $pnrs = collect($booking->tripjack_flight_pnr ?? []);
        $travellers = collect($booking->flight_segments_payload['travellerInfo'] ?? [])->reject(fn ($t) => ($t['pt'] ?? '') === 'INFANT');
        if (! $segments || $pnrs->isEmpty() || $travellers->isEmpty()) {
            return [];
        }

        $out = [];
        foreach ($segments as $seg) {
            $sector = ($seg['from'] ?? '').'-'.($seg['to'] ?? '');
            $pnr = (string) ($pnrs[$sector] ?? $pnrs->first());
            foreach ($travellers as $t) {
                $out[] = [
                    'passenger' => trim(($t['ti'] ?? '').' '.($t['fN'] ?? '').' '.($t['lN'] ?? '')),
                    'sector' => $sector,
                    'flight' => ($seg['airlineCode'] ?? '').'-'.($seg['flightNo'] ?? ''),
                    'pnr' => $pnr,
                    'text' => self::text($t['lN'] ?? '', $t['fN'] ?? '', $pnr, $seg['from'] ?? '', $seg['to'] ?? '', $seg['airlineCode'] ?? '', (string) ($seg['flightNo'] ?? ''), $seg['departs'] ?? now()),
                ];
            }
        }

        return $out;
    }

    private static function clean(string $value): string
    {
        return preg_replace('/[^A-Z ]/', '', strtoupper(trim(preg_replace('/\s+/', ' ', $value))));
    }
}
