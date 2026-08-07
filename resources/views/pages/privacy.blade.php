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
  <div class="page-subtitle">TYT Luxe</div>
  <h1 class="page-title">Privacy Policy</h1>
</div>

<div class="content-section">
  <p><strong>Effective Date:</strong> August 06, 2026</p>
  
  <p>Welcome to TYT Luxe ("we", "our", "us"). Your privacy is important to us. This Privacy Policy explains how we collect, use, disclose, and protect your personal information when you visit tytluxe.in or use any of our travel-related services.</p>
  <p>By accessing or using our website, you agree to the practices described in this Privacy Policy.</p>

  <h2>1. Information We Collect</h2>
  <p>We may collect the following types of information:</p>

  <h3>Personal Information</h3>
  <ul>
    <li>Full Name</li>
    <li>Email Address</li>
    <li>Mobile Number</li>
    <li>Residential Address</li>
    <li>Date of Birth</li>
    <li>Nationality</li>
    <li>Passport Details (where required)</li>
    <li>Government-issued Identification (where required)</li>
    <li>Emergency Contact Details</li>
  </ul>

  <h3>Booking Information</h3>
  <p>When you book a trip or enquire about our services, we may collect:</p>
  <ul>
    <li>Destination preferences</li>
    <li>Travel dates</li>
    <li>Passenger details</li>
    <li>Hotel preferences</li>
    <li>Flight preferences</li>
    <li>Meal preferences</li>
    <li>Special requests</li>
    <li>Visa-related information (if applicable)</li>
  </ul>

  <h3>Payment Information</h3>
  <p>Payments are processed through secure third-party payment gateways.</p>
  <p>We do not store your debit card, credit card, CVV, or net banking credentials on our servers.</p>

  <h3>Technical Information</h3>
  <p>When you visit our website, we may automatically collect:</p>
  <ul>
    <li>IP Address</li>
    <li>Browser Type</li>
    <li>Device Information</li>
    <li>Operating System</li>
    <li>Pages Visited</li>
    <li>Referral Source</li>
    <li>Date and Time of Visit</li>
    <li>Cookies and Analytics Data</li>
  </ul>

  <h2>2. How We Use Your Information</h2>
  <p>We use your information to:</p>
  <ul>
    <li>Process travel bookings</li>
    <li>Confirm reservations</li>
    <li>Arrange flights, hotels, transfers and sightseeing</li>
    <li>Respond to enquiries</li>
    <li>Provide customer support</li>
    <li>Send booking confirmations</li>
    <li>Send travel updates</li>
    <li>Process payments</li>
    <li>Prevent fraud</li>
    <li>Improve our website</li>
    <li>Personalize your experience</li>
    <li>Comply with legal obligations</li>
    <li>Send promotional offers (only where permitted)</li>
  </ul>

  <h2>3. Sharing of Information</h2>
  <p>We may share your information only when necessary with:</p>
  <ul>
    <li>Airlines</li>
    <li>Hotels</li>
    <li>Resorts</li>
    <li>Cruise Operators</li>
    <li>Transport Providers</li>
    <li>Visa Processing Agencies</li>
    <li>Payment Gateway Providers</li>
    <li>Government Authorities (when legally required)</li>
    <li>Travel Insurance Providers</li>
    <li>Technology Service Providers</li>
  </ul>
  <p>We never sell your personal information to third parties.</p>

  <h2>4. Cookies</h2>
  <p>Our website uses cookies to:</p>
  <ul>
    <li>Remember your preferences</li>
    <li>Improve website performance</li>
    <li>Analyse visitor behaviour</li>
    <li>Enhance user experience</li>
  </ul>
  <p>You may disable cookies through your browser settings, although some website features may not function properly.</p>

  <h2>5. Marketing Communications</h2>
  <p>With your consent, we may send:</p>
  <ul>
    <li>Holiday offers</li>
    <li>Promotional campaigns</li>
    <li>Travel inspiration</li>
    <li>Festival offers</li>
    <li>Package launches</li>
    <li>Newsletter updates</li>
  </ul>
  <p>You may unsubscribe at any time using the unsubscribe link in our emails or by contacting us.</p>

  <h2>6. Data Security</h2>
  <p>We take appropriate technical and organizational measures to protect your information against:</p>
  <ul>
    <li>Unauthorized access</li>
    <li>Data loss</li>
    <li>Misuse</li>
    <li>Alteration</li>
    <li>Disclosure</li>
  </ul>
  <p>While we strive to use commercially acceptable means to protect your information, no internet transmission or electronic storage is completely secure.</p>

  <h2>7. Data Retention</h2>
  <p>We retain your personal information only for as long as necessary to:</p>
  <ul>
    <li>Provide our services</li>
    <li>Meet legal obligations</li>
    <li>Resolve disputes</li>
    <li>Enforce agreements</li>
    <li>Maintain accounting and tax records</li>
  </ul>

  <h2>8. International Transfers</h2>
  <p>If your travel booking involves international destinations, your information may be shared with overseas hotels, airlines, destination management companies, visa authorities, or other travel partners located outside India.</p>
  <p>By making an international booking, you consent to such transfers where necessary to fulfil your travel arrangements.</p>

  <h2>9. Third-Party Links</h2>
  <p>Our website may contain links to third-party websites such as:</p>
  <ul>
    <li>Airline websites</li>
    <li>Hotel websites</li>
    <li>Payment gateways</li>
    <li>Tourism boards</li>
    <li>Visa service providers</li>
  </ul>
  <p>We are not responsible for the privacy practices or content of these external websites.</p>

  <h2>10. Children's Privacy</h2>
  <p>Our services are not intended for children under the age of 18 without the involvement of a parent or legal guardian.</p>
  <p>If we become aware that we have collected personal information from a child without appropriate consent, we will take reasonable steps to delete such information.</p>

  <h2>11. Your Rights</h2>
  <p>Subject to applicable law, you may have the right to:</p>
  <ul>
    <li>Access your personal information</li>
    <li>Correct inaccurate information</li>
    <li>Request deletion of your data</li>
    <li>Withdraw consent (where applicable)</li>
    <li>Restrict certain processing activities</li>
    <li>Request information regarding how your data is used</li>
  </ul>
  <p>To exercise these rights, please contact us using the details below.</p>

  <h2>12. Changes to this Privacy Policy</h2>
  <p>We may update this Privacy Policy from time to time.</p>
  <p>Any changes will be posted on this page with the updated "Effective Date." Continued use of the website after such changes constitutes your acceptance of the revised policy.</p>

  <h2>13. Contact Us</h2>
  <p>If you have any questions regarding this Privacy Policy or the handling of your personal information, please contact us:</p>
  <p>
    <strong>TYT Luxe</strong><br>
    Website: tytluxe.in<br>
    Email: takeyourtrip7@gmail.com<br>
    Phone: +91 98750 73788
  </p>

  <h2>14. Compliance with Applicable Laws</h2>
  <p>TYT Luxe is committed to handling personal information in accordance with applicable Indian laws, including the provisions of the Digital Personal Data Protection Act, 2023 (DPDP Act) and other applicable regulations governing the collection, processing, storage, and protection of personal data.</p>
</div>
@endsection
