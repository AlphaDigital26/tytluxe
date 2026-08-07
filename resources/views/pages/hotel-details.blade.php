@extends('layouts.frontend')

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com" />
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;1,400;1,600&family=Jost:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
<style>
/* ===== CORE VARIABLES ===== */
:root {
  --gold: #c9a84c;
  --gold-light: #e8c96b;
  --gold-dim: rgba(201,168,76,0.18);
  --dark: #0d0d0d;
  --dark-2: #141414;
  --dark-3: #1c1c1c;
  --white-80: rgba(255,255,255,0.80);
  --white-60: rgba(255,255,255,0.60);
  --white-30: rgba(255,255,255,0.30);
  --white-10: rgba(255,255,255,0.08);
  --radius: 14px;
  --tr: 0.32s cubic-bezier(0.25, 0.46, 0.45, 0.94);
}
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
body { background: var(--dark); color: #fff; }

/* ===== BACK LINK ===== */
.hd-back {
  display: inline-flex; align-items: center; gap: 8px;
  font-family: 'Jost', sans-serif; font-size: 12.5px; font-weight: 600;
  letter-spacing: 0.12em; text-transform: uppercase; color: var(--gold);
  text-decoration: none; padding: 20px 40px 0;
  transition: opacity var(--tr);
}
.hd-back:hover { opacity: 0.7; }
.hd-back svg { width: 14px; height: 14px; }

/* ===== HERO GALLERY ===== */
.hd-gallery { position: relative; }
.hd-gallery-main {
  position: relative; width: 100%; height: 72vh; min-height: 480px; overflow: hidden;
  background: var(--dark-2);
}
.hd-gallery-main img {
  width: 100%; height: 100%; object-fit: cover;
  transition: transform 0.7s ease; display: block;
}
.hd-gallery-main:hover img { transform: scale(1.03); }
.hd-gallery-overlay {
  position: absolute; inset: 0;
  background: linear-gradient(to top, rgba(13,13,13,0.92) 0%, rgba(13,13,13,0.3) 50%, transparent 100%);
  pointer-events: none;
}
.hd-gallery-hero-info {
  position: absolute; bottom: 0; left: 0; right: 0;
  padding: 48px 48px 40px;
  display: flex; justify-content: space-between; align-items: flex-end; gap: 24px; flex-wrap: wrap;
}
.hd-badge-row { display: flex; align-items: center; gap: 10px; margin-bottom: 14px; flex-wrap: wrap; }
.hd-badge {
  font-family: 'Jost', sans-serif; font-size: 11px; font-weight: 700; letter-spacing: 0.15em;
  text-transform: uppercase; padding: 6px 16px; border-radius: 100px;
  background: var(--gold); color: var(--dark); display: inline-block;
}
.hd-badge-outline {
  font-family: 'Jost', sans-serif; font-size: 11px; font-weight: 600; letter-spacing: 0.12em;
  text-transform: uppercase; padding: 5px 14px; border-radius: 100px;
  border: 1px solid var(--gold); color: var(--gold); display: inline-block;
}
.hd-stars { display: flex; gap: 3px; align-items: center; }
.hd-stars span { color: var(--gold); font-size: 15px; }
.hd-hero-title {
  font-family: 'Cormorant Garamond', serif;
  font-size: clamp(2.4rem, 5vw, 4.2rem); font-weight: 500; line-height: 1.05;
  color: #fff; margin-bottom: 12px;
}
.hd-hero-location {
  font-family: 'Jost', sans-serif; font-size: 14.5px; font-weight: 300;
  color: var(--white-60); display: flex; align-items: center; gap: 8px;
}
.hd-hero-location svg { color: var(--gold); flex-shrink: 0; }
/* Gallery thumbnail strip */
.hd-thumbs {
  display: grid; grid-template-columns: repeat(4, 1fr); gap: 4px;
  max-height: 120px;
}
.hd-thumb {
  height: 120px; overflow: hidden; cursor: pointer; background: var(--dark-2);
  position: relative;
}
.hd-thumb img { width: 100%; height: 100%; object-fit: cover; transition: transform var(--tr); }
.hd-thumb:hover img { transform: scale(1.08); }
.hd-thumb.active::after {
  content: ''; position: absolute; inset: 0; border: 2px solid var(--gold);
}
.hd-thumb-more {
  position: relative; cursor: pointer; background: var(--dark-3);
}
.hd-thumb-more img { opacity: 0.5; }
.hd-thumb-more-label {
  position: absolute; inset: 0; display: flex; flex-direction: column; align-items: center; justify-content: center;
  font-family: 'Jost', sans-serif; font-size: 13px; font-weight: 600; color: #fff;
}

/* ===== LAYOUT ===== */
.hd-layout {
  max-width: 1280px; margin: 0 auto;
  padding: 56px 40px 80px;
  display: grid; grid-template-columns: 1fr 380px; gap: 56px; align-items: start;
}
.hd-section { margin-bottom: 52px; }
.hd-section:last-child { margin-bottom: 0; }
.hd-section-title {
  font-family: 'Cormorant Garamond', serif; font-size: 2rem; font-weight: 500;
  color: var(--gold); margin-bottom: 24px;
  display: flex; align-items: center; gap: 14px;
}
.hd-section-title::after { content: ''; flex: 1; height: 1px; background: var(--gold-dim); }

/* ===== QUICK FACTS ===== */
.hd-quick-facts {
  display: grid; grid-template-columns: repeat(3, 1fr); gap: 1px;
  background: var(--gold-dim); border: 1px solid var(--gold-dim); border-radius: var(--radius);
  overflow: hidden; margin-bottom: 52px;
}
.hd-fact {
  background: var(--dark-2); padding: 22px 20px; text-align: center;
}
.hd-fact-label {
  font-family: 'Jost', sans-serif; font-size: 10px; font-weight: 600;
  letter-spacing: 0.18em; text-transform: uppercase; color: var(--white-30); margin-bottom: 6px;
}
.hd-fact-value {
  font-family: 'Cormorant Garamond', serif; font-size: 1.35rem; font-weight: 600; color: #fff;
}

/* ===== DESCRIPTION ===== */
.hd-desc {
  font-family: 'Jost', sans-serif; font-size: 15.5px; font-weight: 300;
  line-height: 1.85; color: var(--white-80);
}

/* ===== AMENITIES ===== */
.hd-amenities { display: flex; flex-wrap: wrap; gap: 10px; }
.hd-amenity {
  display: inline-flex; align-items: center; gap: 8px;
  border: 1px solid rgba(255,255,255,0.12); padding: 10px 18px; border-radius: 100px;
  font-family: 'Jost', sans-serif; font-size: 13.5px; font-weight: 400; color: var(--white-80);
  transition: border-color var(--tr), background var(--tr);
}
.hd-amenity:hover { border-color: var(--gold); background: var(--gold-dim); }
.hd-amenity-dot { width: 6px; height: 6px; border-radius: 50%; background: var(--gold); flex-shrink: 0; }

/* ===== ROOMS ===== */
.hd-rooms { display: grid; gap: 18px; }
.hd-room-card {
  background: var(--dark-2); border: 1px solid rgba(255,255,255,0.07);
  border-radius: var(--radius); padding: 28px 28px 24px;
  display: grid; grid-template-columns: 1fr auto; gap: 20px; align-items: start;
  transition: border-color var(--tr), background var(--tr);
}
.hd-room-card:hover { border-color: var(--gold-dim); background: #171717; }
.hd-room-header { display: flex; align-items: center; gap: 12px; margin-bottom: 14px; flex-wrap: wrap; }
.hd-room-name {
  font-family: 'Cormorant Garamond', serif; font-size: 1.6rem; font-weight: 600; color: #fff;
}
.hd-room-board {
  font-family: 'Jost', sans-serif; font-size: 11px; font-weight: 700; letter-spacing: 0.12em;
  text-transform: uppercase; background: rgba(201,168,76,0.12); color: var(--gold);
  padding: 4px 12px; border-radius: 100px;
}
.hd-room-meta {
  font-family: 'Jost', sans-serif; font-size: 13px; color: var(--white-60); margin-bottom: 14px;
  display: flex; align-items: center; gap: 16px; flex-wrap: wrap;
}
.hd-room-meta span { display: flex; align-items: center; gap: 6px; }
.hd-room-inclusions { display: flex; flex-wrap: wrap; gap: 8px; }
.hd-room-inc {
  font-family: 'Jost', sans-serif; font-size: 12px; font-weight: 400;
  background: rgba(255,255,255,0.05); color: var(--white-60);
  border: 1px solid rgba(255,255,255,0.09);
  padding: 5px 12px; border-radius: 6px;
}
.hd-room-price-col { text-align: right; flex-shrink: 0; }
.hd-room-price {
  font-family: 'Jost', sans-serif; font-size: 1.9rem; font-weight: 700; color: #fff; margin-bottom: 2px;
}
.hd-room-per {
  font-family: 'Jost', sans-serif; font-size: 11.5px; color: var(--white-30); margin-bottom: 6px;
}
.hd-cancel-note {
  font-family: 'Jost', sans-serif; font-size: 11.5px; color: #4ade80; margin-bottom: 16px;
}
.hd-cancel-note.no-refund { color: #f87171; }
.hd-enquire-btn {
  display: inline-flex; align-items: center; justify-content: center; gap: 8px;
  background: var(--gold); color: var(--dark); text-decoration: none;
  font-family: 'Jost', sans-serif; font-size: 12.5px; font-weight: 700; letter-spacing: 0.1em;
  text-transform: uppercase; padding: 11px 24px; border-radius: 100px; white-space: nowrap;
  transition: all var(--tr);
}
.hd-enquire-btn:hover { background: var(--gold-light); transform: translateY(-2px); box-shadow: 0 6px 20px rgba(201,168,76,0.35); }

/* ===== POLICIES ===== */
.hd-policies { list-style: none; display: grid; gap: 12px; }
.hd-policy {
  display: flex; align-items: flex-start; gap: 12px;
  font-family: 'Jost', sans-serif; font-size: 14.5px; font-weight: 300; color: var(--white-80); line-height: 1.6;
}
.hd-policy-icon { color: var(--gold); margin-top: 3px; flex-shrink: 0; font-size: 16px; }

/* ===== CHECK-IN INFO ===== */
.hd-checkin-grid {
  display: grid; grid-template-columns: 1fr 1fr; gap: 14px;
}
.hd-checkin-box {
  background: var(--dark-2); border: 1px solid var(--gold-dim); border-radius: var(--radius);
  padding: 20px 22px; text-align: center;
}
.hd-checkin-label {
  font-family: 'Jost', sans-serif; font-size: 10px; font-weight: 600;
  letter-spacing: 0.18em; text-transform: uppercase; color: var(--gold); margin-bottom: 6px;
}
.hd-checkin-value {
  font-family: 'Cormorant Garamond', serif; font-size: 2rem; font-weight: 500; color: #fff;
}

/* ===== SIDEBAR ===== */
.hd-sidebar { position: sticky; top: 100px; }
.hd-sidebar-box {
  background: var(--dark-2); border: 1px solid var(--gold-dim); border-radius: var(--radius);
  overflow: hidden; margin-bottom: 20px;
}
.hd-sidebar-head {
  background: linear-gradient(135deg, rgba(201,168,76,0.12) 0%, transparent 100%);
  border-bottom: 1px solid var(--gold-dim); padding: 28px 28px 20px;
}
.hd-sidebar-eyebrow {
  font-family: 'Jost', sans-serif; font-size: 10px; font-weight: 700; letter-spacing: 0.2em;
  text-transform: uppercase; color: var(--gold); margin-bottom: 8px;
}
.hd-sidebar-title {
  font-family: 'Cormorant Garamond', serif; font-size: 1.65rem; font-weight: 500; color: #fff; line-height: 1.2;
}
.hd-sidebar-body { padding: 24px 28px 28px; }
.hd-sidebar-price {
  font-family: 'Jost', sans-serif; font-size: 0.9rem; color: var(--white-60); margin-bottom: 4px;
}
.hd-sidebar-amount {
  font-family: 'Cormorant Garamond', serif; font-size: 2.4rem; font-weight: 600; color: #fff; margin-bottom: 4px;
}
.hd-sidebar-amount span { font-size: 1rem; font-family: 'Jost', sans-serif; color: var(--white-30); font-weight: 300; }
.hd-sidebar-divider { height: 1px; background: var(--gold-dim); margin: 20px 0; }
.hd-sidebar-point {
  display: flex; align-items: center; gap: 10px;
  font-family: 'Jost', sans-serif; font-size: 13.5px; font-weight: 300; color: var(--white-60);
  margin-bottom: 12px;
}
.hd-sidebar-point-dot { width: 6px; height: 6px; border-radius: 50%; background: var(--gold); flex-shrink: 0; }
.hd-sidebar-wa {
  display: flex; align-items: center; justify-content: center; gap: 10px;
  background: #25D366; color: #fff; text-decoration: none;
  font-family: 'Jost', sans-serif; font-size: 14px; font-weight: 700; letter-spacing: 0.08em;
  text-transform: uppercase; padding: 15px; border-radius: 100px; width: 100%;
  transition: all var(--tr); margin-bottom: 10px;
}
.hd-sidebar-wa:hover { background: #20c45b; transform: translateY(-2px); box-shadow: 0 8px 24px rgba(37,211,102,0.3); }
.hd-sidebar-call {
  display: flex; align-items: center; justify-content: center; gap: 10px;
  border: 1px solid rgba(255,255,255,0.15); color: var(--white-80); text-decoration: none;
  font-family: 'Jost', sans-serif; font-size: 13.5px; font-weight: 500;
  padding: 13px; border-radius: 100px; width: 100%;
  transition: all var(--tr);
}
.hd-sidebar-call:hover { border-color: var(--gold); color: var(--gold); }

/* Second sidebar box: quick summary */
.hd-sidebar-mini { background: var(--dark-2); border: 1px solid rgba(255,255,255,0.06); border-radius: var(--radius); padding: 22px 24px; }
.hd-sidebar-mini-row {
  display: flex; justify-content: space-between; align-items: center;
  font-family: 'Jost', sans-serif; font-size: 13.5px; padding: 10px 0;
  border-bottom: 1px solid rgba(255,255,255,0.05); color: var(--white-60);
}
.hd-sidebar-mini-row:last-child { border-bottom: none; }
.hd-sidebar-mini-row strong { color: #fff; font-weight: 500; text-align: right; max-width: 55%; }

/* ===== PHOTO LIGHTBOX ===== */
.hd-lightbox {
  display: none; position: fixed; inset: 0; z-index: 9999;
  background: rgba(0,0,0,0.93); align-items: center; justify-content: center;
}
.hd-lightbox.open { display: flex; }
.hd-lightbox-img {
  max-width: 90vw; max-height: 85vh; object-fit: contain; border-radius: 8px;
}
.hd-lightbox-close {
  position: absolute; top: 20px; right: 26px; font-size: 30px; color: #fff;
  cursor: pointer; line-height: 1; background: none; border: none;
  font-family: sans-serif; opacity: 0.7; transition: opacity var(--tr);
}
.hd-lightbox-close:hover { opacity: 1; }
.hd-lightbox-nav {
  position: absolute; top: 50%; transform: translateY(-50%);
  background: rgba(255,255,255,0.1); border: none; color: #fff;
  font-size: 28px; width: 52px; height: 52px; border-radius: 50%; cursor: pointer;
  display: flex; align-items: center; justify-content: center;
  transition: background var(--tr);
}
.hd-lightbox-nav:hover { background: rgba(255,255,255,0.25); }
.hd-lightbox-prev { left: 20px; }
.hd-lightbox-next { right: 20px; }
.hd-lightbox-counter {
  position: absolute; bottom: 24px; left: 50%; transform: translateX(-50%);
  font-family: 'Jost', sans-serif; font-size: 13px; color: rgba(255,255,255,0.5);
}

/* ===== RESPONSIVE ===== */
@media (max-width: 1100px) {
  .hd-layout { grid-template-columns: 1fr; gap: 40px; }
  .hd-sidebar { position: static; }
}
@media (max-width: 768px) {
  .hd-gallery-main { height: 55vw; min-height: 300px; }
  .hd-gallery-hero-info { padding: 28px 20px 24px; }
  .hd-layout { padding: 32px 20px 60px; }
  .hd-back { padding: 16px 20px 0; }
  .hd-quick-facts { grid-template-columns: 1fr 1fr; }
  .hd-room-card { grid-template-columns: 1fr; }
  .hd-room-price-col { text-align: left; }
  .hd-checkin-grid { grid-template-columns: 1fr 1fr; }
  .hd-thumbs { grid-template-columns: repeat(4,1fr); max-height: 80px; }
  .hd-thumb { height: 80px; }
}
</style>
@endpush

@php
    $name       = $hotel['name']        ?? 'Unknown Hotel';
    $desc       = $hotel['description'] ?? 'Contact us for more details about this property.';
    $rating     = (int)($hotel['rt']    ?? 4);
    $category   = $hotel['pt']          ?? 'Hotel';
    $city       = $hotel['ad']['city']['name']    ?? '';
    $country    = $hotel['ad']['country']['name'] ?? '';
    $address    = trim(($hotel['ad']['adr'] ?? '') . ($city ? ', '.$city : '') . ($country ? ', '.$country : ''), ', ');
    $images     = $hotel['img']         ?? [];
    $heroImg    = $images[0]['url'] ?? 'https://images.unsplash.com/photo-1566073771259-6a8506099945?w=1600&q=80';
    $amenities  = $hotel['fl']          ?? [];
    $rooms      = $hotel['rooms']       ?? [];
    $policies   = $hotel['policies']    ?? [];
    $checkIn    = $hotel['checkIn']     ?? '14:00';
    $checkOut   = $hotel['checkOut']    ?? '12:00';
    $hotelId    = $hotel['id']          ?? '';
    $lowestPrice = 0;
    if (!empty($rooms)) {
        $lowestPrice = min(array_column($rooms, 'price'));
    }
@endphp


@section('content')

{{-- BACK LINK --}}
<a href="{{ route('hotels') }}" class="hd-back">
  <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2"><path d="M13 8H3M7 4L3 8l4 4"/></svg>
  Back to Hotels
</a>

{{-- ===== GALLERY ===== --}}
<div class="hd-gallery">
  {{-- Main hero image --}}
  <div class="hd-gallery-main" id="hdMainImg">
    <img src="{{ $heroImg }}" alt="{{ $name }}" id="hdMainImgTag" />
    <div class="hd-gallery-overlay"></div>
    <div class="hd-gallery-hero-info">
      <div>
        <div class="hd-badge-row">
          <span class="hd-badge">{{ $rating }} Star {{ $category }}</span>
          @if($city)
            <span class="hd-badge-outline">📍 {{ $city }}</span>
          @endif
        </div>
        <div class="hd-stars">
          @for($s = 0; $s < $rating; $s++) <span>★</span> @endfor
          @for($s = $rating; $s < 5; $s++) <span style="opacity:0.25">★</span> @endfor
        </div>
        <h1 class="hd-hero-title">{{ $name }}</h1>
        @if($address)
        <div class="hd-hero-location">
          <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13S3 17 3 10a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
          {{ $address }}
        </div>
        @endif
      </div>
      @if(count($images) > 1)
        <div>
          <button onclick="openLightbox(0)" style="background:rgba(255,255,255,0.12); border:1px solid rgba(255,255,255,0.2); color:#fff; font-family:'Jost',sans-serif; font-size:12px; font-weight:600; letter-spacing:0.1em; text-transform:uppercase; padding:10px 20px; border-radius:100px; cursor:pointer; transition:all 0.3s ease; display:flex; align-items:center; gap:8px;" onmouseover="this.style.background='rgba(255,255,255,0.2)'" onmouseout="this.style.background='rgba(255,255,255,0.12)'">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
            {{ count($images) }} Photos
          </button>
        </div>
      @endif
    </div>
  </div>

  {{-- Thumbnail strip --}}
  @if(count($images) > 1)
  <div class="hd-thumbs">
    @foreach(array_slice($images, 0, 4) as $idx => $img)
      @if($idx === 3 && count($images) > 4)
        <div class="hd-thumb hd-thumb-more {{ $idx === 0 ? 'active' : '' }}" onclick="openLightbox({{ $idx }})">
          <img src="{{ $img['url'] }}" alt="Gallery photo {{ $idx+1 }}" loading="lazy"/>
          <div class="hd-thumb-more-label">
            <span style="font-size:22px; margin-bottom:2px">+{{ count($images) - 3 }}</span>
            <span>Photos</span>
          </div>
        </div>
      @else
        <div class="hd-thumb {{ $idx === 0 ? 'active' : '' }}" onclick="switchMain('{{ $img['url'] }}', {{ $idx }})" id="thumb-{{ $idx }}">
          <img src="{{ $img['url'] }}" alt="Gallery photo {{ $idx+1 }}" loading="lazy"/>
        </div>
      @endif
    @endforeach
  </div>
  @endif
</div>

{{-- ===== MAIN LAYOUT ===== --}}
<div class="hd-layout">
  {{-- ── LEFT COLUMN ── --}}
  <div class="hd-main">

    {{-- Quick Facts --}}
    <div class="hd-quick-facts">
      <div class="hd-fact">
        <div class="hd-fact-label">Star Rating</div>
        <div class="hd-fact-value">{{ $rating }} ★ {{ $category }}</div>
      </div>
      <div class="hd-fact">
        <div class="hd-fact-label">Check-In</div>
        <div class="hd-fact-value">{{ $checkIn }}</div>
      </div>
      <div class="hd-fact">
        <div class="hd-fact-label">Check-Out</div>
        <div class="hd-fact-value">{{ $checkOut }}</div>
      </div>
    </div>

    {{-- About --}}
    <div class="hd-section">
      <h2 class="hd-section-title">About The Property</h2>
      <p class="hd-desc">{{ $desc }}</p>
    </div>

    {{-- Amenities --}}
    @if(count($amenities) > 0)
    <div class="hd-section">
      <h2 class="hd-section-title">Facilities &amp; Amenities</h2>
      <div class="hd-amenities">
        @foreach($amenities as $amenity)
          <span class="hd-amenity"><span class="hd-amenity-dot"></span>{{ $amenity }}</span>
        @endforeach
      </div>
    </div>
    @endif

    {{-- Rooms --}}
    @if(count($rooms) > 0)
    <div class="hd-section">
      <h2 class="hd-section-title">Available Rooms</h2>
      <div class="hd-rooms">
        @foreach($rooms as $room)
        @php
          $roomName    = $room['name']         ?? 'Standard Room';
          $roomPrice   = $room['price']        ?? 0;
          $roomOcc     = $room['maxOccupancy'] ?? 2;
          $roomBoard   = $room['boardType']    ?? 'Room Only';
          $roomIncs    = $room['inclusions']   ?? [];
          $roomCancel  = $room['cancellation'] ?? '';
          $isNonRefund = str_contains(strtolower($roomCancel), 'non');
          $waMsg       = urlencode('Hi TYT Luxe! I\'d like to enquire about the ' . $roomName . ' at ' . $name . '. Hotel ID: ' . $hotelId);
        @endphp
        <div class="hd-room-card">
          <div>
            <div class="hd-room-header">
              <h3 class="hd-room-name">{{ $roomName }}</h3>
              <span class="hd-room-board">{{ $roomBoard }}</span>
            </div>
            <div class="hd-room-meta">
              <span>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                Max {{ $roomOcc }} {{ $roomOcc > 1 ? 'guests' : 'guest' }}
              </span>
              @if($roomCancel)
              <span>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                {{ $roomCancel }}
              </span>
              @endif
            </div>
            @if(count($roomIncs) > 0)
            <div class="hd-room-inclusions">
              @foreach($roomIncs as $inc)
                <span class="hd-room-inc">{{ $inc }}</span>
              @endforeach
            </div>
            @endif
          </div>
          <div class="hd-room-price-col">
            <div class="hd-room-price">₹{{ number_format($roomPrice) }}</div>
            <div class="hd-room-per">per night</div>
            @if($roomCancel)
              <div class="hd-cancel-note {{ $isNonRefund ? 'no-refund' : '' }}">
                {{ $isNonRefund ? '⚠ Non-refundable' : '✓ ' . $roomCancel }}
              </div>
            @endif
            <a href="https://wa.me/919875073788?text={{ $waMsg }}" class="hd-enquire-btn" target="_blank" rel="noopener">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
              Enquire Now
            </a>
          </div>
        </div>
        @endforeach
      </div>
    </div>
    @endif

    {{-- Check-in / Check-out times --}}
    <div class="hd-section">
      <h2 class="hd-section-title">Check-In &amp; Check-Out</h2>
      <div class="hd-checkin-grid">
        <div class="hd-checkin-box">
          <div class="hd-checkin-label">Check-In Time</div>
          <div class="hd-checkin-value">{{ $checkIn }}</div>
        </div>
        <div class="hd-checkin-box">
          <div class="hd-checkin-label">Check-Out Time</div>
          <div class="hd-checkin-value">{{ $checkOut }}</div>
        </div>
      </div>
    </div>

    {{-- Policies --}}
    @if(count($policies) > 0)
    <div class="hd-section">
      <h2 class="hd-section-title">Hotel Policies</h2>
      <ul class="hd-policies">
        @foreach($policies as $policy)
          <li class="hd-policy">
            <span class="hd-policy-icon">◆</span>
            <span>{{ $policy }}</span>
          </li>
        @endforeach
      </ul>
    </div>
    @endif

  </div>

  {{-- ── SIDEBAR ── --}}
  <aside class="hd-sidebar">
    {{-- Booking CTA box --}}
    <div class="hd-sidebar-box">
      <div class="hd-sidebar-head">
        <p class="hd-sidebar-eyebrow">Expert Assistance</p>
        <h3 class="hd-sidebar-title">Book This Hotel With Our Experts</h3>
      </div>
      <div class="hd-sidebar-body">
        @if($lowestPrice > 0)
          <p class="hd-sidebar-price">Starting from</p>
          <div class="hd-sidebar-amount">₹{{ number_format($lowestPrice) }} <span>/ night</span></div>
        @endif
        <div class="hd-sidebar-divider"></div>
        <div class="hd-sidebar-point"><span class="hd-sidebar-point-dot"></span>Best rate guaranteed</div>
        <div class="hd-sidebar-point"><span class="hd-sidebar-point-dot"></span>No hidden charges</div>
        <div class="hd-sidebar-point"><span class="hd-sidebar-point-dot"></span>Free personalised assistance</div>
        <div class="hd-sidebar-point"><span class="hd-sidebar-point-dot"></span>Flexible booking support</div>
        <div class="hd-sidebar-divider"></div>
        <a href="https://wa.me/919875073788?text={{ urlencode('Hi TYT Luxe! I want to book ' . $name . ' in ' . $city . '. Please help me with availability and best rates.') }}"
           class="hd-sidebar-wa" target="_blank" rel="noopener">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
          Chat on WhatsApp
        </a>
        <a href="tel:9875073788" class="hd-sidebar-call">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.61 3.47 2 2 0 0 1 3.6 1.27h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L7.91 8.96a16 16 0 0 0 6.06 6.06l.96-.96a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 21.99 16.9z"/></svg>
          Call Us Directly
        </a>
      </div>
    </div>

    {{-- Quick hotel summary --}}
    <div class="hd-sidebar-mini">
      <div class="hd-sidebar-mini-row">
        <span>Hotel Type</span>
        <strong>{{ $category }}</strong>
      </div>
      <div class="hd-sidebar-mini-row">
        <span>Star Rating</span>
        <strong>{{ $rating }} ★</strong>
      </div>
      @if($city)
      <div class="hd-sidebar-mini-row">
        <span>City</span>
        <strong>{{ $city }}</strong>
      </div>
      @endif
      @if($country)
      <div class="hd-sidebar-mini-row">
        <span>Country</span>
        <strong>{{ $country }}</strong>
      </div>
      @endif
      <div class="hd-sidebar-mini-row">
        <span>Check-In</span>
        <strong>{{ $checkIn }}</strong>
      </div>
      <div class="hd-sidebar-mini-row">
        <span>Check-Out</span>
        <strong>{{ $checkOut }}</strong>
      </div>
      @if(count($rooms) > 0)
      <div class="hd-sidebar-mini-row">
        <span>Room Options</span>
        <strong>{{ count($rooms) }} Type(s)</strong>
      </div>
      @endif
      @if(count($amenities) > 0)
      <div class="hd-sidebar-mini-row">
        <span>Total Amenities</span>
        <strong>{{ count($amenities) }}+</strong>
      </div>
      @endif
    </div>
  </aside>
</div>

{{-- ===== LIGHTBOX ===== --}}
<div class="hd-lightbox" id="hdLightbox" onclick="if(event.target===this) closeLightbox()">
  <button class="hd-lightbox-close" onclick="closeLightbox()">×</button>
  <button class="hd-lightbox-nav hd-lightbox-prev" onclick="lightboxNav(-1)">‹</button>
  <img class="hd-lightbox-img" id="hdLightboxImg" src="" alt="Hotel Photo" />
  <button class="hd-lightbox-nav hd-lightbox-next" onclick="lightboxNav(1)">›</button>
  <div class="hd-lightbox-counter" id="hdLightboxCounter"></div>
</div>

@endsection

@push('scripts')
<script>
(function () {
  const images = @json(array_column($images, 'url'));
  let currentIdx = 0;

  // Thumbnail switcher
  window.switchMain = function(url, idx) {
    document.getElementById('hdMainImgTag').src = url;
    document.querySelectorAll('.hd-thumb').forEach((t, i) => {
      t.classList.toggle('active', i === idx);
    });
    currentIdx = idx;
  };

  // Lightbox
  window.openLightbox = function(idx) {
    currentIdx = idx;
    document.getElementById('hdLightboxImg').src = images[currentIdx];
    document.getElementById('hdLightboxCounter').textContent = (currentIdx + 1) + ' / ' + images.length;
    document.getElementById('hdLightbox').classList.add('open');
    document.body.style.overflow = 'hidden';
  };

  window.closeLightbox = function() {
    document.getElementById('hdLightbox').classList.remove('open');
    document.body.style.overflow = '';
  };

  window.lightboxNav = function(dir) {
    currentIdx = (currentIdx + dir + images.length) % images.length;
    document.getElementById('hdLightboxImg').src = images[currentIdx];
    document.getElementById('hdLightboxCounter').textContent = (currentIdx + 1) + ' / ' + images.length;
  };

  // Keyboard navigation
  document.addEventListener('keydown', (e) => {
    if (!document.getElementById('hdLightbox').classList.contains('open')) return;
    if (e.key === 'Escape')      closeLightbox();
    if (e.key === 'ArrowRight')  lightboxNav(1);
    if (e.key === 'ArrowLeft')   lightboxNav(-1);
  });
})();
</script>
@endpush
