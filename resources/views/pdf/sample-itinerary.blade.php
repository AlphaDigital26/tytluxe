<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $package->title ?? 'Itinerary' }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: Arial, Helvetica, sans-serif;
            background: #ffffff;
            color: #2d2621;
            font-size: 11px;
            line-height: 1.6;
            width: 794px;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        /* ─── Hero block: photo with topbar + gradient caption overlaid on top ─── */
        .hero {
            position: relative;
            width: 100%;
            height: 500px;
            overflow: hidden;
        }
        .cover-crop {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 500px;
            overflow: hidden;
        }
        .cover-crop img { width: 100%; height: 100%; object-fit: cover; display: block; }
        .cover-placeholder {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 500px;
            background-color: #362a20;
        }

        .hero-gradient {
            position: absolute;
            left: 0;
            bottom: 0;
            width: 100%;
            height: 260px;
            background: linear-gradient(to top, rgba(8,6,5,0.82) 0%, rgba(8,6,5,0.45) 45%, rgba(8,6,5,0) 100%);
        }

        .hero-top-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 210px;
            background: linear-gradient(to bottom, rgba(0,0,0,0.88) 0%, rgba(0,0,0,0.75) 30%, rgba(0,0,0,0.30) 65%, rgba(0,0,0,0) 100%);
        }

        /* ─── Topbar overlaid on hero photo ─── */
        .topbar {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            padding: 10px 26px;
            background: rgba(0, 0, 0, 0.62);
        }
        .topbar-contact {
            font-size: 9.5px;
            color: #ffffff;
            text-align: right;
            line-height: 1.65;
        }

        /* ─── Caption overlaid on the faded bottom of the hero photo ─── */
        .caption-band {
            position: absolute;
            left: 0;
            bottom: 0;
            width: 100%;
            padding: 20px 26px 20px;
        }
        .region-badge {
            display: inline-block;
            border: 1px solid rgba(255,255,255,0.5);
            border-radius: 100px;
            background: rgba(0,0,0,0.35);
            padding: 3px 13px;
            font-size: 8.5px;
            font-weight: bold;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            color: #ffffff;
            margin-bottom: 9px;
        }
        .caption-title {
            font-size: 28px;
            font-weight: bold;
            color: #ffffff;
            line-height: 1.18;
            margin-bottom: 4px;
        }
        .caption-nights {
            font-size: 16px;
            font-weight: bold;
            color: #ffffff;
            line-height: 1.1;
            margin-bottom: 6px;
        }
        .caption-tagline {
            font-size: 10.5px;
            color: rgba(255,255,255,0.88);
            line-height: 1.55;
            max-width: 520px;
        }

        /* ─── Meta bar ─── */
        .meta-table {
            width: 100%;
            background: #1e1a16;
            border-top: 1px solid rgba(255,255,255,0.06);
        }
        .meta-cell {
            text-align: center;
            padding: 11px 6px;
            border-right: 1px solid rgba(255,255,255,0.08);
            width: 20%;
        }
        .meta-cell:last-child { border-right: none; }
        .meta-val {
            display: block;
            font-size: 11.5px;
            font-weight: bold;
            color: #c19a6b;
            margin-bottom: 3px;
        }
        .meta-lbl {
            display: block;
            font-size: 7px;
            font-weight: bold;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            color: #5a5048;
        }

        /* ─── Main white content area ─── */
        .content {
            background: #ffffff;
            padding: 22px 26px 4px;
        }

        /* ─── Eyebrow / section labels ─── */
        .eyebrow {
            font-size: 8px;
            font-weight: bold;
            letter-spacing: 0.26em;
            text-transform: uppercase;
            color: #c19a6b;
            margin-bottom: 4px;
        }
        .section-h1 {
            font-size: 18px;
            font-weight: bold;
            color: #1a1410;
            margin-bottom: 10px;
            line-height: 1.2;
        }
        .section-h2 {
            font-size: 15px;
            font-weight: bold;
            color: #1a1410;
            margin-bottom: 10px;
            line-height: 1.2;
        }

        /* ─── About text ─── */
        .about-p {
            font-size: 11px;
            color: #4a3e35;
            line-height: 1.75;
            margin-bottom: 20px;
        }

        /* ─── Brief itinerary list ─── */
        .brief-sep { border: none; border-top: 1px solid #ede9e1; margin: 0; }
        .brief-row { padding: 8px 0; }
        .day-pill {
            background: #c19a6b;
            color: #fff;
            font-size: 7.5px;
            font-weight: bold;
            letter-spacing: 0.07em;
            text-transform: uppercase;
            padding: 3px 9px;
            border-radius: 3px;
            white-space: nowrap;
        }
        .brief-text {
            font-size: 11px;
            color: #3b3028;
            padding-left: 12px;
            vertical-align: middle;
        }

        /* ─── Detailed day cards ─── */
        .day-card {
            border: 1px solid #e3ddd4;
            border-radius: 7px;
            overflow: hidden;
            margin-bottom: 14px;
            background: #ffffff;
        }
        .day-card-img-crop {
            width: 148px;
            min-width: 148px;
            height: 130px;
            overflow: hidden;
        }
        .day-card-img-crop img { width: 100%; height: 100%; object-fit: cover; display: block; }
        .day-card-head {
            background: #f6f2eb;
            padding: 9px 14px;
            border-bottom: 1px solid #e3ddd4;
        }
        .day-card-day-lbl {
            font-size: 7.5px;
            font-weight: bold;
            letter-spacing: 0.1em;
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
            font-size: 10.5px;
            color: #3b3028;
            line-height: 1.72;
        }

        /* ─── Travel dates ─── */
        .dates-card {
            border: 1px solid #e3ddd4;
            border-top: 2px solid #c19a6b;
            border-radius: 0 0 6px 6px;
            background: #faf7f1;
            padding: 13px 14px 15px;
        }
        .dates-month {
            font-size: 7.5px;
            font-weight: bold;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            color: #c19a6b;
            margin-bottom: 8px;
        }
        .dates-item {
            font-size: 11px;
            color: #3b3028;
            margin-bottom: 4px;
        }

        /* ─── Inclusions / Exclusions ─── */
        .io-heading {
            font-size: 11.5px;
            font-weight: bold;
            padding-bottom: 6px;
            border-bottom: 2px solid;
            margin-bottom: 10px;
        }
        .io-heading.incl { color: #2f7d4f; border-color: #2f7d4f; }
        .io-heading.excl { color: #b23b3b; border-color: #b23b3b; }
        .io-row { font-size: 10.5px; color: #3b3028; line-height: 1.6; margin-bottom: 7px; }
        .io-mark { font-weight: bold; padding-right: 6px; }
        .io-mark.incl { color: #2f7d4f; }
        .io-mark.excl { color: #b23b3b; }

        /* ─── Pricing box ─── */
        .price-box {
            background: #1e1613;
            border-radius: 8px;
            padding: 18px 22px;
            margin: 4px 0 20px;
        }
        .price-amount { font-size: 22px; font-weight: bold; color: #c19a6b; margin-bottom: 3px; }
        .price-note { font-size: 9px; color: #a89a89; }
        .price-side { font-size: 10px; color: #cfc4b7; text-align: right; line-height: 1.7; }
        .price-side strong { color: #ffffff; }

        /* ─── Booking text ─── */
        .booking-p {
            font-size: 10.5px;
            color: #3b3028;
            line-height: 1.75;
            margin: 16px 0 18px;
        }
        .pay-pill {
            display: inline-block;
            width: 31.5%;
            text-align: center;
            border: 1px solid #dedad2;
            border-radius: 6px;
            padding: 9px 6px;
            font-size: 10px;
            font-weight: bold;
            color: #3b3028;
            margin-right: 2%;
        }

        /* ─── Get In Touch card ─── */
        .contact-card {
            background: #f6f2eb;
            border-radius: 9px;
            padding: 16px 20px 12px;
            margin-bottom: 16px;
        }
        .contact-card-title {
            font-size: 15px;
            font-weight: bold;
            color: #1a1410;
            margin-bottom: 14px;
        }
        .contact-lbl {
            font-size: 7.5px;
            font-weight: bold;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            color: #c19a6b;
            margin-bottom: 2px;
        }
        .contact-val {
            font-size: 11.5px;
            font-weight: bold;
            color: #1a1410;
            margin-bottom: 11px;
            line-height: 1.5;
        }
        .social-sep { border: none; border-top: 1px solid #e3ddd4; margin: 10px 0 9px; }
        .social-pill {
            display: inline-block;
            border: 1px solid #dedad2;
            border-radius: 100px;
            padding: 5px 14px;
            font-size: 10px;
            font-weight: bold;
            color: #3b3028;
            background: #ffffff;
            margin-right: 5px;
        }
        .social-dot { color: #c19a6b; font-size: 8px; margin-right: 4px; }

        /* ─── Notes ─── */
        .notes-title { font-size: 10.5px; font-weight: bold; color: #1a1410; margin-bottom: 8px; }
        .notes-list { font-size: 9.5px; color: #5c5148; line-height: 1.7; }
        .notes-list div { padding-left: 12px; position: relative; margin-bottom: 3px; }
        .notes-dot { position: absolute; left: 0; }

        /* ─── Footer ─── */
        .footer {
            background: #1e1613;
            text-align: center;
            font-size: 8.5px;
            letter-spacing: 0.06em;
            color: #8a7d6e;
            padding: 12px 20px;
        }
        .footer .brand { color: #c19a6b; font-weight: bold; letter-spacing: 0.16em; }

        /* Section spacing */
        .mt16 { margin-top: 16px; }
        .mt20 { margin-top: 20px; }
    </style>
</head>
<body>

@php
    $package->loadMissing(['itineraryDays', 'inclusions', 'exclusions', 'departures', 'images']);

    $allowedInlineTags = '<strong><b><em><i><br>';

    // Embed images as base64 data URIs
    $toDataUri = function (?string $path): ?string {
        if (!$path || !file_exists($path)) return null;
        $mime = match (strtolower(pathinfo($path, PATHINFO_EXTENSION))) {
            'png'  => 'image/png',
            'webp' => 'image/webp',
            'gif'  => 'image/gif',
            default => 'image/jpeg',
        };
        return 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($path));
    };

    // Cover image
    $coverImg = null;
    $coverImgAbsPath = null;
    if (!empty($package->hero_bg_image) && file_exists(public_path('storage/' . $package->hero_bg_image))) {
        $coverImgAbsPath = public_path('storage/' . $package->hero_bg_image);
        $coverImg = $toDataUri($coverImgAbsPath);
    } else {
        $firstImg = $package->images->sortBy('sort_order')->first();
        if ($firstImg) {
            $imgPath = $firstImg->image_path ?? $firstImg->path ?? null;
            if ($imgPath && file_exists(public_path('storage/' . $imgPath))) {
                $coverImgAbsPath = public_path('storage/' . $imgPath);
                $coverImg = $toDataUri($coverImgAbsPath);
            }
        }
    }

    // Logo
    $logoPath   = $toDataUri(public_path('assets/images/tyt-logo.png'));
    $logoExists = !empty($logoPath);

    // Package meta
    $departureCity = is_array($package->departure_from)
        ? implode(', ', array_filter($package->departure_from))
        : ($package->departure_from ?? 'Delhi');

    $destination = $package->destination?->name
        ?? preg_replace('/\s*[|\/]\s*\d+\s*(Nights?|Days?).*/i', '', $package->title ?? 'Destination');

    $nights  = (int)($package->duration_nights ?? 0);
    $days    = $nights + 1;

    // Short tagline under the hero title, derived from the description
    $plainDesc = trim(strip_tags($package->description ?? ''));
    $tagline   = null;
    if ($plainDesc) {
        $tagline = mb_strlen($plainDesc) > 130 ? mb_substr($plainDesc, 0, 127) . '...' : $plainDesc;
    }

    // Group departures by month (show max 2 months + "More Dates")
    $grouped = $package->departures
        ->sortBy('start_date')
        ->groupBy(fn($d) => \Carbon\Carbon::parse($d->start_date)->format('F Y'))
        ->take(2);

    $notes = is_array($package->notes) ? array_filter($package->notes) : [];
@endphp

{{-- ══════════════════ HERO: photo + overlaid topbar + overlaid caption ══════════════════ --}}
<div class="hero">
    @if($coverImg)
        <div class="cover-crop">
            <img src="{{ $coverImg }}" alt="{{ $destination }}">
        </div>
    @else
        <div class="cover-placeholder"></div>
    @endif

    <div class="hero-gradient"></div>
    <div class="hero-top-overlay"></div>

    <table width="100%" cellpadding="0" cellspacing="0" class="topbar">
        <tr>
            <td valign="middle">
                @if($logoExists)
                    <img src="{{ $logoPath }}" alt="TYT Luxe" style="height:70px; object-fit:contain;">
                @else
                    <span style="font-size:17px; font-weight:bold; color:#ffffff; letter-spacing:0.08em;">TYT LUXE</span>
                @endif
            </td>
            <td valign="middle" class="topbar-contact">
                +91 98750 73788<br>
                takeyourtrip7@gmail.com<br>
                www.tytluxe.in
            </td>
        </tr>
    </table>

    <div class="caption-band">
        @if(!empty($package->hero_eyebrow) || !empty($package->region_type) || $package->destination)
            <div class="region-badge">
                {{ $package->hero_eyebrow ?? strtoupper($package->destination?->name ?? $package->region_type ?? 'India') }}
            </div><br>
        @endif
        <div class="caption-title">{{ $package->title }}</div>
        <div class="caption-nights">{{ $nights }} Nights / {{ $days }} Days</div>
    </div>
</div>

{{-- ══════════════════ META BAR ══════════════════ --}}
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
            <span class="meta-val">{{ $package->meals_info ?? 'B &amp; D' }}</span>
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

{{-- ══════════════════ MAIN WHITE CONTENT ══════════════════ --}}
<div class="content">

    {{-- ABOUT --}}
    @if($package->description)
        <div class="eyebrow">Discover</div>
        <div class="section-h1">About {{ $destination }}</div>
        <p class="about-p">{{ $plainDesc }}</p>
    @endif

    {{-- BRIEF ITINERARY --}}
    @if($package->itineraryDays->count() > 0)
        <div class="eyebrow">Day By Day</div>
        <div class="section-h2">Brief Itinerary</div>
        @foreach($package->itineraryDays as $day)
            <hr class="brief-sep">
            <div class="brief-row">
                <table width="100%" cellpadding="0" cellspacing="0">
                    <tr>
                        <td width="55" valign="middle">
                            <span class="day-pill">{{ $day->day_number == 0 ? 'DEP' : 'Day ' . $day->day_number }}</span>
                        </td>
                        <td valign="middle" class="brief-text">{{ $day->title }}</td>
                    </tr>
                </table>
            </div>
        @endforeach
        <hr class="brief-sep">
    @endif

    {{-- DETAILED ITINERARY (day cards with image) --}}
    @if($package->itineraryDays->count() > 0)
        <div class="mt20">
            <div class="eyebrow">In Detail</div>
            <div class="section-h2">Your Itinerary, Day By Day</div>
            @foreach($package->itineraryDays as $day)
                @php
                    $dayImgAbsPath = ($day->image && file_exists(public_path('storage/' . $day->image)))
                                  ? public_path('storage/' . $day->image)
                                  : null;
                    $dayImgPath = $dayImgAbsPath ? $toDataUri($dayImgAbsPath) : null;
                @endphp
                <div class="day-card">
                    <table width="100%" cellpadding="0" cellspacing="0">
                        <tr>
                            @if($dayImgPath)
                            <td style="width:148px; padding:0; vertical-align:top;">
                                <div class="day-card-img-crop">
                                    <img src="{{ $dayImgPath }}" alt="Day {{ $day->day_number }}">
                                </div>
                            </td>
                            @endif
                            <td style="padding:0; vertical-align:top;">
                                <div class="day-card-head">
                                    <table width="100%" cellpadding="0" cellspacing="0">
                                        <tr>
                                            <td width="52" valign="middle">
                                                <span class="day-card-day-lbl">{{ $day->day_number == 0 ? 'DEP' : 'DAY ' . $day->day_number }}</span>
                                            </td>
                                            <td valign="middle" class="day-card-title">{{ $day->title }}</td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="day-card-body">
                                    {!! nl2br(strip_tags($day->description ?? '', $allowedInlineTags)) !!}
                                </div>
                            </td>
                        </tr>
                    </table>
                </div>
            @endforeach
        </div>
    @endif

    {{-- TRAVEL DATES --}}
    @if($package->departures->count() > 0)
        <div class="eyebrow mt16">Plan Your Trip</div>
        <div class="section-h2">Travel Dates</div>
        <table width="100%" cellpadding="0" cellspacing="0">
            <tr>
                @foreach($grouped as $month => $deps)
                <td style="width:33%; vertical-align:top; padding-right:10px;">
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
                <td style="width:33%; vertical-align:top; padding-right:0;">
                    <div class="dates-card">
                        <div class="dates-month">More Dates</div>
                        <div class="dates-item">New batches added regularly</div>
                        <div class="dates-item">Contact us to check availability</div>
                    </div>
                </td>
            </tr>
        </table>
    @endif

    {{-- INCLUSIONS/EXCLUSIONS + PRICING + BOOKING + CONTACT + NOTES --}}
    <div class="mt20">

        {{-- INCLUSIONS / EXCLUSIONS --}}
        @if($package->inclusions->count() > 0 || $package->exclusions->count() > 0)
            <div class="eyebrow">What's Covered</div>
            <div class="section-h2">Inclusions &amp; Exclusions</div>
            <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom: 18px;">
                <tr>
                    <td width="50%" valign="top" style="padding-right: 16px;">
                        <div class="io-heading incl">&#10003; What's Included</div>
                        @foreach($package->inclusions as $inc)
                            <div class="io-row"><span class="io-mark incl">&#10003;</span>{{ $inc->label }}</div>
                        @endforeach
                    </td>
                    <td width="50%" valign="top">
                        <div class="io-heading excl">&#10007; What's Excluded</div>
                        @foreach($package->exclusions as $exc)
                            <div class="io-row"><span class="io-mark excl">&#10007;</span>{{ $exc->name }}</div>
                        @endforeach
                    </td>
                </tr>
            </table>
        @endif

        {{-- PRICING --}}
        @if($package->price_from)
            <div class="eyebrow">Pricing</div>
            <div class="section-h2">Package Price</div>
            <div class="price-box">
                <table width="100%" cellpadding="0" cellspacing="0">
                    <tr>
                        <td valign="middle">
                            <div class="price-amount">&#8377;{{ number_format($package->price_from) }} / person</div>
                            <div class="price-note">Starting from &middot; inclusive of all taxes &middot; ex. {{ $departureCity }}</div>
                        </td>
                        @if($package->booking_amount)
                            <td valign="middle" class="price-side">
                                Booking Amount: <strong>&#8377;{{ number_format($package->booking_amount) }} / person</strong><br>
                                Balance payable before departure
                            </td>
                        @endif
                    </tr>
                </table>
            </div>
        @endif

        {{-- BOOKING PROCESS --}}
        <div class="eyebrow">How To Book</div>
        <div class="section-h2">Booking Process</div>
        <p class="booking-p">
            To book this package, simply reach out to us on WhatsApp or call us directly.
            @if($package->booking_amount)
                A booking amount of <strong>&#8377;{{ number_format($package->booking_amount) }} per person</strong> is required to confirm your seat.
            @endif
            Our team will then share the full itinerary and payment details with you.
        </p>
        <div style="margin-bottom: 18px;">
            <span class="pay-pill">Bank Transfer</span><span class="pay-pill">GPay / PhonePe</span><span class="pay-pill" style="margin-right:0;">Paytm / UPI</span>
        </div>

        {{-- GET IN TOUCH CARD --}}
        <div class="contact-card">
            <div class="contact-card-title">Get In Touch</div>
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
                        <div class="contact-val" style="font-size:10.5px;">
                            Surana Supremus, 4th Floor, Cabin No - 9,<br>
                            Near Safal Square, Vesu, Surat 394518
                        </div>
                    </td>
                </tr>
            </table>
            <hr class="social-sep">
            <span class="social-pill"><span class="social-dot">&#9679;</span>Instagram @tytluxe_</span>
            <span class="social-pill"><span class="social-dot">&#9679;</span>WhatsApp +91 98750 73788</span>
            <span class="social-pill"><span class="social-dot">&#9679;</span>Facebook /TYTLuxe</span>
        </div>

        {{-- NOTES --}}
        @if(count($notes) > 0)
            <div class="notes-title">Notes</div>
            <div class="notes-list">
                @foreach($notes as $note)
                    <div><span class="notes-dot">&#8226;</span>{{ $note }}</div>
                @endforeach
            </div>
        @endif

    </div>

    <div id="end-marker" style="height:1px;"></div>

</div>{{-- /content --}}

{{-- ══════════════════ DARK FOOTER ══════════════════ --}}
<div class="footer">
    <span class="brand">TYT LUXE</span>
    &nbsp;&nbsp;|&nbsp;&nbsp;
    Surana Supremus, 4th Floor, Vesu, Surat 394518
    &nbsp;&nbsp;|&nbsp;&nbsp;
    www.tytluxe.in
</div>

</body>
</html>
