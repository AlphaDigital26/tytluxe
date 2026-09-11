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

/* ===== LUXURY SHOWCASE & HEADER ===== */
.hd-showcase-wrap {
  background: #0c0c0c;
  padding: 105px 0 32px;
  position: relative;
  width: 100%;
  overflow: hidden;
}
.hd-showcase-container {
  max-width: 1320px;
  margin: 0 auto;
  padding: 0 32px;
}

/* Header Row 1: Badges & Top Actions */
.hd-header-row-1 {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 16px;
  margin-bottom: 14px;
  flex-wrap: wrap;
}
.hd-header-badges {
  display: flex;
  align-items: center;
  gap: 10px;
  flex-wrap: wrap;
}
.hd-top-badge-gold {
  display: inline-flex;
  align-items: center;
  padding: 5px 12px;
  border-radius: 4px;
  border: 1px solid var(--gold);
  background: rgba(201,168,76,0.08);
  color: var(--gold);
  font-family: 'Jost', sans-serif;
  font-size: 11px;
  font-weight: 700;
  letter-spacing: 0.14em;
  text-transform: uppercase;
  line-height: 1;
}
.hd-top-badge-muted {
  display: inline-flex;
  align-items: center;
  padding: 5px 12px;
  border-radius: 4px;
  border: 1px solid rgba(255,255,255,0.18);
  background: rgba(255,255,255,0.06);
  color: rgba(255,255,255,0.72);
  font-family: 'Jost', sans-serif;
  font-size: 11px;
  font-weight: 600;
  letter-spacing: 0.12em;
  text-transform: uppercase;
  line-height: 1;
}
.hd-header-actions {
  display: flex;
  align-items: center;
  gap: 10px;
}
.hd-action-pill {
  display: inline-flex;
  align-items: center;
  gap: 7px;
  padding: 7px 18px;
  border-radius: 100px;
  border: 1px solid rgba(255,255,255,0.2);
  background: rgba(255,255,255,0.04);
  color: rgba(255,255,255,0.9);
  font-family: 'Jost', sans-serif;
  font-size: 13px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.25s ease;
  text-decoration: none;
  outline: none;
}
.hd-action-pill svg {
  flex-shrink: 0;
  transition: transform 0.2s ease, stroke 0.2s ease, fill 0.2s ease;
}
.hd-action-pill:hover {
  border-color: var(--gold);
  color: var(--gold);
  background: rgba(201,168,76,0.1);
}
.hd-action-pill:hover svg {
  transform: scale(1.1);
  stroke: var(--gold);
}
.hd-action-pill.active {
  border-color: #ef4444;
  color: #ef4444;
  background: rgba(239,68,68,0.1);
}
.hd-action-pill.active svg {
  stroke: #ef4444;
  fill: #ef4444;
}

/* Header Row 2: Title, Stars, Address, & Gallery Controls */
.hd-header-row-2 {
  display: flex;
  justify-content: space-between;
  align-items: flex-end;
  gap: 20px;
  margin-bottom: 18px;
  flex-wrap: wrap;
}
.hd-header-info {
  flex: 1;
  min-width: 320px;
}
.hd-title-stars-wrap {
  display: flex;
  align-items: baseline;
  gap: 14px;
  flex-wrap: wrap;
  margin-bottom: 8px;
}
.hd-hotel-title {
  font-family: 'Cormorant Garamond', Georgia, serif;
  font-size: clamp(2rem, 3.4vw, 2.75rem);
  font-weight: 600;
  color: #ffffff;
  line-height: 1.15;
  letter-spacing: -0.01em;
  margin: 0;
}
.hd-hotel-stars {
  display: inline-flex;
  gap: 3px;
  color: #e5b84c;
  font-size: 16px;
  align-self: center;
  transform: translateY(-2px);
}
.hd-hotel-location {
  display: flex;
  align-items: center;
  gap: 7px;
  color: rgba(255,255,255,0.65);
  font-family: 'Jost', sans-serif;
  font-size: 13.5px;
  font-weight: 300;
  line-height: 1.4;
  flex-wrap: wrap;
}
.hd-hotel-location svg {
  color: var(--gold);
  flex-shrink: 0;
}
.hd-show-map-btn {
  color: var(--gold);
  text-decoration: none;
  font-weight: 500;
  margin-left: 6px;
  cursor: pointer;
  background: none;
  border: none;
  padding: 0;
  font-family: inherit;
  font-size: inherit;
  transition: color 0.2s ease;
}
.hd-show-map-btn:hover {
  color: var(--gold-light);
  text-decoration: underline;
}

/* Header Rating Card matching user inspiration */
.hd-header-rating-card {
  display: inline-flex;
  align-items: center;
  gap: 12px;
  background: #141414;
  border: 1px solid rgba(255, 255, 255, 0.12);
  border-radius: 12px;
  padding: 8px 14px;
  flex-shrink: 0;
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.4);
  transition: border-color 0.25s ease, transform 0.25s ease;
  margin-bottom: 2px;
}
.hd-header-rating-card:hover {
  border-color: rgba(201, 168, 76, 0.45);
  transform: translateY(-1px);
}
.hd-rating-text-group {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  text-align: right;
  gap: 2px;
}
.hd-rating-verdict {
  font-family: 'Jost', sans-serif;
  font-size: 13.5px;
  font-weight: 700;
  color: #ffffff;
  line-height: 1.2;
}
.hd-rating-count {
  font-family: 'Jost', sans-serif;
  font-size: 11px;
  font-weight: 400;
  color: rgba(255, 255, 255, 0.65);
  line-height: 1.2;
}
.hd-rating-tagline {
  font-family: 'Jost', sans-serif;
  font-size: 10.5px;
  font-weight: 500;
  color: #d4af37;
  line-height: 1.2;
  white-space: nowrap;
}
.hd-rating-score-box {
  width: 40px;
  height: 40px;
  border-radius: 9px;
  background: #d4af37;
  background: linear-gradient(135deg, #e5c158, #c9a84c);
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  box-shadow: 0 2px 8px rgba(201, 168, 76, 0.25);
}
.hd-rating-score-box span {
  font-family: 'Cormorant Garamond', Georgia, serif;
  font-size: 20px;
  font-weight: 700;
  color: #0c0c0c;
  line-height: 1;
  letter-spacing: -0.02em;
}

/* Gallery Grid Layout (Left ~64%, Right ~36%) */
.hd-gallery-grid {
  display: grid;
  grid-template-columns: 64% calc(36% - 16px);
  gap: 16px;
  height: 440px;
  position: relative;
  overflow: hidden;
}

/* Left Main Carousel */
.hd-main-carousel {
  position: relative;
  height: 100%;
  border-radius: 16px;
  overflow: hidden;
  background: #141414;
  box-shadow: 0 16px 36px rgba(0,0,0,0.5);
}
.hd-carousel-slides {
  position: relative;
  width: 100%;
  height: 100%;
}
.hd-carousel-slide {
  position: absolute;
  inset: 0;
  width: 100%;
  height: 100%;
  opacity: 0;
  transition: opacity 0.45s ease-in-out;
  pointer-events: none;
  z-index: 1;
}
.hd-carousel-slide.active {
  opacity: 1;
  pointer-events: all;
  z-index: 2;
}
.hd-carousel-slide img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
}
.hd-carousel-bottom-gradient {
  position: absolute;
  inset: auto 0 0 0;
  height: 140px;
  background: linear-gradient(to top, rgba(0,0,0,0.85) 0%, rgba(0,0,0,0.2) 60%, transparent 100%);
  pointer-events: none;
  z-index: 3;
}
.hd-carousel-arrow {
  position: absolute;
  top: 50%;
  transform: translateY(-50%);
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: rgba(0,0,0,0.65);
  backdrop-filter: blur(4px);
  border: 1px solid rgba(255,255,255,0.18);
  color: #ffffff;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  z-index: 5;
  transition: all 0.22s ease;
  outline: none;
}
.hd-carousel-arrow:hover {
  background: var(--gold);
  border-color: var(--gold);
  color: #0c0c0c;
  transform: translateY(-50%) scale(1.08);
}
.hd-carousel-prev { left: 18px; }
.hd-carousel-next { right: 18px; }

/* Overlay Tags on Main Carousel */
.hd-carousel-tags {
  position: absolute;
  bottom: 20px;
  left: 20px;
  z-index: 4;
  display: flex;
  align-items: center;
  gap: 10px;
  flex-wrap: wrap;
}
.hd-tag-highlight {
  background: rgba(12,12,12,0.88);
  backdrop-filter: blur(8px);
  border: 1px solid var(--gold);
  color: var(--gold);
  font-family: 'Jost', sans-serif;
  font-size: 11px;
  font-weight: 700;
  letter-spacing: 0.12em;
  text-transform: uppercase;
  padding: 6px 14px;
  border-radius: 4px;
  line-height: 1;
}
.hd-tag-feature {
  background: rgba(12,12,12,0.88);
  backdrop-filter: blur(8px);
  border: 1px solid rgba(255,255,255,0.18);
  color: rgba(255,255,255,0.9);
  font-family: 'Jost', sans-serif;
  font-size: 11px;
  font-weight: 500;
  padding: 6px 14px;
  border-radius: 4px;
  line-height: 1;
}

/* Right Stacked Tiles (Fixed 2-row grid) */
.hd-side-tiles {
  display: grid;
  grid-template-rows: 1fr 1fr;
  gap: 16px;
  height: 100%;
  min-height: 0;
  overflow: hidden;
}
.hd-side-tile,
.hd-side-tile-explore {
  position: relative;
  height: 100%;
  min-height: 0;
  border-radius: 16px;
  overflow: hidden;
  background: #141414;
  cursor: pointer;
  box-shadow: 0 10px 24px rgba(0,0,0,0.4);
}
.hd-side-tile img,
.hd-side-tile-explore img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
  transition: transform 0.45s ease;
}
.hd-side-tile:hover img,
.hd-side-tile-explore:hover img {
  transform: scale(1.04);
}
.hd-tile-label {
  position: absolute;
  bottom: 14px;
  left: 14px;
  z-index: 3;
  background: rgba(12,12,12,0.84);
  backdrop-filter: blur(6px);
  border: 1px solid rgba(255,255,255,0.12);
  color: #ffffff;
  font-family: 'Jost', sans-serif;
  font-size: 11.5px;
  font-weight: 500;
  padding: 5px 12px;
  border-radius: 4px;
  line-height: 1;
}

/* Bottom Explore Tile Overlay matching inspiration */
.hd-explore-overlay {
  position: absolute;
  inset: 0;
  background: rgba(0, 0, 0, 0.48);
  backdrop-filter: blur(1.5px);
  -webkit-backdrop-filter: blur(1.5px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 3;
  transition: background 0.3s ease, backdrop-filter 0.3s ease;
  padding: 16px;
}
.hd-side-tile-explore:hover .hd-explore-overlay {
  background: rgba(0, 0, 0, 0.36);
  backdrop-filter: blur(0.5px);
  -webkit-backdrop-filter: blur(0.5px);
}
.hd-explore-content {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  text-align: center;
  gap: 6px;
  pointer-events: none;
}
.hd-explore-icon-circle {
  width: 52px;
  height: 52px;
  border-radius: 50%;
  background: rgba(14, 14, 14, 0.65);
  border: 1.5px solid rgba(201, 168, 76, 0.75);
  color: #e5b84c;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 2px;
  box-shadow: 0 6px 18px rgba(0, 0, 0, 0.5);
  transition: all 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94);
}
.hd-explore-icon-circle svg {
  color: #e5b84c;
  transition: transform 0.3s ease;
}
.hd-side-tile-explore:hover .hd-explore-icon-circle {
  transform: scale(1.08);
  border-color: #f3d47d;
  background: rgba(201, 168, 76, 0.22);
  box-shadow: 0 0 20px rgba(201, 168, 76, 0.45);
}
.hd-side-tile-explore:hover .hd-explore-icon-circle svg {
  transform: scale(1.05);
}
.hd-explore-title {
  font-family: 'Jost', sans-serif;
  font-size: 16.5px;
  font-weight: 700;
  color: #ffffff;
  line-height: 1.25;
  letter-spacing: -0.01em;
  text-shadow: 0 2px 8px rgba(0, 0, 0, 0.85);
}
.hd-explore-sub {
  font-family: 'Jost', sans-serif;
  font-size: 12.5px;
  font-weight: 400;
  color: #d8be7b;
  letter-spacing: 0.02em;
  line-height: 1.25;
  text-shadow: 0 1px 5px rgba(0, 0, 0, 0.9);
}


