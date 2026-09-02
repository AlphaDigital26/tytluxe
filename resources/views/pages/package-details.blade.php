@extends('layouts.frontend')

@section('meta_title', ($package->title ?? 'Travel Package') . ' — Book Now | TYT Luxe')
@section('meta_description', 'Book the ' . ($package->title ?? 'luxury travel package') . ' with TYT Luxe. Includes curated itinerary, handpicked accommodations and personalised service. Best price guaranteed.')

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com" />
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;1,400;1,600&family=Jost:wght@300;400;500;600;700&display=swap" rel="stylesheet" />

<style>
:root {
  --gold: #c9a84c;
  --gold-light: #e8c96b;
  --gold-dim: rgba(201,168,76,0.15);
  --dark: #0d0d0d;
  --dark-2: #141414;
  --dark-3: #1a1a1a;
  --white: #ffffff;
  --white-80: rgba(255,255,255,0.8);
  --white-60: rgba(255,255,255,0.6);
  --white-30: rgba(255,255,255,0.3);
  --white-20: rgba(255,255,255,0.2);
  --white-10: rgba(255,255,255,0.08);
  --green: #4caf82;
  --red: #e05c5c;
  --radius: 14px;
  --transition: 0.35s cubic-bezier(0.25, 0.46, 0.45, 0.94);
}
* { box-sizing: border-box; margin: 0; padding: 0; }

/* ===== HERO ===== */
.pd-hero {
  position: relative; height: 100vh; min-height: 600px;
  overflow: hidden; display: flex; align-items: flex-end;
}
.pd-hero-bg {
  position: absolute; inset: 0;
  background-size: cover; background-position: center; background-repeat: no-repeat;
  transform: scale(1.05); animation: heroZoom 8s ease-out forwards;
}
@keyframes heroZoom { to { transform: scale(1); } }
.pd-hero-overlay {
  position: absolute; inset: 0;
  background: linear-gradient(to top, rgba(13,13,13,1) 0%, rgba(13,13,13,0.55) 50%, rgba(13,13,13,0.15) 100%);
}
.pd-hero-content {
  position: relative; z-index: 2;
  width: 100%; max-width: 1200px;
  margin: 0 auto; padding: 0 40px 72px;
}
.pd-back-btn {
  display: inline-flex; align-items: center; gap: 8px;
  font-family: 'Jost', sans-serif; font-size: 12px; font-weight: 500;
  letter-spacing: 0.12em; text-transform: uppercase; color: var(--white-60);
  text-decoration: none; margin-bottom: 24px; transition: color var(--transition);
}
.pd-back-btn:hover { color: var(--gold); }
.pd-eyebrow {
  font-family: 'Jost', sans-serif; font-size: 11px; font-weight: 600;
  letter-spacing: 0.25em; text-transform: uppercase; color: var(--gold);
  margin-bottom: 16px; display: flex; align-items: center; gap: 10px;
}
.pd-eyebrow::before { content: ''; display: inline-block; width: 32px; height: 1px; background: var(--gold); }
.pd-hero-title {
  font-family: 'Cormorant Garamond', serif;
  font-size: clamp(2rem, 5vw, 4rem); font-weight: 500;
  color: #fff; line-height: 1.0; margin-bottom: 28px;
}
.pd-hero-title em { font-style: italic; color: var(--gold-light); }
.pd-hero-pills { display: flex; flex-wrap: wrap; gap: 12px; margin-bottom: 32px; }
.pd-pill {
  display: inline-flex; align-items: center; gap: 8px;
  background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2);
  backdrop-filter: blur(8px); color: #fff;
  font-family: 'Jost', sans-serif; font-size: 12.5px; font-weight: 500;
  padding: 8px 16px; border-radius: 100px;
}
.pd-pill i { color: var(--gold); font-size: 11px; }
.pd-pill.gold-pill { background: var(--gold); color: var(--dark); border-color: var(--gold); font-weight: 700; }
.pd-pill.gold-pill i { color: var(--dark); }

/* ===== STICKY NAV ===== */
.pd-sticky-nav {
  position: sticky; top: 0; z-index: 100;
  background: rgba(13,13,13,0.95);
  backdrop-filter: blur(16px);
  border-bottom: 1px solid var(--gold-dim);
}
.pd-sticky-nav-inner {
  max-width: 1200px; margin: 0 auto; padding: 0 40px;
  display: flex; align-items: center; gap: 0;
  overflow-x: auto; scrollbar-width: none;
}
.pd-sticky-nav-inner::-webkit-scrollbar { display: none; }
.pd-nav-link {
  font-family: 'Jost', sans-serif; font-size: 12px; font-weight: 600;
  letter-spacing: 0.1em; text-transform: uppercase;
  color: var(--white-60); text-decoration: none;
  padding: 20px 22px; display: block;
  border-bottom: 2px solid transparent;
  transition: all var(--transition); white-space: nowrap;
}
.pd-nav-link:hover, .pd-nav-link.active { color: var(--gold); border-bottom-color: var(--gold); }

/* ===== PAGE LAYOUT ===== */
.pd-page { background: var(--dark); }
.pd-container { max-width: 1200px; margin: 0 auto; padding: 0 40px; }
.pd-layout { display: grid; grid-template-columns: 1fr 360px; gap: 48px; padding: 64px 0; }
.pd-layout > div { min-width: 0; }

/* ===== SECTIONS ===== */
.pd-section { padding: 64px 0; border-bottom: 1px solid var(--white-10); }
.pd-section:last-child { border-bottom: none; }
.pd-section-label {
  font-family: 'Jost', sans-serif; font-size: 10px; font-weight: 700;
  letter-spacing: 0.28em; text-transform: uppercase; color: var(--gold); margin-bottom: 12px;
}
.pd-section-title {
  font-family: 'Cormorant Garamond', serif;
  font-size: clamp(2rem, 3.5vw, 2.8rem); font-weight: 500;
  color: #fff; line-height: 1.1; margin-bottom: 32px;
  word-wrap: break-word; overflow-wrap: break-word;
}
.pd-section-title em { font-style: italic; color: var(--gold-light); }

/* ===== ABOUT ===== */
.pd-about-text {
  font-family: 'Jost', sans-serif; font-size: 17px; line-height: 1.9;
  color: var(--white-60); font-weight: 400; text-align: left;
  word-wrap: break-word; overflow-wrap: break-word;
}

