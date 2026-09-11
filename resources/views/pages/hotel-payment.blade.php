@extends('layouts.frontend')

@section('meta_title', 'Secure Payment — ' . $booking->hotel->title . ' | TYT Luxe')

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com" />
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,500;0,600&family=Jost:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
<style>
  :root {
    --gold: #c9a84c; --gold-light: #e8c96b; --gold-dim: rgba(201,168,76,0.18);
    --dark: #0d0d0d; --dark-2: #141414;
    --white-80: rgba(255,255,255,0.80); --white-60: rgba(255,255,255,0.60); --white-30: rgba(255,255,255,0.30);
    --transition: 0.32s cubic-bezier(0.25,0.46,0.45,0.94);
  }
  body { background: var(--dark); }

  .pay-wrap {
    max-width: 460px; margin: 0 auto; padding: 105px 24px 90px;
    display: flex; flex-direction: column; align-items: center; text-align: center;
  }
  .pay-card {
    width: 100%; background: var(--dark-2); border: 1px solid rgba(201,168,76,0.25); border-radius: 22px;
    padding: 40px 36px; box-sizing: border-box;
  }
  @media (max-width: 560px) {
    .pay-wrap { padding: 90px 16px 60px; }
    .pay-card { padding: 30px 22px; }
    .pay-title { font-size: 1.6rem; }
    .pay-amount-value { font-size: 2rem; }
  }
  .pay-eyebrow { font-family: 'Jost', sans-serif; font-size: 10.5px; font-weight: 600; letter-spacing: 0.22em; text-transform: uppercase; color: var(--gold); margin-bottom: 14px; }
  .pay-title { font-family: 'Cormorant Garamond', serif; font-size: 1.9rem; color: #fff; margin-bottom: 10px; line-height: 1.25; }
  .pay-hotel { font-family: 'Jost', sans-serif; font-size: 13.5px; color: var(--white-60); margin-bottom: 30px; }

  .pay-amount { font-family: 'Jost', sans-serif; font-size: 12px; color: var(--white-60); margin-bottom: 4px; text-transform: uppercase; letter-spacing: 0.08em; }
  .pay-amount-value { font-family: 'Cormorant Garamond', serif; font-size: 2.4rem; color: var(--gold-light); margin-bottom: 30px; }

  .pay-spinner {
    width: 34px; height: 34px; border: 3px solid rgba(201,168,76,0.2); border-top-color: var(--gold);
    border-radius: 50%; animation: paySpin 0.8s linear infinite; margin: 0 auto 20px;
  }
  @keyframes paySpin { to { transform: rotate(360deg); } }
  .pay-status { font-family: 'Jost', sans-serif; font-size: 13px; color: var(--white-60); line-height: 1.7; }

  .pay-retry-btn {
    display: none; width: 100%; padding: 16px; border: none; border-radius: 100px; margin-top: 24px;
    background: linear-gradient(90deg, #c9a84c, #e8c96b); color: var(--dark);
    font-family: 'Jost', sans-serif; font-size: 13px; font-weight: 800; letter-spacing: 0.1em; text-transform: uppercase;
    cursor: pointer; transition: all var(--transition);
  }
  .pay-retry-btn:hover { background: linear-gradient(90deg, #e8c96b, #f5e4a8); }
  .pay-retry-btn.visible { display: inline-block; }

  .pay-error {
    margin-bottom: 24px; padding: 14px 18px; border-radius: 12px; width: 100%; box-sizing: border-box;
    background: rgba(220,80,80,0.08); border: 1px solid rgba(220,80,80,0.3);
    color: #f3a3a3; font-family: 'Jost', sans-serif; font-size: 13px;
  }
  .pay-secure-note { font-family: 'Jost', sans-serif; font-size: 11px; color: var(--white-30); margin-top: 24px; line-height: 1.6; }
</style>
@endpush

@section('content')
<div class="pay-wrap">
  @if(session('booking_error'))
  <div class="pay-error">⚠️ {{ session('booking_error') }}</div>
  @endif

  <div class="pay-card">
    <p class="pay-eyebrow">Secure Payment</p>
    <h1 class="pay-title">Complete Your Booking</h1>
    <p class="pay-hotel">{{ $booking->hotel->title }}</p>

    <p class="pay-amount">Amount Payable</p>
    <p class="pay-amount-value">{{ $payment->currency ?? 'INR' }} {{ number_format($payment->amount) }}</p>

    <div id="payWaiting">
      <div class="pay-spinner"></div>
      <p class="pay-status" id="payStatusText">Opening secure payment window…</p>
    </div>

    <p class="pay-error" id="payFailedNote" style="display:none; margin-top:16px; margin-bottom:0;"></p>

    <button type="button" class="pay-retry-btn" id="payRetryBtn">Retry Payment</button>

    <p class="pay-secure-note">Payments are processed securely by Razorpay. TYTLUXE never stores your card or bank details.</p>
  </div>
</div>

<form method="POST" action="{{ route('payment.razorpay.callback') }}" id="payCallbackForm" style="display:none;">
  @csrf
  <input type="hidden" name="razorpay_payment_id" id="payFieldPaymentId">
  <input type="hidden" name="razorpay_order_id" id="payFieldOrderId">
  <input type="hidden" name="razorpay_signature" id="payFieldSignature">
</form>
@endsection

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
        description: @json($booking->hotel->title),
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
        var desc = (response && response.error && response.error.description) || 'Your payment did not go through. Please try again.';
        failedNote.textContent = '⚠️ ' + desc;
        failedNote.style.display = '';
      });

      rzp.open();
    }

    retryBtn.addEventListener('click', openCheckout);
    openCheckout();
  })();
</script>
@endpush
