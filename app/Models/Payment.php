<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    /** @use HasFactory<\Database\Factories\PaymentFactory> */
    use HasFactory;

    protected $fillable = [
        'booking_id', 'razorpay_order_id', 'razorpay_payment_id', 'razorpay_signature',
        'amount', 'currency', 'status', 'method', 'raw_response',
        'refund_amount', 'refund_reason',
    ];

    protected $casts = [
        'raw_response' => 'array',
    ];

    public function booking() { return $this->belongsTo(Booking::class); }
}
