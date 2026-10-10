<?php

namespace App\Support;

/**
 * How much a guest gets back when they cancel (business rule, 2026-10-08):
 *
 * - With a cancellation penalty: exactly what TripJack refunds us — our
 *   markup is not refunded (TripJack refunds 20,000 → guest gets 20,000).
 * - Free cancellation (TripJack refunds us its whole price): everything the
 *   guest paid, minus a flat FREE_CANCELLATION_FEE (30,000 paid → 29,900).
 *
 * A booking that fails after payment (airline/hotel couldn't confirm it) is
 * not a cancellation — it is still refunded in full, elsewhere.
 */
class RefundPolicy
{
    public const FREE_CANCELLATION_FEE = 100.0;

    /**
     * @param  float  $paid  what the guest paid us for what is being cancelled
     * @param  float  $supplierCost  TripJack's price for it
     * @param  float  $supplierRefund  what TripJack refunds us
     */
    public static function cancellationRefund(float $paid, float $supplierCost, float $supplierRefund): float
    {
        if ($paid <= 0 || $supplierRefund <= 0) {
            return 0.0;
        }

        if (self::isFreeCancellation($supplierCost, $supplierRefund)) {
            return round(max(0.0, $paid - self::FREE_CANCELLATION_FEE), 2);
        }

        return round(min($paid, $supplierRefund), 2);
    }

    /** TripJack gives back its whole price — nothing was kept as a penalty. */
    public static function isFreeCancellation(float $supplierCost, float $supplierRefund): bool
    {
        return $supplierCost > 0 && $supplierRefund >= $supplierCost - 0.01;
    }

    /**
     * Hotels: TripJack refunds its price minus the hotel's penalty.
     */
    public static function hotelRefund(float $paid, float $tripjackPrice, float $penalty): float
    {
        return self::cancellationRefund($paid, $tripjackPrice, max(0.0, $tripjackPrice - $penalty));
    }
}
