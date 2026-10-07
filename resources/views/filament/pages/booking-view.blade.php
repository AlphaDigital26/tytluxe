@php
    $record = $this->record;
    $hotel = $record->hotel;
    $nights = $record->check_in && $record->check_out
        ? \Illuminate\Support\Carbon::parse($record->check_in)->diffInDays(\Illuminate\Support\Carbon::parse($record->check_out))
        : null;
    $travelerNames = $record->travelers->pluck('full_name')->filter()->implode(', ') ?: $record->lead_guest_name;
    $plain = \App\Support\HotelBookingStatus::for($record);
    [$fg, $bg] = match ($plain['color']) {
        'success' => ['#1a7f4b', '#e7f6ee'],
        'warning' => ['#b3790a', '#fdf3df'],
        'danger' => ['#9c1c1c', '#fbe9e9'],
        'info' => ['#1d4ed8', '#e8efff'],
        default => ['#555', '#f1f1f1'],
    };
    $statusMeta = ['label' => $plain['label'], 'color' => $fg, 'bg' => $bg];
@endphp

<x-filament-panels::page>
    <div class="tyt-bkv" style="--tyt-gold: #c9a84c;">
        {{-- Status banner --}}
        <div class="tyt-bkv-banner" style="background: {{ $statusMeta['bg'] }}; border-color: {{ $statusMeta['color'] }}22;">
            <div class="tyt-bkv-banner-left">
                <span class="tyt-bkv-check" style="background: {{ $statusMeta['color'] }};">{{ $plain['color'] === 'success' ? '✓' : ($plain['color'] === 'danger' ? '!' : 'i') }}</span>
                <div>
                    <h2 style="color: {{ $statusMeta['color'] }};">{{ $statusMeta['label'] }}</h2>
                    <p class="tyt-bkv-banner-note">{{ $plain['help'] }}</p>
                    @if($record->status === 'pending_payment' && $record->tripjack_hold_expires_at)
                        <p class="tyt-bkv-banner-note">Complete payment before <strong>{{ $record->tripjack_hold_expires_at->format('j F') }}</strong> by <strong>{{ $record->tripjack_hold_expires_at->format('g:i A') }}</strong> to avoid automatic cancellation.</p>
                    @endif
                </div>
            </div>
            <div class="tyt-bkv-bookingid">
                <span>Booking ID</span>
                <strong>{{ $record->tripjack_booking_id ?: $record->reference }}</strong>
            </div>
        </div>

        @if($record->status === 'pending_payment' && $record->tripjack_hold_expires_at)
            <div class="tyt-bkv-countdown" data-deadline="{{ $record->tripjack_hold_expires_at->toIso8601String() }}">
                <p class="tyt-bkv-countdown-label">Complete payment within</p>
                <div class="tyt-bkv-countdown-boxes">
                    <div><strong data-unit="days">--</strong><span>Days</span></div>
                    <div><strong data-unit="hours">--</strong><span>Hours</span></div>
                    <div><strong data-unit="minutes">--</strong><span>Minutes</span></div>
                    <div><strong data-unit="seconds">--</strong><span>Seconds</span></div>
                </div>
            </div>
        @endif

        @if($record->admin_note)
            <div class="tyt-bkv-card" style="border-left: 4px solid var(--tyt-gold);">
                <h3 class="tyt-bkv-card-title">Notes</h3>
                <p style="white-space: pre-line; margin: 0; font-size: 13.5px;">{{ $record->admin_note }}</p>
            </div>
        @endif

        <div class="tyt-bkv-layout">
            {{-- Left column --}}
            <div class="tyt-bkv-main">
                <div class="tyt-bkv-card">
                    <h3 class="tyt-bkv-hotel-name">
                        {{ $hotel->title ?? '— Hotel not in local catalogue —' }}
                        @if($hotel?->star_rating)
                            <span class="tyt-bkv-stars">{{ str_repeat('★', (int) $hotel->star_rating) }}</span>
                        @endif
                    </h3>
                    @if($hotel?->address)
                        <p class="tyt-bkv-address">{{ $hotel->address }}</p>
                    @endif

                    <div class="tyt-bkv-stat-grid">
                        <div class="tyt-bkv-stat">
                            <span>Check in</span>
                            <strong>{{ $record->check_in ? \Illuminate\Support\Carbon::parse($record->check_in)->format('d-m-Y') : '—' }}</strong>
                            @if($hotel?->check_in_time)<small>{{ $hotel->check_in_time }}</small>@endif
                        </div>
                        <div class="tyt-bkv-stat">
                            <span>Check out</span>
                            <strong>{{ $record->check_out ? \Illuminate\Support\Carbon::parse($record->check_out)->format('d-m-Y') : '—' }}</strong>
                            @if($hotel?->check_out_time)<small>{{ $hotel->check_out_time }}</small>@endif
                        </div>
                        <div class="tyt-bkv-stat">
                            <span>Total Rooms</span>
                            <strong>1</strong>
                        </div>
                        <div class="tyt-bkv-stat">
                            <span>Total Guests</span>
                            <strong>{{ $record->pax_adults }} Adult{{ $record->pax_adults === 1 ? '' : 's' }}{{ $record->pax_children ? ', '.$record->pax_children.' Child'.($record->pax_children === 1 ? '' : 'ren') : '' }}</strong>
                        </div>
                        <div class="tyt-bkv-stat">
                            <span>Total Stay</span>
                            <strong>{{ $nights !== null ? $nights.' Night'.($nights === 1 ? '' : 's') : '—' }}</strong>
                        </div>
                    </div>

                    <div class="tyt-bkv-room-row">
                        <div>
                            <strong>{{ $record->room_name ?: '— Room name not recorded —' }}</strong>
                            @if($record->meal_basis)<span class="tyt-bkv-meal">Incl: {{ $record->meal_basis }}</span>@endif
                        </div>
                        <div>
                            <span>Total Guest: {{ $record->pax_adults }} Adult{{ $record->pax_children ? ', '.$record->pax_children.' Child' : '' }}</span>
                            <span>Name: {{ $travelerNames ?: '—' }}</span>
                        </div>
                    </div>

                    @if($record->special_requests)
                        <div class="tyt-bkv-section">
                            <h4>Special Request(s)</h4>
                            <p>{{ $record->special_requests }}</p>
                        </div>
                    @endif

                    <div class="tyt-bkv-section">
                        <h4>Contact Details</h4>
                        <p>Email: {{ $record->guest_email }}</p>
                        <p>Mobile: {{ $record->guest_phone }}</p>
                    </div>
                </div>

                {{-- Cancellation policy --}}
                <div class="tyt-bkv-card">
                    <h3 class="tyt-bkv-card-title">Cancellation Policy</h3>

                    @if($this->penaltySchedule)
                        <table class="tyt-bkv-table">
                            <thead>
                                <tr>
                                    <th>Cancellation on or After</th>
                                    <th>Cancellation on or Before</th>
                                    <th>Cancellation Charges/Comments</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($this->penaltySchedule as $slab)
                                    <tr>
                                        <td>{{ $slab['from'] }}</td>
                                        <td>{{ $slab['to'] }}</td>
                                        <td>{{ $record->currency }} {{ number_format($slab['amount'], 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <p class="tyt-bkv-fineprint">From TripJack, last checked {{ $this->penaltyFetchedAt }}. Charges shown are calculated per the schedule above and may include non-refundable taxes/fees per the property's own policy.</p>
                    @else
                        <p class="tyt-bkv-fineprint">{{ $this->penaltyError ?? 'No cancellation schedule available.' }}</p>
                    @endif
                </div>

                {{-- Booking notes / property policies --}}
                @if($hotel && ($hotel->know_before_you_go || $hotel->special_instructions || $hotel->mandatory_fees || $hotel->house_rules))
                    <div class="tyt-bkv-card">
                        <h3 class="tyt-bkv-card-title">Booking Notes</h3>

                        @if($hotel->know_before_you_go)
                            <div class="tyt-bkv-section">
                                <h4>Know Before You Go</h4>
                                <div class="tyt-bkv-html">{!! $hotel->know_before_you_go !!}</div>
                            </div>
                        @endif

                        @if($hotel->special_instructions)
                            <div class="tyt-bkv-section">
                                <h4>Checkin Instructions</h4>
                                <div class="tyt-bkv-html">{!! $hotel->special_instructions !!}</div>
                            </div>
                        @endif

                        @if($hotel->mandatory_fees)
                            <div class="tyt-bkv-section">
                                <h4>Fees</h4>
                                <div class="tyt-bkv-html">{!! $hotel->mandatory_fees !!}</div>
                            </div>
                        @endif

                        @if($hotel->house_rules)
                            <div class="tyt-bkv-section">
                                <h4>General Terms &amp; Conditions</h4>
                                <div class="tyt-bkv-html">{!! is_string($hotel->house_rules) ? $hotel->house_rules : '' !!}</div>
                            </div>
                        @endif
                    </div>
                @endif
            </div>

            {{-- Right column: fare summary --}}
            <div class="tyt-bkv-side">
                <div class="tyt-bkv-card tyt-bkv-fare">
                    <h3 class="tyt-bkv-card-title">Fare Summary</h3>
                    <div class="tyt-bkv-fare-row">
                        <span>Base Fare</span>
                        <strong>{{ $record->currency }} {{ number_format($record->base_amount, 2) }}</strong>
                    </div>
                    <div class="tyt-bkv-fare-row">
                        <span>Taxes and fees</span>
                        <strong>{{ $record->currency }} {{ number_format($record->tax_amount, 2) }}</strong>
                    </div>
                    @if((float) $record->discount_amount > 0)
                        <div class="tyt-bkv-fare-row">
                            <span>Discount</span>
                            <strong>- {{ $record->currency }} {{ number_format($record->discount_amount, 2) }}</strong>
                        </div>
                    @endif
                    <div class="tyt-bkv-fare-row tyt-bkv-fare-total">
                        <span>Total Amount Payable</span>
                        <strong>{{ $record->currency }} {{ number_format($record->total_amount, 2) }}</strong>
                    </div>
                </div>

                <div class="tyt-bkv-card">
                    <h3 class="tyt-bkv-card-title">Payments &amp; Refunds</h3>
                    @forelse($record->payments->sortBy('created_at') as $payment)
                        <div class="tyt-bkv-fare-row" style="align-items: flex-start;">
                            <span>
                                {{ $payment->created_at->format('j M Y, g:i A') }}<br>
                                <small style="color:#888;">
                                    {{ match ($payment->status) {
                                        'captured' => 'Paid',
                                        'refunded' => 'Refunded in full',
                                        'partially_refunded' => 'Partly refunded',
                                        'failed' => 'Failed',
                                        default => 'Not completed',
                                    } }}
                                    @if((float) $payment->refund_amount > 0) · {{ $record->currency }} {{ number_format($payment->refund_amount, 2) }} back to guest @endif
                                </small>
                            </span>
                            <strong>{{ $record->currency }} {{ number_format($payment->amount, 2) }}</strong>
                        </div>
                    @empty
                        <p class="tyt-bkv-fineprint">No payment has been made for this booking.</p>
                    @endforelse
                    @if($record->payments->contains(fn ($p) => $p->refund_reason))
                        <p class="tyt-bkv-fineprint" style="margin-top: 8px;">Refund note: {{ $record->payments->pluck('refund_reason')->filter()->last() }}</p>
                    @endif
                </div>
                <div class="tyt-bkv-card">
                    <h3 class="tyt-bkv-card-title">Booking Status</h3>
                    <div class="tyt-bkv-fare-row">
                        <span>Reference</span>
                        <strong>{{ $record->reference }}</strong>
                    </div>
                    <div class="tyt-bkv-fare-row">
                        <span>Status</span>
                        <strong>{{ $plain['label'] }}</strong>
                    </div>
                    <div class="tyt-bkv-fare-row">
                        <span>Booked On</span>
                        <strong>{{ $record->created_at->format('M j, Y, g:i A') }}</strong>
                    </div>
                    @if($record->cancellation_reason)
                        <div class="tyt-bkv-fare-row" style="flex-direction: column; align-items: flex-start; gap: 4px;">
                            <span>Cancellation Notes</span>
                            <strong style="font-weight: 500;">{{ $record->cancellation_reason }}</strong>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <style>
        .tyt-bkv { color: #1f2430; }
        .tyt-bkv-banner { display: flex; justify-content: space-between; align-items: center; gap: 16px; padding: 18px 20px; border-radius: 12px; border: 1px solid; margin-bottom: 16px; flex-wrap: wrap; }
        .tyt-bkv-banner-left { display: flex; align-items: center; gap: 14px; }
        .tyt-bkv-check { width: 34px; height: 34px; border-radius: 999px; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 18px; flex-shrink: 0; }
        .tyt-bkv-banner h2 { font-size: 18px; font-weight: 700; margin: 0; }
        .tyt-bkv-banner-note { margin: 4px 0 0; font-size: 13px; color: #444; }
        .tyt-bkv-bookingid { text-align: right; font-size: 13px; color: #555; }
        .tyt-bkv-bookingid strong { display: block; font-size: 15px; color: #1f2430; }
        .tyt-bkv-countdown { background: #fdf3df; border: 1px solid var(--tyt-gold); border-radius: 12px; padding: 14px 20px; margin-bottom: 16px; }
        .tyt-bkv-countdown-label { margin: 0 0 8px; font-size: 13px; font-weight: 600; color: #7a5c10; }
        .tyt-bkv-countdown-boxes { display: flex; gap: 12px; }
        .tyt-bkv-countdown-boxes div { background: #fff; border: 1px solid var(--tyt-gold); border-radius: 8px; padding: 8px 14px; text-align: center; min-width: 64px; }
        .tyt-bkv-countdown-boxes strong { display: block; font-size: 20px; color: var(--tyt-gold); }
        .tyt-bkv-countdown-boxes span { font-size: 11px; text-transform: uppercase; color: #888; }
        .tyt-bkv-layout { display: grid; grid-template-columns: minmax(0, 2fr) minmax(280px, 1fr); gap: 20px; align-items: start; }
        @media (max-width: 900px) { .tyt-bkv-layout { grid-template-columns: 1fr; } }
        .tyt-bkv-card { background: #fff; border: 1px solid #e8e5db; border-radius: 12px; padding: 20px; margin-bottom: 20px; }
        .dark .tyt-bkv-card { background: #1f2430; border-color: #333a4d; }
        .dark .tyt-bkv { color: #eceef2; }
        .tyt-bkv-hotel-name { font-size: 19px; font-weight: 700; margin: 0 0 4px; }
        .tyt-bkv-stars { color: var(--tyt-gold); font-size: 14px; margin-left: 8px; }
        .tyt-bkv-address { color: #777; font-size: 13px; margin: 0 0 16px; }
        .tyt-bkv-stat-grid { display: grid; grid-template-columns: repeat(5, 1fr); gap: 10px; margin-bottom: 16px; }
        @media (max-width: 700px) { .tyt-bkv-stat-grid { grid-template-columns: repeat(2, 1fr); } }
        .tyt-bkv-stat { background: #faf6ea; border-radius: 8px; padding: 10px 12px; }
        .dark .tyt-bkv-stat { background: #262c3a; }
        .tyt-bkv-stat span { display: block; font-size: 11px; color: #8a7a4a; font-weight: 600; text-transform: uppercase; margin-bottom: 4px; }
        .tyt-bkv-stat strong { display: block; font-size: 14px; }
        .tyt-bkv-stat small { display: block; font-size: 11px; color: #888; }
        .tyt-bkv-room-row { display: flex; justify-content: space-between; gap: 16px; border: 1px solid #e8e5db; border-radius: 10px; padding: 14px 16px; margin-bottom: 16px; flex-wrap: wrap; }
        .dark .tyt-bkv-room-row { border-color: #333a4d; }
        .tyt-bkv-meal { display: block; font-size: 12px; color: #777; margin-top: 2px; }
        .tyt-bkv-room-row > div:last-child { display: flex; flex-direction: column; gap: 2px; font-size: 13px; text-align: right; }
        .tyt-bkv-section { margin-top: 14px; }
        .tyt-bkv-section h4 { font-size: 13px; font-weight: 700; text-transform: uppercase; letter-spacing: .02em; color: var(--tyt-gold); margin: 0 0 6px; }
        .tyt-bkv-section p { margin: 0 0 4px; font-size: 13px; }
        .tyt-bkv-card-title { font-size: 15px; font-weight: 700; margin: 0 0 14px; }
        .tyt-bkv-table { width: 100%; border-collapse: collapse; font-size: 13px; margin-bottom: 12px; }
        .tyt-bkv-table th { text-align: left; font-size: 11px; text-transform: uppercase; color: #8a7a4a; border-bottom: 2px solid #eee; padding: 8px; }
        .tyt-bkv-table td { padding: 8px; border-bottom: 1px solid #eee; }
        .dark .tyt-bkv-table td, .dark .tyt-bkv-table th { border-color: #333a4d; }
        .tyt-bkv-fineprint { font-size: 12px; color: #888; margin: 0; }
        .tyt-bkv-html :where(ul) { padding-left: 18px; margin: 0; font-size: 13px; }
        .tyt-bkv-html :where(li) { margin-bottom: 4px; }
        .tyt-bkv-fare-row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px dashed #eee; font-size: 13px; }
        .dark .tyt-bkv-fare-row { border-color: #333a4d; }
        .tyt-bkv-fare-row:last-child { border-bottom: none; }
        .tyt-bkv-fare-total { font-weight: 700; font-size: 15px; border-top: 2px solid #eee; margin-top: 4px; padding-top: 12px; }
        .dark .tyt-bkv-fare-total { border-color: #333a4d; }
    </style>

    @if($record->status === 'pending_payment' && $record->tripjack_hold_expires_at)
        <script>
            (function () {
                var el = document.querySelector('.tyt-bkv-countdown');
                if (!el) return;
                var deadline = new Date(el.dataset.deadline).getTime();
                var days = el.querySelector('[data-unit="days"]');
                var hours = el.querySelector('[data-unit="hours"]');
                var minutes = el.querySelector('[data-unit="minutes"]');
                var seconds = el.querySelector('[data-unit="seconds"]');

                function tick() {
                    var remaining = deadline - Date.now();
                    if (remaining <= 0) {
                        days.textContent = hours.textContent = minutes.textContent = seconds.textContent = '00';
                        return;
                    }
                    days.textContent = String(Math.floor(remaining / 86400000)).padStart(2, '0');
                    hours.textContent = String(Math.floor((remaining % 86400000) / 3600000)).padStart(2, '0');
                    minutes.textContent = String(Math.floor((remaining % 3600000) / 60000)).padStart(2, '0');
                    seconds.textContent = String(Math.floor((remaining % 60000) / 1000)).padStart(2, '0');
                }
                tick();
                setInterval(tick, 1000);
            })();
        </script>
    @endif
</x-filament-panels::page>
