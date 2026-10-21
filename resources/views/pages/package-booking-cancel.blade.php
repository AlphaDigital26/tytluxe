@extends('layouts.frontend')

@section('meta_title', 'Cancel Booking ' . $booking->reference . ' | TYT Luxe')

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com" />
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,500;0,600&family=Jost:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
<style>
  :root { --gold: #c9a84c; --dark: #0d0d0d; --dark-2: #141414; --dark-3: #1b1b1b; --white-60: rgba(255,255,255,0.60); --white-10: rgba(255,255,255,0.10); }
  body { background: var(--dark); }
  .pk-wrap { max-width: 560px; margin: 0 auto; padding: 110px 24px 90px; font-family: 'Jost', sans-serif; color: rgba(255,255,255,0.85); }
  .pk-card { background: var(--dark-2); border: 1px solid var(--white-10); border-radius: 20px; padding: 32px; }
  .pk-title { font-family: 'Cormorant Garamond', serif; font-size: 2rem; color: #fff; margin: 0 0 6px; }
  .pk-sub { font-size: 13.5px; color: var(--white-60); margin: 0 0 22px; line-height: 1.6; }
  .pk-row { display: flex; justify-content: space-between; gap: 12px; font-size: 13.5px; padding: 7px 0; border-bottom: 1px solid var(--white-10); }
  .pk-row span { color: var(--white-60); }
  .pk-row strong { color: #fff; font-weight: 500; text-align: right; }
  .pk-policy { margin: 20px 0; padding: 14px 18px; border-radius: 12px; background: var(--dark-3); font-size: 13px; line-height: 1.7; }
  .pk-label { display: block; font-size: 10.5px; font-weight: 700; letter-spacing: 0.1em; text-transform: uppercase; color: var(--white-60); margin-bottom: 6px; }
  .pk-input { width: 100%; box-sizing: border-box; background: var(--dark-3); border: 1px solid var(--white-10); border-radius: 10px; padding: 11px 13px; color: #fff; font-family: inherit; font-size: 14px; }
  .pk-check { display: flex; gap: 10px; align-items: flex-start; font-size: 13px; margin: 16px 0 0; cursor: pointer; }
  .pk-check input { margin-top: 3px; accent-color: var(--gold); }
  .pk-btn { width: 100%; padding: 15px; border-radius: 100px; border: 1px solid rgba(220,80,80,0.5); background: rgba(220,80,80,0.12); color: #f3a3a3; font-family: inherit; font-size: 13px; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; cursor: pointer; margin-top: 20px; }
  .pk-back { display: block; text-align: center; margin-top: 14px; font-size: 13px; color: var(--white-60); text-decoration: none; }
  .pk-error { margin-bottom: 18px; padding: 12px 16px; border-radius: 12px; background: rgba(220,80,80,0.08); border: 1px solid rgba(220,80,80,0.3); color: #f3a3a3; font-size: 13px; }
  @media (max-width: 560px) { .pk-wrap { padding: 92px 16px 60px; } .pk-card { padding: 24px 18px; } }
</style>
@endpush

@section('content')
<div class="pk-wrap">
  <div class="pk-card">
    <h1 class="pk-title">Cancel this booking?</h1>
    <p class="pk-sub">{{ $booking->package->title }} · Booking no. {{ $booking->reference }}</p>

    @if($errors->any())
      <div class="pk-error" role="alert">{{ $errors->first() }}</div>
    @endif

    <div class="pk-row"><span>Travel dates</span><strong>{{ \Carbon\Carbon::parse($booking->check_in)->format('d M Y') }}{{ $booking->check_out ? ' – '.\Carbon\Carbon::parse($booking->check_out)->format('d M Y') : '' }}</strong></div>
    <div class="pk-row"><span>Travellers</span><strong>{{ $booking->packageTravellersLabel() }}</strong></div>
    <div class="pk-row"><span>Paid so far</span><strong>&#8377;{{ number_format($paid, 2) }}</strong></div>

    <div class="pk-policy">
      Your request goes to our team, who will confirm the cancellation and the refund under our
      <a href="{{ route('cancellation') }}" target="_blank" style="color:var(--gold);">cancellation policy</a>.
      Any refund is paid back to your original payment method. <strong>Your seats stay booked until our team confirms.</strong>
    </div>

    <form method="POST" action="{{ route('hotel.booking.cancel', $booking->reference) }}">
      @csrf
      <label class="pk-label" for="pkReason">Reason (optional)</label>
      <textarea class="pk-input" id="pkReason" name="reason" rows="3" maxlength="500">{{ old('reason') }}</textarea>
      <label class="pk-check">
        <input type="checkbox" name="confirm" value="1" required>
        <span>Yes, I want to cancel this booking.</span>
      </label>
      <button type="submit" class="pk-btn">Request Cancellation</button>
    </form>
    <a href="{{ route('hotel.booking.confirmation', $booking->reference) }}" class="pk-back">Keep my booking</a>
  </div>
</div>
@endsection