/* ===== HIGHLIGHTS ===== */
.pd-highlights { display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px; margin-top: 32px; }
.pd-highlight-card {
  background: var(--dark-3); border: 1px solid var(--white-10);
  border-radius: var(--radius); padding: 24px; transition: all var(--transition);
}
.pd-highlight-card:hover { border-color: var(--gold-dim); transform: translateY(-3px); }
.pd-highlight-icon {
  width: 44px; height: 44px; background: var(--gold-dim);
  border-radius: 10px; display: flex; align-items: center; justify-content: center; margin-bottom: 14px;
}
.pd-highlight-icon i { color: var(--gold); font-size: 18px; }
.pd-highlight-title { font-family: 'Jost', sans-serif; font-size: 13px; font-weight: 600; color: #fff; margin-bottom: 6px; }
.pd-highlight-desc { font-family: 'Jost', sans-serif; font-size: 14.5px; color: var(--white-60); line-height: 1.6; }

/* ===== ITINERARY ===== */
.pd-itinerary { display: flex; flex-direction: column; gap: 0; }
.pd-day-card { position: relative; display: grid; grid-template-columns: 80px 1fr; gap: 0; }
.pd-day-left { display: flex; flex-direction: column; align-items: center; padding-top: 4px; }
.pd-day-num-wrap {
  width: 48px; height: 48px; background: var(--dark-3); border: 2px solid var(--gold);
  border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0; z-index: 1;
}
.pd-day-num-wrap span { font-family: 'Jost', sans-serif; font-size: 11px; font-weight: 700; color: var(--gold); text-align: center; line-height: 1.2; }
.pd-day-line { width: 2px; background: var(--white-10); flex: 1; margin: 8px 0; min-height: 40px; }
.pd-day-card:last-child .pd-day-line { display: none; }
.pd-day-right { padding: 0 0 48px 24px; }
.pd-day-tag {
  font-family: 'Jost', sans-serif; font-size: 9.5px; font-weight: 700;
  letter-spacing: 0.2em; text-transform: uppercase; color: var(--gold);
  background: var(--gold-dim); padding: 4px 10px; border-radius: 100px;
  display: inline-block; margin-bottom: 10px;
}
.pd-day-title { font-family: 'Cormorant Garamond', serif; font-size: 22px; font-weight: 500; color: #fff; margin-bottom: 16px; }
.pd-day-body { font-family: 'Jost', sans-serif; font-size: 16.5px; color: var(--white-60); line-height: 1.85; text-align: justify; }
.pd-day-chips { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 14px; }
.pd-day-chip {
  font-family: 'Jost', sans-serif; font-size: 11px; font-weight: 500;
  color: var(--white-80); background: var(--white-10); border: 1px solid rgba(255,255,255,0.1);
  padding: 5px 12px; border-radius: 100px; display: flex; align-items: center; gap: 6px;
}
.pd-day-chip i { color: var(--gold); font-size: 10px; }

/* ===== INCLUSIONS / EXCLUSIONS ===== */
.pd-inc-exc { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; }
.pd-inc-box, .pd-exc-box { background: var(--dark-3); border-radius: var(--radius); padding: 28px; border: 1px solid var(--white-10); }
.pd-inc-box { border-top: 3px solid var(--green); }
.pd-exc-box { border-top: 3px solid var(--red); }
.pd-box-title { font-family: 'Jost', sans-serif; font-size: 11px; font-weight: 700; letter-spacing: 0.15em; text-transform: uppercase; margin-bottom: 20px; }
.pd-inc-box .pd-box-title { color: var(--green); }
.pd-exc-box .pd-box-title { color: var(--red); }
.pd-inc-list, .pd-exc-list { list-style: none; display: flex; flex-direction: column; gap: 12px; }
.pd-inc-list li, .pd-exc-list li { display: flex; align-items: flex-start; gap: 12px; font-family: 'Jost', sans-serif; font-size: 15px; color: var(--white-60); line-height: 1.5; }
.pd-inc-list li i { color: var(--green); font-size: 14px; margin-top: 2px; flex-shrink: 0; }
.pd-exc-list li i { color: var(--red); font-size: 14px; margin-top: 2px; flex-shrink: 0; }

/* ===== SIDEBAR ===== */
.pd-sidebar-wrap { display: flex; flex-direction: column; gap: 24px; }
.pd-sidebar-card { background: var(--dark-3); border: 1px solid var(--white-10); border-radius: var(--radius); overflow: hidden; }
.pd-price-card-top { background: linear-gradient(135deg, var(--dark-2) 0%, var(--dark-3) 100%); padding: 32px 28px 24px; border-bottom: 1px solid var(--white-10); }
.pd-price-label { font-family: 'Jost', sans-serif; font-size: 10px; font-weight: 700; letter-spacing: 0.2em; text-transform: uppercase; color: var(--gold); margin-bottom: 8px; }
.pd-price-val { font-family: 'Jost', sans-serif; font-size: 2.8rem; font-weight: 200; color: #fff; line-height: 1; }
.pd-price-val .curr { font-size: 1.6rem; color: var(--gold); vertical-align: middle; margin-right: 4px; display: inline-block; margin-top: 0; }
.pd-price-pp { font-family: 'Jost', sans-serif; font-size: 12px; color: var(--white-60); margin-top: 6px; }
.pd-price-card-body { padding: 24px 28px; }
.pd-price-row { display: flex; justify-content: space-between; align-items: center; font-family: 'Jost', sans-serif; font-size: 14px; padding: 10px 0; }
.pd-price-row span { color: var(--white-60); }
.pd-price-row strong { color: #fff; font-weight: 600; }
.pd-price-divider { height: 1px; background: var(--white-10); }
.pd-booking-badge { background: var(--gold-dim); border: 1px solid var(--gold); border-radius: 8px; padding: 12px 16px; font-family: 'Jost', sans-serif; font-size: 13px; color: var(--gold); margin: 16px 0; line-height: 1.4; }
.pd-btn {
  display: flex; align-items: center; justify-content: center; gap: 10px;
  background: var(--gold); color: var(--dark); border: none; border-radius: 10px;
  padding: 16px 24px; font-family: 'Jost', sans-serif; font-size: 14px; font-weight: 700;
  letter-spacing: 0.08em; text-transform: uppercase; cursor: pointer; text-decoration: none;
  transition: all var(--transition); margin-bottom: 12px; width: 100%;
}
.pd-btn:hover { background: var(--gold-light); transform: translateY(-2px); }
.pd-btn-outline {
  display: flex; align-items: center; justify-content: center; gap: 10px;
  background: transparent; color: #fff; border: 1px solid var(--white-30); border-radius: 10px;
  padding: 14px 24px; font-family: 'Jost', sans-serif; font-size: 14px; font-weight: 600;
  cursor: pointer; text-decoration: none; transition: all var(--transition); margin-bottom: 12px; width: 100%;
}
.pd-btn-outline:hover { border-color: var(--gold); color: var(--gold); }
.pd-info-list { display: flex; flex-direction: column; gap: 0; }
.pd-info-row { display: flex; align-items: flex-start; gap: 14px; padding: 14px 0; border-bottom: 1px solid var(--white-10); }
.pd-info-row:last-child { border-bottom: none; }
.pd-info-icon { width: 36px; height: 36px; background: var(--gold-dim); border-radius: 8px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.pd-info-icon i { color: var(--gold); font-size: 14px; }
.pd-info-label { font-family: 'Jost', sans-serif; font-size: 10px; font-weight: 700; letter-spacing: 0.1em; text-transform: uppercase; color: var(--white-60); margin-bottom: 2px; }
.pd-info-val { font-family: 'Jost', sans-serif; font-size: 14px; font-weight: 500; color: #fff; }

/* ===== CONTACT ===== */
.pd-note { font-family: 'Jost', sans-serif; font-size: 16px; color: var(--white-60); line-height: 1.8; margin-bottom: 24px; }
.pd-contact-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
.pd-contact-card { background: var(--dark-3); border: 1px solid var(--white-10); border-radius: var(--radius); padding: 28px; }
.pd-contact-title { font-family: 'Jost', sans-serif; font-size: 11px; font-weight: 700; letter-spacing: 0.15em; text-transform: uppercase; color: var(--gold); margin-bottom: 20px; }
.pd-contact-item { display: flex; align-items: flex-start; gap: 12px; font-family: 'Jost', sans-serif; font-size: 14px; color: var(--white-60); margin-bottom: 14px; line-height: 1.5; }
.pd-contact-item:last-child { margin-bottom: 0; }
.pd-contact-item i { color: var(--gold); font-size: 14px; margin-top: 2px; flex-shrink: 0; }

/* ===== PAYMENT SECTION ===== */
.pd-payment-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-bottom: 24px; }
.pd-payment-card {
  background: var(--dark-3); border: 1px solid var(--white-10);
  border-radius: var(--radius); padding: 20px; text-align: center;
  transition: all var(--transition);
}
.pd-payment-card:hover { border-color: var(--gold-dim); transform: translateY(-3px); }
.pd-payment-card i { font-size: 28px; color: var(--gold); margin-bottom: 10px; display: block; }
.pd-payment-card-label { font-family: 'Jost', sans-serif; font-size: 12px; font-weight: 500; color: var(--white-60); }

/* ===== TRAVEL DATES ===== */
.pd-dates-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; }
.pd-month-card {
  background: var(--dark-3); border: 1px solid var(--white-10);
  border-radius: var(--radius); padding: 20px;
  transition: all var(--transition);
}
.pd-month-card:hover { border-color: var(--gold-dim); }
.pd-month-name {
  font-family: 'Jost', sans-serif; font-size: 10px; font-weight: 700;
  letter-spacing: 0.2em; text-transform: uppercase; color: var(--gold);
  margin-bottom: 12px;
}
.pd-date-list { list-style: none; display: flex; flex-direction: column; gap: 8px; margin: 0; padding: 0; }
.pd-date-item {
  font-family: 'Jost', sans-serif; font-size: 14.5px; color: var(--white-60);
  font-weight: 400; display: flex; align-items: center; gap: 8px; padding: 0; border: none; text-align: left;
}
.pd-date-item::before { content: '→'; color: var(--gold); font-size: 11px; }

/* ===== REVIEWS ===== */
.pd-review-item { border-bottom: 1px solid var(--white-10); padding-bottom: 20px; margin-bottom: 20px; }
.pd-review-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; }
.pd-review-name { font-family: 'Jost', sans-serif; font-size: 15px; font-weight: 600; color: #fff; }
.pd-review-stars { color: var(--gold); }
.pd-review-body { font-family: 'Jost', sans-serif; font-size: 15px; color: var(--white-60); line-height: 1.7; }
.pd-review-form { background: var(--dark-3); border: 1px solid var(--white-10); border-radius: var(--radius); padding: 28px; margin-bottom: 32px; }
.pd-review-form h3 { font-family: 'Cormorant Garamond', serif; font-size: 1.6rem; color: #fff; margin-bottom: 20px; }
.pd-form-group { margin-bottom: 16px; }
.pd-form-label { font-family: 'Jost', sans-serif; font-size: 11px; font-weight: 700; letter-spacing: 0.1em; text-transform: uppercase; color: var(--white-60); margin-bottom: 8px; display: block; }
.pd-form-select, .pd-form-textarea {
  width: 100%; background: var(--dark); border: 1px solid var(--white-10); border-radius: 8px;
  color: #fff; font-family: 'Jost', sans-serif; font-size: 15px; padding: 12px 16px;
  transition: border-color var(--transition);
}
.pd-form-select:focus, .pd-form-textarea:focus { outline: none; border-color: var(--gold); }
.pd-form-textarea { resize: vertical; min-height: 120px; }
.pd-alert { padding: 14px 20px; border-radius: 8px; margin-bottom: 20px; font-family: 'Jost', sans-serif; font-size: 14px; }
.pd-alert-success { background: rgba(76,175,130,0.15); border: 1px solid var(--green); color: var(--green); }
.pd-alert-error { background: rgba(224,92,92,0.15); border: 1px solid var(--red); color: var(--red); }
.pd-alert-info { padding: 14px 20px; border-left: 4px solid var(--gold); background: var(--gold-dim); color: var(--white-60); border-radius: 0 8px 8px 0; font-family: 'Jost', sans-serif; font-size: 14px; margin-bottom: 20px; }
.pd-alert-info a { color: var(--gold); }

/* ===== GALLERY ===== */
.pd-gallery-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 16px; }
.pd-gallery-item { height: 250px; overflow: hidden; border-radius: var(--radius); border: 1px solid var(--white-10); }
.pd-gallery-item img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.4s ease; display: block; }
.pd-gallery-item:hover img { transform: scale(1.06); }

/* ===== RESPONSIVE ===== */
@media (max-width: 900px) {
  .pd-layout { grid-template-columns: 1fr; }
  .pd-sidebar-wrap { position: static; }
  .pd-inc-exc { grid-template-columns: 1fr; }
  .pd-contact-grid { grid-template-columns: 1fr; }
  .pd-highlights { grid-template-columns: 1fr 1fr; }
  .pd-payment-grid { grid-template-columns: repeat(3, 1fr); }
  .pd-dates-grid { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 640px) {
  .pd-hero-content { padding: 0 20px 56px; }
  .pd-container { padding: 0 20px; }
  .pd-hero-title { font-size: 1.8rem; }
  .pd-sticky-nav-inner { padding: 0 20px; }
  .pd-highlights { grid-template-columns: 1fr; }
  .pd-payment-grid { grid-template-columns: 1fr; }
  .pd-dates-grid { grid-template-columns: 1fr; }
}
</style>
@endpush

@section('content')
@php
    $heroImage = $package->hero_image_url;

    $hasBooked = auth()->check()
        ? \App\Models\Booking::where('user_id', auth()->id())
            ->where('vertical', 'package')
            ->where('package_id', $package->id)
            ->where('status', 'confirmed')
            ->exists()
        : false;

    $publishedReviews = isset($package->reviews)
        ? $package->reviews->where('is_published', true)
        : collect();
@endphp

{{-- ===== HERO ===== --}}
<section class="pd-hero" id="overview">
  <div class="pd-hero-bg" style="background-image: url('{{ $heroImage }}');"></div>
  <div class="pd-hero-overlay"></div>
  <div class="pd-hero-content">
    <a href="{{ route('packages') }}" class="pd-back-btn">
      <i class="fa-solid fa-arrow-left"></i> All Packages
    </a>
    @if($package->hero_eyebrow)
      <div class="pd-eyebrow">{{ $package->hero_eyebrow }}</div>
    @elseif($package->destination)
      <div class="pd-eyebrow">{{ $package->destination->name }}</div>
    @endif
    <h1 class="pd-hero-title">{!! nl2br(e($package->title)) !!}</h1>
    @if (session('error'))
        <div style="background: rgba(192, 57, 43, 0.9); color: white; padding: 15px 20px; border-radius: 8px; margin-bottom: 20px; font-family: 'Jost', sans-serif; font-weight: 500;">
            <i class="fas fa-exclamation-triangle" style="margin-right: 8px;"></i> {{ session('error') }}
        </div>
    @endif
    <div class="pd-hero-pills">
      @if($package->duration_nights)
        <div class="pd-pill"><i class="fa-regular fa-moon"></i> {{ $package->duration_nights }} Night{{ $package->duration_nights > 1 ? 's' : '' }}</div>
        <div class="pd-pill"><i class="fa-solid fa-sun"></i> {{ $package->duration_nights + 1 }} Days</div>
      @endif
      @if(!empty($package->departure_from))
        <div class="pd-pill"><i class="fa-solid fa-bus"></i> {{ is_array($package->departure_from) ? implode(', ', $package->departure_from) : $package->departure_from }} Departure</div>
      @endif
      @if($package->meals_info)
        <div class="pd-pill"><i class="fa-solid fa-utensils"></i> {{ $package->meals_info }}</div>
      @endif
      @if($package->price_from)
        <div class="pd-pill gold-pill"><i class="fa-solid fa-indian-rupee-sign"></i> Starting &#8377;{{ number_format($package->price_from, 0) }}</div>
      @endif
    </div>
  </div>
</section>

{{-- ===== STICKY NAV ===== --}}
<nav class="pd-sticky-nav">
  <div class="pd-sticky-nav-inner">
    <a href="#overview" class="pd-nav-link active">Overview</a>
    @if($package->itineraryDays && $package->itineraryDays->count() > 0)
      <a href="#itinerary" class="pd-nav-link">Itinerary</a>
    @endif
    @if($package->departures && $package->departures->count() > 0)
      <a href="#dates" class="pd-nav-link">Dates</a>
    @endif
    @if($package->inclusions && $package->inclusions->count() > 0)
      <a href="#inclusions" class="pd-nav-link">Inclusions</a>
    @endif
    <a href="#booking" class="pd-nav-link">Booking</a>
    <a href="#contact" class="pd-nav-link">Contact</a>
    @if($package->images && $package->images->count() > 0)
      <a href="#gallery" class="pd-nav-link">Gallery</a>
    @endif
  </div>
</nav>

{{-- ===== MAIN CONTENT ===== --}}
<div class="pd-page">
  <div class="pd-container">
    <div class="pd-layout">

      {{-- ===== LEFT / MAIN ===== --}}
      <div>

        {{-- ABOUT --}}
        <div class="pd-section" id="about">
          <div class="pd-section-label">Discover</div>
          <h2 class="pd-section-title">About <em>{{ $package->title }}</em></h2>
          <div class="pd-about-text">
            {!! nl2br(e($package->description)) !!}
          </div>

          @if($package->highlights && $package->highlights->count() > 0)
            <div class="pd-highlights">
              @foreach($package->highlights as $hl)
                <div class="pd-highlight-card">
                  <div class="pd-highlight-icon"><i class="{{ $hl->icon }}"></i></div>
                  <div class="pd-highlight-title">{{ $hl->title }}</div>
                  <div class="pd-highlight-desc">{{ $hl->description }}</div>
                </div>
              @endforeach
            </div>
          @endif
        </div>

        {{-- ITINERARY --}}
        @if($package->itineraryDays && $package->itineraryDays->count() > 0)
          <div class="pd-section" id="itinerary">
            <div class="pd-section-label">Day by Day</div>
            <h2 class="pd-section-title">Your <em>Itinerary</em></h2>
            <div class="pd-itinerary">
              @foreach($package->itineraryDays as $day)
                <div class="pd-day-card">
                  <div class="pd-day-left">
                    <div class="pd-day-num-wrap">
                      <span>{{ $day->day_number == 0 ? 'DEP' : 'DAY ' . $day->day_number }}</span>
                    </div>
                    <div class="pd-day-line"></div>
                  </div>
                  <div class="pd-day-right">
                    <div class="pd-day-tag">{{ $day->day_number == 0 ? 'Departure' : 'Day ' . $day->day_number }}</div>
                    <div class="pd-day-title">{{ $day->title }}</div>
                    <div class="pd-day-body">{{ $day->description }}</div>
                    @if($day->chips && count($day->chips) > 0)
                      <div class="pd-day-chips">
                        @foreach($day->chips as $chip)
                          <div class="pd-day-chip"><i class="fa-solid fa-location-pin"></i> {{ $chip }}</div>
                        @endforeach
                      </div>
                    @endif
                  </div>
                </div>
              @endforeach
            </div>
          </div>
        @endif

        {{-- TRAVEL DATES --}}
        @if($package->departures && $package->departures->count() > 0)
          @php
              // Group departures by Year and Month, e.g., "August 2025"
              $groupedDates = $package->departures->groupBy(function($d) {
                  return \Carbon\Carbon::parse($d->start_date)->format('F Y');
              });
          @endphp
          <div class="pd-section" id="dates">
            <div class="pd-section-label">Plan Your Trip</div>
            <h2 class="pd-section-title">Travel <em>Dates</em></h2>
            <div class="pd-dates-grid">
              @foreach($groupedDates as $monthName => $dates)
                <div class="pd-month-card">
                  <div class="pd-month-name">{{ $monthName }}</div>
                  <ul class="pd-date-list">
                    @foreach($dates as $date)
                      <li class="pd-date-item">
                        {{ \Carbon\Carbon::parse($date->start_date)->format('d M') }} – {{ \Carbon\Carbon::parse($date->end_date)->format('d M') }}
                      </li>
                    @endforeach
                  </ul>
                </div>
              @endforeach
            </div>
          </div>
        @endif

        {{-- INCLUSIONS & EXCLUSIONS --}}
        @if(($package->inclusions && $package->inclusions->count() > 0) || ($package->exclusions && $package->exclusions->count() > 0))
          <div class="pd-section" id="inclusions">
            <div class="pd-section-label">What's Covered</div>
            <h2 class="pd-section-title">Inclusions & <em>Exclusions</em></h2>
            <div class="pd-inc-exc">
              @if($package->inclusions && $package->inclusions->count() > 0)
                <div class="pd-inc-box">
                  <div class="pd-box-title">✓ What's Included</div>
                  <ul class="pd-inc-list">
                    @foreach($package->inclusions as $inc)
                      <li><i class="fa-solid fa-check-circle"></i> <span>{{ $inc->label ?? $inc->name ?? $inc->title }}</span></li>
                    @endforeach
                  </ul>
                </div>
              @endif
              @if($package->exclusions && $package->exclusions->count() > 0)
                <div class="pd-exc-box">
                  <div class="pd-box-title">✗ What's Excluded</div>
                  <ul class="pd-exc-list">
                    @foreach($package->exclusions as $exc)
                      <li><i class="fa-solid fa-times-circle"></i> <span>{{ $exc->label ?? $exc->name ?? $exc->title }}</span></li>
                    @endforeach
                  </ul>
                </div>
              @endif
            </div>
          </div>
        @endif

        {{-- BOOKING NOTES --}}
        <div class="pd-section" id="booking">
          <div class="pd-section-label">How to Book</div>
          <h2 class="pd-section-title">Book Your <em>Spot</em></h2>
          <div class="pd-note">
            To book this package, simply reach out to us on WhatsApp or call us directly.
            @if($package->booking_amount)
              A booking amount of <strong style="color:var(--gold);">&#8377;{{ number_format($package->booking_amount, 0) }} per person</strong> is required to confirm your seat.
            @endif
            Our team will then share the full itinerary and payment details with you.
          </div>
          <div class="pd-payment-grid">
            <div class="pd-payment-card">
              <i class="fa-solid fa-building-columns"></i>
              <div class="pd-payment-card-label">Bank Transfer</div>
            </div>
            <div class="pd-payment-card">
              <i class="fa-brands fa-google-pay"></i>
              <div class="pd-payment-card-label">GPay / PhonePe</div>
            </div>
            <div class="pd-payment-card">
              <i class="fa-solid fa-mobile-screen-button"></i>
              <div class="pd-payment-card-label">Paytm / UPI</div>
            </div>
          </div>
          <div style="display:flex;gap:16px;flex-wrap:wrap;">
            <a href="https://wa.me/919875073788?text=Hi!%20I'm%20interested%20in%20the%20{{ urlencode($package->title) }}%20package.%20Please%20share%20details." target="_blank" class="pd-btn" style="max-width:260px;">
              <i class="fa-solid fa-calendar-check"></i> Book Now
            </a>
            <a href="https://wa.me/919875073788" target="_blank" class="pd-btn-outline" style="max-width:260px;">
              <i class="fa-brands fa-whatsapp"></i> WhatsApp Us
            </a>
          </div>
        </div>

        {{-- CONTACT --}}
        <div class="pd-section" id="contact" style="border-bottom:none;">
          <div class="pd-section-label">Get in Touch</div>
          <h2 class="pd-section-title">Contact <em>TYTLuxe</em></h2>
          <div class="pd-note">
            <strong style="color:#fff;">Ready to book?</strong> Reach out to us and our team will get back to you promptly. We specialise in thoughtfully designed tour and travel packages, customised to your unique preferences and pace.
          </div>
          <div class="pd-contact-grid">
            <div class="pd-contact-card">
              <div class="pd-contact-title">Contact Details</div>
              <div class="pd-contact-item"><i class="fa-solid fa-phone"></i> +91 9875073788</div>
              <div class="pd-contact-item"><i class="fa-solid fa-envelope"></i> takeyourtrip7@gmail.com</div>
              <div class="pd-contact-item"><i class="fa-solid fa-globe"></i> www.tytluxe.in</div>
              <div class="pd-contact-item"><i class="fa-solid fa-location-dot"></i> Cabin No - 9, 4th Floor, Surana Supremus,  Near Safal Square, Vesu, Surat 394518</div>
            </div>
            <div class="pd-contact-card">
              <div class="pd-contact-title">Why TYTLuxe?</div>
              <div class="pd-contact-item"><i class="fa-solid fa-bolt"></i> Seamless &amp; Exciting Travel</div>
              <div class="pd-contact-item"><i class="fa-solid fa-sliders"></i> Tailored Packages for your pace</div>
              <div class="pd-contact-item"><i class="fa-solid fa-earth-asia"></i> Versatile Destinations</div>
              <div class="pd-contact-item"><i class="fa-solid fa-magnifying-glass"></i> Attention to Every Detail</div>
            </div>
          </div>
        </div>

      </div>

      {{-- ===== SIDEBAR ===== --}}
      <div>
        <div class="pd-sidebar-wrap">

          {{-- Price Card --}}
          <div class="pd-sidebar-card">
            <div class="pd-price-card-top">
              <div class="pd-price-label">Starting From</div>
              <div class="pd-price-val"><span class="curr">&#8377;</span>{{ number_format($package->price_from, 0) }} <span style="font-size: 13px; font-weight: 500; color: var(--white-80); margin-left: 6px;">(inclusive of all taxes)</span></div>
              <div class="pd-price-pp">per person{{ !empty($package->departure_from) ? ' (ex. ' . (is_array($package->departure_from) ? implode(', ', $package->departure_from) : $package->departure_from) . ')' : '' }}</div>
            </div>
            <div class="pd-price-card-body">
              @if($package->duration_nights)
                <div class="pd-price-row"><span>Duration</span><strong>{{ $package->duration_nights }} Nights / {{ $package->duration_nights + 1 }} Days</strong></div>
                <div class="pd-price-divider"></div>
              @endif
              @if($package->booking_amount)
                <div class="pd-price-row"><span>Booking Amount</span><strong>&#8377;{{ number_format($package->booking_amount, 0) }} / person</strong></div>
                <div class="pd-price-divider"></div>
              @endif
              @if(!empty($package->departure_from))
                <div class="pd-price-row"><span>Departure</span><strong>{{ is_array($package->departure_from) ? implode(', ', $package->departure_from) : $package->departure_from }}</strong></div>
                <div class="pd-price-divider"></div>
              @endif
              @if($package->meals_info)
                <div class="pd-price-row"><span>Meals</span><strong>{{ $package->meals_info }}</strong></div>
              @endif
              @if($package->booking_amount)
                <div class="pd-booking-badge">
                  <i class="fa-solid fa-tag" style="margin-right:6px;"></i> Book now for &#8377;{{ number_format($package->booking_amount, 0) }} to confirm your seat
                </div>
              @endif
              <a href="https://wa.me/919875073788?text=Hi!%20I'm%20interested%20in%20the%20{{ urlencode($package->title) }}%20package." target="_blank" class="pd-btn">
                <i class="fa-solid fa-calendar-check"></i> Book Now
              </a>
              <a href="https://wa.me/919875073788" target="_blank" class="pd-btn-outline">
                <i class="fa-brands fa-whatsapp"></i> WhatsApp Us
              </a>
                @auth
                  <a href="{{ route('package.download', ['slug' => $package->slug]) }}" class="pd-btn-outline" target="_blank" download>
                    <i class="fa-solid fa-download"></i> Download Itinerary
                  </a>
                @else
                  <button type="button" onclick="document.getElementById('itineraryDownloadModal').style.display='flex'" class="pd-btn-outline">
                    <i class="fa-solid fa-download"></i> Download Itinerary
                  </button>
                @endauth
            </div>
          </div>

          {{-- Quick Info Card --}}
          @if($package->destination || $package->transport_info || $package->stay_info)
            <div class="pd-sidebar-card">
              <div class="pd-price-card-body">
                <div class="pd-section-label" style="margin-bottom:16px;">Quick Info</div>
                <div class="pd-info-list">
                  @if($package->destination)
                    <div class="pd-info-row">
                      <div class="pd-info-icon"><i class="fa-solid fa-location-dot"></i></div>
                      <div><div class="pd-info-label">Destination</div><div class="pd-info-val">{{ $package->destination->name }}</div></div>
                    </div>
                  @endif
                  @if($package->duration_nights)
                    <div class="pd-info-row">
                      <div class="pd-info-icon"><i class="fa-solid fa-moon"></i></div>
                      <div><div class="pd-info-label">Duration</div><div class="pd-info-val">{{ $package->duration_nights }} Nights · {{ $package->duration_nights + 1 }} Days</div></div>
                    </div>
                  @endif
                  @if($package->transport_info)
                    <div class="pd-info-row">
                      <div class="pd-info-icon"><i class="fa-solid fa-bus"></i></div>
                      <div><div class="pd-info-label">Transport</div><div class="pd-info-val">{{ $package->transport_info }}</div></div>
                    </div>
                  @endif
                  @if($package->stay_info)
                    <div class="pd-info-row">
                      <div class="pd-info-icon"><i class="fa-solid fa-hotel"></i></div>
                      <div><div class="pd-info-label">Stay</div><div class="pd-info-val">{{ $package->stay_info }}</div></div>
                    </div>
                  @endif
                  @if($package->meals_info)
                    <div class="pd-info-row">
                      <div class="pd-info-icon"><i class="fa-solid fa-utensils"></i></div>
                      <div><div class="pd-info-label">Meals</div><div class="pd-info-val">{{ $package->meals_info }}</div></div>
                    </div>
                  @endif
                </div>
              </div>
            </div>
          @endif

        </div>
      </div>

    </div>
  </div>
</div>

{{-- ===== GALLERY — Scrapbook Style ===== --}}
@if($package->images && $package->images->count() > 0)
@php
  $galleryItems = $package->images->map(function($img) use ($package) {
    return [
      'image_url' => Str::startsWith($img->image_path, 'http')
                      ? $img->image_path
                      : Storage::disk('public')->url($img->image_path),
      'title'   => $img->alt_text ?? $package->title,
      'caption' => $img->alt_text ?? 'A beautiful memory',
    ];
  });
@endphp
<div id="gallery">
  <x-scrapbook-gallery
    :items="$galleryItems"
    label="Memories"
    heading="Digital"
    em="Journal"
  />
</div>
@endif

@if($publishedReviews->count() > 0 || $hasBooked)
{{-- ===== REVIEWS ===== --}}
<div class="pd-page" style="padding: 80px 0; background: var(--dark-3);">
  <div class="pd-container">
    <div class="pd-section-label" style="text-align:center;margin-bottom:12px;">Experiences</div>
    <h2 class="pd-section-title" style="text-align:center;margin-bottom:10px;">Traveller <em>Reviews</em></h2>

    @if($publishedReviews->count() > 0)
      @php
        $avgRating = number_format($publishedReviews->avg('rating'), 1);
      @endphp
      <div style="text-align:center; margin-bottom:40px; color:var(--white-80); font-family:'Jost',sans-serif;">
        <span style="font-size:2rem; font-weight:bold; color:var(--gold);">★ {{ $avgRating }}</span> / 5
        <br>
        Based on {{ $publishedReviews->count() }} verified reviews
      </div>
    @else
      <div style="margin-bottom:40px;"></div>
    @endif

    @if(session('success'))
      <div class="pd-alert pd-alert-success" style="max-width:700px;margin:0 auto 24px;">{{ session('success') }}</div>
    @endif
    @if(session('error'))
      <div class="pd-alert pd-alert-error" style="max-width:700px;margin:0 auto 24px;">{{ session('error') }}</div>
    @endif

    @if($hasBooked)
      <div class="pd-review-form" style="max-width:700px;margin:0 auto 40px;">
        <h3>Write a Review</h3>
        <form action="{{ route('package.reviews.store', $package->slug) }}" method="POST" enctype="multipart/form-data">
          @csrf
          
          <div class="pd-form-group">
            <label class="pd-form-label">Review Title (Optional)</label>
            <input type="text" name="title" class="pd-form-input" placeholder="e.g. Unforgettable Experience!">
          </div>

          <div style="display:grid; grid-template-columns: 1fr 1fr; gap:20px;">
            <div class="pd-form-group">
              <label class="pd-form-label">Overall Rating *</label>
              <select name="rating" required class="pd-form-select">
                <option value="5">★★★★★ Excellent</option>
                <option value="4">★★★★☆ Very Good</option>
                <option value="3">★★★☆☆ Average</option>
                <option value="2">★★☆☆☆ Poor</option>
                <option value="1">★☆☆☆☆ Terrible</option>
              </select>
            </div>
            <div class="pd-form-group">
              <label class="pd-form-label">Guide/Service (Optional)</label>
              <select name="rating_guide" class="pd-form-select">
                <option value="">-- Select --</option>
                <option value="5">5 - Excellent</option>
                <option value="4">4 - Good</option>
                <option value="3">3 - Average</option>
                <option value="2">2 - Poor</option>
                <option value="1">1 - Terrible</option>
              </select>
            </div>
            <div class="pd-form-group">
              <label class="pd-form-label">Accommodation (Optional)</label>
              <select name="rating_accommodation" class="pd-form-select">
                <option value="">-- Select --</option>
                <option value="5">5 - Excellent</option>
                <option value="4">4 - Good</option>
                <option value="3">3 - Average</option>
                <option value="2">2 - Poor</option>
                <option value="1">1 - Terrible</option>
              </select>
            </div>
            <div class="pd-form-group">
              <label class="pd-form-label">Itinerary (Optional)</label>
              <select name="rating_itinerary" class="pd-form-select">
                <option value="">-- Select --</option>
                <option value="5">5 - Excellent</option>
                <option value="4">4 - Good</option>
                <option value="3">3 - Average</option>
                <option value="2">2 - Poor</option>
                <option value="1">1 - Terrible</option>
              </select>
            </div>
          </div>

          <div class="pd-form-group">
            <label class="pd-form-label">Your Review *</label>
            <textarea name="body" rows="4" required class="pd-form-textarea" placeholder="Share your experience..."></textarea>
          </div>
          
          <div class="pd-form-group">
            <label class="pd-form-label">Upload Photos (Optional, Max 2MB each)</label>
            <input type="file" name="images[]" multiple accept="image/*" class="pd-form-input" style="padding: 10px; background: rgba(255,255,255,0.05); color: #fff;">
          </div>

          <button type="submit" class="pd-btn" style="max-width:220px;">Submit Review</button>
        </form>
      </div>
    @endif

    @if($publishedReviews->count() > 0)
      <div style="max-width:700px;margin:0 auto;">
        @foreach($publishedReviews->sortByDesc('is_featured') as $review)
          <div class="pd-review-item" style="border: 1px solid var(--border-color); padding: 24px; border-radius: 8px; margin-bottom: 24px; background: rgba(255,255,255,0.02); position: relative;">
            <div class="pd-review-header" style="margin-bottom: 12px;">
              <span class="pd-review-name" style="font-size: 1.1rem; font-weight: 500;">{{ $review->author_name }}</span>
              <span class="pd-review-stars" style="color: var(--gold);">
                @for($i=1; $i<=5; $i++)
                  <i class="fa-{{ $i <= $review->rating ? 'solid' : 'regular' }} fa-star"></i>
                @endfor
              </span>
            </div>
            
            @if($review->title)
                <h4 style="color: #fff; margin-bottom: 10px; font-size: 1.2rem;">{{ $review->title }}</h4>
            @endif

            <div class="pd-review-body-container" style="margin-bottom: 15px;">
                <div class="pd-review-text" style="color: var(--white-80); line-height: 1.6; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; transition: all 0.3s ease;">
                    {{ $review->body }}
                </div>
                @if(strlen($review->body) > 150)
                    <span onclick="toggleReviewText(this)" style="color: var(--gold); cursor: pointer; font-size: 0.9rem; font-weight: 500; display: inline-block; margin-top: 5px;">Read more</span>
                @endif
            </div>
            
            @if($review->images && is_array($review->images) && count($review->images) > 0)
                <div style="display: flex; gap: 10px; margin-bottom: 15px; flex-wrap: wrap;">
                    @foreach($review->images as $img)
                        <img src="{{ Storage::disk('public')->url($img) }}" alt="Review Photo" style="width: 120px; height: 90px; object-fit: cover; border-radius: 4px; cursor: pointer; transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'" onclick="openReviewImageModal(this.src)">
                    @endforeach
                </div>
            @endif

            @if($review->admin_reply)
                <div style="background: rgba(0,0,0,0.3); border-left: 3px solid var(--gold); padding: 15px; margin-top: 15px; border-radius: 4px;">
                    <strong style="color: var(--gold); display: block; margin-bottom: 5px; font-size: 0.9rem;">Response from TYT Luxe</strong>
                    <p style="color: var(--white-80); margin: 0; font-size: 0.95rem;">{{ $review->admin_reply }}</p>
                </div>
            @endif
          </div>
        @endforeach
      </div>
    @endif
  </div>
</div>
@endif

{{-- Guest Download Modal --}}
<div id="itineraryDownloadModal" style="display: none; position: fixed; z-index: 10000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.8); align-items: center; justify-content: center;">
  <div style="background: var(--dark-3); padding: 40px; border-radius: 8px; max-width: 450px; width: 90%; border: 1px solid var(--gold); position: relative;">
    <span onclick="closeItineraryModal()" style="position: absolute; top: 15px; right: 25px; font-size: 28px; cursor: pointer; color: var(--gold);">&times;</span>
    <h3 style="color: var(--gold); margin-bottom: 15px; font-family: 'Cinzel', serif; font-size: 1.5rem;">Download Itinerary</h3>
    <p style="color: var(--white-80); margin-bottom: 25px; font-size: 0.95rem; line-height: 1.5;">Please enter your details to download the detailed itinerary for this package.</p>

    {{-- Inline feedback message --}}
    <div id="itineraryModalMsg" style="display:none; padding: 12px 16px; border-radius: 6px; margin-bottom: 16px; font-family: 'Jost', sans-serif; font-size: 14px;"></div>

    <form id="itineraryDownloadForm">
      @csrf
      <input type="text" name="name" id="idf_name" required placeholder="Your Name"
        style="width: 100%; padding: 14px; margin-bottom: 12px; background: rgba(255,255,255,0.05); border: 1px solid var(--white-20); color: #fff; border-radius: 4px; font-family: 'Jost', sans-serif;">
      <input type="tel" name="phone" id="idf_phone" required placeholder="Your Phone Number"
        style="width: 100%; padding: 14px; margin-bottom: 12px; background: rgba(255,255,255,0.05); border: 1px solid var(--white-20); color: #fff; border-radius: 4px; font-family: 'Jost', sans-serif;">
      <input type="email" name="email" id="idf_email" required placeholder="Your Email Address"
        style="width: 100%; padding: 14px; margin-bottom: 20px; background: rgba(255,255,255,0.05); border: 1px solid var(--white-20); color: #fff; border-radius: 4px; font-family: 'Jost', sans-serif;">
      <button type="submit" id="idf_submit" class="pd-btn" style="width: 100%; cursor: pointer;">
        <i class="fa-solid fa-download"></i> <span id="idf_btn_text">Download Now</span>
      </button>
    </form>
  </div>
</div>

<script>
function closeItineraryModal() {
  document.getElementById('itineraryDownloadModal').style.display = 'none';
  // Reset feedback when closing
  var msg = document.getElementById('itineraryModalMsg');
  msg.style.display = 'none';
  msg.textContent = '';
}

// Close when clicking the dark backdrop
document.getElementById('itineraryDownloadModal').addEventListener('click', function(e) {
  if (e.target === this) closeItineraryModal();
});

document.getElementById('itineraryDownloadForm').addEventListener('submit', function(e) {
  e.preventDefault();

  var form    = this;
  var btn     = document.getElementById('idf_submit');
  var btnText = document.getElementById('idf_btn_text');
  var msg     = document.getElementById('itineraryModalMsg');

  // Show loading state
  btn.disabled = true;
  btnText.textContent = 'Downloading PDF…';
  msg.style.display = 'none';

  var csrfToken  = form.querySelector('input[name="_token"]').value;
  var formData   = new FormData(form);

  fetch('{{ route('package.download.guest', $package->slug) }}', {
    method: 'POST',
    headers: {
      'X-CSRF-TOKEN': csrfToken,
      'X-Requested-With': 'XMLHttpRequest', // Lets Laravel detect this as an AJAX request
    },
    body: formData,
  })
  .then(function(response) {
    if (response.status === 429) {
      throw new Error('Too many requests. Please wait a minute and try again.');
    }
    if (!response.ok) {
      return response.text().then(function() {
        throw new Error('PDF generation failed. Please try again shortly.');
      });
    }
    // Guard: if the response is not a PDF (e.g. an HTML redirect/error page),
    // reject it so we never save a corrupt file to disk.
    var contentType = response.headers.get('content-type') || '';
    if (!contentType.includes('pdf')) {
      throw new Error('PDF generation failed. Please try again shortly.');
    }
    return response.blob();
  })
  .then(function(blob) {
    // Trigger browser download of the PDF blob
    var url      = URL.createObjectURL(blob);
    var anchor   = document.createElement('a');
    anchor.href  = url;
    anchor.download = '{{ $package->slug }}-itinerary.pdf';
    document.body.appendChild(anchor);
    anchor.click();
    document.body.removeChild(anchor);
    URL.revokeObjectURL(url);

    // Show success, close modal after 1.5s
    msg.style.cssText = 'display:block; background:rgba(76,175,130,0.15); border:1px solid #4caf82; color:#4caf82;';
    msg.textContent = '✓ Your itinerary is downloading!';
    setTimeout(closeItineraryModal, 1500);
  })
  .catch(function(error) {
    msg.style.cssText = 'display:block; background:rgba(224,92,92,0.15); border:1px solid #e05c5c; color:#e05c5c;';
    msg.textContent = '✗ ' + error.message;
  })
  .finally(function() {
    btn.disabled = false;
    btnText.textContent = 'Download Now';
  });
});
</script>

{{-- Image Modal --}}
<div id="reviewImageModal" style="display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.9); align-items: center; justify-content: center;">
  <span onclick="closeReviewImageModal()" style="position: absolute; top: 20px; right: 35px; color: #f1f1f1; font-size: 40px; font-weight: bold; cursor: pointer;">&times;</span>
  <img id="reviewModalImage" style="max-width: 90%; max-height: 90%; object-fit: contain; border-radius: 8px; box-shadow: 0 4px 20px rgba(0,0,0,0.5);">
</div>

<script>
function toggleReviewText(btn) {
  const textDiv = btn.previousElementSibling;
  if (textDiv.style.webkitLineClamp === 'unset') {
    textDiv.style.webkitLineClamp = '2';
    btn.innerText = 'Read more';
  } else {
    textDiv.style.webkitLineClamp = 'unset';
    btn.innerText = 'Read less';
  }
}

function openReviewImageModal(src) {
  const modal = document.getElementById('reviewImageModal');
  const modalImg = document.getElementById('reviewModalImage');
  modal.style.display = "flex";
  modalImg.src = src;
}

function closeReviewImageModal() {
  document.getElementById('reviewImageModal').style.display = "none";
}

// Close modal when clicking outside the image
document.getElementById('reviewImageModal').addEventListener('click', function(e) {
  if (e.target === this) {
    closeReviewImageModal();
  }
});

// Active nav highlight on scroll
const pdSections = document.querySelectorAll('[id]');
const pdNavLinks = document.querySelectorAll('.pd-nav-link');
const pdObserver = new IntersectionObserver((entries) => {
  entries.forEach(entry => {
    if (entry.isIntersecting) {
      pdNavLinks.forEach(l => l.classList.remove('active'));
      const active = document.querySelector(`.pd-nav-link[href="#${entry.target.id}"]`);
      if (active) active.classList.add('active');
    }
  });
}, { rootMargin: '-50% 0px -50% 0px' });
pdSections.forEach(s => pdObserver.observe(s));
</script>

{{-- GSAP carousel is auto-initialised from app.js (DOMContentLoaded listener) --}}

@endsection
