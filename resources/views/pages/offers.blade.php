@extends('layouts.frontend')

@section('meta_title', 'Exclusive Travel Offers & Deals — Limited Time Discounts | TYT Luxe')
@section('meta_description', 'Explore exclusive limited-time travel deals and offers from TYT Luxe. Discover discounted luxury hotel packages, cruise deals and special promotions tailored for Indian travellers.')

@push('styles')
<style>
  *, *::before, *::after { box-sizing: border-box; }

  :root {
    --black:      #080808;
    --dark:       #111111;
    --dark-card:  #141414;
    --dark-mid:   #1a1a1a;
    --border:     rgba(255,255,255,0.07);
    --border-h:   rgba(201,168,76,0.35);
    --gold:       #C9A84C;
    --gold-light: #E2C97E;
    --gold-dim:   rgba(201,168,76,0.12);
    --gold-glow:  rgba(201,168,76,0.25);
    --white:      #ffffff;
    --w80:        rgba(255,255,255,0.80);
    --w60:        rgba(255,255,255,0.60);
    --w40:        rgba(255,255,255,0.40);
    --w20:        rgba(255,255,255,0.20);
    --red:        #e74c3c;
    --green:      #27ae60;
  }

  body { font-family: 'Poppins', sans-serif; background: var(--black); color: var(--white); }

  /* ──────────────────────────────────────────────────────
     AMBIENT BACKGROUND GLOW
  ────────────────────────────────────────────────────── */
  .offers-page-wrap {
    position: relative;
    overflow: hidden;
  }
  .offers-page-wrap::before,
  .offers-page-wrap::after {
    content: '';
    position: fixed;
    border-radius: 50%;
    pointer-events: none;
    z-index: 0;
  }
  .offers-page-wrap::before {
    width: 700px; height: 700px;
    top: -200px; left: -200px;
    background: radial-gradient(circle, rgba(201,168,76,.06) 0%, transparent 70%);
  }
  .offers-page-wrap::after {
    width: 600px; height: 600px;
    bottom: 10%; right: -150px;
    background: radial-gradient(circle, rgba(201,168,76,.05) 0%, transparent 70%);
  }

  /* ──────────────────────────────────────────────────────
     FILTER BAR — pill tabs with glow active state
  ────────────────────────────────────────────────────── */
  .filter-bar {
    display: flex; align-items: center; justify-content: center;
    gap: .5rem; padding: 2.8rem 2rem 0; flex-wrap: wrap;
    position: relative; z-index: 1;
  }
  .filter-btn {
    padding: .5rem 1.35rem; border-radius: 100px;
    border: 1px solid var(--border);
    background: transparent; color: var(--w40);
    font-family: 'Poppins', sans-serif;
    font-size: 10.5px; font-weight: 500; letter-spacing: .1em; text-transform: uppercase;
    cursor: pointer; transition: all .25s cubic-bezier(.4,0,.2,1);
    position: relative; overflow: hidden;
  }
  .filter-btn::before {
    content: '';
    position: absolute; inset: 0;
    background: var(--gold-dim);
    opacity: 0; transition: opacity .25s;
    border-radius: inherit;
  }
  .filter-btn:hover { border-color: var(--gold); color: var(--gold); }
  .filter-btn:hover::before { opacity: 1; }
  .filter-btn.active {
    background: linear-gradient(135deg, #C9A84C 0%, #E2C97E 50%, #C9A84C 100%);
    border-color: transparent; color: #080808; font-weight: 700;
    box-shadow: 0 4px 20px rgba(201,168,76,.4);
  }
  .filter-btn.active::before { opacity: 0; }

  /* ──────────────────────────────────────────────────────
     SECTION WRAPPER
  ────────────────────────────────────────────────────── */
  .slider-section {
    padding: 4rem 0 0;
    position: relative; z-index: 1;
    animation: sectionFadeUp .5s ease both;
  }
  @keyframes sectionFadeUp {
    from { opacity: 0; transform: translateY(24px); }
    to   { opacity: 1; transform: translateY(0); }
  }

  /* ── SECTION HEADER ── */
  .slider-header {
    display: flex; align-items: flex-end; justify-content: space-between;
    padding: 0 3.5rem; margin-bottom: 2rem;
  }
  .slider-label {
    font-size: 10px; font-weight: 700; letter-spacing: .28em;
    text-transform: uppercase; color: var(--gold); margin-bottom: .4rem;
    display: flex; align-items: center; gap: .5rem;
  }
  .slider-label::before {
    content: ''; display: inline-block;
    width: 18px; height: 1px; background: var(--gold); flex-shrink: 0;
  }
  .slider-title {
    font-family: 'Playfair Display', serif; font-size: 2rem;
    font-weight: 400; color: var(--white); line-height: 1.18;
  }
  .slider-title em { font-style: italic; color: var(--gold-light); }

  .slider-arrows { display: flex; gap: .5rem; }
  .arrow-btn {
    width: 40px; height: 40px; border-radius: 50%;
    border: 1px solid var(--border);
    background: var(--dark-card); color: var(--w40); font-size: 15px;
    cursor: pointer; display: flex; align-items: center; justify-content: center;
    transition: all .22s; flex-shrink: 0;
  }
  .arrow-btn:hover {
    border-color: var(--gold); color: var(--gold);
    background: var(--gold-dim);
    box-shadow: 0 0 16px var(--gold-glow);
  }

  /* ── SLIDER TRACK ── */
  .slider-track {
    display: flex; gap: 1.5rem; padding: 0.5rem 3.5rem 1.5rem;
    overflow-x: auto; scroll-behavior: smooth;
    scrollbar-width: none; -ms-overflow-style: none;
    scroll-snap-type: x mandatory;
  }
  .slider-track::-webkit-scrollbar { display: none; }

  /* ──────────────────────────────────────────────────────
     OFFER CARD — the star of the show
  ────────────────────────────────────────────────────── */
  .offer-card {
    flex: 0 0 300px; scroll-snap-align: start;
    border-radius: 16px; overflow: hidden;
    position: relative; cursor: pointer;
    border: 1px solid var(--border);
    background: var(--dark-card);
    transition: transform .3s cubic-bezier(.4,0,.2,1),
                box-shadow .3s cubic-bezier(.4,0,.2,1),
                border-color .3s;
    /* Stagger each card slightly */
  }
  .offer-card:hover {
    transform: translateY(-8px) scale(1.01);
    box-shadow: 0 28px 60px rgba(0,0,0,.65), 0 0 0 1px var(--border-h);
    border-color: var(--border-h);
  }

  /* Image */
  .card-img-wrap { position: relative; overflow: hidden; height: 210px; }
  .card-img {
    width: 100%; height: 100%; object-fit: cover; display: block;
    transition: transform .5s cubic-bezier(.4,0,.2,1);
  }
  .offer-card:hover .card-img { transform: scale(1.07); }

  /* Gradient overlay on image */
  .card-img-wrap::after {
    content: ''; position: absolute; inset: 0;
    background: linear-gradient(
      to bottom,
      rgba(0,0,0,0) 30%,
      rgba(0,0,0,.5) 70%,
      rgba(0,0,0,.85) 100%
    );
    transition: opacity .3s;
  }
  .offer-card:hover .card-img-wrap::after { opacity: .8; }

  /* No-image fallback */
  .card-img-placeholder {
    width: 100%; height: 100%;
    background: linear-gradient(135deg, #1a1a1a 0%, #222 50%, #181818 100%);
    display: flex; align-items: center; justify-content: center;
    font-size: 2.5rem; opacity: .4;
  }

  /* Badge */
  .card-badge {
    position: absolute; top: 14px; left: 14px; z-index: 3;
    font-size: 8.5px; font-weight: 800; letter-spacing: .18em; text-transform: uppercase;
    padding: 4px 11px; border-radius: 100px;
  }
  .badge-gold {
    background: linear-gradient(135deg, #C9A84C, #E2C97E);
    color: #080808;
    box-shadow: 0 2px 12px rgba(201,168,76,.5);
  }
  .badge-hot  { background: var(--red);   color: #fff; box-shadow: 0 2px 12px rgba(231,76,60,.45); }
  .badge-new  { background: var(--green); color: #fff; box-shadow: 0 2px 12px rgba(39,174,96,.45); }

  /* Expiry ticker on image */
  .card-expiry-ticker {
    position: absolute; bottom: 10px; right: 12px; z-index: 3;
    font-size: 9px; font-weight: 600; letter-spacing: .06em;
    color: var(--w60); background: rgba(0,0,0,.55); backdrop-filter: blur(6px);
    padding: 3px 9px; border-radius: 100px; border: 1px solid rgba(255,255,255,.1);
    display: flex; align-items: center; gap: 4px;
  }
  .card-expiry-ticker::before { content: '⏳'; font-size: 8px; }

  /* Coming soon ribbon */
  .soon-ribbon {
    position: absolute; bottom: 0; left: 0; right: 0; z-index: 4;
    background: rgba(8,8,8,.88); backdrop-filter: blur(6px);
    text-align: center; padding: .6rem;
    font-size: 9.5px; font-weight: 700; letter-spacing: .2em; text-transform: uppercase;
    color: var(--gold);
    border-top: 1px solid var(--border-h);
  }

  /* Card body */
  .card-body {
    padding: 1.25rem 1.4rem 1.35rem;
    background: var(--dark-card);
    position: relative;
  }
  /* subtle gold glow on hover inside body */
  .offer-card:hover .card-body::before {
    content: '';
    position: absolute; inset: 0;
    background: linear-gradient(180deg, var(--gold-dim) 0%, transparent 60%);
    pointer-events: none;
  }

  .card-name {
    font-family: 'Playfair Display', serif; font-size: 1.1rem;
    font-weight: 400; color: var(--white); margin-bottom: .3rem;
    line-height: 1.3;
  }
  .card-sub {
    font-size: 11px; color: var(--w40); line-height: 1.65; margin-bottom: 1rem;
  }
  .card-footer {
    display: flex; align-items: center; justify-content: space-between;
    gap: .5rem;
  }
  .card-price {
    font-size: 13px; color: var(--gold); font-weight: 600;
    font-variant-numeric: tabular-nums;
  }
  .card-price-contact { font-size: 11px; color: var(--w40); font-style: italic; }

  .card-cta {
    font-size: 9.5px; font-weight: 700; letter-spacing: .1em; text-transform: uppercase;
    color: #080808;
    background: linear-gradient(135deg, #C9A84C 0%, #E2C97E 100%);
    border: none; border-radius: 6px;
    padding: .5rem 1rem; cursor: pointer; text-decoration: none;
    transition: all .22s; flex-shrink: 0;
    box-shadow: 0 2px 10px rgba(201,168,76,.3);
  }
  .card-cta:hover {
    box-shadow: 0 4px 20px rgba(201,168,76,.55);
    transform: translateY(-1px);
  }

  /* ──────────────────────────────────────────────────────
     SLIDER DOTS
  ────────────────────────────────────────────────────── */
  .slider-dots {
    display: flex; justify-content: center; gap: 7px;
    padding: .4rem 0 2.5rem;
  }
  .dot {
    width: 6px; height: 6px; border-radius: 50%;
    background: rgba(255,255,255,.12); cursor: pointer;
    transition: all .3s cubic-bezier(.4,0,.2,1);
  }
  .dot.active {
    background: var(--gold); width: 22px; border-radius: 3px;
    box-shadow: 0 0 8px var(--gold-glow);
  }

  /* ──────────────────────────────────────────────────────
     SECTION DIVIDER — golden shimmer line
  ────────────────────────────────────────────────────── */
  .s-divider {
    display: flex; align-items: center; gap: 1.25rem;
    max-width: 1100px; margin: .5rem auto 0; padding: 0 3.5rem;
  }
  .s-divider::before,
  .s-divider::after {
    content: ''; flex: 1; height: 1px;
    background: linear-gradient(to right, transparent, var(--border), transparent);
  }
  .s-divider span {
    font-size: 9px; color: var(--gold); letter-spacing: .25em;
    text-transform: uppercase; white-space: nowrap; opacity: .6;
  }

  /* ──────────────────────────────────────────────────────
     EMPTY STATE — "Coming Soon"
  ────────────────────────────────────────────────────── */
  .offers-empty-state {
    display: flex; align-items: center; justify-content: center;
    padding: 5rem 2rem 4rem; min-height: 50vh;
    position: relative; z-index: 1;
  }
  .oes-inner {
    max-width: 580px; text-align: center;
    background: var(--dark-card); border: 1px solid var(--border);
    border-radius: 20px; padding: 4rem 3rem;
    position: relative; overflow: hidden;
  }
  .oes-inner::before {
    content: ''; position: absolute; top: -100px; left: 50%;
    transform: translateX(-50%);
    width: 400px; height: 400px; border-radius: 50%;
    background: radial-gradient(circle, rgba(201,168,76,.12) 0%, transparent 70%);
    pointer-events: none;
  }
  .oes-icon { font-size: 3.5rem; margin-bottom: 1.5rem; display: block; animation: float 3s ease-in-out infinite; }
  @keyframes float { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-10px)} }
  .oes-eyebrow {
    font-size: 10px; font-weight: 700; letter-spacing: .28em;
    text-transform: uppercase; color: var(--gold); margin-bottom: .65rem;
  }
  .oes-heading {
    font-family: 'Playfair Display', serif; font-size: 2.1rem;
    font-weight: 400; color: var(--white); line-height: 1.22; margin-bottom: 1rem;
  }
  .oes-heading em { font-style: italic; color: var(--gold-light); }
  .oes-body { font-size: 13px; color: var(--w40); line-height: 1.9; margin-bottom: 2rem; }
  .oes-btn {
    display: inline-block;
    background: linear-gradient(135deg, #C9A84C 0%, #E2C97E 50%, #C9A84C 100%);
    color: #080808; text-decoration: none; border-radius: 8px; padding: .85rem 2rem;
    font-size: 11px; font-weight: 700; letter-spacing: .12em; text-transform: uppercase;
    box-shadow: 0 4px 24px rgba(201,168,76,.4);
    transition: box-shadow .25s, transform .2s;
  }
  .oes-btn:hover { box-shadow: 0 6px 32px rgba(201,168,76,.6); transform: translateY(-2px); }

  /* ──────────────────────────────────────────────────────
     BOTTOM CTA SECTION
  ────────────────────────────────────────────────────── */
  .bottom-section {
    max-width: 680px; margin: 4rem auto 6rem;
    padding: 0 2rem; text-align: center;
    position: relative; z-index: 1;
  }
  .bottom-card {
    background: var(--dark-card); border: 1px solid var(--border);
    border-radius: 20px; padding: 3rem 2.5rem;
    position: relative; overflow: hidden;
  }
  .bottom-card::before {
    content: ''; position: absolute; top: -80px; left: 50%;
    transform: translateX(-50%);
    width: 400px; height: 400px; border-radius: 50%;
    background: radial-gradient(circle, rgba(201,168,76,.09) 0%, transparent 70%);
    pointer-events: none;
  }
  .bc-tag {
    font-size: 10px; font-weight: 700; letter-spacing: .24em;
    text-transform: uppercase; color: var(--gold);
    display: block; margin-bottom: .9rem;
  }
  .bottom-card h3 {
    font-family: 'Playfair Display', serif; font-size: 1.85rem;
    font-weight: 400; color: var(--white); margin-bottom: .6rem; line-height: 1.25;
  }
  .bottom-card h3 em { font-style: italic; color: var(--gold-light); }
  .bottom-card p { font-size: 13px; color: var(--w40); line-height: 1.85; margin-bottom: 1.75rem; }
  .notify-form {
    display: flex; gap: .75rem; max-width: 420px; margin: 0 auto 1rem;
  }
  .notify-form input {
    flex: 1; background: var(--dark-mid); border: 1px solid var(--border);
    border-radius: 8px; padding: .8rem 1.1rem;
    font-family: 'Poppins', sans-serif; font-size: 13px; color: var(--white);
    outline: none; transition: border-color .2s, box-shadow .2s;
  }
  .notify-form input::placeholder { color: var(--w20); }
  .notify-form input:focus { border-color: var(--gold); box-shadow: 0 0 0 3px var(--gold-dim); }
  .notify-form button {
    background: linear-gradient(135deg, #C9A84C, #E2C97E);
    border: none; border-radius: 8px; padding: .8rem 1.2rem;
    font-family: 'Poppins', sans-serif; font-size: 10px; font-weight: 700;
    letter-spacing: .1em; text-transform: uppercase;
    color: #080808; cursor: pointer; white-space: nowrap;
    box-shadow: 0 2px 14px rgba(201,168,76,.35);
    transition: box-shadow .22s, transform .2s;
  }
  .notify-form button:hover { box-shadow: 0 4px 22px rgba(201,168,76,.55); transform: translateY(-1px); }
  .notify-note { font-size: 11px; color: var(--w20); margin-bottom: 1.75rem; }
  .or-row { display: flex; align-items: center; gap: .75rem; margin: 1.5rem 0; }
  .or-row::before,.or-row::after {
    content: ''; flex: 1; height: 1px;
    background: linear-gradient(to right, transparent, var(--border), transparent);
  }
  .or-row span { font-size: 10px; color: var(--w20); letter-spacing: .12em; }
  .wa-btn {
    display: inline-flex; align-items: center; gap: 10px;
    background: #25D366; color: #fff;
    text-decoration: none; border-radius: 8px; padding: .9rem 1.9rem;
    font-size: 12px; font-weight: 700; letter-spacing: .09em; text-transform: uppercase;
    box-shadow: 0 4px 18px rgba(37,211,102,.3);
    transition: box-shadow .22s, transform .2s;
  }
  .wa-btn:hover { box-shadow: 0 6px 26px rgba(37,211,102,.5); transform: translateY(-1px); }
  .wa-btn svg { width: 18px; height: 18px; fill: #fff; flex-shrink: 0; }

  /* ──────────────────────────────────────────────────────
     CARD ENTRANCE ANIMATION
  ────────────────────────────────────────────────────── */
  .offer-card {
    opacity: 0;
    animation: cardIn .45s ease forwards;
  }
  .offer-card:nth-child(1)  { animation-delay: .05s; }
  .offer-card:nth-child(2)  { animation-delay: .12s; }
  .offer-card:nth-child(3)  { animation-delay: .19s; }
  .offer-card:nth-child(4)  { animation-delay: .26s; }
  .offer-card:nth-child(5)  { animation-delay: .33s; }
  .offer-card:nth-child(n+6){ animation-delay: .38s; }
  @keyframes cardIn {
    from { opacity: 0; transform: translateY(20px) scale(.98); }
    to   { opacity: 1; transform: translateY(0)    scale(1);   }
  }

  /* ──────────────────────────────────────────────────────
     RESPONSIVE
  ────────────────────────────────────────────────────── */
  @media(max-width: 768px) {
    .slider-header { padding: 0 1.5rem; }
    .slider-track  { padding: .5rem 1.5rem 1.5rem; gap: 1rem; }
    .offer-card    { flex: 0 0 265px; }
    .notify-form   { flex-direction: column; }
    .slider-title  { font-size: 1.55rem; }
    .s-divider     { padding: 0 1.5rem; }
  }
</style>
@endpush

@section('content')

<div class="offers-page-wrap">

{{-- HERO --}}
<x-hero-carousel
  :slides="$heroImages"
  eyebrow="{{ $heroEyebrow }}"
  :title="$heroTitle"
  subtitle="{{ $heroSubtitle }}"
  ctaText=""
  ctaLink=""
/>

{{-- FILTER TABS --}}
<div class="filter-bar">
  @foreach($filterTabs as $tab)
    <button
      class="filter-btn {{ $loop->first ? 'active' : '' }}"
      onclick="filterOffers('{{ $tab['key'] }}', this)"
    >{{ $tab['label'] }}</button>
  @endforeach
</div>

{{-- ── DYNAMIC OFFER CATEGORIES ── --}}
@if(empty($categories))

  {{-- EMPTY STATE --}}
  <div class="offers-empty-state">
    <div class="oes-inner">
      <span class="oes-icon">🎁</span>
      <div class="oes-eyebrow">Stay Tuned</div>
      <h2 class="oes-heading">Exciting Offers are <em>Coming Soon</em></h2>
      <p class="oes-body">We're handpicking the finest deals on luxury hotels, cruises, flights &amp; packages — curated just for you. Check back shortly, or leave your number and we'll WhatsApp you the moment a deal drops.</p>
      <a href="#notify" class="oes-btn" onclick="document.getElementById('notifyPhone').focus();return false;">
        Notify Me When Deals Drop
      </a>
    </div>
  </div>

@else

  @foreach($categories as $catIndex => $category)
    @php
      $trackId = $category['category_key'] . '-track';
      $dotsId  = $category['category_key'] . '-dots';
    @endphp

    @if($catIndex > 0)
      <div class="s-divider"><span>✦</span></div>
    @endif

    <div class="slider-section" data-category="{{ $category['category_key'] }}">

      <div class="slider-header">
        <div>
          @if(!empty($category['slider_label']))
            <div class="slider-label">{{ $category['slider_label'] }}</div>
          @endif
          @if(!empty($category['slider_title']))
            <div class="slider-title">{!! $category['slider_title'] !!}</div>
          @endif
        </div>
        <div class="slider-arrows">
          <button class="arrow-btn" onclick="slide('{{ $trackId }}',-1)" aria-label="Previous">&#8592;</button>
          <button class="arrow-btn" onclick="slide('{{ $trackId }}', 1)" aria-label="Next">&#8594;</button>
        </div>
      </div>

      <div class="slider-track" id="{{ $trackId }}">
        @foreach($category['cards'] as $card)
          <div class="offer-card">

            {{-- Image --}}
            <div class="card-img-wrap">
              @if(!empty($card['resolved_image']))
                <img class="card-img" src="{{ $card['resolved_image'] }}" alt="{{ $card['name'] ?? '' }}" loading="lazy">
              @else
                <div class="card-img-placeholder">✈️</div>
              @endif

              @if(!empty($card['badge_label']))
                <span class="card-badge {{ $card['badge_type'] ?? 'badge-gold' }}">{{ $card['badge_label'] }}</span>
              @endif

              @if(!empty($card['coming_soon']))
                <div class="soon-ribbon">Deal Coming Soon</div>
              @endif
            </div>

            {{-- Body --}}
            <div class="card-body">
              <div class="card-name">{{ $card['name'] ?? '' }}</div>
              @if(!empty($card['subtitle']))
                <div class="card-sub">{{ $card['subtitle'] }}</div>
              @endif
              <div class="card-footer">
                @if(!empty($card['price']))
                  <span class="card-price">{{ $card['price'] }}</span>
                @else
                  <span class="card-price-contact">Contact for price</span>
                @endif
                @unless(!empty($card['coming_soon']))
                  <a href="{{ $card['enquire_link'] ?? $ctaWhatsapp }}" class="card-cta" target="_blank" rel="noopener">Enquire</a>
                @endunless
              </div>
            </div>

          </div>
        @endforeach
      </div>

      @if(count($category['cards']) > 1)
        <div class="slider-dots" id="{{ $dotsId }}">
          @foreach($category['cards'] as $di => $__)
            <div class="dot {{ $di === 0 ? 'active' : '' }}" onclick="scrollToDot('{{ $trackId }}', {{ $di }})"></div>
          @endforeach
        </div>
      @endif

    </div>
  @endforeach

@endif

{{-- BOTTOM CTA --}}
<div class="bottom-section" id="notify">
  <div class="bottom-card">
    @if(!empty($ctaTag))<span class="bc-tag">{{ $ctaTag }}</span>@endif
    @if(!empty($ctaHeading))<h3>{!! $ctaHeading !!}</h3>@endif
    @if(!empty($ctaBody))<p>{{ $ctaBody }}</p>@endif
    <form class="notify-form" onsubmit="handleNotify(event)">
      <input type="tel" id="notifyPhone" placeholder="+91 XXXXX XXXXX" required>
      <button type="submit">Notify Me</button>
    </form>
    @if(!empty($ctaNotifyNote))<p class="notify-note">{{ $ctaNotifyNote }}</p>@endif
    <div class="or-row"><span>OR</span></div>
    <a href="{{ $ctaWhatsapp }}" target="_blank" class="wa-btn">
      <svg viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
      {{ $ctaWaLabel }}
    </a>
  </div>
</div>

</div>{{-- .offers-page-wrap --}}

@endsection

@push('scripts')
<script>
  /* ── Slider ── */
  function slide(id, dir) {
    const t = document.getElementById(id);
    const card = t.querySelector('.offer-card');
    if (!card) return;
    t.scrollBy({ left: dir * (card.offsetWidth + 24), behavior: 'smooth' });
    setTimeout(() => updateDots(id), 380);
  }
  function scrollToDot(id, idx) {
    const t = document.getElementById(id);
    const card = t.querySelector('.offer-card');
    if (!card) return;
    t.scrollTo({ left: idx * (card.offsetWidth + 24), behavior: 'smooth' });
    setTimeout(() => updateDots(id), 380);
  }
  function updateDots(id) {
    const t = document.getElementById(id);
    const card = t.querySelector('.offer-card');
    if (!card) return;
    const idx = Math.round(t.scrollLeft / (card.offsetWidth + 24));
    const dotsEl = document.getElementById(id.replace('-track', '-dots'));
    if (dotsEl) dotsEl.querySelectorAll('.dot').forEach((d,i) => d.classList.toggle('active', i === idx));
  }
  document.querySelectorAll('.slider-track').forEach(t => {
    t.addEventListener('scroll', () => updateDots(t.id), { passive: true });
  });

  /* ── Filter ── */
  function filterOffers(cat, btn) {
    document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    document.querySelectorAll('.slider-section').forEach(s => {
      s.style.display = (cat === 'all' || s.dataset.category === cat) ? '' : 'none';
    });
    document.querySelectorAll('.s-divider').forEach(d => d.style.display = cat === 'all' ? '' : 'none');
  }

  /* ── Notify Me ── */
  async function handleNotify(e) {
    e.preventDefault();
    const btn = e.target.querySelector('button');
    const orig = btn.textContent;
    btn.textContent = 'Sending…'; btn.disabled = true;
    try {
      await fetch("{{ route('enquiries.store') }}", {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify({
          vertical: 'general', reference_id: 0,
          name: 'Guest (Notify Me)',
          phone: document.getElementById('notifyPhone').value,
          message: 'Requested to be notified about latest deals via WhatsApp.'
        })
      });
      e.target.reset();
      btn.textContent = '✓ You\'re on the list!';
      btn.style.background = '#27ae60';
      setTimeout(() => { btn.textContent = orig; btn.style.background = ''; btn.disabled = false; }, 4000);
    } catch (err) {
      btn.textContent = orig; btn.disabled = false;
      showToast?.('Error', 'Something went wrong. Please try again.', 'error');
    }
  }
</script>
@endpush
