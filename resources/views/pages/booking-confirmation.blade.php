@extends('layouts.frontend')

@section('meta_title', 'Booking Confirmation | TYT Luxe')

@php
  $terminalGood = in_array($liveStatus, ['SUCCESS', 'ON_HOLD'], true);
  $terminalBad = in_array($liveStatus, ['ABORTED', 'FAILED'], true);
@endphp

@if($stillPolling)
<meta http-equiv="refresh" content="5;url={{ route('hotel.booking.confirmation', $booking->reference) }}?polling_since={{ $pollingSince }}">
@endif

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,500;0,600&family=Jost:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
<style>
  :root {
    --gold: #c9a84c; --gold-light: #e8c96b; --dark: #0d0d0d; --dark-2: #141414;
    --white-60: rgba(255,255,255,0.60); --white-30: rgba(255,255,255,0.30);
    --green: #4ade80; --amber: #e0b34a; --red: #f3a3a3; --transition: 0.32s cubic-bezier(0.25,0.46,0.45,0.94);
  }
  body { background: var(--dark); }
  .bc-wrap { max-width: 640px; margin: 0 auto; padding: 64px 24px 80px; text-align: center; }
  @media (max-width: 560px) { .bc-wrap { padding: 40px 18px 60px; } }

  .bc-icon-ring {
    width: 76px; height: 76px; margin: 0 auto 22px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center; font-size: 32px;
    border: 1px solid rgba(201,168,76,0.3);
  }
  .bc-icon-ring.good { background: rgba(74,222,128,0.08); border-color: rgba(74,222,128,0.35); }
  .bc-icon-ring.bad { background: rgba(220,80,80,0.08); border-color: rgba(220,80,80,0.35); }
  .bc-icon-ring.pending { background: rgba(201,168,76,0.06); position: relative; }
  .bc-icon-ring.pending::before {
    content: ''; position: absolute; inset: -1px; border-radius: 50%;
    border: 2px solid transparent; border-top-color: var(--gold); animation: bcSpin 1.1s linear infinite;
  }

  .bc-title { font-family: 'Cormorant Garamond', serif; font-size: clamp(1.9rem, 4vw, 2.4rem); color: #fff; margin-bottom: 10px; }
  .bc-sub { font-family: 'Jost', sans-serif; font-size: 13.5px; color: var(--white-60); margin-bottom: 36px; font-weight: 300; line-height: 1.6; max-width: 480px; margin-left: auto; margin-right: auto; }

  .bc-card { background: var(--dark-2); border: 1px solid rgba(201,168,76,0.22); border-radius: 20px; padding: 30px; text-align: left; box-shadow: 0 20px 50px rgba(0,0,0,0.3); }
  .bc-line { display: flex; justify-content: space-between; gap: 12px; font-family: 'Jost', sans-serif; font-size: 13.5px; color: #fff; padding: 11px 0; border-bottom: 1px dashed rgba(255,255,255,0.08); }
  .bc-line:last-child { border-bottom: none; }
  .bc-line span:first-child { color: var(--white-60); flex-shrink: 0; }
  .bc-line span:last-child { text-align: right; }
  .bc-status { padding: 3px 12px; border-radius: 100px; font-size: 11.5px; font-weight: 700; letter-spacing: 0.04em; }
  .bc-status.good { color: var(--green); background: rgba(74,222,128,0.1); }
  .bc-status.pending { color: var(--amber); background: rgba(224,179,74,0.1); }
  .bc-status.bad { color: var(--red); background: rgba(220,80,80,0.1); }

  .bc-polling-bar { height: 3px; background: rgba(255,255,255,0.06); border-radius: 100px; overflow: hidden; margin-top: 24px; }
  .bc-polling-bar-fill { height: 100%; width: 40%; background: linear-gradient(90deg, var(--gold), var(--gold-light)); border-radius: 100px; animation: bcSlide 1.6s ease-in-out infinite; }
  .bc-polling-note { font-family: 'Jost', sans-serif; font-size: 11.5px; color: var(--white-30); margin-top: 12px; }

  .bc-next { margin-top: 28px; padding: 16px 18px; border-radius: 12px; background: rgba(201,168,76,0.05); border: 1px solid rgba(201,168,76,0.15); font-family: 'Jost', sans-serif; font-size: 12.5px; color: var(--white-60); text-align: left; line-height: 1.6; }
  .bc-next strong { color: var(--gold); }

  @keyframes bcSpin { to { transform: rotate(360deg); } }
  @keyframes bcSlide { 0% { margin-left: -40%; } 100% { margin-left: 100%; } }
</style>
@endpush

@section('content')
<div class="bc-wrap">

  {{-- Step completion bar --}}
  <div style="display:flex; align-items:center; gap:0; margin-bottom:36px; font-family:'Jost',sans-serif; font-size:11px; font-weight:600; letter-spacing:0.08em; text-transform:uppercase; max-width:480px; margin-left:auto; margin-right:auto;">
    <div style="display:flex; align-items:center; gap:8px; color:rgba(255,255,255,0.35);">
      <span style="width:22px; height:22px; border-radius:50%; background:rgba(255,255,255,0.1); border:1px solid rgba(255,255,255,0.2); display:flex; align-items:center; justify-content:center; font-size:10px; flex-shrink:0;">✓</span>
      Choose Room
    </div>
    <div style="flex:1; height:1px; background:rgba(201,168,76,0.3); margin:0 12px; min-width:14px;"></div>
    <div style="display:flex; align-items:center; gap:8px; color:rgba(255,255,255,0.35);">
      <span style="width:22px; height:22px; border-radius:50%; background:rgba(255,255,255,0.1); border:1px solid rgba(255,255,255,0.2); display:flex; align-items:center; justify-content:center; font-size:10px; flex-shrink:0;">✓</span>
      Guest Details
    </div>
    <div style="flex:1; height:1px; background:rgba(201,168,76,0.3); margin:0 12px; min-width:14px;"></div>
    <div style="display:flex; align-items:center; gap:8px; color:{{ $terminalGood ? '#4ade80' : ($terminalBad ? '#f3a3a3' : '#c9a84c') }};">
      <span style="width:22px; height:22px; border-radius:50%; background:{{ $terminalGood ? 'rgba(74,222,128,0.12)' : ($terminalBad ? 'rgba(220,80,80,0.08)' : 'rgba(201,168,76,0.1)') }}; border:1px solid {{ $terminalGood ? '#4ade80' : ($terminalBad ? '#f3a3a3' : '#c9a84c') }}; display:flex; align-items:center; justify-content:center; font-size:10px; flex-shrink:0;">{{ $terminalGood ? '✓' : ($terminalBad ? '!' : '3') }}</span>
      Confirmed
    </div>
  </div>

  @if($terminalBad)
    <div class="bc-icon-ring bad">⚠️</div>
    <h1 class="bc-title">Booking Not Confirmed</h1>
    <p class="bc-sub">The hotel could not confirm this booking. No charge was made. Our team has been notified and will follow up with alternatives.</p>
  @elseif($terminalGood)
    <div class="bc-icon-ring good">✅</div>
    <h1 class="bc-title">Room Held</h1>
    <p class="bc-sub">Your room has been reserved with the hotel. Our team will reach out shortly to complete payment before your hold expires.</p>
  @else
    <div class="bc-icon-ring pending">⏳</div>
    <h1 class="bc-title">Confirming With the Hotel…</h1>
    <p class="bc-sub">This can take up to a few minutes. This page refreshes itself automatically — no need to resubmit anything.</p>
  @endif

  <div class="bc-card">
    <div class="bc-line"><span>Booking Reference</span><span>{{ $booking->reference }}</span></div>
    <div class="bc-line"><span>Hotel</span><span>{{ $booking->hotel?->title }}</span></div>
    <div class="bc-line"><span>Check-in</span><span>{{ \Illuminate\Support\Carbon::parse($booking->check_in)->format('d M Y') }}</span></div>
    <div class="bc-line"><span>Check-out</span><span>{{ \Illuminate\Support\Carbon::parse($booking->check_out)->format('d M Y') }}</span></div>
    <div class="bc-line"><span>Guests</span><span>{{ $booking->pax_adults }} Adult{{ $booking->pax_adults > 1 ? 's' : '' }}@if($booking->pax_children), {{ $booking->pax_children }} Child(ren) @endif</span></div>
    <div class="bc-line"><span>Total Amount</span><span>{{ $booking->currency }} {{ number_format($booking->total_amount, 2) }}</span></div>
    @if($booking->tripjack_hold_expires_at)
    <div class="bc-line"><span>Hold Expires</span><span>{{ \Illuminate\Support\Carbon::parse($booking->tripjack_hold_expires_at)->format('d M Y, h:i A') }}</span></div>
    @endif
    <div class="bc-line">
      <span>Status</span>
      <span class="bc-status {{ $terminalBad ? 'bad' : ($terminalGood ? 'good' : 'pending') }}">
        {{ $liveStatus ?? ucfirst(str_replace('_',' ',$booking->status)) }}
      </span>
    </div>
  </div>

  @if($stillPolling)
  <div class="bc-polling-bar"><div class="bc-polling-bar-fill"></div></div>
  <p class="bc-polling-note">Checking again in a few seconds…</p>
  @endif

  @if($terminalGood)
  <div class="bc-next">
    <strong>What happens next:</strong> our team will reach out on your registered phone/email to complete payment before the hold expires. No action is needed from you right now.
  </div>
  @endif

  {{-- Post-confirmation navigation — users shouldn't be stranded --}}
  <div style="display:flex; gap:12px; justify-content:center; flex-wrap:wrap; margin-top:32px;">
    <a href="{{ route('hotels') }}"
       style="display:inline-flex; align-items:center; gap:8px; padding:13px 26px; border-radius:100px;
              background:rgba(201,168,76,0.12); border:1px solid rgba(201,168,76,0.3); color:#c9a84c;
              font-family:'Jost',sans-serif; font-size:12.5px; font-weight:600; letter-spacing:0.08em;
              text-transform:uppercase; text-decoration:none; transition:all 0.28s ease;"
       onmouseover="this.style.background='rgba(201,168,76,0.2)'" onmouseout="this.style.background='rgba(201,168,76,0.12)'">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
      Browse More Hotels
    </a>
    <a href="{{ route('home') }}"
       style="display:inline-flex; align-items:center; gap:8px; padding:13px 26px; border-radius:100px;
              background:transparent; border:1px solid rgba(255,255,255,0.15); color:rgba(255,255,255,0.55);
              font-family:'Jost',sans-serif; font-size:12.5px; font-weight:500; letter-spacing:0.08em;
              text-transform:uppercase; text-decoration:none; transition:all 0.28s ease;"
       onmouseover="this.style.borderColor='rgba(255,255,255,0.35)'; this.style.color='rgba(255,255,255,0.85)'"
       onmouseout="this.style.borderColor='rgba(255,255,255,0.15)'; this.style.color='rgba(255,255,255,0.55)'">
      Back to Home
    </a>
  </div>

</div>
@endsection
