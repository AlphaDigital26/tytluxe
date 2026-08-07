@extends('layouts.frontend')

@push('styles')
<style>
  *, *::before, *::after { box-sizing: border-box; }

  :root {
    --black: #0a0a0a;
    --dark: #111111;
    --dark-mid: #1a1a1a;
    --dark-card: #161616;
    --border: rgba(255,255,255,0.08);
    --gold: #C9A84C;
    --gold-light: #E2C97E;
    --white: #ffffff;
    --white-70: rgba(255,255,255,0.7);
    --white-40: rgba(255,255,255,0.4);
    --white-15: rgba(255,255,255,0.07);
  }

  body {
    font-family: 'Poppins', sans-serif;
    background: var(--black);
    color: var(--white);
    min-height: 100vh;
  }


  /* MAIN GRID */
  .contact-main {
    max-width: 1100px;
    margin: 0 auto;
    padding: 3.5rem 2rem 6rem;
    display: grid;
    grid-template-columns: 1fr 1.5fr;
    gap: 2.5rem;
    align-items: start;
  }

  /* INFO CARDS */
  .info-stack { display: flex; flex-direction: column; gap: 1rem; }

  .info-card {
    background: var(--dark-card);
    border: 1px solid var(--border);
    border-radius: 8px;
    padding: 1.25rem 1.5rem;
    display: flex;
    align-items: flex-start;
    gap: 1rem;
    transition: border-color 0.2s;
  }
  .info-card:hover { border-color: rgba(201,168,76,0.3); }

  .icon-circle {
    width: 42px;
    height: 42px;
    border-radius: 50%;
    background: var(--white-15);
    border: 1px solid var(--border);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
  }
  .icon-circle svg {
    width: 18px; height: 18px;
    stroke: var(--gold);
    fill: none;
    stroke-width: 1.5;
    stroke-linecap: round;
    stroke-linejoin: round;
  }
  .info-label {
    font-size: 9px;
    font-weight: 600;
    letter-spacing: 0.2em;
    text-transform: uppercase;
    color: var(--gold);
    margin-bottom: 4px;
  }
  .info-value {
    font-size: 14px;
    font-weight: 400;
    color: var(--white);
    line-height: 1.5;
  }
  .info-value a { color: var(--white); text-decoration: none; }
  .info-value a:hover { color: var(--gold); }
  .info-note { font-size: 11px; color: var(--white-40); margin-top: 2px; }

  /* WHATSAPP */
  .wa-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    background: #25D366;
    color: #fff;
    text-decoration: none;
    border-radius: 8px;
    padding: 0.9rem 1.5rem;
    font-size: 13px;
    font-weight: 600;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    transition: opacity 0.2s, transform 0.15s;
  }
  .wa-btn:hover { opacity: 0.9; transform: translateY(-1px); color: #fff; }
  .wa-btn svg { width: 20px; height: 20px; fill: #fff; flex-shrink: 0; }

  /* MAP CONTAINER */
  .map-container {
    border-radius: 8px;
    border: 1px solid var(--border);
    background: var(--dark-card);
    height: 190px;
    overflow: hidden;
  }
  .map-container iframe {
    width: 100%;
    height: 100%;
    border: 0;
  }

  /* DIVIDER */
  .row-divider {
    display: flex;
    align-items: center;
    gap: 10px;
    margin: 0.25rem 0;
  }
  .row-divider::before, .row-divider::after { content: ''; flex: 1; height: 1px; background: var(--border); }
  .row-divider span { font-size: 9px; color: var(--white-40); letter-spacing: 0.15em; text-transform: uppercase; white-space: nowrap; }

  /* FORM CARD */
  .form-card {
    background: var(--dark-card);
    border: 1px solid var(--border);
    border-radius: 8px;
    padding: 2.5rem;
  }
  .form-heading {
    font-family: 'Playfair Display', serif;
    font-size: 1.8rem;
    font-weight: 400;
    color: var(--white);
    margin-bottom: 0.3rem;
  }
  .form-heading em { font-style: italic; color: var(--gold-light); }
  .form-sub {
    font-size: 12px;
    color: var(--white-40);
    font-weight: 300;
    margin-bottom: 2rem;
    line-height: 1.6;
  }

  .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
  .fg { display: flex; flex-direction: column; gap: 6px; }
  .fg.full { grid-column: 1 / -1; }
  .fg label { font-size: 11px; font-weight: 500; color: var(--white-70); letter-spacing: 0.05em; }
  .fg input, .fg select, .fg textarea {
    background: var(--dark-mid);
    border: 1px solid var(--border);
    border-radius: 6px;
    padding: 0.7rem 1rem;
    color: var(--white);
    font-family: 'Poppins', sans-serif;
    font-size: 13px;
    outline: none;
    transition: border-color 0.2s;
  }
  .fg input:focus, .fg select:focus, .fg textarea:focus { border-color: var(--gold); }
  .fg select { appearance: none; cursor: pointer; }
  .fg select option { background: var(--dark-mid); }
  .fg textarea { resize: vertical; min-height: 110px; }

  .submit-btn {
    width: 100%;
    background: var(--gold);
    color: var(--black);
    border: none;
    border-radius: 6px;
    padding: 0.85rem;
    font-family: 'Poppins', sans-serif;
    font-size: 13px;
    font-weight: 600;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    cursor: pointer;
    transition: opacity 0.2s, transform 0.15s;
  }
  .submit-btn:hover { opacity: 0.88; transform: translateY(-1px); }
  .submit-btn:disabled { opacity: 0.5; cursor: not-allowed; transform: none; }

  .success-msg {
    display: none;
    background: rgba(201,168,76,0.08);
    border: 1px solid rgba(201,168,76,0.3);
    border-radius: 8px;
    padding: 1.5rem;
    color: var(--gold-light);
    font-size: 14px;
    font-weight: 500;
    text-align: center;
    margin-top: 1rem;
  }

  @media (max-width: 768px) {
    .contact-main { grid-template-columns: 1fr; padding: 2rem 1rem 4rem; }
    .form-grid { grid-template-columns: 1fr; }
  }
</style>
@endpush

@section('content')

<!-- HERO -->
<x-hero-carousel 
  :slides="[
    'https://images.unsplash.com/photo-1423666639041-f56000c27a9a?w=1600&q=80',
    'https://images.unsplash.com/photo-1516738901171-8eb4fc13bd20?w=1600&q=80',
    'https://images.unsplash.com/photo-1510414842594-a61c69b5ae57?w=1600&q=80'
  ]"
  eyebrow="Get in Touch"
  title="We're Here to Help You <em>Travel Better</em>"
  subtitle="Reach out and our travel experts will get back to you within 2 hours"
  ctaText=""
  ctaLink=""
/>

<!-- MAIN -->
<div class="contact-main">

  <!-- LEFT -->
  <div class="info-stack">

    <div class="info-card">
      <div class="icon-circle">
        <svg viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 9.8a19.79 19.79 0 01-3.07-8.68A2 2 0 012 .82h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L6.09 8.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0122 16.92z"/></svg>
      </div>
      <div>
        <div class="info-label">Call / WhatsApp</div>
        <div class="info-value"><a href="tel:9875073788">+91 98750 73788</a></div>
        <div class="info-note">Mon – Sat &nbsp;|&nbsp; 10:00 AM – 7:00 PM IST</div>
      </div>
    </div>

    <div class="info-card">
      <div class="icon-circle">
        <svg viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
      </div>
      <div>
        <div class="info-label">Email</div>
        <div class="info-value"><a href="mailto:takeyourtrip7@gmail.com">takeyourtrip7@gmail.com</a></div>
        <div class="info-note">Write to us for hotel, cruise and staycation enquiries</div>
      </div>
    </div>

    <div class="info-card">
      <div class="icon-circle">
        <svg viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
      </div>
      <div>
        <div class="info-label">Office Address</div>
        <div class="info-value">Surana Supremus, 4th Floor, Cabin No - 9, Near Safal Square, Vesu, Surat 394518</div>
        <div class="info-note">Visit our office for travel planning assistance</div>
      </div>
    </div>

    <div class="row-divider"><span>Quick Connect</span></div>

    <a href="https://wa.me/919875073788" target="_blank" class="wa-btn">
      <svg viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
      Chat with Us on WhatsApp
    </a>

    <div class="row-divider"><span>Our Location</span></div>

    <div class="map-container">
      <iframe src="https://maps.google.com/maps?q=Surana%20Supremus,%20Near%20Safal%20Square,%20Vesu,%20Surat%20394518&t=&z=15&ie=UTF8&iwloc=&output=embed" allowfullscreen="" loading="lazy"></iframe>
    </div>

  </div>

  <!-- RIGHT: FORM -->
  <div class="form-card">
    <h2 class="form-heading">Send Us a <em>Message</em></h2>
    <p class="form-sub">Fill in the details and our travel expert will WhatsApp you within 2 hours with the best options.</p>

    <form id="contactForm" onsubmit="handleSubmit(event)">
      <div class="form-grid">

        <div class="fg">
          <label for="fname">Full Name *</label>
          <input type="text" id="fname" placeholder="Your full name" required>
        </div>

        <div class="fg">
          <label for="phone">Phone / WhatsApp *</label>
          <input type="tel" id="phone" placeholder="+91 XXXXX XXXXX" required>
        </div>

        <div class="fg full">
          <label for="email">Email Address</label>
          <input type="email" id="email" placeholder="your@email.com">
        </div>

        <div class="fg full">
          <label for="interest">I'm Interested In</label>
          <select id="interest">
            <option value="">Select a service...</option>
            <option>Hotel Booking</option>
            <option>Cruise Package</option>
            <option>Flight Booking</option>
            <option>Honeymoon Package</option>
            <option>Family Holiday</option>
            <option>International Trip</option>
            <option>Custom Package</option>
            <option>Other</option>
          </select>
        </div>

        <div class="fg full">
          <label for="message">Your Message *</label>
          <textarea id="message" placeholder="Tell us about your travel plans, destination, dates, budget, or any questions..." required></textarea>
        </div>

        <div class="fg full">
          <button type="submit" class="submit-btn">Send Enquiry →</button>
        </div>

      </div>
    </form>

    <div class="success-msg" id="successMsg">
      ✓ Thank you! Our travel expert will WhatsApp you shortly.
    </div>
  </div>

</div>

@endsection

@push('scripts')
<script>
  function handleSubmit(e) {
    e.preventDefault();
    const btn = document.querySelector('.submit-btn');
    btn.textContent = 'Sending...';
    btn.disabled = true;
    setTimeout(() => {
      document.getElementById('contactForm').style.display = 'none';
      document.getElementById('successMsg').style.display = 'block';
    }, 900);
  }
</script>
@endpush
