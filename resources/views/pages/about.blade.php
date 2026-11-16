@extends('layouts.frontend')

@section('meta_title', 'About Take Your Trip (TYT Luxe) — Our Story, Team & Values')
@section('meta_description', 'Learn about TYT Luxe — founded in 2020, we handpick luxury hotels and curated cruise experiences for Indian travellers. Meet our founders Kartik Singhal, Neha Singhal and the team behind every journey.')

@push('scripts')
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@@type": "AboutPage",
  "name": "About TYT Luxe",
  "url": "https://tytluxe.in/about",
  "description": "TYT Luxe was founded in 2020 with a passion for curated luxury travel. We handpick hotels and cruises and serve Indian travellers with personalised itineraries.",
  "publisher": {
    "@@type": "TravelAgency",
    "name": "TYT Luxe",
    "url": "https://tytluxe.in",
    "telephone": "+91-98750-73788"
  }
}
</script>
@endpush

@push('styles')
<style>
*, *::before, *::after { box-sizing: border-box; }

:root {
  --cream: #f5f0e8;
  --dark: #0f0c08;
  --dark-mid: #1a1510;
  --dark-card: #161210;
  --gold: #b8935a;
  --gold-light: #d4af78;
  --white: #ffffff;
  --text-dark: #1a1108;
  --text-muted: #777;
  --border-gold: rgba(184,147,90,0.2);
}

body {
  font-family: 'Poppins', sans-serif;
  background: var(--cream);
  color: var(--text-dark);
  overflow-x: hidden;
}



/* ── STATS BAND ── */
.ta-stats-band {
  background: var(--dark-mid);
  padding: 22px 24px;
}
.ta-stats-inner {
  display: flex; justify-content: center;
  flex-wrap: wrap; gap: 0;
  max-width: 900px; margin: 0 auto;
}
.ta-stat {
  flex: 1; min-width: 140px; text-align: center;
  padding: 16px 16px;
  border-right: 1px solid rgba(184,147,90,.15);
}
.ta-stat:last-child { border-right: none; }
.ta-stat-num {
  font-family: 'Playfair Display', serif;
  font-size: 2.2rem; font-weight: 700;
  color: var(--gold); display: block;
}
.ta-stat-lbl {
  font-size: .7rem; letter-spacing: .14em;
  text-transform: uppercase;
  color: rgba(245,240,232,.55);
  margin-top: 4px; display: block;
}

/* ── UTIL ── */
.ta-label {
  font-size: .72rem; font-weight: 600;
  letter-spacing: .3em; text-transform: uppercase;
  color: var(--gold); margin-bottom: 12px;
}
.ta-h2 {
  font-family: 'Playfair Display', serif;
  font-size: clamp(2rem, 4vw, 3rem);
  font-weight: 700; line-height: 1.15;
  color: var(--text-dark); margin-bottom: 16px;
}
.ta-h2-light { color: var(--cream); }
.ta-divider { width: 52px; height: 2px; background: var(--gold); margin-bottom: 52px; }
.ta-divider-center { margin: 0 auto 52px; }

.ta-section {
  padding: 88px 60px;
}
.ta-section-dark {
  background: var(--dark);
  padding: 88px 60px;
}
.ta-section-cream {
  background: var(--cream);
  padding: 88px 60px;
}

/* ── STORY SECTION ── */
.ta-story-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 80px;
  align-items: center;
  max-width: 1200px;
  margin: 0 auto;
}
.ta-story-img {
  position: relative;
}
.ta-story-img img {
  width: 100%; height: 520px;
  object-fit: cover;
}
.ta-story-img-badge {
  position: absolute;
  bottom: -24px; right: -24px;
  background: var(--gold);
  padding: 28px 32px;
  text-align: center;
}
.ta-story-img-badge span {
  font-family: 'Playfair Display', serif;
  font-size: 2.4rem; font-weight: 700;
  color: #fff; display: block; line-height: 1;
}
.ta-story-img-badge small {
  font-size: .68rem; letter-spacing: .2em;
  text-transform: uppercase; color: rgba(255,255,255,.8);
  margin-top: 4px; display: block;
}
.ta-story-text p {
  font-size: .95rem; color: var(--text-muted);
  line-height: 1.9; margin-bottom: 20px;
}
.ta-story-text p strong {
  color: var(--text-dark); font-weight: 600;
}

