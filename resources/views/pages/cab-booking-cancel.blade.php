@extends('layouts.frontend')

@section('meta_title', 'Cancel Cab Booking '.$booking->reference.' | TYT Luxe')

@php $d = $booking->cab_details ?? []; @endphp

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600&family=Jost:wght@300;400;500;600;700;800&display=swap" rel="stylesheet" />
<style>
  body { background: #0d0d0d; }
  .cc-wrap { max-width: 560px; margin: 0 auto; padding: 110px 24px 80px; font-family: 'Jost', sans-serif; color: #fff; }
  .cc-card { background: #141414; border: 1px solid rgba(201,168,76,0.25); border-radius: 22px; padding: 36px; }
  .cc-eyebrow { font-size: 10.5px; font-weight: 600; letter-spacing: 0.22em; text-transform: uppercase; color: #c9a84c; margin: 0 0 12px; }
  .cc-card h1.cc-title { color: #fff; } /* cab-heading-fix */
  .cc-title { font-family: 'Cormorant Garamond', serif; font-size: 2rem; font-weight: 500; margin: 0 0 8px; }
  .cc-sub { font-size: 13.5px; color: rgba(255,255,255,0.65); margin: 0 0 24px; line-height: 1.6; }
  .cc-line { display: flex; justify-content: space-between; gap: 12px; font-size: 13.5px; padding: 10px 0; border-bottom: 1px dashed rgba(255,255,255,0.08); color: rgba(255,255,255,0.8); }
  .cc-line span:first-child { color: rgba(255,255,255,0.6); }
  .cc-line.total { border-bottom: none; font-size: 17px; font-weight: 700; color: #fff; }
  .cc-line.total span:first-child { color: #fff; }
  .cc-policy { margin: 18px 0; padding: 12px 14px; border-radius: 10px; background: rgba(201,168,76,0.07); border: 1px solid rgba(201,168,76,0.22); font-size: 13px; color: rgba(255,255,255,0.8); }
  .cc-warn { margin: 18px 0; padding: 12px 14px; border-radius: 10px; background: rgba(220,80,80,0.08); border: 1px solid rgba(220,80,80,0.3); color: #f3a3a3; font-size: 13px; }
  .cc-check { display: flex; gap: 10px; align-items: flex-start; font-size: 13px; color: rgba(255,255,255,0.85); margin: 18px 0; }
  .cc-check input { accent-color: #c9a84c; width: 18px; height: 18px; flex-shrink: 0; }
  .cc-btn { width: 100%; padding: 16px; border: none; border-radius: 100px; background: #b91c1c; color: #fff; font-family: inherit; font-size: 13px; font-weight: 700; letter-spacing: 0.1em; text-transform: uppercase; cursor: pointer; }
  .cc-btn:disabled { opacity: .7; cursor: wait; }
  .cc-back { display: block; text-align: center; margin-top: 16px; font-size: 13px; color: #e8c96b; }
  .cc-error { font-size: 12px; color: #f3a3a3; }
  @media (max-width: 560px) { .cc-wrap { padding-top: 90px; } .cc-card { padding: 24px 20px; } }
</style>
@endpush

@section('content')
<div class="cc-wrap">
  <div class="cc-card">
    <p class="cc-eyebrow">Cancel booking</p>
    <h1 class="cc-title">Cancel your cab?</h1>
    <p class="cc-sub">{{ $d['option']['label'] ?? 'Cab' }} · {{ $booking->flight_route }}<br>Pickup {{ $booking->cab_pickup_at?->format('D, j M Y, g:i A') }} · Booking ID {{ $booking->reference }}</p>

    <div class="cc-line"><span>You paid</span><span>&#8377;{{ number_format((float) $booking->total_amount, 2) }}</span></div>
    @if($quote)
      <div class="cc-line total"><span>Refund if you cancel now</span><span>&#8377;{{ number_format($quote['refund'], 2) }}</span></div>
      @if($quote['description'])<div class="cc-policy">{{ $quote['description'] }}</div>@endif
      @if($quote['refund'] > 0)
        <p class="cc-sub" style="margin:0;">The online payment charge (&#8377;{{ number_format((float) $booking->razorpay_recovery, 2) }}) can't be refunded. The refund goes back to your original payment method in 5–7 business days.</p>
      @endif
    @else
      <div class="cc-warn">We couldn't fetch the refund amount right now. If you cancel, the refund follows the cab's cancellation policy shown when you booked.</div>
    @endif

    <form method="POST" action="{{ route('hotel.booking.cancel', $booking->reference) }}" id="cabCancelForm">
      @csrf
      <label class="cc-check"><input type="checkbox" name="confirm" value="1" required> <span>Yes, cancel this cab. I understand this can't be undone.</span></label>
      @error('confirm')<p class="cc-error">{{ $message }}</p>@enderror
      <button type="submit" class="cc-btn">Cancel Cab</button>
    </form>
    <a href="{{ route('hotel.booking.confirmation', $booking->reference) }}" class="cc-back">Keep my booking</a>
  </div>
</div>
@endsection

@push('scripts')
<script>
  document.getElementById('cabCancelForm').addEventListener('submit', function () {
    var b = this.querySelector('.cc-btn');
    setTimeout(function () { b.disabled = true; b.textContent = 'Cancelling…'; }, 0);
  });
</script>
@endpush
