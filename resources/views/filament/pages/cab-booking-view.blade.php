@php
    $record = $this->record;
    $status = \App\Support\CabBookingStatus::for($record);
    $tone = match ($status['color']) {
        'success' => ['#1a7f4b', '#e7f6ee'],
        'warning' => ['#b3790a', '#fdf3df'],
        'danger' => ['#9c1c1c', '#fbe9e9'],
        default => ['#555', '#f1f1f1'],
    };
    $money = fn ($v) => '₹'.number_format((float) $v, 2);
    $time = fn ($v) => $v ? \Illuminate\Support\Carbon::parse($v)->format('D, j M Y · g:i A') : '—';
    $d = $record->cab_details ?? [];
    $o = $d['option'] ?? [];
    $live = $d['live'] ?? [];
    $words = fn ($v) => $v ? ucfirst(str_replace('_', ' ', strtolower((string) $v))) : '—';
    $paymentStatus = fn (?string $s) => match ($s) {
        'captured' => 'Paid',
        'refunded' => 'Refunded in full',
        'partially_refunded' => 'Partly refunded',
        'failed' => 'Failed',
        'created', 'authorized' => 'Not completed',
        default => ucfirst((string) $s),
    };
    $paidIn = (float) $record->payments->whereIn('status', \App\Models\Booking::PAID_PAYMENT_STATUSES)->sum('amount');
    $refunded = (float) $record->payments->sum('refund_amount');
