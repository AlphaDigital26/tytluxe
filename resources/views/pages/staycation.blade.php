@extends('layouts.app')

@push('styles')
<style>
  *, *::before, *::after { box-sizing: border-box; }

  :root {
    --black:      #0a0a0a;
    --dark:       #111111;
    --dark-card:  #161616;
    --dark-mid:   #1c1c1c;
    --border:     rgba(255,255,255,0.09);
    --gold:       #C9A84C;
    --gold-light: #E2C97E;
    --gold-dim:   rgba(201,168,76,0.15);
    --white:      #ffffff;
    --w80:        rgba(255,255,255,0.80);
    --w50:        rgba(255,255,255,0.50);
    --w30:        rgba(255,255,255,0.30);
  }

  body { font-family: 'Poppins', sans-serif; background: var(--black); color: var(--white); }

  /* HERO */
  .hero {
    position: relative; overflow: hidden;
    height: 520px; display: flex; align-items: center;
    justify-content: center; text-align: center;
    border-bottom: 1px solid var(--border);
  }
  .hero-slides { position: absolute; inset: 0; z-index: 0; }
  .hero-slide {
    position: absolute; inset: 0;
    background-size: cover; background-position: center;
    opacity: 0; transition: opacity 1.2s ease;
  }
  .hero-slide.active { opacity: 1; }
  .hero-slide::after {
    content: ''; position: absolute; inset: 0;
    background: linear-gradient(to bottom, rgba(0,0,0,0.4) 0%, rgba(0,0,0,0.6) 50%, rgba(0,0,0,0.82) 100%);
  }
  .hero-content { position: relative; z-index: 2; padding: 0 2rem; }
  .hero-tag { font-size: 10px; font-weight: 600; letter-spacing: .25em; text-transform: uppercase; color: var(--gold); display: block; margin-bottom: 1rem; }
  .hero h1 { font-family: 'Playfair Display', serif; font-size: clamp(2.2rem,4.5vw,3.5rem); font-weight: 400; color: var(--white); line-height: 1.2; margin-bottom: .75rem; }
  .hero h1 em { font-style: italic; color: var(--gold-light); }
  .hero p { font-size: 14px; color: rgba(255,255,255,0.72); font-weight: 300; max-width: 520px; margin: 0 auto; }
  .gold-line { width: 50px; height: 2px; background: var(--gold); margin: 1.5rem auto 0; opacity: .7; }

  .hero-arrow {
    position: absolute; top: 50%; transform: translateY(-50%);
    z-index: 3; width: 40px; height: 40px; border-radius: 50%;
    border: 1px solid rgba(255,255,255,0.3); background: rgba(0,0,0,0.35);
    color: rgba(255,255,255,0.8); font-size: 18px; cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    transition: all .2s; backdrop-filter: blur(4px);
  }
  .hero-arrow:hover { border-color: var(--gold); color: var(--gold); }
  .hero-arrow.prev { left: 1.5rem; }
  .hero-arrow.next { right: 1.5rem; }
  .hero-dots { position: absolute; bottom: 1.25rem; left: 50%; transform: translateX(-50%); z-index: 3; display: flex; gap: 7px; }
  .hero-dot { width: 7px; height: 7px; border-radius: 50%; background: rgba(255,255,255,0.35); border: 1px solid rgba(255,255,255,0.5); cursor: pointer; transition: all .3s; }
  .hero-dot.active { background: var(--gold); border-color: var(--gold); width: 22px; border-radius: 3px; }

  /* SECTION HELPERS */
  .s-divider { display: flex; align-items: center; gap: 1rem; max-width: 1100px; margin: 0 auto; padding: 0 3rem; }
  .s-divider::before,.s-divider::after { content: ''; flex: 1; height: 1px; background: var(--border); }
  .s-divider span { font-size: 9px; color: var(--w30); letter-spacing: .18em; text-transform: uppercase; }

  /* RESORT SECTION */
  .resort-section { padding: 4rem 3rem; max-width: 1200px; margin: 0 auto; }

  .resort-header { margin-bottom: 2.5rem; }
  .resort-label { font-size: 10px; font-weight: 600; letter-spacing: .22em; text-transform: uppercase; color: var(--gold); margin-bottom: .4rem; }
  .resort-name { font-family: 'Playfair Display', serif; font-size: clamp(1.6rem,3vw,2.2rem); font-weight: 400; color: var(--white); line-height: 1.2; margin-bottom: .75rem; }
  .resort-name em { font-style: italic; color: var(--gold-light); }
  .resort-desc { font-size: 13.5px; color: var(--w50); font-weight: 300; line-height: 1.8; max-width: 780px; }

  /* ROOM CATEGORY LABEL */
  .rooms-label { font-size: 10px; font-weight: 600; letter-spacing: .22em; text-transform: uppercase; color: var(--w30); margin-bottom: 1.5rem; }

  /* ROOM CARDS GRID */
  .rooms-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px,1fr)); gap: 1.25rem; }

  .room-card {
    background: var(--dark-card); border: 1px solid var(--border);
    border-radius: 10px; overflow: hidden;
    transition: transform .25s, box-shadow .25s, border-color .25s;
    position: relative;
  }
  .room-card:hover { transform: translateY(-4px); box-shadow: 0 18px 45px rgba(0,0,0,.5); border-color: rgba(201,168,76,.3); }

  .room-img { width: 100%; height: 180px; object-fit: cover; display: block; transition: transform .4s; }
  .room-card:hover .room-img { transform: scale(1.04); }
  .room-img-wrap { overflow: hidden; position: relative; }
  .room-img-wrap::after {
    content: ''; position: absolute; inset: 0;
    background: linear-gradient(to bottom, rgba(0,0,0,0) 40%, rgba(0,0,0,.65) 100%);
  }

  .room-body { padding: 1.25rem; }
  .room-name { font-family: 'Playfair Display', serif; font-size: 1.05rem; font-weight: 400; color: var(--white); margin-bottom: .5rem; border-left: 3px solid var(--gold); padding-left: .7rem; line-height: 1.3; }
  .room-desc { font-size: 11.5px; color: var(--w50); font-weight: 300; line-height: 1.7; margin-bottom: 1rem; }

  /* Amenities */
  .amenities { display: flex; flex-wrap: wrap; gap: .5rem; margin-bottom: 1rem; }
  .amenity {
    display: flex; align-items: center; gap: 5px;
    font-size: 10px; font-weight: 500; letter-spacing: .06em; text-transform: uppercase;
    color: var(--w50); background: var(--dark-mid); border: 1px solid var(--border);
    border-radius: 100px; padding: 4px 10px;
  }

  .room-footer { display: flex; align-items: center; justify-content: space-between; padding-top: .75rem; border-top: 1px solid var(--border); }
  .room-price { font-size: 12px; color: var(--gold); font-weight: 500; }
  .room-cta {
    font-size: 10px; font-weight: 600; letter-spacing: .08em; text-transform: uppercase;
    color: var(--black); background: var(--gold); border: none; border-radius: 5px;
    padding: .4rem .9rem; cursor: pointer; text-decoration: none; transition: background .2s;
  }
  .room-cta:hover { background: var(--gold-light); }

  /* BOTTOM CTA */
  .bottom-section { max-width: 700px; margin: 1rem auto 5rem; padding: 0 2rem; text-align: center; }
  .bottom-card {
    background: var(--dark-card); border: 1px solid var(--border);
    border-radius: 12px; padding: 2.5rem 2rem;
    position: relative; overflow: hidden;
  }
  .bottom-card::before {
    content: ''; position: absolute; top: -60px; left: 50%; transform: translateX(-50%);
    width: 300px; height: 300px; border-radius: 50%;
    background: radial-gradient(circle,rgba(201,168,76,.08) 0%,transparent 70%);
  }
  .bc-tag { font-size: 10px; font-weight: 600; letter-spacing: .2em; text-transform: uppercase; color: var(--gold); display: block; margin-bottom: .75rem; }
  .bottom-card h3 { font-family: 'Playfair Display', serif; font-size: 1.6rem; font-weight: 400; color: var(--white); margin-bottom: .5rem; }
  .bottom-card h3 em { font-style: italic; color: var(--gold-light); }
  .bottom-card p { font-size: 13px; color: var(--w50); line-height: 1.8; margin-bottom: 1.5rem; }
  .or-row { display: flex; align-items: center; gap: .75rem; margin: 1.25rem 0; }
  .or-row::before,.or-row::after { content: ''; flex: 1; height: 1px; background: var(--border); }
  .or-row span { font-size: 10px; color: var(--w30); letter-spacing: .1em; }
  .wa-btn {
    display: inline-flex; align-items: center; gap: 10px; background: #25D366; color: #fff;
    text-decoration: none; border-radius: 6px; padding: .85rem 1.75rem;
    font-size: 12px; font-weight: 600; letter-spacing: .08em; text-transform: uppercase;
    transition: opacity .2s, transform .15s;
  }
  .wa-btn:hover { opacity: .9; transform: translateY(-1px); }
  .wa-btn svg { width: 18px; height: 18px; fill: #fff; flex-shrink: 0; }

  @media(max-width:768px) {
    .resort-section { padding: 3rem 1.25rem; }
    .rooms-grid { grid-template-columns: 1fr; }
  }
</style>
@endpush

@section('content')

<!-- HERO -->
<section class="hero">
  <div class="hero-slides">
    <div class="hero-slide active" style="background-image:url('https://meritashotels.com/wp-content/uploads/2023/06/Deluxe-Room.jpg')"></div>
    <div class="hero-slide" style="background-image:url('https://meritashotels.com/wp-content/uploads/2023/03/DSC_9452-HDR-copy.jpg')"></div>
    <div class="hero-slide" style="background-image:url('https://meritashotels.com/wp-content/uploads/2023/03/Standard-Room-with-Sit-Out3.png')"></div>
    <div class="hero-slide" style="background-image:url('https://meritashotels.com/wp-content/uploads/2023/03/Suite-Bed-Room-%40-Picaddle.jpg')"></div>
    <div class="hero-slide" style="background-image:url('https://meritashotels.com/wp-content/uploads/2023/03/DSC_9476-HDR-copy.jpg')"></div>
  </div>
  <button class="hero-arrow prev" onclick="heroSlide(-1)">&#8592;</button>
  <button class="hero-arrow next" onclick="heroSlide(1)">&#8594;</button>
  <div class="hero-content">
    <span class="hero-tag">Curated Staycations</span>
    <h1>Escape the Ordinary. <em>Stay Extraordinary.</em></h1>
    <p>Handpicked resort stays near Mumbai &amp; Pune - perfect for weekends, honeymoons &amp; family getaways.</p>
    <div class="gold-line"></div>
  </div>
  <div class="hero-dots">
    <div class="hero-dot active" onclick="goToHeroSlide(0)"></div>
    <div class="hero-dot" onclick="goToHeroSlide(1)"></div>
    <div class="hero-dot" onclick="goToHeroSlide(2)"></div>
    <div class="hero-dot" onclick="goToHeroSlide(3)"></div>
    <div class="hero-dot" onclick="goToHeroSlide(4)"></div>
  </div>
</section>


<!-- RESORT 1: MERITAS PICADDLE -->
<div class="resort-section">
  <div class="resort-header">
    <div class="resort-label">Lonavala - Resort 01</div>
    <div class="resort-name">Meritas Picaddle Resort, <em>Lonavala</em></div>
    <p class="resort-desc">Known for its opulence and grandness, Meritas Picaddle Resort has firmly established itself as one of the top ranking luxury hotels in Lonavala. This 3 star resort has earned the recognition of being the best resort in Lonavala &amp; within the vicinity of Mumbai and Pune.</p>
  </div>
  <div class="rooms-label">Room Categories</div>
  <div class="rooms-grid">

    <div class="room-card">
      <div class="room-img-wrap">
        <img class="room-img" src="https://meritashotels.com/wp-content/uploads/2023/06/Deluxe-Room.jpg" alt="Deluxe Room">
      </div>
      <div class="room-body">
        <div class="room-name">Deluxe Room</div>
        <p class="room-desc">Pool-side view with elegant charm. Ideal for a luxurious and comforting retreat. Size: 280 Sq. Ft.</p>
        <div class="amenities">
          <span class="amenity">Up to 3 Guests</span>
          <span class="amenity">Queen Bed</span>
          <span class="amenity">AC</span>
          <span class="amenity">Flat-Screen TV</span>
          <span class="amenity">Free WiFi</span>
          <span class="amenity">Minibar</span>
          <span class="amenity">Kettle</span>
        </div>
        <div class="room-footer">
          <a href="https://wa.me/919875073788" class="room-cta">Enquire</a>
        </div>
      </div>
    </div>

    <div class="room-card">
      <div class="room-img-wrap">
        <img class="room-img" src="https://meritashotels.com/wp-content/uploads/2023/06/1683639117_593_Bathtub.jpeg" alt="Deluxe Room with Bathtub">
      </div>
      <div class="room-body">
        <div class="room-name">Deluxe Room with Bathtub</div>
        <p class="room-desc">Pool-side view with a private bathtub for added luxury. Size: 280 Sq. Ft.</p>
        <div class="amenities">
          <span class="amenity">Up to 3 Guests</span>
          <span class="amenity">Queen Bed</span>
          <span class="amenity">AC</span>
          <span class="amenity">Flat-Screen TV</span>
          <span class="amenity">Free WiFi</span>
          <span class="amenity">Bathtub</span>
          <span class="amenity">Minibar</span>
          <span class="amenity">Kettle</span>
        </div>
        <div class="room-footer">
          <a href="https://wa.me/919875073788" class="room-cta">Enquire</a>
        </div>
      </div>
    </div>

    <div class="room-card">
      <div class="room-img-wrap">
        <img class="room-img" src="https://meritashotels.com/wp-content/uploads/2023/03/Executive-Room-Image-2.jpg" alt="Executive Room">
      </div>
      <div class="room-body">
        <div class="room-name">Executive Room</div>
        <p class="room-desc">City or pool views in lavish fully-equipped rooms. Size: 310-340 Sq. Ft.</p>
        <div class="amenities">
          <span class="amenity">Up to 3 Guests</span>
          <span class="amenity">Queen Bed</span>
          <span class="amenity">AC</span>
          <span class="amenity">Flat-Screen TV</span>
          <span class="amenity">Free WiFi</span>
          <span class="amenity">Minibar</span>
          <span class="amenity">Kettle</span>
        </div>
        <div class="room-footer">
          <a href="https://wa.me/919875073788" class="room-cta">Enquire</a>
        </div>
      </div>
    </div>

    <div class="room-card">
      <div class="room-img-wrap">
        <img class="room-img" src="https://meritashotels.com/wp-content/uploads/2023/03/Executive-Room-image-3.jpg" alt="Executive Room with Bathtub">
      </div>
      <div class="room-body">
        <div class="room-name">Executive Room with Bathtub</div>
        <p class="room-desc">City or pool views with a private bathtub. Size: 310-340 Sq. Ft.</p>
        <div class="amenities">
          <span class="amenity">Up to 3 Guests</span>
          <span class="amenity">Queen Bed</span>
          <span class="amenity">AC</span>
          <span class="amenity">Flat-Screen TV</span>
          <span class="amenity">Free WiFi</span>
          <span class="amenity">Bathtub</span>
          <span class="amenity">Minibar</span>
          <span class="amenity">Kettle</span>
        </div>
        <div class="room-footer">
          <a href="https://wa.me/919875073788" class="room-cta">Enquire</a>
        </div>
      </div>
    </div>

    <div class="room-card" style="border-color:rgba(201,168,76,0.3);">
      <div class="room-img-wrap">
        <img class="room-img" src="https://meritashotels.com/wp-content/uploads/2023/03/Suite-Bed-Room-%40-Picaddle.jpg" alt="Suite">
      </div>
      <div class="room-body">
        <div class="room-name">Suite</div>
        <p class="room-desc">Spacious 550 Sq. Ft. suite with private living room, bedroom and exquisite furnishings.</p>
        <div class="amenities">
          <span class="amenity">Up to 3 Guests</span>
          <span class="amenity">Queen Bed</span>
          <span class="amenity">AC</span>
          <span class="amenity">Flat-Screen TV</span>
          <span class="amenity">Free WiFi</span>
          <span class="amenity">Bathtub</span>
          <span class="amenity">Minibar</span>
          <span class="amenity">Kettle</span>
        </div>
        <div class="room-footer">
          <a href="https://wa.me/919875073788" class="room-cta">Enquire</a>
        </div>
      </div>
    </div>

  </div>
</div>

<div class="s-divider"><span>*</span></div>


<!-- RESORT 2: MERITAS AURA -->
<div class="resort-section">
  <div class="resort-header">
    <div class="resort-label">Lonavala - Resort 02</div>
    <div class="resort-name">Meritas Aura Resort, <em>Lonavala</em></div>
    <p class="resort-desc">A magnificent resort settled within lush hills. Whether you're looking for a quiet romantic retreat, a rejuvenating time with friends, or some solitary respite - the resort will ensure your stay is truly remarkable.</p>
  </div>
  <div class="rooms-label">Room Categories</div>
  <div class="rooms-grid">

    <div class="room-card">
      <div class="room-img-wrap">
        <img class="room-img" src="https://meritashotels.com/wp-content/uploads/2023/03/DSC_9452-HDR-copy.jpg" alt="Deluxe Room with Sit Out">
      </div>
      <div class="room-body">
        <div class="room-name">Deluxe Room with Sit Out</div>
        <p class="room-desc">Comfortable deluxe rooms with a private sit-out balcony to soak in the lush surroundings.</p>
        <div class="amenities">
          <span class="amenity">Up to 3 Guests</span>
          <span class="amenity">Queen Bed</span>
          <span class="amenity">AC</span>
          <span class="amenity">LED TV</span>
          <span class="amenity">Free WiFi</span>
          <span class="amenity">Balcony</span>
        </div>
        <div class="room-footer">
          <a href="https://wa.me/919875073788" class="room-cta">Enquire</a>
        </div>
      </div>
    </div>

    <div class="room-card">
      <div class="room-img-wrap">
        <img class="room-img" src="https://meritashotels.com/wp-content/uploads/2023/03/DSC_9419-HDR-copy.jpg" alt="Superior Room with Balcony">
      </div>
      <div class="room-body">
        <div class="room-name">Superior Room with Balcony</div>
        <p class="room-desc">Elevated comfort with a private balcony overlooking the hills of Lonavala.</p>
        <div class="amenities">
          <span class="amenity">Up to 3 Guests</span>
          <span class="amenity">Queen Bed</span>
          <span class="amenity">AC</span>
          <span class="amenity">LED TV</span>
          <span class="amenity">Free WiFi</span>
          <span class="amenity">Balcony</span>
          <span class="amenity">Kettle</span>
        </div>
        <div class="room-footer">
          <a href="https://wa.me/919875073788" class="room-cta">Enquire</a>
        </div>
      </div>
    </div>

    <div class="room-card">
      <div class="room-img-wrap">
        <img class="room-img" src="https://meritashotels.com/wp-content/uploads/2023/03/DSC_9424-HDR-copy.jpg" alt="Suite Room with Sit Out">
      </div>
      <div class="room-body">
        <div class="room-name">Suite Room with Sit Out</div>
        <p class="room-desc">Spacious suite experience with a private sit-out, perfect for families and couples.</p>
        <div class="amenities">
          <span class="amenity">Up to 4 Guests</span>
          <span class="amenity">Queen Bed</span>
          <span class="amenity">AC</span>
          <span class="amenity">LED TV</span>
          <span class="amenity">Free WiFi</span>
          <span class="amenity">Balcony</span>
          <span class="amenity">Kettle</span>
        </div>
        <div class="room-footer">
          <a href="https://wa.me/919875073788" class="room-cta">Enquire</a>
        </div>
      </div>
    </div>

    <div class="room-card" style="border-color:rgba(201,168,76,0.3);">
      <div class="room-img-wrap">
        <img class="room-img" src="https://meritashotels.com/wp-content/uploads/2023/03/DSC_9476-HDR-copy.jpg" alt="Suite Room with Plunge Pool">
      </div>
      <div class="room-body">
        <div class="room-name">Suite Room with Plunge Pool</div>
        <p class="room-desc">Ultimate luxury - a private plunge pool suite with balcony views of the hills.</p>
        <div class="amenities">
          <span class="amenity">Up to 4 Guests</span>
          <span class="amenity">Queen Bed</span>
          <span class="amenity">AC</span>
          <span class="amenity">LED TV</span>
          <span class="amenity">Free WiFi</span>
          <span class="amenity">Balcony</span>
          <span class="amenity">Kettle</span>
        </div>
        <div class="room-footer">
          <a href="https://wa.me/919875073788" class="room-cta">Enquire</a>
        </div>
      </div>
    </div>

    <div class="room-card">
      <div class="room-img-wrap">
        <img class="room-img" src="https://meritashotels.com/wp-content/uploads/2023/03/DSC_9452-HDR-copy.jpg" alt="Row House with Sit Out">
      </div>
      <div class="room-body">
        <div class="room-name">Row House with Sit Out</div>
        <p class="room-desc">Ideal for large groups - a full row house with sit-out terrace and valley views.</p>
        <div class="amenities">
          <span class="amenity">Up to 10 Guests</span>
          <span class="amenity">Queen Bed</span>
          <span class="amenity">AC</span>
          <span class="amenity">LED TV</span>
          <span class="amenity">Free WiFi</span>
          <span class="amenity">Balcony</span>
          <span class="amenity">Kettle</span>
        </div>
        <div class="room-footer">
          <a href="https://wa.me/919875073788" class="room-cta">Enquire</a>
        </div>
      </div>
    </div>

    <div class="room-card" style="border-color:rgba(201,168,76,0.3);">
      <div class="room-img-wrap">
        <img class="room-img" src="https://meritashotels.com/wp-content/uploads/2023/03/DSC_9476-HDR-copy.jpg" alt="Row House with Large Plunge Pool">
      </div>
      <div class="room-body">
        <div class="room-name">Row House with Large Plunge Pool</div>
        <p class="room-desc">Exclusive row house with a private large plunge pool - the ultimate group retreat.</p>
        <div class="amenities">
          <span class="amenity">Up to 10 Guests</span>
          <span class="amenity">Queen Bed</span>
          <span class="amenity">AC</span>
          <span class="amenity">LED TV</span>
          <span class="amenity">Free WiFi</span>
          <span class="amenity">Balcony</span>
          <span class="amenity">Kettle</span>
        </div>
        <div class="room-footer">
          <a href="https://wa.me/919875073788" class="room-cta">Enquire</a>
        </div>
      </div>
    </div>

  </div>
</div>

<div class="s-divider"><span>*</span></div>


<!-- RESORT 3: MERITAS CRYSTAL -->
<div class="resort-section">
  <div class="resort-header">
    <div class="resort-label">Lonavala - Resort 03</div>
    <div class="resort-name">Meritas Crystal Resort, <em>Lonavala</em></div>
    <p class="resort-desc">Nestled in the majestic Lonavala valley, Meritas Crystal Resort is an extension of the trademark luxury offered across all Meritas properties. A great combination of accommodation, amenities and serenity - with state-of-the-art facilities for complete comfort.</p>
  </div>
  <div class="rooms-label">Room Categories</div>
  <div class="rooms-grid">

    <div class="room-card">
      <div class="room-img-wrap">
        <img class="room-img" src="https://meritashotels.com/wp-content/uploads/2023/03/Standard-Room-with-Sit-Out3.png" alt="Standard Room with Sit Out">
      </div>
      <div class="room-body">
        <div class="room-name">Standard Room with Sit Out</div>
        <p class="room-desc">Comfortable well-appointed room with a private sit-out to enjoy the valley breeze.</p>
        <div class="amenities">
          <span class="amenity">Up to 2 Guests</span>
          <span class="amenity">Queen Bed</span>
          <span class="amenity">AC</span>
          <span class="amenity">LED TV</span>
          <span class="amenity">Free WiFi</span>
          <span class="amenity">Kettle</span>
        </div>
        <div class="room-footer">
          <a href="https://wa.me/919875073788" class="room-cta">Enquire</a>
        </div>
      </div>
    </div>

    <div class="room-card">
      <div class="room-img-wrap">
        <img class="room-img" src="https://meritashotels.com/wp-content/uploads/2023/03/Deluxe-Room.png" alt="Deluxe Room">
      </div>
      <div class="room-body">
        <div class="room-name">Deluxe Room</div>
        <p class="room-desc">Stylish deluxe rooms with modern amenities and the serene Lonavala atmosphere.</p>
        <div class="amenities">
          <span class="amenity">Up to 3 Guests</span>
          <span class="amenity">Queen Bed</span>
          <span class="amenity">AC</span>
          <span class="amenity">LED TV</span>
          <span class="amenity">Free WiFi</span>
          <span class="amenity">Kettle</span>
        </div>
        <div class="room-footer">
          <a href="https://wa.me/919875073788" class="room-cta">Enquire</a>
        </div>
      </div>
    </div>

    <div class="room-card">
      <div class="room-img-wrap">
        <img class="room-img" src="https://meritashotels.com/wp-content/uploads/2023/03/Executive-Room-1.png" alt="Executive Room">
      </div>
      <div class="room-body">
        <div class="room-name">Executive Room</div>
        <p class="room-desc">Indulgent executive accommodation with king bed and premium minibar service.</p>
        <div class="amenities">
          <span class="amenity">Up to 3 Guests</span>
          <span class="amenity">King Bed</span>
          <span class="amenity">AC</span>
          <span class="amenity">LED TV</span>
          <span class="amenity">Free WiFi</span>
          <span class="amenity">Minibar</span>
          <span class="amenity">Kettle</span>
        </div>
        <div class="room-footer">
          <a href="https://wa.me/919875073788" class="room-cta">Enquire</a>
        </div>
      </div>
    </div>

    <div class="room-card">
      <div class="room-img-wrap">
        <img class="room-img" src="https://meritashotels.com/wp-content/uploads/2023/03/Superior-Room-with-Loft-3.png" alt="Superior Room with Loft">
      </div>
      <div class="room-body">
        <div class="room-name">Superior Room with Loft</div>
        <p class="room-desc">Unique loft-style room ideal for families - king bed with ample space for up to 4 guests.</p>
        <div class="amenities">
          <span class="amenity">Up to 4 Guests</span>
          <span class="amenity">King Bed</span>
          <span class="amenity">AC</span>
          <span class="amenity">LED TV</span>
          <span class="amenity">Free WiFi</span>
          <span class="amenity">Minibar</span>
          <span class="amenity">Kettle</span>
        </div>
        <div class="room-footer">
          <a href="https://wa.me/919875073788" class="room-cta">Enquire</a>
        </div>
      </div>
    </div>

    <div class="room-card">
      <div class="room-img-wrap">
        <img class="room-img" src="https://meritashotels.com/wp-content/uploads/2023/03/Super-Deluxe-Room-with-Bathtub-and-Balcony1.png" alt="Super Deluxe Room with Bathtub and Balcony">
      </div>
      <div class="room-body">
        <div class="room-name">Super Deluxe Room with Bathtub &amp; Balcony</div>
        <p class="room-desc">Premium room with king bed, private bathtub and a stunning balcony view.</p>
        <div class="amenities">
          <span class="amenity">Up to 3 Guests</span>
          <span class="amenity">King Bed</span>
          <span class="amenity">AC</span>
          <span class="amenity">LED TV</span>
          <span class="amenity">Free WiFi</span>
          <span class="amenity">Bathtub</span>
          <span class="amenity">Minibar</span>
          <span class="amenity">Kettle</span>
        </div>
        <div class="room-footer">
          <a href="https://wa.me/919875073788" class="room-cta">Enquire</a>
        </div>
      </div>
    </div>

    <div class="room-card" style="border-color:rgba(201,168,76,0.3);">
      <div class="room-img-wrap">
        <img class="room-img" src="https://meritashotels.com/wp-content/uploads/2023/03/337629365.jpg" alt="Premium Room with Jacuzzi and Terrace">
      </div>
      <div class="room-body">
        <div class="room-name">Premium Room with Jacuzzi &amp; Terrace</div>
        <p class="room-desc">The pinnacle of luxury - king bed, sofa bed, private jacuzzi and a personal terrace.</p>
        <div class="amenities">
          <span class="amenity">Up to 4 Guests</span>
          <span class="amenity">King Bed</span>
          <span class="amenity">Sofa Bed</span>
          <span class="amenity">AC</span>
          <span class="amenity">LED TV</span>
          <span class="amenity">Free WiFi</span>
          <span class="amenity">Jacuzzi</span>
          <span class="amenity">Minibar</span>
          <span class="amenity">Kettle</span>
        </div>
        <div class="room-footer">
          <a href="https://wa.me/919875073788" class="room-cta">Enquire</a>
        </div>
      </div>
    </div>

  </div>
</div>


<!-- BOTTOM CTA -->
<div class="bottom-section">
  <div class="bottom-card">
    <span class="bc-tag">Book Your Staycation</span>
    <h3>Ready for Your <em>Perfect Escape?</em></h3>
    <p>WhatsApp us with your dates and preferences - we'll get you the best rates on all Meritas properties instantly.</p>
    <div class="or-row"><span>CONTACT US</span></div>
    <a href="https://wa.me/919875073788" target="_blank" class="wa-btn">
      <svg viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
      WhatsApp Us
    </a>
  </div>
</div>

@endsection

@push('scripts')
<script>
  let heroIdx = 0;
  const heroSlides = document.querySelectorAll('.hero-slide');
  const heroDots   = document.querySelectorAll('.hero-dot');
  function goToHeroSlide(n) {
    heroSlides[heroIdx].classList.remove('active'); heroDots[heroIdx].classList.remove('active');
    heroIdx = (n + heroSlides.length) % heroSlides.length;
    heroSlides[heroIdx].classList.add('active'); heroDots[heroIdx].classList.add('active');
  }
  function heroSlide(dir) { goToHeroSlide(heroIdx + dir); }
  setInterval(() => heroSlide(1), 4500);
</script>
@endpush
