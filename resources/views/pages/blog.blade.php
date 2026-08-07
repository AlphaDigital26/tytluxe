@extends('layouts.frontend')

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
*, *::before, *::after { box-sizing: border-box; }
:root {
  --cream: #f9f6f1;
  --dark: #0f0c08;
  --dark-mid: #1a1510;
  --gold: #b8935a;
  --gold-light: rgba(184,147,90,0.15);
  --text-dark: #1a1108;
  --text-muted: #666;
  --border: rgba(0,0,0,0.08);
  --white: #fff;
  --transition: 0.3s ease;
}

/* ── Hero ────────────────────────────────────────────── */
.blog-hero {
  position: relative;
  height: 82vh;
  min-height: 580px;
  overflow: hidden;
  background: var(--dark);
}
.blog-slider-track {
  display: flex;
  height: 100%;
  width: 100%;
  transition: transform 0.8s cubic-bezier(0.25, 1, 0.5, 1);
}
.blog-hero-slide {
  flex: 0 0 100%;
  position: relative;
  background-size: cover;
  background-position: center;
  display: flex;
  align-items: flex-end;
  padding: 0 0 90px;
}
.blog-hero-slide::before {
  content: '';
  position: absolute;
  inset: 0;
  background-image: linear-gradient(to bottom, rgba(15,12,8,0.2) 0%, rgba(15,12,8,0.9) 100%);
  z-index: -1;
}
.blog-slider-controls {
  position: absolute;
  bottom: 40px;
  left: 50%;
  transform: translateX(-50%);
  z-index: 10;
  display: flex;
  gap: 12px;
}
.blog-dot {
  width: 10px;
  height: 10px;
  border-radius: 50%;
  background: rgba(255,255,255,0.3);
  border: none;
  cursor: pointer;
  padding: 0;
  transition: all var(--transition);
}
.blog-dot.active {
  background: var(--gold);
  transform: scale(1.2);
}
.blog-hero-inner {
  max-width: 1200px;
  width: 100%;
  margin: 0 auto;
  padding: 100px 40px 0; /* top clears fixed navbar; sides give breathing room */
}
.blog-hero-badge {
  display: inline-block;
  background: var(--gold);
  color: #fff;
  font-size: 0.72rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.15em;
  padding: 5px 14px;
  border-radius: 20px;
  margin-bottom: 16px;
}
.blog-hero-title {
  font-family: 'Playfair Display', serif;
  font-size: clamp(2.2rem, 4.5vw, 3.6rem);
  color: #fff;
  line-height: 1.2;
  margin-bottom: 16px;
  max-width: 680px;
}
.blog-hero-meta {
  display: flex;
  align-items: center;
  gap: 20px;
  color: rgba(255,255,255,0.8);
  font-size: 0.9rem;
}
.blog-hero-meta span {
  display: flex;
  align-items: center;
  gap: 6px;
}
.hero-read-btn {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  margin-top: 24px;
  background: var(--gold);
  color: #fff;
  padding: 13px 28px;
  border-radius: 4px;
  font-size: 0.88rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  text-decoration: none;
  transition: background var(--transition), transform var(--transition);
}
.hero-read-btn:hover {
  background: #a17f4b;
  transform: translateY(-2px);
}

