@extends('layouts.frontend')

@section('meta_title', 'Travel Packages — Domestic & International Holiday Packages | TYT Luxe')
@section('meta_description', 'Discover curated domestic and international travel packages with TYT Luxe. Honeymoon specials, family packages, adventure trips and luxury getaways — all tailored for Indian travellers.')

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com" />
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;1,400;1,600&family=Jost:wght@300;400;500;600;700&display=swap" rel="stylesheet" />

<style>
/* ===== RESET & VARIABLES ===== */
:root {
  --gold: #c9a84c;
  --gold-light: #e8c96b;
  --gold-dim: rgba(201,168,76,0.15);
  --gold-glow: rgba(201,168,76,0.25);
  --dark: #0d0d0d;
  --dark-2: #141414;
  --dark-3: #1a1a1a;
  --dark-4: #111111;
  --white: #ffffff;
  --white-80: rgba(255,255,255,0.80);
  --white-60: rgba(255,255,255,0.60);
  --white-30: rgba(255,255,255,0.30);
  --white-10: rgba(255,255,255,0.08);
  --green: #4caf82;
  --radius: 14px;
  --transition: 0.35s cubic-bezier(0.25, 0.46, 0.45, 0.94);
}

* { box-sizing: border-box; margin: 0; padding: 0; }

/* ===== TRUST BAR ===== */
.pkg-trust {
  background: var(--dark-2);
  border-top: 1px solid var(--gold-dim); border-bottom: 1px solid var(--gold-dim);
  padding: 18px 40px;
}
.pkg-trust-inner {
  max-width: 1200px; margin: 0 auto;
  display: flex; justify-content: center; gap: 48px; flex-wrap: wrap;
}
.pkg-trust-item {
  display: flex; align-items: center; gap: 10px;
  font-family: 'Jost', sans-serif; font-size: 12px; font-weight: 500;
  letter-spacing: 0.08em; color: var(--white-60); text-transform: uppercase;
}
.pkg-trust-item i { color: var(--gold); }

/* ===== MAIN WRAPPER ===== */
.pkg-page { background: var(--dark); min-height: 80vh; padding-bottom: 80px; }

/* ===== SECTION HEADER ===== */
.pkg-section { padding: 80px 40px 0; }
.pkg-section-inner { max-width: 1200px; margin: 0 auto; }
.pkg-header { text-align: center; margin-bottom: 48px; }
.pkg-eyebrow {
  font-family: 'Jost', sans-serif; font-size: 10.5px; font-weight: 600;
  letter-spacing: 0.22em; text-transform: uppercase; color: var(--gold); margin-bottom: 14px;
}
.pkg-title {
  font-family: 'Cormorant Garamond', serif;
  font-size: clamp(2.2rem, 4vw, 3.4rem); font-weight: 500;
  line-height: 1.1; color: #fff; margin-bottom: 14px;
}
.pkg-title em { font-style: italic; color: var(--gold-light); }
.pkg-desc {
  font-family: 'Jost', sans-serif; font-size: 14.5px; color: var(--white-60);
  max-width: 520px; margin: 0 auto; font-weight: 300; line-height: 1.7;
}

/* ===== CATEGORY TABS ===== */
.cat-tabs-wrap {
  display: flex; justify-content: center; margin-bottom: 56px;
  gap: 0; background: var(--dark-3); border-radius: 100px;
  border: 1px solid var(--white-10); padding: 6px;
  width: fit-content; margin-left: auto; margin-right: auto;
}
.cat-tab {
  font-family: 'Jost', sans-serif; font-size: 12px; font-weight: 600;
  letter-spacing: 0.12em; text-transform: uppercase;
  color: var(--white-60); background: transparent;
  border: none; cursor: pointer; padding: 13px 36px; border-radius: 100px;
  display: flex; align-items: center; gap: 10px;
  transition: all var(--transition); white-space: nowrap;
}
.cat-tab i { font-size: 13px; }
.cat-tab:hover { color: #fff; }
.cat-tab.active {
  background: var(--gold); color: var(--dark);
  box-shadow: 0 4px 20px rgba(201,168,76,0.35);
}
.cat-tab.active i { color: var(--dark); }

/* ===== CATEGORY PANEL ===== */
.cat-panel { display: none; }
.cat-panel.active { display: block; }

/* ===== DESTINATION SECTION LABEL ===== */
.dest-label {
  font-family: 'Jost', sans-serif; font-size: 10px; font-weight: 700;
  letter-spacing: 0.25em; text-transform: uppercase;
  color: var(--gold); margin-bottom: 28px;
  display: flex; align-items: center; gap: 12px;
}
.dest-label::after { content: ''; flex: 1; height: 1px; background: var(--white-10); }

/* ===== DESTINATION GRID ===== */
.dest-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
  gap: 22px; margin-bottom: 60px;
}

