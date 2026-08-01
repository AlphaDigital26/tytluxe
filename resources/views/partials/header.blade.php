<header>
    <div class="container">
        <div class="header-inner">

            <!-- Logo -->
            <div class="logo">
                <a href="{{ url('/') }}">
                    <img src="{{ asset('assets/images/tyt-logo.png') }}" alt="TYT Luxe">
                </a>
            </div>

            <!-- Navigation -->
            <nav class="nav-menu">
                <div class="mobile-menu-header">
                    <img src="{{ asset('assets/images/tyt-logo.png') }}" alt="TYT Luxe">
                    <button class="menu-close"><i class="fa-solid fa-xmark"></i></button>
                </div>
                <ul>
                    <li><a href="{{ url('/') }}" class="{{ request()->is('/') ? 'active' : '' }}">Home</a></li>
                    <li><a href="{{ url('/about') }}" class="{{ request()->is('about') ? 'active' : '' }}">About Us</a></li>
                    <li><a href="{{ url('/hotels') }}" class="{{ request()->is('hotels') ? 'active' : '' }}">Hotels</a></li>
                    <li><a href="{{ url('/flights') }}" class="{{ request()->is('flights') ? 'active' : '' }}">Flights</a></li>
                    <li><a href="{{ url('/cruises') }}" class="{{ request()->is('cruises') ? 'active' : '' }}">Cruises</a></li>
                    <li><a href="{{ url('/staycation') }}" class="{{ request()->is('staycation') ? 'active' : '' }}">Staycation</a></li>
                    <li><a href="{{ url('/packages') }}" class="{{ request()->is('packages') ? 'active' : '' }}">Packages</a></li>
                    <li><a href="{{ url('/offers') }}" class="{{ request()->is('offers') ? 'active' : '' }}">Offers</a></li>
                    <li><a href="{{ url('/contact') }}" class="{{ request()->is('contact') ? 'active' : '' }}">Contact Us</a></li>
                </ul>
            </nav>

            <!-- Header Buttons -->
            <div class="header-contact">

                <a class="header-cta header-cta-call"
                   href="tel:+919875073788">
                    <i class="fa-solid fa-phone"></i>
                    <span>Call Now</span>
                </a>

                <a class="header-cta header-cta-wa"
                   href="https://wa.me/919875073788"
                   target="_blank">
                    <i class="fa-brands fa-whatsapp"></i>
                    <span>WhatsApp</span>
                </a>

                <a href="#" class="mobile-toggle">
                    <i class="fa-solid fa-bars"></i>
                </a>

            </div>

        </div>
    </div>
</header>