/* ── Filter Bar ────────────────────────────────────────── */
.filter-bar {
  background: var(--white);
  border-bottom: 1px solid var(--border);
  position: sticky;
  top: 0;
  z-index: 100;
  box-shadow: 0 2px 20px rgba(0,0,0,0.06);
}
.filter-bar-inner {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 24px;
  display: flex;
  align-items: center;
  flex-wrap: wrap;
  gap: 0;
}
.filter-tab {
  flex-shrink: 0;
  padding: 18px 20px;
  font-size: 0.88rem;
  font-weight: 500;
  color: var(--text-muted);
  cursor: pointer;
  border-bottom: 3px solid transparent;
  transition: all var(--transition);
  white-space: nowrap;
  text-decoration: none;
}
.filter-tab:hover { color: var(--gold); }
.filter-tab.active {
  color: var(--gold);
  border-bottom-color: var(--gold);
  font-weight: 600;
}
.filter-dropdown {
  position: relative;
  display: inline-block;
}
.dropdown-menu {
  display: none;
  position: absolute;
  top: 100%;
  left: 0;
  background: var(--white);
  min-width: 180px;
  box-shadow: 0 8px 24px rgba(0,0,0,0.1);
  border-radius: 4px;
  padding: 8px 0;
  z-index: 200;
  border: 1px solid var(--border);
}
.filter-dropdown:hover .dropdown-menu {
  display: block;
}
.dropdown-menu a {
  display: block;
  padding: 10px 20px;
  color: var(--text-muted);
  font-size: 0.85rem;
  text-decoration: none;
  transition: all var(--transition);
}
.dropdown-menu a:hover {
  background: var(--cream);
  color: var(--gold);
  padding-left: 24px;
}
.filter-search {
  margin-left: auto;
  flex-shrink: 0;
  display: flex;
  align-items: center;
  gap: 8px;
  border-left: 1px solid var(--border);
  padding: 12px 0 12px 20px;
}
.filter-search input {
  border: 1px solid var(--border);
  border-radius: 4px;
  padding: 8px 14px;
  font-size: 0.85rem;
  width: 180px;
  outline: none;
  font-family: inherit;
  transition: border-color var(--transition);
}
.filter-search input:focus { border-color: var(--gold); }

/* ── Page Layout ────────────────────────────────────── */
.blog-page-wrap {
  max-width: 1200px;
  margin: 0 auto;
  padding: 60px 24px 60px;
}


/* ── Section headings ─────────────────────────────── */
.section-label {
  font-size: 0.75rem;
  font-weight: 700;
  letter-spacing: 0.15em;
  text-transform: uppercase;
  color: var(--gold);
  margin-bottom: 6px;
}
.section-title {
  font-family: 'Playfair Display', serif;
  font-size: 1.8rem;
  color: var(--text-dark);
  margin-bottom: 32px;
  padding-bottom: 16px;
  border-bottom: 2px solid var(--border);
}

