<?php

namespace Tests\Doubles;

use App\Services\Payment\RazorpayService;

/**
 * Test double for RazorpayService — the real SDK makes actual network calls
 * to Razorpay, so tests bind this instead via $this->app->bind(). Control
 * signature-verification outcomes per test via the public flags.
 */
class FakeRazorpayService extends RazorpayService
{
    public array $orders = [];

    public array $refunds = [];

    public bool $nextSignatureValid = true;

    public bool $nextWebhookSignatureValid = true;

    protected int $orderSeq = 0;

    public function __construct()
    {
        // Deliberately skip the parent constructor — it builds a real
        // Razorpay\Api\Api client, which this double never uses.
    }

    public function createOrder(float $amountRupees, string $receipt): array
    {
        $this->orderSeq++;
        $order = [
            'id' => "order_fake_{$this->orderSeq}",
            'amount' => (int) round($amountRupees * 100),
            'currency' => 'INR',
            'receipt' => $receipt,
        ];
        $this->orders[] = $order;

        return $order;
    }

    public function verifyPaymentSignature(array $attributes): bool
    {
        return $this->nextSignatureValid;
    }

    public function verifyWebhookSignature(string $body, string $signature): bool
    {
        return $this->nextWebhookSignatureValid;
    }

    public function refund(string $paymentId, ?float $amountRupees = null): array
    {
        $refund = [
            'id' => 'rfnd_fake_'.count($this->refunds),
            'payment_id' => $paymentId,
            'amount' => $amountRupees !== null ? (int) round($amountRupees * 100) : null,
        ];
        $this->refunds[] = $refund;

        return $refund;
    }
}
