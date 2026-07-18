@extends('layouts.app')

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

/* ── HERO ── */
.ta-hero {
  position: relative;
  width: 100%;
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  text-align: center;
  overflow: hidden;
}

.ta-slide {
  position: absolute; inset: 0;
  background-size: cover; background-position: center;
  opacity: 0; transition: opacity 1.4s ease; z-index: 0;
}
.ta-slide-active { opacity: 1; z-index: 1; }

.ta-hero-overlay {
  position: absolute; inset: 0; z-index: 2;
  background: linear-gradient(
    to bottom,
    rgba(5,3,2,0.6) 0%,
    rgba(5,3,2,0.45) 40%,
    rgba(5,3,2,0.75) 80%,
    rgba(5,3,2,0.9) 100%
  );
}

.ta-hero-content {
  position: relative; z-index: 3;
  max-width: 860px; padding: 0 28px;
  animation: taFadeUp 1s ease both;
}

@keyframes taFadeUp {
  from { opacity: 0; transform: translateY(40px); }
  to { opacity: 1; transform: translateY(0); }
}

.ta-eyebrow {
  font-size: .75rem; font-weight: 600;
  letter-spacing: .32em; text-transform: uppercase;
  color: var(--gold); margin-bottom: 18px;
}

.ta-hero-title {
  font-family: 'Playfair Display', serif;
  font-size: clamp(2.8rem, 6.5vw, 5rem);
  font-weight: 700; color: var(--cream);
  line-height: 1.1; margin-bottom: 22px;
  text-shadow: 0 2px 30px rgba(0,0,0,.45);
}
.ta-hero-title em { font-style: italic; color: var(--gold); }

.ta-hero-sub {
  font-size: 1rem;
  color: rgba(245,240,232,.75);
  letter-spacing: .06em; margin-bottom: 38px;
  line-height: 1.8;
}

.ta-hero-cta {
  display: inline-block;
  font-size: .82rem; font-weight: 600;
  letter-spacing: .18em; text-transform: uppercase;
  color: #0a0806; background: var(--gold);
  padding: 16px 48px; text-decoration: none;
  transition: all .25s;
}
.ta-hero-cta:hover { background: var(--cream); transform: translateY(-2px); }

.ta-slide-dots {
  position: absolute; bottom: 28px; left: 50%; transform: translateX(-50%);
  display: flex; gap: 10px; z-index: 4;
}
.ta-dot {
  width: 8px; height: 8px; border-radius: 50%;
  background: rgba(245,240,232,0.35); cursor: pointer;
  transition: background .3s, transform .3s;
}
.ta-dot-active { background: var(--gold); transform: scale(1.3); }

/* ── STATS BAND ── */
.ta-stats-band {
  background: var(--dark-mid);
  padding: 48px 24px;
}
.ta-stats-inner {
  display: flex; justify-content: center;
  flex-wrap: wrap; gap: 0;
  max-width: 900px; margin: 0 auto;
}
.ta-stat {
  flex: 1; min-width: 140px; text-align: center;
  padding: 28px 16px;
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
  grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
  gap: 24px;
  max-width: 1200px; margin: 0 auto;
}
.ta-value-card {
  background: rgba(255,255,255,0.06);
  border: 1px solid var(--border-gold);
  padding: 40px 32px;
  transition: transform .22s, box-shadow .22s, border-color .22s;
}
.ta-value-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 16px 48px rgba(0,0,0,.3);
  border-color: rgba(184,147,90,0.5);
}
.ta-value-icon { font-size: 2rem; margin-bottom: 18px; display: block; }
.ta-value-card h3 {
  font-family: 'Playfair Display', serif;
  font-size: 1.2rem; color: var(--cream);
  margin-bottom: 12px;
}
.ta-value-card p {
  font-size: .84rem; color: rgba(245,240,232,.55);
  line-height: 1.8;
}

/* ── TEAM ── */
.ta-team-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 28px;
  max-width: 1200px; margin: 0 auto;
}
.ta-team-card {
  background: #fff;
  border: 1px solid var(--border-gold);
  overflow: hidden;
  transition: transform .22s, box-shadow .22s;
}
.ta-team-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 12px 40px rgba(0,0,0,.09);
}
.ta-team-card img {
  width: 100%; height: 260px; object-fit: cover;
}
.ta-team-photo-placeholder {
  width: 100%; height: 260px;
  background: #f7f3ea;
  border-bottom: 1px solid var(--border-gold);
}
.ta-team-card-body { padding: 24px; }
.ta-team-role {
  font-size: .65rem; font-weight: 600;
  letter-spacing: .2em; text-transform: uppercase;
  color: var(--gold); margin-bottom: 6px;
}
.ta-team-card h3 {
  font-family: 'Playfair Display', serif;
  font-size: 1.15rem; color: var(--text-dark);
  margin-bottom: 8px;
}
.ta-team-card p {
  font-size: .82rem; color: var(--text-muted);
  line-height: 1.7;
}

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
  padding: 44px 24px;
}
.ta-trust-inner {
  max-width: 1000px; margin: 0 auto;
  display: flex; flex-wrap: wrap;
  justify-content: center; gap: 48px; text-align: center;
}
.ta-trust-icon { font-size: 1.5rem; margin-bottom: 12px; display: block; }
.ta-trust-title {
  font-size: .78rem; font-weight: 600;
  letter-spacing: .12em; text-transform: uppercase;
  color: var(--gold); margin-bottom: 6px;
}
.ta-trust-desc {
  font-size: .82rem; color: rgba(245,240,232,.55);
  line-height: 1.6; max-width: 160px;
}

