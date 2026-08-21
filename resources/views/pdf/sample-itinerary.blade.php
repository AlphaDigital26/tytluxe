<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $package->title ?? 'Itinerary' }}</title>
    <style>
        @page {
            margin: 0px;
            padding: 0px;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #333;
            line-height: 1.5;
            margin: 0;
            padding: 0;
            background-color: #fff;
            margin-bottom: 50px; /* Space for footer */
        }
        
        /* Fixed Footer */
        footer {
            position: fixed;
            bottom: 0px;
            left: 0px;
            right: 0px;
            height: 40px;
            background-color: #fff;
            color: #2a2520; 
            text-align: center;
            line-height: 40px;
            font-size: 10px;
            letter-spacing: 1px;
            z-index: 1000;
            border-top: 1px solid #ddd;
        }

        /* ------------------ COVER PAGE ------------------ */
        .cover-page {
            position: relative;
            width: 100%;
            height: 100%;
            page-break-after: always;
            background-color: #fcfcfc;
        }
        
        .hero-img-container {
            position: relative;
            width: 100%;
            height: 60%;
            background-color: #333;
        }
        
        @php
            $coverImagePath = $package->hero_bg_image ? public_path('storage/' . $package->hero_bg_image) : 'https://images.unsplash.com/photo-1527668752968-14ce70a27dd3?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80';
        @endphp
        
        .hero-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        /* Top Overlay Header */
        .hero-header {
            position: absolute;
            top: 20px;
            left: 30px;
            right: 30px;
            height: 80px;
        }
        .hero-logo {
            float: left;
            width: 100px;
        }
        .hero-contact {
            float: right;
            text-align: right;
            color: #fff;
            font-size: 11px;
            line-height: 1.4;
            text-shadow: 1px 1px 3px rgba(0,0,0,0.8);
        }
        
        /* Bottom Overlay Title */
        .hero-title-container {
            position: absolute;
            bottom: 0px;
            left: 0px;
            right: 0px;
            background-color: rgba(0,0,0,0.6); /* Fallback for gradient */
            padding: 40px 30px 20px 30px;
            color: #fff;
        }
        .pill-badge {
            display: inline-block;
            border: 1px solid #fff;
            border-radius: 15px;
            padding: 3px 12px;
            font-size: 10px;
            letter-spacing: 2px;
            margin-bottom: 15px;
            text-transform: uppercase;
        }
        .hero-title {
            font-size: 36px;
            font-weight: bold;
            margin: 0 0 5px 0;
            line-height: 1.1;
        }
        .hero-subtitle {
            font-size: 28px;
            font-weight: bold;
            margin: 0 0 10px 0;
            line-height: 1.1;
        }
        .hero-desc {
            font-size: 12px;
            color: #ddd;
        }
        
        /* Features Bar */
        .features-bar {
            background-color: #2c251f;
            width: 100%;
        }
        .features-table {
            width: 100%;
            border-collapse: collapse;
        }
        .features-table td {
            text-align: center;
            vertical-align: middle;
            padding: 20px 10px;
            border-right: 1px solid rgba(255,255,255,0.1);
            width: 25%;
        }
        .features-table td:last-child {
            border-right: none;
        }
        .feat-val {
            color: #D4AF37;
            font-size: 18px;
            font-weight: bold;
            display: block;
            margin-bottom: 5px;
        }
        .feat-lbl {
            color: #aaa;
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 1px;
            display: block;
        }

        /* Intro Content (Cover Page Bottom) */
        .intro-content {
            padding: 30px 40px;
        }
        .section-eyebrow {
            color: #D4AF37;
            font-size: 11px;
            font-weight: bold;
            letter-spacing: 2px;
            text-transform: uppercase;
            margin-bottom: 5px;
        }
        .section-title {
            font-size: 22px;
            color: #222;
            font-weight: bold;
            margin-bottom: 15px;
            margin-top: 0;
        }
        .intro-text {
            font-size: 13px;
            color: #555;
            text-align: justify;
            margin-bottom: 30px;
            line-height: 1.6;
        }
        
        .brief-itinerary {
            margin-top: 20px;
        }
        .brief-row {
            margin-bottom: 10px;
        }
        .brief-badge {
            background-color: #b79c65;
            color: #fff;
            font-size: 10px;
            font-weight: bold;
            padding: 3px 8px;
            border-radius: 4px;
            display: inline-block;
            width: 45px;
            text-align: center;
        }
        .brief-text {
            display: inline-block;
            font-size: 13px;
            color: #333;
            margin-left: 15px;
            vertical-align: middle;
        }

        /* ------------------ CONTENT PAGES ------------------ */
        .page-break {
            page-break-after: always;
        }
        .content-page {
            padding: 40px;
        }
        
        /* Detailed Itinerary */
        .day-block {
            background-color: #fbf9f4;
            border: 1px solid #e8e3d9;
            border-radius: 8px;
            margin-bottom: 25px;
            page-break-inside: avoid;
            overflow: hidden;
            width: 100%;
        }
        .day-table {
            width: 100%;
            border-collapse: collapse;
        }
        .day-img-cell {
            width: 30%;
            padding: 0;
            vertical-align: top;
        }
        .day-img-cell img {
            width: 100%;
            height: 180px; /* Fixed height for uniformity */
            object-fit: cover;
            display: block;
            border-radius: 8px 0 0 8px;
        }
        .day-content-cell {
            width: 70%;
            padding: 20px;
            vertical-align: top;
        }
        .day-title-row {
            margin-bottom: 15px;
        }
        .day-title-badge {
            color: #b79c65;
            font-weight: bold;
            font-size: 12px;
            margin-right: 15px;
            text-transform: uppercase;
        }
        .day-title-text {
            color: #222;
            font-weight: bold;
            font-size: 16px;
        }
        .day-desc {
            font-size: 12px;
            color: #555;
            line-height: 1.5;
        }
        
        /* Dates Section */
        .dates-container {
            margin-bottom: 40px;
        }
        .dates-table {
            width: 100%;
            border-collapse: collapse;
            border-top: 2px solid #b79c65;
            margin-top: 15px;
        }
        .dates-table th {
            text-align: left;
            padding: 15px 10px;
            font-size: 12px;
            color: #b79c65;
            text-transform: uppercase;
            font-weight: bold;
        }
        .dates-table td {
            padding: 5px 10px 15px 10px;
            font-size: 13px;
            color: #333;
            vertical-align: top;
            width: 33.33%;
        }
        
        /* Inclusions / Exclusions */
        .inc-exc-container {
            margin-bottom: 40px;
        }
        .inc-exc-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        .inc-exc-table td {
            width: 50%;
            vertical-align: top;
        }
        .inc-header {
            font-weight: bold;
            font-size: 14px;
            color: #008000;
            padding-bottom: 10px;
            border-bottom: 2px solid #008000;
            margin-bottom: 15px;
        }
        .exc-header {
            font-weight: bold;
            font-size: 14px;
            color: #B22222;
            padding-bottom: 10px;
            border-bottom: 2px solid #B22222;
            margin-bottom: 15px;
            margin-left: 20px;
        }
        .inc-list, .exc-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .exc-list {
            margin-left: 20px;
        }
        .inc-list li, .exc-list li {
            position: relative;
            padding-left: 20px;
            margin-bottom: 10px;
            font-size: 12px;
            color: #444;
            line-height: 1.4;
        }
        .inc-list li:before {
            content: '✓';
            color: #008000;
            position: absolute;
            left: 0;
            top: 0;
        }
        .exc-list li:before {
            content: '✕';
            color: #B22222;
            position: absolute;
            left: 0;
            top: 0;
        }

        /* Pricing */
        .price-box {
            background-color: #2c251f;
            color: #fff;
            border-radius: 8px;
            padding: 25px;
            margin-top: 15px;
            margin-bottom: 40px;
        }
        .price-table {
            width: 100%;
        }
        .price-table td {
            vertical-align: middle;
        }
        .price-left {
            width: 60%;
        }
        .price-right {
            width: 40%;
            text-align: right;
            font-size: 11px;
            color: #ccc;
            line-height: 1.6;
        }
        .price-val {
            font-size: 32px;
            font-weight: bold;
            color: #D4AF37;
            margin-bottom: 5px;
        }
        .price-sub {
            font-size: 11px;
            color: #aaa;
        }
        .price-right span {
            color: #fff;
            font-weight: bold;
        }

        /* Booking */
        .booking-text {
            font-size: 13px;
            color: #555;
            margin-bottom: 20px;
        }
        .payment-methods {
            width: 100%;
            border-collapse: separate;
            border-spacing: 15px 0;
            margin-bottom: 30px;
            margin-left: -15px;
        }
        .payment-methods td {
            border: 1px solid #ddd;
            border-radius: 5px;
            text-align: center;
            padding: 10px;
            font-size: 12px;
            font-weight: bold;
            color: #333;
            width: 33.33%;
        }
        
        /* Contact Box */
        .contact-box {
            background-color: #fbf9f4;
            border-radius: 8px;
            padding: 25px;
        }
        .contact-title {
            font-size: 18px;
            font-weight: bold;
            color: #222;
            margin-bottom: 20px;
        }
        .contact-table {
            width: 100%;
        }
        .contact-table td {
            width: 50%;
            vertical-align: top;
            padding-bottom: 15px;
        }
        .contact-lbl {
            font-size: 10px;
            color: #b79c65;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 3px;
        }
        .contact-val {
            font-size: 13px;
            color: #333;
        }
        
        /* Notes */
        .notes-section {
            margin-top: 40px;
        }
        .notes-section ul {
            padding-left: 20px;
            font-size: 12px;
            color: #555;
        }
        .notes-section li {
            margin-bottom: 5px;
        }

        .rupee {
            font-family: 'DejaVu Sans', sans-serif;
        }

    </style>
