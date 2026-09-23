<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Invoice {{ $booking->reference }}</title>
<style>
  * { margin: 0; padding: 0; box-sizing: border-box; }
  body { font-family: 'DejaVu Sans', sans-serif; font-size: 11.5px; color: #262626; padding: 34px 40px; }

  /* ===== Header ===== */
  /* dompdf has no flexbox support at all, so every `display: flex` in this
     file was silently ignored — the logo/address and the invoice meta
     block, meant to sit at opposite ends of one row, instead stacked one
     above the other as plain blocks. Real HTML <table>s are what dompdf
     actually lays out reliably side-by-side. */
  table.inv-header { width: 100%; border-collapse: collapse; padding-bottom: 20px; border-bottom: 2px solid #c9a84c; margin-bottom: 24px; }
  table.inv-header td { vertical-align: middle; padding: 0; }
  .inv-logo { height: 58px; display: block; }
  .inv-meta { text-align: right; }
  .inv-title { font-size: 21px; font-weight: bold; color: #171717; text-transform: uppercase; letter-spacing: 3px; margin-bottom: 10px; }
  .inv-meta-line { display: flex; justify-content: flex-end; gap: 8px; font-size: 10.5px; color: #999; margin-top: 5px; text-transform: uppercase; letter-spacing: 0.4px; }
  .inv-meta-line strong { color: #1a1a1a; font-size: 11px; text-transform: none; letter-spacing: normal; min-width: 110px; text-align: right; }

  /* ===== Party blocks (plain bordered boxes, like a wholesale-style invoice) ===== */
  /* Same dompdf flexbox gap as the header above — these were meant to sit
     side by side as two columns but silently stacked instead. A table. */
  table.inv-parties { width: 100%; border-collapse: collapse; border: 1px solid #ccc; margin-bottom: 22px; }
  .inv-party { width: 50%; padding: 12px 16px; vertical-align: top; }
  .inv-party + .inv-party { border-left: 1px solid #ccc; }
  .inv-party-title { font-size: 9.5px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; color: #b8944a; margin-bottom: 7px; }
  .inv-party-name { font-size: 13.5px; font-weight: bold; color: #1a1a1a; margin-bottom: 3px; }
  .inv-party-line { font-size: 10.5px; color: #666; line-height: 1.5; }

  .inv-status-badge { display: inline-block; margin-top: 8px; padding: 3px 11px; border-radius: 3px; font-size: 9.5px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px; }
  .inv-status-good { background: #eafaf1; color: #1a7a4a; }
  .inv-status-bad { background: #fdeeee; color: #b83b3b; }
  .inv-status-neutral { background: #f4f0e6; color: #8a6d2f; }

  /* ===== Itemized table — full bordered grid, like the sample ===== */
  .inv-section-title { font-size: 9.5px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; color: #b8944a; margin-bottom: 8px; }
  table.inv-table { width: 100%; border-collapse: collapse; margin-bottom: 0; border: 1px solid #ccc; }
  table.inv-table thead th {
    background: #171717; color: #e8c96b; text-align: left; font-size: 9.5px; text-transform: uppercase;
    letter-spacing: 0.5px; padding: 8px 10px; border: 1px solid #171717;
  }
  table.inv-table thead th.right { text-align: right; }
  table.inv-table tbody td { padding: 9px 10px; border: 1px solid #ddd; font-size: 11px; vertical-align: top; }
  table.inv-table tbody td.right { text-align: right; }
  /* Table, not flex + gap — dompdf doesn't support either, so the old
     flex row rendered with no real spacing between items (everything
     ran together). A table's cells give dompdf real, reliable columns. */
  table.inv-stay-strip { width: 100%; border-collapse: collapse; border: 1px solid #ccc; border-top: none; margin-bottom: 22px; }
  table.inv-stay-strip td { padding: 10px 14px; border-right: 1px solid #eee; }
  table.inv-stay-strip td:last-child { border-right: none; }
  .inv-stay-label { display: block; font-size: 8.5px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.6px; color: #b8944a; margin-bottom: 3px; }
  .inv-stay-value { font-size: 11px; color: #1a1a1a; font-weight: bold; }

  /* ===== Charges summary ===== */
  /* Same dompdf flexbox gap — `justify-content: flex-end` never applied,
     so this table rendered flush left instead of pushed to the right.
     A fixed-width table with `margin-left: auto` is the dompdf-safe way
     to right-align a block-level element. */
  .inv-summary-wrap { margin-bottom: 22px; }
  table.inv-price-table { width: 290px; margin-left: auto; }
  .inv-price-table td { padding: 6px 0; font-size: 11.5px; color: #555; }
  .inv-price-table td.right { text-align: right; color: #1a1a1a; }
  .inv-price-table tr.discount td.right { color: #2e8b57; }
  .inv-price-table tr.gross td { border-top: 1px solid #ddd; padding-top: 10px; font-weight: bold; color: #1a1a1a; }
  .inv-price-table tr.gross td.right { padding-top: 10px; }
  .inv-price-table tr.total td {
    background: #171717; color: #fff; font-weight: bold; font-size: 14px; padding: 11px 12px; border-radius: 6px 0 0 6px;
  }
  .inv-price-table tr.total td.right { background: #171717; color: #e8c96b; border-radius: 0 6px 6px 0; padding: 11px 12px; }

  /* ===== Terms ===== */
  .inv-terms-title { font-size: 9.5px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; color: #b8944a; margin-bottom: 8px; padding-top: 14px; border-top: 1px solid #eee; }
  .inv-terms { font-size: 9px; color: #999; line-height: 1.6; padding-left: 14px; }
  .inv-terms li { margin-bottom: 3px; }

  .inv-footer { margin-top: 24px; padding-top: 14px; border-top: 1px solid #eee; font-size: 10px; color: #999; text-align: center; line-height: 1.6; }
</style>
</head>
<body>

  @php
    $logoPath = public_path('assets/images/tyt-logo.png');
    $logoSrc = file_exists($logoPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath)) : null;
    $statusClass = in_array($booking->status, ['confirmed'], true) ? 'inv-status-good'
        : (in_array($booking->status, ['refunded', 'cancelled', 'failed_needs_review'], true) ? 'inv-status-bad' : 'inv-status-neutral');
    $nights = ($booking->check_in && $booking->check_out)
        ? max(1, (int) \Illuminate\Support\Carbon::parse($booking->check_in)->diffInDays(\Illuminate\Support\Carbon::parse($booking->check_out)))
        : 1;
    $guestsCount = $booking->pax_adults + ($booking->pax_children ?? 0);
  @endphp

  <table class="inv-header">
    <tr>
      <td>
        @if($logoSrc)
          <img src="{{ $logoSrc }}" alt="TYT Luxe" class="inv-logo">
        @else
          <div style="font-size:22px;font-weight:bold;color:#0d0d0d;letter-spacing:1px;">TYTLUXE</div>
        @endif
      </td>
      <td class="inv-meta">
        <div class="inv-title">Invoice</div>
        <div class="inv-meta-line">Invoice No <strong>{{ $booking->reference }}</strong></div>
        <div class="inv-meta-line">Invoice Date <strong>{{ now()->format('d M Y') }}</strong></div>
        <div class="inv-meta-line">Booking Reference <strong>{{ $booking->reference }}</strong></div>
      </td>
    </tr>
  </table>

  <table class="inv-parties">
    <tr>
    <td class="inv-party">
      <div class="inv-party-title">Issued By</div>
      <div class="inv-party-name">TYT Luxe (Take Your Trip)</div>
      <div class="inv-party-line">
        Surana Supremus, 4th Floor, Cabin No - 9,<br>
        Near Safal Square, Vesu, Surat 394518, Gujarat
      </div>
      <div class="inv-party-line" style="margin-top:6px;">
        takeyourtrip7@gmail.com &bull; +91 98750 73788
      </div>
      <div class="inv-party-line" style="margin-top:6px;">
        GSTIN: <strong style="color:#1a1a1a;">24ABAFT6627K1ZZ</strong>
      </div>
    </td>
    <td class="inv-party">
      <div class="inv-party-title">Billed To</div>
      <div class="inv-party-name">{{ $booking->lead_guest_name }}</div>
      @if($booking->guest_email)
        <div class="inv-party-line">{{ $booking->guest_email }}</div>
      @endif
      @if($booking->guest_phone)
        <div class="inv-party-line">{{ $booking->guest_phone }}</div>
      @endif
      <span class="inv-status-badge {{ $statusClass }}">{{ ucfirst(str_replace('_', ' ', $booking->status)) }}</span>
    </td>
    </tr>
  </table>

  @if($booking->hotel)
  <div class="inv-section-title">Booking Details</div>
  <table class="inv-table">
    <thead>
      <tr>
        <th>Hotel Name</th>
        <th>Room Type</th>
        <th>Guest(s)</th>
        <th class="right">Nights</th>
        <th class="right">Amount</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>
          <strong>{{ $booking->hotel->title }}</strong><br>
          <span style="color:#999;">{{ $booking->hotel->address }}</span>
        </td>
        <td>
          {{ $booking->room_name ?? $booking->roomType->name ?? '—' }}
          @if($booking->meal_basis)<br><span style="color:#999;">{{ $booking->meal_basis }}</span>@endif
        </td>
        <td>
          {{ $booking->lead_guest_name }}
          @if($booking->travelers->count() > 1)
            &amp; {{ $booking->travelers->count() - 1 }} other{{ $booking->travelers->count() - 1 > 1 ? 's' : '' }}
          @endif
        </td>
        <td class="right">{{ $nights }}</td>
        <td class="right">{{ $booking->currency }} {{ number_format($booking->base_amount, 2) }}</td>
      </tr>
    </tbody>
  </table>
  <table class="inv-stay-strip">
    <tr>
      <td><span class="inv-stay-label">City</span><span class="inv-stay-value">{{ $booking->hotel->destination->name ?? '—' }}</span></td>
      <td><span class="inv-stay-label">Check-in</span><span class="inv-stay-value">{{ \Illuminate\Support\Carbon::parse($booking->check_in)->format('d M Y') }}</span></td>
      <td><span class="inv-stay-label">Check-out</span><span class="inv-stay-value">{{ \Illuminate\Support\Carbon::parse($booking->check_out)->format('d M Y') }}</span></td>
      <td><span class="inv-stay-label">Booked On</span><span class="inv-stay-value">{{ $booking->created_at->format('d M Y') }}</span></td>
    </tr>
  </table>
  @endif

  <div class="inv-section-title">Guests</div>
  <table class="inv-table" style="margin-bottom:22px;">
    <thead>
      <tr><th>Name</th><th class="right">Type</th></tr>
    </thead>
    <tbody>
      @foreach($booking->travelers as $traveler)
        <tr>
          <td>{{ $traveler->full_name }}</td>
          <td class="right">{{ ucfirst($traveler->traveler_type) }}</td>
        </tr>
      @endforeach
    </tbody>
  </table>

  <div class="inv-summary-wrap">
    <table class="inv-price-table">
      <tr>
        <td>Room Charges</td>
        <td class="right">{{ $booking->currency }} {{ number_format($booking->base_amount, 2) }}</td>
      </tr>
      @if($booking->tax_amount > 0)
      <tr>
        <td>Taxes &amp; Fees</td>
        <td class="right">{{ $booking->currency }} {{ number_format($booking->tax_amount, 2) }}</td>
      </tr>
      @endif
      @if($booking->discount_amount > 0)
      <tr class="discount">
        <td>Discount</td>
        <td class="right">&minus; {{ $booking->currency }} {{ number_format($booking->discount_amount, 2) }}</td>
      </tr>
      @endif
      {{-- TripJack's docs require Management Fee and Management Fee Tax to
           be shown as their own line items, not folded silently into Taxes
           & Fees above — both are already included in the totals, so these
           two rows are informational only and don't add to the total. --}}
      @if($booking->tripjack_mf > 0)
      <tr>
        <td>Management Fee <span style="color:#999; font-weight:normal;">(incl. above)</span></td>
        <td class="right">{{ $booking->currency }} {{ number_format($booking->tripjack_mf, 2) }}</td>
      </tr>
      @endif
      @if($booking->tripjack_mft > 0)
      <tr>
        <td>Management Fee Tax <span style="color:#999; font-weight:normal;">(incl. above)</span></td>
        <td class="right">{{ $booking->currency }} {{ number_format($booking->tripjack_mft, 2) }}</td>
      </tr>
      @endif
      <tr class="gross">
        <td>Gross Amount</td>
        <td class="right">{{ $booking->currency }} {{ number_format($booking->total_amount, 2) }}</td>
      </tr>
      <tr class="total">
        <td>{{ in_array($booking->status, ['refunded'], true) ? 'Total Refunded' : 'Net Amount Paid' }}</td>
        <td class="right">{{ $booking->currency }} {{ number_format($booking->total_amount, 2) }}</td>
      </tr>
    </table>
  </div>

  <div class="inv-terms-title">Terms &amp; Conditions</div>
  <ol class="inv-terms">
    <li>This booking is fulfilled through TYT Luxe's hotel supply partners and is governed by the respective property's cancellation and check-in policies.</li>
    <li>TYT Luxe acts as a booking facilitator; the hotel remains the principal service provider for stay-related services.</li>
    <li>Refunds, where applicable, are processed to the original payment method and may take 5-7 business days to reflect.</li>
    <li>Any discrepancy in this invoice must be reported within 7 days of the invoice date.</li>
    <li>This is a computer-generated invoice and does not require a physical signature.</li>
  </ol>

  <div class="inv-footer">
    TYT Luxe &bull; takeyourtrip7@gmail.com &bull; +91 98750 73788 &bull; Booking Reference: {{ $booking->reference }}
  </div>

</body>
</html>
