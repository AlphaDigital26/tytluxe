@extends('layouts.frontend')

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

.htl-modal-slider-wrap {
  position: relative; width: 100%; height: 300px;
  border-radius: 16px 16px 0 0; overflow: hidden;
}
.htl-modal-img {
  width: 100%; height: 100%; object-fit: cover;
  display: block; transition: opacity 0.4s ease;
}
.htl-modal-nav {
  position: absolute; top: 50%; transform: translateY(-50%);
  background: rgba(13,13,13,0.5); backdrop-filter: blur(4px);
  border: 1px solid rgba(255,255,255,0.15); color: #fff;
  width: 34px; height: 34px; border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  cursor: pointer; transition: all var(--transition);
  font-size: 14px; opacity: 0; pointer-events: none; z-index: 2;
}
.htl-modal-slider-wrap:hover .htl-modal-nav { opacity: 1; pointer-events: all; }
.htl-modal-nav:hover { background: var(--gold); color: var(--dark); border-color: var(--gold); }
.htl-modal-prev { left: 16px; }
.htl-modal-next { right: 16px; }
.htl-modal-dots {
  position: absolute; bottom: 12px; left: 0; right: 0;
  display: flex; justify-content: center; gap: 6px; z-index: 2;
}
.htl-modal-dot {
  width: 6px; height: 6px; border-radius: 50%;
  background: rgba(255,255,255,0.4); cursor: pointer;
  transition: all 0.3s ease;
}
.htl-modal-dot.active { background: var(--gold); width: 16px; border-radius: 10px; }
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
.htl-modal-overview { grid-row: span 2; }
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
  subtitle="From Himalayan retreats to beachside escapes - every property personally vetted for comfort, luxury &amp; value."
  :pills="['Shimla', 'Manali', 'Goa', 'Kasauli', 'Dalhousie', 'Mussoorie']"
/>

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
      @foreach($hotels as $hotel)
      <div class="htl-card" data-category="{{ Str::slug($hotel->destination->name) }}"
        data-name="{{ $hotel->title }}"
        data-badge="{{ ucfirst(str_replace('_', ' ', $hotel->category)) }}"
        data-location="{{ $hotel->destination->name }}"
        data-img="placeholder.jpg"
        data-desc="{{ $hotel->description }}"
        data-checkin="2:00 PM" data-checkout="11:00 AM"
        data-features="{{ $hotel->amenities->pluck('name')->implode(',') }}"
        data-rooms="Standard Room; Deluxe Room; Executive Room; Suite. Exact availability and occupancy can be confirmed on enquiry."
        data-wa="I'm interested in {{ $hotel->title }}, {{ $hotel->destination->name }}. Please share availability and rates.">
        <div class="htl-card-img">
          <img src="placeholder.jpg" alt="{{ $hotel->title }}, {{ $hotel->destination->name }}" loading="lazy" />
          <span class="htl-badge">{{ ucfirst(str_replace('_', ' ', $hotel->category)) }}</span>
          <span class="htl-loc-badge">&#128205; {{ $hotel->destination->name }}</span>
        </div>
        <div class="htl-card-body">
          <h3 class="htl-card-name">{{ $hotel->title }}</h3>
          <p class="htl-card-desc">{{ Str::limit($hotel->description, 100) }}</p>
          <div class="htl-card-meta">
            @foreach($hotel->amenities->take(2) as $amenity)
              <span>{{ $amenity->name }}</span>
            @endforeach
          </div>
          <div class="htl-card-footer">
            <span class="htl-card-timing">Check-in 2:00 PM &middot; Check-out 11:00 AM</span>
            <button class="htl-book-btn">View Details <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 8h10M9 4l4 4-4 4"/></svg></button>
          </div>
        </div>
      </div>
      @endforeach
    </div>
</section>

<!-- ======================================================
     HOTEL DETAIL MODAL
