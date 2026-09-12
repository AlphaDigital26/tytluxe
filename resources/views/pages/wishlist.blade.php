@extends('layouts.frontend')

@section('meta_title', 'My Wishlist — Saved Luxury Stays & Sanctuaries | TYT Luxe')
@section('meta_description', 'View and manage your curated wishlist of handpicked luxury hotels, boutique sanctuaries, and heritage stays across India and worldwide on TYT Luxe.')

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com" />
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;0,700;1,400;1,600&family=Jost:wght@300;400;500;600;700&display=swap" rel="stylesheet" />

<style>
/* ===== VARIABLES ===== */
:root {
  --wl-gold: #c9a84c;
  --wl-gold-light: #e8c96b;
  --wl-gold-dim: rgba(201, 168, 76, 0.15);
  --wl-dark: #0d0d0d;
  --wl-dark-2: #141414;
  --wl-dark-3: #1c1c1c;
  --wl-card-bg: #161616;
  --wl-border: rgba(255, 255, 255, 0.08);
  --wl-border-gold: rgba(201, 168, 76, 0.35);
  --wl-text-muted: rgba(255, 255, 255, 0.65);
  --wl-text-dim: rgba(255, 255, 255, 0.4);
  --wl-radius: 16px;
  --wl-transition: 0.35s cubic-bezier(0.25, 0.46, 0.45, 0.94);
}

.wl-page-wrapper {
  background: var(--wl-dark);
  color: #fff;
  min-height: 80vh;
  padding-bottom: 80px;
}

/* ===== HERO SECTION ===== */
.wl-hero {
  position: relative;
  background: radial-gradient(circle at 50% 0%, rgba(201, 168, 76, 0.14) 0%, rgba(13, 13, 13, 0.98) 70%), #0d0d0d;
  border-bottom: 1px solid var(--wl-gold-dim);
  padding: 135px 0 34px;
  text-align: center;
}
@media (max-width: 768px) {
  .wl-hero {
    padding: 105px 0 26px;
  }
}
.wl-hero-inner {
  max-width: 900px;
  margin: 0 auto;
}
.wl-breadcrumb {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  font-family: 'Jost', sans-serif;
  font-size: 11.5px;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  color: var(--wl-text-dim);
  margin-bottom: 12px;
}
.wl-breadcrumb a {
  color: var(--wl-text-muted);
  text-decoration: none;
  transition: color 0.2s ease;
}
.wl-breadcrumb a:hover {
  color: var(--wl-gold);
}
.wl-breadcrumb span {
  color: var(--wl-gold);
}
.wl-kicker {
  font-family: 'Jost', sans-serif;
  font-size: 10.5px;
  font-weight: 700;
  letter-spacing: 0.2em;
  text-transform: uppercase;
  color: var(--wl-gold);
  margin-bottom: 8px;
  display: inline-block;
  background: rgba(201, 168, 76, 0.1);
  border: 1px solid rgba(201, 168, 76, 0.25);
  padding: 3px 12px;
  border-radius: 100px;
}
.wl-hero-title {
  font-family: 'Cormorant Garamond', Georgia, serif;
  font-size: clamp(2.2rem, 4vw, 3.2rem);
  font-weight: 500;
  color: #fff;
  line-height: 1.15;
  margin: 0 0 10px;
  letter-spacing: -0.01em;
}
.wl-hero-title span {
  font-style: italic;
  color: var(--wl-gold-light);
}
.wl-hero-sub {
  font-family: 'Jost', sans-serif;
  font-size: 14px;
  color: var(--wl-text-muted);
  max-width: 540px;
  margin: 0 auto;
  line-height: 1.55;
  font-weight: 300;
}

/* ===== MAIN SECTION & SITE CONTAINER ===== */
.wl-content-section {
  padding: 36px 0 80px;
}
.wl-page-wrapper .container {
  max-width: var(--container-max, 1200px);
  margin-left: auto;
  margin-right: auto;
  padding-left: 40px;
  padding-right: 40px;
  box-sizing: border-box;
}
@media (max-width: 1024px) {
  .wl-page-wrapper .container {
    padding-left: 30px;
    padding-right: 30px;
  }
}
@media (max-width: 768px) {
  .wl-content-section {
    padding: 24px 0 60px;
  }
  .wl-page-wrapper .container {
    padding-left: 20px;
    padding-right: 20px;
  }
}
@media (max-width: 480px) {
  .wl-page-wrapper .container {
    padding-left: 15px;
    padding-right: 15px;
  }
}
.wl-content-inner {
  width: 100%;
}