/* ── VALUES ── */
.ta-values-grid {
  display: grid;
  grid-template-columns: repeat(4, minmax(0, 1fr));
  gap: 24px;
  max-width: 1200px; margin: 0 auto;
}
.ta-value-card {
  position: relative;
  background: var(--dark-card);
  border: 1px solid rgba(184,147,90,0.25);
  padding: 36px 30px 34px;
  transition: transform .22s, box-shadow .22s, border-color .22s;
}
/* A gold line along the top edge on hover. */
.ta-value-card::before {
  content: ''; position: absolute; left: -1px; right: -1px; top: -1px; height: 2px;
  background: var(--gold);
  transform: scaleX(0); transform-origin: left; transition: transform .3s ease;
}
.ta-value-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 16px 48px rgba(0,0,0,.3);
  border-color: rgba(184,147,90,0.5);
}
.ta-value-card:hover::before { transform: scaleX(1); }
.ta-value-num {
  position: absolute; top: 18px; right: 24px;
  font-family: 'Playfair Display', serif; font-size: 2.75rem; line-height: 1;
  color: rgba(184,147,90,0.18);
}
.ta-value-icon {
  width: 54px; height: 54px; border-radius: 50%;
  border: 1px solid rgba(184,147,90,0.6);
  display: flex; align-items: center; justify-content: center;
  margin-bottom: 24px; color: var(--gold-light);
  transition: box-shadow .22s, border-color .22s;
}
.ta-value-card:hover .ta-value-icon { border-color: var(--gold); box-shadow: 0 0 18px rgba(184,147,90,.35); }
.ta-value-icon svg { width: 26px; height: 26px; }
.ta-value-card h3 {
  font-family: 'Playfair Display', serif;
  font-size: 1.2rem; line-height: 1.35; color: var(--cream);
  margin-bottom: 12px;
  /* Two lines tall in every card, so the text below starts level. */
  min-height: 2.7em;
}
.ta-value-card p {
  font-size: .85rem; color: rgba(245,240,232,.66);
  line-height: 1.8;
}

/* ── TEAM ── */
/* Equal cards: a short summary each, the full bio in a popup
   ("Read full profile"), so a long bio never stretches the row. */
.ta-team-grid {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 28px;
  max-width: 1200px; margin: 0 auto;
}
.ta-team-card {
  background: #fff;
  border: 1px solid var(--border-gold);
  overflow: hidden;
  display: flex; flex-direction: column;
  transition: transform .22s, box-shadow .22s;
}
.ta-team-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 12px 40px rgba(0,0,0,.09);
}
/* The photos are 3:4 portraits with the face about 40% down: a box that
   keeps its shape at any card width, cropped around the face. */
.ta-team-card img {
  display: block;
  width: 100%; height: auto; aspect-ratio: 5 / 4; object-fit: cover; object-position: 50% 35%;
}
.ta-team-photo-placeholder {
  width: 100%; aspect-ratio: 5 / 4;
  background: #f3ece0;
  border-bottom: 1px solid var(--border-gold);
  display: flex; align-items: center; justify-content: center;
}
.ta-team-monogram {
  width: 120px; height: 120px; border-radius: 50%;
  border: 1.5px solid var(--gold);
  display: flex; align-items: center; justify-content: center;
  font-family: 'Playfair Display', serif; font-size: 3rem; color: var(--gold);
}
.ta-team-card-body { padding: 24px 26px 26px; display: flex; flex-direction: column; flex-grow: 1; }
.ta-team-role {
  font-size: .65rem; font-weight: 600;
  letter-spacing: .2em; text-transform: uppercase;
  color: var(--gold); margin-bottom: 6px;
}
.ta-team-card h3 {
  font-family: 'Playfair Display', serif;
  font-size: 1.25rem; color: var(--text-dark);
  margin-bottom: 10px;
}
.ta-team-card p {
  font-size: .85rem; color: var(--text-muted);
  line-height: 1.7;
}
.ta-team-summary {
  display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;
  margin-bottom: 18px;
}
.ta-team-more {
  margin-top: auto; align-self: flex-start;
  background: none; border: none; border-bottom: 1px solid var(--gold);
  padding: 10px 0; cursor: pointer;
  font-family: inherit; font-size: .72rem; font-weight: 600;
  letter-spacing: .12em; text-transform: uppercase; color: var(--text-dark);
  transition: color .2s;
}
.ta-team-more:hover, .ta-team-more:focus-visible { color: var(--gold); }
.ta-team-full { display: none; }

