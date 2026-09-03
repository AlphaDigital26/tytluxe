@extends('layouts.frontend')

@section('meta_title', ($hotel->name ?? 'Luxury Hotel') . ' — Book Now | TYT Luxe')
@section('meta_description', 'Book ' . ($hotel->name ?? 'this luxury hotel') . ' with TYT Luxe. Handpicked for quality and comfort. Personalised service, best price guaranteed, and 2-hour WhatsApp response.')

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com" />
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;1,400;1,600&family=Jost:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<style>
/* ===== FLATPICKR THEME OVERRIDES ===== */
.flatpickr-calendar { background: #1c1c1c !important; border: 1px solid rgba(201,168,76,0.3) !important; box-shadow: 0 10px 30px rgba(0,0,0,0.8) !important; border-radius: 12px !important; }
.flatpickr-month { color: var(--gold) !important; fill: var(--gold) !important; }
.flatpickr-current-month .flatpickr-monthDropdown-months { font-family: 'Jost', sans-serif !important; }
.flatpickr-current-month input.cur-year { font-family: 'Jost', sans-serif !important; color: var(--gold) !important; }
span.flatpickr-weekday { color: var(--white-60) !important; font-family: 'Jost', sans-serif !important; font-weight: 500 !important; }
.flatpickr-day { color: #fff !important; font-family: 'Jost', sans-serif !important; border-radius: 6px !important; }
.flatpickr-day.inRange { background: rgba(201,168,76,0.15) !important; border-color: rgba(201,168,76,0.15) !important; box-shadow: none !important; }
.flatpickr-day.selected, .flatpickr-day.startRange, .flatpickr-day.endRange, .flatpickr-day.selected.inRange, .flatpickr-day.startRange.inRange, .flatpickr-day.endRange.inRange, .flatpickr-day.selected:focus, .flatpickr-day.startRange:focus, .flatpickr-day.endRange:focus, .flatpickr-day.selected:hover, .flatpickr-day.startRange:hover, .flatpickr-day.endRange:hover, .flatpickr-day.selected.prevMonthDay, .flatpickr-day.startRange.prevMonthDay, .flatpickr-day.endRange.prevMonthDay, .flatpickr-day.selected.nextMonthDay, .flatpickr-day.startRange.nextMonthDay, .flatpickr-day.endRange.nextMonthDay {
    background: var(--gold) !important; color: var(--dark) !important; border-color: var(--gold) !important; font-weight: 600 !important;
}
.flatpickr-day:hover { background: rgba(255,255,255,0.1) !important; }
.flatpickr-day.flatpickr-disabled, .flatpickr-day.flatpickr-disabled:hover { color: rgba(255,255,255,0.2) !important; }
.flatpickr-months .flatpickr-prev-month, .flatpickr-months .flatpickr-next-month { fill: var(--gold) !important; color: var(--gold) !important; }
.flatpickr-months .flatpickr-prev-month:hover svg, .flatpickr-months .flatpickr-next-month:hover svg { fill: var(--gold-light) !important; }

/* Date input custom style to match theme */
.hd-date-input {
  width: 100%; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); 
  border-radius: 10px; padding: 13px 16px; color: #fff; cursor: pointer; 
  font-family: 'Jost', sans-serif; font-size: 13.5px; font-weight: 300; outline: none;
}
.hd-date-input:focus { border-color: var(--gold); }

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
.hd-room-btn:disabled { opacity: 0.65; cursor: wait; }
.hd-room-btn { gap: 8px; }
.hd-room-btn-spinner { width: 13px; height: 13px; border: 2px solid rgba(201,168,76,0.3); border-top-color: var(--gold); border-radius: 50%; animation: hdBtnSpin 0.7s linear infinite; display: none; }
.hd-room-btn.loading .hd-room-btn-spinner { display: inline-block; }
.hd-room-btn.loading .hd-room-btn-label { display: none; }
.hd-room-btn.loading:hover { background: transparent; color: var(--gold); }
@keyframes hdBtnSpin { to { transform: rotate(360deg); } }
.hd-room-more-btn {
  display: inline-block; font-family: 'Jost', sans-serif; font-size: 12.5px;
  color: var(--gold); text-decoration: none; border-bottom: 1px dashed var(--gold);
  margin-top: -4px; margin-bottom: 14px; cursor: pointer; transition: all var(--tr);
}
.hd-room-more-btn:hover { color: #fff; border-bottom-color: #fff; }

.hd-room-gallery {
  display: flex; overflow-x: auto; scroll-snap-type: x mandatory; gap: 12px;
  margin-bottom: 20px; padding-bottom: 8px;
  -ms-overflow-style: none; scrollbar-width: none;
}
.hd-room-gallery::-webkit-scrollbar { display: none; }
.hd-room-gallery img {
  width: 100%; flex-shrink: 0; scroll-snap-align: start;
  border-radius: 12px; max-height: 280px; object-fit: cover;
}

/* ===== PLACES SECTIONS (Nearby, Restaurants, Top Attractions) ===== */
.hd-places-grid {
  display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px;
}
.hd-place-card {
  background: var(--dark-2);
  border: 1px solid rgba(255,255,255,0.07);
  border-radius: 12px; padding: 16px;
  display: flex; align-items: flex-start; gap: 12px;
  transition: border-color var(--tr), box-shadow var(--tr);
  cursor: default;
}
.hd-place-card:hover {
  border-color: rgba(201,168,76,0.35);
  box-shadow: 0 4px 20px rgba(201,168,76,0.08);
}
.hd-place-icon {
  width: 36px; height: 36px; border-radius: 8px;
  background: rgba(201,168,76,0.12);
  display: flex; align-items: center; justify-content: center;
  font-size: 16px; flex-shrink: 0; margin-top: 1px;
}
.hd-place-info { flex: 1; min-width: 0; }
.hd-place-name {
  font-family: 'Jost', sans-serif; font-size: 13px; font-weight: 500;
  color: var(--white-80); line-height: 1.4;
  white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.hd-place-dist {
  font-family: 'Jost', sans-serif; font-size: 11.5px; font-weight: 300;
  color: var(--gold); margin-top: 3px; letter-spacing: 0.02em;
}
/* Top-attraction cards get a gold left-border accent */
.hd-place-card.top {
  border-left: 2px solid rgba(201,168,76,0.45);
}
.hd-place-card.top:hover { border-left-color: var(--gold); }
@media (max-width: 1024px) { .hd-places-grid { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 500px)  { .hd-places-grid { grid-template-columns: 1fr; } }

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
.hd-book-trust {
  display: flex; flex-wrap: wrap; justify-content: center; gap: 8px 12px;
  margin-top: 18px; padding-top: 16px; border-top: 1px dashed rgba(255,255,255,0.08);
}
.hd-trust-item {
  display: inline-flex; align-items: center; gap: 5px;
  font-family: 'Jost', sans-serif; font-size: 11px; color: var(--white-60);
}
.hd-trust-item svg { color: var(--gold); }

/* ===== GUEST SELECTOR POPOVER ===== */
.hd-guest-btn {
  background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1);
  border-radius: 10px; padding: 13px 16px; color: #fff; cursor: pointer;
  font-family: 'Jost', sans-serif; font-size: 13.5px; font-weight: 300;
  display: flex; justify-content: space-between; align-items: center;
}
.hd-guest-btn::after { content: '▼'; font-size: 10px; color: var(--gold); }
.hd-guest-popover {
  position: absolute; top: calc(100% + 5px); left: 0; width: 100%; z-index: 100;
  background: #1c1c1c; border: 1px solid rgba(201,168,76,0.3); border-radius: 12px;
  padding: 14px; box-shadow: 0 10px 30px rgba(0,0,0,0.8);
  max-height: 350px; overflow-y: auto; display: none;
  -ms-overflow-style: none; scrollbar-width: none;
}
.hd-guest-popover::-webkit-scrollbar { display: none; }
.hd-guest-popover.open { display: block; }
.hd-guest-room { border-bottom: 1px dashed rgba(255,255,255,0.1); padding-bottom: 12px; margin-bottom: 12px; }
.hd-guest-room:last-of-type { border-bottom: none; padding-bottom: 0; margin-bottom: 0; }
.hd-guest-room-header {
  display: flex; justify-content: space-between; align-items: center;
  font-family: 'Cormorant Garamond', serif; font-size: 1.15rem; color: var(--gold);
  margin-bottom: 10px; font-weight: 500;
}
.hd-guest-room-del {
  background: transparent; border: none; color: var(--white-60); cursor: pointer; font-size: 14px; padding: 0 5px;
}
.hd-guest-room-del:hover { color: #fff; }
.hd-guest-row {
  display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;
}
.hd-guest-label { font-family: 'Jost', sans-serif; font-size: 12.5px; color: #fff; display: flex; flex-direction: column; }
.hd-guest-label small { font-size: 10.5px; color: var(--white-60); }
.hd-guest-ctrl { display: flex; align-items: center; gap: 12px; }
.hd-guest-ctrl button {
  background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1);
  color: #fff; width: 28px; height: 28px; border-radius: 6px; cursor: pointer;
  display: flex; align-items: center; justify-content: center; font-size: 14px;
}
.hd-guest-ctrl button:hover { border-color: var(--gold); color: var(--gold); }
.hd-guest-ctrl span { color: #fff; font-family: 'Jost', sans-serif; font-size: 13.5px; width: 14px; text-align: center; }
.hd-guest-child-ages { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-top: 8px; }
.hd-guest-child-ages select {
  background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1);
  color: #fff; padding: 8px 10px; border-radius: 8px; font-family: 'Jost', sans-serif;
  font-size: 12px; outline: none;
}
.hd-guest-actions {
  display: flex; justify-content: space-between; align-items: center;
  border-top: 1px solid rgba(255,255,255,0.1); margin-top: 16px; padding-top: 16px;
}
.hd-guest-add-btn {
  background: transparent; border: none; color: var(--gold); font-family: 'Jost', sans-serif;
  font-size: 12px; font-weight: 600; text-transform: uppercase; cursor: pointer; letter-spacing: 0.1em; padding: 0;
}
.hd-guest-done-btn {
  background: var(--gold); color: var(--dark); border: none; border-radius: 100px;
  font-family: 'Jost', sans-serif; font-size: 12px; font-weight: 600; text-transform: uppercase;
  padding: 8px 16px; cursor: pointer; letter-spacing: 0.1em;
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
  -ms-overflow-style: none; scrollbar-width: none; /* Hide scrollbar for IE, Edge and Firefox */
}
.hd-modal::-webkit-scrollbar { display: none; /* Hide scrollbar for Chrome, Safari and Opera */ }
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

  $amenities    = $hotel->amenities ?? collect();
  $cancelDate   = now()->addDays(14)->format('d M Y');
  $ratingScore  = number_format(min(5, max(1, ($stars * 0.92))), 1);
  $ratingLabel  = $stars >= 5 ? 'Exceptional' : ($stars >= 4 ? 'Excellent' : ($stars >= 3 ? 'Very Good' : 'Good'));
  // Room categories — stored as newline-separated text in DB
  $roomCats     = array_filter(array_map('trim', explode("\n", $hotel->room_categories ?? '')));
  // Nearby attractions, restaurants & top attractions — stored as newline-separated text in DB
  $nearbyAttr       = array_filter(array_map('trim', explode("\n", $hotel->nearby_attractions   ?? '')));
  $restaurantsCafes = array_filter(array_map('trim', explode("\n", $hotel->restaurants_cafes   ?? '')));
  $topAttractions   = array_filter(array_map('trim', explode("\n", $hotel->top_attractions     ?? '')));
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

{{-- ===================================================
     BREADCRUMB / BACK NAV
=================================================== --}}
<div style="background:var(--dark-2); border-bottom:1px solid rgba(255,255,255,0.05); padding:12px 48px;">
  <a href="{{ route('hotels', array_filter(['destination' => $destination, 'check_in' => $checkIn, 'check_out' => $checkOut, 'adults' => $adults, 'children' => $children, 'rooms' => $roomCount])) }}"
     style="display:inline-flex; align-items:center; gap:7px; font-family:'Jost',sans-serif; font-size:12.5px; color:rgba(255,255,255,0.5); text-decoration:none; transition:color 0.25s ease;"
     onmouseover="this.style.color='#c9a84c'" onmouseout="this.style.color='rgba(255,255,255,0.5)'">
    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
    Hotels
  </a>
  <span style="margin:0 10px; color:rgba(255,255,255,0.2); font-size:12px;">&#47;</span>
  <span style="font-family:'Jost',sans-serif; font-size:12.5px; color:rgba(255,255,255,0.35);">{{ $hotel->title }}</span>
</div>

<!-- ===================================================
     HERO GALLERY & HEADER
=================================================== -->
<div class="hd-layout-top" style="max-width:1280px; margin:0 auto; padding:40px 40px 0;">
  
  <div class="hd-badge-row" style="margin-bottom: 14px;">
    <span class="hd-badge">{{ $catLabel }}</span>
    @if($hotel->is_featured)
      <span class="hd-badge-outline">Featured</span>
    @endif
  </div>

  <!-- Title & Location -->
  <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:20px; flex-wrap:wrap; gap:16px;">
    <div>
      <div style="display:flex; align-items:center; gap:12px; margin-bottom:8px;">
        <h1 class="hd-hero-title" style="margin:0; font-size:clamp(2rem, 4vw, 2.8rem); line-height:1.1;">{{ $hotel->title }}</h1>
        <div class="hd-stars" style="display:flex; gap:3px; color:var(--gold); font-size:16px; margin-top:6px;">
          @for($i = 0; $i < $stars; $i++) <span>★</span> @endfor
        </div>
      </div>
      <div style="font-family:'Jost',sans-serif; font-size:14.5px; color:var(--white-60); display:flex; align-items:center; gap:8px;">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/></svg>
        {{ $hotel->address ?? $destination }}
        <a href="#" style="color:var(--gold); text-decoration:none; border-bottom:1px dashed var(--gold); margin-left:8px;">Show on map</a>
      </div>
    </div>
    <button class="hd-favourite-btn" style="background:transparent; border:1px solid rgba(255,255,255,0.2); color:#fff; border-radius:100px; padding:10px 18px; display:flex; align-items:center; gap:8px; font-family:'Jost',sans-serif; font-size:14px; cursor:pointer; transition:all 0.3s;" onmouseover="this.style.borderColor='var(--gold)'; this.style.color='var(--gold)';" onmouseout="this.style.borderColor='rgba(255,255,255,0.2)'; this.style.color='#fff';">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
      Favourite
    </button>
  </div>

  <!-- Masonry Gallery -->
  <div class="hd-masonry-gallery" style="display:grid; grid-template-columns: 2fr 1fr; gap:12px; height:50vh; min-height:400px; border-radius:24px; overflow:hidden;">
    <!-- Large image -->
    <div style="position:relative; width:100%; height:100%;">
      @php
        $mainImg = $imageCount > 0 ? $images[0] : null;
        $mainImgUrl = $mainImg ? (Str::startsWith($mainImg->path, ['http://', 'https://']) ? $mainImg->path : Storage::disk('public')->url($mainImg->path)) : 'https://images.unsplash.com/photo-1542314831-068cd1dbfeeb?w=1600&q=85';
      @endphp
      <img src="{{ $mainImgUrl }}" alt="{{ $hotel->title }}" style="width:100%; height:100%; object-fit:cover; display:block;" />
    </div>
    
    <!-- Small images -->
    <div style="display:grid; grid-template-rows:1fr 1fr; gap:12px; height:100%;">
      @php
        $img2 = $imageCount > 1 ? $images[1] : $mainImg;
        $img2Url = $img2 ? (Str::startsWith($img2->path, ['http://', 'https://']) ? $img2->path : Storage::disk('public')->url($img2->path)) : $mainImgUrl;
        
        $img3 = $imageCount > 2 ? $images[2] : $mainImg;
        $img3Url = $img3 ? (Str::startsWith($img3->path, ['http://', 'https://']) ? $img3->path : Storage::disk('public')->url($img3->path)) : $mainImgUrl;
      @endphp
      <div style="width:100%; height:100%; position:relative;">
        <img src="{{ $img2Url }}" alt="{{ $hotel->title }}" style="width:100%; height:100%; object-fit:cover; display:block;" />
      </div>
      <div style="width:100%; height:100%; position:relative;">
        <img src="{{ $img3Url }}" alt="{{ $hotel->title }}" style="width:100%; height:100%; object-fit:cover; display:block;" />
        @if($imageCount > 3)
          <button style="position:absolute; bottom:16px; right:16px; background:rgba(0,0,0,0.6); backdrop-filter:blur(8px); border:1px solid rgba(255,255,255,0.2); color:#fff; font-family:'Jost',sans-serif; font-size:13px; font-weight:600; padding:8px 16px; border-radius:100px; cursor:pointer; transition:all 0.3s;" onmouseover="this.style.background='var(--gold)'; this.style.color='var(--dark)';" onmouseout="this.style.background='rgba(0,0,0,0.6)'; this.style.color='#fff';">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:middle; margin-right:4px; margin-top:-2px;"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
            +{{ $imageCount - 3 }} photos
          </button>
        @endif
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
      <div class="hd-desc">{!! $hotel->description !!}</div>
    </div>

    <!-- Mandatory Fees — charged at the property, not included in the room rate -->
    @if(!empty($hotel->mandatory_fees))
    <div class="hd-section">
      <div style="padding:18px 20px; background:rgba(201,168,76,0.06); border:1px solid var(--gold-dim); border-radius:14px;">
        <p style="font-family:'Jost',sans-serif; font-size:11px; font-weight:700; letter-spacing:0.1em; text-transform:uppercase; color:var(--gold); margin-bottom:8px;">Fees Payable at the Property</p>
        <div style="font-family:'Jost',sans-serif; font-size:13.5px; color:var(--white-80); line-height:1.6;">{!! $hotel->mandatory_fees !!}</div>
      </div>
    </div>
    @endif

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

    <!-- Live TripJack Room Options -->
    @if($hotel->source === 'tripjack')
    <div class="hd-section" id="htl-room-section">
      <h2 class="hd-section-title">Available Rooms</h2>

      @if(session('booking_error'))
      <div style="margin-bottom:18px; padding:14px 18px; border-radius:12px; background:rgba(220,80,80,0.08); border:1px solid rgba(220,80,80,0.3); color:#f3a3a3; font-family:'Jost',sans-serif; font-size:13.5px;">
        {{ session('booking_error') }}
      </div>
      @endif

      @if(($liveOptions ?? collect())->isNotEmpty())
      <div class="hd-room-list">
        @php
          $groupedOptions = collect($liveOptions)->groupBy(function($option) {
              return collect($option['roomInfo'] ?? [])->pluck('name')->unique()->implode(' + ') ?: 'Standard Room';
          });
        @endphp

        @foreach($groupedOptions as $roomName => $options)
          @php
            // Attempt to find a matching local room type to pull an image and description
            $localRoom = null;
            if($hotel->roomTypes) {
                // simple fuzzy match on name
                $localRoom = $hotel->roomTypes->first(function($rt) use ($roomName) {
                    return str_contains(strtolower($roomName), strtolower($rt->name)) || str_contains(strtolower($rt->name), strtolower($roomName));
                });
            }
            $roomImage = $localRoom && $localRoom->image_path ? Storage::disk('public')->url($localRoom->image_path) : 'https://images.unsplash.com/photo-1578683010236-d716f9a3f461?w=600&q=80'; // Placeholder
          @endphp
          
          <div class="hd-room-group-card" style="background: var(--dark-2); border: 1px solid rgba(255,255,255,0.08); border-radius: 16px; overflow: hidden; margin-bottom: 24px;">
            <div style="display: flex; flex-direction: column; @media(min-width: 992px) { flex-direction: row; }">
              
              <!-- Left Column: Room Info -->
              <div style="width: 100%; max-width: 320px; border-right: 1px solid rgba(255,255,255,0.08); padding: 20px;">
                <h3 style="font-family: 'Cormorant Garamond', serif; font-size: 1.6rem; color: #fff; margin-bottom: 12px; line-height: 1.2;">{{ $roomName }}</h3>
                <div style="border-radius: 12px; overflow: hidden; height: 180px; margin-bottom: 16px; position: relative;">
                  <img src="{{ $roomImage }}" alt="{{ $roomName }}" style="width: 100%; height: 100%; object-fit: cover;">
                </div>
                <div class="hd-room-specs" style="display: flex; flex-wrap: wrap; gap: 10px;">
                  @if($localRoom && $localRoom->room_size)
                  <span style="font-family: 'Jost', sans-serif; font-size: 13px; color: var(--white-80); display: flex; align-items: center; gap: 6px;"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16v16H4z M4 9h16 M9 4v16"/></svg>{{ $localRoom->room_size }}</span>
                  @endif
                  @if($localRoom && $localRoom->bed_type)
                  <span style="font-family: 'Jost', sans-serif; font-size: 13px; color: var(--white-80); display: flex; align-items: center; gap: 6px;"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 4v16M22 4v16M2 8h20M6 4v4M18 4v4"/></svg>{{ $localRoom->bed_type }}</span>
                  @endif
                </div>
                @if($localRoom && $localRoom->description)
                <div style="font-family: 'Jost', sans-serif; font-size: 12px; color: var(--white-60); margin-top: 12px; line-height: 1.5; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;">
                  {!! strip_tags($localRoom->description) !!}
                </div>
                @endif
              </div>

              <!-- Right Columns: Options List -->
              <div style="flex: 1; display: flex; flex-direction: column;">
                @foreach($options->sortBy('pricing.totalPrice') as $index => $option)
                  @php
                    $pricing = $option['pricing'] ?? [];
                    $cancellation = $option['cancellation'] ?? [];
                    $compliance = $option['compliance'] ?? [];
                    $isRefundable = $cancellation['isRefundable'] ?? false;
                    $freeUntil = collect($cancellation['penalties'] ?? [])->firstWhere('amount', 0);
                    $mealBasis = $option['mealBasis'] ?? 'Room Only';
                  @endphp
                  
                  <div style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; padding: 20px; border-bottom: 1px solid rgba(255,255,255,0.05); {{ $loop->last ? 'border-bottom: none;' : '' }}">
                    
                    <!-- Option Details (Middle Column) -->
                    <div style="flex: 1; min-width: 200px; padding-right: 20px;">
                      <div style="font-family: 'Jost', sans-serif; font-size: 14.5px; font-weight: 600; color: #fff; margin-bottom: 8px;">
                        {{ $mealBasis }} | {{ $isRefundable ? 'Refundable' : 'Non-Refundable' }}
                      </div>
                      
                      <div style="display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 12px;">
                        @if($isRefundable)
                        <span style="display: inline-flex; align-items: center; gap: 4px; font-family: 'Jost', sans-serif; font-size: 11.5px; color: var(--green); background: rgba(74, 222, 128, 0.08); padding: 4px 10px; border-radius: 100px;">
                          ✅ Free cancellation @if($freeUntil) until {{ \Illuminate\Support\Carbon::parse($freeUntil['to'])->format('d M') }} @endif
                        </span>
                        @else
                        <span style="display: inline-flex; align-items: center; gap: 4px; font-family: 'Jost', sans-serif; font-size: 11.5px; color: #f87171; background: rgba(248, 113, 113, 0.08); padding: 4px 10px; border-radius: 100px;">
                          ❌ Non-refundable
                        </span>
                        @endif
                      </div>

                      @if(!empty($option['inclusions']))
                      <div class="hd-room-inc" style="margin-bottom: 8px;">
                        @foreach(array_slice($option['inclusions'], 0, 3) as $inc)
                          <span><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M20 6L9 17l-5-5"/></svg> {{ $inc }}</span>
                        @endforeach
                      </div>
                      @endif
                      
                      @if(($compliance['panRequired'] ?? false) || ($compliance['passportRequired'] ?? false))
                      <div style="font-family: 'Jost', sans-serif; font-size: 11.5px; color: var(--gold); margin-top: 8px;">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right:4px; vertical-align:middle;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        @if($compliance['panRequired'] ?? false) PAN Required @endif
                        @if($compliance['passportRequired'] ?? false) | Passport Required @endif
                      </div>
                      @endif
                    </div>

                    <!-- Pricing & Select (Right Column) -->
                    <div style="text-align: right; min-width: 150px; border-left: 1px dashed rgba(255,255,255,0.1); padding-left: 20px;">
                      <div style="font-family: 'Jost', sans-serif; font-size: 12px; color: var(--white-60); margin-bottom: 2px;">Total Price</div>
                      <div style="font-family: 'Jost', sans-serif; font-size: 1.6rem; font-weight: 700; color: #fff; line-height: 1; margin-bottom: 12px;">
                        {{ $pricing['currency'] ?? 'INR' }} {{ number_format($pricing['totalPrice'] ?? 0) }}
                      </div>
                      <form method="POST" action="{{ route('hotel.review', $hotel->slug) }}" class="hd-select-room-form">
                        @csrf
                        <input type="hidden" name="option_id" value="{{ $option['optionId'] ?? '' }}">
                        <input type="hidden" name="check_in" value="{{ $checkIn }}">
                        <input type="hidden" name="check_out" value="{{ $checkOut }}">
                        <input type="hidden" name="adults" value="{{ $adults }}">
                        <input type="hidden" name="children" value="{{ $children }}">
                        <input type="hidden" name="rooms" value="{{ $roomCount }}">
                        <button type="submit" class="hd-room-btn" style="width: 100%; border-radius: 8px; padding: 10px 16px;">
                          <span class="hd-room-btn-spinner"></span>
                          <span class="hd-room-btn-label">Select Room</span>
                        </button>
                      </form>
                    </div>

                  </div>
                @endforeach
              </div>
            </div>
          </div>
        @endforeach
      </div>
      @elseif(!empty($pricingError))
      <div style="padding:20px 22px; background:rgba(201,168,76,0.06); border:1px solid var(--gold-dim); border-radius:14px; font-family:'Jost',sans-serif; font-size:13.5px; color:var(--white-60); line-height:1.6;">
        {{ $pricingError }}
      </div>
      @elseif(!empty($checkIn) && !empty($checkOut))
      <div style="padding:20px 22px; background:rgba(201,168,76,0.06); border:1px solid var(--gold-dim); border-radius:14px; font-family:'Jost',sans-serif; font-size:13.5px; color:var(--white-60); line-height:1.6;">
        No rooms are available for this hotel on {{ $checkIn }} – {{ $checkOut }}.
        <a href="{{ route('hotels', array_filter(['destination' => $destination, 'check_in' => $checkIn, 'check_out' => $checkOut])) }}" style="color:var(--gold);">Try different dates</a>, or send us an enquiry and we'll check alternatives for you.
      </div>
      @else
      <div style="padding:20px 22px; background:rgba(201,168,76,0.06); border:1px solid var(--gold-dim); border-radius:14px; font-family:'Jost',sans-serif; font-size:13.5px; color:var(--white-60); line-height:1.6;">
        <a href="{{ route('hotels', ['destination' => $destination]) }}" style="color:var(--gold);">Search dates for {{ $destination }}</a> to see available rooms, meal plans and live prices for this hotel.
      </div>
      @endif
    </div>
    @endif

    <!-- Room Categories / Types -->
    @if($hotel->roomTypes && $hotel->roomTypes->where('is_active', true)->count() > 0)
    <div class="hd-section" id="htl-static-rooms-section">
      {{-- Heading differs from the live TripJack section ("Available Rooms") to avoid confusion --}}
      <h2 class="hd-section-title">Room Types</h2>
      <div class="hd-room-list">
        @foreach($hotel->roomTypes->where('is_active', true) as $room)
          @php
             $roomImage = $room->image_path ? Storage::disk('public')->url($room->image_path) : 'https://images.unsplash.com/photo-1578683010236-d716f9a3f461?w=600&q=80';
          @endphp
          
          <div class="hd-room-group-card" style="background: var(--dark-2); border: 1px solid rgba(255,255,255,0.08); border-radius: 16px; overflow: hidden; margin-bottom: 24px;">
            <div style="display: flex; flex-direction: column; @media(min-width: 992px) { flex-direction: row; }">
              
              <!-- Left Column: Room Info -->
              <div style="width: 100%; max-width: 320px; border-right: 1px solid rgba(255,255,255,0.08); padding: 20px;">
                <h3 style="font-family: 'Cormorant Garamond', serif; font-size: 1.6rem; color: #fff; margin-bottom: 12px; line-height: 1.2;">{{ $room->name }}</h3>
                <div style="border-radius: 12px; overflow: hidden; height: 180px; margin-bottom: 16px; position: relative;">
                  <img src="{{ $roomImage }}" alt="{{ $room->name }}" style="width: 100%; height: 100%; object-fit: cover;">
                </div>
                <div class="hd-room-specs" style="display: flex; flex-wrap: wrap; gap: 10px;">
                  @if($room->room_size)
                  <span style="font-family: 'Jost', sans-serif; font-size: 13px; color: var(--white-80); display: flex; align-items: center; gap: 6px;"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16v16H4z M4 9h16 M9 4v16"/></svg>{{ $room->room_size }}</span>
                  @endif
                  @if($room->bed_type)
                  <span style="font-family: 'Jost', sans-serif; font-size: 13px; color: var(--white-80); display: flex; align-items: center; gap: 6px;"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 4v16M22 4v16M2 8h20M6 4v4M18 4v4"/></svg>{{ $room->bed_type }}</span>
                  @endif
                </div>
                @if($room->description)
                <div style="font-family: 'Jost', sans-serif; font-size: 12px; color: var(--white-60); margin-top: 12px; line-height: 1.5; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;">
                  {!! strip_tags($room->description) !!}
                </div>
                <a class="hd-room-more-btn" data-modal="hdRoomModal_{{ $room->id }}" style="margin-top: 8px;">More Details</a>
                @endif
              </div>

              <!-- Right Columns: Action -->
              <div style="flex: 1; display: flex; flex-direction: column;">
                <div style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; padding: 20px;">
                  <div style="flex: 1; min-width: 200px; padding-right: 20px;">
                    <div style="font-family: 'Jost', sans-serif; font-size: 14.5px; font-weight: 600; color: #fff; margin-bottom: 8px;">
                      Standard Rate
                    </div>
                    @if($room->cancellation_policy)
                    <div style="display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 12px;">
                      <span style="display: inline-flex; align-items: center; gap: 4px; font-family: 'Jost', sans-serif; font-size: 11.5px; color: {{ $room->cancellation_policy == 'free_cancellation' ? 'var(--green)' : '#f87171' }}; background: {{ $room->cancellation_policy == 'free_cancellation' ? 'rgba(74, 222, 128, 0.08)' : 'rgba(248, 113, 113, 0.08)' }}; padding: 4px 10px; border-radius: 100px;">
                        @if($room->cancellation_policy == 'free_cancellation') ✅ @elseif($room->cancellation_policy == 'non_refundable') ❌ @else ⚠️ @endif
                        {{ str_replace('_', ' ', Str::title($room->cancellation_policy)) }}
                      </span>
                    </div>
                    @endif
                    @if($room->inclusions && count($room->inclusions) > 0)
                    <div class="hd-room-inc" style="margin-bottom: 8px;">
                      @foreach(array_slice($room->inclusions, 0, 4) as $inc)
                        <span><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M20 6L9 17l-5-5"/></svg> {{ $inc }}</span>
                      @endforeach
                    </div>
                    @endif
                  </div>
                  
                  <div style="text-align: right; min-width: 150px; border-left: 1px dashed rgba(255,255,255,0.1); padding-left: 20px;">
                    <button class="hd-room-btn" type="button" style="width: 100%; border-radius: 8px; padding: 10px 16px;" onclick="document.getElementById('hdEnquirySection').scrollIntoView({behavior:'smooth', block:'start'})">
                      Enquire
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Room Details Modal -->
          <div class="hd-modal-backdrop hd-room-details-modal" id="hdRoomModal_{{ $room->id }}" role="dialog" aria-modal="true">
            <div class="hd-modal">
              <button class="hd-modal-close hd-room-modal-close" aria-label="Close">✕</button>
              <h2 class="hd-modal-title" style="font-size: 1.8rem; margin-bottom: 16px;">{{ $room->name }}</h2>
              
              @php
                $roomImages = collect();
                if ($room->image_path) $roomImages->push($room->image_path);
                if (is_array($room->images)) {
                  foreach ($room->images as $img) $roomImages->push($img);
                }
              @endphp

              @if($roomImages->count() > 0)
                <div class="hd-room-gallery">
                  @foreach($roomImages as $img)
                    <img src="{{ Storage::disk('public')->url($img) }}" alt="{{ $room->name }} Image">
                  @endforeach
                </div>
              @endif
              
              <div class="hd-room-specs" style="margin-bottom: 24px; border-bottom: 1px dashed rgba(255,255,255,0.1); padding-bottom: 20px;">
                @if($room->room_size)
                  <span class="hd-room-spec"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16v16H4z M4 9h16 M9 4v16"/></svg>{{ $room->room_size }}</span>
                @endif
                @if($room->bed_type)
                  <span class="hd-room-spec"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 4v16M22 4v16M2 8h20M6 4v4M18 4v4"/></svg>{{ $room->bed_type }}</span>
                @endif
                <span class="hd-room-spec"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2 M12 3a4 4 0 1 0 0 8 4 4 0 0 0 0-8z"/></svg>{{ $room->occupancy_adults }} Adults @if($room->occupancy_children) , {{ $room->occupancy_children }} Child @endif</span>
              </div>
              
              @if($room->description)
                <h3 style="font-family: 'Jost', sans-serif; font-size: 14px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.1em; color: var(--gold); margin-bottom: 12px;">About this room</h3>
                <p style="font-family: 'Jost', sans-serif; font-size: 14px; color: var(--white-80); line-height: 1.6; margin-bottom: 24px;">
                    {!! $room->description !!}
                </p>
              @endif
              
              @if($room->inclusions && count($room->inclusions) > 0)
                <h3 style="font-family: 'Jost', sans-serif; font-size: 14px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.1em; color: var(--gold); margin-bottom: 14px;">Inclusions & Amenities</h3>
                <div class="hd-room-inc" style="gap: 12px; margin-bottom: 24px;">
                  @foreach($room->inclusions as $inc)
                    <span><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M20 6L9 17l-5-5"/></svg> {{ $inc }}</span>
                  @endforeach
                </div>
              @endif
              
              {{-- Close modal and scroll to enquiry section, not trigger the modal button --}}
              <button class="hd-room-btn" style="margin-top: 10px;" type="button"
                onclick="document.getElementById('hdRoomModal_{{ $room->id }}').classList.remove('open'); document.body.style.overflow=''; document.getElementById('hdEnquirySection').scrollIntoView({behavior:'smooth', block:'start'});">Enquire About This Room</button>
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

    <!-- ===== NEARBY ATTRACTIONS ===== -->
    @if(count($nearbyAttr) > 0)
    <div class="hd-section">
      <h2 class="hd-section-title">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" style="color:var(--gold);flex-shrink:0;">
          <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/>
        </svg>
        Nearby Attractions
      </h2>
      <div class="hd-places-grid">
        @foreach($nearbyAttr as $attr)
          @php
            // Split "Name (dist)" or "Name — dist" or "Name - dist" into name + distance
            $parts = preg_split('/\s*[\(\-—]\s*/', $attr, 2);
            $placeName = trim($parts[0]);
            $placeDist = isset($parts[1]) ? trim(rtrim($parts[1], ')')) : '';
          @endphp
          <div class="hd-place-card">
            <div class="hd-place-icon">📍</div>
            <div class="hd-place-info">
              <div class="hd-place-name" title="{{ $placeName }}">{{ $placeName }}</div>
              @if($placeDist)<div class="hd-place-dist">{{ $placeDist }}</div>@endif
            </div>
          </div>
        @endforeach
      </div>
    </div>
    @endif

    <!-- ===== RESTAURANTS & CAFÉS ===== -->
    @if(count($restaurantsCafes) > 0)
    <div class="hd-section">
      <h2 class="hd-section-title">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" style="color:var(--gold);flex-shrink:0;">
          <path d="M18 8h1a4 4 0 0 1 0 8h-1"/><path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z"/><line x1="6" y1="1" x2="6" y2="4"/><line x1="10" y1="1" x2="10" y2="4"/><line x1="14" y1="1" x2="14" y2="4"/>
        </svg>
        Restaurants &amp; Caf&eacute;s
      </h2>
      <div class="hd-places-grid">
        @foreach($restaurantsCafes as $item)
          @php
            $parts = preg_split('/\s*[\(\-—]\s*/', $item, 2);
            $placeName = trim($parts[0]);
            $placeDist = isset($parts[1]) ? trim(rtrim($parts[1], ')')) : '';
          @endphp
          <div class="hd-place-card">
            <div class="hd-place-icon">🍽️</div>
            <div class="hd-place-info">
              <div class="hd-place-name" title="{{ $placeName }}">{{ $placeName }}</div>
              @if($placeDist)<div class="hd-place-dist">{{ $placeDist }}</div>@endif
            </div>
          </div>
        @endforeach
      </div>
    </div>
    @endif

    <!-- ===== TOP ATTRACTIONS ===== -->
    @if(count($topAttractions) > 0)
    <div class="hd-section">
      <h2 class="hd-section-title">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" style="color:var(--gold);flex-shrink:0;">
          <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
        </svg>
        Top Attractions
      </h2>
      <div class="hd-places-grid">
        @foreach($topAttractions as $item)
          @php
            $parts = preg_split('/\s*[\(\-—]\s*/', $item, 2);
            $placeName = trim($parts[0]);
            $placeDist = isset($parts[1]) ? trim(rtrim($parts[1], ')')) : '';
          @endphp
          <div class="hd-place-card top">
            <div class="hd-place-icon">🏛️</div>
            <div class="hd-place-info">
              <div class="hd-place-name" title="{{ $placeName }}">{{ $placeName }}</div>
              @if($placeDist)<div class="hd-place-dist">{{ $placeDist }}</div>@endif
            </div>
          </div>
        @endforeach
      </div>
    </div>
    @endif

  </div>

  <!-- RIGHT COLUMN — Sticky Booking Card -->
  <div class="hd-right">
    <div class="hd-book-card" style="padding: 24px;">

      @php
        $cheapestLive = ($liveOptions ?? collect())->isNotEmpty()
          ? ($liveOptions ?? collect())->sortBy('pricing.totalPrice')->first()
          : null;
        $cheapestRoomName = $cheapestLive ? collect($cheapestLive['roomInfo'] ?? [])->pluck('name')->unique()->implode(' + ') : 'Standard Room';
      @endphp

      <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom: 20px;">
        <div>
          <h2 style="font-family: 'Cormorant Garamond', serif; font-size: 1.8rem; font-weight: 500; color: #fff; margin-bottom: 8px; line-height: 1.2;">{{ $cheapestRoomName ?: $hotel->title }}</h2>
          <div style="font-family: 'Jost', sans-serif; font-size: 13px; color: var(--green); display: flex; align-items: center; gap: 4px;">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 6L9 17l-5-5"/></svg>
            Best price available
          </div>
        </div>
      </div>

      @if($cheapestLive)
      <!-- Live TripJack Price -->
      <div style="margin-bottom: 24px;">
        <div style="font-family: 'Jost', sans-serif; font-size: 2.2rem; font-weight: 700; color: #fff; line-height: 1;">
          {{ $cheapestLive['pricing']['currency'] ?? 'INR' }} {{ number_format($cheapestLive['pricing']['totalPrice'] ?? 0) }}
        </div>
        <div style="font-family: 'Jost', sans-serif; font-size: 12px; color: var(--white-60); margin-top: 4px;">
          Total for {{ $roomCount }} room, {{ $adults }} adults
        </div>
      </div>
      @elseif(!empty($pricingError))
      <div style="margin-bottom: 20px; padding: 16px; background: rgba(201,168,76,0.07); border: 1px solid rgba(201,168,76,0.28); border-radius: 12px;">
        <p style="font-family:'Jost',sans-serif; font-size:13px; color:rgba(255,255,255,0.65); line-height:1.55; margin:0;">{{ $pricingError }}</p>
      </div>
      @else
      <!-- Price on Request -->
      <div style="margin-bottom: 20px; padding: 16px; background: rgba(201,168,76,0.07); border: 1px solid rgba(201,168,76,0.28); border-radius: 12px;">
        <div style="display:flex; align-items:center; gap: 10px; margin-bottom: 6px;">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#c9a84c" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 8v4m0 4h.01"/></svg>
          <span style="font-family:'Jost',sans-serif; font-size:12px; font-weight:700; letter-spacing:0.1em; text-transform:uppercase; color:#c9a84c;">Price on Request</span>
        </div>
        <p style="font-family:'Jost',sans-serif; font-size:13px; color:rgba(255,255,255,0.65); line-height:1.55; margin:0;">Send us an enquiry or WhatsApp us and we'll share the best available rates for your dates.</p>
      </div>
      @endif

      <!-- Call to Actions -->
      <div style="display: flex; flex-direction: column; gap: 12px; margin-bottom: 24px;">
        <button onclick="document.getElementById('htl-room-section')?.scrollIntoView({behavior:'smooth', block:'start'})" style="background: var(--gold); color: var(--dark); border: none; padding: 14px; border-radius: 12px; font-family: 'Jost', sans-serif; font-size: 15px; font-weight: 600; cursor: pointer; transition: all 0.3s; text-align: center; width: 100%;">
          Select Room
        </button>
        
        <a href="https://wa.me/919875073788?text={{ $waText }}" target="_blank" style="background: rgba(255,255,255,0.05); color: #fff; border: 1px solid rgba(255,255,255,0.1); padding: 14px; border-radius: 12px; font-family: 'Jost', sans-serif; font-size: 14px; font-weight: 500; cursor: pointer; transition: all 0.3s; text-align: center; text-decoration: none; display: flex; justify-content: center; align-items: center; gap: 8px;" onmouseover="this.style.borderColor='var(--gold)'; this.style.color='var(--gold)';" onmouseout="this.style.borderColor='rgba(255,255,255,0.1)'; this.style.color='#fff';">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
          Send an Enquiry
        </a>
      </div>

      <!-- Rating Section -->
      <div style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08); border-radius: 12px; padding: 16px; display: flex; justify-content: space-between; align-items: center;">
        <div>
          <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 4px;">
            <span style="background: var(--gold); color: var(--dark); font-family: 'Jost', sans-serif; font-size: 14px; font-weight: 700; padding: 2px 6px; border-radius: 4px;">{{ $ratingScore }}</span>
            <span style="font-family: 'Jost', sans-serif; font-size: 14px; font-weight: 600; color: #fff;">{{ $ratingLabel }}</span>
          </div>
          <div style="font-family: 'Jost', sans-serif; font-size: 12px; color: var(--white-60);">Based on guest reviews</div>
        </div>
        <a href="#" style="font-family: 'Jost', sans-serif; font-size: 13px; color: var(--gold); text-decoration: underline;">Read all</a>
      </div>

    </div>
  </div>

</div>

<!-- ===================================================
     ENQUIRY SECTION ANCHOR (used by scroll targets on this page)
=================================================== -->
<div id="hdEnquirySection" style="scroll-margin-top:80px;"></div>

<!-- ===================================================
     ENQUIRY MODAL
=================================================== -->
<div class="hd-modal-backdrop" id="hdEnquiryModal" role="dialog" aria-modal="true" aria-label="Enquiry form">
  <div class="hd-modal">
    <button class="hd-modal-close" id="hdModalClose" aria-label="Close">✕</button>

    <h2 class="hd-modal-title">Enquire About This Hotel</h2>
    <p class="hd-modal-sub">Fill in your details and our travel expert will respond within 2 hours.</p>

    <form class="hd-mform" id="hdEnquiryForm" novalidate>
      @csrf
      <input type="hidden" name="vertical" value="hotel" />
      <input type="hidden" name="reference_id" value="{{ $hotel->id }}" />
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

      <div class="hd-mform-group full" style="position:relative;" id="hdGuestWidget">
        <label>Persons & Rooms *</label>
        <input type="hidden" id="hdGuestData" name="guest_data" value="1 Room, 2 Adults, 0 Children" />
        <div class="hd-guest-btn" id="hdGuestBtn">1 Room, 2 Adults</div>
        
        <div class="hd-guest-popover" id="hdGuestPopover">
          <div id="hdGuestList"></div>
          <div class="hd-guest-actions">
            <button type="button" class="hd-guest-add-btn" id="hdGuestAddBtn">+ ADD ROOM</button>
            <button type="button" class="hd-guest-done-btn" id="hdGuestDoneBtn">DONE</button>
          </div>
        </div>
      </div>

      <div class="hd-mform-group">
        <label>Check In *</label>
        <div style="position:relative;">
            <input type="text" id="hdCheckin" class="hd-date-input" placeholder="Check in date" readonly required />
            <input type="text" id="hdDates" style="position:absolute; width:0; height:0; opacity:0; pointer-events:none; padding:0; border:none; top:0; left:0;" tabindex="-1" />
            <svg style="position:absolute; right:16px; top:13px; pointer-events:none; color:var(--gold);" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
        </div>
      </div>

      <div class="hd-mform-group">
        <label>Check Out *</label>
        <div style="position:relative;">
            <input type="text" id="hdCheckout" class="hd-date-input" placeholder="Check out date" readonly required />
            <svg style="position:absolute; right:16px; top:13px; pointer-events:none; color:var(--gold);" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
        </div>
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

  /* ===== SELECT ROOM: LOADING STATE ===== */
  document.querySelectorAll('.hd-select-room-form').forEach(function (form) {
    form.addEventListener('submit', function () {
      const btn = form.querySelector('.hd-room-btn');
      if (!btn) return;
      btn.classList.add('loading');
      btn.disabled = true;
    });
  });

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
  
  /* ===== ROOM DETAILS MODALS ===== */
  const roomMoreBtns = document.querySelectorAll('.hd-room-more-btn');
  const roomModalCloses = document.querySelectorAll('.hd-room-modal-close');

  roomMoreBtns.forEach(btn => {
    btn.addEventListener('click', (e) => {
      e.preventDefault();
      const targetId = btn.getAttribute('data-modal');
      const targetModal = document.getElementById(targetId);
      if(targetModal) {
        targetModal.classList.add('open');
        document.body.style.overflow = 'hidden';
      }
    });
  });

  roomModalCloses.forEach(btn => {
    btn.addEventListener('click', (e) => {
      e.preventDefault();
      const modalBox = btn.closest('.hd-modal-backdrop');
      if(modalBox) {
        modalBox.classList.remove('open');
        document.body.style.overflow = '';
      }
    });
  });
  
  document.querySelectorAll('.hd-room-details-modal').forEach(modBox => {
    modBox.addEventListener('click', (e) => {
      if (e.target === modBox) {
        modBox.classList.remove('open');
        document.body.style.overflow = '';
      }
    });
  });

  // Global escape key
  document.addEventListener('keydown', (e) => { 
    if (e.key === 'Escape') {
      closeModal(); 
      document.querySelectorAll('.hd-room-details-modal.open').forEach(m => m.classList.remove('open'));
      document.body.style.overflow = '';
    } 
  });

  /* ===== MODAL FORM SUBMIT → WHATSAPP & DB ===== */
  if (mForm) {
    mForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      const submitBtn = mForm.querySelector('button[type="submit"]');
      const originalBtnText = submitBtn.innerHTML;

      const name    = document.getElementById('hdName').value.trim();
      const phone   = document.getElementById('hdPhone').value.trim();

      if (!name || !phone) {
        showToast('Validation Error', 'Please enter your name and phone number.', 'error');
        return;
      }

      submitBtn.innerHTML = 'Sending...';
      submitBtn.disabled = true;

      const email   = document.getElementById('hdEmail').value.trim();
      const checkin = document.getElementById('hdCheckin').value || '';
      const checkout= document.getElementById('hdCheckout').value || '';
      const message = document.getElementById('hdMessage').value.trim();
      const hotel   = mForm.querySelector('[name="hotel_name"]').value;
      const dest    = mForm.querySelector('[name="hotel_destination"]').value;
      const vertical = mForm.querySelector('[name="vertical"]').value;
      const refId    = mForm.querySelector('[name="reference_id"]').value;
      const guestData = document.getElementById('hdGuestData')?.value || '';
      
      let roomsStr = window.hdRoomsData ? window.hdRoomsData.map((r, i) => {
        let str = `Room ${i+1}: ${r.adults} Adult${r.adults > 1 ? 's' : ''}`;
        if (r.children.length > 0) {
           let ages = r.children.map(a => a ? `${a} yrs` : 'Unknown').join(', ');
           str += `, ${r.children.length} Child${r.children.length > 1 ? 'ren' : ''} (${ages})`;
        }
        return str;
      }).join('\n') : (guestData || 'Not specified');

      // 1. Save to DB via AJAX
      try {
          const csrfToken = mForm.querySelector('input[name="_token"]').value;
          const formData = new FormData();
          formData.append('_token', csrfToken);
          formData.append('vertical', vertical);
          formData.append('reference_id', refId);
          formData.append('name', name);
          formData.append('phone', phone);
          formData.append('email', email);
          formData.append('checkin', checkin);
          formData.append('checkout', checkout);
          formData.append('message', message);
          if (window.hdRoomsData) formData.append('guest_data', JSON.stringify(window.hdRoomsData));
          else formData.append('guest_data', guestData);
          
          await fetch("{{ route('enquiries.store') }}", {
              method: 'POST',
              body: formData
          });
      } catch (err) {
          console.error('Failed to save enquiry to db:', err);
      }

      // 2. Open WhatsApp
      const wa = `Hi TYT Luxe! I'd like to enquire about a stay.\n\nHotel: ${hotel} (${dest})\nName: ${name}\nPhone: ${phone}${email ? '\nEmail: ' + email : ''}\nCheck-in: ${checkin || 'Flexible'}\nCheck-out: ${checkout || 'Flexible'}\n\nGuests & Rooms:\n${roomsStr}${message ? '\n\nRequirements: ' + message : ''}`;

      window.open('https://wa.me/919875073788?text=' + encodeURIComponent(wa), '_blank');

      mForm.reset();
      closeModal();
      showToast('Enquiry Sent', 'Thank you! Our travel expert will contact you within 2 hours with personalised hotel recommendations.');

      submitBtn.innerHTML = originalBtnText;
      submitBtn.disabled = false;
    });
  }

  /* ===== DYNAMIC GUEST SELECTOR ===== */
  const guestBtn = document.getElementById('hdGuestBtn');
  const guestPopover = document.getElementById('hdGuestPopover');
  const guestList = document.getElementById('hdGuestList');
  const guestAddBtn = document.getElementById('hdGuestAddBtn');
  const guestDoneBtn = document.getElementById('hdGuestDoneBtn');
  const guestDataInput = document.getElementById('hdGuestData');
  
  window.hdRoomsData = [ { adults: 2, children: [] } ];
  
  function renderGuestList() {
    if(!guestList) return;
    guestList.innerHTML = '';
    
    window.hdRoomsData.forEach((room, rIndex) => {
      const roomDiv = document.createElement('div');
      roomDiv.className = 'hd-guest-room';
      
      let childHtml = '';
      if(room.children.length > 0) {
        let selects = '';
        room.children.forEach((age, cIndex) => {
          let options = '<option value="" disabled selected>Age</option>';
          options += `<option value="<1" ${age === '<1' ? 'selected' : ''}>Under 1</option>`;
          for(let i=1; i<=12; i++) {
             options += `<option value="${i}" ${age == i ? 'selected' : ''}>${i} yrs</option>`;
          }
          selects += `<select onchange="window.updateChildAge(${rIndex}, ${cIndex}, this.value)">${options}</select>`;
        });
        childHtml = `
          <div class="hd-guest-label" style="margin-top:10px;">Age of Child</div>
          <div class="hd-guest-child-ages">${selects}</div>
        `;
      }

      roomDiv.innerHTML = `
        <div class="hd-guest-room-header">
          <span>Room ${rIndex + 1}</span>
          ${window.hdRoomsData.length > 1 ? `<button type="button" class="hd-guest-room-del" onclick="window.removeRoom(${rIndex})">✕</button>` : ''}
        </div>
        <div class="hd-guest-row">
          <div class="hd-guest-label">Adults <small>12+ Years</small></div>
          <div class="hd-guest-ctrl">
            <button type="button" onclick="window.updateAdults(${rIndex}, -1)">-</button>
            <span>${room.adults}</span>
            <button type="button" onclick="window.updateAdults(${rIndex}, 1)">+</button>
          </div>
        </div>
        <div class="hd-guest-row" style="margin-bottom:0;">
          <div class="hd-guest-label">Children <small>0 - 12 Years</small></div>
          <div class="hd-guest-ctrl">
            <button type="button" onclick="window.updateChildren(${rIndex}, -1)">-</button>
            <span>${room.children.length}</span>
            <button type="button" onclick="window.updateChildren(${rIndex}, 1)">+</button>
          </div>
        </div>
        ${childHtml}
      `;
      guestList.appendChild(roomDiv);
    });
    
    updateGuestSummary();
  }
  
  function updateGuestSummary() {
    let totalAdults = 0;
    let totalChildren = 0;
    window.hdRoomsData.forEach(r => {
      totalAdults += r.adults;
      totalChildren += r.children.length;
    });
    const txt = `${window.hdRoomsData.length} Room${window.hdRoomsData.length > 1 ? 's' : ''}, ${totalAdults} Adult${totalAdults > 1 ? 's' : ''}${totalChildren > 0 ? `, ${totalChildren} Child${totalChildren > 1 ? 'ren' : ''}` : ''}`;
    if(guestBtn) guestBtn.textContent = txt;
    if(guestDataInput) guestDataInput.value = txt;
  }
  
  window.updateAdults = function(rIndex, delta) {
    let newA = window.hdRoomsData[rIndex].adults + delta;
    if(newA >= 1 && newA <= 6) {
      window.hdRoomsData[rIndex].adults = newA;
      renderGuestList();
    }
  }
  
  window.updateChildren = function(rIndex, delta) {
    if(delta > 0 && window.hdRoomsData[rIndex].children.length < 4) {
      window.hdRoomsData[rIndex].children.push('');
      renderGuestList();
    } else if(delta < 0 && window.hdRoomsData[rIndex].children.length > 0) {
      window.hdRoomsData[rIndex].children.pop();
      renderGuestList();
    }
  }
  
  window.updateChildAge = function(rIndex, cIndex, age) {
    window.hdRoomsData[rIndex].children[cIndex] = age;
  }
  
  window.removeRoom = function(rIndex) {
    window.hdRoomsData.splice(rIndex, 1);
    renderGuestList();
  }
  
  if(guestAddBtn) {
    guestAddBtn.addEventListener('click', () => {
      if(window.hdRoomsData.length < 6) {
        window.hdRoomsData.push({ adults: 1, children: [] });
        renderGuestList();
      }
    });
  }
  
  if(guestBtn) {
    guestBtn.addEventListener('click', (e) => {
      e.stopPropagation();
      guestPopover.classList.toggle('open');
    });
  }
  
  if(guestDoneBtn) {
    guestDoneBtn.addEventListener('click', () => {
      guestPopover.classList.remove('open');
    });
  }
  
  if(guestPopover) {
    guestPopover.addEventListener('click', (e) => { e.stopPropagation(); });
    document.addEventListener('click', () => { guestPopover.classList.remove('open'); });
  }
  
  renderGuestList();

})();
</script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    const dateInput = document.getElementById('hdDates');
    const checkinInput = document.getElementById('hdCheckin');
    const checkoutInput = document.getElementById('hdCheckout');
    
    if(dateInput && checkinInput && checkoutInput) {
      const fp = flatpickr(dateInput, {
        mode: "range",
        minDate: "today",
        showMonths: window.innerWidth > 768 ? 2 : 1,
        positionElement: checkinInput,
        onChange: function(selectedDates, dateStr, instance) {
          if(selectedDates.length > 0) {
            checkinInput.value = instance.formatDate(selectedDates[0], "d M Y");
          } else {
            checkinInput.value = "";
          }
          if(selectedDates.length === 2) {
            checkoutInput.value = instance.formatDate(selectedDates[1], "d M Y");
          } else {
            checkoutInput.value = "";
          }
        }
      });

      checkinInput.addEventListener('click', () => fp.open());
      checkoutInput.addEventListener('click', () => fp.open());
    }
  });
</script>
@endpush
