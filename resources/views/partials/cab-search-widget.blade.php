{{--
  Cab search bar — same look as the flight search bar (partials.flight-search-widget).
  Used on /cabs and as "Modify search" on the results page. $q: the current
  search's query string, to prefill the fields.
--}}
@php
  $q = $q ?? request()->query();
  $journey = $q['journey'] ?? 'airport_transfer';
  $trip = $q['trip'] ?? 'oneway';
  $minPickup = now()->addMinutes(\App\Services\CabBookingService::MIN_LEAD_MINUTES)->format('Y-m-d\TH:i');
  $defaultPickup = now()->addDays(2)->setTime(9, 0)->format('Y-m-d\TH:i');
@endphp
<form class="cab-sb" method="GET" action="{{ route('cabs.search') }}" id="cabSearchForm" novalidate data-suggest="{{ request()->routeIs('cabs') && request()->filled('from') && ! request()->filled('from_id') ? '1' : '0' }}">
  <div class="cab-sb-tabs" role="radiogroup" aria-label="Type of cab">
    @foreach(\App\Services\CabBookingService::JOURNEY_TYPES as $key => $label)
      <label class="cab-sb-tab">
        <input type="radio" name="journey" value="{{ $key }}" @checked($journey === $key)>
        <span>{{ $label }}</span>
      </label>
    @endforeach
  </div>

  <div class="cab-sb-bar">
    <div class="cab-sb-row">
      <div class="cab-sb-field cab-sb-place">
        <label class="cab-sb-flabel" for="cabFrom">Pickup from</label>
        <input type="text" id="cabFrom" name="from" class="cab-sb-input" value="{{ $q['from'] ?? '' }}" placeholder="Airport, hotel or area" autocomplete="off" required>
        <input type="hidden" name="from_id" value="{{ $q['from_id'] ?? '' }}">
        <div class="cab-sb-ac" role="listbox" aria-label="Pickup suggestions"></div>
      </div>
      <button type="button" class="cab-sb-swap" aria-label="Swap pickup and drop" title="Swap pickup and drop"><i class="fa-solid fa-arrow-right-arrow-left" aria-hidden="true"></i></button>
      <div class="cab-sb-field cab-sb-place">
        <label class="cab-sb-flabel" for="cabTo">Drop to</label>
        <input type="text" id="cabTo" name="to" class="cab-sb-input" value="{{ $q['to'] ?? '' }}" placeholder="Where are you going?" autocomplete="off" required>
        <input type="hidden" name="to_id" value="{{ $q['to_id'] ?? '' }}">
        <div class="cab-sb-ac" role="listbox" aria-label="Drop suggestions"></div>
      </div>
      <div class="cab-sb-field cab-dt" data-min="{{ $minPickup }}">
        <span class="cab-sb-flabel" id="cabPickupLbl">Pickup date &amp; time</span>
        <button type="button" class="cab-dt-btn" id="cabPickupBtn" aria-labelledby="cabPickupLbl cabPickupBtn" aria-haspopup="dialog"></button>
        <input type="hidden" id="cabPickup" name="pickup" value="{{ $q['pickup'] ?? $defaultPickup }}">
      </div>
      <div class="cab-sb-field cab-dt cab-sb-return {{ $trip === 'roundtrip' ? '' : 'is-off' }}" data-min="{{ $minPickup }}" data-after="cabPickup">
        <span class="cab-sb-flabel" id="cabReturnLbl">Return</span>
        <button type="button" class="cab-dt-btn" id="cabReturnBtn" aria-labelledby="cabReturnLbl cabReturnBtn" aria-haspopup="dialog"></button>
        <input type="hidden" id="cabReturn" name="return" value="{{ $q['return'] ?? '' }}" {{ $trip === 'roundtrip' ? '' : 'disabled' }}>
        <button type="button" class="cab-sb-addreturn" @if($trip === 'roundtrip') hidden @endif>+ Add return</button>
      </div>
      @php $pax = max(1, min(\App\Services\CabBookingService::MAX_PASSENGERS, (int) ($q['passengers'] ?? 2))); $bags = max(0, min(20, (int) ($q['bags'] ?? $pax))); @endphp
      <div class="cab-sb-field cab-sb-pax">
        <span class="cab-sb-flabel" id="cabPaxLbl">Passengers &amp; bags</span>
        <button type="button" class="cab-dt-btn cab-pax-btn" aria-labelledby="cabPaxLbl" aria-haspopup="dialog" aria-expanded="false"></button>
        <input type="hidden" name="passengers" value="{{ $pax }}">
        <input type="hidden" name="bags" value="{{ $bags }}">
        <div class="cab-pax-pop" role="dialog" aria-label="Passengers and bags">
          <div class="cab-pax-row" data-key="passengers" data-min="1" data-max="{{ \App\Services\CabBookingService::MAX_PASSENGERS }}">
            <div><div class="cab-pax-name">Passengers</div><div class="cab-pax-sub">Up to {{ \App\Services\CabBookingService::MAX_PASSENGERS }}</div></div>
            <div class="cab-pax-step"><button type="button" data-step="-1" aria-label="Fewer passengers">−</button><span aria-live="polite"></span><button type="button" data-step="1" aria-label="More passengers">+</button></div>
          </div>
          <div class="cab-pax-row" data-key="bags" data-min="0" data-max="20">
            <div><div class="cab-pax-name">Bags</div><div class="cab-pax-sub">Check-in size suitcases</div></div>
            <div class="cab-pax-step"><button type="button" data-step="-1" aria-label="Fewer bags">−</button><span aria-live="polite"></span><button type="button" data-step="1" aria-label="More bags">+</button></div>
          </div>
          <button type="button" class="cab-dt-done cab-pax-done">Done</button>
        </div>
      </div>
      <div class="cab-sb-actions">
        <input type="hidden" name="trip" value="{{ $trip }}">
        <button type="submit" class="cab-sb-search-btn">Search Cabs</button>
      </div>
    </div>
    <div class="cab-sb-row2">
      <span>Pickup at least {{ \App\Services\CabBookingService::MIN_LEAD_MINUTES / 60 }} hours from now</span><span aria-hidden="true">·</span>
      <span>Up to {{ \App\Services\CabBookingService::MAX_PASSENGERS }} passengers</span><span aria-hidden="true">·</span>
      <span>Chauffeur-driven, all-inclusive pricing</span>
    </div>
    <p class="cab-sb-error" role="alert" hidden></p>
  </div>
