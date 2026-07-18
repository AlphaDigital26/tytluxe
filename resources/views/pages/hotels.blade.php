@extends('layouts.app')

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com" />
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;1,400;1,600&family=Jost:wght@300;400;500;600&display=swap" rel="stylesheet" />

<style>
/* ===== RESET & VARIABLES ===== */
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
  --radius: 12px;
  --transition: 0.35s cubic-bezier(0.25, 0.46, 0.45, 0.94);
}

/* ===== HERO SLIDER ===== */
.htl-hero {
  position: relative;
  height: 88vh;
  min-height: 560px;
  display: flex;
  align-items: center;
  justify-content: center;
  text-align: center;
  overflow: hidden;
}

.htl-slide {
  position: absolute;
  inset: 0;
  background-size: cover;
  background-position: center;
  opacity: 0;
  transition: opacity 1.2s ease, transform 7s ease;
  transform: scale(1.06);
}
.htl-slide.active { opacity: 1; transform: scale(1); }
.htl-slide:nth-child(1) { background-image: url('https://images.unsplash.com/photo-1542314831-068cd1dbfeeb?w=1600&q=85'); }
.htl-slide:nth-child(2) { background-image: url('https://images.unsplash.com/photo-1571003123894-1f0594d2b5d9?w=1600&q=85'); }
.htl-slide:nth-child(3) { background-image: url('https://images.unsplash.com/photo-1564501049412-61c2a3083791?w=1600&q=85'); }
.htl-slide:nth-child(4) { background-image: url('https://images.unsplash.com/photo-1549294413-26f195200c16?w=1600&q=85'); }
.htl-slide:nth-child(5) { background-image: url('https://images.unsplash.com/photo-1520250497591-112f2f40a3f4?w=1600&q=85'); }

.htl-hero-overlay {
  position: absolute; inset: 0; z-index: 1;
  background: linear-gradient(180deg, rgba(13,13,13,0.25) 0%, rgba(13,13,13,0.55) 55%, rgba(13,13,13,0.92) 100%);
}

.htl-hero-content {
  position: relative; z-index: 2;
  max-width: 820px; padding: 0 24px;
  animation: htlFadeUp 1s ease 0.3s both;
}

@keyframes htlFadeUp {
  from { opacity: 0; transform: translateY(32px); }
  to   { opacity: 1; transform: translateY(0); }
}

.htl-hero-label {
  display: inline-block;
  font-family: 'Jost', sans-serif; font-size: 11px; font-weight: 600;
  letter-spacing: 0.22em; text-transform: uppercase; color: var(--gold);
  border: 1px solid var(--gold); padding: 6px 18px; border-radius: 100px; margin-bottom: 24px;
}
.htl-hero-title {
  font-family: 'Cormorant Garamond', serif;
  font-size: clamp(3rem, 7vw, 5.5rem); font-weight: 500; color: #fff;
  line-height: 1.08; margin-bottom: 20px; letter-spacing: -0.01em;
}
.htl-hero-title em { font-style: italic; color: var(--gold-light); }
.htl-hero-sub {
  font-family: 'Jost', sans-serif; font-size: 15px; font-weight: 300;
  color: var(--white-60); letter-spacing: 0.04em; margin-bottom: 36px;
  max-width: 500px; margin-left: auto; margin-right: auto;
}
.htl-hero-pills { display: flex; flex-wrap: wrap; gap: 10px; justify-content: center; }
.htl-hero-pills span {
  font-family: 'Jost', sans-serif; font-size: 12px; font-weight: 500;
  letter-spacing: 0.08em; padding: 7px 18px; border-radius: 100px;
  border: 1px solid var(--white-30); color: var(--white-60);
  text-transform: uppercase; background: var(--white-10);
}

.htl-slider-dots {
  position: absolute; bottom: 28px; left: 50%; transform: translateX(-50%);
  z-index: 3; display: flex; gap: 8px;
}
.htl-dot {
  width: 28px; height: 3px; border-radius: 2px; background: var(--white-30);
  cursor: pointer; transition: all 0.4s ease; border: none; padding: 0;
}
.htl-dot.active { background: var(--gold); width: 44px; }

.htl-arrow {
  position: absolute; top: 50%; transform: translateY(-50%); z-index: 3;
  width: 44px; height: 44px; border-radius: 50%;
  border: 1px solid var(--white-30); background: rgba(13,13,13,0.4);
  backdrop-filter: blur(8px); color: #fff; cursor: pointer;
  display: flex; align-items: center; justify-content: center;
  transition: all var(--transition); font-size: 18px; line-height: 1;
}
.htl-arrow:hover { border-color: var(--gold); background: rgba(201,168,76,0.2); }
.htl-arrow-prev { left: 24px; }
.htl-arrow-next { right: 24px; }

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

/* ===== FILTER TABS ===== */
.htl-search-wrap {
  max-width: 720px;
  margin: 0 auto 28px;
  display: grid;
  grid-template-columns: 1fr auto;
  gap: 10px;
  align-items: center;
  background: rgba(255,255,255,0.06);
  border: 1px solid rgba(255,255,255,0.12);
  border-radius: 100px;
  padding: 8px 8px 8px 22px;
}
.htl-search-wrap label {
  position: absolute;
  width: 1px;
  height: 1px;
  overflow: hidden;
}
.htl-search-wrap input {
  width: 100%;
  min-height: 42px;
  border: none;
  outline: none;
  background: transparent;
  color: #fff;
  font-family: 'Jost', sans-serif;
  font-size: 14px;
}
.htl-search-wrap input::placeholder { color: var(--white-60); }
.htl-search-pill {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-height: 42px;
  padding: 0 18px;
  border-radius: 100px;
  background: var(--gold);
  color: var(--dark);
  font-family: 'Jost', sans-serif;
  font-size: 12px;
  font-weight: 700;
  letter-spacing: .08em;
  text-transform: uppercase;
}
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
.htl-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 28px; }

/* ===== HOTEL CARD ===== */
.htl-card {
  background: var(--dark-3); border-radius: var(--radius); overflow: hidden;
  border: 1px solid rgba(255,255,255,0.06); transition: all var(--transition);
  display: flex; flex-direction: column;
  color: inherit; cursor: pointer;
}
.htl-card:hover {
  transform: translateY(-6px);
  border-color: rgba(201,168,76,0.35);
  box-shadow: 0 20px 48px rgba(0,0,0,0.5), 0 0 0 1px rgba(201,168,76,0.1);
}
.htl-card.htl-hidden { display: none; }

.htl-card-img { position: relative; height: 220px; overflow: hidden; }
.htl-card-img img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.6s ease; }
.htl-card:hover .htl-card-img img { transform: scale(1.07); }

.htl-badge {
  position: absolute; top: 14px; left: 14px; background: var(--gold); color: var(--dark);
  font-family: 'Jost', sans-serif; font-size: 10px; font-weight: 700;
  letter-spacing: 0.12em; text-transform: uppercase; padding: 4px 12px; border-radius: 100px;
}
.htl-loc-badge {
  position: absolute; bottom: 14px; left: 14px;
  background: rgba(13,13,13,0.82); backdrop-filter: blur(8px); color: var(--white-60);
  font-family: 'Jost', sans-serif; font-size: 11px; font-weight: 500;
  letter-spacing: 0.08em; padding: 5px 12px; border-radius: 100px;
}

.htl-card-body { padding: 22px 22px 20px; flex: 1; display: flex; flex-direction: column; }
.htl-card-name {
  font-family: 'Cormorant Garamond', serif; font-size: 1.3rem; font-weight: 600;
  line-height: 1.25; margin-bottom: 8px; color: #fff;
}
.htl-card-desc {
  font-family: 'Jost', sans-serif; font-size: 13px; color: var(--white-60);
  font-weight: 300; line-height: 1.6; flex: 1; margin-bottom: 18px;
}
.htl-card-meta { display: flex; gap: 14px; margin-bottom: 20px; flex-wrap: wrap; }
.htl-card-meta span {
  font-family: 'Jost', sans-serif; font-size: 11.5px; color: var(--white-60);
  display: flex; align-items: center; gap: 5px;
}
.htl-card-meta span::before {
  content: ''; width: 4px; height: 4px; border-radius: 50%; background: var(--gold); flex-shrink: 0;
}
.htl-card-footer {
  display: flex; align-items: center; justify-content: space-between;
  border-top: 1px solid rgba(255,255,255,0.07); padding-top: 16px; gap: 10px;
}
.htl-card-timing { font-family: 'Jost', sans-serif; font-size: 11px; color: rgba(255,255,255,0.3); }
.htl-book-btn {
  display: inline-flex; align-items: center; gap: 7px;
  background: var(--gold); color: var(--dark);
  font-family: 'Jost', sans-serif; font-size: 11.5px; font-weight: 700;
  letter-spacing: 0.1em; text-transform: uppercase; padding: 9px 20px; border-radius: 100px;
  text-decoration: none; transition: all var(--transition); white-space: nowrap; border: none; cursor: pointer;
}
.htl-book-btn:hover { background: var(--gold-light); }
.htl-book-btn svg { width: 12px; height: 12px; flex-shrink: 0; }

/* ===== MODAL ===== */
.htl-modal-backdrop {
  position: fixed; inset: 0; z-index: 9999;
  background: rgba(0,0,0,0.82); backdrop-filter: blur(6px);
  display: flex; align-items: center; justify-content: center;
  padding: 20px;
  opacity: 0; pointer-events: none; transition: opacity 0.35s ease;
}
.htl-modal-backdrop.open { opacity: 1; pointer-events: all; }

.htl-modal {
  background: var(--dark-3); border-radius: 16px;
  border: 1px solid rgba(201,168,76,0.25);
  max-width: 820px; width: 100%; max-height: 90vh; overflow-y: auto;
  transform: translateY(28px) scale(0.97);
  transition: transform 0.38s cubic-bezier(0.25,0.46,0.45,0.94);
  position: relative;
}
.htl-modal-backdrop.open .htl-modal { transform: translateY(0) scale(1); }

.htl-modal-img {
  width: 100%; height: 300px; object-fit: cover;
  border-radius: 16px 16px 0 0; display: block;
}

