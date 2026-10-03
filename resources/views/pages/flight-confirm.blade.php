@extends('layouts.frontend')

@section('meta_title', 'Review Your Booking | TYT Luxe')

@push('styles')
@include('partials.flight-booking-styles')
<style>
  .flc-title { font-family: 'Cormorant Garamond', serif; font-size: clamp(1.9rem, 3.4vw, 2.4rem); color: #fff; margin: 0 0 22px; }
  .flc-head { display: flex; align-items: baseline; justify-content: space-between; gap: 16px; margin-bottom: 20px; }
  .flc-head h2 { margin-bottom: 0; }
  .flc-edit { font-family: 'Jost', sans-serif; font-size: 12px; font-weight: 600; letter-spacing: 0.04em; color: var(--gold-light); text-decoration: none; white-space: nowrap; }
  .flc-edit:hover { color: #fff; }

  /* Passenger table — stacks into cards on narrow screens */
  .flc-table { width: 100%; border-collapse: separate; border-spacing: 0; font-family: 'Jost', sans-serif; font-size: 13px; border: 1px solid rgba(255,255,255,0.08); border-radius: 14px; overflow: hidden; }
  .flc-table th { padding: 13px 16px; background: rgba(255,255,255,0.04); border-bottom: 1px solid rgba(255,255,255,0.08); color: var(--gold); font-size: 10.5px; font-weight: 700; letter-spacing: 0.1em; text-transform: uppercase; text-align: left; white-space: nowrap; }
  .flc-table td { padding: 15px 16px; color: var(--white-80); vertical-align: top; line-height: 1.55; }
  .flc-table tr + tr td { border-top: 1px solid rgba(255,255,255,0.06); }
  .flc-sr { width: 44px; color: var(--white-30) !important; }
  .flc-name { color: #fff; font-weight: 600; }
  .flc-type { display: inline-block; margin-left: 6px; padding: 1px 7px; border-radius: 100px; background: var(--gold-dim); color: var(--gold-light); font-size: 10px; font-weight: 700; letter-spacing: 0.06em; vertical-align: 1px; }
  .flc-sub { display: block; font-size: 11.5px; color: var(--white-60); margin-top: 3px; }
  .flc-list { margin: 0; padding: 0; list-style: none; }
  .flc-list li + li { margin-top: 4px; }
  .flc-na { color: var(--white-30); }
  @media (max-width: 640px) {
    .flc-table, .flc-table tbody, .flc-table tr, .flc-table td { display: block; width: 100%; box-sizing: border-box; }
    .flc-table thead { display: none; }
    .flc-table { border: none; }
    .flc-table tr { border: 1px solid rgba(255,255,255,0.08); border-radius: 14px; margin-bottom: 12px; padding: 6px 0; }
    .flc-table tr + tr td { border-top: none; }
    .flc-table td { padding: 8px 16px; }
    .flc-table td[data-label]::before { content: attr(data-label); display: block; margin-bottom: 3px; color: var(--gold); font-size: 10px; font-weight: 700; letter-spacing: 0.1em; text-transform: uppercase; }
    .flc-sr { display: none !important; }
  }

  /* Contact details */
  .flc-contact { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 14px 24px; font-family: 'Jost', sans-serif; }
  .flc-contact div { min-width: 0; }
  .flc-contact small { display: block; font-size: 10.5px; font-weight: 700; letter-spacing: 0.1em; text-transform: uppercase; color: var(--white-30); margin-bottom: 4px; }
  .flc-contact span { font-size: 14px; color: #fff; overflow-wrap: anywhere; }

  /* Agreement + actions */
  .flc-agree { display: flex; align-items: flex-start; gap: 12px; font-family: 'Jost', sans-serif; font-size: 13px; color: var(--white-80); line-height: 1.6; margin: 0 0 20px; padding: 14px 16px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.08); background: rgba(255,255,255,0.02); cursor: pointer; transition: border-color .2s ease, background .2s ease; }
  .flc-agree:hover { border-color: rgba(201,168,76,0.35); }
  .flc-agree:has(input:checked) { border-color: rgba(201,168,76,0.45); background: rgba(201,168,76,0.05); }
  .flc-agree.invalid { border-color: rgba(243,163,163,0.6); background: rgba(220,80,80,0.06); }
  .flc-agree input { appearance: none; -webkit-appearance: none; flex-shrink: 0; width: 20px; height: 20px; margin: 1px 0 0; border-radius: 6px; border: 1.5px solid rgba(201,168,76,0.6); background: transparent; cursor: pointer; display: grid; place-content: center; transition: background .15s ease; }
  .flc-agree input::after { content: ''; width: 10px; height: 6px; border-left: 2px solid var(--dark); border-bottom: 2px solid var(--dark); transform: rotate(-45deg) translate(1px, -1px); opacity: 0; }
  .flc-agree input:checked { background: var(--gold); border-color: var(--gold); }
  .flc-agree input:checked::after { opacity: 1; }
  .flc-agree input:focus-visible { outline: 2px solid var(--gold-light); outline-offset: 2px; }
  .flc-agree a { color: var(--gold-light); }
  .flc-agree-error { margin: -12px 0 18px; font-family: 'Jost', sans-serif; font-size: 12px; color: #f3a3a3; }
  .flc-agree-error[hidden] { display: none; }
  .flc-actions { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; }
  .flc-actions .flr-submit { width: auto; margin: 0; padding: 15px 30px; }
  .flc-actions a.flr-submit { display: inline-flex; align-items: center; }
  .flc-actions .flc-spacer { flex: 1; }
  .flc-hold-note { font-family: 'Jost', sans-serif; font-size: 11.5px; color: var(--white-30); margin: 12px 0 0; line-height: 1.55; }
  @media (max-width: 560px) {
    .flc-actions .flc-spacer { display: none; }
    .flc-actions .flr-submit { flex: 1 1 100%; justify-content: center; }
  }
</style>
@endpush

@section('content')
@php
  $paxCode = ['ADULT' => 'A', 'CHILD' => 'C', 'INFANT' => 'I'];
  $firstDeparture = $tripInfos[0]['sI'][0]['dt'] ?? null;
  $contactPhoneFull = \App\Services\FlightBookingService::phoneFromInput($input['contact_dial_code'] ?? null, (string) ($input['contact_phone'] ?? ''));
  $emergencyPhoneFull = \App\Services\FlightBookingService::phoneFromInput($input['emergency_dial_code'] ?? null, (string) ($input['emergency_phone'] ?? ''));
@endphp

@include('partials.flight-booking-steps', ['current' => 3, 'stepUrls' => [
  1 => route('flights.review.show'),
  2 => route('flights.passengers.show', ['booking' => $bookingId]),
]])
@include('partials.flight-session-timer', ['expiresAt' => $sessionExpiresAt ?? null, 'resultsUrl' => $resultsUrl ?? null])

<div class="flr-wrap">
  <div>
    <h1 class="flc-title">Review Your Booking</h1>

    @if($errors->any())
    <div class="flr-error" role="alert">
      @foreach($errors->all() as $error)
        <div>⚠️ {{ $error }}</div>
      @endforeach
    </div>
    @endif

    <div class="flr-section">
      <div class="flc-head">
        <h2>Flight Details</h2>
      </div>
      @include('partials.flight-trip-cards', ['tripInfos' => $tripInfos, 'context' => $context])
    </div>

    <div class="flr-section">
      <div class="flc-head">
        <h2>Passenger Details ({{ count($travellers) }})</h2>
        <a href="{{ route('flights.passengers.show', ['booking' => $bookingId]) }}" class="flc-edit">Edit &rsaquo;</a>
      </div>
      <table class="flc-table">
        <thead>
          <tr><th class="flc-sr">Sr.</th><th>Name, Age &amp; Passport</th><th>Seat Booking</th><th>Meal &amp; Baggage Preference</th></tr>
        </thead>
        <tbody>
          @foreach($travellers as $i => $t)
            @php
              $age = ($t['dob'] && $firstDeparture) ? \Carbon\Carbon::parse($t['dob'])->diffInYears(\Carbon\Carbon::parse($firstDeparture)) : null;
              $age = $age !== null ? (int) floor($age) : null;
            @endphp
            <tr>
              <td class="flc-sr">{{ $i + 1 }}</td>
              <td data-label="Name, Age &amp; Passport">
                <span class="flc-name">{{ strtoupper($t['name']) }}</span><span class="flc-type" title="{{ ucfirst(strtolower($t['type'])) }}">{{ $paxCode[$t['type']] ?? $t['type'] }}</span>
                @if($t['dob'])
                  <span class="flc-sub">{{ \Carbon\Carbon::parse($t['dob'])->format('d/m/Y') }}@if($age !== null) &middot; {{ $age === 0 ? 'Under 1 yr' : $age.' yrs' }}@endif</span>
                @endif
                @if($t['passport'])
                  <span class="flc-sub">Passport {{ strtoupper($t['passport']) }}@if($t['passportExpiry']) &middot; expires {{ \Carbon\Carbon::parse($t['passportExpiry'])->format('d/m/Y') }}@endif</span>
                @endif
                @if($t['frequentFlyer'])
                  <span class="flc-sub">Frequent flyer {{ strtoupper($t['frequentFlyer']) }}</span>
                @endif
              </td>
              <td data-label="Seat Booking">
                @if($t['seats'])
                  <ul class="flc-list">@foreach($t['seats'] as $line)<li>{{ $line }}</li>@endforeach</ul>
                @else
                  <span class="flc-na">NA</span>
                @endif
              </td>
              <td data-label="Meal &amp; Baggage Preference">
                @if($t['extras'])
                  <ul class="flc-list">@foreach($t['extras'] as $line)<li>{{ $line }}</li>@endforeach</ul>
                @else
                  <span class="flc-na">NA</span>
                @endif
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>

    <div class="flr-section">
      <div class="flc-head">
        <h2>Contact Details</h2>
        <a href="{{ route('flights.passengers.show', ['booking' => $bookingId]) }}" class="flc-edit">Edit &rsaquo;</a>
      </div>
      <div class="flc-contact">
        <div><small>Email</small><span>{{ $input['contact_email'] ?? '' }}</span></div>
        <div><small>Mobile</small><span>{{ $contactPhoneFull }}</span></div>
        @if(! empty($input['emergency_name']))
          <div><small>Emergency Contact</small><span>{{ $input['emergency_name'] }} &middot; {{ $emergencyPhoneFull }}@if(! empty($input['emergency_email'])) &middot; {{ $input['emergency_email'] }}@endif</span></div>
        @endif
        @if(! empty($input['gst_number']))
          <div><small>GST</small><span>{{ strtoupper($input['gst_number']) }} &middot; {{ $input['gst_registered_name'] ?? '' }}</span></div>
        @endif
        @if(! empty($input['special_requests']))
          <div><small>Notes</small><span>{{ $input['special_requests'] }}</span></div>
        @endif
      </div>
    </div>

    <div class="flr-section">
      <form method="POST" action="{{ route('flights.book') }}" id="flcForm">
        @csrf
        <label class="flc-agree {{ $errors->has('accept_terms') ? 'invalid' : '' }}" id="flcAgreeBox">
          <input type="checkbox" name="accept_terms" value="1" id="flcAgree" required>
          <span>I have reviewed my booking and agree to the <a href="{{ route('terms') }}" target="_blank" rel="noopener">Terms of Use</a>, <a href="{{ route('privacy') }}" target="_blank" rel="noopener">Privacy Policy</a> and the airline's fare rules.</span>
        </label>
        <p class="flc-agree-error" id="flcAgreeError" role="alert" @unless($errors->has('accept_terms')) hidden @endunless>Please tick the box above to continue.</p>

        <input type="hidden" name="review_booking_id" value="{{ $bookingId }}">
        <input type="hidden" name="from_review" value="1">
        <input type="hidden" name="intent" id="flcIntent" value="pay">

        <div class="flc-actions">
          <a href="{{ route('flights.passengers.show', ['booking' => $bookingId]) }}" class="flr-submit outline">&laquo; Back</a>
          <span class="flc-spacer"></span>
          @if($canHold)
            <button type="submit" class="flr-submit outline" data-intent="hold">&#8987; Block</button>
          @endif
          <button type="submit" class="flr-submit" data-intent="pay">Proceed to Pay &raquo;</button>
        </div>
        @if($canHold)
          <p class="flc-hold-note"><b>Block</b> holds this fare with the airline for a few hours without payment. You'll get a link to confirm and pay before it expires.</p>
        @endif
      </form>
    </div>
  </div>

  <aside class="flr-summary">
    <h3>Fare Summary</h3>
    <p class="flr-sum-pax">{{ $context['adults'] }} Adult{{ $context['adults'] > 1 ? 's' : '' }}@if($context['children']), {{ $context['children'] }} Child{{ $context['children'] > 1 ? 'ren' : '' }}@endif@if($context['infants']), {{ $context['infants'] }} Infant{{ $context['infants'] > 1 ? 's' : '' }}@endif &middot; {{ ucwords(strtolower(str_replace('_', ' ', $context['cabinClass']))) }}</p>

    <div class="flr-sum-row"><span>Base fare</span><span>&#8377;{{ number_format($summary['base_fare'], 2) }}</span></div>
    <div class="flr-sum-row">
      <span><button type="button" class="flr-sum-toggle" aria-expanded="false" aria-controls="flcTaxBreakdown">Taxes and fees</button></span>
      <span>&#8377;{{ number_format($summary['airline_taxes'] + $summary['convenience_fee'], 2) }}</span>
    </div>
    <div class="flr-sum-sub" id="flcTaxBreakdown" hidden>
      <div><span>Airline taxes &amp; surcharges</span><span>&#8377;{{ number_format($summary['airline_taxes'], 2) }}</span></div>
      <div><span>Convenience fee</span><span>&#8377;{{ number_format($summary['convenience_fee'], 2) }}</span></div>
    </div>
    @if($summary['addons'] > 0 || $summary['addon_rows'])
      <div class="flr-sum-row">
        <span><button type="button" class="flr-sum-toggle" aria-expanded="false" aria-controls="flcAddonBreakdown">Add-ons</button></span>
        <span>&#8377;{{ number_format($summary['addons'], 2) }}</span>
      </div>
      <div class="flr-sum-sub" id="flcAddonBreakdown" hidden>
        @foreach($summary['addon_rows'] as $row)
          <div><span>{{ $row['label'] }} &times; {{ $row['count'] }}</span><span>@if($row['amount'] > 0)&#8377;{{ number_format($row['amount'], 2) }}@else Free @endif</span></div>
        @endforeach
      </div>
    @endif
    <div class="flr-line total"><span>Amount to Pay</span><span>&#8377;{{ number_format($summary['total'], 2) }}</span></div>

    <button type="submit" form="flcForm" class="flr-submit" data-intent="pay">Proceed to Pay</button>
    <p class="flr-note">You'll be redirected to our secure payment partner to complete your booking.</p>
  </aside>
</div>
@endsection

@push('scripts')
<script>
(function () {
  var form = document.getElementById('flcForm');
  var intent = document.getElementById('flcIntent');
  var submitting = false;

  document.querySelectorAll('[data-intent]').forEach(function (btn) {
    btn.addEventListener('click', function () { intent.value = btn.dataset.intent; });
  });

  // The agreement box must be ticked — our own message instead of the
  // browser's bubble (which the sidebar's Pay button would show far away).
  var agree = document.getElementById('flcAgree');
  var agreeBox = document.getElementById('flcAgreeBox');
  var agreeError = document.getElementById('flcAgreeError');
  agree.addEventListener('invalid', function (e) {
    e.preventDefault();
    agreeBox.classList.add('invalid');
    agreeError.hidden = false;
    agreeBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
    agree.focus({ preventScroll: true });
  });
  agree.addEventListener('change', function () {
    if (agree.checked) { agreeBox.classList.remove('invalid'); agreeError.hidden = true; }
  });

  // One click books once: the second click of a double-click is ignored.
  form.addEventListener('submit', function (e) {
    if (submitting) { e.preventDefault(); return; }
    submitting = true;
    document.querySelectorAll('[data-intent]').forEach(function (b) { b.disabled = true; });
    var active = document.querySelector('[data-intent="' + intent.value + '"]');
    if (active) active.textContent = intent.value === 'hold' ? 'Blocking…' : 'Please wait…';
  });

  document.querySelectorAll('.flr-sum-toggle').forEach(function (t) {
    t.addEventListener('click', function () {
      var box = document.getElementById(t.getAttribute('aria-controls'));
      var open = t.getAttribute('aria-expanded') !== 'true';
      t.setAttribute('aria-expanded', open ? 'true' : 'false');
      box.hidden = !open;
    });
  });
})();
</script>
@endpush
