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
.help-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 32px;
  margin-top: 40px;
}
.help-card {
  background: #fff;
  padding: 40px 32px;
  border: 1px solid rgba(184,147,90,0.2);
  text-align: center;
  transition: transform 0.3s, box-shadow 0.3s;
}
.help-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 10px 30px rgba(0,0,0,0.05);
}
.help-icon {
  font-size: 2.5rem;
  margin-bottom: 16px;
  display: block;
  color: var(--gold);
}
.help-card h3 {
  font-family: 'Playfair Display', serif;
  font-size: 1.5rem;
  color: var(--text-dark);
  margin-top: 0;
  margin-bottom: 12px;
}
.help-card p {
  font-size: 0.9rem;
  margin-bottom: 24px;
}
.help-btn {
  display: inline-block;
  padding: 12px 24px;
  background: var(--dark);
  color: #fff;
  text-decoration: none;
  font-size: 0.8rem;
  letter-spacing: 0.1em;
  text-transform: uppercase;
  transition: background 0.3s;
}
.help-btn:hover {
  background: #000;
  color: #fff;
}
.help-btn-outline {
  background: transparent;
  color: var(--dark);
  border: 1px solid var(--dark);
}
.help-btn-outline:hover {
  background: var(--dark);
  color: #fff;
}
</style>
@endpush

@section('content')
<div class="page-header">
  <div class="page-subtitle">Take Your Trip</div>
  <h1 class="page-title">Help Center</h1>
</div>

<div class="content-section">
  <div style="text-align: center; margin-bottom: 48px;">
    <h2 style="font-family: 'Playfair Display', serif; font-size: 2rem; color: var(--text-dark); margin-bottom: 16px;">How can we assist you today?</h2>
    <p>Whether you need help with an existing booking, want to inquire about a new package, or just have some general questions, our team is here for you.</p>
  </div>
  
  <div class="help-grid">
    <div class="help-card">
      <span class="help-icon"><i class="fa-solid fa-phone"></i></span>
      <h3>Call Us</h3>
      <p>Speak directly with our travel experts for immediate assistance.</p>
      <a href="tel:+919875073788" class="help-btn">Call +91 98750 73788</a>
    </div>
    <div class="help-card">
      <span class="help-icon"><i class="fa-brands fa-whatsapp"></i></span>
      <h3>WhatsApp Support</h3>
      <p>Send us a message and we'll reply within 2 hours.</p>
      <a href="https://wa.me/919875073788" class="help-btn help-btn-outline">Message Us</a>
    </div>
    <div class="help-card">
      <span class="help-icon"><i class="fa-regular fa-envelope"></i></span>
      <h3>Email Us</h3>
      <p>Prefer writing? Drop us an email and we will get back to you shortly.</p>
      <a href="{{ url('/contact') }}" class="help-btn">Contact Form</a>
    </div>
  </div>
</div>
@endsection