/* ── RESPONSIVE ── */
@media (max-width: 900px) {
  .ta-story-grid { grid-template-columns: 1fr; gap: 48px; }
  .ta-story-img-badge { bottom: -16px; right: -8px; }
  .ta-why-grid { grid-template-columns: 1fr 1fr; }
  .ta-section, .ta-section-dark, .ta-section-cream { padding: 60px 24px; }
  .ta-cta { padding: 60px 24px; }
}
@media (max-width: 580px) {
  .ta-why-grid { grid-template-columns: 1fr; }
  .ta-stats-inner { flex-direction: column; align-items: center; }
  .ta-stat { border-right: none; border-bottom: 1px solid rgba(184,147,90,.15); width: 100%; }
}
</style>
@endpush

@section('content')

<!-- ══════════════════════════════════
     HERO
══════════════════════════════════ -->
<section class="ta-hero" id="ta-hero-root">
  <div class="ta-slide ta-slide-active" style="background-image:url('https://images.unsplash.com/photo-1476514525535-07fb3b4ae5f1?auto=format&fit=crop&w=1800&q=80')"></div>
  <div class="ta-slide" style="background-image:url('https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=1800&q=80')"></div>
  <div class="ta-slide" style="background-image:url('https://images.unsplash.com/photo-1500375592092-40eb2168fd21?auto=format&fit=crop&w=1800&q=80')"></div>
  <div class="ta-hero-overlay"></div>
  <div class="ta-hero-content">
    <p class="ta-eyebrow">About Take Your Trip &middot; Our Story</p>
    <h1 class="ta-hero-title">We Craft Journeys,<br>Not Just <em>Itineraries</em></h1>
    <p class="ta-hero-sub">Born from a passion for travel, built on trust.<br>Take Your Trip is your personal gateway to the world's finest hotels and cruises.</p>
    <a href="{{ url('/contact') }}" class="ta-hero-cta">Get In Touch</a>
  </div>
  <div class="ta-slide-dots">
    <span class="ta-dot ta-dot-active" onclick="taGoSlide(0)"></span>
    <span class="ta-dot" onclick="taGoSlide(1)"></span>
    <span class="ta-dot" onclick="taGoSlide(2)"></span>
  </div>