/* ===== CATEGORY TABS ===== */
.wl-category-nav {
  display: flex;
  justify-content: center;
  margin-bottom: 26px;
}
.wl-category-tabs {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  background: #141414;
  border: 1px solid rgba(255, 255, 255, 0.09);
  padding: 6px;
  border-radius: 100px;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.4);
  max-width: 100%;
  overflow-x: auto;
  -webkit-overflow-scrolling: touch;
  scrollbar-width: none;
}
.wl-category-tabs::-webkit-scrollbar {
  display: none;
}
.wl-tab-btn {
  background: transparent;
  border: 1px solid transparent;
  color: var(--wl-text-muted);
  font-family: 'Jost', sans-serif;
  font-size: 13px;
  font-weight: 500;
  letter-spacing: 0.05em;
  text-transform: uppercase;
  padding: 8px 22px;
  border-radius: 100px;
  cursor: pointer;
  transition: all 0.25s ease;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  white-space: nowrap;
  outline: none;
}
.wl-tab-btn:hover {
  color: #fff;
  background: rgba(255, 255, 255, 0.05);
}
.wl-tab-btn.active {
  background: var(--wl-gold);
  border-color: var(--wl-gold);
  color: #0d0d0d;
  font-weight: 700;
  box-shadow: 0 4px 14px rgba(201, 168, 76, 0.35);
}
.wl-tab-icon {
  width: 15px;
  height: 15px;
  flex-shrink: 0;
}
.wl-tab-badge {
  background: rgba(255, 255, 255, 0.1);
  color: var(--wl-text-muted);
  font-size: 11px;
  font-weight: 600;
  padding: 1px 7px;
  border-radius: 10px;
  transition: all 0.25s ease;
}
.wl-tab-btn.active .wl-tab-badge {
  background: #0d0d0d;
  color: var(--wl-gold);
}
@media (max-width: 640px) {
  .wl-category-nav {
    justify-content: flex-start;
    margin-bottom: 20px;
    width: 100%;
  }
  .wl-category-tabs {
    width: 100%;
    justify-content: space-between;
    padding: 4px;
    gap: 4px;
  }
  .wl-tab-btn {
    padding: 7px 12px;
    font-size: 11px;
    gap: 5px;
    flex: 1;
    justify-content: center;
  }
  .wl-tab-icon {
    display: none;
  }
}

/* ===== ACTION TOOLBAR ===== */
.wl-toolbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 18px 0 16px;
  border-bottom: 1px solid var(--wl-border);
  margin-bottom: 24px;
  flex-wrap: wrap;
  gap: 14px;
}
.wl-stats {
  font-family: 'Jost', sans-serif;
  font-size: 13.5px;
  color: var(--wl-text-muted);
  display: flex;
  align-items: center;
  gap: 8px;
}
.wl-stats-badge {
  background: rgba(201, 168, 76, 0.15);
  color: var(--wl-gold-light);
  font-weight: 600;
  font-size: 12px;
  padding: 2px 9px;
  border-radius: 12px;
  border: 1px solid rgba(201, 168, 76, 0.3);
}
.wl-actions {
  display: flex;
  align-items: center;
  gap: 12px;
}
.wl-btn-clear {
  background: transparent;
  border: 1px solid rgba(255, 255, 255, 0.16);
  color: var(--wl-text-muted);
  font-family: 'Jost', sans-serif;
  font-size: 12px;
  font-weight: 500;
  letter-spacing: 0.04em;
  padding: 7px 16px;
  border-radius: 100px;
  cursor: pointer;
  transition: all 0.25s ease;
  display: inline-flex;
  align-items: center;
  gap: 6px;
}
.wl-btn-clear:hover {
  border-color: #ff6b6b;
  color: #ff6b6b;
  background: rgba(255, 107, 107, 0.08);
}
.wl-btn-explore {
  background: var(--wl-gold);
  border: 1px solid var(--wl-gold);
  color: #0d0d0d;
  font-family: 'Jost', sans-serif;
  font-size: 12px;
  font-weight: 700;
  letter-spacing: 0.07em;
  text-transform: uppercase;
  padding: 8px 18px;
  border-radius: 100px;
  text-decoration: none;
  cursor: pointer;
  transition: all 0.25s ease;
  display: inline-flex;
  align-items: center;
  gap: 6px;
}
.wl-btn-explore:hover {
  background: var(--wl-gold-light);
  transform: translateY(-1px);
  box-shadow: 0 4px 14px rgba(201, 168, 76, 0.35);
}
@media (max-width: 640px) {
  .wl-toolbar {
    flex-direction: column;
    align-items: flex-start;
    gap: 12px;
    padding: 14px 0;
    margin-bottom: 18px;
  }
  .wl-actions {
    width: 100%;
    justify-content: space-between;
  }
  .wl-btn-clear, .wl-btn-explore {
    flex: 1;
    justify-content: center;
    text-align: center;
    font-size: 11.5px;
    padding: 7px 12px;
  }
}

/* ===== GRID OF SAVED CARDS ===== */
.wl-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 24px;
}
@media (max-width: 1024px) {
  .wl-grid {
    grid-template-columns: repeat(2, 1fr);
    gap: 18px;
  }
}
@media (max-width: 640px) {
  .wl-grid {
    grid-template-columns: 1fr;
    gap: 18px;
  }
}

/* ===== HOTEL CARD ===== */
.wl-card {
  background: var(--wl-card-bg);
  border: 1px solid var(--wl-border);
  border-radius: var(--wl-radius);
  overflow: hidden;
  display: flex;
  flex-direction: column;
  position: relative;
  transition: all var(--wl-transition);
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
}
.wl-card:hover {
  transform: translateY(-5px);
  border-color: var(--wl-border-gold);
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.7), 0 0 25px rgba(201, 168, 76, 0.12);
}
.wl-card.removing {
  opacity: 0;
  transform: scale(0.92);
  transition: all 0.32s ease;
  pointer-events: none;
}

/* Card Image Area */
.wl-card-media {
  position: relative;
  height: 205px;
  width: 100%;
  overflow: hidden;
  background: #111;
}
.wl-card-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.6s cubic-bezier(0.25, 0.46, 0.45, 0.94);
}
.wl-card:hover .wl-card-img {
  transform: scale(1.07);
}
.wl-card-gradient {
  position: absolute;
  inset: 0;
  background: linear-gradient(180deg, rgba(0,0,0,0.3) 0%, transparent 40%, rgba(13,13,13,0.85) 100%);
  pointer-events: none;
}

