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
  --green: #4ade80;
  --radius: 14px;
  --tr: 0.32s cubic-bezier(0.25, 0.46, 0.45, 0.94);
}
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
body { background: var(--dark); color: #fff; }

/* ===== HERO GALLERY ===== */
.hd-gallery { position: relative; }
.hd-gallery-main {
  position: relative; width: 100%; height: 70vh; min-height: 480px; overflow: hidden;
  background: var(--dark-2);
}
.hd-gallery-slide {
  position: absolute; inset: 0;
  opacity: 0; transition: opacity 0.55s ease;
}
.hd-gallery-slide.active { opacity: 1; }
.hd-gallery-slide img {
  width: 100%; height: 100%; object-fit: cover; display: block;
}
.hd-gallery-overlay {
  position: absolute; inset: 0;
  background: linear-gradient(to top, rgba(13,13,13,0.92) 0%, rgba(13,13,13,0.25) 55%, transparent 100%);
  pointer-events: none;
}
/* Gallery nav arrows */
.hd-gallery-prev, .hd-gallery-next {
  position: absolute; top: 50%; transform: translateY(-50%);
  background: rgba(13,13,13,0.5); backdrop-filter: blur(6px);
  border: 1px solid rgba(255,255,255,0.18); color: #fff;
  width: 44px; height: 44px; border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  cursor: pointer; transition: all var(--tr); font-size: 18px;
  z-index: 3; opacity: 0; pointer-events: none;
}
.hd-gallery-main:hover .hd-gallery-prev,
.hd-gallery-main:hover .hd-gallery-next { opacity: 1; pointer-events: all; }
.hd-gallery-prev { left: 20px; }
.hd-gallery-next { right: 20px; }
.hd-gallery-prev:hover, .hd-gallery-next:hover {
  background: var(--gold); border-color: var(--gold); color: var(--dark);
}
/* Slide counter */
.hd-gallery-counter {
  position: absolute; bottom: 20px; right: 20px; z-index: 3;
  background: rgba(0,0,0,0.6); backdrop-filter: blur(8px);
  color: #fff; font-family: 'Jost', sans-serif; font-size: 12px; font-weight: 500;
  padding: 5px 14px; border-radius: 100px; letter-spacing: 0.05em;
}
/* Hero info overlay */
.hd-gallery-hero-info {
  position: absolute; bottom: 0; left: 0; right: 0;
  padding: 48px 48px 40px;
  display: flex; justify-content: space-between; align-items: flex-end; gap: 24px; flex-wrap: wrap;
  z-index: 2;
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
.hd-fact { background: var(--dark-2); padding: 22px 20px; text-align: center; }
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

/* ===== ROOM CATEGORIES ===== */
.hd-room-cats { display: flex; flex-wrap: wrap; gap: 10px; }
/* ===== ROOM CARDS ===== */
.hd-room-list { display: flex; flex-direction: column; gap: 20px; }
.hd-room-card {
  background: var(--dark-2); border: 1px solid rgba(255,255,255,0.08);
  border-radius: 14px; overflow: hidden; display: flex; flex-direction: column;
}
@media (min-width: 768px) { .hd-room-card { flex-direction: row; } }
.hd-room-img { width: 100%; height: 180px; }
@media (min-width: 768px) { .hd-room-img { width: 260px; height: auto; flex-shrink: 0; } }
.hd-room-img img { width: 100%; height: 100%; object-fit: cover; }
.hd-room-content {
  padding: 22px; display: flex; flex-direction: column; gap: 20px; flex: 1;
}
@media (min-width: 992px) {
  .hd-room-content { flex-direction: row; justify-content: space-between; align-items: center; }
}
.hd-room-info { flex: 1; border-bottom: 1px dashed rgba(255,255,255,0.1); padding-bottom: 20px; }
@media (min-width: 992px) {
  .hd-room-info { border-bottom: none; border-right: 1px dashed rgba(255,255,255,0.1); padding-bottom: 0; padding-right: 24px; margin-right: 24px; }
}
.hd-room-title {
  font-family: 'Cormorant Garamond', serif; font-size: 1.6rem; color: #fff; margin-bottom: 10px; line-height: 1.2;
}
.hd-room-specs {
  display: flex; flex-wrap: wrap; gap: 14px; margin-bottom: 14px;
}
.hd-room-spec {
  display: flex; align-items: center; gap: 6px;
  font-family: 'Jost', sans-serif; font-size: 13px; color: var(--white-80);
}
.hd-room-spec svg { width: 14px; height: 14px; color: var(--gold); }
.hd-room-desc-text {
  font-size: 13px; margin-bottom: 14px; color: var(--white-60); line-height: 1.5;
  display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
}
.hd-room-inc {
  display: flex; flex-wrap: wrap; gap: 8px;
}
.hd-room-inc span {
  display: inline-flex; align-items: center; gap: 6px;
  font-family: 'Jost', sans-serif; font-size: 11.5px; color: var(--green);
  background: rgba(74, 222, 128, 0.08); padding: 4px 10px; border-radius: 100px;
}
.hd-room-price {
  display: flex; flex-direction: column; justify-content: center; align-items: flex-start;
  min-width: 160px;
}
@media (min-width: 992px) { .hd-room-price { align-items: center; text-align: center; } }
.hd-room-price-val {
  font-family: 'Jost', sans-serif; font-size: 1.6rem; font-weight: 700; color: #fff; line-height: 1;
}
.hd-room-price-val small { font-size: 12px; font-weight: 400; color: var(--white-60); margin-left: 4px; }
.hd-room-cancel {
  font-family: 'Jost', sans-serif; font-size: 12px; color: var(--white-60); margin-top: 6px; margin-bottom: 16px;
}
.hd-room-cancel.text-green { color: var(--green); }
.hd-room-btn {
  display: inline-flex; align-items: center; justify-content: center;
  padding: 12px 24px; border-radius: 100px;
  background: transparent; border: 1px solid var(--gold);
  color: var(--gold); font-family: 'Jost', sans-serif; font-size: 12px;
  font-weight: 600; text-transform: uppercase; letter-spacing: 0.1em;
  cursor: pointer; transition: all var(--tr); width: 100%;
}
.hd-room-btn:hover { background: var(--gold); color: var(--dark); }

/* ===== NEARBY ATTRACTIONS ===== */
.hd-nearby-list {
  display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px;
}
.hd-nearby-item {
  background: var(--dark-2); border: 1px solid rgba(255,255,255,0.07);
  border-radius: 10px; padding: 14px 18px;
  font-family: 'Jost', sans-serif; font-size: 13.5px; color: var(--white-80);
  display: flex; align-items: center; gap: 10px;
  transition: border-color var(--tr);
}
.hd-nearby-item:hover { border-color: var(--gold-dim); }
.hd-nearby-item::before {
  content: '📍'; font-size: 14px; flex-shrink: 0;
}

/* ===== STICKY BOOKING CARD ===== */
.hd-book-card {
  position: sticky; top: 100px;
  background: var(--dark-2); border: 1px solid rgba(201,168,76,0.25);
  border-radius: 20px; padding: 32px 28px;
}
.hd-book-card-title {
  font-family: 'Cormorant Garamond', serif; font-size: 1.6rem;
  font-weight: 500; color: #fff; margin-bottom: 6px;
}
.hd-book-card-loc {
  font-family: 'Jost', sans-serif; font-size: 12.5px; color: var(--gold);
  letter-spacing: 0.08em; text-transform: uppercase; margin-bottom: 20px;
}
.hd-book-price-row { margin-bottom: 20px; }
.hd-book-from {
  font-family: 'Jost', sans-serif; font-size: 11px; color: var(--white-30);
  text-transform: uppercase; letter-spacing: 0.1em; display: block; margin-bottom: 4px;
}
.hd-book-price {
  font-family: 'Jost', sans-serif; font-size: 2rem; font-weight: 700; color: #fff;
}
.hd-book-price-req {
  font-family: 'Jost', sans-serif; font-size: 1.1rem; font-weight: 400; color: var(--white-60); font-style: italic;
}
.hd-book-price-note {
  font-family: 'Jost', sans-serif; font-size: 11px; color: var(--white-30);
  margin-top: 2px; display: block;
}

/* Card quick info */
.hd-book-facts { display: grid; grid-template-columns: 1fr 1fr; gap: 1px; background: rgba(255,255,255,0.06); border-radius: 12px; overflow: hidden; margin-bottom: 24px; }
.hd-book-fact { background: var(--dark-2); padding: 14px 16px; }
.hd-book-fact-label {
  font-family: 'Jost', sans-serif; font-size: 10px; font-weight: 600;
  letter-spacing: 0.15em; text-transform: uppercase; color: var(--gold); margin-bottom: 4px;
}
.hd-book-fact-val {
  font-family: 'Jost', sans-serif; font-size: 13px; color: #fff; font-weight: 400;
}

/* Card perks */
.hd-book-perks { margin-bottom: 24px; display: flex; flex-direction: column; gap: 8px; }
.hd-book-perk {
  font-family: 'Jost', sans-serif; font-size: 12.5px; color: var(--white-60);
  display: flex; align-items: center; gap: 8px;
}
.hd-book-perk-dot { width: 6px; height: 6px; border-radius: 50%; background: var(--gold); flex-shrink: 0; }
.hd-book-perk.green-perk { color: var(--green); }
.hd-book-perk.green-perk .hd-book-perk-dot { background: var(--green); }

/* Card CTA buttons */
.hd-book-enquire {
  display: flex; align-items: center; justify-content: center; gap: 9px;
  width: 100%; padding: 15px 24px; border-radius: 100px;
  background: var(--gold); color: var(--dark); text-decoration: none;
  font-family: 'Jost', sans-serif; font-size: 13px; font-weight: 700;
  letter-spacing: 0.1em; text-transform: uppercase;
  border: none; cursor: pointer; transition: all var(--tr); margin-bottom: 12px;
}
.hd-book-enquire:hover { background: var(--gold-light); transform: translateY(-2px); box-shadow: 0 8px 24px rgba(201,168,76,0.3); }
.hd-book-wa {
  display: flex; align-items: center; justify-content: center; gap: 9px;
  width: 100%; padding: 14px 24px; border-radius: 100px;
  background: #25D366; color: #fff; text-decoration: none;
  font-family: 'Jost', sans-serif; font-size: 13px; font-weight: 700;
  letter-spacing: 0.08em; text-transform: uppercase;
  transition: all var(--tr);
}
.hd-book-wa:hover { background: #20c45b; transform: translateY(-2px); box-shadow: 0 8px 24px rgba(37,211,102,0.3); }
.hd-book-note {
  text-align: center; margin-top: 14px;
  font-family: 'Jost', sans-serif; font-size: 11px; color: rgba(255,255,255,0.25);
}

/* ===== ENQUIRY MODAL FORM ===== */
.hd-modal-backdrop {
  position: fixed; inset: 0; z-index: 9999;
  background: rgba(0,0,0,0.82); backdrop-filter: blur(8px);
  display: flex; align-items: center; justify-content: center; padding: 20px;
  opacity: 0; pointer-events: none; transition: opacity 0.3s ease;
}
.hd-modal-backdrop.open { opacity: 1; pointer-events: all; }
.hd-modal {
  background: #1c1c1c; border: 1px solid rgba(201,168,76,0.25); border-radius: 20px;
  max-width: 580px; width: 100%; max-height: 90vh; overflow-y: auto;
  padding: 40px 36px; position: relative;
  transform: translateY(24px) scale(0.97);
  transition: transform 0.35s cubic-bezier(0.25,0.46,0.45,0.94);
}
.hd-modal-backdrop.open .hd-modal { transform: translateY(0) scale(1); }
.hd-modal-close {
  position: absolute; top: 16px; right: 16px;
  width: 36px; height: 36px; border-radius: 50%;
  background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.12);
  color: var(--white-60); font-size: 18px; cursor: pointer;
  display: flex; align-items: center; justify-content: center;
  transition: all var(--tr);
}
.hd-modal-close:hover { border-color: var(--gold); color: var(--gold); }
.hd-modal-title {
  font-family: 'Cormorant Garamond', serif; font-size: 1.9rem; font-weight: 500;
  color: #fff; margin-bottom: 6px;
}
.hd-modal-sub {
  font-family: 'Jost', sans-serif; font-size: 13px; color: var(--white-60); font-weight: 300;
  margin-bottom: 28px;
}
.hd-mform { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
.hd-mform-group { display: flex; flex-direction: column; gap: 7px; }
.hd-mform-group.full { grid-column: 1 / -1; }
.hd-mform-group label {
  font-family: 'Jost', sans-serif; font-size: 10px; font-weight: 600;
  letter-spacing: 0.18em; text-transform: uppercase; color: var(--gold);
}
.hd-mform-group input,
.hd-mform-group select,
.hd-mform-group textarea {
  background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1);
  border-radius: 10px; padding: 13px 16px; color: #fff;
  font-family: 'Jost', sans-serif; font-size: 13.5px; font-weight: 300;
  outline: none; width: 100%; box-sizing: border-box;
  -webkit-appearance: none; appearance: none;
  transition: border-color var(--tr), background var(--tr);
}
.hd-mform-group select {
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23c9a84c' d='M6 8L1 3h10z'/%3E%3C/svg%3E");
  background-repeat: no-repeat; background-position: right 14px center;
  background-color: rgba(255,255,255,0.05); padding-right: 36px; cursor: pointer;
}
.hd-mform-group select option { background: #1c1c1c; color: #fff; }
.hd-mform-group textarea { resize: vertical; min-height: 100px; }
.hd-mform-group input::placeholder, .hd-mform-group textarea::placeholder { color: rgba(255,255,255,0.22); }
.hd-mform-group input:focus, .hd-mform-group select:focus, .hd-mform-group textarea:focus {
  border-color: var(--gold); background: rgba(201,168,76,0.05);
}
.hd-mform-submit {
  grid-column: 1 / -1; margin-top: 8px;
  display: flex; align-items: center; justify-content: space-between; gap: 14px; flex-wrap: wrap;
}
.hd-mform-btn {
  display: inline-flex; align-items: center; gap: 9px;
  background: var(--gold); color: var(--dark);
  font-family: 'Jost', sans-serif; font-size: 13px; font-weight: 700;
  letter-spacing: 0.1em; text-transform: uppercase;
  padding: 15px 32px; border-radius: 100px; border: none; cursor: pointer;
  transition: all var(--tr);
}
.hd-mform-btn:hover { background: var(--gold-light); transform: translateY(-2px); }
.hd-mform-note { font-family: 'Jost', sans-serif; font-size: 11.5px; color: rgba(255,255,255,0.28); font-weight: 300; }
.hd-mform-success {
  display: none; text-align: center; padding: 40px 0 10px;
}
.hd-mform-success.show { display: block; }
.hd-mform-success-icon { font-size: 42px; margin-bottom: 14px; display: block; }
.hd-mform-success h3 { font-family: 'Cormorant Garamond', serif; font-size: 1.7rem; color: #fff; margin-bottom: 8px; }
.hd-mform-success p { font-family: 'Jost', sans-serif; font-size: 13.5px; color: var(--white-60); font-weight: 300; }

/* ===== RESPONSIVE ===== */
@media (max-width: 1024px) {
  .hd-layout { grid-template-columns: 1fr; padding: 40px 24px 60px; gap: 40px; }
  .hd-book-card { position: static; }
  .hd-gallery-hero-info { padding: 32px 24px 28px; }
}
@media (max-width: 768px) {
  .hd-back { padding: 18px 20px 0; }
  .hd-gallery-main { height: 55vw; min-height: 280px; }
  .hd-hero-title { font-size: clamp(1.8rem, 6vw, 2.8rem); }
  .hd-quick-facts { grid-template-columns: 1fr 1fr; }
  .hd-nearby-list { grid-template-columns: 1fr; }
  .hd-mform { grid-template-columns: 1fr; }
  .hd-mform-group.full { grid-column: 1; }
  .hd-mform-submit { flex-direction: column; align-items: stretch; }
  .hd-mform-btn { justify-content: center; }
  .hd-modal { padding: 28px 20px; }
}
</style>
@endpush

@php
  $destination  = $hotel->destination?->name ?? 'Unknown Location';
  $images       = $hotel->images ?? collect();
  $imageCount   = $images->count();
  $stars        = min((int) ($hotel->star_rating ?? 3), 5);
  $price        = (float) ($hotel->price_from ?? 0);
  $amenities    = $hotel->amenities ?? collect();
  $cancelDate   = now()->addDays(14)->format('d M Y');
  $ratingScore  = number_format(min(5, max(1, ($stars * 0.92))), 1);
  $ratingLabel  = $stars >= 5 ? 'Exceptional' : ($stars >= 4 ? 'Excellent' : ($stars >= 3 ? 'Very Good' : 'Good'));
  // Room categories — stored as newline-separated text in DB
  $roomCats     = array_filter(array_map('trim', explode("\n", $hotel->room_categories ?? '')));
  // Nearby attractions — stored as newline-separated text in DB
  $nearbyAttr   = array_filter(array_map('trim', explode("\n", $hotel->nearby_attractions ?? '')));
  // Category badge label
  $catLabels = [
    'beach_resort'    => 'Beach Resort',
    'city_luxury'     => 'City Luxury',
    'honeymoon'       => 'Honeymoon',
    'family_friendly' => 'Family Friendly',
  ];
  $catLabel = $catLabels[$hotel->category ?? ''] ?? 'Hotel';
  // WhatsApp enquiry pretext
  $waText = urlencode("Hi TYT Luxe! I'm interested in the hotel: {$hotel->title} ({$destination}). Please share availability and pricing.");
@endphp

@section('content')

<!-- ===================================================
     HERO GALLERY
=================================================== -->
<div class="hd-gallery">
  <div class="hd-gallery-main" id="hdGalleryMain">

    @if($imageCount > 0)
      @foreach($images as $i => $img)
        <div class="hd-gallery-slide {{ $i === 0 ? 'active' : '' }}" data-index="{{ $i }}">
          <img src="{{ Storage::disk('public')->url($img->path) }}"
               alt="{{ $img->alt_text ?: $hotel->title }}" />
        </div>
      @endforeach
    @else
      <div class="hd-gallery-slide active">
        <img src="https://images.unsplash.com/photo-1542314831-068cd1dbfeeb?w=1600&q=85"
             alt="{{ $hotel->title }}" />
      </div>
    @endif

    <div class="hd-gallery-overlay"></div>

    @if($imageCount > 1)
    <button class="hd-gallery-prev" id="hdGallPrev" aria-label="Previous photo">&#8592;</button>
    <button class="hd-gallery-next" id="hdGallNext" aria-label="Next photo">&#8594;</button>
    <span class="hd-gallery-counter" id="hdGallCounter">1 / {{ $imageCount }}</span>
    @endif

    <div class="hd-gallery-hero-info">
      <div>
        <div class="hd-badge-row">
          <span class="hd-badge">{{ $catLabel }}</span>
          @if($hotel->is_featured)
            <span class="hd-badge-outline">Featured</span>
          @endif
          <div class="hd-stars">
            @for($i = 0; $i < $stars; $i++)
              <span>★</span>
            @endfor
          </div>
        </div>
        <h1 class="hd-hero-title">{{ $hotel->title }}</h1>
        <p class="hd-hero-location">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/></svg>
          {{ $destination }}
          @if($hotel->address && $hotel->address !== $destination)
            &nbsp;·&nbsp; {{ $hotel->address }}
          @endif
        </p>
      </div>
    </div>
  </div>


</div>

<!-- ===================================================
     MAIN LAYOUT (2-col: content + sticky booking card)
=================================================== -->
<div class="hd-layout">

  <!-- LEFT COLUMN -->
  <div class="hd-left">

    <!-- Quick Facts -->
    <div class="hd-quick-facts">
      <div class="hd-fact">
        <div class="hd-fact-label">Check-in</div>
        <div class="hd-fact-value">{{ $hotel->check_in_time ?? '2:00 PM' }}</div>
      </div>
      <div class="hd-fact">
        <div class="hd-fact-label">Check-out</div>
        <div class="hd-fact-value">{{ $hotel->check_out_time ?? '11:00 AM' }}</div>
      </div>
      <div class="hd-fact">
        <div class="hd-fact-label">Star Rating</div>
        <div class="hd-fact-value">{{ $stars }}-Star Hotel</div>
      </div>
    </div>

    <!-- About -->
    <div class="hd-section">
      <h2 class="hd-section-title">About This Hotel</h2>
      <p class="hd-desc">{{ $hotel->description }}</p>
    </div>

    <!-- Amenities -->
    @if($amenities->isNotEmpty())
    <div class="hd-section">
      <h2 class="hd-section-title">Amenities & Facilities</h2>
      <div class="hd-amenities">
        @foreach($amenities as $amenity)
          <span class="hd-amenity">
            <span class="hd-amenity-dot"></span>
            {{ $amenity->name }}
          </span>
        @endforeach
      </div>
    </div>
    @endif

    <!-- Room Categories / Types -->
    @if($hotel->roomTypes && $hotel->roomTypes->where('is_active', true)->count() > 0)
    <div class="hd-section">
      <h2 class="hd-section-title">Select Room</h2>
      <div class="hd-room-list">
        @foreach($hotel->roomTypes->where('is_active', true) as $room)
          <div class="hd-room-card">
            @if($room->image_path)
            <div class="hd-room-img">
              <img src="{{ Storage::disk('public')->url($room->image_path) }}" alt="{{ $room->name }}">
            </div>
            @endif
            <div class="hd-room-content">
              <div class="hd-room-info">
                <h3 class="hd-room-title">{{ $room->name }}</h3>
                <div class="hd-room-specs">
                  @if($room->room_size)
                  <span class="hd-room-spec">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16v16H4z M4 9h16 M9 4v16"/></svg>
                    {{ $room->room_size }}
                  </span>
                  @endif
                  @if($room->bed_type)
                  <span class="hd-room-spec">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 4v16M22 4v16M2 8h20M6 4v4M18 4v4"/></svg>
                    {{ $room->bed_type }}
                  </span>
                  @endif
                  <span class="hd-room-spec">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2 M12 3a4 4 0 1 0 0 8 4 4 0 0 0 0-8z"/></svg>
                    {{ $room->occupancy_adults }} Adults @if($room->occupancy_children) , {{ $room->occupancy_children }} Child @endif
                  </span>
                </div>
                @if($room->description)
                  <div class="hd-room-desc-text">{{ $room->description }}</div>
                @endif
                @if($room->inclusions && count($room->inclusions) > 0)
                <div class="hd-room-inc">
                  @foreach($room->inclusions as $inc)
                    <span><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M20 6L9 17l-5-5"/></svg> {{ $inc }}</span>
                  @endforeach
                </div>
                @endif
              </div>
              <div class="hd-room-price">
                <div class="hd-room-price-val">₹{{ number_format($room->price_per_night) }}<small>/ night</small></div>
                <div class="hd-room-cancel @if($room->cancellation_policy == 'free_cancellation') text-green @endif">
                  {{ str_replace('_', ' ', Str::title($room->cancellation_policy)) }}
                </div>
                <button class="hd-room-btn" onclick="document.getElementById('hdEnquireBtn').click()">Select Room</button>
              </div>
            </div>
          </div>
        @endforeach
      </div>
    </div>
    @elseif(isset($roomCats) && count($roomCats) > 0)
    <div class="hd-section">
      <h2 class="hd-section-title">Room Types</h2>
      <div class="hd-room-list" style="display:flex; flex-direction:row; flex-wrap:wrap; gap:10px;">
        @foreach($roomCats as $cat)
          <span style="display: inline-flex; align-items: center; gap: 8px; background: rgba(201,168,76,0.08); border: 1px solid rgba(201,168,76,0.25); padding: 10px 18px; border-radius: 10px; font-family: 'Jost', sans-serif; font-size: 13.5px; color: #fff;">
            <span style="width:7px;height:7px;border-radius:50%;background:var(--gold)"></span> {{ $cat }}
          </span>
        @endforeach
      </div>
    </div>
    @endif

    <!-- Nearby Attractions -->
    @if(count($nearbyAttr) > 0)
    <div class="hd-section">
      <h2 class="hd-section-title">Nearby Attractions</h2>
      <div class="hd-nearby-list">
        @foreach($nearbyAttr as $attr)
          <div class="hd-nearby-item">{{ $attr }}</div>
        @endforeach
      </div>
    </div>
    @endif

  </div>

  <!-- RIGHT COLUMN — Sticky Booking Card -->
  <div class="hd-right">
    <div class="hd-book-card">

      <p class="hd-book-card-loc">{{ $destination }}</p>
      <h2 class="hd-book-card-title">{{ $hotel->title }}</h2>

      <!-- Price -->
      <div class="hd-book-price-row">
        <span class="hd-book-from">Starting from</span>
        @if($price > 0)
          <div class="hd-book-price">₹{{ number_format($price) }}</div>
          <span class="hd-book-price-note">Per night · All taxes included</span>
        @else
          <div class="hd-book-price-req">Price on request</div>
        @endif
      </div>

      <!-- Quick facts -->
      <div class="hd-book-facts">
        <div class="hd-book-fact">
          <div class="hd-book-fact-label">Check-in</div>
          <div class="hd-book-fact-val">{{ $hotel->check_in_time ?? '2:00 PM' }}</div>
        </div>
        <div class="hd-book-fact">
          <div class="hd-book-fact-label">Check-out</div>
          <div class="hd-book-fact-val">{{ $hotel->check_out_time ?? '11:00 AM' }}</div>
        </div>
      </div>

      <!-- Perks -->
      <div class="hd-book-perks">
        <div class="hd-book-perk"><span class="hd-book-perk-dot"></span> Breakfast Included</div>
        <div class="hd-book-perk"><span class="hd-book-perk-dot"></span> Best Rate Guarantee</div>
        <div class="hd-book-perk"><span class="hd-book-perk-dot"></span> 24/7 Dedicated Support</div>
      </div>

      <!-- CTA -->
      <button class="hd-book-enquire" id="hdEnquireBtn">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/></svg>
        Send an Enquiry
      </button>
      <a href="https://wa.me/919875073788?text={{ $waText }}" class="hd-book-wa" target="_blank">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
        Chat on WhatsApp
      </a>
      <p class="hd-book-note">No booking fees · Instant confirmation · Expert support</p>

    </div>
  </div>

</div>

<!-- ===================================================
     ENQUIRY MODAL
=================================================== -->
<div class="hd-modal-backdrop" id="hdEnquiryModal" role="dialog" aria-modal="true" aria-label="Enquiry form">
  <div class="hd-modal">
    <button class="hd-modal-close" id="hdModalClose" aria-label="Close">✕</button>

    <h2 class="hd-modal-title">Enquire About This Hotel</h2>
    <p class="hd-modal-sub">Fill in your details and our travel expert will respond within 2 hours.</p>

    <form class="hd-mform" id="hdEnquiryForm" novalidate>
      <input type="hidden" name="hotel_name" value="{{ $hotel->title }}" />
      <input type="hidden" name="hotel_destination" value="{{ $destination }}" />

      <div class="hd-mform-group">
        <label for="hdName">Your Name *</label>
        <input type="text" id="hdName" name="name" placeholder="e.g. Rahul Sharma" required />
      </div>

      <div class="hd-mform-group">
        <label for="hdPhone">Phone / WhatsApp *</label>
        <input type="tel" id="hdPhone" name="phone" placeholder="e.g. 98765 43210" required />
      </div>

      <div class="hd-mform-group">
        <label for="hdEmail">Email Address</label>
        <input type="email" id="hdEmail" name="email" placeholder="you@email.com" />
      </div>

      <div class="hd-mform-group">
        <label for="hdGuests">No. of Guests</label>
        <select id="hdGuests" name="guests">
          <option value="" disabled selected>Select guests</option>
          <option>1 Guest</option>
          <option>2 Guests</option>
          <option>3 Guests</option>
          <option>4 Guests</option>
          <option>5+ Guests</option>
        </select>
      </div>

      <div class="hd-mform-group">
        <label for="hdCheckin">Check-in Date</label>
        <input type="date" id="hdCheckin" name="checkin" />
      </div>

      <div class="hd-mform-group">
        <label for="hdCheckout">Check-out Date</label>
        <input type="date" id="hdCheckout" name="checkout" />
      </div>

      <div class="hd-mform-group full">
        <label for="hdMessage">Additional Requirements</label>
        <textarea id="hdMessage" name="message" placeholder="Special requests, room preferences, budget, occasions..."></textarea>
      </div>

      <div class="hd-mform-submit">
        <span class="hd-mform-note">No spam · We respond within 2 hours</span>
        <button type="submit" class="hd-mform-btn">
          Send Enquiry
          <svg width="14" height="14" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 8h10M9 4l4 4-4 4"/></svg>
        </button>
      </div>
    </form>

    <div class="hd-mform-success" id="hdMFormSuccess">
      <span class="hd-mform-success-icon">✓</span>
      <h3>Enquiry Sent!</h3>
      <p>Opening WhatsApp… Our team will get back to you shortly with personalised options.</p>
    </div>

  </div>
</div>

@endsection

@push('scripts')
<script>
(function () {

  /* ===== GALLERY SLIDER ===== */
  const slides   = document.querySelectorAll('.hd-gallery-slide');
  const thumbs   = document.querySelectorAll('.hd-thumb');
  const counter  = document.getElementById('hdGallCounter');
  const prevBtn  = document.getElementById('hdGallPrev');
  const nextBtn  = document.getElementById('hdGallNext');
  let current    = 0;

  function hdGallTo(n) {
    slides[current]?.classList.remove('active');
    thumbs[current]?.classList.remove('active');
    current = (n + slides.length) % slides.length;
    slides[current]?.classList.add('active');
    thumbs[current]?.classList.add('active');
    if (counter) counter.textContent = `${current + 1} / ${slides.length}`;
  }

  window.hdGallTo = hdGallTo;

  if (prevBtn) prevBtn.addEventListener('click', () => hdGallTo(current - 1));
  if (nextBtn) nextBtn.addEventListener('click', () => hdGallTo(current + 1));

  // Auto-slide every 5s
  if (slides.length > 1) {
    setInterval(() => hdGallTo(current + 1), 5000);
  }

  /* ===== ENQUIRY MODAL ===== */
  const modal     = document.getElementById('hdEnquiryModal');
  const openBtn   = document.getElementById('hdEnquireBtn');
  const closeBtn  = document.getElementById('hdModalClose');
  const mForm     = document.getElementById('hdEnquiryForm');
  const mSuccess  = document.getElementById('hdMFormSuccess');

  function openModal()  { modal.classList.add('open'); document.body.style.overflow = 'hidden'; }
  function closeModal() { modal.classList.remove('open'); document.body.style.overflow = ''; }

  if (openBtn)  openBtn.addEventListener('click', openModal);
  if (closeBtn) closeBtn.addEventListener('click', closeModal);
  modal?.addEventListener('click', (e) => { if (e.target === modal) closeModal(); });
  document.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeModal(); });

  /* ===== MODAL FORM SUBMIT → WHATSAPP ===== */
  if (mForm) {
    mForm.addEventListener('submit', (e) => {
      e.preventDefault();
      const name    = document.getElementById('hdName').value.trim();
      const phone   = document.getElementById('hdPhone').value.trim();

      if (!name || !phone) {
        alert('Please enter your name and phone number.');
        return;
      }

      const email   = document.getElementById('hdEmail').value.trim();
      const guests  = document.getElementById('hdGuests').value || 'Not specified';
      const checkin = document.getElementById('hdCheckin').value || 'Flexible';
      const checkout= document.getElementById('hdCheckout').value || 'Flexible';
      const message = document.getElementById('hdMessage').value.trim();
      const hotel   = mForm.querySelector('[name="hotel_name"]').value;
      const dest    = mForm.querySelector('[name="hotel_destination"]').value;

      const wa = `Hi TYT Luxe! I'd like to enquire about a stay.\n\nHotel: ${hotel} (${dest})\nName: ${name}\nPhone: ${phone}${email ? '\nEmail: ' + email : ''}\nGuests: ${guests}\nCheck-in: ${checkin}\nCheck-out: ${checkout}${message ? '\nRequirements: ' + message : ''}`;

      window.open('https://wa.me/919875073788?text=' + encodeURIComponent(wa), '_blank');

      mForm.style.display = 'none';
      mSuccess.classList.add('show');
    });
  }

})();
</script>
@endpush