</section>

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
      <img src="https://images.unsplash.com/photo-1522199755839-a2bacb67c546?auto=format&fit=crop&w=800&q=80" alt="Our Story">
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
  <div class="ta-team-grid">
    <div class="ta-team-card">
      <div class="ta-team-photo-placeholder" aria-label="Founder photo placeholder"></div>
      <div class="ta-team-card-body">
        <div class="ta-team-role">Founder</div>
        <h3>Kartik Singhal</h3>
        <p>Kartik Singhal is the visionary founder behind TYT Luxe, a premium travel brand created with the passion to redefine luxury travel experiences. With a strong entrepreneurial mindset and deep understanding of the travel industry, he envisioned a company that delivers not just holidays, but curated journeys designed with elegance, comfort and exclusivity.</p>
        <p>Driven by attention to detail and a commitment to personalized service, Kartik focuses on handpicked luxury stays, seamless travel planning and bespoke itineraries tailored to each client's preferences.</p>
        <p><strong>"Crafting Luxury Journeys. Creating Timeless Memories."</strong></p>
      </div>
    </div>
    <div class="ta-team-card">
      <div class="ta-team-photo-placeholder" aria-label="Associate head photo placeholder"></div>
      <div class="ta-team-card-body">
        <div class="ta-team-role">Associate Head</div>
        <h3>Swati Lohariwal</h3>
        <p>Swati Lohariwal serves as Associate Head at TYT Luxe with a sharp focus on precision, personalization and service excellence. She helps oversee end-to-end operational management so every journey reflects the brand's premium standards.</p>
        <p>Swati brings a strong eye for detail, client servicing expertise and a passion for curated travel experiences. Her approach emphasizes understanding individual preferences and translating them into thoughtfully designed itineraries that deliver comfort, exclusivity and memorable moments.</p>
      </div>
    </div>
    <div class="ta-team-card">
      <div class="ta-team-photo-placeholder" aria-label="Operational head photo placeholder"></div>
      <div class="ta-team-card-body">
        <div class="ta-team-role">Operational Head</div>
        <h3>Ashish Lohariwal</h3>
        <p>Ashish complements operations with strategic coordination, vendor partnerships and on-ground execution. His strength lies in streamlining logistics, maintaining quality benchmarks and ensuring smooth travel experiences across destinations.</p>
        <p>His operational leadership ensures that every booking is handled with efficiency and professionalism.</p>
      </div>
    </div>
    <div class="ta-team-card">
      <div class="ta-team-photo-placeholder" aria-label="Financial and compliance partner photo placeholder"></div>
      <div class="ta-team-card-body">
        <div class="ta-team-role">Financial &amp; Compliance Partner</div>
        <h3>Shailesh</h3>
        <p>Shailesh leads the financial framework of TYT Luxe with a sharp focus on accuracy, compliance and structured growth. As Financial &amp; Compliance Partner, he oversees budgeting, financial planning, vendor reconciliations and regulatory adherence across all operations.</p>
        <p>His meticulous approach ensures transparent accounting practices, efficient fund management and seamless financial coordination with global partners.</p>
      </div>
    </div>
    <div class="ta-team-card">
      <div class="ta-team-photo-placeholder" aria-label="Accounts and administration partner photo placeholder"></div>
      <div class="ta-team-card-body">
        <div class="ta-team-role">Accounts &amp; Administration Partner</div>
        <h3>Mayur</h3>
        <p>Mayur brings operational precision and administrative efficiency to TYT Luxe as Accounts &amp; Administration Partner. He manages day-to-day accounting workflows, financial documentation, invoicing systems and backend administrative processes that keep the organization running smoothly.</p>
        <p>His attention to detail and structured execution ensure timely reporting, streamlined coordination and organized financial operations.</p>
      </div>
    </div>
  </div>
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
      <span class="ta-value-icon">🎯</span>
      <h3>Curation Over Quantity</h3>
      <p>We don't list everything. We list the best. Every hotel and cruise is handpicked, reviewed, and selected for a specific type of traveller.</p>
    </div>
    <div class="ta-value-card">
      <span class="ta-value-icon">🤝</span>
      <h3>Personal Relationships</h3>
      <p>We're not a faceless booking engine. We're your travel partner — available on call, on WhatsApp, and always ready to go the extra mile.</p>
    </div>
    <div class="ta-value-card">
      <span class="ta-value-icon">💎</span>
      <h3>Value at Every Level</h3>
      <p>Luxury doesn't always mean expensive. We find you the best experience for your budget — whether that's ₹5,000 or ₹5,00,000 per night.</p>
    </div>
    <div class="ta-value-card">
      <span class="ta-value-icon">🌍</span>
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
        <img src="https://images.unsplash.com/photo-1506869640319-fe1a24fd76dc?auto=format&fit=crop&w=160&q=80" alt="Neha Malhotra">
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
        <img src="https://images.unsplash.com/photo-1529156069898-49953e39b3ac?auto=format&fit=crop&w=160&q=80" alt="Rohan Mehta">
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
        <img src="https://images.unsplash.com/photo-1521336575822-6da63fb45455?auto=format&fit=crop&w=160&q=80" alt="Priya Sharma">
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
      <span class="ta-trust-icon">🏨</span>
      <div class="ta-trust-title">100+ Hotels</div>
      <div class="ta-trust-desc">Handpicked across India &amp; internationally</div>
    </div>
    <div class="ta-trust-item">
      <span class="ta-trust-icon">🚢</span>
      <div class="ta-trust-title">Premium Cruises</div>
      <div class="ta-trust-desc">Cordelia &amp; international cruise lines</div>
    </div>
    <div class="ta-trust-item">
      <span class="ta-trust-icon">🙏</span>
      <div class="ta-trust-title">Indian Friendly</div>
      <div class="ta-trust-desc">Veg, Jain &amp; family options always available</div>
    </div>
    <div class="ta-trust-item">
      <span class="ta-trust-icon">📞</span>
      <div class="ta-trust-title">98750 73788</div>
      <div class="ta-trust-desc">Call or WhatsApp — Mon to Sat, 10AM–7PM</div>
    </div>
  </div>
</div>

@endsection

@push('scripts')
<script>
// Slideshow
var taSlideIdx = 0;
var taSlides = document.querySelectorAll('.ta-slide');
var taDots = document.querySelectorAll('.ta-dot');
function taGoSlide(n) {
  taSlides[taSlideIdx].classList.remove('ta-slide-active');
  taDots[taSlideIdx].classList.remove('ta-dot-active');
  taSlideIdx = n % taSlides.length;
  taSlides[taSlideIdx].classList.add('ta-slide-active');
  taDots[taSlideIdx].classList.add('ta-dot-active');
}
setInterval(function(){ taGoSlide(taSlideIdx + 1); }, 5000);
</script>
@endpush
