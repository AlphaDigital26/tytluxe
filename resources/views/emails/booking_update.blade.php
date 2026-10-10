@php
  use App\Mail\BookingUpdateMail as M;
  use Illuminate\Support\Carbon;

  $isFlight = $booking->vertical === 'flight';
  $cur = $booking->currency ?: 'INR';
  $money = fn ($v) => $cur.' '.number_format((float) $v, 2);
  $date = fn ($v) => $v ? Carbon::parse($v)->format('D, j M Y') : '—';
  $dateTime = fn ($v) => $v ? Carbon::parse($v)->format('D, j M Y · g:i A') : '—';

  [$headline, $intro] = match ($kind) {
      M::CONFIRMED => $isFlight
          ? ['Your flight is booked', 'Your tickets have been issued by the airline. Your PNR and ticket numbers are below — keep this email handy for check-in.']
          : ['Your booking is confirmed', 'Your payment was successful and the hotel has confirmed your room. Your booking details are below.'],
      M::RESCHEDULED => ['Your new flight details', 'Your flight has been rescheduled and the airline has issued your new tickets. Please use the details below — your old PNR is no longer valid.'],
      M::HELD => ['Your fare is on hold', 'We have reserved this fare with the airline. No payment has been taken yet — confirm and pay before the deadline below, or the seats will be released.'],
      M::HOLD_EXPIRED => ['Your held fare has expired', 'The fare was not confirmed before the airline\'s deadline, so the seats have been released. No payment was taken. You are welcome to search again for current fares.'],
      M::CANCELLED => ['Your booking has been cancelled', $booking->guestCancellationMessage()],
      M::EXTRAS => match ($ssrStatus) {
          'confirmed' => ['Your extras are confirmed', 'The airline has confirmed the seats, meals and baggage you added. You can see them on your booking page.'],
          'partially_confirmed' => ['Some of your extras couldn\'t be added', 'The airline confirmed part of what you added, but not everything. We have refunded what couldn\'t be added — it can take 5–7 business days to show in your account.'],
          default => $booking->manual_refund_due_at
              ? ['We couldn\'t add your extras', 'The airline couldn\'t add the seats, meals or baggage you chose. Our team is processing your refund and will contact you if we need anything.']
              : ['We couldn\'t add your extras', 'The airline couldn\'t add the seats, meals or baggage you chose, so we have refunded you in full. It can take 5–7 business days to show in your account.'],
      },
      M::FAILED_REFUNDED => ['We couldn\'t confirm your booking', 'Unfortunately the '.($isFlight ? 'airline' : 'hotel').' could not confirm this booking after your payment, so we have refunded you in full. It can take 5–7 business days to show in your account. We are sorry for the trouble.'],
      default => ['Update on your booking', ''],
  };

  // Flight passengers with their ticket numbers (per-traveller list, or the
  // itinerary's tickets keyed by name).
  $travellers = collect($booking->flight_segments_payload['travellerInfo'] ?? []);
  $storedTickets = collect($booking->tripjack_flight_ticket_numbers ?? []);
  $itineraryTickets = $booking->flight_itinerary['tickets'] ?? [];
  $ticketFor = function (array $t) use ($storedTickets, $itineraryTickets) {
      $full = trim(preg_replace('/\s+/', ' ', ($t['ti'] ?? '').' '.($t['fN'] ?? '').' '.($t['lN'] ?? '')));
      $entry = $storedTickets->first(fn ($e) => is_array($e) && strcasecmp($e['name'] ?? '', $full) === 0);
      $tickets = $entry['tickets'] ?? ($itineraryTickets[strtoupper(trim(($t['fN'] ?? '').' '.($t['lN'] ?? '')))] ?? []);

      return collect($tickets)->unique()->implode(', ');
  };
  $segments = $booking->flight_itinerary['segments'] ?? [];
  $pnrs = collect($booking->tripjack_flight_pnr ?? [])->unique()->implode(', ');
  $totals = $booking->hasInvoice() ? $booking->invoiceTotals() : null;
  $extrasPayment = $kind === M::EXTRAS
      ? $booking->payments()->where('purpose', 'flight_ssr')->whereIn('status', \App\Models\Booking::PAID_PAYMENT_STATUSES)->latest()->first()
      : null;
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $headline }} - TYT Luxe</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', Arial, sans-serif; background-color: #f4f1eb; color: #333; }
        .wrapper { max-width: 600px; margin: 40px auto; padding: 20px; }
        .card { background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 8px 40px rgba(0,0,0,0.10); }
        .header { background: #0a0a0a; padding: 36px 40px; text-align: center; }
        .header-logo { font-family: Georgia, serif; font-size: 28px; font-weight: 600; color: #d4af37; letter-spacing: 3px; text-transform: uppercase; }
        .header-tagline { font-size: 11px; color: #888; letter-spacing: 2px; text-transform: uppercase; margin-top: 4px; }
        .gold-bar { height: 3px; background: #d4af37; }
        .body { padding: 40px 44px; }
        .headline { font-family: Georgia, serif; font-size: 24px; font-weight: 600; color: #111; margin-bottom: 10px; }
        .greeting { font-size: 14px; color: #333; margin-bottom: 10px; }
        .intro { font-size: 14px; line-height: 1.75; color: #555; margin-bottom: 26px; }
        .ref { display: inline-block; background: #0a0a0a; color: #d4af37; font-family: 'Courier New', monospace; font-size: 16px; font-weight: 700; letter-spacing: 2px; padding: 8px 16px; border-radius: 6px; margin-bottom: 24px; }
        .section-title { font-size: 11px; text-transform: uppercase; letter-spacing: 2px; color: #999; margin: 22px 0 10px; }
        table.rows { width: 100%; border-collapse: collapse; }
        table.rows td { font-size: 13px; padding: 8px 0; border-bottom: 1px solid #f0ece2; vertical-align: top; }
        table.rows td.label { color: #888; width: 42%; }
        table.rows td.value { color: #222; text-align: right; }
        .highlight { color: #111; font-weight: 700; }
        .info-box { background: #fdf9ee; border-left: 3px solid #d4af37; border-radius: 6px; padding: 14px 18px; margin: 26px 0 0; }
        .info-box p { font-size: 13px; color: #666; line-height: 1.6; }
        .btn-wrap { text-align: center; margin: 30px 0 6px; }
        .btn { display: inline-block; background: #d4af37; color: #0a0a0a !important; text-decoration: none; font-size: 13px; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; padding: 13px 30px; border-radius: 100px; }
        .footer { background: #f9f7f3; padding: 26px 44px; text-align: center; border-top: 1px solid #ece8de; }
        .footer p { font-size: 12px; color: #999; line-height: 1.7; }
        .footer a { color: #b8932b; text-decoration: none; }
        .footer-brand { font-family: Georgia, serif; font-size: 16px; font-weight: 600; color: #bbb; letter-spacing: 2px; display: block; margin-bottom: 8px; }
    </style>
</head>
<body>
<div class="wrapper">
  <div class="card">
    <div class="header">
      <div class="header-logo">TYT Luxe</div>
      <div class="header-tagline">Elevate Your Journey</div>
    </div>
    <div class="gold-bar"></div>

    <div class="body">
      <p class="headline">{{ $headline }}</p>
      <p class="greeting">Hello {{ $booking->lead_guest_name ?: 'there' }},</p>
      <p class="intro">{{ $intro }}</p>
      <div class="ref">{{ $booking->reference }}</div>

      @if($isFlight)
        <div class="section-title">Your trip</div>
        <table class="rows">
          @if($pnrs && in_array($kind, [M::CONFIRMED, M::RESCHEDULED, M::HELD], true))
          <tr><td class="label">Airline PNR</td><td class="value highlight">{{ $pnrs }}</td></tr>
          @endif
          <tr><td class="label">Route</td><td class="value">{{ str_replace('-', ' → ', (string) $booking->flight_route) }}</td></tr>
          <tr><td class="label">Departure</td><td class="value">{{ $date($booking->flight_departure_date) }}</td></tr>
          @if($booking->flight_return_date)
          <tr><td class="label">Return</td><td class="value">{{ $date($booking->flight_return_date) }}</td></tr>
          @endif
          @if($kind === M::HELD && $booking->tripjack_hold_expires_at)
          <tr><td class="label">Pay before</td><td class="value highlight">{{ $dateTime($booking->tripjack_hold_expires_at) }}</td></tr>
          @endif
        </table>

        @if($segments && in_array($kind, [M::CONFIRMED, M::RESCHEDULED], true))
          <div class="section-title">Flights</div>
          <table class="rows">
            @foreach($segments as $seg)
            <tr>
              <td class="label">{{ trim(($seg['airline'] ?? '').' '.($seg['airlineCode'] ?? '').'-'.($seg['flightNo'] ?? '')) }}</td>
              <td class="value">{{ $seg['from'] ?? '' }} {{ $dateTime($seg['departs'] ?? null) }}<br>→ {{ $seg['to'] ?? '' }} {{ $dateTime($seg['arrives'] ?? null) }}</td>
            </tr>
            @endforeach
          </table>
        @endif

        @if($travellers->isNotEmpty())
          <div class="section-title">Passengers</div>
          <table class="rows">
            @foreach($travellers as $t)
            @php $ticket = in_array($kind, [M::CONFIRMED, M::RESCHEDULED], true) ? $ticketFor($t) : ''; @endphp
            <tr>
              <td class="label">{{ trim(($t['ti'] ?? '').' '.($t['fN'] ?? '').' '.($t['lN'] ?? '')) }}</td>
              <td class="value">{{ $ticket ? 'Ticket '.$ticket : ucfirst(strtolower($t['pt'] ?? 'Adult')) }}</td>
            </tr>
            @endforeach
          </table>
        @endif
      @else
        <div class="section-title">Your stay</div>
        <table class="rows">
          @if($booking->hotel)
          <tr><td class="label">Hotel</td><td class="value highlight">{{ $booking->hotel->title }}</td></tr>
          @endif
          @if($booking->room_name)
          <tr><td class="label">Room</td><td class="value">{{ $booking->room_name }}@if($booking->meal_basis)<br><span style="color:#888;">{{ $booking->meal_basis }}</span>@endif</td></tr>
          @endif
          <tr><td class="label">Check-in</td><td class="value">{{ $date($booking->check_in) }}</td></tr>
          <tr><td class="label">Check-out</td><td class="value">{{ $date($booking->check_out) }}</td></tr>
          <tr><td class="label">Guests</td><td class="value">{{ (int) $booking->pax_adults }} adult{{ $booking->pax_adults == 1 ? '' : 's' }}@if($booking->pax_children), {{ (int) $booking->pax_children }} child{{ $booking->pax_children == 1 ? '' : 'ren' }}@endif</td></tr>
          @if($booking->hotel_confirmation_number && $kind === M::CONFIRMED)
          <tr><td class="label">Hotel confirmation no.</td><td class="value highlight">{{ $booking->hotel_confirmation_number }}</td></tr>
          @endif
        </table>
      @endif

      @if($extrasPayment)
        <div class="section-title">Payment for extras</div>
        <table class="rows">
          <tr><td class="label">Paid</td><td class="value">{{ $money($extrasPayment->amount) }}</td></tr>
          @if((float) $extrasPayment->refund_amount > 0)
          <tr><td class="label">Refunded</td><td class="value">− {{ $money($extrasPayment->refund_amount) }}</td></tr>
          @endif
        </table>
      @elseif($totals && in_array($kind, [M::CONFIRMED, M::RESCHEDULED, M::CANCELLED, M::FAILED_REFUNDED], true))
        <div class="section-title">Payment</div>
        <table class="rows">
          <tr><td class="label">Total paid</td><td class="value">{{ $money($totals['paid']) }}</td></tr>
          @if($totals['refunded'] > 0)
          <tr><td class="label">Refunded</td><td class="value">− {{ $money($totals['refunded']) }}</td></tr>
          @endif
        </table>
      @elseif($kind === M::HELD)
        <div class="section-title">Payment</div>
        <table class="rows">
          <tr><td class="label">Amount to pay</td><td class="value highlight">{{ $money($booking->total_amount) }}</td></tr>
        </table>
      @endif

      @if(in_array($kind, [M::CONFIRMED, M::RESCHEDULED], true) && $booking->hasETicket())
      <div class="info-box"><p>Your e-ticket{{ $totals ? ' and invoice are' : ' is' }} attached to this email. Please carry the e-ticket (printed or on your phone) with a photo ID to the airport. You can also download {{ $totals ? 'both' : 'it' }} any time from your booking page.</p></div>
      @elseif(in_array($kind, [M::CONFIRMED, M::RESCHEDULED, M::CANCELLED], true) && $totals)
      <div class="info-box"><p>Your invoice is attached to this email. You can also download it any time from your booking page.</p></div>
      @endif

      <div class="btn-wrap">
        <a class="btn" href="{{ route('hotel.booking.confirmation', $booking->reference) }}">{{ $kind === M::HELD ? 'Confirm & pay' : 'View your booking' }}</a>
      </div>
    </div>

    <div class="footer">
      <span class="footer-brand">TYT Luxe</span>
      <p>
        Questions about this booking? Write to <a href="mailto:takeyourtrip7@gmail.com">takeyourtrip7@gmail.com</a> or call / WhatsApp +91 98750 73788, quoting <strong>{{ $booking->reference }}</strong>.<br>
        © {{ date('Y') }} TYT Luxe. You received this email because you made a booking with us.
      </p>
    </div>
  </div>
</div>
</body>
</html>