/* ===== TOUR TYPE PILL ===== */
.pkg-tour-type-pill {
  position: absolute;
  top: 15px;
  right: 15px;
  background-color: var(--gold);
  color: var(--dark);
  padding: 6px 14px;
  border-radius: 20px;
  font-size: 0.8rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  z-index: 2;
  box-shadow: 0 4px 10px rgba(0,0,0,0.3);
}



/* ===== STATE FILTER BAR ===== */
.state-filter-wrap {
  display: flex; align-items: center; gap: 8px;
  overflow-x: auto; scrollbar-width: none; -ms-overflow-style: none;
  margin-bottom: 24px; padding-bottom: 4px;
}
.state-filter-wrap::-webkit-scrollbar { display: none; }
.state-tab {
  flex-shrink: 0; padding: 6px 16px; font-size: 0.88rem; font-weight: 500;
  color: var(--white-60); background: rgba(255,255,255,0.03);
  border: 1px solid rgba(255,255,255,0.06); border-radius: 30px;
  cursor: pointer; transition: all var(--transition);
}
.state-tab:hover { color: #fff; background: rgba(255,255,255,0.1); }
.state-tab.active { color: #fff; background: var(--gold); border-color: var(--gold); font-weight: 600; }

/* ===== DESTINATION CARD ===== */
.dest-card {
  position: relative; border-radius: var(--radius); overflow: hidden;
  cursor: pointer; height: 380px;
  border: 1px solid rgba(255,255,255,0.06);
  transition: all var(--transition);
  flex-shrink: 0;
}
.dest-card:hover {
  transform: translateY(-6px);
  border-color: rgba(201,168,76,0.4);
  box-shadow: 0 24px 56px rgba(0,0,0,0.6), 0 0 0 1px rgba(201,168,76,0.15);
}
.dest-card-img {
  position: absolute; inset: 0;
  background-size: cover; background-position: center;
  transition: transform 0.7s ease;
}
.dest-card:hover .dest-card-img { transform: scale(1.08); }
.dest-card-overlay {
  position: absolute; inset: 0;
  background: linear-gradient(to top, rgba(13,13,13,0.98) 0%, rgba(13,13,13,0.55) 55%, rgba(13,13,13,0.1) 100%);
  transition: background var(--transition);
}
.dest-card:hover .dest-card-overlay {
  background: linear-gradient(to top, rgba(13,13,13,0.99) 0%, rgba(13,13,13,0.7) 60%, rgba(13,13,13,0.2) 100%);
}
.dest-card-content {
  position: absolute; bottom: 0; left: 0; right: 0; padding: 24px;
}
.dest-card-country {
  font-family: 'Jost', sans-serif; font-size: 10px; font-weight: 600;
  letter-spacing: 0.2em; text-transform: uppercase; color: var(--gold);
  margin-bottom: 6px; display: flex; align-items: center; gap: 6px;
}
.dest-card-name {
  font-family: 'Cormorant Garamond', serif; font-size: 1.65rem; font-weight: 500;
  color: #fff; line-height: 1.15; margin-bottom: 10px;
}
.dest-card-meta {
  display: flex; flex-direction: column; gap: 8px; margin-bottom: 14px;
}
.dest-meta-row {
  display: flex; justify-content: space-between; align-items: center;
}
.dest-meta-item {
  font-family: 'Jost', sans-serif; font-size: 11.5px; font-weight: 500;
  color: var(--white-60); display: flex; align-items: center; gap: 6px;
}
.dest-meta-item i { color: var(--gold); font-size: 12px; }
.dest-meta-price {
  font-family: 'Jost', sans-serif; font-size: 13px; font-weight: 600; color: #fff;
}
.dest-card-cta {
  display: inline-flex; align-items: center; gap: 8px;
  font-family: 'Jost', sans-serif; font-size: 11px; font-weight: 700;
  letter-spacing: 0.12em; text-transform: uppercase; color: var(--gold);
  opacity: 0; transform: translateY(8px);
  transition: all var(--transition);
}
.dest-card:hover .dest-card-cta { opacity: 1; transform: translateY(0); }
.dest-card-cta i { font-size: 10px; transition: transform var(--transition); }
.dest-card:hover .dest-card-cta i { transform: translateX(4px); }

/* ===== PACKAGE DETAIL DRAWER ===== */
.pkg-drawer-backdrop {
  position: fixed; inset: 0; background: rgba(0,0,0,0.7);
  backdrop-filter: blur(6px); -webkit-backdrop-filter: blur(6px);
  z-index: 1010; opacity: 0; pointer-events: none;
  transition: opacity 0.4s ease;
}
.pkg-drawer-backdrop.open { opacity: 1; pointer-events: all; }

.pkg-drawer {
  position: fixed; top: 0; right: 0; bottom: 0; width: min(620px, 100vw);
  background: var(--dark-2); z-index: 1020;
  transform: translateX(100%); transition: transform 0.45s cubic-bezier(0.23, 1, 0.32, 1);
  display: flex; flex-direction: column; overflow: hidden;
  border-left: 1px solid var(--white-10);
}
.pkg-drawer.open { transform: translateX(0); }

.pkg-drawer-hero {
  position: relative; height: 280px; flex-shrink: 0; overflow: hidden;
}
.pkg-drawer-hero-img {
  width: 100%; height: 100%; object-fit: cover;
  transition: transform 6s ease;
}
.pkg-drawer.open .pkg-drawer-hero-img { transform: scale(1.06); }
.pkg-drawer-hero-overlay {
  position: absolute; inset: 0;
  background: linear-gradient(to top, rgba(13,13,13,1) 0%, rgba(13,13,13,0.5) 60%, rgba(13,13,13,0.1) 100%);
}
.pkg-drawer-close {
  position: absolute; top: 18px; right: 18px;
  width: 40px; height: 40px; background: rgba(13,13,13,0.7);
  backdrop-filter: blur(8px); border: 1px solid var(--white-10);
  border-radius: 50%; display: flex; align-items: center; justify-content: center;
  cursor: pointer; color: var(--white-60); font-size: 16px;
  transition: all var(--transition); z-index: 2;
}
.pkg-drawer-close:hover { background: var(--gold); color: var(--dark); border-color: var(--gold); }
.pkg-drawer-hero-content {
  position: absolute; bottom: 0; left: 0; right: 0; padding: 22px 28px;
}
.pkg-drawer-eyebrow {
  font-family: 'Jost', sans-serif; font-size: 10px; font-weight: 600;
  letter-spacing: 0.22em; text-transform: uppercase; color: var(--gold);
  margin-bottom: 8px;
}
.pkg-drawer-title {
  font-family: 'Cormorant Garamond', serif; font-size: 2rem; font-weight: 500;
  color: #fff; line-height: 1.15;
}

.pkg-drawer-body {
  flex: 1; overflow-y: auto; padding: 28px;
  scrollbar-width: thin; scrollbar-color: var(--gold-dim) transparent;
}
.pkg-drawer-body::-webkit-scrollbar { width: 4px; }
.pkg-drawer-body::-webkit-scrollbar-thumb { background: var(--gold-dim); border-radius: 4px; }

.pkg-drawer-pills { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 24px; }
.pkg-drawer-pill {
  font-family: 'Jost', sans-serif; font-size: 11.5px; font-weight: 500;
  color: var(--white-80); background: var(--white-10); border: 1px solid rgba(255,255,255,0.12);
  padding: 6px 14px; border-radius: 100px;
  display: inline-flex; align-items: center; gap: 7px;
}
.pkg-drawer-pill i { color: var(--gold); font-size: 10px; }
.pkg-drawer-pill.gold { background: var(--gold-dim); border-color: var(--gold-dim); color: var(--gold); font-weight: 700; }

.pkg-drawer-desc {
  font-family: 'Jost', sans-serif; font-size: 15.5px; color: var(--white-60);
  line-height: 1.85; font-weight: 400; margin-bottom: 28px;
}

.pkg-drawer-section-title {
  font-family: 'Jost', sans-serif; font-size: 10px; font-weight: 700;
  letter-spacing: 0.2em; text-transform: uppercase; color: var(--gold);
  margin-bottom: 16px;
}
.pkg-inc-list { list-style: none; display: flex; flex-direction: column; gap: 10px; margin-bottom: 28px; }
.pkg-inc-list li {
  display: flex; align-items: flex-start; gap: 10px;
  font-family: 'Jost', sans-serif; font-size: 14.5px; color: var(--white-60); line-height: 1.5;
}
.pkg-inc-list li i { color: var(--green); font-size: 13px; margin-top: 2px; flex-shrink: 0; }

.pkg-price-row {
  display: flex; align-items: center; justify-content: space-between;
  padding: 20px 0; border-top: 1px solid var(--white-10);
  border-bottom: 1px solid var(--white-10); margin-bottom: 20px;
}
.pkg-price-from {
  font-family: 'Jost', sans-serif; font-size: 11px; font-weight: 500;
  letter-spacing: 0.08em; text-transform: uppercase; color: var(--white-60); margin-bottom: 4px;
}
.pkg-price-val {
  font-family: 'Jost', sans-serif; font-size: 2.2rem; font-weight: 200; color: #fff;
}
.pkg-price-val .curr { font-size: 1.3rem; color: var(--gold); vertical-align: middle; margin-right: 2px; }
.pkg-price-pp { font-family: 'Jost', sans-serif; font-size: 10.5px; color: var(--white-60); }
.pkg-duration-badge {
  text-align: right;
  font-family: 'Jost', sans-serif; font-size: 13px; color: var(--white-60);
}
.pkg-duration-badge strong { display: block; font-size: 1.1rem; color: #fff; font-weight: 500; }

.pkg-drawer-actions { display: flex; flex-direction: column; gap: 10px; }
.pkg-drawer-btn {
  display: block; width: 100%; text-align: center;
  background: var(--gold); color: var(--dark);
  font-family: 'Jost', sans-serif; font-size: 12px; font-weight: 700;
  letter-spacing: 0.15em; text-transform: uppercase; padding: 17px 24px;
  border-radius: 100px; text-decoration: none; transition: all var(--transition); border: none; cursor: pointer;
}
.pkg-drawer-btn:hover { background: var(--gold-light); transform: translateY(-2px); box-shadow: 0 8px 24px rgba(201,168,76,0.3); }
.pkg-drawer-btn-outline {
  display: block; width: 100%; text-align: center;
  background: transparent; border: 1px solid var(--white-30); color: #fff;
  font-family: 'Jost', sans-serif; font-size: 12px; font-weight: 600;
  letter-spacing: 0.15em; text-transform: uppercase; padding: 17px 24px;
  border-radius: 100px; text-decoration: none; transition: all var(--transition);
}
.pkg-drawer-btn-outline:hover { border-color: var(--gold); color: var(--gold); }

/* ===== RESPONSIVE ===== */
@media (max-width: 1024px) {
  .dest-grid { grid-template-columns: repeat(2, 1fr); }
  .pkg-section { padding: 60px 24px 0; }
  .pkg-trust { padding: 18px 24px; }
}
@media (max-width: 640px) {
  .dest-grid { grid-template-columns: 1fr; }
  .pkg-section { padding: 48px 20px 0; }
  .cat-tabs-wrap { width: calc(100% - 40px); }
  .cat-tab { padding: 12px 20px; font-size: 11px; flex: 1; justify-content: center; }
  .pkg-trust-inner { gap: 18px; }
  .pkg-drawer { width: 100vw; }
  .pkg-drawer-hero { height: 220px; }
  .pkg-drawer-title { font-size: 1.6rem; }
}
</style>
@endpush

@section('content')

<!-- HERO -->
<x-hero-carousel 
  :slides="[
    'https://images.unsplash.com/photo-1540202404-b71180fb78d1?auto=format&fit=crop&w=1800&q=80',
    'https://images.unsplash.com/photo-1571003123894-1f0594d2b5d9?auto=format&fit=crop&w=1800&q=80'
  ]"
  eyebrow="Exclusive Travel Packages"
  title="Curated Journeys<br><em>Unforgettable Memories</em>"
  subtitle="Discover hand-picked itineraries designed for the ultimate luxury experience"
  ctaText="Explore Packages"
  ctaLink="#packages-list"
/>

<!-- TRUST BAR -->
<div class="pkg-trust">
  <div class="pkg-trust-inner">
    <div class="pkg-trust-item"><i class="fa-solid fa-check-circle"></i> Curated Experiences</div>
    <div class="pkg-trust-item"><i class="fa-solid fa-star"></i> Premium Support</div>
    <div class="pkg-trust-item"><i class="fa-solid fa-shield-halved"></i> Seamless Booking</div>
    <div class="pkg-trust-item"><i class="fa-solid fa-globe"></i> 50+ Destinations</div>
  </div>
</div>

<!-- PACKAGES SECTION -->
<div class="pkg-page" id="packages-list">
  <section class="pkg-section">
    <div class="pkg-section-inner">

      <div class="pkg-header">
        <div class="pkg-eyebrow">Our Collection</div>
        <h1 class="pkg-title">Discover <em>Your Next</em> Adventure</h1>
        <p class="pkg-desc">Choose your journey — explore iconic international destinations or uncover the hidden gems of incredible India.</p>
      </div>

      <!-- CATEGORY TABS -->
      <div class="cat-tabs-wrap" role="tablist" aria-label="Package Categories">
        <button class="cat-tab active" id="tab-domestic" role="tab" aria-selected="true" aria-controls="panel-domestic" onclick="switchTab('domestic')">
          <i class="fa-solid fa-map-location-dot"></i> Domestic
        </button>
        <button class="cat-tab" id="tab-international" role="tab" aria-selected="false" aria-controls="panel-international" onclick="switchTab('international')">
          <i class="fa-solid fa-earth-americas"></i> International
        </button>
      </div>

      <!-- DOMESTIC PANEL -->
      <div class="cat-panel active" id="panel-domestic" role="tabpanel" aria-labelledby="tab-domestic">
        
        @php
          $domesticStates = $packages->where('region_type', 'domestic')->pluck('destination.name')->unique();
        @endphp
        @if($domesticStates->count() > 0)
        <div class="state-filter-wrap">
          <button class="state-tab active" data-state="all" onclick="filterState('domestic', 'all')">All Destinations</button>
          @foreach($domesticStates as $state)
            <button class="state-tab" data-state="{{ Str::slug($state) }}" onclick="filterState('domestic', '{{ Str::slug($state) }}')">{{ $state }}</button>
          @endforeach
        </div>
        @endif

        <div id="grid-domestic" data-active-state="all">
          @foreach(['group' => 'Group Packages', 'custom' => 'Custom Packages', 'all' => 'All Packages'] as $type => $title)
            @php
              $typePackages = $type === 'all' 
                ? $packages->where('region_type', 'domestic') 
                : $packages->where('region_type', 'domestic')->where('tour_type', $type);
            @endphp
            @if($typePackages->isNotEmpty())
              <h3 class="pkg-title" style="font-size: 1.8rem; margin: 40px 0 20px; text-align: left;">{{ $title }}</h3>
              <div class="dest-grid type-section-grid" data-tour-type="{{ $type }}">
                @foreach($typePackages as $pkg)
                <div class="dest-card" data-state="{{ Str::slug($pkg->destination->name) }}" onclick="openDrawer({{ $pkg->id }})" tabindex="0" role="button"
                  aria-label="View {{ $pkg->title }} package details"
                  onkeydown="if(event.key==='Enter'||event.key===' ') openDrawer({{ $pkg->id }})">
                  <div class="dest-card-img" style="background-image: url('{{ $pkg->hero_image_url }}')"></div>
                  <div class="pkg-tour-type-pill">{{ ucfirst($pkg->tour_type) }}</div>
                  <div class="dest-card-overlay"></div>
                  <div class="dest-card-content">
                    <div class="dest-card-country">
                      <i class="fa-solid fa-location-dot"></i> {{ $pkg->destination->name }}
                    </div>
                    <div class="dest-card-name">{{ $pkg->title }}</div>
                    <div class="dest-card-meta">
                      <div class="dest-meta-row">
                        <span class="dest-meta-item"><i class="fa-regular fa-clock"></i> {{ $pkg->duration_nights }}N/{{ $pkg->duration_nights + 1 }}D</span>
                        <span class="dest-meta-item"><i class="fa-solid fa-location-dot"></i> {{ !empty($pkg->departure_from) ? (is_array($pkg->departure_from) ? implode(', ', $pkg->departure_from) : $pkg->departure_from) : 'Delhi' }} - {{ $pkg->destination->name }}</span>
                      </div>
                      <div class="dest-meta-row">
                        @if($pkg->tour_type == 'custom')
                          <span class="dest-meta-item"><i class="fa-regular fa-calendar"></i> Any date of your choice</span>
                        @else
                          @php
                             $firstDate = $pkg->departures ? ($pkg->departures->where('start_date', '>=', now()->format('Y-m-d'))->sortBy('start_date')->first() ?? $pkg->departures->first()) : null;
                          @endphp
                          @if($firstDate)
                             <span class="dest-meta-item"><i class="fa-regular fa-calendar"></i> {{ \Carbon\Carbon::parse($firstDate->start_date)->format('d M') }} – {{ \Carbon\Carbon::parse($firstDate->end_date)->format('d M Y') }}</span>
                          @else
                             <span class="dest-meta-item"><i class="fa-regular fa-calendar"></i> Specific Dates</span>
                          @endif
                        @endif
                      </div>
                      <div class="dest-meta-row" style="margin-top: 4px;">
                        <span class="dest-meta-price" style="color:var(--gold);">From ₹{{ number_format($pkg->price_from) }}</span>
                      </div>
                    </div>
                    <div class="dest-card-cta">Explore Package <i class="fa-solid fa-arrow-right"></i></div>
                  </div>
                </div>
                @endforeach
              </div>
            @endif
          @endforeach

          @if($packages->where('region_type', 'domestic')->isEmpty())
          <p style="color:var(--white-60); font-family:'Jost',sans-serif; grid-column:1/-1; text-align:center; padding:48px 0;">
            Domestic packages coming soon. Stay tuned!
          </p>
          @endif
        </div>
      </div>

      <!-- INTERNATIONAL PANEL -->
      <div class="cat-panel" id="panel-international" role="tabpanel" aria-labelledby="tab-international">
        
        @php
          $intlStates = $packages->where('region_type', 'international')->pluck('destination.name')->unique();
        @endphp
        @if($intlStates->count() > 0)
        <div class="state-filter-wrap">
          <button class="state-tab active" data-state="all" onclick="filterState('international', 'all')">All Destinations</button>
          @foreach($intlStates as $state)
            <button class="state-tab" data-state="{{ Str::slug($state) }}" onclick="filterState('international', '{{ Str::slug($state) }}')">{{ $state }}</button>
          @endforeach
        </div>
        @endif

        <div id="grid-international" data-active-state="all">
          @foreach(['group' => 'Group Packages', 'custom' => 'Custom Packages', 'all' => 'All Packages'] as $type => $title)
            @php
              $typePackages = $type === 'all' 
                ? $packages->where('region_type', 'international') 
                : $packages->where('region_type', 'international')->where('tour_type', $type);
            @endphp
            @if($typePackages->isNotEmpty())
              <h3 class="pkg-title" style="font-size: 1.8rem; margin: 40px 0 20px; text-align: left;">{{ $title }}</h3>
              <div class="dest-grid type-section-grid" data-tour-type="{{ $type }}">
                @foreach($typePackages as $pkg)
                <div class="dest-card" data-state="{{ Str::slug($pkg->destination->name) }}" onclick="openDrawer({{ $pkg->id }})" tabindex="0" role="button"
                  aria-label="View {{ $pkg->title }} package details"
                  onkeydown="if(event.key==='Enter'||event.key===' ') openDrawer({{ $pkg->id }})">
                  <div class="dest-card-img" style="background-image: url('{{ $pkg->hero_image_url }}')"></div>
                  <div class="pkg-tour-type-pill">{{ ucfirst($pkg->tour_type) }}</div>
                  <div class="dest-card-overlay"></div>
                  <div class="dest-card-content">
                    <div class="dest-card-country">
                      <i class="fa-solid fa-location-dot"></i> {{ $pkg->destination->name }}
                    </div>
                    <div class="dest-card-name">{{ $pkg->title }}</div>
                    <div class="dest-card-meta">
                      <div class="dest-meta-row">
                        <span class="dest-meta-item"><i class="fa-regular fa-clock"></i> {{ $pkg->duration_nights }}N/{{ $pkg->duration_nights + 1 }}D</span>
                        <span class="dest-meta-item"><i class="fa-solid fa-location-dot"></i> {{ !empty($pkg->departure_from) ? (is_array($pkg->departure_from) ? implode(', ', $pkg->departure_from) : $pkg->departure_from) : 'Delhi' }} - {{ $pkg->destination->name }}</span>
                      </div>
                      <div class="dest-meta-row">
                        @if($pkg->tour_type == 'custom')
                          <span class="dest-meta-item"><i class="fa-regular fa-calendar"></i> Any date of your choice</span>
                        @else
                          @php
                             $firstDate = $pkg->departures ? ($pkg->departures->where('start_date', '>=', now()->format('Y-m-d'))->sortBy('start_date')->first() ?? $pkg->departures->first()) : null;
                          @endphp
                          @if($firstDate)
                             <span class="dest-meta-item"><i class="fa-regular fa-calendar"></i> {{ \Carbon\Carbon::parse($firstDate->start_date)->format('d M') }} – {{ \Carbon\Carbon::parse($firstDate->end_date)->format('d M Y') }}</span>
                          @else
                             <span class="dest-meta-item"><i class="fa-regular fa-calendar"></i> Specific Dates</span>
                          @endif
                        @endif
                      </div>
                      <div class="dest-meta-row" style="margin-top: 4px;">
                        <span class="dest-meta-price" style="color:var(--gold);">From ₹{{ number_format($pkg->price_from) }}</span>
                      </div>
                    </div>
                    <div class="dest-card-cta">Explore Package <i class="fa-solid fa-arrow-right"></i></div>
                  </div>
                </div>
                @endforeach
              </div>
            @endif
          @endforeach

          @if($packages->where('region_type', 'international')->isEmpty())
          <p style="color:var(--white-60); font-family:'Jost',sans-serif; grid-column:1/-1; text-align:center; padding:48px 0;">
            International packages coming soon. Stay tuned!
          </p>
          @endif
        </div>
      </div>

    </div>
  </section>
</div>

<!-- ===== PACKAGE DETAIL DRAWER ===== -->
<div class="pkg-drawer-backdrop" id="drawerBackdrop" onclick="closeDrawer()"></div>
<aside class="pkg-drawer" id="pkgDrawer" role="dialog" aria-modal="true" aria-labelledby="drawerTitle">

  <!-- Hero Image Area -->
  <div class="pkg-drawer-hero">
    <img class="pkg-drawer-hero-img" id="drawerImg" src="" alt="">
    <div class="pkg-drawer-hero-overlay"></div>
    <button class="pkg-drawer-close" onclick="closeDrawer()" aria-label="Close package details">
      <i class="fa-solid fa-xmark"></i>
    </button>
    <div class="pkg-drawer-hero-content">
      <div class="pkg-drawer-eyebrow" id="drawerEyebrow"></div>
      <div class="pkg-drawer-title" id="drawerTitle"></div>
    </div>
  </div>

  <!-- Scrollable Body -->
  <div class="pkg-drawer-body">
    <!-- Pills -->
    <div class="pkg-drawer-pills" id="drawerPills"></div>

    <!-- Description -->
    <p class="pkg-drawer-desc" id="drawerDesc"></p>

    <!-- Inclusions -->
    <div class="pkg-drawer-section-title">What's Included</div>
    <ul class="pkg-inc-list" id="drawerInclusions"></ul>

    <!-- Price & Duration -->
    <div class="pkg-price-row">
      <div>
        <div class="pkg-price-from">Starting From</div>
        <div class="pkg-price-val"><span class="curr">₹</span><span id="drawerPrice"></span></div>
        <div class="pkg-price-pp">per person</div>
      </div>
      <div class="pkg-duration-badge">
        <div style="font-size:10px;letter-spacing:.1em;text-transform:uppercase;color:var(--white-60);margin-bottom:4px;">Duration</div>
        <strong id="drawerDuration"></strong>
      </div>
    </div>

    <!-- Actions -->
    <div class="pkg-drawer-actions">
      <a id="drawerViewBtn" href="#" class="pkg-drawer-btn">
        <i class="fa-solid fa-compass" style="margin-right:8px;"></i> View Full Itinerary
      </a>
      <a id="drawerWhatsApp" href="#" target="_blank" class="pkg-drawer-btn-outline">
        <i class="fa-brands fa-whatsapp" style="margin-right:8px;"></i> WhatsApp Us
      </a>
    </div>
  </div>
</aside>

<script>
// ─── Package Data ────────────────────────────────────────────────────────────
@php
$pkgJsonData = [];
foreach($packages as $p) {
    $pkgJsonData[] = [
        'id'        => $p->id,
        'category'  => $p->category,
        'title'     => $p->title,
        'nights'    => $p->duration_nights,
        'price'     => $p->price_from,
        'short_desc'=> $p->short_desc ?? '',
        'country'   => $p->destination->name,
        'image'     => $p->hero_image_url,
        'inclusions'=> $p->inclusions->map(fn($i) => $i->label ?? $i->name ?? $i->title)->filter()->values()->toArray(),
        'detailUrl' => route('package.details', ['id' => $p->id]),
    ];
}
@endphp
const packages = @json($pkgJsonData);

// ─── Tab Switching ────────────────────────────────────────────────────────────
function switchTab(cat) {
  document.querySelectorAll('.cat-tab').forEach(t => {
    const active = t.id === 'tab-' + cat;
    t.classList.toggle('active', active);
    t.setAttribute('aria-selected', active);
  });
  document.querySelectorAll('.cat-panel').forEach(p => {
    p.classList.toggle('active', p.id === 'panel-' + cat);
  });
}

function filterState(category, stateSlug) {
  const panel = document.getElementById('panel-' + category);
  if (!panel) return;
  const gridWrap = document.getElementById('grid-' + category);

  // Update tabs
  panel.querySelectorAll('.state-tab').forEach(tab => {
    tab.classList.toggle('active', tab.dataset.state === stateSlug);
  });
  
  // Update state
  gridWrap.dataset.activeState = stateSlug;
  applyFilters(category);
}

function applyFilters(category) {
  const gridWrap = document.getElementById('grid-' + category);
  if (!gridWrap) return;
  
  const activeState = gridWrap.dataset.activeState || 'all';
  
  // Filter cards
  gridWrap.querySelectorAll('.dest-card').forEach(card => {
    const matchState = (activeState === 'all' || card.dataset.state === activeState);
    card.style.display = matchState ? '' : 'none';
  });

  // Hide empty sections
  gridWrap.querySelectorAll('.type-section-grid').forEach(grid => {
    const visibleCards = Array.from(grid.querySelectorAll('.dest-card')).filter(card => card.style.display !== 'none');
    const heading = grid.previousElementSibling;
    if (visibleCards.length === 0) {
      grid.style.display = 'none';
      if (heading) heading.style.display = 'none';
    } else {
      grid.style.display = 'grid'; // Restore grid layout
      if (heading) heading.style.display = '';
    }
  });
}

// ─── Drawer Logic ────────────────────────────────────────────────────────────
function openDrawer(id) {
  const pkg = packages.find(p => p.id == id);
  if (!pkg) return;

  // Populate fields
  document.getElementById('drawerImg').src       = pkg.image;
  document.getElementById('drawerImg').alt       = pkg.title;
  document.getElementById('drawerEyebrow').textContent = pkg.country;
  document.getElementById('drawerTitle').textContent   = pkg.title;
  document.getElementById('drawerDesc').textContent    = pkg.short_desc;
  document.getElementById('drawerPrice').textContent   = Number(pkg.price).toLocaleString('en-IN');
  document.getElementById('drawerDuration').textContent = pkg.nights + 'N ' + (pkg.nights + 1) + 'D';

  // Pills
  const pillsEl = document.getElementById('drawerPills');
  pillsEl.innerHTML = `
    <span class="pkg-drawer-pill"><i class="fa-regular fa-moon"></i> ${pkg.nights} Nights</span>
    <span class="pkg-drawer-pill"><i class="fa-solid fa-sun"></i> ${pkg.nights + 1} Days</span>
    <span class="pkg-drawer-pill"><i class="fa-solid fa-location-dot"></i> ${pkg.country}</span>
    <span class="pkg-drawer-pill gold"><i class="fa-solid fa-indian-rupee-sign"></i> From ₹${Number(pkg.price).toLocaleString('en-IN')}</span>
  `;

  // Inclusions
  const incEl = document.getElementById('drawerInclusions');
  incEl.innerHTML = pkg.inclusions.map(inc =>
    `<li><i class="fa-solid fa-circle-check"></i> ${inc}</li>`
  ).join('');

  // Buttons
  document.getElementById('drawerViewBtn').href  = pkg.detailUrl;
  document.getElementById('drawerWhatsApp').href = `https://wa.me/919875073788?text=Hi!%20I'm%20interested%20in%20the%20${encodeURIComponent(pkg.title)}%20package.%20Please%20share%20details.`;

  // Open
  document.getElementById('drawerBackdrop').classList.add('open');
  document.getElementById('pkgDrawer').classList.add('open');
  document.body.style.overflow = 'hidden';
}

function closeDrawer() {
  document.getElementById('drawerBackdrop').classList.remove('open');
  document.getElementById('pkgDrawer').classList.remove('open');
  document.body.style.overflow = '';
}

// Close on Escape
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeDrawer(); });