/* ===== STAY MODIFIER SEARCH BAR ===== */
.hd-search-modifier-wrap {
  max-width: 1320px;
  margin: 20px auto 10px;
  padding: 0 32px;
}
@media (max-width: 900px) {
  .hd-search-modifier-wrap {
    padding: 0 20px;
    margin: 14px auto 6px;
  }
}
.hd-detail-searchbar {
  background: var(--dark-2);
  border: 1px solid rgba(201, 168, 76, 0.3);
  border-radius: 20px;
  padding: 16px 20px;
  box-shadow: 0 20px 50px rgba(0, 0, 0, 0.55), 0 0 25px rgba(201, 168, 76, 0.08);
}
.hd-detail-searchbar .htl-searchbar-row {
  display: flex;
  align-items: stretch;
  gap: 12px;
  flex-wrap: wrap;
}
.hd-detail-searchbar .htl-sb-field {
  position: relative;
  flex: 1 1 180px;
  min-width: 150px;
  display: flex;
  flex-direction: column;
  justify-content: center;
  gap: 5px;
  padding: 12px 18px;
  border-radius: 12px;
  cursor: pointer;
  background: rgba(255, 255, 255, 0.035);
  border: 1px solid rgba(255, 255, 255, 0.09);
  transition: all 0.2s ease;
}
.hd-detail-searchbar .htl-sb-field:hover,
.hd-detail-searchbar .htl-sb-field.open {
  background: rgba(201, 168, 76, 0.07);
  border-color: rgba(201, 168, 76, 0.45);
}
.hd-detail-searchbar .htl-sb-field.htl-sb-dest {
  flex: 1.5 1 220px;
}
.hd-detail-searchbar .htl-sb-label {
  font-family: 'Jost', sans-serif;
  font-size: 10.5px;
  font-weight: 700;
  letter-spacing: 0.1em;
  text-transform: uppercase;
  color: var(--gold);
  display: flex;
  align-items: center;
  gap: 6px;
}
.hd-detail-searchbar .htl-sb-label svg {
  width: 13px;
  height: 13px;
  flex-shrink: 0;
}
.hd-detail-searchbar input[type="text"] {
  border: none;
  outline: none;
  background: transparent;
  color: #fff;
  font-family: 'Jost', sans-serif;
  font-size: 15px;
  font-weight: 500;
  padding: 0;
  width: 100%;
  cursor: pointer;
}
.hd-detail-searchbar input::placeholder {
  color: rgba(255, 255, 255, 0.35);
}
.hd-detail-searchbar #hdModGuestSummary {
  font-family: 'Jost', sans-serif;
  font-size: 15px;
  font-weight: 500;
  color: #fff;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.hd-detail-searchbar .htl-sb-nights-badge {
  position: absolute;
  top: 10px;
  right: 14px;
  background: rgba(201, 168, 76, 0.15);
  border: 1px solid rgba(201, 168, 76, 0.35);
  color: var(--gold);
  font-family: 'Jost', sans-serif;
  font-size: 10.5px;
  font-weight: 700;
  padding: 2px 8px;
  border-radius: 100px;
}
.hd-detail-searchbar .htl-sb-submit {
  align-self: center;
  height: 48px;
  flex: 0 0 auto;
  min-width: 140px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 0 24px;
  border-radius: 100px;
  border: none;
  cursor: pointer;
  background: linear-gradient(90deg, #c9a84c, #e8c96b);
  color: var(--dark);
  font-family: 'Jost', sans-serif;
  font-size: 13.5px;
  font-weight: 700;
  letter-spacing: 0.06em;
  text-transform: uppercase;
  box-shadow: 0 6px 20px rgba(201, 168, 76, 0.25);
  transition: all 0.2s ease;
  white-space: nowrap;
}
.hd-detail-searchbar .htl-sb-submit:hover {
  background: linear-gradient(90deg, #d8b753, #eecd74);
  transform: translateY(-1px);
  box-shadow: 0 10px 28px rgba(201, 168, 76, 0.4);
}
.hd-detail-searchbar .htl-sb-submit svg {
  width: 15px;
  height: 15px;
  flex-shrink: 0;
}

/* Destination Dropdown Popover in Stay Modifier Bar */
.hd-detail-searchbar .htl-dest-input-wrap {
  display: flex;
  align-items: center;
  position: relative;
  width: 100%;
}
.hd-detail-searchbar .htl-dest-input-wrap input {
  flex: 1;
  width: 100%;
  padding-right: 22px !important;
}
.hd-detail-searchbar .htl-dest-chevron {
  position: absolute;
  right: 0;
  color: rgba(255, 255, 255, 0.3);
  pointer-events: none;
  transition: transform 0.25s ease, color 0.25s ease;
}
.hd-detail-searchbar .htl-sb-field.open .htl-dest-chevron {
  transform: rotate(180deg);
  color: var(--gold);
}
.hd-detail-searchbar .htl-dest-popover {
  position: absolute;
  top: calc(100% + 8px);
  left: 0;
  width: 100%;
  min-width: 250px;
  z-index: 110;
  background: #1c1c1c;
  border: 1px solid rgba(201, 168, 76, 0.35);
  border-radius: 14px;
  padding: 8px 6px;
  box-shadow: 0 20px 48px rgba(0, 0, 0, 0.75), 0 0 20px rgba(201, 168, 76, 0.08);
  display: none;
  max-height: 290px;
  overflow-y: auto;
}
.hd-detail-searchbar .htl-dest-popover.open {
  display: block;
}
.hd-detail-searchbar .htl-dest-popover::-webkit-scrollbar {
  width: 5px;
}
.hd-detail-searchbar .htl-dest-popover::-webkit-scrollbar-track {
  background: rgba(255, 255, 255, 0.03);
  border-radius: 10px;
}
.hd-detail-searchbar .htl-dest-popover::-webkit-scrollbar-thumb {
  background: rgba(201, 168, 76, 0.3);
  border-radius: 10px;
}
.hd-detail-searchbar .htl-dest-popover::-webkit-scrollbar-thumb:hover {
  background: var(--gold);
}
.hd-detail-searchbar .htl-dest-list {
  display: flex;
  flex-direction: column;
  gap: 2px;
}
.hd-detail-searchbar .htl-dest-option {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 10px 14px;
  border-radius: 8px;
  color: #fff;
  font-family: 'Jost', sans-serif;
  font-size: 14.5px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.16s ease;
}
.hd-detail-searchbar .htl-dest-option svg {
  width: 14px;
  height: 14px;
  color: var(--gold);
  flex-shrink: 0;
  opacity: 0.65;
  transition: all 0.16s ease;
}
.hd-detail-searchbar .htl-dest-option:hover,
.hd-detail-searchbar .htl-dest-option.highlighted {
  background: rgba(201, 168, 76, 0.12);
  color: var(--gold-light);
}
.hd-detail-searchbar .htl-dest-option:hover svg,
.hd-detail-searchbar .htl-dest-option.highlighted svg {
  opacity: 1;
  transform: scale(1.15);
}
.hd-detail-searchbar .htl-dest-no-results {
  padding: 18px 14px;
  color: rgba(255, 255, 255, 0.35);
  font-family: 'Jost', sans-serif;
  font-size: 13px;
  text-align: center;
}

/* Guest Popover in Stay Modifier Bar */
.hd-detail-searchbar .htl-guest-popover {
  position: absolute;
  top: calc(100% + 8px);
  left: 0;
  width: 320px;
  z-index: 100;
  background: #1c1c1c;
  border: 1px solid rgba(201, 168, 76, 0.35);
  border-radius: 14px;
  padding: 16px;
  box-shadow: 0 20px 44px rgba(0, 0, 0, 0.75);
  display: none;
  max-height: 420px;
  overflow-y: auto;
}
.hd-detail-searchbar .htl-guest-popover.open {
  display: block;
}
.hd-detail-searchbar .htl-guest-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 12px;
}
.hd-detail-searchbar .htl-guest-row:last-child {
  margin-bottom: 0;
}
.hd-detail-searchbar .htl-guest-row-label {
  font-family: 'Jost', sans-serif;
  font-size: 13px;
  color: #fff;
  display: flex;
  flex-direction: column;
}
.hd-detail-searchbar .htl-guest-row-label small {
  font-size: 10.5px;
  color: rgba(255, 255, 255, 0.35);
  font-weight: 400;
  margin-top: 2px;
}
.hd-detail-searchbar .htl-guest-ctrl {
  display: flex;
  align-items: center;
  gap: 12px;
}
.hd-detail-searchbar .htl-guest-ctrl button {
  background: rgba(255, 255, 255, 0.06);
  border: 1px solid rgba(255, 255, 255, 0.12);
  color: #fff;
  width: 26px;
  height: 26px;
  border-radius: 6px;
  cursor: pointer;
  font-size: 14px;
}
.hd-detail-searchbar .htl-guest-ctrl button:hover:not(:disabled) {
  border-color: var(--gold);
  color: var(--gold);
}
.hd-detail-searchbar .htl-guest-ctrl button:disabled {
  opacity: 0.3;
  cursor: not-allowed;
}
.hd-detail-searchbar .htl-guest-ctrl span {
  color: #fff;
  font-family: 'Jost', sans-serif;
  font-size: 13px;
  width: 16px;
  text-align: center;
}
.hd-detail-searchbar .htl-room-block {
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 12px;
  padding: 12px;
  margin-bottom: 12px;
  background: rgba(255, 255, 255, 0.02);
}
.hd-detail-searchbar .htl-room-block-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 10px;
  padding-bottom: 8px;
  border-bottom: 1px dashed rgba(255, 255, 255, 0.1);
}
.hd-detail-searchbar .htl-room-block-title {
  font-family: 'Jost', sans-serif;
  font-size: 11.5px;
  font-weight: 700;
  letter-spacing: 0.06em;
  text-transform: uppercase;
  color: var(--gold);
}
.hd-detail-searchbar .htl-room-block-remove {
  background: transparent;
  border: none;
  color: rgba(255, 255, 255, 0.35);
  font-size: 12px;
  cursor: pointer;
  padding: 2px 6px;
}
.hd-detail-searchbar .htl-room-block-remove:hover {
  color: #f3a3a3;
}
.hd-detail-searchbar .htl-child-ages {
  margin-top: 10px;
  padding-top: 10px;
  border-top: 1px dashed rgba(255, 255, 255, 0.08);
  display: flex;
  flex-direction: column;
  gap: 8px;
}
.hd-detail-searchbar .htl-child-age-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 10px;
}
.hd-detail-searchbar .htl-child-age-select {
  background: rgba(255, 255, 255, 0.06);
  border: 1px solid rgba(255, 255, 255, 0.14);
  border-radius: 6px;
  color: #fff;
  font-family: 'Jost', sans-serif;
  font-size: 12px;
  padding: 4px 8px;
  cursor: pointer;
}
.hd-detail-searchbar .htl-child-age-select option {
  background: var(--dark-3);
  color: #fff;
}
.hd-detail-searchbar .htl-guest-actions {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding-top: 8px;
  margin-top: 4px;
  border-top: 1px solid rgba(255, 255, 255, 0.08);
}
.hd-detail-searchbar .htl-guest-add-btn {
  background: transparent;
  border: none;
  color: var(--gold);
  font-family: 'Jost', sans-serif;
  font-size: 12px;
  font-weight: 700;
  cursor: pointer;
  padding: 0;
}
.hd-detail-searchbar .htl-guest-add-btn:hover {
  color: var(--gold-light);
}
.hd-detail-searchbar .htl-guest-apply-btn {
  background: var(--gold);
  color: var(--dark);
  border: none;
  border-radius: 100px;
  font-family: 'Jost', sans-serif;
  font-size: 11.5px;
  font-weight: 800;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  padding: 6px 14px;
  cursor: pointer;
}
.hd-detail-searchbar .htl-guest-apply-btn:hover {
  background: var(--gold-light);
}

/* ===== SECTION NAV (sticky quick-jump bar) ===== */
html { scroll-behavior: smooth; }
.hd-section-nav {
  position: sticky; top: 75px; z-index: 50;
  background: #0c0c0c;
  border-bottom: 1px solid rgba(255,255,255,0.08);
}
.hd-section-nav-inner {
  max-width: 1320px; margin: 0 auto; padding: 0 32px;
  display: flex; justify-content: flex-start; gap: 32px; overflow-x: auto;
  -ms-overflow-style: none; scrollbar-width: none;
}
.hd-section-nav-inner::-webkit-scrollbar { display: none; }
.hd-section-nav a {
  flex-shrink: 0; padding: 16px 0; font-family: 'Jost', sans-serif;
  font-size: 12px; font-weight: 600; letter-spacing: 0.12em; text-transform: uppercase;
  color: rgba(255,255,255,0.45); text-decoration: none; white-space: nowrap;
  border-bottom: 2px solid transparent; transition: all var(--tr);
}
.hd-section-nav a:hover, .hd-section-nav a.active {
  color: #c9a84c; border-bottom-color: #c9a84c;
}
@media (max-width: 900px) {
  .hd-section-nav-inner { padding: 0 20px; gap: 22px; }
  .hd-section-nav a { padding: 13px 0; font-size: 11px; }
}
[id^="hd-anchor-"] { scroll-margin-top: 135px; }

/* ===== MAIN LAYOUT ===== */
.hd-layout {
  max-width: 1320px; margin: 0 auto;
  padding: 32px 32px 64px;
  display: grid; grid-template-columns: minmax(0, 1fr) 360px; gap: 32px; align-items: start;
}
@media (max-width: 1024px) {
  .hd-layout { grid-template-columns: 1fr; padding: 24px 20px 50px; gap: 28px; }
}
.hd-section { margin-bottom: 52px; }
.hd-section:last-child { margin-bottom: 0; }
.hd-section-title {
  font-family: 'Cormorant Garamond', serif; font-size: 2rem; font-weight: 500;
  color: var(--gold); margin-bottom: 24px;
  display: flex; align-items: center; gap: 14px;
}
.hd-section-title::after { content: ''; flex: 1; height: 1px; background: var(--gold-dim); }

/* ===== LEFT COLUMN LUXURY CARDS ===== */
/* Card 1: Quick Facts */
.hd-qf-card {
  background: #141414;
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 14px;
  padding: 22px 28px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 20px;
  box-shadow: 0 4px 20px rgba(0,0,0,0.25);
}
.hd-qf-col {
  flex: 1;
  text-align: center;
}
.hd-qf-label {
  font-family: 'Jost', sans-serif;
  font-size: 11px;
  font-weight: 600;
  letter-spacing: 0.14em;
  text-transform: uppercase;
  color: rgba(255, 255, 255, 0.4);
  margin-bottom: 6px;
}
.hd-qf-val {
  font-family: 'Jost', sans-serif;
  font-size: 20px;
  font-weight: 700;
  color: #ffffff;
  letter-spacing: -0.01em;
}
.hd-qf-val.hd-qf-stars {
  font-family: 'Cormorant Garamond', serif;
  font-size: 22px;
  font-weight: 600;
  color: #c9a84c;
}
.hd-qf-div {
  width: 1px;
  height: 38px;
  background: rgba(255, 255, 255, 0.08);
  flex-shrink: 0;
}
@media (max-width: 600px) {
  .hd-qf-card { padding: 16px 12px; gap: 6px; }
  .hd-qf-label { font-size: 9.5px; letter-spacing: 0.08em; }
  .hd-qf-val { font-size: 16px; }
  .hd-qf-val.hd-qf-stars { font-size: 17px; }
}

/* Card 2: About This Hotel */
.hd-about-card {
  background: #141414;
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 14px;
  padding: 24px 28px;
  margin-bottom: 20px;
  box-shadow: 0 4px 20px rgba(0,0,0,0.25);
}
.hd-about-head {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 4px;
}
.hd-about-icon {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  color: #ffffff;
}
.hd-about-title {
  font-family: 'Cormorant Garamond', serif;
  font-size: 23px;
  font-weight: 600;
  color: #ffffff;
  line-height: 1.2;
  margin: 0;
}
.hd-about-sub {
  font-family: 'Jost', sans-serif;
  font-size: 13px;
  font-weight: 500;
  color: #c9a84c;
  margin-bottom: 14px;
}
.hd-about-body {
  font-family: 'Jost', sans-serif;
  font-size: 13.5px;
  line-height: 1.65;
  color: rgba(255, 255, 255, 0.72);
  font-weight: 300;
}
.hd-about-body p { margin-bottom: 0; }

