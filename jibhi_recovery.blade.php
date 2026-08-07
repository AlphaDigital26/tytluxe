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
  --dark-4: #111111;
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
.jb-hero {
  position: relative;
  height: 100vh;
  min-height: 600px;
  overflow: hidden;
  display: flex;
  align-items: flex-end;
}
.jb-hero-bg {
  position: absolute;
  inset: 0;
  background: url('https://images.unsplash.com/photo-1585409677983-0f6c41ca9c3b?w=1800&q=85') center/cover no-repeat;
  transform: scale(1.05);
  animation: heroZoom 8s ease-out forwards;
}
@keyframes heroZoom { to { transform: scale(1); } }
.jb-hero-overlay {
  position: absolute; inset: 0;
  background: linear-gradient(to top, rgba(13,13,13,1) 0%, rgba(13,13,13,0.55) 50%, rgba(13,13,13,0.15) 100%);
}
.jb-hero-content {
  position: relative; z-index: 2;
  width: 100%; max-width: 1200px;
  margin: 0 auto;
  padding: 0 40px 72px;
}
.jb-back-btn {
  display: inline-flex; align-items: center; gap: 8px;
  font-family: 'Jost', sans-serif; font-size: 12px; font-weight: 500;
  letter-spacing: 0.12em; text-transform: uppercase; color: var(--white-60);
  text-decoration: none; margin-bottom: 24px;
  transition: color var(--transition);
}
.jb-back-btn:hover { color: var(--gold); }
.jb-eyebrow {
  font-family: 'Jost', sans-serif; font-size: 11px; font-weight: 600;
  letter-spacing: 0.25em; text-transform: uppercase; color: var(--gold);
  margin-bottom: 16px; display: flex; align-items: center; gap: 10px;
}
.jb-eyebrow::before {
  content: ''; display: inline-block; width: 32px; height: 1px; background: var(--gold);
}
.jb-hero-title {
  font-family: 'Cormorant Garamond', serif;
  font-size: clamp(3rem, 7vw, 6rem); font-weight: 500;
  color: #fff; line-height: 1.0; margin-bottom: 28px;
}
.jb-hero-title em { font-style: italic; color: var(--gold-light); }
.jb-hero-pills {
  display: flex; flex-wrap: wrap; gap: 12px; margin-bottom: 32px;
}
.jb-pill {
  display: inline-flex; align-items: center; gap: 8px;
  background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2);
  backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px);
  color: #fff; font-family: 'Jost', sans-serif; font-size: 12.5px; font-weight: 500;
  padding: 8px 16px; border-radius: 100px;
}
.jb-pill i { color: var(--gold); font-size: 11px; }
.jb-pill.gold-pill { background: var(--gold); color: var(--dark); border-color: var(--gold); font-weight: 700; }
.jb-pill.gold-pill i { color: var(--dark); }

/* ===== STICKY NAV ===== */
.jb-sticky-nav {
  position: sticky; top: 0; z-index: 100;
  background: rgba(13,13,13,0.95);
  backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px);
  border-bottom: 1px solid var(--gold-dim);
}
.jb-sticky-nav-inner {
  max-width: 1200px; margin: 0 auto;
  padding: 0 40px;
  display: flex; align-items: center; gap: 0;
  overflow-x: auto; -ms-overflow-style: none; scrollbar-width: none;
}
.jb-sticky-nav-inner::-webkit-scrollbar { display: none; }
.jb-nav-link {
  font-family: 'Jost', sans-serif; font-size: 12px; font-weight: 600;
  letter-spacing: 0.1em; text-transform: uppercase;
  color: var(--white-60); text-decoration: none;
  padding: 20px 22px; display: block;
  border-bottom: 2px solid transparent;
  transition: all var(--transition); white-space: nowrap;
}
.jb-nav-link:hover, .jb-nav-link.active { color: var(--gold); border-bottom-color: var(--gold); }

/* ===== PAGE LAYOUT ===== */
.jb-page { background: var(--dark); }
.jb-container { max-width: 1200px; margin: 0 auto; padding: 0 40px; }
.jb-layout { display: grid; grid-template-columns: 1fr 360px; gap: 48px; padding: 64px 0; }

/* ===== SECTIONS ===== */
.jb-section { padding: 64px 0; border-bottom: 1px solid var(--white-10); }
.jb-section:last-child { border-bottom: none; }
.jb-section-label {
  font-family: 'Jost', sans-serif; font-size: 10px; font-weight: 700;
  letter-spacing: 0.28em; text-transform: uppercase; color: var(--gold);
  margin-bottom: 12px;
}
.jb-section-title {
  font-family: 'Cormorant Garamond', serif;
  font-size: clamp(2rem, 3.5vw, 2.8rem); font-weight: 500;
  color: #fff; line-height: 1.1; margin-bottom: 32px;
}
.jb-section-title em { font-style: italic; color: var(--gold-light); }

/* ===== ABOUT ===== */
.jb-about-text {
  font-family: 'Jost', sans-serif; font-size: 15.5px; line-height: 1.9;
  color: var(--white-60); font-weight: 300;
}
.jb-about-text p { margin-bottom: 16px; }

