<?php

namespace Tests\Unit;

use App\Support\RefundPolicy;
use PHPUnit\Framework\TestCase;

/**
 * Business rule (2026-10-08): with a penalty the guest gets exactly what
 * TripJack refunds us (no markup); a free cancellation gives back everything
 * paid less a flat ₹100.
 */
class RefundPolicyTest extends TestCase
{
    public function test_penalty_refunds_exactly_what_tripjack_refunds(): void
    {
        $this->assertSame(20000.0, RefundPolicy::cancellationRefund(30000, 27000, 20000));
        $this->assertSame(20000.0, RefundPolicy::hotelRefund(30000, 25000, 5000));
    }

    public function test_free_cancellation_refunds_everything_less_the_fee(): void
    {
        $this->assertSame(29900.0, RefundPolicy::cancellationRefund(30000, 25000, 25000));
        $this->assertSame(29900.0, RefundPolicy::hotelRefund(30000, 25000, 0));
    }

    public function test_free_hotel_cancellation_keeps_only_the_management_fee(): void
    {
        // 2026-10-13: a free cancellation keeps only the ₹50 management fee —
        // its ₹9 GST is refunded (₹3,059 paid → ₹3,009 back).
        $this->assertSame(3009.0, RefundPolicy::hotelRefund(3059, 2500, 0, 50.0));
        // With a penalty the guest still gets what TripJack refunds.
        $this->assertSame(20000.0, RefundPolicy::hotelRefund(30000, 25000, 5000, 50.0));
        $this->assertSame(20000.0, RefundPolicy::cancellationRefund(30000, 27000, 20000, 100.0));

        $booking = new \App\Models\Booking(['management_fee' => 50, 'management_fee_gst' => 9]);
        $this->assertSame(50.0, $booking->nonRefundableFee());
        $this->assertNull((new \App\Models\Booking)->nonRefundableFee()); // booked before the fee: older rule
    }

    public function test_never_more_than_paid_and_nothing_when_tripjack_refunds_nothing(): void
    {
        $this->assertSame(500.0, RefundPolicy::cancellationRefund(500, 27000, 20000));
        $this->assertSame(0.0, RefundPolicy::cancellationRefund(30000, 25000, 0));
        $this->assertSame(0.0, RefundPolicy::hotelRefund(30000, 25000, 25000));
        $this->assertSame(0.0, RefundPolicy::cancellationRefund(80, 80, 80)); // fee can't go negative
    }
}
