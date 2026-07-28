@extends('layouts.frontend')

@section('content')

<!-- ===========================
        HERO SECTION
============================ -->

<section class="hero"
    style="background-image: url('{{ asset('assets/images/hero-bg.png') }}');">

    <div class="container">

        <div class="hero-content">

            <h1>

                Experience <br>

                <span class="highlight">
                    Travel
                </span>

                <br>

                the Right Way

            </h1>

            <p>

                Handpicked Hotels & Cruises <br>

                Curated for Comfort, Luxury & Value

            </p>

            <div class="hero-btns">

                <a href="{{ url('/hotels') }}"
                    class="btn btn-primary">

                    Explore Hotels

                </a>

                <a href="{{ url('/cruises') }}"
                    class="btn btn-outline">

                    Explore Cruises

                </a>

            </div>

            <div class="hero-contact">

                <i class="fa-brands fa-whatsapp"></i>

                Call / WhatsApp : 98750 73788

            </div>

        </div>

    </div>

</section>

<!-- ===========================
    STAY • SAIL • EXPLORE
============================ -->

<section id="hotels"
    class="section-padding">

    <div class="container">

        <div class="section-header">

            <span class="section-subtitle">

                Explore What Moves You

            </span>

            <h2 class="section-title">

                Stay. Sail. Explore.

            </h2>

        </div>

        <div class="cat-grid">

            <a href="{{ url('/hotels') }}"
                class="cat-card">

                <img src="{{ asset('assets/images/hotel-cat.png') }}"
                    alt="Hotels">

                <div class="cat-overlay">

                    <h3>

                        <i class="fa-solid fa-hotel"></i>

                        Hotels

                    </h3>

                </div>

            </a>

            <a href="{{ url('/cruises') }}"
                class="cat-card">

                <img src="{{ asset('assets/images/cruise-cat.png') }}"
                    alt="Cruises">

                <div class="cat-overlay">

                    <h3>

                        <i class="fa-solid fa-ship"></i>

                        Cruises

                    </h3>

                </div>

            </a>

        </div>

    </div>

</section>

<!-- ===========================
        FEATURES
============================ -->

<section class="container">

    <div class="features-grid">

        <div class="feature-item">

            <i class="fa-solid fa-tag"></i>

            <h4>

                Zero Hidden Fees

            </h4>

            <p>

                Transparent pricing,
                no surprises

            </p>

        </div>

        <div class="feature-item">

            <i class="fa-solid fa-headset"></i>

            <h4>

                24 / 7 Support

            </h4>

            <p>

                Call or WhatsApp anytime

            </p>

        </div>

        <div class="feature-item">

            <i class="fa-solid fa-medal"></i>

            <h4>

                Best Fare Guarantee

            </h4>

            <p>

                We find the best
                price for you

            </p>

        </div>

        <div class="feature-item">

            <i class="fa-solid fa-rotate"></i>

            <h4>

                Flexible Changes

            </h4>

            <p>

                Hassle-free
                rescheduling help

            </p>

        </div>

    </div>

</section>

<!-- Hotel Collections -->
<section id="hotel-collections" class="section-padding">
    <div class="container">
        <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 40px;">
            <div>
                <span class="section-subtitle">Hotel Collections</span>
                <h2 class="section-title" style="margin-bottom: 0;">Find Your Perfect Stay</h2>
            </div>
            <a href="{{ url('/hotels') }}" class="btn btn-outline" style="color: var(--secondary); border-color: var(--secondary);">View All Hotels</a>
        </div>
        <div class="items-grid">
            <a href="{{ url('/hotels') }}" class="item-card">
                <img src="{{ asset('assets/images/hotel-1.png') }}" alt="Beach Resorts">
                <div class="item-info"><h4>Beach Resorts</h4></div>
            </a>
            <a href="{{ url('/hotels') }}" class="item-card">
                <img src="{{ asset('assets/images/hotel-2.png') }}" alt="City Luxury Hotels">
                <div class="item-info"><h4>City Luxury Hotels</h4></div>
            </a>
            <a href="{{ url('/hotels') }}" class="item-card">
                <img src="{{ asset('assets/images/hotel-3.png') }}" alt="Honeymoon Stays">
                <div class="item-info"><h4>Honeymoon Stays</h4></div>
            </a>
            <a href="{{ url('/hotels') }}" class="item-card">
                <img src="{{ asset('assets/images/hotel-1.png') }}" alt="Family Friendly">
                <div class="item-info"><h4>Family Friendly</h4></div>
            </a>
        </div>
    </div>
