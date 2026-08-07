@extends('layouts.frontend')

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
*, *::before, *::after { box-sizing: border-box; }
:root {
  --cream: #f9f6f1;
  --dark: #0f0c08;
  --dark-mid: #1a1510;
  --gold: #b8935a;
  --gold-light: rgba(184,147,90,0.12);
  --text-dark: #1a1108;
  --text-body: #3d3d3d;
  --text-muted: #777;
  --border: rgba(0,0,0,0.08);
  --white: #fff;
  --transition: 0.3s ease;
}

/* ── Hero ────────────────────────────────────────── */
.detail-hero {
  position: relative;
  height: 65vh;
  min-height: 480px;
  background-image: linear-gradient(to bottom, rgba(15,12,8,0.1) 0%, rgba(15,12,8,0.75) 100%),
    url('https://images.unsplash.com/photo-1499793983690-e29da59ef1c2?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80');
  background-size: cover;
  background-position: center;
  display: flex;
  align-items: flex-end;
  padding-bottom: 60px;
}
.detail-hero-inner {
  max-width: 860px;
  margin: 0 auto;
  padding: 0 24px;
  width: 100%;
}
.detail-category-badge {
  display: inline-block;
  background: var(--gold);
  color: #fff;
  font-size: 0.72rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.15em;
  padding: 5px 14px;
  border-radius: 20px;
  margin-bottom: 18px;
}
.detail-hero-title {
  font-family: 'Playfair Display', serif;
  font-size: clamp(2rem, 4vw, 3.2rem);
  color: #fff;
  line-height: 1.2;
  margin-bottom: 20px;
}
.detail-hero-meta {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 20px;
  color: rgba(255,255,255,0.75);
  font-size: 0.88rem;
}
.detail-hero-meta span {
  display: flex;
  align-items: center;
  gap: 6px;
}

/* ── Breadcrumb ──────────────────────────────────── */
.breadcrumb {
  background: var(--cream);
  border-bottom: 1px solid var(--border);
  padding: 12px 24px;
}
.breadcrumb-inner {
  max-width: 860px;
  margin: 0 auto;
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 0.82rem;
  color: var(--text-muted);
}
.breadcrumb-inner a { color: var(--gold); text-decoration: none; }
.breadcrumb-sep { color: var(--text-muted); }

/* ── Content Layout ──────────────────────────────── */
.detail-content-wrap {
  max-width: 860px;
  margin: 50px auto 0;
  padding: 0 24px 80px;
}