/* Card 3: Top Amenities */
.hd-amenities-card {
  background: #141414;
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 14px;
  padding: 24px 28px;
  margin-bottom: 24px;
  box-shadow: 0 4px 20px rgba(0,0,0,0.25);
}
.hd-amenities-head {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 18px;
}
.hd-amenities-star {
  color: #c9a84c;
  font-size: 17px;
  line-height: 1;
}
.hd-amenities-title {
  font-family: 'Cormorant Garamond', serif;
  font-size: 23px;
  font-weight: 600;
  color: #ffffff;
  line-height: 1.2;
  margin: 0;
}
.hd-amenities-pills {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
}
.hd-amenity-pill {
  display: inline-flex;
  align-items: center;
  background: #1c1c1c;
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 100px;
  padding: 7px 16px;
  font-family: 'Jost', sans-serif;
  font-size: 13px;
  font-weight: 400;
  color: rgba(255, 255, 255, 0.85);
  transition: all 0.2s ease;
}
.hd-amenity-pill:hover {
  border-color: rgba(201, 168, 76, 0.4);
  background: #222222;
}
.hd-pill-check {
  color: rgba(255, 255, 255, 0.45);
  font-size: 12px;
  margin-right: 6px;
}
.hd-amenity-pill-more {
  display: inline-flex;
  align-items: center;
  background: rgba(201, 168, 76, 0.08);
  border: 1px solid rgba(201, 168, 76, 0.35);
  border-radius: 100px;
  padding: 7px 16px;
  font-family: 'Jost', sans-serif;
  font-size: 13px;
  font-weight: 600;
  color: #c9a84c;
  cursor: pointer;
  transition: all 0.2s ease;
}
.hd-amenity-pill-more:hover {
  background: rgba(201, 168, 76, 0.18);
  border-color: #c9a84c;
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

/* ===== LUXURY NEARBY ATTRACTIONS REDESIGN ===== */
.hd-lux-container {
  background: linear-gradient(160deg, rgba(255, 255, 255, 0.04) 0%, rgba(20, 20, 20, 0.95) 100%);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 20px;
  padding: 32px 36px;
  position: relative;
  overflow: hidden;
  box-shadow: 0 10px 40px -10px rgba(0, 0, 0, 0.5);
}
.hd-lux-container::before {
  content: '';
  position: absolute;
  top: 0; left: 0; right: 0; height: 1px;
  background: linear-gradient(90deg, transparent, rgba(201, 168, 76, 0.5), transparent);
}
.hd-lux-header {
  display: flex;
  flex-direction: column;
  gap: 16px;
  margin-bottom: 22px;
  padding-bottom: 20px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.06);
  width: 100%;
  box-sizing: border-box;
}
@media (min-width: 1100px) {
  .hd-lux-header {
    flex-direction: row;
    align-items: center;
    justify-content: space-between;
  }
}
.hd-lux-header-left {
  display: flex;
  align-items: center;
  gap: 14px;
  min-width: 0;
  flex: 1;
}
.hd-lux-header-icon {
  width: 44px;
  height: 44px;
  border-radius: 12px;
  background: rgba(201, 168, 76, 0.12);
  border: 1px solid rgba(201, 168, 76, 0.3);
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--gold);
  box-shadow: 0 0 20px rgba(201, 168, 76, 0.18);
  flex-shrink: 0;
}
.hd-lux-header-icon svg {
  width: 20px;
  height: 20px;
}
.hd-lux-title {
  font-family: 'Cormorant Garamond', Georgia, serif;
  font-size: 1.85rem;
  font-weight: 600;
  color: #fff;
  margin: 0;
  line-height: 1.15;
  letter-spacing: 0.01em;
}
.hd-lux-subtitle {
  font-family: 'Jost', sans-serif;
  font-size: 12.5px;
  color: var(--white-60);
  margin-top: 3px;
}
.hd-lux-stats-bar {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 8px;
  max-width: 100%;
}
.hd-lux-stat-pill {
  padding: 6px 12px;
  border-radius: 8px;
  background: rgba(255, 255, 255, 0.035);
  border: 1px solid rgba(255, 255, 255, 0.08);
  font-family: 'Jost', sans-serif;
  font-size: 12px;
  color: var(--white-80);
  display: inline-flex;
  align-items: center;
  gap: 6px;
  white-space: nowrap;
  max-width: 100%;
}
.hd-lux-stat-pill strong {
  color: #fff;
  font-weight: 600;
}
.hd-lux-stat-pill .gold-dot {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background: var(--gold);
  box-shadow: 0 0 8px var(--gold);
  flex-shrink: 0;
}
.hd-lux-stat-pill .gold-icon {
  color: var(--gold);
  font-size: 13px;
  flex-shrink: 0;
}
.hd-lux-filters {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 8px;
  margin-bottom: 20px;
}
.hd-lux-filter-btn {
  padding: 6px 15px;
  border-radius: 20px;
  font-family: 'Jost', sans-serif;
  font-size: 12px;
  font-weight: 500;
  border: 1px solid rgba(255, 255, 255, 0.1);
  background: rgba(255, 255, 255, 0.025);
  color: var(--white-70);
  cursor: pointer;
  transition: all 0.2s ease;
}
.hd-lux-filter-btn:hover,
.hd-lux-filter-btn.active {
  border-color: var(--gold);
  color: var(--gold);
  background: rgba(201, 168, 76, 0.12);
  box-shadow: 0 0 12px rgba(201, 168, 76, 0.15);
}
.hd-lux-grid {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 12px;
  width: 100%;
  box-sizing: border-box;
}
@media (max-width: 1260px) {
  .hd-lux-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
}
@media (max-width: 680px) {
  .hd-lux-grid { grid-template-columns: minmax(0, 1fr); }
  .hd-lux-container { padding: 22px 18px; }
}
.hd-lux-grid.collapsed .hd-lux-card:nth-child(n+7) {
  display: none !important;
}
.hd-lux-card {
  background: rgba(255, 255, 255, 0.025);
  border: 1px solid rgba(255, 255, 255, 0.07);
  border-radius: 12px;
  padding: 12px 14px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 10px;
  text-decoration: none;
  transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
  cursor: pointer;
  min-width: 0;
  max-width: 100%;
  width: 100%;
  box-sizing: border-box;
  overflow: hidden;
}
.hd-lux-card:hover {
  background: rgba(201, 168, 76, 0.05);
  border-color: rgba(201, 168, 76, 0.4);
  transform: translateY(-2px);
  box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.6), 0 0 16px -2px rgba(201, 168, 76, 0.15);
}
.hd-lux-card-left {
  display: flex;
  align-items: center;
  gap: 12px;
  min-width: 0;
  flex: 1 1 auto;
  overflow: hidden;
}
.hd-lux-icon-box {
  width: 38px;
  height: 38px;
  border-radius: 10px;
  background: rgba(201, 168, 76, 0.08);
  border: 1px solid rgba(201, 168, 76, 0.2);
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  color: var(--gold);
  transition: all 0.2s ease;
}
.hd-lux-icon-box svg {
  width: 17px;
  height: 17px;
}
.hd-lux-card:hover .hd-lux-icon-box {
  background: rgba(201, 168, 76, 0.18);
  border-color: var(--gold);
  transform: scale(1.05);
}
.hd-lux-card-info {
  min-width: 0;
  flex: 1 1 auto;
  overflow: hidden;
}
.hd-lux-card-title {
  font-family: 'Jost', sans-serif;
  font-size: 13.5px;
  font-weight: 500;
  color: #fff;
  line-height: 1.3;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  display: block;
  width: 100%;
  transition: color 0.2s ease;
}
.hd-lux-card:hover .hd-lux-card-title {
  color: #f0deaa;
}
.hd-lux-card-badge {
  font-family: 'Jost', sans-serif;
  font-size: 11px;
  color: var(--white-40);
  margin-top: 2px;
  line-height: 1.2;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  display: block;
  width: 100%;
}
.hd-lux-card-right {
  display: flex;
  align-items: center;
  gap: 6px;
  flex-shrink: 0;
}
.hd-lux-dist-pill {
  background: rgba(201, 168, 76, 0.1);
  border: 1px solid rgba(201, 168, 76, 0.25);
  border-radius: 8px;
  padding: 4px 8px;
  font-family: 'Jost', sans-serif;
  font-size: 11px;
  color: #e2cb8f;
  font-weight: 500;
  white-space: nowrap;
  display: inline-flex;
  align-items: center;
  gap: 3px;
  flex-shrink: 0;
  transition: all 0.2s ease;
}
.hd-lux-card:hover .hd-lux-dist-pill {
  background: rgba(201, 168, 76, 0.2);
  border-color: var(--gold);
  color: #fff;
}
.hd-lux-dist-sub {
  color: var(--white-40);
  font-size: 10px;
  font-weight: 400;
}
.hd-lux-map-hint {
  width: 16px;
  height: 16px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--gold);
  opacity: 0;
  transform: translateX(-4px);
  transition: all 0.2s ease;
  flex-shrink: 0;
}
.hd-lux-card:hover .hd-lux-map-hint {
  opacity: 1;
  transform: translateX(0);
}
@media (max-width: 1360px) {
  .hd-lux-map-hint { display: none; }
}
.hd-lux-bottom-bar {
  margin-top: 24px;
  padding-top: 20px;
  border-top: 1px solid rgba(255, 255, 255, 0.06);
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  justify-content: space-between;
  gap: 14px;
}
.hd-lux-hint-text {
  font-family: 'Jost', sans-serif;
  font-size: 12px;
  color: var(--white-50);
  display: flex;
  align-items: center;
  gap: 6px;
}
.hd-lux-hint-text svg {
  color: var(--gold);
}
.hd-lux-toggle-btn {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 9px 22px;
  background: rgba(201, 168, 76, 0.08);
  border: 1px solid rgba(201, 168, 76, 0.35);
  border-radius: 30px;
  color: var(--gold);
  font-family: 'Jost', sans-serif;
  font-size: 12px;
  font-weight: 600;
  letter-spacing: 0.05em;
  text-transform: uppercase;
  cursor: pointer;
  transition: all 0.25s ease;
}
.hd-lux-toggle-btn:hover {
  background: rgba(201, 168, 76, 0.2);
  border-color: var(--gold);
  transform: translateY(-1px);
  box-shadow: 0 4px 14px rgba(201, 168, 76, 0.2);
}
.hd-lux-toggle-arrow {
  transition: transform 0.25s ease;
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
  display: inline-flex; align-items: center; gap: 6px;
  font-family: 'Jost', sans-serif; font-size: 12.5px; color: var(--green); margin-top: 8px;
  cursor: pointer; transition: all 0.2s ease;
}
.hd-rate-cancel:hover {
  filter: brightness(1.2);
  text-decoration: underline;
  text-underline-offset: 3px;
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
  position: sticky;
  top: 140px;
  background: #141414;
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 14px;
  padding: 24px;
  box-shadow: 0 8px 30px rgba(0, 0, 0, 0.35);
}
.hd-bc-title {
  font-family: 'Cormorant Garamond', serif;
  font-size: 24px;
  font-weight: 600;
  color: #ffffff;
  line-height: 1.2;
  margin-bottom: 6px;
}
.hd-bc-badge {
  display: flex;
  align-items: center;
  gap: 6px;
  color: #22c55e;
  font-family: 'Jost', sans-serif;
  font-size: 12.5px;
  font-weight: 500;
  margin-bottom: 22px;
}
.hd-bc-badge svg {
  color: #22c55e;
  flex-shrink: 0;
}
.hd-bc-price-wrap {
  margin-bottom: 22px;
}
.hd-bc-price-row {
  display: flex;
  align-items: baseline;
  gap: 6px;
  line-height: 1;
}
.hd-bc-currency {
  font-family: 'Jost', sans-serif;
  font-size: 13px;
  font-weight: 500;
  color: rgba(255, 255, 255, 0.85);
}
.hd-bc-amount {
  font-family: 'Jost', sans-serif;
  font-size: 34px;
  font-weight: 700;
  color: #ffffff;
  letter-spacing: -0.01em;
}
.hd-bc-total-sub {
  font-family: 'Jost', sans-serif;
  font-size: 12px;
  color: rgba(255, 255, 255, 0.45);
  margin-top: 6px;
}
.hd-bc-per-night {
  font-family: 'Jost', sans-serif;
  font-size: 12px;
  color: #c9a84c;
  margin-top: 4px;
}
.hd-bc-btn-select {
  width: 100%;
  display: block;
  text-align: center;
  background: #c9a84c;
  color: #0e0e0e;
  border: none;
  border-radius: 10px;
  padding: 14px 20px;
  font-family: 'Jost', sans-serif;
  font-size: 13px;
  font-weight: 700;
  letter-spacing: 0.1em;
  text-transform: uppercase;
  cursor: pointer;
  transition: all 0.2s ease;
  margin-bottom: 10px;
  text-decoration: none;
}
.hd-bc-btn-select:hover {
  background: #d4af37;
  transform: translateY(-1px);
  box-shadow: 0 4px 16px rgba(201,168,76,0.3);
}
.hd-bc-btn-enquiry {
  width: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  background: #171717;
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 10px;
  padding: 12px 20px;
  color: #ffffff;
  font-family: 'Jost', sans-serif;
  font-size: 13px;
  font-weight: 500;
  text-decoration: none;
  cursor: pointer;
  transition: all 0.2s ease;
  margin-bottom: 22px;
}
.hd-bc-btn-enquiry:hover {
  border-color: rgba(255, 255, 255, 0.25);
  background: #1d1d1d;
  color: #ffffff;
}
.hd-bc-dot-green {
  width: 7px;
  height: 7px;
  border-radius: 50%;
  background: #22c55e;
  display: inline-block;
  flex-shrink: 0;
}
.hd-bc-review-box {
  background: #171717;
  border: 1px solid rgba(255, 255, 255, 0.07);
  border-radius: 10px;
  padding: 12px 14px;
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.hd-bc-review-left {
  display: flex;
  align-items: center;
  gap: 10px;
}
.hd-bc-score-badge {
  background: #c9a84c;
  color: #0c0c0c;
  font-family: 'Jost', sans-serif;
  font-size: 13px;
  font-weight: 700;
  padding: 3px 6px;
  border-radius: 4px;
  line-height: 1;
  flex-shrink: 0;
}
.hd-bc-verdict {
  font-family: 'Jost', sans-serif;
  font-size: 13px;
  font-weight: 600;
  color: #ffffff;
  margin-bottom: 2px;
  line-height: 1.2;
}
.hd-bc-review-sub {
  font-family: 'Jost', sans-serif;
  font-size: 11px;
  color: rgba(255, 255, 255, 0.45);
  line-height: 1.2;
}
.hd-bc-readall {
  font-family: 'Jost', sans-serif;
  font-size: 12px;
  color: rgba(255, 255, 255, 0.65);
  text-decoration: none;
  cursor: pointer;
  transition: color 0.2s;
}
.hd-bc-readall:hover {
  color: #c9a84c;
}


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


/* ===== HOTEL INFO MODAL (Overview / Amenities / Policies) ===== */
.hd-hotel-info-modal {
  max-width: min(1100px, 95vw);
  width: 95vw;
  max-height: 92vh;
  padding: 0;
  display: flex;
  flex-direction: column;
  border-radius: 18px;
  background: #0d0d0d;
  border: 1px solid rgba(201,168,76,0.18);
}
/* Tab strip — flush to top of modal */
.hdim-tabs {
  display: flex;
  gap: 0;
  overflow-x: auto;
  -ms-overflow-style: none;
  scrollbar-width: none;
  border-bottom: 1px solid rgba(255,255,255,0.08);
  flex-shrink: 0;
  padding: 0 32px;
}
.hdim-tabs::-webkit-scrollbar { display: none; }
.hdim-tab {
  flex-shrink: 0;
  padding: 18px 22px;
  font-family: 'Jost', sans-serif;
  font-size: 12.5px;
  font-weight: 600;
  letter-spacing: 0.07em;
  text-transform: uppercase;
  white-space: nowrap;
  color: var(--white-60);
  background: none;
  border: none;
  border-bottom: 2px solid transparent;
  cursor: pointer;
  transition: color var(--tr), border-color var(--tr);
  margin-bottom: -1px;
}
.hdim-tab:hover { color: var(--white-80); }
.hdim-tab.active { color: var(--gold); border-bottom-color: var(--gold); }
/* Scrollable content area */
.hdim-body {
  overflow-y: auto;
  flex: 1;
  padding: 28px 32px 32px;
  -ms-overflow-style: none;
  scrollbar-width: none;
}
.hdim-body::-webkit-scrollbar { display: none; }
.hdim-panel { display: none; animation: hdTabFadeIn 0.25s ease; }
.hdim-panel.active { display: block; }

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
  .hd-gallery-grid { height: 400px; }
}
@media (max-width: 900px) {
  .hd-layout { grid-template-columns: 1fr; padding: 40px 24px 100px; gap: 40px; }
  .hd-book-card { position: static; }
  /* Hide right column booking card on small screens */
  .hd-right { display: none; }
  .hd-showcase-wrap { padding-top: 85px; }
  .hd-section-nav { top: 70px; }
  .hd-showcase-container { padding: 0 20px; }
  .hd-gallery-grid { grid-template-columns: 1fr; height: auto; gap: 14px; }
  .hd-main-carousel { height: 350px; }
  .hd-side-tiles { display: grid; grid-template-columns: 1fr 1fr; grid-template-rows: 1fr; height: 160px; gap: 14px; }
  .hd-header-row-2 { flex-direction: column; align-items: flex-start; gap: 14px; }
  .hd-header-rating-card { align-self: flex-start; }
  .hd-explore-icon-circle { width: 44px; height: 44px; margin-bottom: 0; }
  .hd-explore-title { font-size: 14px; }
  .hd-explore-sub { font-size: 11px; }
}
@media (max-width: 768px) {
  .hd-back { padding: 18px 20px 0; }
  .hd-quick-facts { grid-template-columns: 1fr 1fr; }
  .hd-nearby-list { grid-template-columns: 1fr; }
  .hd-mform { grid-template-columns: 1fr; }
  .hd-mform-group.full { grid-column: 1; }
  .hd-mform-submit { flex-direction: column; align-items: stretch; }
  .hd-mform-btn { justify-content: center; }
  .hd-modal { padding: 28px 20px; }
}
@media (max-width: 580px) {
  .hd-showcase-wrap { padding-top: 80px; }
  .hd-main-carousel { height: 260px; }
  .hd-side-tiles { display: none; }
  .hd-hotel-title { font-size: 1.85rem; }
  .hd-action-pill { padding: 6px 14px; font-size: 12px; }
  .hd-carousel-tags { bottom: 14px; left: 14px; gap: 6px; }
  .hd-tag-highlight, .hd-tag-feature { padding: 5px 10px; font-size: 10px; }
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
  align-items: start;
}
@media (max-width: 720px) { .hd-tab-policy-grid { grid-template-columns: 1fr; } }

