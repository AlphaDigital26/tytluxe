@extends('layouts.frontend')

@section('meta_title', 'Luxury Hotels — Handpicked Stays Across India & World | TYT Luxe')
@section('meta_description', 'Browse TYT Luxe\'s curated collection of luxury hotels — beach resorts, city hotels, honeymoon getaways and family-friendly stays across India and international destinations. Best price guaranteed.')

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com" />
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;1,400;1,600&family=Jost:wght@300;400;500;600&display=swap" rel="stylesheet" />

<style>
/* ===== VARIABLES ===== */
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
  --green: #4ade80;
  --radius: 12px;
  --transition: 0.35s cubic-bezier(0.25, 0.46, 0.45, 0.94);
}

/* ===== TRUST BAR ===== */
.htl-trust {
  background: var(--dark-2);
  border-top: 1px solid var(--gold-dim); border-bottom: 1px solid var(--gold-dim);
  padding: 20px 40px;
}
.htl-trust-inner {
  max-width: 1200px; margin: 0 auto;
  display: flex; justify-content: center; gap: 48px; flex-wrap: wrap;
}
.htl-trust-item {
  display: flex; align-items: center; gap: 10px;
  font-family: 'Jost', sans-serif; font-size: 12.5px; font-weight: 500;
  letter-spacing: 0.06em; color: var(--white-60); text-transform: uppercase;
}
.htl-trust-item .ti { color: var(--gold); }

/* ===== SECTION ===== */
.htl-section { padding: 96px 40px; background: var(--dark); }
.htl-section-inner { max-width: 1200px; margin: 0 auto; }
.htl-section-header { text-align: center; margin-bottom: 56px; }

.htl-eyebrow {
  font-family: 'Jost', sans-serif; font-size: 10.5px; font-weight: 600;
  letter-spacing: 0.22em; text-transform: uppercase; color: var(--gold); margin-bottom: 14px;
}
.htl-title {
  font-family: 'Cormorant Garamond', serif;
  font-size: clamp(2.4rem, 4.5vw, 3.6rem); font-weight: 500;
  line-height: 1.1; color: #fff; margin-bottom: 16px;
}
.htl-title em { font-style: italic; color: var(--gold-light); }
.htl-desc {
  font-family: 'Jost', sans-serif; font-size: 14.5px; color: var(--white-60);
  max-width: 520px; margin: 0 auto; font-weight: 300;
}

.htl-divider {
  display: flex; align-items: center; justify-content: center;
  gap: 16px; margin: 0 auto 48px; max-width: 300px;
}
.htl-divider::before, .htl-divider::after {
  content: ''; flex: 1; height: 1px; background: var(--gold-dim);
}
.htl-divider span { color: var(--gold); font-size: 16px; }

/* ===== SEARCH ===== */
.htl-search-wrap {
  max-width: 720px; margin: 0 auto 28px;
  display: grid; grid-template-columns: 1fr auto; gap: 10px; align-items: center;
  background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.12);
  border-radius: 100px; padding: 8px 8px 8px 22px;
}
.htl-search-wrap label { position: absolute; width: 1px; height: 1px; overflow: hidden; }
.htl-search-wrap input {
  width: 100%; min-height: 42px; border: none; outline: none;
  background: transparent; color: #fff;
  font-family: 'Jost', sans-serif; font-size: 14px;
}
.htl-search-wrap input::placeholder { color: var(--white-60); }
.htl-search-pill {
  display: inline-flex; align-items: center; justify-content: center;
  min-height: 42px; padding: 0 18px; border-radius: 100px;
  background: var(--gold); color: var(--dark);
  font-family: 'Jost', sans-serif; font-size: 12px; font-weight: 700;
  letter-spacing: .08em; text-transform: uppercase;
}

/* ===== FILTER TABS ===== */
.htl-filter-tabs {
  display: flex; justify-content: center; gap: 8px; flex-wrap: wrap; margin-bottom: 56px;
}
.htl-tab {
  padding: 10px 24px; border-radius: 100px; border: 1px solid var(--white-30);
  background: transparent; color: var(--white-60);
  font-family: 'Jost', sans-serif; font-size: 12.5px; font-weight: 500;
  letter-spacing: 0.08em; text-transform: uppercase; cursor: pointer; transition: all var(--transition);
}
.htl-tab:hover { border-color: var(--gold); color: var(--gold); }
.htl-tab.active { background: var(--gold); border-color: var(--gold); color: var(--dark); }