/* Progress bar */
.reading-progress {
  position: fixed;
  top: 0;
  left: 0;
  height: 3px;
  background: linear-gradient(90deg, var(--gold), #d4a96a);
  z-index: 9999;
  width: 0%;
  transition: width 0.1s linear;
}

/* Article body */
.article-body {
  font-family: 'Poppins', sans-serif;
  font-size: 1.05rem;
  line-height: 1.85;
  color: var(--text-body);
}
.article-body p { margin-bottom: 28px; }
.article-body h2 {
  font-family: 'Playfair Display', serif;
  font-size: 1.9rem;
  color: var(--text-dark);
  margin: 48px 0 18px;
  line-height: 1.25;
}
.article-body h3 {
  font-family: 'Playfair Display', serif;
  font-size: 1.4rem;
  color: var(--text-dark);
  margin: 36px 0 14px;
}
.article-body img {
  width: 100%;
  max-height: 450px;
  object-fit: cover;
  border-radius: 10px;
  margin: 36px 0;
  display: block;
}
.article-body img + em {
  display: block;
  text-align: center;
  font-size: 0.82rem;
  color: var(--text-muted);
  margin-top: -24px;
  margin-bottom: 32px;
}
.article-blockquote {
  border-left: 4px solid var(--gold);
  margin: 40px 0;
  padding: 20px 28px;
  background: var(--gold-light);
  border-radius: 0 8px 8px 0;
}
.article-blockquote p {
  font-family: 'Playfair Display', serif;
  font-size: 1.3rem;
  font-style: italic;
  color: var(--text-dark);
  margin: 0;
  line-height: 1.5;
}
.article-blockquote cite {
  display: block;
  margin-top: 12px;
  font-size: 0.82rem;
  color: var(--gold);
  font-style: normal;
  font-weight: 600;
}

/* Number list */
.gem-list { list-style: none; padding: 0; margin: 0 0 28px; counter-reset: gem; }
.gem-list li {
  counter-increment: gem;
  display: flex;
  gap: 18px;
  margin-bottom: 22px;
  background: var(--white);
  padding: 20px;
  border-radius: 8px;
  box-shadow: 0 4px 16px rgba(0,0,0,0.04);
  border-left: 3px solid var(--gold);
}
.gem-list li::before {
  content: counter(gem, decimal-leading-zero);
  font-family: 'Playfair Display', serif;
  font-size: 1.5rem;
  color: var(--gold);
  flex-shrink: 0;
  width: 42px;
  font-weight: 700;
}
.gem-list li strong {
  display: block;
  color: var(--text-dark);
  margin-bottom: 6px;
  font-size: 1rem;
}

/* ── CTA Banner ──────────────────────────────────── */
.article-cta {
  background: linear-gradient(135deg, var(--dark-mid), #2c1f10);
  border-radius: 12px;
  padding: 40px;
  text-align: center;
  margin: 50px 0;
  position: relative;
  overflow: hidden;
}
.article-cta::before {
  content: '';
  position: absolute;
  top: -40px; right: -40px;
  width: 160px; height: 160px;
  border-radius: 50%;
  background: rgba(184,147,90,0.12);
}
.article-cta h3 {
  font-family: 'Playfair Display', serif;
  font-size: 1.7rem;
  color: #fff;
  margin-bottom: 12px;
}
.article-cta p {
  color: rgba(255,255,255,0.7);
  margin-bottom: 24px;
  font-size: 0.95rem;
}
.article-cta a {
  display: inline-block;
  background: var(--gold);
  color: #fff;
  padding: 14px 32px;
  border-radius: 4px;
  font-size: 0.9rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  text-decoration: none;
  transition: background var(--transition), transform var(--transition);
}
.article-cta a:hover { background: #a17f4b; transform: translateY(-2px); }

/* ── FAQs ────────────────────────────────────────── */
.article-faqs {
  margin: 50px 0;
}
.article-faqs h2 {
  font-family: 'Playfair Display', serif;
  font-size: 1.9rem;
  margin-bottom: 24px;
}
.faq-item {
  border-bottom: 1px solid rgba(0,0,0,0.1);
  padding: 16px 0;
}
.faq-item details {
  cursor: pointer;
}
.faq-item summary {
  font-weight: 600;
  font-size: 1.1rem;
  color: var(--text-dark);
  list-style: none;
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.faq-item summary::-webkit-details-marker {
  display: none;
}
.faq-item summary::after {
  content: '+';
  font-size: 1.5rem;
  font-weight: 400;
  color: var(--gold);
  transition: transform 0.3s;
}
.faq-item details[open] summary::after {
  transform: rotate(45deg);
}
.faq-item p {
  margin-top: 12px;
  margin-bottom: 0;
  font-size: 0.95rem;
  color: var(--text-body);
}

/* ── Tags ────────────────────────────────────────── */
.article-tags {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
  margin: 40px 0;
}
.tag {
  background: rgba(184,147,90,0.18);
  color: #8a6d42; /* Darker gold for better contrast */
  font-size: 0.8rem;
  font-weight: 600;
  padding: 5px 14px;
  border-radius: 20px;
  text-decoration: none;
  border: 1px solid rgba(184,147,90,0.3);
  transition: all var(--transition);
}
.tag:hover { background: #8a6d42; color: #fff; border-color: #8a6d42; }

/* ── Related Posts ───────────────────────────────── */
.related-section {
  background: var(--cream);
  padding: 60px 24px;
  margin-top: 0;
}
.related-inner {
  max-width: 1200px;
  margin: 0 auto;
}
.related-inner h2 {
  font-family: 'Playfair Display', serif;
  font-size: 2rem;
  color: var(--text-dark);
  margin-bottom: 32px;
  text-align: center;
}
.related-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 24px;
}
@media (max-width: 768px) { .related-grid { grid-template-columns: 1fr; } }
.related-card {
  background: var(--white);
  border-radius: 8px;
  overflow: hidden;
  box-shadow: 0 4px 16px rgba(0,0,0,0.05);
  text-decoration: none;
  transition: transform var(--transition), box-shadow var(--transition);
}
.related-card:hover { transform: translateY(-5px); box-shadow: 0 12px 30px rgba(0,0,0,0.09); }
.related-card img { width: 100%; height: 180px; object-fit: cover; display: block; }
.related-card-body { padding: 18px; }
.related-category { font-size: 0.72rem; font-weight: 700; color: var(--gold); text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 6px; }
.related-title {
  font-family: 'Playfair Display', serif;
  font-size: 1rem;
  color: var(--text-dark);
  line-height: 1.35;
  transition: color var(--transition);
}
.related-card:hover .related-title { color: var(--gold); }
.related-min { font-size: 0.78rem; color: var(--text-muted); margin-top: 8px; }

/* ── Services CTA ────────────────────────────────── */
.services-cta {
  background: var(--dark-mid);
  padding: 60px 24px;
}
.services-cta-inner {
  max-width: 1200px;
  margin: 0 auto;
  text-align: center;
}
.services-cta h2 {
  font-family: 'Playfair Display', serif;
  font-size: 2rem;
  color: #fff;
  margin-bottom: 10px;
}
.services-cta p { color: rgba(255,255,255,0.65); margin-bottom: 36px; font-size: 0.95rem; }
.services-cta-grid {
  display: grid;
  grid-template-columns: repeat(5, 1fr);
  gap: 14px;
}
@media (max-width: 768px) { .services-cta-grid { grid-template-columns: 1fr 1fr; } }
.service-cta-card {
  background: rgba(255,255,255,0.05);
  border: 1px solid rgba(255,255,255,0.1);
  border-radius: 8px;
  padding: 22px 14px;
  text-align: center;
  text-decoration: none;
  color: #fff;
  transition: all var(--transition);
}
.service-cta-card:hover {
  background: var(--gold-light);
  border-color: var(--gold);
  color: var(--gold);
  transform: translateY(-4px);
}
.service-cta-icon { font-size: 1.8rem; display: block; margin-bottom: 10px; }
.service-cta-label { font-size: 0.85rem; font-weight: 500; }
</style>
@endpush

@section('content')

{{-- Reading Progress Bar --}}
<div class="reading-progress" id="readingProgress"></div>

{{-- ── HERO ──────────────────────────────────────── --}}
<section class="detail-hero">
  <div class="detail-hero-inner">
    <span class="detail-category-badge">Destination Guide</span>
    <h1 class="detail-hero-title">10 Hidden Gems in the Maldives for Your Next Luxury Staycation</h1>
    <div class="detail-hero-meta">
      <span>
        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
        August 6, 2026
      </span>
      <span>
        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
        6 min read
      </span>
      <span>By TYT Luxe Experts</span>
    </div>
  </div>
</section>

{{-- Breadcrumb --}}
<div class="breadcrumb">
  <div class="breadcrumb-inner">
    <a href="{{ route('home') }}">Home</a>
    <span class="breadcrumb-sep">›</span>
    <a href="{{ route('blog') }}">Travel Journal</a>
    <span class="breadcrumb-sep">›</span>
    <span>Maldives Hidden Gems</span>
  </div>
</div>

{{-- ── ARTICLE ───────────────────────────────────── --}}
<div class="detail-content-wrap">
  <div class="article-body">
    <p>The <strong>Maldives</strong> has long been synonymous with luxury travel — crystal-clear turquoise lagoons, powdery white sand beaches, and iconic overwater bungalows. But beyond the well-trodden paths of popular resorts lie extraordinary hidden gems, waiting to be explored by discerning travellers who seek exclusivity, intimacy, and untouched natural beauty.</p>

    <p>We have curated the ten most breathtaking, lesser-known experiences the Maldives has to offer. These are the secrets that only seasoned luxury travellers know — and now, so will you.</p>

    <img src="https://images.unsplash.com/photo-1514282401047-d79a71a590e8?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80" alt="Maldives aerial view">
    <em>Above: The surreal turquoise lagoons of the Maldives seen from above</em>

    <h2>The 10 Hidden Gems</h2>

    <ul class="gem-list">
      <li>
        <div>
          <strong>Private Sandbank Picnics at Sunset</strong>
          Imagine a stretch of pristine sand entirely to yourself, with nothing but the Indian Ocean as your horizon. Many boutique resorts offer private speedboat transfers to secluded sandbanks for bespoke sunset dinners.
        </div>
      </li>
      <li>
        <div>
          <strong>Underwater Dining Experiences</strong>
          A handful of resorts now offer multi-course gourmet meals beneath the waves. Descend into a glass-walled underwater restaurant and dine as vibrant coral reefs and schools of fish drift past your window.
        </div>
      </li>
      <li>
        <div>
          <strong>Eco-Luxury Sanctuaries</strong>
          The next wave of ultra-luxury resorts in the Maldives is fully solar-powered, with organic gardens supplying fine dining menus and active coral reef restoration programmes that guests can join.
        </div>
      </li>
      <li>
        <div>
          <strong>Seaplane Journeys to Remote Atolls</strong>
          The best way to discover the Maldives is from the air. A private seaplane charter opens up remote southern atolls that commercial routes never reach — atolls with resorts that host fewer than ten guests at a time.
        </div>
      </li>
      <li>
        <div>
          <strong>Bioluminescent Night Swims</strong>
          On moonless nights in certain atolls, the ocean glows with bioluminescent plankton. This once-in-a-lifetime swim feels like diving into a galaxy of stars.
        </div>
      </li>
    </ul>

    <div class="article-blockquote">
      <p>"In the Maldives, every expectation of paradise you have ever held is not just met — it is quietly, profoundly exceeded."</p>
      <cite>— A TYT Luxe Traveller, 2026</cite>
    </div>

    <h2>When to Visit</h2>
    <p>The best time to visit the Maldives is from <strong>November to April</strong>, when the northeast monsoon brings dry, sunny weather with calm seas. The shoulder months of May and October can bring occasional showers but reward visitors with lower rates and dramatically fewer tourists.</p>

    <h3>Pro Tip: Travel Off-Season for Exclusivity</h3>
    <p>The Maldives is exceptional year-round. Travelling in May or October means you will have resort beaches almost entirely to yourself, enjoy more attentive service, and often access private experiences that are fully booked during peak season.</p>

    {{-- In-article CTA --}}
    <div class="article-cta">
      <h3>Experience the Maldives with TYT Luxe</h3>
      <p>Explore our carefully curated Maldives tour packages and premium staycations. Unforgettable memories await.</p>
      <a href="{{ route('packages') }}">Browse Tour Packages</a>
    </div>

    <h2>How to Get There</h2>
    <p>The Maldives is well-connected to major Indian cities via <strong>direct flights to Malé's Velana International Airport</strong>. From Malé, your resort will typically arrange a speedboat or seaplane transfer, often the most spectacular part of the journey.</p>

    <img src="https://images.unsplash.com/photo-1590523277543-a94d2e4eb00b?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80" alt="Overwater Bungalows">
    <em>Above: Iconic overwater villas — a Maldivian signature</em>

    <p>Whichever hidden gem you choose to explore, the Maldives promises a journey that will stay with you long after the turquoise waters have faded from sight. Let TYT Luxe handle every detail, so all you need to do is arrive and be amazed.</p>

    {{-- FAQs --}}
    <div class="article-faqs">
      <h2>Frequently Asked Questions</h2>
      
      <div class="faq-item">
        <details>
          <summary>What is the best time to visit the Maldives?</summary>
          <p>The best weather is between November and April. However, traveling off-season (May to October) offers better exclusivity and fewer crowds, perfect for a peaceful luxury retreat.</p>
        </details>
      </div>

      <div class="faq-item">
        <details>
          <summary>Are these hidden gems suitable for families?</summary>
          <p>Yes, many luxury resorts have dedicated kids' clubs and family villas, though some exclusive resorts are adults-only. TYT Luxe can help you pick the perfect family-friendly option.</p>
        </details>
      </div>

      <div class="faq-item">
        <details>
          <summary>How do seaplane transfers work?</summary>
          <p>Seaplanes operate during daylight hours. Your resort or our travel curators will coordinate your transfer seamlessly upon arrival at Malé International Airport.</p>
        </details>
      </div>
    </div>

    {{-- Tags --}}
    <div class="article-tags">
      <a href="#" class="tag">#Maldives</a>
      <a href="#" class="tag">#LuxuryTravel</a>
      <a href="#" class="tag">#DestinationGuide</a>
      <a href="#" class="tag">#Staycation</a>
      <a href="#" class="tag">#HiddenGems</a>
      <a href="#" class="tag">#OverwaterVilla</a>
    </div>



    <a href="{{ route('blog') }}" style="display:inline-flex;align-items:center;gap:8px;color:#8a6d42;font-weight:600;font-size:0.95rem;text-decoration:none;margin-top:10px;transition:color 0.3s;" onmouseover="this.style.color='#0f0c08'" onmouseout="this.style.color='#8a6d42'">
      ← Back to Travel Journal
    </a>
  </div>
</div>

{{-- ── RELATED POSTS ──────────────────────────────── --}}
<section class="related-section">
  <div class="related-inner">
    <h2>You Might Also Love</h2>
    <div class="related-grid">
      <a href="{{ route('blog.details') }}" class="related-card">
        <img src="https://images.unsplash.com/photo-1502602898657-3e91760cbb34?auto=format&fit=crop&w=600&q=80" alt="Paris">
        <div class="related-card-body">
          <p class="related-category">Culinary Journeys</p>
          <h4 class="related-title">A Taste of Elegance: Dining Through Paris</h4>
          <p class="related-min">5 min read · Jul 28, 2026</p>
        </div>
      </a>
      <a href="{{ route('blog.details') }}" class="related-card">
        <img src="https://images.unsplash.com/photo-1599640842225-85d111c60e6b?auto=format&fit=crop&w=600&q=80" alt="Cruise">
        <div class="related-card-body">
          <p class="related-category">Cruises</p>
          <h4 class="related-title">The Ultimate Luxury Cruise Packing List</h4>
          <p class="related-min">4 min read · Jul 20, 2026</p>
        </div>
      </a>
      <a href="{{ route('blog.details') }}" class="related-card">
        <img src="https://images.unsplash.com/photo-1537996194471-e657df975ab4?auto=format&fit=crop&w=600&q=80" alt="Bali">
        <div class="related-card-body">
          <p class="related-category">Wellness</p>
          <h4 class="related-title">Top 5 Wellness Retreats in Bali</h4>
          <p class="related-min">7 min read · Jul 15, 2026</p>
        </div>
      </a>
    </div>
  </div>
</section>

{{-- ── SERVICES CTA ──────────────────────────────── --}}
<section class="services-cta">
  <div class="services-cta-inner">
    <h2>Explore More from TYT Luxe</h2>
    <p>Discover our full range of handcrafted luxury travel services.</p>
    <div class="services-cta-grid">
      <a href="{{ route('hotels') }}" class="service-cta-card">
        <span class="service-cta-icon">🏨</span>
        <span class="service-cta-label">Luxury Hotels</span>
      </a>
      <a href="{{ route('cruises') }}" class="service-cta-card">
        <span class="service-cta-icon">🚢</span>
        <span class="service-cta-label">Cruises</span>
      </a>
      <a href="{{ route('staycations') }}" class="service-cta-card">
        <span class="service-cta-icon">🌴</span>
        <span class="service-cta-label">Staycations</span>
      </a>
      <a href="{{ route('packages') }}" class="service-cta-card">
        <span class="service-cta-icon">✈️</span>
        <span class="service-cta-label">Tour Packages</span>
      </a>
      <a href="{{ route('offers') }}" class="service-cta-card">
        <span class="service-cta-icon">🎁</span>
        <span class="service-cta-label">Special Offers</span>
      </a>
    </div>
  </div>
</section>

@push('scripts')
<script>
  // Reading progress bar
  window.addEventListener('scroll', function() {
    const winScroll = document.body.scrollTop || document.documentElement.scrollTop;
    const height = document.documentElement.scrollHeight - document.documentElement.clientHeight;
    const scrolled = (winScroll / height) * 100;
    document.getElementById('readingProgress').style.width = scrolled + '%';
  });
</script>
@endpush

@endsection