// ─── Auto-activate tab from navbar dropdown or URL param ──────────────────────
(function() {
  // 1. Check sessionStorage (set by navbar dropdown click)
  let tab = sessionStorage.getItem('pkgActiveTab');
  if (tab) {
    sessionStorage.removeItem('pkgActiveTab');
  } else {
    // 2. Fall back to URL ?tab= param
    const urlParams = new URLSearchParams(window.location.search);
    tab = urlParams.get('tab');
  }
  if (tab === 'domestic' || tab === 'international') {
    switchTab(tab);
  }
})();

// ─── Drag to Scroll for State Filters ─────────────────────────────────────────
document.querySelectorAll('.state-filter-wrap').forEach(slider => {
  let isDown = false;
  let startX;
  let scrollLeft;

  slider.addEventListener('mousedown', (e) => {
    isDown = true;
    slider.style.cursor = 'grabbing';
    startX = e.pageX - slider.offsetLeft;
    scrollLeft = slider.scrollLeft;
  });
  slider.addEventListener('mouseleave', () => {
    isDown = false;
    slider.style.cursor = '';
  });
  slider.addEventListener('mouseup', () => {
    isDown = false;
    slider.style.cursor = '';
  });
  slider.addEventListener('mousemove', (e) => {
    if (!isDown) return;
    e.preventDefault();
    const x = e.pageX - slider.offsetLeft;
    const walk = (x - startX) * 2; // scroll speed multiplier
    slider.scrollLeft = scrollLeft - walk;
  });
});
</script>

@endsection