</form>

@once
@push('styles')
<style>
.cab-sb{font-family:'Poppins',sans-serif}
.cab-sb-tabs{display:flex;gap:26px;margin-bottom:10px;padding-left:6px;flex-wrap:wrap}
.cab-sb-tab{cursor:pointer;font-size:12.5px;font-weight:600;letter-spacing:.5px;color:rgba(255,255,255,0.6)}
.cab-sb-tab input{position:absolute;opacity:0;pointer-events:none}
.cab-sb-tab span{display:inline-block;padding:4px 0;border-bottom:2px solid transparent;transition:color .2s,border-color .2s}
.cab-sb-tab input:checked+span{color:#fff;border-color:#C9A84C}
.cab-sb-tab input:focus-visible+span{outline:2px solid #C9A84C;outline-offset:4px}
.cab-sb-bar{background:#141414;border:1px solid rgba(201,168,76,0.25);border-radius:14px;box-shadow:0 20px 50px rgba(0,0,0,0.45);padding:16px}
.cab-sb-row{display:flex;align-items:stretch}
.cab-sb-field{flex:1;min-width:0;padding:8px 18px;position:relative;border-radius:10px;transition:background .2s,box-shadow .2s}
.cab-sb-field:focus-within{background:rgba(201,168,76,0.06);box-shadow:inset 0 -2px 0 #C9A84C}
.cab-sb-field+.cab-sb-field{border-left:1px solid rgba(255,255,255,0.08)}
.cab-sb-place{flex:1.35}
.cab-sb-pax{flex:.85}
.cab-sb-flabel{display:block;font-size:9.5px;font-weight:600;letter-spacing:1.5px;text-transform:uppercase;color:#C9A84C;margin-bottom:5px}
.cab-sb-input{width:100%;background:transparent;border:none;color:#fff;font-family:'Poppins',sans-serif;font-size:14px;outline:none;padding:0;-webkit-appearance:none;appearance:none}
.cab-sb-input::placeholder{color:#777}
select.cab-sb-input option{background:#1a1a1a}
.cab-sb-return.is-off .cab-dt-btn{display:none}
/* Date & time picker (black and gold, like the flight search calendar) */
.cab-dt-btn{display:block;width:100%;text-align:left;background:none;border:none;padding:0;color:#fff;font-family:'Poppins',sans-serif;font-size:13px;cursor:pointer;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.cab-dt-btn.is-empty{color:#C9A84C;opacity:.8}
.cab-dt-pop{position:absolute;top:calc(100% + 10px);left:0;width:300px;background:#1a1a1a;border:1px solid rgba(201,168,76,0.3);border-radius:14px;box-shadow:0 20px 50px rgba(0,0,0,0.55);z-index:50;padding:16px;display:none;font-family:'Poppins',sans-serif;cursor:default}
.cab-dt-pop.open{display:block}
.cab-dt-head{display:flex;align-items:center;justify-content:space-between;margin-bottom:12px}
.cab-dt-month{font-size:13px;font-weight:700;color:#C9A84C;letter-spacing:.3px}
.cab-dt-nav{width:28px;height:28px;border-radius:50%;background:#0d0d0d;border:1px solid rgba(255,255,255,0.15);color:#C9A84C;font-size:14px;cursor:pointer;display:flex;align-items:center;justify-content:center;padding:0}
.cab-dt-nav:hover:not(:disabled){border-color:#C9A84C}
.cab-dt-nav:disabled{opacity:.25;cursor:not-allowed}
.cab-dt-grid{display:grid;grid-template-columns:repeat(7,1fr);gap:3px;text-align:center}
.cab-dt-dow{font-size:9.5px;font-weight:600;letter-spacing:.5px;text-transform:uppercase;color:#777;padding:4px 0}
.cab-dt-day{padding:7px 0;border:1px solid transparent;border-radius:8px;background:none;font-family:inherit;font-size:12px;color:#ddd;cursor:pointer}
.cab-dt-day:hover:not(:disabled){background:rgba(201,168,76,0.15)}
.cab-dt-day.today{border-color:rgba(201,168,76,0.6);color:#C9A84C}
.cab-dt-day.sel{background:#C9A84C;color:#0a0a0a;font-weight:700}
.cab-dt-day:disabled{color:#3d3d3d;cursor:not-allowed}
.cab-dt-time{display:flex;align-items:center;gap:8px;margin-top:14px;padding-top:14px;border-top:1px solid rgba(255,255,255,0.08)}
.cab-dt-time-lbl{font-size:10px;font-weight:600;letter-spacing:1.5px;text-transform:uppercase;color:#C9A84C;margin-right:auto}
.cab-dt-time select{background:#0d0d0d;border:1px solid rgba(255,255,255,0.15);border-radius:8px;color:#fff;font-family:inherit;font-size:13px;padding:7px 8px;cursor:pointer;outline:none}
.cab-dt-time select:focus{border-color:#C9A84C}
.cab-dt-ampm{display:flex;border:1px solid rgba(255,255,255,0.15);border-radius:8px;overflow:hidden}
.cab-dt-ampm button{background:#0d0d0d;border:none;color:#999;font-family:inherit;font-size:11.5px;font-weight:600;padding:7px 9px;cursor:pointer}
.cab-dt-ampm button.on{background:#C9A84C;color:#0a0a0a}
.cab-dt-foot{display:flex;justify-content:space-between;align-items:center;margin-top:14px}
.cab-dt-note{font-size:10.5px;color:#888}
.cab-dt-done{padding:8px 18px;border:none;border-radius:8px;background:linear-gradient(90deg,#C9A84C,#e8c96b);color:#0a0a0a;font-family:inherit;font-size:11px;font-weight:700;letter-spacing:1px;text-transform:uppercase;cursor:pointer}
.cab-sb-swap{flex:0 0 auto;align-self:center;width:34px;height:34px;border-radius:50%;background:#1c1c1c;border:1px solid rgba(255,255,255,0.12);color:#C9A84C;display:flex;align-items:center;justify-content:center;cursor:pointer;margin:0 8px;position:relative;z-index:2;font-size:12px;transition:transform .25s,border-color .25s}
/* The swap sits in its own gap: no divider line next to it. */
.cab-sb-swap+.cab-sb-field{border-left:none}
.cab-sb-place{padding-right:12px}
.cab-sb-row2{padding-left:18px}
.cab-sb-swap:hover{border-color:#C9A84C;transform:rotate(180deg)}
.cab-pax-pop{position:absolute;top:calc(100% + 10px);right:0;width:280px;background:#1a1a1a;border:1px solid rgba(201,168,76,0.3);border-radius:14px;box-shadow:0 20px 50px rgba(0,0,0,0.55);z-index:50;padding:8px 18px 18px;display:none;font-family:'Poppins',sans-serif}
.cab-pax-pop.open{display:block}
.cab-pax-row{display:flex;align-items:center;justify-content:space-between;padding:12px 0;border-bottom:1px solid rgba(255,255,255,0.08)}
.cab-pax-name{font-size:13px;color:#eee;font-weight:500}
.cab-pax-sub{font-size:10.5px;color:#777;margin-top:2px}
.cab-pax-step{display:flex;align-items:center;gap:12px}
.cab-pax-step button{width:30px;height:30px;border-radius:50%;background:#0d0d0d;border:1px solid rgba(201,168,76,0.5);color:#C9A84C;font-size:16px;line-height:1;cursor:pointer;display:flex;align-items:center;justify-content:center;padding:0}
.cab-pax-step button:disabled{opacity:.3;cursor:not-allowed}
.cab-pax-step span{min-width:18px;text-align:center;font-size:14px;color:#fff}
.cab-pax-done{width:100%;margin-top:14px;padding:11px}
@media(max-width:980px){.cab-sb-swap{position:absolute;right:28px;top:52px;margin:0;transform:rotate(90deg)}.cab-sb-swap:hover{transform:rotate(270deg)}.cab-sb-row{position:relative}.cab-pax-pop{right:auto;left:0}}
@media(max-width:560px){.cab-dt-pop{left:50%;transform:translateX(-50%);width:min(300px,92vw)}}
.cab-sb-addreturn{background:none;border:none;padding:0;color:#C9A84C;font-family:inherit;font-size:13px;font-weight:500;cursor:pointer}
.cab-sb-actions{flex:0 0 auto;display:flex;align-items:center;padding-left:14px}
.cab-sb-search-btn{padding:14px 30px;border:none;border-radius:10px;background:linear-gradient(90deg,#C9A84C,#e8c96b);color:#0a0a0a;font-family:'Poppins',sans-serif;font-size:12px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;cursor:pointer;white-space:nowrap}
.cab-sb-search-btn:hover{opacity:.88}
.cab-sb-search-btn[disabled]{opacity:.6;cursor:wait}
.cab-sb-row2{display:flex;flex-wrap:wrap;gap:10px 14px;margin-top:14px;padding-top:14px;border-top:1px solid rgba(255,255,255,0.06);font-size:11.5px;color:rgba(255,255,255,0.6)}
.cab-sb-error{margin:12px 0 0;padding:10px 14px;border-radius:10px;background:rgba(220,80,80,0.08);border:1px solid rgba(220,80,80,0.3);color:#f3a3a3;font-size:12.5px}
.cab-sb-ac{position:absolute;top:calc(100% + 8px);left:0;width:min(340px,90vw);max-height:300px;overflow-y:auto;background:#1a1a1a;border:1px solid rgba(201,168,76,0.3);border-radius:12px;box-shadow:0 20px 50px rgba(0,0,0,0.5);z-index:40;display:none}
.cab-sb-ac.open{display:block}
.cab-sb-ac button{display:block;width:100%;text-align:left;background:none;border:none;padding:10px 14px;color:#eee;font-family:'Poppins',sans-serif;font-size:13px;cursor:pointer;line-height:1.4}
.cab-sb-ac button:hover,.cab-sb-ac button.hover{background:rgba(201,168,76,0.1)}
.cab-sb-ac-empty{padding:12px 14px;font-size:12px;color:#888}
.cab-sb-ac-loading{display:flex;align-items:center;gap:10px;color:#bbb}
.cab-sb-ac-spin{width:14px;height:14px;border:2px solid rgba(201,168,76,0.25);border-top-color:#C9A84C;border-radius:50%;animation:cabSpin .7s linear infinite;flex-shrink:0}
@keyframes cabSpin{to{transform:rotate(360deg)}}
@media(max-width:980px){
  .cab-sb-row{flex-wrap:wrap;gap:6px}
  .cab-sb-field{flex:1 1 100%}
  .cab-sb-field+.cab-sb-field{border-left:none;border-top:1px solid rgba(255,255,255,0.08)}
  .cab-sb-field.cab-sb-return,.cab-sb-field.cab-sb-pax{flex:1 1 40%}
  .cab-sb-actions{width:100%;padding:6px 0 0}
  .cab-sb-search-btn{flex:1;padding:15px}
}
@media(max-width:560px){.cab-sb-bar{padding:12px}.cab-sb-tabs{gap:18px}}
</style>
@endpush

@push('scripts')
<script>
(function () {
  var form = document.getElementById('cabSearchForm');
  if (!form) return;
  var placesUrl = @json(route('cabs.places'));
  var errorBox = form.querySelector('.cab-sb-error');

  // Round trip: shown once "Add return" is clicked.
  var returnField = form.querySelector('.cab-sb-return');
  var returnInput = form.querySelector('#cabReturn');
  var tripInput = form.querySelector('input[name="trip"]');
  // ── Date & time picker ───────────────────────────────────────────
  var MONTHS = ['January','February','March','April','May','June','July','August','September','October','November','December'];
  var pad = function (n) { return (n < 10 ? '0' : '') + n; };
  var parse = function (v) { var m = /^(\d{4})-(\d\d)-(\d\d)T(\d\d):(\d\d)/.exec(v || ''); return m ? new Date(+m[1], m[2] - 1, +m[3], +m[4], +m[5]) : null; };
  var fmt = function (d) { return d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate()) + 'T' + pad(d.getHours()) + ':' + pad(d.getMinutes()); };
  var label = function (d) {
    var h = d.getHours() % 12 || 12;
    return d.toLocaleDateString('en-GB', { weekday: 'short', day: 'numeric', month: 'short' }) + ' · ' + h + ':' + pad(d.getMinutes()) + ' ' + (d.getHours() < 12 ? 'AM' : 'PM');
  };
  var pickers = [];

  form.querySelectorAll('.cab-dt').forEach(function (field) {
    var btn = field.querySelector('.cab-dt-btn');
    var input = field.querySelector('input[type="hidden"]');
    var after = field.dataset.after ? document.getElementById(field.dataset.after) : null;
    var pop = document.createElement('div');
    pop.className = 'cab-dt-pop';
    pop.setAttribute('role', 'dialog');
    pop.setAttribute('aria-label', 'Choose date and time');
    field.appendChild(pop);
    var view, draft;

    function minDate() {
      var min = parse(field.dataset.min);
      var a = after && parse(after.value);
      if (a) { a = new Date(a.getTime() + 30 * 60000); if (!min || a > min) min = a; }
      return min;
    }
    function show() {
      var d = parse(input.value);
      btn.textContent = d ? label(d) : 'Select date & time';
      btn.classList.toggle('is-empty', !d);
    }
    function render() {
      var min = minDate(), y = view.getFullYear(), m = view.getMonth();
      var first = new Date(y, m, 1).getDay(), days = new Date(y, m + 1, 0).getDate();
      var today = new Date(); today.setHours(0, 0, 0, 0);
      var minDay = min ? new Date(min.getFullYear(), min.getMonth(), min.getDate()) : today;
      var html = '<div class="cab-dt-head"><button type="button" class="cab-dt-nav" data-nav="-1" aria-label="Previous month"' + (new Date(y, m, 1) <= new Date(minDay.getFullYear(), minDay.getMonth(), 1) ? ' disabled' : '') + '>‹</button>'
        + '<span class="cab-dt-month">' + MONTHS[m] + ' ' + y + '</span><button type="button" class="cab-dt-nav" data-nav="1" aria-label="Next month">›</button></div><div class="cab-dt-grid">';
      ['Su','Mo','Tu','We','Th','Fr','Sa'].forEach(function (w) { html += '<span class="cab-dt-dow">' + w + '</span>'; });
      for (var i = 0; i < first; i++) html += '<span></span>';
      for (var dd = 1; dd <= days; dd++) {
        var day = new Date(y, m, dd);
        var cls = 'cab-dt-day' + (+day === +today ? ' today' : '') + (draft && draft.getFullYear() === y && draft.getMonth() === m && draft.getDate() === dd ? ' sel' : '');
        html += '<button type="button" class="' + cls + '" data-day="' + dd + '"' + (day < minDay ? ' disabled' : '') + '>' + dd + '</button>';
      }
      var h = draft.getHours(), h12 = h % 12 || 12, pm = h >= 12;
      html += '</div><div class="cab-dt-time"><span class="cab-dt-time-lbl">Time</span><select data-h aria-label="Hour">';
      for (var hh = 1; hh <= 12; hh++) html += '<option' + (hh === h12 ? ' selected' : '') + '>' + hh + '</option>';
      html += '</select><select data-mi aria-label="Minutes">';
      for (var mi = 0; mi < 60; mi += 15) html += '<option value="' + mi + '"' + (mi === draft.getMinutes() - draft.getMinutes() % 15 ? ' selected' : '') + '>' + pad(mi) + '</option>';
      html += '</select><span class="cab-dt-ampm"><button type="button" data-ap="am" class="' + (pm ? '' : 'on') + '">AM</button><button type="button" data-ap="pm" class="' + (pm ? 'on' : '') + '">PM</button></span></div>'
        + '<div class="cab-dt-foot"><span class="cab-dt-note">' + (min ? 'Earliest: ' + label(min) : '') + '</span><button type="button" class="cab-dt-done">Done</button></div>';
      pop.innerHTML = html;
    }
    function setTime() {
      var h = +pop.querySelector('[data-h]').value % 12 + (pop.querySelector('[data-ap="pm"]').classList.contains('on') ? 12 : 0);
      draft.setHours(h, +pop.querySelector('[data-mi]').value, 0, 0);
    }
    function open() {
      pickers.forEach(function (p) { if (p !== api) p.close(); });
      var min = minDate();
      draft = parse(input.value) || new Date((min || new Date()).getTime());
      if (min && draft < min) draft = new Date(min.getTime());
      draft.setMinutes(Math.ceil(draft.getMinutes() / 15) * 15, 0, 0);
      view = new Date(draft.getFullYear(), draft.getMonth(), 1);
      render();
      pop.classList.add('open');
      btn.setAttribute('aria-expanded', 'true');
    }
    function close() { pop.classList.remove('open'); btn.setAttribute('aria-expanded', 'false'); }
    function commit() {
      setTime();
      var min = minDate();
      if (min && draft < min) { draft = new Date(min.getTime()); draft.setMinutes(Math.ceil(draft.getMinutes() / 15) * 15, 0, 0); }
      input.value = fmt(draft);
      show();
      close();
      // A pickup moved past the return pushes the return on too.
      pickers.forEach(function (p) { if (p.after === input) p.check(); });
    }

    pop.addEventListener('click', function (e) {
      e.stopPropagation();
      var t = e.target.closest('button');
      if (!t || t.disabled) return;
      if (t.dataset.nav) { view.setMonth(view.getMonth() + +t.dataset.nav); setTime(); render(); }
      else if (t.dataset.day) { setTime(); draft.setFullYear(view.getFullYear(), view.getMonth(), +t.dataset.day); render(); }
      else if (t.dataset.ap) { pop.querySelectorAll('[data-ap]').forEach(function (b) { b.classList.toggle('on', b === t); }); }
      else if (t.classList.contains('cab-dt-done')) { commit(); btn.focus(); }
    });
    pop.addEventListener('keydown', function (e) { if (e.key === 'Escape') { close(); btn.focus(); } });
    btn.addEventListener('click', function (e) { e.stopPropagation(); pop.classList.contains('open') ? close() : open(); });

    var api = {
      close: close, open: open, after: after,
      check: function () { var d = parse(input.value), min = minDate(); if (d && min && d < min) { input.value = fmt(min); show(); } },
    };
    pickers.push(api);
    field.cabPicker = api;
    show();
  });
  document.addEventListener('click', function (e) { if (!e.target.closest('.cab-dt')) pickers.forEach(function (p) { p.close(); }); });

  // ── Swap pickup and drop ─────────────────────────────────────────
  form.querySelector('.cab-sb-swap').addEventListener('click', function () {
    [['from', 'to'], ['from_id', 'to_id']].forEach(function (pair) {
      var a = form.elements[pair[0]], b = form.elements[pair[1]], t = a.value;
      a.value = b.value; b.value = t;
    });
  });

  // ── Passengers & bags ────────────────────────────────────────────
  var paxField = form.querySelector('.cab-sb-pax');
  var paxBtn = paxField.querySelector('.cab-pax-btn');
  var paxPop = paxField.querySelector('.cab-pax-pop');
  function paxShow() {
    var p = +form.elements.passengers.value, b = +form.elements.bags.value;
    paxBtn.textContent = p + (p === 1 ? ' Passenger' : ' Passengers') + ', ' + b + (b === 1 ? ' Bag' : ' Bags');
    paxPop.querySelectorAll('.cab-pax-row').forEach(function (row) {
      var v = +form.elements[row.dataset.key].value;
      row.querySelector('span').textContent = v;
      row.querySelector('[data-step="-1"]').disabled = v <= +row.dataset.min;
      row.querySelector('[data-step="1"]').disabled = v >= +row.dataset.max;
    });
  }
  function paxClose() { paxPop.classList.remove('open'); paxBtn.setAttribute('aria-expanded', 'false'); }
  paxBtn.addEventListener('click', function (e) {
    e.stopPropagation();
    pickers.forEach(function (p) { p.close(); });
    var open = !paxPop.classList.contains('open');
    paxPop.classList.toggle('open', open);
    paxBtn.setAttribute('aria-expanded', String(open));
  });
  paxPop.addEventListener('click', function (e) {
    e.stopPropagation();
    var t = e.target.closest('button');
    if (!t) return;
    if (t.dataset.step) {
      var row = t.closest('.cab-pax-row'), input = form.elements[row.dataset.key];
      input.value = Math.max(+row.dataset.min, Math.min(+row.dataset.max, +input.value + +t.dataset.step));
      paxShow();
    } else if (t.classList.contains('cab-pax-done')) { paxClose(); paxBtn.focus(); }
  });
  paxPop.addEventListener('keydown', function (e) { if (e.key === 'Escape') { paxClose(); paxBtn.focus(); } });
  document.addEventListener('click', function (e) { if (!e.target.closest('.cab-sb-pax')) paxClose(); });
  paxShow();

  form.querySelector('.cab-sb-addreturn').addEventListener('click', function (e) {
    e.stopPropagation();
    returnField.classList.remove('is-off');
    returnInput.disabled = false;
    this.hidden = true;
    tripInput.value = 'roundtrip';
    returnField.cabPicker.open();
  });

  // Place suggestions (TripJack Location Search API) — a place counts only once picked from the list.
  var placeCache = {}; // shared by the pickup and drop boxes for this visit
  form.querySelectorAll('.cab-sb-place').forEach(function (field) {
    var input = field.querySelector('.cab-sb-input');
    var hidden = field.querySelector('input[type="hidden"]');
    var list = field.querySelector('.cab-sb-ac');
    var timer, active = -1, lastQuery = '';

    function close() { list.classList.remove('open'); active = -1; }
    function render(places) {
      list.innerHTML = '';
      if (!places.length) {
        list.innerHTML = '<div class="cab-sb-ac-empty">No places found. Try another name.</div>';
      }
      places.forEach(function (p) {
        var b = document.createElement('button');
        b.type = 'button';
        b.setAttribute('role', 'option');
        b.textContent = p.label;
        b.addEventListener('mousedown', function (e) { e.preventDefault(); pick(p); });
        list.appendChild(b);
      });
      list.classList.add('open');
    }
    function pick(p) { input.value = p.label; hidden.value = p.id; close(); }

    var controller = null;
    function status(text) {
      list.innerHTML = '<div class="cab-sb-ac-empty cab-sb-ac-loading"><span class="cab-sb-ac-spin" aria-hidden="true"></span>' + text + '</div>';
      list.classList.add('open');
    }

    input.addEventListener('input', function () {
      hidden.value = '';
      var q = input.value.trim();
      var key = q.toLowerCase().replace(/\s+/g, ' ');
      clearTimeout(timer);
      if (q.length < 3) { close(); return; }
      lastQuery = key;

      // Already looked up during this visit: show at once.
      if (placeCache[key]) { render(placeCache[key]); return; }

      // Show that something is happening straight away, not a blank box.
      status('Searching places…');
      timer = setTimeout(function () {
        if (controller) controller.abort(); // a reply to older letters is no longer needed
        controller = window.AbortController ? new AbortController() : null;
        fetch(placesUrl + '?q=' + encodeURIComponent(q), { headers: { 'Accept': 'application/json' }, signal: controller ? controller.signal : undefined })
          .then(function (r) { return r.json(); })
          .then(function (d) {
            var places = d.places || [];
            if (places.length) placeCache[key] = places;
            if (key !== lastQuery) return;
            if (!places.length && d.message) { status(d.message); return; }
            render(places);
          })
          .catch(function (err) { if (!err || err.name !== 'AbortError') { if (key === lastQuery) status('Couldn\'t load places. Please type again.'); } });
      }, 200);
    });
    input.addEventListener('keydown', function (e) {
      var items = list.querySelectorAll('button');
      if (!list.classList.contains('open') || !items.length) return;
      if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
        e.preventDefault();
        active = (active + (e.key === 'ArrowDown' ? 1 : -1) + items.length) % items.length;
        items.forEach(function (it, i) { it.classList.toggle('hover', i === active); });
      } else if (e.key === 'Enter' && active >= 0) {
        e.preventDefault();
        items[active].dispatchEvent(new MouseEvent('mousedown'));
      } else if (e.key === 'Escape') {
        close();
      }
    });
    input.addEventListener('blur', function () { setTimeout(close, 150); });

    // Prefilled by name only (e.g. "Add Cab" for a flight): show the matching
    // places so the guest picks the exact one with one click.
    if (form.dataset.suggest === '1' && !hidden.value && input.value.trim().length >= 3) {
      input.focus({ preventScroll: true });
      input.dispatchEvent(new Event('input'));
    }
  });

  form.addEventListener('submit', function (e) {
    var msg = '';
    if (!form.from_id.value) msg = 'Please choose your pickup place from the suggestions.';
    else if (!form.to_id.value) msg = 'Please choose your drop place from the suggestions.';
    else if (!form.pickup.value) msg = 'Please choose a pickup date and time.';
    else if (tripInput.value === 'roundtrip' && !returnInput.value) msg = 'Please choose a return date and time.';
    if (msg) {
      e.preventDefault();
      errorBox.textContent = msg;
      errorBox.hidden = false;
      return;
    }
    // Recent searches (this browser only), newest first, 6 kept.
    try {
      var keys = ['journey', 'trip', 'from', 'from_id', 'to', 'to_id', 'pickup', 'return', 'passengers', 'bags'];
      var entry = {};
      keys.forEach(function (k) { var el = form.elements[k]; if (el && !el.disabled && el.value) entry[k] = el.value; });
      var list = JSON.parse(localStorage.getItem('tytCabRecent') || '[]').filter(function (r) {
        return !(r.from_id === entry.from_id && r.to_id === entry.to_id && r.pickup === entry.pickup);
      });
      list.unshift(entry);
      localStorage.setItem('tytCabRecent', JSON.stringify(list.slice(0, 6)));
    } catch (err) { /* storage off: nothing to remember */ }

    form.querySelector('.cab-sb-search-btn').disabled = true;
    form.querySelector('.cab-sb-search-btn').textContent = 'Searching…';
  });
})();
</script>
@endpush
@endonce