/* ===== HOTEL GRID ===== */
.htl-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 32px; }
@media (max-width: 1100px) { .htl-grid { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 640px)  { .htl-grid { grid-template-columns: 1fr; } }

/* ===== HOTEL CARD — Ultra Premium ===== */
.htl-card {
  background: var(--dark-2); /* Blends much better with the main background */
  border-radius: 22px;
  overflow: hidden;
  border: 1px solid var(--white-10);
  transition: transform 0.4s cubic-bezier(0.25,0.46,0.45,0.94),
              box-shadow 0.4s ease,
              border-color 0.3s ease;
  display: flex; flex-direction: column;
  color: inherit; text-decoration: none;
  cursor: pointer; position: relative;
}
/* Animated gold shimmer border on hover */
.htl-card::before {
  content: '';
  position: absolute; inset: 0;
  border-radius: 22px;
  padding: 1px;
  background: linear-gradient(135deg, transparent 20%, rgba(201,168,76,0.3) 50%, transparent 80%);
  -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
  -webkit-mask-composite: xor; mask-composite: exclude;
  opacity: 0; transition: opacity 0.4s ease;
  pointer-events: none; z-index: 10;
}
.htl-card:hover::before { opacity: 1; }
.htl-card:hover {
  transform: translateY(-8px) scale(1.01);
  border-color: rgba(201,168,76,0.25);
  box-shadow:
    0 32px 72px rgba(0,0,0,0.6),
    0 0 0 1px rgba(201,168,76,0.1),
    0 0 60px rgba(201,168,76,0.03);
}
.htl-card.htl-hidden { display: none; }

/* Card image */
.htl-card-img-wrap {
  position: relative; height: 280px; overflow: hidden; flex-shrink: 0;
}
.htl-card-img-wrap img {
  width: 100%; height: 100%; object-fit: cover;
  transition: transform 0.8s cubic-bezier(0.25,0.46,0.45,0.94); display: block;
  filter: brightness(0.92);
}
.htl-card:hover .htl-card-img-wrap img {
  transform: scale(1.1);
  filter: brightness(1.0);
}
/* Deep rich gradient overlay */
.htl-card-img-overlay {
  position: absolute; inset: 0;
  background: linear-gradient(
    to top,
    rgba(0,0,0,0.92) 0%,
    rgba(0,0,0,0.45) 40%,
    rgba(0,0,0,0.05) 75%,
    transparent 100%
  );
  pointer-events: none;
}
/* Hotel name & location on image */
.htl-card-img-info {
  position: absolute; bottom: 0; left: 0; right: 0;
  padding: 20px 20px 18px; z-index: 3;
}
.htl-card-img-name {
  font-family: 'Cormorant Garamond', serif;
  font-size: 1.5rem; font-weight: 600; line-height: 1.2;
  color: #fff; margin-bottom: 6px;
  text-shadow: 0 2px 12px rgba(0,0,0,0.8);
  transition: color 0.3s ease;
}
.htl-card:hover .htl-card-img-name { color: #f5e4a8; }
.htl-card-img-loc {
  display: flex; align-items: center; gap: 5px;
  font-family: 'Jost', sans-serif; font-size: 12.5px;
  color: rgba(255,255,255,0.7); font-weight: 400; letter-spacing: 0.03em;
}
.htl-card-img-loc svg { flex-shrink: 0; color: var(--gold); }

/* Category badge */
.htl-card-badge {
  position: absolute; top: 16px; left: 16px; z-index: 6;
  background: rgba(0,0,0,0.6); backdrop-filter: blur(12px);
  border: 1px solid rgba(201,168,76,0.55);
  color: var(--gold); font-family: 'Jost', sans-serif;
  font-size: 10px; font-weight: 700; letter-spacing: 0.12em; text-transform: uppercase;
  padding: 5px 12px; border-radius: 100px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.4);
}

/* Featured ribbon */
.htl-card-featured {
  position: absolute; top: 16px; left: 16px; z-index: 6;
  background: linear-gradient(90deg, #c9a84c, #e8c96b);
  color: #0d0d0d; font-family: 'Jost', sans-serif;
  font-size: 9px; font-weight: 800; letter-spacing: 0.15em; text-transform: uppercase;
  padding: 5px 12px; border-radius: 100px;
  box-shadow: 0 4px 16px rgba(201,168,76,0.5);
  display: flex; align-items: center; gap: 4px;
}

/* Photo counter */
.htl-img-counter {
  position: absolute; bottom: 16px; right: 16px; z-index: 5;
  background: rgba(0,0,0,0.6); backdrop-filter: blur(8px);
  color: rgba(255,255,255,0.9); font-family: 'Jost', sans-serif;
  font-size: 11px; font-weight: 500;
  padding: 4px 12px; border-radius: 100px;
  letter-spacing: 0.05em; border: 1px solid rgba(255,255,255,0.12);
}

/* Wishlist heart */
.htl-heart {
  position: absolute; top: 16px; right: 16px; z-index: 7;
  width: 38px; height: 38px; border-radius: 50%;
  background: rgba(0,0,0,0.5); backdrop-filter: blur(10px);
  display: flex; align-items: center; justify-content: center;
  cursor: pointer; border: 1px solid rgba(255,255,255,0.2);
  transition: all 0.28s ease; color: #fff;
}
.htl-heart:hover {
  background: rgba(201,168,76,0.2); border-color: var(--gold);
  color: var(--gold); transform: scale(1.12);
  box-shadow: 0 0 20px rgba(201,168,76,0.3);
}
.htl-heart.active { background: var(--gold); border-color: var(--gold); color: #0d0d0d; }
.htl-heart.active svg { fill: currentColor; }

/* Card body — glassmorphism */
.htl-card-body {
  padding: 18px 20px 20px;
  display: flex; flex-direction: column; flex: 1;
  background: linear-gradient(180deg, rgba(255,255,255,0.025) 0%, transparent 100%);
}

/* Stars row */
.htl-card-stars-row {
  display: flex; align-items: center; justify-content: space-between;
  margin-bottom: 14px;
}
.htl-card-stars { display: flex; gap: 3px; }
.htl-card-stars span { color: var(--gold); font-size: 13px; line-height: 1; filter: drop-shadow(0 0 3px rgba(201,168,76,0.6)); }
.htl-card-star-label {
  font-family: 'Jost', sans-serif; font-size: 10.5px; font-weight: 600;
  color: var(--white-60); letter-spacing: 0.06em; text-transform: uppercase;
  background: rgba(201,168,76,0.08); border: 1px solid rgba(201,168,76,0.2);
  padding: 3px 10px; border-radius: 100px;
}

/* Amenity chips */
.htl-amenity-chips {
  display: flex; flex-wrap: wrap; gap: 7px;
  margin-bottom: 18px; flex: 1;
}
.htl-amenity-chip {
  background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.09);
  color: rgba(255,255,255,0.6); font-family: 'Jost', sans-serif;
  font-size: 11px; font-weight: 400; padding: 5px 12px;
  border-radius: 100px; white-space: nowrap;
  transition: all 0.2s ease;
}
.htl-card:hover .htl-amenity-chip { border-color: rgba(201,168,76,0.2); color: rgba(255,255,255,0.75); }

/* Divider */
.htl-card-divider {
  height: 1px;
  background: linear-gradient(90deg, transparent, rgba(201,168,76,0.2) 50%, transparent);
  margin-bottom: 16px;
}

/* Card footer */
.htl-card-footer {
  display: flex; align-items: center; justify-content: space-between; gap: 10px;
}
.htl-star-badge {
  display: flex; align-items: center; gap: 6px;
  font-family: 'Jost', sans-serif; font-size: 12px;
  color: var(--white-60); font-weight: 400;
}
.htl-star-badge svg { color: var(--gold); filter: drop-shadow(0 0 4px rgba(201,168,76,0.5)); }
.htl-req-btn {
  display: inline-flex; align-items: center; gap: 7px;
  background: linear-gradient(90deg, #c9a84c, #e8c96b);
  color: #0d0d0d;
  font-family: 'Jost', sans-serif; font-size: 11px; font-weight: 800;
  letter-spacing: 0.1em; text-transform: uppercase;
  padding: 10px 20px; border-radius: 100px;
  transition: all 0.28s ease;
  white-space: nowrap; border: none;
  box-shadow: 0 4px 12px rgba(201,168,76,0.15);
}
.htl-card:hover .htl-req-btn {
  background: linear-gradient(90deg, #e8c96b, #f5e4a8);
  box-shadow: 0 6px 18px rgba(201,168,76,0.3);
  transform: translateY(-1px);
}


/* ===== FEATURED BANNER ===== */
.htl-banner { background: var(--dark-2); border-top: 1px solid var(--gold-dim); border-bottom: 1px solid var(--gold-dim); }
.htl-banner-inner {
  max-width: 1200px; margin: 0 auto; padding: 72px 40px;
  display: grid; grid-template-columns: 1fr 1fr; gap: 64px; align-items: center;
}
.htl-banner-text p.htl-eyebrow { margin-bottom: 16px; }
.htl-banner-text h2 {
  font-family: 'Cormorant Garamond', serif; font-size: clamp(2rem, 3.5vw, 3rem);
  font-weight: 500; line-height: 1.15; color: #fff; margin-bottom: 20px;
}
.htl-banner-text h2 em { font-style: italic; color: var(--gold-light); }
.htl-banner-text p.htl-body {
  font-family: 'Jost', sans-serif; font-size: 14.5px; color: var(--white-60);
  font-weight: 300; line-height: 1.7; margin-bottom: 32px;
}
.htl-banner-btns { display: flex; gap: 14px; flex-wrap: wrap; }
.htl-btn-primary {
  display: inline-flex; align-items: center; gap: 8px; background: var(--gold); color: var(--dark);
  font-family: 'Jost', sans-serif; font-size: 12.5px; font-weight: 700;
  letter-spacing: 0.1em; text-transform: uppercase; padding: 14px 28px; border-radius: 100px;
  text-decoration: none; transition: all var(--transition);
}
.htl-btn-primary:hover { background: var(--gold-light); transform: translateY(-2px); box-shadow: 0 8px 24px rgba(201,168,76,0.3); }
.htl-btn-outline {
  display: inline-flex; align-items: center; gap: 8px; background: transparent; color: #fff;
  font-family: 'Jost', sans-serif; font-size: 12.5px; font-weight: 600;
  letter-spacing: 0.1em; text-transform: uppercase; padding: 14px 28px; border-radius: 100px;
  text-decoration: none; border: 1px solid var(--white-30); transition: all var(--transition);
}
.htl-btn-outline:hover { border-color: var(--gold); color: var(--gold); }

.htl-banner-img { border-radius: var(--radius); overflow: hidden; aspect-ratio: 4/3; position: relative; }
.htl-banner-img img { width: 100%; height: 100%; object-fit: cover; }
.htl-banner-img::after {
  content: ''; position: absolute; inset: 0;
  border: 1px solid rgba(201,168,76,0.2); border-radius: var(--radius); pointer-events: none;
}

/* ===== ENQUIRY FORM ===== */
.htl-enquiry {
  padding: 96px 40px;
  background: var(--dark-2);
  border-top: 1px solid var(--gold-dim);
}
.htl-enquiry-inner { max-width: 860px; margin: 0 auto; }
.htl-enquiry-header { text-align: center; margin-bottom: 52px; }

.htl-enquiry-form { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
.htl-form-group { display: flex; flex-direction: column; gap: 8px; }
.htl-form-group.full { grid-column: 1 / -1; }
.htl-form-group label {
  font-family: 'Jost', sans-serif; font-size: 10.5px; font-weight: 600;
  letter-spacing: 0.18em; text-transform: uppercase; color: var(--gold);
}
.htl-form-group input,
.htl-form-group select,
.htl-form-group textarea {
  background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1);
  border-radius: 10px; padding: 14px 18px; color: #fff;
  font-family: 'Jost', sans-serif; font-size: 14px; font-weight: 300;
  outline: none; transition: border-color var(--transition), background var(--transition);
  width: 100%; box-sizing: border-box; -webkit-appearance: none; appearance: none;
}
.htl-form-group select {
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23c9a84c' d='M6 8L1 3h10z'/%3E%3C/svg%3E");
  background-repeat: no-repeat; background-position: right 16px center;
  background-color: rgba(255,255,255,0.04); padding-right: 40px; cursor: pointer;
}
.htl-form-group select option { background: var(--dark-3); color: #fff; }
.htl-form-group textarea { resize: vertical; min-height: 120px; }
.htl-form-group input::placeholder, .htl-form-group textarea::placeholder { color: rgba(255,255,255,0.25); }
.htl-form-group input:focus, .htl-form-group select:focus, .htl-form-group textarea:focus {
  border-color: var(--gold); background: rgba(201,168,76,0.05);
}

.htl-form-submit-row {
  grid-column: 1 / -1; display: flex; align-items: center; justify-content: space-between;
  gap: 20px; flex-wrap: wrap; margin-top: 8px;
}
.htl-form-note { font-family: 'Jost', sans-serif; font-size: 12px; color: rgba(255,255,255,0.3); font-weight: 300; }
.htl-form-btn {
  display: inline-flex; align-items: center; gap: 10px; background: var(--gold); color: var(--dark);
  font-family: 'Jost', sans-serif; font-size: 13px; font-weight: 700;
  letter-spacing: 0.1em; text-transform: uppercase;
  padding: 16px 36px; border-radius: 100px; border: none; cursor: pointer;
  transition: all var(--transition); white-space: nowrap;
}
.htl-form-btn:hover { background: var(--gold-light); transform: translateY(-2px); box-shadow: 0 8px 24px rgba(201,168,76,0.3); }
.htl-form-btn svg { width: 15px; height: 15px; flex-shrink: 0; }

.htl-form-success {
  display: none; text-align: center; padding: 48px 32px;
  background: rgba(201,168,76,0.06); border: 1px solid var(--gold-dim); border-radius: var(--radius);
}
.htl-form-success.show { display: block; }
.htl-form-success .htl-success-icon { font-size: 40px; margin-bottom: 16px; }
.htl-form-success h3 { font-family: 'Cormorant Garamond', serif; font-size: 1.8rem; color: #fff; margin-bottom: 10px; }
.htl-form-success p { font-family: 'Jost', sans-serif; font-size: 14px; color: var(--white-60); font-weight: 300; }

/* ===== CTA STRIP ===== */
.htl-cta { padding: 80px 40px; text-align: center; background: var(--dark); }
.htl-cta-inner { max-width: 700px; margin: 0 auto; }
.htl-cta h2 {
  font-family: 'Cormorant Garamond', serif; font-size: clamp(2rem, 4vw, 3rem);
  font-weight: 500; color: #fff; margin-bottom: 16px; line-height: 1.2;
}
.htl-cta h2 em { font-style: italic; color: var(--gold-light); }
.htl-cta p { font-family: 'Jost', sans-serif; font-size: 14.5px; color: var(--white-60); font-weight: 300; margin-bottom: 36px; }
.htl-cta-btns { display: flex; gap: 14px; justify-content: center; flex-wrap: wrap; }
.htl-wa-btn {
  display: inline-flex; align-items: center; gap: 9px; background: #25D366; color: #fff;
  font-family: 'Jost', sans-serif; font-size: 12.5px; font-weight: 700;
  letter-spacing: 0.08em; text-transform: uppercase; padding: 14px 28px;
  border-radius: 100px; text-decoration: none; transition: all var(--transition);
}
.htl-wa-btn:hover { background: #20c45b; transform: translateY(-2px); box-shadow: 0 8px 24px rgba(37,211,102,0.3); }

/* ===== RESPONSIVE ===== */
@media (max-width: 1024px) {
  .htl-grid { grid-template-columns: repeat(2, 1fr); }
  .htl-banner-inner { grid-template-columns: 1fr; gap: 40px; }
}
@media (max-width: 768px) {
  .htl-section { padding: 64px 20px; }
  .htl-grid { grid-template-columns: 1fr; }
  .htl-trust { padding: 16px 20px; }
  .htl-trust-inner { gap: 24px; }
  .htl-filter-tabs { gap: 6px; justify-content: flex-start; overflow-x: auto; -webkit-overflow-scrolling: touch; scrollbar-width: none; padding-bottom: 8px; flex-wrap: nowrap; }
  .htl-filter-tabs::-webkit-scrollbar { display: none; }
  .htl-tab { padding: 8px 16px; font-size: 11px; flex: 0 0 auto; }
  .htl-banner-inner { padding: 48px 20px; }
  .htl-cta { padding: 56px 20px; }
  .htl-enquiry { padding: 64px 20px; }
  .htl-enquiry-form { grid-template-columns: 1fr; }
  .htl-form-group.full { grid-column: 1; }
  .htl-form-submit-row { flex-direction: column; align-items: stretch; }
  .htl-form-btn { justify-content: center; }
}
</style>
<style>
@keyframes htlCardIn {
  from { opacity: 0; transform: translateY(22px); }
  to   { opacity: 1; transform: translateY(0); }
}
</style>

@endpush

@section('content')

<!-- ===================================================
     HERO SLIDER
=================================================== -->
<x-hero-carousel
  :slides="[
    'https://images.unsplash.com/photo-1542314831-068cd1dbfeeb?w=1600&q=85',
    'https://images.unsplash.com/photo-1571003123894-1f0594d2b5d9?w=1600&q=85',
    'https://images.unsplash.com/photo-1564501049412-61c2a3083791?w=1600&q=85',
    'https://images.unsplash.com/photo-1549294413-26f195200c16?w=1600&q=85',
    'https://images.unsplash.com/photo-1520250497591-112f2f40a3f4?w=1600&q=85'
  ]"
  eyebrow="Curated Collection"
  title="Handpicked Hotels &amp;<br><em>Luxury Stays</em>"
  subtitle="From Himalayan retreats to beachside escapes — every property personally vetted for comfort, luxury &amp; value."
  :pills="['Shimla', 'Manali', 'Goa', 'Kasauli', 'Dalhousie', 'Mussoorie']"
/>

<!-- ===================================================
     TRUST BAR
=================================================== -->
<div class="htl-trust">
  <div class="htl-trust-inner">
    <div class="htl-trust-item"><span class="ti">★</span> Zero Hidden Fees</div>
    <div class="htl-trust-item"><span class="ti">★</span> Best Rate Guarantee</div>
    <div class="htl-trust-item"><span class="ti">★</span> 24/7 Support</div>
    <div class="htl-trust-item"><span class="ti">★</span> Flexible Changes</div>
    <div class="htl-trust-item"><span class="ti">★</span> Expert Curation</div>
  </div>
</div>

<!-- ===================================================
     HOTEL GRID
=================================================== -->
<section class="htl-section" id="hotels">
  <div class="htl-section-inner">
    <div class="htl-section-header">
      <p class="htl-eyebrow">Our Collection</p>
      <h2 class="htl-title">Find Your Perfect <em>Stay</em></h2>
      <p class="htl-desc">Every hotel in our collection is handpicked for its exceptional service, location and experience.</p>
    </div>
    <div class="htl-divider"><span>✦</span></div>

    <!-- Search -->
    <div class="htl-search-wrap" role="search">
      <label for="htlDestinationSearch">Search hotels by destination</label>
      <input type="search" id="htlDestinationSearch" placeholder="Search Shimla, Manali, Goa, Udaipur, Jaipur..." autocomplete="off">
      <span class="htl-search-pill">Search</span>
    </div>

    <!-- Filter Tabs -->
    <div class="htl-filter-tabs">
      <button class="htl-tab active" data-filter="all">All Hotels</button>
      <button class="htl-tab" data-filter="shimla">Shimla</button>
      <button class="htl-tab" data-filter="manali">Manali</button>
      <button class="htl-tab" data-filter="kasol">Kasol</button>
      <button class="htl-tab" data-filter="mussoorie">Mussoorie</button>
      <button class="htl-tab" data-filter="rishikesh">Rishikesh</button>
      <button class="htl-tab" data-filter="bhimtal">Bhimtal</button>
      <button class="htl-tab" data-filter="goa">Goa</button>
      <button class="htl-tab" data-filter="jaipur">Jaipur</button>
      <button class="htl-tab" data-filter="udaipur">Udaipur</button>
      <button class="htl-tab" data-filter="jibhi">Jibhi</button>
    </div>

    <!-- Hotel Grid -->
    <div class="htl-grid" id="htlGrid">

      @forelse($hotels as $hotel)
      @php
        $destination  = $hotel->destination?->name ?? 'Unknown';
        $slug         = Str::slug($destination);
        $images       = $hotel->images ?? collect();
        $imageCount   = $images->count();
        $firstImage   = $imageCount > 0
                          ? Storage::disk('public')->url($images->first()->path)
                          : null;
        $stars        = min((int) $hotel->star_rating, 5);
        $amenities    = $hotel->amenities ?? collect();
        $amenityNames = $amenities->pluck('name')->take(5)->implode('  ');
        $starLabel    = match(true) {
            $stars === 5 => '5-Star Hotel',
            $stars === 4 => '4-Star Hotel',
            $stars === 3 => '3-Star Hotel',
            $stars === 2 => '2-Star Hotel',
            $stars === 1 => '1-Star Hotel',
            default      => 'Hotel',
        };
      @endphp

@php
        $categoryLabel = match($hotel->category ?? '') {
            'beach_resort'    => '🏖️ Beach Resort',
            'city_luxury'     => '🏙️ City Luxury',
            'honeymoon'       => '💑 Honeymoon',
            'family_friendly' => '👨‍👩‍👧 Family',
            default           => null,
        };
      @endphp
      <a href="{{ route('hotel.details', $hotel->slug) }}"
         class="htl-card"
         data-category="{{ $slug }}"
         data-name="{{ Str::slug($hotel->title) }}"
         data-location="{{ $slug }}"
         data-amenities="{{ Str::slug($amenityNames) }}"
         style="text-decoration: none;">

        <!-- Image with overlay info -->
        <div class="htl-card-img-wrap">
          @if($firstImage)
            <img src="{{ $firstImage }}" alt="{{ $hotel->title }}, {{ $destination }}" loading="lazy" />
          @else
            <div style="width:100%; height:100%; background:linear-gradient(135deg,#1c1c1c,#252525); display:flex; align-items:center; justify-content:center; flex-direction:column; gap:12px;">
              <svg width="44" height="44" viewBox="0 0 24 24" fill="none" stroke="rgba(201,168,76,0.25)" stroke-width="1.2"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
              <span style="font-family:'Jost',sans-serif; font-size:11px; color:rgba(255,255,255,0.2); letter-spacing:0.1em; text-transform:uppercase;">No Photo Yet</span>
            </div>
          @endif

          {{-- Gradient overlay --}}
          <div class="htl-card-img-overlay"></div>

          {{-- Category badge --}}
          @if($categoryLabel)
          <div class="htl-card-badge">{{ $categoryLabel }}</div>
          @endif

          {{-- Heart --}}
          <button class="htl-heart" aria-label="Save to wishlist" onclick="event.preventDefault(); this.classList.toggle('active');">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
          </button>

          {{-- Hotel name & location on image --}}
          <div class="htl-card-img-info">
            <div class="htl-card-img-name">{{ $hotel->title }}</div>
            <div class="htl-card-img-loc">
              <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
              {{ $destination }}
            </div>
          </div>

          {{-- Photo counter --}}
          @if($imageCount > 1)
          <span class="htl-img-counter">📷 {{ $imageCount }} Photos</span>
          @endif
        </div>

        <!-- Body -->
        <div class="htl-card-body">

          {{-- Stars row --}}
          @if($stars > 0)
          <div class="htl-card-stars-row">
            <div class="htl-card-stars">
              @for($i = 0; $i < $stars; $i++) <span>★</span> @endfor
            </div>
            <span class="htl-card-star-label">{{ $starLabel }}</span>
          </div>
          @endif

          {{-- Amenity chips --}}
          @if($amenities->isNotEmpty())
          <div class="htl-amenity-chips">
            @foreach($amenities->take(4) as $am)
            <span class="htl-amenity-chip">{{ $am->name }}</span>
            @endforeach
            @if($amenities->count() > 4)
            <span class="htl-amenity-chip" style="color:var(--gold); border-color:rgba(201,168,76,0.3); background:rgba(201,168,76,0.06);">+{{ $amenities->count() - 4 }} more</span>
            @endif
          </div>
          @else
          <div style="flex:1;"></div>
          @endif

          {{-- Gold divider --}}
          <div class="htl-card-divider"></div>

          <!-- Footer -->
          <div class="htl-card-footer">
            <div class="htl-star-badge">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path><line x1="7" y1="7" x2="7.01" y2="7"></line></svg>
              Price on Request
            </div>
            <button class="htl-req-btn" onclick="event.preventDefault(); window.location.href=this.closest('a').href;">
              Enquire Now
              <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
            </button>
          </div>
        </div>
      </a>

      @empty
        <div style="grid-column: 1 / -1; text-align: center; padding: 60px 40px; color: var(--white-60);">
          <p style="font-family: 'Cormorant Garamond', serif; font-size: 1.8rem; color: #fff; margin-bottom: 12px;">No hotels listed yet</p>
          <p style="font-family: 'Jost', sans-serif; font-size: 14px; font-weight: 300;">Check back soon — our team is curating an exquisite collection for you.</p>
        </div>
      @endforelse

    </div>
  </div>
</section>


<!-- ===================================================
     FEATURED BANNER
=================================================== -->
<div class="htl-banner">
  <div class="htl-banner-inner">
    <div class="htl-banner-text">
      <p class="htl-eyebrow">Why Book With Us</p>
      <h2>More Than a Booking —<br><em>A Curated Experience</em></h2>
      <p class="htl-body">We don't just list hotels. We personally vet every property, negotiate the best rates and stay with you from enquiry to check-out. No hidden charges, no last-minute surprises.</p>
      <div class="htl-banner-btns">
        <a href="https://wa.me/919875073788" class="htl-btn-primary" target="_blank">WhatsApp Us Now</a>
      </div>
    </div>
    <div class="htl-banner-img">
      <img src="https://images.unsplash.com/photo-1564501049412-61c2a3083791?w=800&q=85" alt="Luxury Hotel Lobby" loading="lazy" />
    </div>
  </div>
</div>

<!-- ===================================================
     CTA STRIP
=================================================== -->
<section class="htl-cta">
  <div class="htl-cta-inner">
    <p class="htl-eyebrow">Ready to Travel</p>
    <h2>Your Dream Stay Is<br><em>One Message Away</em></h2>
    <p>Tell us your destination, dates and budget. Our travel experts will curate the perfect hotel options — usually within 2 hours.</p>
    <div class="htl-cta-btns">
      <a href="https://wa.me/919875073788" class="htl-wa-btn" target="_blank">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
        Chat on WhatsApp
      </a>
      <a href="tel:9875073788" class="htl-btn-outline">Call Us Directly</a>
    </div>
  </div>
</section>

@endsection

@push('scripts')
<script>
(function () {

  /* ===== FILTER TABS ===== */
  const tabs  = document.querySelectorAll('.htl-tab');
  const cards = document.querySelectorAll('.htl-card');
  const destinationSearch = document.getElementById('htlDestinationSearch');

  function applyHotelFilters(activeFilter) {
    const search = destinationSearch ? destinationSearch.value.trim().toLowerCase() : '';
    let delay = 0;

    cards.forEach(card => {
      const categoryMatch = activeFilter === 'all' || card.dataset.category === activeFilter;
      const searchText = [
        card.dataset.location,
        card.dataset.name,
        card.dataset.category,
        card.dataset.amenities,
      ].join(' ').toLowerCase();
      const searchMatch = !search || searchText.includes(search);
      const match = categoryMatch && searchMatch;

      if (match) {
        card.classList.remove('htl-hidden');
        card.style.animation = 'none';
        card.offsetHeight;
        card.style.animation = `htlCardIn 0.45s ease ${delay}ms both`;
        delay += 60;
      } else {
        card.classList.add('htl-hidden');
      }
    });
  }

  tabs.forEach(tab => {
    tab.addEventListener('click', () => {
      tabs.forEach(t => t.classList.remove('active'));
      tab.classList.add('active');
      applyHotelFilters(tab.dataset.filter);
    });
  });

  if (destinationSearch) {
    destinationSearch.addEventListener('input', () => {
      const activeTab = document.querySelector('.htl-tab.active');
      applyHotelFilters(activeTab ? activeTab.dataset.filter : 'all');
    });
  }

  /* ===== SCROLL REVEAL ===== */
  const revealObs = new IntersectionObserver((entries) => {
    entries.forEach((e, i) => {
      if (e.isIntersecting) {
        e.target.style.animation = `htlCardIn 0.55s ease ${i * 70}ms both`;
        revealObs.unobserve(e.target);
      }
    });
  }, { threshold: 0.08 });
  cards.forEach(c => revealObs.observe(c));

  /* ===== ENQUIRY FORM ===== */
  const form    = document.getElementById('htlEnquiryForm');
  const success = document.getElementById('htlFormSuccess');

  if (form) {
    form.addEventListener('submit', async (e) => {
      e.preventDefault();
      const submitBtn = form.querySelector('button[type="submit"]');
      const originalHtml = submitBtn.innerHTML;
      const name  = document.getElementById('htlName').value.trim();
      const phone = document.getElementById('htlPhone').value.trim();

      if (!name || !phone) {
        showToast('Validation Error', 'Please enter your name and phone number.', 'error');
        return;
      }

      submitBtn.innerHTML = 'Sending...';
      submitBtn.disabled = true;

      const dest    = document.getElementById('htlDestination').value || 'Not specified';
      const checkin = document.getElementById('htlCheckin').value || '';
      const guests  = document.getElementById('htlGuests').value || 'Not specified';
      const email   = document.getElementById('htlEmail').value.trim();
      const message = document.getElementById('htlMessage').value.trim();

      const wa = `Hi TYT Luxe! I'd like to enquire about a hotel stay.\n\nName: ${name}\nPhone: ${phone}${email ? '\nEmail: ' + email : ''}\nDestination: ${dest}\nCheck-in: ${checkin}\nGuests: ${guests}${message ? '\nRequirements: ' + message : ''}`;

      try {
          await fetch("{{ route('enquiries.store') }}", {
              method: 'POST',
              headers: {
                  'Content-Type': 'application/json',
                  'X-CSRF-TOKEN': '{{ csrf_token() }}'
              },
              body: JSON.stringify({
                  vertical: 'hotel',
                  reference_id: 0,
                  name: name,
                  phone: phone,
                  email: email,
                  checkin: checkin,
                  message: `Destination: ${dest}\nGuests: ${guests}\nRequirements: ${message}`
              })
          });
          
          window.open('https://wa.me/919875073788?text=' + encodeURIComponent(wa), '_blank');
    
          form.reset();
          submitBtn.innerHTML = originalHtml;
          submitBtn.disabled = false;
          showToast('Enquiry Sent', 'Thank you! Our travel expert will contact you within 2 hours with personalised hotel recommendations.');
      } catch (error) {
          console.error(error);
          submitBtn.innerHTML = originalHtml;
          submitBtn.disabled = false;
          showToast('Error', 'Something went wrong. Please try again.', 'error');
      }
    });
  }

})();
</script>
@endpush