.htl-modal-close {
  position: absolute; top: 16px; right: 16px;
  width: 38px; height: 38px; border-radius: 50%;
  background: rgba(13,13,13,0.75); backdrop-filter: blur(8px);
  border: 1px solid rgba(255,255,255,0.15); color: #fff;
  font-size: 18px; cursor: pointer; display: flex; align-items: center; justify-content: center;
  transition: all var(--transition); z-index: 2;
}
.htl-modal-close:hover { border-color: var(--gold); color: var(--gold); }

.htl-modal-body { padding: 32px 36px 36px; }

.htl-modal-top { display: flex; align-items: flex-start; justify-content: space-between; gap: 16px; margin-bottom: 8px; flex-wrap: wrap; }

.htl-modal-badge {
  background: var(--gold); color: var(--dark);
  font-family: 'Jost', sans-serif; font-size: 10px; font-weight: 700;
  letter-spacing: 0.12em; text-transform: uppercase; padding: 4px 12px; border-radius: 100px;
}
.htl-modal-loc {
  font-family: 'Jost', sans-serif; font-size: 12px; color: var(--gold);
  letter-spacing: 0.08em; font-weight: 500;
}
.htl-modal-name {
  font-family: 'Cormorant Garamond', serif; font-size: clamp(1.6rem, 3vw, 2.2rem);
  font-weight: 600; color: #fff; margin-bottom: 12px; line-height: 1.2;
}
.htl-modal-desc {
  font-family: 'Jost', sans-serif; font-size: 14px; color: var(--white-60);
  font-weight: 300; line-height: 1.75; margin-bottom: 24px;
}

.htl-modal-details {
  display: grid; grid-template-columns: repeat(2, 1fr); gap: 14px; margin-bottom: 28px;
}
.htl-modal-detail-item {
  background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.07);
  border-radius: 10px; padding: 14px 18px;
}
.htl-modal-detail-item label {
  display: block; font-family: 'Jost', sans-serif; font-size: 10px; font-weight: 600;
  letter-spacing: 0.18em; text-transform: uppercase; color: var(--gold); margin-bottom: 5px;
}
.htl-modal-detail-item span {
  font-family: 'Jost', sans-serif; font-size: 13.5px; color: #fff; font-weight: 400;
}

.htl-modal-detail-wide { grid-column: 1 / -1; }
.htl-room-category-list {
  display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
  gap: 10px; margin-top: 8px;
}
.htl-room-category {
  display: flex; align-items: center; gap: 8px;
  padding: 10px 12px; border-radius: 8px;
  background: rgba(201,168,76,0.1); border: 1px solid rgba(201,168,76,0.24);
  font-family: 'Jost', sans-serif; font-size: 13px; color: #fff;
}
.htl-room-category::before {
  content: ''; width: 6px; height: 6px; border-radius: 50%;
  background: var(--gold); flex: 0 0 6px;
}
.htl-room-note {
  display: block; margin-top: 10px;
  font-family: 'Jost', sans-serif; font-size: 12px; color: var(--white-50);
}
.htl-modal-features { display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 28px; }
.htl-modal-feature {
  font-family: 'Jost', sans-serif; font-size: 11.5px; color: var(--white-60);
  border: 1px solid rgba(255,255,255,0.12); padding: 6px 14px; border-radius: 100px;
  display: flex; align-items: center; gap: 7px;
}
.htl-modal-feature::before {
  content: ''; width: 5px; height: 5px; border-radius: 50%; background: var(--gold); flex-shrink: 0;
}

.htl-modal-actions { display: flex; gap: 14px; flex-wrap: wrap; }

.htl-modal-enquire {
  display: inline-flex; align-items: center; gap: 8px;
  background: var(--gold); color: var(--dark);
  font-family: 'Jost', sans-serif; font-size: 12.5px; font-weight: 700;
  letter-spacing: 0.1em; text-transform: uppercase; padding: 14px 28px;
  border-radius: 100px; border: none; cursor: pointer; text-decoration: none;
  transition: all var(--transition);
}
.htl-modal-enquire:hover { background: var(--gold-light); transform: translateY(-2px); }

