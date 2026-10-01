@extends('layouts.frontend')

@section('meta_title', 'Add Seats, Meals & Baggage — ' . $booking->flight_route . ' | TYT Luxe')

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com" />
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,500;0,600&family=Jost:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
<style>
  :root {
    --gold: #c9a84c; --gold-light: #e8c96b;
    --dark: #0d0d0d; --dark-2: #141414; --dark-3: #1c1c1c;
    --white-80: rgba(255,255,255,0.80); --white-60: rgba(255,255,255,0.60); --white-30: rgba(255,255,255,0.30);
    --green: #4ade80; --red: #f3a3a3;
  }
  body { background: var(--dark); }
  * { box-sizing: border-box; }

  .fx-wrap { max-width: 980px; margin: 0 auto; padding: 100px 24px 140px; font-family: 'Jost', sans-serif; }
  .fx-eyebrow { font-size: 10.5px; font-weight: 600; letter-spacing: 0.22em; text-transform: uppercase; color: var(--gold); margin-bottom: 10px; }
  .fx-title { font-family: 'Cormorant Garamond', serif; font-size: clamp(1.8rem, 3.5vw, 2.4rem); color: #fff; margin-bottom: 6px; }
  .fx-sub { color: var(--white-60); font-size: 13px; margin-bottom: 34px; }
  .fx-error { margin-bottom: 24px; padding: 14px 18px; border-radius: 12px; background: rgba(220,80,80,0.08); border: 1px solid rgba(220,80,80,0.3); color: var(--red); font-size: 13px; }

  .fx-segment { background: var(--dark-2); border: 1px solid rgba(255,255,255,0.08); border-radius: 14px; padding: 22px; margin-bottom: 22px; }
  .fx-seg-head { display: flex; align-items: center; gap: 14px; margin-bottom: 20px; padding-bottom: 16px; border-bottom: 1px solid rgba(255,255,255,0.07); }
  .fx-seg-airline img { width: 34px; height: 34px; object-fit: contain; border-radius: 6px; background: #fff; padding: 3px; }
  .fx-seg-title { color: #fff; font-weight: 600; font-size: 14px; }
  .fx-seg-sub { color: var(--white-30); font-size: 11.5px; margin-top: 2px; }
  .fx-seg-route { margin-left: auto; text-align: right; color: var(--white-60); font-size: 12.5px; }

  .fx-traveller { margin-bottom: 22px; padding-bottom: 22px; border-bottom: 1px dashed rgba(255,255,255,0.07); }
  .fx-traveller:last-child { margin-bottom: 0; padding-bottom: 0; border-bottom: none; }
  .fx-traveller-name { color: var(--gold-light); font-weight: 600; font-size: 13px; margin-bottom: 14px; }

  .fx-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px; }
  @media (max-width: 640px) { .fx-row { grid-template-columns: 1fr; } }
  .fx-field label { display: block; font-size: 10px; font-weight: 600; letter-spacing: 0.08em; text-transform: uppercase; color: var(--gold); margin-bottom: 6px; }
  .fx-field select { width: 100%; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.12); border-radius: 8px; padding: 10px 12px; color: #fff; font-family: 'Jost', sans-serif; font-size: 13px; outline: none; }
  .fx-field select option { background: #1a1a1a; }
  .fx-already { font-size: 11.5px; color: var(--green); margin-top: 6px; }

  .fx-seatmap-toggle { display: inline-block; font-size: 11.5px; color: var(--gold-light); cursor: pointer; text-decoration: underline; text-underline-offset: 2px; margin-bottom: 12px; }
  .fx-seatmap { display: none; margin-top: 10px; }
  .fx-seatmap.open { display: block; }
  .fx-seatgrid { display: grid; gap: 5px; margin-bottom: 12px; }
  .fx-seat { width: 30px; height: 30px; border-radius: 6px; font-size: 9.5px; display: flex; align-items: center; justify-content: center; cursor: pointer; border: 1px solid rgba(255,255,255,0.15); background: #0d0d0d; color: var(--white-60); }
  .fx-seat.booked { background: #2a2a2a; color: #555; cursor: not-allowed; border-color: transparent; }
  .fx-seat.selected { background: var(--gold); color: var(--dark); border-color: var(--gold); font-weight: 700; }
  .fx-seat-empty { visibility: hidden; }
  .fx-seat-legend { display: flex; gap: 16px; font-size: 11px; color: var(--white-30); margin-bottom: 4px; }
  .fx-seat-legend span { display: inline-flex; align-items: center; gap: 5px; }
  .fx-seat-legend i { width: 12px; height: 12px; border-radius: 3px; display: inline-block; }

  .fx-summary-bar { position: sticky; bottom: 20px; margin-top: 26px; display: flex; align-items: center; justify-content: space-between; gap: 20px; background: var(--dark-3); border: 1px solid rgba(201,168,76,0.3); border-radius: 100px; padding: 12px 12px 12px 26px; box-shadow: 0 20px 50px rgba(0,0,0,0.5); }
  .fx-summary-label { font-size: 10.5px; color: var(--white-60); text-transform: uppercase; letter-spacing: 0.06em; }
  .fx-summary-val { font-size: 18px; font-weight: 700; color: var(--gold-light); }
  .fx-pay-btn { padding: 15px 34px; border: none; border-radius: 100px; background: linear-gradient(90deg, #c9a84c, #e8c96b); color: var(--dark); font-size: 12.5px; font-weight: 800; letter-spacing: 0.1em; text-transform: uppercase; cursor: pointer; }
  .fx-empty { text-align: center; padding: 60px 20px; color: var(--white-60); }
</style>
@endpush

@section('content')
<div class="fx-wrap">
  <p class="fx-eyebrow">Manage Your Flight</p>
  <h1 class="fx-title">Add Seats, Meals &amp; Baggage</h1>
  <p class="fx-sub">{{ $booking->flight_route }} &middot; Booking {{ $booking->reference }}</p>

  @if($errors->any())
    <div class="fx-error">⚠️ {{ $errors->first() }}</div>
  @endif
  @if(session('booking_error'))
    <div class="fx-error">⚠️ {{ session('booking_error') }}</div>
  @endif

  @php
    // Anything the guest can actually pick — baggage (first segment of a
    // journey), a meal not yet added, or a seat map for someone without one.
    $hasAnyOption = collect($tripGroups ?? [])->contains(fn ($g) => collect($g['segments'])->contains(fn ($s, $i) =>
        collect($s['travellers'])->contains(fn ($t) =>
            ($i === 0 && count($t['baggageOptions']))
            || (empty($t['alreadyMeal']) && count($t['mealOptions']))
            || (empty($t['alreadySeat']) && count($s['seatMap']['sInfo'] ?? []))
        )
    ));
  @endphp

  @if(! $hasAnyOption)
    <div class="fx-empty">No add-ons are available for this booking right now.</div>
  @else
    <form method="POST" action="{{ route('flights.extras.submit', $booking->reference) }}" id="fxForm">
      @csrf

      @foreach($tripGroups as $group)
        @foreach($group['segments'] as $segIdx => $segment)
          <div class="fx-segment">
            <div class="fx-seg-head">
              <div class="fx-seg-airline">
                <img src="https://images.kiwi.com/airlines/64/{{ $segment['airlineCode'] }}.png" onerror="this.onerror=null;this.src='https://ui-avatars.com/api/?name={{ urlencode($segment['airlineName'] ?: $segment['airlineCode']) }}&background=1a1a1a&color=C9A84C&size=64'" alt="">
              </div>
              <div>
                <div class="fx-seg-title">{{ $segment['airlineName'] ?: $segment['airlineCode'] }} {{ $segment['flightNo'] }}</div>
                <div class="fx-seg-sub">{{ $segment['from'] }} &rarr; {{ $segment['to'] }}</div>
              </div>
              <div class="fx-seg-route">
                {{ \Carbon\Carbon::parse($segment['depTime'])->format('D, M j · H:i') }}
              </div>
            </div>

            @foreach($segment['travellers'] as $traveller)
              <div class="fx-traveller">
                <div class="fx-traveller-name">{{ $traveller['name'] }}</div>

                <div class="fx-row">
                  @if($segIdx === 0 && count($traveller['baggageOptions']))
                    <div class="fx-field">
                      <label>Extra Baggage</label>
                      <select name="selections[{{ $segment['id'] }}][{{ $traveller['id'] }}][baggage]">
                        <option value="">No extra baggage</option>
                        @foreach($traveller['baggageOptions'] as $opt)
                          @if(isset($opt['code']))
                            <option value="{{ $opt['code'] }}">{{ $opt['desc'] ?? $opt['code'] }} — &#8377;{{ number_format($opt['amount'] ?? 0) }}</option>
                          @endif
                        @endforeach
                      </select>
                      @if(count($traveller['alreadyBaggage']))
                        <div class="fx-already">✓ Already added: {{ collect($traveller['alreadyBaggage'])->pluck('code')->implode(', ') }}</div>
                      @endif
                    </div>
                  @elseif(count($traveller['baggageOptions']))
                    <div class="fx-field">
                      <label>Extra Baggage</label>
                      <div class="fx-already" style="margin-top:0;color:var(--white-30);">Selected on the first segment applies to this connecting flight too.</div>
                    </div>
                  @endif

                  @if(! empty($traveller['alreadyMeal']))
                    {{-- Doc: a meal can only be added once per pax per segment. --}}
                    <div class="fx-field">
                      <label>Meal</label>
                      <div class="fx-already" style="margin-top:0;">✓ Already added: {{ collect($traveller['alreadyMeal'])->map(fn ($m) => $m['desc'] ?? $m['code'] ?? '')->filter()->implode(', ') ?: 'Meal' }}</div>
                    </div>
                  @elseif(count($traveller['mealOptions']))
                    <div class="fx-field">
                      <label>Meal</label>
                      <select name="selections[{{ $segment['id'] }}][{{ $traveller['id'] }}][meal]">
                        <option value="">No meal</option>
                        @foreach($traveller['mealOptions'] as $opt)
                          @if(isset($opt['code']))
                            <option value="{{ $opt['code'] }}">{{ $opt['desc'] ?? $opt['code'] }} — {{ ($opt['amount'] ?? 0) > 0 ? '₹'.number_format($opt['amount']) : 'Free' }}</option>
                          @endif
                        @endforeach
                      </select>
                    </div>
                  @endif
                </div>

                @if(! empty($traveller['alreadySeat']))
                  {{-- Doc: a seat can only be added once per pax per segment. --}}
                  <div class="fx-already">✓ Seat already booked: {{ collect($traveller['alreadySeat'])->map(fn ($s) => $s['seatNo'] ?? $s['desc'] ?? $s['code'] ?? '')->filter()->implode(', ') ?: 'Seat' }}</div>
                @elseif($segment['seatMap'] && count($segment['seatMap']['sInfo'] ?? []))
                  @php $seatMapId = 'fxSeat_'.$segment['id'].'_'.$traveller['id']; @endphp
                  <span class="fx-seatmap-toggle" data-toggle="{{ $seatMapId }}">Choose a seat &darr;</span>
                  <div class="fx-seatmap" id="{{ $seatMapId }}">
                    <div class="fx-seat-legend">
                      <span><i style="background:#0d0d0d;border:1px solid rgba(255,255,255,0.15)"></i> Available</span>
                      <span><i style="background:#2a2a2a"></i> Booked</span>
                      <span><i style="background:var(--gold)"></i> Selected</span>
                    </div>
                    <div class="fx-seatgrid" style="grid-template-columns: repeat({{ $segment['seatMap']['sData']['column'] ?? 6 }}, 30px);">
                      @php
                        $cols = $segment['seatMap']['sData']['column'] ?? 6;
                        $byPos = [];
                        foreach ($segment['seatMap']['sInfo'] as $s) {
                            $byPos[($s['seatPosition']['row'] ?? 0).'-'.($s['seatPosition']['column'] ?? 0)] = $s;
                        }
                        $maxRow = collect($segment['seatMap']['sInfo'])->max(fn ($s) => $s['seatPosition']['row'] ?? 0);
                      @endphp
                      @for($r = 1; $r <= $maxRow; $r++)
                        @for($c = 1; $c <= $cols; $c++)
                          @php $s = $byPos[$r.'-'.$c] ?? null; @endphp
                          @if($s)
                            <div class="fx-seat {{ ($s['isBooked'] ?? false) ? 'booked' : '' }}" data-seat="{{ $s['code'] }}" data-target="selections[{{ $segment['id'] }}][{{ $traveller['id'] }}][seat]" title="{{ $s['seatNo'] }} — ₹{{ number_format($s['amount'] ?? 0) }}">{{ $s['seatNo'] }}</div>
                          @else
                            <div class="fx-seat fx-seat-empty"></div>
                          @endif
                        @endfor
                      @endfor
                    </div>
                  </div>
                  <input type="hidden" name="selections[{{ $segment['id'] }}][{{ $traveller['id'] }}][seat]" id="fxSeatInput_{{ $segment['id'] }}_{{ $traveller['id'] }}">
                @endif
              </div>
            @endforeach
          </div>
        @endforeach
      @endforeach

      <div class="fx-summary-bar">
        <div>
          <div class="fx-summary-label">Estimated Total</div>
          <div class="fx-summary-val" id="fxTotal">&#8377;0</div>
        </div>
        <button type="submit" class="fx-pay-btn">Continue to Pay</button>
      </div>
    </form>
  @endif
</div>
@endsection

@push('scripts')
<script>
(function () {
  var form = document.getElementById('fxForm');
  if (!form) return;

  document.querySelectorAll('.fx-seatmap-toggle').forEach(function (t) {
    t.addEventListener('click', function () {
      document.getElementById(t.dataset.toggle).classList.toggle('open');
    });
  });

  var priceMap = {};
  document.querySelectorAll('.fx-field select').forEach(function (sel) {
    Array.prototype.slice.call(sel.options).forEach(function (opt) {
      var m = opt.textContent.match(/[₹]([\d,]+)/);
      if (opt.value) priceMap[sel.name + '::' + opt.value] = m ? parseInt(m[1].replace(/,/g, ''), 10) : 0;
    });
  });

  document.querySelectorAll('.fx-seat[data-seat]').forEach(function (seat) {
    if (seat.classList.contains('booked')) return;
    var price = (seat.title.match(/[₹]([\d,]+)/) || [])[1];
    priceMap[seat.dataset.target + '::' + seat.dataset.seat] = price ? parseInt(price.replace(/,/g, ''), 10) : 0;

    seat.addEventListener('click', function () {
      var input = document.querySelector('input[name="' + seat.dataset.target + '"]');
      var group = seat.closest('.fx-seatmap');
      if (seat.classList.contains('selected')) {
        seat.classList.remove('selected');
        input.value = '';
      } else {
        group.querySelectorAll('.fx-seat.selected').forEach(function (s) { s.classList.remove('selected'); });
        seat.classList.add('selected');
        input.value = seat.dataset.seat;
      }
      updateTotal();
    });
  });

  function updateTotal() {
    var total = 0;
    document.querySelectorAll('.fx-field select').forEach(function (sel) {
      if (sel.value) total += priceMap[sel.name + '::' + sel.value] || 0;
    });
    document.querySelectorAll('input[type="hidden"][id^="fxSeatInput_"]').forEach(function (input) {
      if (input.value) total += priceMap[input.name + '::' + input.value] || 0;
    });
    document.getElementById('fxTotal').innerHTML = '&#8377;' + total.toLocaleString('en-IN');
  }

  document.querySelectorAll('.fx-field select').forEach(function (sel) {
    sel.addEventListener('change', updateTotal);
  });
})();
</script>
@endpush
