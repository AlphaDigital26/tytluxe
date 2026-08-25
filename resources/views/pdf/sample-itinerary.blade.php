<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $package->title ?? 'Itinerary' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Montserrat:wght@600;700;800&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Inter', sans-serif; background: #fff; color: #27201a; }
        h1, h2, h3, h4, .heading { font-family: 'Montserrat', sans-serif; }

        /* Exact colors from sample PDF */
        .gold { color: #b08c45; }
        .gold-bg { background-color: #b08c45; }
        .dark-bg { background-color: #27201a; }
        .beige { background-color: #f7f4ee; }

        /* @page rules for proper multi-page PDF with footer */
        @page {
            margin: 0;
        }

        /* Footer that appears at bottom of every page */
        #footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            height: 42px;
            background: #27201a;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            letter-spacing: 0.12em;
            color: #9a8b7a;
            z-index: 1000;
        }
        #footer .brand { color: #b08c45; font-weight: 700; }

        /* Push body content up so footer doesn't overlap */
        body { padding-bottom: 42px; }

        /* Page break utility */
        .page-break { page-break-after: always; break-after: page; }

        /* Eyebrow label */
        .eyebrow { font-size: 10px; font-weight: 700; letter-spacing: 0.22em; text-transform: uppercase; color: #b08c45; margin-bottom: 6px; }

        /* Section title */
        .section-title { font-family: 'Montserrat', sans-serif; font-size: 26px; font-weight: 800; color: #1a1612; margin-bottom: 20px; letter-spacing: -0.02em; }

        /* Day badge */
        .day-badge { background: #b08c45; color: #fff; font-size: 10px; font-weight: 700; padding: 4px 10px; border-radius: 4px; text-transform: uppercase; letter-spacing: 0.05em; white-space: nowrap; }

        /* Brief itinerary row */
        .brief-row { display: flex; align-items: center; gap: 14px; padding: 13px 0; border-bottom: 1px solid #ece9e1; }
        .brief-row:last-child { border-bottom: none; }
        .brief-text { font-size: 13.5px; color: #3d3228; line-height: 1.4; }

        /* Detail card */
        .detail-card { display: flex; border: 1px solid #e2ddd5; border-radius: 10px; overflow: hidden; margin-bottom: 20px; }
        .detail-card-img { width: 200px; min-width: 200px; object-fit: cover; }
        .detail-card-right { flex: 1; display: flex; flex-direction: column; }
        .detail-card-header { background: #f7f4ee; padding: 14px 20px; display: flex; align-items: center; gap: 14px; border-bottom: 1px solid #e2ddd5; }
        .detail-card-title { font-family: 'Montserrat', sans-serif; font-size: 16px; font-weight: 700; color: #1a1612; }
        .detail-card-body { padding: 14px 20px; font-size: 13px; color: #4a4035; line-height: 1.7; }

        /* Date card */
        .date-card { flex: 1; background: #f7f4ee; border-top: 3px solid #b08c45; border-radius: 0 0 8px 8px; padding: 20px; }
        .date-card-month { font-size: 10px; font-weight: 700; letter-spacing: 0.18em; text-transform: uppercase; color: #b08c45; margin-bottom: 12px; }
        .date-card li { font-size: 13px; color: #3d3228; margin-bottom: 8px; list-style: none; }

        /* Inclusions/Exclusions */
        .incl-header { font-size: 14px; font-weight: 700; padding-bottom: 10px; margin-bottom: 14px; display: flex; align-items: center; gap: 8px; }
        .incl-header.green { color: #16803c; border-bottom: 2px solid #16803c; }
        .incl-header.red { color: #c0392b; border-bottom: 2px solid #c0392b; }
        .incl-li { display: flex; align-items: flex-start; gap: 10px; font-size: 13px; color: #3d3228; margin-bottom: 10px; line-height: 1.5; }
        .incl-mark { font-weight: 700; flex-shrink: 0; margin-top: 1px; }
        .incl-mark.green { color: #16803c; }
        .incl-mark.red { color: #c0392b; }

        /* Pricing card */
        .price-card { background: #27201a; border-radius: 12px; padding: 28px 32px; display: flex; justify-content: space-between; align-items: center; color: #fff; }
        .price-val { font-family: 'Montserrat', sans-serif; font-size: 32px; font-weight: 800; color: #b08c45; letter-spacing: -0.02em; }
        .price-sub { font-size: 12px; color: #9a8b7a; margin-top: 5px; }
        .price-right-label { font-size: 12px; color: #9a8b7a; text-align: right; }
        .price-right-val { font-family: 'Montserrat', sans-serif; font-size: 15px; font-weight: 700; color: #fff; }

        /* Payment buttons */
        .pay-btn { flex: 1; border: 1px solid #ddd; border-radius: 8px; padding: 11px; text-align: center; font-size: 13px; font-weight: 600; color: #3d3228; }

        /* Contact block */
        .contact-block { background: #f7f4ee; border-radius: 12px; padding: 28px 28px 20px; }
        .contact-label { font-size: 9px; font-weight: 700; letter-spacing: 0.18em; text-transform: uppercase; color: #b08c45; margin-bottom: 4px; }
        .contact-val { font-size: 13px; font-weight: 600; color: #27201a; }

        /* Social pill */
        .social-pill { background: #fff; border: 1px solid #e5e0d6; border-radius: 999px; padding: 7px 16px; font-size: 12px; font-weight: 600; color: #3d3228; display: flex; align-items: center; gap: 6px; }
        .social-dot { width: 7px; height: 7px; border-radius: 50%; background: #b08c45; flex-shrink: 0; }

        /* Notes */
        .notes-title { font-size: 13px; font-weight: 700; color: #1a1612; margin-bottom: 10px; }
        .notes-li { font-size: 12px; color: #6b5f52; margin-bottom: 6px; padding-left: 4px; }

        /* Feature bar */
        .feat-bar { background: #27201a; display: flex; }
        .feat-cell { flex: 1; text-align: center; padding: 18px 8px; border-right: 1px solid rgba(255,255,255,0.08); }
        .feat-cell:last-child { border-right: none; }
        .feat-val { font-family: 'Montserrat', sans-serif; font-size: 18px; font-weight: 700; color: #b08c45; display: block; margin-bottom: 4px; }
        .feat-lbl { font-size: 9px; font-weight: 700; letter-spacing: 0.2em; text-transform: uppercase; color: #7a6d5e; display: block; }
    </style>
</head>
<body>

    <!-- FIXED FOOTER (appears on every page) -->
    <div id="footer">
        <span class="brand">TYT LUXE</span>
        &nbsp;&nbsp;|&nbsp;&nbsp;
        831, Tower C, Bhutani Alphathum, Sector 90, Noida, UP - 201305
        &nbsp;&nbsp;|&nbsp;&nbsp;
        www.tytluxe.in
    </div>

    @php
        // Eager-load all relationships if not already loaded
        $package->loadMissing(['itineraryDays', 'inclusions', 'exclusions', 'departures', 'images']);

        // Cover image
        $firstImg = $package->images->sortBy('sort_order')->first();
        $coverImg = $firstImg ? public_path('storage/' . $firstImg->image) : 'https://images.unsplash.com/photo-1527668752968-14ce70a27dd3?auto=format&fit=crop&w=1200&q=80';

        // Logo
        $logoPath = public_path('assets/images/tyt-logo.png');

        // Departure city (handle array or string)
        $departureCity = is_array($package->departure_from) ? implode(', ', array_filter($package->departure_from)) : ($package->departure_from ?? 'Delhi');

        // Title - clean up if it contains duration info
        $packageTitle = $package->title ?? 'Package';
    @endphp


    <!-- ==================== PAGE 1: COVER ==================== -->
    <div class="page-break" style="min-height:calc(100vh - 42px); display:flex; flex-direction:column;">

        <!-- HERO IMAGE SECTION -->
        <div style="position:relative; height:520px; overflow:hidden; background:#222;">
            <img src="{{ $coverImg }}" style="position:absolute;inset:0;width:100%;height:100%;object-fit:cover;" alt="Cover">

            <!-- Dark gradient overlay top (for logo/contact) -->
            <div style="position:absolute;inset:0;background:linear-gradient(180deg, rgba(0,0,0,0.65) 0%, rgba(0,0,0,0.05) 45%, rgba(0,0,0,0.6) 70%, rgba(0,0,0,0.88) 100%);"></div>

            <!-- Top bar: Logo + Contact -->
            <div style="position:absolute;top:24px;left:30px;right:30px;display:flex;justify-content:space-between;align-items:flex-start;z-index:10;">
                <img src="{{ $logoPath }}" alt="TYT Luxe" style="height:48px;object-fit:contain;">
                <div style="text-align:right;color:#fff;font-size:12px;font-weight:500;line-height:1.8;text-shadow:0 1px 4px rgba(0,0,0,0.6);">
                    +91 98750 73788<br>
                    takeyourtrip7@gmail.com<br>
                    www.tytluxe.in
                </div>
            </div>

            <!-- Bottom: Region badge + Title + Subtitle -->
            <div style="position:absolute;bottom:28px;left:30px;right:30px;z-index:10;">
                @if($package->region_type)
                    <div style="display:inline-block;border:1px solid rgba(255,255,255,0.6);border-radius:999px;padding:4px 16px;font-size:10px;letter-spacing:0.15em;text-transform:uppercase;color:#fff;margin-bottom:12px;">
                        {{ $package->region_type }}
                    </div>
                @endif
                @php
                    // Determine destination name (without "2 Nights | 3 Days" if present)
                    $destination = $package->destination?->name ?? preg_replace('/\s*[\|\/]\s*\d+\s*(Nights?|Days?).*/i', '', $packageTitle);
                @endphp
                <h1 class="heading" style="font-size:46px;font-weight:800;color:#fff;line-height:1.15;letter-spacing:-0.02em;margin-bottom:8px;text-shadow:0 2px 8px rgba(0,0,0,0.5);">{{ $destination }}</h1>
                <h2 class="heading" style="font-size:28px;font-weight:700;color:#fff;text-shadow:0 2px 6px rgba(0,0,0,0.5);">{{ $package->duration_nights }} Nights / {{ (int)$package->duration_nights + 1 }} Days</h2>
            </div>
        </div>

        <!-- FEATURE BAR -->
        <div class="feat-bar">
            <div class="feat-cell">
                <span class="feat-val">{{ $package->duration_nights }}N / {{ (int)$package->duration_nights + 1 }}D</span>
                <span class="feat-lbl">Duration</span>
            </div>
            <div class="feat-cell">
                <span class="feat-val">{{ $departureCity }}</span>
                <span class="feat-lbl">Departure</span>
            </div>
            <div class="feat-cell">
                <span class="feat-val">{{ $package->meals_info ?? 'B & D' }}</span>
                <span class="feat-lbl">Meals</span>
            </div>
            <div class="feat-cell">
                <span class="feat-val">{{ $package->transport_info ?? 'Volvo / TT' }}</span>
                <span class="feat-lbl">Transport</span>
            </div>
            <div class="feat-cell">
                <span class="feat-val">₹{{ number_format($package->price_from ?? 0) }}</span>
                <span class="feat-lbl">Starting From</span>
            </div>
        </div>

        <!-- DISCOVER SECTION -->
        <div style="padding: 36px 30px 20px;">
            <div class="eyebrow">Discover</div>
            <div class="section-title">About {{ $destination }}</div>
            <p style="font-size:14px;color:#4a4035;line-height:1.75;margin-bottom:32px;">{{ strip_tags($package->description ?? '') }}</p>

            @if($package->itineraryDays->count() > 0)
                <div class="eyebrow">Day By Day</div>
                <div class="section-title">Brief Itinerary</div>
                <div>
                    @foreach($package->itineraryDays as $day)
                        <div class="brief-row">
                            <span class="day-badge">Day {{ $day->day_number }}</span>
                            <span class="brief-text">{{ $day->title }}</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

    </div><!-- END PAGE 1 -->


    <!-- ==================== PAGE 2: ITINERARY + DATES ==================== -->
    @if($package->itineraryDays->count() > 0)
    <div class="page-break" style="min-height:calc(100vh - 42px);">
        <div style="padding: 36px 30px 20px;">
            <div class="eyebrow">In Detail</div>
            <div class="section-title">Your Itinerary, Day By Day</div>

            @foreach($package->itineraryDays as $day)
                @php
                    // Per-day image: use day's own image if available, else fall back to cover
                    $dayImg = $day->image ? public_path('storage/' . $day->image) : $coverImg;
                @endphp
                <div class="detail-card">
                    <img src="{{ $dayImg }}" class="detail-card-img" alt="Day {{ $day->day_number }}">
                    <div class="detail-card-right">
                        <div class="detail-card-header">
                            <span class="day-badge">Day {{ $day->day_number }}</span>
                            <span class="detail-card-title">{{ $day->title }}</span>
                        </div>
                        <div class="detail-card-body">
                            {{ strip_tags($day->description ?? '') }}
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- TRAVEL DATES -->
        @if($package->departures->count() > 0)
        <div style="padding: 20px 30px 36px;">
            <div class="eyebrow">Plan Your Trip</div>
            <div class="section-title">Travel Dates</div>

            @php
                // Group departures by month
                $grouped = $package->departures->groupBy(function($d) {
                    return \Carbon\Carbon::parse($d->start_date)->format('F Y');
                });
                $groupedArr = $grouped->toArray();
                $months = array_keys($groupedArr);
            @endphp

            <div style="display:flex; gap:16px;">
                @foreach($grouped->take(2) as $month => $deps)
                <div class="date-card">
                    <div class="date-card-month">{{ $month }}</div>
                    <ul>
                        @foreach($deps as $dep)
                            <li>{{ \Carbon\Carbon::parse($dep->start_date)->format('d M') }} – {{ \Carbon\Carbon::parse($dep->end_date)->format('d M Y') }}</li>
                        @endforeach
                    </ul>
                </div>
                @endforeach
                <div class="date-card">
                    <div class="date-card-month">More Dates</div>
                    <ul>
                        <li>New batches added regularly</li>
                        <li style="margin-top:8px;">Contact us to check availability</li>
                    </ul>
                </div>
            </div>
        </div>
        @endif
    </div><!-- END PAGE 2 -->
    @endif


    <!-- ==================== PAGE 3: INCLUSIONS, PRICING, BOOKING ==================== -->
    <div style="min-height:calc(100vh - 42px); padding: 36px 30px 20px;">

        <!-- INCLUSIONS & EXCLUSIONS -->
        <div class="eyebrow">What's Covered</div>
        <div class="section-title">Inclusions & Exclusions</div>

        <div style="display:flex; gap:40px; margin-bottom:36px;">
            <!-- Inclusions -->
            <div style="flex:1;">
                <div class="incl-header green">✓ &nbsp;What's Included</div>
                @if($package->inclusions->count() > 0)
                    @foreach($package->inclusions as $item)
                        <div class="incl-li">
                            <span class="incl-mark green">✓</span>
                            <span>{{ $item->label }}</span>
                        </div>
                    @endforeach
                @else
                    <div class="incl-li"><span class="incl-mark green">✓</span><span>Transportation as per itinerary</span></div>
                @endif
            </div>
            <!-- Exclusions -->
            <div style="flex:1;">
                <div class="incl-header red">✕ &nbsp;What's Excluded</div>
                @if($package->exclusions->count() > 0)
                    @foreach($package->exclusions as $item)
                        <div class="incl-li">
                            <span class="incl-mark red">✕</span>
                            <span>{{ $item->name }}</span>
                        </div>
                    @endforeach
                @else
                    <div class="incl-li"><span class="incl-mark red">✕</span><span>Personal expenses</span></div>
                @endif
            </div>
        </div>

        <!-- PRICING -->
        <div class="eyebrow">Pricing</div>
        <div class="section-title">Package Price</div>

        <div class="price-card" style="margin-bottom:36px;">
            <div>
                <div class="price-val">₹{{ number_format($package->price_from ?? 0) }} / person</div>
                <div class="price-sub">Starting from &middot; inclusive of all taxes &middot; ex. {{ $departureCity }}</div>
            </div>
            @if($package->booking_amount)
            <div style="text-align:right;">
                <div class="price-right-label">Booking Amount: <span class="price-right-val">₹{{ number_format($package->booking_amount) }} / person</span></div>
                <div class="price-right-label" style="margin-top:4px;">Balance payable before departure</div>
            </div>
            @endif
        </div>

        <!-- BOOKING PROCESS -->
        <div class="eyebrow">How To Book</div>
        <div class="section-title">Booking Process</div>
        <p style="font-size:14px;color:#4a4035;line-height:1.75;margin-bottom:20px;">
            To book this package, simply reach out to us on WhatsApp or call us directly. A booking amount of ₹{{ number_format($package->booking_amount ?? 2000) }} per person is required to confirm your seat. Our team will then share the full itinerary and payment details with you.
        </p>

        <div style="display:flex;gap:12px;margin-bottom:28px;">
            <div class="pay-btn">Bank Transfer</div>
            <div class="pay-btn">GPay / PhonePe</div>
            <div class="pay-btn">Paytm / UPI</div>
        </div>

        <!-- CONTACT / GET IN TOUCH -->
        <div class="contact-block">
            <h3 class="heading" style="font-size:20px;font-weight:700;color:#1a1612;margin-bottom:22px;">Get In Touch</h3>
            <div style="display:flex;gap:40px;margin-bottom:20px;">
                <div>
                    <div class="contact-label">Phone</div>
                    <div class="contact-val">+91 98750 73788</div>
                    <div style="margin-top:16px;">
                        <div class="contact-label">Website</div>
                        <div class="contact-val">www.tytluxe.in</div>
                    </div>
                </div>
                <div>
                    <div class="contact-label">Email</div>
                    <div class="contact-val">takeyourtrip7@gmail.com</div>
                    <div style="margin-top:16px;">
                        <div class="contact-label">Address</div>
                        <div class="contact-val">831, Tower C, Bhutani Alphathum,<br>Sector 90, Noida, UP - 201305</div>
                    </div>
                </div>
            </div>
            <!-- Social pills -->
            <div style="display:flex;gap:10px;flex-wrap:wrap;">
                <div class="social-pill"><div class="social-dot"></div>Instagram @tytluxe_</div>
                <div class="social-pill"><div class="social-dot"></div>WhatsApp +91 98750 73788</div>
                <div class="social-pill"><div class="social-dot"></div>Facebook /TYTLuxe</div>
            </div>
        </div>

        <!-- NOTES -->
        @if($package->notes && is_array($package->notes) && count($package->notes) > 0)
        <div style="margin-top:28px;padding-top:20px;border-top:1px solid #e8e2d8;">
            <div class="notes-title">Notes</div>
            @foreach($package->notes as $note)
                <div class="notes-li" style="display:flex;gap:8px;align-items:flex-start;">
                    <span style="color:#b08c45;font-weight:700;margin-top:1px;">•</span>
                    <span>{{ $note }}</span>
                </div>
            @endforeach
        </div>
        @endif

    </div><!-- END PAGE 3 -->

</body>
</html>
