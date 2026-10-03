{{-- Flight details cards — one per itinerary in Review's tripInfos (a flat
     array: 0 = onward, 1 = return / next leg, each with its own sI segment
     list). Used by Step 1 (Flight Itinerary) and Step 3 (Review).
     Needs: $tripInfos, $context. --}}
@once
@push('styles')
<style>
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
</style>
@endpush
@endonce
@php
  $tripMinsFmt = fn ($mins) => $mins ? intdiv((int) $mins, 60).'h '.((int) $mins % 60).'m' : '';
  $paxLabels = ['ADULT' => 'Adult', 'CHILD' => 'Child', 'INFANT' => 'Infant'];
@endphp
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
        <span class="flr-chip">&#9719; {{ $tripMinsFmt($tripMins) }}</span>
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
            <div class="flr-mid-dur">{{ $tripMinsFmt($seg['duration'] ?? 0) }}</div>
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
        <div class="flr-layover"><span>&#8634; Change of plane at {{ $seg['aa']['city'] ?? $seg['aa']['code'] }} &middot; {{ $tripMinsFmt($seg['cT']) }} layover</span></div>
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
