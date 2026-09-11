<?php

namespace App\Services;

/**
 * TYTLUXE's hotel markup formula — founder-defined, do not reinterpret.
 *
 * TripJack's pricing.totalPrice (already inclusive of TripJack's own hotel
 * taxes) is treated as our supplier cost. On top of it we recover, in order:
 *   1. A 10% margin.
 *   2. GST on that margin only (5% below ₹7,500, 18% at/above ₹7,500 —
 *      checked against the raw TripJack price, before any markup).
 *   3. Razorpay's effective 2.36% (2% fee + 18% GST on that fee), which
 *      Razorpay deducts from the FINAL amount the customer pays — so it
 *      must be grossed up via division, never added on top:
 *          correct:   preRazorpay / (1 - 0.0236)
 *          wrong:     preRazorpay * 1.0236
 *      Dividing by a smaller number vs. multiplying by a slightly-larger
 *      one look similar but aren't: gross-up guarantees TYTLUXE still nets
 *      exactly 10% of the TripJack price after Razorpay's cut; multiplying
 *      would under-recover it.
 *
 * This is the only place this formula is implemented — every screen that
 * shows or charges a hotel price (search, detail, review/checkout, booking
 * record, payment) must call price() rather than re-deriving it, so the
 * business rule only ever needs to change in one place.
 */
class HotelPricingService
{
    protected const MARGIN_RATE = 0.10;

    protected const GST_RATE_BELOW_THRESHOLD = 0.05;

    protected const GST_RATE_AT_OR_ABOVE_THRESHOLD = 0.18;

    protected const GST_THRESHOLD = 7500.0;

    protected const RAZORPAY_EFFECTIVE_RATE = 0.0236;

    /**
     * Computes the customer-facing price from TripJack's raw totalPrice,
     * with the full breakdown kept traceable for auditing.
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
    public static function price(float $tripjackTotalPrice): array
    {
        // The ₹7,500 threshold is checked against TripJack's raw price,
        // before any markup — never against the marked-up customer price.
        $gstSlab = $tripjackTotalPrice < self::GST_THRESHOLD
            ? self::GST_RATE_BELOW_THRESHOLD
            : self::GST_RATE_AT_OR_ABOVE_THRESHOLD;

        $marginAmount = $tripjackTotalPrice * self::MARGIN_RATE;
        $gstOnMargin = $marginAmount * $gstSlab;
        $preRazorpayAmount = $tripjackTotalPrice + $marginAmount + $gstOnMargin;

        // Gross-up, not a flat add-on — see class docblock.
        $customerPrice = $preRazorpayAmount / (1 - self::RAZORPAY_EFFECTIVE_RATE);
        $razorpayRecovery = $customerPrice - $preRazorpayAmount;

        return [
            'tripjack_total_price' => round($tripjackTotalPrice, 2),
            'gst_slab' => $gstSlab,
            'margin_amount' => round($marginAmount, 2),
            'gst_on_margin' => round($gstOnMargin, 2),
            'pre_razorpay_amount' => round($preRazorpayAmount, 2),
            'razorpay_recovery' => round($razorpayRecovery, 2),
            'customer_price' => round($customerPrice, 2),
        ];
    }
}
