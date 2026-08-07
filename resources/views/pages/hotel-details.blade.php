@extends('layouts.frontend')

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com" />
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;1,400;1,600&family=Jost:wght@300;400;500;600&display=swap" rel="stylesheet" />
<style>
/* Reusing core luxury variables */
:root {
  --gold: #c9a84c;
  --gold-light: #e8c96b;
  --gold-dim: rgba(201,168,76,0.15);
  --dark: #0d0d0d;
  --dark-2: #141414;
  --dark-3: #1a1a1a;
  --radius: 12px;
}
body { background: var(--dark); color: #fff; }

.hd-hero {
  position: relative; width: 100%; height: 60vh; min-height: 400px;
}
.hd-hero img {
  width: 100%; height: 100%; object-fit: cover;
}
.hd-hero-overlay {
  position: absolute; inset: 0; background: linear-gradient(to top, var(--dark) 0%, rgba(13,13,13,0) 60%);
}
.hd-hero-content {
  position: absolute; bottom: 0; left: 0; right: 0; padding: 40px; max-width: 1200px; margin: 0 auto;
}
.hd-badge {
  background: var(--gold); color: var(--dark); font-family: 'Jost', sans-serif; font-size: 11px;
  font-weight: 700; letter-spacing: 0.12em; text-transform: uppercase; padding: 5px 14px; border-radius: 100px;
  display: inline-block; margin-bottom: 12px;
}
.hd-title {
  font-family: 'Cormorant Garamond', serif; font-size: clamp(2.5rem, 4vw, 4rem); font-weight: 500; line-height: 1.1; margin-bottom: 8px;
}
.hd-location {
  font-family: 'Jost', sans-serif; font-size: 15px; color: rgba(255,255,255,0.7); display: flex; align-items: center; gap: 8px;
}

.hd-container {
  max-width: 1200px; margin: 0 auto; padding: 60px 40px;
  display: grid; grid-template-columns: 2fr 1fr; gap: 48px;
}

.hd-section { margin-bottom: 48px; }
.hd-section-title {
  font-family: 'Cormorant Garamond', serif; font-size: 2rem; font-weight: 500; margin-bottom: 24px; color: var(--gold);
  display: flex; align-items: center; gap: 12px;
}
.hd-section-title::after { content: ''; flex: 1; height: 1px; background: var(--gold-dim); }

.hd-desc { font-family: 'Jost', sans-serif; font-size: 15.5px; font-weight: 300; line-height: 1.8; color: rgba(255,255,255,0.8); }

.hd-amenities { display: flex; flex-wrap: wrap; gap: 12px; }
.hd-amenity {
  border: 1px solid rgba(255,255,255,0.15); padding: 10px 18px; border-radius: 100px;
  font-family: 'Jost', sans-serif; font-size: 13.5px; color: rgba(255,255,255,0.7);
  display: flex; align-items: center; gap: 8px;
}
.hd-amenity::before { content: ''; width: 6px; height: 6px; border-radius: 50%; background: var(--gold); }

/* Rooms Grid */
.hd-rooms { display: grid; gap: 20px; }
.hd-room-card {
  background: var(--dark-2); border: 1px solid rgba(255,255,255,0.06); border-radius: var(--radius);
  padding: 24px; display: flex; justify-content: space-between; align-items: center; gap: 20px; flex-wrap: wrap;
}
.hd-room-info h4 { font-family: 'Cormorant Garamond', serif; font-size: 1.6rem; font-weight: 600; margin-bottom: 8px; }
.hd-room-inclusions { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 10px; }
.hd-room-inc { font-family: 'Jost', sans-serif; font-size: 12px; background: rgba(201,168,76,0.1); color: var(--gold); padding: 4px 10px; border-radius: 6px; }

.hd-room-price-wrap { text-align: right; }
.hd-room-price { font-family: 'Jost', sans-serif; font-size: 1.8rem; font-weight: 600; color: #fff; margin-bottom: 4px; }
.hd-room-note { font-family: 'Jost', sans-serif; font-size: 12px; color: rgba(255,255,255,0.4); margin-bottom: 12px; }
.hd-book-btn {
  background: var(--gold); color: var(--dark); font-family: 'Jost', sans-serif; font-size: 13px; font-weight: 700;
  padding: 10px 24px; border-radius: 100px; text-decoration: none; display: inline-block; text-transform: uppercase; letter-spacing: 0.1em;
}

/* Sidebar */
.hd-sidebar-box {
  background: var(--dark-2); border: 1px solid var(--gold-dim); border-radius: var(--radius); padding: 32px;
  position: sticky; top: 120px;
}
.hd-sidebar-title { font-family: 'Cormorant Garamond', serif; font-size: 1.8rem; margin-bottom: 20px; text-align: center; }
.hd-sidebar-btn {
  display: block; width: 100%; text-align: center; background: #25D366; color: #fff; text-decoration: none;
  font-family: 'Jost', sans-serif; font-size: 14px; font-weight: 600; padding: 14px; border-radius: 100px; margin-top: 20px;
}

@media (max-width: 900px) {
  .hd-container { grid-template-columns: 1fr; }
}
</style>
@endpush

@section('content')

@php
    $name = $hotel['name'] ?? 'Unknown Hotel';
    $desc = $hotel['description'] ?? 'No description available.';
    $rating = $hotel['rt'] ?? 3;
    $category = $hotel['pt'] ?? 'Hotel';
    $city = $hotel['ad']['city']['name'] ?? '';
    $country = $hotel['ad']['country']['name'] ?? '';
    $address = trim($hotel['ad']['adr'] . ', ' . $city . ', ' . $country, ', ');
    $images = $hotel['img'] ?? [];
    $heroImg = count($images) > 0 ? $images[0]['url'] : 'https://images.unsplash.com/photo-1566073771259-6a8506099945?w=1600&q=80';
    $amenities = $hotel['fl'] ?? [];
    $rooms = $hotel['rooms'] ?? [];
@endphp

<div class="hd-hero">
    <img src="{{ $heroImg }}" alt="{{ $name }}">
    <div class="hd-hero-overlay"></div>
    <div class="hd-hero-content">
        <span class="hd-badge">{{ $rating }} Star {{ $category }}</span>
        <h1 class="hd-title">{{ $name }}</h1>
        <div class="hd-location">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
            {{ $address }}
        </div>
    </div>
</div>

<div class="hd-container">
    <div class="hd-main">
        <div class="hd-section">
            <h2 class="hd-section-title">About The Property</h2>
            <p class="hd-desc">{{ $desc }}</p>
        </div>

        @if(count($amenities) > 0)
        <div class="hd-section">
            <h2 class="hd-section-title">Facilities & Amenities</h2>
            <div class="hd-amenities">
                @foreach($amenities as $amenity)
                    <span class="hd-amenity">{{ $amenity }}</span>
                @endforeach
            </div>
        </div>
        @endif

        @if(count($rooms) > 0)
        <div class="hd-section">
            <h2 class="hd-section-title">Available Rooms</h2>
            <div class="hd-rooms">
                @foreach($rooms as $room)
                <div class="hd-room-card">
                    <div class="hd-room-info">
                        <h4>{{ $room['name'] ?? 'Standard Room' }}</h4>
                        <div class="hd-room-inclusions">
                            @foreach($room['inclusions'] ?? [] as $inc)
                                <span class="hd-room-inc">{{ $inc }}</span>
                            @endforeach
                        </div>
                    </div>
                    <div class="hd-room-price-wrap">
                        <div class="hd-room-price">₹{{ number_format($room['price'] ?? 0) }}</div>
                        <div class="hd-room-note">Tripjack Live Rate (per night)</div>
                        <a href="https://wa.me/919875073788?text={{ urlencode('I want to book the ' . ($room['name'] ?? 'Room') . ' at ' . $name) }}" class="hd-book-btn" target="_blank">Book Now</a>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    <div class="hd-sidebar">
        <div class="hd-sidebar-box">
            <h3 class="hd-sidebar-title">Need Help Booking?</h3>
            <p class="hd-desc" style="text-align: center; font-size: 14px;">Speak to our travel experts to customize your stay, arrange airport transfers, or request special arrangements.</p>
            <a href="https://wa.me/919875073788?text={{ urlencode('Hi, I need help booking ' . $name) }}" class="hd-sidebar-btn" target="_blank">Chat on WhatsApp</a>
        </div>
    </div>
</div>

@endsection
