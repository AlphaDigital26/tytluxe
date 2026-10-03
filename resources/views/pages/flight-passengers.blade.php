@extends('layouts.frontend')

@section('meta_title', 'Passenger Details | TYT Luxe')

@push('styles')
@include('partials.flight-booking-styles')
<style>
  .flp-title { font-family: 'Cormorant Garamond', serif; font-size: 1.9rem; color: #fff; margin: 0 0 16px; }

  /* Compact flight bar */
  .flp-flight { display: flex; align-items: center; gap: 14px; padding: 14px 18px; margin-bottom: 12px; background: var(--dark-2); border: 1px solid rgba(201,168,76,0.25); border-radius: 14px; font-family: 'Jost', sans-serif; }
  .flp-flight img { width: 36px; height: 36px; border-radius: 8px; background: #fff; object-fit: contain; flex-shrink: 0; }
  .flp-flight-main { flex: 1; min-width: 0; }
  .flp-flight-route { font-size: 14px; font-weight: 600; color: #fff; }
  .flp-flight-route span { font-weight: 400; color: var(--white-60); font-size: 12px; margin-left: 8px; }
  .flp-flight-sub { font-size: 12px; color: var(--white-60); margin-top: 3px; display: flex; flex-wrap: wrap; gap: 4px 16px; }
  .flp-trip-badge { flex-shrink: 0; padding: 4px 12px; border-radius: 100px; background: var(--gold-dim); color: var(--gold-light); font-size: 10.5px; font-weight: 700; letter-spacing: 0.06em; text-transform: uppercase; }
  .flp-flights { margin-bottom: 26px; }

  /* Traveller cards */
  .flp-pax { border: 1px solid rgba(255,255,255,0.08); border-radius: 16px; margin-bottom: 18px; overflow: visible; }
  .flp-pax-head { width: 100%; display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 16px 20px; background: rgba(255,255,255,0.035); border: none; border-radius: 16px 16px 0 0; color: #fff; font-family: 'Jost', sans-serif; font-size: 13px; font-weight: 700; letter-spacing: 0.06em; text-transform: uppercase; cursor: pointer; text-align: left; }
  .flp-pax-head small { font-weight: 400; color: var(--white-60); letter-spacing: 0; text-transform: none; margin-left: 6px; }
  .flp-pax-head .flp-chev { width: 28px; height: 28px; border-radius: 50%; background: rgba(255,255,255,0.06); display: flex; align-items: center; justify-content: center; color: var(--gold); transition: transform 0.15s; }
  .flp-pax-head[aria-expanded="false"] { border-radius: 16px; }
  .flp-pax-head[aria-expanded="false"] .flp-chev { transform: rotate(180deg); }
  .flp-pax-body { padding: 20px; }
  .flp-pax-body[hidden] { display: none; }
  .flp-lookup { position: relative; max-width: 320px; margin-bottom: 20px; }
  .flp-lookup input { padding-left: 40px !important; }
  .flp-lookup-icon { position: absolute; left: 15px; top: 37px; color: var(--gold); font-size: 13px; pointer-events: none; }
  .flp-lookup-list { position: absolute; z-index: 20; left: 0; right: 0; top: calc(100% + 4px); max-height: 220px; overflow-y: auto; background: #1b1b1b; border: 1px solid rgba(201,168,76,0.3); border-radius: 12px; box-shadow: 0 16px 40px rgba(0,0,0,0.5); padding: 6px; }
  .flp-lookup-list[hidden] { display: none; }
  .flp-lookup-item { display: block; width: 100%; text-align: left; padding: 10px 12px; border: none; border-radius: 8px; background: transparent; color: var(--white-80); font-family: 'Jost', sans-serif; font-size: 13px; cursor: pointer; }
  .flp-lookup-item small { display: block; color: var(--white-30); font-size: 11px; }
  .flp-lookup-item:hover, .flp-lookup-item:focus { background: rgba(201,168,76,0.12); color: #fff; outline: none; }
  .flp-lookup-empty { padding: 10px 12px; font-family: 'Jost', sans-serif; font-size: 12px; color: var(--white-30); }
  .flp-label-row { display: flex; justify-content: space-between; align-items: baseline; }
  .flp-count { font-family: 'Jost', sans-serif; font-size: 10.5px; color: var(--white-30); }
  .flp-ff { margin-top: 4px; padding-top: 16px; border-top: 1px dashed rgba(255,255,255,0.1); }
  .flp-ff-toggle { background: none; border: none; padding: 0; margin-bottom: 14px; color: var(--white-80); font-family: 'Jost', sans-serif; font-size: 11px; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; }
  .flp-ff-toggle::after { content: '▾'; color: var(--gold); font-size: 10px; transition: transform 0.15s; }
  .flp-ff-toggle[aria-expanded="true"]::after { transform: rotate(180deg); }
  .flp-ff-grid { display: grid; grid-template-columns: 110px 1fr; gap: 14px; max-width: 460px; }
  .flp-ff-grid[hidden] { display: none; }
  .flp-ff-grid input[readonly] { color: var(--white-60); }
  .flp-check { display: inline-flex; align-items: center; gap: 10px; margin-top: 6px; font-family: 'Jost', sans-serif; font-size: 12.5px; color: var(--white-80); cursor: pointer; }
  .flp-check input { width: 16px; height: 16px; accent-color: var(--gold); }
  .flp-check small { color: var(--white-30); }

  .flp-phone { display: flex; }
  .flp-hint { font-family: 'Jost', sans-serif; font-size: 11px; color: var(--white-30); }
  .flp-phone span { display: flex; align-items: center; padding: 0 14px; border: 1px solid rgba(255,255,255,0.12); border-right: none; border-radius: 10px 0 0 10px; background: rgba(255,255,255,0.03); color: var(--white-60); font-family: 'Jost', sans-serif; font-size: 13.5px; }
  .flp-phone input { border-radius: 0 10px 10px 0 !important; }
  .flp-phone input.flp-dial { flex: 0 0 74px; width: 74px; text-align: center; border-right: none; border-radius: 10px 0 0 10px !important; }
  .flp-extras { display: flex; flex-wrap: wrap; gap: 12px; margin-bottom: 26px; }
  .flp-extra-btn { padding: 11px 20px; border-radius: 100px; border: 1px solid rgba(201,168,76,0.45); background: transparent; color: var(--gold-light); font-family: 'Jost', sans-serif; font-size: 12px; font-weight: 600; letter-spacing: 0.04em; cursor: pointer; }
  .flp-extra-btn[aria-expanded="true"] { background: rgba(201,168,76,0.12); }

  /* Add-ons */
  .flp-addon { border: 1px solid rgba(255,255,255,0.08); border-radius: 16px; padding: 20px; margin-bottom: 18px; font-family: 'Jost', sans-serif; }
  .flp-addon h3 { display: flex; align-items: center; gap: 10px; margin: 0 0 16px; font-size: 13px; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; color: #fff; }
  .flp-addon h3 span { color: var(--gold); font-size: 15px; }
  .flp-tabs { display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 16px; }
  .flp-tab { padding: 8px 16px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.12); background: transparent; color: var(--white-60); font-family: 'Jost', sans-serif; font-size: 12px; font-weight: 700; letter-spacing: 0.04em; cursor: pointer; text-align: center; line-height: 1.4; }
  .flp-tab small { display: block; font-weight: 500; font-size: 10.5px; color: var(--white-30); }
  .flp-tab.active { border-color: var(--gold); background: rgba(201,168,76,0.1); color: var(--gold-light); }
  .flp-note-box { margin-top: 14px; padding: 12px 14px; border-radius: 10px; background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.07); font-size: 11.5px; line-height: 1.6; color: var(--white-60); }
  .flp-note-box.warn { border-color: rgba(201,168,76,0.4); color: var(--gold-light); background: rgba(201,168,76,0.06); }
  .flp-total { margin-top: 14px; font-size: 13px; color: var(--white-80); }
  .flp-total b { color: var(--gold-light); }

  .flp-legend { display: flex; flex-wrap: wrap; align-items: center; gap: 8px 16px; margin-bottom: 14px; font-size: 11.5px; color: var(--white-60); }
  .flp-legend i { display: inline-block; width: 14px; height: 14px; border-radius: 4px; vertical-align: -2px; margin-right: 6px; }
  .flp-seatmap-wrap { overflow-x: auto; padding: 16px 16px 16px 0; background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.06); border-radius: 14px; display: flex; }
  .flp-nose { flex-shrink: 0; width: 64px; margin-right: 6px; border-radius: 0 60px 60px 0 / 0 50% 50% 0; background: linear-gradient(90deg, rgba(255,255,255,0.02), rgba(201,168,76,0.08)); border: 1px solid rgba(201,168,76,0.18); border-left: none; }
  .flp-seatgrid { display: grid; gap: 5px; align-items: center; }
  .flp-seat-letter { font-size: 10.5px; font-weight: 700; color: var(--white-30); text-align: center; }
  .flp-seat-rownum { font-size: 10px; color: var(--white-30); text-align: center; }
  .flp-seat { position: relative; width: 32px; height: 30px; border-radius: 7px 7px 5px 5px; border: 1px solid transparent; font-family: 'Jost', sans-serif; font-size: 9.5px; font-weight: 700; color: rgba(0,0,0,0.75); cursor: pointer; padding: 0; transition: transform 0.1s, box-shadow 0.1s; }
  .flp-seat:hover:not(:disabled) { transform: translateY(-1px); box-shadow: 0 4px 10px rgba(0,0,0,0.4); }
  .flp-seat.legroom::before { content: ''; position: absolute; left: 4px; right: 4px; top: -4px; height: 2px; border-radius: 2px; background: #7fd6a0; }
  .flp-seat:disabled { background: #2a2a2a !important; color: #555; cursor: not-allowed; }
  .flp-seat.selected { background: var(--gold) !important; color: var(--dark); border-color: #fff; box-shadow: 0 0 0 2px rgba(201,168,76,0.4); }
  .flp-seat.taken-by-other { outline: 2px solid rgba(255,255,255,0.5); }
  .flp-band-0 { background: #9ec7a8; }
  .flp-band-1 { background: #8fb8e0; }
  .flp-band-2 { background: #b8a6e6; }
  .flp-band-3 { background: #e8c28f; }
  .flp-band-4 { background: #e39a9a; }

  .flp-paxchips { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 14px; padding: 12px; border-radius: 12px; background: rgba(255,255,255,0.025); }
  .flp-paxchip { min-width: 120px; padding: 8px 12px; border-radius: 10px; border: 1px solid rgba(255,255,255,0.12); background: transparent; text-align: left; font-family: 'Jost', sans-serif; cursor: pointer; }
  .flp-paxchip b { display: block; font-size: 11px; letter-spacing: 0.06em; color: #fff; }
  .flp-paxchip span { display: block; font-size: 11px; color: var(--white-30); margin-top: 2px; }
  .flp-paxchip.active { border-color: var(--gold); background: rgba(201,168,76,0.1); }
  .flp-paxchip.filled span { color: var(--gold-light); }
  div.flp-paxchip { cursor: default; }

  .flp-cards { display: grid; grid-template-columns: repeat(auto-fill, minmax(210px, 1fr)); gap: 12px; max-height: 300px; overflow-y: auto; padding: 2px; }
  .flp-card { display: flex; align-items: center; gap: 12px; padding: 12px 14px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.1); background: rgba(255,255,255,0.03); }
  .flp-card.on { border-color: rgba(201,168,76,0.55); background: rgba(201,168,76,0.07); }
  .flp-card-ic { font-size: 20px; flex-shrink: 0; }
  .flp-card-main { flex: 1; min-width: 0; }
  .flp-card-name { font-size: 12.5px; color: #fff; line-height: 1.35; }
  .flp-card-name small { color: var(--white-30); font-size: 10.5px; }
  .flp-card-price { font-size: 13px; font-weight: 700; color: var(--gold-light); margin-top: 3px; }
  .flp-qty { display: flex; align-items: center; gap: 8px; flex-shrink: 0; }
  .flp-qty button { width: 26px; height: 26px; border-radius: 50%; border: 1px solid var(--gold); background: transparent; color: var(--gold-light); font-size: 15px; line-height: 1; cursor: pointer; padding: 0; }
  .flp-qty button:disabled { border-color: rgba(255,255,255,0.15); color: rgba(255,255,255,0.2); cursor: not-allowed; }
  .flp-qty output { min-width: 14px; text-align: center; font-size: 13px; color: #fff; }

  /* Add-ons + actions span the full page width below the two columns. */
  .flr-wrap.flp-wrap { padding-bottom: 0; }
  .flp-full { max-width: 1100px; margin: 0 auto; padding: 0 24px 100px; box-sizing: content-box; } /* same box as .flr-wrap */
  @media (max-width: 900px) { .flr-wrap.flp-wrap { padding-bottom: 28px; } }
  .flp-actions { display: flex; justify-content: space-between; gap: 14px; margin-top: 6px; }
  .flp-actions .flr-submit { width: auto; padding: 15px 32px; margin: 0; }
  .flp-actions a.flr-submit { display: inline-flex; align-items: center; }
  .flp-hidden-note { font-family: 'Jost', sans-serif; font-size: 11px; color: var(--white-30); margin-top: 8px; text-align: center; }
  @media (max-width: 560px) { .flp-flight { flex-wrap: wrap; } .flp-ff-grid { grid-template-columns: 1fr; } }
</style>
@endpush

@section('content')
@php
  $fmtDuration = fn ($mins) => $mins ? intdiv((int) $mins, 60).'h '.((int) $mins % 60).'m' : '';
  $tripBadge = match ($context['tripType']) { 'return' => 'Return', 'multi' => 'Multi-City', default => 'One Way' };
  $totalPax = $context['adults'] + $context['children'] + $context['infants'];
  $firstNameMax = (int) ($conditions['anlm']['fN'] ?? 100);
  $lastNameMax = (int) ($conditions['anlm']['lN'] ?? 100);
  $passportRequired = (bool) ($conditions['pcs']['pm'] ?? $conditions['pm'] ?? false);
  $gstMandatory = (bool) ($conditions['gst']['igm'] ?? false);
  // Review conditions (confirmed live): dc.ida/idm = student / senior ID
  // applicable / mandatory (a STUDENT fare returned both true); iecr =
  // emergency contact required; ipa = PAN applicable.
  $docApplicable = (bool) ($conditions['dc']['ida'] ?? false);
  $docMandatory = (bool) ($conditions['dc']['idm'] ?? false);
  $docLabel = match ($context['fareType'] ?? 'REGULAR') {
      'STUDENT' => 'Student ID Number',
      'SENIOR_CITIZEN' => 'Senior Citizen ID Number',
      default => 'ID Document Number',
  };
  $emergencyRequired = (bool) ($conditions['iecr'] ?? false);
  $panApplicable = (bool) ($conditions['gst']['ipa'] ?? $conditions['ipa'] ?? false);
  $countries = $passportRequired ? require resource_path('data/countries.php') : [];
  $primaryAirlineCode = $tripInfos[0]['sI'][0]['fD']['aI']['code'] ?? null;
  $primaryAirlineName = $tripInfos[0]['sI'][0]['fD']['aI']['name'] ?? $primaryAirlineCode;
  $oldTravellers = old('travellers', []);

  $travellers = [];
  $typeCounters = ['ADULT' => 0, 'CHILD' => 0, 'INFANT' => 0];
  for ($i = 0; $i < $totalPax; $i++) {
      $type = $i < $context['adults'] ? 'ADULT' : ($i < $context['adults'] + $context['children'] ? 'CHILD' : 'INFANT');
      $n = ++$typeCounters[$type];
      $travellers[] = [
          'idx' => $i,
          'type' => $type,
          'label' => ucfirst(strtolower($type)).' '.$n,
          'chip' => $type.'-'.$n,
          'age' => ['ADULT' => '12+ yrs', 'CHILD' => '2–12 yrs', 'INFANT' => 'Under 2 yrs'][$type],
          'eligible' => $type !== 'INFANT',
          'dobRequired' => $type === 'INFANT' || (bool) ($conditions['dob'][['ADULT' => 'adobr', 'CHILD' => 'cdobr', 'INFANT' => 'idobr'][$type]] ?? false),
      ];
  }

  // Seat maps per segment, reduced to what the picker needs.
  $seatSegments = [];
  $seatNotes = [];
  $seatMandatoryAny = false;
  foreach ($addonTrips as $trip) {
      $seatMandatoryAny = $seatMandatoryAny || $trip['seatMandatory'];
      foreach ($trip['segments'] as $seg) {
          $map = $seatMaps[$seg['id']] ?? null;
          if (! $map || empty($map['sInfo'])) {
              // Seat Map doc: `nt` explains why a leg has no seat layout.
              if ($map && ! empty($map['nt'])) {
                  $seatNotes[] = ['label' => $seg['label'], 'note' => trim(str_replace('__nls__', ' ', (string) $map['nt']))];
              }
              continue;
          }
          $seatSegments[] = [
              'id' => $seg['id'],
              'label' => $seg['label'],
              'mandatory' => $trip['seatMandatory'],
              'rows' => (int) ($map['sData']['row'] ?? 0),
              'cols' => (int) ($map['sData']['column'] ?? 0),
              'seats' => collect($map['sInfo'])->map(fn ($s) => [
                  'code' => $s['code'],
                  'no' => $s['seatNo'] ?? $s['code'],
                  'r' => (int) ($s['seatPosition']['row'] ?? 0),
                  'c' => (int) ($s['seatPosition']['column'] ?? 0),
                  'amt' => round((float) ($s['amount'] ?? 0), 2),
                  'booked' => (bool) ($s['isBooked'] ?? false),
                  'legroom' => (bool) ($s['isLegroom'] ?? false),
                  'exit' => (bool) ($s['isExitRow'] ?? false),
                  'window' => (bool) ($s['isWindow'] ?? false),
                  'aisle' => (bool) ($s['isAisle'] ?? false),
              ])->values()->all(),
          ];
      }
  }
  $seatMapMissing = $seatMandatoryAny && count($seatSegments) === 0;

  $optionGroups = function (string $type) use ($addonTrips) {
      $groups = [];
      foreach ($addonTrips as $trip) {
          foreach ($trip['segments'] as $seg) {
              if (! empty($seg['options'][$type])) {
                  $groups[] = [
                      'id' => $seg['id'],
                      'label' => $type === 'BAGGAGE' ? $trip['label'] : $seg['label'],
                      'options' => collect($seg['options'][$type])->map(fn ($o, $code) => ['code' => (string) $code] + $o)->values()->all(),
                  ];
              }
          }
      }
      return $groups;
  };
  $baggageGroups = $optionGroups('BAGGAGE');
  $mealGroups = $optionGroups('MEAL');
  $ffGroups = $optionGroups('FASTFORWARD');
  $hasAddons = $seatSegments || $baggageGroups || $mealGroups || $ffGroups || $seatMapMissing || $seatNotes;

  $savedTravellerData = $savedTravellers->map(fn ($t) => [
      'first' => $t->first_name,
      'last' => $t->last_name,
      'gender' => $t->gender,
      'dob' => $t->dob?->format('Y-m-d'),
      'passport' => $t->passport_number,
      'passportExpiry' => $t->passport_expiry?->format('Y-m-d'),
      'ffAirline' => $t->frequent_flyer_airline,
      'ffNumber' => $t->frequent_flyer_number,
  ])->values();

  $contactPhone = old('contact_phone', substr(preg_replace('/\D/', '', (string) auth()->user()->phone), -10));
  $convenienceFee = max(0, $breakdown['customer_price'] - $breakdown['tripjack_total_price']);
  $canHold = (bool) ($conditions['isBA'] ?? false);
  $pricingJs = ['tf' => $breakdown['tripjack_total_price'], 'taxes' => $breakdown['airline_taxes']] + \App\Services\FlightPricingService::formula();
@endphp

@include('partials.flight-booking-steps', ['current' => 2, 'stepOneUrl' => route('flights.review.show')])
@include('partials.flight-session-timer', ['expiresAt' => $sessionExpiresAt ?? null, 'resultsUrl' => $resultsUrl ?? null])

<div class="flr-wrap flp-wrap">
  <div>
    <h1 class="flp-title">Passenger Details</h1>

    @if($errors->any())
    <div class="flr-error" role="alert">
      @foreach($errors->all() as $error)
        <div>⚠️ {{ $error }}</div>
      @endforeach
    </div>
    @endif

    <div class="flp-flights">
      @foreach($tripInfos as $trip)
        @php
          $segs = $trip['sI'] ?? [];
          $first = $segs[0] ?? null;
          $last = $segs ? end($segs) : null;
          $mins = collect($segs)->sum(fn ($s) => (int) ($s['duration'] ?? 0) + (int) ($s['cT'] ?? 0));
          $stopsLabel = count($segs) > 1 ? (count($segs) - 1).' stop'.(count($segs) > 2 ? 's' : '') : 'Non stop';
          $cabin = $trip['totalPriceList'][0]['fd']['ADULT']['cc'] ?? $context['cabinClass'];
        @endphp
        @if($first)
        <div class="flp-flight">
          <img src="https://images.kiwi.com/airlines/64/{{ $first['fD']['aI']['code'] ?? '' }}.png" alt="" onerror="this.style.visibility='hidden'">
          <div class="flp-flight-main">
            <div class="flp-flight-route">{{ $first['da']['code'] }}, {{ $first['da']['city'] ?? '' }} &rarr; {{ $last['aa']['code'] }}, {{ $last['aa']['city'] ?? '' }}<span>{{ ucwords(strtolower($cabin)) }}</span></div>
            <div class="flp-flight-sub">
              <span>&#9992; {{ $first['fD']['aI']['name'] ?? '' }}, {{ collect($segs)->map(fn ($s) => ($s['fD']['aI']['code'] ?? '').' - '.($s['fD']['fN'] ?? ''))->implode(', ') }}</span>
              <span>&#9719; {{ \Carbon\Carbon::parse($first['dt'])->format('g:i A') }} &rarr; {{ \Carbon\Carbon::parse($last['at'])->format('g:i A') }}</span>
              <span>{{ \Carbon\Carbon::parse($first['dt'])->format("D, j M 'y") }} | {{ $stopsLabel }} | {{ $fmtDuration($mins) }}</span>
            </div>
          </div>
          @if($loop->first)<span class="flp-trip-badge">{{ $tripBadge }}</span>@endif
        </div>
        @endif
      @endforeach
    </div>

    <form method="POST" action="{{ route('flights.book') }}" id="flrForm" novalidate>
      @csrf
      <input type="hidden" name="intent" id="flrIntent" value="review">
      <input type="hidden" name="review_booking_id" value="{{ $response['bookingId'] }}">
      <div id="flpAddonInputs"></div>

      <div class="flr-section">
        <h2>Travellers</h2>
        @foreach($travellers as $t)
          @php $i = $t['idx']; $old = $oldTravellers[$i] ?? []; @endphp
          <div class="flp-pax" data-pax="{{ $i }}" data-type="{{ $t['type'] }}">
            <button type="button" class="flp-pax-head" aria-expanded="true" aria-controls="flpPaxBody{{ $i }}">
              <span>{{ $t['label'] }}<small>({{ $t['age'] }})</small></span>
              <span class="flp-chev">&#8963;</span>
            </button>
            <div class="flp-pax-body" id="flpPaxBody{{ $i }}">
              @if($savedTravellerData->isNotEmpty())
              <div class="flr-field flp-lookup">
                <label for="flpLookup{{ $i }}">Traveller List</label>
                <span class="flp-lookup-icon">&#128101;</span>
                <input type="text" id="flpLookup{{ $i }}" class="flp-lookup-input" placeholder="Search from Travellers List" autocomplete="off" role="combobox" aria-expanded="false" aria-controls="flpLookupList{{ $i }}">
                <div class="flp-lookup-list" id="flpLookupList{{ $i }}" role="listbox" hidden></div>
              </div>
              @endif

              <div class="flr-row">
                <div class="flr-field">
                  <label for="flpTitle{{ $i }}">Title</label>
                  <select name="travellers[{{ $i }}][title]" id="flpTitle{{ $i }}" required>
                    @foreach($t['type'] === 'ADULT' ? ['Mr', 'Mrs', 'Ms'] : ['Master', 'Miss'] as $title)
                      <option value="{{ $title }}" @selected(($old['title'] ?? null) === $title)>{{ $title }}</option>
                    @endforeach
                  </select>
                </div>
                <div class="flr-field">
                  <div class="flp-label-row"><label for="flpFirst{{ $i }}">First Name</label><span class="flp-count" data-count-for="flpFirst{{ $i }}">0/{{ $firstNameMax }}</span></div>
                  <input type="text" name="travellers[{{ $i }}][first_name]" id="flpFirst{{ $i }}" value="{{ $old['first_name'] ?? '' }}" maxlength="{{ $firstNameMax }}" required autocomplete="off">
                </div>
                <div class="flr-field">
                  <div class="flp-label-row"><label for="flpLast{{ $i }}">Last Name</label><span class="flp-count" data-count-for="flpLast{{ $i }}">0/{{ $lastNameMax }}</span></div>
                  <input type="text" name="travellers[{{ $i }}][last_name]" id="flpLast{{ $i }}" value="{{ $old['last_name'] ?? '' }}" maxlength="{{ $lastNameMax }}" required autocomplete="off">
                </div>
              </div>

              @if($t['dobRequired'])
              <div class="flr-field" style="max-width:260px;">
                <label for="flpDob{{ $i }}">Date of Birth</label>
                <input type="date" name="travellers[{{ $i }}][dob]" id="flpDob{{ $i }}" value="{{ $old['dob'] ?? '' }}" max="{{ now()->subDay()->format('Y-m-d') }}" required>
              </div>
              @endif

              @if($passportRequired)
              <div class="flr-row" style="grid-template-columns:1fr 1fr;">
                <div class="flr-field"><label for="flpPass{{ $i }}">Passport Number</label><input type="text" name="travellers[{{ $i }}][passport_number]" id="flpPass{{ $i }}" value="{{ $old['passport_number'] ?? '' }}" required></div>
                <div class="flr-field">
                  <label for="flpPassNat{{ $i }}">Nationality</label>
                  <select name="travellers[{{ $i }}][passport_nationality]" id="flpPassNat{{ $i }}" required>
                    @php $natSel = $old['passport_nationality'] ?? 'IN'; @endphp
                    <option value="IN" @selected($natSel === 'IN')>India</option>
                    @foreach($countries as $code => $name)
                      @if($code !== 'IN')<option value="{{ $code }}" @selected($natSel === $code)>{{ $name }}</option>@endif
                    @endforeach
                  </select>
                </div>
              </div>
              <div class="flr-row" style="grid-template-columns:1fr 1fr;">
                <div class="flr-field"><label for="flpPassIss{{ $i }}">Passport Issue Date</label><input type="date" name="travellers[{{ $i }}][passport_issue_date]" id="flpPassIss{{ $i }}" value="{{ $old['passport_issue_date'] ?? '' }}" max="{{ now()->format('Y-m-d') }}" required></div>
                <div class="flr-field"><label for="flpPassExp{{ $i }}">Passport Expiry</label><input type="date" name="travellers[{{ $i }}][passport_expiry]" id="flpPassExp{{ $i }}" value="{{ $old['passport_expiry'] ?? '' }}" min="{{ now()->addDay()->format('Y-m-d') }}" required></div>
              </div>
              @endif

              @if($docApplicable && $t['type'] !== 'INFANT')
              <div class="flr-field" style="max-width:360px;">
                <label for="flpDoc{{ $i }}">{{ $docLabel }}{{ $docMandatory ? '' : ' (Optional)' }}</label>
                <input type="text" name="travellers[{{ $i }}][document_id]" id="flpDoc{{ $i }}" value="{{ $old['document_id'] ?? '' }}" maxlength="30" autocomplete="off" @if($docMandatory) required @endif>
                <small class="flp-hint">The airline may ask to see this ID at check-in.</small>
              </div>
              @endif

              @if($panApplicable && $t['type'] === 'ADULT')
              <div class="flr-field" style="max-width:260px;">
                <label for="flpPan{{ $i }}">PAN Number{{ $i === 0 ? '' : ' (Optional)' }}</label>
                <input type="text" name="travellers[{{ $i }}][pan]" id="flpPan{{ $i }}" value="{{ $old['pan'] ?? '' }}" maxlength="10" style="text-transform:uppercase;" autocomplete="off" @if($i === 0) required @endif>
              </div>
              @endif

              @if($primaryAirlineCode && $t['eligible'])
              <div class="flp-ff">
                <button type="button" class="flp-ff-toggle" aria-expanded="{{ ! empty($old['frequent_flyer']) ? 'true' : 'false' }}" aria-controls="flpFf{{ $i }}">Frequent Flyer Number (Optional)</button>
                <div class="flp-ff-grid" id="flpFf{{ $i }}" @if(empty($old['frequent_flyer'])) hidden @endif>
                  <div class="flr-field" style="margin-bottom:0;"><label>Airline</label><input type="text" value="{{ $primaryAirlineCode }}" readonly title="{{ $primaryAirlineName }}"></div>
                  <div class="flr-field" style="margin-bottom:0;"><label for="flpFfNo{{ $i }}">FF Number</label><input type="text" name="travellers[{{ $i }}][frequent_flyer]" id="flpFfNo{{ $i }}" value="{{ $old['frequent_flyer'] ?? '' }}" maxlength="20" autocomplete="off"></div>
                </div>
              </div>
              @endif

              <label class="flp-check">
                <input type="checkbox" name="travellers[{{ $i }}][save]" value="1" @checked(! $errors->any() || ! empty($old['save']))>
                Add this to My Travellers List <small>(for faster bookings next time)</small>
              </label>
            </div>
          </div>
        @endforeach
      </div>

      <div class="flr-section">
        <h2>Contact Details</h2>
        <div class="flr-row" style="grid-template-columns:1fr 1fr;">
          <div class="flr-field">
            <label for="flpPhone">Mobile Number</label>
            <div class="flp-phone"><input type="text" name="contact_dial_code" class="flp-dial" value="{{ old('contact_dial_code', '+91') }}" inputmode="tel" maxlength="5" pattern="\+?[1-9][0-9]{0,3}" title="Country code, e.g. +91" aria-label="Country code" required autocomplete="tel-country-code"><input type="tel" name="contact_phone" id="flpPhone" value="{{ $contactPhone }}" inputmode="numeric" maxlength="15" required autocomplete="tel-national"></div>
          </div>
          <div class="flr-field">
            <label for="flpEmail">Email ID</label>
            <input type="email" name="contact_email" id="flpEmail" value="{{ old('contact_email', auth()->user()->email) }}" required autocomplete="email">
          </div>
        </div>
        <p class="flr-note" style="margin-top:0;">Your ticket and flight updates will be sent here.</p>
      </div>

      @if($emergencyRequired)
      {{-- The airline needs someone to contact in an emergency (Review's
           conditions.iecr) — sent as the Book request's contactInfo. --}}
      <div class="flr-section">
        <h2>Emergency Contact</h2>
        <div class="flr-row" style="grid-template-columns:1fr 1fr;">
          <div class="flr-field">
            <label for="flpEcName">Contact Name</label>
            <input type="text" name="emergency_name" id="flpEcName" value="{{ old('emergency_name') }}" maxlength="60" required autocomplete="off">
          </div>
          <div class="flr-field">
            <label for="flpEcPhone">Mobile Number</label>
            <div class="flp-phone"><input type="text" name="emergency_dial_code" class="flp-dial" value="{{ old('emergency_dial_code', '+91') }}" inputmode="tel" maxlength="5" pattern="\+?[1-9][0-9]{0,3}" title="Country code, e.g. +91" aria-label="Country code" required autocomplete="off"><input type="tel" name="emergency_phone" id="flpEcPhone" value="{{ old('emergency_phone') }}" inputmode="numeric" maxlength="15" required autocomplete="off"></div>
          </div>
        </div>
        <div class="flr-field" style="max-width:420px;margin-bottom:0;">
          <label for="flpEcEmail">Email (Optional)</label>
          <input type="email" name="emergency_email" id="flpEcEmail" value="{{ old('emergency_email') }}" autocomplete="off">
        </div>
        <p class="flr-note">Someone not travelling with you, whom the airline can reach if needed.</p>
      </div>
      @endif

      <div class="flp-extras">
        <button type="button" class="flp-extra-btn" aria-expanded="{{ old('special_requests') ? 'true' : 'false' }}" aria-controls="flpNotes">+ Add Notes (Optional)</button>
        @unless($gstMandatory)
          <button type="button" class="flp-extra-btn" aria-expanded="{{ old('gst_number') ? 'true' : 'false' }}" aria-controls="flpGst">+ Add GST Details (Optional)</button>
        @endunless
      </div>

      <div class="flr-section" id="flpNotes" @unless(old('special_requests')) hidden @endunless>
        <h2>Notes</h2>
        <div class="flr-field" style="margin-bottom:0;">
          <label for="flpNotesInput">Anything we should know? (Optional)</label>
          <textarea name="special_requests" id="flpNotesInput" rows="3" maxlength="500">{{ old('special_requests') }}</textarea>
        </div>
      </div>

      <div class="flr-section" id="flpGst" @unless($gstMandatory || old('gst_number')) hidden @endunless>
        <h2>GST Details {{ $gstMandatory ? '(Required)' : '(Optional)' }}</h2>
        <div class="flr-row" style="grid-template-columns:1fr 1fr;">
          <div class="flr-field" style="margin-bottom:0;"><label for="flpGstNo">GSTIN</label><input type="text" name="gst_number" id="flpGstNo" value="{{ old('gst_number') }}" maxlength="15" style="text-transform:uppercase;" @if($gstMandatory) required @endif></div>
          <div class="flr-field" style="margin-bottom:0;"><label for="flpGstName">Registered Company Name</label><input type="text" name="gst_registered_name" id="flpGstName" value="{{ old('gst_registered_name') }}" maxlength="35" @if($gstMandatory) required @endif></div>
        </div>
      </div>

    </form>
  </div>

  <aside class="flr-summary">
    <h3>Fare Summary</h3>
    <p class="flr-sum-pax">{{ $context['adults'] }} Adult{{ $context['adults'] > 1 ? 's' : '' }}@if($context['children']), {{ $context['children'] }} Child{{ $context['children'] > 1 ? 'ren' : '' }}@endif@if($context['infants']), {{ $context['infants'] }} Infant{{ $context['infants'] > 1 ? 's' : '' }}@endif &middot; {{ ucwords(strtolower(str_replace('_', ' ', $context['cabinClass']))) }}</p>

    <div class="flr-sum-row"><span>Base fare</span><span>&#8377;{{ number_format($breakdown['base_fare'], 2) }}</span></div>
    <div class="flr-sum-row">
      <span><button type="button" class="flr-sum-toggle" aria-expanded="false" aria-controls="flrTaxBreakdown">Taxes and fees</button></span>
      <span id="flpSumTaxes">&#8377;{{ number_format($breakdown['airline_taxes'] + $convenienceFee, 2) }}</span>
    </div>
    <div class="flr-sum-sub" id="flrTaxBreakdown" hidden>
      <div><span>Airline taxes &amp; surcharges</span><span>&#8377;{{ number_format($breakdown['airline_taxes'], 2) }}</span></div>
      <div><span>Convenience fee</span><span id="flpSumConv">&#8377;{{ number_format($convenienceFee, 2) }}</span></div>
    </div>
    <div class="flr-sum-row" id="flpSumAddonsRow" hidden>
      <span><button type="button" class="flr-sum-toggle" aria-expanded="false" aria-controls="flpAddonBreakdown">Add-ons</button></span>
      <span id="flpSumAddons">&#8377;0.00</span>
    </div>
    <div class="flr-sum-sub" id="flpAddonBreakdown" hidden></div>
    <div class="flr-line total"><span>Amount to Pay</span><span id="flpSumTotal">&#8377;{{ number_format($breakdown['customer_price'], 2) }}</span></div>

    <button type="submit" form="flrForm" class="flr-submit" data-intent="review">Continue &rarr;</button>
    <p class="flr-note">You'll review your booking before paying{{ $canHold ? ' or blocking this fare' : '' }}.</p>
  </aside>
</div>

{{-- Full page width (not the left column) so the seat map and add-on
     cards have room. Holds no form fields of its own: picks are written as
     hidden inputs into #flpAddonInputs inside #flrForm, and the buttons
     submit that form via the `form` attribute. --}}
<div class="flp-full">
      @if($hasAddons)
      <div class="flr-section" id="flpAddons">
        <h2>Flight Add On</h2>

        @if($seatSegments || $seatMapMissing || $seatNotes)
        <div class="flp-addon" id="flpSeatBlock">
          <h3><span>&#128186;</span> Select Seat</h3>
          @foreach($seatNotes as $sn)
            {{-- A leg without a seat layout, with the airline's reason (`nt`). --}}
            <div class="flp-note-box">Seat selection isn't available for {{ $sn['label'] }}: {{ $sn['note'] }}</div>
          @endforeach
          @if($seatMapMissing)
            <div class="flp-note-box warn">The airline requires a seat for this fare, but the seat map couldn't be loaded. Please refresh this page to try again.</div>
          @elseif(! $seatSegments)
            {{-- Only notes — no leg has a seat map to pick from. --}}
          @else
            <div class="flp-tabs" id="flpSeatTabs"></div>
            <div class="flp-legend" id="flpSeatLegend"></div>
            <div class="flp-seatmap-wrap"><div class="flp-nose" aria-hidden="true"></div><div class="flp-seatgrid" id="flpSeatGrid"></div></div>
            <div class="flp-paxchips" id="flpSeatPax"></div>
            <div class="flp-total">Total Seat Fee: <b id="flpSeatTotal">&#8377;0.00</b></div>
            @if($seatMandatoryAny)
              <div class="flp-note-box warn">The airline requires a seat for every traveller on this fare. Free seats are shown in green.</div>
            @endif
            <div class="flp-note-box">* Conditions apply. We'll do our best to honour your seat preference, but for operational reasons the airline can't guarantee it. The seat map may not exactly match the aircraft layout.</div>
          @endif
        </div>
        @endif

        @if($baggageGroups)
        <div class="flp-addon" data-addon="baggage">
          <h3><span>&#129523;</span> Select Baggage</h3>
          <div class="flp-tabs"></div>
          <div class="flp-cards"></div>
          <div class="flp-paxchips"></div>
          <div class="flp-total">Total Baggage Fee: <b class="flp-addon-total">&#8377;0.00</b></div>
        </div>
        @endif

        @if($mealGroups)
        <div class="flp-addon" data-addon="meals">
          <h3><span>&#127869;</span> Select Meal</h3>
          <div class="flp-tabs"></div>
          <div class="flp-cards"></div>
          <div class="flp-paxchips"></div>
          <div class="flp-total">Total Meal Fee: <b class="flp-addon-total">&#8377;0.00</b></div>
        </div>
        @endif

        @if($ffGroups)
        <div class="flp-addon" data-addon="fastforward">
          <h3><span>&#10022;</span> Other Services</h3>
          <div class="flp-tabs"></div>
          <div class="flp-cards"></div>
          <div class="flp-total">Total Fee: <b class="flp-addon-total">&#8377;0.00</b></div>
        </div>
        @endif
      </div>
      @endif

      <div class="flp-actions">
        <a href="{{ route('flights.review.show') }}" class="flr-submit outline">&laquo; Back</a>
        <button type="submit" form="flrForm" class="flr-submit" data-intent="review">Continue &raquo;</button>
      </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
  var TRAVELLERS = @json($travellers);
  var SEAT_SEGMENTS = @json($seatSegments);
  var GROUPS = { baggage: @json($baggageGroups), meals: @json($mealGroups), fastforward: @json($ffGroups) };
  var SAVED = @json($savedTravellerData);
  var OLD = @json($oldTravellers);
  var PRICING = @json($pricingJs);
  var eligible = TRAVELLERS.filter(function (t) { return t.eligible; });

  function money(v) { return '₹' + v.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 }); }
  function esc(s) { var d = document.createElement('div'); d.textContent = s == null ? '' : String(s); return d.innerHTML; }

  // --- state: sel[kind][segId][paxIdx] = code ---
  var sel = { seats: {}, baggage: {}, meals: {}, fastforward: {} };
  Object.keys(sel).forEach(function (kind) {
    Object.keys(OLD || {}).forEach(function (i) {
      var picks = (OLD[i] || {})[kind] || {};
      Object.keys(picks).forEach(function (segId) {
        if (!picks[segId]) return;
        (sel[kind][segId] = sel[kind][segId] || {})[i] = picks[segId];
      });
    });
  });

  var seatPrice = {};
  SEAT_SEGMENTS.forEach(function (seg) {
    seatPrice[seg.id] = {};
    seg.seats.forEach(function (s) { if (!s.booked) seatPrice[seg.id][s.code] = s.amt; });
    // Drop restored picks that are no longer valid.
    Object.keys(sel.seats[seg.id] || {}).forEach(function (i) {
      if (!(sel.seats[seg.id][i] in seatPrice[seg.id])) delete sel.seats[seg.id][i];
    });
  });
  function optionOf(kind, segId, code) {
    var g = GROUPS[kind].find(function (g) { return g.id === segId; });
    return g ? g.options.find(function (o) { return o.code === code; }) : null;
  }

  function kindTotal(kind) {
    var total = 0;
    Object.keys(sel[kind]).forEach(function (segId) {
      Object.keys(sel[kind][segId]).forEach(function (i) {
        var code = sel[kind][segId][i];
        if (kind === 'seats') total += (seatPrice[segId] || {})[code] || 0;
        else { var o = optionOf(kind, segId, code); total += o ? o.amount : 0; }
      });
    });
    return total;
  }

  // Mirrors FlightPricingService::price() exactly, so the total shown here
  // is the amount charged.
  function customerPrice(tf) {
    var gst = tf < PRICING.gstThreshold ? PRICING.gstLow : PRICING.gstHigh;
    var margin = tf * PRICING.marginRate;
    var pre = tf + margin + margin * gst;
    return Math.round(pre / (1 - PRICING.razorpayRate) * 100) / 100;
  }

  var inputsBox = document.getElementById('flpAddonInputs');
  var holdBtn = document.getElementById('flpHoldBtn');
  var holdNote = document.getElementById('flpHoldNote');
  var holdNoteText = holdNote ? holdNote.textContent : '';

  function refresh() {
    inputsBox.innerHTML = '';
    Object.keys(sel).forEach(function (kind) {
      Object.keys(sel[kind]).forEach(function (segId) {
        Object.keys(sel[kind][segId]).forEach(function (i) {
          var input = document.createElement('input');
          input.type = 'hidden';
          input.name = 'travellers[' + i + '][' + kind + '][' + segId + ']';
          input.value = sel[kind][segId][i];
          inputsBox.appendChild(input);
        });
      });
    });

    var totals = { seats: kindTotal('seats'), baggage: kindTotal('baggage'), meals: kindTotal('meals'), fastforward: kindTotal('fastforward') };
    var addons = totals.seats + totals.baggage + totals.meals + totals.fastforward;
    var tf = PRICING.tf + addons;
    var cust = customerPrice(tf);
    var conv = Math.max(0, cust - tf);

    document.getElementById('flpSumConv').textContent = money(conv);
    document.getElementById('flpSumTaxes').textContent = money(PRICING.taxes + conv);
    document.getElementById('flpSumTotal').textContent = money(cust);
    document.getElementById('flpSumAddonsRow').hidden = addons <= 0;
    document.getElementById('flpSumAddons').textContent = money(addons);
    var labels = { seats: 'Seats', baggage: 'Extra baggage', meals: 'Meals', fastforward: 'Other services' };
    document.getElementById('flpAddonBreakdown').innerHTML = Object.keys(totals).filter(function (k) { return totals[k] > 0; })
      .map(function (k) { return '<div><span>' + labels[k] + '</span><span>' + money(totals[k]) + '</span></div>'; }).join('');

    var seatTotalEl = document.getElementById('flpSeatTotal');
    if (seatTotalEl) seatTotalEl.textContent = money(totals.seats);
    document.querySelectorAll('.flp-addon[data-addon]').forEach(function (box) {
      box.querySelector('.flp-addon-total').textContent = money(totals[box.dataset.addon]);
    });

    if (holdBtn) {
      holdBtn.disabled = addons > 0;
      holdNote.textContent = addons > 0 ? 'Paid add-ons can’t be held — remove them to hold this fare, or proceed to pay.' : holdNoteText;
    }
  }

  // --- seat picker ---
  if (SEAT_SEGMENTS.length) {
    var seatTabs = document.getElementById('flpSeatTabs');
    var seatGrid = document.getElementById('flpSeatGrid');
    var seatLegend = document.getElementById('flpSeatLegend');
    var seatPax = document.getElementById('flpSeatPax');
    var activeSeg = SEAT_SEGMENTS[0].id;
    var activePax = eligible.length ? eligible[0].idx : null;

    function bandsFor(seg) {
      var paid = Array.from(new Set(seg.seats.filter(function (s) { return s.amt > 0; }).map(function (s) { return s.amt; }))).sort(function (a, b) { return a - b; });
      var n = Math.min(4, paid.length);
      var bands = [];
      for (var b = 0; b < n; b++) {
        var slice = paid.slice(Math.floor(b * paid.length / n), Math.floor((b + 1) * paid.length / n));
        bands.push({ min: slice[0], max: slice[slice.length - 1] });
      }
      return bands;
    }
    function bandIndex(bands, amt) {
      if (amt <= 0) return 0;
      for (var b = 0; b < bands.length; b++) if (amt <= bands[b].max) return b + 1;
      return bands.length;
    }

    function renderSeats() {
      var seg = SEAT_SEGMENTS.find(function (s) { return s.id === activeSeg; });
      var picks = sel.seats[seg.id] || {};

      seatTabs.innerHTML = SEAT_SEGMENTS.map(function (s) {
        var count = Object.keys(sel.seats[s.id] || {}).length;
        return '<button type="button" class="flp-tab' + (s.id === activeSeg ? ' active' : '') + '" data-seg="' + s.id + '">' + esc(s.label) + '<small>' + count + '/' + eligible.length + (s.mandatory ? ' · required' : '') + '</small></button>';
      }).join('');

      var bands = bandsFor(seg);
      var legend = '<span><i class="flp-band-0"></i>Free</span>';
      bands.forEach(function (b, k) {
        legend += '<span><i class="flp-band-' + (k + 1) + '"></i>' + (b.min === b.max ? money(b.min) : money(b.min) + ' – ' + money(b.max)) + '</span>';
      });
      legend += '<span><i style="background:#2a2a2a"></i>Booked</span><span><i style="background:var(--gold)"></i>Selected</span><span><i style="background:transparent;border-top:2px solid #7fd6a0;border-radius:0;height:8px"></i>Extra legroom</span>';
      seatLegend.innerHTML = legend;

      var byPos = {};
      var usedCols = {};
      seg.seats.forEach(function (s) { byPos[s.r + '-' + s.c] = s; usedCols[s.c] = true; });
      var letters = {};
      seg.seats.forEach(function (s) { letters[s.c] = String(s.no).replace(/\d+/g, ''); });
      var paxByCode = {};
      Object.keys(picks).forEach(function (i) { paxByCode[picks[i]] = +i; });

      seatGrid.style.gridTemplateColumns = '22px repeat(' + seg.rows + ', 32px)';
      var html = '';
      for (var c = seg.cols; c >= 1; c--) {
        html += '<span class="flp-seat-letter">' + (usedCols[c] ? esc(letters[c]) : '') + '</span>';
        for (var r = 1; r <= seg.rows; r++) {
          if (!usedCols[c]) { html += '<span class="flp-seat-rownum">' + r + '</span>'; continue; }
          var s = byPos[r + '-' + c];
          if (!s) { html += '<span></span>'; continue; }
          var mine = paxByCode[s.code];
          var cls = 'flp-seat flp-band-' + bandIndex(bands, s.amt) + (s.legroom ? ' legroom' : '') + (mine !== undefined ? ' selected' : '');
          var tip = s.no + ' · ' + (s.amt > 0 ? money(s.amt) : 'Free') + (s.legroom ? ' · Extra legroom' : '') + (s.exit ? ' · Exit row' : '') + (s.window ? ' · Window' : '') + (s.aisle ? ' · Aisle' : '') + (s.booked ? ' · Booked' : '');
          var label = mine !== undefined ? (TRAVELLERS[mine].chip.split('-')[0][0] + TRAVELLERS[mine].chip.split('-')[1]) : (s.booked ? '✕' : esc(s.no));
          html += '<button type="button" class="' + cls + '" data-code="' + esc(s.code) + '" title="' + esc(tip) + '" aria-label="' + esc(tip) + '"' + (s.booked ? ' disabled' : '') + '>' + label + '</button>';
        }
      }
      seatGrid.innerHTML = html;

      seatPax.innerHTML = eligible.map(function (t) {
        var code = picks[t.idx];
        var amt = code ? seatPrice[seg.id][code] : 0;
        return '<button type="button" class="flp-paxchip' + (t.idx === activePax ? ' active' : '') + (code ? ' filled' : '') + '" data-pax="' + t.idx + '"><b>' + t.chip + '</b><span>' + (code ? esc(code) + ' · ' + (amt > 0 ? money(amt) : 'Free') : 'Select seat') + '</span></button>';
      }).join('');
    }

    seatTabs.addEventListener('click', function (e) {
      var btn = e.target.closest('[data-seg]');
      if (!btn) return;
      activeSeg = btn.dataset.seg;
      activePax = eligible.length ? eligible[0].idx : null;
      renderSeats();
    });
    seatPax.addEventListener('click', function (e) {
      var btn = e.target.closest('[data-pax]');
      if (!btn) return;
      activePax = +btn.dataset.pax;
      renderSeats();
    });
    seatGrid.addEventListener('click', function (e) {
      var btn = e.target.closest('.flp-seat');
      if (!btn || btn.disabled || activePax === null) return;
      var picks = sel.seats[activeSeg] = sel.seats[activeSeg] || {};
      var code = btn.dataset.code;
      var holder = Object.keys(picks).find(function (i) { return picks[i] === code; });
      if (holder !== undefined && +holder !== activePax) { activePax = +holder; renderSeats(); return; }
      if (picks[activePax] === code) {
        delete picks[activePax];
      } else {
        picks[activePax] = code;
        var next = eligible.find(function (t) { return !picks[t.idx]; });
        if (next) activePax = next.idx;
      }
      renderSeats();
      refresh();
    });
    renderSeats();
  }

  // --- baggage / meals / other services ---
  document.querySelectorAll('.flp-addon[data-addon]').forEach(function (box) {
    var kind = box.dataset.addon;
    var groups = GROUPS[kind];
    var perPax = kind !== 'fastforward';
    var active = groups[0].id;
    var tabs = box.querySelector('.flp-tabs');
    var cards = box.querySelector('.flp-cards');
    var chips = box.querySelector('.flp-paxchips');
    var icon = { baggage: '🧳', meals: '🍽', fastforward: '⚡' }[kind];

    function render() {
      var group = groups.find(function (g) { return g.id === active; });
      var picks = sel[kind][active] || {};
      tabs.innerHTML = groups.length > 1 || kind === 'baggage' ? groups.map(function (g) {
        var count = Object.keys(sel[kind][g.id] || {}).length;
        return '<button type="button" class="flp-tab' + (g.id === active ? ' active' : '') + '" data-seg="' + g.id + '">' + esc(g.label) + (perPax ? '<small>' + count + '/' + eligible.length + '</small>' : '') + '</button>';
      }).join('') : '';

      cards.innerHTML = group.options.map(function (o) {
        var qty = Object.keys(picks).filter(function (i) { return picks[i] === o.code; }).length;
        var price = o.amount > 0 ? money(o.amount) : 'Free';
        var control;
        if (perPax) {
          var full = Object.keys(picks).length >= eligible.length;
          control = '<div class="flp-qty"><button type="button" data-act="minus" data-code="' + esc(o.code) + '" aria-label="Remove ' + esc(o.desc) + '"' + (qty ? '' : ' disabled') + '>−</button><output>' + qty + '</output><button type="button" data-act="plus" data-code="' + esc(o.code) + '" aria-label="Add ' + esc(o.desc) + '"' + (full ? ' disabled' : '') + '>+</button></div>';
        } else {
          control = '<div class="flp-qty"><button type="button" data-act="' + (qty ? 'minus' : 'plus') + '" data-code="' + esc(o.code) + '" aria-label="' + (qty ? 'Remove ' : 'Add ') + esc(o.desc) + '">' + (qty ? '✓' : '+') + '</button></div>';
        }
        return '<div class="flp-card' + (qty ? ' on' : '') + '"><span class="flp-card-ic">' + icon + '</span><div class="flp-card-main"><div class="flp-card-name">' + esc(o.desc) + (perPax ? '' : ' <small>(for all passengers)</small>') + '</div><div class="flp-card-price">' + price + (perPax ? '' : ' <small style="color:var(--white-30);font-weight:400;">per passenger</small>') + '</div></div>' + control + '</div>';
      }).join('');

      if (chips) {
        chips.innerHTML = eligible.map(function (t) {
          var code = picks[t.idx];
          var o = code ? group.options.find(function (x) { return x.code === code; }) : null;
          return '<div class="flp-paxchip' + (o ? ' filled' : '') + '"><b>' + t.chip + '</b><span>' + (o ? esc(o.desc) : 'Select ' + (kind === 'meals' ? 'meal' : 'baggage')) + '</span></div>';
        }).join('');
      }
    }

    tabs.addEventListener('click', function (e) {
      var btn = e.target.closest('[data-seg]');
      if (!btn) return;
      active = btn.dataset.seg;
      render();
    });
    cards.addEventListener('click', function (e) {
      var btn = e.target.closest('[data-act]');
      if (!btn) return;
      var picks = sel[kind][active] = sel[kind][active] || {};
      var code = btn.dataset.code;
      if (!perPax) {
        eligible.forEach(function (t) {
          if (btn.dataset.act === 'plus') picks[t.idx] = code; else delete picks[t.idx];
        });
      } else if (btn.dataset.act === 'plus') {
        var free = eligible.find(function (t) { return !picks[t.idx]; });
        if (free) picks[free.idx] = code;
      } else {
        var holder = eligible.slice().reverse().find(function (t) { return picks[t.idx] === code; });
        if (holder) delete picks[holder.idx];
      }
      render();
      refresh();
    });
    render();
  });

  // --- traveller cards ---
  document.querySelectorAll('.flp-pax-head, .flp-ff-toggle, .flp-extra-btn, .flr-sum-toggle').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var panel = document.getElementById(btn.getAttribute('aria-controls'));
      var open = btn.getAttribute('aria-expanded') !== 'true';
      btn.setAttribute('aria-expanded', open ? 'true' : 'false');
      panel.hidden = !open;
    });
  });

  document.querySelectorAll('.flp-count').forEach(function (counter) {
    var input = document.getElementById(counter.dataset.countFor);
    var max = input.maxLength;
    function update() { counter.textContent = input.value.length + '/' + max; }
    input.addEventListener('input', update);
    update();
  });

  function titleFor(type, gender) {
    var male = String(gender || '').toLowerCase() === 'male';
    if (type === 'ADULT') return male ? 'Mr' : 'Ms';
    return male ? 'Master' : 'Miss';
  }

  document.querySelectorAll('.flp-pax').forEach(function (card) {
    var input = card.querySelector('.flp-lookup-input');
    if (!input) return;
    var list = card.querySelector('.flp-lookup-list');
    var i = card.dataset.pax;
    var type = card.dataset.type;

    function show() {
      var q = input.value.trim().toLowerCase();
      var matches = SAVED.filter(function (s) { return (s.first + ' ' + s.last).toLowerCase().indexOf(q) !== -1; });
      list.innerHTML = matches.length ? matches.map(function (s) {
        var idx = SAVED.indexOf(s);
        return '<button type="button" class="flp-lookup-item" role="option" data-saved="' + idx + '">' + esc(s.first + ' ' + s.last) + (s.dob ? '<small>DOB ' + esc(s.dob) + '</small>' : '') + '</button>';
      }).join('') : '<div class="flp-lookup-empty">No saved traveller matches “' + esc(input.value) + '”.</div>';
      list.hidden = false;
      input.setAttribute('aria-expanded', 'true');
    }
    function hide() { list.hidden = true; input.setAttribute('aria-expanded', 'false'); }
    function set(id, value) { var el = document.getElementById(id); if (el && value) { el.value = value; el.dispatchEvent(new Event('input')); } }

    input.addEventListener('focus', show);
    input.addEventListener('input', show);
    input.addEventListener('keydown', function (e) { if (e.key === 'Escape') hide(); });
    document.addEventListener('click', function (e) { if (!card.querySelector('.flp-lookup').contains(e.target)) hide(); });
    list.addEventListener('click', function (e) {
      var item = e.target.closest('[data-saved]');
      if (!item) return;
      var s = SAVED[+item.dataset.saved];
      document.getElementById('flpTitle' + i).value = titleFor(type, s.gender);
      set('flpFirst' + i, s.first);
      set('flpLast' + i, s.last);
      set('flpDob' + i, s.dob);
      set('flpPass' + i, s.passport);
      set('flpPassExp' + i, s.passportExpiry);
      if (s.ffNumber && document.getElementById('flpFfNo' + i)) {
        set('flpFfNo' + i, s.ffNumber);
        var ffGrid = document.getElementById('flpFf' + i);
        ffGrid.hidden = false;
        card.querySelector('.flp-ff-toggle').setAttribute('aria-expanded', 'true');
      }
      input.value = s.first + ' ' + s.last;
      hide();
    });
  });

  // --- submit ---
  var form = document.getElementById('flrForm');
  document.querySelectorAll('[data-intent]').forEach(function (btn) {
    btn.addEventListener('click', function () { document.getElementById('flrIntent').value = btn.dataset.intent; });
  });
  form.addEventListener('submit', function (e) {
    // Required seats must be picked before paying — otherwise the airline
    // rejects the booking after payment.
    var missing = SEAT_SEGMENTS.filter(function (seg) {
      return seg.mandatory && eligible.some(function (t) { return !(sel.seats[seg.id] || {})[t.idx]; });
    });
    if (missing.length) {
      e.preventDefault();
      var block = document.getElementById('flpSeatBlock');
      block.scrollIntoView({ behavior: 'smooth', block: 'start' });
      alert('Please select a seat for every traveller on ' + missing.map(function (s) { return s.label; }).join(', ') + ' — the airline requires it for this fare.');
      return;
    }
    if (!form.checkValidity()) {
      e.preventDefault();
      var bad = form.querySelector(':invalid');
      var body = bad.closest('.flp-pax-body, #flpNotes, #flpGst');
      if (body && body.hidden) {
        body.hidden = false;
        var head = document.querySelector('[aria-controls="' + body.id + '"]');
        if (head) head.setAttribute('aria-expanded', 'true');
      }
      bad.reportValidity();
    }
  });

  refresh();
})();
</script>
@endpush
