@php
  // Shared live flight search bar — used on the marketing /flights page and
  // as the "Modify Search" panel on the results page, so both stay visually
  // and functionally identical. Callers pass current values to pre-fill it.
  $action = $action ?? route('flights.search');
  $sbFrom = $from ?? '';
  $sbTo = $to ?? '';
  $sbDepartDate = $departDate ?? '';
  $sbReturnDate = $returnDate ?? '';
  $sbAdults = $adults ?? 1;
  $sbChildren = $children ?? 0;
  $sbInfants = $infants ?? 0;
  $sbCabinClass = $cabinClass ?? 'ECONOMY';
  $sbTripType = in_array($tripType ?? 'oneway', ['return', 'multi'], true) ? ($tripType ?? 'oneway') : 'oneway';
  $sbPreferredAirline = $preferredAirline ?? '';
  $sbFareType = $fareType ?? 'REGULAR';
  $sbDirectFlightOnly = $directFlightOnly ?? false;
@endphp

<div class="tyt-sb-tabs">
  <button type="button" class="tyt-sb-tab {{ $sbTripType === 'oneway' ? 'active' : '' }}" id="tytSbTabOne" onclick="tytSbSetTrip('oneway',this)">One Way</button>
  <button type="button" class="tyt-sb-tab {{ $sbTripType === 'return' ? 'active' : '' }}" id="tytSbTabRound" onclick="tytSbSetTrip('return',this)">Round Trip</button>
  <button type="button" class="tyt-sb-tab {{ $sbTripType === 'multi' ? 'active' : '' }}" id="tytSbTabMulti" onclick="tytSbSetTrip('multi',this)">Multi City</button>
</div>

<form class="tyt-searchbar" id="tytSbForm" action="{{ $action }}" method="GET">
  <div class="tyt-sb-row">
    <div class="tyt-sb-field" style="flex:0.9">
      <label class="tyt-sb-flabel">From</label>
      <input class="tyt-sb-input" type="text" name="from" id="tytSbFrom" placeholder="Where From?" value="{{ $sbFrom }}" style="text-transform:uppercase" autocomplete="off" required>
      <div class="tyt-sb-autocomplete" id="tytSbFromAc"></div>
    </div>
    <button type="button" class="tyt-sb-swap" id="tytSbSwap" title="Swap origin & destination" aria-label="Swap origin and destination">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 7h16M4 7l4-4M4 7l4 4M20 17H4M20 17l-4-4M20 17l-4 4"/></svg>
    </button>
    <div class="tyt-sb-field" style="flex:0.9">
      <label class="tyt-sb-flabel">To</label>
      <input class="tyt-sb-input" type="text" name="to" id="tytSbTo" placeholder="Where To?" value="{{ $sbTo }}" style="text-transform:uppercase" autocomplete="off" required>
      <div class="tyt-sb-autocomplete" id="tytSbToAc"></div>
    </div>

    <div class="tyt-sb-dates">
      <div class="tyt-sb-field">
        <label class="tyt-sb-flabel">Departure</label>
        <input class="tyt-sb-input" type="text" id="tytSbDepartDisplay" placeholder="Select date" autocomplete="off" readonly>
        <input type="hidden" name="depart_date" id="tytSbDepart" value="{{ $sbDepartDate }}" required>
        <div class="tyt-sb-calendar" id="tytSbDepartCal"></div>
      </div>
      <div class="tyt-sb-field {{ $sbTripType === 'return' ? '' : 'disabled' }}" id="tytSbReturnField">
        <label class="tyt-sb-flabel">Return</label>
        <input class="tyt-sb-input" type="text" id="tytSbReturnDisplay" placeholder="{{ $sbTripType === 'return' ? 'Select date' : '+ Add return' }}" autocomplete="off" readonly {{ $sbTripType === 'return' ? '' : 'disabled' }}>
        <input type="hidden" name="return_date" id="tytSbReturn" value="{{ $sbReturnDate }}" {{ $sbTripType === 'return' ? '' : 'disabled' }}>
        <div class="tyt-sb-calendar" id="tytSbReturnCal"></div>
      </div>
    </div>

    <div class="tyt-sb-field tyt-sb-pax-wrap" id="tytSbPaxWrap">
      <label class="tyt-sb-flabel">Travellers &amp; Class</label>
      <button type="button" class="tyt-sb-pax-btn" id="tytSbPaxToggle">
        <span class="tyt-sb-pax-summary" id="tytSbPaxSummary">1 Passenger | Economy</span>
      </button>

      <div class="tyt-sb-pax-panel" id="tytSbPaxPanel">
        <div class="tyt-sb-pax-row">
          <div><div class="tyt-sb-pax-row-label">Adults</div><div class="tyt-sb-pax-row-sub">12 yrs +</div></div>
          <div class="tyt-sb-stepper">
            <button type="button" data-pax="adults" data-dir="-1">&minus;</button>
            <span id="tytSbAdultsVal">{{ $sbAdults }}</span>
            <button type="button" data-pax="adults" data-dir="1">+</button>
          </div>
        </div>
        <div class="tyt-sb-pax-row">
          <div><div class="tyt-sb-pax-row-label">Children</div><div class="tyt-sb-pax-row-sub">2–11 yrs</div></div>
          <div class="tyt-sb-stepper">
            <button type="button" data-pax="children" data-dir="-1">&minus;</button>
            <span id="tytSbChildrenVal">{{ $sbChildren }}</span>
            <button type="button" data-pax="children" data-dir="1">+</button>
          </div>
        </div>
        <div class="tyt-sb-pax-row">
          <div><div class="tyt-sb-pax-row-label">Infants</div><div class="tyt-sb-pax-row-sub">Under 2 yrs</div></div>
          <div class="tyt-sb-stepper">
            <button type="button" data-pax="infants" data-dir="-1">&minus;</button>
            <span id="tytSbInfantsVal">{{ $sbInfants }}</span>
            <button type="button" data-pax="infants" data-dir="1">+</button>
          </div>
        </div>

        <div class="tyt-sb-class-list" id="tytSbClassList">
          <div class="tyt-sb-class-opt {{ $sbCabinClass === 'ECONOMY' ? 'active' : '' }}" data-class="ECONOMY">Economy</div>
          <div class="tyt-sb-class-opt {{ $sbCabinClass === 'PREMIUM_ECONOMY' ? 'active' : '' }}" data-class="PREMIUM_ECONOMY">Premium Eco</div>
          <div class="tyt-sb-class-opt {{ $sbCabinClass === 'BUSINESS' ? 'active' : '' }}" data-class="BUSINESS">Business</div>
          {{-- "FIRST", not "FIRST_CLASS": the search only accepts ECONOMY /
               PREMIUM_ECONOMY / BUSINESS / FIRST, and anything else silently
               fell back to an Economy search. --}}
          <div class="tyt-sb-class-opt {{ $sbCabinClass === 'FIRST' ? 'active' : '' }}" data-class="FIRST">First Class</div>
        </div>

        <button type="button" class="tyt-sb-pax-done" id="tytSbPaxDone">Done</button>
      </div>

      <input type="hidden" name="adults" id="tytSbAdultsInput" value="{{ $sbAdults }}">
      <input type="hidden" name="children" id="tytSbChildrenInput" value="{{ $sbChildren }}">
      <input type="hidden" name="infants" id="tytSbInfantsInput" value="{{ $sbInfants }}">
      <input type="hidden" name="cabin_class" id="tytSbClassInput" value="{{ $sbCabinClass }}">
      <input type="hidden" name="trip_type" id="tytSbTripType" value="{{ $sbTripType }}">
    </div>

    <div class="tyt-sb-actions">
      <button type="submit" class="tyt-sb-search-btn" id="tytSbSearchBtn">Search</button>
    </div>
  </div>

  <div class="tyt-sb-multi" id="tytSbMultiWrap" style="{{ $sbTripType === 'multi' ? '' : 'display:none;' }}">
    <div class="tyt-sb-multi-legs" id="tytSbMultiLegs"></div>
    <button type="button" class="tyt-sb-multi-add" id="tytSbMultiAdd">+ Add Another Flight</button>
  </div>

  <div class="tyt-sb-row2">
    @php
      $sbAirlines = [
        '6E' => 'IndiGo', 'AI' => 'Air India', 'SG' => 'SpiceJet', 'UK' => 'Vistara',
        'G8' => 'Go First', 'I5' => 'AirAsia India', 'EK' => 'Emirates', 'QR' => 'Qatar Airways',
        'LH' => 'Lufthansa', 'BA' => 'British Airways', 'EY' => 'Etihad Airways',
        'SQ' => 'Singapore Airlines', 'CX' => 'Cathay Pacific',
      ];
    @endphp
    {{-- TODO(future): allow choosing up to 10 preferred airlines (TripJack's
         limit) — see FlightController::search(). --}}
    <select class="tyt-sb-airline-select" name="preferred_airline">
      <option value="">Select Preferred Airline</option>
      @foreach($sbAirlines as $code => $name)
        <option value="{{ $code }}" {{ $sbPreferredAirline === $code ? 'selected' : '' }}>{{ $name }}</option>
      @endforeach
    </select>

    <span class="tyt-sb-farelabel">Select Fare Type:</span>
    <div class="tyt-sb-check-group" id="tytSbFareTypeGroup">
      <label class="tyt-sb-check"><input type="radio" name="fare_type" value="REGULAR" {{ $sbFareType === 'REGULAR' ? 'checked' : '' }}> Regular</label>
      <label class="tyt-sb-check"><input type="radio" name="fare_type" value="STUDENT" {{ $sbFareType === 'STUDENT' ? 'checked' : '' }}> Student</label>
      <label class="tyt-sb-check"><input type="radio" name="fare_type" value="SENIOR_CITIZEN" {{ $sbFareType === 'SENIOR_CITIZEN' ? 'checked' : '' }}> Senior Citizen</label>
    </div>

    <div class="tyt-sb-spacer"></div>

    <div class="tyt-sb-check-group">
      <label class="tyt-sb-check"><input type="checkbox" name="direct_flight" value="1" {{ $sbDirectFlightOnly ? 'checked' : '' }}> Direct Flight</label>
    </div>
  </div>
