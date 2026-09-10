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
  position: relative; width: 100%; height: 78vh; min-height: 540px; overflow: hidden;
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


/* ===== SECTION NAV (sticky quick-jump bar) ===== */
html { scroll-behavior: smooth; }
.hd-section-nav {
  position: sticky; top: 0; z-index: 50;
  background: rgba(13,13,13,0.94); backdrop-filter: blur(10px);
  border-bottom: 1px solid rgba(255,255,255,0.08);
}
.hd-section-nav-inner {
  max-width: 1280px; margin: 0 auto; padding: 0 40px;
  display: flex; justify-content: center; gap: 6px; overflow-x: auto;
  -ms-overflow-style: none; scrollbar-width: none;
}
.hd-section-nav-inner::-webkit-scrollbar { display: none; }
.hd-section-nav a {
  flex-shrink: 0; padding: 16px 18px; font-family: 'Jost', sans-serif;
  font-size: 12.5px; font-weight: 600; letter-spacing: 0.06em; text-transform: uppercase;
  color: var(--white-60); text-decoration: none; white-space: nowrap;
  border-bottom: 2px solid transparent; transition: color var(--tr), border-color var(--tr);
}
.hd-section-nav a:hover, .hd-section-nav a.active { color: var(--gold); border-bottom-color: var(--gold); }
@media (max-width: 900px) { .hd-section-nav-inner { padding: 0 20px; } .hd-section-nav a { padding: 13px 14px; font-size: 12px; } }
[id^="hd-anchor-"] { scroll-margin-top: 76px; }

/* ===== LAYOUT ===== */
.hd-layout {
  max-width: 1280px; margin: 0 auto;
  padding: 56px 40px 80px;
  display: grid; grid-template-columns: minmax(0, 1fr) 380px; gap: 56px; align-items: start;
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
  line-height: 1.85; color: var(--white-80); overflow-wrap: break-word; word-wrap: break-word;
}
.hd-desc img, .hd-desc iframe, .hd-desc table { max-width: 100%; height: auto; display: block; 
}

/* ===== AMENITIES ===== */
.hd-amenity-groups { display: flex; flex-direction: column; gap: 26px; }
.hd-amenity-group-head {
  display: flex; align-items: center; gap: 12px; margin-bottom: 14px;
}
.hd-amenity-group-head .hd-info-icon {
  display: flex; align-items: center; justify-content: center; flex-shrink: 0;
  width: 32px; height: 32px; border-radius: 9px;
  background: linear-gradient(160deg, rgba(201,168,76,0.18), rgba(201,168,76,0.05));
  border: 1px solid rgba(201,168,76,0.25);
}
.hd-amenity-group-head svg { color: var(--gold); flex-shrink: 0; width: 15px; height: 15px; }
/* Collapsible group content — truncated to first 6 items until "Read more" */
.hd-collapsible.collapsed > *:nth-child(n+7) { display: none; }
.hd-collapsible.collapsed .hd-fee-list li:nth-child(n+7) { display: none; }
.hd-point-list.hd-collapsible.collapsed > *:nth-child(n+5) { display: none !important; }
.hd-readmore-btn {
  display: inline-block; margin-top: 10px; background: none; border: none; padding: 0;
  font-family: 'Jost', sans-serif; font-size: 12px; font-weight: 600;
  color: var(--gold); cursor: pointer; border-bottom: 1px dashed var(--gold);
  transition: color var(--tr), border-color var(--tr);
}
.hd-readmore-btn:hover { color: var(--gold-light); border-bottom-color: var(--gold-light); }

.hd-amenity-group-head h3 {
  font-family: 'Jost', sans-serif; font-size: 12.5px; font-weight: 700; letter-spacing: 0.1em;
  text-transform: uppercase; color: var(--gold-light); margin: 0;
}
.hd-amenities { display: flex; flex-wrap: wrap; gap: 8px; }
.hd-amenity {
  display: inline-flex; align-items: center; gap: 7px;
  border: 1px solid rgba(255,255,255,0.12); padding: 7px 14px; border-radius: 100px;
  font-family: 'Jost', sans-serif; font-size: 12.5px; font-weight: 400; color: var(--white-80); overflow-wrap: break-word; word-wrap: break-word;
  transition: border-color var(--tr), background var(--tr);
}
.hd-amenity:hover { border-color: var(--gold); background: var(--gold-dim); }
.hd-amenity-dot { width: 5px; height: 5px; border-radius: 50%; background: var(--gold); flex-shrink: 0; }

/* ===== ABOUT — INFO CARD GRID ===== */
.hd-info-grid {
  display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px;
}
@media (max-width: 640px) { .hd-info-grid { grid-template-columns: 1fr; } }
.hd-info-card {
  position: relative; overflow: hidden;
  background: linear-gradient(160deg, rgba(255,255,255,0.035), rgba(255,255,255,0.01));
  border: 1px solid rgba(255,255,255,0.08);
  border-radius: 14px; padding: 16px 20px;
  transition: border-color var(--tr), background var(--tr), transform var(--tr), box-shadow var(--tr);
}
.hd-info-card::before {
  content: ''; position: absolute; inset: 0 0 auto 0; height: 2px;
  background: linear-gradient(90deg, transparent, var(--gold) 50%, transparent);
  opacity: 0; transition: opacity var(--tr);
}
.hd-info-card:hover {
  border-color: rgba(201,168,76,0.35); background: rgba(255,255,255,0.045);
  transform: translateY(-3px); box-shadow: 0 16px 32px rgba(0,0,0,0.35), 0 0 0 1px rgba(201,168,76,0.06);
}
.hd-info-card:hover::before { opacity: 1; }
.hd-info-card.hd-info-card--wide { grid-column: 1 / -1; }
.hd-info-card-head {
  display: flex; align-items: center; gap: 10px; margin-bottom: 10px;
}
.hd-info-card-head .hd-info-icon {
  display: flex; align-items: center; justify-content: center; flex-shrink: 0;
  width: 32px; height: 32px; border-radius: 8px;
  background: linear-gradient(160deg, rgba(201,168,76,0.18), rgba(201,168,76,0.05));
  border: 1px solid rgba(201,168,76,0.25);
}
.hd-info-card-head svg { color: var(--gold); flex-shrink: 0; width: 15px; height: 15px; }
.hd-info-card-head h3 {
  font-family: 'Jost', sans-serif; font-size: 12px; font-weight: 700; letter-spacing: 0.1em;
  text-transform: uppercase; color: var(--gold-light); margin: 0;
}
.hd-info-card .hd-desc { font-size: 13.5px; }
.hd-info-chip-list { display: flex; flex-wrap: wrap; gap: 8px; }
.hd-info-chip {
  display: inline-flex; align-items: center; gap: 6px; border: 1px solid rgba(255,255,255,0.12);
  padding: 6px 14px; border-radius: 100px; font-family: 'Jost', sans-serif;
  font-size: 12px; color: var(--white-80); background: rgba(255,255,255,0.03);
  transition: all var(--tr);
}
.hd-info-chip:hover { border-color: rgba(201,168,76,0.4); background: var(--gold-dim); color: #fff; }
.hd-info-chip::before {
  content: ''; width: 4px; height: 4px; border-radius: 50%; background: var(--gold); flex-shrink: 0;
}

/* Keyword point-list — sentence paragraphs distilled into scannable features */
.hd-point-list {
  display: grid; grid-template-columns: repeat(2, 1fr); gap: 2px 16px;
}
@media (max-width: 640px) { .hd-point-list { grid-template-columns: 1fr; } }
.hd-point {
  display: flex; align-items: flex-start; gap: 8px;
  padding: 6px 0; font-family: 'Jost', sans-serif; font-size: 13px;
  color: var(--white-80); line-height: 1.35; border-bottom: 1px solid rgba(255,255,255,0.06);
  overflow-wrap: break-word; word-wrap: break-word;
}
.hd-point:nth-last-child(-n+2) { border-bottom: none; }
.hd-point-icon {
  flex-shrink: 0; width: 18px; height: 18px; border-radius: 50%; margin-top: 1px;
  background: var(--gold-dim); color: var(--gold);
  display: flex; align-items: center; justify-content: center;
}

/* ===== PLACE LIST (Attractions/Renovations bottom sections) — ultra-compact pin cards ===== */
.hd-place-list-wrap {
  position: relative;
}
.hd-place-list {
  list-style: none; margin: 0; padding: 0;
  display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px;
}
@media (min-width: 992px) {
  .hd-place-list { grid-template-columns: repeat(3, 1fr); gap: 10px; }
}
@media (max-width: 576px) {
  .hd-place-list { grid-template-columns: 1fr; gap: 8px; }
}
.hd-place-list-wrap.collapsed .hd-place-list li:nth-child(n+7) {
  display: none !important;
}
.hd-place-list li {
  display: flex; align-items: center; justify-content: space-between; gap: 8px;
  background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08);
  border-radius: 10px; padding: 9px 12px;
  font-family: 'Jost', sans-serif; font-size: 13px; color: var(--white-80);
  transition: border-color var(--tr), transform var(--tr), background var(--tr);
}
.hd-place-list li:hover {
  background: rgba(255,255,255,0.06);
  border-color: rgba(201,168,76,0.35); transform: translateY(-2px);
}
.hd-place-list li::before {
  content: '📍';
  font-size: 13px; flex-shrink: 0; margin-right: 2px;
}
.hd-place-list li span:first-child {
  font-weight: 600; color: #fff; font-size: 13px; line-height: 1.3;
  overflow: hidden; text-overflow: ellipsis; white-space: nowrap; flex: 1; min-width: 0;
}
.hd-place-list-dist {
  color: var(--gold); white-space: nowrap; font-weight: 500; font-size: 11.5px;
  background: rgba(201,168,76,0.12); border: 1px solid rgba(201,168,76,0.22);
  padding: 2px 8px; border-radius: 6px; flex-shrink: 0;
}
.hd-attr-toggle-btn {
  display: inline-flex; align-items: center; gap: 6px;
  margin-top: 14px; padding: 8px 18px;
  background: rgba(201,168,76,0.1); border: 1px solid rgba(201,168,76,0.3);
  border-radius: 20px; color: var(--gold); font-family: 'Jost', sans-serif;
  font-size: 13px; font-weight: 500; cursor: pointer; transition: all 0.2s ease;
}
.hd-attr-toggle-btn:hover {
  background: rgba(201,168,76,0.2); border-color: var(--gold); transform: translateY(-1px);
}

/* ===== FEE / POLICY BULLET LIST ===== */
.hd-fee-list {
  list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; gap: 4px;
}
.hd-fee-list li {
  position: relative; padding: 10px 0 10px 24px; font-family: 'Jost', sans-serif;
  font-size: 13.5px; line-height: 1.6; color: var(--white-80);
  border-bottom: 1px dashed rgba(255,255,255,0.07);
}
.hd-fee-list li:last-child { border-bottom: none; }
.hd-fee-list li::before {
  content: '✓'; position: absolute; left: 0; top: 9px;
  width: 16px; height: 16px; border-radius: 50%; background: var(--gold-dim);
  color: var(--gold); font-size: 10px; font-weight: 700; line-height: 16px; text-align: center;
}