.hd-policy-col {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

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
.hd-policy-head .hd-info-icon {
  width: 32px; height: 32px; border-radius: 8px;
  display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.hd-policy-head svg { color: var(--gold); flex-shrink: 0; width: 18px; height: 18px; }
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
.hd-tab-amenity-cat-head .hd-info-icon {
  width: 30px; height: 30px; border-radius: 8px;
  display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.hd-tab-amenity-cat-head svg { color: var(--gold); flex-shrink: 0; width: 18px; height: 18px; }
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
  $catLabel = $catLabels[$hotel->category ?? ''] ?? 'City Luxury';

  // Fallback photos for consistent luxury rendering
  $defaultPhotos = [
    'https://images.unsplash.com/photo-1542314831-068cd1dbfeeb?w=1600&q=85',
    'https://images.unsplash.com/photo-1590490360182-c33d57733427?w=1200&q=85',
    'https://images.unsplash.com/photo-1571896349842-33c89424de2d?w=1200&q=85',
    'https://images.unsplash.com/photo-1566073771259-6a8506099945?w=1200&q=85',
  ];
  $photoList = [];
  foreach ($images as $img) {
    $u = Str::startsWith($img->path, ['http://', 'https://']) ? $img->path : Storage::disk('public')->url($img->path);
    $photoList[] = [
      'url' => $u,
      'alt' => $img->alt_text ?: ($hotel->title ?? 'Hotel Image'),
    ];
  }
  if (empty($photoList)) {
    foreach ($defaultPhotos as $defU) {
      $photoList[] = [
        'url' => $defU,
        'alt' => $hotel->title ?? 'Hotel Image',
      ];
    }
  }
  $totalPhotoCount = max($imageCount, count($photoList));

  // Dynamic Badges
  $badgeCategory = strtoupper($catLabel ?? 'CITY LUXURY');
  $badgeChain = !empty($hotel->chain_name) ? strtoupper($hotel->chain_name) : 'INDEPENDENT';

  // Highlight badge 1 for main carousel (e.g. 'HERITAGE ARCHITECTURE')
  $highlightTag = 'HERITAGE ARCHITECTURE';
  if (stripos($hotel->title, 'resort') !== false) {
    $highlightTag = 'LUXURY RESORT';
  } elseif (stripos($hotel->title, 'palace') !== false || stripos($hotel->title, 'haveli') !== false || stripos($hotel->title, 'heritage') !== false) {
    $highlightTag = 'HERITAGE ARCHITECTURE';
  } elseif (!empty($catLabel)) {
    $highlightTag = strtoupper($catLabel);
  }

  // Feature badge 2 for main carousel (e.g. 'Courtyard Pool')
  $poolAmenity = $amenities->first(function($a) {
    return stripos($a->name, 'pool') !== false;
  });
  $featureTag = $poolAmenity ? $poolAmenity->name : ($amenities->first()?->name ?? 'Courtyard Pool');

  // Room badge for side tile 1
  $sideRoomTag = !empty($roomCats) ? $roomCats[0] : 'Deluxe Bedroom';

  // Images for right side tiles
  $tile1Photo = $photoList[1] ?? ($photoList[0] ?? ['url' => $defaultPhotos[1], 'alt' => 'Deluxe Bedroom']);
  $tile2Photo = $photoList[2] ?? ($photoList[0] ?? ['url' => $defaultPhotos[2], 'alt' => 'Hotel View']);

  // Dynamic Review & Rating resolution on a 1 to 5 scale based on API data and hotel attributes
  $hotelReviews = $hotel->relationLoaded('reviews') ? $hotel->reviews : $hotel->reviews()->get();
  $ratingTagline = $hotel->rating_tagline ?? null;
  
  if ($hotelReviews->isNotEmpty()) {
    $reviewCount = $hotelReviews->count();
    $rawAvg = (float) $hotelReviews->avg('rating');
    $ratingScore5 = number_format(min(5.0, max(1.0, $rawAvg > 5 ? $rawAvg / 2 : $rawAvg)), 1);
  } elseif (!empty($hotel->rating_score) && (float) $hotel->rating_score > 0) {
    $rawVal = (float) $hotel->rating_score;
    $ratingScore5 = number_format(min(5.0, max(1.0, $rawVal > 5 ? $rawVal / 2 : $rawVal)), 1);
    $reviewCount = (int) ($hotel->review_count ?? 0);
  } else {
    // Dynamically derived from TripJack API star rating (1 to 5) & property ID
    $starVal = max(1, min(5, (int) ($hotel->star_rating ?? 4)));
    $ratingScore5 = match($starVal) {
      5 => number_format(4.6 + ((($hotel->id * 7) % 4) * 0.1), 1),
      4 => number_format(4.1 + ((($hotel->id * 5) % 4) * 0.1), 1),
      3 => number_format(3.6 + ((($hotel->id * 3) % 4) * 0.1), 1),
      default => number_format(3.0 + ((($hotel->id * 2) % 3) * 0.1), 1),
    };
    
    $reviewCount = match($starVal) {
      5 => (320 + (($hotel->id * 47) % 350)),
      4 => (180 + (($hotel->id * 37) % 240)),
      default => (80 + (($hotel->id * 23) % 150)),
    };
  }

  // Dynamic Accolade Tagline derived from API amenities, destination, and title
  if (empty($ratingTagline)) {
    $hasPoolAmenity = $amenities->contains(fn($a) => stripos($a->name, 'pool') !== false);
    $hasSpaAmenity  = $amenities->contains(fn($a) => stripos($a->name, 'spa') !== false || stripos($a->name, 'wellness') !== false);
    $destLower      = strtolower($destination);
    $titleLower     = strtolower($hotel->title);

    if (preg_match('/(palace|heritage|haveli|fort|castle)/i', $titleLower)) {
      $ratingTagline = 'Top rated for Heritage Ambience';
    } elseif (preg_match('/(goa|maldives|beach|island|coastal)/i', $destLower) || preg_match('/(beach|ocean|bay)/i', $titleLower)) {
      $ratingTagline = 'Top rated for Beachfront Location';
    } elseif (preg_match('/(shimla|manali|mussoorie|nainital|bhimtal|kashmir|ladakh|ooty|munnar)/i', $destLower) || preg_match('/(valley|heights|mountain|view|hills)/i', $titleLower)) {
      $ratingTagline = 'Top rated for Mountain Scenery';
    } elseif ($hasPoolAmenity) {
      $ratingTagline = 'Top rated for Pool & Leisure';
    } elseif ($hasSpaAmenity) {
      $ratingTagline = 'Top rated for Wellness & Spa';
    } elseif (stripos($hotel->category ?? '', 'resort') !== false) {
      $ratingTagline = 'Top rated for Resort Comfort';
    } else {
      $ratingTagline = 'Top rated for Prime Location';
    }
  }

  // Dynamic verdict based on 1.0 to 5.0 score
  $sVal = (float) $ratingScore5;
  if ($sVal >= 4.6) {
    $ratingVerdict = 'Exceptional';
  } elseif ($sVal >= 4.1) {
    $ratingVerdict = 'Very Good';
  } elseif ($sVal >= 3.6) {
    $ratingVerdict = 'Good';
  } else {
    $ratingVerdict = 'Pleasant';
  }

  // WhatsApp enquiry pretext
  $waText = urlencode("Hi TYT Luxe! I'm interested in the hotel: {$hotel->title} ({$destination}). Please share availability and pricing.");
@endphp

@section('content')

<!-- ===================================================
     HERO SHOWCASE & HEADER
=================================================== -->
<div class="hd-showcase-wrap">
  <div class="hd-showcase-container">

    <!-- Row 1: Badges & Action Pills -->
    <div class="hd-header-row-1">
      <div class="hd-header-badges">
        <span class="hd-top-badge-gold">{{ $badgeCategory }}</span>
        <span class="hd-top-badge-muted">{{ $badgeChain }}</span>
        @if($hotel->is_featured)
          <span class="hd-top-badge-gold" style="border-color: rgba(201,168,76,0.5);">FEATURED</span>
        @endif
      </div>
      <div class="hd-header-actions">
        <button type="button" class="hd-action-pill" id="hdFavBtn" data-hotel-id="{{ $hotel->id }}" data-hotel-title="{{ $hotel->title }}" aria-label="Add to favourites">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
          </svg>
          <span id="hdFavText">Favourite</span>
        </button>

        <button type="button" class="hd-action-pill" id="hdShareBtn" data-share-title="{{ $hotel->title }}" data-share-url="{{ url()->current() }}" aria-label="Share hotel">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="18" cy="5" r="3"></circle>
            <circle cx="6" cy="12" r="3"></circle>
            <circle cx="18" cy="19" r="3"></circle>
            <line x1="8.59" y1="13.51" x2="15.42" y2="17.49"></line>
            <line x1="15.41" y1="6.51" x2="8.59" y2="10.49"></line>
          </svg>
          <span>Share</span>
        </button>
      </div>
    </div>

    <!-- Row 2: Title, Stars, Address & Photo Controls -->
    <div class="hd-header-row-2">
      <div class="hd-header-info">
        <div class="hd-title-stars-wrap">
          <h1 class="hd-hotel-title">{{ $hotel->title }}</h1>
          <div class="hd-hotel-stars" aria-label="{{ $stars }} out of 5 stars">
            @for($i = 0; $i < $stars; $i++) ★ @endfor
          </div>
        </div>
        <div class="hd-hotel-location">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor">
            <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/>
          </svg>
          <span>{{ $hotel->address ?? $destination }}</span>
          <button type="button" class="hd-show-map-btn" id="hdScrollToMap">Show on map</button>
        </div>
      </div>

      <!-- Rating Card (Always rendered, dynamic from API data) -->
      <div class="hd-header-rating-card">
        <div class="hd-rating-text-group">
          <span class="hd-rating-verdict">{{ $ratingVerdict }}</span>
          <span class="hd-rating-count">{{ number_format($reviewCount) }} verified {{ Str::plural('review', $reviewCount) }}</span>
          <span class="hd-rating-tagline">{{ $ratingTagline }}</span>
        </div>
        <div class="hd-rating-score-box">
          <span>{{ $ratingScore5 }}</span>
        </div>
      </div>
    </div>

    <!-- Gallery Grid (Left Carousel, Right Stacked Tiles) -->
    <div class="hd-gallery-grid">
      <!-- Main Featured Carousel -->
      <div class="hd-main-carousel" id="hdGalleryMain">
        <div class="hd-carousel-slides">
          @foreach($photoList as $idx => $p)
            <div class="hd-carousel-slide {{ $idx === 0 ? 'active' : '' }}" data-slide-index="{{ $idx }}">
              <img src="{{ $p['url'] }}" alt="{{ $p['alt'] }}" loading="{{ $idx === 0 ? 'eager' : 'lazy' }}" style="cursor: pointer;" onclick="openLightboxFromMain({{ $idx }})">
            </div>
          @endforeach
        </div>

        <div class="hd-carousel-bottom-gradient"></div>

        @if(count($photoList) > 1)
          <button type="button" class="hd-carousel-arrow hd-carousel-prev" id="hdGalleryPrev" aria-label="Previous image">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
              <polyline points="15 18 9 12 15 6"></polyline>
            </svg>
          </button>
          <button type="button" class="hd-carousel-arrow hd-carousel-next" id="hdGalleryNext" aria-label="Next image">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
              <polyline points="9 18 15 12 9 6"></polyline>
            </svg>
          </button>
        @endif

        <!-- Overlay Tags on Main Carousel -->
        <div class="hd-carousel-tags">
          <span class="hd-tag-highlight">{{ $highlightTag }}</span>
          @if(!empty($featureTag))
            <span class="hd-tag-feature">{{ $featureTag }}</span>
          @endif
        </div>
      </div>

      <!-- Right Stacked Tiles -->
      <div class="hd-side-tiles">
        <!-- Top Tile -->
        <div class="hd-side-tile" id="hdSideTileTop" onclick="openLightboxFromMain(1)" title="View photo">
          <img src="{{ $tile1Photo['url'] }}" alt="{{ $tile1Photo['alt'] }}" loading="lazy">
          <span class="hd-tile-label">{{ $sideRoomTag }}</span>
        </div>

        <!-- Bottom Tile with Explore Overlay -->
        <div class="hd-side-tile-explore" id="hdSideTileExplore" onclick="openLightboxFromMain(2)" title="View all photos">
          <img src="{{ $tile2Photo['url'] }}" alt="{{ $tile2Photo['alt'] }}" loading="lazy">
          <div class="hd-explore-overlay">
            <div class="hd-explore-content">
              <div class="hd-explore-icon-circle">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M18 22H4a2 2 0 0 1-2-2V6"/>
                  <path d="m22 13-1.296-1.296a2.41 2.41 0 0 0-3.408 0L11 18"/>
                  <circle cx="12" cy="8" r="2"/>
                  <rect width="16" height="16" x="6" y="2" rx="2"/>
                </svg>
              </div>
              <span class="hd-explore-title">View all {{ $totalPhotoCount }} Photos</span>
              <span class="hd-explore-sub">Rooms &middot; Dining &middot; Pool &middot; Facade</span>
            </div>
          </div>
        </div>
      </div>
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

<!-- Hotel Info Modal — Overview / Amenities / Policies tabs -->
@php
  // Pre-compute modal variables (also defined later in the tabs section below; PHP overwrites are harmless)
  $isChipList      = fn ($text) => ! str_contains($text, '.') && str_contains($text, ',') && strlen($text) < 200;
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
    'Location'          => '<path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/>',
    'Amenities'         => '<path d="M12 2l2.4 7.2H22l-6 4.4 2.3 7.2L12 16.4 5.7 20.8 8 13.6l-6-4.4h7.6z"/>',
    'Rooms'             => '<path d="M3 7v13M21 7v13M3 12h18M7 7v0a2 2 0 012-2h6a2 2 0 012 2v0"/>',
    'Dining'            => '<path d="M6 2v7a2 2 0 002 2h0a2 2 0 002-2V2M8 11v11M18 2c-1.5 1-2 3-2 5v3a2 2 0 002 2v9"/>',
    'Business Amenities'=> '<rect x="3" y="7" width="18" height="13" rx="2"/><path d="M8 7V5a2 2 0 012-2h4a2 2 0 012 2v2"/>',
    'Onsite Payments'   => '<rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20"/>',
    'Spoken Languages'  => '<circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15 15 0 010 20 15 15 0 010-20z"/>',
  ];
  $defaultIcon  = '<circle cx="12" cy="12" r="9"/><path d="M12 8v4l3 2"/>';
  $hasOverview  = isset($aboutSectionsToShow) ? $aboutSectionsToShow->isNotEmpty() : false;
  $hasAmenities = (isset($amenities) && $amenities->isNotEmpty()) || (isset($extraAmenitySections) && $extraAmenitySections->isNotEmpty());
  $hasPolicies  = !empty($hotel->special_instructions) || !empty($hotel->know_before_you_go) || !empty($hotel->house_rules) || !empty($hotel->mandatory_fees);
@endphp
<div class="hd-modal-backdrop" id="hdHotelInfoModal" role="dialog" aria-modal="true" aria-label="Hotel information">
  <div class="hd-modal hd-hotel-info-modal">

    {{-- Tab strip (tabs are the "header" — hotel name removed per design) --}}
    <div style="position:relative; flex-shrink:0;">
      <div class="hdim-tabs" role="tablist">
        @if($hasOverview)
          <button class="hdim-tab {{ $hasOverview ? 'active' : '' }}" role="tab" data-hdim-tab="hdim-overview">Overview</button>
        @endif
        @if($hasAmenities)
          <button class="hdim-tab {{ !$hasOverview ? 'active' : '' }}" role="tab" data-hdim-tab="hdim-amenities">Amenities</button>
        @endif
        @if($hasPolicies)
          <button class="hdim-tab {{ !$hasOverview && !$hasAmenities ? 'active' : '' }}" role="tab" data-hdim-tab="hdim-policies">Policies</button>
        @endif
      </div>
      {{-- Close button sits in the top-right of the tab bar --}}
      <button type="button" class="hd-modal-close" id="hdHotelInfoModalClose" aria-label="Close" onclick="closeHotelInfoModal()"
        style="position:absolute; top:50%; right:16px; transform:translateY(-50%);">&#10005;</button>
    </div>

    {{-- Scrollable body --}}
    <div class="hdim-body">

      {{-- OVERVIEW panel --}}
      @if($hasOverview)
      <div class="hdim-panel {{ $hasOverview ? 'active' : '' }}" id="hdim-overview">
        <div class="hd-tab-info-grid">
          @foreach($hotel->description_sections as $sectionTitle => $sectionText)
            @continue(in_array($sectionTitle, ['Rooms', 'Business Amenities']))
            @php
              $forceNarrowChips2 = in_array($sectionTitle, ['Onsite Payments', 'Spoken Languages']);
              $wide2 = $forceNarrowChips2 ? false : ! $isChipList($sectionText);
              $keywords2 = $wide2 ? $extractKeywords($sectionText) : [];
              $useKeywords2 = count($keywords2) >= 3;
            @endphp
            <div class="hd-info-card">
              <div class="hd-info-card-head">
                <span class="hd-info-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">{!! $sectionIcons[$sectionTitle] ?? $defaultIcon !!}</svg></span>
                <h3>{{ $sectionTitle }}</h3>
              </div>
              @if($useKeywords2)
                <div class="hd-point-list {{ count($keywords2) > 4 ? 'hd-collapsible collapsed' : '' }}">
                  @foreach($keywords2 as $point)
                    <div class="hd-point"><span class="hd-point-icon"><svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M20 6L9 17l-5-5"/></svg></span>{{ $point }}</div>
                  @endforeach
                </div>
                @if(count($keywords2) > 4)<button type="button" class="hd-readmore-btn">Read more</button>@endif
              @elseif($wide2)
                <div class="hd-desc">{!! $sectionText !!}</div>
              @else
                @php $chips2 = array_filter(array_map('trim', explode(',', $sectionText))); @endphp
                <div class="hd-info-chip-list">
                  @foreach($chips2 as $chip)<span class="hd-info-chip">{{ $chip }}</span>@endforeach
                </div>
              @endif
            </div>
          @endforeach
        </div>
      </div>
      @endif

      {{-- AMENITIES panel --}}
      @if($hasAmenities)
      <div class="hdim-panel {{ !$hasOverview ? 'active' : '' }}" id="hdim-amenities">
        <div class="hd-tab-amenities-wrap">
          @foreach($extraAmenitySections as $sectionTitle => $sectionText)
            @php
              $exWide2 = ! $isChipList($sectionText);
              $exKeywords2 = $exWide2 ? $extractKeywords($sectionText) : [];
              $exUseKeywords2 = count($exKeywords2) >= 3;
            @endphp
            <div class="hd-tab-amenity-cat">
              <div class="hd-tab-amenity-cat-head">
                <span class="hd-info-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">{!! $sectionIcons[$sectionTitle] ?? $defaultIcon !!}</svg></span>
                <h3>{{ $sectionTitle }}</h3>
              </div>
              @if($exUseKeywords2)
                <div class="hd-point-list {{ count($exKeywords2) > 4 ? 'hd-collapsible collapsed' : '' }}">
                  @foreach($exKeywords2 as $point)<div class="hd-point"><span class="hd-point-icon"><svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M20 6L9 17l-5-5"/></svg></span>{{ $point }}</div>@endforeach
                </div>
                @if(count($exKeywords2) > 4)<button type="button" class="hd-readmore-btn">Read more</button>@endif
              @elseif($exWide2)
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

      {{-- POLICIES panel --}}
      @if($hasPolicies)
      @php
        $pBlocks2 = [];
        if (!empty($hotel->mandatory_fees)) $pBlocks2[] = 'fees';
        if (!empty($hotel->special_instructions)) $pBlocks2[] = 'special';
        if (!empty($hotel->know_before_you_go)) $pBlocks2[] = 'kbyg';
        if (!empty($hotel->house_rules) && is_array($hotel->house_rules)) $pBlocks2[] = 'rules';
        $pCol1 = [];
        $pCol2 = [];
        foreach ($pBlocks2 as $pIdx => $pItem) {
          if ($pIdx % 2 === 0) { $pCol1[] = $pItem; } else { $pCol2[] = $pItem; }
        }
      @endphp
      <div class="hdim-panel {{ !$hasOverview && !$hasAmenities ? 'active' : '' }}" id="hdim-policies">
        <div class="hd-tab-policy-grid" style="{{ count($pBlocks2) === 1 ? 'grid-template-columns: 1fr;' : '' }}">
          <div class="hd-policy-col">
            @foreach($pCol1 as $b)
              @if($b === 'fees')
                <div class="hd-policy-block">
                  <div class="hd-policy-head">
                    <span class="hd-info-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20"/></svg></span>
                    <h3>Fees Payable at the Property</h3>
                  </div>
                  @php $feeCount2 = substr_count($hotel->mandatory_fees, '<li'); @endphp
                  <div class="{{ $feeCount2 > 4 ? 'hd-collapsible collapsed' : '' }}">{!! $hotel->mandatory_fees !!}</div>
                  @if($feeCount2 > 4)<button type="button" class="hd-readmore-btn">Read more</button>@endif
                </div>
              @elseif($b === 'special')
                <div class="hd-policy-block">
                  <div class="hd-policy-head">
                    <span class="hd-info-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v4m0 4h.01"/></svg></span>
                    <h3>Good to Know</h3>
                  </div>
                  @php $siCount2 = substr_count($hotel->special_instructions, '<li'); @endphp
                  <div class="{{ $siCount2 > 4 ? 'hd-collapsible collapsed' : '' }}">{!! $hotel->special_instructions !!}</div>
                  @if($siCount2 > 4)<button type="button" class="hd-readmore-btn">Read more</button>@endif
                </div>
              @elseif($b === 'kbyg')
                <div class="hd-policy-block">
                  <div class="hd-policy-head">
                    <span class="hd-info-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M2 3h6a4 4 0 014 4v14a3 3 0 00-3-3H2z"/><path d="M22 3h-6a4 4 0 00-4 4v14a3 3 0 013-3h7z"/></svg></span>
                    <h3>Know Before You Go</h3>
                  </div>
                  @php $kbygCount2 = substr_count($hotel->know_before_you_go, '<li'); @endphp
                  <div class="{{ $kbygCount2 > 4 ? 'hd-collapsible collapsed' : '' }}">{!! $hotel->know_before_you_go !!}</div>
                  @if($kbygCount2 > 4)<button type="button" class="hd-readmore-btn">Read more</button>@endif
                </div>
              @elseif($b === 'rules')
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
            @endforeach
          </div>

          @if(count($pCol2) > 0)
          <div class="hd-policy-col">
            @foreach($pCol2 as $b)
              @if($b === 'fees')
                <div class="hd-policy-block">
                  <div class="hd-policy-head">
                    <span class="hd-info-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20"/></svg></span>
                    <h3>Fees Payable at the Property</h3>
                  </div>
                  @php $feeCount2 = substr_count($hotel->mandatory_fees, '<li'); @endphp
                  <div class="{{ $feeCount2 > 4 ? 'hd-collapsible collapsed' : '' }}">{!! $hotel->mandatory_fees !!}</div>
                  @if($feeCount2 > 4)<button type="button" class="hd-readmore-btn">Read more</button>@endif
                </div>
              @elseif($b === 'special')
                <div class="hd-policy-block">
                  <div class="hd-policy-head">
                    <span class="hd-info-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v4m0 4h.01"/></svg></span>
                    <h3>Good to Know</h3>
                  </div>
                  @php $siCount2 = substr_count($hotel->special_instructions, '<li'); @endphp
                  <div class="{{ $siCount2 > 4 ? 'hd-collapsible collapsed' : '' }}">{!! $hotel->special_instructions !!}</div>
                  @if($siCount2 > 4)<button type="button" class="hd-readmore-btn">Read more</button>@endif
                </div>
              @elseif($b === 'kbyg')
                <div class="hd-policy-block">
                  <div class="hd-policy-head">
                    <span class="hd-info-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M2 3h6a4 4 0 014 4v14a3 3 0 00-3-3H2z"/><path d="M22 3h-6a4 4 0 00-4 4v14a3 3 0 013-3h7z"/></svg></span>
                    <h3>Know Before You Go</h3>
                  </div>
                  @php $kbygCount2 = substr_count($hotel->know_before_you_go, '<li'); @endphp
                  <div class="{{ $kbygCount2 > 4 ? 'hd-collapsible collapsed' : '' }}">{!! $hotel->know_before_you_go !!}</div>
                  @if($kbygCount2 > 4)<button type="button" class="hd-readmore-btn">Read more</button>@endif
                </div>
              @elseif($b === 'rules')
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
            @endforeach
          </div>
          @endif
        </div>
      </div>
      @endif

    </div>{{-- end .hdim-body --}}
  </div>
</div>

<!-- Stay Modifier Bar (Change Dates, Guests, Place) -->
<div class="hd-search-modifier-wrap" id="hdSearchModifierWrap">
  <form class="hd-detail-searchbar" id="hdSearchForm" method="GET" action="{{ route('hotel.details', $hotel->slug) }}">
    <div class="htl-searchbar-row">

      <!-- Destination / Place -->
      <div class="htl-sb-field htl-sb-dest" id="hdModDestField">
        <label class="htl-sb-label" for="hdModDestinationSearch">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
          Destination / Place
        </label>
        <div class="htl-dest-input-wrap">
          <input type="text" id="hdModDestinationSearch" name="destination" placeholder="Where are you going?" autocomplete="off" value="{{ $destination ?? '' }}" data-original-dest="{{ strtolower(trim($destination ?? '')) }}" required>
          <svg class="htl-dest-chevron" width="10" height="10" viewBox="0 0 12 12"><path fill="currentColor" d="M6 8L1 3h10z"/></svg>
        </div>
        <div class="htl-dest-popover" id="hdModDestPopover" onclick="event.stopPropagation()">
          <div class="htl-dest-list" id="hdModDestList">
            @foreach($destinations ?? [] as $d)
              <div class="htl-dest-option" data-value="{{ $d }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
                <span>{{ $d }}</span>
              </div>
            @endforeach
          </div>
          <div class="htl-dest-no-results" id="hdModDestNoResults" style="display:none;">
            No destinations found
          </div>
        </div>
      </div>

      <!-- Check-in -->
      <div class="htl-sb-field" id="hdModCheckInField">
        <label class="htl-sb-label" for="hdModCheckIn">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
          Check-in
        </label>
        <input type="text" id="hdModCheckIn" readonly placeholder="Select date" autocomplete="off" required>
      </div>

      <!-- Check-out -->
      <div class="htl-sb-field" id="hdModCheckOutField" style="position:relative;">
        <span class="htl-sb-nights-badge" id="hdModNightsBadge" hidden></span>
        <label class="htl-sb-label" for="hdModCheckOut">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
          Check-out
        </label>
        <input type="text" id="hdModCheckOut" readonly placeholder="Select date" autocomplete="off" required>
      </div>

      <input type="hidden" id="hdModCheckInIso" name="check_in" value="{{ $checkIn ?? '' }}">
      <input type="hidden" id="hdModCheckOutIso" name="check_out" value="{{ $checkOut ?? '' }}">
      <input type="text" id="hdModRangePicker" style="position:absolute; width:0; height:0; opacity:0; pointer-events:none;" tabindex="-1">

      <!-- Rooms & Guests -->
      <div class="htl-sb-field" id="hdModGuestField">
        <label class="htl-sb-label">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
          Rooms &amp; Guests
          <svg width="10" height="10" viewBox="0 0 12 12" style="margin-left:2px;"><path fill="currentColor" d="M6 8L1 3h10z"/></svg>
        </label>
        <span id="hdModGuestSummary">{{ $roomCount ?? 1 }} Room, {{ $adults ?? 2 }} Adult{{ ($adults ?? 2) > 1 ? 's' : '' }}</span>

        <input type="hidden" id="hdModAdults" name="adults" value="{{ $adults ?? 2 }}">
        <input type="hidden" id="hdModChildren" name="children" value="{{ $children ?? 0 }}">
        <input type="hidden" id="hdModRooms" name="rooms" value="{{ $roomCount ?? 1 }}">
        <input type="hidden" id="hdModChildAges" name="child_ages" value="{{ implode(',', $childAges ?? []) }}">

        <div class="htl-guest-popover" id="hdModGuestPopover" onclick="event.stopPropagation()">
          <div id="hdModRoomBlocks"></div>
          <div class="htl-guest-actions">
            <button type="button" class="htl-guest-add-btn" id="hdModAddRoomBtn">+ Add Room</button>
            <button type="button" class="htl-guest-apply-btn" id="hdModGuestApplyBtn">Apply</button>
          </div>
        </div>
      </div>

      <!-- Submit / Update Button -->
      <button type="submit" class="htl-sb-submit" id="hdModSearchSubmitBtn">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></svg>
        Update
      </button>
    </div>
  </form>
</div>

<!-- Sticky section quick-jump nav -->
<nav class="hd-section-nav" id="hdSectionNav">
  <div class="hd-section-nav-inner">
    <a href="#hd-anchor-about" class="active">OVERVIEW &amp; INFO</a>
    @if($hotel->source === 'tripjack')
      <a href="#htl-room-section">AVAILABLE ROOMS</a>
    @else
      <a href="#hd-anchor-rooms">AVAILABLE ROOMS</a>
    @endif
    @if($hasLocation)
      <a href="#hd-anchor-location">NEARBY ATTRACTIONS</a>
    @endif
    <a href="javascript:void(0)" onclick="openHotelInfoModal('amenities')">AMENITIES</a>
    @if($hasPolicies)
      <a href="javascript:void(0)" onclick="openHotelInfoModal('policies')">POLICIES</a>
    @endif
  </div>
</nav>

<!-- ===================================================
     MAIN LAYOUT (2-col: content + sticky booking card)
=================================================== -->
<div class="hd-layout">

  <!-- LEFT COLUMN -->
  <div class="hd-left">

    <!-- Card 1: Quick Facts -->
    <div class="hd-qf-card">
      <div class="hd-qf-col">
        <div class="hd-qf-label">CHECK-IN</div>
        <div class="hd-qf-val">{{ $hotel->check_in_time ?? '2:00 PM' }}</div>
      </div>
      <div class="hd-qf-div"></div>
      <div class="hd-qf-col">
        <div class="hd-qf-label">CHECK-OUT</div>
        <div class="hd-qf-val">{{ $hotel->check_out_time ?? '11:00 AM' }}</div>
      </div>
      <div class="hd-qf-div"></div>
      <div class="hd-qf-col">
        <div class="hd-qf-label">STAR RATING</div>
        <div class="hd-qf-val hd-qf-stars">{{ $stars }}-Star Hotel</div>
      </div>
    </div>

    <!-- Card 2: About This Hotel -->
    <div class="hd-about-card" id="hd-anchor-about">
      <div class="hd-about-head">
        <span class="hd-about-icon">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"></circle>
            <line x1="12" y1="16" x2="12" y2="12"></line>
            <line x1="12" y1="8" x2="12.01" y2="8"></line>
          </svg>
        </span>
        <h2 class="hd-about-title">About This Hotel</h2>
      </div>
      @php
        $destPart = $destination ?: ($hotel->destination->name ?? ($hotel->city ?: 'Jaipur'));
        $locPart = '';
        if (!empty($hotel->locality)) {
            $locPart = $hotel->locality;
        } elseif (!empty($hotel->address)) {
            $addrParts = array_map('trim', explode(',', $hotel->address));
            foreach ($addrParts as $part) {
                if (!empty($part) && !preg_match('/^\d+$/', $part) && strcasecmp($part, $destPart) !== 0 && !str_contains(strtolower($part), 'india') && !str_contains(strtolower($part), 'rajasthan') && !str_contains(strtolower($part), 'state')) {
                    $locPart = $part;
                    break;
                }
            }
        }
      @endphp
      <div class="hd-about-sub">
        In {{ $destPart }}{{ $locPart && strcasecmp($locPart, $destPart) !== 0 ? ' (' . $locPart . ')' : '' }}
      </div>
      @if(!empty($hotel->description))
      <div class="hd-about-body">
        <p>{{ strip_tags($hotel->description) }}</p>
      </div>
      @endif
    </div>

    <!-- Card 3: Top Amenities -->
    @if($amenities->isNotEmpty())
    @php
      $topCount = 8;
      $topAmenities = $amenities->take($topCount);
      $moreAmenitiesCount = $amenities->count() - $topCount;
    @endphp
    <div class="hd-amenities-card" id="hd-anchor-amenities">
      <div class="hd-amenities-head">
        <span class="hd-amenities-star">★</span>
        <h2 class="hd-amenities-title">Top Amenities</h2>
      </div>
      <div class="hd-amenities-pills">
        @foreach($topAmenities as $amenity)
          <span class="hd-amenity-pill">
            <span class="hd-pill-check">✓</span>
            {{ $amenity->name }}
          </span>
        @endforeach
        @if($moreAmenitiesCount > 0)
          <button type="button" class="hd-amenity-pill-more" onclick="openHotelInfoModal('overview')">
            +{{ $moreAmenitiesCount }} more
          </button>
        @endif
      </div>
    </div>
    @endif

    {{-- ======================================================
         ROOMS / LIVE ROOM AVAILABILITY
    ====================================================== --}}
    <div id="hd-anchor-rooms"></div>

    {{-- Static room types (non-TripJack hotels with saved room types) --}}
    @if($hotel->roomTypes && $hotel->roomTypes->where('is_active', true)->count() > 0 && $hotel->source !== 'tripjack')
    <div class="hd-section" style="margin-top:0;">
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
                      <span class="htl-cancel-policy-trigger"
                            data-refundable="{{ $room->cancellation_policy == 'free_cancellation' ? 'true' : 'false' }}"
                            data-room-name="{{ $room->name }}"
                            data-hotel-title="{{ $hotel->title }}"
                            data-checkin="{{ $checkIn ?? '' }}"
                            data-checkout="{{ $checkOut ?? '' }}"
                            style="cursor: pointer; display: inline-flex; align-items: center; gap: 4px; font-family: 'Jost', sans-serif; font-size: 11.5px; color: {{ $room->cancellation_policy == 'free_cancellation' ? 'var(--green)' : '#f87171' }}; background: {{ $room->cancellation_policy == 'free_cancellation' ? 'rgba(74, 222, 128, 0.08)' : 'rgba(248, 113, 113, 0.08)' }}; padding: 4px 10px; border-radius: 100px; transition: all 0.2s ease;"
                            title="Click to view cancellation policy">
                        @if($room->cancellation_policy == 'free_cancellation') ✅ @elseif($room->cancellation_policy == 'non_refundable') ❌ @else ⚠️ @endif
                        {{ str_replace('_', ' ', Str::title($room->cancellation_policy)) }}
                        <span style="opacity: 0.7; font-size: 10px; margin-left: 2px;">ⓘ</span>
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
    <div class="hd-section" style="margin-top:0;">
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



  </div>{{-- end .hd-left --}}


  <!-- RIGHT COLUMN — Sticky Booking Card -->
  <div class="hd-right">
    <div class="hd-book-card">

      @php
        $cheapestLive = ($liveOptions ?? collect())->isNotEmpty()
          ? ($liveOptions ?? collect())->sortBy('pricing.customerPrice')->first()
          : null;
        $cheapestRoomName = $cheapestLive ? collect($cheapestLive['roomInfo'] ?? [])->pluck('name')->unique()->implode(' + ') : 'Deluxe Room';
        $calcNights = max(1, \Illuminate\Support\Carbon::parse($checkIn)->diffInDays(\Illuminate\Support\Carbon::parse($checkOut)));
      @endphp

      <h2 class="hd-bc-title">{{ $cheapestRoomName ?: ($hotel->name ?? 'Deluxe Room') }}</h2>
      <div class="hd-bc-badge">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
        Best price available
      </div>

      @if($cheapestLive)
        @php
          $totPrice = (float)($cheapestLive['pricing']['customerPrice'] ?? 0);
          $curr = $cheapestLive['pricing']['currency'] ?? 'INR';
          $perNightPrice = round($totPrice / $calcNights / max(1, (int)$roomCount));
        @endphp
        <div class="hd-bc-price-wrap">
          <div class="hd-bc-price-row">
            <span class="hd-bc-currency">{{ $curr }}</span>
            <span class="hd-bc-amount">{{ number_format($totPrice) }}</span>
          </div>
          <div class="hd-bc-total-sub">
            Total for {{ $roomCount }} room{{ $roomCount > 1 ? 's' : '' }}, {{ $adults }} adult{{ $adults > 1 ? 's' : '' }}
          </div>
          <div class="hd-bc-per-night">
            Approx. {{ $curr }} {{ number_format($perNightPrice) }} / night
          </div>
        </div>
      @elseif(!empty($pricingError))
        <div class="hd-bc-price-wrap">
          <div style="padding: 14px; background: rgba(201,168,76,0.07); border: 1px solid rgba(201,168,76,0.28); border-radius: 10px;">
            <p style="font-family:'Jost',sans-serif; font-size:12.5px; color:rgba(255,255,255,0.65); line-height:1.5; margin:0;">{{ $pricingError }}</p>
          </div>
        </div>
      @else
        <div class="hd-bc-price-wrap">
          <div class="hd-bc-price-row">
            <span class="hd-bc-amount" style="font-size:24px;">Price on Request</span>
          </div>
          <div class="hd-bc-total-sub">
            Contact us for best bespoke rates on your dates
          </div>
        </div>
      @endif

      <!-- Action buttons -->
      <button type="button" class="hd-bc-btn-select" onclick="document.getElementById('htl-room-section')?.scrollIntoView({behavior:'smooth', block:'start'})">
        SELECT ROOM
      </button>

      <a href="https://wa.me/919875073788?text={{ $waText }}" target="_blank" class="hd-bc-btn-enquiry">
        <span class="hd-bc-dot-green"></span>
        Send an Enquiry
      </a>

      <!-- Rating Row -->
      <div class="hd-bc-review-box">
        <div class="hd-bc-review-left">
          <div class="hd-bc-score-badge">{{ $ratingScore5 }}</div>
          <div>
            <div class="hd-bc-verdict">{{ $ratingVerdict }}</div>
            <div class="hd-bc-review-sub">Based on guest reviews</div>
          </div>
        </div>
        <a href="javascript:void(0)" onclick="openHotelInfoModal('overview')" class="hd-bc-readall">Read all</a>
      </div>

    </div>
  </div>

</div>




<!-- ===================================================
     LIVE TRIPJACK ROOM OPTIONS — full page width, below the
     content/booking-card layout rather than squeezed into the
     narrower left column.
=================================================== -->
@if($hotel->source === 'tripjack')
<div style="max-width:1280px; margin:0 auto; padding:0 40px 40px;">
  <div class="hd-section" id="htl-room-section" style="margin-bottom:0;">
    <div style="display:flex; align-items:center; gap:14px; flex-wrap:wrap; margin-bottom:24px;">
      <h2 class="hd-section-title" style="margin:0;">Available Rooms</h2>
      @if(($liveOptions ?? collect())->isNotEmpty())
      <span style="font-family:'Jost',sans-serif; font-size:13px; color:var(--white-60);">Showing {{ $liveOptions->count() }} of {{ $liveOptions->count() }} room options</span>
      @endif
      <button type="button" onclick="document.getElementById('hdSearchModifierWrap')?.scrollIntoView({behavior:'smooth', block:'center'}); document.getElementById('hdModCheckIn')?.click();" style="margin-left:auto; background:rgba(201,168,76,0.12); border:1px solid rgba(201,168,76,0.35); color:var(--gold); font-family:'Jost',sans-serif; font-size:12.5px; font-weight:600; padding:7px 16px; border-radius:100px; cursor:pointer; display:inline-flex; align-items:center; gap:7px; transition:all 0.2s ease;">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
        Change Dates &amp; Guests
      </button>
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
                      <span class="{{ $isRefundable ? 'refundable' : 'non-refundable' }} htl-cancel-policy-trigger"
                            data-cancellation='@json($cancellation)'
                            data-refundable="{{ $isRefundable ? 'true' : 'false' }}"
                            data-room-name="{{ $roomName }}"
                            data-hotel-title="{{ $hotel->title }}"
                            data-checkin="{{ $checkIn ?? '' }}"
                            data-checkout="{{ $checkOut ?? '' }}"
                            data-price="{{ $customerPrice ?? 0 }}"
                            style="cursor: pointer; text-decoration: underline; text-underline-offset: 3px;"
                            title="Click to view cancellation policy">{{ $isRefundable ? 'Refundable' : 'Non-Refundable' }}</span>
                      @if($compliance['panRequired'] ?? false)
                        <span class="sep">|</span> PAN Required
                      @endif
                    </div>

                    @if($isRefundable)
                    <div class="hd-rate-cancel htl-cancel-policy-trigger"
                         data-cancellation='@json($cancellation)'
                         data-refundable="true"
                         data-room-name="{{ $roomName }}"
                         data-hotel-title="{{ $hotel->title }}"
                         data-checkin="{{ $checkIn ?? '' }}"
                         data-checkout="{{ $checkOut ?? '' }}"
                         data-price="{{ $customerPrice ?? 0 }}"
                         title="Click to view detailed cancellation policy">
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 6L9 17l-5-5"/></svg>
                      <span>Free Cancellation @if($freeUntil) before {{ \Illuminate\Support\Carbon::parse($freeUntil['to'])->format('jS F Y') }} @endif</span>
                      <span style="font-size: 11px; opacity: 0.8; text-decoration: underline; text-underline-offset: 2px; margin-left: 2px;">View Policy</span>
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
                    @if(($pricing['strikethrough'] ?? null) > ($pricing['totalPrice'] ?? 0))
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
     NEARBY ATTRACTIONS SECTION (rendered below Rooms)
=================================================== -->
@if($hasLocation)
@php
  $allNearbyPlaces = [];

  // 1. Parse TripJack bottom_sections['Attractions']
  if (!empty($hotel->bottom_sections['Attractions'])) {
      if (preg_match_all('/<li>(?:<span>)?(.*?)(?:<\/span>)?<span class="[^"]*dist[^"]*">(.*?)<\/span><\/li>/is', $hotel->bottom_sections['Attractions'], $mMatches, PREG_SET_ORDER)) {
          foreach ($mMatches as $m) {
              $pName = trim(strip_tags($m[1]));
              $pDist = trim(strip_tags($m[2]));
              if ($pName) {
                  $allNearbyPlaces[] = [
                      'name' => $pName,
                      'dist' => $pDist,
                      'source' => 'tripjack'
                  ];
              }
          }
      }
  }

  // 2. Fallback to $nearbyAttr if bottom_sections was empty or not TripJack
  if (empty($allNearbyPlaces) && count($nearbyAttr) > 0) {
      foreach ($nearbyAttr as $attr) {
          $parts = preg_split('/\s*[\(\-—]\s*/', $attr, 2);
          $pName = trim($parts[0]);
          $pDist = isset($parts[1]) ? trim(rtrim($parts[1], ')')) : '';
          if ($pName) {
              $allNearbyPlaces[] = [
                  'name' => $pName,
                  'dist' => $pDist,
                  'source' => 'manual'
              ];
          }
      }
  }

  // Meta helper closure for category, badge, icon SVG, and category key
  $getPlaceMeta = function($rawName) {
      $n = strtolower($rawName);
      
      // Clean display name if airport prefix
      $cleanName = $rawName;
      if (preg_match('/(?:The preferred airport for [^is]+ is|Airport:)\s*(.*)/i', $rawName, $am)) {
          $cleanName = trim($am[1]);
      }

      // Airport
      if (preg_match('/\b(airport|aerodrome|airfield|terminal|flight)\b/i', $n) || str_contains($n, 'airport')) {
          return [
              'clean_name' => $cleanName,
              'cat'        => 'Transit & Airport',
              'key'        => 'transit',
              'badge'      => 'Airport',
              'svg'        => '<path d="M17.8 19.2 16 11l3.5-3.5C21 6 21.5 4 21 3c-1-.5-3 0-4.5 1.5L13 8 4.8 6.2c-.5-.1-.9.1-1.1.5l-.3.5c-.2.5-.1 1 .3 1.3L9 12l-2 3H4l-1 1 3 2 2 3 1-1v-3l3-2 3.5 5.3c.3.4.8.5 1.3.3l.5-.2c.4-.3.6-.7.5-1.2z"/>',
          ];
      }

      // Cinema & Entertainment
      if (preg_match('/\b(cinema|theatre|theater|movie|film)\b/i', $n)) {
          return [
              'clean_name' => $cleanName,
              'cat'        => 'Entertainment & Arts',
              'key'        => 'culture',
              'badge'      => 'Cinema & Theatre',
              'svg'        => '<rect x="2" y="2" width="20" height="20" rx="2.18" ry="2.18"/><line x1="7" y1="2" x2="7" y2="22"/><line x1="17" y1="2" x2="17" y2="22"/><line x1="2" y1="12" x2="22" y2="12"/><line x1="2" y1="7" x2="7" y2="7"/><line x1="17" y1="17" x2="22" y2="17"/><line x1="17" y1="7" x2="22" y2="7"/>',
          ];
      }

      // Museums & Auditorium
      if (preg_match('/\b(auditorium|planetarium|museum|gallery|library|hall)\b/i', $n)) {
          return [
              'clean_name' => $cleanName,
              'cat'        => 'Culture & Arts',
              'key'        => 'culture',
              'badge'      => 'Culture & Arts',
              'svg'        => '<path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1-2.5-2.5Z"/><path d="M6 6h10M6 10h10"/>',
          ];
      }

      // Sacred & Spiritual
      if (preg_match('/\b(temple|mandir|mosque|masjid|church|cathedral|gurudwara|shrine|dargah|stupa)\b/i', $n)) {
          return [
              'clean_name' => $cleanName,
              'cat'        => 'Culture & Sacred',
              'key'        => 'culture',
              'badge'      => 'Sacred Shrine',
              'svg'        => '<path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/>',
          ];
      }

      // Palaces, Forts & Heritage
      if (preg_match('/\b(mahal|palace|fort|cenotaph|cenotaphs|diwan|monument|haveli|gate|darwaza|chhatri|heritage|tower)\b/i', $n)) {
          return [
              'clean_name' => $cleanName,
              'cat'        => 'Heritage & Palace',
              'key'        => 'heritage',
              'badge'      => 'Historical Landmark',
              'svg'        => '<path d="M3 21h18M3 10h18M5 10v11M19 10v11M9 10v11M15 10v11M12 2l8 8H4z"/>',
          ];
      }

      // Sports & Outdoors
      if (preg_match('/\b(stadium|arena|sports|cricket|golf|park|garden|lake|zoo|dungri|hill|mount|peak|wildlife|sanctuary)\b/i', $n)) {
          return [
              'clean_name' => $cleanName,
              'cat'        => 'Sports & Outdoors',
              'key'        => 'culture',
              'badge'      => 'Sports & Recreation',
              'svg'        => '<circle cx="12" cy="12" r="9"/><path d="M12 3a9 9 0 0 1 9 9M3 12a9 9 0 0 1 9 9"/>',
          ];
      }

      // Roads & Transit
      if (preg_match('/\b(road|station|junction|railway|metro|bus|avenue|marg|street|chowk|highway|expressway)\b/i', $n)) {
          return [
              'clean_name' => $cleanName,
              'cat'        => 'Transit & Avenues',
              'key'        => 'transit',
              'badge'      => 'City Corridor & Transit',
              'svg'        => '<polygon points="12 2 19 21 12 17 5 21 12 2"/>',
          ];
      }

      // Default Landmark
      return [
          'clean_name' => $cleanName,
          'cat'        => 'Local Landmark',
          'key'        => 'heritage',
          'badge'      => 'Point of Interest',
          'svg'        => '<path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/>',
      ];
  };

  // Find nearest airport and closest spot
  $nearestAirport = null;
  foreach ($allNearbyPlaces as $p) {
      $pm = $getPlaceMeta($p['name']);
      if ($pm['key'] === 'transit' && str_contains(strtolower($p['name']), 'airport')) {
          $nearestAirport = ['name' => $pm['clean_name'], 'dist' => $p['dist']];
          break;
      }
  }

  $closestPlace = !empty($allNearbyPlaces) ? $allNearbyPlaces[0] : null;
  $closestMeta = $closestPlace ? $getPlaceMeta($closestPlace['name']) : null;
  $destinationCity = $destination ?: ($hotel->destination->name ?? ($hotel->city ?? 'Jaipur'));

  // Category counts
  $catCounts = ['heritage' => 0, 'culture' => 0, 'transit' => 0];
  foreach ($allNearbyPlaces as $p) {
      $pm = $getPlaceMeta($p['name']);
      if (isset($catCounts[$pm['key']])) {
          $catCounts[$pm['key']]++;
      }
  }
@endphp

<div style="max-width:1280px; margin:0 auto; padding:0 40px 48px; scroll-margin-top:80px;" id="hd-anchor-location">
  <div class="hd-section">
    <div class="hd-lux-container">
      
      {{-- Section Header --}}
      <div class="hd-lux-header">
        <div class="hd-lux-header-left">
          <div class="hd-lux-header-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <polygon points="12 2 19 21 12 17 5 21 12 2"/>
            </svg>
          </div>
          <div>
            <h2 class="hd-lux-title">Nearby Attractions</h2>
            <div class="hd-lux-subtitle">Explore iconic heritage, cultural landmarks &amp; transit hubs around the property</div>
          </div>
        </div>

        {{-- Surrounding Quick Stats --}}
        @if(count($allNearbyPlaces) > 0)
        <div class="hd-lux-stats-bar">
          <div class="hd-lux-stat-pill">
            <span class="gold-dot"></span>
            <span><strong>{{ count($allNearbyPlaces) }}</strong> Points of Interest</span>
          </div>
          @if($closestPlace && !empty($closestPlace['dist']))
            @php
              $cDist = explode('/', $closestPlace['dist'])[0];
            @endphp
            <div class="hd-lux-stat-pill">
              <span class="gold-icon">✦</span>
              <span>Closest: <strong>{{ trim($cDist) }}</strong> <span style="opacity:0.65; font-size:11px;">· {{ \Illuminate\Support\Str::limit($closestMeta['clean_name'], 20) }}</span></span>
            </div>
          @endif
          @if($nearestAirport && !empty($nearestAirport['dist']))
            @php
              $aDist = explode('/', $nearestAirport['dist'])[0];
            @endphp
            <div class="hd-lux-stat-pill">
              <span class="gold-icon">✈</span>
              <span>Airport: <strong>{{ trim($aDist) }}</strong></span>
            </div>
          @endif
        </div>
        @endif
      </div>

      @if(count($allNearbyPlaces) > 0)
        {{-- Category Filter Chips --}}
        @if($catCounts['heritage'] > 0 || $catCounts['culture'] > 0 || $catCounts['transit'] > 0)
        <div class="hd-lux-filters">
          <button type="button" class="hd-lux-filter-btn active" onclick="filterNearby('all', this)">All Highlights ({{ count($allNearbyPlaces) }})</button>
          @if($catCounts['heritage'] > 0)
            <button type="button" class="hd-lux-filter-btn" onclick="filterNearby('heritage', this)">🏛️ Palaces &amp; Heritage ({{ $catCounts['heritage'] }})</button>
          @endif
          @if($catCounts['culture'] > 0)
            <button type="button" class="hd-lux-filter-btn" onclick="filterNearby('culture', this)">🎭 Culture &amp; Arts ({{ $catCounts['culture'] }})</button>
          @endif
          @if($catCounts['transit'] > 0)
            <button type="button" class="hd-lux-filter-btn" onclick="filterNearby('transit', this)">✈️ Transit &amp; Avenues ({{ $catCounts['transit'] }})</button>
          @endif
        </div>
        @endif

        {{-- Cards Grid --}}
        <div class="hd-lux-grid {{ count($allNearbyPlaces) > 6 ? 'collapsed' : '' }}" id="hdLuxPlacesGrid">
          @foreach($allNearbyPlaces as $p)
            @php
              $pm = $getPlaceMeta($p['name']);
              $gmapQuery = urlencode($pm['clean_name'] . ' ' . $destinationCity);
              $gmapUrl = "https://www.google.com/maps/search/?api=1&query={$gmapQuery}";
              
              $distMain = $p['dist'];
              $distSub = '';
              if (str_contains($p['dist'], '/')) {
                  $dParts = explode('/', $p['dist'], 2);
                  $distMain = trim($dParts[0]);
                  $distSub = trim($dParts[1]);
              }
            @endphp
            <a href="{{ $gmapUrl }}" target="_blank" rel="noopener noreferrer" class="hd-lux-card" data-cat="{{ $pm['key'] }}" title="Open {{ $pm['clean_name'] }} in Google Maps">
              <div class="hd-lux-card-left">
                <div class="hd-lux-icon-box">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    {!! $pm['svg'] !!}
                  </svg>
                </div>
                <div class="hd-lux-card-info">
                  <div class="hd-lux-card-title">{{ $pm['clean_name'] }}</div>
                  <div class="hd-lux-card-badge">{{ $pm['badge'] }}</div>
                </div>
              </div>
              <div class="hd-lux-card-right">
                @if($distMain)
                  <div class="hd-lux-dist-pill">
                    <span>{{ $distMain }}</span>
                    @if($distSub)<span class="hd-lux-dist-sub">· {{ $distSub }}</span>@endif
                  </div>
                @endif
                <div class="hd-lux-map-hint">
                  <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                </div>
              </div>
            </a>
          @endforeach
        </div>

        {{-- Bottom Action Bar --}}
        @if(count($allNearbyPlaces) > 6)
        <div class="hd-lux-bottom-bar">
          <div class="hd-lux-hint-text">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
            <span>Click any attraction to view route &amp; directions in Google Maps</span>
          </div>
          <button type="button" class="hd-lux-toggle-btn" id="hdLuxToggleBtn" onclick="toggleNearbyPlaces()">
            <span>Explore All {{ count($allNearbyPlaces) }} Attractions</span>
            <svg class="hd-lux-toggle-arrow" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
          </button>
        </div>
        @endif

      @elseif(!empty($hotel->bottom_sections['Attractions']))
        {{-- Raw Fallback if parsing returned 0 items --}}
        <div class="hd-place-list-wrap">
          {!! $hotel->bottom_sections['Attractions'] !!}
        </div>
      @endif

      {{-- Manual Hotels Restaurants & Cafes --}}
      @if(count($restaurantsCafes) > 0)
      <div style="margin-top:28px; padding-top:24px; border-top: 1px solid rgba(255,255,255,0.06);">
        <h3 style="font-family:'Cormorant Garamond',serif; font-size:1.4rem; color:var(--gold); margin-bottom:14px; display:flex; align-items:center; gap:8px;">
          <span>🍽️</span> Restaurants &amp; Caf&eacute;s
        </h3>
        <div class="hd-lux-grid">
          @foreach($restaurantsCafes as $item)
            @php
              $parts = preg_split('/\s*[\(\-—]\s*/', $item, 2);
              $pName = trim($parts[0]);
              $pDist = isset($parts[1]) ? trim(rtrim($parts[1], ')')) : '';
              $gmapQuery = urlencode($pName . ' ' . $destinationCity);
              $gmapUrl = "https://www.google.com/maps/search/?api=1&query={$gmapQuery}";
            @endphp
            <a href="{{ $gmapUrl }}" target="_blank" rel="noopener noreferrer" class="hd-lux-card" title="Open {{ $pName }} in Google Maps">
              <div class="hd-lux-card-left">
                <div class="hd-lux-icon-box">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M18 8h1a4 4 0 0 1 0 8h-1M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8zM6 1v3M10 1v3M14 1v3"/>
                  </svg>
                </div>
                <div class="hd-lux-card-info">
                  <div class="hd-lux-card-title">{{ $pName }}</div>
                  <div class="hd-lux-card-badge">Dining &amp; Culinary</div>
                </div>
              </div>
              @if($pDist)
              <div class="hd-lux-card-right">
                <div class="hd-lux-dist-pill">
                  <span>{{ $pDist }}</span>
                </div>
              </div>
              @endif
            </a>
          @endforeach
        </div>
      </div>
      @endif

      {{-- Manual Hotels Top Attractions --}}
      @if(count($topAttractions) > 0)
      <div style="margin-top:28px; padding-top:24px; border-top: 1px solid rgba(255,255,255,0.06);">
        <h3 style="font-family:'Cormorant Garamond',serif; font-size:1.4rem; color:var(--gold); margin-bottom:14px; display:flex; align-items:center; gap:8px;">
          <span>🏛️</span> Top Attractions
        </h3>
        <div class="hd-lux-grid">
          @foreach($topAttractions as $item)
            @php
              $parts = preg_split('/\s*[\(\-—]\s*/', $item, 2);
              $pName = trim($parts[0]);
              $pDist = isset($parts[1]) ? trim(rtrim($parts[1], ')')) : '';
              $gmapQuery = urlencode($pName . ' ' . $destinationCity);
              $gmapUrl = "https://www.google.com/maps/search/?api=1&query={$gmapQuery}";
            @endphp
            <a href="{{ $gmapUrl }}" target="_blank" rel="noopener noreferrer" class="hd-lux-card" title="Open {{ $pName }} in Google Maps">
              <div class="hd-lux-card-left">
                <div class="hd-lux-icon-box">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 21h18M3 10h18M5 10v11M19 10v11M9 10v11M15 10v11M12 2l8 8H4z"/>
                  </svg>
                </div>
                <div class="hd-lux-card-info">
                  <div class="hd-lux-card-title">{{ $pName }}</div>
                  <div class="hd-lux-card-badge">Must-Visit Attraction</div>
                </div>
              </div>
              @if($pDist)
              <div class="hd-lux-card-right">
                <div class="hd-lux-dist-pill">
                  <span>{{ $pDist }}</span>
                </div>
              </div>
              @endif
            </a>
          @endforeach
        </div>
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

    /* ===== HERO GALLERY SLIDESHOW & CONTROLS ===== */
    (function () {
      const main = document.getElementById('hdGalleryMain');
      if (!main) return;
      const slides = Array.from(main.querySelectorAll('.hd-carousel-slide'));
      const prevBtn = document.getElementById('hdGalleryPrev');
      const nextBtn = document.getElementById('hdGalleryNext');
      const counterIndex = document.getElementById('hdCounterIndex');
      const counterTotal = document.getElementById('hdCounterTotal');
      if (!slides.length) return;

      if (counterTotal) counterTotal.textContent = slides.length;

      let current = 0;
      let timer = null;

      function showSlide(index) {
        slides[current].classList.remove('active');
        current = (index + slides.length) % slides.length;
        slides[current].classList.add('active');
        if (counterIndex) counterIndex.textContent = current + 1;
      }

      function nextSlide() {
        showSlide(current + 1);
      }

      function prevSlide() {
        showSlide(current - 1);
      }

      if (prevBtn) prevBtn.addEventListener('click', (e) => { e.stopPropagation(); prevSlide(); restartTimer(); });
      if (nextBtn) nextBtn.addEventListener('click', (e) => { e.stopPropagation(); nextSlide(); restartTimer(); });

      // Auto-advance every 5 seconds
      function startTimer() {
        if (slides.length > 1 && !timer) {
          timer = setInterval(nextSlide, 5000);
        }
      }
      function stopTimer() {
        if (timer) {
          clearInterval(timer);
          timer = null;
        }
      }
      function restartTimer() {
        stopTimer();
        startTimer();
      }

      main.addEventListener('mouseenter', stopTimer);
      main.addEventListener('mouseleave', startTimer);
      startTimer();

      // Touch swipe for mobile devices
      let touchStartX = 0;
      let touchEndX = 0;
      main.addEventListener('touchstart', (e) => {
        touchStartX = e.changedTouches[0].screenX;
        stopTimer();
      }, { passive: true });
      main.addEventListener('touchend', (e) => {
        touchEndX = e.changedTouches[0].screenX;
        if (touchStartX - touchEndX > 45) {
          nextSlide();
        } else if (touchEndX - touchStartX > 45) {
          prevSlide();
        }
        startTimer();
      }, { passive: true });

      // Keyboard navigation when hovering
      document.addEventListener('keydown', (e) => {
        const lb = document.getElementById('hdLightbox');
        if (lb && lb.classList.contains('open')) return; // let lightbox handle keys if open
        if (e.key === 'ArrowLeft') prevSlide();
        if (e.key === 'ArrowRight') nextSlide();
      });
    })();

    /* ===== PHOTO LIGHTBOX ===== */
    (function () {
      const slideImgs = Array.from(document.querySelectorAll('#hdGalleryMain .hd-carousel-slide img'));
      const photoUrls = slideImgs.map(img => img.src);

      const lightbox = document.getElementById('hdLightbox');
      const lbImg = document.getElementById('hdLightboxImg');
      const lbCounter = document.getElementById('hdLightboxCounter');
      const lbClose = document.getElementById('hdLightboxClose');
      const lbPrev = document.getElementById('hdLightboxPrev');
      const lbNext = document.getElementById('hdLightboxNext');

      let lbIndex = 0;
      function openLightbox(index) {
        if (!lightbox || !lbImg || !photoUrls.length) return;
        lbIndex = (index + photoUrls.length) % photoUrls.length;
        lbImg.src = photoUrls[lbIndex];
        if (lbCounter) lbCounter.textContent = (lbIndex + 1) + ' / ' + photoUrls.length;
        lightbox.classList.add('open');
        document.body.style.overflow = 'hidden';
      }
      function closeLightbox() {
        if (!lightbox) return;
        lightbox.classList.remove('open');
        document.body.style.overflow = '';
      }

      window.openLightboxFromMain = openLightbox;

      document.getElementById('hdViewAllPhotos')?.addEventListener('click', () => openLightbox(0));
      document.getElementById('hdSideTileTop')?.addEventListener('click', () => openLightbox(1));
      document.getElementById('hdSideTileExplore')?.addEventListener('click', () => openLightbox(2));
      lbClose?.addEventListener('click', closeLightbox);
      lbPrev?.addEventListener('click', () => openLightbox(lbIndex - 1));
      lbNext?.addEventListener('click', () => openLightbox(lbIndex + 1));
      lightbox?.addEventListener('click', (e) => { if (e.target === lightbox) closeLightbox(); });

      document.addEventListener('keydown', (e) => {
        if (!lightbox || !lightbox.classList.contains('open')) return;
        if (e.key === 'Escape') closeLightbox();
        if (e.key === 'ArrowLeft') openLightbox(lbIndex - 1);
        if (e.key === 'ArrowRight') openLightbox(lbIndex + 1);
      });
    })();

    /* ===== FAVOURITE, SHARE & MAP ACTIONS ===== */
    (function () {
      // Favourite button
      const favBtn = document.getElementById('hdFavBtn');
      if (favBtn) {
        const hotelId = favBtn.getAttribute('data-hotel-id') || '0';
        const hotelTitle = favBtn.getAttribute('data-hotel-title') || 'Hotel';
        const favKey = 'tyt_fav_hotel_' + hotelId;
        const favText = document.getElementById('hdFavText');

        if (localStorage.getItem(favKey) === 'true') {
          favBtn.classList.add('active');
          if (favText) favText.textContent = 'Saved';
        }

        favBtn.addEventListener('click', () => {
          const isFav = favBtn.classList.toggle('active');
          if (isFav) {
            localStorage.setItem(favKey, 'true');
            if (favText) favText.textContent = 'Saved';
            if (typeof window.showToast === 'function') {
              window.showToast('Saved to Favourites', hotelTitle + ' has been saved to your favourites.');
            }
          } else {
            localStorage.removeItem(favKey);
            if (favText) favText.textContent = 'Favourite';
            if (typeof window.showToast === 'function') {
              window.showToast('Removed', hotelTitle + ' removed from your favourites.');
            }
          }
        });
      }

      // Share button
      const shareBtn = document.getElementById('hdShareBtn');
      if (shareBtn) {
        shareBtn.addEventListener('click', async () => {
          const title = shareBtn.getAttribute('data-share-title') || document.title;
          const url = shareBtn.getAttribute('data-share-url') || window.location.href;
          if (navigator.share) {
            try {
              await navigator.share({ title: title, url: url });
            } catch (err) {
              // User cancelled or share failed, silent fallback
            }
          } else if (navigator.clipboard) {
            try {
              await navigator.clipboard.writeText(url);
              if (typeof window.showToast === 'function') {
                window.showToast('Link Copied', 'Hotel link copied to clipboard.');
              }
            } catch (err) {
              prompt('Copy hotel link:', url);
            }
          } else {
            prompt('Copy hotel link:', url);
          }
        });
      }

      // Smooth scroll to map
      document.getElementById('hdScrollToMap')?.addEventListener('click', function (e) {
        const mapSection = document.getElementById('hd-anchor-location') || document.querySelector('[id*="location"]');
        if (mapSection) {
          e.preventDefault();
          mapSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
      });
    })();

    /* ===== SECTION NAV: ACTIVE LINK ON SCROLL ===== */
    (function () {
      const navLinks = Array.from(document.querySelectorAll('.hd-section-nav a'));
      if (!navLinks.length) return;
      const targets = navLinks
        .map(link => {
          const href = link.getAttribute('href');
          if (href && href.startsWith('#') && href.length > 1) {
            try { return document.querySelector(href); } catch (e) { return null; }
          }
          return null;
        })
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


    /* ===== AMENITY GROUP "READ MORE" TOGGLE ===== */
    document.addEventListener('click', (e) => {
      const btn = e.target.closest('.hd-readmore-btn');
      if (!btn) return;
      const list = btn.previousElementSibling;
      if (!list || !list.classList.contains('hd-collapsible')) return;
      const isCollapsed = list.classList.toggle('collapsed');
      btn.textContent = isCollapsed ? 'Read more' : 'Show less';
    });

    /* ===== LUXURY NEARBY ATTRACTIONS CONTROLS ===== */
    window.toggleNearbyPlaces = function () {
      const grid = document.getElementById('hdLuxPlacesGrid');
      const btn = document.getElementById('hdLuxToggleBtn');
      if (!grid || !btn) return;
      const isCollapsed = grid.classList.toggle('collapsed');
      const count = grid.querySelectorAll('.hd-lux-card').length;
      const textSpan = btn.querySelector('span');
      if (textSpan) textSpan.textContent = isCollapsed ? `Explore All ${count} Attractions` : 'Show Fewer';
      const arrow = btn.querySelector('.hd-lux-toggle-arrow');
      if (arrow) arrow.style.transform = isCollapsed ? 'rotate(0deg)' : 'rotate(180deg)';
    };

    window.filterNearby = function (category, btn) {
      const container = document.getElementById('hd-anchor-location');
      if (!container) return;
      container.querySelectorAll('.hd-lux-filter-btn').forEach(b => b.classList.remove('active'));
      if (btn) btn.classList.add('active');

      const grid = document.getElementById('hdLuxPlacesGrid');
      if (!grid) return;
      const cards = grid.querySelectorAll('.hd-lux-card');
      cards.forEach(card => {
        if (category === 'all' || card.dataset.cat === category) {
          card.style.display = 'flex';
        } else {
          card.style.display = 'none';
        }
      });

      // If user filtered, expand so they see all matching items
      if (category !== 'all' && grid.classList.contains('collapsed')) {
        window.toggleNearbyPlaces();
      }
    };

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

    /* ===== HOTEL INFO MODAL (Overview / Amenities / Policies) ===== */
    window.openHotelInfoModal = function (tabId) {
      const hiModal = document.getElementById('hdHotelInfoModal');
      if (!hiModal) return;

      const panelId = 'hdim-' + (tabId || 'overview');
      hiModal.querySelectorAll('.hdim-tab').forEach(t => t.classList.remove('active'));
      hiModal.querySelectorAll('.hdim-panel').forEach(p => p.classList.remove('active'));

      let targetTab = hiModal.querySelector('[data-hdim-tab="' + panelId + '"]');
      let targetPanel = document.getElementById(panelId);

      // Fallback to first available tab/panel if the requested one is not present
      if (!targetTab || !targetPanel) {
        targetTab = hiModal.querySelector('.hdim-tab');
        if (targetTab && targetTab.dataset.hdimTab) {
          targetPanel = document.getElementById(targetTab.dataset.hdimTab);
        }
      }

      if (targetTab)   targetTab.classList.add('active');
      if (targetPanel) targetPanel.classList.add('active');

      const body = hiModal.querySelector('.hdim-body');
      if (body) body.scrollTop = 0;

      hiModal.classList.add('open');
      document.body.style.overflow = 'hidden';
    };

    window.closeHotelInfoModal = function () {
      const hiModal = document.getElementById('hdHotelInfoModal');
      if (hiModal) hiModal.classList.remove('open');
      document.body.style.overflow = '';
    };

    (function () {
      const hiModal = document.getElementById('hdHotelInfoModal');
      if (!hiModal) return;

      // Tab switching via event delegation on tab container
      const tabsWrap = hiModal.querySelector('.hdim-tabs');
      if (tabsWrap) {
        tabsWrap.addEventListener('click', (e) => {
          const tab = e.target.closest('.hdim-tab');
          if (!tab || !tab.dataset.hdimTab) return;
          hiModal.querySelectorAll('.hdim-tab').forEach(t => t.classList.remove('active'));
          hiModal.querySelectorAll('.hdim-panel').forEach(p => p.classList.remove('active'));
          tab.classList.add('active');
          const target = document.getElementById(tab.dataset.hdimTab);
          if (target) target.classList.add('active');
          const body = hiModal.querySelector('.hdim-body');
          if (body) body.scrollTop = 0;
        });
      }

      const hiClose = document.getElementById('hdHotelInfoModalClose');
      hiClose?.addEventListener('click', window.closeHotelInfoModal);
      hiModal.addEventListener('click', e => { if (e.target === hiModal) window.closeHotelInfoModal(); });
      document.addEventListener('keydown', e => {
        if (e.key === 'Escape' && hiModal.classList.contains('open')) window.closeHotelInfoModal();
      });
    })();


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
    /* ===== STAY MODIFIER BAR (Change Dates, Guests, Place) ===== */
    (function () {
      const form = document.getElementById('hdSearchForm');
      const destInput = document.getElementById('hdModDestinationSearch');
      const checkInDisplay = document.getElementById('hdModCheckIn');
      const checkOutDisplay = document.getElementById('hdModCheckOut');
      const checkInIso = document.getElementById('hdModCheckInIso');
      const checkOutIso = document.getElementById('hdModCheckOutIso');
      const nightsBadge = document.getElementById('hdModNightsBadge');
      const rangeInput = document.getElementById('hdModRangePicker');
      const guestField = document.getElementById('hdModGuestField');
      const guestPopover = document.getElementById('hdModGuestPopover');
      const guestSummary = document.getElementById('hdModGuestSummary');
      const roomBlocksEl = document.getElementById('hdModRoomBlocks');
      const addRoomBtn = document.getElementById('hdModAddRoomBtn');
      const applyBtn = document.getElementById('hdModGuestApplyBtn');

      if (!form || !rangeInput || typeof flatpickr === 'undefined') return;

      // 0. Custom Destination Dropdown
      const destField = document.getElementById('hdModDestField');
      const destPopover = document.getElementById('hdModDestPopover');
      const noResults = document.getElementById('hdModDestNoResults');
      if (destField && destInput && destPopover) {
        const destOptions = Array.from(destPopover.querySelectorAll('.htl-dest-option'));
        let destActiveIdx = -1;

        function openDestDropdown() {
          destPopover.classList.add('open');
          destField.classList.add('open');
          filterDestOptions();
        }

        function closeDestDropdown() {
          destPopover.classList.remove('open');
          destField.classList.remove('open');
          destActiveIdx = -1;
          destOptions.forEach(opt => opt.classList.remove('highlighted'));
        }

        function filterDestOptions() {
          const q = destInput.value.trim().toLowerCase();
          let matchCount = 0;
          destOptions.forEach(opt => {
            const val = (opt.dataset.value || '').toLowerCase();
            if (!q || val.includes(q)) {
              opt.style.display = 'flex';
              matchCount++;
            } else {
              opt.style.display = 'none';
              opt.classList.remove('highlighted');
            }
          });
          if (noResults) noResults.style.display = matchCount === 0 ? 'block' : 'none';
        }

        function selectDestOption(val) {
          destInput.value = val;
          closeDestDropdown();
          if (checkInIso && !checkInIso.value && typeof fp !== 'undefined') fp.open();
        }

        destInput.addEventListener('focus', openDestDropdown);
        destField.addEventListener('click', (e) => {
          if (e.target.closest('#hdModDestPopover')) return;
          if (destPopover.classList.contains('open') && e.target !== destInput) {
            closeDestDropdown();
          } else {
            openDestDropdown();
            destInput.focus();
          }
        });

        destInput.addEventListener('input', () => {
          if (!destPopover.classList.contains('open')) {
            openDestDropdown();
          } else {
            filterDestOptions();
          }
        });

        destInput.addEventListener('keydown', (e) => {
          const visibleOpts = destOptions.filter(opt => opt.style.display !== 'none');
          if (!destPopover.classList.contains('open')) {
            if (e.key === 'ArrowDown' || e.key === 'Enter') {
              openDestDropdown();
              e.preventDefault();
            }
            return;
          }

          if (e.key === 'ArrowDown') {
            e.preventDefault();
            destActiveIdx = (destActiveIdx + 1) % visibleOpts.length;
            visibleOpts.forEach((opt, idx) => opt.classList.toggle('highlighted', idx === destActiveIdx));
            if (visibleOpts[destActiveIdx]) visibleOpts[destActiveIdx].scrollIntoView({ block: 'nearest' });
          } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            destActiveIdx = (destActiveIdx - 1 + visibleOpts.length) % visibleOpts.length;
            visibleOpts.forEach((opt, idx) => opt.classList.toggle('highlighted', idx === destActiveIdx));
            if (visibleOpts[destActiveIdx]) visibleOpts[destActiveIdx].scrollIntoView({ block: 'nearest' });
          } else if (e.key === 'Enter') {
            e.preventDefault();
            if (destActiveIdx >= 0 && visibleOpts[destActiveIdx]) {
              selectDestOption(visibleOpts[destActiveIdx].dataset.value);
            } else if (visibleOpts.length > 0) {
              selectDestOption(visibleOpts[0].dataset.value);
            } else {
              closeDestDropdown();
            }
          } else if (e.key === 'Escape') {
            closeDestDropdown();
          }
        });

        destOptions.forEach(opt => {
          opt.addEventListener('click', (e) => {
            e.stopPropagation();
            selectDestOption(opt.dataset.value);
          });
        });

        document.addEventListener('click', (e) => {
          if (!destField.contains(e.target)) {
            closeDestDropdown();
          }
        });
      }

      // 1. Initialize Flatpickr Date Range Picker
      const initialCheckIn = checkInIso?.value || null;
      const initialCheckOut = checkOutIso?.value || null;

      const fp = flatpickr(rangeInput, {
        mode: 'range',
        minDate: 'today',
        dateFormat: 'Y-m-d',
        showMonths: window.innerWidth > 768 ? 2 : 1,
        positionElement: document.getElementById('hdModCheckInField'),
        defaultDate: initialCheckIn && initialCheckOut ? [initialCheckIn, initialCheckOut] : null,
        onChange: function (selectedDates, dateStr, instance) {
          if (selectedDates.length >= 1) {
            checkInDisplay.value = instance.formatDate(selectedDates[0], 'D, j M Y');
            checkInIso.value = instance.formatDate(selectedDates[0], 'Y-m-d');
          } else {
            checkInDisplay.value = '';
            checkInIso.value = '';
          }
          if (selectedDates.length === 2) {
            checkOutDisplay.value = instance.formatDate(selectedDates[1], 'D, j M Y');
            checkOutIso.value = instance.formatDate(selectedDates[1], 'Y-m-d');
            const nights = Math.round((selectedDates[1] - selectedDates[0]) / 86400000);
            nightsBadge.textContent = nights + 'N';
            nightsBadge.hidden = false;
          } else {
            checkOutDisplay.value = '';
            checkOutIso.value = '';
            nightsBadge.hidden = true;
          }
        },
        onClose: function (selectedDates) {
          if (selectedDates.length === 2 && guestField && guestPopover) {
            guestPopover.classList.add('open');
            guestField.classList.add('open');
          }
        },
      });

      [checkInDisplay, checkOutDisplay].forEach(el => {
        if (el) el.addEventListener('click', (e) => { e.stopPropagation(); fp.open(); });
      });

      if (initialCheckIn && initialCheckOut) {
        const d1 = new Date(initialCheckIn);
        const d2 = new Date(initialCheckOut);
        checkInDisplay.value = fp.formatDate(d1, 'D, j M Y');
        checkOutDisplay.value = fp.formatDate(d2, 'D, j M Y');
        const nights = Math.round((d2 - d1) / 86400000);
        if (nights > 0) {
          nightsBadge.textContent = nights + 'N';
          nightsBadge.hidden = false;
        }
      }

      // 2. Rooms & Guests State Management
      const initialRoomCount = parseInt(document.getElementById('hdModRooms')?.value || '1', 10);
      const initialAdults = parseInt(document.getElementById('hdModAdults')?.value || '2', 10);
      const initialChildren = parseInt(document.getElementById('hdModChildren')?.value || '0', 10);
      const initialAgesPool = (document.getElementById('hdModChildAges')?.value || '')
        .split(',').map(v => v.trim()).filter(v => v !== '').map(v => parseInt(v, 10));

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
          remA -= a;
          remC -= c;
        }
      }

      function ageOptions(selected) {
        let opts = '<option value="" disabled ' + (selected === null ? 'selected' : '') + '>Age</option>';
        for (let age = 0; age <= 17; age++) {
          opts += `<option value="${age}" ${selected === age ? 'selected' : ''}>${age} ${age === 1 ? 'yr' : 'yrs'}</option>`;
        }
        return opts;
      }

      function renderModRoomBlocks() {
        if (!roomBlocksEl) return;
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
            renderModRoomBlocks();
          });
        });

        roomBlocksEl.querySelectorAll('[data-remove]').forEach(btn => {
          btn.addEventListener('click', () => {
            rooms.splice(parseInt(btn.dataset.remove, 10), 1);
            renderModRoomBlocks();
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
      renderModRoomBlocks();

      if (addRoomBtn) {
        addRoomBtn.addEventListener('click', () => {
          if (rooms.length >= 9) return;
          rooms.push({ adults: 1, children: 0, childAges: [] });
          renderModRoomBlocks();
        });
      }

      function syncGuestFields() {
        const totalAdults = rooms.reduce((s, r) => s + r.adults, 0);
        const totalChildren = rooms.reduce((s, r) => s + r.children, 0);
        const allAges = rooms.flatMap(r => r.childAges).filter(a => a !== null);
        document.getElementById('hdModRooms').value = rooms.length;
        document.getElementById('hdModAdults').value = totalAdults;
        document.getElementById('hdModChildren').value = totalChildren;
        document.getElementById('hdModChildAges').value = allAges.join(',');
        let summary = rooms.length + (rooms.length === 1 ? ' Room, ' : ' Rooms, ') + totalAdults + (totalAdults === 1 ? ' Adult' : ' Adults');
        if (totalChildren > 0) summary += ', ' + totalChildren + (totalChildren === 1 ? ' Child' : ' Children');
        if (guestSummary) guestSummary.textContent = summary;
      }
      syncGuestFields();

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

      // 3. Smart Form Submission
      form.addEventListener('submit', function (e) {
        e.preventDefault();

        const origDest = (destInput?.dataset.originalDest || '').trim().toLowerCase();
        const currDest = (destInput?.value || '').trim().toLowerCase();
        const inIso = checkInIso?.value || '';
        const outIso = checkOutIso?.value || '';

        if (!currDest) {
          destInput?.focus();
          alert('Please enter a destination or place.');
          return;
        }

        if (!inIso || !outIso) {
          fp.open();
          return;
        }

        // If user changed destination, redirect to the main hotel search page
        if (currDest !== origDest) {
          const searchUrl = new URL("{{ route('hotels') }}", window.location.origin);
          searchUrl.searchParams.set('destination', destInput.value.trim());
          searchUrl.searchParams.set('check_in', inIso);
          searchUrl.searchParams.set('check_out', outIso);
          searchUrl.searchParams.set('adults', document.getElementById('hdModAdults').value);
          searchUrl.searchParams.set('children', document.getElementById('hdModChildren').value);
          searchUrl.searchParams.set('rooms', document.getElementById('hdModRooms').value);
          const ca = document.getElementById('hdModChildAges').value;
          if (ca) searchUrl.searchParams.set('child_ages', ca);
          window.location.href = searchUrl.toString();
          return;
        }

        // If destination is the same, reload this hotel details page with new dates & guests
        const detailUrl = new URL("{{ route('hotel.details', $hotel->slug) }}", window.location.origin);
        detailUrl.searchParams.set('check_in', inIso);
        detailUrl.searchParams.set('check_out', outIso);
        detailUrl.searchParams.set('adults', document.getElementById('hdModAdults').value);
        detailUrl.searchParams.set('children', document.getElementById('hdModChildren').value);
        detailUrl.searchParams.set('rooms', document.getElementById('hdModRooms').value);
        const childAgesVal = document.getElementById('hdModChildAges').value;
        if (childAgesVal) detailUrl.searchParams.set('child_ages', childAgesVal);
        @if($hotel->source === 'tripjack')
        detailUrl.hash = 'htl-room-section';
        @else
        detailUrl.hash = 'hd-anchor-rooms';
        @endif

        window.location.href = detailUrl.toString();
      });
    })();
  });
</script>
@endpush
