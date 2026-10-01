@extends('layouts.frontend')

@section('meta_title', 'Flight Itinerary | TYT Luxe')

@push('styles')
@include('partials.flight-booking-styles')
<style>
  /* Flight details card */
  .flr-fd-head { display: flex; align-items: baseline; justify-content: space-between; gap: 16px; margin-bottom: 20px; }
  .flr-fd-head h2 { margin-bottom: 0; }
  .flr-back-link { font-family: 'Jost', sans-serif; font-size: 12px; font-weight: 600; letter-spacing: 0.04em; color: var(--gold-light); text-decoration: none; white-space: nowrap; }
  .flr-back-link:hover { color: #fff; }
  .flr-trip { border: 1px solid rgba(255,255,255,0.08); border-radius: 16px; overflow: hidden; margin-bottom: 18px; font-family: 'Jost', sans-serif; background: linear-gradient(180deg, rgba(255,255,255,0.025), rgba(255,255,255,0.01)); }

  /* Trip header: route + date on the left, trip facts as chips on the right */
  .flr-trip-head { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px 16px; padding: 14px 20px; background: rgba(255,255,255,0.035); border-bottom: 1px solid rgba(255,255,255,0.06); }
  .flr-trip-route { display: flex; align-items: center; flex-wrap: wrap; gap: 4px 10px; min-width: 0; }
  .flr-trip-leg { padding: 2px 9px; border-radius: 100px; background: var(--gold-dim); color: var(--gold-light); font-size: 9.5px; font-weight: 700; letter-spacing: 0.1em; text-transform: uppercase; }
  .flr-trip-cities { font-size: 15px; font-weight: 600; color: #fff; }
  .flr-trip-cities span { color: var(--gold); margin: 0 4px; }
  .flr-trip-date { font-size: 12.5px; color: var(--white-60); }
  .flr-chips { display: flex; flex-wrap: wrap; gap: 6px; }
  .flr-chip { display: inline-flex; align-items: center; gap: 5px; padding: 4px 10px; border-radius: 100px; border: 1px solid rgba(255,255,255,0.1); font-size: 11px; font-weight: 600; color: var(--white-80); white-space: nowrap; }
  .flr-chip.refund { border-color: rgba(127,214,160,0.35); color: #7fd6a0; background: rgba(127,214,160,0.06); }
  .flr-chip.nonrefund { border-color: rgba(243,163,163,0.35); color: #f3a3a3; background: rgba(243,163,163,0.06); }

  /* One flight segment: airline strip, then the timeline */
  .flr-seg { padding: 18px 20px 20px; }
  .flr-seg + .flr-seg { border-top: 1px solid rgba(255,255,255,0.05); }
  .flr-seg-airline { display: flex; align-items: center; gap: 12px; margin-bottom: 18px; }
  .flr-seg-airline img { width: 36px; height: 36px; border-radius: 9px; background: #fff; padding: 3px; object-fit: contain; flex-shrink: 0; }
  .flr-seg-aname { font-size: 14px; font-weight: 600; color: #fff; }
  .flr-seg-fno { font-size: 11.5px; color: var(--white-30); margin-top: 1px; }
  .flr-badge { margin-left: auto; padding: 4px 11px; border-radius: 100px; background: var(--gold-dim); color: var(--gold-light); font-size: 10px; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; white-space: nowrap; }

  .flr-timeline { display: grid; grid-template-columns: minmax(0, 1fr) minmax(110px, 0.9fr) minmax(0, 1fr); gap: 18px; align-items: start; }
  .flr-point-time { display: flex; align-items: baseline; gap: 8px; }
  .flr-point-time b { font-size: 24px; font-weight: 700; color: #fff; letter-spacing: 0.01em; line-height: 1.1; }
  .flr-point-time span { font-size: 13px; font-weight: 700; color: var(--gold-light); letter-spacing: 0.08em; }
  .flr-point-date { font-size: 12px; color: var(--white-80); margin-top: 6px; }
  .flr-point-place { font-size: 11.5px; color: var(--white-60); line-height: 1.5; margin-top: 3px; }
  .flr-point-term { display: inline-block; margin-top: 8px; padding: 2px 9px; border-radius: 6px; background: rgba(201,168,76,0.1); color: var(--gold); font-size: 10.5px; font-weight: 600; }
  .flr-point.arr { text-align: right; }
  .flr-point.arr .flr-point-time { justify-content: flex-end; }
  .flr-mid { text-align: center; padding-top: 4px; }
  .flr-mid-dur { font-size: 12px; font-weight: 600; color: #fff; }
  .flr-mid-line { position: relative; height: 1px; margin: 10px 4px; background: repeating-linear-gradient(90deg, rgba(201,168,76,0.55) 0 5px, transparent 5px 9px); }
  .flr-mid-line::before, .flr-mid-line::after { content: ''; position: absolute; top: -3px; width: 7px; height: 7px; border-radius: 50%; border: 1.5px solid var(--gold); background: var(--dark-2); }
  .flr-mid-line::before { left: -4px; }
  .flr-mid-line::after { right: -4px; background: var(--gold); }
  .flr-mid-plane { position: absolute; left: 50%; top: 50%; transform: translate(-50%, -50%) rotate(90deg); color: var(--gold-light); font-size: 13px; background: var(--dark-2); padding: 0 5px; line-height: 1; }
  .flr-mid-stop { font-size: 10.5px; color: var(--white-60); letter-spacing: 0.06em; text-transform: uppercase; }

  .flr-layover { display: flex; align-items: center; gap: 12px; margin: 0 20px; font-size: 11.5px; color: var(--gold-light); }
  .flr-layover::before, .flr-layover::after { content: ''; flex: 1; border-top: 1px dashed rgba(201,168,76,0.3); }
  .flr-layover span { padding: 6px 14px; border-radius: 100px; border: 1px solid rgba(201,168,76,0.3); background: rgba(201,168,76,0.06); white-space: nowrap; }

  .flr-flex-note { margin: 0 20px 14px; padding: 10px 14px; border-radius: 10px; background: rgba(74,222,128,0.07); border: 1px solid rgba(74,222,128,0.25); color: #b8eccb; font-size: 12px; line-height: 1.55; }
  .flr-flex-note b { color: #7fd6a0; }

  /* Baggage tiles, one row per passenger type */
  .flr-baggage { display: flex; flex-wrap: wrap; align-items: center; gap: 8px 10px; padding: 14px 20px; border-top: 1px solid rgba(255,255,255,0.06); background: rgba(255,255,255,0.015); font-size: 12px; color: var(--white-60); }
  .flr-baggage-label { font-size: 10px; font-weight: 700; letter-spacing: 0.1em; text-transform: uppercase; color: var(--white-30); margin-right: 4px; }
  .flr-bag { display: inline-flex; align-items: center; gap: 8px; padding: 6px 12px; border-radius: 10px; border: 1px solid rgba(255,255,255,0.08); background: rgba(255,255,255,0.03); }
  .flr-bag-icon { color: var(--gold); font-size: 13px; }
  .flr-bag small { display: block; font-size: 9.5px; letter-spacing: 0.06em; text-transform: uppercase; color: var(--white-30); }
  .flr-bag b { color: #fff; font-weight: 600; font-size: 12.5px; }

  @media (max-width: 640px) {
    .flr-seg { padding: 16px 14px; }
    .flr-trip-head { padding: 12px 14px; }
    .flr-timeline { grid-template-columns: minmax(0, 1fr) 76px minmax(0, 1fr); gap: 10px; }
    .flr-point-time { flex-direction: column; gap: 0; }
    .flr-point.arr .flr-point-time { align-items: flex-end; }
    .flr-point-time b { font-size: 20px; }
    .flr-point-place { display: none; }
    .flr-badge { margin-left: 0; }
    .flr-seg-airline { flex-wrap: wrap; }
  }

  /* Fare rules (collapsible, tabbed by fee type) */
  .flr-rules-toggle { display: flex; align-items: center; justify-content: space-between; gap: 12px; width: 100%; padding: 15px 20px; border-radius: 14px; border: 1px solid rgba(201,168,76,0.3); background: rgba(201,168,76,0.04); color: var(--gold-light); font-family: 'Jost', sans-serif; text-align: left; cursor: pointer; transition: background 0.2s ease, border-color 0.2s ease; }
  .flr-rules-toggle:hover { background: rgba(201,168,76,0.08); border-color: rgba(201,168,76,0.5); }
  .flr-rules-toggle[aria-expanded="true"] { background: rgba(201,168,76,0.1); border-bottom-left-radius: 14px; }
  .flr-rules-toggle-title { font-size: 12px; font-weight: 700; letter-spacing: 0.1em; text-transform: uppercase; }
  .flr-rules-toggle-sub { display: block; margin-top: 2px; font-size: 11.5px; font-weight: 400; letter-spacing: 0; text-transform: none; color: var(--white-60); }
  .flr-rules-toggle #flrRulesIcon { width: 28px; height: 28px; flex-shrink: 0; border-radius: 50%; border: 1px solid rgba(201,168,76,0.45); display: flex; align-items: center; justify-content: center; font-size: 15px; }
  .flr-rules { margin-top: 14px; padding: 20px; border-radius: 16px; background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.07); font-family: 'Jost', sans-serif; }
  .flr-rules[hidden] { display: none; }
  .flr-rules-block + .flr-rules-block { margin-top: 22px; padding-top: 22px; border-top: 1px solid rgba(255,255,255,0.06); }
  .flr-rules-top { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 8px 14px; margin-bottom: 14px; }
  .flr-rules-sector { display: inline-flex; align-items: center; gap: 6px; padding: 5px 13px; border-radius: 100px; background: var(--gold-dim); color: var(--gold-light); font-size: 12px; font-weight: 700; letter-spacing: 0.08em; }
  .flr-rules-hint { font-size: 11.5px; color: var(--white-30); margin: 0; }

  /* Fee table: fee types as tab headers, scrolls sideways when narrow */
  .flr-rules-scroll { overflow-x: auto; padding-bottom: 12px; scrollbar-width: thin; scrollbar-color: rgba(201,168,76,0.55) rgba(255,255,255,0.05); }
  .flr-rules-scroll::-webkit-scrollbar { height: 6px; }
  .flr-rules-scroll::-webkit-scrollbar-track { background: rgba(255,255,255,0.05); border-radius: 6px; }
  .flr-rules-scroll::-webkit-scrollbar-thumb { background: rgba(201,168,76,0.55); border-radius: 6px; }
  .flr-rules-scroll::-webkit-scrollbar-thumb:hover { background: var(--gold); }
  .flr-rules-table { width: 100%; border-collapse: separate; border-spacing: 0; border: 1px solid rgba(255,255,255,0.08); border-radius: 12px; overflow: hidden; font-size: 13px; }
  .flr-rules-table th { padding: 0; background: rgba(255,255,255,0.04); vertical-align: middle; border-bottom: 1px solid rgba(255,255,255,0.08); }
  .flr-rules-table th.flr-rules-tf { width: 190px; min-width: 190px; padding: 16px 20px; color: var(--white-60); font-size: 11px; font-weight: 600; letter-spacing: 0.08em; text-transform: uppercase; text-align: left; }
  .flr-rules-table th small { display: block; margin-top: 4px; font-size: 10.5px; font-weight: 400; letter-spacing: 0; text-transform: none; color: var(--white-30); line-height: 1.4; }
  .flr-rules-tab { width: 100%; min-width: 150px; height: 100%; padding: 16px 18px; border: none; border-bottom: 2px solid transparent; background: transparent; color: var(--white-60); font-family: 'Jost', sans-serif; font-size: 11.5px; font-weight: 600; letter-spacing: 0.06em; text-transform: uppercase; white-space: nowrap; cursor: pointer; line-height: 1.3; transition: color 0.2s ease, background 0.2s ease; }
  .flr-rules-tab small { display: block; margin-top: 4px; font-size: 10.5px; font-weight: 400; text-transform: none; letter-spacing: 0; color: var(--white-30); }
  .flr-rules-tab:hover { color: #fff; background: rgba(255,255,255,0.03); }
  .flr-rules-tab.active { color: var(--gold-light); border-bottom-color: var(--gold); background: rgba(201,168,76,0.08); }
  .flr-rules-table td { padding: 16px 20px; color: var(--white-80); vertical-align: top; line-height: 1.6; }
  .flr-rules-table tr + tr td { border-top: 1px solid rgba(255,255,255,0.06); }
  .flr-rules-table td:first-child { color: #fff; font-weight: 600; white-space: nowrap; }
  .flr-rules-table td:first-child small { display: block; font-weight: 400; color: var(--white-30); font-size: 11px; margin-top: 2px; }
  .flr-rules-fee { font-size: 15px; font-weight: 700; color: var(--gold-light); }
  .flr-rules-fee.free { color: #7fd6a0; }
  .flr-rules-info { display: block; margin-top: 4px; color: var(--white-80); font-size: 12.5px; }
  .flr-rules-fee + .flr-rules-info { color: var(--white-60); }
  .flr-rules-note { display: block; margin-top: 6px; font-size: 11.5px; color: var(--white-30); }
  .flr-rules-misc { padding: 14px 16px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.08); background: rgba(255,255,255,0.025); font-size: 12.5px; line-height: 1.65; color: var(--white-80); }
  .flr-rules-misc p { margin: 0 0 8px; }
  .flr-rules-misc p:last-child { margin-bottom: 0; }
  .flr-rules-notes { display: flex; gap: 10px; margin-top: 18px; padding: 12px 14px; border-radius: 12px; background: rgba(201,168,76,0.05); border: 1px solid rgba(201,168,76,0.15); font-size: 11.5px; line-height: 1.6; color: var(--white-60); }
  .flr-rules-notes span:first-child { color: var(--gold); font-size: 14px; line-height: 1.3; }

  /* Fare-change popup */
  .flr-alert-overlay { position: fixed; inset: 0; z-index: 2000; display: flex; align-items: center; justify-content: center; padding: 24px; background: rgba(0,0,0,0.72); backdrop-filter: blur(2px); font-family: 'Jost', sans-serif; }
  .flr-alert-overlay[hidden] { display: none; }
  .flr-alert-modal { width: 100%; max-width: 620px; max-height: 85vh; overflow-y: auto; background: var(--dark-2); border: 1px solid rgba(201,168,76,0.3); border-radius: 18px; box-shadow: 0 20px 60px rgba(0,0,0,0.5); padding: 30px 32px 26px; }
  .flr-alert-title { font-family: 'Cormorant Garamond', serif; font-size: 1.7rem; color: var(--gold-light); text-align: center; margin: 0; }
  .flr-alert-sub { text-align: center; font-size: 12.5px; color: var(--white-60); margin: 6px 0 22px; padding-bottom: 18px; border-bottom: 1px solid rgba(255,255,255,0.08); }
  .flr-alert-sector { display: inline-block; margin: 0 0 10px; padding: 4px 12px; border-radius: 100px; background: var(--gold-dim); color: var(--gold-light); font-size: 11px; font-weight: 600; letter-spacing: 0.06em; }
  .flr-alert-table { width: 100%; border-collapse: separate; border-spacing: 0; border: 1px solid rgba(255,255,255,0.08); border-radius: 12px; overflow: hidden; margin-bottom: 18px; font-size: 12.5px; }
  .flr-alert-table th { background: rgba(255,255,255,0.04); color: var(--gold); font-size: 10.5px; font-weight: 600; letter-spacing: 0.08em; text-transform: uppercase; text-align: left; padding: 11px 14px; }
  .flr-alert-table td { padding: 13px 14px; border-top: 1px solid rgba(255,255,255,0.06); color: var(--white-80); vertical-align: top; line-height: 1.5; }
  .flr-alert-table td:first-child { color: var(--white-60); font-weight: 600; text-transform: uppercase; font-size: 10.5px; letter-spacing: 0.06em; white-space: nowrap; }
  .flr-alert-table td.flr-alert-new { color: var(--gold-light); font-weight: 600; }
  .flr-alert-actions { display: flex; justify-content: flex-end; gap: 12px; margin-top: 6px; }
  .flr-alert-back { padding: 12px 26px; border-radius: 100px; border: 1px solid rgba(255,255,255,0.2); background: transparent; color: var(--white-80); font-size: 11.5px; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; text-decoration: none; }
  .flr-alert-back:hover { border-color: rgba(255,255,255,0.4); color: #fff; }
  .flr-alert-continue { padding: 12px 30px; border: none; border-radius: 100px; background: linear-gradient(90deg, #c9a84c, #e8c96b); color: var(--dark); font-family: 'Jost', sans-serif; font-size: 11.5px; font-weight: 800; letter-spacing: 0.08em; text-transform: uppercase; cursor: pointer; }
  @media (max-width: 560px) { .flr-alert-modal { padding: 24px 18px 20px; } .flr-alert-table td:first-child { white-space: normal; } }
</style>
@endpush

@section('content')
@php
  $fmtDuration = fn ($mins) => $mins ? intdiv((int) $mins, 60).'h '.((int) $mins % 60).'m' : '';
  // Fare-rule time bands arrive in hours ("4"–"96", "96"–"8760").
  $fmtBand = function ($h) {
      $h = (int) $h;
      return $h >= 720 ? round($h / 24).' days' : $h.' hrs';
  };
  $convenienceFee = max(0, $breakdown['customer_price'] - $breakdown['tripjack_total_price']);
  $policyColumns = [
      'CANCELLATION' => ['Cancellation Fee', null],
      'DATECHANGE' => ['Date Change Fee', null],
      'NO_SHOW' => ['No Show Fee', 'Post Departure'],
      'SEAT_CHARGEABLE' => ['Seat Chargeable Fee', null],
  ];
@endphp

@if(! empty($fareAlerts))
<div class="flr-alert-overlay" id="flrAlertOverlay" role="dialog" aria-modal="true" aria-labelledby="flrAlertTitle">
  <div class="flr-alert-modal">
    <h2 class="flr-alert-title" id="flrAlertTitle">Confirm to Proceed</h2>
    <p class="flr-alert-sub">{{ isset($fareAlerts['Fare']) ? 'The fare has changed' : 'Fare details have changed' }} since you searched. Please review the changes below.</p>

    @foreach($fareAlerts as $sector => $rows)
      @if($sector !== 'Fare')<span class="flr-alert-sector">{{ $sector }}</span>@endif
      <table class="flr-alert-table">
        <thead><tr><th></th><th>Old</th><th>New</th></tr></thead>
        <tbody>
          @foreach($rows as $row)
            <tr>
              <td>{{ $row['label'] }}</td>
              <td>{{ $row['old'] }}</td>
              <td class="flr-alert-new">{{ $row['new'] }}</td>
            </tr>
          @endforeach
        </tbody>
      </table>
    @endforeach

    <div class="flr-alert-actions">
      <a href="{{ $resultsUrl }}" class="flr-alert-back">Back</a>
      <button type="button" class="flr-alert-continue" id="flrAlertContinue">Continue</button>
    </div>
  </div>
</div>
@endif

@include('partials.flight-booking-steps', ['current' => 1])
@include('partials.flight-session-timer', ['expiresAt' => $sessionExpiresAt ?? null, 'resultsUrl' => $resultsUrl ?? null])

<div class="flr-wrap">
  <div>
    @if($errors->any())
    <div class="flr-error">
      @foreach($errors->all() as $error)
        <div>⚠️ {{ $error }}</div>
      @endforeach
    </div>
    @endif

    <div class="flr-section">
      <div class="flr-fd-head">
        <h2>Flight Details</h2>
        <a href="{{ $resultsUrl }}" class="flr-back-link">&lsaquo; Back to Search</a>
      </div>

      {{-- Review's tripInfos is a flat array of itineraries (0 = onward,
           1 = return / next leg), each with its own sI segment list. --}}
      @foreach($tripInfos as $tIdx => $trip)
        @php
          $segs = $trip['sI'] ?? [];
          $first = $segs[0] ?? null;
          $last = $segs ? end($segs) : null;
          $fare = $trip['totalPriceList'][0] ?? [];
          $adultFd = $fare['fd']['ADULT'] ?? [];
          $tripMins = collect($segs)->sum(fn ($s) => (int) ($s['duration'] ?? 0) + (int) ($s['cT'] ?? 0));
          $tripStops = max(0, count($segs) - 1) + collect($segs)->sum(fn ($s) => (int) ($s['stops'] ?? 0));
          $rT = (int) ($adultFd['rT'] ?? -1);
          $refundLabel = match ($rT) { 1 => 'Refundable', 2 => 'Partially Refundable', 0 => 'Non-Refundable', default => null };
          $legLabel = count($tripInfos) > 1 ? (($context['tripType'] ?? '') === 'return' ? ($tIdx === 0 ? 'Onward' : 'Return') : 'Flight '.($tIdx + 1)) : null;
          $paxLabels = ['ADULT' => 'Adult', 'CHILD' => 'Child', 'INFANT' => 'Infant'];
        @endphp
        @if($first)
        <div class="flr-trip">
          <div class="flr-trip-head">
            <div class="flr-trip-route">
              @if($legLabel)<span class="flr-trip-leg">{{ $legLabel }}</span>@endif
              <span class="flr-trip-cities">{{ $first['da']['city'] ?? $first['da']['code'] }}<span>&rarr;</span>{{ $last['aa']['city'] ?? $last['aa']['code'] }}</span>
              <span class="flr-trip-date">{{ \Carbon\Carbon::parse($first['dt'])->format('D, M jS Y') }}</span>
            </div>
            <div class="flr-chips">
              <span class="flr-chip">&#9719; {{ $fmtDuration($tripMins) }}</span>
              <span class="flr-chip">{{ $tripStops === 0 ? 'Non-Stop' : $tripStops.' Stop'.($tripStops > 1 ? 's' : '') }}</span>
              <span class="flr-chip">{{ ucwords(strtolower(str_replace('_', ' ', $adultFd['cc'] ?? $context['cabinClass']))) }}</span>
              @if($refundLabel)<span class="flr-chip {{ $rT === 0 ? 'nonrefund' : 'refund' }}">{{ $refundLabel }}</span>@endif
            </div>
          </div>

          @foreach($segs as $sIdx => $seg)
            @php $code = $seg['fD']['aI']['code'] ?? ''; @endphp
            <div class="flr-seg">
              <div class="flr-seg-airline">
                <img src="https://images.kiwi.com/airlines/64/{{ $code }}.png" alt="{{ $seg['fD']['aI']['name'] ?? $code }}" onerror="this.style.visibility='hidden'">
                <div>
                  <div class="flr-seg-aname">{{ $seg['fD']['aI']['name'] ?? $code }}</div>
                  <div class="flr-seg-fno">{{ $code }}-{{ $seg['fD']['fN'] ?? '' }}@if(!empty($seg['fD']['eT'])) &middot; Aircraft {{ $seg['fD']['eT'] }}@endif</div>
                </div>
                @if($sIdx === 0 && !empty($fare['fareIdentifier']))
                  <span class="flr-badge">{{ ($fare['fareIdentifier'] === 'TJ_FLEX' ? 'Flex' : ucwords(strtolower(str_replace('_', ' ', $fare['fareIdentifier'])))) }} Fare</span>
                @endif
              </div>

              <div class="flr-timeline">
                <div class="flr-point">
                  <div class="flr-point-time"><b>{{ \Carbon\Carbon::parse($seg['dt'])->format('H:i') }}</b><span>{{ $seg['da']['code'] ?? '' }}</span></div>
                  <div class="flr-point-date">{{ \Carbon\Carbon::parse($seg['dt'])->format('D, M j') }} &middot; {{ $seg['da']['city'] ?? '' }}</div>
                  <div class="flr-point-place">{{ $seg['da']['name'] ?? $seg['da']['code'] }}{{ !empty($seg['da']['country']) ? ', '.$seg['da']['country'] : '' }}</div>
                  @if(!empty($seg['da']['terminal']))<span class="flr-point-term">{{ $seg['da']['terminal'] }}</span>@endif
                </div>
                <div class="flr-mid">
                  <div class="flr-mid-dur">{{ $fmtDuration($seg['duration'] ?? 0) }}</div>
                  <div class="flr-mid-line"><span class="flr-mid-plane">&#9992;</span></div>
                  <div class="flr-mid-stop">{{ ($seg['stops'] ?? 0) > 0 ? $seg['stops'].' Technical Stop(s)' : 'Non-Stop' }}</div>
                </div>
                <div class="flr-point arr">
                  <div class="flr-point-time"><b>{{ \Carbon\Carbon::parse($seg['at'])->format('H:i') }}</b><span>{{ $seg['aa']['code'] ?? '' }}</span></div>
                  <div class="flr-point-date">{{ \Carbon\Carbon::parse($seg['at'])->format('D, M j') }} &middot; {{ $seg['aa']['city'] ?? '' }}</div>
                  <div class="flr-point-place">{{ $seg['aa']['name'] ?? $seg['aa']['code'] }}{{ !empty($seg['aa']['country']) ? ', '.$seg['aa']['country'] : '' }}</div>
                  @if(!empty($seg['aa']['terminal']))<span class="flr-point-term">{{ $seg['aa']['terminal'] }}</span>@endif
                </div>
              </div>
            </div>
            @if(!empty($seg['cT']) && isset($segs[$sIdx + 1]))
              <div class="flr-layover"><span>&#8634; Change of plane at {{ $seg['aa']['city'] ?? $seg['aa']['code'] }} &middot; {{ $fmtDuration($seg['cT']) }} layover</span></div>
            @endif
          @endforeach

          @if(($fare['fareIdentifier'] ?? '') === 'TJ_FLEX')
            {{-- Search doc: TJ_FLEX = no cancellation fee, but only if cancelled
                 at least 24 hours before departure; the flex charge (FTC) is
                 itself non-refundable. --}}
            <div class="flr-flex-note">&#10003; <b>Zero cancellation fee</b> if you cancel at least 24 hours before departure. The flex charge included in this fare is non-refundable.</div>
          @endif

          @php $bagRows = collect($fare['fd'] ?? [])->filter(fn ($fd) => ! empty($fd['bI'])); @endphp
          @if($bagRows->isNotEmpty())
            <div class="flr-baggage">
              <span class="flr-baggage-label">Baggage</span>
              @foreach($bagRows as $paxType => $fd)
                <span class="flr-bag">
                  <span class="flr-bag-icon">&#128188;&#xFE0E;</span>
                  <span><small>{{ $paxLabels[$paxType] ?? ucfirst(strtolower($paxType)) }} &middot; Check-in</small><b>{{ $fd['bI']['iB'] ?? '—' }}</b></span>
                </span>
                <span class="flr-bag">
                  <span class="flr-bag-icon">&#128092;&#xFE0E;</span>
                  <span><small>{{ $paxLabels[$paxType] ?? ucfirst(strtolower($paxType)) }} &middot; Cabin</small><b>{{ $fd['bI']['cB'] ?? '—' }}</b></span>
                </span>
              @endforeach
            </div>
          @endif
        </div>
        @endif
      @endforeach

      @if($fareRules)
        <button type="button" class="flr-rules-toggle" id="flrRulesToggle" aria-expanded="false" aria-controls="flrRules">
          <span><span class="flr-rules-toggle-title">Fare Rules</span><span class="flr-rules-toggle-sub">Cancellation, date change &amp; no-show charges</span></span>
          <span id="flrRulesIcon">+</span>
        </button>
        <div class="flr-rules" id="flrRules" hidden>
          @foreach($fareRules as $sector => $rule)
            @php
              $tfr = $rule['tfr'] ?? [];
              $available = array_intersect_key($policyColumns, $tfr);
              // Doc: with no mini-rule from the airline, a plain-text
              // ("Cat 16") rule comes back under miscInfo instead.
              $miscLines = collect(is_array($rule['miscInfo'] ?? null) ? $rule['miscInfo'] : [$rule['miscInfo'] ?? null])
                  ->filter(fn ($l) => is_string($l) && trim($l) !== '')
                  ->map(fn ($l) => trim(preg_replace('/\s+/', ' ', str_replace('__nls__', ' ', $l))));
            @endphp
            @if(! $available && $miscLines->isNotEmpty())
            <div class="flr-rules-block">
              <div class="flr-rules-top"><span class="flr-rules-sector">{{ str_replace('-', ' → ', $sector) }}</span></div>
              <div class="flr-rules-misc">
                @foreach($miscLines as $line)<p>{{ $line }}</p>@endforeach
              </div>
            </div>
            @endif
            @if($available)
            <div class="flr-rules-block">
              <div class="flr-rules-top">
                <span class="flr-rules-sector">{{ str_replace('-', ' → ', $sector) }}</span>
                <p class="flr-rules-hint">* To view charges, click on the fee sections below.</p>
              </div>
              <div class="flr-rules-scroll">
                <table class="flr-rules-table">
                  <thead>
                    <tr>
                      <th class="flr-rules-tf">Time Frame<small>From first scheduled flight departure</small></th>
                      @foreach($available as $key => [$label, $sub])
                        <th><button type="button" class="flr-rules-tab {{ $loop->first ? 'active' : '' }}" data-policy="{{ $key }}" aria-pressed="{{ $loop->first ? 'true' : 'false' }}">{{ $label }}@if($sub)<small>{{ $sub }}</small>@endif</button></th>
                      @endforeach
                    </tr>
                  </thead>
                  @foreach($available as $key => $meta)
                    <tbody data-policy="{{ $key }}" @unless($loop->first) hidden @endunless>
                      @foreach($tfr[$key] as $band)
                        @php
                          // TripJack's text uses "__nls__" as a line break and
                          // often ends with a generic "Please Note: …"
                          // disclaimer — shown as a quieter line of its own.
                          $info = trim(preg_replace('/\s+/', ' ', str_replace('__nls__', ' ', $band['policyInfo'] ?? '')));
                          $note = null;
                          if (preg_match('/^(.*?)\s*(Please Note:.*)$/i', $info, $m)) { $info = trim($m[1]); $note = trim($m[2]); }
                          $total = (float) ($band['amount'] ?? 0) + (float) ($band['additionalFee'] ?? 0);
                        @endphp
                        <tr>
                          @if(isset($band['st'], $band['et']))
                            <td>{{ $fmtBand($band['st']) }} to {{ $fmtBand($band['et']) }}<small>{{ $key === 'NO_SHOW' ? 'from departure' : 'before departure' }}</small></td>
                          @else
                            {{-- Doc: a band is either st/et OR a policy period (pp), never both. --}}
                            <td>{{ ['BEFORE_DEPARTURE' => 'Before departure', 'AFTER_DEPARTURE' => 'After departure', 'DEFAULT' => 'Any time'][$band['pp'] ?? ''] ?? '—' }}</td>
                          @endif
                          <td colspan="{{ count($available) }}">
                            @if(isset($band['amount']))
                              <span class="flr-rules-fee {{ $total == 0 ? 'free' : '' }}">{{ $total == 0 ? 'No fee' : '₹'.number_format($total, 2) }}</span>
                              @if(!empty($band['additionalFee']))<span class="flr-rules-info">Includes &#8377;{{ number_format($band['additionalFee'], 2) }} additional fee</span>@endif
                            @endif
                            @if($info)<span class="flr-rules-info">{{ $info }}</span>@endif
                            @if($note)<span class="flr-rules-note">{{ $note }}</span>@endif
                            @if(! isset($band['amount']) && ! $info && ! $note)&mdash;@endif
                          </td>
                        </tr>
                      @endforeach
                    </tbody>
                  @endforeach
                </table>
              </div>
            </div>
            @endif
          @endforeach
          <div class="flr-rules-notes">
            <span>&#9432;</span>
            <span>The airline fee is indicative and depends on when you cancel or change, as per the airline's fare rules. Fees shown are per passenger, per sector. GST and other applicable charges may be added.</span>
          </div>
        </div>
      @endif
    </div>
  </div>

  <aside class="flr-summary">
    <h3>Fare Summary</h3>
    <p class="flr-sum-pax">{{ $context['adults'] }} Adult{{ $context['adults'] > 1 ? 's' : '' }}@if($context['children']), {{ $context['children'] }} Child{{ $context['children'] > 1 ? 'ren' : '' }}@endif@if($context['infants']), {{ $context['infants'] }} Infant{{ $context['infants'] > 1 ? 's' : '' }}@endif &middot; {{ ucwords(strtolower(str_replace('_', ' ', $context['cabinClass']))) }}</p>

    <div class="flr-sum-row"><span>Base fare</span><span>&#8377;{{ number_format($breakdown['base_fare'], 2) }}</span></div>
    <div class="flr-sum-row">
      <span><button type="button" class="flr-sum-toggle" id="flrTaxToggle" aria-expanded="false" aria-controls="flrTaxBreakdown">Taxes and fees</button></span>
      <span>&#8377;{{ number_format($breakdown['airline_taxes'] + $convenienceFee, 2) }}</span>
    </div>
    <div class="flr-sum-sub" id="flrTaxBreakdown" hidden>
      <div><span>Airline taxes &amp; surcharges</span><span>&#8377;{{ number_format($breakdown['airline_taxes'], 2) }}</span></div>
      <div><span>Convenience fee</span><span>&#8377;{{ number_format($convenienceFee, 2) }}</span></div>
    </div>
    <div class="flr-line total"><span>Amount to Pay</span><span>&#8377;{{ number_format($breakdown['customer_price'], 2) }}</span></div>

    <a href="{{ route('flights.passengers.show', ['booking' => $response['bookingId']]) }}" class="flr-submit">Continue to Passenger Details &rarr;</a>
    <p class="flr-note">Seats, extra baggage and meals can be added on the next step.</p>
  </aside>
</div>
@endsection

@push('scripts')
<script>
(function () {
  var alertContinue = document.getElementById('flrAlertContinue');
  if (alertContinue) {
    alertContinue.addEventListener('click', function () {
      document.getElementById('flrAlertOverlay').hidden = true;
    });
  }

  function bindToggle(btn, panel, onChange) {
    if (!btn || !panel) return;
    btn.addEventListener('click', function () {
      var open = panel.hidden;
      panel.hidden = !open;
      btn.setAttribute('aria-expanded', open ? 'true' : 'false');
      if (onChange) onChange(open);
    });
  }

  bindToggle(document.getElementById('flrRulesToggle'), document.getElementById('flrRules'), function (open) {
    document.getElementById('flrRulesIcon').textContent = open ? '−' : '+';
  });
  bindToggle(document.getElementById('flrTaxToggle'), document.getElementById('flrTaxBreakdown'));

  document.querySelectorAll('.flr-rules-block').forEach(function (block) {
    block.querySelectorAll('.flr-rules-tab').forEach(function (tab) {
      tab.addEventListener('click', function () {
        block.querySelectorAll('.flr-rules-tab').forEach(function (t) {
          t.classList.toggle('active', t === tab);
          t.setAttribute('aria-pressed', t === tab ? 'true' : 'false');
        });
        block.querySelectorAll('tbody[data-policy]').forEach(function (body) {
          body.hidden = body.dataset.policy !== tab.dataset.policy;
        });
      });
    });
  });
})();
</script>
@endpush