/* The full-profile popup */
.ta-profile {
  /* The site's reset zeroes every margin; a popup centres on margin: auto. */
  position: fixed; inset: 0; margin: auto;
  width: min(880px, calc(100vw - 32px)); height: fit-content; max-height: calc(100vh - 48px);
  padding: 0; border: none; background: #fff; color: var(--text-dark);
  box-shadow: 0 24px 80px rgba(0,0,0,.35);
}
.ta-profile::backdrop { background: rgba(26,17,8,.6); }
.ta-profile-inner { display: flex; max-height: calc(100vh - 48px); }
.ta-profile-photo { width: 300px; flex-shrink: 0; }
.ta-profile-photo img { display: block; width: 100%; height: 100%; aspect-ratio: auto; object-fit: cover; object-position: 50% 35%; }
.ta-profile-photo .ta-team-photo-placeholder { height: 100%; aspect-ratio: auto; border-bottom: none; }
.ta-profile-body { position: relative; padding: 40px 44px; overflow-y: auto; flex-grow: 1; }
.ta-profile-body h3 {
  font-family: 'Playfair Display', serif; font-size: 1.8rem; margin: 0 0 8px;
}
.ta-profile-rule { width: 40px; height: 2px; background: var(--gold); margin-bottom: 20px; }
.ta-profile-text p { font-size: .9rem; color: var(--text-muted); line-height: 1.8; margin: 0 0 12px; }
.ta-profile-close {
  position: absolute; top: 10px; right: 10px;
  width: 44px; height: 44px; border: none; background: none; cursor: pointer;
  font-size: 1.6rem; line-height: 1; color: var(--text-dark);
}
.ta-profile-close:hover, .ta-profile-close:focus-visible { color: var(--gold); }

/* ── WHY US ── */
.ta-why-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 3px;
  max-width: 100%;
}
.ta-why-card {
  background: #fff;
  border: 1px solid rgba(184,147,90,.1);
  padding: 44px 36px;
  transition: background .22s;
}
.ta-why-card:hover { background: var(--gold); }
.ta-why-card:hover h3,
.ta-why-card:hover p { color: #fff; }
.ta-why-num {
  font-family: 'Playfair Display', serif;
  font-size: 3.5rem; font-weight: 700;
  color: rgba(184,147,90,.15); display: block;
  line-height: 1; margin-bottom: 16px;
  transition: color .22s;
}
.ta-why-card:hover .ta-why-num { color: rgba(255,255,255,.25); }
.ta-why-card h3 {
  font-family: 'Playfair Display', serif;
  font-size: 1.2rem; color: var(--text-dark);
  margin-bottom: 10px; transition: color .22s;
}
.ta-why-card p {
  font-size: .84rem; color: var(--text-muted);
  line-height: 1.8; transition: color .22s;
}

/* ── TESTIMONIALS ── */
.ta-testimonials {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 24px;
  max-width: 1200px; margin: 0 auto;
}
.ta-testi-card {
  background: var(--dark-mid);
  border: 1px solid var(--border-gold);
  padding: 36px 32px;
  position: relative;
}
.ta-testi-quote {
  font-family: 'Playfair Display', serif;
  font-size: 4rem; color: var(--gold);
  opacity: .3; line-height: .8;
  margin-bottom: 16px; display: block;
}
.ta-testi-card p {
  font-size: .9rem; color: rgba(245,240,232,.7);
  line-height: 1.8; margin-bottom: 24px;
  font-style: italic;
}
.ta-testi-author {
  display: flex; align-items: center; gap: 12px;
}
.ta-testi-author img {
  width: 44px; height: 44px; border-radius: 50%;
  object-fit: cover; border: 2px solid var(--gold);
}
.ta-testi-name {
  font-size: .84rem; font-weight: 600;
  color: var(--cream); display: block;
}
.ta-testi-loc {
  font-size: .72rem; color: var(--gold);
  letter-spacing: .1em; text-transform: uppercase;
}

/* ── CTA STRIP ── */
.ta-cta {
  background: var(--gold);
  padding: 72px 60px;
  text-align: center;
}
.ta-cta h2 {
  font-family: 'Playfair Display', serif;
  font-size: clamp(1.8rem, 3.5vw, 2.8rem);
  font-weight: 700; color: #fff;
  margin-bottom: 14px;
}
.ta-cta p {
  font-size: .95rem; color: rgba(255,255,255,.8);
  margin-bottom: 36px; line-height: 1.7;
}
.ta-cta-btns { display: flex; gap: 16px; justify-content: center; flex-wrap: wrap; }
.ta-cta-btn {
  display: inline-block;
  font-size: .82rem; font-weight: 600;
  letter-spacing: .18em; text-transform: uppercase;
  padding: 16px 44px; text-decoration: none;
  transition: all .25s;
}
.ta-cta-btn-dark {
  background: var(--dark); color: var(--cream);
}
.ta-cta-btn-dark:hover { background: #000; transform: translateY(-2px); }
.ta-cta-btn-outline {
  background: transparent; color: #fff;
  border: 2px solid rgba(255,255,255,.6);
}
.ta-cta-btn-outline:hover { border-color: #fff; transform: translateY(-2px); }

/* ── TRUST STRIP ── */
.ta-trust {
  background: var(--dark-mid);
  padding: 52px 24px;
}
/* Four equal columns with thin gold lines between them. */
.ta-trust-inner {
  max-width: 1100px; margin: 0 auto;
  display: grid; grid-template-columns: repeat(4, minmax(0, 1fr));
  text-align: center;
}
.ta-trust-item { padding: 0 28px; }
.ta-trust-item + .ta-trust-item { border-left: 1px solid rgba(184,147,90,.25); }
.ta-trust-icon {
  width: 52px; height: 52px; margin: 0 auto 16px; border-radius: 50%;
  border: 1px solid rgba(184,147,90,.6);
  display: flex; align-items: center; justify-content: center;
  color: var(--gold-light);
}
.ta-trust-icon svg { width: 24px; height: 24px; }
.ta-trust-title {
  display: block;
  font-size: .8rem; font-weight: 600;
  letter-spacing: .14em; text-transform: uppercase;
  color: var(--gold-light); margin-bottom: 8px;
  text-decoration: none;
}
a.ta-trust-title:hover { color: var(--cream); }
.ta-trust-desc {
  font-size: .85rem; color: rgba(245,240,232,.68);
  line-height: 1.6;
}

/* ── RESPONSIVE ── */
@media (max-width: 900px) {
  .ta-story-grid { grid-template-columns: 1fr; gap: 48px; }
  .ta-story-img-badge { bottom: -16px; right: -8px; }
  .ta-why-grid { grid-template-columns: 1fr 1fr; }
  .ta-section, .ta-section-dark, .ta-section-cream { padding: 60px 24px; }
  .ta-cta { padding: 60px 24px; }
  .ta-team-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 20px; }
  .ta-values-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 20px; }
  /* Trust strip: two per row, no column lines. */
  .ta-trust-inner { grid-template-columns: repeat(2, minmax(0, 1fr)); row-gap: 36px; }
  .ta-trust-item + .ta-trust-item { border-left: none; }
  .ta-trust-item { padding: 0 12px; }
}
@media (max-width: 680px) {
  /* Popup: photo on top, bio below. */
  .ta-profile-inner { flex-direction: column; overflow-y: auto; }
  .ta-profile-photo { width: 100%; height: 260px; }
  .ta-profile-body { padding: 28px 22px; overflow: visible; }
}
@media (max-width: 580px) {
  .ta-team-grid { grid-template-columns: 1fr; }
  .ta-values-grid { grid-template-columns: 1fr; }
  .ta-value-card h3 { min-height: 0; }
  .ta-why-grid { grid-template-columns: 1fr; }
  /* Stats: 2 × 2 on phones, so the band stays thin. */
  .ta-stats-inner { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); }
  .ta-stat { min-width: 0; padding: 14px 8px; border-right: none; }
  .ta-stat:nth-child(odd) { border-right: 1px solid rgba(184,147,90,.15); }
  .ta-stat:nth-child(-n+2) { border-bottom: 1px solid rgba(184,147,90,.15); }
}
</style>
@endpush