/* ===== ROOM CATEGORIES ===== */
.hd-room-cats { display: flex; flex-wrap: wrap; gap: 10px; }
/* ===== ROOM CARDS ===== */
.hd-room-list { display: flex; flex-direction: column; gap: 20px; }
.hd-room-card {
  background: var(--dark-2); border: 1px solid rgba(255,255,255,0.08);
  border-radius: 14px; overflow: hidden; display: flex; flex-direction: column;
}
@media (min-width: 768px) {
  .hd-room-card {
    flex-direction: row;
    align-items: stretch;
  }
}
.hd-room-group-row { display: flex; flex-direction: column; }
@media (min-width: 992px) { .hd-room-group-row { flex-direction: row; align-items: flex-start; } }
.hd-room-img { 
  width: 100%; 
  height: 200px; 
  position: relative; 
  overflow: hidden; 
  flex-shrink: 0;
  background: var(--dark-3);
}
@media (min-width: 768px) { 
  .hd-room-img { 
    width: 260px; 
    min-width: 260px;
    max-width: 260px;
    height: auto; 
    min-height: 100%;
  } 
}
.hd-room-img img { 
  position: absolute; 
  inset: 0; 
  width: 100%; 
  height: 100%; 
  object-fit: cover; 
  display: block;
}
.hd-room-content {
  padding: 16px; display: flex; flex-direction: column; gap: 16px; flex: 1;
}
@media (min-width: 992px) {
  .hd-room-content { flex-direction: row; justify-content: space-between; align-items: center; }
}
.hd-room-info { flex: 1; border-bottom: 1px dashed rgba(255,255,255,0.1); padding-bottom: 16px; }
@media (min-width: 992px) {
  .hd-room-info { border-bottom: none; border-right: 1px dashed rgba(255,255,255,0.1); padding-bottom: 0; padding-right: 20px; margin-right: 20px; }
}
.hd-room-title {
  font-family: 'Cormorant Garamond', serif; font-size: 1.6rem; color: #fff; margin-bottom: 8px; line-height: 1.2;
}
.hd-room-specs {
  display: flex; flex-wrap: wrap; gap: 12px; margin-bottom: 10px;
}
.hd-room-spec {
  display: flex; align-items: center; gap: 6px;
  font-family: 'Jost', sans-serif; font-size: 13px; color: var(--white-80); overflow-wrap: break-word; word-wrap: break-word;
}
.hd-room-spec svg { width: 14px; height: 14px; color: var(--gold); }
.hd-room-badge {
  display: inline-flex; align-items: center; gap: 7px;
  border: 1px solid rgba(255,255,255,0.14); border-radius: 100px;
  padding: 7px 14px; font-family: 'Jost', sans-serif; font-size: 12.5px;
  color: var(--white-80); background: rgba(255,255,255,0.03); white-space: nowrap;
}
.hd-room-badge svg { width: 14px; height: 14px; color: var(--gold); flex-shrink: 0; }

