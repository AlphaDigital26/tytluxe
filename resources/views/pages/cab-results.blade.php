@extends('layouts.frontend')

@section('meta_title', 'Cabs — '.($query['from'] ?? '').' to '.($query['to'] ?? '').' | TYT Luxe')

@php
  $options = $result['options'] ?? [];
  $journey = $result['journeyInfo'] ?? [];
  $pickup = \Illuminate\Support\Carbon::parse($search['pickupAt']);
  $return = $search['returnAt'] ? \Illuminate\Support\Carbon::parse($search['returnAt']) : null;
  $categories = collect($options)->pluck('vehicleCategory')->filter()->unique()->values();
  $types = collect($options)->pluck('vehicleType')->filter()->unique()->values();
  $short = fn ($p) => \App\Services\CabBookingService::shortPlace((string) $p);
@endphp

@push('styles')
<style>
.cr-page{background:#0a0a0a;color:#fff;font-family:'Jost',sans-serif;padding:110px 0 70px;min-height:70vh}
.cr-top{max-width:1180px;margin:0 auto 26px;padding:0 24px}
.cr-summary{display:flex;justify-content:space-between;align-items:center;gap:14px;flex-wrap:wrap;background:#141414;border:1px solid rgba(201,168,76,0.25);border-radius:14px;padding:16px 20px}
.cr-route{font-size:17px;font-weight:600}
.cr-meta{font-size:13px;color:rgba(255,255,255,0.65);margin-top:3px}
.cr-modify{padding:10px 18px;border:1.5px solid rgba(201,168,76,0.5);border-radius:10px;background:transparent;color:#C9A84C;font-family:'Poppins',sans-serif;font-size:11px;font-weight:600;letter-spacing:1.5px;text-transform:uppercase;cursor:pointer}
.cr-modify-panel{margin-top:16px}
.cr-wrap{max-width:1180px;margin:0 auto;padding:0 24px;display:flex;gap:22px;align-items:flex-start;flex-wrap:wrap}
.cr-side{flex:1 1 240px;max-width:270px;background:#141414;border:1px solid rgba(255,255,255,0.08);border-radius:14px;padding:20px;position:sticky;top:110px}
.cr-side h2{font-size:15px;margin:0 0 16px;display:flex;justify-content:space-between;align-items:center}
.cr-side h2 button{background:none;border:none;color:#e8c96b;font-family:inherit;font-size:12px;cursor:pointer}
.cr-group{margin-bottom:20px}
.cr-group-title{font-size:10.5px;font-weight:600;letter-spacing:.12em;text-transform:uppercase;color:#C9A84C;margin-bottom:10px}
.cr-group label{display:flex;gap:9px;align-items:center;font-size:13.5px;color:rgba(255,255,255,0.85);padding:4px 0;cursor:pointer}
.cr-group input{accent-color:#C9A84C;width:16px;height:16px}
.cr-list{flex:999 1 560px;min-width:0;display:flex;flex-direction:column;gap:14px}
.cr-count{font-size:14px;color:rgba(255,255,255,0.75)}
.cr-card{background:#141414;border:1px solid rgba(255,255,255,0.08);border-radius:14px;padding:18px 20px;display:flex;gap:20px;align-items:center;flex-wrap:wrap;transition:border-color .2s}
.cr-card:hover{border-color:rgba(201,168,76,0.35)}
.cr-img{width:150px;height:92px;border-radius:10px;background:#f6f1e7;display:flex;align-items:center;justify-content:center;overflow:hidden;flex-shrink:0}
.cr-img img{max-width:100%;max-height:100%;object-fit:contain}
.cr-info{flex:1 1 260px;min-width:0;display:flex;flex-direction:column;gap:6px}
.cr-name{display:flex;gap:10px;align-items:center;flex-wrap:wrap}
.cr-page .cr-name h3{margin:0;font-family:'Jost',sans-serif;font-size:17px;font-weight:600;color:#fff;line-height:1.3}
.cr-page .cr-side h2{font-family:'Jost',sans-serif;color:#fff}
.cr-page .cr-details-grid h4{font-family:'Jost',sans-serif}
.cr-badge{font-size:10px;font-weight:600;letter-spacing:.08em;text-transform:uppercase;color:#e8c96b;border:1px solid rgba(201,168,76,0.4);padding:3px 9px;border-radius:100px}
.cr-similar{font-size:12.5px;color:rgba(255,255,255,0.65)}
.cr-facts{display:flex;gap:16px;flex-wrap:wrap;font-size:12.5px;color:rgba(255,255,255,0.85)}
.cr-free{font-size:12px;color:#4ade80}
.cr-price{display:flex;flex-direction:column;align-items:flex-end;gap:6px;min-width:150px;margin-left:auto}
.cr-amount{font-size:22px;font-weight:600}
.cr-tax{font-size:11.5px;color:rgba(255,255,255,0.65)}
.cr-book{padding:11px 26px;border-radius:10px;background:linear-gradient(90deg,#C9A84C,#e8c96b);color:#0a0a0a;font-family:'Poppins',sans-serif;font-size:11px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;text-decoration:none}
.cr-book:hover{opacity:.9;color:#0a0a0a}
.cr-details{width:100%;border-top:1px dashed rgba(255,255,255,0.1);padding-top:12px;font-size:13px;color:rgba(255,255,255,0.8)}
.cr-details summary{cursor:pointer;color:#e8c96b;font-size:12.5px;list-style:none}
.cr-details summary::-webkit-details-marker{display:none}
.cr-details-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px;margin-top:12px;line-height:1.7}
.cr-details-grid h4{margin:0 0 4px;font-size:12px;letter-spacing:.08em;text-transform:uppercase;color:#C9A84C}
.cr-details-grid ul{margin:0;padding-left:18px}
.cr-empty,.cr-error{background:#141414;border:1px solid rgba(255,255,255,0.08);border-radius:14px;padding:40px 24px;text-align:center;color:rgba(255,255,255,0.8);font-size:15px}
.cr-error{border-color:rgba(220,80,80,0.3);color:#f3a3a3}
@media(max-width:860px){
  .cr-side{position:static;max-width:none}
  .cr-price{flex-direction:row;align-items:center;justify-content:space-between;width:100%;margin-left:0}
  .cr-details-grid{grid-template-columns:1fr}
}
@media(max-width:560px){.cr-page{padding-top:90px}.cr-img{width:100%;height:120px}}
</style>
@endpush

@section('content')
<div class="cr-page">
  <div class="cr-top">
    <div class="cr-summary">
      <div>
        <div class="cr-route">{{ $short($query['from'] ?? '') }} → {{ $short($query['to'] ?? '') }}</div>
        <div class="cr-meta">
          {{ \App\Services\CabBookingService::JOURNEY_TYPES[$search['journeyType']] ?? 'Cab' }} · {{ $return ? 'Round trip' : 'One way' }}
          · {{ $pickup->format('D, j M · g:i A') }}@if($return) – {{ $return->format('D, j M · g:i A') }}@endif
          · {{ $search['passengers'] }} {{ $search['passengers'] == 1 ? 'passenger' : 'passengers' }}
          @if(!empty($journey['distance'])) · {{ $journey['distance'] }}@endif
          @if(!empty($journey['duration'])) · about {{ $journey['duration'] }} min @endif
        </div>
      </div>
      <button type="button" class="cr-modify" aria-expanded="false" aria-controls="crModify" onclick="var p=document.getElementById('crModify');p.hidden=!p.hidden;this.setAttribute('aria-expanded',String(!p.hidden));">Modify search</button>
    </div>
    <div class="cr-modify-panel" id="crModify" hidden>
      @include('partials.cab-search-widget', ['q' => $query])
    </div>
  </div>

  <div class="cr-wrap">
    @if($error)
      <div class="cr-list"><div class="cr-error" role="alert">{{ $error }}</div></div>
    @elseif(empty($options))
      <div class="cr-list"><div class="cr-empty">No cabs are available for this trip and time. Please try a different pickup time, or call us on +91 98750 73788.</div></div>
    @else
      <aside class="cr-side" aria-label="Filters">
        <h2>Filters <button type="button" id="crReset">Reset</button></h2>
        @if($types->count() > 1)
          <div class="cr-group">
            <div class="cr-group-title">Vehicle type</div>
            @foreach($types as $t)
              <label><input type="checkbox" class="cr-filter" data-key="type" value="{{ $t }}" checked> {{ $t }}</label>
            @endforeach
          </div>
        @endif
        @if($categories->count() > 1)
          <div class="cr-group">
            <div class="cr-group-title">Category</div>
            @foreach($categories as $c)
              <label><input type="checkbox" class="cr-filter" data-key="category" value="{{ $c }}" checked> {{ $c }}</label>
            @endforeach
          </div>
        @endif
        <div class="cr-group">
          <div class="cr-group-title">Sort by</div>
          <label><input type="radio" name="crSort" value="price" checked> Lowest price</label>
          <label><input type="radio" name="crSort" value="seats"> Most seats</label>
        </div>
      </aside>

      <section class="cr-list" aria-label="Available cabs">
        <div class="cr-count" id="crCount">{{ count($options) }} {{ count($options) === 1 ? 'cab' : 'cabs' }} found</div>
        @foreach($options as $o)
          @php $p = $o['policies'] ?? []; $free = collect($p['cancellationPolicy'] ?? [])->where('refundPercentage', '>=', 100)->sortBy('minHours')->first(); @endphp
          <article class="cr-card" data-type="{{ $o['vehicleType'] }}" data-category="{{ $o['vehicleCategory'] }}" data-price="{{ $o['price']['total'] }}" data-seats="{{ $o['seats'] }}">
            <div class="cr-img">
              @if($o['image'])
                <img src="{{ $o['image'] }}" alt="{{ $o['label'] }}" loading="lazy" referrerpolicy="no-referrer">
              @endif
            </div>
            <div class="cr-info">
              <div class="cr-name">
                <h3>{{ $o['label'] }}</h3>
                @if($o['vehicleCategory'])<span class="cr-badge">{{ $o['vehicleCategory'] }}</span>@endif
              </div>
              @if($o['similar'])<div class="cr-similar">{{ $o['similar'] }}</div>@endif
              <div class="cr-facts">
                <span>{{ $o['seats'] }} seats</span>
                <span>{{ $o['bags'] }} bags</span>
                @if(!empty($p['waitingTime']))<span>{{ $p['waitingTime'] }}</span>@endif
                @if(!empty($p['meetAndGreet']))<span>Meet &amp; greet</span>@endif
              </div>
              @if($free)
                <div class="cr-free">✓ Free cancellation up to {{ $free['minHours'] }} hours before pickup</div>
              @endif
            </div>
            <div class="cr-price">
              <div>
                <div class="cr-amount">&#8377;{{ number_format($o['price']['total']) }}</div>
                <div class="cr-tax">All-inclusive{{ $return ? ', both ways' : '' }}</div>
              </div>
              <a class="cr-book" href="{{ route('cabs.review', ['search' => $searchId, 'cab' => $o['key']]) }}">Book</a>
            </div>
            @if($p)
              <details class="cr-details">
                <summary>Inclusions &amp; policies</summary>
                <div class="cr-details-grid">
                  @if(!empty($p['inclusions']))<div><h4>Included</h4><ul>@foreach($p['inclusions'] as $x)<li>{{ $x }}</li>@endforeach</ul></div>@endif
                  @if(!empty($p['exclusions']))<div><h4>Not included</h4><ul>@foreach($p['exclusions'] as $x)<li>{{ $x }}</li>@endforeach</ul></div>@endif
                  @if(!empty($p['cancellationPolicy']))<div><h4>Cancellation</h4><ul>@foreach(collect($p['cancellationPolicy'])->sortByDesc('minHours') as $c)<li>{{ $c['description'] ?? '' }}: {{ (float) ($c['refundPercentage'] ?? 0) }}% refund</li>@endforeach</ul></div>@endif
                  @if(!empty($p['baggagePolicy']))<div><h4>Luggage</h4><ul>@foreach($p['baggagePolicy'] as $x)<li>{{ $x }}</li>@endforeach</ul></div>@endif
                </div>
              </details>
            @endif
          </article>
        @endforeach
      </section>
    @endif
  </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
  var list = document.querySelector('.cr-list[aria-label]');
  if (!list) return;
  var cards = Array.prototype.slice.call(list.querySelectorAll('.cr-card'));
  var count = document.getElementById('crCount');

  function apply() {
    var on = {};
    document.querySelectorAll('.cr-filter').forEach(function (f) {
      on[f.dataset.key] = on[f.dataset.key] || [];
      if (f.checked) on[f.dataset.key].push(f.value);
    });
    var shown = 0;
    cards.forEach(function (c) {
      var ok = (!on.type || on.type.indexOf(c.dataset.type) > -1) && (!on.category || on.category.indexOf(c.dataset.category) > -1);
      c.hidden = !ok;
      if (ok) shown++;
    });
    count.textContent = shown + (shown === 1 ? ' cab' : ' cabs') + ' found';
    var by = document.querySelector('input[name="crSort"]:checked').value;
    cards.slice().sort(function (a, b) {
      return by === 'seats' ? (b.dataset.seats - a.dataset.seats) || (a.dataset.price - b.dataset.price) : a.dataset.price - b.dataset.price;
    }).forEach(function (c) { list.appendChild(c); });
  }
  document.querySelectorAll('.cr-filter, input[name="crSort"]').forEach(function (el) { el.addEventListener('change', apply); });
  document.getElementById('crReset').addEventListener('click', function () {
    document.querySelectorAll('.cr-filter').forEach(function (f) { f.checked = true; });
    document.querySelector('input[name="crSort"][value="price"]').checked = true;
    apply();
  });
})();
</script>
@endpush
