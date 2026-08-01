@extends('layouts.frontend')

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com" />
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;1,400;1,600&family=Jost:wght@300;400;500;600&display=swap" rel="stylesheet" />

<style>
/* ===== RESET & VARIABLES ===== */
:root {
  --gold: #c9a84c;
  --gold-light: #e8c96b;
  --gold-dim: rgba(201,168,76,0.15);
  --dark: #0d0d0d;
  --dark-2: #141414;
  --dark-3: #1a1a1a;
  --white: #ffffff;
  --white-60: rgba(255,255,255,0.6);
  --white-30: rgba(255,255,255,0.3);
  --white-10: rgba(255,255,255,0.08);
  --radius: 12px;
  --transition: 0.35s cubic-bezier(0.25, 0.46, 0.45, 0.94);
}

/* ===== TRUST BAR ===== */
.pkg-trust {
  background: var(--dark-2);
  border-top: 1px solid var(--gold-dim); border-bottom: 1px solid var(--gold-dim);
  padding: 20px 40px;
}
.pkg-trust-inner {
  max-width: 1200px; margin: 0 auto;
  display: flex; justify-content: center; gap: 48px; flex-wrap: wrap;
}
.pkg-trust-item {
  display: flex; align-items: center; gap: 10px;
  font-family: 'Jost', sans-serif; font-size: 12.5px; font-weight: 500;
  letter-spacing: 0.06em; color: var(--white-60); text-transform: uppercase;
}
.pkg-trust-item .ti { color: var(--gold); }

/* ===== SECTION ===== */
.pkg-section { padding: 96px 40px; background: var(--dark); }
.pkg-section-inner { max-width: 1200px; margin: 0 auto; }
.pkg-section-header { text-align: center; margin-bottom: 56px; }

.pkg-eyebrow {
  font-family: 'Jost', sans-serif; font-size: 10.5px; font-weight: 600;
  letter-spacing: 0.22em; text-transform: uppercase; color: var(--gold); margin-bottom: 14px;
}
.pkg-title {
  font-family: 'Cormorant Garamond', serif;
  font-size: clamp(2.4rem, 4.5vw, 3.6rem); font-weight: 500;
  line-height: 1.1; color: #fff; margin-bottom: 16px;
}
.pkg-title em { font-style: italic; color: var(--gold-light); }
.pkg-desc {
  font-family: 'Jost', sans-serif; font-size: 14.5px; color: var(--white-60);
  max-width: 520px; margin: 0 auto; font-weight: 300;
}

.pkg-divider {
  display: flex; align-items: center; justify-content: center;
  gap: 16px; margin: 0 auto 48px; max-width: 300px;
}
.pkg-divider::before, .pkg-divider::after {
  content: ''; flex: 1; height: 1px; background: var(--gold-dim);
}
.pkg-divider span { color: var(--gold); font-size: 16px; }

/* ===== FILTER TABS ===== */
.pkg-search-wrap {
  max-width: 720px;
  margin: 0 auto 28px;
  display: grid;
  grid-template-columns: 1fr auto;
  gap: 10px;
  align-items: center;
  background: rgba(255,255,255,0.06);
  border: 1px solid rgba(255,255,255,0.12);
  border-radius: 100px;
  padding: 8px 8px 8px 22px;
}
.pkg-search-wrap label {
  position: absolute;
  width: 1px;
  height: 1px;
  overflow: hidden;
}
.pkg-search-wrap input {
  width: 100%;
  min-height: 42px;
  border: none;
  outline: none;
  background: transparent;
  color: #fff;
  font-family: 'Jost', sans-serif;
  font-size: 14px;
}
.pkg-search-wrap input::placeholder { color: var(--white-60); }
.pkg-search-pill {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-height: 42px;
  padding: 0 18px;
  border-radius: 100px;
  background: var(--gold);
  color: var(--dark);
  font-family: 'Jost', sans-serif;
  font-size: 12px;
  font-weight: 700;
  letter-spacing: .08em;
  text-transform: uppercase;
}

