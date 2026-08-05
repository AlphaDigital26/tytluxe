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
  <h1 class="page-title">Terms & Conditions</h1>
</div>

<div class="content-section">
  <h2>1. Introduction</h2>
  <p>Welcome to Take Your Trip. These terms and conditions outline the rules and regulations for the use of our website and services.</p>
  
  <h2>2. Booking & Payments</h2>
  <p>All bookings are subject to availability and confirmation. A deposit or full payment may be required at the time of booking depending on the service selected. Prices are subject to change without prior notice.</p>
  
  <h2>3. Cancellations & Refunds</h2>
  <p>Please refer to our Cancellation Policy for detailed information regarding cancellations and refunds. Policies vary depending on the specific hotel, cruise, or package booked.</p>
  
  <h2>4. Travel Documents</h2>
  <p>It is the traveller's responsibility to ensure they have valid passports, visas, and necessary health documents for their trip. Take Your Trip is not liable for any issues arising from incorrect or missing travel documents.</p>
  
  <h2>5. Limitation of Liability</h2>
  <p>Take Your Trip acts as an agent for airlines, hotels, tour operators, and other travel service providers. We are not liable for any injury, loss, delay, or damage arising from the services provided by these third parties.</p>

  <h2>6. Changes to Terms</h2>
  <p>We reserve the right to amend these terms and conditions at any time. Any changes will be posted on this page, and your continued use of our services constitutes acceptance of those changes.</p>
</div>
@endsection