/* Rating Pill */
.wl-rating-pill {
  position: absolute;
  top: 14px;
  left: 14px;
  background: rgba(13, 13, 13, 0.75);
  backdrop-filter: blur(8px);
  border: 1px solid rgba(201, 168, 76, 0.35);
  border-radius: 100px;
  padding: 4px 10px;
  font-family: 'Jost', sans-serif;
  font-size: 11px;
  font-weight: 600;
  color: var(--wl-gold);
  display: flex;
  align-items: center;
  gap: 4px;
  z-index: 3;
}

/* Remove Button */
.wl-remove-btn {
  position: absolute;
  top: 14px;
  right: 14px;
  width: 34px;
  height: 34px;
  border-radius: 50%;
  background: rgba(13, 13, 13, 0.75);
  backdrop-filter: blur(8px);
  border: 1px solid rgba(255, 255, 255, 0.2);
  color: #ff6b6b;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  z-index: 3;
  transition: all 0.22s ease;
}
.wl-remove-btn:hover {
  background: #ff5252;
  color: #fff;
  border-color: #ff5252;
  transform: scale(1.12);
  box-shadow: 0 4px 12px rgba(255, 82, 82, 0.4);
}
.wl-remove-btn svg {
  width: 15px;
  height: 15px;
}

/* Card Body */
.wl-card-body {
  padding: 16px 18px 18px;
  display: flex;
  flex-direction: column;
  flex: 1;
}
.wl-card-title {
  font-family: 'Cormorant Garamond', Georgia, serif;
  font-size: 20px;
  font-weight: 600;
  color: #ffffff;
  line-height: 1.25;
  margin: 0 0 4px;
  text-decoration: none;
  display: -webkit-box;
  -webkit-line-clamp: 1;
  -webkit-box-orient: vertical;
  overflow: hidden;
  transition: color 0.2s ease;
}
.wl-card-title:hover {
  color: var(--wl-gold-light);
}
.wl-card-loc {
  font-family: 'Jost', sans-serif;
  font-size: 12.5px;
  color: var(--wl-text-muted);
  display: flex;
  align-items: center;
  gap: 6px;
  margin-bottom: 14px;
}
.wl-card-loc svg {
  color: var(--wl-gold);
  flex-shrink: 0;
}
.wl-card-divider {
  height: 1px;
  background: rgba(255, 255, 255, 0.08);
  margin-top: auto;
  margin-bottom: 14px;
}

/* Card Footer */
.wl-card-footer {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
}
.wl-price-group {
  display: flex;
  flex-direction: column;
}
.wl-price-caption {
  font-family: 'Jost', sans-serif;
  font-size: 10.5px;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  color: var(--wl-text-dim);
}
.wl-price-value {
  font-family: 'Jost', sans-serif;
  font-size: 16px;
  font-weight: 700;
  color: var(--wl-gold-light);
  line-height: 1.2;
}
.wl-view-btn {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 7px 16px;
  border-radius: 100px;
  border: 1px solid var(--wl-border-gold);
  background: rgba(201, 168, 76, 0.08);
  color: var(--wl-gold-light);
  font-family: 'Jost', sans-serif;
  font-size: 11.5px;
  font-weight: 600;
  letter-spacing: 0.06em;
  text-transform: uppercase;
  text-decoration: none;
  transition: all 0.25s ease;
  white-space: nowrap;
}
.wl-view-btn:hover {
  background: var(--wl-gold);
  border-color: var(--wl-gold);
  color: #0d0d0d;
  transform: translateY(-1px);
}

/* ===== EMPTY STATE ===== */
.wl-empty {
  display: none;
  background: #141414;
  border: 1px solid var(--wl-border);
  border-radius: 20px;
  padding: 64px 32px;
  text-align: center;
  max-width: 720px;
  margin: 20px auto 40px;
  box-shadow: 0 16px 40px rgba(0, 0, 0, 0.5);
}
.wl-empty-icon {
  width: 80px;
  height: 80px;
  border-radius: 50%;
  background: rgba(201, 168, 76, 0.08);
  border: 1px solid rgba(201, 168, 76, 0.25);
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 24px;
  color: var(--wl-gold);
  box-shadow: 0 0 30px rgba(201, 168, 76, 0.1);
}
.wl-empty-icon svg {
  width: 36px;
  height: 36px;
}
.wl-empty-title {
  font-family: 'Cormorant Garamond', Georgia, serif;
  font-size: 32px;
  font-weight: 600;
  color: #fff;
  margin: 0 0 12px;
}
.wl-empty-sub {
  font-family: 'Jost', sans-serif;
  font-size: 14.5px;
  color: var(--wl-text-muted);
  max-width: 480px;
  margin: 0 auto 28px;
  line-height: 1.6;
  font-weight: 300;
}
.wl-empty-cta {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 12px 28px;
  border-radius: 100px;
  background: var(--wl-gold);
  color: #0d0d0d;
  font-family: 'Jost', sans-serif;
  font-size: 13px;
  font-weight: 700;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  text-decoration: none;
  transition: all 0.25s ease;
}
.wl-empty-cta:hover {
  background: var(--wl-gold-light);
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(201, 168, 76, 0.35);
}

