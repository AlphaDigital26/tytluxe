@extends('layouts.frontend')

@section('meta_title', 'Cancel Booking — ' . $booking->flight_route . ' | TYT Luxe')

@php
  $legs = $legs ?? [];
  $travellers = $travellers ?? [];
  $voidEligibility = $voidEligibility ?? null;
  // Infants can't be cancelled on their own — each goes with its adult.
  $infantPartners = app(\App\Services\FlightBookingService::class)->infantPartners($travellers);
  $selectableTravellers = collect($travellers)->reject(fn ($t) => strtoupper($t['pt'] ?? '') === 'INFANT');
@endphp

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,500;0,600&family=Jost:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
<style>
  :root {
    --gold: #c9a84c; --gold-light: #e8c96b; --dark: #0d0d0d; --dark-2: #141414;
    --white-60: rgba(255,255,255,0.60); --white-30: rgba(255,255,255,0.30);
    --green: #4ade80; --red: #f3a3a3;
  }
  body { background: var(--dark); }
  .cx-wrap { max-width: 600px; margin: 0 auto; padding: 105px 24px 80px; font-family: 'Jost', sans-serif; }
  @media (max-width: 560px) { .cx-wrap { padding: 90px 18px 60px; } }

  .cx-eyebrow { font-size: 10.5px; font-weight: 600; letter-spacing: 0.22em; text-transform: uppercase; color: var(--gold); margin-bottom: 14px; }
  .cx-title { font-family: 'Cormorant Garamond', serif; font-size: clamp(1.8rem, 4vw, 2.3rem); color: #fff; margin-bottom: 10px; }
  .cx-sub { font-size: 13.5px; color: var(--white-60); margin-bottom: 26px; line-height: 1.6; }

  .cx-card { background: var(--dark-2); border: 1px solid rgba(201,168,76,0.22); border-radius: 20px; padding: 26px; margin-bottom: 20px; }
  .cx-line { display: flex; justify-content: space-between; gap: 12px; font-size: 13.5px; color: #fff; padding: 9px 0; border-bottom: 1px dashed rgba(255,255,255,0.08); }
  .cx-line:last-child { border-bottom: none; }
  .cx-line span:first-child { color: var(--white-60); }

  .cx-void-box { border-radius: 16px; padding: 20px 22px; margin-bottom: 20px; background: rgba(74,222,128,0.06); border: 1px solid rgba(74,222,128,0.3); }
  .cx-void-title { font-size: 13.5px; font-weight: 700; color: var(--green); margin-bottom: 6px; }
  .cx-void-note { font-size: 12.5px; color: var(--white-60); line-height: 1.6; margin-bottom: 14px; }
  .cx-void-btn { width: 100%; padding: 14px; border: none; border-radius: 100px; background: var(--green); color: #0d0d0d; font-size: 12.5px; font-weight: 800; letter-spacing: 0.08em; text-transform: uppercase; cursor: pointer; }

  .cx-scope-card { background: var(--dark-2); border: 1px solid rgba(255,255,255,0.08); border-radius: 20px; padding: 26px; margin-bottom: 20px; }
  .cx-scope-title { font-size: 12px; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; color: var(--gold-light); margin-bottom: 16px; }
  .cx-scope-opt { display: flex; align-items: flex-start; gap: 10px; padding: 10px 0; cursor: pointer; }
  .cx-scope-opt input[type="radio"] { margin-top: 3px; accent-color: var(--gold); width: 15px; height: 15px; flex-shrink: 0; }
  .cx-scope-opt-label { font-size: 13.5px; color: #fff; font-weight: 600; }
  .cx-scope-opt-sub { font-size: 11.5px; color: var(--white-30); margin-top: 2px; }

  .cx-sub-picker { display: none; margin: 4px 0 14px 25px; padding: 14px; background: rgba(255,255,255,0.03); border-radius: 12px; }
  .cx-sub-picker.open { display: block; }
  .cx-check-row { display: flex; align-items: center; gap: 10px; padding: 7px 0; font-size: 13px; color: var(--white-60); cursor: pointer; }
  .cx-check-row input { accent-color: var(--gold); width: 15px; height: 15px; cursor: pointer; }

  .cx-quote { background: var(--dark-2); border: 1px solid rgba(201,168,76,0.22); border-radius: 20px; padding: 22px 26px; margin-bottom: 20px; }
  .cx-quote-title { font-size: 12px; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; color: var(--gold-light); margin-bottom: 12px; }
  .cx-quote-msg { font-size: 13px; color: var(--white-60); line-height: 1.6; }
  .cx-quote-loading { color: var(--white-30); animation: cxPulse 1.2s ease-in-out infinite; }
  @keyframes cxPulse { 50% { opacity: 0.45; } }
  .cx-quote-row { display: flex; justify-content: space-between; gap: 12px; font-size: 13.5px; color: #fff; padding: 8px 0; }
  .cx-quote-row span:first-child { color: var(--white-60); }
  .cx-quote-row.charges span:last-child { color: var(--red); }
  .cx-quote-row.refund { border-top: 1px dashed rgba(255,255,255,0.1); margin-top: 4px; padding-top: 12px; font-weight: 700; }
  .cx-quote-row.refund span:last-child { color: var(--green); font-size: 17px; }
  .cx-quote-foot { font-size: 11.5px; color: var(--white-30); margin-top: 10px; line-height: 1.6; }

  .cx-refund-note { font-size: 12px; color: var(--white-30); line-height: 1.7; margin-bottom: 22px; }

  .cx-error { margin-bottom: 20px; padding: 14px 18px; border-radius: 12px; background: rgba(220,80,80,0.08); border: 1px solid rgba(220,80,80,0.3); color: var(--red); font-size: 13px; }

  .cx-actions { display: flex; gap: 12px; flex-wrap: wrap; }
  .cx-btn { flex: 1; min-width: 180px; text-align: center; padding: 15px 20px; border-radius: 100px; font-size: 12.5px; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; text-decoration: none; cursor: pointer; border: none; transition: all 0.28s ease; }
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
  <p class="cx-sub">Review your booking before you confirm — this can't be undone.</p>

  @if(session('booking_error'))
  <div class="cx-error">⚠️ {{ session('booking_error') }}</div>
  @endif

  <div class="cx-card">
    <div class="cx-line"><span>Booking Reference</span><span>{{ $booking->reference }}</span></div>
    <div class="cx-line"><span>Route</span><span>{{ $booking->flight_route }}</span></div>
    <div class="cx-line"><span>Departure</span><span>{{ \Illuminate\Support\Carbon::parse($booking->flight_departure_date)->format('d M Y') }}</span></div>
    @if($booking->flight_return_date)
    <div class="cx-line"><span>Return</span><span>{{ \Illuminate\Support\Carbon::parse($booking->flight_return_date)->format('d M Y') }}</span></div>
    @endif
    <div class="cx-line"><span>Amount Paid</span><span>{{ $booking->currency }} {{ number_format($booking->total_amount, 2) }}</span></div>
  </div>

  @if($voidEligibility)
  <div class="cx-void-box">
    <div class="cx-void-title">✓ Free Same-Day Cancellation Available</div>
    <p class="cx-void-note">You're still within the airline's free same-day cancellation window — cancel now for a full refund, no fees.</p>
    <form method="POST" action="{{ route('hotel.booking.cancel', $booking->reference) }}">
      @csrf
      <input type="hidden" name="cancel_scope" value="void">
      <button type="submit" class="cx-void-btn">Cancel for Free (Same-Day)</button>
    </form>
  </div>
  @endif

  <form method="POST" action="{{ route('hotel.booking.cancel', $booking->reference) }}" id="cxForm">
    @csrf
    <input type="hidden" name="cancel_scope" id="cxScope" value="full">

    <div class="cx-scope-card">
      <p class="cx-scope-title">What would you like to cancel?</p>

      <label class="cx-scope-opt">
        <input type="radio" name="cx_scope_radio" value="full" checked>
        <div>
          <div class="cx-scope-opt-label">Entire booking</div>
          <div class="cx-scope-opt-sub">Cancels every traveller on every flight in this booking.</div>
        </div>
      </label>

      @if($selectableTravellers->count() > 1)
      <label class="cx-scope-opt">
        <input type="radio" name="cx_scope_radio" value="travellers">
        <div>
          <div class="cx-scope-opt-label">Specific traveller(s) only</div>
          <div class="cx-scope-opt-sub">Removes only the selected people — everyone else keeps their booking.</div>
        </div>
      </label>
      <div class="cx-sub-picker" id="cxTravellerPicker">
        @foreach($selectableTravellers as $i => $t)
        <label class="cx-check-row">
          <input type="checkbox" name="traveller_indexes[]" value="{{ $i }}">
          <span>
            {{ $t['fN'] ?? '' }} {{ $t['lN'] ?? '' }}
            @isset($infantPartners[$i])
              <span class="cx-scope-opt-sub" style="display:block;">+ infant {{ $travellers[$infantPartners[$i]]['fN'] ?? '' }} {{ $travellers[$infantPartners[$i]]['lN'] ?? '' }} (travels with this adult, so is cancelled too)</span>
            @endisset
          </span>
        </label>
        @endforeach
      </div>
      @endif

      @if(count($legs) > 1)
      <label class="cx-scope-opt">
        <input type="radio" name="cx_scope_radio" value="trip">
        <div>
          <div class="cx-scope-opt-label">Specific flight(s) only</div>
          <div class="cx-scope-opt-sub">Cancels only the selected leg(s) — the rest of your itinerary stays booked.</div>
        </div>
      </label>
      <div class="cx-sub-picker" id="cxTripPicker">
        @foreach($legs as $i => $leg)
        <label class="cx-check-row">
          <input type="checkbox" name="leg_indexes[]" value="{{ $i }}">
          {{ $leg['src'] }} → {{ $leg['dest'] }}, {{ \Illuminate\Support\Carbon::parse($leg['departureDate'])->format('d M Y') }}
        </label>
        @endforeach
      </div>
      @endif
    </div>

    <div class="cx-quote" aria-live="polite">
      <p class="cx-quote-title">Your Refund</p>
      <div id="cxQuoteBody"><p class="cx-quote-msg cx-quote-loading">Checking the airline's current cancellation charges…</p></div>
    </div>

    <p class="cx-refund-note">Once you confirm, we'll submit the request and your refund will be processed automatically to your original payment method as soon as the airline finalises it — this can take a little while.</p>

    <div class="cx-actions">
      <a href="{{ route('hotel.booking.confirmation', $booking->reference) }}" class="cx-btn cx-btn-back">Keep My Booking</a>
      <button type="submit" class="cx-btn cx-btn-confirm" id="cxConfirmBtn">Confirm Cancellation</button>
    </div>
  </form>
</div>
@endsection

@push('scripts')
<script>
  (function () {
    var travellerPicker = document.getElementById('cxTravellerPicker');
    var tripPicker = document.getElementById('cxTripPicker');
    var scopeInput = document.getElementById('cxScope');
    var form = document.getElementById('cxForm');
    var quoteBody = document.getElementById('cxQuoteBody');
    var quoteUrl = @json(route('flights.cancel.quote', $booking->reference));
    var quoteSeq = 0, quoteTimer = null;

    function money(currency, n) {
      return (currency === 'INR' ? '₹' : currency + ' ') + Number(n).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }
    function showMsg(text, loading) {
      quoteBody.innerHTML = '';
      var p = document.createElement('p');
      p.className = 'cx-quote-msg' + (loading ? ' cx-quote-loading' : '');
      p.textContent = text;
      quoteBody.appendChild(p);
    }
    function row(cls, label, value) {
      return '<div class="cx-quote-row ' + cls + '"><span>' + label + '</span><span>' + value + '</span></div>';
    }

    function fetchQuote() {
      var seq = ++quoteSeq;
      var scope = scopeInput.value;
      var data = new FormData();
      data.append('_token', form.querySelector('input[name="_token"]').value);
      data.append('cancel_scope', scope);
      if (scope === 'travellers') form.querySelectorAll('input[name="traveller_indexes[]"]:checked').forEach(function (c) { data.append('traveller_indexes[]', c.value); });
      if (scope === 'trip') form.querySelectorAll('input[name="leg_indexes[]"]:checked').forEach(function (c) { data.append('leg_indexes[]', c.value); });

      showMsg('Checking the airline\'s current cancellation charges…', true);

      fetch(quoteUrl, { method: 'POST', body: data, headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
        .then(function (r) { return r.ok ? r.json() : { quote: null }; })
        .then(function (res) {
          if (seq !== quoteSeq) return; // a newer selection is already loading
          if (res.empty) {
            showMsg(scope === 'travellers' ? 'Select the traveller(s) to cancel to see your refund.' : 'Select the flight(s) to cancel to see your refund.');
            return;
          }
          var q = res.quote;
          if (!q) {
            showMsg('We couldn\'t fetch the airline\'s current charges right now. Your exact refund will be confirmed once the cancellation is processed.');
            return;
          }
          quoteBody.innerHTML =
            row('charges', 'Cancellation charges &amp; service fee', '− ' + money(q.currency, q.charges)) +
            row('refund', 'Estimated refund', money(q.currency, q.refund)) +
            '<p class="cx-quote-foot">Live quote from the airline as of now. You get back what the airline refunds; our service fee is not refundable (on a free cancellation you get everything back less a {{ number_format(\App\Support\RefundPolicy::FREE_CANCELLATION_FEE, 0) }} fee). Charges can change closer to departure — the final amount is confirmed when the cancellation completes.</p>';
        })
        .catch(function () {
          if (seq === quoteSeq) showMsg('We couldn\'t fetch the airline\'s current charges right now. Your exact refund will be confirmed once the cancellation is processed.');
        });
    }
    function queueQuote() {
      clearTimeout(quoteTimer);
      quoteTimer = setTimeout(fetchQuote, 350);
    }

    document.querySelectorAll('input[name="cx_scope_radio"]').forEach(function (radio) {
      radio.addEventListener('change', function () {
        scopeInput.value = radio.value;
        if (travellerPicker) travellerPicker.classList.toggle('open', radio.value === 'travellers');
        if (tripPicker) tripPicker.classList.toggle('open', radio.value === 'trip');
        queueQuote();
      });
    });
    form.querySelectorAll('input[name="traveller_indexes[]"], input[name="leg_indexes[]"]').forEach(function (c) {
      c.addEventListener('change', queueQuote);
    });
    fetchQuote();

    document.getElementById('cxForm').addEventListener('submit', function () {
      var btn = document.getElementById('cxConfirmBtn');
      btn.disabled = true;
      btn.textContent = 'Cancelling…';
    });
  })();
</script>
@endpush