/* ===== HIGHLIGHTS GRID ===== */
.jb-highlights {
  display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px;
  margin-top: 32px;
}
.jb-highlight-card {
  background: var(--dark-3); border: 1px solid var(--white-10);
  border-radius: var(--radius); padding: 24px;
  transition: all var(--transition);
}
.jb-highlight-card:hover {
  border-color: var(--gold-dim);
  transform: translateY(-3px);
}
.jb-highlight-icon {
  width: 44px; height: 44px; background: var(--gold-dim);
  border-radius: 10px; display: flex; align-items: center; justify-content: center;
  margin-bottom: 14px;
}
.jb-highlight-icon i { color: var(--gold); font-size: 18px; }
.jb-highlight-title {
  font-family: 'Jost', sans-serif; font-size: 13px; font-weight: 600;
  color: #fff; margin-bottom: 6px;
}
.jb-highlight-desc {
  font-family: 'Jost', sans-serif; font-size: 12.5px;
  color: var(--white-60); font-weight: 300; line-height: 1.6;
}

/* ===== ITINERARY ===== */
.jb-itinerary { display: flex; flex-direction: column; gap: 0; }
.jb-day-card {
  position: relative;
  display: grid; grid-template-columns: 80px 1fr;
  gap: 0;
}
.jb-day-left {
  display: flex; flex-direction: column; align-items: center;
  padding-top: 4px;
}
.jb-day-num-wrap {
  width: 48px; height: 48px;
  background: var(--dark-3); border: 2px solid var(--gold);
  border-radius: 50%; display: flex; align-items: center; justify-content: center;
  flex-shrink: 0; z-index: 1;
}
.jb-day-num-wrap span {
  font-family: 'Jost', sans-serif; font-size: 11px; font-weight: 700;
  color: var(--gold); letter-spacing: 0.05em; text-align: center; line-height: 1.2;
}
.jb-day-line {
  width: 2px; background: var(--white-10);
  flex: 1; margin: 8px 0;
  min-height: 40px;
}
.jb-day-card:last-child .jb-day-line { display: none; }
.jb-day-right {
  padding: 0 0 48px 24px;
}
.jb-day-tag {
  font-family: 'Jost', sans-serif; font-size: 9.5px; font-weight: 700;
  letter-spacing: 0.2em; text-transform: uppercase; color: var(--gold);
  background: var(--gold-dim); padding: 4px 10px; border-radius: 100px;
  display: inline-block; margin-bottom: 10px;
}
.jb-day-title {
  font-family: 'Cormorant Garamond', serif; font-size: 22px; font-weight: 500;
  color: #fff; margin-bottom: 16px; line-height: 1.2;
}
.jb-day-body {
  font-family: 'Jost', sans-serif; font-size: 14.5px;
  color: var(--white-60); line-height: 1.85; font-weight: 300;
}
.jb-day-body p { margin-bottom: 12px; }
.jb-day-body p:last-child { margin-bottom: 0; }
.jb-day-highlights {
  display: flex; flex-wrap: wrap; gap: 8px; margin-top: 14px;
}
.jb-day-chip {
  font-family: 'Jost', sans-serif; font-size: 11px; font-weight: 500;
  color: var(--white-80); background: var(--white-10);
  border: 1px solid rgba(255,255,255,0.1);
  padding: 5px 12px; border-radius: 100px;
  display: flex; align-items: center; gap: 6px;
}
.jb-day-chip i { color: var(--gold); font-size: 10px; }

/* ===== INCLUSIONS / EXCLUSIONS ===== */
.jb-inc-exc {
  display: grid; grid-template-columns: 1fr 1fr; gap: 24px;
}
.jb-inc-box, .jb-exc-box {
  background: var(--dark-3); border-radius: var(--radius);
  padding: 28px; border: 1px solid var(--white-10);
}
.jb-inc-box { border-top: 3px solid var(--green); }
.jb-exc-box { border-top: 3px solid var(--red); }
.jb-box-title {
  font-family: 'Jost', sans-serif; font-size: 11px; font-weight: 700;
  letter-spacing: 0.15em; text-transform: uppercase;
  margin-bottom: 20px;
}
.jb-inc-box .jb-box-title { color: var(--green); }
.jb-exc-box .jb-box-title { color: var(--red); }
.jb-inc-list, .jb-exc-list { list-style: none; display: flex; flex-direction: column; gap: 12px; }
.jb-inc-list li, .jb-exc-list li {
  display: flex; align-items: flex-start; gap: 10px;
  font-family: 'Jost', sans-serif; font-size: 13.5px;
  color: var(--white-60); font-weight: 300; line-height: 1.5;
}
.jb-inc-list li i { color: var(--green); font-size: 14px; margin-top: 2px; flex-shrink: 0; }
.jb-exc-list li i { color: var(--red); font-size: 14px; margin-top: 2px; flex-shrink: 0; }

/* ===== CANCELLATION TABLE ===== */
.jb-cancel-table { width: 100%; border-collapse: collapse; }
.jb-cancel-table thead th {
  font-family: 'Jost', sans-serif; font-size: 10px; font-weight: 700;
  letter-spacing: 0.15em; text-transform: uppercase; color: var(--gold);
  padding: 14px 20px; text-align: left;
  background: var(--gold-dim); border-bottom: 1px solid var(--white-10);
}
.jb-cancel-table tbody td {
  font-family: 'Jost', sans-serif; font-size: 13.5px;
  color: var(--white-60); font-weight: 300;
  padding: 16px 20px; border-bottom: 1px solid var(--white-10);
}
.jb-cancel-table tbody tr:last-child td { border-bottom: none; }
.jb-cancel-table tbody tr { transition: background var(--transition); }
.jb-cancel-table tbody tr:hover { background: var(--white-10); }
.jb-refund-badge {
  display: inline-flex; align-items: center; gap: 6px;
  font-size: 12px; font-weight: 600; padding: 4px 12px; border-radius: 100px;
}
.jb-refund-badge.full { background: rgba(76,175,130,0.12); color: var(--green); }
.jb-refund-badge.adjust { background: rgba(201,168,76,0.12); color: var(--gold); }
.jb-refund-badge.none { background: rgba(224,92,92,0.12); color: var(--red); }

