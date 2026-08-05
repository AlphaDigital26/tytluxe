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
.faq-item {
  margin-bottom: 24px;
  border-bottom: 1px solid rgba(184,147,90,0.2);
  padding-bottom: 24px;
}
.faq-question {
  font-family: 'Playfair Display', serif;
  font-size: 1.25rem;
  font-weight: 700;
  color: var(--text-dark);
  margin-bottom: 12px;
}
.faq-answer {
  color: var(--text-muted);
}
</style>
@endpush

@section('content')
<div class="page-header">
  <div class="page-subtitle">Take Your Trip</div>
  <h1 class="page-title">Frequently Asked Questions</h1>
</div>

<div class="content-section">
  
  <div class="faq-item">
    <div class="faq-question">How do I make a booking?</div>
    <div class="faq-answer">You can easily make a booking by exploring our curated packages and submitting an enquiry form, or by contacting our travel experts directly via phone or WhatsApp. We typically respond within 2 hours.</div>
  </div>

  <div class="faq-item">
    <div class="faq-question">Do you offer customized travel itineraries?</div>
    <div class="faq-answer">Yes, we specialize in creating bespoke travel itineraries tailored to your specific preferences, whether you are looking for a romantic honeymoon, a family vacation, or an adventure trip.</div>
  </div>

  <div class="faq-item">
    <div class="faq-question">Are vegetarian and Jain food options available on international trips?</div>
    <div class="faq-answer">Absolutely. We understand the dietary needs of Indian travellers and work closely with hotels and cruise lines to ensure vegetarian and Jain meal options are available for you.</div>
  </div>

  <div class="faq-item">
    <div class="faq-question">What is your cancellation policy?</div>
    <div class="faq-answer">Our cancellation policy varies depending on the specific booking, hotel, and airline involved. Please review our Cancellation Policy page or speak to our travel experts for detailed information regarding your specific package.</div>
  </div>

  <div class="faq-item">
    <div class="faq-question">Can you assist with visa applications?</div>
    <div class="faq-answer">Yes, we provide guidance and support for visa applications for most international destinations to ensure a smooth and hassle-free travel experience.</div>
  </div>

</div>
@endsection