@section('content')

<!-- ══════════════════════════════════
     HERO
══════════════════════════════════ -->
<x-hero-carousel 
  :slides="[
    '/assets/images/stock/photo-1476514525535-07fb3b4ae5f1-1600.webp',
    '/assets/images/stock/photo-1507525428034-b723cf961d3e-1600.webp',
    '/assets/images/stock/photo-1500375592092-40eb2168fd21-1600.webp'
  ]"
  eyebrow="About Take Your Trip · Our Story"
  title="We Craft Journeys,<br>Not Just <em>Itineraries</em>"
  subtitle="Born from a passion for travel, built on trust.<br>Take Your Trip is your personal gateway to the world's finest hotels and cruises."
  ctaText="Get In Touch"
  ctaLink="{{ url('/contact') }}"
/>

<!-- ══════════════════════════════════
     STATS BAND
══════════════════════════════════ -->
<div class="ta-stats-band">
  <div class="ta-stats-inner">
    <div class="ta-stat"><span class="ta-stat-num">500+</span><span class="ta-stat-lbl">Happy Travellers</span></div>
    <div class="ta-stat"><span class="ta-stat-num">50+</span><span class="ta-stat-lbl">Destinations</span></div>
    <div class="ta-stat"><span class="ta-stat-num">100+</span><span class="ta-stat-lbl">Curated Hotels</span></div>
    <div class="ta-stat"><span class="ta-stat-num">2 hrs</span><span class="ta-stat-lbl">Response Time</span></div>
  </div>
</div>

<!-- ══════════════════════════════════
     OUR STORY