/* ===== RATE PLAN ROW (per meal-basis / cancellation option) ===== */
.hd-rate-row {
  display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 16px;
  padding: 18px 20px; background: rgba(255,255,255,0.025);
  border: 1px solid rgba(255,255,255,0.08); border-radius: 14px;
  transition: border-color var(--tr), background var(--tr);
}
.hd-rate-row:hover { border-color: rgba(201,168,76,0.3); background: rgba(255,255,255,0.045); }
.hd-rate-room-name {
  font-family: 'Cormorant Garamond', serif; font-size: 1.3rem; font-weight: 600;
  color: #fff; margin-bottom: 6px; line-height: 1.2;
}
.hd-rate-title {
  font-family: 'Jost', sans-serif; font-size: 14.5px; font-weight: 600; color: #fff;
}
.hd-rate-title .refundable { color: var(--green); }
.hd-rate-title .non-refundable { color: #f87171; }
.hd-rate-title .sep { color: rgba(255,255,255,0.25); margin: 0 6px; font-weight: 400; }
.hd-rate-cancel {
  display: flex; align-items: center; gap: 6px;
  font-family: 'Jost', sans-serif; font-size: 12.5px; color: var(--green); margin-top: 8px;
}
.hd-rate-cancel svg { width: 14px; height: 14px; flex-shrink: 0; }
.hd-rate-more {
  display: inline-block; margin-top: 8px; font-family: 'Jost', sans-serif; font-size: 12.5px;
  color: var(--gold); text-decoration: none; border-bottom: 1px dashed var(--gold); cursor: pointer;
}
.hd-rate-more:hover { color: var(--gold-light); border-bottom-color: var(--gold-light); }
.hd-rate-price {
  text-align: right; min-width: 170px;
}
.hd-rate-price-per-night {
  font-family: 'Jost', sans-serif; font-size: 12px; color: var(--white-60); margin-bottom: 4px;
}
.hd-rate-price-total {
  font-family: 'Jost', sans-serif; font-size: 1.5rem; font-weight: 700; color: #fff; line-height: 1;
}
.hd-rate-price-caption {
  font-family: 'Jost', sans-serif; font-size: 11.5px; color: var(--white-60); margin: 4px 0 12px;
}
.hd-room-desc-text {
  font-size: 13px; margin-bottom: 10px; color: var(--white-60); line-height: 1.5;
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
/* Inclusions beyond the per-card limit stay hidden inline; "Read more" opens the full list in a popup */
.hd-inc-hidden { display: none !important; }
.hd-inc-readmore-btn {
  display: inline-block; margin-top: 8px; background: none; border: none; padding: 0;
  font-family: 'Jost', sans-serif; font-size: 12px; font-weight: 600;
  color: var(--gold); cursor: pointer; border-bottom: 1px dashed var(--gold);
  transition: color var(--tr), border-color var(--tr);
}
.hd-inc-readmore-btn:hover { color: var(--gold-light); border-bottom-color: var(--gold-light); }
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
  margin-top: -4px; margin-bottom: 10px; cursor: pointer; transition: all var(--tr);
}
.hd-room-more-btn:hover { color: #fff; border-bottom-color: #fff; }

.hd-room-gallery {
  display: flex; overflow-x: auto; scroll-snap-type: x mandatory; gap: 12px;
  margin-bottom: 20px; padding-bottom: 8px;
  -ms-overflow-style: none; scrollbar-width: none;
  cursor: grab;
}
.hd-room-gallery:active {
  cursor: grabbing;
}
.hd-room-gallery::-webkit-scrollbar { display: none; }
.hd-room-gallery img {
  width: 100%; flex-shrink: 0; scroll-snap-align: start;
  border-radius: 12px; max-height: 280px; object-fit: cover;
  user-select: none; -webkit-user-drag: none;
}
.hd-rg-btn {
  position: absolute; top: 50%; transform: translateY(-50%);
  background: rgba(0,0,0,0.6); backdrop-filter: blur(4px);
  color: #fff; border: 1px solid rgba(255,255,255,0.2);
  width: 36px; height: 36px; border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  cursor: pointer; z-index: 10; transition: all 0.2s;
  font-size: 14px;
}
.hd-rg-btn:hover { background: var(--gold); border-color: var(--gold); color: var(--dark); }
.hd-rg-prev { left: 10px; }
.hd-rg-next { right: 10px; }

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
  color: var(--white-80); overflow-wrap: break-word; word-wrap: break-word;
  line-height: 1.4;
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

/* ===== ROOM AMENITIES POPUP ===== */
.hd-amenities-modal { max-width: 640px; }
.hd-amenities-modal-img {
  border-radius: 14px; overflow: hidden; margin: 18px 0 20px;
  height: 260px; background: var(--dark-3);
}
.hd-amenities-modal-img img { width: 100%; height: 100%; object-fit: cover; display: block; }
.hd-amenities-modal-meta {
  display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 22px;
}
.hd-amenities-modal-meta span {
  display: inline-flex; align-items: center; gap: 7px;
  border: 1px solid rgba(255,255,255,0.14); border-radius: 100px;
  padding: 7px 14px; font-family: 'Jost', sans-serif; font-size: 12.5px;
  color: var(--white-80); background: rgba(255,255,255,0.03);
}
.hd-amenities-modal-meta span svg { width: 14px; height: 14px; color: var(--gold); flex-shrink: 0; }
.hd-amenities-modal-subhead {
  font-family: 'Jost', sans-serif; font-size: 12.5px; font-weight: 700; letter-spacing: 0.1em;
  text-transform: uppercase; color: var(--gold-light); margin-bottom: 16px;
  border-top: 1px dashed rgba(255,255,255,0.1); padding-top: 18px;
}
.hd-amenities-modal-grid {
  display: grid; grid-template-columns: repeat(2, 1fr); gap: 4px 20px;
  padding-bottom: 4px;
}
@media (min-width: 560px) { .hd-amenities-modal-grid { grid-template-columns: repeat(3, 1fr); } }
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
@media (max-width: 1150px) {
  .hd-layout { grid-template-columns: minmax(0, 1fr) 330px; gap: 30px; }
}
@media (max-width: 900px) {
  .hd-layout { grid-template-columns: 1fr; padding: 40px 24px 100px; gap: 40px; }
  .hd-book-card { position: static; }
  .hd-gallery-hero-info { padding: 32px 24px 28px; }
  /* Hide right column booking card on small screens */
  .hd-right { display: none; }
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

/* ===== PHOTO LIGHTBOX ===== */
.hd-view-all-btn {
  margin-top: 16px; z-index: 3;
  background: rgba(255,255,255,0.06); backdrop-filter: blur(8px);
  border: 1px solid rgba(255,255,255,0.25); color: #fff;
  font-family: 'Jost', sans-serif; font-size: 12.5px; font-weight: 600;
  padding: 10px 18px; border-radius: 100px; cursor: pointer;
  display: inline-flex; align-items: center; gap: 8px; transition: all var(--tr);
}
.hd-view-all-btn:hover { border-color: var(--gold); color: var(--gold); background: rgba(201,168,76,0.1); }
.hd-view-all-btn svg { flex-shrink: 0; }
@media (max-width: 768px) {
  .hd-view-all-btn { margin-top: 12px; font-size: 12px; padding: 8px 14px; }
}

.hd-lightbox {
  position: fixed; inset: 0; z-index: 9998; background: rgba(0,0,0,0.96);
  display: flex; align-items: center; justify-content: center;
  opacity: 0; pointer-events: none; transition: opacity 0.3s ease;
}
.hd-lightbox.open { opacity: 1; pointer-events: all; }
.hd-lightbox img { max-width: 90vw; max-height: 82vh; object-fit: contain; border-radius: 8px; }
.hd-lightbox-close {
  position: absolute; top: 20px; right: 24px; width: 42px; height: 42px; border-radius: 50%;
  background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.2); color: #fff;
  font-size: 20px; display: flex; align-items: center; justify-content: center; cursor: pointer;
}
.hd-lightbox-close:hover { border-color: var(--gold); color: var(--gold); }
.hd-lightbox-counter {
  position: absolute; bottom: 24px; left: 50%; transform: translateX(-50%);
  color: var(--white-60); font-family: 'Jost', sans-serif; font-size: 13px;
}
.hd-lightbox-prev, .hd-lightbox-next {
  position: absolute; top: 50%; transform: translateY(-50%);
  background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.2); color: #fff;
  width: 46px; height: 46px; border-radius: 50%; display: flex; align-items: center; justify-content: center;
  cursor: pointer; font-size: 18px;
}
.hd-lightbox-prev:hover, .hd-lightbox-next:hover { background: var(--gold); border-color: var(--gold); color: var(--dark); }
.hd-lightbox-prev { left: 24px; } .hd-lightbox-next { right: 24px; }
@media (max-width: 640px) { .hd-lightbox-prev { left: 8px; } .hd-lightbox-next { right: 8px; } }

/* ===== EMBEDDED MAP ===== */
/* ===== STICKY MOBILE CTA BAR ===== */
.hd-sticky-cta {
  display: none; /* Hidden on desktop */
  position: fixed; bottom: 0; left: 0; right: 0; z-index: 500;
  background: rgba(13,13,13,0.96); backdrop-filter: blur(12px);
  border-top: 1px solid rgba(201,168,76,0.25);
  padding: 12px 20px 14px;
}
@media (max-width: 900px) {
  .hd-sticky-cta { display: flex; align-items: center; gap: 10px; }
}
.hd-sticky-cta-info {
  flex: 1; min-width: 0;
  font-family: 'Jost', sans-serif;
}
.hd-sticky-cta-name {
  font-size: 13px; font-weight: 600; color: #fff;
  white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.hd-sticky-cta-price {
  font-size: 11px; color: var(--gold); font-weight: 400; margin-top: 1px;
}
.hd-sticky-cta-btn {
  display: inline-flex; align-items: center; gap: 7px; flex-shrink: 0;
  padding: 12px 22px; border-radius: 100px;
  background: var(--gold); color: var(--dark);
  font-family: 'Jost', sans-serif; font-size: 12px; font-weight: 700;
  letter-spacing: 0.1em; text-transform: uppercase;
  border: none; cursor: pointer; transition: all var(--tr);
}
.hd-sticky-cta-btn:hover { background: var(--gold-light); }
.hd-sticky-cta-wa {
  display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0;
  width: 44px; height: 44px; border-radius: 50%;
  background: #25D366; color: #fff;
  font-size: 18px; text-decoration: none;
  transition: background var(--tr);
}
.hd-sticky-cta-wa:hover { background: #20c45b; }

/* ===== HOTEL INFO TABS PANEL ===== */
.hd-info-tabs-section {
  max-width: 1280px; margin: 0 auto; padding: 0 40px 56px;
}
@media (max-width: 900px) { .hd-info-tabs-section { padding: 0 24px 56px; } }

.hd-tabs-strip {
  display: flex; gap: 0; overflow-x: auto; -ms-overflow-style: none; scrollbar-width: none;
  border-bottom: 1px solid rgba(255,255,255,0.08);
  margin-bottom: 32px;
}
.hd-tabs-strip::-webkit-scrollbar { display: none; }
.hd-tab-btn {
  flex-shrink: 0; padding: 14px 20px;
  font-family: 'Jost', sans-serif; font-size: 12.5px; font-weight: 600;
  letter-spacing: 0.07em; text-transform: uppercase; white-space: nowrap;
  color: var(--white-60); background: none; border: none; border-bottom: 2px solid transparent;
  cursor: pointer; transition: color var(--tr), border-color var(--tr);
  margin-bottom: -1px;
}
.hd-tab-btn:hover { color: var(--white-80); }
.hd-tab-btn.active { color: var(--gold); border-bottom-color: var(--gold); }

.hd-tab-panel { display: none; animation: hdTabFadeIn 0.28s ease; }
.hd-tab-panel.active { display: block; }
@keyframes hdTabFadeIn { from { opacity: 0; transform: translateY(6px); } to { opacity: 1; transform: translateY(0); } }

/* Compact two-col grid for info cards inside tabs */
.hd-tab-info-grid {
  display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px;
}
@media (max-width: 720px) { .hd-tab-info-grid { grid-template-columns: 1fr; } }

/* Policy items: 2-column grid container */
.hd-tab-policy-grid {
  display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px;
}
@media (max-width: 720px) { .hd-tab-policy-grid { grid-template-columns: 1fr; } }

.hd-policy-block {
  background: linear-gradient(160deg, rgba(255,255,255,0.035), rgba(255,255,255,0.01));
  border: 1px solid rgba(255,255,255,0.08); border-radius: 14px; padding: 16px 20px;
  margin-bottom: 0;
  transition: border-color var(--tr), background var(--tr), transform var(--tr);
}
.hd-policy-block:hover {
  border-color: rgba(201,168,76,0.35); background: rgba(255,255,255,0.045);
}
.hd-collapsible.collapsed .hd-fee-list li:nth-child(n+5) { display: none !important; }
.hd-collapsible.collapsed > ul > li:nth-child(n+5) { display: none !important; }
.hd-collapsible.collapsed > p:nth-of-type(n+3) { display: none !important; }

.hd-policy-head {
  display: flex; align-items: center; gap: 10px; margin-bottom: 10px;
}
.hd-policy-head .hd-info-icon { width: 32px; height: 32px; border-radius: 8px; }
.hd-policy-head h3 {
  font-family: 'Jost', sans-serif; font-size: 12px; font-weight: 700;
  letter-spacing: 0.1em; text-transform: uppercase; color: var(--gold-light); margin: 0;
}

/* Compact amenity cards inside tabs: 2-column grid */
.hd-tab-amenities-wrap {
  display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px;
}
@media (max-width: 720px) { .hd-tab-amenities-wrap { grid-template-columns: 1fr; } }

.hd-tab-amenity-cat {
  background: linear-gradient(160deg, rgba(255,255,255,0.035), rgba(255,255,255,0.01));
  border: 1px solid rgba(255,255,255,0.08); border-radius: 14px; padding: 16px 20px;
  transition: border-color var(--tr), background var(--tr), transform var(--tr);
}
.hd-tab-amenity-cat:hover {
  border-color: rgba(201,168,76,0.35); background: rgba(255,255,255,0.045);
}

.hd-tab-amenity-cat-head {
  display: flex; align-items: center; gap: 10px; margin-bottom: 10px;
}
.hd-tab-amenity-cat-head .hd-info-icon { width: 30px; height: 30px; border-radius: 8px; }
.hd-tab-amenity-cat-head h3 {
  font-family: 'Jost', sans-serif; font-size: 11.5px; font-weight: 700;
  letter-spacing: 0.1em; text-transform: uppercase; color: var(--gold-light); margin: 0;
}
</style>
@endpush

@php
  $destination  = $hotel->destination?->name ?? 'Unknown Location';
  $images       = $hotel->images ?? collect();
  $imageCount   = $images->count();
  $stars        = min((int) ($hotel->star_rating ?? 3), 5);

  $amenities    = $hotel->amenities ?? collect();
  // "Rooms" and "Business Amenities" description sections read as facility
  // listings, so they're folded into Amenities & Facilities instead of About.
  $extraAmenitySections = collect($hotel->description_sections ?? [])->only(['Rooms', 'Business Amenities']);
  $aboutSectionsToShow = collect($hotel->description_sections ?? [])->except(['Rooms', 'Business Amenities']);

  // Group amenities into display categories (Property Facilities, Food & Beverage,
  // etc.) by matching keywords against each amenity's name — the amenities table
  // has no category column, so this buckets existing data without a migration.
  $amenityCategoryMap = [
    'Food & Beverage' => ['restaurant', 'breakfast', 'buffet', 'bar', 'lounge', 'snack', 'bakery', 'room service', 'dining', 'cuisine', 'kitchen', 'minibar', 'mini bar', 'coffee maker', 'tea/coffee', 'wine', 'bbq', 'barbecue'],
    'Activities & Recreation' => ['pool', 'swimming', 'spa', 'sauna', 'steam room', 'gym', 'fitness', 'yoga', 'water sport', 'kids club', 'playground', 'game room', 'billiard', 'tennis', 'golf', 'hiking', 'cycling', 'bicycle', 'beach', 'nightclub', 'night club', 'casino', 'cinema', 'movie', 'entertainment', 'massage', 'salon'],
    'Technology & Connectivity' => ['wifi', 'wi-fi', 'internet', 'wireless', 'television', 'tv', 'telephone', 'charging', 'air condition', 'a/c', 'computer'],
    'Parking & Transport' => ['parking', 'valet', 'shuttle', 'airport transfer', 'transport', 'car rental', 'bicycle rental', 'taxi'],
    'Family & Accessibility' => ['wheelchair', 'accessible', 'family room', 'crib', 'babysitting', 'child'],
    'Property Facilities' => ['front desk', 'reception', 'elevator', 'lift', 'concierge', 'luggage', 'storage', 'atm', 'currency exchange', 'coffee shop', 'cafe', 'laundry', 'dry clean', 'smoking area', 'non-smoking', 'garden', 'terrace', 'business', 'meeting room', 'conference', 'banquet', 'safe deposit', 'locker', 'housekeeping', 'doctor', 'first aid', 'security', '24-hour', '24 hour', 'check-in', 'newspaper'],
  ];
  $amenityCategoryOrder = ['Property Facilities', 'Food & Beverage', 'Activities & Recreation', 'Technology & Connectivity', 'Parking & Transport', 'Family & Accessibility', 'Other Amenities'];
  $groupedAmenities = $amenities->groupBy(function ($amenity) use ($amenityCategoryMap) {
    $name = strtolower($amenity->name);
    foreach ($amenityCategoryMap as $category => $keywords) {
      foreach ($keywords as $keyword) {
        // Leading word-boundary only (not trailing) so short keywords like "spa" or
        // "tv" don't false-positive mid-word (e.g. inside "newspaper"), while still
        // matching plurals/suffixes ("water sport" -> "water sports", "air condition" -> "air conditioning")
        if (preg_match('/\b' . preg_quote($keyword, '/') . '/i', $name)) return $category;
      }
    }
    return 'Other Amenities';
  })->sortBy(function ($group, $category) use ($amenityCategoryOrder) {
    $pos = array_search($category, $amenityCategoryOrder);
    return $pos === false ? count($amenityCategoryOrder) : $pos;
  }, SORT_REGULAR, false);
  $amenityCategoryIcons = [
    'Property Facilities' => '<rect x="3" y="10" width="18" height="10" rx="1"/><path d="M7 10V6a5 5 0 0110 0v4"/>',
    'Food & Beverage' => '<path d="M6 2v7a2 2 0 002 2h0a2 2 0 002-2V2M8 11v11M18 2c-1.5 1-2 3-2 5v3a2 2 0 002 2v9"/>',
    'Activities & Recreation' => '<path d="M12 2l2.4 7.2H22l-6 4.4 2.3 7.2L12 16.4 5.7 20.8 8 13.6l-6-4.4h7.6z"/>',
    'Technology & Connectivity' => '<path d="M5 12.55a11 11 0 0114.08 0M1.42 9a16 16 0 0121.16 0M8.53 16.11a6 6 0 016.95 0M12 20h.01"/>',
    'Parking & Transport' => '<rect x="1" y="6" width="15" height="12" rx="2"/><circle cx="6" cy="18" r="2"/><circle cx="16" cy="18" r="2"/><path d="M16 8h3l3 4v4h-2"/>',
    'Family & Accessibility' => '<circle cx="9" cy="7" r="4"/><path d="M2 21v-2a4 4 0 014-4h6a4 4 0 014 4v2"/><circle cx="19" cy="8" r="2"/>',
    'Other Amenities' => '<circle cx="12" cy="12" r="9"/><path d="M12 8v4l3 2"/>',
  ];
  $cancelDate   = now()->addDays(14)->format('d M Y');
  $ratingScore  = number_format(min(5, max(1, ($stars * 0.92))), 1);
  $ratingLabel  = $stars >= 5 ? 'Exceptional' : ($stars >= 4 ? 'Excellent' : ($stars >= 3 ? 'Very Good' : 'Good'));
  // Room categories — stored as newline-separated text in DB
  $roomCats     = array_filter(array_map('trim', explode("\n", $hotel->room_categories ?? '')));
  // Nearby attractions, restaurants & top attractions — stored as newline-separated text in DB
  $nearbyAttr       = array_filter(array_map('trim', explode("\n", $hotel->nearby_attractions   ?? '')));
  $restaurantsCafes = array_filter(array_map('trim', explode("\n", $hotel->restaurants_cafes   ?? '')));
  $topAttractions   = array_filter(array_map('trim', explode("\n", $hotel->top_attractions     ?? '')));
  $hasLocation      = count($nearbyAttr) > 0 || count($restaurantsCafes) > 0 || count($topAttractions) > 0 || !empty($hotel->bottom_sections['Attractions']);
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
     HERO GALLERY & HEADER
=================================================== -->
<div class="hd-gallery">
  <div class="hd-gallery-main" id="hdGalleryMain">
    @forelse($images as $i => $img)
      @php
        $slideUrl = Str::startsWith($img->path, ['http://', 'https://']) ? $img->path : Storage::disk('public')->url($img->path);
      @endphp
      <div class="hd-gallery-slide {{ $i === 0 ? 'active' : '' }}" data-index="{{ $i }}">
        <img src="{{ $slideUrl }}" alt="{{ $hotel->title }}" loading="{{ $i === 0 ? 'eager' : 'lazy' }}">
      </div>
    @empty
      <div class="hd-gallery-slide active" data-index="0">
        <img src="https://images.unsplash.com/photo-1542314831-068cd1dbfeeb?w=1600&q=85" alt="{{ $hotel->title }}">
      </div>
    @endforelse

    <div class="hd-gallery-overlay"></div>

    @if($imageCount > 1)
      <button type="button" class="hd-gallery-prev" id="hdGalleryPrev" aria-label="Previous photo">&#10094;</button>
      <button type="button" class="hd-gallery-next" id="hdGalleryNext" aria-label="Next photo">&#10095;</button>
      <div class="hd-gallery-counter" id="hdGalleryCounter">1 / {{ $imageCount }}</div>
    @endif

    <div class="hd-gallery-hero-info">
      <div>
        <div class="hd-badge-row">
          <span class="hd-badge">{{ $catLabel }}</span>
          @if($hotel->is_featured)
            <span class="hd-badge-outline">Featured</span>
          @endif
          @if(!empty($hotel->chain_name))
            <span class="hd-badge-outline">{{ $hotel->chain_name }}</span>
          @endif
        </div>
        <div style="display:flex; align-items:center; gap:14px; flex-wrap:wrap;">
          <h1 class="hd-hero-title">{{ $hotel->title }}</h1>
          <div class="hd-stars">
            @for($i = 0; $i < $stars; $i++) <span>★</span> @endfor
          </div>
        </div>
        <div class="hd-hero-location">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/></svg>
          {{ $hotel->address ?? $destination }}
          <a href="#hd-anchor-location" style="color:var(--gold); text-decoration:none; border-bottom:1px dashed var(--gold); margin-left:8px;">Show on map</a>
        </div>

        @if($imageCount > 1)
          <button type="button" class="hd-view-all-btn" id="hdViewAllPhotos">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
            View all {{ $imageCount }} photos
          </button>
        @endif
      </div>
      <button class="hd-favourite-btn" style="background:transparent; border:1px solid rgba(255,255,255,0.2); color:#fff; border-radius:100px; padding:10px 18px; display:flex; align-items:center; gap:8px; font-family:'Jost',sans-serif; font-size:14px; cursor:pointer; transition:all 0.3s; margin-bottom:48px;" onmouseover="this.style.borderColor='var(--gold)'; this.style.color='var(--gold)';" onmouseout="this.style.borderColor='rgba(255,255,255,0.2)'; this.style.color='#fff';">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
        Favourite
      </button>
    </div>
  </div>
</div>

<!-- Lightbox -->
<div class="hd-lightbox" id="hdLightbox" role="dialog" aria-modal="true">
  <button type="button" class="hd-lightbox-close" id="hdLightboxClose" aria-label="Close">&#10005;</button>
  <button type="button" class="hd-lightbox-prev" id="hdLightboxPrev" aria-label="Previous">&#10094;</button>
  <img id="hdLightboxImg" src="" alt="{{ $hotel->title }}">
  <button type="button" class="hd-lightbox-next" id="hdLightboxNext" aria-label="Next">&#10095;</button>
  <div class="hd-lightbox-counter" id="hdLightboxCounter"></div>
</div>

<!-- Amenities popup — shared modal, filled in by JS when a room card's "Read more" is clicked -->
<div class="hd-modal-backdrop" id="hdAmenitiesModal" role="dialog" aria-modal="true">
  <div class="hd-modal hd-amenities-modal">
    <button type="button" class="hd-modal-close" id="hdAmenitiesModalClose" aria-label="Close">&#10005;</button>
    <h2 class="hd-modal-title" id="hdAmenitiesModalTitle">Room Amenities</h2>

    <div class="hd-amenities-modal-img" id="hdAmenitiesModalImgWrap" hidden>
      <img id="hdAmenitiesModalImg" src="" alt="">
    </div>

    <div class="hd-amenities-modal-meta" id="hdAmenitiesModalMeta"></div>

    <h3 class="hd-amenities-modal-subhead">Room Amenities</h3>
    <div class="hd-amenities-modal-grid" id="hdAmenitiesModalList"></div>
  </div>
</div>

<!-- Sticky section quick-jump nav -->
<nav class="hd-section-nav" id="hdSectionNav">
  <div class="hd-section-nav-inner">
    <a href="#hd-anchor-rooms">Rooms</a>
    <a href="#hd-anchor-overview">Overview &amp; Info</a>
    @if($hotel->source === 'tripjack')<a href="#htl-room-section">Available Rooms</a>@endif
    @if($hasLocation)<a href="#hd-anchor-location">Location</a>@endif
  </div>
</nav>

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

    {{-- ======================================================
         ROOMS — shown FIRST so they're immediately visible
    ====================================================== --}}

    {{-- Static room types (non-TripJack hotels with saved room types) --}}
    @if($hotel->roomTypes && $hotel->roomTypes->where('is_active', true)->count() > 0 && $hotel->source !== 'tripjack')
    <div class="hd-section" id="hd-anchor-rooms" style="margin-top:0;">
      <h2 class="hd-section-title">Room Types</h2>
      <div class="hd-room-list">
        @foreach($hotel->roomTypes->where('is_active', true) as $room)
          @php
             $roomImage = match(true) {
                 empty($room->image_path) => 'https://images.unsplash.com/photo-1578683010236-d716f9a3f461?w=600&q=80',
                 Str::startsWith($room->image_path, ['http://', 'https://']) => $room->image_path,
                 default => Storage::disk('public')->url($room->image_path),
             };
          @endphp
          
          <div class="hd-room-group-card" data-room-title="{{ $room->name }}" data-room-image="{{ $roomImage }}" data-room-bed="{{ $room->bed_type }}" data-room-size="{{ $room->room_size }}" data-room-guests="{{ ($room->occupancy_adults ?? 0) + ($room->occupancy_children ?? 0) }}" style="background: var(--dark-2); border: 1px solid rgba(255,255,255,0.08); border-radius: 16px; overflow: hidden; margin-bottom: 24px;">
            <div class="hd-room-group-row">

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
                @if($room->occupancy_adults)
                <div style="margin-top:10px; margin-bottom:2px;">
                  <span style="font-family: 'Jost', sans-serif; font-size: 13px; color: var(--white-80); display: flex; align-items: center; gap: 6px;"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2 M12 3a4 4 0 1 0 0 8 4 4 0 0 0 0-8z"/></svg>{{ $room->occupancy_adults }} Adult{{ $room->occupancy_adults > 1 ? 's' : '' }}@if($room->occupancy_children), {{ $room->occupancy_children }} Child{{ $room->occupancy_children > 1 ? 'ren' : '' }}@endif</span>
                </div>
                @endif
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
                      @foreach($room->inclusions as $incIndex => $inc)
                        <span class="{{ $incIndex >= 6 ? 'hd-inc-hidden' : '' }}"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M20 6L9 17l-5-5"/></svg> {{ $inc }}</span>
                      @endforeach
                    </div>
                    @if(count($room->inclusions) > 6)
                      <button type="button" class="hd-inc-readmore-btn">Read more</button>
                    @endif
                    @endif
                  </div>
                  
                  <div style="text-align: right; min-width: 150px; border-left: 1px dashed rgba(255,255,255,0.1); padding-left: 20px;">
                    <button class="hd-room-btn" type="button" style="width: 100%; border-radius: 100px; padding: 12px 16px;" onclick="document.getElementById('hdEnquirySection').scrollIntoView({behavior:'smooth', block:'start'})">
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
                <div class="hd-room-gallery-wrap" style="position: relative; margin-bottom: 20px;">
                  @if($roomImages->count() > 1)
                    <button type="button" class="hd-rg-btn hd-rg-prev" aria-label="Previous">❮</button>
                  @endif
                  
                  <div class="hd-room-gallery" style="margin-bottom: 0;">
                    @foreach($roomImages as $img)
                      <img src="{{ Str::startsWith($img, ['http://', 'https://']) ? $img : Storage::disk('public')->url($img) }}" alt="{{ $room->name }} Image">
                    @endforeach
                  </div>

                  @if($roomImages->count() > 1)
                    <button type="button" class="hd-rg-btn hd-rg-next" aria-label="Next">❯</button>
                  @endif
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
                <p style="font-family: 'Jost', sans-serif; font-size: 14px; color: var(--white-80); overflow-wrap: break-word; word-wrap: break-word; line-height: 1.6; margin-bottom: 24px;">
                    {!! $room->description !!}
                </p>
              @endif
              
              @if($room->inclusions && count($room->inclusions) > 0)
                <h3 style="font-family: 'Jost', sans-serif; font-size: 14px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.1em; color: var(--gold); margin-bottom: 14px;">Inclusions &amp; Amenities</h3>
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
    @elseif($hotel->source !== 'tripjack' && isset($roomCats) && count($roomCats) > 0)
    <div class="hd-section" id="hd-anchor-rooms">
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

    {{-- Placeholder anchor for TripJack rooms (scrolled to from the booking card) --}}
    <div id="hd-anchor-rooms"></div>

    {{-- ============================================================
         TRIPJACK HOTELS: Fill the left column with hotel highlights
         so the 2-col layout doesn't have an empty left side.
    ============================================================ --}}
    @if($hotel->source === 'tripjack')

      {{-- Hotel Description / About blurb --}}
      @if(!empty($hotel->description))
      <div style="margin-bottom: 24px;">
        <h2 style="font-family:'Cormorant Garamond',serif; font-size:1.5rem; font-weight:600; color:#fff; margin-bottom:12px; display:flex; align-items:center; gap:10px;">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--gold)" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v4m0 4h.01"/></svg>
          About This Hotel
        </h2>
        <p style="font-family:'Jost',sans-serif; font-size:14px; color:var(--white-60); line-height:1.75; display:-webkit-box; -webkit-line-clamp:5; -webkit-box-orient:vertical; overflow:hidden;">
          {{ strip_tags($hotel->description) }}
        </p>
      </div>
      @endif

      {{-- Top amenity highlights — show up to 6 as quick pill badges --}}
      @if($amenities->isNotEmpty())
      @php $topAmenities = $amenities->take(8); @endphp
      <div style="margin-bottom: 24px;">
        <h2 style="font-family:'Cormorant Garamond',serif; font-size:1.5rem; font-weight:600; color:#fff; margin-bottom:14px; display:flex; align-items:center; gap:10px;">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--gold)" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2l2.4 7.2H22l-6 4.4 2.3 7.2L12 16.4 5.7 20.8 8 13.6l-6-4.4h7.6z"/></svg>
          Top Amenities
        </h2>
        <div style="display:flex; flex-wrap:wrap; gap:8px;">
          @foreach($topAmenities as $amenity)
            <span style="display:inline-flex; align-items:center; gap:6px; background:rgba(201,168,76,0.07); border:1px solid rgba(201,168,76,0.2); border-radius:100px; padding:6px 14px; font-family:'Jost',sans-serif; font-size:12.5px; color:var(--white-80);">
              <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="var(--gold)" stroke-width="3"><path d="M20 6L9 17l-5-5"/></svg>
              {{ $amenity->name }}
            </span>
          @endforeach
          @if($amenities->count() > 8)
            <span style="display:inline-flex; align-items:center; padding:6px 14px; font-family:'Jost',sans-serif; font-size:12px; color:var(--gold); cursor:pointer;" onclick="document.querySelector('[data-tab=hd-tab-amenities]')?.click(); document.getElementById('hd-anchor-overview')?.scrollIntoView({behavior:'smooth'});">
              +{{ $amenities->count() - 8 }} more
            </span>
          @endif
        </div>
      </div>
      @endif

      {{-- CTA — scroll down to live room options --}}
      <div style="background:linear-gradient(135deg, rgba(201,168,76,0.10), rgba(201,168,76,0.04)); border:1px solid rgba(201,168,76,0.22); border-radius:16px; padding:20px 22px; display:flex; align-items:center; gap:16px;">
        <div style="width:42px; height:42px; border-radius:12px; background:rgba(201,168,76,0.15); display:flex; align-items:center; justify-content:center; flex-shrink:0;">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--gold)" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 7v13M21 7v13M3 12h18M7 7v0a2 2 0 012-2h6a2 2 0 012 2v0"/></svg>
        </div>
        <div style="flex:1; min-width:0;">
          <div style="font-family:'Jost',sans-serif; font-size:13.5px; font-weight:600; color:#fff; margin-bottom:3px;">Live Room Availability</div>
          <div style="font-family:'Jost',sans-serif; font-size:12px; color:var(--white-60); line-height:1.4;">Real-time prices & availability shown below for your selected dates.</div>
        </div>
        <a href="#htl-room-section" style="flex-shrink:0; display:inline-flex; align-items:center; gap:6px; background:var(--gold); color:var(--dark); border-radius:100px; padding:10px 18px; font-family:'Jost',sans-serif; font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:0.07em; text-decoration:none; transition:background 0.2s;">
          View Rooms
          <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
        </a>
      </div>

    @endif

  </div>{{-- end .hd-left --}}


  <!-- RIGHT COLUMN — Sticky Booking Card -->
  <div class="hd-right">
    <div class="hd-book-card" style="padding: 24px;">

      @php
        $cheapestLive = ($liveOptions ?? collect())->isNotEmpty()
          ? ($liveOptions ?? collect())->sortBy('pricing.customerPrice')->first()
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
          {{ $cheapestLive['pricing']['currency'] ?? 'INR' }} {{ number_format($cheapestLive['pricing']['customerPrice'] ?? 0) }}
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

