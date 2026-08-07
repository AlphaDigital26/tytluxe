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
    url('https://images.unsplash.com/photo-1605806616949-1e87b487cb2a?auto=format&fit=crop&w=1920&q=80');
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
    <span class="detail-category-badge">Cultural Journeys</span>
    <h1 class="detail-hero-title">Secret Spots in the Pink City: A Detailed Guide to Jaipur's Hidden Wonders</h1>
    <div class="detail-hero-meta">
      <span>
        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
        August 15, 2026
      </span>
      <span>
        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
        10 min read
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
    <span>Jaipur Secret Spots</span>
  </div>
</div>

{{-- ── ARTICLE ───────────────────────────────────── --}}
<div class="detail-content-wrap">
  <div class="article-body">
    <p><strong>Jaipur</strong>, affectionately known as the <em>Pink City</em>, is famed globally for its majestic forts, bustling bazaars, and grand palaces. The City Palace, Amer Fort, and Hawa Mahal draw millions of tourists each year. But beneath the surface of this well-trodden tourist trail lies a different Jaipur—a city of hidden stepwells, secret artisan quarters, serene temples nestled in the Aravalli hills, and boutique havelis that whisper tales of bygone eras.</p>

    <p>For the luxury traveler seeking a more profound, intimate connection with Rajasthan’s royal capital, stepping off the beaten path is essential. In this extensive guide, we uncover the secret spots in the Pink City that offer tranquility, exclusivity, and authentic cultural immersion.</p>

    <img src="https://images.unsplash.com/photo-1599370868853-2fb5eab4e4f7?auto=format&fit=crop&w=1200&q=80" alt="Jaipur architectural details" loading="lazy">
    <em>Above: The intricate lattice work (Jali) synonymous with Rajput architecture</em>

    <h2>The Aravalli Secrets: Beyond the City Walls</h2>
    <p>While the city center buzzes with energy, the surrounding Aravalli hills harbor some of Jaipur’s best-kept secrets. These spots provide a sanctuary of peace and a glimpse into the spiritual and architectural grandeur of the region.</p>

    <ul class="gem-list">
      <li>
        <div>
          <strong>Panna Meena Ka Kund</strong>
          Just a stone's throw from the imposing Amer Fort, this beautifully symmetrical 16th-century stepwell remains surprisingly quiet. The interlocking stairs create a mesmerizing geometric pattern. It's the perfect spot for quiet reflection and stunning, crowd-free photography during the golden hour.
        </div>
      </li>
      <li>
        <div>
          <strong>Gatore Ki Chhatriyan</strong>
          This complex of royal cenotaphs is an architectural marvel of marble and sandstone. Nestled in a narrow valley, it served as the cremation ground for the Kachwaha Rajput kings. The intricate carvings of elephants, battle scenes, and deities on the marble domes are breathtaking, and you will often have the entire complex to yourself.
        </div>
      </li>
      <li>
        <div>
          <strong>The Monkey Temple (Galtaji) at Dawn</strong>
          While Galtaji is known, visiting it at the break of dawn transforms the experience. Built into a mountain pass, this ancient Hindu pilgrimage site features natural springs and pavilions. In the early morning light, watching the resident macaques and the sun rising over the pink cliffs is a deeply spiritual experience.
        </div>
      </li>
    </ul>

    <div class="article-blockquote">
      <p>"To truly understand Jaipur, one must look beyond its grand facades and seek out the quiet courtyards where the soul of Rajasthan still lingers."</p>
      <cite>— A TYT Luxe Heritage Curator</cite>
    </div>

    <h2>Royal Architecture Beyond the Postcards</h2>
    <p>While the Hawa Mahal is iconic, there are lesser-known palaces that offer a more intimate look at royal life without the overwhelming crowds. These architectural marvels are steeped in history and romance.</p>

    <img src="https://images.unsplash.com/photo-1591081622359-0097a82cbfdc?auto=format&fit=crop&w=1200&q=80" alt="Jaipur hidden palace courtyard" loading="lazy">
    <em>Above: A serene, forgotten courtyard bathed in the warm Rajasthani sun</em>

    <ul class="gem-list">
      <li>
        <div>
          <strong>Samode Palace</strong>
          Located about an hour outside Jaipur, this 475-year-old palace is a masterclass in Indo-Saracenic architecture. While not entirely unknown, its distance from the city center keeps casual tourists away. The Durbar Hall, with its intricately hand-painted walls and mirrored ceilings, is arguably more magnificent than the City Palace itself.
        </div>
      </li>
      <li>
        <div>
          <strong>Sisodia Rani Ka Bagh</strong>
          A stunning royal garden built by Maharaja Sawai Jai Singh II in 1728 for his second wife. This multi-tiered garden is adorned with beautiful murals depicting the love story of Radha and Krishna, cascading water channels, and painted pavilions. It is an oasis of tranquility just on the outskirts of the bustling city.
        </div>
      </li>
    </ul>

    <h2>Secret Markets for the Discerning Buyer</h2>
    <p>Jaipur is a shopper's paradise, but navigating Johari Bazaar or Bapu Bazaar can be overwhelming. The true luxury of shopping in Jaipur lies in visiting the private ateliers of the city's master craftsmen.</p>

    <img src="https://images.unsplash.com/photo-1534759846116-5799c33ce22a?auto=format&fit=crop&w=1200&q=80" alt="Vibrant textiles of Jaipur" loading="lazy">
    <em>Above: The vibrant colors of authentic Rajasthani textiles</em>

    <ul class="gem-list">
      <li>
        <div>
          <strong>The Gemstone Ateliers of Gopal Ji Ka Rasta</strong>
          While Johari Bazaar is famous for jewelry, the real magic happens in the narrow alleys branching off it. Gopal Ji Ka Rasta is lined with generations-old workshops where rough emeralds and sapphires are cut and polished. With a private guide, you can visit these ateliers, observe the meticulous process, and commission bespoke pieces at a fraction of the cost found in high-end boutiques.
        </div>
      </li>
      <li>
        <div>
          <strong>Blue Pottery in Sanganer</strong>
          The distinctive Turko-Persian Blue Pottery of Jaipur is famous worldwide. Instead of buying from city emporiums, travel to the workshops in Sanganer. Here, you can meet the master potters, learn about the unique dough (which remarkably uses no clay), and purchase directly from the creators.
        </div>
      </li>
    </ul>

    <h2>Hidden Culinary and Artisan Delights</h2>
    <p>Jaipur is a UNESCO Creative City of Crafts and Folk Arts, but the best artisans don't always have shopfronts on the main roads. Similarly, authentic culinary experiences are often hidden in centuries-old mansions.</p>

    <ul class="gem-list">
      <li>
        <div>
          <strong>A Private Dinner at a Heritage Haveli</strong>
          Skip the crowded fine-dining restaurants and arrange a private, candlelit dinner in the courtyard of a restored 18th-century Haveli. Enjoy a traditional Rajasthani thali featuring Laal Maas and Dal Baati Churma, prepared by descendants of royal chefs.
        </div>
      </li>
      <li>
        <div>
          <strong>The Block Printers of Bagru</strong>
          Venture slightly outside Jaipur to the village of Bagru, where the centuries-old tradition of block printing is kept alive. TYT Luxe can arrange a private workshop where you can learn the natural dyeing processes and create your own bespoke textile piece alongside master craftsmen.
        </div>
      </li>
    </ul>

    <h2>When to Visit</h2>
    <p>The ideal time to explore Jaipur is from <strong>October to March</strong>, when the weather is cool and pleasant. The winter months are perfect for exploring outdoor monuments and enjoying evenings by the fire.</p>

    <h3>Pro Tip: The Magic of the Monsoon</h3>
    <p>For a truly unique perspective, visit Jaipur during the monsoon season (July to September). The Aravalli hills turn a vibrant, lush green, the tourist crowds thin out, and watching a rainstorm from the balcony of a heritage palace hotel is an unforgettable, romantic experience.</p>

    {{-- In-article CTA --}}
    <div class="article-cta">
      <h3>Discover Jaipur with TYT Luxe</h3>
      <p>Explore our exclusive heritage tour packages and luxury haveli staycations in the Pink City.</p>
      <a href="{{ route('packages') }}">Browse Tour Packages</a>
    </div>

    <h2>Getting There and Around</h2>
    <p>Jaipur International Airport (JAI) is well-connected to major global and domestic hubs. Once you arrive, navigating the city's hidden alleys is best done with a knowledgeable local guide. TYT Luxe provides private, chauffeur-driven luxury vehicles and expert heritage guides to ensure your exploration is both comfortable and deeply informative.</p>

    <img src="https://images.unsplash.com/photo-1582293041079-7814c2f12063?auto=format&fit=crop&w=1200&q=80" alt="Jaipur Palace Door" loading="lazy">
    <em>Above: The exquisite detailing of the Peacock Gate at the City Palace</em>

    <p>Jaipur rewards the curious traveler. By venturing beyond the majestic forts and diving into the city's hidden stepwells, artisan workshops, and quiet havelis, you uncover the true, timeless magic of the Pink City.</p>

    {{-- FAQs --}}
    <div class="article-faqs">
      <h2>Frequently Asked Questions</h2>
      
      <div class="faq-item">
        <details>
          <summary>Is it safe to visit the hidden spots in Jaipur?</summary>
          <p>Yes, Jaipur is generally very safe for tourists. However, when exploring off-the-beaten-path locations like Gatore Ki Chhatriyan or remote stepwells, it is highly recommended to go with a certified local guide for both safety and historical context.</p>
        </details>
      </div>

      <div class="faq-item">
        <details>
          <summary>Can I take photographs at these secret spots?</summary>
          <p>Most hidden gems in Jaipur are a photographer's dream and allow photography. Some active temples or private artisan workshops may require permission or have minor fees for professional camera equipment.</p>
        </details>
      </div>

      <div class="faq-item">
        <details>
          <summary>How many days are needed to explore Jaipur fully?</summary>
          <p>To see the main attractions and take the time to explore these hidden gems without rushing, a minimum of 3 to 4 days is recommended.</p>
        </details>
      </div>
    </div>

    {{-- Tags --}}
    <div class="article-tags">
      <a href="#" class="tag">#Jaipur</a>
      <a href="#" class="tag">#PinkCity</a>
      <a href="#" class="tag">#Heritage</a>
      <a href="#" class="tag">#HiddenGems</a>
      <a href="#" class="tag">#CulturalJourney</a>
      <a href="#" class="tag">#LuxuryTravel</a>
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
