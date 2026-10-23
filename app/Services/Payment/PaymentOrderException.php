<?php

namespace App\Services\Payment;

/**
 * Razorpay refused to create a payment order. The message is written for
 * the guest; Razorpay's own reason is kept in the log.
 */
class PaymentOrderException extends \RuntimeException
{
    public const OVER_LIMIT_MESSAGE = 'This booking total is above the limit for a single online payment. Please contact our team on call or WhatsApp and we\'ll arrange it for you, or choose fewer rooms.';

    public const GENERIC_MESSAGE = 'We couldn\'t start the payment just now. Please try again in a moment.';
}
