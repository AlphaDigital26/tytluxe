@extends('layouts.frontend')

@section('meta_title', 'Cab Booking — Airport Transfers, Outstation & Local Cabs | TYT Luxe')
@section('meta_description', 'Book chauffeur-driven cabs with TYT Luxe: airport pickups and drops, outstation trips and local rides, with all-inclusive prices and free cancellation up to 24 hours before pickup.')

@push('styles')
<style>
.tyt-cabs{font-family:'Poppins',sans-serif;width:100%;background:#0a0a0a}
.cab-sb-outer{position:relative;z-index:5;max-width:1180px;margin:-46px auto 0;padding:0 24px}
.cab-notice{max-width:760px;margin:0 auto;padding:22px 26px;border-radius:14px;background:rgba(201,168,76,0.1);border:1px solid rgba(201,168,76,0.4);color:#f3e6c2;font-family:'Jost',sans-serif;font-size:15px;text-align:center}
.cab-flash{max-width:1132px;margin:0 auto 14px;padding:12px 16px;border-radius:12px;background:rgba(220,80,80,0.08);border:1px solid rgba(220,80,80,0.3);color:#f3a3a3;font-family:'Jost',sans-serif;font-size:14px}
.tyt-dark{background:#111111;padding:64px 24px}
/* Same centred column as the search bar, so every edge lines up. */
.cab-inner{max-width:1132px;margin:0 auto}
#book-cab{scroll-margin-top:150px}
/* Recent searches + upcoming flights */
.cab-extras{max-width:1180px;margin:0 auto;padding:40px 24px 8px;display:flex;flex-direction:column;gap:36px}
.cab-extras:not(:has(section:not([hidden]))){display:none}
.cab-sec-head{display:flex;align-items:baseline;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-bottom:16px}
.tyt-cabs .cab-sec-head h2{font-family:'Playfair Display',serif;font-size:24px;font-weight:700;color:#fff;margin:0}
.cab-sec-sub{font-size:12.5px;color:rgba(255,255,255,0.6)}
.cab-link-btn{background:none;border:none;color:#C9A84C;font-family:'Poppins',sans-serif;font-size:12px;cursor:pointer;padding:4px}
.cab-recent-list{display:grid;grid-template-columns:repeat(auto-fill,minmax(250px,1fr));gap:14px}
.cab-recent-card{display:flex;flex-direction:column;gap:6px;text-align:left;background:#141414;border:1px solid rgba(255,255,255,0.08);border-radius:14px;padding:16px 18px;cursor:pointer;font-family:'Poppins',sans-serif;color:#fff;transition:border-color .2s,transform .2s}
.cab-recent-card:hover{border-color:rgba(201,168,76,0.5);transform:translateY(-2px)}
.cab-recent-top{display:flex;justify-content:space-between;gap:10px;align-items:center}
.cab-recent-route{font-size:13.5px;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.cab-recent-tag{flex-shrink:0;font-size:10px;font-weight:600;letter-spacing:.06em;text-transform:uppercase;color:#C9A84C}
.cab-recent-meta{font-size:12px;color:rgba(255,255,255,0.65)}
.cab-recent-meta.past{color:#e8c96b}
.cab-table-wrap{background:#141414;border:1px solid rgba(255,255,255,0.08);border-radius:14px;overflow-x:auto}
.cab-table{width:100%;border-collapse:collapse;font-size:13.5px;color:rgba(255,255,255,0.85);min-width:640px}
.cab-table th{text-align:left;font-size:10.5px;font-weight:600;letter-spacing:.1em;text-transform:uppercase;color:#C9A84C;padding:14px 18px;border-bottom:1px solid rgba(255,255,255,0.08)}
.cab-table td{padding:14px 18px;border-bottom:1px solid rgba(255,255,255,0.06);vertical-align:middle}
.cab-table tr:last-child td{border-bottom:none}
.cab-table td a{color:#e8c96b}
.cab-td-sub{font-size:11.5px;color:rgba(255,255,255,0.55);margin-top:2px}
.cab-td-act{text-align:right}
.cab-table td a.cab-add-btn{display:inline-flex;align-items:center;gap:8px;padding:9px 16px;border:1.5px solid rgba(201,168,76,0.6);border-radius:10px;color:#C9A84C;font-family:'Poppins',sans-serif;font-size:11px;font-weight:600;letter-spacing:1px;text-transform:uppercase;text-decoration:none;white-space:nowrap;transition:background .2s,color .2s}
.cab-table td a.cab-add-btn:hover{background:#C9A84C;color:#0a0a0a}
.sr-only{position:absolute;width:1px;height:1px;overflow:hidden;clip:rect(0,0,0,0)}
html:has(.tyt-cabs){scroll-behavior:smooth}
.tyt-feat-wrap{background:#0a0a0a;border-top:1px solid rgba(201,168,76,0.18);padding:0 24px}
.tyt-feat-wrap .tyt-feat-row{max-width:1132px;margin:0 auto;border-top:none}
/* Outer text lines up with the cards above; equal space either side of each divider. */
.tyt-feat-wrap .tyt-feat{padding:32px 28px}
.tyt-feat-wrap .tyt-feat:first-child{padding-left:0}
.tyt-feat-wrap .tyt-feat:last-child{padding-right:0}
@media(max-width:768px){
  .tyt-feat-wrap .tyt-feat,.tyt-feat-wrap .tyt-feat:first-child,.tyt-feat-wrap .tyt-feat:last-child{padding:24px 16px}
  .tyt-feat-wrap .tyt-feat:nth-child(odd){padding-left:0}
  .tyt-feat-wrap .tyt-feat:nth-child(even){padding-right:0}
}
.tyt-label{font-size:10px;font-weight:600;letter-spacing:3px;text-transform:uppercase;color:#C9A84C;margin-bottom:10px}
.tyt-title-light{font-family:'Playfair Display',serif;font-size:36px;font-weight:800;color:#fff;line-height:1.15;margin:0 0 36px}
.cab-kinds{display:grid;grid-template-columns:repeat(3,1fr);gap:14px}
.cab-kind{position:relative;display:flex;flex-direction:column;gap:14px;background:linear-gradient(160deg,#171717 0%,#111 100%);border:1px solid rgba(255,255,255,0.08);border-radius:14px;padding:28px 26px 24px;text-decoration:none;overflow:hidden;transition:border-color .25s,transform .25s,box-shadow .25s}
.cab-kind::before{content:'';position:absolute;left:0;top:0;right:0;height:2px;background:linear-gradient(90deg,transparent,#C9A84C,transparent);opacity:0;transition:opacity .25s}
.cab-kind:hover{border-color:rgba(201,168,76,0.45);transform:translateY(-3px);box-shadow:0 18px 40px rgba(0,0,0,0.45)}
.cab-kind:hover::before{opacity:1}
.cab-kind-icon{width:52px;height:52px;border-radius:50%;border:1px solid rgba(201,168,76,0.5);background:rgba(201,168,76,0.08);display:flex;align-items:center;justify-content:center;color:#C9A84C;font-size:20px;transition:background .25s,color .25s}
.cab-kind:hover .cab-kind-icon{background:#C9A84C;color:#0a0a0a}
.tyt-cabs .cab-kind h3{font-family:'Playfair Display',serif;font-size:21px;color:#fff;margin:0}
.cab-kind p{font-size:13px;font-weight:300;color:rgba(255,255,255,0.7);line-height:1.6;margin:0;flex:1}
.cab-kind-cta{font-family:'Poppins',sans-serif;font-size:10.5px;font-weight:600;letter-spacing:2px;text-transform:uppercase;color:#C9A84C;display:inline-flex;align-items:center;gap:8px}
.cab-kind-cta i{transition:transform .25s}
.cab-kind:hover .cab-kind-cta i{transform:translateX(4px)}
.tyt-feat{display:flex;align-items:flex-start;gap:16px}
.tyt-feat-icon{flex-shrink:0;width:44px;height:44px;border-radius:50%;border:1px solid rgba(201,168,76,0.5);display:flex;align-items:center;justify-content:center;color:#C9A84C;font-size:16px}
.tyt-feat-row{display:grid;grid-template-columns:repeat(4,1fr);background:#0a0a0a;border-top:1px solid rgba(201,168,76,0.18)}
.tyt-feat{padding:32px;border-right:1px solid rgba(201,168,76,0.18)}
.tyt-feat:last-child{border-right:none}
.tyt-feat-title{font-size:14px;font-weight:500;color:#fff;margin:0 0 5px}
.tyt-feat-desc{font-size:13px;color:rgba(255,255,255,0.65);font-weight:300;line-height:1.55;margin:0}
@media(max-width:768px){
  .tyt-dark{padding:40px 20px}
  .tyt-title-light{font-size:26px}
  .cab-kinds{grid-template-columns:1fr}
  .tyt-feat-row{grid-template-columns:1fr 1fr}
  .tyt-feat{padding:24px 20px;border-bottom:1px solid rgba(201,168,76,0.18)}
  .tyt-feat:nth-child(2n){border-right:none}
}
@media(max-width:560px){.cab-sb-outer{margin-top:-30px;padding:0 16px}}
</style>
@endpush

@section('content')
<div class="tyt-cabs">
  {{-- TODO(client): swap these for car / chauffeur photos when available. --}}
  <x-hero-carousel
    :slides="[
      '/assets/images/stock/photo-1476514525535-07fb3b4ae5f1-1600.webp',
      '/assets/images/stock/photo-1500375592092-40eb2168fd21-1600.webp',
      '/assets/images/stock/photo-1507525428034-b723cf961d3e-1600.webp'
    ]"
    eyebrow="Chauffeured Cabs"
    title="Arrive in <em>Comfort</em>"
    subtitle="Airport transfers, outstation trips and local rides —<br>professional drivers, all-inclusive prices."
  />

  <div class="cab-sb-outer" id="book-cab">
    @if(session('booking_error'))
      <div class="cab-flash" role="alert">{{ session('booking_error') }}</div>
    @endif
    @if($errors->any())
      <div class="cab-flash" role="alert">{{ $errors->first() }}</div>
    @endif
    @if($bookingEnabled)
      @include('partials.cab-search-widget')
    @else
      <div class="cab-notice">{{ $disabledMessage }}</div>
    @endif
  </div>

  @if($bookingEnabled)
  <div class="cab-extras">
    {{-- Recent searches: filled from this browser's history by the script below. --}}
    <section class="cab-recent" id="cabRecent" hidden aria-labelledby="cabRecentTitle">
      <div class="cab-sec-head">
        <h2 id="cabRecentTitle">Recent searches</h2>
        <button type="button" class="cab-link-btn" id="cabRecentClear">Clear</button>
      </div>
      <div class="cab-recent-list" id="cabRecentList"></div>
    </section>

    @if(!empty($upcomingFlights))
      <section class="cab-flights" aria-labelledby="cabFlightsTitle">
        <div class="cab-sec-head">
          <h2 id="cabFlightsTitle">Your upcoming flights</h2>
          <span class="cab-sec-sub">Add an airport pickup for when you land</span>
        </div>
        <div class="cab-table-wrap">
          <table class="cab-table">
            <thead>
              <tr><th scope="col">Booking ID</th><th scope="col">Passenger</th><th scope="col">Flight</th><th scope="col">Lands</th><th scope="col"><span class="sr-only">Action</span></th></tr>
            </thead>
            <tbody>
              @foreach($upcomingFlights as $f)
                <tr>
                  <td><a href="{{ route('hotel.booking.confirmation', $f['reference']) }}">{{ $f['reference'] }}</a></td>
                  <td>{{ $f['name'] }}</td>
                  <td>{{ $f['route'] }}<div class="cab-td-sub">{{ $f['airport'] }}</div></td>
                  <td>{{ $f['arrives']->format('D, j M Y') }}<div class="cab-td-sub">{{ $f['arrives']->format('g:i A') }}</div></td>
                  <td class="cab-td-act"><a class="cab-add-btn" href="{{ route('cabs', $f['cabQuery']) }}#book-cab"><i class="fa-solid fa-car-side" aria-hidden="true"></i> Add Cab</a></td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </section>
    @endif
  </div>
  @endif

  <div class="tyt-dark">
   <div class="cab-inner">
    <p class="tyt-label">Cab Services</p>
    <h2 class="tyt-title-light">Where Can We Take You?</h2>
    <div class="cab-kinds">
      <a href="#book-cab" class="cab-kind" data-journey="airport_transfer">
        <span class="cab-kind-icon"><i class="fa-solid fa-plane-arrival" aria-hidden="true"></i></span>
        <h3>Airport Transfers</h3>
        <p>Pickup from or drop to the airport. Your driver tracks your flight and waits for you at arrivals with a name card.</p>
        <span class="cab-kind-cta">Book airport cab <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></span>
      </a>
      <a href="#book-cab" class="cab-kind" data-journey="outstation">
        <span class="cab-kind-icon"><i class="fa-solid fa-route" aria-hidden="true"></i></span>
        <h3>Outstation</h3>
        <p>City to city, one way or return. Ideal for weekend getaways and business trips, with no hidden charges.</p>
        <span class="cab-kind-cta">Book outstation cab <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></span>
      </a>
      <a href="#book-cab" class="cab-kind" data-journey="local">
        <span class="cab-kind-icon"><i class="fa-solid fa-city" aria-hidden="true"></i></span>
        <h3>Local</h3>
        <p>Rides within the city — meetings, shopping, dinners or sightseeing, in a clean, comfortable car.</p>
        <span class="cab-kind-cta">Book local cab <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></span>
      </a>
    </div>
   </div>
  </div>

  <div class="tyt-feat-wrap"><div class="tyt-feat-row">
    <div class="tyt-feat"><span class="tyt-feat-icon"><i class="fa-solid fa-indian-rupee-sign" aria-hidden="true"></i></span><div><p class="tyt-feat-title">All-inclusive prices</p><p class="tyt-feat-desc">Tolls, taxes and driver charges included in the price you see.</p></div></div>
    <div class="tyt-feat"><span class="tyt-feat-icon"><i class="fa-solid fa-rotate-left" aria-hidden="true"></i></span><div><p class="tyt-feat-title">Free cancellation</p><p class="tyt-feat-desc">Most cabs can be cancelled free before pickup.</p></div></div>
    <div class="tyt-feat"><span class="tyt-feat-icon"><i class="fa-solid fa-location-dot" aria-hidden="true"></i></span><div><p class="tyt-feat-title">Live driver tracking</p><p class="tyt-feat-desc">Follow your driver on the map once your cab is on the way.</p></div></div>
    <div class="tyt-feat"><span class="tyt-feat-icon"><i class="fa-solid fa-headset" aria-hidden="true"></i></span><div><p class="tyt-feat-title">24/7 support</p><p class="tyt-feat-desc">Call or WhatsApp us any time: +91 98750 73788.</p></div></div>
  </div></div>
</div>
@endsection

@push('scripts')
<script>
  // Recent searches (saved by the search bar in this browser).
  (function () {
    var box = document.getElementById('cabRecent');
    if (!box) return;
    var list;
    try { list = JSON.parse(localStorage.getItem('tytCabRecent') || '[]'); } catch (e) { return; }
    if (!list.length) return;
    var short = function (p) { return (p || '').split(',')[0].trim(); };
    var types = { airport_transfer: 'Airport', outstation: 'Outstation', local: 'Local' };
    var minLead = Date.now() + {{ \App\Services\CabBookingService::MIN_LEAD_MINUTES }} * 60000;
    var wrap = document.getElementById('cabRecentList');
    list.forEach(function (r) {
      var when = r.pickup ? new Date(r.pickup) : null;
      var past = !when || when.getTime() < minLead;
      var card = document.createElement('button');
      card.type = 'button';
      card.className = 'cab-recent-card';
      var top = document.createElement('div'); top.className = 'cab-recent-top';
      var route = document.createElement('span'); route.className = 'cab-recent-route'; route.textContent = short(r.from) + ' → ' + short(r.to);
      var tag = document.createElement('span'); tag.className = 'cab-recent-tag'; tag.textContent = (types[r.journey] || 'Cab') + ' · ' + (r.trip === 'roundtrip' ? 'Round trip' : 'One way');
      top.appendChild(route); top.appendChild(tag);
      var meta = document.createElement('div'); meta.className = 'cab-recent-meta';
      meta.textContent = (when ? when.toLocaleDateString('en-GB', { weekday: 'short', day: 'numeric', month: 'short' }) + ', ' + when.toLocaleTimeString('en-GB', { hour: 'numeric', minute: '2-digit', hour12: true }) : '') + ' · ' + (r.passengers || 1) + ' passengers · ' + (r.bags || 0) + ' bags';
      var note = document.createElement('div'); note.className = 'cab-recent-meta past';
      if (past) note.textContent = 'Date has passed — pick a new time';
      card.appendChild(top); card.appendChild(meta); if (past) card.appendChild(note);
      card.addEventListener('click', function () {
        var q = Object.assign({}, r);
        if (past) { delete q.pickup; delete q.return; location.href = @json(route('cabs')) + '?' + new URLSearchParams(q) + '#book-cab'; return; }
        location.href = @json(route('cabs.search')) + '?' + new URLSearchParams(q);
      });
      wrap.appendChild(card);
    });
    box.hidden = false;
    document.getElementById('cabRecentClear').addEventListener('click', function () {
      try { localStorage.removeItem('tytCabRecent'); } catch (e) {}
      box.hidden = true;
    });
  })();

  // A service card picks that cab type in the search bar and takes the guest there.
  document.querySelectorAll('.cab-kind[data-journey]').forEach(function (card) {
    card.addEventListener('click', function () {
      var radio = document.querySelector('.cab-sb input[name="journey"][value="' + card.dataset.journey + '"]');
      if (radio) radio.checked = true;
      setTimeout(function () { var f = document.getElementById('cabFrom'); if (f) f.focus({ preventScroll: true }); }, 400);
    });
  });
</script>
@endpush
