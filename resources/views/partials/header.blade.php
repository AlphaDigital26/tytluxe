<header role="banner">
    <div class="container">
        <div class="header-inner">

            <!-- Logo -->
            <div class="logo">
                <a href="{{ url('/') }}" aria-label="TYT Luxe — Go to homepage">
                    <img src="{{ asset('assets/images/tyt-logo.png') }}" alt="TYT Luxe logo" width="140" height="48">
                </a>
            </div>

            <!-- Navigation -->
            <nav class="nav-menu" aria-label="Main navigation">
                <div class="mobile-menu-header">
                    <img src="{{ asset('assets/images/tyt-logo.png') }}" alt="TYT Luxe logo" width="120" height="40">
                    <button class="menu-close" aria-label="Close navigation menu">
                        <i class="fa-solid fa-xmark" aria-hidden="true"></i>
                    </button>
                </div>
                <ul role="list">
                    <li><a href="{{ url('/') }}" class="{{ request()->is('/') ? 'active' : '' }}" {{ request()->is('/') ? 'aria-current=page' : '' }}>Home</a></li>
                    <li><a href="{{ url('/about') }}" class="{{ request()->is('about') ? 'active' : '' }}" {{ request()->is('about') ? 'aria-current=page' : '' }}>About Us</a></li>
                    <li><a href="{{ url('/hotels') }}" class="{{ request()->is('hotels') ? 'active' : '' }}" {{ request()->is('hotels') ? 'aria-current=page' : '' }}>Hotels</a></li>
                    <li><a href="{{ url('/flights') }}" class="{{ request()->is('flights') ? 'active' : '' }}" {{ request()->is('flights') ? 'aria-current=page' : '' }}>Flights</a></li>
                    <li><a href="{{ url('/cruises') }}" class="{{ request()->is('cruises') ? 'active' : '' }}" {{ request()->is('cruises') ? 'aria-current=page' : '' }}>Cruises</a></li>
                    <li class="nav-has-dropdown {{ request()->is('packages') ? 'nav-dropdown-active' : '' }}">
                        <a href="{{ url('/packages') }}" class="{{ request()->is('packages') ? 'active' : '' }}" {{ request()->is('packages') ? 'aria-current=page' : '' }} aria-haspopup="true" aria-expanded="false">
                            Packages <i class="fa-solid fa-chevron-down nav-arrow" aria-hidden="true"></i>
                        </a>
                        <ul class="nav-dropdown-menu" role="list">
                            <li>
                                <a href="{{ url('/packages') }}?tab=domestic" onclick="setPackageTab('domestic')">
                                    <span class="nav-dd-icon"><i class="fa-solid fa-map-location-dot" aria-hidden="true"></i></span>
                                    <span class="nav-dd-text">
                                        <strong>Domestic</strong>
                                        <small>Explore India</small>
                                    </span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ url('/packages') }}?tab=international" onclick="setPackageTab('international')">
                                    <span class="nav-dd-icon"><i class="fa-solid fa-earth-americas" aria-hidden="true"></i></span>
                                    <span class="nav-dd-text">
                                        <strong>International</strong>
                                        <small>World Destinations</small>
                                    </span>
                                </a>
                            </li>
                        </ul>
                    </li>

                    <li><a href="{{ url('/offers') }}" class="{{ request()->is('offers') ? 'active' : '' }}" {{ request()->is('offers') ? 'aria-current=page' : '' }}>Offers</a></li>
                    <li><a href="{{ url('/contact') }}" class="{{ request()->is('contact') ? 'active' : '' }}" {{ request()->is('contact') ? 'aria-current=page' : '' }}>Contact Us</a></li>
                </ul>
            </nav>

            <!-- Header Buttons -->
            <div class="header-contact">

                @auth
                    <div class="header-dropdown">
                        <button
                            class="header-cta"
                            onclick="toggleProfileDropdown(event)"
                            aria-label="Open profile menu"
                            aria-haspopup="true"
                            aria-expanded="false"
                            aria-controls="profileDropdown"
                            style="background: transparent; border: 1px solid var(--primary); color: var(--primary); cursor: pointer; padding: 10px 14px;"
                        >
                            <i class="fa-solid fa-user" aria-hidden="true"></i>
                        </button>
                        <div class="header-dropdown-menu" id="profileDropdown" role="menu">
                            <a href="{{ route('profile.edit') }}" role="menuitem"><i class="fa-solid fa-user" aria-hidden="true"></i> Profile</a>
                            <a href="{{ route('history') }}" role="menuitem"><i class="fa-solid fa-suitcase" aria-hidden="true"></i> Booking History</a>
                            <form method="POST" action="{{ route('logout') }}" style="margin: 0;">
                                @csrf
                                <button type="submit" role="menuitem"><i class="fa-solid fa-right-from-bracket" aria-hidden="true"></i> Logout</button>
                            </form>
                        </div>
                    </div>
                @else
                    <a
                        class="header-cta"
                        style="border: 1px solid rgba(255,255,255,0.25); color: var(--text-light); padding: 10px 16px;"
                        href="{{ route('login') }}"
                        aria-label="Login or Sign Up"
                    >
                        <i class="fa-solid fa-user" aria-hidden="true"></i>
                        <span class="hidden-mobile">Login</span>
                    </a>
                @endauth

                <button
                    class="mobile-toggle"
                    aria-label="Open navigation menu"
                    aria-expanded="false"
                    aria-controls="nav-menu"
                >
                    <i class="fa-solid fa-bars" aria-hidden="true"></i>
                </button>

            </div>

        </div>
    </div>
