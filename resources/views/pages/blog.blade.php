@extends('layouts.frontend')

@section('meta_title', 'Travel Journal — Luxury Travel Tips, Guides & Inspiration | TYT Luxe')
@section('meta_description', 'Read TYT Luxe Travel Journal for expert travel guides, destination inspiration, packing tips and insider advice on luxury hotels and cruises. Plan your next trip with confidence.')

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
  height: 100%;          /* ← required so align-items:flex-end works */
  position: relative;
  background-size: cover;
  background-position: center;
  display: flex;
  align-items: flex-end; /* content anchored to bottom */
  padding: 0 0 90px;
}
.blog-hero-slide::before {
  content: '';
  position: absolute;
  inset: 0;
  background-image: linear-gradient(to bottom, rgba(15,12,8,0.2) 0%, rgba(15,12,8,0.9) 100%);
  z-index: 0;
}
.blog-hero-inner {
  position: relative;
  z-index: 1;
  max-width: 1200px;
  width: 100%;
  margin: 0 auto;
  padding: 0 40px;       /* no top padding — content sits at bottom of slide */
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
  font-size: clamp(1.6rem, 3.5vw, 2.8rem); /* slightly tighter so long titles fit */
  color: #fff;
  line-height: 1.25;
  margin-bottom: 16px;
  max-width: 680px;
  /* Clamp to 3 lines — never overflow upward into the navbar */
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
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

/* ── No-content placeholders ─────────────────────────── */
.hero-placeholder {
  display: flex;
  align-items: center;
  justify-content: center;
  height: 100%;
  width: 100%;
  background: linear-gradient(135deg, var(--dark-mid) 0%, #2a1f12 100%);
}
.hero-placeholder-inner { text-align: center; padding: 40px; }
.hero-placeholder-inner h2 { font-family:'Playfair Display',serif; color:#fff; font-size: 2rem; margin-bottom: 12px; }
.hero-placeholder-inner p { color: rgba(255,255,255,0.6); font-size: 1rem; }

/* ── Filter Bar ────────────────────────────────────────── */
.filter-bar {
  background: var(--white);
  border-bottom: 1px solid var(--border);
  position: sticky;
  top: 0;
  z-index: 100;
  box-shadow: 0 4px 24px rgba(0,0,0,0.04);
}
.filter-bar-inner {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 24px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 20px;
  height: 64px;
}
.filter-tabs-container {
  display: flex;
  align-items: center;
  gap: 8px;
  overflow-x: auto;
  scrollbar-width: none; /* Firefox */
  -ms-overflow-style: none; /* IE/Edge */
  flex-grow: 1;
}
.filter-tabs-container::-webkit-scrollbar {
  display: none; /* Chrome/Safari */
}
.filter-tab {
  flex-shrink: 0;
  padding: 8px 16px;
  font-size: 0.9rem;
  font-weight: 500;
  color: var(--text-muted);
  cursor: pointer;
  border-radius: 30px;
  transition: all var(--transition);
  white-space: nowrap;
  text-decoration: none;
  background: transparent;
}
.filter-tab:hover {
  color: var(--dark);
  background: rgba(0,0,0,0.04);
}
.filter-tab.active {
  color: #fff;
  background: var(--gold);
  font-weight: 500;
}
.filter-search {
  flex-shrink: 0;
  display: flex;
  align-items: center;
  background: rgba(0,0,0,0.03);
  border-radius: 30px;
  padding: 0 16px;
  height: 40px;
  width: 240px;
  transition: all var(--transition);
  border: 1px solid transparent;
}
.filter-search:focus-within {
  background: #fff;
  border-color: var(--gold);
  box-shadow: 0 0 0 3px var(--gold-light);
}
.filter-search .search-icon {
  color: var(--text-muted);
  font-size: 0.85rem;
}
.filter-search input {
  border: none;
  background: transparent;
  padding: 8px 12px;
  font-size: 0.9rem;
  width: 100%;
  outline: none;
  font-family: inherit;
  color: var(--dark);
}
.filter-search input::placeholder {
  color: #999;
}

@media (max-width: 768px) {
  .filter-bar-inner {
    flex-direction: column;
    height: auto;
    padding: 12px 24px;
    gap: 12px;
  }
  .filter-search {
    width: 100%;
  }
}

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

/* ── Empty state ──────────────────────────────────── */
.empty-state {
  grid-column: 1 / -1;
  text-align: center;
  padding: 60px 20px;
  color: var(--text-muted);
}
.empty-state svg { margin-bottom: 16px; opacity: 0.3; }
.empty-state p { font-size: 1rem; }

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
  cursor: pointer;
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
.dest-placeholder {
  text-align: center;
  color: rgba(255,255,255,0.4);
  padding: 40px;
  grid-column: 1 / -1;
}
</style>
@endpush

@section('content')

{{-- ── HERO CAROUSEL ────────────────────────────────────────── --}}
<section class="blog-hero" id="blogHeroSlider">
  @if($trendingPosts->isNotEmpty())
    <div class="blog-slider-track">
      @foreach($trendingPosts as $post)
        <div class="blog-hero-slide" style="background-image: url('{{ $post->cover_image_url ?? 'https://images.unsplash.com/photo-1476514525535-07fb3b4ae5f1?auto=format&fit=crop&w=1920&q=80' }}');">
          <div class="blog-hero-inner">
            <span class="blog-hero-badge">✦ {{ $post->is_trending ? 'Trending' : 'Featured Story' }}</span>
            <h1 class="blog-hero-title">{{ $post->title }}</h1>
            <div class="blog-hero-meta">
              @if($post->published_at)
                <span>
                  <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                  {{ $post->published_at->format('M j, Y') }}
                </span>
              @endif
              <span>
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                {{ $post->read_time_minutes }} min read
              </span>
              @if($post->category)
                <span>{{ $post->category->name }}</span>
              @endif
            </div>
            <a href="{{ route('blog.details') }}" class="hero-read-btn">
              Read Article
              <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
            </a>
          </div>
        </div>
      @endforeach
    </div>

    <div class="blog-slider-controls">
      @foreach($trendingPosts as $i => $post)
        <button class="blog-dot {{ $i === 0 ? 'active' : '' }}" data-index="{{ $i }}"></button>
      @endforeach
    </div>

  @else
    {{-- Fallback when no trending posts exist --}}
    <div class="hero-placeholder">
      <div class="hero-placeholder-inner">
        <h2>Travel Journal</h2>
        <p>No trending stories yet — add blog posts and mark them as Trending in the admin panel.</p>
      </div>
    </div>
  @endif
</section>

{{-- ── FILTER BAR ─────────────────────────────────── --}}
<nav class="filter-bar">
  <div class="filter-bar-inner">
    <div class="filter-tabs-container">
      <a class="filter-tab active" href="#" data-category="all">All Blogs</a>
      @foreach($categories as $cat)
        <a class="filter-tab" href="#" data-category="{{ $cat->slug }}">{{ $cat->name }}</a>
      @endforeach
    </div>
    <div class="filter-search">
      <i class="fa-solid fa-magnifying-glass search-icon"></i>
      <input type="text" placeholder="Search blogs..." id="blogSearchInput">
    </div>
  </div>
</nav>

{{-- ── MAIN CONTENT ────────────────────────────────── --}}
<div class="blog-page-wrap">
  <main>
    <p class="section-label">Latest Stories</p>
    <h2 class="section-title" id="sectionTitle">From Our Travel Journal</h2>

    <div class="blog-grid" id="blogGrid">
      @forelse($posts as $post)
        <a href="{{ route('blog.details') }}" class="blog-card" data-category="{{ $post->category?->slug ?? '' }}">
          <div class="blog-card-img-wrap">
            <img
              src="{{ $post->cover_image_url ?? 'https://images.unsplash.com/photo-1476514525535-07fb3b4ae5f1?auto=format&fit=crop&w=800&q=80' }}"
              alt="{{ $post->title }}"
              loading="lazy"
            >
            @if($post->category)
              <span class="card-category-tag">{{ $post->category->name }}</span>
            @endif
          </div>
          <div class="blog-card-body">
            <div class="blog-card-meta">
              @if($post->published_at)
                <span>{{ $post->published_at->format('M j, Y') }}</span>
                <span class="dot"></span>
              @endif
              <span>{{ $post->read_time_minutes }} min read</span>
            </div>
            <h3 class="blog-card-title">{{ $post->title }}</h3>
            @if($post->excerpt)
              <p class="blog-card-excerpt">{{ $post->excerpt }}</p>
            @endif
            <span class="read-link">Read Article →</span>
          </div>
        </a>
      @empty
        <div class="empty-state">
          <svg width="48" height="48" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
            <path d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10l6 6v8a2 2 0 01-2 2z"/>
            <polyline points="17 21 17 13 7 13 7 21"/>
            <polyline points="7 3 7 8 15 8"/>
          </svg>
          <p>No blog posts yet. Add some from the admin panel!</p>
        </div>
      @endforelse
    </div>
  </main>
</div>

{{-- ── DESTINATIONS STRIP ─────────────────────────── --}}
<section class="destinations-strip">
  <div class="destinations-strip-inner">
    <h2>Explore by Destination</h2>
    <p>Find travel stories, tips, and hidden gems across the world's most luxurious destinations.</p>
    <div class="dest-grid">
      @forelse($destinations as $dest)
        <div class="dest-card" data-dest="{{ $dest->name }}">
          @if($dest->image_url)
            <img src="{{ $dest->image_url }}" alt="{{ $dest->name }}" loading="lazy">
          @else
            <img src="https://images.unsplash.com/photo-1476514525535-07fb3b4ae5f1?auto=format&fit=crop&w=600&q=80" alt="{{ $dest->name }}" loading="lazy">
          @endif
          <div class="dest-card-overlay">
            <div>
              <span class="dest-card-name">{{ $dest->name }}</span>
              <span class="dest-card-count">{{ $dest->story_count }} {{ Str::plural('story', $dest->story_count) }}</span>
            </div>
          </div>
        </div>
      @empty
        <div class="dest-placeholder">
          <p>No featured destinations yet. Add some from the admin panel!</p>
        </div>
      @endforelse
    </div>
  </div>
</section>

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', () => {
    const filterTabs  = document.querySelectorAll('.filter-tab');
    const blogCards   = document.querySelectorAll('.blog-card');
    const sectionTitle = document.getElementById('sectionTitle');
    const grid        = document.getElementById('blogGrid');
    const searchInput = document.getElementById('blogSearchInput');

    // ── Filter Tabs ────────────────────────────────────
    filterTabs.forEach(tab => {
      tab.addEventListener('click', (e) => {
        e.preventDefault();
        filterTabs.forEach(t => t.classList.remove('active'));
        tab.classList.add('active');

        const category = tab.dataset.category;
        const label    = tab.textContent.trim();

        if (sectionTitle) {
          sectionTitle.textContent = category === 'all'
            ? 'From Our Travel Journal'
            : 'Showing: ' + label;
        }

        fadeGrid(() => {
          blogCards.forEach(card => {
            if (category === 'all') {
              card.style.display = 'flex';
            } else {
              card.style.display = (card.dataset.category === category) ? 'flex' : 'none';
            }
          });
        });
      });
    });

    // ── Search Bar ──────────────────────────────────────
    if (searchInput) {
      searchInput.addEventListener('input', (e) => {
        const term = e.target.value.toLowerCase().trim();
        filterTabs.forEach(t => t.classList.remove('active'));
        if (sectionTitle) {
          sectionTitle.textContent = term ? 'Search Results' : 'From Our Travel Journal';
        }
        blogCards.forEach(card => {
          card.style.display = card.textContent.toLowerCase().includes(term) ? 'flex' : 'none';
        });
      });
    }

    // ── Destination Cards ────────────────────────────────
    document.querySelectorAll('.dest-card').forEach(card => {
      card.addEventListener('click', () => {
        const destName = (card.dataset.dest || '').toLowerCase();
        if (sectionTitle) {
          sectionTitle.textContent = 'Showing results for: ' + card.dataset.dest;
        }

        filterTabs.forEach(t => t.classList.remove('active'));

        fadeGrid(() => {
          blogCards.forEach(bc => {
            bc.style.display = bc.textContent.toLowerCase().includes(destName) ? 'flex' : 'none';
          });
        });

        const titleEl = document.querySelector('.section-label');
        if (titleEl) {
          const y = titleEl.getBoundingClientRect().top + window.scrollY - 120;
          window.scrollTo({ top: y, behavior: 'smooth' });
        }
      });
    });

    // ── Hero Slider ──────────────────────────────────────
    const track  = document.querySelector('.blog-slider-track');
    const slides = document.querySelectorAll('.blog-hero-slide');
    const dots   = document.querySelectorAll('.blog-dot');
    let currentSlide = 0;
    let slideInterval;

    function goToSlide(n) {
      if (!dots[currentSlide]) return;
      dots[currentSlide].classList.remove('active');
      currentSlide = (n + slides.length) % slides.length;
      if (track) track.style.transform = `translateX(-${currentSlide * 100}%)`;
      if (dots[currentSlide]) dots[currentSlide].classList.add('active');
    }

    if (slides.length > 1) {
      slideInterval = setInterval(() => goToSlide(currentSlide + 1), 5000);
      dots.forEach((dot, i) => {
        dot.addEventListener('click', () => {
          clearInterval(slideInterval);
          goToSlide(i);
          slideInterval = setInterval(() => goToSlide(currentSlide + 1), 5000);
        });
      });
    }

    // ── Helpers ──────────────────────────────────────────
    function fadeGrid(cb) {
      if (grid) {
        grid.style.opacity = '0.2';
        setTimeout(() => { cb(); grid.style.opacity = '1'; }, 200);
      } else {
        cb();
      }
    }
  });
</script>
@endpush

@endsection
