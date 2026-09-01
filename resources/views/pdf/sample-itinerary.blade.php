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
            color: #2d2621;
            font-size: 10px;
            line-height: 1.5;
        }

        /* ── Header bar ── */
        .header {
            background-color: #1e1613;
            padding: 10px 20px;
            width: 100%;
        }
        .header-logo {
            font-size: 16px;
            font-weight: bold;
            color: #c19a6b;
            letter-spacing: 0.08em;
        }
        .header-contact {
            font-size: 9px;
            color: #f0ede8;
            text-align: right;
            line-height: 1.6;
        }

        /* ── Cover image ── */
        .cover-img {
            width: 100%;
            height: 190px;
            object-fit: cover;
            display: block;
        }
        .cover-img-placeholder {
            width: 100%;
            height: 60px;
            background: #2b2118;
        }

        /* ── Caption band ── */
        .caption {
            background: #1a1410;
            padding: 12px 20px 10px;
        }
        .caption-title {
            font-size: 22px;
            font-weight: bold;
            color: #ffffff;
            line-height: 1.1;
        }
        .caption-sub {
            font-size: 13px;
            font-weight: bold;
            color: #c19a6b;
            margin-top: 2px;
        }
        .caption-tag {
            font-size: 8px;
            font-weight: bold;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            color: #ffffff;
            border: 1px solid rgba(255,255,255,0.5);
            border-radius: 100px;
            padding: 2px 10px;
            display: inline-block;
            margin-bottom: 6px;
        }

        /* ── Meta bar ── */
        .meta-table {
            width: 100%;
            background: #2a201a;
        }
        .meta-cell {
            text-align: center;
            padding: 8px 4px;
            border-right: 1px solid rgba(255,255,255,0.09);
            width: 20%;
        }
        .meta-cell:last-child { border-right: none; }
        .meta-val {
            display: block;
            font-size: 10px;
            font-weight: bold;
            color: #c19a6b;
            margin-bottom: 2px;
        }
        .meta-lbl {
            display: block;
            font-size: 7px;
            font-weight: bold;
            letter-spacing: 0.15em;
            text-transform: uppercase;
            color: #6a5d50;
        }

        /* ── Two-column body ── */
        .body-table {
            width: 100%;
        }
        .col-left  { width: 56%; vertical-align: top; padding: 14px 10px 14px 18px; }
        .col-right { width: 44%; vertical-align: top; padding: 14px 18px 14px 10px;
                     border-left: 1px solid #ede9e1; }

        /* ── Section labels ── */
        .eyebrow {
            font-size: 7.5px;
            font-weight: bold;
            letter-spacing: 0.22em;
            text-transform: uppercase;
            color: #c19a6b;
            margin-bottom: 3px;
        }
        .section-h {
            font-size: 13px;
            font-weight: bold;
            color: #1a1410;
            margin-bottom: 7px;
            line-height: 1.2;
        }
        .divider {
            border: none;
            border-top: 1px solid #ede9e1;
            margin: 10px 0;
        }

        /* ── About text ── */
        .about-p {
            font-size: 9.5px;
            color: #4a3e35;
            line-height: 1.7;
            margin-bottom: 10px;
        }

        /* ── Itinerary rows ── */
        .itin-row { padding: 5px 0; }
        .day-pill {
            background: #c19a6b;
            color: #fff;
            font-size: 7px;
            font-weight: bold;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            padding: 2px 7px;
            border-radius: 3px;
            white-space: nowrap;
        }
        .itin-title {
            font-size: 9.5px;
            color: #3b3028;
            padding-left: 8px;
            vertical-align: middle;
        }

        /* ── Price card ── */
        .price-card {
            background: #1e1613;
            border-radius: 8px;
            padding: 14px 16px;
            margin-bottom: 10px;
            color: #fff;
        }
        .price-amount {
            font-size: 18px;
            font-weight: bold;
            color: #c19a6b;
        }
        .price-sub {
            font-size: 8px;
            color: #8a7d6e;
            margin-top: 2px;
        }
        .price-booking {
            font-size: 8.5px;
            color: #c0b0a0;
            margin-top: 6px;
            line-height: 1.5;
        }
        .price-booking span { color: #c19a6b; font-weight: bold; }

        /* ── Inclusions / Exclusions ── */
        .inc-head {
            font-size: 9px;
            font-weight: bold;
            padding-bottom: 5px;
            margin-bottom: 6px;
        }
        .inc-head.green { color: #1b7a3d; border-bottom: 1.5px solid #1b7a3d; }
        .inc-head.red   { color: #b03232; border-bottom: 1.5px solid #b03232; }
        .inc-item { font-size: 9px; color: #3b3028; margin-bottom: 4px; }
        .mark-g { color: #1b7a3d; margin-right: 5px; }
        .mark-r { color: #b03232; margin-right: 5px; }

        /* ── CTA / booking ── */
        .cta-row { margin-bottom: 6px; }
        .cta-label {
            font-size: 7.5px;
            font-weight: bold;
            letter-spacing: 0.15em;
            text-transform: uppercase;
            color: #c19a6b;
            margin-bottom: 2px;
        }
        .cta-val {
            font-size: 9.5px;
            font-weight: bold;
            color: #1a1410;
            line-height: 1.4;
        }

        /* ── Footer ── */
        .footer {
            background: #1e1613;
            text-align: center;
            font-size: 8px;
            letter-spacing: 0.1em;
            color: #8a7d6e;
            padding: 8px 20px;
            margin-top: 4px;
        }
        .footer .brand { color: #c19a6b; font-weight: bold; letter-spacing: 0.16em; }
    </style>
</head>
<body>

@php
    $package->loadMissing(['itineraryDays', 'inclusions', 'exclusions', 'departures', 'images']);

    // Embed images as base64 data URIs (DomPDF needs local files, not URLs)
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
    if (!empty($package->hero_bg_image) && file_exists(public_path('storage/' . $package->hero_bg_image))) {
        $coverImg = $toDataUri(public_path('storage/' . $package->hero_bg_image));
    } else {
        $firstImg = $package->images->sortBy('sort_order')->first();
        if ($firstImg) {
            $imgPath = !empty($firstImg->image_path) ? $firstImg->image_path : ($firstImg->path ?? null);
            if ($imgPath && file_exists(public_path('storage/' . $imgPath))) {
                $coverImg = $toDataUri(public_path('storage/' . $imgPath));
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
    $tagline = $package->tagline ?? '';

    // Truncate description for one-pager (max 3 lines ≈ 300 chars)
    $about = strip_tags($package->description ?? '');
    if (mb_strlen($about) > 340) {
        $about = mb_substr($about, 0, 337) . '…';
    }
@endphp

{{-- ══════════════════════════════════════════════
     HEADER: Logo + Contact
══════════════════════════════════════════════ --}}
<table width="100%" cellpadding="0" cellspacing="0" class="header">
    <tr>
        <td valign="middle">
            @if($logoExists)
                <img src="{{ $logoPath }}" alt="TYT Luxe" style="height:44px; object-fit:contain;">
            @else
                <span class="header-logo">TYT LUXE</span>
            @endif
        </td>
        <td valign="middle" class="header-contact">
            +91 98750 73788 &nbsp;|&nbsp; takeyourtrip7@gmail.com &nbsp;|&nbsp; www.tytluxe.in
        </td>
    </tr>
</table>

{{-- ══════════════════════════════════════════════
     COVER IMAGE
══════════════════════════════════════════════ --}}
@if($coverImg)
    <img src="{{ $coverImg }}" class="cover-img" alt="{{ $destination }}">
@else
    <div class="cover-img-placeholder"></div>
@endif

{{-- ══════════════════════════════════════════════
     CAPTION BAND
══════════════════════════════════════════════ --}}
<div class="caption">
    @if(!empty($package->hero_eyebrow))
        <div class="caption-tag">{{ $package->hero_eyebrow }}</div><br>
    @endif
    <div class="caption-title">{{ $destination }}</div>
    <div class="caption-sub">{{ $nights }} Nights / {{ $days }} Days
        @if($tagline) &nbsp;·&nbsp; {{ $tagline }}@endif
    </div>
</div>

{{-- ══════════════════════════════════════════════
     META BAR
══════════════════════════════════════════════ --}}
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

{{-- ══════════════════════════════════════════════
     TWO-COLUMN BODY
══════════════════════════════════════════════ --}}
<table class="body-table" cellpadding="0" cellspacing="0">
    <tr>

        {{-- LEFT COLUMN: About + Itinerary --}}
        <td class="col-left">

            {{-- About --}}
            @if($about)
                <div class="eyebrow">Discover</div>
                <div class="section-h">About {{ $destination }}</div>
                <p class="about-p">{{ $about }}</p>
                <hr class="divider">
            @endif

            {{-- Itinerary --}}
            @if($package->itineraryDays->count() > 0)
                <div class="eyebrow">Day By Day</div>
                <div class="section-h">Your Itinerary</div>
                @foreach($package->itineraryDays as $day)
                    <div class="itin-row">
                        <table width="100%" cellpadding="0" cellspacing="0">
                            <tr>
                                <td width="45" valign="middle">
                                    <span class="day-pill">{{ $day->day_number == 0 ? 'DEP' : 'Day '.$day->day_number }}</span>
                                </td>
                                <td valign="middle" class="itin-title">{{ $day->title }}</td>
                            </tr>
                        </table>
                    </div>
                @endforeach
                <hr class="divider">
            @endif

            {{-- Travel Dates (compact) --}}
            @if($package->departures->count() > 0)
                <div class="eyebrow">Next Departures</div>
                <p style="font-size:9px; color:#3b3028; line-height:1.7;">
                @foreach($package->departures->take(6) as $dep)
                    &#8594; {{ \Carbon\Carbon::parse($dep->start_date)->format('d M Y') }}
                    &ndash; {{ \Carbon\Carbon::parse($dep->end_date)->format('d M') }}&nbsp;&nbsp;
                @endforeach
                </p>
            @endif

        </td>

        {{-- RIGHT COLUMN: Price + Inc/Exc + Contact --}}
        <td class="col-right">

            {{-- Price Card --}}
            <div class="price-card">
                <div class="price-amount">&#8377;{{ number_format($package->price_from ?? 0) }}</div>
                <div class="price-sub">per person &middot; inclusive of all taxes &middot; ex. {{ $departureCity }}</div>
                @if($package->booking_amount)
                    <div class="price-booking">
                        Book now for just <span>&#8377;{{ number_format($package->booking_amount) }} / person</span><br>
                        Balance payable before departure
                    </div>
                @endif
            </div>

            {{-- Inclusions --}}
            @if($package->inclusions->count() > 0)
                <div class="inc-head green">&#10003; What's Included</div>
                @foreach($package->inclusions->take(8) as $item)
                    <div class="inc-item"><span class="mark-g">&#10003;</span>{{ $item->label }}</div>
                @endforeach
                <hr class="divider">
            @endif

            {{-- Exclusions --}}
            @if($package->exclusions->count() > 0)
                <div class="inc-head red">&#10007; Not Included</div>
                @foreach($package->exclusions->take(6) as $item)
                    <div class="inc-item"><span class="mark-r">&#10007;</span>{{ $item->name }}</div>
                @endforeach
                <hr class="divider">
            @endif

            {{-- Contact / Booking --}}
            <div class="eyebrow">How to Book</div>
            <div style="margin-top:5px;">
                <div class="cta-row">
                    <div class="cta-label">Phone / WhatsApp</div>
                    <div class="cta-val">+91 98750 73788</div>
                </div>
                <div class="cta-row">
                    <div class="cta-label">Email</div>
                    <div class="cta-val">takeyourtrip7@gmail.com</div>
                </div>
                <div class="cta-row">
                    <div class="cta-label">Website</div>
                    <div class="cta-val">www.tytluxe.in</div>
                </div>
                <div class="cta-row" style="margin-top:6px;">
                    <div class="cta-label">Office</div>
                    <div class="cta-val" style="font-size:8.5px;">Cabin No 9, 4th Floor, Surana Supremus,<br>Near Safal Square, Vesu, Surat 394518</div>
                </div>
            </div>

        </td>
    </tr>
</table>

{{-- ══════════════════════════════════════════════
     FOOTER
══════════════════════════════════════════════ --}}
<div class="footer">
    <span class="brand">TYT LUXE</span>
    &nbsp;&nbsp;|&nbsp;&nbsp;
    +91 98750 73788
    &nbsp;&nbsp;|&nbsp;&nbsp;
    takeyourtrip7@gmail.com
    &nbsp;&nbsp;|&nbsp;&nbsp;
    www.tytluxe.in
    &nbsp;&nbsp;|&nbsp;&nbsp;
    Instagram @tytluxe_
</div>

</body>
</html>