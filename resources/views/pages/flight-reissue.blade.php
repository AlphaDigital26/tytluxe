@extends('layouts.frontend')

@section('meta_title', 'Reschedule Flight — ' . $booking->flight_route . ' | TYT Luxe')

@php
  $legs = $legs ?? [];
  $options = $options ?? null;
  $legIndex = $legIndex ?? null;
  $newDate = $newDate ?? null;
@endphp

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com" />
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,500;0,600&family=Jost:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
<style>
  :root {
    --gold: #c9a84c; --gold-light: #e8c96b;
    --dark: #0d0d0d; --dark-2: #141414;
    --white-60: rgba(255,255,255,0.60); --white-30: rgba(255,255,255,0.30); --red: #f3a3a3;
  }
  body { background: var(--dark); }
  .rx-wrap { max-width: 680px; margin: 0 auto; padding: 105px 24px 90px; font-family: 'Jost', sans-serif; }
  @media (max-width: 560px) { .rx-wrap { padding: 90px 18px 60px; } }

  .rx-eyebrow { font-size: 10.5px; font-weight: 600; letter-spacing: 0.22em; text-transform: uppercase; color: var(--gold); margin-bottom: 10px; }
  .rx-title { font-family: 'Cormorant Garamond', serif; font-size: clamp(1.8rem, 4vw, 2.3rem); color: #fff; margin-bottom: 8px; }
  .rx-sub { font-size: 13.5px; color: var(--white-60); margin-bottom: 28px; line-height: 1.6; }
  .rx-error { margin-bottom: 20px; padding: 14px 18px; border-radius: 12px; background: rgba(220,80,80,0.08); border: 1px solid rgba(220,80,80,0.3); color: var(--red); font-size: 13px; }

  .rx-card { background: var(--dark-2); border: 1px solid rgba(201,168,76,0.22); border-radius: 20px; padding: 26px; margin-bottom: 20px; }
  .rx-field { margin-bottom: 18px; }
  .rx-field label { display: block; font-size: 10px; font-weight: 600; letter-spacing: 0.08em; text-transform: uppercase; color: var(--gold); margin-bottom: 8px; }
  .rx-field select, .rx-field input { width: 100%; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.12); border-radius: 10px; padding: 12px 14px; color: #fff; font-family: 'Jost', sans-serif; font-size: 13.5px; outline: none; box-sizing: border-box; }
  .rx-btn { width: 100%; padding: 15px; border: none; border-radius: 100px; background: linear-gradient(90deg, #c9a84c, #e8c96b); color: var(--dark); font-size: 12.5px; font-weight: 800; letter-spacing: 0.08em; text-transform: uppercase; cursor: pointer; }

  .rx-option { display: block; background: var(--dark-2); border: 1px solid rgba(255,255,255,0.08); border-radius: 14px; padding: 16px 20px; margin-bottom: 12px; cursor: pointer; transition: border-color .2s ease; }
  .rx-option:hover, .rx-option:has(input:checked) { border-color: rgba(201,168,76,0.45); }
  .rx-option-top { display: flex; align-items: center; gap: 14px; }
  .rx-option input { accent-color: var(--gold); width: 17px; height: 17px; flex-shrink: 0; }
  .rx-option-logo { width: 32px; height: 32px; border-radius: 8px; background: #fff; object-fit: contain; flex-shrink: 0; }
  .rx-option-main { flex: 1; min-width: 0; font-size: 13.5px; color: #fff; }
  .rx-option-airline { font-weight: 600; }
  .rx-option-sub { font-size: 11.5px; color: var(--white-30); margin-top: 3px; }
  .rx-option-times { display: flex; align-items: center; gap: 10px; margin-top: 8px; font-size: 15px; font-weight: 600; color: #fff; }
  .rx-option-times small { font-size: 11px; font-weight: 500; color: var(--white-60); }
  .rx-option-times sup { font-size: 10px; color: var(--gold-light); margin-left: 2px; }
  .rx-option-path { flex: 1; min-width: 50px; text-align: center; font-size: 10.5px; color: var(--white-30); border-bottom: 1px dashed rgba(255,255,255,0.15); padding-bottom: 3px; }
  .rx-option-price { text-align: right; font-size: 15px; font-weight: 700; color: var(--gold-light); white-space: nowrap; }
  .rx-option-price small { display: block; font-size: 10.5px; font-weight: 500; color: var(--white-30); }
  .rx-breakdown { margin: 12px 0 0 31px; font-size: 12px; color: var(--white-60); }
  .rx-breakdown summary { cursor: pointer; color: var(--gold); font-size: 11.5px; list-style: none; }
  .rx-breakdown summary::-webkit-details-marker { display: none; }
  .rx-breakdown summary::after { content: ' ▾'; }
  .rx-breakdown[open] summary::after { content: ' ▴'; }
  .rx-breakdown-rows { margin-top: 8px; padding: 10px 14px; border-radius: 10px; background: rgba(255,255,255,0.03); }
  .rx-breakdown-row { display: flex; justify-content: space-between; gap: 12px; padding: 4px 0; }
  .rx-breakdown-row.total { border-top: 1px dashed rgba(255,255,255,0.1); margin-top: 4px; padding-top: 8px; color: #fff; font-weight: 700; }
  .rx-breakdown-row.credit span:last-child { color: #4ade80; }
  @media (max-width: 560px) {
    .rx-option { padding: 14px 16px; }
    .rx-option-logo { display: none; }
    .rx-breakdown { margin-left: 0; }
  }
  .rx-note { font-size: 11.5px; color: var(--white-30); line-height: 1.6; margin: 16px 0; }
</style>
@endpush

@section('content')
<div class="rx-wrap">
  <p class="rx-eyebrow">Reschedule Flight</p>
  <h1 class="rx-title">Change Your Travel Date</h1>
  <p class="rx-sub">{{ $booking->flight_route }} &middot; Booking {{ $booking->reference }} — you can reschedule this booking once. Any fare difference plus the airline's reschedule fee will apply.</p>

  @if(session('booking_error') || ($searchError ?? null))
  <div class="rx-error">⚠️ {{ session('booking_error') ?? $searchError }}</div>
  @endif

  <div class="rx-card">
    <form method="POST" action="{{ route('flights.reissue.search', $booking->reference) }}">
      @csrf
      <div class="rx-field">
        <label>Which flight?</label>
        <select name="leg_index" required>
          @foreach($legs as $i => $leg)
            <option value="{{ $i }}" {{ (int) $legIndex === $i ? 'selected' : '' }}>{{ $leg['src'] }} → {{ $leg['dest'] }} (currently {{ \Carbon\Carbon::parse($leg['departureDate'])->format('d M Y') }})</option>
          @endforeach
        </select>
      </div>
      <div class="rx-field">
        <label>New Travel Date</label>
        <input type="date" name="new_date" value="{{ $newDate }}" min="{{ now()->addDay()->toDateString() }}" required>
      </div>
      <button type="submit" class="rx-btn">Find New Flights</button>
    </form>
  </div>

  @if(is_array($options))
    @if(empty($options))
      <p class="rx-note">No flights found for this date. Try a different date.</p>
    @else
      <form method="POST" action="{{ route('flights.reissue.review', $booking->reference) }}" id="rxReviewForm">
        @csrf
        <input type="hidden" name="leg_index" value="{{ $legIndex }}">
        @foreach($options as $option)
          @php
            $dep = \Carbon\Carbon::parse($option['depTime']);
            $arr = \Carbon\Carbon::parse($option['arrTime']);
            $dayDiff = $dep->copy()->startOfDay()->diffInDays($arr->copy()->startOfDay());
            $stops = $option['stops'] ?? 0;
            $bd = $option['breakdown'] ?? null;
            $money = fn ($v) => '₹'.number_format($v, 2);
          @endphp
          <label class="rx-option">
            <div class="rx-option-top">
              <input type="radio" name="price_id" value="{{ $option['id'] }}" required>
              <img class="rx-option-logo" src="https://images.kiwi.com/airlines/64/{{ $option['airlineCode'] }}.png" alt="" loading="lazy" onerror="this.style.visibility='hidden'">
              <div class="rx-option-main">
                <div class="rx-option-airline">{{ $option['airlineName'] ?? '' ?: $option['airlineCode'] }}</div>
                <div class="rx-option-sub">{{ $option['flightNos'] ?? ($option['airlineCode'].' '.($option['flightNo'] ?? '')) }}</div>
              </div>
              <div class="rx-option-price">₹{{ number_format($option['amountPayable']) }}<small>to pay</small></div>
            </div>
            <div class="rx-option-times">
              <span>{{ $dep->format('H:i') }} <small>{{ $option['from'] ?? '' }}</small></span>
              <span class="rx-option-path">
                @if(! empty($option['duration'])){{ intdiv($option['duration'], 60) }}h {{ $option['duration'] % 60 }}m · @endif
                {{ $stops === 0 ? 'Non-stop' : $stops.' stop'.($stops > 1 ? 's' : '').(! empty($option['via']) ? ' via '.$option['via'] : '') }}
              </span>
              <span>{{ $arr->format('H:i') }}@if($dayDiff > 0)<sup>+{{ $dayDiff }}</sup>@endif <small>{{ $option['to'] ?? '' }}</small></span>
            </div>
            <div class="rx-option-sub" style="margin-left:31px;">{{ $dep->format('D, j M Y') }}</div>
            @if($bd)
              <details class="rx-breakdown">
                <summary>Price breakdown</summary>
                <div class="rx-breakdown-rows">
                  <div class="rx-breakdown-row"><span>Fare &amp; tax difference</span><span>{{ $money($bd['fareDifference']) }}</span></div>
                  @if($bd['airlineFee'] > 0)<div class="rx-breakdown-row"><span>Airline reschedule fee</span><span>{{ $money($bd['airlineFee']) }}</span></div>@endif
                  @if($bd['serviceFee'] > 0)<div class="rx-breakdown-row"><span>Reschedule service fee</span><span>{{ $money($bd['serviceFee']) }}</span></div>@endif
                  @if($bd['ancillaryRefund'] > 0)<div class="rx-breakdown-row credit"><span>Refund for earlier seats/meals/baggage</span><span>− {{ $money($bd['ancillaryRefund']) }}</span></div>@endif
                  <div class="rx-breakdown-row total"><span>Total to pay (all travellers)</span><span>{{ $money($option['amountPayable']) }}</span></div>
                </div>
              </details>
            @endif
          </label>
        @endforeach
        <p class="rx-note">Prices cover all travellers on this booking. The fare is re-confirmed with the airline when you continue, and the payment page shows the exact amount before you pay.</p>
        <button type="submit" class="rx-btn">Continue to Pay</button>
      </form>
    @endif
  @endif
</div>
@endsection
