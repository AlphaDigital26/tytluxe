@extends('layouts.frontend')

@section('meta_title', 'Booking Confirmation | TYT Luxe')

@php
  $paymentFailed = $booking->status === 'payment_failed';
  $cancellationPending = $booking->cancellation_requested_at !== null && $booking->status !== 'cancelled';
  $terminalGood = $booking->status === 'confirmed' && ! $cancellationPending;
  $terminalBad = in_array($booking->status, ['refunded', 'failed_needs_review', 'cancelled', 'hold_expired'], true);
  $onHold = $booking->status === 'on_hold';
  $holdExpiresAt = $booking->tripjack_hold_expires_at ? \Illuminate\Support\Carbon::parse($booking->tripjack_hold_expires_at) : null;
  $holdAlreadyExpired = $onHold && $holdExpiresAt && $holdExpiresAt->isPast();
  $canRequestCancellation = $booking->status === 'confirmed' && ! $cancellationPending
      && $booking->flight_departure_date && \Illuminate\Support\Carbon::parse($booking->flight_departure_date)->isFuture();
  // Flight Settings → "Guests can cancel online" off: the guest is pointed to support instead.
  $cancelViaSupport = $canRequestCancellation && ! \App\Support\FlightSettings::allowGuestCancel();
  $canRequestCancellation = $canRequestCancellation && ! $cancelViaSupport;
  $pnr = $booking->tripjack_flight_pnr ?? [];
  $ticketNumbers = $booking->tripjack_flight_ticket_numbers ?? [];
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
    --green: #4ade80; --amber: #e0b34a; --red: #f3a3a3;
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

  .bd-wrap { max-width: 720px; margin: 0 auto; padding: 44px 24px 90px; text-align: left; }
  @media (max-width: 560px) { .bd-wrap { padding: 34px 18px 60px; } }

  .bd-panel { background: var(--dark-2); border: 1px solid rgba(255,255,255,0.08); border-radius: 18px; padding: 26px 28px; margin-bottom: 22px; }
  @media (max-width: 480px) { .bd-panel { padding: 18px 16px; border-radius: 14px; margin-bottom: 16px; } }
  .bd-panel-title { font-family: 'Jost', sans-serif; font-size: 11px; font-weight: 700; letter-spacing: 0.1em; text-transform: uppercase; color: var(--gold); margin-bottom: 18px; }

  .bd-route { font-family: 'Cormorant Garamond', serif; font-size: 22px; color: #fff; margin-bottom: 8px; }
  .bd-stat-label { font-family: 'Jost', sans-serif; font-size: 10.5px; color: var(--white-30); text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 6px; }
  .bd-stat-value { font-family: 'Jost', sans-serif; font-size: 15px; color: #fff; font-weight: 500; }
  .bd-stay-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px 16px; }
  @media (max-width: 480px) { .bd-stay-grid { grid-template-columns: repeat(2, 1fr); } }

  .bd-price-row { display: flex; justify-content: space-between; font-family: 'Jost', sans-serif; font-size: 13px; color: var(--white-60); padding: 8px 0; }
  .bd-price-row.total { border-top: 1px solid rgba(201,168,76,0.25); margin-top: 6px; padding-top: 14px; font-size: 16px; color: #fff; font-weight: 600; }
  .bd-price-row.total span:last-child { color: var(--gold-light); }
  .bc-notice { margin: 0 0 22px; padding: 12px 16px; border-radius: 8px; text-align: left; font-size: 14px; line-height: 1.5;
    background: rgba(224,179,74,0.08); border: 1px solid rgba(224,179,74,0.35); color: var(--amber); }
</style>
@endpush

@section('content')
<div class="bc-wrap">

  {{-- Messages from actions that redirect here (hold confirm / fare change,
       extras unavailable, …) — previously flashed but never shown. --}}
  @if(session('booking_error'))
    <div class="bc-notice" role="alert">⚠️ {{ session('booking_error') }}</div>
  @endif

  @if($paymentFailed)
    <div class="bc-icon-ring bad">⚠️</div>
    <h1 class="bc-title">Payment Not Completed</h1>
    <p class="bc-sub">Your payment could not be completed, so this booking hasn't been confirmed. Nothing has been charged — you can retry payment below.</p>
  @elseif($booking->status === 'refunded')
    <div class="bc-icon-ring bad">⚠️</div>
    <h1 class="bc-title">Booking Failed — Refund Issued</h1>
    <p class="bc-sub">The airline couldn't confirm this booking after payment. We've automatically refunded you in full — it should reflect in your account within 5–7 business days.</p>
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
    <p class="bc-sub">We've submitted your cancellation request to the airline. This is processed offline and can take a little while to finalise — this page will show "Booking Cancelled" once it's done. No need to keep refreshing.</p>
    @if($booking->cancellation_requested_at->lt(now()->subMinutes(10)))
    <p class="bc-sub">This is taking longer than usual with the airline — our team has been notified and is following it up. Your refund will be processed automatically as soon as the airline confirms.</p>
    @endif
  @elseif($booking->status === 'hold_expired')
    <div class="bc-icon-ring bad">⚠️</div>
    <h1 class="bc-title">Held Fare Expired</h1>
    <p class="bc-sub">This fare was not confirmed before the airline's hold deadline and is no longer available. No payment was ever taken. Please search again for current fares.</p>
  @elseif($onHold && $holdAlreadyExpired)
    <div class="bc-icon-ring bad">⚠️</div>
    <h1 class="bc-title">Held Fare Expired</h1>
    <p class="bc-sub">This fare's hold deadline has passed and is no longer available. No payment was ever taken. Please search again for current fares.</p>
  @elseif($onHold)
    <div class="bc-icon-ring pending">⏳</div>
    <h1 class="bc-title">Fare On Hold — Payment Pending</h1>
    <p class="bc-sub">
      We've blocked this fare with the airline, no payment taken yet.
      @if($holdExpiresAt)Confirm and pay before <strong>{{ $holdExpiresAt->format('d M Y, h:i A') }}</strong> or it will be automatically released.@endif
    </p>
  @elseif($terminalGood)
    <div class="bc-icon-ring good">✅</div>
    <h1 class="bc-title">Booking Confirmed!</h1>
    <p class="bc-sub">Your payment was successful and your flight is booked. A confirmation email is on its way to you.</p>
  @else
    <div class="bc-icon-ring pending">⏳</div>
    <h1 class="bc-title">Confirming Your Booking…</h1>
    <p class="bc-sub">This can take a few seconds. This page refreshes itself automatically — no need to resubmit anything.</p>
  @endif

  @if($stillPolling)
  <div class="bc-polling-bar"><div class="bc-polling-bar-fill"></div></div>
  <p class="bc-polling-note">Checking again in a few seconds…</p>
  @endif

</div>

<div class="bd-wrap">

  <div class="bd-panel">
    <div class="bd-panel-title">Your Flight</div>
    <div class="bd-route">{{ $booking->flight_route }}</div>
    <div class="bd-stay-grid" style="margin-top:14px;">
      <div>
        <div class="bd-stat-label">Departure</div>
        <div class="bd-stat-value">{{ $booking->flight_departure_date ? \Illuminate\Support\Carbon::parse($booking->flight_departure_date)->format('d M Y') : '—' }}</div>
      </div>
      @if($booking->flight_return_date)
      <div>
        <div class="bd-stat-label">Return</div>
        <div class="bd-stat-value">{{ \Illuminate\Support\Carbon::parse($booking->flight_return_date)->format('d M Y') }}</div>
      </div>
      @endif
      <div>
        <div class="bd-stat-label">Passengers</div>
        <div class="bd-stat-value">{{ $booking->pax_adults }} Adult{{ $booking->pax_adults > 1 ? 's' : '' }}@if($booking->pax_children), {{ $booking->pax_children }} Child(ren) @endif@if($booking->pax_infants), {{ $booking->pax_infants }} Infant(s) @endif</div>
      </div>
    </div>
  </div>

  @if(count($pnr) || count($ticketNumbers))
  <div class="bd-panel">
    <div class="bd-panel-title">PNR & Ticketing</div>
    @foreach($pnr as $segmentKey => $pnrNumber)
    <div class="bc-line"><span>PNR ({{ $segmentKey }})</span><span>{{ $pnrNumber }}</span></div>
    @endforeach
    {{-- Per traveller: [{name, tickets: {"DEL-BOM": "098…"}}]. Bookings
         ticketed before that was stored hold one traveller's
         {"DEL-BOM": "098…"} map — still shown, keyed by sector. --}}
    @foreach($ticketNumbers as $key => $entry)
      @if(is_array($entry))
        @foreach(($entry['tickets'] ?? []) as $sector => $ticketNumber)
        <div class="bc-line"><span>Ticket — {{ $entry['name'] ?? 'Traveller' }}{{ count($entry['tickets']) > 1 ? ' ('.$sector.')' : '' }}</span><span>{{ $ticketNumber }}</span></div>
        @endforeach
      @else
        <div class="bc-line"><span>Ticket ({{ $key }})</span><span>{{ $entry }}</span></div>
      @endif
    @endforeach
  </div>
  @endif

  @if($booking->travelers && $booking->travelers->count())
  <div class="bd-panel">
    <div class="bd-panel-title">Travellers</div>
    @foreach($booking->travelers as $traveler)
    <div class="bc-line"><span>{{ $traveler->full_name }}</span><span style="text-transform:capitalize;">{{ $traveler->traveler_type }}</span></div>
    @endforeach
  </div>
  @endif

  <div class="bd-panel">
    <div class="bd-panel-title">Price Summary</div>
    <div class="bd-price-row total"><span>Total Paid</span><span>{{ $booking->currency }} {{ number_format($booking->total_amount, 2) }}</span></div>
  </div>

  @if($booking->vertical === 'flight' && count($booking->flight_partial_amendments ?? []))
  <div class="bd-panel">
    <div class="bd-panel-title">Amendments</div>
    @foreach($booking->flight_partial_amendments as $amendment)
      <div class="bc-line">
        <span>{{ ucwords(strtolower(str_replace('_', ' ', $amendment['type']))) }} &middot; {{ \Illuminate\Support\Carbon::parse($amendment['resolvedAt'])->format('d M Y') }}</span>
        <span>
          @if($amendment['outcome'] === 'REJECTED') Rejected
          @elseif($amendment['outcome'] === 'SUCCESS') Refunded {{ $booking->currency }} {{ number_format($amendment['refundAmount'], 2) }}
          @else Processed — refund pending manual review
          @endif
        </span>
      </div>
    @endforeach
  </div>
  @endif

  <div class="bd-panel">
    <div class="bd-panel-title">Booking Details</div>
    <div class="bc-line"><span>Reference</span><span>{{ $booking->reference }}</span></div>
    <div class="bc-line"><span>Booked On</span><span>{{ $booking->created_at->format('d M Y') }}</span></div>
    <div class="bc-line">
      <span>Status</span>
      <span class="bc-status {{ ($terminalBad || $paymentFailed) ? 'bad' : ($terminalGood ? 'good' : 'pending') }}">
        {{ $cancellationPending ? 'Cancellation Pending' : ($liveStatus ?? ucfirst(str_replace('_',' ',$booking->status))) }}
      </span>
    </div>
  </div>

  @if($terminalGood)
  <div class="bc-next">
    <strong>What happens next:</strong> you'll receive a confirmation email with your e-ticket and booking details. No further action is needed from you.
  </div>
  @endif

  @if(! in_array($booking->status, ['pending_payment', 'payment_failed', 'on_hold', 'hold_expired'], true))
  <div style="margin-top:16px;">
    <a href="{{ route('hotel.booking.invoice', $booking->reference) }}"
       style="display:flex; align-items:center; justify-content:center; gap:8px; padding:13px 24px; border-radius:100px;
              background:rgba(255,255,255,0.06); border:1px solid rgba(255,255,255,0.15); color:#fff;
              font-family:'Jost',sans-serif; font-size:12.5px; font-weight:600; letter-spacing:0.06em;
              text-transform:uppercase; text-decoration:none;">
      Download Invoice
    </a>
  </div>
  @endif

  @if($onHold && ! $holdAlreadyExpired)
  <div style="margin-top:16px; display:flex; flex-direction:column; gap:10px;">
    <form method="POST" action="{{ route('flights.hold.confirm', $booking->reference) }}">
      @csrf
      <button type="submit" style="width:100%; padding:15px 24px; border:none; border-radius:100px;
              background:linear-gradient(90deg, #c9a84c, #e8c96b); color:#0d0d0d;
              font-family:'Jost',sans-serif; font-size:12.5px; font-weight:800; letter-spacing:0.1em;
              text-transform:uppercase; cursor:pointer;">
        Confirm &amp; Pay Now
      </button>
    </form>
    <form method="POST" action="{{ route('flights.hold.release', $booking->reference) }}" onsubmit="return confirm('Release this held fare? This cannot be undone.');">
      @csrf
      <button type="submit" style="width:100%; padding:13px 24px; border-radius:100px;
              background:transparent; border:1px solid rgba(220,80,80,0.35); color:#f3a3a3;
              font-family:'Jost',sans-serif; font-size:12px; font-weight:600; letter-spacing:0.06em;
              text-transform:uppercase; cursor:pointer;">
        Release Hold
      </button>
    </form>
  </div>
  @endif

  @if($booking->vertical === 'flight' && $terminalGood && \App\Support\FlightSettings::allowGuestExtras())
  <div style="margin-top:16px;">
    <a href="{{ route('flights.extras.show', $booking->reference) }}"
       style="display:flex; align-items:center; justify-content:center; gap:8px; padding:13px 24px; border-radius:100px;
              background:rgba(201,168,76,0.1); border:1px solid rgba(201,168,76,0.35); color:#e8c96b;
              font-family:'Jost',sans-serif; font-size:12.5px; font-weight:600; letter-spacing:0.06em;
              text-transform:uppercase; text-decoration:none;">
      Add Seats, Meals &amp; Baggage
    </a>
  </div>
  @endif

  @if($booking->vertical === 'flight' && $terminalGood && $booking->flight_reissued_at === null && \App\Support\FlightSettings::allowGuestReschedule())
  <div style="margin-top:16px;">
    <a href="{{ route('flights.reissue.show', $booking->reference) }}"
       style="display:flex; align-items:center; justify-content:center; gap:8px; padding:13px 24px; border-radius:100px;
              background:rgba(255,255,255,0.06); border:1px solid rgba(255,255,255,0.15); color:#fff;
              font-family:'Jost',sans-serif; font-size:12.5px; font-weight:600; letter-spacing:0.06em;
              text-transform:uppercase; text-decoration:none;">
      Reschedule Flight
    </a>
  </div>
  @endif

  @if($booking->vertical === 'flight' && $terminalGood)
  <div style="margin-top:16px;">
    <a href="{{ route('flights.fare-rules.show', $booking->reference) }}"
       style="display:flex; align-items:center; justify-content:center; gap:8px; padding:13px 24px; border-radius:100px;
              background:rgba(255,255,255,0.06); border:1px solid rgba(255,255,255,0.15); color:#fff;
              font-family:'Jost',sans-serif; font-size:12.5px; font-weight:600; letter-spacing:0.06em;
              text-transform:uppercase; text-decoration:none;">
      View Cancellation &amp; Change Policy
    </a>
  </div>
  @endif

  @if($canRequestCancellation)
  <div style="margin-top:16px;">
    <a href="{{ route('hotel.booking.cancel.show', $booking->reference) }}"
       style="display:flex; align-items:center; justify-content:center; gap:8px; padding:13px 24px; border-radius:100px;
              background:transparent; border:1px solid rgba(220,80,80,0.35); color:#f3a3a3;
              font-family:'Jost',sans-serif; font-size:12px; font-weight:600; letter-spacing:0.06em;
              text-transform:uppercase; text-decoration:none;">
      Cancel Booking
    </a>
  </div>
  @endif

  @if($cancelViaSupport)
  <p style="margin-top:16px; text-align:center; font-family:'Jost',sans-serif; font-size:13px; color:rgba(255,255,255,0.6);">
    Need to cancel or change this booking? {{ \App\Support\FlightSettings::contactUsMessage() }}
  </p>
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

  <div style="display:flex; flex-direction:column; gap:10px; margin-top:16px;">
    <a href="{{ route('flights.search') }}"
       style="display:flex; align-items:center; justify-content:center; gap:8px; padding:13px 24px; border-radius:100px;
              background:rgba(201,168,76,0.12); border:1px solid rgba(201,168,76,0.3); color:#c9a84c;
              font-family:'Jost',sans-serif; font-size:12.5px; font-weight:600; letter-spacing:0.08em;
              text-transform:uppercase; text-decoration:none;">
      Search More Flights
    </a>
    <a href="{{ route('history') }}"
       style="display:flex; align-items:center; justify-content:center; gap:8px; padding:13px 24px; border-radius:100px;
              background:transparent; border:1px solid rgba(255,255,255,0.15); color:rgba(255,255,255,0.55);
              font-family:'Jost',sans-serif; font-size:12.5px; font-weight:500; letter-spacing:0.08em;
              text-transform:uppercase; text-decoration:none;">
      Back to My Trips
    </a>
  </div>

</div>
@endsection
