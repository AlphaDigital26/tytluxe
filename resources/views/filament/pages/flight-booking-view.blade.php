@php
    $record = $this->record;
    $status = \App\Support\FlightBookingStatus::for($record);
    $tone = match ($status['color']) {
        'success' => ['#1a7f4b', '#e7f6ee'],
        'warning' => ['#b3790a', '#fdf3df'],
        'danger' => ['#9c1c1c', '#fbe9e9'],
        'info' => ['#1d4ed8', '#e8efff'],
        default => ['#555', '#f1f1f1'],
    };
    $money = fn ($v) => '₹'.number_format((float) $v, 2);
    $time = fn ($v) => $v ? \Illuminate\Support\Carbon::parse($v)->format('D, j M Y · g:i A') : '—';

    $itinerary = $record->flight_itinerary ?? [];
    $segments = $itinerary['segments'] ?? [];
    $ticketsByName = $itinerary['tickets'] ?? [];
    $legs = $record->flight_legs ?? [];
    $pnrs = collect($record->tripjack_flight_pnr ?? [])->unique();
    $travellers = $record->flight_segments_payload['travellerInfo'] ?? [];

    $paxType = fn ($t) => match (strtoupper($t['pt'] ?? 'ADULT')) { 'CHILD' => 'Child', 'INFANT' => 'Infant', default => 'Adult' };
    $extras = function (array $t): string {
        $parts = [];
        foreach (['ssrSeatInfos' => 'Seat', 'ssrMealInfos' => 'Meal', 'ssrBaggageInfos' => 'Extra baggage'] as $field => $label) {
            $codes = collect($t[$field] ?? [])->pluck('code')->filter()->implode(', ');
            if ($codes) {
                $parts[] = $label.': '.$codes;
            }
        }

        return implode(' · ', $parts) ?: '—';
    };
    // Saved ticket numbers: one entry per traveller ({name, tickets}), or the
    // older single-traveller {"DEL-BOM": "…"} shape.
    $storedTickets = $record->tripjack_flight_ticket_numbers ?? [];
    $storedPerTraveller = is_array(reset($storedTickets) ?: null);
    $ticketsFor = function (array $t, int $i) use ($ticketsByName, $storedTickets, $storedPerTraveller) {
        $key = strtoupper(trim(($t['fN'] ?? '').' '.($t['lN'] ?? '')));
        if ($tickets = collect($ticketsByName[$key] ?? [])->unique()->implode(', ')) {
            return $tickets;
        }
        if ($storedPerTraveller) {
            $name = strtoupper(trim(preg_replace('/\s+/', ' ', ($t['ti'] ?? '').' '.($t['fN'] ?? '').' '.($t['lN'] ?? ''))));
            $entry = collect($storedTickets)->first(fn ($e) => strtoupper($e['name'] ?? '') === $name);

            return collect($entry['tickets'] ?? [])->unique()->implode(', ');
        }

        return $i === 0 ? collect($storedTickets)->unique()->implode(', ') : '';
    };

    $paymentPurpose = fn (?string $p) => match ($p) {
        'booking' => 'Ticket payment',
        'flight_confirm_book' => 'Payment for reserved seat',
        'flight_ssr' => 'Seats / meals / baggage',
        'flight_reissue' => 'Date change',
        default => ucfirst(str_replace('_', ' ', (string) $p)),
    };
    $paymentStatus = fn (?string $s) => match ($s) {
        'captured' => 'Paid',
        'refunded' => 'Refunded in full',
        'partially_refunded' => 'Partly refunded',
        'failed' => 'Failed',
        'created', 'authorized' => 'Not completed',
        default => ucfirst((string) $s),
    };
    $amendmentOutcome = fn (?string $o) => match ($o) {
        'SUCCESS' => 'Done, refund paid',
        'SUCCESS_NO_REFUND' => 'Done, no refund due',
        'SUCCESS_REFUND_FAILED' => 'Done, refund needs manual action',
        'REJECTED' => 'Rejected by airline',
        default => (string) $o,
    };
@endphp