{{-- =====================================================
     HOTEL INFO TABS — Overview, Amenities, Location, Policies
     Shown below the 2-col layout so rooms stay primary
===================================================== --}}
@php
  // Re-declare helpers used inside the tab panel (same logic as before)
  $isChipList = fn ($text) => ! str_contains($text, '.') && str_contains($text, ',') && strlen($text) < 200;
  $extractKeywords = function ($text) {
    $text = trim(preg_replace('/\s+/', ' ', strip_tags($text)));
    if ($text === '') return [];
    $fillers = ['make yourself at home in ','one of the ','featuring ','feature ','features ','furnished with ','while ','conveniences include ','convenience includes ','includes ','include ','including ','providing ','guests can enjoy ','guests will enjoy ','enjoy ','your ','all rooms are ','rooms are ','this room has ','this room includes ','complimentary '];
    $connectors = [' comes with ',' come with ',' furnished with ',' provide ',' provides ',' keeps you connected',' keep you connected'];
    $dropExact = ['all','you','which','and','the'];
    $stripFillers = function ($frag) use ($fillers) {
      for ($i = 0; $i < 3; $i++) { $matched = false; foreach ($fillers as $f) { if (stripos($frag, $f) === 0) { $frag = trim(substr($frag, strlen($f))); $matched = true; break; } } if (! $matched) break; }
      return trim($frag);
    };
    $points = [];
    foreach (preg_split('/(?<=[.!])\s+/', $text) as $sentence) {
      $sentence = trim($sentence, " .\t\n\r\0\x0B");
      if ($sentence === '') continue;
      foreach (preg_split('/\s*,\s*(?:and\s+)?|\s+and\s+/i', $sentence) as $fragment) {
        $fragment = $stripFillers(trim($fragment));
        $subParts = [$fragment];
        foreach ($connectors as $c) { if (stripos($fragment, $c) !== false) { $subParts = array_map('trim', preg_split('/' . preg_quote($c, '/') . '/i', $fragment, 2)); break; } }
        foreach ($subParts as $sp) {
          $sp = $stripFillers($sp);
          if ($sp === '' || strlen($sp) < 3 || strlen($sp) > 70) continue;
          if (in_array(strtolower($sp), $dropExact, true)) continue;
          $sp = mb_strtoupper(mb_substr($sp, 0, 1)) . mb_substr($sp, 1);
          $points[] = $sp;
        }
      }
    }
    return array_values(array_unique($points));
  };
  $sectionIcons = [
    'Location' => '<path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/>',
    'Amenities' => '<path d="M12 2l2.4 7.2H22l-6 4.4 2.3 7.2L12 16.4 5.7 20.8 8 13.6l-6-4.4h7.6z"/>',
    'Rooms' => '<path d="M3 7v13M21 7v13M3 12h18M7 7v0a2 2 0 012-2h6a2 2 0 012 2v0"/>',
    'Dining' => '<path d="M6 2v7a2 2 0 002 2h0a2 2 0 002-2V2M8 11v11M18 2c-1.5 1-2 3-2 5v3a2 2 0 002 2v9"/>',
    'Business Amenities' => '<rect x="3" y="7" width="18" height="13" rx="2"/><path d="M8 7V5a2 2 0 012-2h4a2 2 0 012 2v2"/>',
    'Onsite Payments' => '<rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20"/>',
    'Spoken Languages' => '<circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15 15 0 010 20 15 15 0 010-20z"/>',
  ];
  $defaultIcon = '<circle cx="12" cy="12" r="9"/><path d="M12 8v4l3 2"/>';

  $hasOverview  = $aboutSectionsToShow->isNotEmpty();
  $hasAmenities = $amenities->isNotEmpty() || $extraAmenitySections->isNotEmpty();
  $hasLocation  = count($nearbyAttr) > 0 || count($restaurantsCafes) > 0 || count($topAttractions) > 0 || !empty($hotel->bottom_sections['Attractions']);
  $hasPolicies  = !empty($hotel->special_instructions) || !empty($hotel->know_before_you_go) || !empty($hotel->house_rules) || !empty($hotel->mandatory_fees);
  $anyTab = $hasOverview || $hasAmenities || $hasLocation || $hasPolicies;
