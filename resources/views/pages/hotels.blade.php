@extends('layouts.frontend')

@section('meta_title', 'Luxury Hotels — Handpicked Stays Across India & World | TYT Luxe')
@section('meta_description', 'Browse TYT Luxe\'s curated collection of luxury hotels — beach resorts, city hotels, honeymoon getaways and family-friendly stays across India and international destinations. Best price guaranteed.')

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com" />
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;1,400;1,600&family=Jost:wght@300;400;500;600&display=swap" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

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

/* ===== SEARCH BAR (OTA-style: MakeMyTrip / Agoda layout) ===== */
.htl-searchbar {
  max-width: 1180px; margin: 0 auto 18px;
  background: var(--dark-2); border: 1px solid rgba(201,168,76,0.28);
  border-radius: 20px; padding: 18px; box-shadow: 0 24px 60px rgba(0,0,0,0.45);
}
.htl-searchbar-row { display: flex; align-items: stretch; gap: 12px; flex-wrap: wrap; }
.htl-sb-field {
  position: relative; flex: 1 1 190px; min-width: 160px;
  display: flex; flex-direction: column; justify-content: center; gap: 5px;
  padding: 14px 20px; border-radius: 14px; cursor: pointer;
  background: rgba(255,255,255,0.035); border: 1.5px solid rgba(255,255,255,0.09);
  transition: all var(--transition);
}
.htl-sb-field:hover, .htl-sb-field.open {
  background: rgba(201,168,76,0.07); border-color: rgba(201,168,76,0.45);
}
.htl-sb-field.htl-sb-dest { flex: 1.7 1 260px; }
.htl-sb-label {
  font-family: 'Jost', sans-serif; font-size: 11px; font-weight: 700;
  letter-spacing: 0.1em; text-transform: uppercase; color: var(--gold);
  display: flex; align-items: center; gap: 7px;
}
.htl-sb-label svg { width: 14px; height: 14px; flex-shrink: 0; }
.htl-sb-required { color: #f3a3a3; font-weight: 700; }
.htl-sb-field.htl-sb-error {
  border-color: #f3a3a3 !important; background: rgba(243,163,163,0.06) !important;
  animation: htlShake 0.35s ease;
}
@keyframes htlShake {
  0%, 100% { transform: translateX(0); }
  25% { transform: translateX(-4px); }
  75% { transform: translateX(4px); }
}
.htl-sb-field input[type="text"],
.htl-sb-field input[type="date"] {
  border: none; outline: none; background: transparent; color: #fff;
  font-family: 'Jost', sans-serif; font-size: 16px; font-weight: 500; padding: 0; width: 100%;
  cursor: pointer; color-scheme: dark;
}
.htl-sb-field input::placeholder { color: var(--white-30); font-weight: 400; }
.htl-sb-field input[readonly] { cursor: pointer; }
#htlGuestSummary { font-family: 'Jost', sans-serif; font-size: 16px; font-weight: 500; color: #fff; }
.htl-sb-nights-badge {
  position: absolute; top: 12px; right: 16px;
  background: rgba(201,168,76,0.15); border: 1px solid rgba(201,168,76,0.35);
  color: var(--gold); font-family: 'Jost', sans-serif; font-size: 11px; font-weight: 700;
  padding: 3px 10px; border-radius: 100px;
}
.htl-sb-divider { display: none; }
.htl-sb-submit {
  align-self: center; height: 50px;
  flex: 0 0 auto; min-width: 140px; display: inline-flex; align-items: center; justify-content: center; gap: 8px;
  padding: 0 24px; border-radius: 100px; border: none; cursor: pointer;
  background: linear-gradient(90deg, #c9a84c, #e8c96b); color: var(--dark);
  font-family: 'Jost', sans-serif; font-size: 14px; font-weight: 700;
  letter-spacing: 0.06em; text-transform: uppercase; transition: all var(--transition);
  box-shadow: 0 8px 24px rgba(201,168,76,0.25); white-space: nowrap;
}
.htl-sb-submit svg { width: 16px; height: 16px; flex-shrink: 0; }
.htl-sb-submit:hover { background: linear-gradient(90deg, #d8b753, #eecd74); transform: translateY(-2px); box-shadow: 0 12px 30px rgba(201,168,76,0.4); }

/* Guests popover */
.htl-guest-popover {
  position: absolute; top: calc(100% + 8px); left: 0; width: 320px; z-index: 60;
  background: #1c1c1c; border: 1px solid rgba(201,168,76,0.3); border-radius: 14px;
  padding: 16px; box-shadow: 0 20px 44px rgba(0,0,0,0.6);
  display: none; max-height: 420px; overflow-y: auto;
}
.htl-guest-popover.open { display: block; }
.htl-guest-row { display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px; }
.htl-guest-row:last-child { margin-bottom: 0; }
.htl-guest-row-label { font-family: 'Jost', sans-serif; font-size: 13px; color: #fff; display: flex; flex-direction: column; }
.htl-guest-row-label small { font-size: 10.5px; color: var(--white-30); font-weight: 400; margin-top: 2px; }
.htl-guest-ctrl { display: flex; align-items: center; gap: 12px; }
.htl-guest-ctrl button {
  background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.12);
  color: #fff; width: 26px; height: 26px; border-radius: 6px; cursor: pointer; font-size: 14px;
}
.htl-guest-ctrl button:hover:not(:disabled) { border-color: var(--gold); color: var(--gold); }
.htl-guest-ctrl button:disabled { opacity: 0.3; cursor: not-allowed; }
.htl-guest-ctrl span { color: #fff; font-family: 'Jost', sans-serif; font-size: 13px; width: 16px; text-align: center; }

/* Room blocks (multi-room) */
.htl-room-block { border: 1px solid rgba(255,255,255,0.08); border-radius: 12px; padding: 14px; margin-bottom: 12px; background: rgba(255,255,255,0.02); }
.htl-room-block-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; padding-bottom: 10px; border-bottom: 1px dashed rgba(255,255,255,0.1); }
.htl-room-block-title { font-family: 'Jost', sans-serif; font-size: 12px; font-weight: 700; letter-spacing: 0.06em; text-transform: uppercase; color: var(--gold); }
.htl-room-block-remove { background: transparent; border: none; color: var(--white-30); font-size: 13px; cursor: pointer; padding: 2px 6px; }
.htl-room-block-remove:hover { color: #f3a3a3; }
.htl-child-ages { margin-top: 10px; padding-top: 10px; border-top: 1px dashed rgba(255,255,255,0.08); display: flex; flex-direction: column; gap: 8px; }
.htl-child-age-row { display: flex; align-items: center; justify-content: space-between; gap: 10px; }
.htl-child-age-select {
  background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.14); border-radius: 6px;
  color: #fff; font-family: 'Jost', sans-serif; font-size: 12px; padding: 5px 8px; cursor: pointer;
}
.htl-child-age-select option { background: var(--dark-3); color: #fff; }
.htl-guest-actions { display: flex; align-items: center; justify-content: space-between; padding-top: 6px; margin-top: 4px; border-top: 1px solid rgba(255,255,255,0.08); }
.htl-guest-add-btn {
  background: transparent; border: none; color: var(--gold); font-family: 'Jost', sans-serif;
  font-size: 12px; font-weight: 700; letter-spacing: 0.05em; cursor: pointer; padding: 0;
}
.htl-guest-add-btn:hover { color: var(--gold-light); }
.htl-guest-apply-btn {
  background: var(--gold); color: var(--dark); border: none; border-radius: 100px;
  font-family: 'Jost', sans-serif; font-size: 11.5px; font-weight: 800; letter-spacing: 0.08em;
  text-transform: uppercase; padding: 9px 20px; cursor: pointer;
}
.htl-guest-apply-btn:hover { background: var(--gold-light); }

/* Flatpickr theme (dark/gold, matches site convention) */
.flatpickr-calendar { background: #1c1c1c !important; border: 1px solid rgba(201,168,76,0.3) !important; box-shadow: 0 20px 44px rgba(0,0,0,0.7) !important; border-radius: 12px !important; }
.flatpickr-months .flatpickr-month, .flatpickr-current-month { color: var(--gold) !important; fill: var(--gold) !important; }
.flatpickr-current-month .flatpickr-monthDropdown-months, .flatpickr-current-month input.cur-year { font-family: 'Jost', sans-serif !important; color: var(--gold) !important; background: transparent !important; }
.flatpickr-current-month .flatpickr-monthDropdown-months option { background: #1c1c1c; color: #fff; }
span.flatpickr-weekday { color: var(--white-60) !important; font-family: 'Jost', sans-serif !important; font-weight: 500 !important; background: transparent !important; }
.flatpickr-day { color: #fff !important; font-family: 'Jost', sans-serif !important; border-radius: 6px !important; }
.flatpickr-day.inRange, .flatpickr-day.inRange:hover { background: rgba(201,168,76,0.15) !important; border-color: rgba(201,168,76,0.15) !important; box-shadow: -5px 0 0 rgba(201,168,76,0.15), 5px 0 0 rgba(201,168,76,0.15) !important; }
.flatpickr-day.selected, .flatpickr-day.startRange, .flatpickr-day.endRange { background: var(--gold) !important; color: var(--dark) !important; border-color: var(--gold) !important; font-weight: 600 !important; }
.flatpickr-day:hover { background: rgba(255,255,255,0.1) !important; }
.flatpickr-day.flatpickr-disabled, .flatpickr-day.flatpickr-disabled:hover, .flatpickr-day.prevMonthDay, .flatpickr-day.nextMonthDay { color: rgba(255,255,255,0.2) !important; }
.flatpickr-months .flatpickr-prev-month, .flatpickr-months .flatpickr-next-month { fill: var(--gold) !important; color: var(--gold) !important; }
.flatpickr-months .flatpickr-prev-month:hover svg, .flatpickr-months .flatpickr-next-month:hover svg { fill: var(--gold-light) !important; }
.flatpickr-innerContainer, .flatpickr-rContainer { background: transparent !important; }

/* More Options row */
.htl-more-options {
  display: flex; align-items: center; gap: 18px; flex-wrap: wrap;
  padding: 12px 8px 4px; font-family: 'Jost', sans-serif; font-size: 12.5px; color: var(--white-60);
}
.htl-more-options .htl-mo-label { font-weight: 600; letter-spacing: 0.05em; color: var(--white-60); }
.htl-more-options select {
  background: transparent; border: none; color: var(--white-60);
  font-family: 'Jost', sans-serif; font-size: 12.5px; cursor: pointer; outline: none;
}
.htl-more-options select option { background: var(--dark-3); color: #fff; }
.htl-mo-checkbox { display: flex; align-items: center; gap: 6px; cursor: not-allowed; opacity: 0.5; }
.htl-mo-checkbox input { accent-color: var(--gold); }

/* ===== HOTEL GRID & RESULTS LAYOUT ===== */
.htl-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 32px; margin-top: 48px; }
@media (max-width: 1100px) { .htl-grid { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 640px)  { .htl-grid { grid-template-columns: 1fr; } }

.htl-results-layout {
  display: grid; grid-template-columns: 280px 1fr; gap: 40px; margin-top: 40px;
}
@media (max-width: 992px) {
  .htl-results-layout { grid-template-columns: 1fr; gap: 32px; }
}

/* Results render as a vertical list of horizontal rows (OTA-style), not a card grid */
.htl-results-layout .htl-grid {
  grid-template-columns: 1fr; margin-top: 0; gap: 20px;
}
/* Fixed row height (not min-height) is deliberate: without a hard-defined
   ancestor height, img{height:100%} can't resolve, so the browser falls back
   to the photo's own intrinsic aspect ratio at 280px wide — tall/portrait
   source photos then dictate the whole row's height. A fixed height breaks
   that dependency outright; overflow:hidden on the text columns clips any
   hotel with unusually long content instead of growing the row. */
.htl-results-layout .htl-card {
  flex-direction: row; align-items: stretch; height: 232px;
}
.htl-list-thumb {
  width: 280px; height: 100%; flex-shrink: 0; position: relative; overflow: hidden;
  border-radius: 22px 0 0 22px;
}
.htl-list-thumb img { width: 100%; height: 100%; object-fit: cover; display: block; }
.htl-list-mid {
  flex: 1; min-width: 0; height: 100%; padding: 18px 20px; display: flex; flex-direction: column;
  border-right: 1px solid rgba(255,255,255,0.07); overflow: hidden;
}
.htl-list-title {
  display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;
  overflow: hidden; word-break: break-word;
}
.htl-list-amenities {
  display: flex; flex-wrap: nowrap; gap: 6px; margin-top: auto;
  overflow: hidden; white-space: nowrap;
}
.htl-list-amenities > span { flex-shrink: 0; }
.htl-list-side {
  width: 210px; height: 100%; flex-shrink: 0; padding: 18px 20px; overflow: hidden;
  display: flex; flex-direction: column; align-items: flex-end; justify-content: space-between; text-align: right;
}
@media (max-width: 900px) {
  .htl-results-layout .htl-card { flex-direction: column; height: auto; }
  .htl-list-thumb { width: 100%; height: 220px; border-radius: 22px 22px 0 0; }
  .htl-list-mid { height: auto; border-right: none; border-bottom: 1px solid rgba(255,255,255,0.07); }
  .htl-list-side { width: 100%; height: auto; flex-direction: row-reverse; align-items: center; }
}

/* Sidebar */
.htl-sidebar {
  background: var(--dark-2); border: 1px solid var(--white-10); border-radius: 16px;
  padding: 24px; height: max-content; position: sticky; top: 24px;
}
.htl-sidebar-title {
  font-family: 'Jost', sans-serif; font-size: 16px; font-weight: 600; color: #fff;
  margin-bottom: 24px; padding-bottom: 14px; border-bottom: 1px solid var(--white-10);
}
.htl-filter-group { margin-bottom: 28px; }
.htl-filter-group:last-child { margin-bottom: 0; }
.htl-filter-title {
  font-family: 'Jost', sans-serif; font-size: 12px; font-weight: 700; color: var(--gold);
  text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 14px;
}
.htl-filter-list { display: flex; flex-direction: column; gap: 12px; }
.htl-filter-label {
  display: flex; align-items: center; gap: 10px;
  font-family: 'Jost', sans-serif; font-size: 14px; color: var(--white-60); cursor: pointer; transition: color 0.2s ease;
}
.htl-filter-label:hover { color: #fff; }
.htl-filter-label input[type="radio"], .htl-filter-label input[type="checkbox"] {
  accent-color: var(--gold); width: 16px; height: 16px; cursor: pointer;
}

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
  align-items: flex-start;
  align-content: flex-start;
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
  .htl-searchbar { padding: 12px; border-radius: 16px; }
  .htl-searchbar-row { gap: 10px; }
  .htl-sb-field { flex: 1 1 100%; padding: 12px 16px; }
  .htl-sb-submit { flex: 1 1 100%; padding: 14px; min-height: 50px; }
  .htl-trust-inner { gap: 24px; }
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
    <form class="htl-searchbar" role="search" method="GET" action="{{ route('hotels') }}" id="htlSearchForm">
      <div class="htl-searchbar-row">

        <div class="htl-sb-field htl-sb-dest">
          <label class="htl-sb-label" for="htlDestinationSearch">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
            Destination
          </label>
          <input type="text" id="htlDestinationSearch" name="destination" placeholder="Where are you going?" autocomplete="off" list="htlDestinationList" value="{{ $destinationQuery ?? '' }}" required>
          <datalist id="htlDestinationList">
            @foreach($destinations ?? [] as $d)
              <option value="{{ $d }}"></option>
            @endforeach
          </datalist>
        </div>

        <div class="htl-sb-field" id="htlCheckInField">
          <label class="htl-sb-label" for="htlCheckIn">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
            Check-in
          </label>
          <input type="text" id="htlCheckIn" readonly placeholder="Select date" autocomplete="off" required>
        </div>

        <div class="htl-sb-field" id="htlCheckOutField" style="position:relative;">
          <span class="htl-sb-nights-badge" id="htlNightsBadge" hidden></span>
          <label class="htl-sb-label" for="htlCheckOut">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
            Check-out
          </label>
          <input type="text" id="htlCheckOut" readonly placeholder="Select date" autocomplete="off" required>
        </div>

        <input type="hidden" id="htlCheckInIso" name="check_in" value="{{ $checkIn ?? '' }}">
        <input type="hidden" id="htlCheckOutIso" name="check_out" value="{{ $checkOut ?? '' }}">
        <input type="text" id="htlDateRangePicker" style="position:absolute; width:0; height:0; opacity:0; pointer-events:none;" tabindex="-1">

        <div class="htl-sb-field" id="htlGuestField">
          <label class="htl-sb-label">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            Rooms &amp; Guests
            <svg width="10" height="10" viewBox="0 0 12 12" style="margin-left:2px;"><path fill="currentColor" d="M6 8L1 3h10z"/></svg>
          </label>
          <span id="htlGuestSummary">{{ $roomCount ?? 1 }} Room, {{ $adults ?? 2 }} Adult{{ ($adults ?? 2) > 1 ? 's' : '' }}</span>

          <input type="hidden" id="htlAdults" name="adults" value="{{ $adults ?? 2 }}">
          <input type="hidden" id="htlChildren" name="children" value="{{ $children ?? 0 }}">
          <input type="hidden" id="htlRooms" name="rooms" value="{{ $roomCount ?? 1 }}">
          <input type="hidden" id="htlChildAges" name="child_ages" value="{{ implode(',', $childAges ?? []) }}">

          <div class="htl-guest-popover" id="htlGuestPopover" onclick="event.stopPropagation()">
            <div id="htlRoomBlocks"></div>
            <div class="htl-guest-actions">
              <button type="button" class="htl-guest-add-btn" id="htlAddRoomBtn">+ Add Room</button>
              <button type="button" class="htl-guest-apply-btn" id="htlGuestApplyBtn">Apply</button>
            </div>
          </div>
        </div>

        <button type="submit" class="htl-sb-submit">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></svg>
          Search
        </button>
      </div>

      @if(empty($hasSearched))
      <div class="htl-more-options">
        <span class="htl-mo-label">More Options :</span>

        <label style="display:flex; align-items:center; gap:6px; cursor:pointer;">
          Rating
          <select name="min_rating">
            <option value="0" {{ ($minRating ?? 0) == 0 ? 'selected' : '' }}>Any</option>
            @for($r = 3; $r <= 5; $r++)
              <option value="{{ $r }}" {{ ($minRating ?? 0) == $r ? 'selected' : '' }}>{{ $r }} Stars</option>
            @endfor
          </select>
        </label>

      </div>
      @else
      <!-- Hidden input to preserve min_rating when doing a new search from the results page -->
      <input type="hidden" name="min_rating" id="hiddenMinRating" value="{{ $minRating ?? 0 }}">
      @endif
    </form>

    @if(!empty($searchError))
    <div style="max-width:720px; margin:0 auto 28px; padding:14px 20px; border-radius:12px; background:rgba(201,168,76,0.08); border:1px solid var(--gold-dim); color:var(--white-60); font-family:'Jost',sans-serif; font-size:13px; text-align:center;">
      {{ $searchError }}
    </div>
    @endif

    <!-- Hotel Grid or Results Layout -->
    @if(!empty($hasSearched))
    <div class="htl-results-layout">
      <!-- Sidebar Filters -->
      <aside class="htl-sidebar">
        <div class="htl-sidebar-title">Filters</div>
        
        <div class="htl-filter-group">
          <div class="htl-filter-title">Search by Name</div>
          <input type="text" id="htlDestinationSearch" placeholder="E.g. Taj Dubai..." style="width:100%; padding: 12px 14px; background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.1); border-radius: 8px; color:#fff; font-family:'Jost', sans-serif; outline:none;" autocomplete="off">
        </div>

        <div class="htl-filter-group">
          <div class="htl-filter-title">Star Rating</div>
          <div class="htl-filter-list" id="htlRatingFilterGroup">
            <label class="htl-filter-label">
              <input type="radio" name="sidebar_rating" value="0" {{ ($minRating ?? 0) == 0 ? 'checked' : '' }}>
              Any Rating
            </label>
            <label class="htl-filter-label">
              <input type="radio" name="sidebar_rating" value="5" {{ ($minRating ?? 0) == 5 ? 'checked' : '' }}>
              5 Stars
            </label>
            <label class="htl-filter-label">
              <input type="radio" name="sidebar_rating" value="4" {{ ($minRating ?? 0) == 4 ? 'checked' : '' }}>
              4 Stars
            </label>
            <label class="htl-filter-label">
              <input type="radio" name="sidebar_rating" value="3" {{ ($minRating ?? 0) == 3 ? 'checked' : '' }}>
              3 Stars
            </label>
          </div>
        </div>

        <div class="htl-filter-group">
          <div class="htl-filter-title">Cancellation Policy</div>
          <div class="htl-filter-list">
            <label class="htl-filter-label">
              <input type="checkbox" id="sidebar_free_cancellation" value="true">
              Free Cancellation Available
            </label>
          </div>
        </div>

        <div class="htl-filter-group">
          <div class="htl-filter-title">Price Range</div>
          <div class="htl-filter-list">
             <!-- Visual Slider -->
             <div style="margin-bottom: 12px; padding:0 2px;">
               <input type="range" id="filterPriceSlider" min="0" max="250000" step="5000" value="250000" style="width:100%; accent-color:var(--gold); cursor:pointer;">
               <div style="display:flex; justify-content:space-between; font-family:'Jost',sans-serif; font-size:12px; color:var(--white-60); margin-top:6px;">
                 <span>₹0</span>
                 <span id="filterPriceLabel">₹250,000+</span>
               </div>
             </div>
             <!-- Radio buckets (shortcuts) -->
             <label class="htl-filter-label">
              <input type="radio" name="sidebar_price" value="250000" checked>
              Any Price
            </label>
            <label class="htl-filter-label">
              <input type="radio" name="sidebar_price" value="10000">
              Up to ₹10,000
            </label>
            <label class="htl-filter-label">
              <input type="radio" name="sidebar_price" value="25000">
              Up to ₹25,000
            </label>
            <label class="htl-filter-label">
              <input type="radio" name="sidebar_price" value="50000">
              Up to ₹50,000
            </label>
            <label class="htl-filter-label">
              <input type="radio" name="sidebar_price" value="100000">
              Up to ₹100,000
            </label>
          </div>
        </div>

        <div class="htl-filter-group">
          <div class="htl-filter-title">Meal Basis</div>
          <div class="htl-filter-list" id="htlMealFilterGroup">
            <label class="htl-filter-label">
              <input type="checkbox" name="sidebar_meal" value="room">
              Room Only
            </label>
            <label class="htl-filter-label">
              <input type="checkbox" name="sidebar_meal" value="breakfast">
              Breakfast Included
            </label>
            <label class="htl-filter-label">
              <input type="checkbox" name="sidebar_meal" value="half">
              Half Board
            </label>
            <label class="htl-filter-label">
              <input type="checkbox" name="sidebar_meal" value="full">
              Full Board
            </label>
          </div>
        </div>
      </aside>

      <!-- Main Results -->
      <main class="htl-results-main">
        <div class="htl-grid" id="htlGrid">

      @forelse($hotels as $hotel)
      @php
        $destination  = $hotel->destination?->name ?? 'Unknown';
        $slug         = Str::slug($destination);
        $images       = $hotel->images ?? collect();
        $imageCount   = $images->count();
        $firstImagePath = $imageCount > 0 ? $images->first()->path : null;
        $firstImage   = $firstImagePath
                          ? (Str::startsWith($firstImagePath, ['http://', 'https://']) ? $firstImagePath : Storage::disk('public')->url($firstImagePath))
                          : null;
        $stars        = min((int) $hotel->star_rating, 5);
        $liveOption   = ($liveOptions ?? collect())->get((string) $hotel->tripjack_hotel_id);
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
      <a href="{{ route('hotel.details', array_filter([
            'slug' => $hotel->slug,
            'check_in' => $checkIn ?? null,
            'check_out' => $checkOut ?? null,
            'adults' => $adults ?? null,
            'children' => $children ?? null,
            'rooms' => $roomCount ?? null,
            'child_ages' => !empty($childAges) ? implode(',', $childAges) : null,
         ])) }}"
         class="htl-card"
         data-category="{{ $slug }}"
         data-name="{{ Str::slug($hotel->title) }}"
         data-location="{{ $slug }}"
         data-amenities="{{ Str::slug($amenityNames) }}"
         data-rating="{{ $stars }}"
         data-price="{{ $liveOption['totalPrice'] ?? 0 }}"
         data-cancellation="{{ ($liveOption['isRefundable'] ?? false) ? 'true' : 'false' }}"
         data-meal="{{ Str::slug($liveOption['mealBasis'] ?? 'none') }}"
         style="text-decoration: none;">

        <!-- Thumbnail -->
        <div class="htl-list-thumb">
          @if($firstImage)
            <img src="{{ $firstImage }}" alt="{{ $hotel->title }}, {{ $destination }}" loading="lazy" style="width:100%; height:100%; object-fit:cover;" />
          @else
            <div style="width:100%; height:100%; background:linear-gradient(135deg,#1c1c1c,#252525); display:flex; align-items:center; justify-content:center; flex-direction:column; gap:12px;">
              <svg width="44" height="44" viewBox="0 0 24 24" fill="none" stroke="rgba(201,168,76,0.25)" stroke-width="1.2"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
              <span style="font-family:'Jost',sans-serif; font-size:11px; color:rgba(255,255,255,0.2); letter-spacing:0.1em; text-transform:uppercase;">No Photo Yet</span>
            </div>
          @endif

          {{-- Heart --}}
          <button class="htl-heart" aria-label="Save to wishlist" onclick="event.preventDefault(); this.classList.toggle('active');" style="top:12px; right:12px; width:32px; height:32px; background:rgba(0,0,0,0.45); backdrop-filter:blur(6px);">
            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
          </button>

          {{-- Image counter bottom center --}}
          @if($imageCount > 0)
          <div style="position:absolute; bottom:12px; left:50%; transform:translateX(-50%); background:rgba(0,0,0,0.65); color:#fff; font-family:'Jost',sans-serif; font-size:11px; font-weight:600; padding:4px 10px; border-radius:12px; z-index:2; backdrop-filter:blur(4px);">
            1 / {{ $imageCount }}
          </div>
          @endif
        </div>

        <!-- Middle: name, location, rating, meal/cancellation, amenities -->
        <div class="htl-list-mid">
          <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:10px;">
            <div class="htl-list-title" style="flex:1; min-width:0; font-family:'Cormorant Garamond', serif; font-size:20px; font-weight:600; color:#fff; line-height:1.25;">{{ $hotel->title }}</div>
            <div style="display:flex; gap:2px; color:var(--gold); font-size:13px; flex-shrink:0; white-space:nowrap; margin-top:4px;">
              @for($i = 0; $i < $stars; $i++) ★ @endfor
            </div>
          </div>

          <div style="color:var(--white-60); font-family:'Jost',sans-serif; font-size:13.5px; display:flex; align-items:center; gap:5px; margin-top:4px;">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
            {{ $destination }}
          </div>

          <div style="height:1px; background:rgba(255,255,255,0.08); margin:14px 0;"></div>

          {{-- Meal & cancellation --}}
          <ul style="list-style:disc; margin-left:16px; padding-left:4px; font-family:'Jost',sans-serif; font-size:12.5px; color:var(--white-60); margin-bottom:10px;">
            @if($liveOption && !empty($liveOption['mealBasis']))
              <li>{{ $liveOption['mealBasis'] }}</li>
            @else
              <li>Room Only</li>
            @endif
            @if($liveOption && isset($liveOption['isRefundable']))
              @if($liveOption['isRefundable'])
                <li style="color:var(--green); font-weight:500;">Free Cancellation Available</li>
              @else
                <li style="color:#f3a3a3;">Non-Refundable</li>
              @endif
            @endif
          </ul>

          {{-- Amenity chips --}}
          @if($amenities->isNotEmpty())
          <div class="htl-list-amenities" data-extra-count="{{ max(0, $amenities->count() - 12) }}">
            @foreach($amenities->take(12) as $am)
              <span class="htl-amenity-chip" style="background:rgba(255,255,255,0.04); border:1px solid rgba(255,255,255,0.09); color:rgba(255,255,255,0.65); font-family:'Jost',sans-serif; font-size:11px; padding:4px 11px; border-radius:100px; white-space:nowrap;">{{ $am->name }}</span>
            @endforeach
            <span class="htl-amenity-more" style="display:none; color:var(--gold); font-family:'Jost',sans-serif; font-size:11px; padding:4px 4px; white-space:nowrap; flex-shrink:0;"></span>
          </div>
          @endif
        </div>

        <!-- Right: star rating label + price -->
        <div class="htl-list-side">
          @if($stars > 0)
          <div style="font-family:'Jost',sans-serif; font-size:12px; color:var(--white-60); font-weight:500; text-align:right;">
            {{ $starLabel }}
          </div>
          @endif

          <div style="font-family:'Jost',sans-serif;">
            @if($liveOption && $liveOption['totalPrice'])
              @php
                 // Carbon v3's diffInDays() is signed (unlike v2's always-absolute
                 // default) — calling it in the wrong order silently returned a
                 // negative number here, which max(1, ...) then floored to 1,
                 // making per-night always equal the total regardless of stay length.
                 $nights = max(1, abs(Carbon\Carbon::parse($checkIn ?? now())->diffInDays(Carbon\Carbon::parse($checkOut ?? now()->addDay()))));
                 $pricePerNight = round($liveOption['totalPrice'] / $nights);
              @endphp
              <div style="font-size:12px; color:var(--white-60); margin-bottom:2px;">
                ₹ {{ number_format($pricePerNight) }} <span style="font-size:10px;">/night</span>
              </div>
              <div style="font-size:22px; font-weight:700; color:#fff; line-height:1.1;">
                ₹ {{ number_format($liveOption['totalPrice']) }}
              </div>
              <div style="font-size:11px; color:var(--white-60); margin-top:2px;">Total (incl. taxes)</div>
              <div class="htl-req-btn" style="margin-top:12px; display:inline-flex;">View Deal</div>
            @else
              <div style="font-size:16px; font-weight:600; color:var(--gold); line-height:1.1;">Price on Request</div>
              <div style="font-size:11px; color:var(--white-60); margin-top:4px;">Contact us for details</div>
              <div class="htl-req-btn" style="margin-top:12px; display:inline-flex;">Enquire Now</div>
            @endif
          </div>
        </div>
      </a>

      @empty
        @if(empty($hasSearched))
        <div style="grid-column: 1 / -1; text-align: center; padding: 60px 40px; color: var(--white-60);">
          <div style="display:inline-flex; align-items:center; justify-content:center; width:64px; height:64px; border-radius:50%; background:rgba(201,168,76,0.1); color:var(--gold); margin-bottom:20px;">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
          </div>
          <p style="font-family: 'Cormorant Garamond', serif; font-size: 1.8rem; color: #fff; margin-bottom: 12px;">Discover Luxury Stays</p>
          <p style="font-family: 'Jost', sans-serif; font-size: 14px; font-weight: 300;">Enter your destination and dates above to find the perfect hotel for your next trip.</p>
        </div>
        @else
        <div style="grid-column: 1 / -1; text-align: center; padding: 60px 40px; color: var(--white-60);">
          <p style="font-family: 'Cormorant Garamond', serif; font-size: 1.8rem; color: #fff; margin-bottom: 12px;">No hotels found</p>
          <p style="font-family: 'Jost', sans-serif; font-size: 14px; font-weight: 300;">We couldn't find any hotels matching your search criteria. Try adjusting your filters or destination.</p>
        </div>
        @endif
      @endforelse

        </div>
      </main>
    </div>
    @else
    <!-- Empty state wrapper when not searched -->
    <div class="htl-grid" id="htlGrid">
      @if(empty($hasSearched))
      <div style="grid-column: 1 / -1; text-align: center; padding: 60px 40px; color: var(--white-60);">
        <div style="display:inline-flex; align-items:center; justify-content:center; width:64px; height:64px; border-radius:50%; background:rgba(201,168,76,0.1); color:var(--gold); margin-bottom:20px;">
          <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
        </div>
        <p style="font-family: 'Cormorant Garamond', serif; font-size: 1.8rem; color: #fff; margin-bottom: 12px;">Discover Luxury Stays</p>
        <p style="font-family: 'Jost', sans-serif; font-size: 14px; font-weight: 300;">Enter your destination and dates above to find the perfect hotel for your next trip.</p>
      </div>
      @endif
    </div>
    @endif

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
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
(function () {

  /* ===== SEARCH BAR: ROOMS & GUESTS (multi-room blocks) ===== */
  const guestField = document.getElementById('htlGuestField');
  const guestPopover = document.getElementById('htlGuestPopover');
  const guestSummary = document.getElementById('htlGuestSummary');
  const roomBlocksEl = document.getElementById('htlRoomBlocks');
  const addRoomBtn = document.getElementById('htlAddRoomBtn');
  const applyBtn = document.getElementById('htlGuestApplyBtn');

  const initialRoomCount = parseInt(document.getElementById('htlRooms')?.value || '1', 10);
  const initialAdults = parseInt(document.getElementById('htlAdults')?.value || '2', 10);
  const initialChildren = parseInt(document.getElementById('htlChildren')?.value || '0', 10);
  const initialAgesPool = (document.getElementById('htlChildAges')?.value || '')
    .split(',').map(v => v.trim()).filter(v => v !== '').map(v => parseInt(v, 10));

  // Distribute the flat adults/children/rooms (and any server-provided ages,
  // e.g. from a prior search) across N room blocks for initial display.
  let rooms = [];
  {
    let remA = initialAdults, remC = initialChildren;
    let ageCursor = 0;
    for (let i = 0; i < initialRoomCount; i++) {
      const left = initialRoomCount - i;
      const a = Math.max(1, Math.ceil(remA / left));
      const c = Math.floor(remC / left);
      const ages = [];
      for (let k = 0; k < c; k++) {
        ages.push(ageCursor < initialAgesPool.length ? initialAgesPool[ageCursor++] : null);
      }
      rooms.push({ adults: a, children: c, childAges: ages });
      remA -= a; remC -= c;
    }
  }

  function ageOptions(selected) {
    let opts = '<option value="" disabled ' + (selected === null ? 'selected' : '') + '>Age</option>';
    for (let age = 0; age <= 17; age++) {
      opts += `<option value="${age}" ${selected === age ? 'selected' : ''}>${age} ${age === 1 ? 'yr' : 'yrs'}</option>`;
    }
    return opts;
  }

  function renderRoomBlocks() {
    roomBlocksEl.innerHTML = '';
    rooms.forEach((room, i) => {
      const block = document.createElement('div');
      block.className = 'htl-room-block';
      const childAgeRows = room.children > 0 ? `
        <div class="htl-child-ages">
          ${room.childAges.map((age, ci) => `
            <div class="htl-child-age-row">
              <span class="htl-guest-row-label" style="font-size:12px;">Child ${ci + 1} Age</span>
              <select data-room="${i}" data-child="${ci}" class="htl-child-age-select" required>${ageOptions(age)}</select>
            </div>
          `).join('')}
        </div>` : '';
      block.innerHTML = `
        <div class="htl-room-block-header">
          <span class="htl-room-block-title">Room ${i + 1}</span>
          ${rooms.length > 1 ? `<button type="button" class="htl-room-block-remove" data-remove="${i}">Remove</button>` : ''}
        </div>
        <div class="htl-guest-row">
          <span class="htl-guest-row-label">Adults</span>
          <div class="htl-guest-ctrl">
            <button type="button" data-room="${i}" data-key="adults" data-dir="-1" ${room.adults <= 1 ? 'disabled' : ''}>&minus;</button>
            <span>${room.adults}</span>
            <button type="button" data-room="${i}" data-key="adults" data-dir="1" ${room.adults >= 6 ? 'disabled' : ''}>+</button>
          </div>
        </div>
        <div class="htl-guest-row">
          <span class="htl-guest-row-label">Children<small>0-17 years old</small></span>
          <div class="htl-guest-ctrl">
            <button type="button" data-room="${i}" data-key="children" data-dir="-1" ${room.children <= 0 ? 'disabled' : ''}>&minus;</button>
            <span>${room.children}</span>
            <button type="button" data-room="${i}" data-key="children" data-dir="1" ${room.children >= 4 ? 'disabled' : ''}>+</button>
          </div>
        </div>
        ${childAgeRows}
      `;
      roomBlocksEl.appendChild(block);
    });

    roomBlocksEl.querySelectorAll('[data-key]').forEach(btn => {
      btn.addEventListener('click', () => {
        const i = parseInt(btn.dataset.room, 10);
        const key = btn.dataset.key;
        const dir = parseInt(btn.dataset.dir, 10);
        const max = key === 'adults' ? 6 : 4;
        const min = key === 'adults' ? 1 : 0;
        rooms[i][key] = Math.min(max, Math.max(min, rooms[i][key] + dir));
        if (key === 'children') {
          const c = rooms[i].children;
          const ages = rooms[i].childAges;
          rooms[i].childAges = c > ages.length ? ages.concat(new Array(c - ages.length).fill(null)) : ages.slice(0, c);
        }
        renderRoomBlocks();
      });
    });
    roomBlocksEl.querySelectorAll('[data-remove]').forEach(btn => {
      btn.addEventListener('click', () => {
        rooms.splice(parseInt(btn.dataset.remove, 10), 1);
        renderRoomBlocks();
      });
    });
    roomBlocksEl.querySelectorAll('.htl-child-age-select').forEach(sel => {
      sel.addEventListener('change', () => {
        const i = parseInt(sel.dataset.room, 10);
        const ci = parseInt(sel.dataset.child, 10);
        rooms[i].childAges[ci] = sel.value === '' ? null : parseInt(sel.value, 10);
      });
    });
  }
  renderRoomBlocks();

  if (addRoomBtn) {
    addRoomBtn.addEventListener('click', () => {
      if (rooms.length >= 9) return;
      rooms.push({ adults: 1, children: 0, childAges: [] });
      renderRoomBlocks();
    });
  }

  // Syncs the hidden form fields + summary text from current room state.
  // Only called on page load (to match server-rendered defaults) and when
  // Apply is clicked — never on individual stepper/room clicks, so nothing
  // is actually submitted/reflected in the summary until the user confirms.
  function syncGuestFields() {
    const totalAdults = rooms.reduce((s, r) => s + r.adults, 0);
    const totalChildren = rooms.reduce((s, r) => s + r.children, 0);
    const allAges = rooms.flatMap(r => r.childAges).filter(a => a !== null);
    document.getElementById('htlRooms').value = rooms.length;
    document.getElementById('htlAdults').value = totalAdults;
    document.getElementById('htlChildren').value = totalChildren;
    document.getElementById('htlChildAges').value = allAges.join(',');
    let summary = rooms.length + (rooms.length === 1 ? ' Room, ' : ' Rooms, ') + totalAdults + (totalAdults === 1 ? ' Adult' : ' Adults');
    if (totalChildren > 0) summary += ', ' + totalChildren + (totalChildren === 1 ? ' Child' : ' Children');
    if (guestSummary) guestSummary.textContent = summary;
  }
  syncGuestFields();

  // Called only by the Apply button — this is where missing ages actually block.
  function applyGuestState() {
    const missingAge = rooms.some(r => r.childAges.some(a => a === null));
    if (missingAge) {
      alert('Please select an age for every child before applying.');
      return false;
    }
    syncGuestFields();
    return true;
  }

  if (applyBtn) {
    applyBtn.addEventListener('click', () => {
      if (!applyGuestState()) return;
      guestPopover.classList.remove('open');
      guestField.classList.remove('open');
    });
  }
  if (guestField) {
    guestField.addEventListener('click', (e) => {
      guestPopover.classList.toggle('open');
      guestField.classList.toggle('open');
    });
  }
  document.addEventListener('click', (e) => {
    if (guestPopover && !guestField.contains(e.target)) { 
      guestPopover.classList.remove('open'); 
      guestField.classList.remove('open'); 
    }
  });

  /* ===== SEARCH BAR: DATE RANGE CALENDAR (flatpickr) ===== */
  function initHotelSearchDatePicker() {
    const rangeInput = document.getElementById('htlDateRangePicker');
    const checkInDisplay = document.getElementById('htlCheckIn');
    const checkOutDisplay = document.getElementById('htlCheckOut');
    const checkInIso = document.getElementById('htlCheckInIso');
    const checkOutIso = document.getElementById('htlCheckOutIso');
    const nightsBadge = document.getElementById('htlNightsBadge');
    if (!rangeInput || typeof flatpickr === 'undefined') return;

    const initialCheckIn = checkInIso.value || null;
    const initialCheckOut = checkOutIso.value || null;

    const fp = flatpickr(rangeInput, {
      mode: 'range',
      minDate: 'today',
      dateFormat: 'Y-m-d',
      showMonths: window.innerWidth > 768 ? 2 : 1,
      positionElement: document.getElementById('htlCheckInField'),
      defaultDate: initialCheckIn && initialCheckOut ? [initialCheckIn, initialCheckOut] : null,
      onChange: function (selectedDates, dateStr, instance) {
        if (selectedDates.length >= 1) {
          checkInDisplay.value = instance.formatDate(selectedDates[0], 'D, j M Y');
          checkInIso.value = instance.formatDate(selectedDates[0], 'Y-m-d');
        } else {
          checkInDisplay.value = ''; checkInIso.value = '';
        }
        if (selectedDates.length === 2) {
          checkOutDisplay.value = instance.formatDate(selectedDates[1], 'D, j M Y');
          checkOutIso.value = instance.formatDate(selectedDates[1], 'Y-m-d');
          const nights = Math.round((selectedDates[1] - selectedDates[0]) / 86400000);
          nightsBadge.textContent = nights + 'N';
          nightsBadge.hidden = false;
        } else {
          checkOutDisplay.value = ''; checkOutIso.value = '';
          nightsBadge.hidden = true;
        }
      },
      onClose: function (selectedDates) {
        // Guided flow: once both check-in and check-out are picked (range
        // complete, calendar auto-closes), open Rooms & Guests next.
        if (selectedDates.length === 2 && guestField && guestPopover) {
          guestPopover.classList.add('open');
          guestField.classList.add('open');
        }
      },
    });

    [checkInDisplay, checkOutDisplay].forEach(el => {
      if (el) el.addEventListener('click', (e) => { e.stopPropagation(); fp.open(); });
    });

    // Guided flow: once a destination is entered/selected, open the date
    // picker automatically instead of making the user click it themselves.
    const destInput = document.getElementById('htlDestinationSearch');
    if (destInput) {
      destInput.addEventListener('change', () => {
        if (destInput.value.trim() && !checkInIso.value) fp.open();
      });
    }

    if (initialCheckIn && initialCheckOut) {
      const d1 = new Date(initialCheckIn);
      const d2 = new Date(initialCheckOut);
      checkInDisplay.value = fp.formatDate(d1, 'D, j M Y');
      checkOutDisplay.value = fp.formatDate(d2, 'D, j M Y');
      const nights = Math.round((d2 - d1) / 86400000);
      if (nights > 0) { nightsBadge.textContent = nights + 'N'; nightsBadge.hidden = false; }
    }
  }
  initHotelSearchDatePicker();

  /* ===== REQUIRE DESTINATION + DATES BEFORE SEARCH ===== */
  (function () {
    const form = document.getElementById('htlSearchForm');
    if (!form) return;

    function flagError(fieldEl) {
      if (!fieldEl) return;
      fieldEl.classList.add('htl-sb-error');
      fieldEl.addEventListener('animationend', () => fieldEl.classList.remove('htl-sb-error'), { once: true });
    }

    form.addEventListener('submit', function (e) {
      const destInput = document.getElementById('htlDestinationSearch');
      const checkInIso = document.getElementById('htlCheckInIso');
      const checkOutIso = document.getElementById('htlCheckOutIso');

      let firstInvalid = null;
      const missing = [];

      if (!destInput || !destInput.value.trim()) {
        flagError(destInput ? destInput.closest('.htl-sb-field') : null);
        firstInvalid = firstInvalid || destInput;
        missing.push('where you\'d like to go');
      }
      if (!checkInIso || !checkInIso.value) {
        flagError(document.getElementById('htlCheckInField'));
        firstInvalid = firstInvalid || document.getElementById('htlCheckIn');
        missing.push('a check-in date');
      }
      if (!checkOutIso || !checkOutIso.value) {
        flagError(document.getElementById('htlCheckOutField'));
        firstInvalid = firstInvalid || document.getElementById('htlCheckOut');
        missing.push('a check-out date');
      }

      if (firstInvalid) {
        e.preventDefault();
        firstInvalid.focus();
        if (typeof showToast === 'function') {
          const list = missing.length > 1
            ? missing.slice(0, -1).join(', ') + ' and ' + missing[missing.length - 1]
            : missing[0];
          showToast('Almost there', `Let us know ${list} so we can find the best stays for you.`, 'error');
        }
      }
    });
  })();

  /* ===== QUICK FILTERS (live, no page reload) ===== */
  const cards = document.querySelectorAll('.htl-card');
  const destinationSearch = document.getElementById('htlDestinationSearch');
  
  // Depending on layout, we either have a select or radio buttons
  const ratingSelect = document.querySelector('select[name="min_rating"]');
  const ratingRadios = document.querySelectorAll('input[name="sidebar_rating"]');
  const hiddenMinRating = document.getElementById('hiddenMinRating');

  // New filters
  const freeCancelCheckbox = document.getElementById('sidebar_free_cancellation');
  const priceSlider = document.getElementById('filterPriceSlider');
  const priceRadios = document.querySelectorAll('input[name="sidebar_price"]');
  const priceLabel = document.getElementById('filterPriceLabel');
  const mealCheckboxes = document.querySelectorAll('input[name="sidebar_meal"]');

  // Sync price radios with slider
  priceRadios.forEach(radio => {
    radio.addEventListener('change', (e) => {
      if (priceSlider) {
        priceSlider.value = e.target.value;
        priceLabel.textContent = e.target.value == 250000 ? '₹250,000+' : 'Up to ₹' + parseInt(e.target.value).toLocaleString();
        applyHotelFilters();
      }
    });
  });

  if (priceSlider) {
    priceSlider.addEventListener('input', (e) => {
      priceLabel.textContent = e.target.value == 250000 ? '₹250,000+' : 'Up to ₹' + parseInt(e.target.value).toLocaleString();
      // Uncheck radios when slider moves manually
      const matchingRadio = document.querySelector(`input[name="sidebar_price"][value="${e.target.value}"]`);
      if (matchingRadio) matchingRadio.checked = true;
      else {
        const anyChecked = document.querySelector('input[name="sidebar_price"]:checked');
        if (anyChecked) anyChecked.checked = false;
      }
      applyHotelFilters();
    });
  }

  function getMinRating() {
    if (ratingSelect) return parseInt(ratingSelect.value, 10) || 0;
    const checkedRadio = document.querySelector('input[name="sidebar_rating"]:checked');
    if (checkedRadio) return parseInt(checkedRadio.value, 10) || 0;
    return 0;
  }

  function applyHotelFilters() {
    const search = destinationSearch ? destinationSearch.value.trim().toLowerCase() : '';
    const minRating = getMinRating();
    const requireFreeCancel = freeCancelCheckbox ? freeCancelCheckbox.checked : false;
    const maxPrice = priceSlider ? parseInt(priceSlider.value, 10) : 250000;
    const selectedMeals = Array.from(mealCheckboxes).filter(cb => cb.checked).map(cb => cb.value);

    let delay = 0;

    // Sync hidden input for form submission
    if (hiddenMinRating) {
      hiddenMinRating.value = minRating;
    }

    cards.forEach(card => {
      const searchText = [
        card.dataset.location,
        card.dataset.name,
        card.dataset.category,
        card.dataset.amenities,
      ].join(' ').toLowerCase();
      
      const textMatch = !search || searchText.includes(search);
      const ratingMatch = minRating === 0 || parseInt(card.dataset.rating || 0, 10) === minRating;
      
      const cardPrice = parseInt(card.dataset.price || 0, 10);
      // Hide 'Price on request' (cardPrice === 0) if a specific price filter is applied
      const priceMatch = maxPrice === 250000 || (cardPrice > 0 && cardPrice <= maxPrice);

      const cancelMatch = !requireFreeCancel || card.dataset.cancellation === 'true';

      const cardMeal = card.dataset.meal || '';
      const mealMatch = selectedMeals.length === 0 || selectedMeals.some(m => cardMeal.includes(m));

      if (textMatch && ratingMatch && priceMatch && cancelMatch && mealMatch) {
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

  /* ===== SEARCH FORM VALIDATION ===== */
  const htlSearchForm = document.getElementById('htlSearchForm');
  if (htlSearchForm && destinationSearch) {
    htlSearchForm.addEventListener('submit', (e) => {
      const value = destinationSearch.value.trim();
      const validOptions = Array.from(document.querySelectorAll('#htlDestinationList option')).map(o => o.value);
      const isValid = value !== '' && validOptions.includes(value);

      if (!isValid) {
        e.preventDefault();
        showToast('Search Error', 'Please pick a valid city/hotel.', 'error');
        destinationSearch.focus();
      }
    });
  }

  if (destinationSearch) {
    destinationSearch.addEventListener('input', applyHotelFilters);
  }
  if (ratingSelect) {
    ratingSelect.addEventListener('change', applyHotelFilters);
  }
  ratingRadios.forEach(radio => {
    radio.addEventListener('change', applyHotelFilters);
  });
  if (freeCancelCheckbox) freeCancelCheckbox.addEventListener('change', applyHotelFilters);
  mealCheckboxes.forEach(cb => cb.addEventListener('change', applyHotelFilters));

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

function truncateAmenityChips() {
    document.querySelectorAll('.htl-list-amenities').forEach(function (container) {
      var chips = Array.prototype.slice.call(container.querySelectorAll('.htl-amenity-chip'));
      var moreBadge = container.querySelector('.htl-amenity-more');
      if (!chips.length) return;

      chips.forEach(function (chip) { chip.style.display = ''; });
      if (moreBadge) moreBadge.style.display = 'none';

      var containerWidth = container.clientWidth;
      var reserved = 80; // space kept for the "+N more" badge
      var gap = 6;
      var used = 0;
      var hiddenCount = parseInt(container.getAttribute('data-extra-count'), 10) || 0;

      chips.forEach(function (chip, index) {
        var chipWidth = chip.offsetWidth + (index > 0 ? gap : 0);
        var budget = containerWidth - reserved;
        if (index > 0 && used + chipWidth > budget) {
          chip.style.display = 'none';
          hiddenCount++;
        } else {
          used += chipWidth;
        }
      });

      if (hiddenCount > 0 && moreBadge) {
        moreBadge.textContent = '+' + hiddenCount + ' more';
        moreBadge.style.display = 'inline-flex';
      }
    });
  }
  window.addEventListener('load', truncateAmenityChips);
  window.addEventListener('resize', truncateAmenityChips);

})();
</script>
@endpush
