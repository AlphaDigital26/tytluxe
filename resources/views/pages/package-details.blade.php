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

.pd-hero {
  position: relative;
  height: 60vh;
  min-height: 400px;
  background-size: cover;
  background-position: center;
  display: flex;
  align-items: center;
  justify-content: center;
}
.pd-hero::before {
  content: '';
  position: absolute;
  inset: 0;
  background: linear-gradient(to top, rgba(13,13,13,1) 0%, rgba(13,13,13,0.3) 100%);
}
.pd-hero-content {
  position: relative;
  z-index: 2;
  text-align: center;
  max-width: 800px;
  padding: 0 24px;
}
.pd-eyebrow {
  font-family: 'Jost', sans-serif; font-size: 11px; font-weight: 600;
  letter-spacing: 0.22em; text-transform: uppercase; color: var(--gold); margin-bottom: 16px;
  display: inline-block;
}
.pd-title {
  font-family: 'Cormorant Garamond', serif;
  font-size: clamp(2.8rem, 5vw, 4.5rem); font-weight: 500;
  line-height: 1.1; color: #fff; margin-bottom: 24px;
}
.pd-meta {
  display: flex; align-items: center; justify-content: center; gap: 24px;
  font-family: 'Jost', sans-serif; font-size: 14px; color: var(--white-60);
  text-transform: uppercase; letter-spacing: 0.1em;
}
.pd-meta i { color: var(--gold); }

.pd-section { padding: 80px 40px; background: var(--dark); }
.pd-section-inner { max-width: 1100px; margin: 0 auto; display: grid; grid-template-columns: 2fr 1fr; gap: 60px; }

.pd-main { color: var(--white-60); font-family: 'Jost', sans-serif; font-size: 16px; line-height: 1.8; font-weight: 300; }
.pd-main h2 {
  font-family: 'Cormorant Garamond', serif; font-size: 32px; font-weight: 500;
  color: #fff; margin-bottom: 24px; line-height: 1.2; border-bottom: 1px solid var(--white-10); padding-bottom: 16px;
}
.pd-desc { margin-bottom: 40px; }

.pd-inclusions { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 40px; }
.pd-inc-item { display: flex; align-items: center; gap: 12px; }
.pd-inc-item i { color: var(--gold); font-size: 18px; }

