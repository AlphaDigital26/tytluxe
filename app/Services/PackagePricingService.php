<?php

namespace App\Services;

use App\Models\Package;
use App\Models\PackageDeparture;

/**
 * What a guest pays for a group package (client's decision, 2026-10-14):
 * the admin's per-person price (sharing price or "Starting From", plus the
 * departure's peak-date extra) × travellers, + 5% GST (tour operator rate),
 * + the Razorpay fee (~2.36%) grossed up on each payment so TYT Luxe isn't
 * left paying it.
 *
 * Paying a booking amount now and the balance later splits the package
 * price; GST and the gateway fee are worked out on each part when it is
 * paid, so the two payments add up to the same price as paying in full
 * (bar the gateway fee's own rounding).
 */
class PackagePricingService
{
    public const GST_RATE = 0.05;

    protected const RAZORPAY_EFFECTIVE_RATE = 0.0236;

    /**
     * One payment: a share of the package price with its GST and gateway fee.
     *
     * @return array{base: float, gst: float, gateway: float, total: float}
     */
    public static function charge(float $base): array
    {
        $base = round(max(0, $base), 2);
        $gst = round($base * self::GST_RATE, 2);
        $total = $base > 0 ? round(($base + $gst) / (1 - self::RAZORPAY_EFFECTIVE_RATE), 2) : 0.0;

        return ['base' => $base, 'gst' => $gst, 'gateway' => round($total - $base - $gst, 2), 'total' => $total];
    }

    /**
     * The full quote for a booking. $plan: 'part' (booking amount now, balance
     * later) or 'full'. 'part' falls back to 'full' when the package has no
     * booking amount, or it would cover the whole price anyway.
     *
     * @return array{per_person: float, travellers: int, subtotal: float, gst: float, trip_total: float, plan: string, now: array{base: float, gst: float, gateway: float, total: float}, balance: array{base: float, gst: float, gateway: float, total: float}}
     */
    public static function quote(Package $package, PackageDeparture $departure, float $optionPrice, int $travellers, string $plan): array
    {
        $perPerson = round($optionPrice + (float) $departure->extra_price, 2);
        $subtotal = round($perPerson * $travellers, 2);
        $deposit = round((float) $package->booking_amount * $travellers, 2);

        $plan = ($plan === 'part' && $deposit > 0 && $deposit < $subtotal) ? 'part' : 'full';
        $nowBase = $plan === 'part' ? $deposit : $subtotal;

        $gst = round($subtotal * self::GST_RATE, 2);

        return [
            'per_person' => $perPerson,
            'travellers' => $travellers,
            'subtotal' => $subtotal,
            'gst' => $gst,
            'trip_total' => round($subtotal + $gst, 2),
            'plan' => $plan,
            'now' => self::charge($nowBase),
            'balance' => self::charge($subtotal - $nowBase),
        ];
    }

    /** The constants, for the checkout page's live price summary (must match charge()). */
    public static function formula(): array
    {
        return ['gstRate' => self::GST_RATE, 'razorpayRate' => self::RAZORPAY_EFFECTIVE_RATE];
    }
}
