<?php

namespace App\Services;

use App\Services\TripJack\TripJackFlightClient;
use App\Support\FlightSettings;

/**
 * TYTLUXE's flight markup formula — the same margin/GST/Razorpay gross-up
 * order of operations as HotelPricingService (see its docblock), applied to
 * TripJack's TF (Total Fare).
 *
 * Unlike hotels, the markup is a fixed ₹ amount, not a % (founder's
 * decision, 2026-11-02): per passenger, per flight — ₹250 for a domestic
 * flight, ₹700 for an international one, both set in Flights → Flight
 * Settings. A "flight" is one leg of the trip (onward, return, each
 * Multi-City flight); a connection inside a leg doesn't count again.
 * Adults and children pay it; infants pay their own amount per flight
 * (₹100, domestic or international — founder's decision, 2026-11-04).
 *
 * GST on the markup is 5% (same decision). Our own fees — the management
 * fee and the reschedule service charge — keep 18%. The airline's own GST
 * is already inside TF.
 */
class FlightPricingService
{
    /** GST on our markup. */
    protected const MARKUP_GST_RATE = 0.05;

    /** GST on our fees (management fee, reschedule service charge). */
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
     * $markup: the booking's ₹ markup (see price()).
     *
     * @return array{markup: float, gstRate: float, razorpayRate: float, managementFee: float, managementFeeGst: float}
     */
    public static function formula(float $markup = 0.0): array
    {
        return [
            'markup' => $markup,
            'gstRate' => self::MARKUP_GST_RATE,
            'razorpayRate' => self::RAZORPAY_EFFECTIVE_RATE,
            'managementFee' => self::MANAGEMENT_FEE,
            'managementFeeGst' => round(self::MANAGEMENT_FEE * self::GST_RATE, 2),
        ];
    }

    /**
     * Our ₹ markup for ONE passenger on an itinerary: the domestic or
     * international amount for each leg in its `sI` segment list (several
     * legs for a COMBO international return / multi-city itinerary).
     */
    public static function markupPerPassenger(array $segments): float
    {
        $markup = 0.0;
        foreach (TripJackFlightClient::itineraryLegs($segments) as $leg) {
            $markup += self::legIsInternational($leg) ? FlightSettings::internationalMarkup() : FlightSettings::domesticMarkup();
        }

        return $markup;
    }

    /**
     * The whole booking's ₹ markup, from Review's tripInfos: every leg's
     * domestic/international amount × adults and children, plus the infant
     * amount × infants on every leg.
     *
     * @param  array<int, array>  $tripInfos  Review's tripInfos (each with an `sI` list)
     */
    public static function bookingMarkup(array $tripInfos, int $adults, int $children, int $infants = 0): float
    {
        $perPassenger = 0.0;
        $flights = 0;
        foreach ($tripInfos as $trip) {
            $perPassenger += self::markupPerPassenger($trip['sI'] ?? []);
            $flights += count(TripJackFlightClient::itineraryLegs($trip['sI'] ?? []));
        }

        return round($perPassenger * max(0, $adults + $children) + $flights * FlightSettings::infantMarkup() * max(0, $infants), 2);
    }

    /** A leg is international when any of its airports is outside India. */
    public static function legIsInternational(array $leg): bool
    {
        foreach ($leg as $segment) {
            foreach (['da', 'aa'] as $end) {
                if (! self::isIndianAirport($segment[$end] ?? [])) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * TripJack names the country on each airport ("India"); when it doesn't,
     * the airport code is looked up in the site's own airport list.
     */
    protected static function isIndianAirport(array $airport): bool
    {
        $country = strtoupper(trim((string) ($airport['countryCode'] ?? $airport['country'] ?? '')));
        if ($country !== '') {
            return in_array($country, ['IN', 'IND', 'INDIA'], true);
        }

        return isset(self::indianAirportCodes()[strtoupper((string) ($airport['code'] ?? ''))]);
    }

    /** @return array<string, true> IATA codes of India's airports, from public/data/airports.json */
    protected static function indianAirportCodes(): array
    {
        static $codes = null;
        if ($codes === null) {
            $rows = json_decode((string) @file_get_contents(public_path('data/airports.json')), true) ?: [];
            $codes = [];
            foreach ($rows as $row) {
                if (($row[3] ?? null) === 'India' && ! empty($row[0])) {
                    $codes[$row[0]] = true;
                }
            }
        }

        return $codes;
    }

    /**
     * Computes the customer-facing price from TripJack's raw TF (Total
     * Fare), with the full breakdown kept traceable for auditing.
     *
     * $markup: our ₹ markup for the fare being priced — markupPerPassenger()
     * for a per-adult fare on the results page, bookingMarkup() for a whole
     * booking. A booking's amount is fixed when the guest picks the fare
     * (saved in the booking draft), so a change in Flight Settings
     * mid-booking doesn't change the price they already saw.
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
    public static function price(float $totalFare, float $markup, bool $withManagementFee = true): array
    {
        $gstSlab = self::MARKUP_GST_RATE;

        $marginAmount = max(0.0, $markup);
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
