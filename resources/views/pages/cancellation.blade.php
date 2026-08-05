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
</style>
@endpush

@section('content')
<div class="page-header">
  <div class="page-subtitle">Take Your Trip</div>
  <h1 class="page-title">Cancellation Policy</h1>
</div>

<div class="content-section">
  <h2>1. General Cancellation Rules</h2>
  <p>Cancellations must be requested via email or through our customer support hotline. The cancellation date will be considered as the date we receive your formal request.</p>
  
  <h2>2. Hotel & Accommodation</h2>
  <p>Each hotel has its own cancellation policy which will be provided to you at the time of booking. Non-refundable rates cannot be cancelled or amended once confirmed.</p>
  
  <h2>3. Flights</h2>
  <p>Airline cancellation charges apply as per the respective airline's policy. Take Your Trip may charge a nominal service fee for processing the flight cancellation.</p>
  
  <h2>4. Packages & Cruises</h2>
  <ul>
    <li>Cancellations made 30 days or more before departure: 10% of total booking amount as cancellation fee.</li>
    <li>Cancellations made 15-29 days before departure: 50% of total booking amount as cancellation fee.</li>
    <li>Cancellations made less than 15 days before departure: 100% of total booking amount as cancellation fee (No refund).</li>
  </ul>
  <p><em>* Note: Specific cruise lines or special package deals may have stricter policies which supersede these general rules.</em></p>

  <h2>5. Refund Process</h2>
  <p>Eligible refunds will be processed back to the original method of payment within 7-14 business days from the date of confirmation of cancellation.</p>
</div>
@endsection
