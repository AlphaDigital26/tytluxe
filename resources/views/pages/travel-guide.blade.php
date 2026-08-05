@extends('layouts.frontend')

@push('styles')
<style>
*, *::before, *::after { box-sizing: border-box; }
:root {
  --cream: #f5f0e8;
  --dark: #0f0c08;
  --dark-mid: #1a1510;
  --gold: #b8935a;
  --text-dark: #1a1108;
  --text-muted: #777;
}
body {
  font-family: 'Poppins', sans-serif;
  background: var(--cream);
  color: var(--text-dark);
}
.page-header {
  background: var(--dark-mid);
  padding: 120px 24px 80px;
  text-align: center;
}
.page-title {
  font-family: 'Playfair Display', serif;
  font-size: clamp(2.5rem, 5vw, 4rem);
  font-weight: 700;
  color: #fff;
  margin-bottom: 16px;
}
.page-subtitle {
  font-size: 1rem;
  color: var(--gold);
  letter-spacing: .15em;
  text-transform: uppercase;
}
.content-section {
  max-width: 900px;
  margin: 0 auto;
  padding: 80px 24px;
  line-height: 1.8;
  color: var(--text-muted);
}
.content-section h2, .content-section h3 {
  font-family: 'Playfair Display', serif;
  color: var(--text-dark);
  margin-top: 40px;
  margin-bottom: 16px;
}
.content-section h2 { font-size: 2rem; }
.content-section h3 { font-size: 1.5rem; }
.content-section p { margin-bottom: 24px; }
.content-section ul { margin-bottom: 24px; padding-left: 20px; }
.content-section li { margin-bottom: 8px; }

.guide-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 32px;
  margin-top: 40px;
}
.guide-card {
  background: #fff;
  padding: 32px;
  border: 1px solid rgba(184,147,90,0.2);
  transition: transform 0.3s;
}
.guide-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 10px 30px rgba(0,0,0,0.05);
}
.guide-icon {
  font-size: 2.5rem;
  margin-bottom: 16px;
  display: block;
}
.guide-card h3 {
  margin-top: 0;
  font-size: 1.25rem;
}
</style>
@endpush

@section('content')
<div class="page-header">
  <div class="page-subtitle">Take Your Trip</div>
  <h1 class="page-title">Travel Guide</h1>
</div>

<div class="content-section">
  <h2>Your Ultimate Companion to Exploring the World</h2>
  <p>Whether you're planning a serene staycation, an exotic honeymoon, or an adventurous cruise, our travel guide is here to help you prepare. Check out our expert tips and essential information below.</p>
  
  <div class="guide-grid">
    <div class="guide-card">
      <span class="guide-icon">🧳</span>
      <h3>Packing Essentials</h3>
      <p>Learn what to pack for different climates and types of vacations. From beach holidays to mountain retreats, we have a checklist for you.</p>
    </div>
    <div class="guide-card">
      <span class="guide-icon">🛂</span>
      <h3>Visa & Documents</h3>
      <p>Ensure a hassle-free journey with our tips on passport validity, visa applications, and essential travel insurance.</p>
    </div>
    <div class="guide-card">
      <span class="guide-icon">🍲</span>
      <h3>Dietary Preferences</h3>
      <p>Find out how we cater to specific dietary requirements like Jain and pure vegetarian options across our curated global destinations.</p>
    </div>
    <div class="guide-card">
      <span class="guide-icon">📱</span>
      <h3>Travel Tech</h3>
      <p>Stay connected and organized with recommendations for travel apps, international SIM cards, and tech gadgets you shouldn't travel without.</p>
    </div>
  </div>
</div>
@endsection
