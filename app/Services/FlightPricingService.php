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
     * Our management fee per booking, before 18% GST (2026-10-13), added on
     * top of the final price. The ₹100 is never refunded, not even on a
     * free or airline-caused cancellation; its GST is.
     */
    public const MANAGEMENT_FEE = 100.0;

    /** ₹100 + ₹18 GST. */
    public static function managementFeeWithGst(): float
    {
        return round(self::MANAGEMENT_FEE * (1 + self::GST_RATE), 2);
    }

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
     * @return array{marginRate: float, gstRate: float, razorpayRate: float, managementFee: float, managementFeeGst: float}
     */
    public static function formula(?float $marginRate = null): array
    {
        return [
            'marginRate' => $marginRate ?? FlightSettings::marginRate(),
            'gstRate' => self::GST_RATE,
            'razorpayRate' => self::RAZORPAY_EFFECTIVE_RATE,
            'managementFee' => self::MANAGEMENT_FEE,
            'managementFeeGst' => round(self::MANAGEMENT_FEE * self::GST_RATE, 2),
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
     * $withManagementFee: false for per-person fares on search results and
     * the fare calendar — the fee is once per booking, added at review.
     *
     * @return array{
     *   tripjack_total_price: float,
     *   gst_slab: float,
     *   margin_amount: float,
     *   gst_on_margin: float,
     *   management_fee: float,
     *   management_fee_gst: float,
     *   pre_razorpay_amount: float,
     *   razorpay_recovery: float,
     *   customer_price: float,
     * }
     */
    public static function price(float $totalFare, ?float $marginRate = null, bool $withManagementFee = true): array
    {
        $gstSlab = self::GST_RATE;

        $marginAmount = $totalFare * ($marginRate ?? FlightSettings::marginRate());
        $gstOnMargin = $marginAmount * $gstSlab;
        $preRazorpayAmount = $totalFare + $marginAmount + $gstOnMargin;

        $grossedUp = $preRazorpayAmount / (1 - self::RAZORPAY_EFFECTIVE_RATE);
        $razorpayRecovery = $grossedUp - $preRazorpayAmount;

        // Management fee + GST go on top of the final price as they are,
        // not through the gross-up.
        $managementFee = $withManagementFee ? self::MANAGEMENT_FEE : 0.0;
        $managementFeeGst = round($managementFee * self::GST_RATE, 2);
        $customerPrice = round($grossedUp, 2) + $managementFee + $managementFeeGst;

        return [
            'tripjack_total_price' => round($totalFare, 2),
            'gst_slab' => $gstSlab,
            'margin_amount' => round($marginAmount, 2),
            'gst_on_margin' => round($gstOnMargin, 2),
            'management_fee' => $managementFee,
            'management_fee_gst' => $managementFeeGst,
            'pre_razorpay_amount' => round($preRazorpayAmount, 2),
            'razorpay_recovery' => round($razorpayRecovery, 2),
            'customer_price' => round($customerPrice, 2),
        ];
    }

}
