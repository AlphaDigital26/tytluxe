@extends('layouts.frontend')

@section('meta_title', 'Cancel Booking — ' . $booking->hotel->title . ' | TYT Luxe')

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,500;0,600&family=Jost:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
<style>
  :root {
    --gold: #c9a84c; --gold-light: #e8c96b; --dark: #0d0d0d; --dark-2: #141414;
    --white-60: rgba(255,255,255,0.60); --white-30: rgba(255,255,255,0.30);
    --green: #4ade80; --red: #f3a3a3;
  }
  body { background: var(--dark); }
  .cx-wrap { max-width: 560px; margin: 0 auto; padding: 105px 24px 80px; }
  @media (max-width: 560px) { .cx-wrap { padding: 90px 18px 60px; } }

  .cx-eyebrow { font-family: 'Jost', sans-serif; font-size: 10.5px; font-weight: 600; letter-spacing: 0.22em; text-transform: uppercase; color: var(--gold); margin-bottom: 14px; }
  .cx-title { font-family: 'Cormorant Garamond', serif; font-size: clamp(1.8rem, 4vw, 2.3rem); color: #fff; margin-bottom: 10px; }
  .cx-sub { font-family: 'Jost', sans-serif; font-size: 13.5px; color: var(--white-60); margin-bottom: 30px; line-height: 1.6; }

  .cx-card { background: var(--dark-2); border: 1px solid rgba(201,168,76,0.22); border-radius: 20px; padding: 28px; margin-bottom: 24px; }
  .cx-line { display: flex; justify-content: space-between; gap: 12px; font-family: 'Jost', sans-serif; font-size: 13.5px; color: #fff; padding: 10px 0; border-bottom: 1px dashed rgba(255,255,255,0.08); }
  .cx-line:last-child { border-bottom: none; }
  .cx-line span:first-child { color: var(--white-60); }

  .cx-refund-box { border-radius: 16px; padding: 22px; margin-bottom: 26px; text-align: center; }
  .cx-refund-box.good { background: rgba(74,222,128,0.06); border: 1px solid rgba(74,222,128,0.3); }
  .cx-refund-box.bad { background: rgba(220,80,80,0.06); border: 1px solid rgba(220,80,80,0.3); }
  .cx-refund-box.unknown { background: rgba(201,168,76,0.06); border: 1px solid rgba(201,168,76,0.25); }
  .cx-refund-label { font-family: 'Jost', sans-serif; font-size: 11px; text-transform: uppercase; letter-spacing: 0.08em; color: var(--white-60); margin-bottom: 6px; }
  .cx-refund-amount { font-family: 'Cormorant Garamond', serif; font-size: 2rem; color: var(--gold-light); }
  .cx-refund-note { font-family: 'Jost', sans-serif; font-size: 12px; color: var(--white-60); margin-top: 8px; line-height: 1.6; }

  .cx-error { margin-bottom: 20px; padding: 14px 18px; border-radius: 12px; background: rgba(220,80,80,0.08); border: 1px solid rgba(220,80,80,0.3); color: var(--red); font-family: 'Jost', sans-serif; font-size: 13px; }

  .cx-actions { display: flex; gap: 12px; flex-wrap: wrap; }
  .cx-btn { flex: 1; min-width: 180px; text-align: center; padding: 15px 20px; border-radius: 100px; font-family: 'Jost', sans-serif; font-size: 12.5px; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; text-decoration: none; cursor: pointer; border: none; transition: all 0.28s ease; }
  .cx-btn-confirm { background: linear-gradient(90deg, #c0453f, #d9645e); color: #fff; }
  .cx-btn-confirm:hover { background: linear-gradient(90deg, #d9645e, #e88883); }
  .cx-btn-back { background: transparent; border: 1px solid rgba(255,255,255,0.15); color: rgba(255,255,255,0.7); }
  .cx-btn-back:hover { border-color: rgba(255,255,255,0.35); color: #fff; }
</style>
@endpush

@section('content')
<div class="cx-wrap">
  <p class="cx-eyebrow">Cancel Booking</p>
  <h1 class="cx-title">Are you sure?</h1>
  <p class="cx-sub">Review what cancelling this booking means before you confirm — this can't be undone.</p>

  @if(session('booking_error'))
  <div class="cx-error">⚠️ {{ session('booking_error') }}</div>
  @endif

  <div class="cx-card">
    <div class="cx-line"><span>Booking Reference</span><span>{{ $booking->reference }}</span></div>
    <div class="cx-line"><span>Hotel</span><span>{{ $booking->hotel?->title }}</span></div>
    <div class="cx-line"><span>Check-in</span><span>{{ \Illuminate\Support\Carbon::parse($booking->check_in)->format('d M Y') }}</span></div>
    <div class="cx-line"><span>Check-out</span><span>{{ \Illuminate\Support\Carbon::parse($booking->check_out)->format('d M Y') }}</span></div>
    <div class="cx-line"><span>Amount Paid</span><span>{{ $booking->currency }} {{ number_format($booking->total_amount, 2) }}</span></div>
  </div>

  @if($penalty === null)
    <div class="cx-refund-box unknown">
      <p class="cx-refund-label">Estimated Refund</p>
      <p class="cx-refund-amount">Unable to estimate</p>
      <p class="cx-refund-note">We couldn't determine the cancellation policy for this booking right now. If you proceed, our team will confirm your exact refund amount after reviewing the hotel's policy.</p>
    </div>
  @elseif($penalty['amount'] <= 0.0)
    <div class="cx-refund-box good">
      <p class="cx-refund-label">Estimated Refund</p>
      <p class="cx-refund-amount">{{ $booking->currency }} {{ number_format($estimatedRefund, 2) }}</p>
      <p class="cx-refund-note">You're within the free-cancellation window — this refund is issued automatically to your original payment method within 5–7 business days.</p>
    </div>
  @else
    <div class="cx-refund-box bad">
      <p class="cx-refund-label">Estimated Refund</p>
      <p class="cx-refund-amount">{{ $booking->currency }} {{ number_format($estimatedRefund, 2) }}</p>
      <p class="cx-refund-note">A cancellation charge applies at this stage per the hotel's policy. This is an estimate — our team will confirm the final refund amount and process it manually.</p>
    </div>
  @endif

  <form method="POST" action="{{ route('hotel.booking.cancel', $booking->reference) }}" id="cxForm">
    @csrf
    <div class="cx-actions">
      <a href="{{ route('hotel.booking.confirmation', $booking->reference) }}" class="cx-btn cx-btn-back">Keep My Booking</a>
      <button type="submit" class="cx-btn cx-btn-confirm" id="cxConfirmBtn">Confirm Cancellation</button>
    </div>
  </form>
</div>
@endsection

@push('scripts')
<script>
  document.getElementById('cxForm').addEventListener('submit', function () {
    var btn = document.getElementById('cxConfirmBtn');
    btn.disabled = true;
    btn.textContent = 'Cancelling…';
  });
</script>
@endpush
