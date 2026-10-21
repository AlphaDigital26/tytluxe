<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    /** @use HasFactory<\Database\Factories\PaymentFactory> */
    use HasFactory;

    /** A payment the team received outside the website (cash, UPI, bank transfer). */
    public const METHOD_OFFLINE = 'offline';

    protected $fillable = [
        'booking_id', 'razorpay_order_id', 'razorpay_payment_id', 'razorpay_signature',
        'amount', 'currency', 'status', 'method', 'raw_response',
        'refund_amount', 'refund_reason', 'purpose',
    ];

    protected $casts = [
        'raw_response' => 'array',
    ];

    public function booking() { return $this->belongsTo(Booking::class); }

    /** Received outside Razorpay, so it can never be refunded through Razorpay. */
    public function isOffline(): bool
    {
        return $this->method === self::METHOD_OFFLINE;
    }
}
