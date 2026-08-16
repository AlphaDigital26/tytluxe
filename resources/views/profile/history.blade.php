@extends('layouts.frontend')

@section('content')

<!-- Profile Hero Section -->
<section class="profile-hero" style="background-image: linear-gradient(to bottom, rgba(0,0,0,0.4), rgba(0,0,0,1)), url('{{ asset('assets/images/Carousel.jpeg') }}');">
    <div class="profile-hero-inner">
        <div class="profile-avatar-container">
            <img src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name) }}&color=c9a84c&background=111&size=120" alt="{{ auth()->user()->name }}" class="profile-avatar">
        </div>
        <h1 class="profile-name">{{ auth()->user()->name }}</h1>
    </div>
</section>

<!-- Profile Content Section -->
<section class="profile-content-section">

    <div class="history-container">
        <div class="history-grid">
            
            <!-- LEFT COLUMN -->
            <div class="history-main">
                
                <!-- Upcoming Journeys -->
                <div class="history-section-title">
                    <h2>Upcoming Journeys</h2>
                </div>
                
                @forelse($upcomingBookings as $booking)
                <div class="journey-card">
                    @php
                        $imageUrl = 'https://images.unsplash.com/photo-1512453979798-5ea266f8880c?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80';
                        $title = 'Booking ' . $booking->reference;
                        $location = '';
                        if ($booking->vertical === 'hotel' && $booking->hotel) {
                            $title = $booking->hotel->name;
                            $location = $booking->hotel->destination ? $booking->hotel->destination->name : '';
                            if ($booking->hotel->images->count() > 0) {
                                $imageUrl = str_starts_with($booking->hotel->images->first()->image_path, 'http') ? $booking->hotel->images->first()->image_path : Storage::disk('public')->url($booking->hotel->images->first()->image_path);
                            }
                        } elseif ($booking->vertical === 'package' && $booking->package) {
                            $title = $booking->package->name;
                            $location = $booking->package->destination ? $booking->package->destination->name : '';
                            $imageUrl = $booking->package->hero_image_url;
                        } elseif ($booking->vertical === 'flight') {
                            $title = 'Flight: ' . $booking->flight_route;
                        }
                    @endphp
                    <img src="{{ $imageUrl }}" alt="{{ $title }}" class="journey-img">
                    <div class="journey-content">
                        <div class="journey-header">
                            <span class="status-badge">{{ ucfirst(str_replace('_', ' ', $booking->status)) }}</span>
                            <span>
                                @if($booking->check_in && $booking->check_out)
                                    {{ \Carbon\Carbon::parse($booking->check_in)->format('M d') }} - {{ \Carbon\Carbon::parse($booking->check_out)->format('M d, Y') }}
                                @elseif($booking->check_in)
                                    {{ \Carbon\Carbon::parse($booking->check_in)->format('M d, Y') }}
                                @endif
                            </span>
                        </div>
                        <h3>{{ $title }}</h3>
                        <div class="journey-location">
                            @if($location)
                                <svg viewBox="0 0 24 24"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/></svg>
                                {{ $location }}
                            @endif
                        </div>
                        <a href="#" class="btn btn-primary journey-btn">View Details</a>
                    </div>
                </div>
                @empty
                    <p style="padding: 10px; color: #666;">You have no upcoming journeys.</p>
                @endforelse

                <!-- Enquiries -->
                <div class="history-section-title">
                    <h2>Your Enquiries</h2>
                </div>

                <div class="concierge-list">
                    @forelse(auth()->user()->enquiries as $enquiry)
                        <div class="concierge-item">
                            <div class="concierge-icon">
                                <i class="fa-solid fa-clipboard-list" style="font-size: 24px; color: var(--primary);"></i>
                            </div>
                            <div class="concierge-info">
                                <div class="concierge-title">Enquiry for {{ ucfirst($enquiry->vertical) }}</div>
                                <div class="concierge-desc">
                                    Status: {{ ucfirst($enquiry->status) }} 
                                    @if($enquiry->travel_date_from)
                                    &bull; Travel Date: {{ \Carbon\Carbon::parse($enquiry->travel_date_from)->format('M d, Y') }}
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <p style="padding: 10px; color: #666;">You have no enquiries yet.</p>
                    @endforelse
                </div>

                <!-- Past Journeys -->
                <div class="history-section-title">
                    <h2>Past Journeys</h2>
                </div>
                
                <div class="past-journeys-grid">
                    @forelse($pastBookings as $booking)
                        @php
                            $imageUrl = 'https://images.unsplash.com/photo-1570077188670-e3a8d69ac5f1?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80';
                            $title = 'Booking ' . $booking->reference;
                            $location = '';
                            if ($booking->vertical === 'hotel' && $booking->hotel) {
                                $title = $booking->hotel->name;
                                $location = $booking->hotel->destination ? $booking->hotel->destination->name : '';
                                if ($booking->hotel->images->count() > 0) {
                                    $imageUrl = str_starts_with($booking->hotel->images->first()->image_path, 'http') ? $booking->hotel->images->first()->image_path : Storage::disk('public')->url($booking->hotel->images->first()->image_path);
                                }
                            } elseif ($booking->vertical === 'package' && $booking->package) {
                                $title = $booking->package->name;
                                $location = $booking->package->destination ? $booking->package->destination->name : '';
                                $imageUrl = $booking->package->hero_image_url;
                            } elseif ($booking->vertical === 'flight') {
                                $title = 'Flight: ' . $booking->flight_route;
                            }
                        @endphp
                        <div class="past-card">
                            <img src="{{ $imageUrl }}" alt="{{ $title }}" class="past-img">
                            <div class="past-content">
                                <div class="journey-header">
                                    <span class="status-badge" style="background: var(--white-10); color: var(--white-60); border: 1px solid var(--border-color);">{{ ucfirst(str_replace('_', ' ', $booking->status)) }}</span>
                                    <span>
                                        @if($booking->check_in)
                                            {{ \Carbon\Carbon::parse($booking->check_in)->format('M Y') }}
                                        @endif
                                    </span>
                                </div>
                                <h3 style="font-size: 20px;">{{ $title }}</h3>
                                <div class="journey-location" style="margin-bottom: 0;">
                                    @if($location)
                                        <svg viewBox="0 0 24 24"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/></svg>
                                        {{ $location }}
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <p style="padding: 10px; color: #666; grid-column: 1 / -1;">You have no past journeys.</p>
                    @endforelse
                </div>

            </div>
            
            <!-- RIGHT COLUMN -->
            <div class="history-sidebar">
                
                <!-- Saved Inspiration -->
                <div class="history-section-title">
                    <h2>Saved Inspiration</h2>
                </div>
                <div class="inspiration-grid">
                    <img src="https://images.unsplash.com/photo-1499793983690-e29da59ef1c2?ixlib=rb-4.0.3&w=300&q=80" alt="Insp" class="insp-img">
                    <img src="https://images.unsplash.com/photo-1540541338287-41700207dee6?ixlib=rb-4.0.3&w=300&q=80" alt="Insp" class="insp-img">
                    <div class="insp-img large">
                        <img src="https://images.unsplash.com/photo-1439066615861-d1af74d74000?ixlib=rb-4.0.3&w=600&q=80" alt="Villas" style="width:100%; height:100%; object-fit:cover; border-radius:inherit;">
                        <div class="insp-overlay">
                            <span>Overwater Villas</span>
                        </div>
                    </div>
                </div>


            </div>
        </div>



    </div>
</section>
@endsection