/* ===== PACKAGE GRID ===== */
.pkg-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 28px; }

/* ===== PACKAGE CARD ===== */
.pkg-card {
  background: var(--dark-3); border-radius: var(--radius); overflow: hidden;
  border: 1px solid rgba(255,255,255,0.06); transition: all var(--transition);
  display: flex; flex-direction: column;
  color: inherit; cursor: pointer; text-decoration: none;
}
.pkg-card:hover {
  transform: translateY(-6px);
  border-color: rgba(201,168,76,0.35);
  box-shadow: 0 20px 48px rgba(0,0,0,0.5), 0 0 0 1px rgba(201,168,76,0.1);
}

.pkg-card-img { position: relative; height: 220px; overflow: hidden; }
.pkg-card-img img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.6s ease; }
.pkg-card:hover .pkg-card-img img { transform: scale(1.07); }

.pkg-badge {
  position: absolute; top: 14px; left: 14px; background: var(--gold); color: var(--dark);
  font-family: 'Jost', sans-serif; font-size: 9.5px; font-weight: 700;
  letter-spacing: 0.1em; text-transform: uppercase; padding: 6px 12px; border-radius: 100px;
}
.pkg-nights {
  position: absolute; bottom: 14px; right: 14px; background: rgba(0,0,0,0.65);
  backdrop-filter: blur(4px); -webkit-backdrop-filter: blur(4px);
  color: #fff; font-family: 'Jost', sans-serif; font-size: 11px; font-weight: 500;
  padding: 6px 12px; border-radius: 100px; border: 1px solid rgba(255,255,255,0.15);
  display: flex; align-items: center; gap: 6px;
}
.pkg-nights i { color: var(--gold); font-size: 10px; }

.pkg-card-body { padding: 24px; display: flex; flex-direction: column; flex: 1; }

.pkg-location {
  display: flex; align-items: center; gap: 6px; color: var(--gold);
  font-family: 'Jost', sans-serif; font-size: 11px; font-weight: 500;
  letter-spacing: 0.08em; text-transform: uppercase; margin-bottom: 8px;
}
.pkg-name {
  font-family: 'Cormorant Garamond', serif; font-size: 24px; font-weight: 500;
  color: #fff; line-height: 1.2; margin-bottom: 12px;
}

.pkg-inclusions {
  display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 20px;
}
.pkg-inclusion-badge {
  background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1);
  color: var(--white-60); font-family: 'Jost', sans-serif; font-size: 11px;
  padding: 4px 10px; border-radius: 100px; display: flex; align-items: center; gap: 4px;
}
.pkg-inclusion-badge i { color: var(--gold); }

.pkg-footer {
  margin-top: auto; padding-top: 20px; border-top: 1px solid rgba(255,255,255,0.06);
  display: flex; justify-content: space-between; align-items: flex-end;
}
.pkg-price-lbl {
  font-family: 'Jost', sans-serif; font-size: 11px; color: var(--white-60);
  text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 2px;
}
.pkg-price-val {
  font-family: 'Jost', sans-serif; font-size: 20px; font-weight: 500; color: #fff;
}
.pkg-price-curr { font-size: 14px; color: var(--gold); }

.pkg-btn-sm {
  background: transparent; border: 1px solid var(--white-30); color: #fff;
  font-family: 'Jost', sans-serif; font-size: 11px; font-weight: 600;
  letter-spacing: 0.1em; text-transform: uppercase; padding: 10px 20px; border-radius: 100px;
  transition: all var(--transition);
}
.pkg-card:hover .pkg-btn-sm {
  background: var(--gold); border-color: var(--gold); color: var(--dark);
}