====================================================== -->
<div class="htl-modal-backdrop" id="htlModalBackdrop" role="dialog" aria-modal="true" aria-labelledby="htlModalName">
  <div class="htl-modal" id="htlModal">
    <button class="htl-modal-close" id="htlModalClose" aria-label="Close">X</button>
    <div class="htl-modal-slider-wrap">
      <img class="htl-modal-img" id="htlModalImg" src="" alt="" />
      <button class="htl-modal-nav htl-modal-prev" id="htlModalPrev" style="display:none;">❮</button>
      <button class="htl-modal-nav htl-modal-next" id="htlModalNext" style="display:none;">❯</button>
      <div class="htl-modal-dots" id="htlModalDots"></div>
    </div>
    <div class="htl-modal-body">
      <div class="htl-modal-top">
        <span class="htl-modal-badge" id="htlModalBadge"></span>
        <span class="htl-modal-loc" id="htlModalLoc"></span>
      </div>
      <h2 class="htl-modal-name" id="htlModalName"></h2>
      <p class="htl-modal-desc" id="htlModalDesc"></p>
      <div class="htl-modal-details">
                <div class="htl-modal-detail-item htl-modal-overview">
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
    const parts = raw.split(/[;\n,]/).map(item => item.trim()).filter(Boolean);
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

  let currentImages = [];
  let currentImageIndex = 0;

  const modalImgWrap = document.querySelector('.htl-modal-slider-wrap');
  const modalPrev = document.getElementById('htlModalPrev');
  const modalNext = document.getElementById('htlModalNext');
  const modalDots = document.getElementById('htlModalDots');

  function updateSlider() {
    modalImg.style.opacity = '0';
    setTimeout(() => {
      modalImg.src = currentImages[currentImageIndex];
      modalImg.style.opacity = '1';
    }, 200);

    Array.from(modalDots.children).forEach((dot, idx) => {
      dot.className = idx === currentImageIndex ? 'htl-modal-dot active' : 'htl-modal-dot';
    });
  }

  modalPrev.addEventListener('click', () => {
    currentImageIndex = (currentImageIndex - 1 + currentImages.length) % currentImages.length;
    updateSlider();
  });

  modalNext.addEventListener('click', () => {
    currentImageIndex = (currentImageIndex + 1) % currentImages.length;
    updateSlider();
  });

  function openModal(card) {
    const d = card.dataset;
    currentImages = JSON.parse(d.images || '["placeholder.jpg"]');
    currentImageIndex = 0;

    modalImg.src = currentImages[0];
    modalImg.style.opacity = '1';
    modalImg.alt = d.name;

    modalDots.innerHTML = '';
    if (currentImages.length > 1) {
      modalPrev.style.display = 'flex';
      modalNext.style.display = 'flex';
      currentImages.forEach((_, idx) => {
        const dot = document.createElement('div');
        dot.className = idx === 0 ? 'htl-modal-dot active' : 'htl-modal-dot';
        dot.addEventListener('click', () => {
          currentImageIndex = idx;
          updateSlider();
        });
        modalDots.appendChild(dot);
      });
    } else {
      modalPrev.style.display = 'none';
      modalNext.style.display = 'none';
    }

    modalBadge.textContent = d.badge;
    modalLoc.textContent   = d.location;
    modalName.textContent  = d.name;
    modalLocationDetail.textContent = d.desc;
    modalDesc.textContent  = d.desc;
    modalCi.textContent    = d.checkin;
    modalCo.textContent    = d.checkout;
    renderRoomCategories(d.rooms);
    modalBestFor.textContent = d.attractions;

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
    const btn = card.querySelector('.htl-book-btn');
    if (btn) {
      btn.addEventListener('click', (e) => {
        e.preventDefault();
        e.stopPropagation();
        openModal(card);
      });
    }
    card.addEventListener('click', (e) => {
      // Don't trigger again if the click was on the button (handled above)
      if (!e.target.closest('.htl-book-btn')) {
        openModal(card);
      }
    });
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
