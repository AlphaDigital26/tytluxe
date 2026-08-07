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
.policy-table {
  width: 100%;
  border-collapse: collapse;
  margin-bottom: 24px;
}
.policy-table th, .policy-table td {
  border: 1px solid var(--gold);
  padding: 12px;
  text-align: left;
}
.policy-table th {
  background-color: var(--dark-mid);
  color: #fff;
}
</style>
@endpush

@section('content')
<div class="page-header">
  <div class="page-subtitle">TYT Luxe</div>
  <h1 class="page-title">Cancellation & Refund Policy</h1>
</div>

<div class="content-section">
  <p><strong>Effective Date:</strong> August 2026</p>
  
  <p>Welcome to TYT Luxe. We understand that travel plans may change unexpectedly. This Cancellation & Refund Policy explains the terms applicable to cancellations, amendments, and refunds for bookings made through TYTLuxe.in.</p>
  <p>By confirming a booking with TYT Luxe, you agree to the following terms.</p>

  <h2>1. Booking Confirmation</h2>
  <p>A booking is considered confirmed only after:</p>
  <ul>
    <li>Full payment or the applicable advance payment has been received.</li>
    <li>You receive a booking confirmation from TYT Luxe via email or WhatsApp.</li>
    <li>All required documents have been submitted.</li>
  </ul>

  <h2>2. Cancellation by the Customer</h2>
  <p>Cancellation requests must be made in writing via email or WhatsApp from the registered contact details used during booking.</p>
  <p>The cancellation date shall be considered the date on which TYT Luxe receives the written cancellation request.</p>
  <p>Cancellation charges are as follows:</p>
  
  <table class="policy-table">
    <thead>
      <tr>
        <th>Cancellation Before Departure</th>
        <th>Cancellation Charges</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>More than 30 days</td>
        <td>10% of total booking amount or actual supplier charges (whichever is higher)</td>
      </tr>
      <tr>
        <td>21–30 days</td>
        <td>25% of total booking amount</td>
      </tr>
      <tr>
        <td>15–20 days</td>
        <td>50% of total booking amount</td>
      </tr>
      <tr>
        <td>8–14 days</td>
        <td>75% of total booking amount</td>
      </tr>
      <tr>
        <td>0–7 days / No Show</td>
        <td>100% of total booking amount</td>
      </tr>
    </tbody>
  </table>
  
  <p>Please note that airlines, hotels, cruise operators, visa authorities, and other suppliers may impose additional cancellation charges, which will also be payable by the customer.</p>

  <h2>3. Flight Ticket Cancellation</h2>
  <ul>
    <li>Airline tickets are governed entirely by the respective airline's cancellation policy.</li>
    <li>Any airline cancellation charges, convenience fees, service fees, GST, or non-refundable taxes shall be deducted before processing any eligible refund.</li>
  </ul>

  <h2>4. Hotel Cancellation</h2>
  <ul>
    <li>Hotels have individual cancellation policies.</li>
    <li>Refunds for hotel bookings will depend upon the cancellation terms of the booked property.</li>
    <li>Certain promotional, festive, peak season, early bird, and non-refundable rates cannot be cancelled or refunded.</li>
  </ul>

  <h2>5. Visa Charges</h2>
  <p>Visa fees, appointment charges, documentation charges, insurance charges, courier charges, and embassy fees are strictly non-refundable once the application process has started, irrespective of visa approval or rejection.</p>

  <h2>6. Cruise Bookings</h2>
  <ul>
    <li>Cruise bookings are governed by the cancellation policy of the respective cruise operator.</li>
    <li>Additional penalties may apply depending upon the sailing date and cabin category.</li>
  </ul>

  <h2>7. Group Tours</h2>
  <p>For fixed departures and group tours:</p>
  <ul>
    <li>Booking deposits are generally non-refundable.</li>
    <li>Name changes may not be permitted.</li>
    <li>Cancellation charges may be higher due to supplier commitments.</li>
  </ul>
  <p>Special event departures, festivals, and holiday packages may have separate cancellation terms communicated at the time of booking.</p>

  <h2>8. Amendment / Rescheduling</h2>
  <p>Any request for:</p>
  <ul>
    <li>Date changes</li>
    <li>Destination changes</li>
    <li>Hotel changes</li>
    <li>Passenger name corrections</li>
    <li>Itinerary modifications</li>
  </ul>
  <p>shall be subject to availability and supplier approval.</p>
  <p>Additional charges and fare differences may apply.</p>

  <h2>9. Refund Processing</h2>
  <p>Where a refund is applicable:</p>
  <ul>
    <li>Refunds will be processed only after TYT Luxe receives the refund from the respective supplier.</li>
    <li>Refunds are generally processed within 7–15 working days after receiving the supplier refund.</li>
    <li>Refunds will be made through the original mode of payment wherever possible.</li>
  </ul>
  <p>Processing time by banks or payment gateways may vary.</p>

  <h2>10. Non-Refundable Services</h2>
  <p>The following are generally non-refundable unless specifically stated otherwise:</p>
  <ul>
    <li>Visa fees</li>
    <li>Travel insurance</li>
    <li>Convenience fees</li>
    <li>Processing charges</li>
    <li>Service charges</li>
    <li>GST</li>
    <li>Foreign exchange charges</li>
    <li>Any third-party handling fees</li>
  </ul>

  <h2>11. Cancellation by TYT Luxe</h2>
  <p>In rare circumstances, TYT Luxe may cancel a booking due to:</p>
  <ul>
    <li>Natural disasters</li>
    <li>Government restrictions</li>
    <li>Political unrest</li>
    <li>Pandemic-related restrictions</li>
    <li>Force majeure events</li>
    <li>Supplier operational issues</li>
  </ul>
  <p>In such cases, TYT Luxe will make reasonable efforts to offer:</p>
  <ul>
    <li>An alternative travel arrangement,</li>
    <li>A travel credit (where applicable), or</li>
    <li>A refund limited to the amount recoverable from suppliers.</li>
  </ul>
  <p>TYT Luxe shall not be liable for indirect losses such as loss of salary, business, or consequential expenses.</p>

  <h2>12. Force Majeure</h2>
  <p>TYT Luxe shall not be held responsible for cancellations, delays, losses, or additional expenses arising due to circumstances beyond its reasonable control, including but not limited to:</p>
  <ul>
    <li>Natural calamities</li>
    <li>Floods</li>
    <li>Earthquakes</li>
    <li>Landslides</li>
    <li>War</li>
    <li>Civil disturbances</li>
    <li>Government regulations</li>
    <li>Airline operational issues</li>
    <li>Epidemics or pandemics</li>
    <li>Weather conditions</li>
  </ul>

  <h2>13. No Show</h2>
  <p>Failure to arrive for any booked service without prior written cancellation shall be treated as a "No Show."</p>
  <p>No refund shall be provided in such cases.</p>

  <h2>14. Refund Exceptions</h2>
  <p>Refunds shall not be applicable for:</p>
  <ul>
    <li>Unused hotel nights</li>
    <li>Missed flights</li>
    <li>Missed sightseeing</li>
    <li>Voluntary early departure</li>
    <li>Late arrivals</li>
    <li>Unused transfers</li>
    <li>Services not utilized after commencement of the trip</li>
  </ul>

  <h2>15. Contact Us</h2>
  <p>For cancellation requests or refund-related queries, please contact:</p>
  <p>
    <strong>TYT Luxe</strong><br>
    Website: www.tytluxe.in<br>
    Email: takeyourtrip7@gmail.com<br>
    WhatsApp/Phone: +91 98750 73788
  </p>

  <p>TYT Luxe reserves the right to amend this Cancellation & Refund Policy at any time without prior notice. The latest version published on TYTLuxe.in shall supersede all previous versions.</p>
</div>
@endsection