<x-filament-panels::page>
    <div class="tyt-fbv">
        {{-- Status --}}
        <div class="tyt-fbv-banner" style="background: {{ $tone[1] }}; border-color: {{ $tone[0] }}33;">
            <div>
                <h2 style="color: {{ $tone[0] }};">{{ $status['label'] }}</h2>
                <p>{{ $status['help'] }}</p>
                @if($record->status === 'on_hold' && $record->tripjack_hold_expires_at)
                    <p><strong>Guest must pay by {{ $record->tripjack_hold_expires_at->format('D, j M Y, g:i A') }}</strong> ({{ $record->tripjack_hold_expires_at->diffForHumans() }}).</p>
                @endif
            </div>
            <div class="tyt-fbv-ids">
                <span>Booking No.</span><strong>{{ $record->reference }}</strong>
                @if($pnrs->isNotEmpty())
                    <span>Airline PNR</span><strong>{{ $pnrs->implode(', ') }}</strong>
                @endif
            </div>
        </div>

        @if($record->admin_note || $record->cancellation_reason)
            <div class="tyt-fbv-card tyt-fbv-notes">
                <h3>Notes</h3>
                @if($record->cancellation_reason)
                    <p><strong>Cancellation:</strong> {{ $record->cancellation_reason }}</p>
                @endif
                @if($record->admin_note)
                    <p style="white-space: pre-line;">{{ $record->admin_note }}</p>
                @endif
            </div>
        @endif

        <div class="tyt-fbv-layout">
            <div>
                {{-- Journey --}}
                <div class="tyt-fbv-card">
                    <h3>Journey</h3>
                    <div class="tyt-fbv-stats">
                        <div><span>Trip type</span><strong>{{ $record->flight_journey_type === 'RETURN' ? 'Round trip' : 'One way' }}</strong></div>
                        <div><span>Travel date</span><strong>{{ $record->flight_departure_date?->format('D, j M Y') ?? '—' }}</strong></div>
                        @if($record->flight_return_date)
                            <div><span>Return date</span><strong>{{ $record->flight_return_date->format('D, j M Y') }}</strong></div>
                        @endif
                        <div><span>Class</span><strong>{{ ucwords(strtolower(str_replace('_', ' ', $record->flight_cabin_class ?? 'Economy'))) }}</strong></div>
                        <div><span>Travellers</span><strong>{{ $record->pax_adults }} adult{{ $record->pax_adults == 1 ? '' : 's' }}@if($record->pax_children), {{ $record->pax_children }} child{{ $record->pax_children == 1 ? '' : 'ren' }}@endif @if($record->pax_infants), {{ $record->pax_infants }} infant{{ $record->pax_infants == 1 ? '' : 's' }}@endif</strong></div>
                    </div>

                    @if($segments)
                        <table class="tyt-fbv-table">
                            <thead><tr><th>Flight</th><th>From</th><th>To</th></tr></thead>
                            <tbody>
                                @foreach($segments as $seg)
                                    <tr>
                                        <td><strong>{{ $seg['airline'] ?: $seg['airlineCode'] }}</strong><br><small>{{ $seg['airlineCode'] }} {{ $seg['flightNo'] }}</small></td>
                                        <td><strong>{{ $seg['from'] }}</strong> {{ $seg['fromCity'] }}<br><small>{{ $time($seg['departs']) }}</small></td>
                                        <td><strong>{{ $seg['to'] }}</strong> {{ $seg['toCity'] }}<br><small>{{ $time($seg['arrives']) }}</small></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <table class="tyt-fbv-table">
                            <thead><tr><th>From</th><th>To</th><th>Date</th></tr></thead>
                            <tbody>
                                @forelse($legs as $leg)
                                    <tr><td><strong>{{ $leg['src'] ?? '' }}</strong></td><td><strong>{{ $leg['dest'] ?? '' }}</strong></td><td>{{ isset($leg['departureDate']) ? \Illuminate\Support\Carbon::parse($leg['departureDate'])->format('D, j M Y') : '—' }}</td></tr>
                                @empty
                                    <tr><td colspan="3">{{ str_replace('-', ' → ', (string) $record->flight_route) }}</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                        @if($record->tripjack_booking_id)
                            <p class="tyt-fbv-fine">Airline name and flight times appear here after you click "Check status with airline".</p>
                        @endif
                    @endif
                </div>

                {{-- Passengers --}}
                <div class="tyt-fbv-card">
                    <h3>Passengers</h3>
                    <table class="tyt-fbv-table">
                        <thead><tr><th>Name</th><th>Type</th><th>Ticket number</th><th>Seat / meal / baggage</th></tr></thead>
                        <tbody>
                            @forelse($travellers as $i => $t)
                                @php $tickets = $ticketsFor($t, $i); @endphp
                                <tr>
                                    <td><strong>{{ trim(($t['ti'] ?? '').' '.($t['fN'] ?? '').' '.($t['lN'] ?? '')) }}</strong></td>
                                    <td>{{ $paxType($t) }}</td>
                                    <td>{{ $tickets ?: '—' }}</td>
                                    <td><small>{{ $extras($t) }}</small></td>
                                </tr>
                            @empty
                                <tr><td colspan="4">{{ $record->lead_guest_name ?: 'No passenger details saved.' }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- History --}}
                @if(count($record->flight_partial_amendments ?? []) || count($record->flight_reissue_history ?? []) || $record->flight_ssr_status)
                    <div class="tyt-fbv-card">
                        <h3>Changes after booking</h3>
                        <table class="tyt-fbv-table">
                            <thead><tr><th>What</th><th>Result</th><th>Refund</th><th>When</th></tr></thead>
                            <tbody>
                                @foreach($record->flight_partial_amendments ?? [] as $a)
                                    <tr>
                                        <td>{{ ($a['type'] ?? '') === 'VOIDED' ? 'Same-day free cancellation' : 'Part of the booking cancelled' }}
                                            @if(! empty($a['trips']))
                                                <br><small>{{ collect($a['trips'])->map(fn ($tr) => ($tr['src'] ?? '').'→'.($tr['dest'] ?? ''))->implode(', ') }}</small>
                                            @endif
                                        </td>
                                        <td>{{ $amendmentOutcome($a['outcome'] ?? null) }}</td>
                                        <td>{{ $money($a['refundAmount'] ?? 0) }}</td>
                                        <td>{{ isset($a['resolvedAt']) ? \Illuminate\Support\Carbon::parse($a['resolvedAt'])->format('j M Y, g:i A') : '—' }}</td>
                                    </tr>
                                @endforeach
                                @foreach($record->flight_reissue_history ?? [] as $r)
                                    <tr>
                                        <td>Travel date changed<br><small>{{ isset($r['oldDepartureDate']) ? \Illuminate\Support\Carbon::parse($r['oldDepartureDate'])->format('j M') : '?' }} → {{ isset($r['newDepartureDate']) ? \Illuminate\Support\Carbon::parse($r['newDepartureDate'])->format('j M Y') : '?' }}</small></td>
                                        <td>Done (guest paid {{ $money($r['amountPaid'] ?? 0) }})</td>
                                        <td>—</td>
                                        <td>{{ isset($r['resolvedAt']) ? \Illuminate\Support\Carbon::parse($r['resolvedAt'])->format('j M Y, g:i A') : ($record->flight_reissued_at?->format('j M Y, g:i A') ?? '—') }}</td>
                                    </tr>
                                @endforeach
                                @if($record->flight_ssr_status)
                                    <tr>
                                        <td>Seats / meals / baggage added</td>
                                        <td>{{ match ($record->flight_ssr_status) { 'pending' => 'Being added by the airline', 'confirmed' => 'Added', 'partially_confirmed' => 'Partly added, the rest refunded', 'refunded' => 'Not added, money refunded', 'failed' => 'Failed, money refunded', 'needs_review' => 'Needs your attention', default => ucfirst(str_replace('_', ' ', $record->flight_ssr_status)) } }}</td>
                                        <td>—</td>
                                        <td>—</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            <div>
                {{-- Money --}}
                <div class="tyt-fbv-card">
                    <h3>Money</h3>
                    <div class="tyt-fbv-row tyt-fbv-total"><span>Guest paid</span><strong>{{ $money($record->total_amount) }}</strong></div>
                    @if((float) $record->tripjack_total_price > 0)
                        <div class="tyt-fbv-row"><span>Airline ticket cost (paid from TripJack wallet)</span><strong>{{ $money($record->tripjack_total_price) }}</strong></div>
                        <div class="tyt-fbv-row tyt-fbv-earn"><span>Your earning</span><strong>{{ $money($record->margin_amount) }}</strong></div>
                        <div class="tyt-fbv-row"><span>GST on your earning</span><strong>{{ $money($record->gst_on_margin) }}</strong></div>
                        <div class="tyt-fbv-row"><span>Online payment charge</span><strong>{{ $money($record->razorpay_recovery) }}</strong></div>
                    @endif
                </div>

                <div class="tyt-fbv-card">
                    <h3>Payments</h3>
                    @forelse($record->payments->sortBy('created_at') as $payment)
                        <div class="tyt-fbv-pay">
                            <div><strong>{{ $paymentPurpose($payment->purpose) }}</strong><small>{{ $payment->created_at->format('j M Y, g:i A') }}</small></div>
                            <div style="text-align: right;"><strong>{{ $money($payment->amount) }}</strong><small>{{ $paymentStatus($payment->status) }}@if((float) $payment->refund_amount > 0) · {{ $money($payment->refund_amount) }} back to guest @endif</small></div>
                        </div>
                    @empty
                        <p class="tyt-fbv-fine">No payment has been made for this booking.</p>
                    @endforelse
                </div>

                <div class="tyt-fbv-card">
                    <h3>Guest contact</h3>
                    <div class="tyt-fbv-row"><span>Name</span><strong>{{ $record->lead_guest_name ?: ($record->user?->name ?? '—') }}</strong></div>
                    <div class="tyt-fbv-row"><span>Phone</span><strong><a href="tel:{{ $record->guest_phone }}">{{ $record->guest_phone ?: '—' }}</a></strong></div>
                    <div class="tyt-fbv-row"><span>Email</span><strong><a href="mailto:{{ $record->guest_email }}">{{ $record->guest_email ?: '—' }}</a></strong></div>
                    <div class="tyt-fbv-row"><span>Booked on</span><strong>{{ $record->created_at->format('j M Y, g:i A') }}</strong></div>
                    @if($record->tripjack_booking_id)
                        <div class="tyt-fbv-row"><span>TripJack booking ID<br><small>(quote this to TripJack support)</small></span><strong>{{ $record->tripjack_booking_id }}</strong></div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <style>
        .tyt-fbv { color: #1f2430; }
        .dark .tyt-fbv { color: #eceef2; }
        .tyt-fbv-banner { display: flex; justify-content: space-between; gap: 16px; flex-wrap: wrap; padding: 18px 20px; border-radius: 12px; border: 1px solid; margin-bottom: 16px; }
        .tyt-fbv-banner h2 { font-size: 19px; font-weight: 700; margin: 0 0 4px; }
        .tyt-fbv-banner p { margin: 2px 0 0; font-size: 13.5px; color: #333; max-width: 640px; }
        .tyt-fbv-ids { display: grid; grid-template-columns: auto auto; gap: 2px 12px; align-items: baseline; font-size: 12px; color: #555; }
        .tyt-fbv-ids strong { font-size: 15px; color: #1f2430; }
        .tyt-fbv-layout { display: grid; grid-template-columns: minmax(0, 2fr) minmax(280px, 1fr); gap: 20px; align-items: start; }
        @media (max-width: 900px) { .tyt-fbv-layout { grid-template-columns: 1fr; } }
        .tyt-fbv-card { background: #fff; border: 1px solid #e8e5db; border-radius: 12px; padding: 18px 20px; margin-bottom: 20px; }
        .dark .tyt-fbv-card { background: #1f2430; border-color: #333a4d; }
        .tyt-fbv-card h3 { font-size: 15px; font-weight: 700; margin: 0 0 12px; }
        .tyt-fbv-notes { border-left: 4px solid #c9a84c; }
        .tyt-fbv-notes p { margin: 0 0 6px; font-size: 13.5px; }
        .tyt-fbv-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(130px, 1fr)); gap: 10px; margin-bottom: 14px; }
        .tyt-fbv-stats div { background: #faf6ea; border-radius: 8px; padding: 10px 12px; }
        .dark .tyt-fbv-stats div { background: #262c3a; }
        .tyt-fbv-stats span { display: block; font-size: 11px; color: #8a7a4a; font-weight: 600; text-transform: uppercase; margin-bottom: 3px; }
        .tyt-fbv-stats strong { font-size: 14px; }
        .tyt-fbv-table { width: 100%; border-collapse: collapse; font-size: 13px; }
        .tyt-fbv-table th { text-align: left; font-size: 11px; text-transform: uppercase; color: #8a7a4a; border-bottom: 2px solid #eee; padding: 8px; }
        .tyt-fbv-table td { padding: 9px 8px; border-bottom: 1px solid #eee; vertical-align: top; }
        .dark .tyt-fbv-table td, .dark .tyt-fbv-table th { border-color: #333a4d; }
        .tyt-fbv-table small { color: #777; }
        .tyt-fbv-fine { font-size: 12px; color: #888; margin: 10px 0 0; }
        .tyt-fbv-row { display: flex; justify-content: space-between; gap: 12px; padding: 8px 0; border-bottom: 1px dashed #eee; font-size: 13px; }
        .tyt-fbv-row:last-child { border-bottom: none; }
        .tyt-fbv-row strong { text-align: right; }
        .tyt-fbv-row small { color: #888; }
        .dark .tyt-fbv-row { border-color: #333a4d; }
        .tyt-fbv-total { font-size: 15px; font-weight: 700; }
        .tyt-fbv-earn strong { color: #16a34a; }
        .tyt-fbv-pay { display: flex; justify-content: space-between; gap: 12px; padding: 8px 0; border-bottom: 1px dashed #eee; font-size: 13px; }
        .tyt-fbv-pay:last-child { border-bottom: none; }
        .tyt-fbv-pay small { display: block; color: #888; font-size: 12px; }
        .dark .tyt-fbv-pay { border-color: #333a4d; }
    </style>
</x-filament-panels::page>