@endphp

@if($anyTab)
<section class="hd-info-tabs-section" id="hd-anchor-overview">
  <div class="hd-tabs-strip" role="tablist" id="hdInfoTabStrip">
    @if($hasOverview)  <button class="hd-tab-btn active" role="tab" data-tab="hd-tab-overview"  aria-selected="true">Overview</button>@endif
    @if($hasAmenities) <button class="hd-tab-btn {{ !$hasOverview ? 'active' : '' }}" role="tab" data-tab="hd-tab-amenities" aria-selected="{{ !$hasOverview ? 'true' : 'false' }}" id="hd-anchor-amenities">Amenities</button>@endif
    @if($hasPolicies)  <button class="hd-tab-btn {{ !$hasOverview && !$hasAmenities ? 'active' : '' }}" role="tab" data-tab="hd-tab-policies"  aria-selected="{{ !$hasOverview && !$hasAmenities ? 'true' : 'false' }}" id="hd-anchor-policies">Policies</button>@endif
  </div>

  {{-- TAB: OVERVIEW --}}
  @if($hasOverview)
  <div class="hd-tab-panel active" id="hd-tab-overview" role="tabpanel">
    <div class="hd-tab-info-grid">
      @foreach($hotel->description_sections as $sectionTitle => $sectionText)
        @continue(in_array($sectionTitle, ['Rooms', 'Business Amenities']))
        @php
          $forceNarrowChips = in_array($sectionTitle, ['Onsite Payments', 'Spoken Languages']);
          $wide = $forceNarrowChips ? false : ! $isChipList($sectionText);
          $keywords = $wide ? $extractKeywords($sectionText) : [];
          $useKeywords = count($keywords) >= 3;
        @endphp
        <div class="hd-info-card">
          <div class="hd-info-card-head">
            <span class="hd-info-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">{!! $sectionIcons[$sectionTitle] ?? $defaultIcon !!}</svg></span>
            <h3>{{ $sectionTitle }}</h3>
          </div>
          @if($useKeywords)
            <div class="hd-point-list {{ count($keywords) > 4 ? 'hd-collapsible collapsed' : '' }}">
              @foreach($keywords as $point)
                <div class="hd-point"><span class="hd-point-icon"><svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M20 6L9 17l-5-5"/></svg></span>{{ $point }}</div>
              @endforeach
            </div>
            @if(count($keywords) > 4)<button type="button" class="hd-readmore-btn">Read more</button>@endif
          @elseif($wide)
            <div class="hd-desc">{!! $sectionText !!}</div>
          @else
            @php $chips = array_filter(array_map('trim', explode(',', $sectionText))); @endphp
            <div class="hd-info-chip-list {{ count($chips) > 6 ? 'hd-collapsible collapsed' : '' }}">
              @foreach($chips as $chip)<span class="hd-info-chip">{{ $chip }}</span>@endforeach
            </div>
            @if(count($chips) > 6)<button type="button" class="hd-readmore-btn">Read more</button>@endif
          @endif
        </div>
      @endforeach
    </div>
  </div>
  @endif

  {{-- TAB: AMENITIES --}}
  @if($hasAmenities)
  <div class="hd-tab-panel {{ !$hasOverview ? 'active' : '' }}" id="hd-tab-amenities" role="tabpanel">
    <div class="hd-tab-amenities-wrap">
      @foreach($extraAmenitySections as $sectionTitle => $sectionText)
        @php
          $exWide = ! $isChipList($sectionText);
          $exKeywords = $exWide ? $extractKeywords($sectionText) : [];
          $exUseKeywords = count($exKeywords) >= 3;
        @endphp
        <div class="hd-tab-amenity-cat">
          <div class="hd-tab-amenity-cat-head">
            <span class="hd-info-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">{!! $sectionIcons[$sectionTitle] ?? $defaultIcon !!}</svg></span>
            <h3>{{ $sectionTitle }}</h3>
          </div>
          @if($exUseKeywords)
            <div class="hd-point-list {{ count($exKeywords) > 4 ? 'hd-collapsible collapsed' : '' }}">
              @foreach($exKeywords as $point)<div class="hd-point"><span class="hd-point-icon"><svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M20 6L9 17l-5-5"/></svg></span>{{ $point }}</div>@endforeach
            </div>
            @if(count($exKeywords) > 4)<button type="button" class="hd-readmore-btn">Read more</button>@endif
          @elseif($exWide)
            <div class="hd-desc">{!! $sectionText !!}</div>
          @else
            <div class="hd-info-chip-list">
              @foreach(array_filter(array_map('trim', explode(',', $sectionText))) as $chip)<span class="hd-info-chip">{{ $chip }}</span>@endforeach
            </div>
          @endif
        </div>
      @endforeach

      @foreach($groupedAmenities as $category => $items)
        <div class="hd-tab-amenity-cat">
          <div class="hd-tab-amenity-cat-head">
            <span class="hd-info-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">{!! $amenityCategoryIcons[$category] ?? $amenityCategoryIcons['Other Amenities'] !!}</svg></span>
            <h3>{{ $category }}</h3>
          </div>
          <div class="hd-amenities {{ $items->count() > 6 ? 'hd-collapsible collapsed' : '' }}">
            @foreach($items as $amenity)<span class="hd-amenity"><span class="hd-amenity-dot"></span>{{ $amenity->name }}</span>@endforeach
          </div>
          @if($items->count() > 6)<button type="button" class="hd-readmore-btn">Read more</button>@endif
        </div>
      @endforeach
    </div>
  </div>
  @endif



  {{-- TAB: POLICIES --}}
  @if($hasPolicies)
  <div class="hd-tab-panel" id="hd-tab-policies" role="tabpanel">
    <div class="hd-tab-policy-grid">
      @if(!empty($hotel->mandatory_fees))
      <div class="hd-policy-block">
        <div class="hd-policy-head">
          <span class="hd-info-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20"/></svg></span>
          <h3>Fees Payable at the Property</h3>
        </div>
        @php $feeCount = substr_count($hotel->mandatory_fees, '<li'); @endphp
        <div class="{{ $feeCount > 4 ? 'hd-collapsible collapsed' : '' }}">{!! $hotel->mandatory_fees !!}</div>
        @if($feeCount > 4)<button type="button" class="hd-readmore-btn">Read more</button>@endif
      </div>
      @endif

      @if(!empty($hotel->special_instructions))
      <div class="hd-policy-block">
        <div class="hd-policy-head">
          <span class="hd-info-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v4m0 4h.01"/></svg></span>
          <h3>Good to Know</h3>
        </div>
        @php $siCount = substr_count($hotel->special_instructions, '<li'); @endphp
        <div class="{{ $siCount > 4 ? 'hd-collapsible collapsed' : '' }}">{!! $hotel->special_instructions !!}</div>
        @if($siCount > 4)<button type="button" class="hd-readmore-btn">Read more</button>@endif
      </div>
      @endif

      @if(!empty($hotel->know_before_you_go))
      <div class="hd-policy-block">
        <div class="hd-policy-head">
          <span class="hd-info-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M2 3h6a4 4 0 014 4v14a3 3 0 00-3-3H2z"/><path d="M22 3h-6a4 4 0 00-4 4v14a3 3 0 013-3h7z"/></svg></span>
          <h3>Know Before You Go</h3>
        </div>
        @php $kbygCount = substr_count($hotel->know_before_you_go, '<li'); @endphp
        <div class="{{ $kbygCount > 4 ? 'hd-collapsible collapsed' : '' }}">{!! $hotel->know_before_you_go !!}</div>
        @if($kbygCount > 4)<button type="button" class="hd-readmore-btn">Read more</button>@endif
      </div>
      @endif

      @if(!empty($hotel->house_rules) && is_array($hotel->house_rules))
      <div class="hd-policy-block">
        <div class="hd-policy-head">
          <span class="hd-info-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg></span>
          <h3>House Rules</h3>
        </div>
        <div class="hd-amenities {{ count($hotel->house_rules) > 6 ? 'hd-collapsible collapsed' : '' }}">
          @foreach($hotel->house_rules as $rule => $value)
            <span class="hd-amenity"><span class="hd-amenity-dot"></span>{{ Str::title(str_replace('_',' ', $rule)) }}: {{ is_bool($value) ? ($value ? 'Yes' : 'No') : $value }}</span>
          @endforeach
        </div>
        @if(count($hotel->house_rules) > 6)<button type="button" class="hd-readmore-btn">Read more</button>@endif
      </div>
      @endif
    </div>
  </div>
  @endif