</header>

<script>
    function toggleProfileDropdown(e) {
        e.stopPropagation();
        const dropdown = document.getElementById('profileDropdown');
        const btn = e.currentTarget;
        const isOpen = dropdown && dropdown.classList.contains('show');
        if (dropdown) dropdown.classList.toggle('show');
        if (btn) btn.setAttribute('aria-expanded', String(!isOpen));
    }

    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        let dropdown = document.getElementById('profileDropdown');
        if (dropdown && dropdown.classList.contains('show')) {
            dropdown.classList.remove('show');
            const btn = document.querySelector('[aria-controls="profileDropdown"]');
            if (btn) btn.setAttribute('aria-expanded', 'false');
        }
    });

    // Store chosen package category tab before navigating
    function setPackageTab(tab) {
        sessionStorage.setItem('pkgActiveTab', tab);
    }

    // Mobile: toggle dropdown accordion for Packages nav item
    document.addEventListener('DOMContentLoaded', function() {
        var dropdownLi = document.querySelector('.nav-has-dropdown');
        if (!dropdownLi) return;
        var dropdownLink = dropdownLi.querySelector(':scope > a');
        if (!dropdownLink) return;

        dropdownLink.addEventListener('click', function(e) {
            // Only intercept on mobile
            if (window.innerWidth <= 768) {
                e.preventDefault();
                var isOpen = dropdownLi.classList.toggle('mobile-open');
                var arrow = dropdownLi.querySelector('.nav-arrow');
                if (arrow) arrow.style.transform = isOpen ? 'rotate(180deg)' : '';
                dropdownLink.setAttribute('aria-expanded', String(isOpen));
            }
        });

        // Mobile toggle button
        var mobileToggle = document.querySelector('.mobile-toggle');
        if (mobileToggle) {
            mobileToggle.addEventListener('click', function() {
                var navMenu = document.querySelector('.nav-menu');
                if (navMenu) {
                    var isOpen = navMenu.classList.toggle('active');
                    mobileToggle.setAttribute('aria-expanded', String(isOpen));
                    mobileToggle.setAttribute('aria-label', isOpen ? 'Close navigation menu' : 'Open navigation menu');
                }
            });
        }

        // Close button in mobile menu
        var menuClose = document.querySelector('.menu-close');
        if (menuClose) {
            menuClose.addEventListener('click', function() {
                var navMenu = document.querySelector('.nav-menu');
                if (navMenu) navMenu.classList.remove('active');
                if (mobileToggle) {
                    mobileToggle.setAttribute('aria-expanded', 'false');
                    mobileToggle.setAttribute('aria-label', 'Open navigation menu');
                }
            });
        }
    });
</script>