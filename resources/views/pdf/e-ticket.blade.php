<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>E-Ticket {{ $booking->reference }}</title>
<style>
  /* dompdf: no flexbox — every side-by-side layout here is a <table>. */
  * { margin: 0; padding: 0; box-sizing: border-box; }
  body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; color: #262626; padding: 30px 36px; }

  table { border-collapse: collapse; }
  table.et-header { width: 100%; padding-bottom: 16px; border-bottom: 2px solid #c9a84c; margin-bottom: 18px; }
  table.et-header td { vertical-align: middle; }
  .et-logo { height: 52px; display: block; }
  .et-title { font-size: 20px; font-weight: bold; color: #171717; text-transform: uppercase; letter-spacing: 3px; text-align: right; }
  .et-meta { font-size: 10px; color: #999; text-align: right; margin-top: 4px; text-transform: uppercase; letter-spacing: 0.4px; }
  .et-meta strong { color: #1a1a1a; font-size: 10.5px; text-transform: none; letter-spacing: normal; }

  table.et-pnr { width: 100%; background: #171717; margin-bottom: 18px; }
  table.et-pnr td { padding: 12px 16px; color: #fff; vertical-align: middle; }
  .et-pnr-label { font-size: 8.5px; color: #e8c96b; text-transform: uppercase; letter-spacing: 1px; display: block; margin-bottom: 3px; }
  .et-pnr-value { font-size: 17px; font-weight: bold; letter-spacing: 2px; }
  .et-pnr-small { font-size: 11.5px; font-weight: bold; }

  .et-section { font-size: 9.5px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; color: #b8944a; margin: 4px 0 8px; }

  table.et-flight { width: 100%; border: 1px solid #ccc; margin-bottom: 12px; }
  table.et-flight td { padding: 10px 12px; vertical-align: top; }
  .et-flight-head td { background: #f4f0e6; border-bottom: 1px solid #ddd; padding: 7px 12px !important; font-size: 10px; }
  .et-airline { font-size: 12px; font-weight: bold; color: #171717; }
  .et-muted { color: #888; font-size: 9.5px; }
  .et-time { font-size: 17px; font-weight: bold; color: #171717; }
  .et-code { font-size: 12px; font-weight: bold; color: #171717; }
  .et-mid { text-align: center; color: #888; font-size: 9.5px; }
  .et-line { border-top: 1px dashed #bbb; margin: 6px 10px; }
  .et-right { text-align: right; }

  table.et-grid { width: 100%; border: 1px solid #ccc; margin-bottom: 18px; }
  table.et-grid th { background: #171717; color: #e8c96b; text-align: left; font-size: 9px; text-transform: uppercase; letter-spacing: 0.5px; padding: 7px 10px; }
  table.et-grid td { padding: 8px 10px; border-top: 1px solid #ddd; font-size: 10.5px; vertical-align: top; }

  .et-notes { font-size: 9.5px; color: #555; line-height: 1.6; padding-left: 14px; margin-bottom: 16px; }
  .et-notes li { margin-bottom: 3px; }
  .et-footer { padding-top: 12px; border-top: 1px solid #eee; font-size: 9.5px; color: #999; text-align: center; line-height: 1.6; }
</style>
</head>
<body>
@php
  $logoPath = public_path('assets/images/tyt-logo.png');
  $logoSrc = file_exists($logoPath) ? 'data:image/png;base64,'.base64_encode(file_get_contents($logoPath)) : null;
  $time = fn ($iso, $fmt) => $iso ? \Illuminate\Support\Carbon::parse($iso)->format($fmt) : '—';
  $title = fn ($s) => ucwords(strtolower(str_replace('_', ' ', (string) $s)));
  $duration = fn ($m) => $m ? intdiv($m, 60).'h '.($m % 60).'m' : null;

  $pnrBySector = collect($booking->tripjack_flight_pnr ?? []);
  $singlePnr = $pnrBySector->unique()->count() === 1 ? $pnrBySector->first() : null;
  $passengers = $booking->flightPassengers();
  $paxCounts = ['ADULT' => (int) $booking->pax_adults, 'CHILD' => (int) $booking->pax_children, 'INFANT' => (int) $booking->pax_infants];

  // Segments from Booking Details; bookings ticketed before those were
  // stored fall back to the searched legs (route + date only).
  $segments = $booking->flight_itinerary['segments'] ?? [];
  $legsOnly = ! $segments;
  if ($legsOnly) {
      $segments = collect($booking->flight_legs ?? [])->map(fn ($l) => [
          'from' => $l['src'] ?? '', 'to' => $l['dest'] ?? '', 'departs' => $l['departureDate'] ?? null,
      ])->all();
  }
  $international = collect($segments)->contains(fn ($s) => ! empty($s['fromCountry'] ?? null) && ($s['fromCountry'] ?? null) !== ($s['toCountry'] ?? null))
      || filled($booking->flight_segments_payload['travellerInfo'][0]['pNum'] ?? null);
@endphp

  <table class="et-header">
    <tr>
      <td>
        @if($logoSrc)
          <img src="{{ $logoSrc }}" alt="TYT Luxe" class="et-logo">
        @else
          <div style="font-size:22px;font-weight:bold;color:#0d0d0d;letter-spacing:1px;">TYTLUXE</div>
        @endif
      </td>
      <td>
        <div class="et-title">E-Ticket</div>
        <div class="et-meta">Booking Reference <strong>{{ $booking->reference }}</strong></div>
        <div class="et-meta">Booked On <strong>{{ $booking->created_at->format('d M Y') }}</strong></div>
        @if($booking->flight_reissued_at)
        <div class="et-meta">Rescheduled On <strong>{{ $booking->flight_reissued_at->format('d M Y') }}</strong></div>
        @endif
      </td>
    </tr>
  </table>

  <table class="et-pnr">
    <tr>
      <td>
        <span class="et-pnr-label">Airline PNR</span>
        @if($singlePnr)
          <span class="et-pnr-value">{{ $singlePnr }}</span>
        @else
          @foreach($pnrBySector as $sector => $pnr)
            <span class="et-pnr-small">{{ $sector }}: {{ $pnr }}</span>@if(! $loop->last)&nbsp;&nbsp;&nbsp;@endif
          @endforeach
        @endif
      </td>
      <td class="et-right">
        <span class="et-pnr-label">Status</span>
        <span class="et-pnr-small" style="color:#4ade80;">CONFIRMED</span>
      </td>
      <td class="et-right">
        <span class="et-pnr-label">Cabin</span>
        <span class="et-pnr-small">{{ $title($segments[0]['cabin'] ?? $booking->flight_cabin_class ?? 'Economy') }}</span>
      </td>
    </tr>
  </table>

  <div class="et-section">Flight Details</div>
  @foreach($segments as $i => $seg)
    @php $sector = ($seg['from'] ?? '').'-'.($seg['to'] ?? ''); @endphp
    <table class="et-flight">
      <tr class="et-flight-head">
        <td colspan="2">
          @if($legsOnly)
            <span class="et-airline">Flight {{ $i + 1 }}</span>
          @else
            <span class="et-airline">{{ $seg['airline'] ?: $seg['airlineCode'] }}</span>
            &nbsp;<span class="et-muted">{{ $seg['airlineCode'] }}-{{ $seg['flightNo'] }}{{ ! empty($seg['aircraft']) ? ' · '.$seg['aircraft'] : '' }}</span>
          @endif
        </td>
        <td class="et-right">
          @if($pnrBySector->has($sector) && ! $singlePnr)
            <span class="et-muted">PNR</span> <strong>{{ $pnrBySector[$sector] }}</strong>
          @endif
        </td>
      </tr>
      <tr>
        <td style="width:38%;">
          <div class="et-time">{{ $legsOnly ? '' : $time($seg['departs'], 'H:i') }}</div>
          <div class="et-code">{{ $seg['from'] }}{{ ! empty($seg['fromCity']) ? ' · '.$seg['fromCity'] : '' }}</div>
          <div class="et-muted">{{ $time($seg['departs'], 'D, d M Y') }}</div>
          @if(! empty($seg['fromAirport']))<div class="et-muted">{{ $seg['fromAirport'] }}</div>@endif
          @if(! empty($seg['fromTerminal']))<div class="et-muted"><strong style="color:#262626;">Terminal {{ preg_replace('/^terminal\s*/i', '', $seg['fromTerminal']) }}</strong></div>@endif
        </td>
        <td style="width:24%;" class="et-mid">
          <div style="margin-top:6px;">{{ $duration($seg['duration'] ?? null) }}</div>
          <div class="et-line"></div>
        </td>
        <td style="width:38%;" class="et-right">
          <div class="et-time">{{ $legsOnly ? '' : $time($seg['arrives'] ?? null, 'H:i') }}</div>
          <div class="et-code">{{ $seg['to'] }}{{ ! empty($seg['toCity']) ? ' · '.$seg['toCity'] : '' }}</div>
          @if(! $legsOnly)<div class="et-muted">{{ $time($seg['arrives'] ?? null, 'D, d M Y') }}</div>@endif
          @if(! empty($seg['toAirport']))<div class="et-muted">{{ $seg['toAirport'] }}</div>@endif
          @if(! empty($seg['toTerminal']))<div class="et-muted"><strong style="color:#262626;">Terminal {{ preg_replace('/^terminal\s*/i', '', $seg['toTerminal']) }}</strong></div>@endif
        </td>
      </tr>
      @php $bags = collect($seg['baggage'] ?? [])->filter(fn ($bag, $paxType) => ($paxCounts[$paxType] ?? 0) > 0); @endphp
      @if($bags->isNotEmpty())
      <tr>
        <td colspan="3" style="border-top:1px solid #eee; padding-top:7px; padding-bottom:7px;">
          <span class="et-muted">Baggage:</span>
          @foreach($bags as $paxType => $bag)
            <span style="font-size:10px;">{{ ucfirst(strtolower($paxType)) }} — Check-in {{ $bag['checkin'] ?: '—' }}, Cabin {{ $bag['cabin'] ?: '—' }}</span>@if(! $loop->last)&nbsp;&nbsp;·&nbsp;&nbsp;@endif
          @endforeach
        </td>
      </tr>
      @endif
    </table>
  @endforeach

  <div class="et-section" style="margin-top:8px;">Passengers</div>
  <table class="et-grid">
    <thead>
      <tr><th>#</th><th>Passenger</th><th>Type</th><th>E-Ticket Number</th></tr>
    </thead>
    <tbody>
      @foreach($passengers as $i => $p)
      <tr>
        <td>{{ $i + 1 }}</td>
        <td><strong>{{ $p['name'] }}</strong></td>
        <td>{{ $p['type'] }}</td>
        <td>
          @forelse(collect($p['tickets'])->unique() as $sector => $ticket)
            {{ $ticket }}@if(count(array_unique($p['tickets'])) > 1) <span class="et-muted">({{ $sector }})</span>@endif<br>
          @empty
            <span class="et-muted">Use the airline PNR</span>
          @endforelse
        </td>
      </tr>
      @endforeach
    </tbody>
  </table>

  <div class="et-section">Important Information</div>
  <ol class="et-notes">
    <li>Carry a valid government photo ID{{ $international ? ' and your passport (plus any visa needed)' : '' }}. The name must match this ticket exactly.</li>
    <li>Reach the airport at least {{ $international ? '3 hours' : '2 hours' }} before departure. Check-in counters usually close {{ $international ? '60' : '45' }} minutes before departure.</li>
    <li>Web check-in is available on the airline's website or app using the PNR and your last name.</li>
    <li>Baggage shown is the fare's free allowance; seats, meals or extra baggage you added are confirmed separately by the airline.</li>
    <li>Flight times are local to each airport and may be changed by the airline. Please check your flight status before leaving.</li>
    <li>To cancel or change this booking, use "My Bookings" on tytluxe.in or contact us. Airline fare rules apply.</li>
  </ol>

  <div class="et-footer">
    TYT Luxe &bull; takeyourtrip7@gmail.com &bull; +91 98750 73788 &bull; Booking Reference: {{ $booking->reference }}<br>
    This is an electronic ticket issued through the airline's reservation system. Please present it with your ID at check-in.
  </div>
</body>
</html>
