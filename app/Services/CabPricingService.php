<?php

namespace App\Services;

use App\Support\CabSettings;

/**
 * What a guest pays for a cab, following the flight formula
 * (FlightPricingService): TripJack's total fare (their taxes included)
 * + our fixed ₹ markup + 5% GST on the markup, with Razorpay's ~2.36% fee
 * grossed up on top so TYT Luxe isn't left paying it.
 *
 * The markup is per one-way trip, set in Cabs → Cab Settings; a round trip
 * (onward + return) pays it twice. No management fee on cabs.
 */
class CabPricingService
{
    protected const MARKUP_GST_RATE = 0.05;

    protected const RAZORPAY_EFFECTIVE_RATE = 0.0236;

    /** Our markup for a trip: once per leg. */
    public static function markupFor(bool $roundTrip): float
    {
        return round(CabSettings::markup() * ($roundTrip ? 2 : 1), 2);
    }

    /**
     * @return array{supplier: float, markup: float, gst: float, gateway: float, total: float}
     */
    public static function price(float $supplier, float $markup): array
    {
        $supplier = round(max(0, $supplier), 2);
        $markup = round(max(0, $markup), 2);
        $gst = round($markup * self::MARKUP_GST_RATE, 2);
        $net = $supplier + $markup + $gst;
        $total = $net > 0 ? round($net / (1 - self::RAZORPAY_EFFECTIVE_RATE), 2) : 0.0;

        return [
            'supplier' => $supplier,
            'markup' => $markup,
            'gst' => $gst,
            'gateway' => round($total - $net, 2),
            'total' => $total,
        ];
    }
}
