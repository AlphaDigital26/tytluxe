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
 *
 * GST differs from hotels: a flat 18% on our markup (our service fee) at
 * any fare (client's decision, 2026-10-10). The hotels' 5%/18% split at
 * ₹7,500 is a hotel-room rule. The airline's own GST is already inside TF.
 */
class FlightPricingService
{
    protected const GST_RATE = 0.18;

    protected const RAZORPAY_EFFECTIVE_RATE = 0.0236;

    /** Our service charge per reschedule, before GST (founder's decision, 2026-10-13). */
    public const RESCHEDULE_SERVICE_FEE = 100.0;

    /**
     * An amount the guest pays with Razorpay's ~2.36% fee added, so TYT
     * Luxe isn't left paying it — for charges taken at TripJack's own price
     * (seats/meals/baggage added after booking, reschedules).
     */
    public static function withGatewayFee(float $amount): float
    {
        return $amount > 0 ? round($amount / (1 - self::RAZORPAY_EFFECTIVE_RATE), 2) : 0.0;
    }

    /**
     * What the guest pays for a reschedule: TripJack's reissue TF (no
     * markup) + our service charge + 18% GST on it, with the gateway fee
     * added. A negative total (a much cheaper new flight) is the amount
     * refunded to the guest instead — no gateway fee on that.
     *
     * @return array{tripjack: float, service_fee: float, gst: float, gateway: float, total: float}
     */
    public static function reschedule(float $tripjackAmount): array
    {
        $fee = self::RESCHEDULE_SERVICE_FEE;
        $gst = round($fee * self::GST_RATE, 2);
        $net = round($tripjackAmount + $fee + $gst, 2);
        $total = $net > 0 ? self::withGatewayFee($net) : $net;

        return [
            'tripjack' => round($tripjackAmount, 2),
            'service_fee' => $fee,
            'gst' => $gst,
            'gateway' => round(max(0, $total - $net), 2),
            'total' => $total,
        ];
    }

    /**
     * The formula's constants, for pages that must recompute price() live
     * in the browser (e.g. as add-ons are picked) and match it exactly.
     * $marginRate: see price().
     *
     * @return array{marginRate: float, gstRate: float, razorpayRate: float}
     */
    public static function formula(?float $marginRate = null): array
    {
        return [
            'marginRate' => $marginRate ?? FlightSettings::marginRate(),
            'gstRate' => self::GST_RATE,
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
        $gstSlab = self::GST_RATE;

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
