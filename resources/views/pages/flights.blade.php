@extends('layouts.frontend')

@section('meta_title', 'Flight Bookings — Best Deals on Domestic & International Flights | TYT Luxe')
@section('meta_description', 'Book affordable domestic and international flights with TYT Luxe. Our travel experts find the best fares for Indian travellers. Call or WhatsApp +91 98750 73788 for instant assistance.')

@push('styles')
<style>
*{box-sizing:border-box;}
.tyt-flights{font-family:'Poppins',sans-serif;width:100%;background:#0a0a0a}


/* SECTIONS */
.tyt-dark{background:#111111;padding:64px 48px}
.tyt-darker{background:#0a0a0a;padding:64px 48px}
.tyt-label{font-family:'Poppins',sans-serif;font-size:10px;font-weight:600;letter-spacing:3px;text-transform:uppercase;color:#C9A84C;margin-bottom:10px}
.tyt-head-row{display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:36px;gap:16px;flex-wrap:wrap}
.tyt-title-light{font-family:'Playfair Display',serif;font-size:36px;font-weight:800;color:#ffffff;line-height:1.15}
.tyt-outline-btn{
  display:inline-block;padding:13px 26px;
  font-family:'Poppins',sans-serif;font-size:10px;font-weight:600;
  letter-spacing:2.5px;text-transform:uppercase;
  border:1.5px solid rgba(201,168,76,0.5);
  color:#C9A84C;background:transparent;
  cursor:pointer;white-space:nowrap;text-decoration:none;
  transition:background .2s,color .2s;align-self:center;
}
.tyt-outline-btn:hover{background:#C9A84C;color:#0a0a0a;border-color:#C9A84C}
.tyt-gold-btn{display:inline-block;padding:14px 32px;font-family:'Poppins',sans-serif;font-size:11px;font-weight:600;letter-spacing:2.5px;text-transform:uppercase;background:#C9A84C;color:#0a0a0a;border:none;cursor:pointer;transition:background .2s}
.tyt-gold-btn:hover{background:#b8935a}

/* FLIGHT CARDS */
.tyt-cards-6{display:grid;grid-template-columns:repeat(3,1fr);gap:14px}
.tyt-img-card{position:relative;border-radius:6px;overflow:hidden;height:300px;cursor:pointer}
.tyt-img-card img{width:100%;height:100%;object-fit:cover;display:block;transition:transform .4s ease}
.tyt-img-card:hover img{transform:scale(1.05)}
.tyt-card-overlay{position:absolute;inset:0;background:linear-gradient(to top,rgba(0,0,0,0.80) 0%,rgba(0,0,0,0) 55%)}
.tyt-card-label{position:absolute;bottom:20px;left:20px;font-family:'Playfair Display',serif;font-size:17px;font-weight:700;color:#fff}
.tyt-card-sub{font-family:'Poppins',sans-serif;font-size:11px;font-weight:400;color:rgba(255,255,255,0.75);margin-top:4px}

/* AIRLINE STRIP (logos scroll sideways in a loop; the list is printed twice so the loop is seamless) */
.tyt-airlines{text-align:center;padding-bottom:56px}
.tyt-airlines .tyt-title-light em{font-style:italic;color:#C9A84C}
.tyt-air-marquee{margin-top:36px;overflow:hidden;-webkit-mask-image:linear-gradient(90deg,transparent,#000 8%,#000 92%,transparent);mask-image:linear-gradient(90deg,transparent,#000 8%,#000 92%,transparent)}
.tyt-air-track{display:flex;gap:18px;width:max-content;animation:tytAirScroll 40s linear infinite}
.tyt-air-marquee:hover .tyt-air-track{animation-play-state:paused}
.tyt-air-tile{flex-shrink:0;width:200px;height:84px;border-radius:12px;background:#f6f1e7;display:flex;align-items:center;justify-content:center;padding:0 26px}
.tyt-air-tile img{max-width:100%;max-height:40px;object-fit:contain;display:block}
@keyframes tytAirScroll{from{transform:translateX(0)}to{transform:translateX(calc(-50% - 9px))}}
@media(prefers-reduced-motion:reduce){
  .tyt-air-track{animation:none;flex-wrap:wrap;justify-content:center;width:auto}
  .tyt-air-track [aria-hidden="true"]{display:none}
}

/* FORM */
.tyt-form-2{display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px}
.tyt-form-3{display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;margin-bottom:16px}
.tyt-flabel{display:block;font-size:10px;font-weight:600;letter-spacing:2px;text-transform:uppercase;color:#666;margin-bottom:7px;font-family:'Poppins',sans-serif}
.tyt-finput{width:100%;background:#0d0d0d;border:1px solid rgba(255,255,255,0.08);padding:12px 14px;color:#fff;font-family:'Poppins',sans-serif;font-size:13px;font-weight:300;outline:none;transition:border-color .2s;-webkit-appearance:none;border-radius:0}
.tyt-finput:focus{border-color:#C9A84C}
.tyt-finput option{background:#1a1a1a}
.tyt-finput::placeholder{color:#444}
input[type="date"].tyt-finput::-webkit-calendar-picker-indicator{filter:invert(0.4)}
.tyt-trip-toggle{display:flex;margin-bottom:24px}
.tyt-trip-btn{padding:10px 22px;font-size:10px;font-weight:600;letter-spacing:2px;text-transform:uppercase;font-family:'Poppins',sans-serif;background:transparent;color:#666;border:1px solid rgba(255,255,255,0.1);cursor:pointer;transition:all .2s;margin-right:-1px}
.tyt-trip-btn.active{background:#C9A84C;color:#0a0a0a;border-color:#C9A84C;z-index:1}
.tyt-class-row{display:grid;grid-template-columns:repeat(4,1fr);gap:8px;margin-bottom:24px}
.tyt-class-btn{padding:10px 6px;font-size:10px;font-weight:600;letter-spacing:1.5px;text-transform:uppercase;font-family:'Poppins',sans-serif;background:#0d0d0d;color:#555;border:1px solid rgba(255,255,255,0.08);cursor:pointer;text-align:center;transition:all .2s}
.tyt-class-btn.active{color:#C9A84C;border-color:#C9A84C}

/* FEATURES BAR */
.tyt-feat-row{display:grid;grid-template-columns:repeat(4,1fr);gap:0;background:#0a0a0a;border-top:1px solid rgba(201,168,76,0.18)}
.tyt-feat{display:flex;align-items:flex-start;gap:16px;padding:32px 32px;border-right:1px solid rgba(201,168,76,0.18)}
.tyt-feat:last-child{border-right:none}
.tyt-feat-icon{flex-shrink:0;width:44px;height:44px;border-radius:50%;border:1px solid rgba(201,168,76,0.5);display:flex;align-items:center;justify-content:center;color:#C9A84C}
.tyt-feat-title{font-family:'Poppins',sans-serif;font-size:14px;font-weight:500;color:#fff;margin:0 0 5px}
.tyt-feat-desc{font-size:13px;color:rgba(255,255,255,0.65);font-weight:300;line-height:1.55;margin:0}

.tyt-success{display:none;background:#0d1f0d;border:1px solid #2a5a2a;padding:20px;margin-top:20px;text-align:center;color:#7ec87e;font-family:'Poppins',sans-serif;font-size:13px;font-weight:300}
.tyt-ret{display:none}
.tyt-ret.show{display:block}

/* DIVIDER */
.tyt-section-divider{width:50px;height:2px;background:#C9A84C;margin-bottom:32px;opacity:0.5}

/* ── LIVE SEARCH BAR positioning (widget itself lives in
     partials.flight-search-widget, shared with the results page) ──────── */
.tyt-sb-outer{position:relative;z-index:5;max-width:1180px;margin:-46px auto 0;padding:0 24px}
@media(max-width:560px){
  .tyt-sb-outer{margin-top:-30px}
}

@media(max-width:768px){
  .tyt-dark,.tyt-darker{padding:40px 20px}
  .tyt-flights-hero{padding:120px 24px 60px;min-height:400px}
  .tyt-cards-6{grid-template-columns:1fr 1fr}
  .tyt-air-tile{width:150px;height:68px;padding:0 18px}
  .tyt-air-tile img{max-height:32px}
  .tyt-airlines{padding-bottom:40px}
  .tyt-form-2,.tyt-form-3{grid-template-columns:1fr}
  .tyt-class-row{grid-template-columns:repeat(2,1fr)}
  .tyt-feat-row{grid-template-columns:1fr 1fr}
  .tyt-feat{padding:24px 20px;border-bottom:1px solid rgba(201,168,76,0.18)}
  .tyt-feat:nth-child(2n){border-right:none}
  .tyt-title-light{font-size:26px}
}
@media(max-width:520px){
  .tyt-feat-row{grid-template-columns:1fr}
  .tyt-feat{border-right:none}
}
</style>
@endpush

@section('content')

<div class="tyt-flights">

  <!-- HERO SLIDER -->
  <x-hero-carousel 
    :slides="[
      '/assets/images/stock/photo-1436491865332-7a61a109cc05-1600.webp',
      '/assets/images/stock/photo-1540339832862-474599807836-1600.webp',
      '/assets/images/stock/photo-1503221043305-f7498f8b7888-1600.webp',
      '/assets/images/stock/photo-1464037866556-6812c9d1c72e-1600.webp'
    ]"
    eyebrow="Flight Experiences"
    title="Fly the <em>Right Way</em>"
    subtitle="Domestic &amp; international flights, business class and beyond —<br>curated by real travel experts, not bots."
  />

  <!-- LIVE FLIGHT SEARCH (shared partial — also used as "Modify Search" on the results page) -->
  <div class="tyt-sb-outer">
    @if(\App\Support\FlightSettings::bookingEnabled())
      @include('partials.flight-search-widget')
    @else
      <div style="max-width:760px; margin:0 auto; padding:22px 26px; border-radius:14px; background:rgba(201,168,76,0.1); border:1px solid rgba(201,168,76,0.4); color:#f3e6c2; font-family:'Jost',sans-serif; font-size:15px; text-align:center;">
        {{ \App\Support\FlightSettings::disabledMessage() }}
      </div>
    @endif
  </div>

  <!-- FLIGHT CATEGORIES -->
  <div class="tyt-dark">
    <p class="tyt-label">Flight Experiences</p>
    <div class="tyt-head-row">
      <h2 class="tyt-title-light">Choose Your Journey</h2>
    </div>
    <div class="tyt-cards-6">
      <div class="tyt-img-card" onclick="location.href='#tyt-book-flight'">
        <img src="/assets/images/stock/photo-1436491865332-7a61a109cc05-700.webp" alt="Domestic Flights" loading="lazy"/>
        <div class="tyt-card-overlay"></div>
        <div class="tyt-card-label">Domestic Flights<div class="tyt-card-sub">Pan-India routes, best fares</div></div>
      </div>

      <div class="tyt-img-card" onclick="location.href='#tyt-book-flight'">
        <img src="/assets/images/stock/photo-1503221043305-f7498f8b7888-700.webp" alt="International Flights" loading="lazy"/>
        <div class="tyt-card-overlay"></div>
        <div class="tyt-card-label">International<div class="tyt-card-sub">Global destinations covered</div></div>
      </div>

      <div class="tyt-img-card" onclick="location.href='#tyt-book-flight'">
        <img src="/assets/images/stock/photo-1540339832862-474599807836-700.webp" alt="Business Class" loading="lazy"/>
        <div class="tyt-card-overlay"></div>
        <div class="tyt-card-label">Business Class<div class="tyt-card-sub">Lie-flat beds, premium lounges</div></div>
      </div>

      <div class="tyt-img-card" onclick="location.href='#tyt-book-flight'">
        <img src="/assets/images/stock/photo-1464037866556-6812c9d1c72e-700.webp" alt="First Class" loading="lazy"/>
        <div class="tyt-card-overlay"></div>
        <div class="tyt-card-label">First Class<div class="tyt-card-sub">Suite experience, concierge</div></div>
      </div>

      <div class="tyt-img-card" onclick="location.href='#tyt-book-flight'">
        <img src="/assets/images/stock/photo-1578474846511-04ba529f0b88-700.webp" alt="Charter Flights" loading="lazy"/>
        <div class="tyt-card-overlay"></div>
        <div class="tyt-card-label">Charter Flights<div class="tyt-card-sub">Private &amp; group aircraft</div></div>
      </div>

      <div class="tyt-img-card" onclick="location.href='#tyt-book-flight'">
        <img src="/assets/images/stock/photo-1569154941061-e231b4725ef1-700.webp" alt="Multi-City" loading="lazy"/>
        <div class="tyt-card-overlay"></div>
        <div class="tyt-card-label">Multi-City<div class="tyt-card-sub">Complex itineraries, seamless</div></div>
      </div>
    </div>
  </div>

  <!-- AIRLINE PARTNERS -->
  @php
    $airlines = [
      ['IndiGo', 'indigo.svg'],
      ['Air India', 'air_india.png'],
      ['SpiceJet', 'spicejet.png'],
      ['Emirates', 'emirates.svg'],
      ['Qatar Airways', 'qatar.svg'],
      ['Lufthansa', 'lufthansa.svg'],
      ['British Airways', 'british_airways.svg'],
      ['Etihad Airways', 'etihad.png'],
      ['Singapore Airlines', 'singapore.png'],
      ['Cathay Pacific', 'cathay.svg'],
    ];
  @endphp
  <div class="tyt-darker tyt-airlines">
    <p class="tyt-label">Our Airline Partners</p>
    <h2 class="tyt-title-light">All Major Airlines. <em>One Search.</em></h2>
    <div class="tyt-air-marquee">
      <div class="tyt-air-track">
        @foreach ([false, true] as $copy)
          @foreach ($airlines as [$name, $file])
            <div class="tyt-air-tile" @if($copy) aria-hidden="true" @endif>
              <img src="{{ asset('assets/images/airlines/'.$file) }}" alt="{{ $copy ? '' : $name }}" loading="lazy"/>
            </div>
          @endforeach
        @endforeach
      </div>
    </div>
  </div>

  <!-- FEATURES BAR -->
  <div class="tyt-feat-row">
    <div class="tyt-feat">
      <span class="tyt-feat-icon"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 12l-8 8-9-9V3h8z"/><circle cx="7.5" cy="7.5" r="1.5"/></svg></span>
      <div><p class="tyt-feat-title">Zero Hidden Fees</p><p class="tyt-feat-desc">The price you see includes taxes and fees.</p></div>
    </div>
    <div class="tyt-feat">
      <span class="tyt-feat-icon"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 13a8 8 0 0116 0"/><path d="M4 13v4a2 2 0 002 2h1v-6H4zM20 13v4a2 2 0 01-2 2h-1v-6h3z"/></svg></span>
      <div><p class="tyt-feat-title">Real People, Real Help</p><p class="tyt-feat-desc">Call or WhatsApp us, Mon–Sat, <span style="white-space:nowrap">10 AM – 7 PM</span>.</p></div>
    </div>
    <div class="tyt-feat">
      <span class="tyt-feat-icon"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 4v6h6"/><path d="M20 20v-6h-6"/><path d="M5 15a8 8 0 0013.5 3M19 9A8 8 0 005.5 6"/></svg></span>
      <div><p class="tyt-feat-title">Flexible Changes</p><p class="tyt-feat-desc">Help with rescheduling and cancellations.</p></div>
    </div>
    <div class="tyt-feat">
      <span class="tyt-feat-icon"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3l2.6 5.6 6.1.7-4.5 4.2 1.2 6L12 16.6 6.6 19.5l1.2-6-4.5-4.2 6.1-.7z"/></svg></span>
      <div><p class="tyt-feat-title">Best Fares, Every Airline</p><p class="tyt-feat-desc">We compare fares across all airlines for you.</p></div>
    </div>
  </div>

</div>

@endsection