</section>
@endif

<!-- ===================================================
     LIVE TRIPJACK ROOM OPTIONS — full page width, below the
     content/booking-card layout rather than squeezed into the
     narrower left column.
=================================================== -->
@if($hotel->source === 'tripjack')
<div style="max-width:1280px; margin:0 auto; padding:0 40px 40px;">
  <div class="hd-section" id="htl-room-section" style="margin-bottom:0;">
    <div style="display:flex; align-items:baseline; gap:14px; flex-wrap:wrap; margin-bottom:24px;">
      <h2 class="hd-section-title" style="margin:0;">Available Rooms</h2>
      @if(($liveOptions ?? collect())->isNotEmpty())
      <span style="font-family:'Jost',sans-serif; font-size:13px; color:var(--white-60);">Showing {{ $liveOptions->count() }} of {{ $liveOptions->count() }} room options</span>
      @endif
    </div>

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
        $nights = max(1, \Illuminate\Support\Carbon::parse($checkIn)->diffInDays(\Illuminate\Support\Carbon::parse($checkOut)));
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
          $roomImage = match(true) {
              empty($localRoom?->image_path) => 'https://images.unsplash.com/photo-1578683010236-d716f9a3f461?w=600&q=80', // Placeholder
              Str::startsWith($localRoom->image_path, ['http://', 'https://']) => $localRoom->image_path,
              default => Storage::disk('public')->url($localRoom->image_path),
          };
          // More rate-option cards in a room group means less vertical room per
          // card, so show fewer inclusion pills per card as the card count grows.
          $incLimit = match(true) {
              $options->count() <= 1 => 6,
              $options->count() == 2 => 4,
              default => 3,
          };
        @endphp

        @php
          $roomAdults = $localRoom->occupancy_adults ?? $adults;
          $roomChildren = $localRoom->occupancy_children ?? $children;
        @endphp
        <div class="hd-room-group-card" data-room-title="{{ $roomName }}" data-room-image="{{ $roomImage }}" data-room-bed="{{ $localRoom->bed_type ?? '' }}" data-room-size="{{ $localRoom->room_size ?? '' }}" data-room-guests="{{ $roomAdults + $roomChildren }}" style="background: var(--dark-2); border: 1px solid rgba(255,255,255,0.08); border-radius: 16px; overflow: hidden; margin-bottom: 24px;">
          <div class="hd-room-group-row">

            <!-- Left Column: Room Info -->
            <div style="width: 100%; max-width: 320px; border-right: 1px solid rgba(255,255,255,0.08); padding: 20px;">
              <div style="border-radius: 12px; overflow: hidden; height: 180px; margin-bottom: 16px; position: relative;">
                <img src="{{ $roomImage }}" alt="{{ $roomName }}" style="width: 100%; height: 100%; object-fit: cover;">
              </div>
              <div class="hd-room-specs" style="display: flex; flex-wrap: wrap; gap: 10px;">
                @if($localRoom && $localRoom->bed_type)
                <span class="hd-room-badge"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 4v16M22 4v16M2 8h20M6 4v4M18 4v4"/></svg>{{ $localRoom->bed_type }}</span>
                @endif
                @if($localRoom && $localRoom->room_size)
                <span class="hd-room-badge"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16v16H4z M4 9h16 M9 4v16"/></svg>{{ $localRoom->room_size }}</span>
                @endif
                <span class="hd-room-badge"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2 M12 3a4 4 0 1 0 0 8 4 4 0 0 0 0-8z"/></svg>Fits max. {{ $roomAdults + $roomChildren }} guests</span>
              </div>
              @if($localRoom && $localRoom->description)
              <div style="font-family: 'Jost', sans-serif; font-size: 12px; color: var(--white-60); margin-top: 14px; line-height: 1.5; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;">
                {!! strip_tags($localRoom->description) !!}
              </div>
              @endif
              @if(!empty($localRoom?->inclusions) && count($localRoom->inclusions) > 0)
              <div class="hd-room-inc" style="margin-top: 12px;">
                @foreach($localRoom->inclusions as $incIndex => $inc)
                  <span class="{{ $incIndex >= 4 ? 'hd-inc-hidden' : '' }}"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M20 6L9 17l-5-5"/></svg> {{ $inc }}</span>
                @endforeach
              </div>
              @if(count($localRoom->inclusions) > 4)
                <button type="button" class="hd-inc-readmore-btn">Read more</button>
              @endif
              @endif
            </div>

            <!-- Right Columns: Options List -->
            <div style="flex: 1; display: flex; flex-direction: column; justify-content: center; gap: 14px; padding: 20px; align-self: stretch;">
              @foreach($options->sortBy('pricing.customerPrice') as $index => $option)
                @php
                  $pricing = $option['pricing'] ?? [];
                  $cancellation = $option['cancellation'] ?? [];
                  $compliance = $option['compliance'] ?? [];
                  $isRefundable = $cancellation['isRefundable'] ?? false;
                  $freeUntil = collect($cancellation['penalties'] ?? [])->firstWhere('amount', 0);
                  $mealBasis = $option['mealBasis'] ?? 'Room Only';
                  // Customer-facing price (TripJack's raw price + TYTLUXE markup) — never
                  // display pricing['totalPrice'] directly, that's TripJack's raw cost.
                  $customerPrice = $pricing['customerPrice'] ?? 0;
                  $perNight = $customerPrice / $nights / max(1, $roomCount);
                  $rateId = 'hdRate_'.Str::slug($roomName).'_'.$loop->index;
                @endphp

                <div class="hd-rate-row">

                  <!-- Option Details (Middle Column) -->
                  <div style="flex: 1; min-width: 220px; padding-right: 20px;">
                    <h3 class="hd-rate-room-name">{{ $roomName }}</h3>
                    <div class="hd-rate-title">
                      {{ $mealBasis }}
                      <span class="sep">|</span>
                      <span class="{{ $isRefundable ? 'refundable' : 'non-refundable' }}">{{ $isRefundable ? 'Refundable' : 'Non-Refundable' }}</span>
                      @if($compliance['panRequired'] ?? false)
                        <span class="sep">|</span> PAN Required
                      @endif
                    </div>

                    @if($isRefundable)
                    <div class="hd-rate-cancel">
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 6L9 17l-5-5"/></svg>
                      Free Cancellation @if($freeUntil) before {{ \Illuminate\Support\Carbon::parse($freeUntil['to'])->format('jS F Y') }} @endif
                    </div>
                    @endif

                    @if(!empty($option['inclusions']))
                    <div class="hd-room-inc" style="margin-top: 10px;">
                      @foreach($option['inclusions'] as $incIndex => $inc)
                        <span class="{{ $incIndex >= $incLimit ? 'hd-inc-hidden' : '' }}"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M20 6L9 17l-5-5"/></svg> {{ $inc }}</span>
                      @endforeach
                    </div>
                    @if(count($option['inclusions']) > $incLimit)
                      <button type="button" class="hd-inc-readmore-btn">Read more</button>
                    @endif
                    @endif

                    @if($compliance['passportRequired'] ?? false)
                    <div style="font-family: 'Jost', sans-serif; font-size: 11.5px; color: var(--gold); margin-top: 8px;">
                      <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right:4px; vertical-align:middle;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                      Passport Required
                    </div>
                    @endif

                    @if($localRoom && $localRoom->description)
                    <a class="hd-rate-more" data-toggle="{{ $rateId }}">View more</a>
                    <div id="{{ $rateId }}" class="hd-desc" style="display:none; margin-top:10px; font-size:13px;">{!! strip_tags($localRoom->description) !!}</div>
                    @endif
                  </div>

                  <!-- Pricing & Select (Right Column) -->
                  <div class="hd-rate-price">
                    @if(($pricing['strikethrough'] ?? null) > $totalPrice)
                    <div style="font-family:'Jost',sans-serif; font-size:12px; color:rgba(255,255,255,0.35); text-decoration:line-through;">{{ $pricing['currency'] ?? 'INR' }} {{ number_format($pricing['strikethrough']) }}</div>
                    @endif
                    <div class="hd-rate-price-per-night">{{ $pricing['currency'] ?? 'INR' }} {{ number_format($perNight) }}/night</div>
                    <div class="hd-rate-price-total">{{ $pricing['currency'] ?? 'INR' }} {{ number_format($customerPrice) }}</div>
                    <div class="hd-rate-price-caption">Total price for {{ $roomCount }} room{{ $roomCount > 1 ? 's' : '' }}</div>
                    <form method="POST" action="{{ route('hotel.review', $hotel->slug) }}" class="hd-select-room-form">
                      @csrf
                      <input type="hidden" name="option_id" value="{{ $option['optionId'] ?? '' }}">
                      <input type="hidden" name="check_in" value="{{ $checkIn }}">
                      <input type="hidden" name="check_out" value="{{ $checkOut }}">
                      <input type="hidden" name="adults" value="{{ $adults }}">
                      <input type="hidden" name="children" value="{{ $children }}">
                      <input type="hidden" name="rooms" value="{{ $roomCount }}">
                      <button type="submit" class="hd-room-btn" style="width: 100%; border-radius: 100px; padding: 12px 16px;">
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
</div>
@endif

