<?php

namespace App\Services\Payment;

use Razorpay\Api\Api;
use Razorpay\Api\Errors\SignatureVerificationError;

/**
 * Thin wrapper around the Razorpay SDK — kept injectable (not static) so
 * tests can bind a fake implementation instead of hitting Razorpay's API.
 */
class RazorpayService
{
    protected Api $api;

    protected string $webhookSecret;

    public function __construct()
    {
        $this->api = new Api(
            (string) config('services.razorpay.key_id'),
            (string) config('services.razorpay.key_secret'),
        );
        $this->webhookSecret = (string) config('services.razorpay.webhook_secret');
    }

    /**
     * Creates a Razorpay order for the customer-facing (marked-up) amount.
     * Auto-captures on successful authorization — this is a straightforward
     * purchase flow, not one needing a manual capture step.
     */
    public function createOrder(float $amountRupees, string $receipt): array
    {
        $order = $this->api->order->create([
            'amount' => $this->toPaise($amountRupees),
            'currency' => 'INR',
            'receipt' => $receipt,
            'payment_capture' => 1,
        ]);

        return $order->toArray();
    }

    /**
     * Verifies the razorpay_order_id/payment_id/signature trio Checkout.js's
     * client-side success handler posts back to us.
     */
    public function verifyPaymentSignature(array $attributes): bool
    {
        try {
            $this->api->utility->verifyPaymentSignature($attributes);

            return true;
        } catch (SignatureVerificationError) {
            return false;
        }
    }

    /**
     * Verifies a webhook request's X-Razorpay-Signature header against the
     * raw request body — the authoritative confirmation path (unlike the
     * client-side callback, this fires even if the guest closes the browser
     * right after paying).
     */
    public function verifyWebhookSignature(string $body, string $signature): bool
    {
        try {
            $this->api->utility->verifyWebhookSignature($body, $signature, $this->webhookSecret);

            return true;
        } catch (SignatureVerificationError) {
            return false;
        }
    }

    /**
     * Refunds a captured payment — full refund when $amountRupees is null,
     * partial otherwise. Used when TripJack's Book call fails after payment
     * was already captured.
     */
    public function refund(string $paymentId, ?float $amountRupees = null): array
    {
        $attributes = $amountRupees !== null ? ['amount' => $this->toPaise($amountRupees)] : [];

        $refund = $this->api->payment->fetch($paymentId)->refund($attributes);

        return $refund->toArray();
    }

    protected function toPaise(float $amountRupees): int
    {
        return (int) round($amountRupees * 100);
    }
}
