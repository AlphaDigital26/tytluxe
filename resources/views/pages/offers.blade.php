@extends('layouts.frontend')

@push('styles')
<style>
  *, *::before, *::after { box-sizing: border-box; }

  :root {
    --black:     #0a0a0a;
    --dark:      #111111;
    --dark-card: #161616;
    --dark-mid:  #1c1c1c;
    --border:    rgba(255,255,255,0.09);
    --gold:      #C9A84C;
    --gold-light:#E2C97E;
    --gold-dim:  rgba(201,168,76,0.15);
    --white:     #ffffff;
    --w80:       rgba(255,255,255,0.80);
    --w50:       rgba(255,255,255,0.50);
    --w30:       rgba(255,255,255,0.30);
  }

  body { font-family: 'Poppins', sans-serif; background: var(--black); color: var(--white); }



  /* ── FILTER TABS ── */
  .filter-bar { display: flex; align-items: center; justify-content: center; gap: .6rem; padding: 2.5rem 2rem 0; flex-wrap: wrap; }
  .filter-btn {
    padding: .45rem 1.2rem; border-radius: 100px; border: 1px solid var(--border);
    background: transparent; color: var(--w50); font-family: 'Poppins', sans-serif;
    font-size: 11px; font-weight: 500; letter-spacing: .08em; text-transform: uppercase;
    cursor: pointer; transition: all .2s;
  }
  .filter-btn:hover { border-color: var(--gold); color: var(--gold); }
  .filter-btn.active { background: var(--gold); border-color: var(--gold); color: var(--black); }

  /* ── SLIDER SECTION ── */
  .slider-section { padding: 3.5rem 0 0; }

  .slider-header { display: flex; align-items: flex-end; justify-content: space-between; padding: 0 3rem; margin-bottom: 1.75rem; }
  .slider-label { font-size: 10px; font-weight: 600; letter-spacing: .22em; text-transform: uppercase; color: var(--gold); margin-bottom: .3rem; }
  .slider-title { font-family: 'Playfair Display', serif; font-size: 1.75rem; font-weight: 400; color: var(--white); line-height: 1.2; }
  .slider-title em { font-style: italic; color: var(--gold-light); }
  .slider-arrows { display: flex; gap: .5rem; }
  .arrow-btn {
    width: 38px; height: 38px; border-radius: 50%; border: 1px solid var(--border);
    background: var(--dark-card); color: var(--w50); font-size: 16px; cursor: pointer;
    display: flex; align-items: center; justify-content: center; transition: all .2s;
  }
  .arrow-btn:hover { border-color: var(--gold); color: var(--gold); background: var(--gold-dim); }

  /* TRACK */
  .slider-track {
    display: flex; gap: 1.25rem; padding: 0 3rem 2rem;
    overflow-x: auto; scroll-behavior: smooth;
    scrollbar-width: none; -ms-overflow-style: none;
    scroll-snap-type: x mandatory;
  }
  .slider-track::-webkit-scrollbar { display: none; }

  /* ── OFFER CARD ── */
  .offer-card {
    flex: 0 0 290px; scroll-snap-align: start;
    border-radius: 12px; overflow: hidden;
    position: relative; cursor: pointer;
    transition: transform .25s, box-shadow .25s;
    border: 1px solid var(--border);
  }
  .offer-card:hover { transform: translateY(-5px); box-shadow: 0 20px 50px rgba(0,0,0,.5); border-color: rgba(201,168,76,.3); }

  .card-img {
    width: 100%; height: 200px; object-fit: cover; display: block;
    transition: transform .4s;
  }
  .offer-card:hover .card-img { transform: scale(1.04); }

  /* gradient overlay on image */
  .card-img-wrap { position: relative; overflow: hidden; }
  .card-img-wrap::after {
    content: ''; position: absolute; inset: 0;
    background: linear-gradient(to bottom, rgba(0,0,0,0) 40%, rgba(0,0,0,.7) 100%);
  }
  .card-badge {
    position: absolute; top: 12px; left: 12px; z-index: 2;
    font-size: 9px; font-weight: 700; letter-spacing: .15em; text-transform: uppercase;
    padding: 4px 10px; border-radius: 100px;
  }
  .badge-gold { background: var(--gold); color: var(--black); }
  .badge-hot  { background: #e74c3c; color: #fff; }
  .badge-new  { background: #27ae60; color: #fff; }

  .card-body { background: var(--dark-card); padding: 1.1rem 1.25rem 1.25rem; }
  .card-name { font-family: 'Playfair Display', serif; font-size: 1.05rem; font-weight: 400; color: var(--white); margin-bottom: .3rem; }
  .card-sub  { font-size: 11px; color: var(--w50); line-height: 1.6; margin-bottom: .9rem; }
  .card-footer { display: flex; align-items: center; justify-content: space-between; }
  .card-price { font-size: 13px; color: var(--gold); font-weight: 500; }
  .card-cta {
    font-size: 10px; font-weight: 600; letter-spacing: .08em; text-transform: uppercase;
    color: var(--black); background: var(--gold); border: none; border-radius: 5px;
    padding: .4rem .9rem; cursor: pointer; text-decoration: none; transition: background .2s;
  }
  .card-cta:hover { background: var(--gold-light); }

  /* DOTS */
  .slider-dots { display: flex; justify-content: center; gap: 6px; padding: .5rem 0 2rem; }
  .dot { width: 6px; height: 6px; border-radius: 50%; background: var(--border); border: 1px solid rgba(255,255,255,.15); cursor: pointer; transition: all .25s; }
  .dot.active { background: var(--gold); border-color: var(--gold); width: 20px; border-radius: 3px; }

  /* SECTION DIVIDER */
  .s-divider { display: flex; align-items: center; gap: 1rem; max-width: 1100px; margin: 0 auto; padding: 0 3rem; }
  .s-divider::before,.s-divider::after { content: ''; flex: 1; height: 1px; background: var(--border); }
  .s-divider span { font-size: 9px; color: var(--w30); letter-spacing: .18em; text-transform: uppercase; white-space: nowrap; }

  /* ── COMING SOON RIBBON ── */
  .soon-ribbon {
    position: absolute; bottom: 0; left: 0; right: 0; z-index: 3;
    background: rgba(10,10,10,.82); backdrop-filter: blur(4px);
    text-align: center; padding: .5rem;
    font-size: 10px; font-weight: 600; letter-spacing: .15em; text-transform: uppercase;
    color: var(--gold);
  }

  /* ── NOTIFY + WA ── */
  .bottom-section { max-width: 700px; margin: 3rem auto 5rem; padding: 0 2rem; text-align: center; }
  .bottom-card {
    background: var(--dark-card); border: 1px solid var(--border);
    border-radius: 12px; padding: 2.5rem 2rem;
    position: relative; overflow: hidden;
  }
  .bottom-card::before {
    content: ''; position: absolute; top: -60px; left: 50%; transform: translateX(-50%);
    width: 300px; height: 300px; border-radius: 50%;
    background: radial-gradient(circle, rgba(201,168,76,.08) 0%, transparent 70%);
  }
  .bc-tag { font-size: 10px; font-weight: 600; letter-spacing: .2em; text-transform: uppercase; color: var(--gold); display: block; margin-bottom: .75rem; }
  .bottom-card h3 { font-family: 'Playfair Display', serif; font-size: 1.6rem; font-weight: 400; color: var(--white); margin-bottom: .5rem; }
  .bottom-card h3 em { font-style: italic; color: var(--gold-light); }
  .bottom-card p { font-size: 13px; color: var(--w50); line-height: 1.8; margin-bottom: 1.5rem; }
  .notify-form { display: flex; gap: .75rem; max-width: 400px; margin: 0 auto .75rem; }
  .notify-form input {
    flex: 1; background: var(--dark-mid); border: 1px solid var(--border); border-radius: 6px;
    padding: .7rem 1rem; font-family: 'Poppins', sans-serif; font-size: 13px; color: var(--white); outline: none; transition: border-color .2s;
  }
  .notify-form input::placeholder { color: var(--w30); }
  .notify-form input:focus { border-color: var(--gold); }
  .notify-form button {
    background: var(--gold); border: none; border-radius: 6px; padding: .7rem 1.1rem;
    font-family: 'Poppins', sans-serif; font-size: 10px; font-weight: 600; letter-spacing: .1em;
    text-transform: uppercase; color: var(--black); cursor: pointer; white-space: nowrap; transition: background .2s;
  }
  .notify-form button:hover { background: var(--gold-light); }
  .notify-note { font-size: 11px; color: var(--w30); margin-bottom: 1.5rem; }
  .notify-success { display: none; font-size: 13px; color: #25D366; margin: .5rem 0 1.25rem; }
  .or-row { display: flex; align-items: center; gap: .75rem; margin: 1.25rem 0; }
  .or-row::before,.or-row::after { content: ''; flex: 1; height: 1px; background: var(--border); }
  .or-row span { font-size: 10px; color: var(--w30); letter-spacing: .1em; }
  .wa-btn {
    display: inline-flex; align-items: center; gap: 10px; background: #25D366; color: #fff;
    text-decoration: none; border-radius: 6px; padding: .85rem 1.75rem;
    font-size: 12px; font-weight: 600; letter-spacing: .08em; text-transform: uppercase;
    transition: opacity .2s, transform .15s;
  }
  .wa-btn:hover { opacity: .9; transform: translateY(-1px); }
  .wa-btn svg { width: 18px; height: 18px; fill: #fff; flex-shrink: 0; }

  @media(max-width:768px){
    .hero { padding: 3.5rem 1.5rem 3rem; }
    .slider-header { padding: 0 1.25rem; }
    .slider-track { padding: 0 1.25rem 1.5rem; }
    .notify-form { flex-direction: column; }
  }
</style>
@endpush

@section('content')

<!-- HERO -->
<!-- HERO -->
<x-hero-carousel 
  :slides="[
    'https://images.unsplash.com/photo-1542314831-068cd1dbfeeb?w=1400&q=85',
    'https://images.unsplash.com/photo-1548574505-5e239809ee19?w=1400&q=85',
    'https://images.unsplash.com/photo-1436491865332-7a61a109cc05?w=1400&q=85',
    'https://images.unsplash.com/photo-1512343879784-a960bf40e7f2?w=1400&q=85',
    'https://images.unsplash.com/photo-1590490360182-c33d57733427?w=1400&q=85'
  ]"
  eyebrow="Limited Time Deals"
  title="Exclusive Deals. <em>Unforgettable</em> Experiences."
  subtitle="Handpicked offers on hotels, cruises &amp; flights — updated regularly"
  ctaText=""
  ctaLink=""
/>

<!-- FILTER -->
<div class="filter-bar">
  <button class="filter-btn active" onclick="filterOffers('all',this)">All Offers</button>
  <button class="filter-btn" onclick="filterOffers('hotels',this)">Hotels</button>
  <button class="filter-btn" onclick="filterOffers('cruises',this)">Cruises</button>
  <button class="filter-btn" onclick="filterOffers('flights',this)">Flights</button>
  <button class="filter-btn" onclick="filterOffers('honeymoon',this)">Honeymoon</button>
  <button class="filter-btn" onclick="filterOffers('family',this)">Family</button>
</div>

<!-- ══ SLIDER 1: HOTELS ══ -->
<div class="slider-section" data-category="hotels">
  <div class="slider-header">
    <div>
      <div class="slider-label">Hotel Offers</div>
      <div class="slider-title">Handpicked <em>Stays</em></div>
    </div>
    <div class="slider-arrows">
      <button class="arrow-btn" onclick="slide('hotels-track',-1)">&#8592;</button>
      <button class="arrow-btn" onclick="slide('hotels-track', 1)">&#8594;</button>
    </div>
  </div>
  <div class="slider-track" id="hotels-track">

    <div class="offer-card">
      <div class="card-img-wrap">
        <img class="card-img" src="https://images.unsplash.com/photo-1542314831-068cd1dbfeeb?w=700&q=80" alt="Beach Resort">
        <span class="card-badge badge-hot">Hot Deal</span>
        <div class="soon-ribbon">Deal Coming Soon</div>
      </div>
      <div class="card-body">
        <div class="card-name">Beach Resort Getaway</div>
        <div class="card-sub">Sun, sand &amp; luxury — handpicked beachfront stays</div>
        <div class="card-footer">
          <span class="card-price">Contact for Price</span>
          <a href="https://wa.me/9875073788" class="card-cta">Enquire</a>
        </div>
      </div>
    </div>

    <div class="offer-card">
      <div class="card-img-wrap">
        <img class="card-img" src="https://images.unsplash.com/photo-1571003123894-1f0594d2b5d9?w=700&q=80" alt="City Luxury">
        <span class="card-badge badge-gold">City Luxury</span>
        <div class="soon-ribbon">Deal Coming Soon</div>
      </div>
      <div class="card-body">
        <div class="card-name">City Luxury Hotels</div>
        <div class="card-sub">5-star stays in the heart of India's finest cities</div>
        <div class="card-footer">
          <span class="card-price">Contact for Price</span>
          <a href="https://wa.me/9875073788" class="card-cta">Enquire</a>
        </div>
      </div>
    </div>

    <div class="offer-card">
      <div class="card-img-wrap">
        <img class="card-img" src="https://images.unsplash.com/photo-1520250497591-112f2f40a3f4?w=700&q=80" alt="Honeymoon Stay">
        <span class="card-badge badge-new">Romantic</span>
        <div class="soon-ribbon">Deal Coming Soon</div>
      </div>
      <div class="card-body">
        <div class="card-name">Honeymoon Stays</div>
        <div class="card-sub">Private villas &amp; romantic escapes for couples</div>
        <div class="card-footer">
          <span class="card-price">Contact for Price</span>
          <a href="https://wa.me/9875073788" class="card-cta">Enquire</a>
        </div>
      </div>
    </div>

    <div class="offer-card">
      <div class="card-img-wrap">
        <img class="card-img" src="https://images.unsplash.com/photo-1596436889106-be35e843f974?w=700&q=80" alt="Family Resort">
        <span class="card-badge badge-gold">Family</span>
        <div class="soon-ribbon">Deal Coming Soon</div>
      </div>
      <div class="card-body">
        <div class="card-name">Family Friendly Resorts</div>
        <div class="card-sub">Fun-filled stays the whole family will love</div>
        <div class="card-footer">
          <span class="card-price">Contact for Price</span>
          <a href="https://wa.me/9875073788" class="card-cta">Enquire</a>
        </div>
      </div>
    </div>

  </div>
  <div class="slider-dots" id="hotels-dots">
    <div class="dot active" onclick="scrollToDot('hotels-track',0)"></div>
    <div class="dot" onclick="scrollToDot('hotels-track',1)"></div>
    <div class="dot" onclick="scrollToDot('hotels-track',2)"></div>
    <div class="dot" onclick="scrollToDot('hotels-track',3)"></div>
  </div>
</div>

<div class="s-divider"><span>✦</span></div>

<!-- ══ SLIDER 2: CRUISES ══ -->
<div class="slider-section" data-category="cruises">
  <div class="slider-header">
    <div>
      <div class="slider-label">Cruise Offers</div>
      <div class="slider-title">Sail <em>Beyond Ordinary</em></div>
    </div>
    <div class="slider-arrows">
      <button class="arrow-btn" onclick="slide('cruises-track',-1)">&#8592;</button>
      <button class="arrow-btn" onclick="slide('cruises-track', 1)">&#8594;</button>
    </div>
  </div>
  <div class="slider-track" id="cruises-track">

    <div class="offer-card">
      <div class="card-img-wrap">
        <img class="card-img" src="https://images.unsplash.com/photo-1548574505-5e239809ee19?w=700&q=80" alt="Scenic Cruise">
        <span class="card-badge badge-gold">Scenic</span>
        <div class="soon-ribbon">Deal Coming Soon</div>
      </div>
      <div class="card-body">
        <div class="card-name">Scenic Getaways</div>
        <div class="card-sub">Breathtaking coastal routes — Mumbai to Sri Lanka</div>
        <div class="card-footer">
          <span class="card-price">Contact for Price</span>
          <a href="https://wa.me/9875073788" class="card-cta">Enquire</a>
        </div>
      </div>
    </div>

    <div class="offer-card">
      <div class="card-img-wrap">
        <img class="card-img" src="https://images.unsplash.com/photo-1590490360182-c33d57733427?w=700&q=80" alt="Luxury Cruise">
        <span class="card-badge badge-hot">Luxury</span>
        <div class="soon-ribbon">Deal Coming Soon</div>
      </div>
      <div class="card-body">
        <div class="card-name">Luxury Cruises</div>
        <div class="card-sub">Suite experiences, fine dining &amp; concierge at sea</div>
        <div class="card-footer">
          <span class="card-price">Contact for Price</span>
          <a href="https://wa.me/9875073788" class="card-cta">Enquire</a>
        </div>
      </div>
    </div>

    <div class="offer-card">
      <div class="card-img-wrap">
        <img class="card-img" src="https://images.unsplash.com/photo-1545579133-99bb5ad189be?w=700&q=80" alt="International Cruise">
        <span class="card-badge badge-new">International</span>
        <div class="soon-ribbon">Deal Coming Soon</div>
      </div>
      <div class="card-body">
        <div class="card-name">International Cruises</div>
        <div class="card-sub">Global voyages — Mediterranean, Caribbean &amp; beyond</div>
        <div class="card-footer">
          <span class="card-price">Contact for Price</span>
          <a href="https://wa.me/9875073788" class="card-cta">Enquire</a>
        </div>
      </div>
    </div>

    <div class="offer-card">
      <div class="card-img-wrap">
        <img class="card-img" src="https://images.unsplash.com/photo-1582510003544-4d00b7f74220?w=700&q=80" alt="Lakshadweep Cruise">
        <span class="card-badge badge-gold">Exclusive</span>
        <div class="soon-ribbon">Deal Coming Soon</div>
      </div>
      <div class="card-body">
        <div class="card-name">Lakshadweep Special</div>
        <div class="card-sub">India's best-kept secret — pristine islands by sea</div>
        <div class="card-footer">
          <span class="card-price">Contact for Price</span>
          <a href="https://wa.me/9875073788" class="card-cta">Enquire</a>
        </div>
      </div>
    </div>

  </div>
  <div class="slider-dots" id="cruises-dots">
    <div class="dot active" onclick="scrollToDot('cruises-track',0)"></div>
    <div class="dot" onclick="scrollToDot('cruises-track',1)"></div>
    <div class="dot" onclick="scrollToDot('cruises-track',2)"></div>
    <div class="dot" onclick="scrollToDot('cruises-track',3)"></div>
  </div>
</div>

<div class="s-divider"><span>✦</span></div>

<!-- ══ SLIDER 3: FLIGHTS ══ -->
<div class="slider-section" data-category="flights">
  <div class="slider-header">
    <div>
      <div class="slider-label">Flight Offers</div>
      <div class="slider-title">Fly the <em>Right Way</em></div>
    </div>
    <div class="slider-arrows">
      <button class="arrow-btn" onclick="slide('flights-track',-1)">&#8592;</button>
      <button class="arrow-btn" onclick="slide('flights-track', 1)">&#8594;</button>
    </div>
  </div>
  <div class="slider-track" id="flights-track">

    <div class="offer-card">
      <div class="card-img-wrap">
        <img class="card-img" src="https://images.unsplash.com/photo-1436491865332-7a61a109cc05?w=700&q=80" alt="Domestic Flight">
        <span class="card-badge badge-gold">Domestic</span>
        <div class="soon-ribbon">Deal Coming Soon</div>
      </div>
      <div class="card-body">
        <div class="card-name">Domestic Flights</div>
        <div class="card-sub">Pan-India routes at the best available fares</div>
        <div class="card-footer">
          <span class="card-price">Contact for Price</span>
          <a href="https://wa.me/9875073788" class="card-cta">Enquire</a>
        </div>
      </div>
    </div>

    <div class="offer-card">
      <div class="card-img-wrap">
        <img class="card-img" src="https://images.unsplash.com/photo-1503221043305-f7498f8b7888?w=700&q=80" alt="International Flight">
        <span class="card-badge badge-hot">International</span>
        <div class="soon-ribbon">Deal Coming Soon</div>
      </div>
      <div class="card-body">
        <div class="card-name">International Flights</div>
        <div class="card-sub">Global destinations — all major airlines covered</div>
        <div class="card-footer">
          <span class="card-price">Contact for Price</span>
          <a href="https://wa.me/9875073788" class="card-cta">Enquire</a>
        </div>
      </div>
    </div>

    <div class="offer-card">
      <div class="card-img-wrap">
        <img class="card-img" src="https://images.unsplash.com/photo-1540339832862-474599807836?w=700&q=80" alt="Business Class">
        <span class="card-badge badge-new">Business</span>
        <div class="soon-ribbon">Deal Coming Soon</div>
      </div>
      <div class="card-body">
        <div class="card-name">Business Class</div>
        <div class="card-sub">Lie-flat beds, premium lounges &amp; priority boarding</div>
        <div class="card-footer">
          <span class="card-price">Contact for Price</span>
          <a href="https://wa.me/9875073788" class="card-cta">Enquire</a>
        </div>
      </div>
    </div>

    <div class="offer-card">
      <div class="card-img-wrap">
        <img class="card-img" src="https://images.unsplash.com/photo-1464037866556-6812c9d1c72e?w=700&q=80" alt="First Class">
        <span class="card-badge badge-gold">First Class</span>
        <div class="soon-ribbon">Deal Coming Soon</div>
      </div>
      <div class="card-body">
        <div class="card-name">First Class</div>
        <div class="card-sub">Suite experience, personal concierge &amp; fine dining</div>
        <div class="card-footer">
          <span class="card-price">Contact for Price</span>
          <a href="https://wa.me/9875073788" class="card-cta">Enquire</a>
        </div>
      </div>
    </div>

  </div>
  <div class="slider-dots" id="flights-dots">
    <div class="dot active" onclick="scrollToDot('flights-track',0)"></div>
    <div class="dot" onclick="scrollToDot('flights-track',1)"></div>
    <div class="dot" onclick="scrollToDot('flights-track',2)"></div>
    <div class="dot" onclick="scrollToDot('flights-track',3)"></div>
  </div>
</div>

<div class="s-divider"><span>✦</span></div>

<!-- ══ SLIDER 4: HONEYMOON ══ -->
<div class="slider-section" data-category="honeymoon">
  <div class="slider-header">
    <div>
      <div class="slider-label">Honeymoon Packages</div>
      <div class="slider-title">Romance <em>Awaits</em></div>
    </div>
    <div class="slider-arrows">
      <button class="arrow-btn" onclick="slide('honeymoon-track',-1)">&#8592;</button>
      <button class="arrow-btn" onclick="slide('honeymoon-track', 1)">&#8594;</button>
    </div>
  </div>
  <div class="slider-track" id="honeymoon-track">

    <div class="offer-card">
      <div class="card-img-wrap">
        <img class="card-img" src="https://images.unsplash.com/photo-1512343879784-a960bf40e7f2?w=700&q=80" alt="Goa Honeymoon">
        <span class="card-badge badge-hot">Popular</span>
        <div class="soon-ribbon">Deal Coming Soon</div>
      </div>
      <div class="card-body">
        <div class="card-name">Goa Honeymoon</div>
        <div class="card-sub">Beachside romance in India's party capital</div>
        <div class="card-footer">
          <span class="card-price">Contact for Price</span>
          <a href="https://wa.me/9875073788" class="card-cta">Enquire</a>
        </div>
      </div>
    </div>

    <div class="offer-card">
      <div class="card-img-wrap">
        <img class="card-img" src="https://images.unsplash.com/photo-1546975490-a79e31768668?w=700&q=80" alt="Sri Lanka Honeymoon">
        <span class="card-badge badge-gold">Exotic</span>
        <div class="soon-ribbon">Deal Coming Soon</div>
      </div>
      <div class="card-body">
        <div class="card-name">Sri Lanka Escape</div>
        <div class="card-sub">Island paradise — the perfect honeymoon destination</div>
        <div class="card-footer">
          <span class="card-price">Contact for Price</span>
          <a href="https://wa.me/9875073788" class="card-cta">Enquire</a>
        </div>
      </div>
    </div>

    <div class="offer-card">
      <div class="card-img-wrap">
        <img class="card-img" src="https://images.unsplash.com/photo-1582510003544-4d00b7f74220?w=700&q=80" alt="Lakshadweep Honeymoon">
        <span class="card-badge badge-new">Private</span>
        <div class="soon-ribbon">Deal Coming Soon</div>
      </div>
      <div class="card-body">
        <div class="card-name">Lakshadweep Retreat</div>
        <div class="card-sub">Secluded island bliss for just the two of you</div>
        <div class="card-footer">
          <span class="card-price">Contact for Price</span>
          <a href="https://wa.me/9875073788" class="card-cta">Enquire</a>
        </div>
      </div>
    </div>

    <div class="offer-card">
      <div class="card-img-wrap">
        <img class="card-img" src="https://images.unsplash.com/photo-1631049307264-da0ec9d70304?w=700&q=80" alt="Cruise Honeymoon">
        <span class="card-badge badge-gold">Cruise Bundle</span>
        <div class="soon-ribbon">Deal Coming Soon</div>
      </div>
      <div class="card-body">
        <div class="card-name">Cruise + Hotel Bundle</div>
        <div class="card-sub">Sail by day, luxury stay by night — the perfect combo</div>
        <div class="card-footer">
          <span class="card-price">Contact for Price</span>
          <a href="https://wa.me/9875073788" class="card-cta">Enquire</a>
        </div>
      </div>
    </div>

  </div>
  <div class="slider-dots" id="honeymoon-dots">
    <div class="dot active" onclick="scrollToDot('honeymoon-track',0)"></div>
    <div class="dot" onclick="scrollToDot('honeymoon-track',1)"></div>
    <div class="dot" onclick="scrollToDot('honeymoon-track',2)"></div>
    <div class="dot" onclick="scrollToDot('honeymoon-track',3)"></div>
  </div>
</div>

<div class="s-divider"><span>✦</span></div>

<!-- ══ SLIDER 5: FAMILY ══ -->
<div class="slider-section" data-category="family">
  <div class="slider-header">
    <div>
      <div class="slider-label">Family Packages</div>
      <div class="slider-title">Holidays for <em>Everyone</em></div>
    </div>
    <div class="slider-arrows">
      <button class="arrow-btn" onclick="slide('family-track',-1)">&#8592;</button>
      <button class="arrow-btn" onclick="slide('family-track', 1)">&#8594;</button>
    </div>
  </div>
  <div class="slider-track" id="family-track">

    <div class="offer-card">
      <div class="card-img-wrap">
        <img class="card-img" src="https://images.unsplash.com/photo-1596436889106-be35e843f974?w=700&q=80" alt="Family Resort">
        <span class="card-badge badge-gold">Family</span>
        <div class="soon-ribbon">Deal Coming Soon</div>
      </div>
      <div class="card-body">
        <div class="card-name">Family Resorts</div>
        <div class="card-sub">Kid-friendly stays with pools, activities &amp; more</div>
        <div class="card-footer">
          <span class="card-price">Contact for Price</span>
          <a href="https://wa.me/9875073788" class="card-cta">Enquire</a>
        </div>
      </div>
    </div>

    <div class="offer-card">
      <div class="card-img-wrap">
        <img class="card-img" src="https://images.unsplash.com/photo-1609766857272-ec2b31534384?w=700&q=80" alt="Kochi Family">
        <span class="card-badge badge-hot">Explore</span>
        <div class="soon-ribbon">Deal Coming Soon</div>
      </div>
      <div class="card-body">
        <div class="card-name">South India Family Tour</div>
        <div class="card-sub">Kochi, Munnar &amp; Kerala backwaters — a cultural journey</div>
        <div class="card-footer">
          <span class="card-price">Contact for Price</span>
          <a href="https://wa.me/9875073788" class="card-cta">Enquire</a>
        </div>
      </div>
    </div>

    <div class="offer-card">
      <div class="card-img-wrap">
        <img class="card-img" src="https://images.unsplash.com/photo-1595658658481-d53d3f999875?w=700&q=80" alt="Mumbai Family">
        <span class="card-badge badge-new">City Break</span>
        <div class="soon-ribbon">Deal Coming Soon</div>
      </div>
      <div class="card-body">
        <div class="card-name">Mumbai City Break</div>
        <div class="card-sub">The city of dreams — sightseeing, food &amp; fun for all</div>
        <div class="card-footer">
          <span class="card-price">Contact for Price</span>
          <a href="https://wa.me/9875073788" class="card-cta">Enquire</a>
        </div>
      </div>
    </div>

    <div class="offer-card">
      <div class="card-img-wrap">
        <img class="card-img" src="https://images.unsplash.com/photo-1555396273-367ea4eb4db5?w=700&q=80" alt="All Inclusive Family">
        <span class="card-badge badge-gold">All-Inclusive</span>
        <div class="soon-ribbon">Deal Coming Soon</div>
      </div>
      <div class="card-body">
        <div class="card-name">All-Inclusive Packages</div>
        <div class="card-sub">Flights + hotel + meals — everything taken care of</div>
        <div class="card-footer">
          <span class="card-price">Contact for Price</span>
          <a href="https://wa.me/9875073788" class="card-cta">Enquire</a>
        </div>
      </div>
    </div>

  </div>
  <div class="slider-dots" id="family-dots">
    <div class="dot active" onclick="scrollToDot('family-track',0)"></div>
    <div class="dot" onclick="scrollToDot('family-track',1)"></div>
    <div class="dot" onclick="scrollToDot('family-track',2)"></div>
    <div class="dot" onclick="scrollToDot('family-track',3)"></div>
  </div>
</div>

<!-- NOTIFY + WA -->
<div class="bottom-section">
  <div class="bottom-card">
    <span class="bc-tag">Stay Ahead</span>
    <h3>Be the First to <em>Know</em></h3>
    <p>Drop your WhatsApp number and we'll notify you the moment a new deal goes live — no spam, ever.</p>
    <form class="notify-form" onsubmit="handleNotify(event)">
      <input type="tel" id="notifyPhone" placeholder="+91 XXXXX XXXXX" required>
      <button type="submit">Notify Me</button>
    </form>
    <p class="notify-note">WhatsApp only. We won't call unless you ask.</p>
    <p class="notify-success" id="notifySuccess">✓ Done! We'll WhatsApp you as soon as a deal drops.</p>
    <div class="or-row"><span>OR</span></div>
    <a href="https://wa.me/9875073788" target="_blank" class="wa-btn">
      <svg viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
      Ask for Latest Deals on WhatsApp
    </a>
  </div>
</div>

@endsection

@push('scripts')
<script>


  function slide(id, dir) {
    const t = document.getElementById(id);
    const w = t.querySelector('.offer-card').offsetWidth + 20;
    t.scrollBy({ left: dir * w, behavior: 'smooth' });
    setTimeout(() => updateDots(id), 350);
  }
  function scrollToDot(id, idx) {
    const t = document.getElementById(id);
    const w = t.querySelector('.offer-card').offsetWidth + 20;
    t.scrollTo({ left: idx * w, behavior: 'smooth' });
    setTimeout(() => updateDots(id), 350);
  }
  function updateDots(id) {
    const t = document.getElementById(id);
    const w = t.querySelector('.offer-card').offsetWidth + 20;
    const idx = Math.round(t.scrollLeft / w);
    const dotsId = id.replace('-track', '-dots');
    document.querySelectorAll(`#${dotsId} .dot`).forEach((d, i) => d.classList.toggle('active', i === idx));
  }
  ['hotels','cruises','flights','honeymoon','family'].forEach(c => {
    const t = document.getElementById(`${c}-track`);
    if (t) t.addEventListener('scroll', () => updateDots(`${c}-track`));
  });
  function filterOffers(cat, btn) {
    document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    document.querySelectorAll('.slider-section').forEach(s => {
      s.style.display = (cat === 'all' || s.dataset.category === cat) ? '' : 'none';
    });
    document.querySelectorAll('.s-divider').forEach(d => d.style.display = cat === 'all' ? '' : 'none');
  }
  function handleNotify(e) {
    e.preventDefault();
    const btn = e.target.querySelector('button');
    btn.textContent = 'Done!'; btn.disabled = true;
    document.getElementById('notifySuccess').style.display = 'block';
  }
</script>
@endpush