</head>
<body>
    <!-- FOOTER -->
    <footer>
        TYT LUXE &nbsp;|&nbsp; 831, Tower C, Bhutani Alphathum, Sector 90, Noida, UP - 201305 &nbsp;|&nbsp; www.tytluxe.in
    </footer>

    <!-- COVER PAGE -->
    <div class="cover-page">
        <!-- Hero Background Area -->
        <div class="hero-img-container">
            <img class="hero-image" src="{{ $coverImagePath }}" alt="Cover">
            
            <div class="hero-header">
                <div class="hero-logo">
                    <img src="{{ public_path('images/logo-gold.png') }}" style="max-height: 40px;" alt="TYT Luxe">
                </div>
                <div class="hero-contact">
                    +91 98750 73788<br>
                    takeyourtrip7@gmail.com<br>
                    www.tytluxe.in
                </div>
            </div>

            <div class="hero-title-container">
                @if($package->region_type)
                    <div class="pill-badge">{{ $package->region_type }}</div>
                @endif
                <div class="hero-title">{{ $package->title }}</div>
                <div class="hero-subtitle">{{ $package->duration_nights }} Nights / {{ $package->duration_nights + 1 }} Days</div>
                <div class="hero-desc">
                    {{ Str::limit(strip_tags($package->description), 120) }}
                </div>
            </div>
        </div>
        
        <!-- Stats Bar -->
        <div class="features-bar">
            <table class="features-table">
                <tr>
                    <td>
                        <span class="feat-val">{{ $package->duration_nights }}N / {{ $package->duration_nights + 1 }}D</span>
                        <span class="feat-lbl">Duration</span>
                    </td>
                    <td>
                        @php
                            $departureCity = is_array($package->departure_from) ? implode(', ', $package->departure_from) : ($package->departure_from ?? 'N/A');
                        @endphp
                        <span class="feat-val">{{ $departureCity }}</span>
                        <span class="feat-lbl">Departure</span>
                    </td>
                    <td>
                        <span class="feat-val">{{ $package->meals_info ?? 'B & D' }}</span>
                        <span class="feat-lbl">Meals</span>
                    </td>
                    <td>
                        <span class="feat-val"><span class="rupee">&#8377;</span>{{ number_format($package->price_from ?? 0) }}</span>
                        <span class="feat-lbl">Starting From</span>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Cover Intro Content -->
        <div class="intro-content">
            <div class="section-eyebrow">D I S C O V E R</div>
            <div class="section-title">About {{ $package->title }}</div>
            <div class="intro-text">
                {!! nl2br(e(strip_tags($package->description))) !!}
            </div>

            @if($package->itineraryDays && $package->itineraryDays->count() > 0)
                <div class="section-eyebrow">D A Y  B Y  D A Y</div>
                <div class="section-title">Brief Itinerary</div>
                
                <div class="brief-itinerary">
                    @foreach($package->itineraryDays as $day)
                        <div class="brief-row">
                            <span class="brief-badge">DAY {{ $day->day_number }}</span>
                            <span class="brief-text">{{ $day->title }}</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <!-- DETAILED ITINERARY PAGES -->
    @if($package->itineraryDays && $package->itineraryDays->count() > 0)
    <div class="content-page">
        <div class="section-eyebrow">I N  D E T A I L</div>
        <div class="section-title">Your Itinerary, Day By Day</div>
        <br>
        @foreach($package->itineraryDays as $day)
            <div class="day-block">
                <table class="day-table">
                    <tr>
                        <td class="day-img-cell">
                            {{-- Try to find an image for this day, or fallback to hero image --}}
                            <img src="{{ $coverImagePath }}" alt="Day Image">
                        </td>
                        <td class="day-content-cell">
                            <div class="day-title-row">
                                <span class="day-title-badge">DAY {{ $day->day_number }}</span>
                                <span class="day-title-text">{{ $day->title }}</span>
                            </div>
                            <div class="day-desc">
                                {!! nl2br(e(strip_tags($day->description))) !!}
                            </div>
                        </td>
                    </tr>
                </table>
            </div>
        @endforeach
    </div>
    <div class="page-break"></div>
    @endif

    <!-- DETAILS PAGE (Dates, Inclusions, Pricing) -->
    <div class="content-page">
        
        <!-- Travel Dates -->
        @if($package->departures && $package->departures->count() > 0)
        <div class="dates-container">
            <div class="section-eyebrow">P L A N  Y O U R  T R I P</div>
            <div class="section-title">Travel Dates</div>
            
            <table class="dates-table">
                <thead>
                    <tr>
                        <th>Upcoming Batches</th>
                        <th></th>
                        <th>More Dates</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            @foreach($package->departures->take(3) as $dep)
                                {{ \Carbon\Carbon::parse($dep->start_date)->format('d M') }} - {{ \Carbon\Carbon::parse($dep->end_date)->format('d M Y') }}<br>
                            @endforeach
                        </td>
                        <td>
                            @foreach($package->departures->skip(3)->take(3) as $dep)
                                {{ \Carbon\Carbon::parse($dep->start_date)->format('d M') }} - {{ \Carbon\Carbon::parse($dep->end_date)->format('d M Y') }}<br>
                            @endforeach
                        </td>
                        <td>
                            New batches added regularly<br>
                            Contact us to check availability
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        @endif

        <!-- Inclusions / Exclusions -->
        <div class="inc-exc-container">
            <div class="section-eyebrow">W H A T ' S  C O V E R E D</div>
            <div class="section-title">Inclusions & Exclusions</div>
            
            <table class="inc-exc-table">
                <tr>
                    <td>
                        <div class="inc-header">✓ What's Included</div>
                        <ul class="inc-list">
                            @if($package->inclusions && $package->inclusions->count() > 0)
                                @foreach($package->inclusions as $inclusion)
                                    <li>{{ $inclusion->label }}</li>
                                @endforeach
                            @else
                                <li>Standard package inclusions</li>
                            @endif
                        </ul>
                    </td>
                    <td>
                        <div class="exc-header">✕ What's Excluded</div>
                        <ul class="exc-list">
                            @if($package->exclusions && $package->exclusions->count() > 0)
                                @foreach($package->exclusions as $exclusion)
                                    <li>{{ $exclusion->name }}</li>
                                @endforeach
                            @else
                                <li>Personal expenses and anything not mentioned</li>
                            @endif
                        </ul>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Pricing -->
        <div class="section-eyebrow">P R I C I N G</div>
        <div class="section-title">Package Price</div>
        
        <div class="price-box">
            <table class="price-table">
                <tr>
                    <td class="price-left">
                        <div class="price-val"><span class="rupee">&#8377;</span>{{ number_format($package->price_from ?? 0) }} / person</div>
                        <div class="price-sub">Starting from &middot; inclusive of all taxes &middot; ex. {{ $departureCity }}</div>
                    </td>
                    <td class="price-right">
                        @if($package->booking_amount)
                            Booking Amount: <span><span class="rupee">&#8377;</span>{{ number_format($package->booking_amount) }} / person</span><br>
                            Balance payable before departure
                        @endif
                    </td>
                </tr>
            </table>
        </div>

        <!-- Booking -->
        <div class="section-eyebrow">H O W  T O  B O O K</div>
        <div class="section-title">Booking Process</div>
        
        <div class="booking-text">
            To book this package, simply reach out to us on WhatsApp or call us directly. A booking amount of <span class="rupee">&#8377;</span>{{ number_format($package->booking_amount ?? 2000) }} per person is required to confirm your seat. Our team will then share the full itinerary and payment details with you.
        </div>
        
        <table class="payment-methods">
            <tr>
                <td>Bank Transfer</td>
                <td>GPay / PhonePe</td>
                <td>Paytm / UPI</td>
            </tr>
        </table>
        
        <!-- Contact -->
        <div class="contact-box">
            <div class="contact-title">Get In Touch</div>
            <table class="contact-table">
                <tr>
                    <td>
                        <div class="contact-lbl">PHONE</div>
                        <div class="contact-val">+91 98750 73788</div>
                    </td>
                    <td>
                        <div class="contact-lbl">EMAIL</div>
                        <div class="contact-val">takeyourtrip7@gmail.com</div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="contact-lbl">WEBSITE</div>
                        <div class="contact-val">www.tytluxe.in</div>
                    </td>
                    <td>
                        <div class="contact-lbl">ADDRESS</div>
                        <div class="contact-val">831, Tower C, Bhutani Alphathum,<br>Sector 90, Noida, UP-201305</div>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Notes -->
        @if($package->notes && is_array($package->notes) && count($package->notes) > 0)
        <div class="notes-section">
            <div class="section-title" style="font-size: 16px;">Notes</div>
            <ul>
                @foreach($package->notes as $note)
                    <li>{{ $note }}</li>
                @endforeach
            </ul>
        </div>
        @endif
        
    </div>

</body>
</html>