<!-- ===================================================
     LOCATION & ATTRACTIONS SECTION (rendered below Rooms)
=================================================== -->
@if($hasLocation)
<div style="max-width:1280px; margin:0 auto; padding:0 40px 48px; scroll-margin-top:80px;" id="hd-anchor-location">
  <div class="hd-section">
    <div style="background: linear-gradient(160deg, rgba(255,255,255,0.035), rgba(255,255,255,0.01)); border: 1px solid rgba(255,255,255,0.08); border-radius: 18px; padding: 28px 32px;">
      <div class="hd-info-card-head" style="margin-bottom:20px;">
        <span class="hd-info-icon" style="width:40px; height:40px; border-radius:10px;"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="width:18px; height:18px; color:var(--gold);"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg></span>
        <h2 class="hd-section-title" style="margin:0; border:none; font-size:1.6rem;">Location &amp; Nearby Attractions</h2>
      </div>

      @if(!empty($hotel->bottom_sections['Attractions']))
      <div style="margin-bottom:24px;">
        <div class="hd-place-list-wrap">
          {!! $hotel->bottom_sections['Attractions'] !!}
        </div>
      </div>
      @endif

      @if(count($nearbyAttr) > 0)
      <h3 style="font-family:'Cormorant Garamond',serif; font-size:1.3rem; color:var(--gold); margin-bottom:12px;">Nearby Attractions</h3>
      <div class="hd-places-grid" style="margin-bottom:24px;">
        @foreach($nearbyAttr as $attr)
          @php $parts = preg_split('/\s*[\(\-—]\s*/', $attr, 2); $placeName = trim($parts[0]); $placeDist = isset($parts[1]) ? trim(rtrim($parts[1], ')')) : ''; @endphp
          <div class="hd-place-card">
            <div class="hd-place-icon">📍</div>
            <div class="hd-place-info"><div class="hd-place-name" title="{{ $placeName }}">{{ $placeName }}</div>@if($placeDist)<div class="hd-place-dist">{{ $placeDist }}</div>@endif</div>
          </div>
        @endforeach
      </div>
      @endif

      @if(count($restaurantsCafes) > 0)
      <h3 style="font-family:'Cormorant Garamond',serif; font-size:1.3rem; color:var(--gold); margin-bottom:12px;">Restaurants &amp; Caf&eacute;s</h3>
      <div class="hd-places-grid" style="margin-bottom:24px;">
        @foreach($restaurantsCafes as $item)
          @php $parts = preg_split('/\s*[\(\-—]\s*/', $item, 2); $placeName = trim($parts[0]); $placeDist = isset($parts[1]) ? trim(rtrim($parts[1], ')')) : ''; @endphp
          <div class="hd-place-card">
            <div class="hd-place-icon">🍽️</div>
            <div class="hd-place-info"><div class="hd-place-name" title="{{ $placeName }}">{{ $placeName }}</div>@if($placeDist)<div class="hd-place-dist">{{ $placeDist }}</div>@endif</div>
          </div>
        @endforeach
      </div>
      @endif

      @if(count($topAttractions) > 0)
      <h3 style="font-family:'Cormorant Garamond',serif; font-size:1.3rem; color:var(--gold); margin-bottom:12px;">Top Attractions</h3>
      <div class="hd-places-grid">
        @foreach($topAttractions as $item)
          @php $parts = preg_split('/\s*[\(\-—]\s*/', $item, 2); $placeName = trim($parts[0]); $placeDist = isset($parts[1]) ? trim(rtrim($parts[1], ')')) : ''; @endphp
          <div class="hd-place-card top">
            <div class="hd-place-icon">🏛️</div>
            <div class="hd-place-info"><div class="hd-place-name" title="{{ $placeName }}">{{ $placeName }}</div>@if($placeDist)<div class="hd-place-dist">{{ $placeDist }}</div>@endif</div>
          </div>
        @endforeach
      </div>
      @endif
    </div>
  </div>
</div>
@endif

@if(!empty($hotel->bottom_sections) && is_array($hotel->bottom_sections))
@php
  $renderBottomSections = collect($hotel->bottom_sections)->filter(function($text, $title) use ($hasLocation) {
    return !($title === 'Attractions' && $hasLocation);
  });
@endphp

@if($renderBottomSections->isNotEmpty())
<!-- ===================================================
     BOTTOM NOTICES (Renovations, ...)
=================================================== -->
<div style="max-width:1280px; margin:0 auto; padding:0 40px 56px;">
  @php
    $bottomIcons = [
      'Attractions' => '<path d="M3 21l6-14 4 9 3-6 5 11"/><circle cx="7" cy="5" r="2"/>',
      'Renovations' => '<path d="M14.7 6.3a1 1 0 000 1.4l1.6 1.6a1 1 0 001.4 0l3.77-3.77a6 6 0 01-7.94 7.94l-6.91 6.91a2.12 2.12 0 01-3-3l6.91-6.91a6 6 0 017.94-7.94l-3.76 3.76z"/>',
    ];
  @endphp
  @foreach($renderBottomSections as $sectionTitle => $sectionText)
    <div class="hd-section">
      <div style="background: linear-gradient(160deg, rgba(255,255,255,0.035), rgba(255,255,255,0.01)); border: 1px solid rgba(255,255,255,0.08); border-radius: 18px; padding: 30px 32px;">
        <div class="hd-info-card-head" style="margin-bottom:22px;">
          <span class="hd-info-icon" style="width:44px; height:44px; border-radius:12px;"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="width:20px; height:20px; color:var(--gold);">{!! $bottomIcons[$sectionTitle] ?? '<circle cx="12" cy="12" r="9"/>' !!}</svg></span>
          <h2 class="hd-section-title" style="margin:0; border:none; font-size:1.7rem;">{{ $sectionTitle }}</h2>
        </div>
        <div class="hd-desc hd-place-list-wrap">{!! $sectionText !!}</div>
      </div>
    </div>
  @endforeach
</div>
@endif
@endif


<!-- ===================================================
     STICKY MOBILE CTA BAR (shown only on ≤1024px)