══════════════════════════════════ -->
<section class="ta-section-cream">
  <div class="ta-story-grid">
    <div class="ta-story-img">
      <img src="/assets/images/stock/photo-1522199755839-a2bacb67c546-800.webp"
           alt="Travel planner working on a laptop with maps and destinations"
           width="800" height="520"
           loading="lazy">
      <div class="ta-story-img-badge">
        <span>2020</span>
        <small>Founded With Passion</small>
      </div>
    </div>
    <div class="ta-story-text">
      <p class="ta-label">Our Story</p>
      <h2 class="ta-h2">Travel Better,<br>Travel Smarter</h2>
      <div class="ta-divider"></div>
      <p><strong>Take Your Trip</strong> was born from a simple belief — that every traveller deserves a seamless, curated experience that goes beyond just booking a room or a ticket.</p>
      <p>We handpick every hotel and cruise we recommend. From intimate boutique stays to grand luxury resorts, from scenic coastal cruises to international voyages — each option on our platform has been personally vetted for quality, comfort, and value.</p>
      <p>Based in India, we understand the Indian traveller's needs — be it pure vegetarian dining, family-friendly itineraries, or honeymoon escapes. We bring you the best of both worlds: <strong>global experiences with a personal touch.</strong></p>
      <p>Whether you call us, WhatsApp us, or fill a form — a real travel expert gets back to you within 2 hours. Always.</p>
    </div>
  </div>
</section>

<!-- ══════════════════════════════════
     TEAM
══════════════════════════════════ -->
<section class="ta-section-cream">
  <div style="max-width:1200px; margin:0 auto 36px;">
    <p class="ta-label">Our People</p>
    <h2 class="ta-h2">Founders &amp; Associates</h2>
    <div class="ta-divider"></div>
    <p style="max-width:760px;color:var(--text-muted);line-height:1.8;">Meet the leadership and partner team shaping TYT Luxe with premium service, dependable operations and thoughtful travel planning.</p>
  </div>
  @php
    // One card each: photo (or a gold initial until there is one), role,
    // name, a short summary, and the full bio for the "Read full profile"
    // popup. Bios are the full text — nothing is cut, only folded away.
    $team = [
        // 'focus': where the crop centres, when the face isn't at the usual height.
        ['name' => 'Kartik Singhal', 'role' => 'Founder', 'photo' => 'assets/images/Founder.jpeg', 'focus' => '50% 18%', 'alt' => 'Kartik Singhal — Founder, TYT Luxe', 'bio' => [
            'Kartik Singhal is the visionary founder behind TYT Luxe, a premium travel brand created with the passion to redefine luxury travel experiences. With a strong entrepreneurial mindset and deep understanding of the travel industry, he envisioned a company that delivers not just holidays, but curated journeys designed with elegance, comfort and exclusivity.',
            'Driven by attention to detail and a commitment to personalized service, Kartik focuses on handpicked luxury stays, seamless travel planning and bespoke itineraries tailored to each client\'s preferences.',
        ], 'quote' => '"Crafting Luxury Journeys. Creating Timeless Memories."'],
        ['name' => 'Neha Singhal', 'role' => 'CEO', 'photo' => 'assets/images/CEO.jpeg', 'alt' => 'Neha Singhal — CEO, TYT Luxe', 'bio' => [
            'Neha Singhal, CEO of TYT Luxe, brings a refined sense of luxury, personalization, and client-centric thinking to the brand. With a keen eye for detail and a passion for curating meaningful travel experiences, she plays a pivotal role in designing journeys that go beyond conventional itineraries and transform into lifelong memories.',
            'At TYT Luxe, Neha focuses on understanding each traveler\'s unique preferences and translating them into thoughtfully crafted, seamless experiences. From handpicked luxury stays and bespoke itineraries to exclusive on-ground arrangements, her approach blends elegance with comfort, ensuring every journey reflects sophistication and individuality.',
            'Her dedication to service excellence and commitment to building long-term client relationships has been instrumental in shaping TYT Luxe into a trusted name for premium and bespoke travel. Neha believes that true luxury lies in personalization, attention to detail, and creating experiences that feel effortless yet extraordinary.',
        ]],
        ['name' => 'Swati Lohariwal', 'role' => 'Co-Founder', 'photo' => 'assets/images/Co-founder.webp', 'alt' => 'Swati Lohariwal — Co-Founder, TYT Luxe', 'bio' => [
            'Swati Lohariwal serves as Co-Founder at TYT Luxe with a sharp focus on precision, personalization and service excellence. She helps oversee end-to-end operational management so every journey reflects the brand\'s premium standards.',
            'Swati brings a strong eye for detail, client servicing expertise and a passion for curated travel experiences. Her approach emphasizes understanding individual preferences and translating them into thoughtfully designed itineraries that deliver comfort, exclusivity and memorable moments.',
        ]],
        ['name' => 'Ashish Lohariwal', 'role' => 'Operational Head', 'photo' => 'assets/images/operational-head.webp', 'alt' => 'Ashish Lohariwal — Operational Head, TYT Luxe', 'bio' => [
            'Ashish complements operations with strategic coordination, vendor partnerships and on-ground execution. His strength lies in streamlining logistics, maintaining quality benchmarks and ensuring smooth travel experiences across destinations.',
            'His operational leadership ensures that every booking is handled with efficiency and professionalism.',
        ]],
        ['name' => 'Shailesh', 'role' => 'Financial & Compliance Partner', 'photo' => null, 'bio' => [
            'Shailesh leads the financial framework of TYT Luxe with a sharp focus on accuracy, compliance and structured growth. As Financial & Compliance Partner, he oversees budgeting, financial planning, vendor reconciliations and regulatory adherence across all operations.',
            'His meticulous approach ensures transparent accounting practices, efficient fund management and seamless financial coordination with global partners.',
        ]],
        ['name' => 'Mayur', 'role' => 'Accounts & Administration Partner', 'photo' => null, 'bio' => [
            'Mayur brings operational precision and administrative efficiency to TYT Luxe as Accounts & Administration Partner. He manages day-to-day accounting workflows, financial documentation, invoicing systems and backend administrative processes that keep the organization running smoothly.',
            'His attention to detail and structured execution ensure timely reporting, streamlined coordination and organized financial operations.',
        ]],
    ];
  @endphp
  <div class="ta-team-grid">
    @foreach($team as $person)
      <article class="ta-team-card">
        <div class="ta-team-card-photo">
          @if($person['photo'])
            <img src="{{ asset($person['photo']) }}" alt="{{ $person['alt'] }}" width="400" height="320" loading="lazy" @if(! empty($person['focus'])) style="object-position: {{ $person['focus'] }}" @endif>
          @else
            <div class="ta-team-photo-placeholder" role="img" aria-label="{{ $person['name'] }}">
              <span class="ta-team-monogram" aria-hidden="true">{{ mb_substr($person['name'], 0, 1) }}</span>
            </div>
          @endif
        </div>
        <div class="ta-team-card-body">
          <div class="ta-team-role">{{ $person['role'] }}</div>
          <h3>{{ $person['name'] }}</h3>
          <p class="ta-team-summary">{{ $person['bio'][0] }}</p>
          <button type="button" class="ta-team-more" aria-haspopup="dialog">Read full profile <span aria-hidden="true">&rarr;</span></button>
          <div class="ta-team-full">
            @foreach($person['bio'] as $paragraph)
              <p>{{ $paragraph }}</p>
            @endforeach
            @if(! empty($person['quote']))
              <p><strong>{{ $person['quote'] }}</strong></p>
            @endif
          </div>
        </div>
      </article>
    @endforeach
  </div>

  {{-- Full profile popup, filled from the card that opened it. --}}
  <dialog class="ta-profile" id="taProfile" aria-labelledby="taProfileName">
    <div class="ta-profile-inner">
      <div class="ta-profile-photo" id="taProfilePhoto"></div>
      <div class="ta-profile-body">
        <button type="button" class="ta-profile-close" id="taProfileClose" aria-label="Close profile">&times;</button>
        <div class="ta-team-role" id="taProfileRole"></div>
        <h3 id="taProfileName"></h3>
        <div class="ta-profile-rule"></div>
        <div class="ta-profile-text" id="taProfileText"></div>
      </div>
    </div>
  </dialog>
