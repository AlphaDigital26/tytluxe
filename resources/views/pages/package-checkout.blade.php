@extends('layouts.frontend')

@section('meta_title', 'Book ' . $package->title . ' | TYT Luxe')

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com" />
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,500;0,600;1,500&family=Jost:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
<style>
  :root {
    --gold: #c9a84c; --gold-light: #e8c96b; --gold-dim: rgba(201,168,76,0.15);
    --dark: #0d0d0d; --dark-2: #141414; --dark-3: #1b1b1b;
    --white-80: rgba(255,255,255,0.80); --white-60: rgba(255,255,255,0.60); --white-30: rgba(255,255,255,0.30); --white-10: rgba(255,255,255,0.10);
  }
  body { background: var(--dark); }
  .pc-wrap { max-width: 1160px; margin: 0 auto; padding: 110px 24px 90px; font-family: 'Jost', sans-serif; color: var(--white-80); }
  .pc-back { color: var(--white-60); font-size: 13px; text-decoration: none; }
  .pc-back:hover { color: var(--gold); }
  .pc-eyebrow { font-size: 10.5px; font-weight: 600; letter-spacing: 0.22em; text-transform: uppercase; color: var(--gold); margin: 22px 0 8px; }
  .pc-title { font-family: 'Cormorant Garamond', serif; font-size: 2.3rem; color: #fff; margin: 0 0 28px; line-height: 1.2; }
  .pc-layout { display: grid; grid-template-columns: minmax(0, 1fr) 360px; gap: 32px; align-items: start; }
  .pc-card { background: var(--dark-2); border: 1px solid var(--white-10); border-radius: 18px; padding: 26px; margin-bottom: 22px; }
  .pc-card h2 { font-family: 'Cormorant Garamond', serif; font-size: 1.5rem; color: #fff; margin: 0 0 4px; }
  .pc-card .pc-sub { font-size: 13px; color: var(--white-60); margin: 0 0 18px; }
  .pc-choices { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 12px; }
  .pc-choice { position: relative; display: block; cursor: pointer; }
  .pc-choice input { position: absolute; opacity: 0; pointer-events: none; }
  .pc-choice span { display: block; border: 1px solid var(--white-10); border-radius: 12px; padding: 14px 16px; background: var(--dark-3); transition: border-color .2s, background .2s; height: 100%; box-sizing: border-box; }
  .pc-choice strong { display: block; color: #fff; font-size: 14.5px; font-weight: 600; }
  .pc-choice small { display: block; color: var(--white-60); font-size: 12px; margin-top: 4px; }
  .pc-choice input:checked + span { border-color: var(--gold); background: var(--gold-dim); }
  .pc-choice input:focus-visible + span { outline: 2px solid var(--gold); outline-offset: 2px; }
  .pc-tag { color: var(--gold); font-weight: 600; }
  .pc-counter { display: inline-flex; align-items: center; gap: 14px; }
  .pc-counter button { width: 38px; height: 38px; border-radius: 50%; border: 1px solid var(--white-30); background: transparent; color: #fff; font-size: 18px; cursor: pointer; }
  .pc-counter button:disabled { opacity: .35; cursor: not-allowed; }
  .pc-counter output { min-width: 28px; text-align: center; font-size: 18px; color: #fff; font-weight: 600; }
  .pc-traveller { display: grid; grid-template-columns: minmax(0, 2fr) minmax(0, 1fr) 90px; gap: 12px; padding: 14px 0; border-top: 1px solid var(--white-10); }
  .pc-traveller:first-of-type { border-top: none; padding-top: 0; }
  .pc-traveller-head { grid-column: 1 / -1; font-size: 11px; font-weight: 700; letter-spacing: 0.14em; text-transform: uppercase; color: var(--gold); }
  .pc-label { display: block; font-size: 10.5px; font-weight: 700; letter-spacing: 0.1em; text-transform: uppercase; color: var(--white-60); margin-bottom: 6px; }
  .pc-input { width: 100%; box-sizing: border-box; background: var(--dark-3); border: 1px solid var(--white-10); border-radius: 10px; padding: 11px 13px; color: #fff; font-family: inherit; font-size: 14px; }
  .pc-input:focus { outline: none; border-color: var(--gold); }
  .pc-grid-2 { display: grid; grid-template-columns: 110px minmax(0, 1fr); gap: 12px; }
  .pc-field { margin-bottom: 14px; }
  .pc-summary { position: sticky; top: 100px; }
  .pc-row { display: flex; justify-content: space-between; gap: 12px; font-size: 13.5px; padding: 7px 0; }
  .pc-row span { color: var(--white-60); }
  .pc-row strong { color: #fff; font-weight: 600; text-align: right; }
  .pc-divider { height: 1px; background: var(--white-10); margin: 8px 0; }
  .pc-pay-now { display: flex; justify-content: space-between; align-items: baseline; margin-top: 10px; }
  .pc-pay-now span { font-size: 12px; text-transform: uppercase; letter-spacing: 0.1em; color: var(--gold); font-weight: 700; }
  .pc-pay-now strong { font-family: 'Cormorant Garamond', serif; font-size: 2rem; color: var(--gold-light); }
  .pc-note { font-size: 12px; color: var(--white-60); line-height: 1.6; margin-top: 10px; }
  .pc-terms { font-size: 13px; line-height: 1.7; color: var(--white-80); background: var(--dark-3); border-radius: 12px; padding: 14px 18px; margin: 0 0 14px; }
  .pc-terms li { margin-bottom: 4px; }
  .pc-check { display: flex; gap: 10px; align-items: flex-start; font-size: 13px; cursor: pointer; }
  .pc-check input { margin-top: 3px; accent-color: var(--gold); }
  .pc-btn { width: 100%; padding: 16px; border: none; border-radius: 100px; margin-top: 18px; background: linear-gradient(90deg, #c9a84c, #e8c96b); color: var(--dark); font-family: inherit; font-size: 13px; font-weight: 800; letter-spacing: 0.1em; text-transform: uppercase; cursor: pointer; }
  .pc-btn:hover { background: linear-gradient(90deg, #e8c96b, #f5e4a8); }
  .pc-error { margin-bottom: 22px; padding: 14px 18px; border-radius: 12px; background: rgba(220,80,80,0.08); border: 1px solid rgba(220,80,80,0.3); color: #f3a3a3; font-size: 13.5px; }
  .pc-error ul { margin: 6px 0 0 18px; padding: 0; }
  @media (max-width: 900px) {
    .pc-layout { grid-template-columns: 1fr; }
    .pc-summary { position: static; }
  }
  @media (max-width: 560px) {
    .pc-wrap { padding: 92px 16px 60px; }
    .pc-title { font-size: 1.8rem; }
    .pc-card { padding: 20px 16px; }
    .pc-traveller { grid-template-columns: 1fr 1fr; }
    .pc-traveller .pc-name { grid-column: 1 / -1; }
  }
</style>
@endpush

@section('content')
@php
  $depData = $departures->map(fn ($d) => [
      'id' => $d->id,
      'label' => $d->start_date->format('D, d M Y').' – '.$d->end_date->format('d M Y'),
      'extra' => (float) $d->extra_price,
      'seatsLeft' => $d->seatsLeft(),
      'balanceDue' => $d->start_date->copy()->subDays($balanceDueDays)->format('d M Y'),
      'cancelBy' => $d->start_date->copy()->subDays($cancelDays)->format('d M Y'),
  ])->values();
  $oldDeparture = (int) old('departure_id', $selectedDeparture);
  if (! $depData->firstWhere('id', $oldDeparture)) { $oldDeparture = $depData->first()['id']; }
  $oldOption = old('option', $selectedOption);
  if (! collect($options)->firstWhere('label', $oldOption)) { $oldOption = $options[0]['label']; }
  $oldTravellers = old('travellers', []);
  $count = max(1, count($oldTravellers) ?: $travellerCount);
  $bookingAmount = (float) $package->booking_amount;
@endphp

<div class="pc-wrap">
  <a href="{{ route('package.details', $package->slug) }}" class="pc-back"><i class="fa-solid fa-arrow-left"></i> Back to {{ $package->title }}</a>
  <p class="pc-eyebrow">Book Your Trip</p>
  <h1 class="pc-title">{{ $package->title }}</h1>

  @if(session('booking_error') || $errors->any())
    <div class="pc-error" role="alert">
      @if(session('booking_error'))<div>{{ session('booking_error') }}</div>@endif
      @if($errors->any())
        <div>Please check the following:</div>
        <ul>@foreach(collect($errors->all())->unique() as $error)<li>{{ $error }}</li>@endforeach</ul>
      @endif
    </div>
  @endif

  <form method="POST" action="{{ route('package.book', $package->slug) }}" id="pcForm" novalidate>
    @csrf
    <div class="pc-layout">
      <div>
        {{-- 1. Date --}}
        <div class="pc-card">
          <h2>1. Choose your travel date</h2>
          <p class="pc-sub">{{ $package->duration_nights ? $package->duration_nights.' nights / '.($package->duration_nights + 1).' days' : '' }}</p>
          <div class="pc-choices">
            @foreach($depData as $d)
              <label class="pc-choice">
                <input type="radio" name="departure_id" value="{{ $d['id'] }}" @checked($d['id'] === $oldDeparture)>
                <span>
                  <strong>{{ $d['label'] }}</strong>
                  <small>
                    @if($d['seatsLeft'] !== null)<span class="pc-tag">{{ $d['seatsLeft'] }} seat{{ $d['seatsLeft'] === 1 ? '' : 's' }} left</span>@else Seats available @endif
                    @if($d['extra'] > 0) · +&#8377;{{ number_format($d['extra'], 0) }} / person (peak date)@endif
                  </small>
                </span>
              </label>
            @endforeach
          </div>
        </div>

        {{-- 2. Sharing --}}
        <div class="pc-card" @if(count($options) === 1) style="display:none" @endif>
          <h2>2. Room sharing</h2>
          <p class="pc-sub">Price per person.</p>
          <div class="pc-choices">
            @foreach($options as $opt)
              <label class="pc-choice">
                <input type="radio" name="option" value="{{ $opt['label'] }}" @checked($opt['label'] === $oldOption)>
                <span><strong>{{ $opt['label'] }}</strong><small>&#8377;{{ number_format($opt['price'], 0) }} per person</small></span>
              </label>
            @endforeach
          </div>
        </div>

        {{-- 3. Travellers --}}
        <div class="pc-card">
          <h2>{{ count($options) === 1 ? '2' : '3' }}. Travellers</h2>
          <p class="pc-sub">Enter names exactly as on a government photo ID. Infants up to {{ \App\Services\PackageBookingService::FREE_INFANT_MAX_AGE }} years travel free (enter 0 for babies under 1).</p>
          <div class="pc-counter" style="margin-bottom:18px;">
            <button type="button" id="pcMinus" aria-label="Remove a traveller">−</button>
            <output id="pcCount" aria-live="polite">{{ $count }}</output>
            <button type="button" id="pcPlus" aria-label="Add a traveller">+</button>
            <span style="font-size:13px;color:var(--white-60);" id="pcSeatsNote"></span>
          </div>
          <div id="pcTravellers">
            @for($i = 0; $i < $count; $i++)
              <div class="pc-traveller" data-index="{{ $i }}">
                <div class="pc-traveller-head">Traveller {{ $i + 1 }}{{ $i === 0 ? ' (lead)' : '' }}</div>
                <div class="pc-name">
                  <label class="pc-label" for="t{{ $i }}name">Full name</label>
                  <input class="pc-input" id="t{{ $i }}name" name="travellers[{{ $i }}][full_name]" value="{{ $oldTravellers[$i]['full_name'] ?? ($i === 0 ? $user->name : '') }}" maxlength="100" required autocomplete="{{ $i === 0 ? 'name' : 'off' }}">
                </div>
                <div>
                  <label class="pc-label" for="t{{ $i }}gender">Gender</label>
                  <select class="pc-input" id="t{{ $i }}gender" name="travellers[{{ $i }}][gender]" required>
                    <option value="">Select</option>
                    @foreach(['male' => 'Male', 'female' => 'Female', 'other' => 'Other'] as $val => $lbl)
                      <option value="{{ $val }}" @selected(($oldTravellers[$i]['gender'] ?? '') === $val)>{{ $lbl }}</option>
                    @endforeach
                  </select>
                </div>
                <div>
                  <label class="pc-label" for="t{{ $i }}age">Age</label>
                  <input class="pc-input" id="t{{ $i }}age" type="number" min="1" max="99" name="travellers[{{ $i }}][age]" value="{{ $oldTravellers[$i]['age'] ?? '' }}" required>
                </div>
              </div>
            @endfor
          </div>
        </div>

        {{-- 4. Contact --}}
        <div class="pc-card">
          <h2>{{ count($options) === 1 ? '3' : '4' }}. Contact details</h2>
          <p class="pc-sub">We send your confirmation and payment reminders here.</p>
          <div class="pc-field">
            <label class="pc-label" for="pcEmail">Email</label>
            <input class="pc-input" id="pcEmail" type="email" name="email" value="{{ old('email', $user->email) }}" required autocomplete="email">
          </div>
          <div class="pc-field pc-grid-2">
            <div>
              <label class="pc-label" for="pcCode">Code</label>
              <input class="pc-input" id="pcCode" name="phone_code" value="{{ old('phone_code', '91') }}" inputmode="numeric" maxlength="4" required>
            </div>
            <div>
              <label class="pc-label" for="pcPhone">Mobile number</label>
              <input class="pc-input" id="pcPhone" name="phone" value="{{ old('phone', preg_replace('/\D/', '', (string) ($user->phone ?? ''))) }}" inputmode="numeric" maxlength="15" required autocomplete="tel-national">
            </div>
          </div>
          <div class="pc-field" style="margin-bottom:0;">
            <label class="pc-label" for="pcRequests">Special requests (optional)</label>
            <textarea class="pc-input" id="pcRequests" name="special_requests" rows="2" maxlength="500" placeholder="Dietary needs, medical conditions, celebrating something…">{{ old('special_requests') }}</textarea>
          </div>
        </div>

        {{-- 5. Payment plan --}}
        <div class="pc-card">
          <h2>{{ count($options) === 1 ? '4' : '5' }}. How would you like to pay?</h2>
          <p class="pc-sub">Taxes are included in the amounts shown.</p>
          <div class="pc-choices">
            @if($bookingAmount > 0)
              <label class="pc-choice">
                <input type="radio" name="plan" value="part" @checked(old('plan', 'part') === 'part')>
                <span><strong>Pay booking amount now</strong><small>&#8377;{{ number_format($bookingAmount, 0) }} per person now, the balance later</small></span>
              </label>
            @endif
            <label class="pc-choice">
              <input type="radio" name="plan" value="full" @checked(old('plan', $bookingAmount > 0 ? 'part' : 'full') === 'full')>
              <span><strong>Pay in full</strong><small>Nothing more to pay later</small></span>
            </label>
          </div>
        </div>
      </div>

      {{-- Summary --}}
      <aside class="pc-summary">
        <div class="pc-card">
          <h2 style="margin-bottom:14px;">Price summary</h2>
          <div class="pc-row"><span>Travel date</span><strong id="sDate"></strong></div>
          <div class="pc-row"><span id="sPerLabel">Per person</span><strong id="sPer"></strong></div>
          <div class="pc-row"><span>Travellers</span><strong id="sPax"></strong></div>
          <div class="pc-divider"></div>
          <div class="pc-row"><span>Trip total <small style="color:var(--white-30);">(incl. taxes)</small></span><strong id="sTotal"></strong></div>
          <div class="pc-pay-now"><span>Pay now</span><strong id="sNow"></strong></div>
          <p class="pc-note" id="sBalanceNote"></p>

          <ul class="pc-terms" style="margin-top:16px;">
            <li>The balance must be paid by <strong id="tDue"></strong> ({{ $balanceDueDays }} days before departure).</li>
            <li>If the balance is not paid before departure, the booking is cancelled and the booking amount is <strong>not refundable</strong>.</li>
            <li>You can cancel online until <strong id="tCancel"></strong> ({{ $cancelDays }} days before departure). Our team confirms any refund under our <a href="{{ route('cancellation') }}#online-group-packages" target="_blank" style="color:var(--gold);">cancellation policy</a>.</li>
          </ul>
          <label class="pc-check">
            <input type="checkbox" name="terms" value="1" @checked(old('terms')) required>
            <span>I agree to these terms and the <a href="{{ route('terms') }}" target="_blank" style="color:var(--gold);">terms &amp; conditions</a>.</span>
          </label>
          <button type="submit" class="pc-btn" id="pcSubmit">Continue to payment</button>
          <p class="pc-note" style="text-align:center;">Seats are held for {{ \App\Services\PackageBookingService::SEAT_HOLD_MINUTES }} minutes while you pay.</p>
        </div>
      </aside>
    </div>
  </form>
</div>
@endsection

@push('scripts')
<script>
(function () {
  var departures = @json($depData);
  var options = @json($options);
  var bookingAmount = @json($bookingAmount);
  var formula = @json($formula);
  var maxTravellers = @json($maxTravellers);

  var form = document.getElementById('pcForm');
  var list = document.getElementById('pcTravellers');
  var countEl = document.getElementById('pcCount');
  var minus = document.getElementById('pcMinus');
  var plus = document.getElementById('pcPlus');
  var seatsNote = document.getElementById('pcSeatsNote');

  function inr(v) { return '₹' + v.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 }); }
  function round2(v) { return Math.round(v * 100) / 100; }
  // Same steps as PackagePricingService::charge().
  function charge(base) {
    base = round2(Math.max(0, base));
    var gst = round2(base * formula.gstRate);
    var total = base > 0 ? round2((base + gst) / (1 - formula.razorpayRate)) : 0;
    return { base: base, gst: gst, gateway: round2(total - base - gst), total: total };
  }
  function checked(name) { var el = form.querySelector('input[name="' + name + '"]:checked'); return el ? el.value : null; }
  function departure() { var id = parseInt(checked('departure_id'), 10); return departures.find(function (d) { return d.id === id; }) || departures[0]; }
  function option() { var l = checked('option'); return options.find(function (o) { return o.label === l; }) || options[0]; }
  function count() { return list.querySelectorAll('.pc-traveller').length; }
  function maxFor(d) { return d.seatsLeft === null ? maxTravellers : Math.min(maxTravellers, d.seatsLeft); }

  function travellerRow(i) {
    var row = document.createElement('div');
    row.className = 'pc-traveller';
    row.dataset.index = i;
    row.innerHTML =
      '<div class="pc-traveller-head">Traveller ' + (i + 1) + '</div>' +
      '<div class="pc-name"><label class="pc-label" for="t' + i + 'name">Full name</label>' +
      '<input class="pc-input" id="t' + i + 'name" name="travellers[' + i + '][full_name]" maxlength="100" required autocomplete="off"></div>' +
      '<div><label class="pc-label" for="t' + i + 'gender">Gender</label>' +
      '<select class="pc-input" id="t' + i + 'gender" name="travellers[' + i + '][gender]" required><option value="">Select</option><option value="male">Male</option><option value="female">Female</option><option value="other">Other</option></select></div>' +
      '<div><label class="pc-label" for="t' + i + 'age">Age</label>' +
      '<input class="pc-input" id="t' + i + 'age" type="number" min="1" max="99" name="travellers[' + i + '][age]" required></div>';
    return row;
  }

  // Infants up to this age are free and need no seat (same rule as the server).
  var freeInfantMaxAge = @json(\App\Services\PackageBookingService::FREE_INFANT_MAX_AGE);
  function infants() {
    return [].filter.call(list.querySelectorAll('input[name$="[age]"]'), function (i) {
      return i.value !== '' && parseInt(i.value, 10) <= freeInfantMaxAge;
    }).length;
  }

  function setCount(n) {
    // Rows can include free infants, so cap rows at the overall limit;
    // seats are checked against paying travellers in update().
    n = Math.max(1, Math.min(maxTravellers, n));
    while (count() < n) list.appendChild(travellerRow(count()));
    while (count() > n) list.removeChild(list.lastElementChild);
    update();
  }

  function update() {
    var d = departure(), o = option(), rows = count(), free = infants(), n = Math.max(0, rows - free);
    countEl.textContent = rows;
    minus.disabled = rows <= 1;
    plus.disabled = rows >= maxTravellers;
    seatsNote.textContent = d.seatsLeft !== null && n > d.seatsLeft
      ? 'Only ' + d.seatsLeft + ' seat' + (d.seatsLeft === 1 ? '' : 's') + ' left on this date — please remove ' + (n - d.seatsLeft)
      : (d.seatsLeft !== null && n === d.seatsLeft ? 'That takes the last seat' + (d.seatsLeft === 1 ? '' : 's') + ' on this date' : '');

    var perPerson = round2(o.price + d.extra);
    var subtotal = round2(perPerson * n);
    var deposit = round2(bookingAmount * n);
    var part = checked('plan') === 'part' && deposit > 0 && deposit < subtotal;
    var now = charge(part ? deposit : subtotal);
    var balance = charge(subtotal - (part ? deposit : subtotal));

    document.getElementById('sDate').textContent = d.label;
    document.getElementById('sPerLabel').textContent = options.length > 1 ? 'Per person (' + o.label + ')' : 'Per person';
    document.getElementById('sPer').textContent = inr(perPerson);
    document.getElementById('sPax').textContent = n + (free ? ' + ' + free + ' infant' + (free === 1 ? '' : 's') + ' (free)' : '');
    document.getElementById('sTotal').textContent = inr(round2(subtotal * (1 + formula.gstRate)));
    document.getElementById('sNow').textContent = inr(now.total);
    document.getElementById('sBalanceNote').textContent = part
      ? 'Balance of ' + inr(balance.total) + ' due by ' + d.balanceDue + '.'
      : 'Nothing more to pay later.';
    document.getElementById('tDue').textContent = d.balanceDue;
    document.getElementById('tCancel').textContent = d.cancelBy;
  }

  minus.addEventListener('click', function () { setCount(count() - 1); });
  plus.addEventListener('click', function () { setCount(count() + 1); });
  form.addEventListener('change', function (e) {
    if (e.target.name === 'departure_id') { setCount(count()); return; }
    if (['option', 'plan'].indexOf(e.target.name) !== -1) update();
  });
  // Ages decide who is a free infant, so re-price as they are typed.
  form.addEventListener('input', function (e) { if (/\[age\]$/.test(e.target.name || '')) update(); });
  form.addEventListener('submit', function () {
    var btn = document.getElementById('pcSubmit');
    btn.disabled = true;
    btn.textContent = 'Please wait…';
  });
  // Coming back with the browser's Back button must not leave it disabled.
  window.addEventListener('pageshow', function () {
    var btn = document.getElementById('pcSubmit');
    btn.disabled = false;
    btn.textContent = 'Continue to payment';
  });

  setCount(count());
})();
</script>
@endpush