.pd-itinerary { margin-top: 40px; }
.pd-day { margin-bottom: 32px; position: relative; padding-left: 32px; }
.pd-day::before {
  content: ''; position: absolute; left: 0; top: 8px; bottom: -40px; width: 1px; background: var(--white-10);
}
.pd-day:last-child::before { display: none; }
.pd-day-num {
  position: absolute; left: -12px; top: 0; width: 25px; height: 25px; background: var(--gold); color: var(--dark);
  border-radius: 50%; display: flex; align-items: center; justify-content: center;
  font-size: 10px; font-weight: 700; font-family: 'Jost', sans-serif;
}
.pd-day-title { font-size: 18px; font-weight: 500; color: #fff; margin-bottom: 8px; }

.pd-sidebar { position: sticky; top: 120px; }
.pd-box {
  background: var(--dark-2); border: 1px solid var(--white-10); border-radius: var(--radius);
  padding: 32px;
}
.pd-price-lbl { font-family: 'Jost', sans-serif; font-size: 12px; color: var(--white-60); text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 8px; }
.pd-price-val { font-family: 'Jost', sans-serif; font-size: 36px; font-weight: 500; color: #fff; margin-bottom: 24px; }
.pd-price-val span { font-size: 20px; color: var(--gold); margin-right: 4px; }

.pd-btn {
  display: block; width: 100%; text-align: center;
  background: var(--gold); color: var(--dark);
  font-family: 'Jost', sans-serif; font-size: 13px; font-weight: 600;
  letter-spacing: 0.15em; text-transform: uppercase; padding: 18px 24px;
  border-radius: 100px; text-decoration: none; transition: all var(--transition);
}
.pd-btn:hover { background: var(--gold-light); transform: translateY(-2px); }
.pd-btn-outline {
  display: block; width: 100%; text-align: center; margin-top: 12px;
  background: transparent; border: 1px solid var(--white-30); color: #fff;
  font-family: 'Jost', sans-serif; font-size: 13px; font-weight: 600;
  letter-spacing: 0.15em; text-transform: uppercase; padding: 18px 24px;
  border-radius: 100px; text-decoration: none; transition: all var(--transition);
}
.pd-btn-outline:hover { border-color: var(--gold); color: var(--gold); }

@media (max-width: 900px) {
  .pd-section-inner { grid-template-columns: 1fr; gap: 40px; }
  .pd-inclusions { grid-template-columns: 1fr; }
}
@media (max-width: 600px) {
  .pd-section { padding: 60px 20px; }
}
</style>
@endpush

@section('content')

@php
  $heroImage = 'https://images.unsplash.com/photo-1540202404-b71180fb78d1?w=1800&q=80';
  if ($package->images && $package->images->count() > 0) {
      $img = $package->images->first()->image_path;
      $heroImage = \Illuminate\Support\Str::startsWith($img, 'http') ? $img : asset('storage/' . $img);
  }
@endphp

<!-- HERO -->
<div class="pd-hero" style="background-image: url('{{ $heroImage }}');">
  <div class="pd-hero-content">
    <div class="pd-eyebrow"><i class="fa-solid fa-location-dot"></i> {{ $package->destination->name ?? 'Global' }}</div>
    <h1 class="pd-title">{{ $package->title }}</h1>
    <div class="pd-meta">
      <span><i class="fa-regular fa-moon"></i> {{ $package->duration_nights }} Nights</span>
      <span><i class="fa-solid fa-tag"></i> Premium Package</span>
    </div>
  </div>
</div>

<!-- DETAILS SECTION -->
<div class="pd-section">
  <div class="pd-section-inner">
    
    <!-- MAIN CONTENT -->
    <div class="pd-main">
      <h2>Overview</h2>
      <div class="pd-desc">
        {{ $package->description ?? 'Experience luxury like never before.' }}
      </div>

      <h2>What's Included</h2>
      <div class="pd-inclusions">
        @if($package->inclusions && $package->inclusions->count() > 0)
          @foreach($package->inclusions as $inc)
            <div class="pd-inc-item">
              <i class="fa-solid fa-check-circle"></i>
              <span>{{ $inc->name ?? $inc->title }}</span>
            </div>
          @endforeach
        @endif
      </div>

      <h2>Itinerary</h2>
      <div class="pd-itinerary">
        @if($package->itinerary && $package->itinerary->count() > 0)
          @foreach($package->itinerary as $day)
            <div class="pd-day">
              <div class="pd-day-num">{{ $day->day ?? $loop->iteration }}</div>
              <div class="pd-day-title">{{ $day->title ?? 'Day ' . ($day->day ?? $loop->iteration) }}</div>
              <p>{{ $day->description ?? 'Exciting activities planned.' }}</p>
            </div>
          @endforeach
        @else
          <p>Itinerary details will be provided upon enquiry.</p>
        @endif
      </div>
    </div>

    <!-- SIDEBAR -->
    <div>
      <div class="pd-sidebar">
        <div class="pd-box">
          <div class="pd-price-lbl">Starting Price</div>
          <div class="pd-price-val"><span>₹</span>{{ number_format($package->price_from, 0) }}</div>
          <a href="#enquire" class="pd-btn">Enquire Now</a>
          <a href="https://wa.me/919875073788" target="_blank" class="pd-btn-outline"><i class="fa-brands fa-whatsapp"></i> WhatsApp Us</a>
        </div>
      </div>
    </div>

  </div>
</div>

@endsection