</section>

<!-- ══════════════════════════════════
     OUR VALUES
══════════════════════════════════ -->
<section class="ta-section-dark">
  <div style="max-width:1200px; margin:0 auto;">
    <p class="ta-label" style="color:var(--gold);">What We Stand For</p>
    <h2 class="ta-h2 ta-h2-light">Our Core Values</h2>
    <div class="ta-divider"></div>
  </div>
  <div class="ta-values-grid">
    <div class="ta-value-card">
      <span class="ta-value-num" aria-hidden="true">01</span>
      <span class="ta-value-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><polygon points="15.5 8.5 13.5 13.5 8.5 15.5 10.5 10.5"/></svg></span>
      <h3>Curation Over Quantity</h3>
      <p>We don't list everything. We list the best. Every hotel and cruise is handpicked, reviewed, and selected for a specific type of traveller.</p>
    </div>
    <div class="ta-value-card">
      <span class="ta-value-num" aria-hidden="true">02</span>
      <span class="ta-value-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="m11 17 2 2a1 1 0 1 0 3-3"/><path d="m14 14 2.5 2.5a1 1 0 1 0 3-3l-3.88-3.88a3 3 0 0 0-4.24 0l-.88.88a1 1 0 1 1-3-3l2.81-2.81a5.79 5.79 0 0 1 7.06-.87l.47.28a2 2 0 0 0 1.42.25L21 4"/><path d="m21 3 1 11h-2"/><path d="M3 3 2 14l6.5 6.5a1 1 0 1 0 3-3"/><path d="M3 4h8"/></svg></span>
      <h3>Personal Relationships</h3>
      <p>We're not a faceless booking engine. We're your travel partner — available on call, on WhatsApp, and always ready to go the extra mile.</p>
    </div>
    <div class="ta-value-card">
      <span class="ta-value-num" aria-hidden="true">03</span>
      <span class="ta-value-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M6 3h12l4 6-10 13L2 9Z"/><path d="M11 3 8 9l4 13 4-13-3-6"/><path d="M2 9h20"/></svg></span>
      <h3>Value at Every Level</h3>
      <p>Luxury doesn't always mean expensive. We find you the best experience for your budget — whether that's ₹5,000 or ₹5,00,000 per night.</p>
    </div>
    <div class="ta-value-card">
      <span class="ta-value-num" aria-hidden="true">04</span>
      <span class="ta-value-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"/><path d="M2 12h20"/></svg></span>
      <h3>Global Reach, Local Heart</h3>
      <p>From Goa to Greece, Maldives to Mediterranean — we cover the world but never forget the nuances of the Indian traveller.</p>
    </div>
  </div>
