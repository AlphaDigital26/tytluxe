@extends('layouts.frontend')

@section('content')

<!-- Profile Hero Section -->
<section class="profile-hero" style="background-image: linear-gradient(to bottom, rgba(0,0,0,0.4), rgba(0,0,0,1)), url('{{ asset('assets/images/Carousel.jpeg') }}');">
    <div class="profile-hero-inner">
        <div class="profile-avatar-container">
            <img src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name) }}&color=c9a84c&background=111&size=120" alt="{{ auth()->user()->name }}" class="profile-avatar">
        </div>
        <h1 class="profile-name">{{ auth()->user()->name }}</h1>
        <div class="profile-badge">
            <i class="fa-regular fa-star"></i> Platinum Member
        </div>
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
                
                <div class="journey-card">
                    <img src="https://images.unsplash.com/photo-1512453979798-5ea266f8880c?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80" alt="Dubai" class="journey-img">
                    <div class="journey-content">
                        <div class="journey-header">
                            <span class="status-badge">Confirmed</span>
                            <span>Oct 12 - 24, 2026</span>
                        </div>
                        <h3>A Desert Oasis Experience</h3>
                        <div class="journey-location">
                            <svg viewBox="0 0 24 24"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/></svg>
                            Dubai, UAE
                        </div>
                        <a href="#" class="btn btn-primary journey-btn">View Itinerary</a>
                    </div>
                </div>

                <!-- Concierge Requests -->
                <div class="history-section-title">
                    <h2>Concierge Requests</h2>
                    <a href="#" class="history-section-link">New Request</a>
                </div>

                <div class="concierge-list">
                    <!-- Request 1 -->
                    <div class="concierge-item">
                        <div class="concierge-icon">
                            <svg viewBox="0 0 24 24"><path d="M20 21c-1.39 0-2.78-.47-4-1.32-2.44 1.71-5.56 1.71-8 0C6.78 20.53 5.39 21 4 21H2v2h2c1.38 0 2.74-.35 4-.99 2.52 1.29 5.48 1.29 8 0 1.26.65 2.62.99 4 .99h2v-2h-2zM3.95 19H4c1.6 0 3.02-.88 4-2 .98 1.12 2.4 2 4 2s3.02-.88 4-2c.98 1.12 2.4 2 4 2h.05l1.89-6.68c.08-.26.06-.54-.06-.78s-.34-.42-.6-.5L20 11v-1c0-2.21-1.79-4-4-4h-2.5C12.67 4.26 10.97 3 9 3c-2.76 0-5 2.24-5 5v3L2.72 11.04c-.26.08-.48.26-.6.5s-.14.52-.06.78L3.95 19zM6 8c0-1.65 1.35-3 3-3s3 1.35 3 3v3H6V8zm12 3h-2v1h2v.92l-1.33 4.67C15.84 16.71 14.53 16 13 16c-1.4 0-2.67.62-3.5 1.6C8.67 16.62 7.4 16 6 16c-1.53 0-2.84.71-3.67 1.59L1 12.92V12h17v-1z"/></svg>
                        </div>
                        <div class="concierge-info">
                            <div class="concierge-title">Private Yacht Charter</div>
                            <div class="concierge-desc">Maldives &bull; In Progress</div>
                        </div>
                        <div class="concierge-arrow">
                            <svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor"><path d="M12 4l-1.41 1.41L16.17 11H4v2h12.17l-5.58 5.59L12 20l8-8z"/></svg>
                        </div>
                    </div>
                    
                    <!-- Request 2 -->
                    <div class="concierge-item">
                        <div class="concierge-icon">
                            <svg viewBox="0 0 24 24"><path d="M11 9H9V2H7v7H5V2H3v7c0 2.12 1.66 3.84 3.75 3.97V22h2.5v-9.03C11.34 12.84 13 11.12 13 9V2h-2v7zm5-3v8h2.5v8H21V2c-2.76 0-5 2.24-5 4z"/></svg>
                        </div>
                        <div class="concierge-info">
                            <div class="concierge-title">Michelin Star Dinner Reservation</div>
                            <div class="concierge-desc">Tokyo &bull; Awaiting Confirmation</div>
                        </div>
                        <div class="concierge-arrow">
                            <svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor"><path d="M12 4l-1.41 1.41L16.17 11H4v2h12.17l-5.58 5.59L12 20l8-8z"/></svg>
                        </div>
                    </div>
                </div>

                <!-- Past Journeys -->
                <div class="history-section-title">
                    <h2>Past Journeys</h2>
                </div>
                
                <div class="past-journeys-grid">
                    <!-- Past 1 -->
                    <div class="past-card">
                        <img src="https://images.unsplash.com/photo-1570077188670-e3a8d69ac5f1?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80" alt="Santorini" class="past-img">
                        <div class="past-content">
                            <div class="journey-header">
                                <span class="status-badge" style="background: var(--white-10); color: var(--white-60); border: 1px solid var(--border-color);">Completed</span>
                                <span>June 2024</span>
                            </div>
                            <h3 style="font-size: 20px;">Serenity in Santorini</h3>
                            <div class="journey-location" style="margin-bottom: 0;">
                                <svg viewBox="0 0 24 24"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/></svg>
                                Greece
                            </div>
                        </div>
                    </div>
                    
                    <!-- Past 2 -->
                    <div class="past-card">
                        <img src="https://images.unsplash.com/photo-1522792065601-5d09cb61f89d?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80" alt="Alpine Escape" class="past-img">
                        <div class="past-content">
                            <div class="journey-header">
                                <span class="status-badge" style="background: var(--white-10); color: var(--white-60); border: 1px solid var(--border-color);">Completed</span>
                                <span>December 2023</span>
                            </div>
                            <h3 style="font-size: 20px;">Alpine Escape</h3>
                            <div class="journey-location" style="margin-bottom: 0;">
                                <svg viewBox="0 0 24 24"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/></svg>
                                Switzerland
                            </div>
                        </div>
                    </div>
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

                <!-- Platinum Benefits -->
                <div class="history-section-title" style="margin-top: 3rem;">
                    <h2 style="font-size: 20px; font-family: 'Jost', sans-serif; font-weight: 500;">Platinum Benefits</h2>
                </div>
                <div class="benefits-card">
                    
                    <div class="benefit-item">
                        <div class="benefit-icon">
                            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                        </div>
                        <div>
                            <div class="benefit-title">Airport Lounge Access</div>
                            <div class="benefit-desc">Complimentary global access for two.</div>
                        </div>
                    </div>
                    
                    <div class="benefit-item">
                        <div class="benefit-icon">
                            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                        </div>
                        <div>
                            <div class="benefit-title">Complimentary Upgrades</div>
                            <div class="benefit-desc">Room upgrades upon arrival, subject to availability.</div>
                        </div>
                    </div>
                    
                    <div class="benefit-item">
                        <div class="benefit-icon">
                            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                        </div>
                        <div>
                            <div class="benefit-title">Priority Concierge</div>
                            <div class="benefit-desc">24/7 direct access to dedicated specialists.</div>
                        </div>
                    </div>

                </div>

            </div>
        </div>

        <!-- BOTTOM ROW -->
        <div class="history-section-title" style="margin-top: 2rem;">
            <h2>Your Preferences</h2>
        </div>
        
        <div class="pref-grid">
            <div class="pref-card">
                <div class="pref-header">
                    <svg viewBox="0 0 24 24"><path d="M11 9H9V2H7v7H5V2H3v7c0 2.12 1.66 3.84 3.75 3.97V22h2.5v-9.03C11.34 12.84 13 11.12 13 9V2h-2v7zm5-3v8h2.5v8H21V2c-2.76 0-5 2.24-5 4z"/></svg>
                    Dietary
                </div>
                <div class="pref-tags">
                    <span class="pref-tag">Vegan</span>
                    <span class="pref-tag">Gluten-free</span>
                </div>
            </div>
            
            <div class="pref-card">
                <div class="pref-header">
                    <svg viewBox="0 0 24 24"><path d="M21 16v-2l-8-5V3.5c0-.83-.67-1.5-1.5-1.5S10 2.67 10 3.5V9l-8 5v2l8-2.5V19l-2 1.5V22l3.5-1 3.5 1v-1.5L13 19v-5.5l8 2.5z"/></svg>
                    Cabin Class
                </div>
                <div class="pref-tags">
                    <span class="pref-tag">First Class</span>
                </div>
            </div>
            
            <div class="pref-card">
                <div class="pref-header">
                    <svg viewBox="0 0 24 24"><path d="M7 13c1.66 0 3-1.34 3-3S8.66 7 7 7s-3 1.34-3 3 1.34 3 3 3zm12-6h-8v7H3V5H1v15h2v-3h18v3h2v-9c0-2.21-1.79-4-4-4z"/></svg>
                    Room Type
                </div>
                <div class="pref-tags">
                    <span class="pref-tag">High Floor</span>
                    <span class="pref-tag">Ocean View</span>
                </div>
            </div>
        </div>

    </div>
</section>
@endsection
