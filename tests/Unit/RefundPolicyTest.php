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

    public function test_never_more_than_paid_and_nothing_when_tripjack_refunds_nothing(): void
    {
        $this->assertSame(500.0, RefundPolicy::cancellationRefund(500, 27000, 20000));
        $this->assertSame(0.0, RefundPolicy::cancellationRefund(30000, 25000, 0));
        $this->assertSame(0.0, RefundPolicy::hotelRefund(30000, 25000, 25000));
        $this->assertSame(0.0, RefundPolicy::cancellationRefund(80, 80, 80)); // fee can't go negative
    }
}