@endphp
<x-filament-panels::page>
    <div class="tyt-fbv">
        <div class="tyt-fbv-banner" style="background: {{ $tone[1] }}; border-color: {{ $tone[0] }}33;">
            <div>
                <h2 style="color: {{ $tone[0] }};">{{ $status['label'] }}</h2>
                <p><strong>What to do:</strong> {{ $status['help'] }}</p>
            </div>
            <div class="tyt-fbv-ids">
                <span>Booking No.</span><strong>{{ $record->reference }}</strong>
                @if($record->tripjack_booking_id)
                    <span>TripJack ID</span><strong>{{ $record->tripjack_booking_id }}</strong>
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
                <div class="tyt-fbv-card">
                    <h3>Trip</h3>
                    <div class="tyt-fbv-stats">
                        <div><span>Type</span><strong>{{ \App\Services\CabBookingService::JOURNEY_TYPES[$d['journeyType'] ?? ''] ?? 'Cab' }}</strong></div>
                        <div><span>Trip</span><strong>{{ ! empty($d['roundTrip']) ? 'Round trip' : 'One way' }}</strong></div>
                        <div><span>Pickup time</span><strong>{{ $time($record->cab_pickup_at) }}</strong></div>
                        @if(!empty($d['returnAt']))
                            <div><span>Return time</span><strong>{{ $time($d['returnAt']) }}</strong></div>
                        @endif
                        <div><span>Travellers</span><strong>{{ $record->pax_adults }}</strong></div>
                    </div>
                    <table class="tyt-fbv-table">
                        <tbody>
                            <tr><td style="width: 30%;"><strong>From</strong></td><td>{{ $d['from'] ?? '—' }}</td></tr>
                            <tr><td><strong>To</strong></td><td>{{ $d['to'] ?? '—' }}</td></tr>
                            @if(!empty($d['journeyInfo']['distance']))
                                <tr><td><strong>Distance</strong></td><td>{{ $d['journeyInfo']['distance'] }}@if(!empty($d['journeyInfo']['duration'])) · about {{ $d['journeyInfo']['duration'] }} min @endif</td></tr>
                            @endif
                            <tr><td><strong>Vehicle</strong></td><td>{{ $o['label'] ?? '—' }}@if(!empty($o['similar']))<br><small>{{ $o['similar'] }} · {{ $o['seats'] ?? '?' }} seats, {{ $o['bags'] ?? '?' }} bags</small>@endif</td></tr>
                            @if(!empty($d['passenger']['flightNumber']))
                                <tr><td><strong>Guest's flight</strong></td><td>{{ strtoupper($d['passenger']['flightNumber']) }}</td></tr>
                            @endif
                            @if($record->special_requests)
                                <tr><td><strong>Special request</strong></td><td>{{ $record->special_requests }}</td></tr>
                            @endif
                        </tbody>
                    </table>
                </div>

                <div class="tyt-fbv-card">
                    <h3>Latest from TripJack</h3>
                    @if($live)
                        <div class="tyt-fbv-row"><span>Booking status</span><strong>{{ $words($live['status'] ?? null) }}</strong></div>
                        <div class="tyt-fbv-row"><span>Payment to TripJack</span><strong>{{ $words($live['paymentStatus'] ?? null) }}</strong></div>
                        <div class="tyt-fbv-row"><span>Ride</span><strong>{{ $words($live['rideStatus'] ?? null) }}</strong></div>
                        @if(!empty($live['trackingLink']))
                            <div class="tyt-fbv-row"><span>Driver tracking</span><strong><a href="{{ $live['trackingLink'] }}" target="_blank" rel="noopener noreferrer" style="color:#b3790a;">Open tracking page</a></strong></div>
                        @endif
                        @if(!empty($live['helpline']))
                            <div class="tyt-fbv-row"><span>Operator helpline</span><strong>{{ $live['helpline'] }}</strong></div>
                        @endif
                        <p class="tyt-fbv-fine">Checked {{ isset($live['checkedAt']) ? \Illuminate\Support\Carbon::parse($live['checkedAt'])->diffForHumans() : '—' }}. Click "Check status with TripJack" for the latest.</p>
                    @else
                        <p class="tyt-fbv-fine" style="margin:0;">Not checked yet.@if($record->tripjack_booking_id) Click "Check status with TripJack".@endif</p>
                    @endif
                </div>

                @if(!empty($o['policies']['cancellationPolicy']))
                    <div class="tyt-fbv-card">
                        <h3>Cancellation policy (at booking)</h3>
                        @foreach(collect($o['policies']['cancellationPolicy'])->sortByDesc('minHours') as $c)
                            <div class="tyt-fbv-row"><span>{{ $c['description'] ?? '' }}</span><strong>{{ (float) ($c['refundPercentage'] ?? 0) }}% refund</strong></div>
                        @endforeach
                    </div>
                @endif
            </div>

            <div>
                <div class="tyt-fbv-card">
                    <h3>Money</h3>
                    <div class="tyt-fbv-row tyt-fbv-total"><span>{{ $paidIn > 0 ? 'Guest paid' : 'Price (not paid)' }}</span><strong>{{ $money($paidIn > 0 ? $paidIn : $record->total_amount) }}</strong></div>
                    @if($refunded > 0)
                        <div class="tyt-fbv-row"><span>Refunded to guest</span><strong>− {{ $money($refunded) }}</strong></div>
                    @endif
                    @if($record->manual_refund_due_at && (float) $record->admin_refund_amount > 0)
                        <div class="tyt-fbv-row"><span>Refund to pay by hand</span><strong style="color:#9c1c1c;">{{ $money($record->admin_refund_amount) }}</strong></div>
                    @endif
                    @php $walletPaid = in_array($record->status, ['confirmed', 'cancelled'], true) && filled($record->tripjack_booking_id); @endphp
                    <div class="tyt-fbv-row"><span>{{ $walletPaid ? 'Cab cost (paid from TripJack wallet)' : 'Cab cost (not paid to TripJack)' }}</span><strong>{{ $money($record->tripjack_total_price) }}</strong></div>
                    <div class="tyt-fbv-row tyt-fbv-earn"><span>Your earning</span><strong>{{ $money($record->margin_amount) }}</strong></div>
                    <div class="tyt-fbv-row"><span>GST on your earning</span><strong>{{ $money($record->gst_on_margin) }}</strong></div>
                    <div class="tyt-fbv-row"><span>Online payment charge</span><strong>{{ $money($record->razorpay_recovery) }}</strong></div>
                </div>

                <div class="tyt-fbv-card">
                    <h3>Payments</h3>
                    @forelse($record->payments->sortBy('created_at') as $payment)
                        <div class="tyt-fbv-pay">
                            <div><strong>Cab payment</strong><small>{{ $payment->created_at->format('j M Y, g:i A') }}</small></div>
                            <div style="text-align: right;"><strong>{{ $money($payment->amount) }}</strong><small>{{ $paymentStatus($payment->status) }}@if((float) $payment->refund_amount > 0) · {{ $money($payment->refund_amount) }} back to guest @endif</small></div>
                        </div>
                    @empty
                        <p class="tyt-fbv-fine">No payment has been made for this booking.</p>
                    @endforelse
                </div>

                <div class="tyt-fbv-card">
                    <h3>Guest contact</h3>
                    <div class="tyt-fbv-row"><span>Name</span><strong>{{ $record->lead_guest_name ?: ($record->user?->name ?? '—') }}</strong></div>
                    <div class="tyt-fbv-row"><span>Phone</span><strong>@php $tel = $record->guest_phone ? '+'.ltrim((string) ($record->guest_phone_code ?: '91'), '+').' '.$record->guest_phone : null; @endphp<a href="tel:{{ $tel }}">{{ $tel ?: '—' }}</a></strong></div>
                    <div class="tyt-fbv-row"><span>Email</span><strong><a href="mailto:{{ $record->guest_email }}">{{ $record->guest_email ?: '—' }}</a></strong></div>
                    <div class="tyt-fbv-row"><span>Booked on</span><strong>{{ $record->created_at->format('j M Y, g:i A') }}</strong></div>
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
        .tyt-fbv-table td { padding: 9px 8px; border-bottom: 1px solid #eee; vertical-align: top; }
        .dark .tyt-fbv-table td { border-color: #333a4d; }
        .tyt-fbv-table small { color: #777; }
        .tyt-fbv-fine { font-size: 12px; color: #888; margin: 10px 0 0; }
        .tyt-fbv-row { display: flex; justify-content: space-between; gap: 12px; padding: 8px 0; border-bottom: 1px dashed #eee; font-size: 13px; }
        .tyt-fbv-row:last-child { border-bottom: none; }
        .tyt-fbv-row strong { text-align: right; }
        .dark .tyt-fbv-row { border-color: #333a4d; }
        .tyt-fbv-total { font-size: 15px; font-weight: 700; }
        .tyt-fbv-earn strong { color: #16a34a; }
        .tyt-fbv-pay { display: flex; justify-content: space-between; gap: 12px; padding: 8px 0; border-bottom: 1px dashed #eee; font-size: 13px; }
        .tyt-fbv-pay:last-child { border-bottom: none; }
        .tyt-fbv-pay small { display: block; color: #888; font-size: 12px; }
        .dark .tyt-fbv-pay { border-color: #333a4d; }
    </style>
</x-filament-panels::page>
