<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Luxury Itinerary</title>
    <style>
        @page {
            margin: 0;
            padding: 0;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #222;
            line-height: 1.6;
            margin: 0;
            padding: 0;
            background-color: #fcfcfc;
            margin-bottom: 40px; /* Space for the fixed footer on every page */
        }
        
        /* Fixed Footer for every page */
        footer {
            position: fixed;
            bottom: 0px;
            left: 0px;
            right: 0px;
            height: 30px;
            background-color: #2a2520; /* Dark brown matching screenshot */
            color: #9f9175; /* Subdued gold text */
            text-align: center;
            line-height: 30px;
            font-size: 10px;
            letter-spacing: 1px;
            z-index: 1000;
        }
        
        /* Cover Page */
        .cover-page {
            width: 100%;
            height: 100vh;
            background-color: #111;
            color: #fff;
            text-align: center;
            position: relative;
        }
        .cover-image {
            width: 100%;
            height: 60%;
            object-fit: cover;
        }
        .cover-content {
            padding: 40px;
        }
        .brand-name {
            font-size: 28px;
            letter-spacing: 4px;
            text-transform: uppercase;
            color: #D4AF37; /* Gold */
            margin-bottom: 5px;
        }
        .cover-title {
            font-size: 42px;
            font-weight: 300;
            margin: 20px 0;
            font-family: 'Times New Roman', Times, serif;
        }
        .cover-subtitle {
            font-size: 18px;
            color: #aaa;
            letter-spacing: 2px;
            text-transform: uppercase;
        }

        /* Page Break */
        .page-break {
            page-break-after: always;
        }

        /* Content Pages */
        .content-page {
            padding: 40px 0px; /* Removed side spacings */
        }
        .header-small {
            text-align: center;
            border-bottom: 1px solid #eee;
            padding-bottom: 20px;
            margin-bottom: 40px;
        }
        .header-small .brand-name {
            font-size: 20px;
            margin: 0;
        }

        .section-title {
            font-size: 24px;
            color: #111;
            text-transform: uppercase;
            letter-spacing: 2px;
            border-left: 4px solid #D4AF37;
            padding-left: 15px;
            margin-left: 20px;
            margin-bottom: 30px;
            font-family: 'Times New Roman', Times, serif;
        }

        /* Overview Section */
        .overview-box {
            background-color: #fff;
            border: 1px solid #e0e0e0;
            padding: 25px;
            margin: 0 20px 40px 20px;
        }
        .overview-box table {
            width: 100%;
        }
        .overview-box td {
            padding: 10px 0;
            vertical-align: top;
        }
        .overview-label {
            width: 30%;
            font-weight: bold;
            color: #D4AF37;
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 1px;
        }

        /* Itinerary Days */
        .day-block {
            margin-bottom: 40px;
            page-break-inside: avoid;
        }
        .day-header {
            background-color: #111;
            color: #fff;
            padding: 10px 20px;
            font-size: 18px;
            font-family: 'Times New Roman', Times, serif;
        }
        .day-header span {
            color: #D4AF37;
            margin-right: 15px;
        }
        .day-content {
            padding: 20px 0;
        }
        .day-image {
            width: 100%;
            height: 250px;
            object-fit: cover;
            margin-bottom: 15px;
        }
        .day-text {
            font-size: 14px;
            color: #555;
            text-align: justify;
            padding: 0 20px;
        }

        /* Footer */
        .footer {
            text-align: center;
            padding: 20px;
            font-size: 12px;
            color: #888;
            background-color: #111;
            color: #fff;
            position: absolute;
            bottom: 0;
            width: 100%;
        }
        .footer span {
            color: #D4AF37;
        }
    </style>
</head>
<body>
    <!-- FIXED FOOTER ON EACH PAGE -->
    <footer>
        TYT LUXE | 831, Tower C, Bhutani Alphathum, Sector 90, Noida, UP - 201305 | www.tytluxe.in
    </footer>

    <main>
        <!-- COVER PAGE -->
        <div class="cover-page">
        <!-- Cover image, defaulting to Unsplash if none provided -->
        @php
            $coverImagePath = $package->image ? public_path('storage/' . $package->image) : 'https://images.unsplash.com/photo-1527668752968-14ce70a27dd3?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80';
        @endphp
        <img class="cover-image" src="{{ $coverImagePath }}" alt="{{ $package->title }}">
        <div class="cover-content">
            <div class="brand-name">TYT Luxe</div>
            <div class="cover-title">{{ $package->title }}</div>
            <div class="cover-subtitle">Your Exclusive Itinerary</div>
        </div>
    </div>

    <div class="page-break"></div>

    <!-- OVERVIEW PAGE -->
    <div class="content-page">
        <div class="header-small">
            <div class="brand-name">TYT Luxe</div>
        </div>

        <h2 class="section-title">Package Overview</h2>
        
        <div class="overview-box">
            <table>
                <tr>
                    <td class="overview-label">Destinations</td>
                    <td>{{ $package->destination ? $package->destination->name : 'Multiple Locations' }}</td>
                </tr>
                <tr>
                    <td class="overview-label">Duration</td>
                    <td>{{ $package->nights }} Nights / {{ $package->days }} Days</td>
                </tr>
                <tr>
                    <td class="overview-label">Accommodation</td>
                    <td>{{ $package->stay_info ?? 'Premium Resorts & Boutique Hotels' }}</td>
                </tr>
                <tr>
                    <td class="overview-label">Price</td>
                    <td>Starting from &#8377;{{ number_format($package->starting_price ?? 0) }} per person</td>
                </tr>
            </table>
        </div>

        <h2 class="section-title">Your Daily Journey</h2>

        @if($package->itineraries && $package->itineraries->count() > 0)
            @foreach($package->itineraries as $itinerary)
                <!-- DAY {{ $itinerary->day_number }} -->
                <div class="day-block">
                    <div class="day-header"><span>Day {{ $itinerary->day_number }}</span> {{ $itinerary->title }}</div>
                    <div class="day-content">
                        @if($itinerary->image)
                            <img class="day-image" src="{{ public_path('storage/' . $itinerary->image) }}" alt="Day {{ $itinerary->day_number }}">
                        @else
                            <img class="day-image" src="https://images.unsplash.com/photo-1515488764276-beab7607c1e6?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" alt="Day {{ $itinerary->day_number }}">
                        @endif
                        <div class="day-text">
                            {{ strip_tags($itinerary->description) }}
                        </div>
                    </div>
                </div>
            @endforeach
        @else
            <div class="day-block">
                <div class="day-content">
                    <div class="day-text">
                        Itinerary details will be provided soon.
                    </div>
                </div>
            </div>
        @endif

    </div>

    <!-- Old Footer removed as it is now fixed at the bottom via CSS -->
    </main>

</body>
</html>
