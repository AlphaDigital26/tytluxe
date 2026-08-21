@extends('layouts.frontend')

@section('meta_title', 'TYT Luxe — Luxury Hotels & Cruises | Book Your Dream Vacation')
@section('meta_description', 'Discover handpicked luxury hotels, premium cruises and tailored travel packages with TYT Luxe. Serving Indian travellers with personalised itineraries, veg-friendly options and a 2-hour WhatsApp response guarantee.')

@push('scripts')
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@@type": ["TravelAgency", "LocalBusiness"],
  "name": "TYT Luxe",
  "alternateName": "Take Your Trip Luxe",
  "url": "https://tytluxe.in",
  "logo": "{{ asset('assets/images/tyt-logo.png') }}",
  "image": "{{ asset('assets/images/og-image.jpg') }}",
  "description": "Handpicked luxury hotels and premium cruises for Indian travellers. Personalised travel planning with a 2-hour WhatsApp response guarantee.",
  "telephone": "+91-98750-73788",
  "priceRange": "\u20b9\u20b9\u20b9",
  "areaServed": { "@@type": "Country", "name": "India" },
  "sameAs": ["https://wa.me/919875073788"],
  "openingHoursSpecification": {
    "@@type": "OpeningHoursSpecification",
    "dayOfWeek": ["Monday","Tuesday","Wednesday","Thursday","Friday","Saturday"],
    "opens": "10:00",
    "closes": "19:00"
  }
}
</script>
@endpush

@section('content')

<!-- ===========================
        HERO SECTION
============================ -->

<section class="hero"
    style="background-image: url('{{ asset('assets/images/Carousel.jpeg') }}');">

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

                <img src="{{ asset('assets/images/29788-15-hotel_carousel_large.jpg') }}"
                    alt="Luxury hotel exterior with pool"
                    width="600" height="400"
                    loading="lazy">

                <div class="cat-overlay">

                    <h3>

                        <i class="fa-solid fa-hotel"></i>

                        Hotels

                    </h3>

                </div>

            </a>

            <a href="{{ url('/cruises') }}"
                class="cat-card">

                <img src="{{ asset('assets/images/cruise-ship-is-docked-night-with-word-cruise-side_916191-10755.jpg') }}"
                    alt="Luxury cruise ship docked at night"
                    width="600" height="400"
                    loading="lazy">

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
        <div class="section-header-split">
            <div>
                <span class="section-subtitle">Hotel Collections</span>
                <h2 class="section-title" style="margin-bottom: 0;">Find Your Perfect Stay</h2>
            </div>
            <a href="{{ url('/hotels') }}" class="btn btn-outline" style="color: var(--secondary); border-color: var(--secondary);">View All Hotels</a>
        </div>
        <div class="items-grid">
            <a href="{{ url('/hotels') }}" class="item-card">
                <img src="{{ asset('assets/images/Hotel Collections 1.webp') }}" alt="Beach resort with overwater villas" width="400" height="300" loading="lazy">
                <div class="item-info"><h4>Beach Resorts</h4></div>
            </a>
            <a href="{{ url('/hotels') }}" class="item-card">
                <img src="{{ asset('assets/images/Hotel Collections 2.jpg') }}" alt="City luxury hotel lobby" width="400" height="300" loading="lazy">
                <div class="item-info"><h4>City Luxury Hotels</h4></div>
            </a>
            <a href="{{ url('/hotels') }}" class="item-card">
                <img src="{{ asset('assets/images/Hotel Collections 3.jpg') }}" alt="Romantic honeymoon resort suite" width="400" height="300" loading="lazy">
                <div class="item-info"><h4>Honeymoon Stays</h4></div>
            </a>
            <a href="{{ url('/hotels') }}" class="item-card">
                <img src="{{ asset('assets/images/Hotel Collections 4.png') }}" alt="Family-friendly hotel with pool area" width="400" height="300" loading="lazy">
                <div class="item-info"><h4>Family Friendly</h4></div>
            </a>
        </div>
    </div>
</section>

<!-- Cruise Experiences -->
<section id="cruises" class="section-padding" style="background-color: var(--secondary); color: var(--text-light);">
    <div class="container">
        <div class="section-header-split">
            <div>
                <span class="section-subtitle">Cruise Experiences</span>
                <h2 class="section-title" style="margin-bottom: 0; color: var(--text-light);">Sail Beyond Ordinary</h2>
            </div>
            <a href="{{ url('/cruises') }}" class="btn btn-outline">View All Cruises</a>
        </div>
        <div class="items-grid cruise-grid">
            <a href="{{ url('/cruises') }}" class="item-card cruise-card">
                <img src="{{ asset('assets/images/Cruise Experiences 1.webp') }}" alt="Scenic coastal cruise getaway" width="400" height="300" loading="lazy">
                <div class="item-info"><h4>Scenic Getaways</h4></div>
            </a>
            <a href="{{ url('/cruises') }}" class="item-card cruise-card">
                <img src="{{ asset('assets/images/Cruise Experiences 2.png') }}" alt="Luxury cruise ship on open ocean" width="400" height="300" loading="lazy">
                <div class="item-info"><h4>Luxury Cruises</h4></div>
            </a>
            <a href="{{ url('/cruises') }}" class="item-card cruise-card">
                <img src="{{ asset('assets/images/Cruise Experiences 3.webp') }}" alt="International cruise at sea" width="400" height="300" loading="lazy">
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
            <!-- Review 1 -->
            <div class="test-card">
                <div class="test-quote-container">
                    <i class="fa-solid fa-quote-left test-quote-icon"></i>
                    <p class="test-quote">"Our anniversary trip to the Maldives was flawless. The team at TYT Luxe arranged everything perfectly, from the overwater villa to ensuring our specific dietary needs were met. Highly recommended!"</p>
                </div>
                <div class="test-user">
                    <img src="https://i.pravatar.cc/150?u=neha" alt="Testimonial from Neha Sharma" width="48" height="48" loading="lazy">
                    <div class="test-user-info">
                        <h5>Neha Sharma</h5>
                        <span>Mumbai</span>
                    </div>
                </div>
            </div>

            <!-- Review 2 -->
            <div class="test-card">
                <div class="test-quote-container">
                    <i class="fa-solid fa-quote-left test-quote-icon"></i>
                    <p class="test-quote">"Booking our family cruise was a breeze. They found us an amazing deal on Royal Caribbean and made sure we had great vegetarian food options onboard. The WhatsApp support gave us peace of mind."</p>
                </div>
                <div class="test-user">
                    <img src="https://i.pravatar.cc/150?u=rahul" alt="Testimonial from Rahul Desai" width="48" height="48" loading="lazy">
                    <div class="test-user-info">
                        <h5>Rahul Desai</h5>
                        <span>Ahmedabad</span>
                    </div>
                </div>
            </div>

            <!-- Review 3 -->
            <div class="test-card">
                <div class="test-quote-container">
                    <i class="fa-solid fa-quote-left test-quote-icon"></i>
                    <p class="test-quote">"TYT Luxe curated the most luxurious honeymoon in Bali. Every detail, from the private transfers to the romantic dinners, was taken care of. We didn't have to worry about a single thing."</p>
                </div>
                <div class="test-user">
                    <img src="https://i.pravatar.cc/150?u=amit" alt="Testimonial from Priya & Amit" width="48" height="48" loading="lazy">
                    <div class="test-user-info">
                        <h5>Priya & Amit</h5>
                        <span>Delhi</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Banner -->
<section class="cta-banner" style="background-image: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('{{ asset('assets/images/Carousel.jpeg') }}'); background-size: cover; background-position: center;">
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