/* ── Blog Grid ────────────────────────────────────── */
.blog-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 32px;
  margin-bottom: 50px;
  transition: opacity 0.3s ease;
}
@media (max-width: 968px) {
  .blog-grid { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 640px) {
  .blog-grid { grid-template-columns: 1fr; }
}

.blog-card {
  background: var(--white);
  border-radius: 10px;
  overflow: hidden;
  box-shadow: 0 4px 20px rgba(0,0,0,0.05);
  transition: transform var(--transition), box-shadow var(--transition);
  display: flex;
  flex-direction: column;
  text-decoration: none;
}
.blog-card:hover {
  transform: translateY(-6px);
  box-shadow: 0 16px 40px rgba(0,0,0,0.10);
}
.blog-card-img-wrap {
  position: relative;
  overflow: hidden;
  height: 220px;
}
.blog-card-img-wrap img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.5s ease;
}
.blog-card:hover .blog-card-img-wrap img { transform: scale(1.06); }
.card-category-tag {
  position: absolute;
  top: 14px;
  left: 14px;
  background: var(--gold);
  color: #fff;
  font-size: 0.72rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  padding: 4px 10px;
  border-radius: 20px;
}
.blog-card-body {
  padding: 22px;
  flex: 1;
  display: flex;
  flex-direction: column;
}
.blog-card-meta {
  font-size: 0.8rem;
  color: var(--text-muted);
  margin-bottom: 10px;
  display: flex;
  align-items: center;
  gap: 8px;
}
.blog-card-meta .dot { width: 4px; height: 4px; border-radius: 50%; background: var(--text-muted); }
.blog-card-title {
  font-family: 'Playfair Display', serif;
  font-size: 1.15rem;
  color: var(--text-dark);
  line-height: 1.4;
  margin-bottom: 10px;
  transition: color var(--transition);
}
.blog-card:hover .blog-card-title { color: var(--gold); }
.blog-card-excerpt {
  font-size: 0.88rem;
  color: var(--text-muted);
  line-height: 1.6;
  flex: 1;
  margin-bottom: 16px;
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
.read-link {
  font-size: 0.82rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  color: var(--gold);
  display: inline-flex;
  align-items: center;
  gap: 5px;
  text-decoration: none;
  transition: gap var(--transition);
}
.read-link:hover { gap: 10px; }

/* ── Explore More ─────────────────────────────────── */
.explore-more-wrap {
  text-align: center;
  padding-top: 20px;
}
.explore-more-btn {
  display: inline-flex;
  align-items: center;
  gap: 10px;
  border: 2px solid var(--gold);
  color: var(--gold);
  padding: 14px 36px;
  border-radius: 4px;
  font-size: 0.88rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  cursor: pointer;
  background: transparent;
  font-family: inherit;
  transition: all var(--transition);
}
.explore-more-btn:hover { background: var(--gold); color: #fff; }

/* ── Sidebar ──────────────────────────────────────── */
.sidebar {
  display: flex;
  flex-direction: column;
  gap: 32px;
}

/* CTA Widget */
.cta-widget {
  background: linear-gradient(135deg, var(--dark-mid) 0%, #2a1f12 100%);
  border-radius: 10px;
  padding: 32px;
  text-align: center;
  position: relative;
  overflow: hidden;
}
.cta-widget::before {
  content: '';
  position: absolute;
  top: -30px; right: -30px;
  width: 120px; height: 120px;
  border-radius: 50%;
  background: rgba(184,147,90,0.15);
}
.cta-widget-icon {
  font-size: 2rem;
  margin-bottom: 14px;
}
.cta-widget h4 {
  font-family: 'Playfair Display', serif;
  font-size: 1.3rem;
  color: #fff;
  margin-bottom: 10px;
}
.cta-widget p {
  font-size: 0.88rem;
  color: rgba(255,255,255,0.7);
  line-height: 1.6;
  margin-bottom: 22px;
}
.cta-widget-btn {
  display: block;
  background: var(--gold);
  color: #fff;
  padding: 14px 24px;
  border-radius: 4px;
  font-size: 0.88rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  text-decoration: none;
  transition: background var(--transition);
}
.cta-widget-btn:hover { background: #a17f4b; }

/* Newsletter Widget */
.nl-widget {
  background: var(--white);
  border-radius: 10px;
  padding: 28px;
  box-shadow: 0 4px 20px rgba(0,0,0,0.05);
}
.nl-widget h4 {
  font-family: 'Playfair Display', serif;
  font-size: 1.25rem;
  color: var(--text-dark);
  margin-bottom: 8px;
}
.nl-widget p {
  font-size: 0.85rem;
  color: var(--text-muted);
  line-height: 1.6;
  margin-bottom: 18px;
}
.nl-widget input {
  width: 100%;
  border: 1px solid rgba(184,147,90,0.3);
  border-radius: 4px;
  padding: 12px 14px;
  font-size: 0.88rem;
  font-family: inherit;
  margin-bottom: 10px;
  outline: none;
  background: var(--cream);
  transition: border-color var(--transition), box-shadow var(--transition);
}
.nl-widget input:focus {
  border-color: var(--gold);
  box-shadow: 0 0 0 3px rgba(184,147,90,0.12);
}
.nl-widget button {
  width: 100%;
  background: linear-gradient(135deg, var(--gold), #d4a96a);
  color: #fff;
  border: none;
  border-radius: 4px;
  padding: 13px;
  font-size: 0.88rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  font-family: inherit;
  cursor: pointer;
  box-shadow: 0 4px 12px rgba(184,147,90,0.3);
  transition: all var(--transition);
}
.nl-widget button:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 18px rgba(184,147,90,0.4);
}

/* Categories Widget */
.cat-widget {
  background: var(--white);
  border-radius: 10px;
  padding: 28px;
  box-shadow: 0 4px 20px rgba(0,0,0,0.05);
}
.cat-widget h4 {
  font-family: 'Playfair Display', serif;
  font-size: 1.25rem;
  color: var(--text-dark);
  margin-bottom: 20px;
  padding-bottom: 12px;
  border-bottom: 2px solid var(--border);
}
.cat-list { list-style: none; margin: 0; padding: 0; }
.cat-list li { margin-bottom: 4px; }
.cat-list a {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 10px 12px;
  border-radius: 6px;
  text-decoration: none;
  color: var(--text-muted);
  font-size: 0.9rem;
  transition: all var(--transition);
}
.cat-list a:hover { background: var(--gold-light); color: var(--gold); padding-left: 18px; }
.cat-count {
  background: var(--cream);
  padding: 2px 9px;
  border-radius: 20px;
  font-size: 0.78rem;
  color: var(--text-dark);
  font-weight: 600;
}

/* Services Widget */
.services-widget {
  background: var(--cream);
  border-radius: 10px;
  padding: 28px;
  border: 1px solid rgba(184,147,90,0.2);
}
.services-widget h4 {
  font-family: 'Playfair Display', serif;
  font-size: 1.2rem;
  color: var(--text-dark);
  margin-bottom: 18px;
}
.service-links { display: flex; flex-direction: column; gap: 10px; }
.service-link {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 12px 14px;
  background: var(--white);
  border-radius: 6px;
  text-decoration: none;
  color: var(--text-dark);
  font-size: 0.88rem;
  font-weight: 500;
  border: 1px solid var(--border);
  transition: all var(--transition);
}
.service-link:hover { border-color: var(--gold); color: var(--gold); transform: translateX(4px); }
.service-link-icon { font-size: 1.1rem; }

/* ── Popular Destinations ─────────────────────────── */
.destinations-strip {
  background: var(--dark-mid);
  padding: 60px 0;
  margin-top: 0;
}
.destinations-strip-inner {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 24px;
}
.destinations-strip h2 {
  font-family: 'Playfair Display', serif;
  font-size: 2rem;
  color: #fff;
  margin-bottom: 8px;
}
.destinations-strip p {
  color: rgba(255,255,255,0.6);
  margin-bottom: 32px;
  font-size: 0.95rem;
}
.dest-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 16px;
}
@media (max-width: 768px) { .dest-grid { grid-template-columns: 1fr 1fr; } }
.dest-card {
  position: relative;
  border-radius: 8px;
  overflow: hidden;
  height: 180px;
  text-decoration: none;
}
.dest-card img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.5s ease;
}
.dest-card:hover img { transform: scale(1.1); }
.dest-card-overlay {
  position: absolute;
  inset: 0;
  background: linear-gradient(to top, rgba(0,0,0,0.7) 0%, transparent 60%);
  display: flex;
  align-items: flex-end;
  padding: 16px;
}
.dest-card-name {
  font-family: 'Playfair Display', serif;
  color: #fff;
  font-size: 1.05rem;
  font-weight: 600;
}
.dest-card-count {
  font-size: 0.75rem;
  color: var(--gold);
  display: block;
}
</style>
@endpush

@section('content')

{{-- ── HERO ────────────────────────────────────────── --}}
<section class="blog-hero" id="blogHeroSlider">
  <div class="blog-slider-track">
    {{-- Slide 1 (Jaipur) --}}
    <div class="blog-hero-slide" style="background-image: url('https://images.unsplash.com/photo-1605806616949-1e87b487cb2a?auto=format&fit=crop&w=1920&q=80');">
      <div class="blog-hero-inner">
        <span class="blog-hero-badge">✦ New Arrival</span>
        <h1 class="blog-hero-title">Secret Spots in the Pink City: A Detailed Guide to Jaipur's Hidden Wonders</h1>
        <div class="blog-hero-meta">
          <span><svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg> Aug 15, 2026</span>
          <span><svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg> 10 min read</span>
          <span>Cultural Journeys</span>
        </div>
        <a href="{{ route('blog.jaipur') }}" class="hero-read-btn">Read Article <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg></a>
      </div>
    </div>

    {{-- Slide 2 (Maldives) --}}
    <div class="blog-hero-slide" style="background-image: url('https://images.unsplash.com/photo-1476514525535-07fb3b4ae5f1?auto=format&fit=crop&w=1920&q=80');">
      <div class="blog-hero-inner">
        <span class="blog-hero-badge">✦ Featured Story</span>
        <h1 class="blog-hero-title">10 Hidden Gems in the Maldives for Your Next Luxury Staycation</h1>
        <div class="blog-hero-meta">
          <span><svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg> Aug 6, 2026</span>
          <span><svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg> 6 min read</span>
          <span>Destination Guide</span>
        </div>
        <a href="{{ route('blog.details') }}" class="hero-read-btn">Read Article <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg></a>
      </div>
    </div>

    {{-- Slide 3 (Paris) --}}
    <div class="blog-hero-slide" style="background-image: url('https://images.unsplash.com/photo-1502602898657-3e91760cbb34?auto=format&fit=crop&w=1920&q=80');">
      <div class="blog-hero-inner">
        <span class="blog-hero-badge">✦ Editor's Pick</span>
        <h1 class="blog-hero-title">A Taste of Elegance: Dining Through the Streets of Paris</h1>
        <div class="blog-hero-meta">
          <span><svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg> Jul 28, 2026</span>
          <span><svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg> 5 min read</span>
          <span>Culinary</span>
        </div>
        <a href="{{ route('blog.details') }}" class="hero-read-btn">Read Article <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg></a>
      </div>
    </div>
  </div>

  <div class="blog-slider-controls">
    <button class="blog-dot active" data-index="0"></button>
    <button class="blog-dot" data-index="1"></button>
    <button class="blog-dot" data-index="2"></button>
  </div>
</section>

{{-- ── FILTER BAR ─────────────────────────────────── --}}
<nav class="filter-bar">
  <div class="filter-bar-inner">
    <a class="filter-tab active" href="#">All Blogs</a>
    <div class="filter-dropdown">
      <div class="filter-tab">Destinations ▾</div>
      <div class="dropdown-menu">
        <a href="#">Maldives</a>
        <a href="#">Paris</a>
        <a href="#">Dubai</a>
        <a href="#">Tokyo</a>
        <a href="#">New York</a>
      </div>
    </div>
    <a class="filter-tab" href="#">Luxury Hotels</a>
    <a class="filter-tab" href="#">Cruises</a>
    <a class="filter-tab" href="#">Staycations</a>
    <a class="filter-tab" href="#">Travel Tips</a>
    <a class="filter-tab" href="#">Wellness</a>
    <a class="filter-tab" href="#">Food & Culture</a>
    <div class="filter-search">
      <input type="text" placeholder="🔍 Search blogs...">
    </div>
  </div>
</nav>

{{-- ── MAIN CONTENT ────────────────────────────────── --}}
<div class="blog-page-wrap">

  {{-- LEFT: Blog Grid --}}
  <main>
    <p class="section-label">Latest Stories</p>
    <h2 class="section-title">From Our Travel Journal</h2>

    <div class="blog-grid">
    
      {{-- Card Jaipur --}}
      <a href="{{ route('blog.jaipur') }}" class="blog-card">
        <div class="blog-card-img-wrap">
          <img src="https://images.unsplash.com/photo-1605806616949-1e87b487cb2a?auto=format&fit=crop&w=800&q=80" alt="Jaipur">
          <span class="card-category-tag">Cultural Journeys</span>
        </div>
        <div class="blog-card-body">
          <div class="blog-card-meta">
            <span>Aug 15, 2026</span><span class="dot"></span><span>10 min read</span>
          </div>
          <h3 class="blog-card-title">Secret Spots in the Pink City: A Detailed Guide to Jaipur's Hidden Wonders</h3>
          <p class="blog-card-excerpt">Explore the serene stepwells, hidden artisan quarters, and majestic havelis that lie just beyond the typical tourist path.</p>
          <span class="read-link">Read Article →</span>
        </div>
      </a>

      {{-- Card 1 --}}
      <a href="{{ route('blog.details') }}" class="blog-card">
        <div class="blog-card-img-wrap">
          <img src="https://images.unsplash.com/photo-1502602898657-3e91760cbb34?auto=format&fit=crop&w=800&q=80" alt="Paris Streets">
          <span class="card-category-tag">Culinary</span>
        </div>
        <div class="blog-card-body">
          <div class="blog-card-meta">
            <span>Jul 28, 2026</span><span class="dot"></span><span>5 min read</span>
          </div>
          <h3 class="blog-card-title">A Taste of Elegance: Dining Through the Streets of Paris</h3>
          <p class="blog-card-excerpt">From hidden Michelin-starred bistros to the finest patisseries in Montmartre, explore the culinary wonders of the French capital.</p>
          <span class="read-link">Read Article →</span>
        </div>
      </a>

      {{-- Card 2 --}}
      <a href="{{ route('blog.details') }}" class="blog-card">
        <div class="blog-card-img-wrap">
          <img src="https://images.unsplash.com/photo-1599640842225-85d111c60e6b?auto=format&fit=crop&w=800&q=80" alt="Luxury Cruise">
          <span class="card-category-tag">Cruises</span>
        </div>
        <div class="blog-card-body">
          <div class="blog-card-meta">
            <span>Jul 20, 2026</span><span class="dot"></span><span>4 min read</span>
          </div>
          <h3 class="blog-card-title">The Ultimate Packing List for a Luxury Cruise Voyage</h3>
          <p class="blog-card-excerpt">Everything you need for a spectacular voyage, from formal evening wear to effortless daytime excursion outfits.</p>
          <span class="read-link">Read Article →</span>
        </div>
      </a>

      {{-- Card 3 --}}
      <a href="{{ route('blog.details') }}" class="blog-card">
        <div class="blog-card-img-wrap">
          <img src="https://images.unsplash.com/photo-1537996194471-e657df975ab4?auto=format&fit=crop&w=800&q=80" alt="Bali Retreat">
          <span class="card-category-tag">Wellness</span>
        </div>
        <div class="blog-card-body">
          <div class="blog-card-meta">
            <span>Jul 15, 2026</span><span class="dot"></span><span>7 min read</span>
          </div>
          <h3 class="blog-card-title">Top 5 Wellness Retreats in Bali That Will Rejuvenate Your Soul</h3>
          <p class="blog-card-excerpt">Find inner peace in these exclusive lush jungle sanctuaries located in the spiritual heart of Bali.</p>
          <span class="read-link">Read Article →</span>
        </div>
      </a>

      {{-- Card 4 --}}
      <a href="{{ route('blog.details') }}" class="blog-card">
        <div class="blog-card-img-wrap">
          <img src="https://images.unsplash.com/photo-1491555103944-7c647fd857e6?auto=format&fit=crop&w=800&q=80" alt="Swiss Alps">
          <span class="card-category-tag">Adventure</span>
        </div>
        <div class="blog-card-body">
          <div class="blog-card-meta">
            <span>Jul 10, 2026</span><span class="dot"></span><span>8 min read</span>
          </div>
          <h3 class="blog-card-title">Exploring the Swiss Alps: A Complete Winter Luxury Guide</h3>
          <p class="blog-card-excerpt">Discover the most exclusive ski resorts, cozy chalets, and breathtaking alpine experiences in Switzerland.</p>
          <span class="read-link">Read Article →</span>
        </div>
      </a>

      {{-- Card 5 --}}
      <a href="{{ route('blog.details') }}" class="blog-card">
        <div class="blog-card-img-wrap">
          <img src="https://images.unsplash.com/photo-1528360983277-13d401cdc186?auto=format&fit=crop&w=800&q=80" alt="Dubai">
          <span class="card-category-tag">Staycation</span>
        </div>
        <div class="blog-card-body">
          <div class="blog-card-meta">
            <span>Jul 5, 2026</span><span class="dot"></span><span>6 min read</span>
          </div>
          <h3 class="blog-card-title">Why Dubai Is the World's Ultimate Luxury Staycation Hub</h3>
          <p class="blog-card-excerpt">Experience sky-high opulence, iconic architecture, and desert adventures all within one extraordinary city.</p>
          <span class="read-link">Read Article →</span>
        </div>
      </a>

      {{-- Card 6 --}}
      <a href="{{ route('blog.details') }}" class="blog-card">
        <div class="blog-card-img-wrap">
          <img src="https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=800&q=80" alt="Beach">
          <span class="card-category-tag">Destinations</span>
        </div>
        <div class="blog-card-body">
          <div class="blog-card-meta">
            <span>Jun 28, 2026</span><span class="dot"></span><span>5 min read</span>
          </div>
          <h3 class="blog-card-title">12 Secluded Beaches Around the World That Only Insiders Know</h3>
          <p class="blog-card-excerpt">Leave the crowds behind and discover pristine shores that still feel untouched by mass tourism.</p>
          <span class="read-link">Read Article →</span>
        </div>
      </a>

    </div>

  </main>

  </main>
</div>

{{-- ── DESTINATIONS STRIP ─────────────────────────── --}}
<section class="destinations-strip">
  <div class="destinations-strip-inner">
    <h2>Explore by Destination</h2>
    <p>Find travel stories, tips, and hidden gems across the world's most luxurious destinations.</p>
    <div class="dest-grid">
      <a href="{{ route('hotels') }}" class="dest-card">
        <img src="https://images.unsplash.com/photo-1512453979798-5ea266f8880c?auto=format&fit=crop&w=600&q=80" alt="Dubai">
        <div class="dest-card-overlay">
          <div>
            <span class="dest-card-name">Dubai</span>
            <span class="dest-card-count">8 stories</span>
          </div>
        </div>
      </a>
      <a href="{{ route('hotels') }}" class="dest-card">
        <img src="https://images.unsplash.com/photo-1499793983690-e29da59ef1c2?auto=format&fit=crop&w=600&q=80" alt="Maldives">
        <div class="dest-card-overlay">
          <div>
            <span class="dest-card-name">Maldives</span>
            <span class="dest-card-count">11 stories</span>
          </div>
        </div>
      </a>
      <a href="{{ route('hotels') }}" class="dest-card">
        <img src="https://images.unsplash.com/photo-1502602898657-3e91760cbb34?auto=format&fit=crop&w=600&q=80" alt="Paris">
        <div class="dest-card-overlay">
          <div>
            <span class="dest-card-name">Paris</span>
            <span class="dest-card-count">6 stories</span>
          </div>
        </div>
      </a>
      <a href="{{ route('hotels') }}" class="dest-card">
        <img src="https://images.unsplash.com/photo-1537996194471-e657df975ab4?auto=format&fit=crop&w=600&q=80" alt="Bali">
        <div class="dest-card-overlay">
          <div>
            <span class="dest-card-name">Bali</span>
            <span class="dest-card-count">9 stories</span>
          </div>
        </div>
      </a>
    </div>
  </div>
</section>

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', () => {
    // 1. Filter Tabs
    const filterTabs = document.querySelectorAll('.filter-tab:not(.dropdown-toggle), .dropdown-menu a');
    const blogCards = document.querySelectorAll('.blog-card');
    
    // Add dropdown toggle class so we can ignore it
    const dropdownTab = document.querySelector('.filter-dropdown .filter-tab');
    if(dropdownTab) dropdownTab.classList.add('dropdown-toggle');

    filterTabs.forEach(tab => {
      tab.addEventListener('click', (e) => {
        e.preventDefault();
        
        // Update active class state
        document.querySelectorAll('.filter-tab').forEach(t => t.classList.remove('active'));
        const parentTab = e.target.closest('.filter-dropdown') ? e.target.closest('.filter-dropdown').querySelector('.filter-tab') : e.target;
        if(parentTab) parentTab.classList.add('active');

        const filterValue = e.target.innerText.trim().toLowerCase();
        const displayValue = e.target.innerText.trim();
        
        // Visual cue
        const sectionTitle = document.querySelector('.section-title');
        if(sectionTitle) {
          sectionTitle.innerText = filterValue === 'all blogs' ? 'From Our Travel Journal' : 'Showing results for: ' + displayValue.replace(' ▾', '');
        }
        
        const exploreWrap = document.querySelector('.explore-more-wrap');
        if (exploreWrap) {
          exploreWrap.style.display = filterValue === 'all blogs' ? 'block' : 'none';
        }
        const grid = document.querySelector('.blog-grid');
        if(grid) {
          grid.style.opacity = '0.2';
          setTimeout(() => { grid.style.opacity = '1'; }, 300);
        }
        
        blogCards.forEach(card => {
          const textContent = card.innerText.toLowerCase();
          let matchValue = filterValue;
          
          // Map some tab names to actual tags/keywords present in cards
          if (filterValue === 'food & culture') matchValue = 'culinary';
          if (filterValue === 'staycations') matchValue = 'staycation';
          if (filterValue === 'travel tips') matchValue = 'guide';
          if (filterValue === 'luxury hotels') matchValue = 'luxury';
          
          if (filterValue === 'all blogs') {
            card.style.display = 'flex';
          } else if (textContent.includes(matchValue) || textContent.includes(filterValue)) {
            card.style.display = 'flex';
          } else {
            card.style.display = 'none';
          }
        });
      });
    });

    // 2. Search Bar
    const searchInput = document.querySelector('.filter-search input');
    if (searchInput) {
      searchInput.addEventListener('input', (e) => {
        const searchTerm = e.target.value.toLowerCase();
        
        const sectionTitle = document.querySelector('.section-title');
        if(sectionTitle) sectionTitle.innerText = searchTerm ? 'Search Results' : 'From Our Travel Journal';
        
        document.querySelectorAll('.filter-tab').forEach(t => t.classList.remove('active'));
        
        blogCards.forEach(card => {
          const textContent = card.innerText.toLowerCase();
          if (textContent.includes(searchTerm)) {
            card.style.display = 'flex';
          } else {
            card.style.display = 'none';
          }
        });
      });
    }

    // 4. Destination Strip Cards
    const destCards = document.querySelectorAll('.dest-card');
    destCards.forEach(card => {
      card.addEventListener('click', (e) => {
        e.preventDefault();
        
        const destName = card.querySelector('.dest-card-name').innerText.trim().toLowerCase();
        
        // Visual cue: Update title and fade grid
        const sectionTitle = document.querySelector('.section-title');
        if(sectionTitle) sectionTitle.innerText = 'Showing results for: ' + card.querySelector('.dest-card-name').innerText.trim();
        
        const grid = document.querySelector('.blog-grid');
        if(grid) {
          grid.style.opacity = '0.2';
          setTimeout(() => { grid.style.opacity = '1'; }, 300);
        }

        // Update active class state (highlight Destinations tab)
        document.querySelectorAll('.filter-tab').forEach(t => t.classList.remove('active'));
        const destTab = document.querySelector('.filter-dropdown .filter-tab');
        if(destTab) destTab.classList.add('active');

        blogCards.forEach(bc => {
          const textContent = bc.innerText.toLowerCase();
          if (textContent.includes(destName)) {
            bc.style.display = 'flex';
          } else {
            bc.style.display = 'none';
          }
        });

        // Scroll up to the title with an offset for sticky headers
        const titleElement = document.querySelector('.section-label');
        if (titleElement) {
          const y = titleElement.getBoundingClientRect().top + window.scrollY - 120;
          window.scrollTo({ top: y, behavior: 'smooth' });
        }
      });
    });

    // 5. Hero Slider Logic
    const track = document.querySelector('.blog-slider-track');
    const slides = document.querySelectorAll('.blog-hero-slide');
    const dots = document.querySelectorAll('.blog-dot');
    let currentSlide = 0;
    let slideInterval;

    function goToSlide(n) {
      dots[currentSlide].classList.remove('active');
      currentSlide = (n + slides.length) % slides.length;
      track.style.transform = `translateX(-${currentSlide * 100}%)`;
      dots[currentSlide].classList.add('active');
    }

    function nextSlide() {
      goToSlide(currentSlide + 1);
    }

    if (slides.length > 0) {
      slideInterval = setInterval(nextSlide, 5000); // Change slide every 5 seconds

      dots.forEach((dot, index) => {
        dot.addEventListener('click', () => {
          clearInterval(slideInterval);
          goToSlide(index);
          slideInterval = setInterval(nextSlide, 5000);
        });
      });
    }

  });
</script>
@endpush

@endsection