</section>

<!-- Cruise Experiences -->
<section id="cruises" class="section-padding" style="background-color: var(--secondary); color: var(--text-light);">
    <div class="container">
        <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 40px;">
            <div>
                <span class="section-subtitle">Cruise Experiences</span>
                <h2 class="section-title" style="margin-bottom: 0; color: var(--text-light);">Sail Beyond Ordinary</h2>
            </div>
            <a href="{{ url('/cruises') }}" class="btn btn-outline">View All Cruises</a>
        </div>
        <div class="items-grid cruise-grid">
            <a href="{{ url('/cruises') }}" class="item-card cruise-card">
                <img src="{{ asset('assets/images/cruise-cat.png') }}" alt="Scenic Getaways">
                <div class="item-info"><h4>Scenic Getaways</h4></div>
            </a>
            <a href="{{ url('/cruises') }}" class="item-card cruise-card">
                <img src="{{ asset('assets/images/cruise-cat.png') }}" alt="Luxury Cruises">
                <div class="item-info"><h4>Luxury Cruises</h4></div>
            </a>
            <a href="{{ url('/cruises') }}" class="item-card cruise-card">
                <img src="{{ asset('assets/images/cruise-cat.png') }}" alt="International Cruises">
                <div class="item-info"><h4>International Cruises</h4></div>
            </a>
        </div>
    </div>
</section>

<!-- Offers Banner -->
<section id="offers" style="background-color: #1a1a1a; padding: 40px 0;">
    <div class="container">
        <div class="cta-banner-inner" style="background-color: #2a241e; padding: 40px; border-radius: 8px;">
            <div>
                <span class="section-subtitle">Limited Time Offers</span>
                <h2 style="color: var(--text-light);">Exclusive Deals. Unforgettable Experiences.</h2>
            </div>
            <a href="{{ url('/offers') }}" class="btn btn-primary">Explore Offers</a>
        </div>
    </div>
</section>

<!-- Testimonials -->
<section class="section-padding">
    <div class="container">
        <div class="section-header">
            <span class="section-subtitle">Travelers Love Us</span>
            <h2 class="section-title">Trusted by Travelers</h2>
        </div>
        <div class="test-grid">
            @for ($i = 1; $i <= 3; $i++)
            <div class="test-card">
                <div class="test-quote-container">
                    <i class="fa-solid fa-quote-left test-quote-icon"></i>
                    <p class="test-quote">Amazing experience!</p>
                </div>
                <div class="test-user">
                    <img src="https://i.pravatar.cc/150?u=user{{ $i }}" alt="User {{ $i }}">
                    <div class="test-user-info">
                        <h5>User {{ $i }}</h5>
                        <span>Location</span>
                    </div>
                </div>
            </div>
            @endfor
        </div>
    </div>
</section>

<!-- CTA Banner -->
<section class="cta-banner" style="background-image: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('{{ asset('assets/images/hero-bg.png') }}'); background-size: cover; background-position: center;">
    <div class="container">
        <div class="cta-banner-inner">
            <div>
                <h2 style="color: var(--text-light); font-size: 40px;">Ready to Plan Your Next Trip?</h2>
                <p style="margin-top: 10px;">Call / WhatsApp: 98750 73788</p>
            </div>
            <a href="{{ url('/contact') }}" class="btn btn-primary" style="padding: 15px 40px;">Get In Touch</a>
        </div>
    </div>
</section>

@endsection