</form>

@push('styles')
<style>
.tyt-sb-tabs{display:flex;gap:26px;margin-bottom:10px;padding-left:6px}
.tyt-sb-tab{background:none;border:none;cursor:pointer;font-family:'Poppins',sans-serif;font-size:12.5px;font-weight:600;letter-spacing:0.5px;color:rgba(255,255,255,0.45);padding:4px 0;border-bottom:2px solid transparent;transition:color .2s,border-color .2s}
.tyt-sb-tab.active{color:#fff;border-color:#C9A84C}
.tyt-searchbar{background:#141414;border:1px solid rgba(201,168,76,0.25);border-radius:14px;box-shadow:0 20px 50px rgba(0,0,0,0.45);padding:16px}
.tyt-sb-row{display:flex;align-items:stretch;gap:0}
.tyt-sb-field{flex:1;min-width:0;padding:8px 18px;position:relative;border-radius:10px;cursor:pointer;transition:background .2s,box-shadow .2s}
.tyt-sb-field:hover{background:rgba(255,255,255,0.03)}
.tyt-sb-field:focus-within{background:rgba(201,168,76,0.06);box-shadow:inset 0 -2px 0 #C9A84C}
.tyt-sb-dates .tyt-sb-field.disabled{cursor:pointer}
.tyt-sb-dates .tyt-sb-field.disabled .tyt-sb-input::placeholder{color:#C9A84C;opacity:0.75;font-weight:500}
.tyt-sb-dates .tyt-sb-field.disabled:hover .tyt-sb-input::placeholder{opacity:1}
.tyt-sb-field+.tyt-sb-field{border-left:1px solid rgba(255,255,255,0.08)}
.tyt-sb-flabel{display:block;font-family:'Poppins',sans-serif;font-size:9.5px;font-weight:600;letter-spacing:1.5px;text-transform:uppercase;color:#C9A84C;margin-bottom:5px}
.tyt-sb-input{width:100%;background:transparent;border:none;color:#fff;font-family:'Poppins',sans-serif;font-size:14px;font-weight:400;outline:none;padding:0;-webkit-appearance:none}
.tyt-sb-input::placeholder{color:#555}
.tyt-sb-input[type="date"]::-webkit-calendar-picker-indicator{filter:invert(0.55)}
.tyt-sb-swap{flex:0 0 auto;align-self:center;width:34px;height:34px;border-radius:50%;background:#1c1c1c;border:1px solid rgba(255,255,255,0.12);color:#C9A84C;display:flex;align-items:center;justify-content:center;cursor:pointer;transition:transform .25s,border-color .25s;margin:0 -3px;position:relative;z-index:2}
.tyt-sb-swap:hover{border-color:#C9A84C;transform:rotate(180deg)}
.tyt-sb-dates{display:flex;flex:1.6;min-width:0}
.tyt-sb-dates .tyt-sb-field{flex:1;min-width:118px}
.tyt-sb-dates .tyt-sb-field.disabled .tyt-sb-input{color:#444;pointer-events:none}
.tyt-sb-dates .tyt-sb-input{font-size:12.5px;letter-spacing:-0.2px}

.tyt-sb-autocomplete{position:absolute;top:calc(100% + 8px);left:0;width:265px;max-height:300px;overflow-y:auto;scrollbar-width:none;-ms-overflow-style:none;background:#1a1a1a;border:1px solid rgba(201,168,76,0.3);border-radius:12px;box-shadow:0 20px 50px rgba(0,0,0,0.5);z-index:40;display:none}
.tyt-sb-autocomplete::-webkit-scrollbar{display:none}
.tyt-sb-autocomplete.open{display:block}
.tyt-sb-ac-item{display:flex;align-items:center;justify-content:space-between;gap:8px;padding:9px 14px;cursor:pointer;transition:background .15s}
.tyt-sb-ac-item:hover,.tyt-sb-ac-item.hover{background:rgba(201,168,76,0.1)}
.tyt-sb-ac-city{font-family:'Poppins',sans-serif;font-size:13px;color:#eee;font-weight:500}
.tyt-sb-ac-airport{font-size:11px;color:#777;margin-top:2px}
.tyt-sb-ac-code{font-family:'Poppins',sans-serif;font-size:12px;font-weight:700;color:#C9A84C}
.tyt-sb-ac-country{font-size:10.5px;color:#666;text-align:right}
.tyt-sb-ac-empty{padding:14px 16px;font-size:12px;color:#666}

.tyt-sb-calendar{position:absolute;top:calc(100% + 8px);left:0;width:270px;background:#1a1a1a;border:1px solid rgba(201,168,76,0.3);border-radius:12px;box-shadow:0 20px 50px rgba(0,0,0,0.5);z-index:40;padding:16px;display:none}
.tyt-sb-calendar.open{display:block}
.tyt-sb-cal-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:12px}
.tyt-sb-cal-month{font-family:'Poppins',sans-serif;font-size:12.5px;font-weight:700;color:#C9A84C;letter-spacing:0.3px}
.tyt-sb-cal-nav{width:24px;height:24px;border-radius:50%;background:#0d0d0d;border:1px solid rgba(255,255,255,0.15);color:#C9A84C;font-size:14px;cursor:pointer;display:flex;align-items:center;justify-content:center;line-height:1;padding:0}
.tyt-sb-cal-nav:hover{border-color:#C9A84C}
.tyt-sb-cal-grid{display:grid;grid-template-columns:repeat(7,1fr);gap:3px;text-align:center}
.tyt-sb-cal-dow{font-family:'Poppins',sans-serif;font-size:9.5px;font-weight:600;letter-spacing:0.5px;text-transform:uppercase;color:#666;padding:4px 0}
.tyt-sb-cal-day{padding:7px 0;border-radius:8px;font-family:'Poppins',sans-serif;font-size:12px;color:#ccc;cursor:pointer;transition:background .15s,color .15s}
.tyt-sb-cal-day:hover{background:rgba(201,168,76,0.15)}
.tyt-sb-cal-day.today{border:1px solid #C9A84C;color:#C9A84C}
.tyt-sb-cal-day.selected{background:#C9A84C;color:#0a0a0a;font-weight:700}
.tyt-sb-cal-day.disabled{color:#3a3a3a;cursor:not-allowed;pointer-events:none}
.tyt-sb-cal-footer{display:flex;justify-content:space-between;margin-top:12px;padding-top:12px;border-top:1px solid rgba(255,255,255,0.08)}
.tyt-sb-cal-footer span{font-family:'Poppins',sans-serif;font-size:11px;font-weight:600;letter-spacing:0.3px;color:#C9A84C;cursor:pointer}
.tyt-sb-cal-footer span:hover{opacity:0.75}
.tyt-sb-pax-wrap{position:relative;flex:1}
.tyt-sb-pax-btn{width:100%;text-align:left;background:transparent;border:none;cursor:pointer;padding:0}
.tyt-sb-pax-summary{color:#fff;font-family:'Poppins',sans-serif;font-size:14px}
.tyt-sb-pax-panel{display:none;position:absolute;top:calc(100% + 12px);right:0;width:290px;background:#1a1a1a;border:1px solid rgba(201,168,76,0.3);border-radius:12px;box-shadow:0 20px 50px rgba(0,0,0,0.5);padding:18px;z-index:30}
.tyt-sb-pax-panel.open{display:block}
.tyt-sb-pax-row{display:flex;align-items:center;justify-content:space-between;padding:9px 0}
.tyt-sb-pax-row-label{font-family:'Poppins',sans-serif;font-size:12.5px;color:#ddd}
.tyt-sb-pax-row-sub{font-family:'Poppins',sans-serif;font-size:10.5px;color:#666;margin-top:1px}
.tyt-sb-stepper{display:flex;align-items:center;gap:12px}
.tyt-sb-stepper button{width:26px;height:26px;border-radius:50%;background:#0d0d0d;border:1px solid rgba(255,255,255,0.15);color:#C9A84C;font-size:14px;cursor:pointer;display:flex;align-items:center;justify-content:center;line-height:1}
.tyt-sb-stepper button:disabled{opacity:0.3;cursor:not-allowed}
.tyt-sb-stepper span{min-width:16px;text-align:center;font-family:'Poppins',sans-serif;font-size:13px;color:#fff}
.tyt-sb-class-list{display:flex;flex-wrap:wrap;gap:6px;margin-top:12px;padding-top:12px;border-top:1px solid rgba(255,255,255,0.08)}
.tyt-sb-class-opt{padding:7px 12px;border:1px solid rgba(255,255,255,0.12);border-radius:100px;font-family:'Poppins',sans-serif;font-size:11px;color:#999;cursor:pointer;background:#0d0d0d}
.tyt-sb-class-opt.active{color:#0a0a0a;background:#C9A84C;border-color:#C9A84C}
.tyt-sb-pax-done{width:100%;margin-top:14px;padding:10px;border:none;border-radius:8px;background:#C9A84C;color:#0a0a0a;font-family:'Poppins',sans-serif;font-size:11px;font-weight:700;letter-spacing:1px;text-transform:uppercase;cursor:pointer}
.tyt-sb-actions{flex:0 0 auto;display:flex;align-items:center;gap:10px;padding-left:14px}
.tyt-sb-search-btn{padding:14px 30px;border:none;border-radius:10px;background:linear-gradient(90deg,#C9A84C,#e8c96b);color:#0a0a0a;font-family:'Poppins',sans-serif;font-size:12px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;cursor:pointer;white-space:nowrap;transition:opacity .2s}
.tyt-sb-search-btn:hover{opacity:0.88}

.tyt-sb-multi{margin-top:14px;padding-top:14px;border-top:1px solid rgba(255,255,255,0.06)}
.tyt-sb-multi-legs{display:flex;flex-direction:column;gap:10px;margin-bottom:12px}
.tyt-sb-multi-row{display:flex;align-items:stretch;gap:0;background:#181818;border:1px solid rgba(255,255,255,0.08);border-radius:10px}
.tyt-sb-multi-row .tyt-sb-field{flex:1;padding:8px 16px}
.tyt-sb-multi-row .tyt-sb-field+.tyt-sb-field{border-left:1px solid rgba(255,255,255,0.08)}
.tyt-sb-multi-remove{flex:0 0 auto;width:38px;background:transparent;border:none;color:#888;font-size:18px;cursor:pointer;align-self:center;transition:color .2s}
.tyt-sb-multi-remove:hover{color:#f3a3a3}
.tyt-sb-multi-add{padding:9px 18px;border:1px dashed rgba(201,168,76,0.4);border-radius:8px;background:transparent;color:#C9A84C;font-family:'Poppins',sans-serif;font-size:11.5px;font-weight:600;letter-spacing:0.5px;cursor:pointer}
.tyt-sb-multi-add:hover{background:rgba(201,168,76,0.08)}
.tyt-sb-multi-add:disabled{opacity:0.35;cursor:not-allowed}

.tyt-sb-row2{display:flex;align-items:center;flex-wrap:wrap;gap:18px;margin-top:14px;padding-top:14px;border-top:1px solid rgba(255,255,255,0.06)}
.tyt-sb-airline-select{background:#0d0d0d;border:1px solid rgba(255,255,255,0.12);color:#ccc;font-family:'Poppins',sans-serif;font-size:11.5px;padding:9px 14px;border-radius:8px;outline:none}
.tyt-sb-farelabel{font-family:'Poppins',sans-serif;font-size:11px;color:#777}
.tyt-sb-check-group{display:flex;align-items:center;gap:14px;flex-wrap:wrap}
.tyt-sb-check{display:flex;align-items:center;gap:6px;font-family:'Poppins',sans-serif;font-size:11.5px;color:#bbb;cursor:pointer}
.tyt-sb-check input{accent-color:#C9A84C;width:14px;height:14px;cursor:pointer}
.tyt-sb-spacer{flex:1}

@media(max-width:980px){
  .tyt-sb-row{flex-wrap:wrap;gap:10px}
  .tyt-sb-field+.tyt-sb-field{border-left:none;border-top:1px solid rgba(255,255,255,0.08)}
  .tyt-sb-swap{display:none}
  .tyt-sb-dates{flex-wrap:wrap;width:100%}
  .tyt-sb-actions{width:100%;padding-left:0;justify-content:stretch}
  .tyt-sb-search-btn{flex:1}
  .tyt-sb-pax-panel{right:auto;left:0}
  .tyt-sb-multi-row{flex-wrap:wrap}
  .tyt-sb-multi-row .tyt-sb-field+.tyt-sb-field{border-left:none;border-top:1px solid rgba(255,255,255,0.08)}
}
@media(max-width:560px){
  .tyt-searchbar{padding:12px}
}
</style>
@endpush

@push('scripts')
<script>
(function () {
  // Custom themed date picker (native <input type=date> can't be
  // restyled cross-browser — this matches the tytluxe gold/dark theme).
  function tytSbPad(n) { return n < 10 ? '0' + n : '' + n; }
  function tytSbToIso(d) { return d.getFullYear() + '-' + tytSbPad(d.getMonth() + 1) + '-' + tytSbPad(d.getDate()); }
  // "Wed, 30 Sep 2026" — unambiguous, unlike 09/30/2026 vs 30/09/2026.
  var TYT_SB_DAYS = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
  var TYT_SB_MONTHS = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
  function tytSbToDisplay(d) { return TYT_SB_DAYS[d.getDay()] + ', ' + d.getDate() + ' ' + TYT_SB_MONTHS[d.getMonth()] + ' ' + d.getFullYear(); }
  function tytSbFromIso(iso) { var p = iso.split('-'); return new Date(parseInt(p[0], 10), parseInt(p[1], 10) - 1, parseInt(p[2], 10)); }

  function tytSbSetupDatePicker(displayId, hiddenId, calId, getMinDate) {
    var display = document.getElementById(displayId);
    var hidden = document.getElementById(hiddenId);
    var cal = document.getElementById(calId);
    var selected = null;
    var viewDate = new Date();
    var monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];

    function setDate(d) {
      selected = d;
      hidden.value = tytSbToIso(d);
      display.value = tytSbToDisplay(d);
    }

    function render() {
      var year = viewDate.getFullYear();
      var month = viewDate.getMonth();
      var firstDay = new Date(year, month, 1).getDay();
      var daysInMonth = new Date(year, month + 1, 0).getDate();
      var minDate = getMinDate ? getMinDate() : null;
      var todayIso = tytSbToIso(new Date());

      var html = '<div class="tyt-sb-cal-header">'
        + '<button type="button" class="tyt-sb-cal-nav" data-dir="-1">&lsaquo;</button>'
        + '<span class="tyt-sb-cal-month">' + monthNames[month] + ' ' + year + '</span>'
        + '<button type="button" class="tyt-sb-cal-nav" data-dir="1">&rsaquo;</button></div>';
      html += '<div class="tyt-sb-cal-grid">';
      ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'].forEach(function (d) { html += '<div class="tyt-sb-cal-dow">' + d + '</div>'; });
      for (var i = 0; i < firstDay; i++) html += '<div></div>';
      for (var day = 1; day <= daysInMonth; day++) {
        var d = new Date(year, month, day);
        var iso = tytSbToIso(d);
        var classes = ['tyt-sb-cal-day'];
        if (iso === todayIso) classes.push('today');
        if (selected && iso === tytSbToIso(selected)) classes.push('selected');
        if (minDate && iso < minDate) classes.push('disabled');
        html += '<div class="' + classes.join(' ') + '" data-date="' + iso + '">' + day + '</div>';
      }
      html += '</div><div class="tyt-sb-cal-footer"><span class="tyt-sb-cal-clear">Clear</span><span class="tyt-sb-cal-today">Today</span></div>';
      cal.innerHTML = html;

      cal.querySelectorAll('.tyt-sb-cal-nav').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
          e.stopPropagation();
          viewDate.setMonth(viewDate.getMonth() + parseInt(btn.dataset.dir, 10));
          render();
        });
      });
      cal.querySelectorAll('.tyt-sb-cal-day[data-date]:not(.disabled)').forEach(function (el) {
        el.addEventListener('click', function (e) {
          e.stopPropagation();
          var p = el.dataset.date.split('-');
          setDate(new Date(parseInt(p[0], 10), parseInt(p[1], 10) - 1, parseInt(p[2], 10)));
          cal.classList.remove('open');
        });
      });
      var clearEl = cal.querySelector('.tyt-sb-cal-clear');
      if (clearEl) clearEl.addEventListener('click', function (e) {
        e.stopPropagation();
        selected = null;
        hidden.value = '';
        display.value = '';
        render();
      });
      var todayEl = cal.querySelector('.tyt-sb-cal-today');
      if (todayEl) todayEl.addEventListener('click', function (e) {
        e.stopPropagation();
        var t = new Date();
        viewDate = new Date(t);
        setDate(t);
        cal.classList.remove('open');
      });
    }

    display.addEventListener('click', function (e) {
      if (display.disabled) return;
      e.stopPropagation();
      document.querySelectorAll('.tyt-sb-calendar.open').forEach(function (el) { if (el !== cal) el.classList.remove('open'); });
      document.querySelectorAll('.tyt-sb-autocomplete.open').forEach(function (el) { el.classList.remove('open'); });
      if (selected) viewDate = new Date(selected.getFullYear(), selected.getMonth(), 1);
      render();
      cal.classList.add('open');
    });
    document.addEventListener('click', function (e) {
      if (!display.contains(e.target) && !cal.contains(e.target)) cal.classList.remove('open');
    });

    return { setDate: setDate, getDate: function () { return selected; }, getIso: function () { return selected ? tytSbToIso(selected) : null; } };
  }

  var todayDate = new Date();
  var departHidden = document.getElementById('tytSbDepart');
  var returnHidden = document.getElementById('tytSbReturn');

  var departPicker = tytSbSetupDatePicker('tytSbDepartDisplay', 'tytSbDepart', 'tytSbDepartCal', function () {
    return tytSbToIso(todayDate);
  });
  departPicker.setDate(departHidden.value ? tytSbFromIso(departHidden.value) : todayDate);

  var returnPicker = tytSbSetupDatePicker('tytSbReturnDisplay', 'tytSbReturn', 'tytSbReturnCal', function () {
    return departPicker.getIso() || tytSbToIso(todayDate);
  });
  if (returnHidden.value) returnPicker.setDate(tytSbFromIso(returnHidden.value));

  var ret = returnHidden;
  var retDisplay = document.getElementById('tytSbReturnDisplay');

  // On a one-way search, clicking the greyed-out Return field switches to
  // Round Trip and opens its calendar, instead of doing nothing.
  document.getElementById('tytSbReturnField').addEventListener('click', function (e) {
    if (!this.classList.contains('disabled')) return;
    e.stopPropagation();
    tytSbSetTrip('return', document.getElementById('tytSbTabRound'));
    retDisplay.click();
  });

  var multiWrap = document.getElementById('tytSbMultiWrap');
  var multiLegs = document.getElementById('tytSbMultiLegs');
  var multiAddBtn = document.getElementById('tytSbMultiAdd');
  var multiLegCounter = 0;
  var MULTI_MAX_LEGS = 6; // TripJack: Domestic Multi-City supports 2-6 legs total (leg 1 = the main From/To/Departure fields above)

  window.tytSbSetTrip = function (type, btn) {
    document.querySelectorAll('.tyt-sb-tab').forEach(function (t) { t.classList.remove('active'); });
    btn.classList.add('active');
    document.getElementById('tytSbTripType').value = type;

    var returnField = document.getElementById('tytSbReturnField');
    if (type === 'return') {
      returnField.classList.remove('disabled');
      ret.disabled = false;
      ret.required = true;
      retDisplay.disabled = false;
      retDisplay.placeholder = 'Select date';
      // Default a new return date to the day after departure, not today
      // (which could be before the outbound flight).
      if (!returnPicker.getDate() || (departPicker.getIso() && returnPicker.getIso() < departPicker.getIso())) {
        var base = departPicker.getDate() || todayDate;
        returnPicker.setDate(new Date(base.getFullYear(), base.getMonth(), base.getDate() + 1));
      }
    } else {
      returnField.classList.add('disabled');
      ret.disabled = true;
      ret.required = false;
      retDisplay.disabled = true;
      retDisplay.placeholder = '+ Add return';
    }

    if (type === 'multi') {
      multiWrap.style.display = '';
      if (!multiLegs.children.length) {
        tytSbAddMultiLeg();
      }
    } else {
      multiWrap.style.display = 'none';
    }
  };

  function tytSbAddMultiLeg() {
    if (multiLegs.children.length >= MULTI_MAX_LEGS - 1) return;

    var n = ++multiLegCounter;
    var row = document.createElement('div');
    row.className = 'tyt-sb-multi-row';
    row.dataset.leg = n;
    row.innerHTML =
      '<div class="tyt-sb-field">' +
        '<label class="tyt-sb-flabel">From</label>' +
        '<input class="tyt-sb-input" type="text" id="tytSbMcFrom' + n + '" placeholder="Where From?" style="text-transform:uppercase" autocomplete="off">' +
        '<div class="tyt-sb-autocomplete" id="tytSbMcFromAc' + n + '"></div>' +
      '</div>' +
      '<div class="tyt-sb-field">' +
        '<label class="tyt-sb-flabel">To</label>' +
        '<input class="tyt-sb-input" type="text" id="tytSbMcTo' + n + '" placeholder="Where To?" style="text-transform:uppercase" autocomplete="off">' +
        '<div class="tyt-sb-autocomplete" id="tytSbMcToAc' + n + '"></div>' +
      '</div>' +
      '<div class="tyt-sb-field">' +
        '<label class="tyt-sb-flabel">Date</label>' +
        '<input class="tyt-sb-input" type="text" id="tytSbMcDateDisplay' + n + '" placeholder="Select date" autocomplete="off" readonly>' +
        '<input type="hidden" id="tytSbMcDate' + n + '">' +
        '<div class="tyt-sb-calendar" id="tytSbMcDateCal' + n + '"></div>' +
      '</div>' +
      '<button type="button" class="tyt-sb-multi-remove" title="Remove this flight">&times;</button>';

    multiLegs.appendChild(row);

    tytSbSetupAirportField('tytSbMcFrom' + n, 'tytSbMcFromAc' + n);
    tytSbSetupAirportField('tytSbMcTo' + n, 'tytSbMcToAc' + n);
    var legPicker = tytSbSetupDatePicker('tytSbMcDateDisplay' + n, 'tytSbMcDate' + n, 'tytSbMcDateCal' + n, function () {
      return departPicker.getIso() || tytSbToIso(todayDate);
    });
    legPicker.setDate(todayDate);

    row.querySelector('.tyt-sb-multi-remove').addEventListener('click', function () {
      row.remove();
      tytSbUpdateMultiAddState();
    });

    tytSbUpdateMultiAddState();
  }

  function tytSbUpdateMultiAddState() {
    multiAddBtn.disabled = multiLegs.children.length >= MULTI_MAX_LEGS - 1;
  }

  multiAddBtn.addEventListener('click', tytSbAddMultiLeg);

  document.getElementById('tytSbSwap').addEventListener('click', function () {
    var from = document.getElementById('tytSbFrom');
    var to = document.getElementById('tytSbTo');
    var tmp = from.value;
    from.value = to.value;
    to.value = tmp;
  });

  // Airport autocomplete (From / To / Multi-City legs).
  //
  // tytAirports is a short list of popular airports, shown instantly when a
  // field is empty. Typed searches run against the full list in
  // public/data/airports.json — every airport with an IATA code and
  // scheduled passenger service (~4,000, from OurAirports), including
  // alternate names like Bombay / Bangalore / Madras. It's ~90 KB gzipped,
  // so it's fetched only when a guest first focuses an airport field, then
  // cached by the browser.
  var TYT_AIRPORTS_URL = @json(asset('data/airports.json').'?v='.(is_file(public_path('data/airports.json')) ? filemtime(public_path('data/airports.json')) : 0));
  var tytAllAirports = null;
  var tytAirportsLoading = null;

  function tytLoadAirports() {
    if (tytAllAirports) return Promise.resolve(tytAllAirports);
    if (tytAirportsLoading) return tytAirportsLoading;
    tytAirportsLoading = fetch(TYT_AIRPORTS_URL)
      .then(function (r) { if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
      .then(function (rows) {
        // Row: [code, city, airportName, country, sizeRank, keywords?]
        tytAllAirports = rows.map(function (r) {
          return { code: r[0], city: r[1], airport: r[2], country: r[3], rank: r[4], kw: r[5] || '',
            _code: r[0].toLowerCase(), _city: r[1].toLowerCase(), _name: r[2].toLowerCase(), _kw: (r[5] || '').toLowerCase() };
        });
        return tytAllAirports;
      })
      .catch(function () { tytAirportsLoading = null; return null; });
    return tytAirportsLoading;
  }

  // Lower score = better match; null = no match.
  function tytAirportScore(a, term) {
    // An exact code and a city-name start rank equally, so "goa" finds Goa
    // (GOI/GOX) before Genoa (whose code is GOA), and "sur" finds Surat
    // before Summer Beaver (SUR) — the India/size tie-breaks decide.
    if (a._code === term || a._city.indexOf(term) === 0) return 1;
    if (term.length <= 3 && a._code.indexOf(term) === 0) return 2;
    if ((' ' + a._kw).indexOf(' ' + term) !== -1 || (', ' + a._kw).indexOf(', ' + term) !== -1) return 3;
    if ((' ' + a._name).indexOf(' ' + term) !== -1 || (' ' + a._city).indexOf(' ' + term) !== -1) return 4;
    if (term.length >= 3 && (a._city.indexOf(term) !== -1 || a._name.indexOf(term) !== -1 || a._kw.indexOf(term) !== -1)) return 5;
    return null;
  }

  function tytSearchAllAirports(term) {
    var scored = [];
    tytAllAirports.forEach(function (a) {
      var s = tytAirportScore(a, term);
      if (s !== null) scored.push([s, a]);
    });
    scored.sort(function (x, y) {
      return x[0] - y[0]
        // This is an Indian travel site: Indian airports first on ties,
        // then bigger airports before smaller ones.
        || (x[1].country === 'India' ? 0 : 1) - (y[1].country === 'India' ? 0 : 1)
        || x[1].rank - y[1].rank
        // Among equals, the airports on the popular list (e.g. Heathrow
        // over Gatwick for "london").
        || (tytPopularCodes[x[1].code] ? 0 : 1) - (tytPopularCodes[y[1].code] ? 0 : 1)
        || x[1].city.localeCompare(y[1].city);
    });
    return scored.slice(0, 8).map(function (p) { return p[1]; });
  }

  function tytEsc(s) {
    return String(s).replace(/[&<>"']/g, function (c) {
      return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
    });
  }

  var tytAirports = [
    { code: 'DEL', city: 'Delhi', airport: 'Delhi Indira Gandhi Intl', country: 'India' },
    { code: 'BOM', city: 'Mumbai', airport: 'Chhatrapati Shivaji Maharaj Intl', country: 'India' },
    { code: 'BLR', city: 'Bengaluru', airport: 'Kempegowda Intl', country: 'India' },
    { code: 'MAA', city: 'Chennai', airport: 'Chennai Intl', country: 'India' },
    { code: 'CCU', city: 'Kolkata', airport: 'Netaji Subhas Chandra Bose Intl', country: 'India' },
    { code: 'HYD', city: 'Hyderabad', airport: 'Rajiv Gandhi Intl', country: 'India' },
    { code: 'COK', city: 'Kochi', airport: 'Cochin Intl', country: 'India' },
    { code: 'GOI', city: 'Goa', airport: 'Goa Intl (Dabolim)', country: 'India' },
    { code: 'GOX', city: 'Goa', airport: 'Manohar Intl (Mopa)', country: 'India' },
    { code: 'PNQ', city: 'Pune', airport: 'Pune Airport', country: 'India' },
    { code: 'AMD', city: 'Ahmedabad', airport: 'Sardar Vallabhbhai Patel Intl', country: 'India' },
    { code: 'JAI', city: 'Jaipur', airport: 'Jaipur Intl', country: 'India' },
    { code: 'LKO', city: 'Lucknow', airport: 'Chaudhary Charan Singh Intl', country: 'India' },
    { code: 'IXC', city: 'Chandigarh', airport: 'Chandigarh Airport', country: 'India' },
    { code: 'GAU', city: 'Guwahati', airport: 'Lokpriya Gopinath Bordoloi Intl', country: 'India' },
    { code: 'IXZ', city: 'Port Blair', airport: 'Veer Savarkar Intl', country: 'India' },
    { code: 'PAT', city: 'Patna', airport: 'Jay Prakash Narayan Airport', country: 'India' },
    { code: 'IXB', city: 'Bagdogra', airport: 'Bagdogra Airport', country: 'India' },
    { code: 'BBI', city: 'Bhubaneswar', airport: 'Biju Patnaik Intl', country: 'India' },
    { code: 'NAG', city: 'Nagpur', airport: 'Dr. Babasaheb Ambedkar Intl', country: 'India' },
    { code: 'SXR', city: 'Srinagar', airport: 'Srinagar Airport', country: 'India' },
    { code: 'IXJ', city: 'Jammu', airport: 'Jammu Airport', country: 'India' },
    { code: 'ATQ', city: 'Amritsar', airport: 'Sri Guru Ram Dass Jee Intl', country: 'India' },
    { code: 'VNS', city: 'Varanasi', airport: 'Lal Bahadur Shastri Airport', country: 'India' },
    { code: 'UDR', city: 'Udaipur', airport: 'Maharana Pratap Airport', country: 'India' },
    { code: 'DXB', city: 'Dubai', airport: 'Dubai Intl', country: 'UAE' },
    { code: 'AUH', city: 'Abu Dhabi', airport: 'Zayed Intl', country: 'UAE' },
    { code: 'DOH', city: 'Doha', airport: 'Hamad Intl', country: 'Qatar' },
    { code: 'SIN', city: 'Singapore', airport: 'Changi Airport', country: 'Singapore' },
    { code: 'BKK', city: 'Bangkok', airport: 'Suvarnabhumi Airport', country: 'Thailand' },
    { code: 'KUL', city: 'Kuala Lumpur', airport: 'Kuala Lumpur Intl', country: 'Malaysia' },
    { code: 'LHR', city: 'London', airport: 'Heathrow Airport', country: 'United Kingdom' },
    { code: 'JFK', city: 'New York', airport: 'John F. Kennedy Intl', country: 'USA' },
    { code: 'ORD', city: 'Chicago', airport: "O'Hare Intl", country: 'USA' },
    { code: 'SFO', city: 'San Francisco', airport: 'San Francisco Intl', country: 'USA' },
    { code: 'YYZ', city: 'Toronto', airport: 'Toronto Pearson Intl', country: 'Canada' },
    { code: 'CDG', city: 'Paris', airport: 'Charles de Gaulle Airport', country: 'France' },
    { code: 'FRA', city: 'Frankfurt', airport: 'Frankfurt Airport', country: 'Germany' },
    { code: 'IST', city: 'Istanbul', airport: 'Istanbul Airport', country: 'Turkey' },
    { code: 'HKG', city: 'Hong Kong', airport: 'Hong Kong Intl', country: 'Hong Kong' },
    { code: 'SYD', city: 'Sydney', airport: 'Sydney Kingsford Smith', country: 'Australia' },
    { code: 'KTM', city: 'Kathmandu', airport: 'Tribhuvan Intl', country: 'Nepal' },
    { code: 'CMB', city: 'Colombo', airport: 'Bandaranaike Intl', country: 'Sri Lanka' }
  ];
  var tytPopularCodes = {};
  tytAirports.forEach(function (a) { tytPopularCodes[a.code] = true; });

  function tytSbSetupAirportField(inputId, acId) {
    var input = document.getElementById(inputId);
    var ac = document.getElementById(acId);
    var hoverIdx = -1;
    var matches = [];

    function render(list, pending) {
      ac.innerHTML = '';
      if (!list.length) {
        ac.innerHTML = '<div class="tyt-sb-ac-empty">' + (pending ? 'Searching airports…' : 'No airports found') + '</div>';
        ac.classList.add('open');
        return;
      }
      list.forEach(function (a, idx) {
        var row = document.createElement('div');
        row.className = 'tyt-sb-ac-item' + (idx === hoverIdx ? ' hover' : '');
        row.innerHTML = '<div><div class="tyt-sb-ac-city">' + tytEsc(a.city) + ', <span class="tyt-sb-ac-code">' + tytEsc(a.code) + '</span></div>' +
          '<div class="tyt-sb-ac-airport">' + tytEsc(a.airport) + '</div></div>' +
          '<div class="tyt-sb-ac-country">' + tytEsc(a.country) + '</div>';
        row.addEventListener('mousedown', function (e) {
          e.preventDefault();
          input.value = a.code;
          ac.classList.remove('open');
        });
        ac.appendChild(row);
      });
      ac.classList.add('open');
    }

    function search(rawTerm) {
      var term = rawTerm.trim().toLowerCase();
      hoverIdx = -1;
      if (!term) {
        matches = tytAirports.slice(0, 8);
        render(matches);
        return;
      }
      if (tytAllAirports) {
        matches = tytSearchAllAirports(term);
        render(matches);
        return;
      }
      // Full list still loading: show popular matches straight away, then
      // redo the search once it arrives (if the guest is still on this field
      // and hasn't typed something else).
      matches = tytAirports.filter(function (a) {
        return a.code.toLowerCase().indexOf(term) === 0
          || a.city.toLowerCase().indexOf(term) !== -1
          || a.airport.toLowerCase().indexOf(term) !== -1;
      }).slice(0, 8);
      render(matches, true);
      tytLoadAirports().then(function (all) {
        if (all && document.activeElement === input && input.value === rawTerm) search(rawTerm);
        else if (!all && !matches.length && input.value === rawTerm) render([]);
      });
    }

    input.addEventListener('focus', function () {
      tytLoadAirports();
      document.querySelectorAll('.tyt-sb-autocomplete.open').forEach(function (el) {
        if (el !== ac) el.classList.remove('open');
      });
      search(input.value);
    });
    input.addEventListener('input', function () { search(input.value); });
    input.addEventListener('keydown', function (e) {
      if (!ac.classList.contains('open') || !matches.length) return;
      if (e.key === 'ArrowDown') {
        e.preventDefault();
        hoverIdx = Math.min(hoverIdx + 1, matches.length - 1);
        render(matches);
      } else if (e.key === 'ArrowUp') {
        e.preventDefault();
        hoverIdx = Math.max(hoverIdx - 1, 0);
        render(matches);
      } else if (e.key === 'Enter') {
        e.preventDefault();
        if (hoverIdx >= 0) {
          input.value = matches[hoverIdx].code;
          ac.classList.remove('open');
        }
      } else if (e.key === 'Escape') {
        ac.classList.remove('open');
      }
    });
    document.addEventListener('click', function (e) {
      if (!input.contains(e.target) && !ac.contains(e.target)) ac.classList.remove('open');
    });
  }

  tytSbSetupAirportField('tytSbFrom', 'tytSbFromAc');
  tytSbSetupAirportField('tytSbTo', 'tytSbToAc');

  // Passengers & class dropdown
  var paxState = {
    adults: parseInt(document.getElementById('tytSbAdultsInput').value, 10) || 1,
    children: parseInt(document.getElementById('tytSbChildrenInput').value, 10) || 0,
    infants: parseInt(document.getElementById('tytSbInfantsInput').value, 10) || 0,
    cabinClass: document.getElementById('tytSbClassInput').value || 'ECONOMY'
  };
  var panel = document.getElementById('tytSbPaxPanel');
  var PAX_SEATED_MAX = 9;

  function updatePaxSummary() {
    var total = paxState.adults + paxState.children + paxState.infants;
    var classOpt = document.querySelector('.tyt-sb-class-opt[data-class="' + paxState.cabinClass + '"]');
    var classLabel = classOpt ? classOpt.textContent : 'Economy';
    document.getElementById('tytSbPaxSummary').textContent = total + ' Passenger' + (total > 1 ? 's' : '') + ' | ' + classLabel;
    document.getElementById('tytSbAdultsInput').value = paxState.adults;
    document.getElementById('tytSbChildrenInput').value = paxState.children;
    document.getElementById('tytSbInfantsInput').value = paxState.infants;
    document.getElementById('tytSbClassInput').value = paxState.cabinClass;
  }

  document.getElementById('tytSbPaxToggle').addEventListener('click', function (e) {
    e.stopPropagation();
    panel.classList.toggle('open');
  });
  document.getElementById('tytSbPaxDone').addEventListener('click', function () {
    panel.classList.remove('open');
  });
  document.addEventListener('click', function (e) {
    if (!document.getElementById('tytSbPaxWrap').contains(e.target)) panel.classList.remove('open');
  });

  panel.querySelectorAll('[data-pax]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var key = btn.dataset.pax;
      var dir = parseInt(btn.dataset.dir, 10);
      var next = paxState[key] + dir;
      // TripJack: adults + children together at most 9; one infant per adult.
      var max = key === 'infants' ? paxState.adults
        : PAX_SEATED_MAX - (key === 'adults' ? paxState.children : paxState.adults);
      var min = key === 'adults' ? 1 : 0;
      if (next < min || next > max) return;
      paxState[key] = next;
      if (paxState.infants > paxState.adults) paxState.infants = paxState.adults;
      ['adults', 'children', 'infants'].forEach(function (k) {
        document.getElementById('tytSb' + k.charAt(0).toUpperCase() + k.slice(1) + 'Val').textContent = paxState[k];
      });
      updatePaxSummary();
    });
  });

  document.querySelectorAll('.tyt-sb-class-opt').forEach(function (opt) {
    opt.addEventListener('click', function () {
      document.querySelectorAll('.tyt-sb-class-opt').forEach(function (o) { o.classList.remove('active'); });
      opt.classList.add('active');
      paxState.cabinClass = opt.dataset.class;
      updatePaxSummary();
    });
  });

  updatePaxSummary();

  document.getElementById('tytSbForm').addEventListener('submit', function (e) {
    document.getElementById('tytSbFrom').value = document.getElementById('tytSbFrom').value.trim().toUpperCase();
    document.getElementById('tytSbTo').value = document.getElementById('tytSbTo').value.trim().toUpperCase();

    var form = e.target;
    form.querySelectorAll('input[data-mc-injected]').forEach(function (el) { el.remove(); });

    // Disabled fields aren't submitted, so this keeps empty values like
    // "preferred_airline=" out of the results URL.
    form.querySelectorAll('input[name], select[name]').forEach(function (el) {
      if (!el.disabled && el.type !== 'radio' && el.type !== 'checkbox' && el.value === '') {
        el.disabled = true;
        el.dataset.emptyOmitted = '1';
      }
    });

    if (document.getElementById('tytSbTripType').value !== 'multi') return;

    function addLeg(idx, from, to, date) {
      [['from', from], ['to', to], ['date', date]].forEach(function (pair) {
        var input = document.createElement('input');
        input.type = 'hidden';
        input.setAttribute('data-mc-injected', '1');
        input.name = 'legs[' + idx + '][' + pair[0] + ']';
        input.value = pair[1];
        form.appendChild(input);
      });
    }

    addLeg(0, document.getElementById('tytSbFrom').value, document.getElementById('tytSbTo').value, document.getElementById('tytSbDepart').value);

    var legRows = multiLegs.querySelectorAll('.tyt-sb-multi-row');
    legRows.forEach(function (row, i) {
      var n = row.dataset.leg;
      var from = (document.getElementById('tytSbMcFrom' + n).value || '').trim().toUpperCase();
      var to = (document.getElementById('tytSbMcTo' + n).value || '').trim().toUpperCase();
      var date = document.getElementById('tytSbMcDate' + n).value;
      if (from && to && date) addLeg(i + 1, from, to, date);
    });
  });

  // Back-button restores can bring the page back with those fields still disabled.
  window.addEventListener('pageshow', function () {
    document.querySelectorAll('#tytSbForm [data-empty-omitted]').forEach(function (el) {
      el.disabled = false;
      delete el.dataset.emptyOmitted;
    });
  });
})();
</script>
@endpush
