@extends('layouts.frontend')

@section('meta_title', 'Secure Payment — Cab Booking | TYT Luxe')

@php
  $d = $booking->cab_details ?? [];
  $type = \App\Services\CabBookingService::JOURNEY_TYPES[$d['journeyType'] ?? ''] ?? 'Cab';
@endphp

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600&family=Jost:wght@300;400;500;600;700;800&display=swap" rel="stylesheet" />
<style>
  body { background: #0d0d0d; }
  .pay-wrap { max-width: 480px; margin: 0 auto; padding: 105px 24px 90px; text-align: center; font-family: 'Jost', sans-serif; }
  .pay-card { background: #141414; border: 1px solid rgba(201,168,76,0.25); border-radius: 22px; padding: 40px 36px; }
  .pay-eyebrow { font-size: 10.5px; font-weight: 600; letter-spacing: 0.22em; text-transform: uppercase; color: #c9a84c; margin: 0 0 14px; }
  .pay-card h1.pay-title { color: #fff; } /* cab-heading-fix */
  .pay-title { font-family: 'Cormorant Garamond', serif; font-size: 1.9rem; color: #fff; margin: 0 0 8px; line-height: 1.25; }
  .pay-sub { font-size: 13.5px; color: rgba(255,255,255,0.6); margin: 0 0 26px; line-height: 1.6; }
  .pay-rows { text-align: left; border-top: 1px solid rgba(255,255,255,0.1); border-bottom: 1px solid rgba(255,255,255,0.1); padding: 10px 0; margin-bottom: 22px; }
  .pay-row { display: flex; justify-content: space-between; gap: 12px; font-size: 13px; padding: 5px 0; color: rgba(255,255,255,0.6); }
  .pay-row strong { color: #fff; font-weight: 500; text-align: right; }
  .pay-amount { font-size: 12px; color: rgba(255,255,255,0.6); margin: 0 0 4px; text-transform: uppercase; letter-spacing: 0.08em; }
  .pay-amount-value { font-family: 'Cormorant Garamond', serif; font-size: 2.4rem; color: #e8c96b; margin: 0 0 26px; }
  .pay-spinner { width: 34px; height: 34px; border: 3px solid rgba(201,168,76,0.2); border-top-color: #c9a84c; border-radius: 50%; animation: paySpin 0.8s linear infinite; margin: 0 auto 20px; }
  @keyframes paySpin { to { transform: rotate(360deg); } }
  .pay-status { font-size: 13px; color: rgba(255,255,255,0.6); line-height: 1.7; }
  .pay-btn { display: none; width: 100%; padding: 16px; border: none; border-radius: 100px; margin-top: 22px; background: linear-gradient(90deg, #c9a84c, #e8c96b); color: #0d0d0d; font-family: inherit; font-size: 13px; font-weight: 800; letter-spacing: 0.1em; text-transform: uppercase; cursor: pointer; text-decoration: none; box-sizing: border-box; }
  .pay-btn.visible { display: inline-block; }
  .pay-error { margin-bottom: 24px; padding: 14px 18px; border-radius: 12px; background: rgba(220,80,80,0.08); border: 1px solid rgba(220,80,80,0.3); color: #f3a3a3; font-size: 13px; }
  .pay-secure-note { font-size: 11px; color: rgba(255,255,255,0.5); margin-top: 24px; line-height: 1.6; }
  @media (max-width: 560px) { .pay-wrap { padding: 90px 16px 60px; } .pay-card { padding: 30px 22px; } }
</style>
@endpush

@section('content')
<div class="pay-wrap">
  @if(session('booking_error'))
    <div class="pay-error" role="alert">{{ session('booking_error') }}</div>
  @endif

  <div class="pay-card">
    <p class="pay-eyebrow">Secure Payment</p>

    @if(! $payment)
      <h1 class="pay-title">Pickup is too soon to book online</h1>
      <p class="pay-sub">Your pickup is now less than 2 hours away. <b style="color:#fff;">You have not been charged.</b> Please choose a later time, or call us on +91 98750 73788 and we'll arrange it.</p>
      <a href="{{ route('cabs') }}" class="pay-btn visible">Search again</a>
    @else
      <h1 class="pay-title">Complete Your Booking</h1>
      <p class="pay-sub">{{ $d['option']['label'] ?? 'Cab' }} · {{ $type }}<br>{{ $booking->flight_route }}</p>

      <div class="pay-rows">
        <div class="pay-row"><span>Pickup</span><strong>{{ $booking->cab_pickup_at?->format('D, j M Y, g:i A') }}</strong></div>
        @if(!empty($d['returnAt']))
          <div class="pay-row"><span>Return</span><strong>{{ \Illuminate\Support\Carbon::parse($d['returnAt'])->format('D, j M Y, g:i A') }}</strong></div>
        @endif
        <div class="pay-row"><span>Lead passenger</span><strong>{{ $booking->lead_guest_name }} · {{ $booking->pax_adults }} {{ $booking->pax_adults == 1 ? 'passenger' : 'passengers' }}</strong></div>
      </div>

      <p class="pay-amount">Amount Payable</p>
      <p class="pay-amount-value">&#8377;{{ number_format((float) $payment->amount, 2) }}</p>

      <div id="payWaiting">
        <div class="pay-spinner"></div>
        <p class="pay-status" id="payStatusText">Opening secure payment window…</p>
      </div>
      <p class="pay-error" id="payFailedNote" style="display:none; margin:16px 0 0;"></p>
      <button type="button" class="pay-btn" id="payRetryBtn">Retry Payment</button>
    @endif

    <p class="pay-secure-note">Payments are processed securely by Razorpay. TYTLUXE never stores your card or bank details.</p>
  </div>
</div>

@if($payment)
<form method="POST" action="{{ route('payment.razorpay.callback') }}" id="payCallbackForm" style="display:none;">
  @csrf
  <input type="hidden" name="razorpay_payment_id" id="payFieldPaymentId">
  <input type="hidden" name="razorpay_order_id" id="payFieldOrderId">
  <input type="hidden" name="razorpay_signature" id="payFieldSignature">
</form>
@endif
@endsection

@if($payment)
@push('scripts')
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
  (function () {
    var statusText = document.getElementById('payStatusText');
    var waiting = document.getElementById('payWaiting');
    var retryBtn = document.getElementById('payRetryBtn');
    var failedNote = document.getElementById('payFailedNote');

    function openCheckout() {
      waiting.style.display = '';
      retryBtn.classList.remove('visible');
      failedNote.style.display = 'none';
      statusText.textContent = 'Opening secure payment window…';

      var rzp = new Razorpay({
        key: @json($razorpayKeyId),
        order_id: @json($payment->razorpay_order_id),
        amount: @json((int) round($payment->amount * 100)),
        currency: 'INR',
        name: 'TYTLUXE',
        description: @json('Cab — '.$booking->flight_route),
        prefill: {
          name: @json($booking->lead_guest_name),
          email: @json($booking->guest_email),
          contact: @json($booking->guest_phone)
        },
        theme: { color: '#c9a84c' },
        handler: function (response) {
          statusText.textContent = 'Payment received — booking your cab…';
          document.getElementById('payFieldPaymentId').value = response.razorpay_payment_id;
          document.getElementById('payFieldOrderId').value = response.razorpay_order_id;
          document.getElementById('payFieldSignature').value = response.razorpay_signature;
          document.getElementById('payCallbackForm').submit();
        },
        modal: {
          ondismiss: function () {
            waiting.style.display = 'none';
            retryBtn.classList.add('visible');
          }
        }
      });

      rzp.on('payment.failed', function (response) {
        waiting.style.display = 'none';
        retryBtn.classList.add('visible');
        failedNote.textContent = (response && response.error && response.error.description) || 'Your payment did not go through. Please try again.';
        failedNote.style.display = '';
      });

      rzp.open();
    }

    retryBtn.addEventListener('click', openCheckout);
    openCheckout();
  })();
</script>
@endpush
@endif
