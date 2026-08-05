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
  <h1 class="page-title">Privacy Policy</h1>
</div>

<div class="content-section">
  <h2>1. Information We Collect</h2>
  <p>We may collect personal information such as your name, email address, phone number, and payment details when you make a booking or enquiry through our platform.</p>
  
  <h2>2. How We Use Your Information</h2>
  <p>The information we collect is used to process your bookings, communicate with you regarding your travel plans, and provide customer support. We may also use your email to send promotional offers, which you can opt out of at any time.</p>
  
  <h2>3. Data Protection</h2>
  <p>We implement strict security measures to protect your personal data against unauthorized access, alteration, or disclosure. Payment information is securely processed through our trusted payment gateways.</p>
  
  <h2>4. Sharing of Information</h2>
  <p>Your data may be shared with necessary third parties, such as airlines, hotels, and tour operators, strictly for the purpose of fulfilling your booking. We do not sell your personal information to third parties.</p>

  <h2>5. Cookies</h2>
  <p>Our website uses cookies to enhance user experience, analyze site traffic, and personalize content. You can manage your cookie preferences through your browser settings.</p>

  <h2>6. Contact Us</h2>
  <p>If you have any questions or concerns about our Privacy Policy or the handling of your personal data, please contact us at our provided support email or phone number.</p>
</div>
@endsection
