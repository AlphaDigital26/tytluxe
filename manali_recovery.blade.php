@extends('layouts.frontend')

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
  --white-10: rgba(255,255,255,0.08);
  --green: #4caf82;
  --red: #e05c5c;
  --radius: 14px;
  --transition: 0.35s cubic-bezier(0.25, 0.46, 0.45, 0.94);
}

* { box-sizing: border-box; margin: 0; padding: 0; }

/* ===== HERO ===== */
.mn-hero {
  position: relative; height: 100vh; min-height: 600px;
  overflow: hidden; display: flex; align-items: flex-end;
}
.mn-hero-bg {
  position: absolute; inset: 0;
  background: url('https://images.unsplash.com/photo-1626621341517-bbf3d9990a23?w=1800&q=85') center/cover no-repeat;
  transform: scale(1.05);
  animation: heroZoom 8s ease-out forwards;
}
@keyframes heroZoom { to { transform: scale(1); } }
.mn-hero-overlay {
  position: absolute; inset: 0;
  background: linear-gradient(to top, rgba(13,13,13,1) 0%, rgba(13,13,13,0.55) 50%, rgba(13,13,13,0.15) 100%);
}
.mn-hero-content {
  position: relative; z-index: 2; width: 100%; max-width: 1200px;
  margin: 0 auto; padding: 0 40px 72px;
}
.mn-back-btn {
  display: inline-flex; align-items: center; gap: 8px;
  font-family: 'Jost', sans-serif; font-size: 12px; font-weight: 500;
  letter-spacing: 0.12em; text-transform: uppercase; color: var(--white-60);
  text-decoration: none; margin-bottom: 24px; transition: color var(--transition);
}
.mn-back-btn:hover { color: var(--gold); }
.mn-eyebrow {
  font-family: 'Jost', sans-serif; font-size: 11px; font-weight: 600;
  letter-spacing: 0.25em; text-transform: uppercase; color: var(--gold);
  margin-bottom: 16px; display: flex; align-items: center; gap: 10px;
}
.mn-eyebrow::before { content: ''; display: inline-block; width: 32px; height: 1px; background: var(--gold); }
.mn-hero-title {
  font-family: 'Cormorant Garamond', serif;
  font-size: clamp(3rem, 7vw, 6rem); font-weight: 500;
  color: #fff; line-height: 1.0; margin-bottom: 28px;
}
.mn-hero-title em { font-style: italic; color: var(--gold-light); }
.mn-hero-pills { display: flex; flex-wrap: wrap; gap: 12px; margin-bottom: 32px; }
.mn-pill {
  display: inline-flex; align-items: center; gap: 8px;
  background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2);
  backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px);
  color: #fff; font-family: 'Jost', sans-serif; font-size: 12.5px; font-weight: 500;
  padding: 8px 16px; border-radius: 100px;
}
.mn-pill i { color: var(--gold); font-size: 11px; }
.mn-pill.gold-pill { background: var(--gold); color: var(--dark); border-color: var(--gold); font-weight: 700; }
.mn-pill.gold-pill i { color: var(--dark); }

/* ===== STICKY NAV ===== */
.mn-sticky-nav {
  position: sticky; top: 0; z-index: 100;
  background: rgba(13,13,13,0.95);
  backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px);
  border-bottom: 1px solid var(--gold-dim);
}
.mn-sticky-nav-inner {
  max-width: 1200px; margin: 0 auto; padding: 0 40px;
  display: flex; align-items: center; gap: 0;
  overflow-x: auto; -ms-overflow-style: none; scrollbar-width: none;
}
.mn-sticky-nav-inner::-webkit-scrollbar { display: none; }
.mn-nav-link {
  font-family: 'Jost', sans-serif; font-size: 12px; font-weight: 600;
  letter-spacing: 0.1em; text-transform: uppercase;
  color: var(--white-60); text-decoration: none;
  padding: 20px 22px; display: block;
  border-bottom: 2px solid transparent;
  transition: all var(--transition); white-space: nowrap;
}
.mn-nav-link:hover, .mn-nav-link.active { color: var(--gold); border-bottom-color: var(--gold); }

/* ===== LAYOUT ===== */
.mn-page { background: var(--dark); }
.mn-container { max-width: 1200px; margin: 0 auto; padding: 0 40px; }
.mn-layout { display: grid; grid-template-columns: 1fr 360px; gap: 48px; padding: 64px 0; }

/* ===== SECTIONS ===== */
.mn-section { padding: 64px 0; border-bottom: 1px solid var(--white-10); }
.mn-section:last-child { border-bottom: none; }
.mn-section-label {
  font-family: 'Jost', sans-serif; font-size: 10px; font-weight: 700;
  letter-spacing: 0.28em; text-transform: uppercase; color: var(--gold); margin-bottom: 12px;
}
.mn-section-title {
  font-family: 'Cormorant Garamond', serif;
  font-size: clamp(2rem, 3.5vw, 2.8rem); font-weight: 500;
  color: #fff; line-height: 1.1; margin-bottom: 32px;
}
.mn-section-title em { font-style: italic; color: var(--gold-light); }

/* ===== ABOUT ===== */
.mn-about-text {
  font-family: 'Jost', sans-serif; font-size: 15.5px; line-height: 1.9;
  color: var(--white-60); font-weight: 300;
}
.mn-about-text p { margin-bottom: 16px; }

