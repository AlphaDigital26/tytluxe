@extends('layouts.frontend')

@push('styles')
<style>
@import url('https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400&family=Poppins:wght@300;400;500;600&display=swap');

/* ── ROOT ── */
.tyt-cruises {
  font-family:'Poppins',sans-serif;
  background:#f5f0e8;
  color:#1a1108;
  overflow:hidden;
}
.tyt-cruises *, .tyt-cruises *::before, .tyt-cruises *::after { box-sizing:border-box; margin:0; padding:0; }



/* ── UTIL ── */
.tc-section {
  width:100%;
  max-width:none;
  padding:88px 60px;
  margin:0;
}
.tc-section-full{
  width:100%;
  padding:88px 0;
}
.tc-label { font-size:.72rem; font-weight:600; letter-spacing:.3em; text-transform:uppercase; color:#b8935a; margin-bottom:12px; }
.tc-h2 { font-family:'Playfair Display',serif; font-size:clamp(2rem,4vw,3rem); font-weight:700; line-height:1.15; color:#1a1108; margin-bottom:16px; }
.tc-h2-light { color:#f5f0e8; }
.tc-divider { width:52px; height:2px; background:#b8935a; margin-bottom:52px; }
.tc-divider-center { margin:0 auto 52px; }
.tc-dark { background:#0f0c08; }
.tc-cream { background:#f5f0e8; }
.tc-inner {
  width:100%;
  max-width:none;
  margin:0;
  padding:0 60px;
}

/* ── DESTINATIONS ROW ── */
.tc-dest-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:3px; }
.tc-dest-card { position:relative; overflow:hidden; aspect-ratio:4/5; cursor:pointer; }
.tc-dest-card img { width:100%; height:100%; object-fit:cover; transition:transform .6s ease; }
.tc-dest-card:hover img { transform:scale(1.06); }
.tc-dest-overlay { position:absolute; inset:0; background:linear-gradient(to top,rgba(8,6,4,.82) 0%,rgba(8,6,4,.1) 55%,transparent 100%); }
.tc-dest-info { position:absolute; bottom:28px; left:28px; right:28px; }
.tc-dest-city { font-family:'Playfair Display',serif; font-size:1.7rem; font-weight:700; color:#f5f0e8; display:block; }
.tc-dest-tag { font-size:.7rem; letter-spacing:.16em; text-transform:uppercase; color:#b8935a; margin-top:4px; display:block; }

/* ── SHIP STATS ── */
.tc-ship-band { background:#1a1510; padding:48px 24px; }
.tc-ship-stats { display:flex; justify-content:center; flex-wrap:wrap; gap:0; max-width:900px; margin:0 auto; }
.tc-ship-stat { flex:1; min-width:140px; text-align:center; padding:28px 16px; border-right:1px solid rgba(184,147,90,.15); }
.tc-ship-stat:last-child { border-right:none; }
.tc-ship-stat-num { font-family:'Playfair Display',serif; font-size:2.2rem; font-weight:700; color:#b8935a; display:block; }
.tc-ship-stat-lbl { font-size:.7rem; letter-spacing:.14em; text-transform:uppercase; color:rgba(245,240,232,.55); margin-top:4px; display:block; }

/* ── EXPERIENCE TABS ── */
.tc-exp-tabs { display:flex; gap:0; flex-wrap:wrap; margin-bottom:48px; border-bottom:1px solid rgba(184,147,90,.2); }
.tc-exp-tab { font-family:'Poppins',sans-serif; font-size:.78rem; font-weight:600; letter-spacing:.12em; text-transform:uppercase; padding:14px 28px; background:none; border:none; border-bottom:2px solid transparent; cursor:pointer; color:#999; transition:all .2s; margin-bottom:-1px; }
.tc-exp-tab.active { color:#b8935a; border-bottom-color:#b8935a; }
.tc-exp-panel { display:none; animation:tcFadeUp .4s ease both; }
.tc-exp-panel.active { display:block; }

/* Experience cards */
.tc-exp-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(240px,1fr)); gap:24px; }
.tc-exp-card { background:#fff; border:1px solid rgba(184,147,90,.15); padding:32px 28px; transition:transform .22s,box-shadow .22s; }
.tc-exp-card:hover { transform:translateY(-4px); box-shadow:0 12px 40px rgba(0,0,0,.09); }
.tc-exp-card-icon { font-size:1.8rem; margin-bottom:14px; }
.tc-exp-card h3 { font-family:'Playfair Display',serif; font-size:1.15rem; margin-bottom:10px; color:#1a1108; }
.tc-exp-card p { font-size:.84rem; color:#777; line-height:1.75; }

/* Dining specific */
.tc-dining-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(200px,1fr)); gap:20px; }
.tc-dining-card { background:#fff; border:1px solid rgba(184,147,90,.12); overflow:hidden; }
.tc-dining-card img { width:100%; height:160px; object-fit:cover; }
.tc-dining-card-body { padding:20px; }
.tc-dining-card h3 { font-family:'Playfair Display',serif; font-size:1.1rem; color:#1a1108; margin-bottom:8px; }
.tc-dining-card p { font-size:.82rem; color:#888; line-height:1.7; }

/* Cabin cards */
.tc-cabin-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); gap:24px; }
.tc-cabin-card { background:#fff; border:1px solid rgba(184,147,90,.15); overflow:hidden; transition:transform .22s,box-shadow .22s; }
.tc-cabin-card:hover { transform:translateY(-4px); box-shadow:0 12px 40px rgba(0,0,0,.09); }
.tc-cabin-card img { width:100%; height:170px; object-fit:cover; }
.tc-cabin-card-body { padding:22px; }
.tc-cabin-tier { font-size:.65rem; font-weight:600; letter-spacing:.2em; text-transform:uppercase; color:#b8935a; margin-bottom:8px; }
.tc-cabin-card h3 { font-family:'Playfair Display',serif; font-size:1.1rem; color:#1a1108; margin-bottom:8px; }
.tc-cabin-card p { font-size:.81rem; color:#888; line-height:1.7; margin-bottom:12px; }
.tc-cabin-size { font-size:.75rem; font-weight:600; color:#1a1108; background:#f5f0e8; padding:6px 12px; display:inline-block; }

/* ── BOOKING FORM ── */
.tc-form-wrap { background:#fff; border:1px solid rgba(184,147,90,.2); padding:52px 48px; }
.tc-form-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); gap:20px; }
.tc-fg { display:flex; flex-direction:column; gap:7px; }
.tc-fg label { font-size:.7rem; font-weight:600; letter-spacing:.14em; text-transform:uppercase; color:#aaa; }
.tc-fg input, .tc-fg select, .tc-fg textarea {
  padding:13px 16px; border:1px solid #e0d9ce; background:#faf8f5;
  font-family:'Poppins',sans-serif; font-size:.88rem; color:#1a1108;
  outline:none; transition:border-color .2s; border-radius:0; -webkit-appearance:none;
}
.tc-fg input:focus, .tc-fg select:focus, .tc-fg textarea:focus { border-color:#b8935a; }
.tc-fg textarea { resize:vertical; min-height:90px; }
.tc-form-divider { grid-column:1/-1; height:1px; background:#ece7de; margin:4px 0; }
.tc-form-sec { grid-column:1/-1; font-size:.68rem; font-weight:700; letter-spacing:.2em; text-transform:uppercase; color:#b8935a; }
.tc-form-submit-row { grid-column:1/-1; display:flex; justify-content:flex-end; margin-top:8px; }
.tc-submit-btn { background:#b8935a; color:#fff; border:none; font-family:'Poppins',sans-serif; font-size:.84rem; font-weight:600; letter-spacing:.14em; text-transform:uppercase; padding:17px 52px; cursor:pointer; transition:all .2s; }
.tc-submit-btn:hover { background:#9a7a47; transform:translateY(-1px); }

/* ── TRUST STRIP ── */
.tc-trust { background:#1a1510; padding:44px 24px; }
.tc-trust-inner { max-width:1000px; margin:0 auto; display:flex; flex-wrap:wrap; justify-content:center; gap:48px; text-align:center; }
.tc-trust-title { font-size:.78rem; font-weight:600; letter-spacing:.12em; text-transform:uppercase; color:#b8935a; margin-bottom:6px; }
.tc-trust-desc { font-size:.82rem; color:rgba(245,240,232,.55); line-height:1.6; max-width:160px; }
.tc-trust-icon { font-size:1.5rem; margin-bottom:12px; display:block; }



@media(max-width:768px) {
  .tc-dest-grid { grid-template-columns:1fr 1fr; }
  .tc-form-wrap { padding:32px 20px; }
  .tc-hero-stats { gap:24px; }
  .tc-ship-stats { flex-direction:column; align-items:center; }
  .tc-ship-stat { border-right:none; border-bottom:1px solid rgba(184,147,90,.15); width:100%; }
  .tc-exp-tab { padding:10px 16px; font-size:.72rem; }
  .tc-section, .tc-inner { padding-left:24px; padding-right:24px; }
}
@media(max-width:480px) {
  .tc-dest-grid { grid-template-columns:1fr; }
}
</style>
@endpush

@section('content')

<div class="tyt-cruises">

<!-- HERO with brochure images slideshow -->
<!-- HERO with brochure images slideshow -->
<x-hero-carousel 
  :slides="[
    'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=1800&q=80',
    'https://images.unsplash.com/photo-1512100356356-de1b84283e18?auto=format&fit=crop&w=1800&q=80',
    'https://images.unsplash.com/photo-1500375592092-40eb2168fd21?auto=format&fit=crop&w=1800&q=80',
    'https://images.unsplash.com/photo-1520250497591-112f2f40a3f4?auto=format&fit=crop&w=1800&q=80'
  ]"
  eyebrow="Cordelia Cruises · India's Premium Cruise Line"
  title="Destination of<br><em>Your Dreams</em>"
  subtitle="Mumbai &bull; Goa &bull; Kochi &bull; Lakshadweep &bull; Chennai &bull; Sri Lanka"
  ctaText="Enquire Now"
  ctaLink="#tc-booking"
/>

<!-- SHIP STATS BAND -->
<div class="tc-ship-band">
  <div class="tc-ship-stats">
    <div class="tc-ship-stat"><span class="tc-ship-stat-num">All-Inclusive</span><span class="tc-ship-stat-lbl">Dining &amp; Entertainment</span></div>
    <div class="tc-ship-stat"><span class="tc-ship-stat-num">48,563 GT</span><span class="tc-ship-stat-lbl">Gross Tonnage</span></div>
    <div class="tc-ship-stat"><span class="tc-ship-stat-num">6 Ports</span><span class="tc-ship-stat-lbl">Mumbai to Sri Lanka</span></div>
    <div class="tc-ship-stat"><span class="tc-ship-stat-num">24/7</span><span class="tc-ship-stat-lbl">Onboard Support</span></div>
  </div>
</div>

<!-- DESTINATIONS -->
<section class="tc-section-full tc-dark">
  <div class="tc-inner" style="padding-top:0;padding-bottom:0;">
    <div class="tc-section" style="padding-bottom:40px;">
      <p class="tc-label" style="color:#b8935a;">Where We Sail</p>
      <h2 class="tc-h2 tc-h2-light">Six Stunning Destinations</h2>
      <div class="tc-divider"></div>
    </div>
  </div>
  <div class="tc-dest-grid">
    <div class="tc-dest-card">
      <img src="https://images.unsplash.com/photo-1595658658481-d53d3f999875?w=700&q=80" alt="Mumbai" loading="lazy">
      <div class="tc-dest-overlay"></div>
      <div class="tc-dest-info"><span class="tc-dest-city">Mumbai</span><span class="tc-dest-tag">Enjoy Unlimited Experiences</span></div>
    </div>
    <div class="tc-dest-card">
      <img src="https://images.unsplash.com/photo-1512343879784-a960bf40e7f2?w=700&q=80" alt="Goa" loading="lazy">
      <div class="tc-dest-overlay"></div>
      <div class="tc-dest-info"><span class="tc-dest-city">Goa</span><span class="tc-dest-tag">Party Capital of India</span></div>
    </div>
    <div class="tc-dest-card">
      <img src="https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=700&q=80" alt="Lakshadweep" loading="lazy">
      <div class="tc-dest-overlay"></div>
      <div class="tc-dest-info"><span class="tc-dest-city">Lakshadweep</span><span class="tc-dest-tag">India's Best Kept Secret</span></div>
    </div>
    <div class="tc-dest-card">
      <img src="https://images.unsplash.com/photo-1602216056096-3b40cc0c9944?auto=format&fit=crop&w=700&q=80" alt="Kochi" loading="lazy">
      <div class="tc-dest-overlay"></div>
      <div class="tc-dest-info"><span class="tc-dest-city">Kochi</span><span class="tc-dest-tag">Queen of the Arabian Sea</span></div>
    </div>
    <div class="tc-dest-card">
      <img src="https://images.unsplash.com/photo-1582510003544-4d00b7f74220?auto=format&fit=crop&w=700&q=80" alt="Chennai" loading="lazy">
      <div class="tc-dest-overlay"></div>
      <div class="tc-dest-info"><span class="tc-dest-city">Chennai</span><span class="tc-dest-tag">The Cultural Capital of India</span></div>
    </div>
    <div class="tc-dest-card">
      <img src="https://images.unsplash.com/photo-1588411393236-d2524cca1196?auto=format&fit=crop&w=700&q=80" alt="Sri Lanka" loading="lazy">
      <div class="tc-dest-overlay"></div>
      <div class="tc-dest-info"><span class="tc-dest-city">Sri Lanka</span><span class="tc-dest-tag">Island of Wonder</span></div>
    </div>
  </div>
</section>

<!-- EXPERIENCE TABS -->
<section class="tc-section tc-cream">
  <p class="tc-label">Life Onboard</p>
  <h2 class="tc-h2">Everything You Could Ever Want</h2>
  <div class="tc-divider"></div>

  <div class="tc-exp-tabs">
    <button class="tc-exp-tab active" onclick="tcTab(this,'dining')">Dining</button>
    <button class="tc-exp-tab" onclick="tcTab(this,'entertainment')">Entertainment</button>
    <button class="tc-exp-tab" onclick="tcTab(this,'bars')">Bars &amp; Lounges</button>
    <button class="tc-exp-tab" onclick="tcTab(this,'indulgence')">Indulgence</button>
    <button class="tc-exp-tab" onclick="tcTab(this,'events')">Events</button>
  </div>

  <!-- DINING -->
  <div class="tc-exp-panel active" id="tc-panel-dining">
    <p style="font-size:.9rem;color:#777;line-height:1.8;margin-bottom:36px;max-width:720px;">From premium restaurants and world-class dining to street food favourites — all food preferences are taken care of onboard The Empress. Pure vegetarian &amp; Jain options available throughout.</p>
    <div class="tc-dining-grid">
      <div class="tc-dining-card">
        <img src="https://images.unsplash.com/photo-1414235077428-338989a2e8c0?w=500&q=80" alt="Starlight" loading="lazy">
        <div class="tc-dining-card-body"><h3>Starlight</h3><p>Experience waterfront dining at Starlight, a two-level restaurant onboard.</p></div>
      </div>
      <div class="tc-dining-card">
        <img src="https://images.unsplash.com/photo-1579871494447-9811cf80d66c?w=500&q=80" alt="Chopstix" loading="lazy">
        <div class="tc-dining-card-body"><h3>Chopstix</h3><p>A culinary tour of exotic Pan-Asian cuisines at this speciality restaurant.</p></div>
      </div>
      <div class="tc-dining-card">
        <img src="https://images.unsplash.com/photo-1559339352-11d035aa65de?w=500&q=80" alt="Chef's Table" loading="lazy">
        <div class="tc-dining-card-body"><h3>Chef's Table</h3><p>A global culinary pavilion with delectable delicacies from a specially curated menu.</p></div>
      </div>
      <div class="tc-dining-card">
        <img src="https://images.unsplash.com/photo-1555396273-367ea4eb4db5?w=500&q=80" alt="Food Pavilions" loading="lazy">
        <div class="tc-dining-card-body"><h3>Food Pavilions</h3><p>Essence of India · Far Eastern Kadhai · Hot Clay Tandoor · International Grill · Kettle &amp; Bun · Street Food · Frozen desserts · The Cafe.</p></div>
      </div>
    </div>
  </div>

  <!-- ENTERTAINMENT -->
  <div class="tc-exp-panel" id="tc-panel-entertainment">
    <p style="font-size:.9rem;color:#777;line-height:1.8;margin-bottom:36px;max-width:720px;">From India's most popular entertainment shows at the Marquee Theatre to live music, magic shows, outdoor movie nights and professional theatre performances.</p>
    <div class="tc-exp-grid">
      <div class="tc-exp-card"><div class="tc-exp-card-icon">🎭</div><h3>Balle Balle Show</h3><p>A modern Bollywood musical comedy exploring love, arranged marriages and weddings. A heartwarming must-see for all fans of family-friendly musicals.</p></div>
      <div class="tc-exp-card"><div class="tc-exp-card-icon">🎵</div><h3>Live Entertainment</h3><p>From yesteryear's hits to contemporary music — relax your senses with soothing live tunes performed across the ship every evening.</p></div>
      <div class="tc-exp-card"><div class="tc-exp-card-icon">🎬</div><h3>Movies Under the Stars</h3><p>Catch the latest Bollywood &amp; Hollywood blockbusters with your loved ones under the open starry night sky on deck.</p></div>
      <div class="tc-exp-card"><div class="tc-exp-card-icon">🎧</div><h3>DJ Parties</h3><p>Dance to the lively tunes of our resident DJ until the wee hours of the night. Open for after-hours parties on the high seas.</p></div>
      <div class="tc-exp-card"><div class="tc-exp-card-icon">✨</div><h3>The Burlesque Experience</h3><p>An adults-only bold &amp; mesmerising performance on the high sea — perfect for those seeking a little extra spice to their evening.</p></div>
      <div class="tc-exp-card"><div class="tc-exp-card-icon">🎪</div><h3>All-Day Entertainment</h3><p>Entertainment options for everyone, wherever you go onboard — from morning activities to late-night shows, every hour is filled.</p></div>
    </div>
  </div>

  <!-- BARS -->
  <div class="tc-exp-panel" id="tc-panel-bars">
    <p style="font-size:.9rem;color:#777;line-height:1.8;margin-bottom:36px;max-width:720px;">Toast to the good life. Take your pick from our range of speciality creations, classic &amp; premium beverages. Lounge in style as you raise a glass to your getaway on the high seas.</p>
    <div class="tc-exp-grid">
      <div class="tc-exp-card"><div class="tc-exp-card-icon">🥂</div><h3>The Chairman's Club</h3><p>Savour the finest premium and super-premium beverages served in a modern chic setting that truly sets itself apart.</p></div>
      <div class="tc-exp-card"><div class="tc-exp-card-icon">🎶</div><h3>Connexions Bar</h3><p>Celebrate moments and life at the vibrant Connexions Bar. Get grooving to the music as you enjoy a selection of beverages served just the way you like it.</p></div>
      <div class="tc-exp-card"><div class="tc-exp-card-icon">🌅</div><h3>The Pool Bar</h3><p>Watch the sun melt into the waves as you relax by the Pool Bar on deck and sip on a perfect sundowner.</p></div>
      <div class="tc-exp-card"><div class="tc-exp-card-icon">🌙</div><h3>The Dome</h3><p>Savour the night at our late-night bar offering the finest selection of beverages in a private, exclusive space to enjoy your drink.</p></div>
    </div>
  </div>

  <!-- INDULGENCE -->
  <div class="tc-exp-panel" id="tc-panel-indulgence">
    <p style="font-size:.9rem;color:#777;line-height:1.8;margin-bottom:36px;max-width:720px;">Step aboard and discover a ship that has everything. From wellness retreats to adventure activities — Cordelia Cruises brings the 'ALL' in all-inclusive.</p>
    <div class="tc-exp-grid">
      <div class="tc-exp-card"><div class="tc-exp-card-icon">💆</div><h3>Spa &amp; Salon</h3><p>Experience wellness with an unbeatable view of the sea to refresh and rejuvenate your mind and body. Numerous beauty, hair &amp; body treatments available.</p></div>
      <div class="tc-exp-card"><div class="tc-exp-card-icon">💪</div><h3>Fitness Centre</h3><p>Power up with a 180-degree ocean view providing the perfect backdrop for an invigorating workout or a relaxing yoga session.</p></div>
      <div class="tc-exp-card"><div class="tc-exp-card-icon">🧗</div><h3>Rock Climbing</h3><p>Choose to elevate your day on the rock climbing wall in the middle of the ocean. Challenge a friend or just enjoy the stunning view from the top.</p></div>
      <div class="tc-exp-card"><div class="tc-exp-card-icon">🛍️</div><h3>Shopping</h3><p>Experience blissful indulgence with exclusive luxury shopping on your cruise holiday — retail therapy to make your vacation complete.</p></div>
      <div class="tc-exp-card"><div class="tc-exp-card-icon">🚤</div><h3>Shore Excursions</h3><p>Discover exciting new places and enjoy water sports, shopping, and local cuisines through guided shore excursions at every port.</p></div>
      <div class="tc-exp-card"><div class="tc-exp-card-icon">🎡</div><h3>Cordelia Academy</h3><p>A dedicated area for educational and fun activities for kids of all age groups. Child-care certified crew members take care of your little ones while you enjoy some me-time.</p></div>
    </div>
  </div>

  <!-- EVENTS -->
  <div class="tc-exp-panel" id="tc-panel-events">
    <div class="tc-exp-grid">
      <div class="tc-exp-card"><div class="tc-exp-card-icon">💼</div><h3>Corporate Events</h3><p>Decorated venues, spacious lounges, high-end theatres, sound technicians, catering services, live music and entertainment — everything for a grand corporate event at sea.</p></div>
      <div class="tc-exp-card"><div class="tc-exp-card-icon">💍</div><h3>Weddings at Sea</h3><p>Say 'I Do' on a cruise. From vibrant pre-wedding festivities to solemn nuptials, we offer indoor and on-deck venues with customised décor including Havan-Kund setup.</p></div>
    </div>
  </div>
</section>

<!-- ACCOMMODATION -->
<section class="tc-section-full tc-dark" style="padding-top:88px;padding-bottom:88px;">
  <div class="tc-inner">
    <p class="tc-label" style="color:#b8935a;">Your Home at Sea</p>
    <h2 class="tc-h2 tc-h2-light">Choose Your Stateroom</h2>
    <div class="tc-divider"></div>
    <div class="tc-cabin-grid">
      <div class="tc-cabin-card">
        <img src="https://images.unsplash.com/photo-1590490360182-c33d57733427?w=600&q=80" alt="Chairman's Suite" loading="lazy">
        <div class="tc-cabin-card-body">
          <div class="tc-cabin-tier">Most Luxurious</div>
          <h3>The Chairman's Suite</h3>
          <p>Fine linen, plush settings, and spacious living arrangements — the pinnacle of luxury at sea.</p>
          <span class="tc-cabin-size">Cabin: 596 Sq. Ft | Balcony: 222 Sq. Ft</span>
        </div>
      </div>
      <div class="tc-cabin-card">
        <img src="https://images.unsplash.com/photo-1631049307264-da0ec9d70304?w=600&q=80" alt="Suite" loading="lazy">
        <div class="tc-cabin-card-body">
          <div class="tc-cabin-tier">Premium</div>
          <h3>Suite</h3>
          <p>Sail the high seas in the comfort of our luxury Suite with a private balcony overlooking the ocean.</p>
          <span class="tc-cabin-size">Cabin: 303 Sq. Ft | Balcony: 222 Sq. Ft</span>
        </div>
      </div>
      <div class="tc-cabin-card">
        <img src="https://images.unsplash.com/photo-1520250497591-112f2f40a3f4?w=600&q=80" alt="Mini Suite" loading="lazy">
        <div class="tc-cabin-card-body">
          <div class="tc-cabin-tier">Balcony</div>
          <h3>Mini Suite</h3>
          <p>Wake up to a private view of the sea. Your private screening of the ocean is worth a million words.</p>
          <span class="tc-cabin-size">Cabin: 194 Sq. Ft | Balcony: 25 Sq. Ft</span>
        </div>
      </div>
      <div class="tc-cabin-card">
        <img src="https://images.unsplash.com/photo-1584132967334-10e028bd69f7?w=600&q=80" alt="Ocean View" loading="lazy">
        <div class="tc-cabin-card-body">
          <div class="tc-cabin-tier">Ocean View</div>
          <h3>Ocean View Stateroom</h3>
          <p>A private and cosy cabin of your own amidst the sea — exactly what our ocean view staterooms are all about.</p>
          <span class="tc-cabin-size">Cabin: 142 Sq. Ft</span>
        </div>
      </div>
      <div class="tc-cabin-card">
        <img src="https://images.unsplash.com/photo-1566073771259-6a8506099945?w=600&q=80" alt="Interior Stateroom" loading="lazy">
        <div class="tc-cabin-card-body">
          <div class="tc-cabin-tier">Value</div>
          <h3>Interior Stateroom</h3>
          <p>Budget-friendly interior rooms that promise a homely, comfortable feeling at sea — a great value choice.</p>
          <span class="tc-cabin-size">Cabin: 117 Sq. Ft</span>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- BOOKING FORM -->
<section class="tc-section tc-cream" id="tc-booking">
  <p class="tc-label">Plan Your Voyage</p>
  <h2 class="tc-h2">Book Your Cruise</h2>
  <div class="tc-divider"></div>
  <div class="tc-form-wrap">
    <div class="tc-form-grid">

      <div class="tc-form-sec">Voyage Details</div>

      <div class="tc-fg">
        <label>Departure Port</label>
        <select>
          <option value="">Select Port</option>
          <option>Mumbai</option>
          <option>Chennai</option>
          <option>Kochi</option>
        </select>
      </div>
      <div class="tc-fg">
        <label>Destination</label>
        <select>
          <option value="">Select Destination</option>
          <option>Goa</option>
          <option>Lakshadweep</option>
          <option>Kochi</option>
          <option>Chennai</option>
          <option>Sri Lanka</option>
          <option>Multi-Port Voyage</option>
        </select>
      </div>
      <div class="tc-fg">
        <label>Travel Date</label>
        <input type="date">
      </div>
      <div class="tc-fg">
        <label>Duration</label>
        <select>
          <option>2 Nights / 3 Days</option>
          <option>3 Nights / 4 Days</option>
          <option>4 Nights / 5 Days</option>
          <option>5 Nights / 6 Days</option>
          <option>7 Nights / 8 Days</option>
        </select>
      </div>
      <div class="tc-fg">
        <label>Cabin Type</label>
        <select>
          <option>Interior Stateroom</option>
          <option>Ocean View Stateroom</option>
          <option>Mini Suite</option>
          <option>Suite</option>
          <option>Chairman's Suite</option>
        </select>
      </div>
      <div class="tc-fg">
        <label>No. of Guests</label>
        <select>
          <option>1 Guest</option>
          <option>2 Guests</option>
          <option>3 Guests</option>
          <option>4 Guests</option>
          <option>5+ Guests / Group</option>
        </select>
      </div>

      <div class="tc-form-divider"></div>
      <div class="tc-form-sec">Your Details</div>

      <div class="tc-fg">
        <label>Full Name</label>
        <input type="text" placeholder="Your full name">
      </div>
      <div class="tc-fg">
        <label>Phone / WhatsApp</label>
        <input type="tel" placeholder="+91 98750 73788">
      </div>
      <div class="tc-fg">
        <label>Email Address</label>
        <input type="email" placeholder="you@email.com">
      </div>
      <div class="tc-fg" style="grid-column:1/-1">
        <label>Special Requests (optional)</label>
        <textarea placeholder="Dietary requirements, anniversary celebration, wheelchair access, Jain / vegetarian meals, etc."></textarea>
      </div>

      <div class="tc-form-submit-row">
        <button class="tc-submit-btn" onclick="tcSubmit()">Request a Quote &rarr;</button>
      </div>
    </div>
  </div>
</section>

<!-- TRUST STRIP -->
<div class="tc-trust">
  <div class="tc-trust-inner">
    <div class="tc-trust-item">
      <span class="tc-trust-icon">🚢</span>
      <div class="tc-trust-title">India's #1 Cruise</div>
      <div class="tc-trust-desc">Cordelia — the premium cruise line built for Indians</div>
    </div>
    <div class="tc-trust-item">
      <span class="tc-trust-icon">🍽️</span>
      <div class="tc-trust-title">All-Inclusive</div>
      <div class="tc-trust-desc">Dining, entertainment &amp; activities all included</div>
    </div>
    <div class="tc-trust-item">
      <span class="tc-trust-icon">🙏</span>
      <div class="tc-trust-title">Jain &amp; Veg Friendly</div>
      <div class="tc-trust-desc">Dedicated pure veg &amp; Jain counters onboard</div>
    </div>
    <div class="tc-trust-item">
      <span class="tc-trust-icon">📞</span>
      <div class="tc-trust-title">Expert Support</div>
      <div class="tc-trust-desc">Our team responds within 2 hours on WhatsApp</div>
    </div>
  </div>
</div>

</div><!-- end .tyt-cruises -->

@endsection

@push('scripts')
<script>
function tcTab(btn, panel) {
  document.querySelectorAll('.tc-exp-tab').forEach(b => b.classList.remove('active'));
  document.querySelectorAll('.tc-exp-panel').forEach(p => p.classList.remove('active'));
  btn.classList.add('active');
  document.getElementById('tc-panel-' + panel).classList.add('active');
}
function tcSubmit() {
  var req = document.querySelectorAll('.tc-form-wrap input[type="text"], .tc-form-wrap input[type="email"], .tc-form-wrap input[type="tel"]');
  var ok = true;
  req.forEach(function(i){ if(!i.value.trim()){i.style.borderColor='#c0392b';ok=false;}else{i.style.borderColor='';} });
  if(ok) alert('Thank you! Our cruise specialist will WhatsApp you within 2 hours with the best options for your voyage.');
  else alert('Please fill in your name, phone and email to continue.');
}


</script>
@endpush
