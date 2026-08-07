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
  <h1 class="page-title">Terms & Conditions</h1>
</div>

<div class="content-section">
  <h2>1. Booking Confirmation</h2>
  <ul>
    <li>A booking is confirmed only after receipt of the required advance payment and written confirmation from TYT.</li>
    <li>Bookings are subject to availability of hotels, flights, transport, and other services.</li>
  </ul>

  <h2>2. Payment Policy</h2>
  <ul>
    <li>A specified advance payment is required to confirm the booking.</li>
    <li>The remaining balance must be paid before the due date communicated by TYT.</li>
    <li>Failure to make timely payments may result in automatic cancellation.</li>
  </ul>

  <h2>3. Pricing</h2>
  <ul>
    <li>Prices are subject to availability and may change without prior notice until the booking is confirmed.</li>
    <li>Government taxes, fuel surcharges, airline or hotel price revisions may lead to changes in the final package cost.</li>
  </ul>

  <h2>4. Cancellation & Refund Policy</h2>
  <ul>
    <li>Cancellation charges will apply as per the cancellation policy shared at the time of booking.</li>
    <li>Flight tickets, visa fees, insurance, and certain hotel bookings may be non-refundable.</li>
    <li>Refunds, if applicable, will be processed within 7–21 working days after receiving refunds from suppliers.</li>
  </ul>

  <h2>5. Changes & Amendments</h2>
  <ul>
    <li>Any modification requested after booking confirmation is subject to availability.</li>
    <li>Additional charges may apply for changes in dates, hotels, flights, or other services.</li>
  </ul>

  <h2>6. Travel Documents</h2>
  <ul>
    <li>Customers are responsible for ensuring they possess valid passports, visas, permits, identification documents, and any other required travel documents.</li>
    <li>TYT shall not be liable for denied boarding or entry due to incomplete or invalid documentation.</li>
  </ul>

  <h2>7. Customer Responsibilities</h2>
  <p>Customers must:</p>
  <ul>
    <li>Follow local laws and regulations.</li>
    <li>Respect hotel and transport rules.</li>
    <li>Maintain proper behavior throughout the journey.</li>
    <li>Be responsible for personal belongings.</li>
  </ul>

  <h2>8. Force Majeure</h2>
  <p>TYT shall not be liable for delays, cancellations, or changes caused by events beyond its control, including but not limited to:</p>
  <ul>
    <li>Natural disasters</li>
    <li>Floods</li>
    <li>Earthquakes</li>
    <li>Political unrest</li>
    <li>Strikes</li>
    <li>Pandemics</li>
    <li>Government restrictions</li>
    <li>Airline operational issues</li>
  </ul>

  <h2>9. Hotel & Transportation</h2>
  <ul>
    <li>Hotel check-in/check-out timings are governed by the respective hotel.</li>
    <li>Vehicle type is provided as per the confirmed itinerary.</li>
    <li>Sightseeing is subject to weather, road conditions, and local authority permissions.</li>
  </ul>

  <h2>10. Travel Insurance</h2>
  <p>Travel insurance is strongly recommended. TYT is not responsible for losses arising due to medical emergencies, accidents, baggage loss, or trip interruptions.</p>

  <h2>11. Liability</h2>
  <p>TYT acts only as an intermediary between customers and third-party service providers such as airlines, hotels, transport operators, cruise lines, and activity providers. TYT shall not be liable for:</p>
  <ul>
    <li>Service deficiencies by third parties</li>
    <li>Flight delays or cancellations</li>
    <li>Hotel overbooking</li>
    <li>Loss of baggage</li>
    <li>Personal injury</li>
    <li>Property damage</li>
    <li>Missed connections</li>
    <li>Events beyond its reasonable control</li>
  </ul>

  <h2>12. Refunds</h2>
  <p>Refunds, where applicable, are governed by the policies of airlines, hotels, transport providers, and other vendors. Processing timelines may vary.</p>

  <h2>13. Website Usage</h2>
  <ul>
    <li>Users agree not to misuse the website or attempt unauthorized access.</li>
    <li>All website content, logos, trademarks, and images are the intellectual property of TYT unless otherwise stated.</li>
  </ul>

  <h2>14. Privacy</h2>
  <p>Personal information collected through the website will be handled in accordance with TYT's Privacy Policy and applicable data protection laws.</p>

  <h2>15. Governing Law</h2>
  <p>These Terms & Conditions shall be governed by the laws of India. Any disputes shall be subject to the exclusive jurisdiction of the courts in Surat, Gujarat.</p>

  <h2>16. Contact</h2>
  <p>For any queries regarding bookings or these Terms & Conditions, contact:</p>
  <p>
    <strong>TYT (Take Your Trip)</strong><br>
    Email: takeyourtrip7@gmail.com<br>
    Phone: +91 9875073788
  </p>

  <h3>Important Note</h3>
  <ul>
    <li>Itineraries are tentative and may change due to operational reasons.</li>
    <li>Tour managers and guides reserve the right to modify sightseeing schedules for safety or operational requirements.</li>
    <li>No refund shall be provided for unused services after the tour has commenced.</li>
    <li>Customers are advised to carefully read all inclusions, exclusions, and cancellation policies before confirming their booking.</li>
  </ul>
</div>
@endsection