/* Quick Destination Chips */
.wl-quick-destinations {
  margin-top: 36px;
  padding-top: 24px;
  border-top: 1px solid rgba(255, 255, 255, 0.06);
}
.wl-dest-caption {
  font-family: 'Jost', sans-serif;
  font-size: 12px;
  text-transform: uppercase;
  letter-spacing: 0.1em;
  color: var(--wl-text-dim);
  margin-bottom: 12px;
}
.wl-dest-chips {
  display: flex;
  flex-wrap: wrap;
  justify-content: center;
  gap: 8px;
}
.wl-dest-chip {
  background: rgba(255, 255, 255, 0.04);
  border: 1px solid rgba(255, 255, 255, 0.1);
  color: var(--wl-text-muted);
  padding: 6px 14px;
  border-radius: 100px;
  font-family: 'Jost', sans-serif;
  font-size: 12.5px;
  text-decoration: none;
  transition: all 0.2s ease;
}
.wl-dest-chip:hover {
  border-color: var(--wl-gold);
  color: var(--wl-gold-light);
  background: rgba(201, 168, 76, 0.1);
}

/* ===== INSPIRATION / RECOMMENDED SECTION ===== */
.wl-recom-section {
  margin-top: 48px;
  padding-top: 36px;
  border-top: 1px solid var(--wl-border);
}
.wl-recom-header {
  text-align: center;
  margin-bottom: 28px;
}
.wl-recom-title {
  font-family: 'Cormorant Garamond', Georgia, serif;
  font-size: 30px;
  font-weight: 500;
  color: #fff;
  margin: 0 0 6px;
}
.wl-recom-title span {
  font-style: italic;
  color: var(--wl-gold-light);
}
.wl-recom-sub {
  font-family: 'Jost', sans-serif;
  font-size: 13.5px;
  color: var(--wl-text-muted);
  font-weight: 300;
}

/* Clear Confirmation Modal */
.wl-modal-backdrop {
  display: none;
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.85);
  backdrop-filter: blur(8px);
  z-index: 9999;
  align-items: center;
  justify-content: center;
  padding: 20px;
}
.wl-modal-backdrop.open {
  display: flex;
}
.wl-modal-card {
  background: #161616;
  border: 1px solid rgba(201, 168, 76, 0.35);
  border-radius: 18px;
  padding: 32px 28px;
  max-width: 440px;
  width: 100%;
  text-align: center;
  box-shadow: 0 20px 50px rgba(0, 0, 0, 0.8), 0 0 30px rgba(201, 168, 76, 0.1);
  animation: wlModalIn 0.25s ease-out;
}
@keyframes wlModalIn {
  from { opacity: 0; transform: scale(0.95); }
  to { opacity: 1; transform: scale(1); }
}
.wl-modal-title {
  font-family: 'Cormorant Garamond', Georgia, serif;
  font-size: 26px;
  font-weight: 600;
  color: #fff;
  margin: 0 0 10px;
}
.wl-modal-desc {
  font-family: 'Jost', sans-serif;
  font-size: 14px;
  color: var(--wl-text-muted);
  margin-bottom: 24px;
  line-height: 1.5;
}
.wl-modal-btns {
  display: flex;
  gap: 12px;
  justify-content: center;
}
.wl-modal-btn-cancel {
  background: transparent;
  border: 1px solid rgba(255, 255, 255, 0.2);
  color: #fff;
  padding: 10px 20px;
  border-radius: 100px;
  font-family: 'Jost', sans-serif;
  font-size: 13px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
}
.wl-modal-btn-cancel:hover {
  background: rgba(255, 255, 255, 0.08);
}
.wl-modal-btn-confirm {
  background: #ff5252;
  border: 1px solid #ff5252;
  color: #fff;
  padding: 10px 22px;
  border-radius: 100px;
  font-family: 'Jost', sans-serif;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
}
.wl-modal-btn-confirm:hover {
  background: #e03838;
}

/* Recommendation cards wishlist heart */
.htl-heart {
  position: absolute; top: 14px; right: 14px; z-index: 7;
  width: 34px; height: 34px; border-radius: 50%;
  background: rgba(0, 0, 0, 0.65); backdrop-filter: blur(6px);
  display: inline-flex; align-items: center; justify-content: center;
  cursor: pointer; border: 1px solid rgba(255, 255, 255, 0.2);
  transition: all 0.28s ease; color: #fff;
  padding: 0 !important; margin: 0; line-height: 0;
  box-sizing: border-box;
}
.htl-heart svg {
  display: block;
  transform: translateY(1px);
  transition: stroke 0.2s ease, fill 0.2s ease;
}
.htl-heart:hover {
  background: rgba(0, 0, 0, 0.8) !important;
  border-color: var(--wl-gold) !important;
  color: var(--wl-gold) !important;
  transform: scale(1.12);
  box-shadow: 0 0 16px rgba(201, 168, 76, 0.35);
}
.htl-heart:hover svg {
  stroke: var(--wl-gold);
}
.htl-heart.active {
  background: rgba(0, 0, 0, 0.75) !important;
  border-color: var(--wl-gold) !important;
  color: var(--wl-gold) !important;
  box-shadow: 0 0 16px rgba(201, 168, 76, 0.4);
}
.htl-heart.active svg {
  fill: var(--wl-gold) !important;
  stroke: var(--wl-gold) !important;
}
</style>
@endpush

