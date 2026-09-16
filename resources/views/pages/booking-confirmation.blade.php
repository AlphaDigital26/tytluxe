@extends('layouts.frontend')

@section('meta_title', 'Booking Confirmation | TYT Luxe')

@php
  // Payment now happens before TripJack's Book call, so $booking->status is
  // the reliable source of truth — payment_failed/refunded/failed_needs_review
  // have no corresponding $liveStatus at all (no tripjack_booking_id exists
  // yet, or the booking was refunded after a late TripJack failure).
  $paymentFailed = $booking->status === 'payment_failed';
  // A cancellation can sit as TripJack's CANCELLATION_PENDING for days before
  // it resolves offline — status stays 'confirmed' the whole time, so this
  // has to be its own branch rather than folded into $terminalGood.
  $cancellationPending = $booking->cancellation_requested_at !== null && $booking->status !== 'cancelled';
  $terminalGood = $booking->status === 'confirmed' && ! $cancellationPending;
  $terminalBad = in_array($booking->status, ['refunded', 'failed_needs_review', 'cancelled'], true);
  $canRequestCancellation = $booking->status === 'confirmed' && ! $cancellationPending
      && $booking->check_in && \Illuminate\Support\Carbon::parse($booking->check_in)->isFuture();
@endphp

@if($stillPolling)
<meta http-equiv="refresh" content="5;url={{ route('hotel.booking.confirmation', $booking->reference) }}?polling_since={{ $pollingSince }}">
@endif

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,500;0,600&family=Jost:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
<style>
  :root {
    --gold: #c9a84c; --gold-light: #e8c96b; --dark: #0d0d0d; --dark-2: #141414; --dark-3: #191919;
    --white-60: rgba(255,255,255,0.60); --white-30: rgba(255,255,255,0.30);
    --green: #4ade80; --amber: #e0b34a; --red: #f3a3a3; --transition: 0.32s cubic-bezier(0.25,0.46,0.45,0.94);
  }
  body { background: var(--dark); }
  .bc-wrap { max-width: 640px; margin: 0 auto; padding: 105px 24px 0; text-align: center; }
  @media (max-width: 560px) { .bc-wrap { padding: 90px 18px 0; } }

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
  .bc-sub { font-family: 'Jost', sans-serif; font-size: 13.5px; color: var(--white-60); margin-bottom: 0; font-weight: 300; line-height: 1.6; max-width: 480px; margin-left: auto; margin-right: auto; }

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

  .bc-next { margin-top: 20px; padding: 16px 18px; border-radius: 12px; background: rgba(201,168,76,0.05); border: 1px solid rgba(201,168,76,0.15); font-family: 'Jost', sans-serif; font-size: 12.5px; color: var(--white-60); text-align: left; line-height: 1.6; }
  .bc-next strong { color: var(--gold); }

  @keyframes bcSpin { to { transform: rotate(360deg); } }
  @keyframes bcSlide { 0% { margin-left: -40%; } 100% { margin-left: 100%; } }

  /* ── Trip details section ─────────────────────────────────────────── */
  .bd-wrap { max-width: 1080px; margin: 0 auto; padding: 44px 24px 90px; text-align: left; }
  @media (max-width: 560px) { .bd-wrap { padding: 34px 18px 60px; } }

  .bd-hero { position: relative; width: 100%; height: 260px; border-radius: 20px; overflow: hidden; margin-bottom: 28px; border: 1px solid rgba(201,168,76,0.22); }
  .bd-hero img { width: 100%; height: 100%; object-fit: cover; }
  .bd-hero::after { content: ''; position: absolute; inset: 0; background: linear-gradient(to top, rgba(0,0,0,0.85), rgba(0,0,0,0.05) 55%); }
  .bd-hero-caption { position: absolute; left: 26px; right: 26px; bottom: 20px; z-index: 1; }
  .bd-hero-caption h2 { font-family: 'Cormorant Garamond', serif; color: #fff; font-size: clamp(1.5rem, 3vw, 2rem); margin-bottom: 4px; }
  .bd-hero-caption p { font-family: 'Jost', sans-serif; font-size: 12.5px; color: var(--white-60); display: flex; align-items: center; gap: 6px; }
  .bd-hero-caption svg { width: 13px; height: 13px; flex-shrink: 0; }

  .bd-grid { display: grid; grid-template-columns: 1fr 340px; gap: 26px; align-items: start; }
  @media (max-width: 860px) { .bd-grid { grid-template-columns: 1fr; } }

  .bd-panel { background: var(--dark-2); border: 1px solid rgba(255,255,255,0.08); border-radius: 18px; padding: 26px 28px; margin-bottom: 22px; }
  .bd-panel-title { font-family: 'Jost', sans-serif; font-size: 11px; font-weight: 700; letter-spacing: 0.1em; text-transform: uppercase; color: var(--gold); margin-bottom: 18px; display: flex; align-items: center; gap: 8px; }
  .bd-panel-title svg { width: 15px; height: 15px; }

  .bd-stay-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px 16px; }
  @media (max-width: 480px) { .bd-stay-grid { grid-template-columns: repeat(2, 1fr); } }
  .bd-stat-label { font-family: 'Jost', sans-serif; font-size: 10.5px; color: var(--white-30); text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 6px; }
  .bd-stat-value { font-family: 'Jost', sans-serif; font-size: 15px; color: #fff; font-weight: 500; }

  .bd-nights-pill { display: inline-flex; align-items: center; gap: 8px; margin: 18px 0 4px; padding: 8px 16px; border-radius: 100px; background: rgba(201,168,76,0.08); border: 1px solid rgba(201,168,76,0.2); font-family: 'Jost', sans-serif; font-size: 12px; color: var(--gold-light); }

  .bd-room-name { font-family: 'Cormorant Garamond', serif; font-size: 20px; color: #fff; margin-top: 18px; margin-bottom: 8px; }
  .bd-chip-row { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 6px; }
  .bd-chip { padding: 5px 13px; border-radius: 100px; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); font-family: 'Jost', sans-serif; font-size: 11.5px; color: var(--white-60); }

  .bd-guest-row { display: flex; align-items: center; gap: 12px; padding: 12px 0; border-bottom: 1px dashed rgba(255,255,255,0.08); }
  .bd-guest-row:last-child { border-bottom: none; }
  .bd-guest-avatar { width: 34px; height: 34px; border-radius: 50%; background: rgba(201,168,76,0.12); border: 1px solid rgba(201,168,76,0.25); color: var(--gold); display: flex; align-items: center; justify-content: center; font-family: 'Jost', sans-serif; font-size: 12px; font-weight: 700; flex-shrink: 0; }
  .bd-guest-name { font-family: 'Jost', sans-serif; font-size: 13.5px; color: #fff; }
  .bd-guest-tag { font-family: 'Jost', sans-serif; font-size: 11px; color: var(--white-30); text-transform: capitalize; }
  .bd-lead-badge { font-size: 9.5px; font-weight: 700; letter-spacing: 0.04em; text-transform: uppercase; color: var(--gold); background: rgba(201,168,76,0.1); padding: 2px 8px; border-radius: 100px; margin-left: 8px; }

  .bd-note { font-family: 'Jost', sans-serif; font-size: 13px; color: var(--white-60); line-height: 1.7; }

  .bd-addr { font-family: 'Jost', sans-serif; font-size: 13px; color: var(--white-60); line-height: 1.7; display: flex; gap: 8px; align-items: flex-start; }
  .bd-addr svg { width: 15px; height: 15px; flex-shrink: 0; margin-top: 2px; color: var(--gold); }
  .bd-map-link { display: inline-flex; align-items: center; gap: 6px; margin-top: 12px; font-family: 'Jost', sans-serif; font-size: 12px; color: var(--gold); text-decoration: none; }
  .bd-map-link:hover { text-decoration: underline; }
  .bd-times-row { display: flex; gap: 28px; margin-top: 16px; padding-top: 16px; border-top: 1px dashed rgba(255,255,255,0.08); }

  .bd-price-row { display: flex; justify-content: space-between; font-family: 'Jost', sans-serif; font-size: 13px; color: var(--white-60); padding: 8px 0; }
  .bd-price-row.total { border-top: 1px solid rgba(201,168,76,0.25); margin-top: 6px; padding-top: 14px; font-size: 16px; color: #fff; font-weight: 600; }
  .bd-price-row.total span:last-child { color: var(--gold-light); }
  .bd-price-row.discount span:last-child { color: var(--green); }

  .bd-sidebar-sticky { position: sticky; top: 24px; }
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

  @if($paymentFailed)
    <div class="bc-icon-ring bad">⚠️</div>
    <h1 class="bc-title">Payment Not Completed</h1>
    <p class="bc-sub">Your payment could not be completed, so this booking hasn't been confirmed. Nothing has been charged — you can retry payment below.</p>
  @elseif($booking->status === 'refunded')
    <div class="bc-icon-ring bad">⚠️</div>
    <h1 class="bc-title">Booking Failed — Refund Issued</h1>
    <p class="bc-sub">The hotel couldn't confirm this booking after payment. We've automatically refunded you in full — it should reflect in your account within 5–7 business days.</p>
  @elseif($booking->status === 'failed_needs_review')
    <div class="bc-icon-ring bad">⚠️</div>
    <h1 class="bc-title">We're Reviewing Your Booking</h1>
    <p class="bc-sub">Something went wrong confirming this booking after payment. Our team has been alerted and will reach out shortly to resolve this, including a refund if needed.</p>
  @elseif($booking->status === 'cancelled')
    <div class="bc-icon-ring bad">⚠️</div>
    <h1 class="bc-title">Booking Cancelled</h1>
    <p class="bc-sub">{{ $booking->cancellation_reason ?: 'This booking has been cancelled. If a refund is due, it will be processed back to your original payment method.' }}</p>
  @elseif($cancellationPending)
    <div class="bc-icon-ring pending">⏳</div>
    <h1 class="bc-title">Cancellation In Progress</h1>
    <p class="bc-sub">We've submitted your cancellation request to the hotel. Some cancellations are processed offline and can take a little while to finalise — this page will show "Booking Cancelled" once it's done. No need to keep refreshing.</p>
  @elseif($terminalGood)
    <div class="bc-icon-ring good">✅</div>
    <h1 class="bc-title">Booking Confirmed!</h1>
    <p class="bc-sub">Your payment was successful and your room is booked. A confirmation email is on its way to you.</p>
  @else
    <div class="bc-icon-ring pending">⏳</div>
    <h1 class="bc-title">Confirming Your Payment…</h1>
    <p class="bc-sub">This can take a few seconds. This page refreshes itself automatically — no need to resubmit anything.</p>
  @endif

  @if($stillPolling)
  <div class="bc-polling-bar"><div class="bc-polling-bar-fill"></div></div>
  <p class="bc-polling-note">Checking again in a few seconds…</p>
  @endif

</div>

@php
  $nights = ($booking->check_in && $booking->check_out)
      ? \Illuminate\Support\Carbon::parse($booking->check_in)->diffInDays(\Illuminate\Support\Carbon::parse($booking->check_out))
      : null;
  $heroImage = $booking->hotel?->images?->first()?->image_path;
  if ($heroImage && ! str_starts_with($heroImage, 'http')) {
      $heroImage = \Illuminate\Support\Facades\Storage::disk('public')->url($heroImage);
  }
@endphp

<div class="bd-wrap">

  @if($heroImage)
  <div class="bd-hero">
    <img src="{{ $heroImage }}" alt="{{ $booking->hotel?->title }}">
    <div class="bd-hero-caption">
      <h2>{{ $booking->hotel?->title }}</h2>
      @if($booking->hotel?->address)
      <p>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/></svg>
        {{ $booking->hotel->address }}
      </p>
      @endif
    </div>
  </div>
  @endif

  <div class="bd-grid">
    {{-- ── Main column ─────────────────────────────────────────────── --}}
    <div>

      <div class="bd-panel">
        <div class="bd-panel-title">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
          Your Stay
        </div>
        <div class="bd-stay-grid">
          <div>
            <div class="bd-stat-label">Check-in</div>
            <div class="bd-stat-value">{{ \Illuminate\Support\Carbon::parse($booking->check_in)->format('d M Y') }}</div>
          </div>
          <div>
            <div class="bd-stat-label">Check-out</div>
            <div class="bd-stat-value">{{ \Illuminate\Support\Carbon::parse($booking->check_out)->format('d M Y') }}</div>
          </div>
          <div>
            <div class="bd-stat-label">Guests</div>
            <div class="bd-stat-value">{{ $booking->pax_adults }} Adult{{ $booking->pax_adults > 1 ? 's' : '' }}@if($booking->pax_children), {{ $booking->pax_children }} Child(ren) @endif</div>
          </div>
        </div>

        @if($nights !== null)
        <div class="bd-nights-pill">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v20M2 12h20"/></svg>
          {{ $nights }} Night{{ $nights !== 1 ? 's' : '' }} Stay
        </div>
        @endif

        @if($booking->roomType)
        <div class="bd-room-name">{{ $booking->roomType->name }}</div>
        @if(is_array($booking->roomType->inclusions) && count($booking->roomType->inclusions))
        <div class="bd-chip-row">
          @foreach($booking->roomType->inclusions as $inclusion)
          <span class="bd-chip">{{ $inclusion }}</span>
          @endforeach
        </div>
        @endif
        @endif
      </div>

      <div class="bd-panel">
        <div class="bd-panel-title">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg>
          Guests
        </div>
        <div class="bd-guest-row">
          <div class="bd-guest-avatar">{{ strtoupper(substr($booking->lead_guest_name, 0, 1)) }}</div>
          <div>
            <div class="bd-guest-name">{{ $booking->lead_guest_name }}<span class="bd-lead-badge">Lead Guest</span></div>
          </div>
        </div>
        @foreach($booking->travelers as $traveler)
        @if($traveler->full_name !== $booking->lead_guest_name)
        <div class="bd-guest-row">
          <div class="bd-guest-avatar">{{ strtoupper(substr($traveler->full_name, 0, 1)) }}</div>
          <div>
            <div class="bd-guest-name">{{ $traveler->full_name }}</div>
            <div class="bd-guest-tag">{{ $traveler->traveler_type }}</div>
          </div>
        </div>
        @endif
        @endforeach
      </div>

      @if($booking->special_requests)
      <div class="bd-panel">
        <div class="bd-panel-title">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/></svg>
          Special Requests
        </div>
        <p class="bd-note">{{ $booking->special_requests }}</p>
      </div>
      @endif

      @if($booking->hotel)
      <div class="bd-panel">
        <div class="bd-panel-title">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 21h18M5 21V7l7-4 7 4v14M9 21v-6h6v6"/></svg>
          Hotel Information
        </div>
        @if($booking->hotel->address)
        <div class="bd-addr">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/></svg>
          <span>{{ $booking->hotel->address }}</span>
        </div>
        @if($booking->hotel->lat && $booking->hotel->lng)
        <a class="bd-map-link" target="_blank" rel="noopener" href="https://www.google.com/maps/search/?api=1&query={{ $booking->hotel->lat }},{{ $booking->hotel->lng }}">
          View on map →
        </a>
        @endif
        @endif

        @if($booking->hotel->amenities && $booking->hotel->amenities->count())
        <div class="bd-chip-row" style="margin-top:16px;">
          @foreach($booking->hotel->amenities as $amenity)
          <span class="bd-chip">{{ $amenity->name }}</span>
          @endforeach
        </div>
        @endif

        <div class="bd-times-row">
          <div>
            <div class="bd-stat-label">Check-in Time</div>
            <div class="bd-stat-value">{{ $booking->hotel->check_in_time }}</div>
          </div>
          <div>
            <div class="bd-stat-label">Check-out Time</div>
            <div class="bd-stat-value">{{ $booking->hotel->check_out_time }}</div>
          </div>
        </div>
      </div>
      @endif

    </div>

    {{-- ── Sidebar ──────────────────────────────────────────────────── --}}
    <div class="bd-sidebar-sticky">

      <div class="bd-panel">
        <div class="bd-panel-title">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20"/></svg>
          Price Summary
        </div>
        <div class="bd-price-row"><span>Base Amount</span><span>{{ $booking->currency }} {{ number_format($booking->base_amount, 2) }}</span></div>
        @if($booking->tax_amount > 0)
        <div class="bd-price-row"><span>Taxes & Fees</span><span>{{ $booking->currency }} {{ number_format($booking->tax_amount, 2) }}</span></div>
        @endif
        @if($booking->discount_amount > 0)
        <div class="bd-price-row discount"><span>Discount</span><span>&minus; {{ $booking->currency }} {{ number_format($booking->discount_amount, 2) }}</span></div>
        @endif
        <div class="bd-price-row total"><span>Total Paid</span><span>{{ $booking->currency }} {{ number_format($booking->total_amount, 2) }}</span></div>
      </div>

      <div class="bd-panel">
        <div class="bd-panel-title">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 12l2 2 4-4M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          Booking Details
        </div>
        <div class="bc-line"><span>Reference</span><span>{{ $booking->reference }}</span></div>
        <div class="bc-line"><span>Booked On</span><span>{{ $booking->created_at->format('d M Y') }}</span></div>
        @if($booking->tripjack_hold_expires_at)
        <div class="bc-line"><span>Hold Expires</span><span>{{ \Illuminate\Support\Carbon::parse($booking->tripjack_hold_expires_at)->format('d M Y, h:i A') }}</span></div>
        @endif
        <div class="bc-line">
          <span>Status</span>
          <span class="bc-status {{ ($terminalBad || $paymentFailed) ? 'bad' : ($terminalGood ? 'good' : 'pending') }}">
            {{ $cancellationPending ? 'Cancellation Pending' : ($liveStatus ?? ucfirst(str_replace('_',' ',$booking->status))) }}
          </span>
        </div>
      </div>

      @if($terminalGood)
      <div class="bc-next">
        <strong>What happens next:</strong> you'll receive a confirmation email with your booking details and the hotel's contact information. No further action is needed from you.
      </div>
      @endif

      @if($canRequestCancellation)
      <div style="margin-top:16px;">
        <a href="{{ route('hotel.booking.cancel.show', $booking->reference) }}"
           style="display:flex; align-items:center; justify-content:center; gap:8px; padding:13px 24px; border-radius:100px;
                  background:transparent; border:1px solid rgba(220,80,80,0.35); color:#f3a3a3;
                  font-family:'Jost',sans-serif; font-size:12px; font-weight:600; letter-spacing:0.06em;
                  text-transform:uppercase; text-decoration:none; transition:all 0.28s ease;"
           onmouseover="this.style.background='rgba(220,80,80,0.08)'" onmouseout="this.style.background='transparent'">
          Cancel Booking
        </a>
      </div>
      @endif

      @if($paymentFailed)
      <div style="margin-top:16px;">
        <a href="{{ route('hotel.payment.show', $booking->reference) }}"
           style="display:flex; align-items:center; justify-content:center; gap:8px; padding:15px 24px; border-radius:100px;
                  background:linear-gradient(90deg, #c9a84c, #e8c96b); color:#0d0d0d;
                  font-family:'Jost',sans-serif; font-size:12.5px; font-weight:800; letter-spacing:0.1em;
                  text-transform:uppercase; text-decoration:none;">
          Retry Payment
        </a>
      </div>
      @endif

      {{-- Post-confirmation navigation — users shouldn't be stranded --}}
      <div style="display:flex; flex-direction:column; gap:10px; margin-top:16px;">
        <a href="{{ route('hotels') }}"
           style="display:flex; align-items:center; justify-content:center; gap:8px; padding:13px 24px; border-radius:100px;
                  background:rgba(201,168,76,0.12); border:1px solid rgba(201,168,76,0.3); color:#c9a84c;
                  font-family:'Jost',sans-serif; font-size:12.5px; font-weight:600; letter-spacing:0.08em;
                  text-transform:uppercase; text-decoration:none; transition:all 0.28s ease;"
           onmouseover="this.style.background='rgba(201,168,76,0.2)'" onmouseout="this.style.background='rgba(201,168,76,0.12)'">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
          Browse More Hotels
        </a>
        <a href="{{ route('history') }}"
           style="display:flex; align-items:center; justify-content:center; gap:8px; padding:13px 24px; border-radius:100px;
                  background:transparent; border:1px solid rgba(255,255,255,0.15); color:rgba(255,255,255,0.55);
                  font-family:'Jost',sans-serif; font-size:12.5px; font-weight:500; letter-spacing:0.08em;
                  text-transform:uppercase; text-decoration:none; transition:all 0.28s ease;"
           onmouseover="this.style.borderColor='rgba(255,255,255,0.35)'; this.style.color='rgba(255,255,255,0.85)'"
           onmouseout="this.style.borderColor='rgba(255,255,255,0.15)'; this.style.color='rgba(255,255,255,0.55)'">
          Back to My Trips
        </a>
      </div>

    </div>
  </div>
</div>
@endsection