/* ===== SIDEBAR ===== */
.jb-sidebar-wrap { position: sticky; top: 80px; display: flex; flex-direction: column; gap: 20px; }
.jb-sidebar-card {
  background: var(--dark-2); border: 1px solid var(--white-10);
  border-radius: var(--radius); overflow: hidden;
}
.jb-price-card-top {
  background: linear-gradient(135deg, var(--dark-3), var(--dark-2));
  padding: 28px 28px 20px;
  border-bottom: 1px solid var(--gold-dim);
}
.jb-price-label {
  font-family: 'Jost', sans-serif; font-size: 10px; font-weight: 600;
  letter-spacing: 0.2em; text-transform: uppercase; color: var(--white-60);
  margin-bottom: 6px;
}
.jb-price-val {
  font-family: 'Cormorant Garamond', serif; font-size: 3.2rem; font-weight: 500;
  color: #fff; line-height: 1; margin-bottom: 4px;
}
.jb-price-val .curr { font-size: 1.8rem; color: var(--gold); margin-right: 2px; }
.jb-price-pp {
  font-family: 'Jost', sans-serif; font-size: 11px; color: var(--white-60);
}
.jb-price-card-body { padding: 24px 28px; display: flex; flex-direction: column; gap: 12px; }
.jb-price-row {
  display: flex; justify-content: space-between; align-items: center;
  font-family: 'Jost', sans-serif; font-size: 13px; color: var(--white-60);
}
.jb-price-row strong { color: #fff; font-weight: 500; }
.jb-price-divider { height: 1px; background: var(--white-10); }
.jb-booking-badge {
  background: var(--gold-dim); border: 1px solid var(--gold-dim);
  border-radius: 8px; padding: 12px 16px; text-align: center;
  font-family: 'Jost', sans-serif; font-size: 12.5px; color: var(--gold); font-weight: 500;
}
.jb-btn {
  display: block; width: 100%; text-align: center;
  background: var(--gold); color: var(--dark);
  font-family: 'Jost', sans-serif; font-size: 12px; font-weight: 700;
  letter-spacing: 0.15em; text-transform: uppercase; padding: 17px 24px;
  border-radius: 100px; text-decoration: none; transition: all var(--transition);
}
.jb-btn:hover { background: var(--gold-light); transform: translateY(-2px); box-shadow: 0 8px 24px rgba(201,168,76,0.3); }
.jb-btn-outline {
  display: block; width: 100%; text-align: center;
  background: transparent; border: 1px solid var(--white-30); color: #fff;
  font-family: 'Jost', sans-serif; font-size: 12px; font-weight: 600;
  letter-spacing: 0.15em; text-transform: uppercase; padding: 17px 24px;
  border-radius: 100px; text-decoration: none; transition: all var(--transition);
}
.jb-btn-outline:hover { border-color: var(--gold); color: var(--gold); }

/* ===== MINI INFO LIST ===== */
.jb-info-list { display: flex; flex-direction: column; gap: 0; }
.jb-info-row {
  display: flex; align-items: center; gap: 14px;
  padding: 14px 0; border-bottom: 1px solid var(--white-10);
  font-family: 'Jost', sans-serif; font-size: 13px;
}
.jb-info-row:last-child { border-bottom: none; }
.jb-info-icon { width: 32px; height: 32px; background: var(--gold-dim); border-radius: 8px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.jb-info-icon i { color: var(--gold); font-size: 13px; }
.jb-info-label { color: var(--white-60); font-weight: 300; margin-bottom: 2px; font-size: 11px; text-transform: uppercase; letter-spacing: 0.08em; }
.jb-info-val { color: #fff; font-weight: 500; font-size: 13.5px; }

/* ===== CONTACT CARD ===== */
.jb-contact-card { background: var(--dark-2); border: 1px solid var(--white-10); border-radius: var(--radius); padding: 24px; }
.jb-contact-title { font-family: 'Jost', sans-serif; font-size: 13px; font-weight: 600; color: #fff; margin-bottom: 16px; }
.jb-contact-item { display: flex; align-items: center; gap: 12px; font-family: 'Jost', sans-serif; font-size: 13px; color: var(--white-60); margin-bottom: 12px; }
.jb-contact-item:last-child { margin-bottom: 0; }
.jb-contact-item i { color: var(--gold); width: 16px; }

/* ===== PAYMENT SECTION ===== */
.jb-payment-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; }
.jb-payment-card {
  background: var(--dark-3); border: 1px solid var(--white-10);
  border-radius: var(--radius); padding: 20px; text-align: center;
  transition: all var(--transition);
}
.jb-payment-card:hover { border-color: var(--gold-dim); transform: translateY(-3px); }
.jb-payment-card i { font-size: 28px; color: var(--gold); margin-bottom: 10px; display: block; }
.jb-payment-card-label { font-family: 'Jost', sans-serif; font-size: 12px; font-weight: 500; color: var(--white-60); }

/* ===== IMPORTANT NOTE ===== */
.jb-note {
  background: rgba(201,168,76,0.06); border: 1px solid var(--gold-dim);
  border-left: 3px solid var(--gold); border-radius: var(--radius);
  padding: 20px 24px;
  font-family: 'Jost', sans-serif; font-size: 13.5px;
  color: var(--white-60); line-height: 1.7; font-weight: 300;
}
.jb-note strong { color: var(--gold); }

/* ===== RESPONSIVE ===== */
@media (max-width: 1024px) {
  .jb-layout { grid-template-columns: 1fr; padding: 40px 0; }
  .jb-sidebar-wrap { position: static; }
  .jb-inc-exc { grid-template-columns: 1fr; }
  .jb-highlights { grid-template-columns: 1fr; }
  .jb-payment-grid { grid-template-columns: repeat(3, 1fr); }
}
@media (max-width: 640px) {
  .jb-hero-content { padding: 0 20px 56px; }
  .jb-container { padding: 0 20px; }
  .jb-hero-title { font-size: 2.4rem; }
  .jb-sticky-nav-inner { padding: 0 20px; }
  .jb-cancel-table thead th, .jb-cancel-table tbody td { padding: 12px; font-size: 12px; }
  .jb-payment-grid { grid-template-columns: 1fr 1fr 1fr; }
}
</style>
@endpush

@section('content')

{{-- ===== HERO ===== --}}
<section class="jb-hero" id="overview">
  <div class="jb-hero-bg"></div>
  <div class="jb-hero-overlay"></div>
  <div class="jb-hero-content">
    <a href="{{ route('packages') }}" class="jb-back-btn">
      <i class="fa-solid fa-arrow-left"></i> All Packages
    </a>
    <div class="jb-eyebrow">Himachal Pradesh, India</div>
    <h1 class="jb-hero-title">Jibhi <em>Tirthan</em><br>Valley</h1>
    <div class="jb-hero-pills">
      <div class="jb-pill"><i class="fa-regular fa-moon"></i> 2 Nights</div>
      <div class="jb-pill"><i class="fa-solid fa-sun"></i> 3 Days</div>
      <div class="jb-pill"><i class="fa-solid fa-bus"></i> Delhi Departure</div>
      <div class="jb-pill"><i class="fa-solid fa-utensils"></i> MAP AI Meals</div>
      <div class="jb-pill"><i class="fa-solid fa-campground"></i> Bonfire Included</div>
      <div class="jb-pill gold-pill"><i class="fa-solid fa-indian-rupee-sign"></i> Starting ₹6,999</div>
    </div>
  </div>
</section>

{{-- ===== STICKY NAV ===== --}}
<nav class="jb-sticky-nav">
  <div class="jb-sticky-nav-inner">
    <a href="#overview" class="jb-nav-link active">Overview</a>
    <a href="#itinerary" class="jb-nav-link">Itinerary</a>
    <a href="#inclusions" class="jb-nav-link">Inclusions</a>
    <a href="#booking" class="jb-nav-link">Booking</a>
    <a href="#cancellation" class="jb-nav-link">Cancellation</a>
    <a href="#contact" class="jb-nav-link">Contact</a>
  </div>
</nav>

{{-- ===== MAIN CONTENT ===== --}}
<div class="jb-page">
  <div class="jb-container">
    <div class="jb-layout">

      {{-- ===== LEFT / MAIN ===== --}}
      <div>

        {{-- ABOUT --}}
        <div class="jb-section" id="about">
          <div class="jb-section-label">Discover</div>
          <h2 class="jb-section-title">About Jibhi <em>Tirthan Valley</em></h2>
          <div class="jb-about-text">
            <p>Nestled in the tranquil Tirthan Valley of Himachal Pradesh, Jibhi is a captivating destination that offers a perfect mix of adventure, culture, and natural beauty. Surrounded by lush pine forests, sparkling rivers, and snow-capped Himalayan peaks, this offbeat village invites travelers seeking a peaceful retreat away from the chaos of city life.</p>
            <p>Whether you're a nature enthusiast, an adventure seeker, or someone looking to unwind, Jibhi caters to all kinds of explorers. The region is home to some of the most scenic trekking trails, including the breathtaking Serolsar Lake trek and the renowned Jalori Pass, which offers panoramic views of the Great Himalayan Range.</p>
            <p>Jibhi also boasts historical landmarks like the 17th-century Chehni Kothi, a fortified tower that has stood the test of time, and the Shringa Rishi Temple, a sacred site for the locals. Jibhi remains relatively untouched by commercialization, with its rustic homestays and traditional Himachali architecture offering an authentic experience.</p>
          </div>

          <div class="jb-highlights">
            <div class="jb-highlight-card">
              <div class="jb-highlight-icon"><i class="fa-solid fa-water"></i></div>
              <div class="jb-highlight-title">Jibhi Waterfall</div>
              <div class="jb-highlight-desc">Soul-stirring waterfall amid lush greenery — a perfect spot for photography and relaxation.</div>
            </div>
            <div class="jb-highlight-card">
              <div class="jb-highlight-icon"><i class="fa-solid fa-mountain-sun"></i></div>
              <div class="jb-highlight-title">Mini Thailand</div>
              <div class="jb-highlight-desc">Kulhi Katand — two giant rocks stacked with water passing through, giving a secluded beach vibe.</div>
            </div>
            <div class="jb-highlight-card">
              <div class="jb-highlight-icon"><i class="fa-solid fa-person-hiking"></i></div>
              <div class="jb-highlight-title">Jalori Pass</div>
              <div class="jb-highlight-desc">Scenic pass offering panoramic 360° views of the Great Himalayan Range and surrounding valleys.</div>
            </div>
            <div class="jb-highlight-card">
              <div class="jb-highlight-icon"><i class="fa-solid fa-landmark"></i></div>
              <div class="jb-highlight-title">Raghupur Fort Trek</div>
              <div class="jb-highlight-desc">Trek through scenic trails to the ruins of an ancient fort with stunning panoramic Himalayan views.</div>
            </div>
            <div class="jb-highlight-card">
              <div class="jb-highlight-icon"><i class="fa-solid fa-droplet"></i></div>
              <div class="jb-highlight-title">Serolsar Lake</div>
              <div class="jb-highlight-desc">Sacred Himalayan lake through dense pine forests, home to the Buddhi Nagin Temple.</div>
            </div>
            <div class="jb-highlight-card">
              <div class="jb-highlight-icon"><i class="fa-solid fa-rainbow"></i></div>
              <div class="jb-highlight-title">Chhoie Waterfall</div>
              <div class="jb-highlight-desc">Spectacular waterfall — on favourable days, a rare double rainbow appears beneath the falls.</div>
            </div>
          </div>
        </div>

        {{-- ITINERARY --}}
        <div class="jb-section" id="itinerary">
          <div class="jb-section-label">Day by Day</div>
          <h2 class="jb-section-title">Detailed <em>Itinerary</em></h2>

          <div class="jb-itinerary">

            {{-- Day 0 --}}
            <div class="jb-day-card">
              <div class="jb-day-left">
                <div class="jb-day-num-wrap"><span>DAY<br>0</span></div>
                <div class="jb-day-line"></div>
              </div>
              <div class="jb-day-right">
                <div class="jb-day-tag">Night Departure</div>
                <div class="jb-day-title">Departure from Delhi</div>
                <div class="jb-day-body">
                  <p>The group assembles at the pickup point and a small tour briefing session is provided by the trip captain. Departure from Delhi for an overnight comfortable journey to Jibhi — a tiny hamlet situated between lush green forests in the Tirthan Valley of Himachal Pradesh.</p>
                  <p>Departure date, time and place will be shared with the travellers in advance.</p>
                </div>
                <div class="jb-day-highlights">
                  <div class="jb-day-chip"><i class="fa-solid fa-bus"></i> Volvo / Tempo Traveller</div>
                  <div class="jb-day-chip"><i class="fa-solid fa-star"></i> Trip Briefing</div>
                  <div class="jb-day-chip"><i class="fa-solid fa-moon"></i> Overnight Journey</div>
                </div>
              </div>
            </div>

            {{-- Day 1 --}}
            <div class="jb-day-card">
              <div class="jb-day-left">
                <div class="jb-day-num-wrap"><span>DAY<br>1</span></div>
                <div class="jb-day-line"></div>
              </div>
              <div class="jb-day-right">
                <div class="jb-day-tag">Morning → Evening</div>
                <div class="jb-day-title">Jibhi Waterfall & Mini Thailand</div>
                <div class="jb-day-body">
                  <p><strong style="color:#fff;">Morning:</strong> Arrive at Jibhi, check in to your rooms, and relax after the overnight journey.</p>
                  <p><strong style="color:#fff;">Afternoon / Evening:</strong> Head for a local sightseeing tour. Visit the soul-stirring Jibhi Waterfall. Then explore <em>"Mini Thailand"</em> (also known as Kulhi Katand) — undertake a 500-metre trek to this near-by settlement. The name 'Mini Thailand' comes from its unique structure: two giant rocks stacked together with water passing through, giving it a secluded beach vibe renowned for its serene beauty and unique appeal. Explore the local market and go café hopping.</p>
                  <p><strong style="color:#fff;">Evening:</strong> Gather around the bonfire and enjoy dinner under the blanket of the starry sky, followed by an overnight stay in Jibhi.</p>
                </div>
                <div class="jb-day-highlights">
                  <div class="jb-day-chip"><i class="fa-solid fa-hotel"></i> Hotel Check-in</div>
                  <div class="jb-day-chip"><i class="fa-solid fa-water"></i> Jibhi Waterfall</div>
                  <div class="jb-day-chip"><i class="fa-solid fa-person-hiking"></i> Mini Thailand Trek (500m)</div>
                  <div class="jb-day-chip"><i class="fa-solid fa-mug-hot"></i> Café Hopping</div>
                  <div class="jb-day-chip"><i class="fa-solid fa-fire"></i> Bonfire + Dinner</div>
                </div>
              </div>
            </div>

            {{-- Day 2 --}}
            <div class="jb-day-card">
              <div class="jb-day-left">
                <div class="jb-day-num-wrap"><span>DAY<br>2</span></div>
                <div class="jb-day-line"></div>
              </div>
              <div class="jb-day-right">
                <div class="jb-day-tag">Full Day Trek</div>
                <div class="jb-day-title">Jalori Pass & Raghupur Fort <span style="color:var(--gold-light);font-size:0.85em;">or</span> Serolsar Lake Trek</div>
                <div class="jb-day-body">
                  <p><strong style="color:#fff;">Morning:</strong> After an early morning breakfast, head towards Jalori Pass (cab charges are on own cost).</p>
                  <p><strong style="color:#fff;">Choose Your Trek:</strong></p>
                  <p>🏰 <strong style="color:#fff;">Raghupur Fort Trek</strong> — The Raghupur Fort trek takes you through scenic trails to the ruins of an ancient fort, offering stunning panoramic views of the Tirthan Valley. Visit the <strong style="color:#fff;">360° Himalayan Vision Point</strong> which offers panoramic views of surrounding snow-capped mountains. A perfect combination of history, adventure, and breathtaking vistas.</p>
                  <p>🏞️ <strong style="color:#fff;">Serolsar Lake Trek</strong> — Trek through a dense pine forest to reach the sacred Serolsar Lake. Visit the ancient <strong style="color:#fff;">Buddhi Nagin Temple</strong> located near the lake, known for its religious and cultural importance. Experience the refreshing sensation of dipping your feet in the cold waters of the lake.</p>
                  <p><strong style="color:#fff;">Evening:</strong> Dinner under the starry sky and return to room accommodation for overnight stay.</p>
                  <p>Also cover: <strong style="color:#fff;">Himalayan Eagle Vision Point</strong>, and the <strong style="color:#fff;">Buddhi Nagin Temple</strong>.</p>
                </div>
                <div class="jb-day-highlights">
                  <div class="jb-day-chip"><i class="fa-solid fa-road"></i> Jalori Pass</div>
                  <div class="jb-day-chip"><i class="fa-solid fa-fort-awesome"></i> Raghupur Fort</div>
                  <div class="jb-day-chip"><i class="fa-solid fa-droplet"></i> Serolsar Lake</div>
                  <div class="jb-day-chip"><i class="fa-solid fa-place-of-worship"></i> Buddhi Nagin Temple</div>
                  <div class="jb-day-chip"><i class="fa-solid fa-binoculars"></i> 360° Viewpoint</div>
                  <div class="jb-day-chip"><i class="fa-solid fa-fire"></i> Dinner Under Stars</div>
                </div>
              </div>
            </div>

            {{-- Day 3 --}}
            <div class="jb-day-card">
              <div class="jb-day-left">
                <div class="jb-day-num-wrap"><span>DAY<br>3</span></div>
                <div class="jb-day-line"></div>
              </div>
              <div class="jb-day-right">
                <div class="jb-day-tag">Morning + Departure</div>
                <div class="jb-day-title">Chhoie Waterfall & Departure</div>
                <div class="jb-day-body">
                  <p><strong style="color:#fff;">Morning:</strong> After breakfast, complete the check-out formalities at the accommodation. Make a brief stop at <strong style="color:#fff;">Gushaini Village</strong> for a quick exploration.</p>
                  <p><strong style="color:#fff;">Trek to Chhoie Waterfall:</strong> Proceed with the trek to Chhoie Waterfall, renowned for its scenic beauty. Take time to appreciate the natural surroundings and the grandeur of the waterfall. If conditions are favourable, witness the magical occurrence of a <strong style="color:#fff;">double rainbow beneath the waterfall</strong> — a truly blessed sight!</p>
                  <p><strong style="color:#fff;">Departure:</strong> After visiting Chhoie Waterfall, board the vehicle for the journey back to Aut. Continue the travel back to Delhi overnight.</p>
                </div>
                <div class="jb-day-highlights">
                  <div class="jb-day-chip"><i class="fa-solid fa-utensils"></i> Breakfast + Check-out</div>
                  <div class="jb-day-chip"><i class="fa-solid fa-house-flag"></i> Gushaini Village</div>
                  <div class="jb-day-chip"><i class="fa-solid fa-water"></i> Chhoie Waterfall</div>
                  <div class="jb-day-chip"><i class="fa-solid fa-rainbow"></i> Double Rainbow (if lucky!)</div>
                  <div class="jb-day-chip"><i class="fa-solid fa-bus"></i> Return to Delhi</div>
                </div>
              </div>
            </div>

            {{-- Day 4 --}}
            <div class="jb-day-card">
              <div class="jb-day-left">
                <div class="jb-day-num-wrap"><span>DAY<br>4</span></div>
              </div>
              <div class="jb-day-right" style="padding-bottom:0;">
                <div class="jb-day-tag">Arrival</div>
                <div class="jb-day-title">Arrival in Delhi</div>
                <div class="jb-day-body">
                  <p>Arrive in Delhi the following morning, carrying with you memories of the pine forests, sparkling rivers, and Himalayan peaks of the beautiful Tirthan Valley.</p>
                </div>
                <div class="jb-day-highlights">
                  <div class="jb-day-chip"><i class="fa-solid fa-location-dot"></i> Delhi Arrival</div>
                </div>
              </div>
            </div>

          </div>
        </div>

        {{-- INCLUSIONS --}}
        <div class="jb-section" id="inclusions">
          <div class="jb-section-label">What's Covered</div>
          <h2 class="jb-section-title">Inclusions & <em>Exclusions</em></h2>
          <div class="jb-inc-exc">
            <div class="jb-inc-box">
              <div class="jb-box-title"><i class="fa-solid fa-circle-check" style="margin-right:8px;"></i>What's Included</div>
              <ul class="jb-inc-list">
                <li><i class="fa-solid fa-check-circle"></i> Transportation: Delhi to Delhi (Volvo / Tempo Traveller)</li>
                <li><i class="fa-solid fa-check-circle"></i> Surface Transfers for sightseeing as per the itinerary</li>
                <li><i class="fa-solid fa-check-circle"></i> Accommodation in Hotel</li>
                <li><i class="fa-solid fa-check-circle"></i> Meal Plan (MAP AI): 4 meals total — 2 Breakfasts + 2 Dinners</li>
                <li><i class="fa-solid fa-check-circle"></i> Bonfire (subject to availability)</li>
                <li><i class="fa-solid fa-check-circle"></i> Trip Captain (may be present at times during the tour)</li>
                <li><i class="fa-solid fa-check-circle"></i> Driver Allowance</li>
                <li><i class="fa-solid fa-check-circle"></i> Toll Taxes & other State Taxes</li>
                <li><i class="fa-solid fa-check-circle"></i> Parking Charges</li>
              </ul>
            </div>
            <div class="jb-exc-box">
              <div class="jb-box-title"><i class="fa-solid fa-circle-xmark" style="margin-right:8px;"></i>What's Excluded</div>
              <ul class="jb-exc-list">
                <li><i class="fa-solid fa-xmark"></i> Early check-in request charges at the hotel</li>
                <li><i class="fa-solid fa-xmark"></i> Any additional expenses of personal nature</li>
                <li><i class="fa-solid fa-xmark"></i> Cab charges for Jalori Pass (own cost)</li>
                <li><i class="fa-solid fa-xmark"></i> Additional accommodation/food costs due to travel delays</li>
                <li><i class="fa-solid fa-xmark"></i> Parking & monument entry fees during sightseeing</li>
                <li><i class="fa-solid fa-xmark"></i> Costs due to Flight Cancellations, Landslides, Roadblocks or natural calamities</li>
                <li><i class="fa-solid fa-xmark"></i> Any other services not specified in inclusions</li>
                <li><i class="fa-solid fa-xmark"></i> Emergency services (payable on the spot)</li>
              </ul>
            </div>
          </div>
        </div>

        {{-- BOOKING & PAYMENT --}}
        <div class="jb-section" id="booking">
          <div class="jb-section-label">Secure Your Spot</div>
          <h2 class="jb-section-title">Booking & <em>Payment</em></h2>

          <div class="jb-note" style="margin-bottom:32px;">
            <strong>Booking Confirmation Policy:</strong> Kindly ensure that a confirmation email is received from PackandExplore. Payments must be made as per the payment policy to confirm the booking. <strong>Booking amount: ₹2,000 per person.</strong>
          </div>

          <div class="jb-payment-grid">
            <div class="jb-payment-card">
              <i class="fa-solid fa-building-columns"></i>
              <div class="jb-payment-card-label">Bank Transfer</div>
            </div>
            <div class="jb-payment-card">
              <i class="fa-brands fa-google-pay"></i>
              <div class="jb-payment-card-label">GPay / PhonePe</div>
            </div>
            <div class="jb-payment-card">
              <i class="fa-solid fa-mobile-screen-button"></i>
              <div class="jb-payment-card-label">Paytm / UPI</div>
            </div>
          </div>

          <div class="jb-note" style="margin-top:24px;">
            <strong>Bank Details:</strong> Name: PACKTURE EXPLORIFY LLP &nbsp;|&nbsp; A/C: 259074445353 &nbsp;|&nbsp; Bank: IndusInd Bank &nbsp;|&nbsp; IFSC: INDB0002082
          </div>
        </div>

        {{-- CANCELLATION --}}
        <div class="jb-section" id="cancellation">
          <div class="jb-section-label">Cancellation Policy</div>
          <h2 class="jb-section-title">Flexible <em>Cancellations</em></h2>

          <table class="jb-cancel-table">
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
                <td><span class="jb-refund-badge full"><i class="fa-solid fa-check"></i> Full Refund</span></td>
              </tr>
              <tr>
                <td>30–60 days before departure</td>
                <td>0% of trip cost</td>
                <td><span class="jb-refund-badge full"><i class="fa-solid fa-check"></i> Full Refund</span></td>
              </tr>
              <tr>
                <td>15–30 days before departure</td>
                <td>0% of trip cost</td>
                <td><span class="jb-refund-badge full"><i class="fa-solid fa-check"></i> Full Refund</span></td>
              </tr>
              <tr>
                <td>7–15 days before departure</td>
                <td>0% of trip cost</td>
                <td><span class="jb-refund-badge adjust"><i class="fa-solid fa-rotate"></i> Adjust for Next Trip</span></td>
              </tr>
              <tr>
                <td>0–7 days before departure</td>
                <td>100% of trip cost</td>
                <td><span class="jb-refund-badge none"><i class="fa-solid fa-xmark"></i> Non Refundable</span></td>
              </tr>
            </tbody>
          </table>
        </div>

        {{-- CONTACT --}}
        <div class="jb-section" id="contact" style="border-bottom:none;">
          <div class="jb-section-label">Get in Touch</div>
          <h2 class="jb-section-title">Contact <em>PackandExplore</em></h2>
          <div class="jb-note">
            <strong>Ready to book?</strong> Reach out to us and our team will get back to you promptly. We believe travel should be stress-free and filled with excitement!
          </div>
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-top:24px;">
            <div class="jb-contact-card">
              <div class="jb-contact-title">Contact Details</div>
              <div class="jb-contact-item"><i class="fa-solid fa-phone"></i> +91 79992 68526</div>
              <div class="jb-contact-item"><i class="fa-solid fa-envelope"></i> contact@packandexplore.in</div>
              <div class="jb-contact-item"><i class="fa-solid fa-globe"></i> www.packandexplore.in</div>
              <div class="jb-contact-item"><i class="fa-solid fa-location-dot"></i> 831, Tower C, Bhutani Alphathum, Sector 90, Noida UP-201305</div>
            </div>
            <div class="jb-contact-card">
              <div class="jb-contact-title">Why PackandExplore?</div>
              <div class="jb-contact-item"><i class="fa-solid fa-bolt"></i> Seamless & Exciting Travel</div>
              <div class="jb-contact-item"><i class="fa-solid fa-sliders"></i> Tailored Packages for your pace</div>
              <div class="jb-contact-item"><i class="fa-solid fa-earth-asia"></i> Versatile Destinations</div>
              <div class="jb-contact-item"><i class="fa-solid fa-magnifying-glass"></i> Attention to Every Detail</div>
            </div>
          </div>
        </div>

      </div>

      {{-- ===== SIDEBAR ===== --}}
      <div>
        <div class="jb-sidebar-wrap">

          {{-- Price Card --}}
          <div class="jb-sidebar-card">
            <div class="jb-price-card-top">
              <div class="jb-price-label">Starting From</div>
              <div class="jb-price-val"><span class="curr">₹</span>6,999</div>
              <div class="jb-price-pp">per person (ex. Delhi)</div>
            </div>
            <div class="jb-price-card-body">
              <div class="jb-price-row">
                <span>Duration</span>
                <strong>2 Nights / 3 Days</strong>
              </div>
              <div class="jb-price-divider"></div>
              <div class="jb-price-row">
                <span>Booking Amount</span>
                <strong>₹2,000 / person</strong>
              </div>
              <div class="jb-price-divider"></div>
              <div class="jb-price-row">
                <span>Departure</span>
                <strong>Delhi</strong>
              </div>
              <div class="jb-price-divider"></div>
              <div class="jb-price-row">
                <span>Meals</span>
                <strong>2B + 2D</strong>
              </div>
              <div class="jb-booking-badge">
                <i class="fa-solid fa-tag" style="margin-right:6px;"></i> Book now for ₹2,000 to confirm your seat
              </div>
              <a href="https://wa.me/917999268526?text=Hi!%20I'm%20interested%20in%20the%20Jibhi%20Tirthan%20Valley%202N3D%20package" target="_blank" class="jb-btn">
                <i class="fa-solid fa-calendar-check" style="margin-right:8px;"></i> Book Now
              </a>
              <a href="https://wa.me/917999268526" target="_blank" class="jb-btn-outline">
                <i class="fa-brands fa-whatsapp" style="margin-right:8px;"></i> WhatsApp Us
              </a>
            </div>
          </div>

          {{-- Quick Info --}}
          <div class="jb-sidebar-card">
            <div class="jb-price-card-body">
              <div class="jb-section-label" style="margin-bottom:16px;">Quick Info</div>
              <div class="jb-info-list">
                <div class="jb-info-row">
                  <div class="jb-info-icon"><i class="fa-solid fa-location-dot"></i></div>
                  <div>
                    <div class="jb-info-label">Destination</div>
                    <div class="jb-info-val">Jibhi, Himachal Pradesh</div>
                  </div>
                </div>
                <div class="jb-info-row">
                  <div class="jb-info-icon"><i class="fa-solid fa-moon"></i></div>
                  <div>
                    <div class="jb-info-label">Duration</div>
                    <div class="jb-info-val">2 Nights · 3 Days</div>
                  </div>
                </div>
                <div class="jb-info-row">
                  <div class="jb-info-icon"><i class="fa-solid fa-bus"></i></div>
                  <div>
                    <div class="jb-info-label">Transport</div>
                    <div class="jb-info-val">Volvo / Tempo Traveller</div>
                  </div>
                </div>
                <div class="jb-info-row">
                  <div class="jb-info-icon"><i class="fa-solid fa-hotel"></i></div>
                  <div>
                    <div class="jb-info-label">Stay</div>
                    <div class="jb-info-val">Hotel in Jibhi</div>
                  </div>
                </div>
                <div class="jb-info-row">
                  <div class="jb-info-icon"><i class="fa-solid fa-utensils"></i></div>
                  <div>
                    <div class="jb-info-label">Meals</div>
                    <div class="jb-info-val">2 Breakfast + 2 Dinner</div>
                  </div>
                </div>
                <div class="jb-info-row">
                  <div class="jb-info-icon"><i class="fa-solid fa-fire"></i></div>
                  <div>
                    <div class="jb-info-label">Bonfire</div>
                    <div class="jb-info-val">Included (subject to availability)</div>
                  </div>
                </div>
                <div class="jb-info-row">
                  <div class="jb-info-icon"><i class="fa-solid fa-user-tie"></i></div>
                  <div>
                    <div class="jb-info-label">Trip Captain</div>
                    <div class="jb-info-val">Present during the tour</div>
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
const navLinks = document.querySelectorAll('.jb-nav-link');
const observer = new IntersectionObserver((entries) => {
  entries.forEach(entry => {
    if (entry.isIntersecting) {
      navLinks.forEach(l => l.classList.remove('active'));
      const active = document.querySelector(`.jb-nav-link[href="#${entry.target.id}"]`);
      if (active) active.classList.add('active');
    }
  });
}, { rootMargin: '-50% 0px -50% 0px' });
sections.forEach(s => observer.observe(s));
</script>

@endsection