@section('content')
<div class="wl-page-wrapper">

  <!-- ================= HERO ================= -->
  <section class="wl-hero">
    <div class="container">
      <div class="wl-hero-inner">
        <nav class="wl-breadcrumb" aria-label="Breadcrumb">
          <a href="{{ route('home') }}">Home</a>
          <span>&rsaquo;</span>
          <a href="{{ route('hotels') }}">Hotels</a>
          <span>&rsaquo;</span>
          <span>Wishlist</span>
        </nav>
        <span class="wl-kicker">Private Collection</span>
        <h1 class="wl-hero-title">Your Curated <span>Wishlist</span></h1>
        <p class="wl-hero-sub">
          Handpicked luxury sanctuaries, bespoke holiday packages, and flight experiences saved for your upcoming journeys.
        </p>
      </div>
    </div>
  </section>

  <!-- ================= MAIN SECTION ================= -->
  <section class="wl-content-section">
    <div class="container">
      <div class="wl-content-inner">

    <!-- Category Tabs -->
    <div class="wl-category-nav">
      <div class="wl-category-tabs" role="tablist" aria-label="Wishlist Categories">
        <button type="button" class="wl-tab-btn active" role="tab" id="wlTabHotels" aria-selected="true" onclick="switchWishlistTab('hotel')">
          <svg class="wl-tab-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M3 21h18M3 7v14M21 7v14M6 11h2M6 15h2M11 11h2M11 15h2M16 11h2M16 15h2M9 3h6v4H9z"/>
          </svg>
          <span>Hotels</span>
          <span class="wl-tab-badge" id="wlTabBadgeHotels">0</span>
        </button>

        <button type="button" class="wl-tab-btn" role="tab" id="wlTabPackages" aria-selected="false" onclick="switchWishlistTab('package')">
          <svg class="wl-tab-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <rect x="2" y="7" width="20" height="14" rx="2" ry="2"/>
            <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>
          </svg>
          <span>Packages</span>
          <span class="wl-tab-badge" id="wlTabBadgePackages">0</span>
        </button>

        <button type="button" class="wl-tab-btn" role="tab" id="wlTabFlights" aria-selected="false" onclick="switchWishlistTab('flight')">
          <svg class="wl-tab-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M17.8 19.2 16 11l3.5-3.5C21 6 21.5 4 21 3c-1-.5-3 0-4.5 1.5L13 8 4.8 6.2c-.5-.1-.9.1-1.1.5l-.3.5c-.2.5-.1 1 .3 1.3L9 12l-2 3H4l-1 1 3 2 2 3 1-1v-3l3-2 3.5 5.3c.3.4.8.5 1.3.3l.5-.3c.4-.2.6-.6.5-1.1z"/>
          </svg>
          <span>Flights</span>
          <span class="wl-tab-badge" id="wlTabBadgeFlights">0</span>
        </button>
      </div>
    </div>

    <!-- Action Toolbar (Active when items exist) -->
    <div class="wl-toolbar" id="wlToolbar">
      <div class="wl-stats">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="var(--wl-gold)" stroke="currentColor" stroke-width="1.5">
          <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
        </svg>
        <span id="wlStatsLabel">Saved Properties:</span>
        <span class="wl-stats-badge" id="wlStatsCount">0</span>
      </div>

      <div class="wl-actions">
        <button type="button" class="wl-btn-clear" id="wlClearBtn" onclick="openClearModal()">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <polyline points="3 6 5 6 21 6"></polyline>
            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
          </svg>
          <span id="wlClearBtnText">Clear Hotels</span>
        </button>

        <a href="{{ route('hotels') }}" class="wl-btn-explore" id="wlExploreBtn">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="11" cy="11" r="8"></circle>
            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
          </svg>
          <span id="wlExploreBtnText">Explore More Hotels</span>
        </a>
      </div>
    </div>

    <!-- Wishlist Cards Grid -->
    <div class="wl-grid" id="wlGrid">
      {{-- Dynamically populated via tytWishlist in JS --}}
    </div>

    <!-- Empty State -->
    <div class="wl-empty" id="wlEmptyState">
      <div class="wl-empty-icon" id="wlEmptyIcon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
          <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
        </svg>
      </div>
      <h2 class="wl-empty-title" id="wlEmptyTitle">Your Hotel Wishlist is Empty</h2>
      <p class="wl-empty-sub" id="wlEmptySub">
        You haven't saved any sanctuaries yet. As you browse our curated hotels and suites, tap the heart icon to curate your personal collection of dream stays.
      </p>
      <a href="{{ route('hotels') }}" class="wl-empty-cta" id="wlEmptyCta">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
          <circle cx="11" cy="11" r="8"></circle>
          <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
        </svg>
        <span id="wlEmptyCtaText">Discover Luxury Hotels</span>
      </a>

      <div class="wl-quick-destinations" id="wlEmptySuggestions">
        @if(isset($destinations) && $destinations->isNotEmpty())
        <div class="wl-dest-caption" id="wlEmptySuggCaption">Popular Luxury Destinations</div>
        <div class="wl-dest-chips" id="wlEmptyChips">
          @foreach($destinations as $dest)
            <a href="{{ route('hotels', ['destination' => $dest->name]) }}" class="wl-dest-chip">
              {{ $dest->name }}
            </a>
          @endforeach
        </div>
        @endif
      </div>
    </div>

    <!-- Recommended Stays Section (Always provides luxurious inspiration) -->
    @if(isset($featuredHotels) && $featuredHotels->isNotEmpty())
    <section class="wl-recom-section" id="wlRecomSection">
      <div class="wl-recom-header">
        <h2 class="wl-recom-title">Handpicked <span>Inspirations</span></h2>
        <p class="wl-recom-sub">Distinguished properties favored by TYT Luxe guests.</p>
      </div>

      <div class="wl-grid">
        @foreach($featuredHotels as $feat)
          @php
            $featImg = $feat->featured_image ?: ($feat->images->first()?->image_url ?? '');
            $featDest = $feat->destination?->name ?? $feat->locality ?? $feat->city ?? 'India';
            $featPrice = $feat->price_from ? '₹'.number_format($feat->price_from) : 'Price on Request';
            $featStars = (int) ($feat->star_rating ?? 5);
          @endphp
          <div class="wl-card">
            <div class="wl-card-media">
              @if($featImg)
                <img src="{{ $featImg }}" alt="{{ $feat->title }}" class="wl-card-img" loading="lazy" />
              @else
                <div style="width:100%; height:100%; background: #1c1c1c; display:flex; align-items:center; justify-content:center; color:rgba(255,255,255,0.25); font-size:12px; text-transform:uppercase;">Luxury Stay</div>
              @endif
              <div class="wl-card-gradient"></div>
              
              <div class="wl-rating-pill">
                ★ {{ $featStars }}-Star
              </div>

              {{-- Heart button on recommendation card --}}
              <button
                class="htl-heart js-wishlist-btn"
                type="button"
                aria-label="Save to wishlist"
                data-hotel-id="{{ $feat->id }}"
                data-hotel-slug="{{ $feat->slug }}"
                data-hotel-title="{{ $feat->title }}"
                data-hotel-image="{{ $featImg }}"
                data-hotel-destination="{{ $featDest }}"
                data-hotel-stars="{{ $featStars }}"
                data-hotel-price="{{ $featPrice }}"
                data-hotel-url="{{ route('hotel.details', $feat->slug) }}"
                onclick="tytWishlist.toggleFromButton(this, event);"
                style="top:14px; right:14px; width:34px; height:34px; background:rgba(0,0,0,0.65); backdrop-filter:blur(6px);">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                </svg>
              </button>
            </div>

            <div class="wl-card-body">
              <a href="{{ route('hotel.details', $feat->slug) }}" class="wl-card-title">{{ $feat->title }}</a>
              <div class="wl-card-loc">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
                {{ $featDest }}
              </div>
              <div class="wl-card-divider"></div>
              <div class="wl-card-footer">
                <div class="wl-price-group">
                  <span class="wl-price-caption">Starting From</span>
                  <span class="wl-price-value">{{ $featPrice }}</span>
                </div>
                <a href="{{ route('hotel.details', $feat->slug) }}" class="wl-view-btn">
                  View Stay
                </a>
              </div>
            </div>
          </div>
        @endforeach
      </div>
    </section>
    @endif

      </div>
    </div>
  </section>