</section>

<!-- ══════════════════════════════════
     WHY CHOOSE US
══════════════════════════════════ -->
<section class="ta-section-cream" style="padding-left:0; padding-right:0;">
  <div style="padding: 0 60px 52px; max-width:1200px; margin:0 auto;">
    <p class="ta-label">Why Take Your Trip</p>
    <h2 class="ta-h2">6 Reasons Travellers<br>Choose Us</h2>
    <div class="ta-divider"></div>
  </div>
  <div class="ta-why-grid">
    <div class="ta-why-card">
      <span class="ta-why-num">01</span>
      <h3>Handpicked Hotels &amp; Cruises</h3>
      <p>No random listings. Every property we recommend has been carefully vetted for quality, comfort, and genuine value.</p>
    </div>
    <div class="ta-why-card">
      <span class="ta-why-num">02</span>
      <h3>Expert Travel Advice</h3>
      <p>Our team lives and breathes travel. We give you honest, experience-backed recommendations — not just what pays us the most.</p>
    </div>
    <div class="ta-why-card">
      <span class="ta-why-num">03</span>
      <h3>2-Hour Response Guarantee</h3>
      <p>Send an enquiry and our travel expert will WhatsApp you within 2 hours with personalised options. No automated bots.</p>
    </div>
    <div class="ta-why-card">
      <span class="ta-why-num">04</span>
      <h3>Best Price Promise</h3>
      <p>We work directly with properties and cruise lines to get you the best rates, with perks that you won't find on generic booking sites.</p>
    </div>
    <div class="ta-why-card">
      <span class="ta-why-num">05</span>
      <h3>Tailored to Indian Travellers</h3>
      <p>Veg &amp; Jain friendly options, family packages, honeymoon specials — we understand what Indian travellers need and deliver exactly that.</p>
    </div>
    <div class="ta-why-card">
      <span class="ta-why-num">06</span>
      <h3>End-to-End Support</h3>
      <p>From enquiry to check-out, we're with you every step. Any issue during travel? Call us and we'll sort it out immediately.</p>
    </div>
  </div>
</section>

<!-- ══════════════════════════════════
     TESTIMONIALS
══════════════════════════════════ -->
<section class="ta-section-dark">
  <div style="max-width:1200px; margin:0 auto;">
    <p class="ta-label" style="color:var(--gold);">Travellers Love Us</p>
    <h2 class="ta-h2 ta-h2-light">Trusted by Travellers</h2>
    <div class="ta-divider"></div>
  </div>
  <div class="ta-testimonials">
    <div class="ta-testi-card">
      <span class="ta-testi-quote">"</span>
      <p>Amazing stay and exceptional service. Take Your Trip made our vacation truly memorable! They recommended the perfect hotel in Maldives and it was beyond our expectations.</p>
      <div class="ta-testi-author">
        <img src="/assets/images/stock/photo-1506869640319-fe1a24fd76dc-160.webp"
             alt="Neha Malhotra from Jaipur, happy TYT Luxe traveller"
             width="44" height="44"
             loading="lazy">
        <div>
          <span class="ta-testi-name">Neha Malhotra</span>
          <span class="ta-testi-loc">Jaipur</span>
        </div>
      </div>
    </div>
    <div class="ta-testi-card">
      <span class="ta-testi-quote">"</span>
      <p>The cruise was beyond our expectations. Everything was perfect from start to finish. The team responded in under an hour and handled every single detail for our family trip.</p>
      <div class="ta-testi-author">
        <img src="/assets/images/stock/photo-1529156069898-49953e39b3ac-160.webp"
             alt="Rohan Mehta from Mumbai, TYT Luxe cruise traveller"
             width="44" height="44"
             loading="lazy">
        <div>
          <span class="ta-testi-name">Rohan Mehta</span>
          <span class="ta-testi-loc">Mumbai</span>
        </div>
      </div>
    </div>
    <div class="ta-testi-card">
      <span class="ta-testi-quote">"</span>
      <p>Best holiday ever! Great deals and fantastic support throughout our trip. Our honeymoon package was curated so thoughtfully — every detail was just perfect.</p>
      <div class="ta-testi-author">
        <img src="/assets/images/stock/photo-1521336575822-6da63fb45455-160.webp"
             alt="Priya Sharma from Bangalore, TYT Luxe honeymoon traveller"
             width="44" height="44"
             loading="lazy">
        <div>
          <span class="ta-testi-name">Priya Sharma</span>
          <span class="ta-testi-loc">Bangalore</span>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ══════════════════════════════════
     CTA