@media (max-width: 1024px) {
  .pkg-grid { grid-template-columns: repeat(2, 1fr); gap: 24px; }
  .pkg-section { padding: 80px 24px; }
}
@media (max-width: 640px) {
  .pkg-grid { grid-template-columns: 1fr; }
  .pkg-section { padding: 60px 20px; }
  .pkg-trust-inner { gap: 20px; }
  .pkg-search-wrap { grid-template-columns: 1fr; border:none; background:none; padding:0; gap:12px; }
  .pkg-search-wrap input { background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.12); border-radius: 100px; padding: 0 20px; }
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
    <div class="pkg-trust-item"><i class="fa-solid fa-check-circle ti"></i> Curated Experiences</div>
    <div class="pkg-trust-item"><i class="fa-solid fa-star ti"></i> Premium Support</div>
    <div class="pkg-trust-item"><i class="fa-solid fa-shield-halved ti"></i> Seamless Booking</div>
  </div>
</div>

<!-- PACKAGES SECTION -->
<section id="packages-list" class="pkg-section">
  <div class="pkg-section-inner">
    
    <div class="pkg-section-header">
      <div class="pkg-eyebrow">Our Collection</div>
      <h2 class="pkg-title">Hand-picked <em>Destinations</em></h2>
      <div class="pkg-divider">
        <i class="fa-solid fa-compass"></i>
      </div>
      <p class="pkg-desc">Browse our exclusive selection of luxury travel packages, meticulously curated to provide unparalleled experiences in the world's most stunning locations.</p>
    </div>

    <!-- Search -->
    <div class="pkg-search-wrap">
      <label for="pkg-search">Search Packages</label>
      <input type="text" id="pkg-search" placeholder="Search by destination, package name...">
      <button class="pkg-search-pill"><i class="fa-solid fa-search" style="margin-right:8px;"></i> Search</button>
    </div>

    <!-- Grid -->
    <div class="pkg-grid">
      @forelse($packages as $package)
        <a href="{{ route('package.details', ['id' => $package->id ?? 1]) }}" class="pkg-card">
          <div class="pkg-card-img">
            @if($package->images && $package->images->count() > 0)
              @if(Str::startsWith($package->images->first()->image_path, 'http'))
                <img src="{{ $package->images->first()->image_path }}" alt="{{ $package->title }}" loading="lazy">
              @else
                <img src="{{ asset('storage/' . $package->images->first()->image_path) }}" alt="{{ $package->title }}" loading="lazy">
              @endif
            @else
              <img src="https://images.unsplash.com/photo-1540202404-b71180fb78d1?w=700&q=80" alt="{{ $package->title }}" loading="lazy">
            @endif
            <div class="pkg-badge">Featured</div>
            <div class="pkg-nights">
              <i class="fa-solid fa-moon"></i> {{ $package->duration_nights }} Nights
            </div>
          </div>
          
          <div class="pkg-card-body">
            @if($package->destination)
            <div class="pkg-location">
              <i class="fa-solid fa-location-dot"></i> {{ $package->destination->name }}
            </div>
            @endif
            
            <h3 class="pkg-name">{{ $package->title }}</h3>
            
            @if($package->inclusions && $package->inclusions->count() > 0)
            <div class="pkg-inclusions">
              @foreach($package->inclusions->take(3) as $inclusion)
                <div class="pkg-inclusion-badge">
                  <i class="fa-solid fa-check"></i> {{ $inclusion->name ?? $inclusion->title ?? 'Included' }}
                </div>
              @endforeach
            </div>
            @endif
            
            <div class="pkg-footer">
              <div class="pkg-price">
                <div class="pkg-price-lbl">Starting From</div>
                <div class="pkg-price-val"><span class="pkg-price-curr">₹</span>{{ number_format($package->price_from, 0) }}</div>
              </div>
              <div class="pkg-btn-sm">View Details</div>
            </div>
          </div>
        </a>
      @empty
        <p style="color:#fff; text-align:center; grid-column:1/-1;">No packages available at the moment. Please check back soon.</p>
      @endforelse
    </div>
    
  </div>
</section>

@endsection
