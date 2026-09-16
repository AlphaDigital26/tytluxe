<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Invoice {{ $booking->reference }}</title>
<style>
  * { margin: 0; padding: 0; box-sizing: border-box; }
  body { font-family: 'DejaVu Sans', sans-serif; font-size: 12px; color: #1a1a1a; padding: 36px 40px; }

  .inv-header { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 2px solid #c9a84c; padding-bottom: 20px; margin-bottom: 26px; }
  .inv-brand { font-size: 22px; font-weight: bold; color: #0d0d0d; letter-spacing: 1px; }
  .inv-brand-sub { font-size: 10px; color: #888; letter-spacing: 2px; text-transform: uppercase; margin-top: 2px; }
  .inv-meta { text-align: right; }
  .inv-title { font-size: 18px; font-weight: bold; color: #c9a84c; text-transform: uppercase; letter-spacing: 1px; }
  .inv-meta-line { font-size: 11px; color: #555; margin-top: 4px; }

  .inv-section { margin-bottom: 22px; }
  .inv-section-title { font-size: 10px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; color: #c9a84c; margin-bottom: 8px; border-bottom: 1px solid #eee; padding-bottom: 4px; }
  .inv-row { display: flex; justify-content: space-between; padding: 3px 0; }
  .inv-label { color: #777; }
  .inv-value { color: #1a1a1a; font-weight: bold; text-align: right; }

  .inv-two-col { display: flex; justify-content: space-between; gap: 30px; }
  .inv-two-col > div { flex: 1; }

  table.inv-table { width: 100%; border-collapse: collapse; margin-top: 6px; }
  table.inv-table th { text-align: left; font-size: 10px; text-transform: uppercase; letter-spacing: 0.5px; color: #888; border-bottom: 1px solid #ddd; padding: 6px 0; }
  table.inv-table td { padding: 8px 0; border-bottom: 1px solid #f0f0f0; font-size: 12px; }
  table.inv-table td.right { text-align: right; }

  .inv-price-table { width: 100%; margin-top: 14px; }
  .inv-price-table td { padding: 5px 0; font-size: 12px; }
  .inv-price-table td.right { text-align: right; }
  .inv-price-table tr.total td { border-top: 2px solid #c9a84c; padding-top: 10px; font-size: 15px; font-weight: bold; }
  .inv-price-table tr.total td.right { color: #b8944a; }
  .inv-price-table tr.discount td.right { color: #2e8b57; }

  .inv-status-badge { display: inline-block; padding: 3px 10px; border-radius: 3px; font-size: 10px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px; }
  .inv-status-good { background: #eafaf1; color: #1a7a4a; }
  .inv-status-bad { background: #fdeeee; color: #b83b3b; }
  .inv-status-neutral { background: #f4f0e6; color: #8a6d2f; }

  .inv-footer { margin-top: 40px; padding-top: 16px; border-top: 1px solid #eee; font-size: 10px; color: #999; text-align: center; line-height: 1.6; }
</style>
</head>
<body>

  <div class="inv-header">
    <div>
      <div class="inv-brand">TYTLUXE</div>
      <div class="inv-brand-sub">Take Your Trip</div>
    </div>
    <div class="inv-meta">
      <div class="inv-title">Invoice</div>
      <div class="inv-meta-line">Invoice No: {{ $booking->reference }}</div>
      <div class="inv-meta-line">Date: {{ now()->format('d M Y') }}</div>
    </div>
  </div>

  <div class="inv-two-col">
    <div class="inv-section">
      <div class="inv-section-title">Billed To</div>
      <div class="inv-row"><span class="inv-value">{{ $booking->lead_guest_name }}</span></div>
      @if($booking->guest_email)
      <div class="inv-row"><span class="inv-label">{{ $booking->guest_email }}</span></div>
      @endif
      @if($booking->guest_phone)
      <div class="inv-row"><span class="inv-label">{{ $booking->guest_phone }}</span></div>
      @endif
    </div>

    <div class="inv-section">
      <div class="inv-section-title">Booking Status</div>
      @php
        $statusClass = in_array($booking->status, ['confirmed'], true) ? 'inv-status-good'
            : (in_array($booking->status, ['refunded', 'cancelled', 'failed_needs_review'], true) ? 'inv-status-bad' : 'inv-status-neutral');
      @endphp
      <span class="inv-status-badge {{ $statusClass }}">{{ ucfirst(str_replace('_', ' ', $booking->status)) }}</span>
      <div class="inv-row" style="margin-top:8px;"><span class="inv-label">Booked On</span><span class="inv-value">{{ $booking->created_at->format('d M Y') }}</span></div>
    </div>
  </div>

  @if($booking->hotel)
  <div class="inv-section">
    <div class="inv-section-title">Property</div>
    <table class="inv-table">
      <tr>
        <th>Hotel</th>
        <th>Check-in</th>
        <th>Check-out</th>
        <th>Guests</th>
      </tr>
      <tr>
        <td>
          <strong>{{ $booking->hotel->title }}</strong><br>
          <span style="color:#888;">{{ $booking->hotel->address }}</span>
        </td>
        <td>{{ \Illuminate\Support\Carbon::parse($booking->check_in)->format('d M Y') }}</td>
        <td>{{ \Illuminate\Support\Carbon::parse($booking->check_out)->format('d M Y') }}</td>
        <td>
          {{ $booking->pax_adults }} Adult{{ $booking->pax_adults > 1 ? 's' : '' }}
          @if($booking->pax_children), {{ $booking->pax_children }} Child(ren) @endif
        </td>
      </tr>
    </table>
  </div>
  @endif

  <div class="inv-section">
    <div class="inv-section-title">Guests</div>
    <table class="inv-table">
      <tr><th>Name</th><th>Type</th></tr>
      <tr>
        <td>{{ $booking->lead_guest_name }} (Lead Guest)</td>
        <td>Adult</td>
      </tr>
      @foreach($booking->travelers as $traveler)
        @if($traveler->full_name !== $booking->lead_guest_name)
        <tr>
          <td>{{ $traveler->full_name }}</td>
          <td>{{ ucfirst($traveler->traveler_type) }}</td>
        </tr>
        @endif
      @endforeach
    </table>
  </div>

  <div class="inv-section">
    <div class="inv-section-title">Charges</div>
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
      <tr class="total">
        <td>Total {{ in_array($booking->status, ['refunded'], true) ? 'Refunded' : 'Paid' }}</td>
        <td class="right">{{ $booking->currency }} {{ number_format($booking->total_amount, 2) }}</td>
      </tr>
    </table>
  </div>

  <div class="inv-footer">
    This is a computer-generated invoice and does not require a signature.<br>
    TYTLUXE &bull; support@tytluxe.in &bull; Booking Reference: {{ $booking->reference }}
  </div>

</body>
</html>