/* ===== HIGHLIGHTS ===== */
.mn-highlights { display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px; margin-top: 32px; }
.mn-highlight-card {
  background: var(--dark-3); border: 1px solid var(--white-10);
  border-radius: var(--radius); padding: 24px; transition: all var(--transition);
}
.mn-highlight-card:hover { border-color: var(--gold-dim); transform: translateY(-3px); }
.mn-highlight-icon {
  width: 44px; height: 44px; background: var(--gold-dim);
  border-radius: 10px; display: flex; align-items: center; justify-content: center; margin-bottom: 14px;
}
.mn-highlight-icon i { color: var(--gold); font-size: 18px; }
.mn-highlight-title { font-family: 'Jost', sans-serif; font-size: 13px; font-weight: 600; color: #fff; margin-bottom: 6px; }
.mn-highlight-desc { font-family: 'Jost', sans-serif; font-size: 12.5px; color: var(--white-60); font-weight: 300; line-height: 1.6; }

/* ===== TRAVEL DATES ===== */
.mn-dates-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; }
.mn-month-card {
  background: var(--dark-3); border: 1px solid var(--white-10);
  border-radius: var(--radius); padding: 20px;
  transition: all var(--transition);
}
.mn-month-card:hover { border-color: var(--gold-dim); }
.mn-month-name {
  font-family: 'Jost', sans-serif; font-size: 10px; font-weight: 700;
  letter-spacing: 0.2em; text-transform: uppercase; color: var(--gold);
  margin-bottom: 12px;
}
.mn-date-list { list-style: none; display: flex; flex-direction: column; gap: 8px; }
.mn-date-item {
  font-family: 'Jost', sans-serif; font-size: 12.5px; color: var(--white-60);
  font-weight: 300; display: flex; align-items: center; gap: 8px;
}
.mn-date-item::before { content: '→'; color: var(--gold); font-size: 11px; }

