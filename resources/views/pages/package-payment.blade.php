@extends('layouts.frontend')

@section('meta_title', 'Secure Payment — ' . $booking->package->title . ' | TYT Luxe')

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com" />
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,500;0,600&family=Jost:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
<style>
  :root {
    --gold: #c9a84c; --gold-light: #e8c96b;
    --dark: #0d0d0d; --dark-2: #141414;
    --white-60: rgba(255,255,255,0.60); --white-30: rgba(255,255,255,0.30); --white-10: rgba(255,255,255,0.10);
  }
  body { background: var(--dark); }
  .pay-wrap { max-width: 480px; margin: 0 auto; padding: 105px 24px 90px; text-align: center; font-family: 'Jost', sans-serif; }
  .pay-card { background: var(--dark-2); border: 1px solid rgba(201,168,76,0.25); border-radius: 22px; padding: 40px 36px; }
  .pay-eyebrow { font-size: 10.5px; font-weight: 600; letter-spacing: 0.22em; text-transform: uppercase; color: var(--gold); margin-bottom: 14px; }
  .pay-title { font-family: 'Cormorant Garamond', serif; font-size: 1.9rem; color: #fff; margin-bottom: 8px; line-height: 1.25; }
  .pay-sub { font-size: 13.5px; color: var(--white-60); margin-bottom: 26px; line-height: 1.6; }
  .pay-rows { text-align: left; border-top: 1px solid var(--white-10); border-bottom: 1px solid var(--white-10); padding: 10px 0; margin-bottom: 22px; }
  .pay-row { display: flex; justify-content: space-between; font-size: 13px; padding: 5px 0; color: var(--white-60); }
  .pay-row strong { color: #fff; font-weight: 500; }
  .pay-amount { font-size: 12px; color: var(--white-60); margin-bottom: 4px; text-transform: uppercase; letter-spacing: 0.08em; }
  .pay-amount-value { font-family: 'Cormorant Garamond', serif; font-size: 2.4rem; color: var(--gold-light); margin-bottom: 26px; }
  .pay-spinner { width: 34px; height: 34px; border: 3px solid rgba(201,168,76,0.2); border-top-color: var(--gold); border-radius: 50%; animation: paySpin 0.8s linear infinite; margin: 0 auto 20px; }
  @keyframes paySpin { to { transform: rotate(360deg); } }
  .pay-status { font-size: 13px; color: var(--white-60); line-height: 1.7; }
  .pay-btn { display: none; width: 100%; padding: 16px; border: none; border-radius: 100px; margin-top: 22px; background: linear-gradient(90deg, #c9a84c, #e8c96b); color: var(--dark); font-family: inherit; font-size: 13px; font-weight: 800; letter-spacing: 0.1em; text-transform: uppercase; cursor: pointer; text-decoration: none; box-sizing: border-box; }
  .pay-btn.visible { display: inline-block; }
  .pay-error { margin-bottom: 24px; padding: 14px 18px; border-radius: 12px; background: rgba(220,80,80,0.08); border: 1px solid rgba(220,80,80,0.3); color: #f3a3a3; font-size: 13px; }
  .pay-secure-note { font-size: 11px; color: var(--white-30); margin-top: 24px; line-height: 1.6; }
  @media (max-width: 560px) {
    .pay-wrap { padding: 90px 16px 60px; }
    .pay-card { padding: 30px 22px; }
    .pay-amount-value { font-size: 2rem; }
  }
</style>
@endpush

@section('content')
@php
  $isBalance = $purpose === \App\Services\PackageBookingService::PURPOSE_BALANCE;
  $dep = $booking->packageDeparture;
  $dates = $dep ? $dep->start_date->format('d M Y').' – '.$dep->end_date->format('d M Y') : \Carbon\Carbon::parse($booking->check_in)->format('d M Y');
@endphp
<div class="pay-wrap">
  @if(session('booking_error'))
    <div class="pay-error" role="alert">{{ session('booking_error') }}</div>
  @endif

  <div class="pay-card">
    <p class="pay-eyebrow">Secure Payment</p>

    @if(! $payment)
      {{-- Nothing was charged. --}}
      <h1 class="pay-title">Seats no longer available</h1>
      <p class="pay-sub">Sorry — the seats on this date were taken, or online booking has closed for it, before payment was made. <b style="color:#fff;">You have not been charged.</b> Please choose another date.</p>
      <a href="{{ route('package.checkout', $booking->package->slug) }}" class="pay-btn visible">Choose another date</a>
    @else
      <h1 class="pay-title">{{ $isBalance ? 'Pay Your Balance' : 'Complete Your Booking' }}</h1>
      <p class="pay-sub">{{ $booking->package->title }}<br>{{ $dates }} · {{ $booking->pax_adults }} traveller{{ $booking->pax_adults > 1 ? 's' : '' }}{{ $booking->package_option && $booking->package_option !== 'Per person' ? ' · '.$booking->package_option : '' }}</p>

      <div class="pay-rows">
        @if($isBalance)
          <div class="pay-row"><span>Balance (package price)</span><strong>&#8377;{{ number_format($charge['base'], 2) }}</strong></div>
          <div class="pay-row"><span>GST (5%)</span><strong>&#8377;{{ number_format($charge['gst'], 2) }}</strong></div>
          <div class="pay-row"><span>Online payment charge</span><strong>&#8377;{{ number_format($charge['gateway'], 2) }}</strong></div>
        @else
          <div class="pay-row"><span>{{ $booking->package_payment_plan === 'part' ? 'Booking amount now' : 'Paying in full' }}</span><strong>{{ $booking->package_payment_plan === 'part' ? 'Balance due by '.$booking->package_balance_due_on?->format('d M Y') : 'Nothing to pay later' }}</strong></div>
          <div class="pay-row"><span>Trip total (incl. GST)</span><strong>&#8377;{{ number_format((float) $booking->base_amount + (float) $booking->tax_amount, 2) }}</strong></div>
        @endif
      </div>

      <p class="pay-amount">Amount Payable</p>
      <p class="pay-amount-value">&#8377;{{ number_format((float) $payment->amount, 2) }}</p>

      <div id="payWaiting">
        <div class="pay-spinner"></div>
        <p class="pay-status" id="payStatusText">Opening secure payment window…</p>
      </div>
      <p class="pay-error" id="payFailedNote" style="display:none; margin:16px 0 0;"></p>
      <button type="button" class="pay-btn" id="payRetryBtn">{{ $isBalance ? 'Pay Balance' : 'Retry Payment' }}</button>
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
        currency: @json($payment->currency ?? 'INR'),
        name: 'TYTLUXE',
        description: @json(($isBalance ? 'Balance — ' : '') . $booking->package->title),
        prefill: {
          name: @json($booking->lead_guest_name),
          email: @json($booking->guest_email),
          contact: @json($booking->guest_phone)
        },
        theme: { color: '#c9a84c' },
        handler: function (response) {
          statusText.textContent = 'Payment received — confirming your booking…';
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
