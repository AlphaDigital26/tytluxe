<?php

namespace App\Services;

use App\Support\HotelSettings;

/**
 * TYTLUXE's hotel markup formula — founder-defined, do not reinterpret.
 *
 * TripJack's pricing.totalPrice (already inclusive of TripJack's own hotel
 * taxes) is treated as our supplier cost. On top of it we recover, in order:
 *   1. The client's margin — 10% unless changed in the admin's Hotels →
 *      Hotel Settings page (HotelSettings::markupPercent()).
 *   2. GST on that margin only (5% below ₹7,500, 18% at/above ₹7,500 —
 *      checked per room per night against the raw TripJack price, before
 *      any markup).
 *   (Then, after step 3: our flat management fee (MANAGEMENT_FEE, once per
 *   booking) plus 18% GST, added as is. The ₹50 is never refunded; its
 *   GST is — see RefundPolicy.)
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
    protected const GST_RATE_BELOW_THRESHOLD = 0.05;

    protected const GST_RATE_AT_OR_ABOVE_THRESHOLD = 0.18;

    protected const GST_THRESHOLD = 7500.0;

    protected const RAZORPAY_EFFECTIVE_RATE = 0.0236;

    /** Our management fee per booking (founder, 2026-10-13), before GST. */
    public const MANAGEMENT_FEE = 50.0;

    public const MANAGEMENT_FEE_GST_RATE = 0.18;

    /** ₹50 + ₹9 GST — the non-refundable part of every hotel booking. */
    public static function managementFeeWithGst(): float
    {
        return round(self::MANAGEMENT_FEE * (1 + self::MANAGEMENT_FEE_GST_RATE), 2);
    }

    /** Rooms × nights for a stay, never below 1 — the divisor for the GST slab. */
    public static function roomNights(?string $checkIn, ?string $checkOut, int|string|null $rooms): int
    {
        $nights = ($checkIn && $checkOut)
            ? (int) \Illuminate\Support\Carbon::parse($checkIn)->startOfDay()->diffInDays(\Illuminate\Support\Carbon::parse($checkOut)->startOfDay())
            : 1;

        return max(1, $nights) * max(1, (int) $rooms);
    }

    /**
     * The formula's fixed rates, for the admin's Hotel Settings price
     * example (which previews a markup before it is saved).
     *
     * @return array{gstLow: float, gstHigh: float, gstThreshold: float, razorpayRate: float, managementFee: float, managementFeeGst: float}
     */
    public static function formula(): array
    {
        return [
            'gstLow' => self::GST_RATE_BELOW_THRESHOLD,
            'gstHigh' => self::GST_RATE_AT_OR_ABOVE_THRESHOLD,
            'gstThreshold' => self::GST_THRESHOLD,
            'razorpayRate' => self::RAZORPAY_EFFECTIVE_RATE,
            'managementFee' => self::MANAGEMENT_FEE,
            'managementFeeGst' => round(self::MANAGEMENT_FEE * self::MANAGEMENT_FEE_GST_RATE, 2),
        ];
    }
    /**
     * Computes the customer-facing price from TripJack's raw totalPrice,
     * with the full breakdown kept traceable for auditing.
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
    public static function price(float $tripjackTotalPrice, int $roomNights = 1): array
    {
        // The ₹7,500 threshold is checked per room per night (client's CA,
        // 2026-10-15) against TripJack's raw price, before any markup —
        // never against the stay total or the marked-up customer price.
        // $roomNights = rooms × nights of the stay (see roomNights()).
        $perRoomNight = $tripjackTotalPrice / max(1, $roomNights);
        $gstSlab = $perRoomNight < self::GST_THRESHOLD
            ? self::GST_RATE_BELOW_THRESHOLD
            : self::GST_RATE_AT_OR_ABOVE_THRESHOLD;

        $marginAmount = $tripjackTotalPrice * HotelSettings::marginRate();
        $gstOnMargin = $marginAmount * $gstSlab;
        $preRazorpayAmount = $tripjackTotalPrice + $marginAmount + $gstOnMargin;

        // Gross-up, not a flat add-on — see class docblock.
        $grossedUp = $preRazorpayAmount / (1 - self::RAZORPAY_EFFECTIVE_RATE);
        $razorpayRecovery = $grossedUp - $preRazorpayAmount;

        // Management fee + GST go on top of the final price as they are
        // (₹3,000 room price → ₹3,059), not through the gross-up.
        $managementFee = self::MANAGEMENT_FEE;
        $managementFeeGst = round($managementFee * self::MANAGEMENT_FEE_GST_RATE, 2);
        $customerPrice = round($grossedUp, 2) + $managementFee + $managementFeeGst;

        return [
            'tripjack_total_price' => round($tripjackTotalPrice, 2),
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