=================================================== -->
<div class="hd-sticky-cta" id="hdStickyCta">
  <div class="hd-sticky-cta-info">
    <div class="hd-sticky-cta-name">{{ $hotel->title }}</div>
    <div class="hd-sticky-cta-price">Price on Request · {{ $destination }}</div>
  </div>
  <button class="hd-sticky-cta-btn" id="hdStickyEnquireBtn">
    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/></svg>
    Enquire
  </button>
  <a href="https://wa.me/919875073788?text={{ $waText }}" class="hd-sticky-cta-wa" target="_blank" aria-label="Chat on WhatsApp">
    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
  </a>
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
  // Also wire the sticky mobile CTA button
  const stickyEnquireBtn = document.getElementById('hdStickyEnquireBtn');
  if (stickyEnquireBtn) stickyEnquireBtn.addEventListener('click', openModal);
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

  /* ===== RATE PLAN "VIEW MORE" TOGGLE (inline expand, not a modal) ===== */
  document.querySelectorAll('.hd-rate-more[data-toggle]').forEach(btn => {
    btn.addEventListener('click', (e) => {
      e.preventDefault();
      const target = document.getElementById(btn.getAttribute('data-toggle'));
      if (!target) return;
      const isOpen = target.style.display !== 'none';
      target.style.display = isOpen ? 'none' : 'block';
      btn.textContent = isOpen ? 'View more' : 'View less';
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

    /* ===== HERO GALLERY SLIDESHOW ===== */
    (function () {
      const main = document.getElementById('hdGalleryMain');
      if (!main) return;
      const slides = Array.from(main.querySelectorAll('.hd-gallery-slide'));
      const prevBtn = document.getElementById('hdGalleryPrev');
      const nextBtn = document.getElementById('hdGalleryNext');
      const counter = document.getElementById('hdGalleryCounter');
      if (slides.length < 2) return;

      let current = 0;
      function showSlide(index) {
        slides[current].classList.remove('active');
        current = (index + slides.length) % slides.length;
        slides[current].classList.add('active');
        if (counter) counter.textContent = (current + 1) + ' / ' + slides.length;
      }

      if (prevBtn) prevBtn.addEventListener('click', () => showSlide(current - 1));
      if (nextBtn) nextBtn.addEventListener('click', () => showSlide(current + 1));
    })();

    /* ===== PHOTO STRIP + LIGHTBOX ===== */
    (function () {
      const mainSlides = Array.from(document.querySelectorAll('#hdGalleryMain .hd-gallery-slide img'));
      if (!mainSlides.length) return;
      const photoUrls = mainSlides.map(img => img.src);

      const lightbox = document.getElementById('hdLightbox');
      const lbImg = document.getElementById('hdLightboxImg');
      const lbCounter = document.getElementById('hdLightboxCounter');
      const lbClose = document.getElementById('hdLightboxClose');
      const lbPrev = document.getElementById('hdLightboxPrev');
      const lbNext = document.getElementById('hdLightboxNext');
      if (!lightbox || !lbImg) return;

      let lbIndex = 0;
      function openLightbox(index) {
        lbIndex = (index + photoUrls.length) % photoUrls.length;
        lbImg.src = photoUrls[lbIndex];
        lbCounter.textContent = (lbIndex + 1) + ' / ' + photoUrls.length;
        lightbox.classList.add('open');
        document.body.style.overflow = 'hidden';
      }
      function closeLightbox() {
        lightbox.classList.remove('open');
        document.body.style.overflow = '';
      }

      document.getElementById('hdViewAllPhotos')?.addEventListener('click', () => openLightbox(0));
      lbClose?.addEventListener('click', closeLightbox);
      lbPrev?.addEventListener('click', () => openLightbox(lbIndex - 1));
      lbNext?.addEventListener('click', () => openLightbox(lbIndex + 1));
      lightbox.addEventListener('click', (e) => { if (e.target === lightbox) closeLightbox(); });
      document.addEventListener('keydown', (e) => {
        if (!lightbox.classList.contains('open')) return;
        if (e.key === 'Escape') closeLightbox();
        if (e.key === 'ArrowLeft') openLightbox(lbIndex - 1);
        if (e.key === 'ArrowRight') openLightbox(lbIndex + 1);
      });
    })();

    /* ===== SECTION NAV: ACTIVE LINK ON SCROLL ===== */
    (function () {
      const navLinks = Array.from(document.querySelectorAll('.hd-section-nav a'));
      if (!navLinks.length) return;
      const targets = navLinks
        .map(link => document.querySelector(link.getAttribute('href')))
        .filter(Boolean);
      if (!targets.length) return;

      const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
          if (!entry.isIntersecting) return;
          const id = '#' + entry.target.id;
          navLinks.forEach(link => link.classList.toggle('active', link.getAttribute('href') === id));
        });
      }, { rootMargin: '-120px 0px -70% 0px', threshold: 0 });

      targets.forEach(t => observer.observe(t));
    })();

    /* ===== HOTEL INFO TABS ===== */
    (function () {
      const strip = document.getElementById('hdInfoTabStrip');
      if (!strip) return;
      const btns = Array.from(strip.querySelectorAll('.hd-tab-btn'));
      btns.forEach(btn => {
        btn.addEventListener('click', () => {
          btns.forEach(b => { b.classList.remove('active'); b.setAttribute('aria-selected', 'false'); });
          btn.classList.add('active'); btn.setAttribute('aria-selected', 'true');
          document.querySelectorAll('.hd-tab-panel').forEach(p => p.classList.remove('active'));
          const target = document.getElementById(btn.dataset.tab);
          if (target) target.classList.add('active');
        });
      });

      // Allow sticky nav anchors (#hd-anchor-amenities etc.) to activate the right tab
      document.querySelectorAll('.hd-section-nav a[href^="#hd-anchor-"]').forEach(navLink => {
        navLink.addEventListener('click', () => {
          const anchor = navLink.getAttribute('href').replace('#', '');
          // Map anchor IDs to tab data-tab values
          const map = { 'hd-anchor-overview': 'hd-tab-overview', 'hd-anchor-amenities': 'hd-tab-amenities', 'hd-anchor-location': 'hd-tab-location', 'hd-anchor-policies': 'hd-tab-policies' };
          const tabId = map[anchor];
          if (!tabId) return;
          const tabBtn = strip.querySelector(`[data-tab="${tabId}"]`);
          if (tabBtn) setTimeout(() => tabBtn.click(), 80);
        });
      });
    })();

    /* ===== AMENITY GROUP "READ MORE" TOGGLE ===== */
    document.querySelectorAll('.hd-readmore-btn').forEach(btn => {
      const list = btn.previousElementSibling;
      if (!list || !list.classList.contains('hd-collapsible')) return;
      btn.addEventListener('click', () => {
        const isCollapsed = list.classList.toggle('collapsed');
        btn.textContent = isCollapsed ? 'Read more' : 'Show less';
      });
    });

    /* ===== ATTRACTIONS SHOW MORE TOGGLE ===== */
    (function () {
      const wrappers = document.querySelectorAll('.hd-place-list-wrap');
      wrappers.forEach(wrap => {
        const list = wrap.querySelector('.hd-place-list');
        if (!list) return;
        const items = list.querySelectorAll('li');
        if (items.length <= 6) return;

        wrap.classList.add('collapsed');
        
        let btn = wrap.querySelector('.hd-attr-toggle-btn');
        if (!btn) {
          btn = document.createElement('button');
          btn.type = 'button';
          btn.className = 'hd-attr-toggle-btn';
          wrap.appendChild(btn);
        }
        
        const updateBtnText = () => {
          const isCollapsed = wrap.classList.contains('collapsed');
          btn.innerHTML = isCollapsed 
            ? `Show all ${items.length} attractions <span style="font-size:11px; margin-left:2px;">▼</span>`
            : `Show less <span style="font-size:11px; margin-left:2px;">▲</span>`;
        };
        
        updateBtnText();
        
        btn.addEventListener('click', function () {
          wrap.classList.toggle('collapsed');
          updateBtnText();
        });
      });
    })();

    /* ===== ROOM CARD INCLUSIONS "READ MORE" — opens the full list in a popup ===== */
    (function () {
      const modal = document.getElementById('hdAmenitiesModal');
      const listEl = document.getElementById('hdAmenitiesModalList');
      const titleEl = document.getElementById('hdAmenitiesModalTitle');
      const closeBtn = document.getElementById('hdAmenitiesModalClose');
      const imgWrap = document.getElementById('hdAmenitiesModalImgWrap');
      const imgEl = document.getElementById('hdAmenitiesModalImg');
      const metaEl = document.getElementById('hdAmenitiesModalMeta');
      if (!modal || !listEl || !titleEl) return;

      const bedIcon = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 4v16M22 4v16M2 8h20M6 4v4M18 4v4"/></svg>';
      const sizeIcon = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16v16H4z M4 9h16 M9 4v16"/></svg>';
      const guestIcon = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2 M12 3a4 4 0 1 0 0 8 4 4 0 0 0 0-8z"/></svg>';

      function openAmenitiesModal({ items, title, image, bed, size, guests }) {
        titleEl.textContent = title || 'Room Amenities';

        if (image) {
          imgEl.src = image;
          imgEl.alt = title || '';
          imgWrap.hidden = false;
        } else {
          imgWrap.hidden = true;
        }

        const meta = [];
        if (guests) meta.push(guestIcon + 'Fits max. ' + guests + ' guests');
        if (bed) meta.push(bedIcon + bed);
        if (size) meta.push(sizeIcon + size);
        metaEl.innerHTML = meta.map(html => '<span>' + html + '</span>').join('');

        listEl.innerHTML = items.map(text => (
          '<div class="hd-point"><span class="hd-point-icon"><svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M20 6L9 17l-5-5"/></svg></span>' + text + '</div>'
        )).join('');

        modal.classList.add('open');
        document.body.style.overflow = 'hidden';
      }
      function closeAmenitiesModal() {
        modal.classList.remove('open');
        document.body.style.overflow = '';
      }
      closeBtn?.addEventListener('click', closeAmenitiesModal);
      modal.addEventListener('click', (e) => { if (e.target === modal) closeAmenitiesModal(); });
      document.addEventListener('keydown', (e) => { if (e.key === 'Escape' && modal.classList.contains('open')) closeAmenitiesModal(); });

      document.querySelectorAll('.hd-inc-readmore-btn').forEach(btn => {
        const list = btn.previousElementSibling;
        if (!list || !list.classList.contains('hd-room-inc')) return;
        const items = Array.from(list.querySelectorAll('span')).map(s => s.textContent.trim()).filter(Boolean);
        const roomCard = btn.closest('[data-room-title]');
        btn.addEventListener('click', () => openAmenitiesModal({
          items,
          title: roomCard?.dataset.roomTitle,
          image: roomCard?.dataset.roomImage,
          bed: roomCard?.dataset.roomBed,
          size: roomCard?.dataset.roomSize,
          guests: roomCard?.dataset.roomGuests,
        }));
      });
    })();

    /* ===== MOUSE DRAG TO SCROLL FOR GALLERY ===== */
    document.querySelectorAll('.hd-room-gallery').forEach(slider => {
      let isDown = false;
      let startX;
      let scrollLeft;

      slider.addEventListener('mousedown', (e) => {
        isDown = true;
        slider.style.scrollSnapType = 'none';
        startX = e.pageX - slider.offsetLeft;
        scrollLeft = slider.scrollLeft;
      });
      
      slider.addEventListener('mouseleave', () => {
        isDown = false;
        slider.style.scrollSnapType = 'x mandatory';
      });
      
      slider.addEventListener('mouseup', () => {
        isDown = false;
        slider.style.scrollSnapType = 'x mandatory';
      });
      
      slider.addEventListener('mousemove', (e) => {
        if (!isDown) return;
        e.preventDefault();
        const x = e.pageX - slider.offsetLeft;
        const walk = (x - startX) * 2; // Scroll-fast
        slider.scrollLeft = scrollLeft - walk;
      });
    });

    /* ===== ARROW BUTTONS FOR GALLERY ===== */
    document.querySelectorAll('.hd-room-gallery-wrap').forEach(wrap => {
      const slider = wrap.querySelector('.hd-room-gallery');
      const prevBtn = wrap.querySelector('.hd-rg-prev');
      const nextBtn = wrap.querySelector('.hd-rg-next');
      
      if (!slider || !prevBtn || !nextBtn) return;
      
      prevBtn.addEventListener('click', () => {
        const scrollAmount = slider.clientWidth; // Scroll one image width
        slider.scrollBy({ left: -scrollAmount, behavior: 'smooth' });
      });
      
      nextBtn.addEventListener('click', () => {
        const scrollAmount = slider.clientWidth;
        slider.scrollBy({ left: scrollAmount, behavior: 'smooth' });
      });
    });
  });
</script>
@endpush