.htl-modal-wa {
  display: inline-flex; align-items: center; gap: 9px;
  background: #25D366; color: #fff;
  font-family: 'Jost', sans-serif; font-size: 12.5px; font-weight: 700;
  letter-spacing: 0.08em; text-transform: uppercase; padding: 14px 28px;
  border-radius: 100px; text-decoration: none; transition: all var(--transition);
}
.htl-modal-wa:hover { background: #20c45b; transform: translateY(-2px); }

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
.htl-enquiry-inner {
  max-width: 860px; margin: 0 auto;
}
.htl-enquiry-header { text-align: center; margin-bottom: 52px; }

.htl-enquiry-form {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 20px;
}
.htl-form-group { display: flex; flex-direction: column; gap: 8px; }
.htl-form-group.full { grid-column: 1 / -1; }

.htl-form-group label {
  font-family: 'Jost', sans-serif; font-size: 10.5px; font-weight: 600;
  letter-spacing: 0.18em; text-transform: uppercase; color: var(--gold);
}

.htl-form-group input,
.htl-form-group select,
.htl-form-group textarea {
  background: rgba(255,255,255,0.04);
  border: 1px solid rgba(255,255,255,0.1);
  border-radius: 10px;
  padding: 14px 18px;
  color: #fff;
  font-family: 'Jost', sans-serif; font-size: 14px; font-weight: 300;
  outline: none;
  transition: border-color var(--transition), background var(--transition);
  width: 100%; box-sizing: border-box;
  -webkit-appearance: none; appearance: none;
}
.htl-form-group select {
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23c9a84c' d='M6 8L1 3h10z'/%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: right 16px center;
  background-color: rgba(255,255,255,0.04);
  padding-right: 40px;
  cursor: pointer;
}
.htl-form-group select option { background: var(--dark-3); color: #fff; }
.htl-form-group textarea { resize: vertical; min-height: 120px; }

.htl-form-group input::placeholder,
.htl-form-group textarea::placeholder { color: rgba(255,255,255,0.25); }

.htl-form-group input:focus,
.htl-form-group select:focus,
.htl-form-group textarea:focus {
  border-color: var(--gold);
  background: rgba(201,168,76,0.05);
}

.htl-form-submit-row {
  grid-column: 1 / -1;
  display: flex; align-items: center; justify-content: space-between;
  gap: 20px; flex-wrap: wrap; margin-top: 8px;
}

.htl-form-note {
  font-family: 'Jost', sans-serif; font-size: 12px; color: rgba(255,255,255,0.3);
  font-weight: 300;
}

.htl-form-btn {
  display: inline-flex; align-items: center; gap: 10px;
  background: var(--gold); color: var(--dark);
  font-family: 'Jost', sans-serif; font-size: 13px; font-weight: 700;
  letter-spacing: 0.1em; text-transform: uppercase;
  padding: 16px 36px; border-radius: 100px; border: none; cursor: pointer;
  transition: all var(--transition); white-space: nowrap;
}
.htl-form-btn:hover { background: var(--gold-light); transform: translateY(-2px); box-shadow: 0 8px 24px rgba(201,168,76,0.3); }
.htl-form-btn svg { width: 15px; height: 15px; flex-shrink: 0; }

/* Form success */
.htl-form-success {
  display: none;
  text-align: center; padding: 48px 32px;
  background: rgba(201,168,76,0.06); border: 1px solid var(--gold-dim);
  border-radius: var(--radius);
}
.htl-form-success.show { display: block; }
.htl-form-success .htl-success-icon {
  font-size: 40px; margin-bottom: 16px;
}
.htl-form-success h3 {
  font-family: 'Cormorant Garamond', serif; font-size: 1.8rem; color: #fff;
  margin-bottom: 10px;
}
.htl-form-success p {
  font-family: 'Jost', sans-serif; font-size: 14px; color: var(--white-60); font-weight: 300;
}

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
  .htl-modal-details { grid-template-columns: 1fr 1fr; }
}
@media (max-width: 768px) {
  .htl-section { padding: 64px 20px; }
  .htl-grid { grid-template-columns: 1fr; }
  .htl-trust { padding: 16px 20px; }
  .htl-trust-inner { gap: 24px; }
  .htl-filter-tabs { gap: 6px; }
  .htl-tab { padding: 8px 16px; font-size: 11px; }
  .htl-banner-inner { padding: 48px 20px; }
  .htl-cta { padding: 56px 20px; }
  .htl-arrow { display: none; }
  .htl-modal-body { padding: 24px 20px 28px; }
  .htl-modal-details { grid-template-columns: 1fr; }
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


<!-- ======================================================
     HERO SLIDER
====================================================== -->
<section class="htl-hero" id="hotels-top">
  <div class="htl-slide active"></div>
  <div class="htl-slide"></div>
  <div class="htl-slide"></div>
  <div class="htl-slide"></div>
  <div class="htl-slide"></div>
  <div class="htl-hero-overlay"></div>
  <button class="htl-arrow htl-arrow-prev" aria-label="Previous">&#8592;</button>
  <button class="htl-arrow htl-arrow-next" aria-label="Next">&#8594;</button>
  <div class="htl-hero-content">
    <span class="htl-hero-label">Curated Collection</span>
    <h1 class="htl-hero-title">Handpicked Hotels &amp;<br><em>Luxury Stays</em></h1>
    <p class="htl-hero-sub">From Himalayan retreats to beachside escapes - every property personally vetted for comfort, luxury &amp; value.</p>
    <div class="htl-hero-pills">
      <span>Shimla</span><span>Manali</span><span>Goa</span>
      <span>Kasauli</span><span>Dalhousie</span><span>Mussoorie</span>
    </div>
  </div>
  <div class="htl-slider-dots">
    <button class="htl-dot active" data-slide="0"></button>
    <button class="htl-dot" data-slide="1"></button>
    <button class="htl-dot" data-slide="2"></button>
    <button class="htl-dot" data-slide="3"></button>
    <button class="htl-dot" data-slide="4"></button>
  </div>
</section>

<!-- ======================================================
     TRUST BAR
====================================================== -->
<div class="htl-trust">
  <div class="htl-trust-inner">
    <div class="htl-trust-item"><span class="ti">*</span> Zero Hidden Fees</div>
    <div class="htl-trust-item"><span class="ti">*</span> Best Rate Guarantee</div>
    <div class="htl-trust-item"><span class="ti">*</span> 24/7 Support</div>
    <div class="htl-trust-item"><span class="ti">*</span> Flexible Changes</div>
    <div class="htl-trust-item"><span class="ti">*</span> Expert Curation</div>
  </div>
</div>

<!-- ======================================================
     HOTEL GRID
====================================================== -->
<section class="htl-section" id="hotels">
  <div class="htl-section-inner">
    <div class="htl-section-header">
      <p class="htl-eyebrow">Our Collection</p>
      <h2 class="htl-title">Find Your Perfect <em>Stay</em></h2>
      <p class="htl-desc">Every hotel in our collection is handpicked for its exceptional service, location and experience.</p>
    </div>
    <div class="htl-divider"><span>*</span></div>

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

    <div class="htl-grid" id="htlGrid">

      <!-- 1: Snow Valley Resort, Shimla -->
      <div class="htl-card" data-category="shimla"
        data-name="Snow Valley Resort"
        data-badge="Popular"
        data-location="Shimla"
        data-img="placeholder.jpg"
        data-desc="Snow Valley Resort is a comfortable Shimla mountain stay suited for families, couples and leisure travellers who want resort-style facilities with convenient access to the hill city. It works well for guests looking for warm hospitality, restaurant access, room service and a practical base for sightseeing around Shimla. Check-in: 1:00 PM; Check-out: 11:00 AM."
        data-checkin="1:00 PM" data-checkout="11:00 AM"
        data-features="Shimla Mountain Resort,Family Friendly,Restaurant,Room Service,Sightseeing Base,Official Hotel"
        data-rooms="Standard Room; Deluxe Room; Executive Room; Suite. Exact availability and occupancy can be confirmed on enquiry."
        data-wa="I&#39;m interested in Snow Valley Resort, Shimla. Please share availability and rates.">
        <div class="htl-card-img">
          <img src="placeholder.jpg" alt="Snow Valley Resort, Shimla" loading="lazy" />
          <span class="htl-badge">Popular</span>
          <span class="htl-loc-badge">&#128205; Shimla</span>
        </div>
        <div class="htl-card-body">
          <h3 class="htl-card-name">Snow Valley Resort</h3>
          <p class="htl-card-desc">A full-service Shimla resort for mountain holidays, families and easy city access.</p>
          <div class="htl-card-meta"><span>Shimla Mountain Resort</span><span>Family Friendly</span></div>
          <div class="htl-card-footer">
            <span class="htl-card-timing">Check-in 1:00 PM &middot; Check-out 11:00 AM</span>
            <button class="htl-book-btn">View Details <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 8h10M9 4l4 4-4 4"/></svg></button>
          </div>
        </div>
      </div>
      <!-- 2: Snow Valley Heights, Shimla -->
      <div class="htl-card" data-category="shimla"
        data-name="Snow Valley Heights"
        data-badge="Luxury"
        data-location="Shimla"
        data-img="placeholder.jpg"
        data-desc="Snow Valley Heights is an elevated Snow Valley property in Shimla, positioned for travellers who prefer valley-facing surroundings and polished hill-station comfort. The hotel is suitable for relaxed leisure stays, family trips and guests who want a quieter mountain setting with essential hotel services. Check-in: 1:00 PM; Check-out: 11:00 AM."
        data-checkin="1:00 PM" data-checkout="11:00 AM"
        data-features="Valley Views,Premium Rooms,Shimla Stay,Restaurant,Room Service,Official Hotel"
        data-rooms="Standard Room; Deluxe Room; Executive Room; Suite. Exact availability and occupancy can be confirmed on enquiry."
        data-wa="I&#39;m interested in Snow Valley Heights, Shimla. Please share availability and rates.">
        <div class="htl-card-img">
          <img src="placeholder.jpg" alt="Snow Valley Heights, Shimla" loading="lazy" />
          <span class="htl-badge">Luxury</span>
          <span class="htl-loc-badge">&#128205; Shimla</span>
        </div>
        <div class="htl-card-body">
          <h3 class="htl-card-name">Snow Valley Heights</h3>
          <p class="htl-card-desc">An elevated Shimla stay with valley views and premium hill-station comfort.</p>
          <div class="htl-card-meta"><span>Valley Views</span><span>Premium Rooms</span></div>
          <div class="htl-card-footer">
            <span class="htl-card-timing">Check-in 1:00 PM &middot; Check-out 11:00 AM</span>
            <button class="htl-book-btn">View Details <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 8h10M9 4l4 4-4 4"/></svg></button>
          </div>
        </div>
      </div>
      <!-- 3: 8Fold by Larisa, Shimla -->
      <div class="htl-card" data-category="shimla"
        data-name="8Fold by Larisa"
        data-badge="Boutique"
        data-location="Shimla"
        data-img="placeholder.jpg"
        data-desc="8Fold by Larisa in Shimla is a boutique mountain stay from the Larisa collection, designed for guests who want a compact, stylish and calm base in the hills. It is best suited for couples, families and small groups looking for contemporary comfort in a curated Shimla property. Check-in: 2:00 PM; Check-out: 12:00 PM."
        data-checkin="2:00 PM" data-checkout="12:00 PM"
        data-features="Boutique Stay,Larisa Collection,Mountain Setting,Curated Rooms,Shimla,Official Hotel"
        data-rooms="Standard Room; Deluxe Room; Executive Room; Suite. Exact availability and occupancy can be confirmed on enquiry."
        data-wa="I&#39;m interested in 8Fold by Larisa, Shimla. Please share availability and rates.">
        <div class="htl-card-img">
          <img src="placeholder.jpg" alt="8Fold by Larisa, Shimla" loading="lazy" />
          <span class="htl-badge">Boutique</span>
          <span class="htl-loc-badge">&#128205; Shimla</span>
        </div>
        <div class="htl-card-body">
          <h3 class="htl-card-name">8Fold by Larisa</h3>
          <p class="htl-card-desc">A boutique Larisa stay in Shimla with a calm mountain setting.</p>
          <div class="htl-card-meta"><span>Boutique Stay</span><span>Larisa Collection</span></div>
          <div class="htl-card-footer">
            <span class="htl-card-timing">Check-in 2:00 PM &middot; Check-out 12:00 PM</span>
            <button class="htl-book-btn">View Details <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 8h10M9 4l4 4-4 4"/></svg></button>
          </div>
        </div>
      </div>
      <!-- 4: Kanishka, Manali - AM Hotel Kollection -->
      <div class="htl-card" data-category="manali"
        data-name="Kanishka - AM Hotel Kollection"
        data-badge="Boutique"
        data-location="Manali"
        data-img="placeholder.jpg"
        data-desc="Kanishka by AM Hotel Kollection is a Manali stay for travellers who want a comfortable hotel experience close to the hill-town atmosphere. It is suitable for family holidays, couples and leisure guests looking for a practical Manali base with hospitality support and easy access to local experiences."
        data-checkin="2:00 PM" data-checkout="12:00 PM"
        data-features="AM Hotel Kollection,Manali Stay,Family Rooms,Restaurant,Hill Views,Official Hotel"
        data-rooms="Standard Room; Deluxe Room; Executive Room; Suite. Exact availability and occupancy can be confirmed on enquiry."
        data-wa="I&#39;m interested in Kanishka, Manali - AM Hotel Kollection. Please share availability and rates.">
        <div class="htl-card-img">
          <img src="placeholder.jpg" alt="Kanishka, Manali - AM Hotel Kollection" loading="lazy" />
          <span class="htl-badge">Boutique</span>
          <span class="htl-loc-badge">&#128205; Manali</span>
        </div>
        <div class="htl-card-body">
          <h3 class="htl-card-name">Kanishka - AM Hotel Kollection</h3>
          <p class="htl-card-desc">A Manali hotel by AM Hotel Kollection for comfortable hill holidays.</p>
          <div class="htl-card-meta"><span>AM Hotel Kollection</span><span>Manali Stay</span></div>
          <div class="htl-card-footer">
            <span class="htl-card-timing">Check-in 2:00 PM &middot; Check-out 12:00 PM</span>
            <button class="htl-book-btn">View Details <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 8h10M9 4l4 4-4 4"/></svg></button>
          </div>
        </div>
      </div>
      <!-- 5: Larisa Resort, Manali -->
      <div class="htl-card" data-category="manali"
        data-name="Larisa Resort - Manali"
        data-badge="Luxury"
        data-location="Manali"
        data-img="placeholder.jpg"
        data-desc="Larisa Resort Manali is a luxury mountain resort with a refined, nature-led setting and a quieter resort atmosphere. It is ideal for travellers who want upgraded comfort, scenic surroundings and Larisa hospitality while staying in Manali."
        data-checkin="2:00 PM" data-checkout="12:00 PM"
        data-features="Deluxe Garden View,Luxury Suite,Larisa Collection,Fine Dining,Manali Resort,Official Hotel"
        data-rooms="Deluxe Garden View; Luxury Suite."
        data-wa="I&#39;m interested in Larisa Resort, Manali. Please share availability and rates.">
        <div class="htl-card-img">
          <img src="placeholder.jpg" alt="Larisa Resort, Manali" loading="lazy" />
          <span class="htl-badge">Luxury</span>
          <span class="htl-loc-badge">&#128205; Manali</span>
        </div>
        <div class="htl-card-body">
          <h3 class="htl-card-name">Larisa Resort - Manali</h3>
          <p class="htl-card-desc">A luxury Larisa resort in Manali with garden-view and suite options.</p>
          <div class="htl-card-meta"><span>Deluxe Garden View</span><span>Luxury Suite</span></div>
          <div class="htl-card-footer">
            <span class="htl-card-timing">Check-in 2:00 PM &middot; Check-out 12:00 PM</span>
            <button class="htl-book-btn">View Details <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 8h10M9 4l4 4-4 4"/></svg></button>
          </div>
        </div>
      </div>
      <!-- 6: Renest River Country Resort, Manali -->
      <div class="htl-card" data-category="manali"
        data-name="Renest River Country Resort"
        data-badge="Resort"
        data-location="Manali"
        data-img="placeholder.jpg"
        data-desc="Renest River Country Resort is a Manali resort designed for relaxed holidays, family breaks and guests who want resort facilities in a mountain destination. It is a good fit for travellers who prefer an easy stay with restaurant access, hospitality services and a comfortable base for Manali sightseeing."
        data-checkin="2:00 PM" data-checkout="12:00 PM"
        data-features="Renest Hotels,Mountain Resort,Family Friendly,Restaurant,Manali Stay,Official Hotel"
        data-rooms="Standard Room; Deluxe Room; Executive Room; Suite. Exact availability and occupancy can be confirmed on enquiry."
        data-wa="I&#39;m interested in Renest River Country Resort, Manali. Please share availability and rates.">
        <div class="htl-card-img">
          <img src="placeholder.jpg" alt="Renest River Country Resort, Manali" loading="lazy" />
          <span class="htl-badge">Resort</span>
          <span class="htl-loc-badge">&#128205; Manali</span>
        </div>
        <div class="htl-card-body">
          <h3 class="htl-card-name">Renest River Country Resort</h3>
          <p class="htl-card-desc">A relaxed Manali resort for mountain holidays and family getaways.</p>
          <div class="htl-card-meta"><span>Renest Hotels</span><span>Mountain Resort</span></div>
          <div class="htl-card-footer">
            <span class="htl-card-timing">Check-in 2:00 PM &middot; Check-out 12:00 PM</span>
            <button class="htl-book-btn">View Details <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 8h10M9 4l4 4-4 4"/></svg></button>
          </div>
        </div>
      </div>
      <!-- 7: Tiaraa Hotel & Resort, Manali -->
      <div class="htl-card" data-category="manali"
        data-name="Tiaraa Hotel &amp; Resort"
        data-badge="Premium"
        data-location="Manali"
        data-img="placeholder.jpg"
        data-desc="Tiaraa Hotel &amp; Resort Manali is a premium resort-style stay for travellers looking for polished hospitality, mountain scenery and a comfortable holiday base. It suits families, couples and leisure groups who want a more refined Manali resort experience."
        data-checkin="2:00 PM" data-checkout="12:00 PM"
        data-features="Premium Resort,Mountain Scenery,Restaurant,Family Friendly,Manali,Official Hotel"
        data-rooms="Standard Room; Deluxe Room; Executive Room; Suite. Exact availability and occupancy can be confirmed on enquiry."
        data-wa="I&#39;m interested in Tiaraa Hotel &amp; Resort, Manali. Please share availability and rates.">
        <div class="htl-card-img">
          <img src="placeholder.jpg" alt="Tiaraa Hotel &amp; Resort, Manali" loading="lazy" />
          <span class="htl-badge">Premium</span>
          <span class="htl-loc-badge">&#128205; Manali</span>
        </div>
        <div class="htl-card-body">
          <h3 class="htl-card-name">Tiaraa Hotel &amp; Resort</h3>
          <p class="htl-card-desc">A premium Manali hotel and resort with dramatic mountain surroundings.</p>
          <div class="htl-card-meta"><span>Premium Resort</span><span>Mountain Scenery</span></div>
          <div class="htl-card-footer">
            <span class="htl-card-timing">Check-in 2:00 PM &middot; Check-out 12:00 PM</span>
            <button class="htl-book-btn">View Details <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 8h10M9 4l4 4-4 4"/></svg></button>
          </div>
        </div>
      </div>
      <!-- 8: Moustache Select, Manali -->
      <div class="htl-card" data-category="manali"
        data-name="Moustache Select, Manali"
        data-badge="Select"
        data-location="Manali"
        data-img="placeholder.jpg"
        data-desc="Moustache Select Manali is a modern stay for travellers who want style, comfort and easy access to the mountains. It works well for couples, friend groups and young leisure travellers who prefer a hotel with contemporary rooms and social energy."
        data-checkin="2:00 PM" data-checkout="11:00 AM"
        data-features="Moustache Select,Stylish Rooms,Manali,Travel Desk,Social Spaces,Official Hotel"
        data-rooms="Standard Room; Deluxe Room; Executive Room; Suite. Exact availability and occupancy can be confirmed on enquiry."
        data-wa="I&#39;m interested in Moustache Select, Manali. Please share availability and rates.">
        <div class="htl-card-img">
          <img src="placeholder.jpg" alt="Moustache Select, Manali" loading="lazy" />
          <span class="htl-badge">Select</span>
          <span class="htl-loc-badge">&#128205; Manali</span>
        </div>
        <div class="htl-card-body">
          <h3 class="htl-card-name">Moustache Select, Manali</h3>
          <p class="htl-card-desc">A stylish Moustache Select stay in Manali with traveller-friendly spaces.</p>
          <div class="htl-card-meta"><span>Moustache Select</span><span>Stylish Rooms</span></div>
          <div class="htl-card-footer">
            <span class="htl-card-timing">Check-in 2:00 PM &middot; Check-out 11:00 AM</span>
            <button class="htl-book-btn">View Details <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 8h10M9 4l4 4-4 4"/></svg></button>
          </div>
        </div>
      </div>
      <!-- 9: Moustache Select, Mussoorie -->
      <div class="htl-card" data-category="mussoorie"
        data-name="Moustache Select, Mussoorie"
        data-badge="Select"
        data-location="Mussoorie"
        data-img="placeholder.jpg"
        data-desc="Moustache Select Mussoorie is a contemporary hotel option for travellers visiting the Queen of Hills. It is suited for couples, families and friend groups who want a stylish Mussoorie stay with convenient access to the hill-station experience."
        data-checkin="2:00 PM" data-checkout="11:00 AM"
        data-features="Moustache Select,Mussoorie,Stylish Rooms,Valley Setting,Social Spaces,Official Hotel"
        data-rooms="Standard Room; Deluxe Room; Executive Room; Suite. Exact availability and occupancy can be confirmed on enquiry."
        data-wa="I&#39;m interested in Moustache Select, Mussoorie. Please share availability and rates.">
        <div class="htl-card-img">
          <img src="placeholder.jpg" alt="Moustache Select, Mussoorie" loading="lazy" />
          <span class="htl-badge">Select</span>
          <span class="htl-loc-badge">&#128205; Mussoorie</span>
        </div>
        <div class="htl-card-body">
          <h3 class="htl-card-name">Moustache Select, Mussoorie</h3>
          <p class="htl-card-desc">A modern Moustache Select hotel in the Queen of Hills.</p>
          <div class="htl-card-meta"><span>Moustache Select</span><span>Mussoorie</span></div>
          <div class="htl-card-footer">
            <span class="htl-card-timing">Check-in 2:00 PM &middot; Check-out 11:00 AM</span>
            <button class="htl-book-btn">View Details <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 8h10M9 4l4 4-4 4"/></svg></button>
          </div>
        </div>
      </div>
      <!-- 10: Everest Base Camp -->
      <div class="htl-card" data-category="mussoorie"
        data-name="Everest Base Camp"
        data-badge="Unique"
        data-location="Mussoorie"
        data-img="placeholder.jpg"
        data-desc="Everest Base Camp Mussoorie is a themed forest-and-mountain retreat inspired by Himalayan expedition living. The property is best for guests who want a distinctive stay experience, outdoor character and boutique accommodation rather than a standard city hotel. Check-in: 2:00 PM; Check-out: 10:30 AM."
        data-checkin="2:00 PM" data-checkout="10:30 AM"
        data-features="Themed Stay,Mussoorie,Forest Setting,Glamping Style,Room Categories,Official Hotel"
        data-rooms="Andrew&#39;s Villa; Twin Luxury Cottages; The Zenith; Surveyor Suite; The Surveyor; The Glamper; The Camper."
        data-wa="I&#39;m interested in Everest Base Camp. Please share availability and rates.">
        <div class="htl-card-img">
          <img src="placeholder.jpg" alt="Everest Base Camp" loading="lazy" />
          <span class="htl-badge">Unique</span>
          <span class="htl-loc-badge">&#128205; Mussoorie</span>
        </div>
        <div class="htl-card-body">
          <h3 class="htl-card-name">Everest Base Camp</h3>
          <p class="htl-card-desc">A distinctive Mussoorie glamping-style retreat inspired by Himalayan adventure.</p>
          <div class="htl-card-meta"><span>Themed Stay</span><span>Mussoorie</span></div>
          <div class="htl-card-footer">
            <span class="htl-card-timing">Check-in 2:00 PM &middot; Check-out 10:30 AM</span>
            <button class="htl-book-btn">View Details <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 8h10M9 4l4 4-4 4"/></svg></button>
          </div>
        </div>
      </div>
      <!-- 11: Buena Vista, Jaipur -->
      <div class="htl-card" data-category="jaipur"
        data-name="Buena Vista"
        data-badge="Heritage"
        data-location="Jaipur"
        data-img="placeholder.jpg"
        data-desc="Buena Vista Jaipur is a luxury resort retreat with a palatial design language, garden-led spaces and a refined leisure atmosphere. It is suited for couples, luxury travellers and families looking for a polished Jaipur stay away from the regular city-hotel format. Check-in: 2:00 PM; Check-out: 11:00 AM."
        data-checkin="2:00 PM" data-checkout="11:00 AM"
        data-features="Palatial Garden Tents,Jaipur,Heritage Feel,Luxury Resort,Private Villas,Official Hotel"
        data-rooms="Palatial Garden Tents."
        data-wa="I&#39;m interested in Buena Vista, Jaipur. Please share availability and rates.">
        <div class="htl-card-img">
          <img src="placeholder.jpg" alt="Buena Vista, Jaipur" loading="lazy" />
          <span class="htl-badge">Heritage</span>
          <span class="htl-loc-badge">&#128205; Jaipur</span>
        </div>
        <div class="htl-card-body">
          <h3 class="htl-card-name">Buena Vista</h3>
          <p class="htl-card-desc">A Jaipur luxury retreat with palatial garden tents and private-villa charm.</p>
          <div class="htl-card-meta"><span>Palatial Garden Tents</span><span>Jaipur</span></div>
          <div class="htl-card-footer">
            <span class="htl-card-timing">Check-in 2:00 PM &middot; Check-out 11:00 AM</span>
            <button class="htl-book-btn">View Details <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 8h10M9 4l4 4-4 4"/></svg></button>
          </div>
        </div>
      </div>
      <!-- 12: Hotel Lakend, Udaipur -->
      <div class="htl-card" data-category="udaipur"
        data-name="Hotel Lakend"
        data-badge="Lakeside"
        data-location="Udaipur"
        data-img="placeholder.jpg"
        data-desc="Hotel Lakend is a lakeside Udaipur hotel for guests who want scenic water views, city access and a resort-like setting. It works well for couples, families and leisure travellers planning a relaxed Udaipur stay close to the lake experience. Check-in: 1:00 PM; Check-out: 10:00 AM."
        data-checkin="1:00 PM" data-checkout="10:00 AM"
        data-features="Lakeside,Udaipur,Room Categories,Restaurant,City of Lakes,Official Hotel"
        data-rooms="Standard Room; Deluxe Room; Executive Room; Suite. Exact availability and occupancy can be confirmed on enquiry."
        data-wa="I&#39;m interested in Hotel Lakend, Udaipur. Please share availability and rates.">
        <div class="htl-card-img">
          <img src="placeholder.jpg" alt="Hotel Lakend, Udaipur" loading="lazy" />
          <span class="htl-badge">Lakeside</span>
          <span class="htl-loc-badge">&#128205; Udaipur</span>
        </div>
        <div class="htl-card-body">
          <h3 class="htl-card-name">Hotel Lakend</h3>
          <p class="htl-card-desc">A lakeside Udaipur hotel with scenic views and city-of-lakes access.</p>
          <div class="htl-card-meta"><span>Lakeside</span><span>Udaipur</span></div>
          <div class="htl-card-footer">
            <span class="htl-card-timing">Check-in 1:00 PM &middot; Check-out 10:00 AM</span>
            <button class="htl-book-btn">View Details <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 8h10M9 4l4 4-4 4"/></svg></button>
          </div>
        </div>
      </div>
      <!-- 13: Anandam - A Jungle Retreat Resort, Udaipur -->
      <div class="htl-card" data-category="udaipur"
        data-name="Anandam - A Jungle Retreat"
        data-badge="Jungle"
        data-location="Udaipur"
        data-img="placeholder.jpg"
        data-desc="Anandam - A Jungle Retreat is a nature-focused resort near Udaipur for travellers who want greenery, quiet surroundings and a retreat-style break. It is suitable for families, couples and groups who prefer open spaces and a slower stay experience outside the usual city setting. Check-in: 2:00 PM; Check-out: 11:00 AM."
        data-checkin="2:00 PM" data-checkout="11:00 AM"
        data-features="Jungle Retreat,Udaipur,Nature Stay,Resort Experience,Room Categories,Official Hotel"
        data-rooms="Standard Room; Deluxe Room; Executive Room; Suite. Exact availability and occupancy can be confirmed on enquiry."
        data-wa="I&#39;m interested in Anandam - A Jungle Retreat Resort, Udaipur. Please share availability and rates.">
        <div class="htl-card-img">
          <img src="placeholder.jpg" alt="Anandam - A Jungle Retreat Resort, Udaipur" loading="lazy" />
          <span class="htl-badge">Jungle</span>
          <span class="htl-loc-badge">&#128205; Udaipur</span>
        </div>
        <div class="htl-card-body">
          <h3 class="htl-card-name">Anandam - A Jungle Retreat</h3>
          <p class="htl-card-desc">A nature-led jungle retreat near Udaipur with resort-style comfort.</p>
          <div class="htl-card-meta"><span>Jungle Retreat</span><span>Udaipur</span></div>
          <div class="htl-card-footer">
            <span class="htl-card-timing">Check-in 2:00 PM &middot; Check-out 11:00 AM</span>
            <button class="htl-book-btn">View Details <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 8h10M9 4l4 4-4 4"/></svg></button>
          </div>
        </div>
      </div>
      <!-- 14: Moustache Verandah, Udaipur -->
      <div class="htl-card" data-category="udaipur"
        data-name="Moustache Verandah, Udaipur"
        data-badge="Boutique"
        data-location="Udaipur"
        data-img="placeholder.jpg"
        data-desc="Moustache Verandah Udaipur is a boutique stay for travellers who want a stylish city base with the Moustache hospitality style. It suits couples, friend groups and leisure guests planning to explore Udaipur while staying in a character-led property."
        data-checkin="2:00 PM" data-checkout="11:00 AM"
        data-features="Moustache,Udaipur,Boutique Stay,City Stay,Room Categories,Official Hotel"
        data-rooms="Standard Room; Deluxe Room; Executive Room; Suite. Exact availability and occupancy can be confirmed on enquiry."
        data-wa="I&#39;m interested in Moustache Verandah, Udaipur. Please share availability and rates.">
        <div class="htl-card-img">
          <img src="placeholder.jpg" alt="Moustache Verandah, Udaipur" loading="lazy" />
          <span class="htl-badge">Boutique</span>
          <span class="htl-loc-badge">&#128205; Udaipur</span>
        </div>
        <div class="htl-card-body">
          <h3 class="htl-card-name">Moustache Verandah, Udaipur</h3>
          <p class="htl-card-desc">A boutique Moustache stay in Udaipur with city character.</p>
          <div class="htl-card-meta"><span>Moustache</span><span>Udaipur</span></div>
          <div class="htl-card-footer">
            <span class="htl-card-timing">Check-in 2:00 PM &middot; Check-out 11:00 AM</span>
            <button class="htl-book-btn">View Details <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 8h10M9 4l4 4-4 4"/></svg></button>
          </div>
        </div>
      </div>
      <!-- 15: Moustache Luxuria, Udaipur -->
      <div class="htl-card" data-category="udaipur"
        data-name="Moustache Luxuria, Udaipur"
        data-badge="Luxuria"
        data-location="Udaipur"
        data-img="placeholder.jpg"
        data-desc="Moustache Luxuria Udaipur is a premium boutique-luxury option within the Moustache portfolio. It is suited for travellers who want upgraded comfort, polished interiors and an Udaipur base with a more refined stay experience."
        data-checkin="2:00 PM" data-checkout="11:00 AM"
        data-features="Moustache Luxuria,Udaipur,Premium Rooms,Boutique Luxury,Room Categories,Official Hotel"
        data-rooms="Standard Room; Deluxe Room; Executive Room; Suite. Exact availability and occupancy can be confirmed on enquiry."
        data-wa="I&#39;m interested in Moustache Luxuria, Udaipur. Please share availability and rates.">
        <div class="htl-card-img">
          <img src="placeholder.jpg" alt="Moustache Luxuria, Udaipur" loading="lazy" />
          <span class="htl-badge">Luxuria</span>
          <span class="htl-loc-badge">&#128205; Udaipur</span>
        </div>
        <div class="htl-card-body">
          <h3 class="htl-card-name">Moustache Luxuria, Udaipur</h3>
          <p class="htl-card-desc">A premium Moustache Luxuria stay in Udaipur with upgraded room categories.</p>
          <div class="htl-card-meta"><span>Moustache Luxuria</span><span>Udaipur</span></div>
          <div class="htl-card-footer">
            <span class="htl-card-timing">Check-in 2:00 PM &middot; Check-out 11:00 AM</span>
            <button class="htl-book-btn">View Details <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 8h10M9 4l4 4-4 4"/></svg></button>
          </div>
        </div>
      </div>
      <!-- 16: Yog Niketan by Sanskriti, Rishikesh -->
      <div class="htl-card" data-category="rishikesh"
        data-name="Yog Niketan by Sanskriti"
        data-badge="Wellness"
        data-location="Rishikesh"
        data-img="placeholder.jpg"
        data-desc="Yog Niketan by Sanskriti is a calm Rishikesh stay built around wellness, spirituality and a peaceful riverside-town experience. It is ideal for guests interested in yoga, quiet hospitality, temple-town access and a slower retreat atmosphere."
        data-checkin="12:00 PM" data-checkout="10:00 AM"
        data-features="Wellness Stay,Rishikesh,Yoga Setting,Peaceful Retreat,Room Categories,Official Hotel"
        data-rooms="Standard Room; Deluxe Room; Executive Room; Suite. Exact availability and occupancy can be confirmed on enquiry."
        data-wa="I&#39;m interested in Yog Niketan by Sanskriti, Rishikesh. Please share availability and rates.">
        <div class="htl-card-img">
          <img src="placeholder.jpg" alt="Yog Niketan by Sanskriti, Rishikesh" loading="lazy" />
          <span class="htl-badge">Wellness</span>
          <span class="htl-loc-badge">&#128205; Rishikesh</span>
        </div>
        <div class="htl-card-body">
          <h3 class="htl-card-name">Yog Niketan by Sanskriti</h3>
          <p class="htl-card-desc">A peaceful Rishikesh stay with a wellness-first atmosphere near the Ganges.</p>
          <div class="htl-card-meta"><span>Wellness Stay</span><span>Rishikesh</span></div>
          <div class="htl-card-footer">
            <span class="htl-card-timing">Check-in 12:00 PM &middot; Check-out 10:00 AM</span>
            <button class="htl-book-btn">View Details <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 8h10M9 4l4 4-4 4"/></svg></button>
          </div>
        </div>
      </div>
      <!-- 17: Moustache Select Riverside Resort, Rishikesh -->
      <div class="htl-card" data-category="rishikesh"
        data-name="Moustache Select Riverside Resort"
        data-badge="Riverside"
        data-location="Rishikesh"
        data-img="placeholder.jpg"
        data-desc="Moustache Select Riverside Resort Rishikesh is a resort-style stay for travellers who want river proximity, outdoor activities and a relaxed setting. It works well for friend groups, couples and adventure travellers looking for rafting access and nature-led downtime."
        data-checkin="2:00 PM" data-checkout="11:00 AM"
        data-features="Riverside Resort,Rishikesh,Moustache Select,Adventure Access,Room Categories,Official Hotel"
        data-rooms="Standard Room; Deluxe Room; Executive Room; Suite. Exact availability and occupancy can be confirmed on enquiry."
        data-wa="I&#39;m interested in Moustache Select Riverside Resort, Rishikesh. Please share availability and rates.">
        <div class="htl-card-img">
          <img src="placeholder.jpg" alt="Moustache Select Riverside Resort, Rishikesh" loading="lazy" />
          <span class="htl-badge">Riverside</span>
          <span class="htl-loc-badge">&#128205; Rishikesh</span>
        </div>
        <div class="htl-card-body">
          <h3 class="htl-card-name">Moustache Select Riverside Resort</h3>
          <p class="htl-card-desc">A riverside Rishikesh resort for nature, rafting and easygoing stays.</p>
          <div class="htl-card-meta"><span>Riverside Resort</span><span>Rishikesh</span></div>
          <div class="htl-card-footer">
            <span class="htl-card-timing">Check-in 2:00 PM &middot; Check-out 11:00 AM</span>
            <button class="htl-book-btn">View Details <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 8h10M9 4l4 4-4 4"/></svg></button>
          </div>
        </div>
      </div>
      <!-- 18: 8Fold - Pinecrest By Larisa, Bhimtal -->
      <div class="htl-card" data-category="bhimtal"
        data-name="8Fold - Pinecrest by Larisa"
        data-badge="Boutique"
        data-location="Bhimtal"
        data-img="placeholder.jpg"
        data-desc="8Fold - Pinecrest by Larisa is a boutique Bhimtal escape with a calm Kumaon setting. It is suited for guests who want a quieter hill stay, boutique hospitality and easy access to the lake-region atmosphere."
        data-checkin="2:00 PM" data-checkout="12:00 PM"
        data-features="Pinecrest,Larisa Collection,Bhimtal,Boutique Stay,Room Categories,Official Hotel"
        data-rooms="Standard Room; Deluxe Room; Executive Room; Suite. Exact availability and occupancy can be confirmed on enquiry."
        data-wa="I&#39;m interested in 8Fold - Pinecrest By Larisa, Bhimtal. Please share availability and rates.">
        <div class="htl-card-img">
          <img src="placeholder.jpg" alt="8Fold - Pinecrest By Larisa, Bhimtal" loading="lazy" />
          <span class="htl-badge">Boutique</span>
          <span class="htl-loc-badge">&#128205; Bhimtal</span>
        </div>
        <div class="htl-card-body">
          <h3 class="htl-card-name">8Fold - Pinecrest by Larisa</h3>
          <p class="htl-card-desc">A Pinecrest by Larisa boutique escape in Bhimtal surrounded by Kumaon calm.</p>
          <div class="htl-card-meta"><span>Pinecrest</span><span>Larisa Collection</span></div>
          <div class="htl-card-footer">
            <span class="htl-card-timing">Check-in 2:00 PM &middot; Check-out 12:00 PM</span>
            <button class="htl-book-btn">View Details <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 8h10M9 4l4 4-4 4"/></svg></button>
          </div>
        </div>
      </div>
      <!-- 19: Moustache Luxuria, Bhimtal -->
      <div class="htl-card" data-category="bhimtal"
        data-name="Moustache Luxuria, Bhimtal"
        data-badge="Luxuria"
        data-location="Bhimtal"
        data-img="placeholder.jpg"
        data-desc="Moustache Luxuria Bhimtal is a premium boutique stay for travellers who want upgraded comfort in the lake region. It works well for couples, families and groups looking for a scenic Bhimtal base with polished rooms."
        data-checkin="2:00 PM" data-checkout="11:00 AM"
        data-features="Moustache Luxuria,Bhimtal,Premium Rooms,Lake Region,Room Categories,Official Hotel"
        data-rooms="Standard Room; Deluxe Room; Executive Room; Suite. Exact availability and occupancy can be confirmed on enquiry."
        data-wa="I&#39;m interested in Moustache Luxuria, Bhimtal. Please share availability and rates.">
        <div class="htl-card-img">
          <img src="placeholder.jpg" alt="Moustache Luxuria, Bhimtal" loading="lazy" />
          <span class="htl-badge">Luxuria</span>
          <span class="htl-loc-badge">&#128205; Bhimtal</span>
        </div>
        <div class="htl-card-body">
          <h3 class="htl-card-name">Moustache Luxuria, Bhimtal</h3>
          <p class="htl-card-desc">A premium Moustache Luxuria property in Bhimtal for lake-region breaks.</p>
          <div class="htl-card-meta"><span>Moustache Luxuria</span><span>Bhimtal</span></div>
          <div class="htl-card-footer">
            <span class="htl-card-timing">Check-in 2:00 PM &middot; Check-out 11:00 AM</span>
            <button class="htl-book-btn">View Details <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 8h10M9 4l4 4-4 4"/></svg></button>
          </div>
        </div>
      </div>
      <!-- 20: Renest Calangute, Goa -->
      <div class="htl-card" data-category="goa"
        data-name="Renest Calangute"
        data-badge="Beachside"
        data-location="Goa"
        data-img="placeholder.jpg"
        data-desc="Renest Calangute is a Goa hotel positioned for beach holidays, casual leisure trips and easy access to the Calangute area. It is a practical option for couples, families and groups wanting a coastal stay with hotel services."
        data-checkin="2:00 PM" data-checkout="12:00 PM"
        data-features="Renest Hotels,Calangute,Goa Stay,Beach Access,Room Categories,Official Hotel"
        data-rooms="Standard Room; Deluxe Room; Executive Room; Suite. Exact availability and occupancy can be confirmed on enquiry."
        data-wa="I&#39;m interested in Renest Calangute, Goa. Please share availability and rates.">
        <div class="htl-card-img">
          <img src="placeholder.jpg" alt="Renest Calangute, Goa" loading="lazy" />
          <span class="htl-badge">Beachside</span>
          <span class="htl-loc-badge">&#128205; Goa</span>
        </div>
        <div class="htl-card-body">
          <h3 class="htl-card-name">Renest Calangute</h3>
          <p class="htl-card-desc">A Renest hotel in Calangute, Goa for beach holidays and coastal access.</p>
          <div class="htl-card-meta"><span>Renest Hotels</span><span>Calangute</span></div>
          <div class="htl-card-footer">
            <span class="htl-card-timing">Check-in 2:00 PM &middot; Check-out 12:00 PM</span>
            <button class="htl-book-btn">View Details <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 8h10M9 4l4 4-4 4"/></svg></button>
          </div>
        </div>
      </div>
      <!-- 21: Larisa Resort & Spa, Ashwem Goa -->
      <div class="htl-card" data-category="goa"
        data-name="Larisa Resort &amp; Spa - Ashwem Goa"
        data-badge="Luxury"
        data-location="Goa"
        data-img="placeholder.jpg"
        data-desc="Larisa Resort &amp; Spa Ashwem Goa is a beach-resort stay for travellers who want a quieter North Goa setting with cottage-style accommodation. It is well suited for couples, families and leisure guests looking for Larisa hospitality close to Ashwem Beach."
        data-checkin="2:00 PM" data-checkout="12:00 PM"
        data-features="2 Bedroom Cottage,Cottage Suite,Luxury Cottage,Ashwem Beach,Larisa Collection,Official Hotel"
        data-rooms="2 Bedroom Cottage; Cottage Suite; Luxury Cottage."
        data-wa="I&#39;m interested in Larisa Resort &amp; Spa, Ashwem Goa. Please share availability and rates.">
        <div class="htl-card-img">
          <img src="placeholder.jpg" alt="Larisa Resort &amp; Spa, Ashwem Goa" loading="lazy" />
          <span class="htl-badge">Luxury</span>
          <span class="htl-loc-badge">&#128205; Goa</span>
        </div>
        <div class="htl-card-body">
          <h3 class="htl-card-name">Larisa Resort &amp; Spa - Ashwem Goa</h3>
          <p class="htl-card-desc">A Larisa beach resort in Ashwem with cottage and suite-style options.</p>
          <div class="htl-card-meta"><span>2 Bedroom Cottage</span><span>Cottage Suite</span></div>
          <div class="htl-card-footer">
            <span class="htl-card-timing">Check-in 2:00 PM &middot; Check-out 12:00 PM</span>
            <button class="htl-book-btn">View Details <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 8h10M9 4l4 4-4 4"/></svg></button>
          </div>
        </div>
      </div>
      <!-- 22: 8Fold by Larisa, Siolim Goa -->
      <div class="htl-card" data-category="goa"
        data-name="8Fold by Larisa - Siolim Goa"
        data-badge="Boutique"
        data-location="Goa"
        data-img="placeholder.jpg"
        data-desc="8Fold by Larisa Siolim Goa is a boutique stay in a calmer North Goa neighbourhood, suited for travellers who want comfort, design-led hospitality and access to nearby beaches without staying in the busiest beach belts."
        data-checkin="2:00 PM" data-checkout="12:00 PM"
        data-features="Standard Room,Deluxe Room,Premium Room,Siolim,Larisa Collection,Official Hotel"
        data-rooms="Standard Room; Deluxe Room; Premium Room."
        data-wa="I&#39;m interested in 8Fold by Larisa, Siolim Goa. Please share availability and rates.">
        <div class="htl-card-img">
          <img src="placeholder.jpg" alt="8Fold by Larisa, Siolim Goa" loading="lazy" />
          <span class="htl-badge">Boutique</span>
          <span class="htl-loc-badge">&#128205; Goa</span>
        </div>
        <div class="htl-card-body">
          <h3 class="htl-card-name">8Fold by Larisa - Siolim Goa</h3>
          <p class="htl-card-desc">A boutique 8Fold by Larisa stay in Siolim with standard-to-premium rooms.</p>
          <div class="htl-card-meta"><span>Standard Room</span><span>Deluxe Room</span></div>
          <div class="htl-card-footer">
            <span class="htl-card-timing">Check-in 2:00 PM &middot; Check-out 12:00 PM</span>
            <button class="htl-book-btn">View Details <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 8h10M9 4l4 4-4 4"/></svg></button>
          </div>
        </div>
      </div>
      <!-- 23: Bradhi Resorts, Jibhi -->
      <div class="htl-card" data-category="jibhi"
        data-name="Bradhi Resorts"
        data-badge="Forest"
        data-location="Jibhi"
        data-img="placeholder.jpg"
        data-desc="Bradhi Resorts is a Jibhi stay for guests who want valley scenery, nature access and the quieter side of Himachal. It is suited for couples, families and small groups looking for a relaxed mountain break in the Tirthan-Jibhi region."
        data-checkin="12:00 PM" data-checkout="11:00 AM"
        data-features="Jibhi,Tirthan Valley,Nature Stay,Forest Setting,Room Categories,Official Hotel"
        data-rooms="Standard Room; Deluxe Room; Executive Room; Suite. Exact availability and occupancy can be confirmed on enquiry."
        data-wa="I&#39;m interested in Bradhi Resorts, Jibhi. Please share availability and rates.">
        <div class="htl-card-img">
          <img src="placeholder.jpg" alt="Bradhi Resorts, Jibhi" loading="lazy" />
          <span class="htl-badge">Forest</span>
          <span class="htl-loc-badge">&#128205; Jibhi</span>
        </div>
        <div class="htl-card-body">
          <h3 class="htl-card-name">Bradhi Resorts</h3>
          <p class="htl-card-desc">A Jibhi resort surrounded by valley scenery and quiet Himachal charm.</p>
          <div class="htl-card-meta"><span>Jibhi</span><span>Tirthan Valley</span></div>
          <div class="htl-card-footer">
            <span class="htl-card-timing">Check-in 12:00 PM &middot; Check-out 11:00 AM</span>
            <button class="htl-book-btn">View Details <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 8h10M9 4l4 4-4 4"/></svg></button>
          </div>
        </div>
      </div>
      <!-- 24: 8Fold by Larisa, Jibhi -->
      <div class="htl-card" data-category="jibhi"
        data-name="8Fold by Larisa - Jibhi"
        data-badge="Boutique"
        data-location="Jibhi"
        data-img="placeholder.jpg"
        data-desc="8Fold by Larisa Jibhi is a boutique mountain property for travellers who want Larisa-style comfort in a scenic Himachal setting. It is suitable for peaceful holidays, nature-led trips and travellers who prefer small-format boutique stays."
        data-checkin="2:00 PM" data-checkout="12:00 PM"
        data-features="8Fold,Larisa Collection,Jibhi,Boutique Stay,Room Categories,Official Hotel"
        data-rooms="Standard Room; Deluxe Room; Executive Room; Suite. Exact availability and occupancy can be confirmed on enquiry."
        data-wa="I&#39;m interested in 8Fold by Larisa, Jibhi. Please share availability and rates.">
        <div class="htl-card-img">
          <img src="placeholder.jpg" alt="8Fold by Larisa, Jibhi" loading="lazy" />
          <span class="htl-badge">Boutique</span>
          <span class="htl-loc-badge">&#128205; Jibhi</span>
        </div>
        <div class="htl-card-body">
          <h3 class="htl-card-name">8Fold by Larisa - Jibhi</h3>
          <p class="htl-card-desc">A boutique Larisa property in Jibhi with mountain-valley charm.</p>
          <div class="htl-card-meta"><span>8Fold</span><span>Larisa Collection</span></div>
          <div class="htl-card-footer">
            <span class="htl-card-timing">Check-in 2:00 PM &middot; Check-out 12:00 PM</span>
            <button class="htl-book-btn">View Details <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 8h10M9 4l4 4-4 4"/></svg></button>
          </div>
        </div>
      </div>
      <!-- 25: Brij Baggecha, Kukas Jaipur -->
      <div class="htl-card" data-category="jaipur"
        data-name="Brij Baggecha, Kukas Jaipur"
        data-badge="Luxury"
        data-location="Jaipur"
        data-img="placeholder.jpg"
        data-desc="Brij Baggecha Kukas Jaipur is a refined retreat-style stay near Jaipur, suitable for guests who want Brij hospitality, calm surroundings and a polished leisure experience outside the dense city core."
        data-checkin="2:00 PM" data-checkout="11:00 AM"
        data-features="Brij Hotels,Kukas Jaipur,Luxury Retreat,Jaipur Stay,Room Categories,Official Hotel"
        data-rooms="Standard Room; Deluxe Room; Executive Room; Suite. Exact availability and occupancy can be confirmed on enquiry."
        data-wa="I&#39;m interested in Brij Baggecha, Kukas Jaipur. Please share availability and rates.">
        <div class="htl-card-img">
          <img src="placeholder.jpg" alt="Brij Baggecha, Kukas Jaipur" loading="lazy" />
          <span class="htl-badge">Luxury</span>
          <span class="htl-loc-badge">&#128205; Jaipur</span>
        </div>
        <div class="htl-card-body">
          <h3 class="htl-card-name">Brij Baggecha, Kukas Jaipur</h3>
          <p class="htl-card-desc">A Brij Hotels retreat in Kukas, Jaipur with refined hospitality.</p>
          <div class="htl-card-meta"><span>Brij Hotels</span><span>Kukas Jaipur</span></div>
          <div class="htl-card-footer">
            <span class="htl-card-timing">Check-in 2:00 PM &middot; Check-out 11:00 AM</span>
            <button class="htl-book-btn">View Details <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 8h10M9 4l4 4-4 4"/></svg></button>
          </div>
        </div>
      </div>
      <!-- 26: Kailasha - The Himalayan Village Resort, Kasol -->
      <div class="htl-card" data-category="kasol"
        data-name="Kailasha - The Himalayan Village Resort"
        data-badge="Himalayan"
        data-location="Kasol"
        data-img="placeholder.jpg"
        data-desc="Kailasha - The Himalayan Village Resort is a Kasol mountain stay for travellers looking for a Himalayan village-resort atmosphere, nature views and a relaxed base in the Parvati Valley region."
        data-checkin="2:00 PM" data-checkout="11:00 AM"
        data-features="Kasol,Himalayan Resort,Mountain Stay,Nature Escape,Room Categories,Approved Hotel"
        data-rooms="Standard Room; Deluxe Room; Executive Room; Suite. Exact availability and occupancy can be confirmed on enquiry."
        data-wa="I&#39;m interested in Kailasha - The Himalayan Village Resort, Kasol. Please share availability and rates.">
        <div class="htl-card-img">
          <img src="placeholder.jpg" alt="Kailasha - The Himalayan Village Resort, Kasol" loading="lazy" />
          <span class="htl-badge">Himalayan</span>
          <span class="htl-loc-badge">&#128205; Kasol</span>
        </div>
        <div class="htl-card-body">
          <h3 class="htl-card-name">Kailasha - The Himalayan Village Resort</h3>
          <p class="htl-card-desc">A Himalayan village-style resort option in Kasol for nature-led stays.</p>
          <div class="htl-card-meta"><span>Kasol</span><span>Himalayan Resort</span></div>
          <div class="htl-card-footer">
            <span class="htl-card-timing">Check-in 2:00 PM &middot; Check-out 11:00 AM</span>
            <button class="htl-book-btn">View Details <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 8h10M9 4l4 4-4 4"/></svg></button>
          </div>
        </div>
      </div>
      <!-- 27: Itsy Bitsy Cabin, Kasol -->
      <div class="htl-card" data-category="kasol"
        data-name="Itsy Bitsy Cabin"
        data-badge="Cabins"
        data-location="Kasol"
        data-img="placeholder.jpg"
        data-desc="Itsy Bitsy Cabin is a distinctive Kasol cabin stay with multiple themed accommodation types, suited for couples, friends and small groups who want a memorable mountain stay rather than a standard hotel room."
        data-checkin="2:00 PM" data-checkout="11:00 AM"
        data-features="Dome Cabin - up to 3 adults,Moonlight Chalet - up to 3 adults,Luar Chalet - up to 3 adults,A-Frame Cabin - up to 4 adults,Starlight Dome - up to 4 adults,Official Hotel"
        data-rooms="Dome Cabin - up to 3 adults; Moonlight Chalet - up to 3 adults; Luar Chalet - up to 3 adults; A-Frame Cabin - up to 4 adults; Starlight Dome - up to 4 adults."
        data-wa="I&#39;m interested in Itsy Bitsy Cabin, Kasol. Please share availability and rates.">
        <div class="htl-card-img">
          <img src="placeholder.jpg" alt="Itsy Bitsy Cabin, Kasol" loading="lazy" />
          <span class="htl-badge">Cabins</span>
          <span class="htl-loc-badge">&#128205; Kasol</span>
        </div>
        <div class="htl-card-body">
          <h3 class="htl-card-name">Itsy Bitsy Cabin</h3>
          <p class="htl-card-desc">A Kasol cabin stay with dome, chalet and A-frame accommodation options.</p>
          <div class="htl-card-meta"><span>Dome Cabin - up to 3 adults</span><span>Moonlight Chalet - up to 3 adults</span></div>
          <div class="htl-card-footer">
            <span class="htl-card-timing">Check-in 2:00 PM &middot; Check-out 11:00 AM</span>
            <button class="htl-book-btn">View Details <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 8h10M9 4l4 4-4 4"/></svg></button>
          </div>
        </div>
      </div>
    </div><!-- /htl-grid -->
  </div>
</section>

<!-- ======================================================
     HOTEL DETAIL MODAL
====================================================== -->
<div class="htl-modal-backdrop" id="htlModalBackdrop" role="dialog" aria-modal="true" aria-labelledby="htlModalName">
  <div class="htl-modal" id="htlModal">
    <button class="htl-modal-close" id="htlModalClose" aria-label="Close">X</button>
    <img class="htl-modal-img" id="htlModalImg" src="" alt="" />
    <div class="htl-modal-body">
      <div class="htl-modal-top">
        <span class="htl-modal-badge" id="htlModalBadge"></span>
        <span class="htl-modal-loc" id="htlModalLoc"></span>
      </div>
      <h2 class="htl-modal-name" id="htlModalName"></h2>
      <p class="htl-modal-desc" id="htlModalDesc"></p>
      <div class="htl-modal-details">
                <div class="htl-modal-detail-item">
          <label>Property Overview</label>
          <span id="htlModalLocationDetail"></span>
        </div>
<div class="htl-modal-detail-item">
          <label>Check-in</label>
          <span id="htlModalCheckin"></span>
        </div>
        <div class="htl-modal-detail-item">
          <label>Check-out</label>
          <span id="htlModalCheckout"></span>
        </div>
                      <div class="htl-modal-detail-item htl-modal-detail-wide">
          <label>Nearby Attractions</label>
          <span id="htlModalBestFor"></span>
        </div>
<div class="htl-modal-detail-item htl-modal-detail-wide">
          <label>Room Categories</label>
          <span id="htlModalRooms"></span>
        </div>
      </div>
      <div class="htl-modal-features" id="htlModalFeatures"></div>
      <div class="htl-modal-actions">
        <a href="#htl-enquiry" class="htl-modal-enquire" id="htlModalEnquire">
          Send Enquiry
          <svg width="14" height="14" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 8h10M9 4l4 4-4 4"/></svg>
        </a>
        <a href="#" class="htl-modal-wa" id="htlModalWa" target="_blank">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
          WhatsApp Us
        </a>
      </div>
    </div>
  </div>
</div>

<!-- ======================================================
     FEATURED BANNER
====================================================== -->
<div class="htl-banner">
  <div class="htl-banner-inner">
    <div class="htl-banner-text">
      <p class="htl-eyebrow">Why Book With Us</p>
      <h2>More Than a Booking -<br><em>A Curated Experience</em></h2>
      <p class="htl-body">We don't just list hotels. We personally vet every property, negotiate the best rates and stay with you from enquiry to check-out. No hidden charges, no last-minute surprises.</p>
      <div class="htl-banner-btns">
        <a href="https://wa.me/919875073788" class="htl-btn-primary" target="_blank">WhatsApp Us Now</a>
        <a href="#htl-enquiry" class="htl-btn-outline">Send an Enquiry</a>
      </div>
    </div>
    <div class="htl-banner-img">
      <img src="https://images.unsplash.com/photo-1564501049412-61c2a3083791?w=800&q=85" alt="Luxury Hotel Lobby" loading="lazy" />
    </div>
  </div>
</div>

<!-- ======================================================
     ENQUIRY FORM
====================================================== -->
<section class="htl-enquiry" id="htl-enquiry">
  <div class="htl-enquiry-inner">
    <div class="htl-enquiry-header">
      <p class="htl-eyebrow">Send an Enquiry</p>
      <h2 class="htl-title">Tell Us Your <em>Dream Stay</em></h2>
      <p class="htl-desc">Fill in your details and our travel experts will get back to you within 2 hours with personalised hotel options and the best rates.</p>
    </div>
    <div class="htl-divider"><span>*</span></div>

    <form class="htl-enquiry-form" id="htlEnquiryForm" novalidate>

      <div class="htl-form-group">
        <label for="htlName">Your Name</label>
        <input type="text" id="htlName" name="name" placeholder="e.g. Rahul Sharma" required />
      </div>

      <div class="htl-form-group">
        <label for="htlPhone">Phone / WhatsApp</label>
        <input type="tel" id="htlPhone" name="phone" placeholder="e.g. 98765 43210" required />
      </div>

      <div class="htl-form-group">
        <label for="htlEmail">Email Address</label>
        <input type="email" id="htlEmail" name="email" placeholder="e.g. rahul@email.com" />
      </div>

      <div class="htl-form-group">
        <label for="htlDestination">Destination</label>
        <select id="htlDestination" name="destination">
          <option value="" disabled selected>Select a destination</option>
          <option>Shimla</option>
          <option>Manali</option>
          <option>Kasol</option>
          <option>Mussoorie</option>
          <option>Rishikesh</option>
          <option>Bhimtal</option>
          <option>Goa</option>
          <option>Jaipur</option>
          <option>Udaipur</option>
          <option>Jibhi</option>
          <option>Other / Not Sure</option>
        </select>
      </div>

      <div class="htl-form-group">
        <label for="htlCheckin">Check-in Date</label>
        <input type="date" id="htlCheckin" name="checkin" />
      </div>

      <div class="htl-form-group">
        <label for="htlGuests">No. of Guests</label>
        <select id="htlGuests" name="guests">
          <option value="" disabled selected>Select guests</option>
          <option>1 Guest</option>
          <option>2 Guests</option>
          <option>3 Guests</option>
          <option>4 Guests</option>
          <option>5+ Guests</option>
        </select>
      </div>

      <div class="htl-form-group full">
        <label for="htlMessage">Additional Requirements</label>
        <textarea id="htlMessage" name="message" placeholder="Any specific requests, budget range, room preferences, special occasions..."></textarea>
      </div>

      <div class="htl-form-submit-row">
        <span class="htl-form-note">We typically respond within 2 hours - No spam, ever.</span>
        <button type="submit" class="htl-form-btn">
          Send Enquiry
          <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 8h10M9 4l4 4-4 4"/></svg>
        </button>
      </div>

    </form>

    <div class="htl-form-success" id="htlFormSuccess">
      <div class="htl-success-icon">*</div>
      <h3>Enquiry Received!</h3>
      <p>Thank you for reaching out. Our travel expert will contact you within 2 hours with personalised hotel recommendations.</p>
    </div>

  </div>
</section>

<!-- ======================================================
     CTA STRIP
====================================================== -->
<section class="htl-cta">
  <div class="htl-cta-inner">
    <p class="htl-eyebrow">Ready to Travel</p>
    <h2>Your Dream Stay Is<br><em>One Message Away</em></h2>
    <p>Tell us your destination, dates and budget. Our travel experts will curate the perfect hotel options - usually within 2 hours.</p>
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

  /* ===== HERO SLIDER ===== */
  const slides = document.querySelectorAll('.htl-slide');
  const dots   = document.querySelectorAll('.htl-dot');
  let current  = 0, timer;

  function goTo(n) {
    slides[current].classList.remove('active');
    dots[current].classList.remove('active');
    current = (n + slides.length) % slides.length;
    slides[current].classList.add('active');
    dots[current].classList.add('active');
  }
  function startAuto() { clearInterval(timer); timer = setInterval(() => goTo(current + 1), 5000); }
  dots.forEach(dot => dot.addEventListener('click', () => { goTo(+dot.dataset.slide); startAuto(); }));
  document.querySelector('.htl-arrow-prev').addEventListener('click', () => { goTo(current - 1); startAuto(); });
  document.querySelector('.htl-arrow-next').addEventListener('click', () => { goTo(current + 1); startAuto(); });
  startAuto();

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
        card.dataset.desc,
        card.dataset.features
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

  /* ===== MODAL ===== */
  const backdrop   = document.getElementById('htlModalBackdrop');
  const modalClose = document.getElementById('htlModalClose');
  const modalImg   = document.getElementById('htlModalImg');
  const modalBadge = document.getElementById('htlModalBadge');
  const modalLoc   = document.getElementById('htlModalLoc');
  const modalLocationDetail = document.getElementById('htlModalLocationDetail');
  const modalName  = document.getElementById('htlModalName');
  const modalDesc  = document.getElementById('htlModalDesc');
  const modalCi    = document.getElementById('htlModalCheckin');
  const modalCo    = document.getElementById('htlModalCheckout');
  const modalRooms = document.getElementById('htlModalRooms');
  const modalBestFor = document.getElementById('htlModalBestFor');
  const modalFeat  = document.getElementById('htlModalFeatures');
  const modalWa    = document.getElementById('htlModalWa');
  const modalEnq   = document.getElementById('htlModalEnquire');

  function renderRoomCategories(value) {
    const raw = value || 'Room details available on enquiry.';
    const parts = raw.split(';').map(item => item.trim()).filter(Boolean);
    const noteParts = [];
    const categories = parts.filter(item => {
      if (/enquiry|availability|occupancy/i.test(item)) {
        noteParts.push(item.replace(/\.$/, ''));
        return false;
      }
      return true;
    });

    modalRooms.innerHTML = '';

    if (categories.length) {
      const list = document.createElement('div');
      list.className = 'htl-room-category-list';
      categories.forEach(item => {
        const chip = document.createElement('span');
        chip.className = 'htl-room-category';
        chip.textContent = item.replace(/\.$/, '');
        list.appendChild(chip);
      });
      modalRooms.appendChild(list);
    } else {
      modalRooms.textContent = raw;
    }

    if (noteParts.length) {
      const note = document.createElement('small');
      note.className = 'htl-room-note';
      note.textContent = noteParts.join('. ') + '.';
      modalRooms.appendChild(note);
    }
  }

  function openModal(card) {
    const d = card.dataset;
    modalImg.src    = d.img;
    modalImg.alt    = d.name;
    modalBadge.textContent = d.badge;
    modalLoc.textContent   = d.location;
    modalName.textContent  = d.name;
    modalLocationDetail.textContent = d.desc;
    modalDesc.textContent  = d.desc;
    modalCi.textContent    = d.checkin;
    modalCo.textContent    = d.checkout;
    renderRoomCategories(d.rooms);
    modalBestFor.textContent = d.location ? `${d.location} sightseeing, local markets, scenic viewpoints and key leisure experiences near the property.` : 'Nearby attractions can be confirmed on enquiry.';

    modalFeat.innerHTML = '';
    d.features.split(',').forEach(f => {
      const span = document.createElement('span');
      span.className = 'htl-modal-feature';
      span.textContent = f.trim();
      modalFeat.appendChild(span);
    });

    const msg = encodeURIComponent(d.wa);
    modalWa.href = 'https://wa.me/919875073788?text=' + msg;

    modalEnq.addEventListener('click', function handler(e) {
      e.preventDefault();
      closeModal();
      const destSel = document.getElementById('htlDestination');
      if (destSel) {
        const loc = d.location;
        for (let opt of destSel.options) {
          if (opt.value === loc) { opt.selected = true; break; }
        }
      }
      document.getElementById('htl-enquiry').scrollIntoView({ behavior: 'smooth' });
      modalEnq.removeEventListener('click', handler);
    }, { once: true });

    backdrop.classList.add('open');
    document.body.style.overflow = 'hidden';
  }

  function closeModal() {
    backdrop.classList.remove('open');
    document.body.style.overflow = '';
  }

  cards.forEach(card => {
    card.addEventListener('click', () => openModal(card));
  });

  modalClose.addEventListener('click', closeModal);
  backdrop.addEventListener('click', (e) => { if (e.target === backdrop) closeModal(); });
  document.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeModal(); });

  /* ===== ENQUIRY FORM ===== */
  const form    = document.getElementById('htlEnquiryForm');
  const success = document.getElementById('htlFormSuccess');

  form.addEventListener('submit', (e) => {
    e.preventDefault();
    const name  = document.getElementById('htlName').value.trim();
    const phone = document.getElementById('htlPhone').value.trim();

    if (!name || !phone) {
      alert('Please enter your name and phone number.');
      return;
    }

    const dest    = document.getElementById('htlDestination').value || 'Not specified';
    const checkin = document.getElementById('htlCheckin').value || 'Flexible';
    const guests  = document.getElementById('htlGuests').value || 'Not specified';
    const email   = document.getElementById('htlEmail').value.trim();
    const message = document.getElementById('htlMessage').value.trim();

    const wa = `Hi TYT Luxe! I'd like to enquire about a hotel stay.\n\nName: ${name}\nPhone: ${phone}${email ? '\nEmail: ' + email : ''}\nDestination: ${dest}\nCheck-in: ${checkin}\nGuests: ${guests}${message ? '\nRequirements: ' + message : ''}`;

    window.open('https://wa.me/919875073788?text=' + encodeURIComponent(wa), '_blank');

    form.style.display = 'none';
    success.classList.add('show');
  });

})();
</script>


@endpush