</div>

<!-- Clear Confirmation Modal -->
<div class="wl-modal-backdrop" id="wlClearModal" role="dialog" aria-modal="true" aria-labelledby="wlModalTitle">
  <div class="wl-modal-card">
    <h3 class="wl-modal-title" id="wlModalTitle">Clear Saved Hotels?</h3>
    <p class="wl-modal-desc" id="wlModalDesc">
      Are you sure you want to remove all saved hotels from your wishlist? This action cannot be undone.
    </p>
    <div class="wl-modal-btns">
      <button type="button" class="wl-modal-btn-cancel" onclick="closeClearModal()">Cancel</button>
      <button type="button" class="wl-modal-btn-confirm" id="wlModalConfirmBtn" onclick="confirmClearWishlist()">Yes, Clear Hotels</button>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
  var currentTab = 'hotel';

  function initTabFromHash() {
    var hash = (window.location.hash || '').toLowerCase().replace('#', '');
    if (hash === 'packages' || hash === 'package') {
      currentTab = 'package';
    } else if (hash === 'flights' || hash === 'flight') {
      currentTab = 'flight';
    } else {
      currentTab = 'hotel';
    }
  }

  function switchWishlistTab(type) {
    currentTab = type;
    var targetHash = '#' + (type === 'package' ? 'packages' : (type === 'flight' ? 'flights' : 'hotels'));
    if (window.history && window.history.replaceState) {
      window.history.replaceState(null, null, targetHash);
    } else {
      window.location.hash = targetHash;
    }
    renderWishlistPage();
  }

  function renderWishlistPage() {
    if (typeof window.tytWishlist === 'undefined') return;

    var counts = window.tytWishlist.counts();
    var badgeH = document.getElementById('wlTabBadgeHotels');
    var badgeP = document.getElementById('wlTabBadgePackages');
    var badgeF = document.getElementById('wlTabBadgeFlights');
    if (badgeH) badgeH.textContent = counts.hotel;
    if (badgeP) badgeP.textContent = counts.package;
    if (badgeF) badgeF.textContent = counts.flight;

    // Update active tab buttons
    ['hotel', 'package', 'flight'].forEach(function(t) {
      var cap = t.charAt(0).toUpperCase() + t.slice(1);
      var btn = document.getElementById('wlTab' + cap + 's');
      if (btn) {
        if (t === currentTab) {
          btn.classList.add('active');
          btn.setAttribute('aria-selected', 'true');
        } else {
          btn.classList.remove('active');
          btn.setAttribute('aria-selected', 'false');
        }
      }
    });

    var items = window.tytWishlist.getByType(currentTab);
    var count = items.length;

    var grid = document.getElementById('wlGrid');
    var empty = document.getElementById('wlEmptyState');
    var toolbar = document.getElementById('wlToolbar');
    var statsLabel = document.getElementById('wlStatsLabel');
    var statsCount = document.getElementById('wlStatsCount');
    var clearBtnText = document.getElementById('wlClearBtnText');
    var exploreBtn = document.getElementById('wlExploreBtn');
    var exploreBtnText = document.getElementById('wlExploreBtnText');
    var modalTitle = document.getElementById('wlModalTitle');
    var modalDesc = document.getElementById('wlModalDesc');
    var modalConfirmBtn = document.getElementById('wlModalConfirmBtn');

    // Update toolbar text based on category
    if (currentTab === 'package') {
      if (statsLabel) statsLabel.textContent = 'Saved Packages:';
      if (clearBtnText) clearBtnText.textContent = 'Clear Packages';
      if (exploreBtn) exploreBtn.href = "{{ route('packages') }}";
      if (exploreBtnText) exploreBtnText.textContent = 'Explore Packages';
      if (modalTitle) modalTitle.textContent = 'Clear Saved Packages?';
      if (modalDesc) modalDesc.textContent = 'Are you sure you want to remove all saved packages from your wishlist?';
      if (modalConfirmBtn) modalConfirmBtn.textContent = 'Yes, Clear Packages';
    } else if (currentTab === 'flight') {
      if (statsLabel) statsLabel.textContent = 'Saved Flight Experiences:';
      if (clearBtnText) clearBtnText.textContent = 'Clear Flights';
      if (exploreBtn) exploreBtn.href = "{{ route('flights') }}";
      if (exploreBtnText) exploreBtnText.textContent = 'Explore Flights';
      if (modalTitle) modalTitle.textContent = 'Clear Saved Flights?';
      if (modalDesc) modalDesc.textContent = 'Are you sure you want to remove all saved flights from your wishlist?';
      if (modalConfirmBtn) modalConfirmBtn.textContent = 'Yes, Clear Flights';
    } else {
      if (statsLabel) statsLabel.textContent = 'Saved Properties:';
      if (clearBtnText) clearBtnText.textContent = 'Clear Hotels';
      if (exploreBtn) exploreBtn.href = "{{ route('hotels') }}";
      if (exploreBtnText) exploreBtnText.textContent = 'Explore More Hotels';
      if (modalTitle) modalTitle.textContent = 'Clear Saved Hotels?';
      if (modalDesc) modalDesc.textContent = 'Are you sure you want to remove all saved hotels from your wishlist?';
      if (modalConfirmBtn) modalConfirmBtn.textContent = 'Yes, Clear Hotels';
    }

    if (statsCount) statsCount.textContent = count;

    if (count === 0) {
      if (grid) {
        grid.style.display = 'none';
        grid.innerHTML = '';
      }
      if (toolbar) toolbar.style.display = 'none';
      if (empty) {
        empty.style.display = 'block';
        updateEmptyState(currentTab);
      }
      return;
    }

    if (grid) grid.style.display = 'grid';
    if (toolbar) toolbar.style.display = 'flex';
    if (empty) empty.style.display = 'none';

    if (grid) {
      grid.innerHTML = items.map(function(item) {
        var identifier = item.slug || item.id;
        var title = item.title || 'Luxury Journey';
        var dest = item.destination || (currentTab === 'flight' ? 'All Routes' : 'India');
        var price = item.price || (currentTab === 'flight' ? 'Best Fare on Enquiry' : 'Price on Request');
        var img = item.image || '';
        var itemType = window.tytWishlist.getItemType(item);

        var url = item.url;
        if (!url) {
          if (itemType === 'package') {
            url = item.slug ? '/packages/' + item.slug : '/packages';
          } else if (itemType === 'flight') {
            url = '/flights#tyt-book-flight';
          } else {
            url = item.slug ? '/hotels/' + item.slug : '/hotels';
          }
        }

        var badgeHtml = '';
        var pinIconSvg = '';
        var priceCaption = 'Starting From';
        var actionBtnText = 'View Stay';

        if (itemType === 'package') {
          var badgeText = item.badge || 'Holiday Package';
          badgeHtml = '<div class="wl-rating-pill" style="color:var(--wl-gold-light); font-weight:600;"><span style="color:var(--wl-gold)">✦</span> ' + badgeText + '</div>';
          pinIconSvg = '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>';
          priceCaption = 'Package From';
          actionBtnText = 'View Package';
        } else if (itemType === 'flight') {
          var fBadgeText = item.badge || 'Flight Experience';
          badgeHtml = '<div class="wl-rating-pill" style="color:var(--wl-gold-light); font-weight:600;"><span style="color:var(--wl-gold)">✈</span> ' + fBadgeText + '</div>';
          pinIconSvg = '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.8 19.2 16 11l3.5-3.5C21 6 21.5 4 21 3c-1-.5-3 0-4.5 1.5L13 8 4.8 6.2c-.5-.1-.9.1-1.1.5l-.3.5c-.2.5-.1 1 .3 1.3L9 12l-2 3H4l-1 1 3 2 2 3 1-1v-3l3-2 3.5 5.3c.3.4.8.5 1.3.3l.5-.3c.4-.2.6-.6.5-1.1z"/></svg>';
          priceCaption = 'Fare Estimate';
          actionBtnText = 'Enquire Flight';
        } else {
          var stars = item.stars || 5;
          badgeHtml = '<div class="wl-rating-pill">★ ' + stars + '-Star</div>';
          pinIconSvg = '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>';
          priceCaption = 'Starting From';
          actionBtnText = 'View Stay';
        }

        var imgHtml = img ?
          '<img src="' + img + '" alt="' + title + '" class="wl-card-img" loading="lazy" />' :
          '<div style="width:100%; height:100%; background:#1c1c1c; display:flex; align-items:center; justify-content:center; color:rgba(255,255,255,0.25); font-size:12px; text-transform:uppercase;">' + title + '</div>';

        return `
          <div class="wl-card" id="wl-item-${identifier}">
            <div class="wl-card-media">
              ${imgHtml}
              <div class="wl-card-gradient"></div>

              ${badgeHtml}

              <button type="button"
                class="wl-remove-btn"
                aria-label="Remove ${title} from wishlist"
                title="Remove from wishlist"
                onclick="removeFromWishlistPage('${identifier}', event)">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                  <line x1="18" y1="6" x2="6" y2="18"></line>
                  <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
              </button>
            </div>

            <div class="wl-card-body">
              <a href="${url}" class="wl-card-title">${title}</a>
              <div class="wl-card-loc">
                ${pinIconSvg}
                <span>${dest}</span>
              </div>

              <div class="wl-card-divider"></div>

              <div class="wl-card-footer">
                <div class="wl-price-group">
                  <span class="wl-price-caption">${priceCaption}</span>
                  <span class="wl-price-value">${price}</span>
                </div>
                <a href="${url}" class="wl-view-btn">
                  ${actionBtnText}
                </a>
              </div>
            </div>
          </div>
        `;
      }).join('');
    }
  }

  function updateEmptyState(type) {
    var emptyIcon = document.getElementById('wlEmptyIcon');
    var emptyTitle = document.getElementById('wlEmptyTitle');
    var emptySub = document.getElementById('wlEmptySub');
    var emptyCta = document.getElementById('wlEmptyCta');
    var emptyCtaText = document.getElementById('wlEmptyCtaText');
    var emptySugg = document.getElementById('wlEmptySuggestions');

    if (type === 'package') {
      if (emptyIcon) {
        emptyIcon.innerHTML = '<svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>';
      }
      if (emptyTitle) emptyTitle.textContent = 'No Saved Holiday Packages';
      if (emptySub) emptySub.textContent = "You haven't saved any vacation packages yet. Browse our handpicked itineraries across domestic sanctuaries and exotic global destinations to curate your dream journey.";
      if (emptyCta) emptyCta.href = "{{ route('packages') }}";
      if (emptyCtaText) emptyCtaText.textContent = 'Explore Holiday Packages';
      if (emptySugg) emptySugg.style.display = 'none';
    } else if (type === 'flight') {
      if (emptyIcon) {
        emptyIcon.innerHTML = '<svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M17.8 19.2 16 11l3.5-3.5C21 6 21.5 4 21 3c-1-.5-3 0-4.5 1.5L13 8 4.8 6.2c-.5-.1-.9.1-1.1.5l-.3.5c-.2.5-.1 1 .3 1.3L9 12l-2 3H4l-1 1 3 2 2 3 1-1v-3l3-2 3.5 5.3c.3.4.8.5 1.3.3l.5-.3c.4-.2.6-.6.5-1.1z"/></svg>';
      }
      if (emptyTitle) emptyTitle.textContent = 'No Saved Flight Experiences';
      if (emptySub) emptySub.textContent = "You haven't saved any flight routes yet. Explore our domestic, international, business class, and private charter experiences and save routes for your next travel enquiry.";
      if (emptyCta) emptyCta.href = "{{ route('flights') }}";
      if (emptyCtaText) emptyCtaText.textContent = 'Explore Flight Experiences';
      if (emptySugg) emptySugg.style.display = 'none';
    } else {
      if (emptyIcon) {
        emptyIcon.innerHTML = '<svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>';
      }
      if (emptyTitle) emptyTitle.textContent = 'Your Hotel Wishlist is Empty';
      if (emptySub) emptySub.textContent = "You haven't saved any sanctuaries yet. As you browse our curated hotels and suites, tap the heart icon to curate your personal collection of dream stays.";
      if (emptyCta) emptyCta.href = "{{ route('hotels') }}";
      if (emptyCtaText) emptyCtaText.textContent = 'Discover Luxury Hotels';
      if (emptySugg) emptySugg.style.display = 'block';
    }
  }

  function removeFromWishlistPage(identifier, e) {
    if (e) {
      e.preventDefault();
      e.stopPropagation();
    }
    var card = document.getElementById('wl-item-' + identifier);
    if (card) {
      card.classList.add('removing');
      setTimeout(function() {
        if (typeof window.tytWishlist !== 'undefined') {
          window.tytWishlist.remove(identifier);
        }
        renderWishlistPage();
      }, 250);
    } else {
      if (typeof window.tytWishlist !== 'undefined') {
        window.tytWishlist.remove(identifier);
      }
      renderWishlistPage();
    }
  }

  function openClearModal() {
    var modal = document.getElementById('wlClearModal');
    if (modal) modal.classList.add('open');
  }

  function closeClearModal() {
    var modal = document.getElementById('wlClearModal');
    if (modal) modal.classList.remove('open');
  }

  function confirmClearWishlist() {
    if (typeof window.tytWishlist !== 'undefined') {
      window.tytWishlist.clearCategory(currentTab);
    }
    closeClearModal();
    renderWishlistPage();
  }

  // Close modal when clicking outside
  document.addEventListener('click', function(e) {
    var modal = document.getElementById('wlClearModal');
    if (modal && e.target === modal) {
      closeClearModal();
    }
  });

  // Listen for storage / custom updates
  window.addEventListener('tyt:wishlist-updated', function() {
    renderWishlistPage();
  });

  // Listen for hash change in case of browser back/forward
  window.addEventListener('hashchange', function() {
    initTabFromHash();
    renderWishlistPage();
  });

  document.addEventListener('DOMContentLoaded', function() {
    initTabFromHash();
    renderWishlistPage();
  });
</script>
@endpush
