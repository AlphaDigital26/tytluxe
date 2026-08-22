<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $package->title ?? 'Itinerary' }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            background: #ffffff;
            color: #27201a;
            font-size: 13px;
            line-height: 1.6;
        }

        /* ── Fixed footer on every page ── */
        #footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            height: 36px;
            background-color: #27201a;
            text-align: center;
            font-size: 9px;
            letter-spacing: 0.1em;
            color: #9a8b7a;
            padding-top: 12px;
        }
        #footer .brand { color: #b08c45; font-weight: bold; }

        /* Page break */
        .page-break { page-break-after: always; }

        /* ── Colors ── */
        .gold  { color: #b08c45; }
        .dark  { color: #27201a; }
        .muted { color: #6b5f52; }

        /* ── Section labels ── */
        .eyebrow {
            font-size: 9px;
            font-weight: bold;
            letter-spacing: 0.22em;
            text-transform: uppercase;
            color: #b08c45;
            margin-bottom: 4px;
        }
        .section-title {
            font-size: 20px;
            font-weight: bold;
            color: #1a1612;
            margin-bottom: 14px;
        }

        /* ── Cover hero ── */
        .cover-image {
            width: 100%;
            height: 280px;
            object-fit: cover;
            display: block;
        }
        .cover-overlay {
            background-color: #27201a;
            padding: 20px 30px;
        }
        .cover-destination {
            font-size: 32px;
            font-weight: bold;
            color: #ffffff;
            letter-spacing: -0.01em;
            line-height: 1.1;
        }
        .cover-duration {
            font-size: 18px;
            font-weight: bold;
            color: #b08c45;
            margin-top: 4px;
        }

        /* ── Feature bar ── */
        .feat-bar {
            background-color: #3a2e24;
            width: 100%;
        }
        .feat-cell {
            text-align: center;
            padding: 14px 6px;
            border-right: 1px solid rgba(255,255,255,0.1);
        }
        .feat-val {
            font-size: 14px;
            font-weight: bold;
            color: #b08c45;
            display: block;
            margin-bottom: 3px;
        }
        .feat-lbl {
            font-size: 8px;
            font-weight: bold;
            letter-spacing: 0.15em;
            text-transform: uppercase;
            color: #7a6d5e;
        }

        /* ── Content area ── */
        .content { padding: 28px 30px 60px; }

        /* ── Brief itinerary row ── */
        .brief-row {
            padding: 10px 0;
            border-bottom: 1px solid #ece9e1;
        }
        .day-badge {
            background-color: #b08c45;
            color: #ffffff;
            font-size: 9px;
            font-weight: bold;
            padding: 3px 9px;
            border-radius: 3px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        /* ── Detail cards ── */
        .detail-card {
            border: 1px solid #e2ddd5;
            border-radius: 8px;
            overflow: hidden;
            margin-bottom: 16px;
        }
        .detail-card-header {
            background-color: #f7f4ee;
            padding: 10px 16px;
            border-bottom: 1px solid #e2ddd5;
        }
        .detail-card-body {
            padding: 12px 16px;
            font-size: 12px;
            color: #4a4035;
            line-height: 1.65;
        }

        /* ── Inclusions / Exclusions ── */
        .incl-header {
            font-size: 13px;
            font-weight: bold;
            padding-bottom: 8px;
            margin-bottom: 10px;
        }
        .incl-header.green { color: #16803c; border-bottom: 2px solid #16803c; }
        .incl-header.red   { color: #c0392b; border-bottom: 2px solid #c0392b; }
        .incl-li { margin-bottom: 8px; font-size: 12px; color: #3d3228; }
        .incl-mark { font-weight: bold; }
        .incl-mark.green { color: #16803c; }
        .incl-mark.red   { color: #c0392b; }

        /* ── Price card ── */
        .price-card {
            background-color: #27201a;
            border-radius: 10px;
            padding: 22px 26px;
            margin-bottom: 28px;
            color: #ffffff;
        }
        .price-val {
            font-size: 26px;
            font-weight: bold;
            color: #b08c45;
        }
        .price-sub {
            font-size: 11px;
            color: #9a8b7a;
            margin-top: 4px;
        }

        /* ── Contact block ── */
        .contact-block {
            background-color: #f7f4ee;
            border-radius: 10px;
            padding: 22px 22px 16px;
            margin-top: 24px;
        }
        .contact-label {
            font-size: 8px;
            font-weight: bold;
            letter-spacing: 0.15em;
            text-transform: uppercase;
            color: #b08c45;
            margin-bottom: 3px;
        }
        .contact-val {
            font-size: 13px;
            font-weight: bold;
            color: #27201a;
        }

        /* ── Notes ── */
        .note-bullet {
            color: #b08c45;
            font-weight: bold;
            margin-right: 6px;
        }

        /* ── Date card ── */
        .date-card {
            background-color: #f7f4ee;
            border-top: 3px solid #b08c45;
            border-radius: 0 0 6px 6px;
            padding: 16px;
        }
        .date-card-month {
            font-size: 9px;
            font-weight: bold;
            letter-spacing: 0.15em;
            text-transform: uppercase;
            color: #b08c45;
            margin-bottom: 10px;
        }
        .date-card li {
            font-size: 12px;
            color: #3d3228;
            margin-bottom: 6px;
            list-style: none;
        }

        /* Booking buttons */
        .pay-btn {
            border: 1px solid #ddd;
            border-radius: 6px;
            padding: 9px 12px;
            text-align: center;
            font-size: 12px;
            font-weight: bold;
            color: #3d3228;
        }
    </style>
</head>
<body>

    {{-- Fixed footer (renders on every page) --}}
    <div id="footer">
        <span class="brand">TYT LUXE</span>
        &nbsp;&nbsp;|&nbsp;&nbsp;
        831, Tower C, Bhutani Alphathum, Sector 90, Noida, UP - 201305
        &nbsp;&nbsp;|&nbsp;&nbsp;
        www.tytluxe.in
    </div>

    @php
        $package->loadMissing(['itineraryDays', 'inclusions', 'exclusions', 'departures', 'images']);

        // Cover image — local path for dompdf
        $firstImg   = $package->images->sortBy('sort_order')->first();
        $coverImg   = $firstImg ? public_path('storage/' . $firstImg->image) : null;

        // Logo — local path
        $logoPath   = public_path('assets/images/tyt-logo.png');
        $logoExists = file_exists($logoPath);

        // Departure city
        $departureCity = is_array($package->departure_from)
            ? implode(', ', array_filter($package->departure_from))
            : ($package->departure_from ?? 'Delhi');

        // Destination name
        $destination = $package->destination?->name
            ?? preg_replace('/\s*[|\/]\s*\d+\s*(Nights?|Days?).*/i', '', $package->title ?? 'Destination');
    @endphp


    {{-- ==================== PAGE 1: COVER ==================== --}}
    <div class="page-break">

        {{-- Hero image --}}
        @if($coverImg && file_exists($coverImg))
            <img src="{{ $coverImg }}" class="cover-image" alt="Cover">
        @else
            <div style="height:120px; background-color:#3a2e24;"></div>
        @endif

        {{-- Dark overlay with logo + title --}}
        <div class="cover-overlay">
            <table width="100%" cellpadding="0" cellspacing="0">
                <tr>
                    <td width="60%" valign="middle">
                        @if($logoExists)
                            <img src="{{ $logoPath }}" alt="TYT Luxe" style="height:40px; object-fit:contain;">
                        @else
                            <span style="font-size:20px; font-weight:bold; color:#b08c45; letter-spacing:0.1em;">TYT LUXE</span>
                        @endif
                    </td>
                    <td width="40%" valign="middle" style="text-align:right; color:#ccbbaa; font-size:11px; line-height:1.8;">
                        +91 98750 73788<br>
                        takeyourtrip7@gmail.com<br>
                        www.tytluxe.in
                    </td>
                </tr>
            </table>
            <div style="margin-top:16px;">
                @if($package->region_type)
                    <div style="display:inline-block; border:1px solid rgba(255,255,255,0.5); border-radius:999px; padding:3px 12px; font-size:9px; letter-spacing:0.12em; text-transform:uppercase; color:#ffffff; margin-bottom:8px;">
                        {{ $package->region_type }}
                    </div>
                    <br>
                @endif
                <div class="cover-destination">{{ $destination }}</div>
                <div class="cover-duration">{{ $package->duration_nights }} Nights / {{ (int)$package->duration_nights + 1 }} Days</div>
            </div>
        </div>

        {{-- Feature bar --}}
        <table class="feat-bar" width="100%" cellpadding="0" cellspacing="0">
            <tr>
                <td class="feat-cell">
                    <span class="feat-val">{{ $package->duration_nights }}N / {{ (int)$package->duration_nights + 1 }}D</span>
                    <span class="feat-lbl">Duration</span>
                </td>
                <td class="feat-cell">
                    <span class="feat-val">{{ $departureCity }}</span>
                    <span class="feat-lbl">Departure</span>
                </td>
                <td class="feat-cell">
                    <span class="feat-val">{{ $package->meals_info ?? 'B & D' }}</span>
                    <span class="feat-lbl">Meals</span>
                </td>
                <td class="feat-cell">
                    <span class="feat-val">{{ $package->transport_info ?? 'Volvo / TT' }}</span>
                    <span class="feat-lbl">Transport</span>
                </td>
                <td class="feat-cell">
                    <span class="feat-val">₹{{ number_format($package->price_from ?? 0) }}</span>
                    <span class="feat-lbl">Starting From</span>
                </td>
            </tr>
        </table>

        {{-- About section --}}
        <div class="content">
            <div class="eyebrow">Discover</div>
            <div class="section-title">About {{ $destination }}</div>
            <p style="font-size:13px; color:#4a4035; line-height:1.75; margin-bottom:28px;">
                {{ strip_tags($package->description ?? '') }}
            </p>

            @if($package->itineraryDays->count() > 0)
                <div class="eyebrow">Day By Day</div>
                <div class="section-title">Brief Itinerary</div>
                @foreach($package->itineraryDays as $day)
                    <div class="brief-row">
                        <table width="100%" cellpadding="0" cellspacing="0">
                            <tr>
                                <td width="70" valign="top" style="padding-top:2px;">
                                    <span class="day-badge">Day {{ $day->day_number }}</span>
                                </td>
                                <td valign="top" style="font-size:13px; color:#3d3228; padding-left:10px;">
                                    {{ $day->title }}
                                </td>
                            </tr>
                        </table>
                    </div>
                @endforeach
            @endif
        </div>

    </div>{{-- END PAGE 1 --}}


    {{-- ==================== PAGE 2: DETAILED ITINERARY ==================== --}}
    @if($package->itineraryDays->count() > 0)
    <div class="page-break">
        <div class="content">
            <div class="eyebrow">In Detail</div>
            <div class="section-title">Your Itinerary, Day By Day</div>

            @foreach($package->itineraryDays as $day)
                @php
                    $dayImg = ($day->image && file_exists(public_path('storage/' . $day->image)))
                        ? public_path('storage/' . $day->image)
                        : ($coverImg && file_exists($coverImg) ? $coverImg : null);
                @endphp
                <div class="detail-card">
                    @if($dayImg)
                    <div style="height:100px; overflow:hidden;">
                        <img src="{{ $dayImg }}" style="width:100%; height:100px; object-fit:cover;" alt="Day {{ $day->day_number }}">
                    </div>
                    @endif
                    <div class="detail-card-header">
                        <table width="100%" cellpadding="0" cellspacing="0">
                            <tr>
                                <td width="70" valign="middle">
                                    <span class="day-badge">Day {{ $day->day_number }}</span>
                                </td>
                                <td valign="middle" style="padding-left:10px; font-weight:bold; font-size:14px; color:#1a1612;">
                                    {{ $day->title }}
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="detail-card-body">
                        {{ strip_tags($day->description ?? '') }}
                    </div>
                </div>
            @endforeach

            {{-- Travel dates --}}
            @if($package->departures->count() > 0)
                <div style="margin-top:24px;">
                    <div class="eyebrow">Plan Your Trip</div>
                    <div class="section-title">Travel Dates</div>
                    @php
                        $grouped = $package->departures->groupBy(function($d) {
                            return \Carbon\Carbon::parse($d->start_date)->format('F Y');
                        })->take(2);
                    @endphp
                    <table width="100%" cellpadding="0" cellspacing="10">
                        <tr>
                            @foreach($grouped as $month => $deps)
                            <td width="33%" valign="top">
                                <div class="date-card">
                                    <div class="date-card-month">{{ $month }}</div>
                                    <ul>
                                        @foreach($deps as $dep)
                                            <li>{{ \Carbon\Carbon::parse($dep->start_date)->format('d M') }} – {{ \Carbon\Carbon::parse($dep->end_date)->format('d M Y') }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </td>
                            @endforeach
                            <td width="33%" valign="top">
                                <div class="date-card">
                                    <div class="date-card-month">More Dates</div>
                                    <ul>
                                        <li>New batches added regularly</li>
                                        <li style="margin-top:6px;">Contact us to check availability</li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    </table>
                </div>
            @endif
        </div>
    </div>{{-- END PAGE 2 --}}
    @endif


    {{-- ==================== PAGE 3: INCLUSIONS, PRICING, BOOKING ==================== --}}
    <div>
        <div class="content">

            {{-- Inclusions & Exclusions --}}
            <div class="eyebrow">What's Covered</div>
            <div class="section-title">Inclusions &amp; Exclusions</div>

            <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:28px;">
                <tr>
                    <td width="48%" valign="top" style="padding-right:20px;">
                        <div class="incl-header green">&#10003; &nbsp;What's Included</div>
                        @if($package->inclusions->count() > 0)
                            @foreach($package->inclusions as $item)
                                <div class="incl-li">
                                    <span class="incl-mark green">&#10003;</span>
                                    &nbsp;{{ $item->label }}
                                </div>
                            @endforeach
                        @else
                            <div class="incl-li"><span class="incl-mark green">&#10003;</span>&nbsp;Transportation as per itinerary</div>
                            <div class="incl-li"><span class="incl-mark green">&#10003;</span>&nbsp;Accommodation (double sharing)</div>
                            <div class="incl-li"><span class="incl-mark green">&#10003;</span>&nbsp;Meals as per itinerary</div>
                            <div class="incl-li"><span class="incl-mark green">&#10003;</span>&nbsp;Sightseeing as per itinerary</div>
                        @endif
                    </td>
                    <td width="4%"></td>
                    <td width="48%" valign="top">
                        <div class="incl-header red">&#10007; &nbsp;What's Excluded</div>
                        @if($package->exclusions->count() > 0)
                            @foreach($package->exclusions as $item)
                                <div class="incl-li">
                                    <span class="incl-mark red">&#10007;</span>
                                    &nbsp;{{ $item->name }}
                                </div>
                            @endforeach
                        @else
                            <div class="incl-li"><span class="incl-mark red">&#10007;</span>&nbsp;Personal expenses</div>
                            <div class="incl-li"><span class="incl-mark red">&#10007;</span>&nbsp;Travel insurance</div>
                            <div class="incl-li"><span class="incl-mark red">&#10007;</span>&nbsp;Anything not mentioned above</div>
                        @endif
                    </td>
                </tr>
            </table>

            {{-- Pricing --}}
            <div class="eyebrow">Pricing</div>
            <div class="section-title">Package Price</div>

            <div class="price-card">
                <table width="100%" cellpadding="0" cellspacing="0">
                    <tr>
                        <td valign="middle">
                            <div class="price-val">₹{{ number_format($package->price_from ?? 0) }} / person</div>
                            <div class="price-sub">Starting from &middot; inclusive of all taxes &middot; ex. {{ $departureCity }}</div>
                        </td>
                        @if($package->booking_amount)
                        <td valign="middle" style="text-align:right;">
                            <div style="font-size:11px; color:#9a8b7a;">Booking Amount</div>
                            <div style="font-size:15px; font-weight:bold; color:#ffffff;">₹{{ number_format($package->booking_amount) }}/person</div>
                            <div style="font-size:10px; color:#9a8b7a; margin-top:3px;">Balance payable before departure</div>
                        </td>
                        @endif
                    </tr>
                </table>
            </div>

            {{-- Booking Process --}}
            <div class="eyebrow">How To Book</div>
            <div class="section-title">Booking Process</div>
            <p style="font-size:13px; color:#4a4035; line-height:1.75; margin-bottom:16px;">
                To book this package, simply reach out to us on WhatsApp or call us directly. A booking amount of
                ₹{{ number_format($package->booking_amount ?? 2000) }} per person is required to confirm your seat.
                Our team will then share the full itinerary and payment details with you.
            </p>

            <table width="100%" cellpadding="6" cellspacing="6" style="margin-bottom:24px;">
                <tr>
                    <td class="pay-btn">Bank Transfer</td>
                    <td width="10"></td>
                    <td class="pay-btn">GPay / PhonePe</td>
                    <td width="10"></td>
                    <td class="pay-btn">Paytm / UPI</td>
                </tr>
            </table>

            {{-- Contact --}}
            <div class="contact-block">
                <div style="font-size:17px; font-weight:bold; color:#1a1612; margin-bottom:18px;">Get In Touch</div>
                <table width="100%" cellpadding="0" cellspacing="0">
                    <tr>
                        <td width="50%" valign="top">
                            <div class="contact-label">Phone</div>
                            <div class="contact-val" style="margin-bottom:12px;">+91 98750 73788</div>
                            <div class="contact-label">Website</div>
                            <div class="contact-val">www.tytluxe.in</div>
                        </td>
                        <td width="50%" valign="top">
                            <div class="contact-label">Email</div>
                            <div class="contact-val" style="margin-bottom:12px;">takeyourtrip7@gmail.com</div>
                            <div class="contact-label">Address</div>
                            <div class="contact-val">831, Tower C, Bhutani Alphathum,<br>Sector 90, Noida, UP - 201305</div>
                        </td>
                    </tr>
                </table>
                <div style="margin-top:16px; padding-top:14px; border-top:1px solid #e5e0d6; font-size:12px; color:#3d3228;">
                    <strong style="color:#b08c45;">&#9679;</strong> Instagram @tytluxe_
                    &nbsp;&nbsp;
                    <strong style="color:#b08c45;">&#9679;</strong> WhatsApp +91 98750 73788
                    &nbsp;&nbsp;
                    <strong style="color:#b08c45;">&#9679;</strong> Facebook /TYTLuxe
                </div>
            </div>

            {{-- Notes --}}
            @if($package->notes && is_array($package->notes) && count($package->notes) > 0)
            <div style="margin-top:24px; padding-top:18px; border-top:1px solid #e8e2d8;">
                <div style="font-size:13px; font-weight:bold; color:#1a1612; margin-bottom:10px;">Notes</div>
                @foreach($package->notes as $note)
                    <div style="font-size:11px; color:#6b5f52; margin-bottom:5px;">
                        <span class="note-bullet">&#8226;</span>{{ $note }}
                    </div>
                @endforeach
            </div>
            @endif

        </div>
    </div>{{-- END PAGE 3 --}}

</body>
</html>