/* ===== ITINERARY ===== */
.mn-itinerary { display: flex; flex-direction: column; gap: 0; }
.mn-day-card { position: relative; display: grid; grid-template-columns: 80px 1fr; gap: 0; }
.mn-day-left { display: flex; flex-direction: column; align-items: center; padding-top: 4px; }
.mn-day-num-wrap {
  width: 48px; height: 48px; background: var(--dark-3); border: 2px solid var(--gold);
  border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0; z-index: 1;
}
.mn-day-num-wrap span { font-family: 'Jost', sans-serif; font-size: 11px; font-weight: 700; color: var(--gold); letter-spacing: 0.05em; text-align: center; line-height: 1.2; }
.mn-day-line { width: 2px; background: var(--white-10); flex: 1; margin: 8px 0; min-height: 40px; }
.mn-day-card:last-child .mn-day-line { display: none; }
.mn-day-right { padding: 0 0 48px 24px; }
.mn-day-tag {
  font-family: 'Jost', sans-serif; font-size: 9.5px; font-weight: 700;
  letter-spacing: 0.2em; text-transform: uppercase; color: var(--gold);
  background: var(--gold-dim); padding: 4px 10px; border-radius: 100px;
  display: inline-block; margin-bottom: 10px;
}
.mn-day-title { font-family: 'Cormorant Garamond', serif; font-size: 22px; font-weight: 500; color: #fff; margin-bottom: 16px; line-height: 1.2; }
.mn-day-body { font-family: 'Jost', sans-serif; font-size: 14.5px; color: var(--white-60); line-height: 1.85; font-weight: 300; }
.mn-day-body p { margin-bottom: 12px; }
.mn-day-body p:last-child { margin-bottom: 0; }
.mn-day-highlights { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 14px; }
.mn-day-chip {
  font-family: 'Jost', sans-serif; font-size: 11px; font-weight: 500;
  color: var(--white-80); background: var(--white-10); border: 1px solid rgba(255,255,255,0.1);
  padding: 5px 12px; border-radius: 100px; display: flex; align-items: center; gap: 6px;
}
.mn-day-chip i { color: var(--gold); font-size: 10px; }

/* ===== INCLUSIONS / EXCLUSIONS ===== */
.mn-inc-exc { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; }
.mn-inc-box, .mn-exc-box {
  background: var(--dark-3); border-radius: var(--radius); padding: 28px; border: 1px solid var(--white-10);
}
.mn-inc-box { border-top: 3px solid var(--green); }
.mn-exc-box { border-top: 3px solid var(--red); }
.mn-box-title { font-family: 'Jost', sans-serif; font-size: 11px; font-weight: 700; letter-spacing: 0.15em; text-transform: uppercase; margin-bottom: 20px; }
.mn-inc-box .mn-box-title { color: var(--green); }
.mn-exc-box .mn-box-title { color: var(--red); }
.mn-inc-list, .mn-exc-list { list-style: none; display: flex; flex-direction: column; gap: 12px; }
.mn-inc-list li, .mn-exc-list li {
  display: flex; align-items: flex-start; gap: 10px;
  font-family: 'Jost', sans-serif; font-size: 13.5px; color: var(--white-60); font-weight: 300; line-height: 1.5;
}
.mn-inc-list li i { color: var(--green); font-size: 14px; margin-top: 2px; flex-shrink: 0; }
.mn-exc-list li i { color: var(--red); font-size: 14px; margin-top: 2px; flex-shrink: 0; }

/* ===== CANCELLATION TABLE ===== */
.mn-cancel-table { width: 100%; border-collapse: collapse; }
.mn-cancel-table thead th {
  font-family: 'Jost', sans-serif; font-size: 10px; font-weight: 700;
  letter-spacing: 0.15em; text-transform: uppercase; color: var(--gold);
  padding: 14px 20px; text-align: left; background: var(--gold-dim); border-bottom: 1px solid var(--white-10);
}
.mn-cancel-table tbody td {
  font-family: 'Jost', sans-serif; font-size: 13.5px; color: var(--white-60); font-weight: 300;
  padding: 16px 20px; border-bottom: 1px solid var(--white-10);
}
.mn-cancel-table tbody tr:last-child td { border-bottom: none; }
.mn-cancel-table tbody tr { transition: background var(--transition); }
.mn-cancel-table tbody tr:hover { background: var(--white-10); }
.mn-refund-badge {
  display: inline-flex; align-items: center; gap: 6px;
  font-size: 12px; font-weight: 600; padding: 4px 12px; border-radius: 100px;
}
.mn-refund-badge.full { background: rgba(76,175,130,0.12); color: var(--green); }
.mn-refund-badge.adjust { background: rgba(201,168,76,0.12); color: var(--gold); }
.mn-refund-badge.none { background: rgba(224,92,92,0.12); color: var(--red); }

/* ===== SIDEBAR ===== */
.mn-sidebar-wrap { position: sticky; top: 80px; display: flex; flex-direction: column; gap: 20px; }
.mn-sidebar-card { background: var(--dark-2); border: 1px solid var(--white-10); border-radius: var(--radius); overflow: hidden; }
.mn-price-card-top {
  background: linear-gradient(135deg, var(--dark-3), var(--dark-2));
  padding: 28px 28px 20px; border-bottom: 1px solid var(--gold-dim);
}
.mn-price-label { font-family: 'Jost', sans-serif; font-size: 10px; font-weight: 600; letter-spacing: 0.2em; text-transform: uppercase; color: var(--white-60); margin-bottom: 6px; }
.mn-price-val { font-family: 'Cormorant Garamond', serif; font-size: 3.2rem; font-weight: 500; color: #fff; line-height: 1; margin-bottom: 4px; }
.mn-price-val .curr { font-size: 1.8rem; color: var(--gold); margin-right: 2px; }
.mn-price-pp { font-family: 'Jost', sans-serif; font-size: 11px; color: var(--white-60); }
.mn-price-card-body { padding: 24px 28px; display: flex; flex-direction: column; gap: 12px; }
.mn-price-row { display: flex; justify-content: space-between; align-items: center; font-family: 'Jost', sans-serif; font-size: 13px; color: var(--white-60); }
.mn-price-row strong { color: #fff; font-weight: 500; }
.mn-price-divider { height: 1px; background: var(--white-10); }
.mn-booking-badge {
  background: var(--gold-dim); border: 1px solid var(--gold-dim);
  border-radius: 8px; padding: 12px 16px; text-align: center;
  font-family: 'Jost', sans-serif; font-size: 12.5px; color: var(--gold); font-weight: 500;
}
.mn-btn {
  display: block; width: 100%; text-align: center; background: var(--gold); color: var(--dark);
  font-family: 'Jost', sans-serif; font-size: 12px; font-weight: 700;
  letter-spacing: 0.15em; text-transform: uppercase; padding: 17px 24px;
  border-radius: 100px; text-decoration: none; transition: all var(--transition);
}
.mn-btn:hover { background: var(--gold-light); transform: translateY(-2px); box-shadow: 0 8px 24px rgba(201,168,76,0.3); }
.mn-btn-outline {
  display: block; width: 100%; text-align: center;
  background: transparent; border: 1px solid var(--white-30); color: #fff;
  font-family: 'Jost', sans-serif; font-size: 12px; font-weight: 600;
  letter-spacing: 0.15em; text-transform: uppercase; padding: 17px 24px;
  border-radius: 100px; text-decoration: none; transition: all var(--transition);
}
.mn-btn-outline:hover { border-color: var(--gold); color: var(--gold); }

/* ===== INFO LIST ===== */
.mn-info-list { display: flex; flex-direction: column; gap: 0; }
.mn-info-row { display: flex; align-items: center; gap: 14px; padding: 14px 0; border-bottom: 1px solid var(--white-10); font-family: 'Jost', sans-serif; font-size: 13px; }
.mn-info-row:last-child { border-bottom: none; }
.mn-info-icon { width: 32px; height: 32px; background: var(--gold-dim); border-radius: 8px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.mn-info-icon i { color: var(--gold); font-size: 13px; }
.mn-info-label { color: var(--white-60); font-weight: 300; margin-bottom: 2px; font-size: 11px; text-transform: uppercase; letter-spacing: 0.08em; }
.mn-info-val { color: #fff; font-weight: 500; font-size: 13.5px; }

/* ===== CONTACT ===== */
.mn-contact-card { background: var(--dark-2); border: 1px solid var(--white-10); border-radius: var(--radius); padding: 24px; }
.mn-contact-title { font-family: 'Jost', sans-serif; font-size: 13px; font-weight: 600; color: #fff; margin-bottom: 16px; }
.mn-contact-item { display: flex; align-items: center; gap: 12px; font-family: 'Jost', sans-serif; font-size: 13px; color: var(--white-60); margin-bottom: 12px; }
.mn-contact-item:last-child { margin-bottom: 0; }
.mn-contact-item i { color: var(--gold); width: 16px; }

/* ===== NOTE ===== */
.mn-note {
  background: rgba(201,168,76,0.06); border: 1px solid var(--gold-dim);
  border-left: 3px solid var(--gold); border-radius: var(--radius);
  padding: 20px 24px; font-family: 'Jost', sans-serif; font-size: 13.5px;
  color: var(--white-60); line-height: 1.7; font-weight: 300;
}
.mn-note strong { color: var(--gold); }

/* ===== PAYMENT ===== */
.mn-payment-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; }
.mn-payment-card {
  background: var(--dark-3); border: 1px solid var(--white-10); border-radius: var(--radius);
  padding: 20px; text-align: center; transition: all var(--transition);
}
.mn-payment-card:hover { border-color: var(--gold-dim); transform: translateY(-3px); }
.mn-payment-card i { font-size: 28px; color: var(--gold); margin-bottom: 10px; display: block; }
.mn-payment-card-label { font-family: 'Jost', sans-serif; font-size: 12px; font-weight: 500; color: var(--white-60); }

/* ===== RESPONSIVE ===== */
@media (max-width: 1024px) {
  .mn-layout { grid-template-columns: 1fr; padding: 40px 0; }
  .mn-sidebar-wrap { position: static; }
  .mn-inc-exc { grid-template-columns: 1fr; }
  .mn-highlights { grid-template-columns: 1fr; }
  .mn-dates-grid { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 640px) {
  .mn-hero-content { padding: 0 20px 56px; }
  .mn-container { padding: 0 20px; }
  .mn-hero-title { font-size: 2.4rem; }
  .mn-sticky-nav-inner { padding: 0 20px; }
  .mn-dates-grid { grid-template-columns: 1fr 1fr; }
  .mn-cancel-table thead th, .mn-cancel-table tbody td { padding: 12px; font-size: 12px; }
}
</style>
@endpush

@section('content')

{{-- ===== HERO ===== --}}
<section class="mn-hero" id="overview">
  <div class="mn-hero-bg"></div>
  <div class="mn-hero-overlay"></div>
  <div class="mn-hero-content">
    <a href="{{ route('packages') }}" class="mn-back-btn">
      <i class="fa-solid fa-arrow-left"></i> All Packages
    </a>
    <div class="mn-eyebrow">Himachal Pradesh, India</div>
    <h1 class="mn-hero-title">Manali <em>Sisu</em><br>Kasol</h1>
    <div class="mn-hero-pills">
      <div class="mn-pill"><i class="fa-regular fa-moon"></i> 3 Nights</div>
      <div class="mn-pill"><i class="fa-solid fa-sun"></i> 4 Days</div>
      <div class="mn-pill"><i class="fa-solid fa-bus"></i> Delhi / Chandigarh</div>
      <div class="mn-pill"><i class="fa-solid fa-utensils"></i> 3B + 3D Meals</div>
      <div class="mn-pill"><i class="fa-solid fa-tent"></i> Hotel + Camp</div>
      <div class="mn-pill gold-pill"><i class="fa-solid fa-indian-rupee-sign"></i> Starting ₹9,999</div>
    </div>
  </div>
</section>

{{-- ===== STICKY NAV ===== --}}
<nav class="mn-sticky-nav">
  <div class="mn-sticky-nav-inner">
    <a href="#overview" class="mn-nav-link active">Overview</a>
    <a href="#dates" class="mn-nav-link">Travel Dates</a>
    <a href="#itinerary" class="mn-nav-link">Itinerary</a>
    <a href="#inclusions" class="mn-nav-link">Inclusions</a>
    <a href="#booking" class="mn-nav-link">Booking</a>
    <a href="#cancellation" class="mn-nav-link">Cancellation</a>
    <a href="#contact" class="mn-nav-link">Contact</a>
  </div>
</nav>

{{-- ===== MAIN CONTENT ===== --}}
<div class="mn-page">
  <div class="mn-container">
    <div class="mn-layout">

      {{-- ===== LEFT MAIN ===== --}}
      <div>

        {{-- ABOUT --}}
        <div class="mn-section" id="about">
          <div class="mn-section-label">Discover</div>
          <h2 class="mn-section-title">About <em>Manali</em></h2>
          <div class="mn-about-text">
            <p>Nestled in the heart of Himachal Pradesh, Manali is steeped in mythology and natural beauty. Its name comes from "Manu-Alaya," meaning "abode of Manu," the first man in Hindu mythology. Surrounded by snow-capped peaks, lush valleys, rivers, and dense forests, it's often called the <em>"Valley of the Gods."</em></p>
            <p>Must-visit spots include the <strong style="color:#fff;">Hadimba Devi Temple</strong>, dedicated to a demoness from the Mahabharata, known for its unique pagoda-style architecture. Nearby, the <strong style="color:#fff;">Vashisht Temple</strong> and its hot springs are said to have healing powers. The <strong style="color:#fff;">Manu Temple</strong>, dedicated to sage Manu, is the only one of its kind in India. <strong style="color:#fff;">Brighu Lake</strong>, where sage Bhrigu meditated, is believed to be sacred.</p>
            <p>Adventure lovers flock to treks like <strong style="color:#fff;">Hampta Pass</strong> and scenic spots like <strong style="color:#fff;">Solang Valley</strong> and <strong style="color:#fff;">Rohtang Pass</strong>. The region's rich wildlife includes musk deer, snow leopards, and ibex. Manali's culture shines through its vibrant festivals — Dussehra, Hadimba Devi Fair, Doongri Festival, Baisakhi, Lohri, and the Tibetan Losar Festival.</p>
            <p>Food lovers can enjoy local vegetarian dishes like <em>kaddu ka khatta</em> and <em>sepu vadi</em>, as well as kullu trout and chicken anardana for non-vegetarians. Shopping on Mall Road, the Tibetan Market, and Himachal Emporium is a delight.</p>
          </div>

          <div class="mn-highlights">
            <div class="mn-highlight-card">
              <div class="mn-highlight-icon"><i class="fa-solid fa-place-of-worship"></i></div>
              <div class="mn-highlight-title">Hadimba Devi Temple</div>
              <div class="mn-highlight-desc">Unique 16th-century pagoda-style temple dedicated to Hadimba from the Mahabharata, set amidst deodar forests.</div>
            </div>
            <div class="mn-highlight-card">
              <div class="mn-highlight-icon"><i class="fa-solid fa-mountain-sun"></i></div>
              <div class="mn-highlight-title">Solang Valley</div>
              <div class="mn-highlight-desc">Scenic valley offering mesmerising mountain views and adventure activities like paragliding and zorbing.</div>
            </div>
            <div class="mn-highlight-card">
              <div class="mn-highlight-icon"><i class="fa-solid fa-road"></i></div>
              <div class="mn-highlight-title">Atal Tunnel & Sissu</div>
              <div class="mn-highlight-desc">The world's longest highway tunnel (9.2 km) connects Manali to the beautiful Sissu village in Lahaul Valley.</div>
            </div>
            <div class="mn-highlight-card">
              <div class="mn-highlight-icon"><i class="fa-solid fa-water"></i></div>
              <div class="mn-highlight-title">Kasol Riverside</div>
              <div class="mn-highlight-desc">Bohemian village on the banks of the Parvati River — famous for its cafés, scenic beauty, and laid-back vibe.</div>
            </div>
            <div class="mn-highlight-card">
              <div class="mn-highlight-icon"><i class="fa-solid fa-hands-praying"></i></div>
              <div class="mn-highlight-title">Manikaran Gurudwara</div>
              <div class="mn-highlight-desc">Sacred Sikh pilgrimage site with natural hot springs, where langar (community meal) is cooked in geothermal steam.</div>
            </div>
            <div class="mn-highlight-card">
              <div class="mn-highlight-icon"><i class="fa-solid fa-hot-tub-person"></i></div>
              <div class="mn-highlight-title">Vashisht Hot Springs</div>
              <div class="mn-highlight-desc">Ancient temple complex with natural hot springs believed to have medicinal and healing properties.</div>
            </div>
          </div>
        </div>

        {{-- TRAVEL DATES --}}
        <div class="mn-section" id="dates">
          <div class="mn-section-label">Plan Your Trip</div>
          <h2 class="mn-section-title">Travel <em>Dates 2025</em></h2>
          <div class="mn-dates-grid">
            <div class="mn-month-card">
              <div class="mn-month-name">July</div>
              <ul class="mn-date-list">
                <li class="mn-date-item">04 Jul – 08 Jul</li>
                <li class="mn-date-item">11 Jul – 15 Jul</li>
                <li class="mn-date-item">18 Jul – 22 Jul</li>
                <li class="mn-date-item">25 Jul – 29 Jul</li>
              </ul>
            </div>
            <div class="mn-month-card">
              <div class="mn-month-name">August</div>
              <ul class="mn-date-list">
                <li class="mn-date-item">01 Aug – 05 Aug</li>
                <li class="mn-date-item">08 Aug – 12 Aug</li>
                <li class="mn-date-item">14 Aug – 18 Aug</li>
                <li class="mn-date-item">15 Aug – 19 Aug</li>
                <li class="mn-date-item">22 Aug – 26 Aug</li>
                <li class="mn-date-item">29 Aug – 02 Sep</li>
              </ul>
            </div>
            <div class="mn-month-card">
              <div class="mn-month-name">September</div>
              <ul class="mn-date-list">
                <li class="mn-date-item">05 Sep – 09 Sep</li>
                <li class="mn-date-item">12 Sep – 16 Sep</li>
                <li class="mn-date-item">19 Sep – 23 Sep</li>
                <li class="mn-date-item">26 Sep – 30 Sep</li>
              </ul>
            </div>
            <div class="mn-month-card">
              <div class="mn-month-name">October</div>
              <ul class="mn-date-list">
                <li class="mn-date-item">03 Oct – 07 Oct</li>
                <li class="mn-date-item">10 Oct – 14 Oct</li>
                <li class="mn-date-item">17 Oct – 21 Oct</li>
                <li class="mn-date-item">24 Oct – 28 Oct</li>
                <li class="mn-date-item">31 Oct – 04 Nov</li>
              </ul>
            </div>
            <div class="mn-month-card">
              <div class="mn-month-name">November</div>
              <ul class="mn-date-list">
                <li class="mn-date-item">07 Nov – 11 Nov</li>
                <li class="mn-date-item">14 Nov – 18 Nov</li>
                <li class="mn-date-item">21 Nov – 25 Nov</li>
                <li class="mn-date-item">28 Nov – 02 Dec</li>
              </ul>
            </div>
            <div class="mn-month-card">
              <div class="mn-month-name">December</div>
              <ul class="mn-date-list">
                <li class="mn-date-item">05 Dec – 09 Dec</li>
                <li class="mn-date-item">12 Dec – 16 Dec</li>
                <li class="mn-date-item">19 Dec – 23 Dec</li>
                <li class="mn-date-item">25 Dec – 29 Dec</li>
                <li class="mn-date-item">26 Dec – 30 Dec</li>
              </ul>
            </div>
          </div>
        </div>

        {{-- ITINERARY --}}
        <div class="mn-section" id="itinerary">
          <div class="mn-section-label">Day by Day</div>
          <h2 class="mn-section-title">Detailed <em>Itinerary</em></h2>
          <div class="mn-itinerary">

            {{-- Day 0 --}}
            <div class="mn-day-card">
              <div class="mn-day-left">
                <div class="mn-day-num-wrap"><span>DAY<br>0</span></div>
                <div class="mn-day-line"></div>
              </div>
              <div class="mn-day-right">
                <div class="mn-day-tag">Night Departure</div>
                <div class="mn-day-title">Departure from Delhi & Chandigarh</div>
                <div class="mn-day-body">
                  <p>Board the AC Coach / Tempo Traveller from Delhi or Chandigarh at a decided pickup time. You will receive a small briefing from the trip captain about the journey ahead.</p>
                  <p>Enjoy an overnight journey from Delhi to Manali with <strong style="color:#fff;">2–3 halt stops</strong> for dinner and snacks during the journey.</p>
                </div>
                <div class="mn-day-highlights">
                  <div class="mn-day-chip"><i class="fa-solid fa-bus"></i> AC Coach / Tempo Traveller</div>
                  <div class="mn-day-chip"><i class="fa-solid fa-star"></i> Trip Captain Briefing</div>
                  <div class="mn-day-chip"><i class="fa-solid fa-moon"></i> Overnight Journey</div>
                  <div class="mn-day-chip"><i class="fa-solid fa-utensils"></i> Dinner/Snack Halts</div>
                </div>
              </div>
            </div>

            {{-- Day 1 --}}
            <div class="mn-day-card">
              <div class="mn-day-left">
                <div class="mn-day-num-wrap"><span>DAY<br>1</span></div>
                <div class="mn-day-line"></div>
              </div>
              <div class="mn-day-right">
                <div class="mn-day-tag">Arrival + Sightseeing</div>
                <div class="mn-day-title">Manali Local Sightseeing</div>
                <div class="mn-day-body">
                  <p>Reach Manali in the morning and check into the hotel. Freshen up and relax for a few hours after the overnight journey.</p>
                  <p>Leave for <strong style="color:#fff;">Manali local sightseeing</strong> — visit the iconic <strong style="color:#fff;">Hadimba Devi Temple</strong>, a 16th-century pagoda-style temple dedicated to Hadimba, the demoness from the Mahabharata. Then head to the <strong style="color:#fff;">Vashisht Temple</strong> with its famous natural hot springs said to have healing powers. Stroll along the vibrant <strong style="color:#fff;">Mall Road</strong> for shopping and local flavours.</p>
                  <p>Return to the hotel. <strong style="color:#fff;">Dinner served alongside a bonfire and light music</strong>. Overnight stay at the hotel in Manali.</p>
                </div>
                <div class="mn-day-highlights">
                  <div class="mn-day-chip"><i class="fa-solid fa-hotel"></i> Hotel Check-in</div>
                  <div class="mn-day-chip"><i class="fa-solid fa-place-of-worship"></i> Hadimba Devi Temple</div>
                  <div class="mn-day-chip"><i class="fa-solid fa-hot-tub-person"></i> Vashisht Temple</div>
                  <div class="mn-day-chip"><i class="fa-solid fa-shop"></i> Mall Road</div>
                  <div class="mn-day-chip"><i class="fa-solid fa-fire"></i> Bonfire + Dinner</div>
                </div>
              </div>
            </div>

            {{-- Day 2 --}}
            <div class="mn-day-card">
              <div class="mn-day-left">
                <div class="mn-day-num-wrap"><span>DAY<br>2</span></div>
                <div class="mn-day-line"></div>
              </div>
              <div class="mn-day-right">
                <div class="mn-day-tag">Full Day Excursion</div>
                <div class="mn-day-title">Solang Valley – Sissu – Atal Tunnel</div>
                <div class="mn-day-body">
                  <p>After breakfast, leave for sightseeing in <strong style="color:#fff;">Solang Valley, Sissu, and the Atal Tunnel</strong>.</p>
                  <p>En route, cover <strong style="color:#fff;">Solang Valley</strong> — enjoy the mesmerising views and optional adventure activities in the valley.</p>
                  <p>Later, proceed to <strong style="color:#fff;">Sissu</strong> via the famous <strong style="color:#fff;">Atal Tunnel</strong> (the world's longest highway tunnel at 9.2 km), connecting Manali to Lahaul Valley. Spend some time at Sissu and enjoy the beautiful scenery around.</p>
                  <p>Optional: <strong style="color:#fff;">Visit Rohtang Pass</strong> (on personal expense, subject to permit availability).</p>
                  <p>Return to the hotel. <strong style="color:#fff;">Dinner and overnight stay at Manali</strong>.</p>
                </div>
                <div class="mn-day-highlights">
                  <div class="mn-day-chip"><i class="fa-solid fa-mountain-sun"></i> Solang Valley</div>
                  <div class="mn-day-chip"><i class="fa-solid fa-road"></i> Atal Tunnel (9.2 km)</div>
                  <div class="mn-day-chip"><i class="fa-solid fa-snowflake"></i> Sissu Village</div>
                  <div class="mn-day-chip"><i class="fa-solid fa-ticket"></i> Rohtang (optional, own cost)</div>
                </div>
              </div>
            </div>

            {{-- Day 3 --}}
            <div class="mn-day-card">
              <div class="mn-day-left">
                <div class="mn-day-num-wrap"><span>DAY<br>3</span></div>
                <div class="mn-day-line"></div>
              </div>
              <div class="mn-day-right">
                <div class="mn-day-tag">Travel Day</div>
                <div class="mn-day-title">Leave for Kasol</div>
                <div class="mn-day-body">
                  <p>Wake up early, have your breakfast, and check out of the hotel in Manali.</p>
                  <p>Leave for <strong style="color:#fff;">Kasol</strong> — the bohemian riverside village in the Parvati Valley. En route, cover optional activities like <strong style="color:#fff;">river rafting</strong> and <strong style="color:#fff;">paragliding</strong> (on personal expense).</p>
                  <p>Reach the <strong style="color:#fff;">Kasol camps / cottage</strong> in the evening. Relax by the Parvati River and soak in the vibrant atmosphere.</p>
                  <p><strong style="color:#fff;">Dinner served alongside a bonfire</strong>. Overnight stay in Kasol.</p>
                </div>
                <div class="mn-day-highlights">
                  <div class="mn-day-chip"><i class="fa-solid fa-person-swimming"></i> River Rafting (optional)</div>
                  <div class="mn-day-chip"><i class="fa-solid fa-wind"></i> Paragliding (optional)</div>
                  <div class="mn-day-chip"><i class="fa-solid fa-tent"></i> Kasol Camp/Cottage</div>
                  <div class="mn-day-chip"><i class="fa-solid fa-fire"></i> Bonfire + Dinner</div>
                </div>
              </div>
            </div>

            {{-- Day 4 --}}
            <div class="mn-day-card">
              <div class="mn-day-left">
                <div class="mn-day-num-wrap"><span>DAY<br>4</span></div>
              </div>
              <div class="mn-day-right" style="padding-bottom:0;">
                <div class="mn-day-tag">Departure Day</div>
                <div class="mn-day-title">Kasol – Manikaran & Back to Delhi</div>
                <div class="mn-day-body">
                  <p>Wake up early, have your breakfast, and check out of the accommodation in Kasol.</p>
                  <p>Leave for the <strong style="color:#fff;">Manikaran Gurudwara</strong> — a sacred Sikh pilgrimage site where langar (community meal) is cooked using the energy of natural geothermal hot springs. Enjoy the spiritual experience and the famous langar.</p>
                  <p>Later, enjoy <strong style="color:#fff;">Kasol riverside views</strong> and explore the popular <strong style="color:#fff;">Kasol cafés</strong> for a final farewell to the mountains.</p>
                  <p>In the <strong style="color:#fff;">late evening, leave for Delhi</strong> on an overnight journey.</p>
                </div>
                <div class="mn-day-highlights">
                  <div class="mn-day-chip"><i class="fa-solid fa-hands-praying"></i> Manikaran Gurudwara</div>
                  <div class="mn-day-chip"><i class="fa-solid fa-bowl-food"></i> Langar</div>
                  <div class="mn-day-chip"><i class="fa-solid fa-mug-hot"></i> Kasol Cafés</div>
                  <div class="mn-day-chip"><i class="fa-solid fa-bus"></i> Return to Delhi</div>
                </div>
              </div>
            </div>

          </div>
        </div>

        {{-- INCLUSIONS --}}
        <div class="mn-section" id="inclusions">
          <div class="mn-section-label">What's Covered</div>
          <h2 class="mn-section-title">Inclusions & <em>Exclusions</em></h2>
          <div class="mn-inc-exc">
            <div class="mn-inc-box">
              <div class="mn-box-title"><i class="fa-solid fa-circle-check" style="margin-right:8px;"></i>What's Included</div>
              <ul class="mn-inc-list">
                <li><i class="fa-solid fa-check-circle"></i> Transportation from Delhi to Delhi by AC Coach / Tempo Traveller</li>
                <li><i class="fa-solid fa-check-circle"></i> Accommodation: 2 Nights in Hotel (Manali) + 1 Night Camp (Kasol)</li>
                <li><i class="fa-solid fa-check-circle"></i> Meals: 3 Breakfasts + 3 Dinners</li>
                <li><i class="fa-solid fa-check-circle"></i> Trip Captain present at all times during the trip</li>
                <li><i class="fa-solid fa-check-circle"></i> All required Permits</li>
                <li><i class="fa-solid fa-check-circle"></i> Driver Allowance, Toll Taxes & other State Taxes</li>
              </ul>
            </div>
            <div class="mn-exc-box">
              <div class="mn-box-title"><i class="fa-solid fa-circle-xmark" style="margin-right:8px;"></i>What's Excluded</div>
              <ul class="mn-exc-list">
                <li><i class="fa-solid fa-xmark"></i> Any extra meals apart from those mentioned in inclusions</li>
                <li><i class="fa-solid fa-xmark"></i> Travel Insurance, personal nature items like tips, laundry, etc.</li>
                <li><i class="fa-solid fa-xmark"></i> Entry fees, optional activity costs, room heaters or tickets</li>
                <li><i class="fa-solid fa-xmark"></i> Cost of snow-chained or 4×4 vehicle if needed due to heavy snowfall</li>
                <li><i class="fa-solid fa-xmark"></i> Costs due to natural calamity, weather conditions, roadblocks or landslides</li>
                <li><i class="fa-solid fa-xmark"></i> GST (5%)</li>
                <li><i class="fa-solid fa-xmark"></i> Anything not mentioned in the above inclusions</li>
              </ul>
            </div>
          </div>
        </div>

        {{-- BOOKING & PAYMENT --}}
        <div class="mn-section" id="booking">
          <div class="mn-section-label">Secure Your Spot</div>
          <h2 class="mn-section-title">Booking & <em>Payment</em></h2>

          <div class="mn-note" style="margin-bottom:32px;">
            <strong>Booking Confirmation Policy:</strong> Kindly ensure that a confirmation email is received from PackandExplore. Payments must be made as per the payment policy to confirm the booking. <strong>Booking amount: ₹2,000 per person.</strong> 100% payment must be made before or at the time of departure. Boarding will only be allowed after full payment is received.
          </div>

          <div class="mn-payment-grid">
            <div class="mn-payment-card">
              <i class="fa-solid fa-building-columns"></i>
              <div class="mn-payment-card-label">Bank Transfer</div>
            </div>
            <div class="mn-payment-card">
              <i class="fa-brands fa-google-pay"></i>
              <div class="mn-payment-card-label">GPay / PhonePe</div>
            </div>
            <div class="mn-payment-card">
              <i class="fa-solid fa-mobile-screen-button"></i>
              <div class="mn-payment-card-label">Paytm / UPI</div>
            </div>
          </div>

          <div class="mn-note" style="margin-top:24px;">
            <strong>Bank Details:</strong> Name: PACKTURE EXPLORIFY LLP &nbsp;|&nbsp; A/C: 259074445353 &nbsp;|&nbsp; Bank: IndusInd Bank &nbsp;|&nbsp; IFSC: INDB0002082
          </div>
        </div>

        {{-- CANCELLATION --}}
        <div class="mn-section" id="cancellation">
          <div class="mn-section-label">Cancellation Policy</div>
          <h2 class="mn-section-title">Flexible <em>Cancellations</em></h2>

          <table class="mn-cancel-table">
            <thead>
              <tr>
                <th>Cancellation Period</th>
                <th>Fee Charged</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>60+ days before departure</td>
                <td>0% of trip cost</td>
                <td><span class="mn-refund-badge full"><i class="fa-solid fa-check"></i> Full Refund</span></td>
              </tr>
              <tr>
                <td>30–60 days before departure</td>
                <td>0% of trip cost</td>
                <td><span class="mn-refund-badge full"><i class="fa-solid fa-check"></i> Full Refund</span></td>
              </tr>
              <tr>
                <td>15–30 days before departure</td>
                <td>0% of trip cost</td>
                <td><span class="mn-refund-badge full"><i class="fa-solid fa-check"></i> Full Refund</span></td>
              </tr>
              <tr>
                <td>7–15 days before departure</td>
                <td>0% of trip cost</td>
                <td><span class="mn-refund-badge adjust"><i class="fa-solid fa-rotate"></i> Adjust for Next Trip</span></td>
              </tr>
              <tr>
                <td>0–7 days before departure</td>
                <td>100% of trip cost</td>
                <td><span class="mn-refund-badge none"><i class="fa-solid fa-xmark"></i> Non Refundable</span></td>
              </tr>
            </tbody>
          </table>
        </div>

        {{-- CONTACT --}}
        <div class="mn-section" id="contact" style="border-bottom:none;">
          <div class="mn-section-label">Get in Touch</div>
          <h2 class="mn-section-title">Contact <em>PackandExplore</em></h2>
          <div class="mn-note">
            <strong>Ready to book?</strong> Reach out to us and our team will get back to you promptly. We specialise in thoughtfully designed tour and travel packages, customised to your unique preferences and pace.
          </div>
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-top:24px;">
            <div class="mn-contact-card">
              <div class="mn-contact-title">Contact Details</div>
              <div class="mn-contact-item"><i class="fa-solid fa-phone"></i> +91 79992 68526</div>
              <div class="mn-contact-item"><i class="fa-solid fa-envelope"></i> contact@packandexplore.in</div>
              <div class="mn-contact-item"><i class="fa-solid fa-globe"></i> www.packandexplore.in</div>
              <div class="mn-contact-item"><i class="fa-solid fa-location-dot"></i> 831, Tower C, Bhutani Alphathum, Sector 90, Noida UP-201305</div>
            </div>
            <div class="mn-contact-card">
              <div class="mn-contact-title">Why PackandExplore?</div>
              <div class="mn-contact-item"><i class="fa-solid fa-bolt"></i> Seamless & Exciting Travel</div>
              <div class="mn-contact-item"><i class="fa-solid fa-sliders"></i> Tailored Packages for your pace</div>
              <div class="mn-contact-item"><i class="fa-solid fa-earth-asia"></i> Versatile Destinations</div>
              <div class="mn-contact-item"><i class="fa-solid fa-magnifying-glass"></i> Attention to Every Detail</div>
            </div>
          </div>
        </div>

      </div>

      {{-- ===== SIDEBAR ===== --}}
      <div>
        <div class="mn-sidebar-wrap">

          {{-- Price Card --}}
          <div class="mn-sidebar-card">
            <div class="mn-price-card-top">
              <div class="mn-price-label">Starting From</div>
              <div class="mn-price-val"><span class="curr">₹</span>9,999</div>
              <div class="mn-price-pp">per person (ex. Delhi / Chandigarh)</div>
            </div>
            <div class="mn-price-card-body">
              <div class="mn-price-row">
                <span>Duration</span>
                <strong>3 Nights / 4 Days</strong>
              </div>
              <div class="mn-price-divider"></div>
              <div class="mn-price-row">
                <span>Booking Amount</span>
                <strong>₹2,000 / person</strong>
              </div>
              <div class="mn-price-divider"></div>
              <div class="mn-price-row">
                <span>Departure</span>
                <strong>Delhi / Chandigarh</strong>
              </div>
              <div class="mn-price-divider"></div>
              <div class="mn-price-row">
                <span>Meals</span>
                <strong>3B + 3D</strong>
              </div>
              <div class="mn-price-divider"></div>
              <div class="mn-price-row">
                <span>Stay</span>
                <strong>2N Hotel + 1N Camp</strong>
              </div>
              <div class="mn-booking-badge">
                <i class="fa-solid fa-tag" style="margin-right:6px;"></i> Book now for ₹2,000 to confirm your seat
              </div>
              <a href="https://wa.me/917999268526?text=Hi!%20I'm%20interested%20in%20the%20Manali%20Sisu%20Kasol%203N4D%20package" target="_blank" class="mn-btn">
                <i class="fa-solid fa-calendar-check" style="margin-right:8px;"></i> Book Now
              </a>
              <a href="https://wa.me/917999268526" target="_blank" class="mn-btn-outline">
                <i class="fa-brands fa-whatsapp" style="margin-right:8px;"></i> WhatsApp Us
              </a>
            </div>
          </div>

          {{-- Quick Info --}}
          <div class="mn-sidebar-card">
            <div class="mn-price-card-body">
              <div class="mn-section-label" style="margin-bottom:16px;">Quick Info</div>
              <div class="mn-info-list">
                <div class="mn-info-row">
                  <div class="mn-info-icon"><i class="fa-solid fa-location-dot"></i></div>
                  <div>
                    <div class="mn-info-label">Destinations</div>
                    <div class="mn-info-val">Manali · Sissu · Kasol</div>
                  </div>
                </div>
                <div class="mn-info-row">
                  <div class="mn-info-icon"><i class="fa-solid fa-moon"></i></div>
                  <div>
                    <div class="mn-info-label">Duration</div>
                    <div class="mn-info-val">3 Nights · 4 Days</div>
                  </div>
                </div>
                <div class="mn-info-row">
                  <div class="mn-info-icon"><i class="fa-solid fa-bus"></i></div>
                  <div>
                    <div class="mn-info-label">Transport</div>
                    <div class="mn-info-val">AC Coach / Tempo Traveller</div>
                  </div>
                </div>
                <div class="mn-info-row">
                  <div class="mn-info-icon"><i class="fa-solid fa-hotel"></i></div>
                  <div>
                    <div class="mn-info-label">Stay</div>
                    <div class="mn-info-val">2N Hotel + 1N Camp</div>
                  </div>
                </div>
                <div class="mn-info-row">
                  <div class="mn-info-icon"><i class="fa-solid fa-utensils"></i></div>
                  <div>
                    <div class="mn-info-label">Meals</div>
                    <div class="mn-info-val">3 Breakfast + 3 Dinner</div>
                  </div>
                </div>
                <div class="mn-info-row">
                  <div class="mn-info-icon"><i class="fa-solid fa-user-tie"></i></div>
                  <div>
                    <div class="mn-info-label">Trip Captain</div>
                    <div class="mn-info-val">Present at all times</div>
                  </div>
                </div>
              </div>
            </div>
          </div>

        </div>
      </div>
      {{-- /sidebar --}}

    </div>
  </div>
</div>

<script>
// Active nav highlight on scroll
const sections = document.querySelectorAll('[id]');
const navLinks = document.querySelectorAll('.mn-nav-link');
const observer = new IntersectionObserver((entries) => {
  entries.forEach(entry => {
    if (entry.isIntersecting) {
      navLinks.forEach(l => l.classList.remove('active'));
      const active = document.querySelector(`.mn-nav-link[href="#${entry.target.id}"]`);
      if (active) active.classList.add('active');
    }
  });
}, { rootMargin: '-50% 0px -50% 0px' });
sections.forEach(s => observer.observe(s));
</script>

@endsection
