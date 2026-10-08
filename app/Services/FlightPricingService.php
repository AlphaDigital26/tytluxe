<?php

namespace App\Services;

use App\Support\FlightSettings;

/**
 * TYTLUXE's flight markup formula — identical philosophy to
 * HotelPricingService, applied to TripJack's TF (Total Fare) instead of a
 * hotel's totalPrice. Kept as a separate class (not a shared base) since the
 * two verticals' commission/GST rules could diverge later — see that
 * class's docblock for the full reasoning behind the margin/GST/Razorpay
 * gross-up order of operations, which is identical here. The markup % itself
 * is the client's to set, in the admin's Flights → Flight Settings page.
 */
class FlightPricingService
{
    protected const GST_RATE_BELOW_THRESHOLD = 0.05;

    protected const GST_RATE_AT_OR_ABOVE_THRESHOLD = 0.18;

    protected const GST_THRESHOLD = 7500.0;

    protected const RAZORPAY_EFFECTIVE_RATE = 0.0236;

    /**
     * The formula's constants, for pages that must recompute price() live
     * in the browser (e.g. as add-ons are picked) and match it exactly.
     * $marginRate: see price().
     *
     * @return array{marginRate: float, gstLow: float, gstHigh: float, gstThreshold: float, razorpayRate: float}
     */
    public static function formula(?float $marginRate = null): array
    {
        return [
            'marginRate' => $marginRate ?? FlightSettings::marginRate(),
            'gstLow' => self::GST_RATE_BELOW_THRESHOLD,
            'gstHigh' => self::GST_RATE_AT_OR_ABOVE_THRESHOLD,
            'gstThreshold' => self::GST_THRESHOLD,
            'razorpayRate' => self::RAZORPAY_EFFECTIVE_RATE,
        ];
    }

    /**
     * Computes the customer-facing price from TripJack's raw TF (Total
     * Fare), with the full breakdown kept traceable for auditing.
     *
     * $marginRate: the markup fixed when the guest picked the fare (saved in
     * the booking draft), so a change in Flight Settings mid-booking doesn't
     * change the price they already saw. Null = today's setting.
     *
     * @return array{
     *   tripjack_total_price: float,
     *   gst_slab: float,
     *   margin_amount: float,
     *   gst_on_margin: float,
     *   pre_razorpay_amount: float,
     *   razorpay_recovery: float,
     *   customer_price: float,
     * }
     */
    public static function price(float $totalFare, ?float $marginRate = null): array
    {
        $gstSlab = $totalFare < self::GST_THRESHOLD
            ? self::GST_RATE_BELOW_THRESHOLD
            : self::GST_RATE_AT_OR_ABOVE_THRESHOLD;

        $marginAmount = $totalFare * ($marginRate ?? FlightSettings::marginRate());
        $gstOnMargin = $marginAmount * $gstSlab;
        $preRazorpayAmount = $totalFare + $marginAmount + $gstOnMargin;

        $customerPrice = $preRazorpayAmount / (1 - self::RAZORPAY_EFFECTIVE_RATE);
        $razorpayRecovery = $customerPrice - $preRazorpayAmount;

        return [
            'tripjack_total_price' => round($totalFare, 2),
            'gst_slab' => $gstSlab,
            'margin_amount' => round($marginAmount, 2),
            'gst_on_margin' => round($gstOnMargin, 2),
            'pre_razorpay_amount' => round($preRazorpayAmount, 2),
            'razorpay_recovery' => round($razorpayRecovery, 2),
            'customer_price' => round($customerPrice, 2),
        ];
    }
}
