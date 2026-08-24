<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $package->title ?? 'Itinerary' }}</title>
    <style>
        @page {
            margin-top: 0;
            margin-bottom: 0;
            margin-left: 0;
            margin-right: 0;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            background: #ffffff;
            color: #2d2621;
            font-size: 12px;
            line-height: 1.65;
            margin-bottom: 40px;
        }

        /* ═══ Footer — fixed to bottom of every page in dompdf ═══ */
        #pdf-footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            width: 100%;
            height: 30px;
            background-color: #1e1613;
            text-align: center;
            font-size: 8.5px;
            letter-spacing: 0.12em;
            color: #8a7d6e;
            padding-top: 10px;
        }
        #pdf-footer .brand { color: #c19a6b; font-weight: bold; letter-spacing: 0.16em; }

        /* ═══ Page break ═══ */
        .page-break { page-break-after: always; }

        /* Top header bar: solid dark, separate from hero image */
        .hero-bg {
            width: 100%;
            height: 440px;
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-color: #2b2118;
        }

        .topbar-overlay {
            background-color: #1a1410;
            padding: 20px 26px;
        }

        .topbar-contact {
            font-size: 11px;
            color: #d4cfc7;
            line-height: 1.7;
            text-align: right;
            font-weight: 500;
        }

        /* Dark gradient overlay caption — bottom of hero only */
        .caption-overlay {
            background: linear-gradient(to top, rgba(0,0,0,0.9) 0%, rgba(0,0,0,0.6) 55%, rgba(0,0,0,0) 100%);
            padding: 230px 26px 20px;
        }
        .region-badge {
            display: inline-block;
            border: 1px solid rgba(255,255,255,0.55);
            border-radius: 100px;
            padding: 4px 14px;
            font-size: 9px;
            font-weight: bold;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            color: #ffffff;
            margin-bottom: 12px;
        }
        .hero-title {
            font-size: 36px;
            font-weight: bold;
            color: #ffffff;
            line-height: 1.1;
            margin-bottom: 2px;
        }
        .hero-nights {
            font-size: 36px;
            font-weight: bold;
            color: #ffffff;
            line-height: 1.1;
            margin-bottom: 14px;
        }
        .hero-tagline {
            font-size: 12.5px;
            color: #c8b9a8;
            line-height: 1.55;
            max-width: 520px;
        }

        /* Metadata bar */
        .meta-table {
            width: 100%;
            background-color: #2a201a;
            border-top: 1px solid rgba(255,255,255,0.07);
        }
        .meta-cell {
            text-align: center;
            padding: 13px 6px;
            border-right: 1px solid rgba(255,255,255,0.09);
            width: 20%;
        }
        .meta-cell:last-child { border-right: none; }
        .meta-val {
            display: block;
            font-size: 13px;
            font-weight: bold;
            color: #c19a6b;
            margin-bottom: 3px;
        }
        .meta-lbl {
            display: block;
            font-size: 7.5px;
            font-weight: bold;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            color: #6a5d50;
        }

        /* ═══ WHITE CONTENT AREA ═══ */
        .content {
            padding: 20px 26px 12px;
            background: #ffffff;
        }

        /* Eyebrow label */
        .eyebrow {
            font-size: 8.5px;
            font-weight: bold;
            letter-spacing: 0.24em;
            text-transform: uppercase;
            color: #c19a6b;
            margin-bottom: 5px;
        }

        /* Section headings */
        .section-h1 {
            font-size: 19px;
            font-weight: bold;
            color: #1a1410;
            margin-bottom: 12px;
            line-height: 1.2;
        }
        .section-h2 {
            font-size: 17px;
            font-weight: bold;
            color: #1a1410;
            margin-bottom: 12px;
            line-height: 1.2;
        }

        /* About text */
        .about-p {
            font-size: 11.5px;
            color: #4a3e35;
            line-height: 1.78;
            margin-bottom: 28px;
        }

        /* ─── Brief Itinerary rows ─── */
        .brief-separator { border: none; border-top: 1px solid #ede9e1; margin: 0; }
        .brief-row { padding: 9px 0; page-break-inside: avoid; }
        .day-pill {
            background-color: #c19a6b;
            color: #ffffff;
            font-size: 8px;
            font-weight: bold;
            letter-spacing: 0.07em;
            text-transform: uppercase;
            padding: 3px 9px;
            border-radius: 3px;
            white-space: nowrap;
        }
        .brief-text {
            font-size: 11.5px;
            color: #3b3028;
            padding-left: 12px;
            vertical-align: middle;
        }

        /* ─── Day Cards (page 2) ─── */
        .day-card {
            border: 1px solid #e3ddd4;
            border-radius: 7px;
            overflow: hidden;
            margin-bottom: 14px;
            background: #ffffff;
            page-break-inside: avoid;
        }
        .day-card-img {
            display: block;
            width: 148px;
            min-width: 148px;
            height: 130px;
            object-fit: cover;
        }
        .day-card-head {
            background-color: #f6f2eb;
            padding: 9px 14px;
            border-bottom: 1px solid #e3ddd4;
        }
        .day-card-day-lbl {
            font-size: 8px;
            font-weight: bold;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: #c19a6b;
        }
        .day-card-title {
            font-size: 13px;
            font-weight: bold;
            color: #1a1410;
            padding-left: 10px;
            vertical-align: middle;
        }
        .day-card-body {
            padding: 11px 14px;
            font-size: 11.5px;
            color: #3b3028;
            line-height: 1.72;
        }
        .day-card-body strong, .day-card-body b { color: #1a1410; font-weight: bold; }

        /* ─── Travel Dates ─── */
        .dates-cell { width: 33%; vertical-align: top; padding-right: 10px; }
        .dates-cell:last-child { padding-right: 0; }
        .dates-card {
            border: 1px solid #e3ddd4;
            border-top: 2px solid #c19a6b;
            border-radius: 0 0 6px 6px;
            background-color: #faf7f1;
            padding: 14px 14px 16px;
            page-break-inside: avoid;
        }
        .dates-month {
            font-size: 8px;
            font-weight: bold;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            color: #c19a6b;
            margin-bottom: 9px;
        }
        .dates-item {
            font-size: 11.5px;
            color: #3b3028;
            margin-bottom: 5px;
        }

        /* ─── Inclusions / Exclusions ─── */
        .incl-head {
            font-size: 12px;
            font-weight: bold;
            padding-bottom: 7px;
            margin-bottom: 11px;
        }
        .incl-head.green { color: #1b7a3d; border-bottom: 2px solid #1b7a3d; }
        .incl-head.red   { color: #b03232; border-bottom: 2px solid #b03232; }
        .incl-item { font-size: 11.5px; color: #3b3028; margin-bottom: 7px; }
        .mark-g { color: #1b7a3d; margin-right: 6px; }
        .mark-r { color: #b03232; margin-right: 6px; }

        /* ─── Price Card ─── */
        .price-card {
            background-color: #1e1613;
            border-radius: 10px;
            padding: 20px 22px;
            margin-bottom: 26px;
            color: #ffffff;
            page-break-inside: avoid;
        }
        .price-amount {
            font-size: 22px;
            font-weight: bold;
            color: #c19a6b;
        }
        .price-sub {
            font-size: 9.5px;
            color: #8a7d6e;
            margin-top: 4px;
        }
        .price-booking-lbl { font-size: 9.5px; color: #c0b0a0; text-align: right; }
        .price-booking-val { font-size: 9.5px; font-weight: bold; color: #c19a6b; }

        /* ─── Payment Pills ─── */
        .pay-pill {
            border: 1px solid #d4cfc7;
            border-radius: 5px;
            padding: 9px 14px;
            text-align: center;
            font-size: 11.5px;
            font-weight: bold;
            color: #3b3028;
            background: #ffffff;
        }

        /* ─── Contact Card ─── */
        .contact-card {
            background-color: #f6f2eb;
            border-radius: 9px;
            padding: 14px 20px 10px;
            margin-bottom: 14px;
        }
        .contact-title {
            font-size: 15px;
            font-weight: bold;
            color: #1a1410;
            margin-bottom: 16px;
        }
        .contact-lbl {
            font-size: 7.5px;
            font-weight: bold;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            color: #c19a6b;
            margin-bottom: 3px;
        }
        .contact-val {
            font-size: 12px;
            font-weight: bold;
            color: #1a1410;
            margin-bottom: 12px;
            line-height: 1.55;
        }
        .social-divider { border: none; border-top: 1px solid #e3ddd4; margin: 12px 0 10px; }
        .social-pill {
            display: inline-block;
            border: 1px solid #dedad2;
            border-radius: 100px;
            padding: 5px 14px;
            font-size: 10.5px;
            font-weight: bold;
            color: #3b3028;
            background: #ffffff;
            margin-right: 5px;
        }
        .social-dot { color: #c19a6b; font-size: 8px; margin-right: 5px; }

        /* ─── Notes ─── */
        .notes-divider { border: none; border-top: 1px solid #e8e3da; margin: 20px 0 14px; }
        .notes-title { font-size: 13px; font-weight: bold; color: #1a1410; margin-bottom: 9px; }
        .notes-item { font-size: 10.5px; color: #5c5148; margin-bottom: 6px; padding-left: 2px; }
        .notes-bullet { color: #3b3028; margin-right: 7px; }

    </style>
</head>
<body>

    @php
        $package->loadMissing(['itineraryDays', 'inclusions', 'exclusions', 'departures', 'images']);

        // Allowed inline tags for rich-text day descriptions
        $allowedInlineTags = '<strong><b><em><i><br>';

        // Cover image – absolute local path so dompdf can read it
        $coverImg = null;
        if (!empty($package->hero_bg_image) && file_exists(public_path('storage/' . $package->hero_bg_image))) {
            $coverImg = public_path('storage/' . $package->hero_bg_image);
        } else {
            $firstImg = $package->images->sortBy('sort_order')->first();
            if ($firstImg && !empty($firstImg->path) && file_exists(public_path('storage/' . $firstImg->path))) {
                $coverImg = public_path('storage/' . $firstImg->path);
            }
        }

        // Logo
        $logoPath   = public_path('assets/images/tyt-logo.png');
        $logoExists = file_exists($logoPath);

        // Departure
        $departureCity = is_array($package->departure_from)
            ? implode(', ', array_filter($package->departure_from))
            : ($package->departure_from ?? 'Delhi');

        // Destination name
        $destination = $package->destination?->name
            ?? preg_replace('/\s*[|\/]\s*\d+\s*(Nights?|Days?).*/i', '', $package->title ?? 'Destination');

        // Duration
        $nights = (int)($package->duration_nights ?? 0);
        $days   = $nights + 1;

        // Tagline
        $tagline = $package->tagline ?? '';
    @endphp


    {{-- ════════════════════════════════════════════════════════
         PAGE 1 — COVER
    ════════════════════════════════════════════════════════ --}}
    <div class="page-break">

        {{-- TOP DARK HEADER BAR: separate from the hero image --}}
        <div class="topbar-overlay">
            <table width="100%" cellpadding="0" cellspacing="0">
                <tr>
                    <td valign="middle">
                        @if($logoExists)
                            <img src="{{ $logoPath }}" alt="TYT Luxe" style="height:75px; object-fit:contain;">
                        @else
                            <span style="font-size:18px; font-weight:bold; color:#c19a6b; letter-spacing:0.08em;">TYT</span>
                        @endif
                    </td>
                    <td valign="middle" class="topbar-contact">
                        +91 98750 73788<br>
                        takeyourtrip7@gmail.com<br>
                        www.tytluxe.in
                    </td>
                </tr>
            </table>
        </div>

        {{-- HERO IMAGE with text overlay at bottom only --}}
        <div class="hero-bg" style="background-image: url('{{ $coverImg }}'); position: relative;">
            {{-- Bottom gradient + text --}}
            <div class="caption-overlay">
                @if(!empty($package->hero_eyebrow) || !empty($package->region_type))
                    <div class="region-badge">{{ $package->hero_eyebrow ?? strtoupper($package->region_type ?? '') }}</div>
                    <br style="line-height:4px;">
                @endif
                <div class="hero-title">{{ $destination }}</div>
                <div class="hero-nights">{{ $nights }} Nights / {{ $days }} Days</div>
                @if($tagline)
                    <div class="hero-tagline">{{ $tagline }}</div>
                @endif
            </div>
        </div>


        {{-- Metadata bar --}}
        <table class="meta-table" cellpadding="0" cellspacing="0">
            <tr>
                <td class="meta-cell">
                    <span class="meta-val">{{ $nights }}N / {{ $days }}D</span>
                    <span class="meta-lbl">Duration</span>
                </td>
                <td class="meta-cell">
                    <span class="meta-val">{{ $departureCity }}</span>
                    <span class="meta-lbl">Departure</span>
                </td>
                <td class="meta-cell">
                    <span class="meta-val">{{ $package->meals_info ?? 'B & D' }}</span>
                    <span class="meta-lbl">Meals</span>
                </td>
                <td class="meta-cell">
                    <span class="meta-val">{{ $package->transport_info ?? 'Volvo / TT' }}</span>
                    <span class="meta-lbl">Transport</span>
                </td>
                <td class="meta-cell">
                    <span class="meta-val">&#8377;{{ number_format($package->price_from ?? 0) }}</span>
                    <span class="meta-lbl">Starting From</span>
                </td>
            </tr>
        </table>

        {{-- About + Brief itinerary --}}
        <div class="content">

            <div class="eyebrow">Discover</div>
            <div class="section-h1">About {{ $destination }}</div>
            <p class="about-p">{{ strip_tags($package->description ?? '') }}</p>

            @if($package->itineraryDays->count() > 0)
                <div class="eyebrow">Day By Day</div>
                <div class="section-h2">Brief Itinerary</div>
                @foreach($package->itineraryDays as $day)
                    <hr class="brief-separator">
                    <div class="brief-row">
                        <table width="100%" cellpadding="0" cellspacing="0">
                            <tr>
                                <td width="55" valign="middle">
                                    <span class="day-pill">Day {{ $day->day_number }}</span>
                                </td>
                                <td valign="middle" class="brief-text">{{ $day->title }}</td>
                            </tr>
                        </table>
                    </div>
                @endforeach
                <hr class="brief-separator">
            @endif

        </div>
    </div>{{-- /PAGE 1 --}}


    {{-- ════════════════════════════════════════════════════════
         PAGE 2 — DETAILED ITINERARY + TRAVEL DATES
    ════════════════════════════════════════════════════════ --}}
    @if($package->itineraryDays->count() > 0)
    <div class="page-break">
        <div class="content">

            <div class="eyebrow">In Detail</div>
            <div class="section-h1">Your Itinerary, Day By Day</div>

            @foreach($package->itineraryDays as $day)
                @php
                    // Only use the image explicitly uploaded for this day.
                    // No fallback to cover image — if none uploaded, show no image.
                    $dayImgPath = ($day->image && file_exists(public_path('storage/' . $day->image)))
                                  ? public_path('storage/' . $day->image)
                                  : null;
                @endphp
                <div class="day-card">
                    <table width="100%" cellpadding="0" cellspacing="0">
                        <tr>
                            @if($dayImgPath)
                            <td style="width:148px; padding:0; vertical-align:top;">
                                <img src="{{ $dayImgPath }}" class="day-card-img" alt="Day {{ $day->day_number }}">
                            </td>
                            @endif
                            <td style="padding:0; vertical-align:top;">
                                {{-- Header row --}}
                                <div class="day-card-head">
                                    <table width="100%" cellpadding="0" cellspacing="0">
                                        <tr>
                                            <td width="52" valign="middle">
                                                <span class="day-card-day-lbl">DAY {{ $day->day_number }}</span>
                                            </td>
                                            <td valign="middle" class="day-card-title">{{ $day->title }}</td>
                                        </tr>
                                    </table>
                                </div>
                                {{-- Body --}}
                                <div class="day-card-body">
                                    {!! nl2br(strip_tags($day->description ?? '', $allowedInlineTags)) !!}
                                </div>
                            </td>
                        </tr>
                    </table>
                </div>
            @endforeach

            {{-- ─── Travel Dates ─── --}}
            @if($package->departures->count() > 0)
                <div style="margin-top:26px;">
                    <div class="eyebrow">Plan Your Trip</div>
                    <div class="section-h2">Travel Dates</div>

                    @php
                        $grouped = $package->departures
                            ->sortBy('start_date')
                            ->groupBy(fn($d) => \Carbon\Carbon::parse($d->start_date)->format('F Y'))
                            ->take(2);
                    @endphp

                    <table width="100%" cellpadding="0" cellspacing="0">
                        <tr>
                            @foreach($grouped as $month => $deps)
                            <td class="dates-cell">
                                <div class="dates-card">
                                    <div class="dates-month">{{ strtoupper($month) }}</div>
                                    @foreach($deps as $dep)
                                        <div class="dates-item">
                                            {{ \Carbon\Carbon::parse($dep->start_date)->format('d M') }}
                                            &ndash;
                                            {{ \Carbon\Carbon::parse($dep->end_date)->format('d M') }}
                                        </div>
                                    @endforeach
                                </div>
                            </td>
                            @endforeach
                            <td class="dates-cell" style="padding-right:0;">
                                <div class="dates-card">
                                    <div class="dates-month">More Dates</div>
                                    <div class="dates-item">New batches added regularly</div>
                                    <div class="dates-item">Contact us to check availability</div>
                                </div>
                            </td>
                        </tr>
                    </table>
                </div>
            @endif

        </div>
    </div>{{-- /PAGE 2 --}}
    @endif


    {{-- ════════════════════════════════════════════════════════
         PAGE 3 — INCLUSIONS · PRICING · BOOKING · CONTACT
    ════════════════════════════════════════════════════════ --}}
    <div>
        <div class="content">

            {{-- ─── Inclusions & Exclusions ─── --}}
            <div class="eyebrow">What's Covered</div>
            <div class="section-h1">Inclusions &amp; Exclusions</div>

            <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:30px;">
                <tr>
                    <td width="49%" valign="top" style="padding-right:20px;">
                        <div class="incl-head green">&#10003; What's Included</div>
                        @if($package->inclusions->count() > 0)
                            @foreach($package->inclusions as $item)
                                <div class="incl-item"><span class="mark-g">&#10003;</span>{{ $item->label }}</div>
                            @endforeach
                        @else
                            <div class="incl-item"><span class="mark-g">&#10003;</span>Transportation: Delhi to Delhi (Volvo / Tempo Traveller)</div>
                            <div class="incl-item"><span class="mark-g">&#10003;</span>Surface transfers for sightseeing as per the itinerary</div>
                            <div class="incl-item"><span class="mark-g">&#10003;</span>Accommodation in hotel</div>
                            <div class="incl-item"><span class="mark-g">&#10003;</span>Meal plan based on MAP (Breakfast &amp; Dinner)</div>
                            <div class="incl-item"><span class="mark-g">&#10003;</span>Driver allowance</div>
                            <div class="incl-item"><span class="mark-g">&#10003;</span>Toll taxes and other state taxes</div>
                        @endif
                    </td>
                    <td width="2%"></td>
                    <td width="49%" valign="top">
                        <div class="incl-head red">&#10007; What's Excluded</div>
                        @if($package->exclusions->count() > 0)
                            @foreach($package->exclusions as $item)
                                <div class="incl-item"><span class="mark-r">&#10007;</span>{{ $item->name }}</div>
                            @endforeach
                        @else
                            <div class="incl-item"><span class="mark-r">&#10007;</span>Early check-in request charges at the hotel</div>
                            <div class="incl-item"><span class="mark-r">&#10007;</span>Any additional expenses of a personal nature</div>
                            <div class="incl-item"><span class="mark-r">&#10007;</span>Anything not specifically mentioned in the inclusions</div>
                            <div class="incl-item"><span class="mark-r">&#10007;</span>Additional accommodation / food costs due to delayed travel</div>
                            <div class="incl-item"><span class="mark-r">&#10007;</span>Parking and monument entry fees during sightseeing</div>
                            <div class="incl-item"><span class="mark-r">&#10007;</span>Emergency services, if opted (payable on the spot)</div>
                        @endif
                    </td>
                </tr>
            </table>

            {{-- ─── Package Price ─── --}}
            <div class="eyebrow">Pricing</div>
            <div class="section-h2">Package Price</div>

            <div class="price-card">
                <table width="100%" cellpadding="0" cellspacing="0">
                    <tr>
                        <td valign="middle">
                            <div class="price-amount">&#8377;{{ number_format($package->price_from ?? 0) }} / person</div>
                            <div class="price-sub">Starting from &middot; inclusive of all taxes &middot; ex. {{ $departureCity }}</div>
                        </td>
                        @if($package->booking_amount)
                        <td valign="middle" style="text-align:right;">
                            <div class="price-booking-lbl">Booking Amount: <span class="price-booking-val">&#8377;{{ number_format($package->booking_amount) }} / person</span></div>
                            <div class="price-booking-lbl" style="margin-top:5px;">Balance payable before departure</div>
                        </td>
                        @endif
                    </tr>
                </table>
            </div>

            {{-- ─── Booking Process ─── --}}
            <div class="eyebrow">How To Book</div>
            <div class="section-h2">Booking Process</div>
            <p style="font-size:11.5px; color:#3b3028; line-height:1.75; margin-bottom:16px;">
                To book this package, simply reach out to us on WhatsApp or call us directly. A booking amount of
                &#8377;{{ number_format($package->booking_amount ?? 2000) }} per person is required to confirm your seat.
                Our team will then share the full itinerary and payment details with you.
            </p>

            {{-- Payment method pills --}}
            <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:24px;">
                <tr>
                    <td class="pay-pill">Bank Transfer</td>
                    <td width="12"></td>
                    <td class="pay-pill" style="text-align:center;">GPay / PhonePe</td>
                    <td width="12"></td>
                    <td class="pay-pill" style="text-align:center;">Paytm / UPI</td>
                </tr>
            </table>

            {{-- ─── Get In Touch ─── --}}
            <div class="contact-card">
                <div class="contact-title">Get In Touch</div>
                <table width="100%" cellpadding="0" cellspacing="0">
                    <tr>
                        <td width="50%" valign="top" style="padding-right:18px;">
                            <div class="contact-lbl">Phone</div>
                            <div class="contact-val">+91 98750 73788</div>
                            <div class="contact-lbl">Website</div>
                            <div class="contact-val">www.tytluxe.in</div>
                        </td>
                        <td width="50%" valign="top">
                            <div class="contact-lbl">Email</div>
                            <div class="contact-val">takeyourtrip7@gmail.com</div>
                            <div class="contact-lbl">Address</div>
                            <div class="contact-val">Surana Supremus, 4th Floor, Cabin No - 9, Near Safal Square, Vesu, Surat 394518</div>
                        </td>
                    </tr>
                </table>

                <hr class="social-divider">
                <span class="social-pill"><span class="social-dot">&#9679;</span>Instagram @tytluxe_</span>
                <span class="social-pill"><span class="social-dot">&#9679;</span>WhatsApp +91 98750 73788</span>
                <span class="social-pill"><span class="social-dot">&#9679;</span>Facebook /TYTLuxe</span>
            </div>

            {{-- ─── Notes ─── --}}
            @if(!empty($package->notes) && is_array($package->notes) && count($package->notes) > 0)
            <hr class="notes-divider">
            <div class="notes-title">Notes</div>
            @foreach($package->notes as $note)
                <div class="notes-item"><span class="notes-bullet">&#8226;</span>{{ $note }}</div>
            @endforeach
            @endif

        </div>
    </div>{{-- /PAGE 3 --}}

    {{-- ─── Footer at the end of the document ─── --}}
    <div id="pdf-footer">
        <span class="brand">TYT LUXE</span>
        &nbsp;&nbsp;|&nbsp;&nbsp;
        +91 98750 73788
        &nbsp;&nbsp;|&nbsp;&nbsp;
        takeyourtrip7@gmail.com
        &nbsp;&nbsp;|&nbsp;&nbsp;
        www.tytluxe.in
    </div>

</body>
</html>