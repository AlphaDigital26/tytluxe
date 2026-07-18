<header>
    <div class="container">
        <div class="header-inner">

            <!-- Logo -->
            <div class="logo">
                <a href="{{ url('/') }}">
                    <img src="{{ asset('assets/images/logo-white.png') }}" alt="TYT Luxe">
                </a>
            </div>

            <!-- Navigation -->
            <nav class="nav-menu">
                <ul>
                    <li><a href="{{ url('/') }}">Home</a></li>
                    <li><a href="{{ url('/about') }}">About Us</a></li>
                    <li><a href="{{ url('/hotels') }}">Hotels</a></li>
                    <li><a href="{{ url('/flights') }}">Flights</a></li>
                    <li><a href="{{ url('/cruises') }}">Cruises</a></li>
                    <li><a href="{{ url('/staycation') }}">Staycation</a></li>
                    <li><a href="{{ url('/offers') }}">Offers</a></li>
                    <li><a href="{{ url('/contact') }}">Contact Us</a></li>
                </ul>
            </nav>

            <!-- Header Buttons -->
            <div class="header-contact">

                <a class="header-cta header-cta-call"
                   href="tel:+919875073788">
                    <i class="fa-solid fa-phone"></i>
                    Call Now
                </a>

                <a class="header-cta header-cta-wa"
                   href="https://wa.me/919875073788"
                   target="_blank">
                    <i class="fa-brands fa-whatsapp"></i>
                    WhatsApp
                </a>

                <a href="#" class="mobile-toggle">
                    <i class="fa-solid fa-bars"></i>
                </a>

            </div>

        </div>
    </div>
</header>