══════════════════════════════════ -->
<section class="ta-cta">
  <h2>Ready to Plan Your Next Trip?</h2>
  <p>Talk to our travel experts today. We'll find you the perfect hotel or cruise — tailored just for you.</p>
  <div class="ta-cta-btns">
    <a href="{{ url('/contact') }}" class="ta-cta-btn ta-cta-btn-dark">Get In Touch</a>
    <a href="https://wa.me/919875073788" class="ta-cta-btn ta-cta-btn-outline">WhatsApp Us</a>
  </div>
</section>

<!-- ══════════════════════════════════
     TRUST STRIP
══════════════════════════════════ -->
<div class="ta-trust">
  <div class="ta-trust-inner">
    <div class="ta-trust-item">
      <span class="ta-trust-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M6 22V4a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v18Z"/><path d="M6 12H4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h2"/><path d="M18 9h2a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2h-2"/><path d="M10 6h4"/><path d="M10 10h4"/><path d="M10 14h4"/><path d="M10 18h4"/></svg></span>
      <div class="ta-trust-title">100+ Hotels</div>
      <div class="ta-trust-desc">Handpicked across India &amp; internationally</div>
    </div>
    <div class="ta-trust-item">
      <span class="ta-trust-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 10.189V14"/><path d="M12 2v3"/><path d="M19 13V7a2 2 0 0 0-2-2H7a2 2 0 0 0-2 2v6"/><path d="M19.38 20A11.6 11.6 0 0 0 21 14l-8.188-3.639a2 2 0 0 0-1.624 0L3 14a11.6 11.6 0 0 0 2.81 7.76"/><path d="M2 21c.6.5 1.2 1 2.5 1 2.5 0 2.5-2 5-2 1.3 0 1.9.5 2.5 1s1.2 1 2.5 1c2.5 0 2.5-2 5-2 1.3 0 1.9.5 2.5 1"/></svg></span>
      <div class="ta-trust-title">Premium Cruises</div>
      <div class="ta-trust-desc">Cordelia &amp; international cruise lines</div>
    </div>
    <div class="ta-trust-item">
      <span class="ta-trust-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 2v7c0 1.1.9 2 2 2h4a2 2 0 0 0 2-2V2"/><path d="M7 2v20"/><path d="M21 15V2a5 5 0 0 0-5 5v6c0 1.1.9 2 2 2h3Zm0 0v7"/></svg></span>
      <div class="ta-trust-title">Indian Friendly</div>
      <div class="ta-trust-desc">Veg, Jain &amp; family options always available</div>
    </div>
    <div class="ta-trust-item">
      <span class="ta-trust-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg></span>
      <a class="ta-trust-title" href="tel:+919875073788">98750 73788</a>
      <div class="ta-trust-desc">Call or WhatsApp — Mon to Sat, 10AM–7PM</div>
    </div>
  </div>
</div>

@endsection

@push('scripts')
<script>
  // Founders & Associates: "Read full profile" opens the card's full bio
  // in a popup. Esc, the × button or a click outside it closes it.
  (function () {
    var dialog = document.getElementById('taProfile');
    if (!dialog || typeof dialog.showModal !== 'function') return;
    var photo = document.getElementById('taProfilePhoto');
    var lastButton = null;

    document.querySelectorAll('.ta-team-more').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var card = btn.closest('.ta-team-card');
        photo.innerHTML = '';
        var pic = card.querySelector('.ta-team-card-photo > *');
        if (pic) {
          var copy = pic.cloneNode(true);
          if (copy.tagName === 'IMG') copy.removeAttribute('loading');
          photo.appendChild(copy);
        }
        document.getElementById('taProfileRole').textContent = card.querySelector('.ta-team-role').textContent;
        document.getElementById('taProfileName').textContent = card.querySelector('h3').textContent;
        var text = document.getElementById('taProfileText');
        text.innerHTML = '';
        card.querySelectorAll('.ta-team-full > p').forEach(function (p) { text.appendChild(p.cloneNode(true)); });
        lastButton = btn;
        dialog.showModal();
        document.body.style.overflow = 'hidden';
      });
    });

    document.getElementById('taProfileClose').addEventListener('click', function () { dialog.close(); });
    dialog.addEventListener('click', function (e) { if (e.target === dialog) dialog.close(); });
    dialog.addEventListener('close', function () {
      document.body.style.overflow = '';
      if (lastButton) lastButton.focus();
    });
  })();
</script>
@endpush
