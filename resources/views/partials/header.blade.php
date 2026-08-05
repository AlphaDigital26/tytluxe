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



                @auth
                    <div class="header-dropdown">
                        <button class="header-cta" onclick="toggleProfileDropdown(event)" style="background: transparent; border: 1px solid var(--primary); color: var(--primary); cursor: pointer; padding: 10px 14px;">
                            <i class="fa-solid fa-user"></i>
                        </button>
                        <div class="header-dropdown-menu" id="profileDropdown">
                            <a href="{{ route('profile.edit') }}"><i class="fa-solid fa-user"></i> Profile</a>
                            <a href="#"><i class="fa-solid fa-suitcase"></i> Booking History</a>
                            <form method="POST" action="{{ route('logout') }}" style="margin: 0;">
                                @csrf
                                <button type="submit"><i class="fa-solid fa-right-from-bracket"></i> Logout</button>
                            </form>
                        </div>
                    </div>
                @else
                    <a class="header-cta" style="border: 1px solid rgba(255,255,255,0.25); color: var(--text-light); padding: 10px 16px;" href="{{ route('login') }}" title="Login / Sign Up">
                        <i class="fa-solid fa-user"></i>
                        <span class="hidden-mobile">Login</span>
                    </a>
                @endauth

                <a href="#" class="mobile-toggle">
                    <i class="fa-solid fa-bars"></i>
                </a>

            </div>

        </div>
    </div>
</header>

<script>
    function toggleProfileDropdown(e) {
        e.stopPropagation();
        document.getElementById('profileDropdown').classList.toggle('show');
    }

    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        let dropdown = document.getElementById('profileDropdown');
        if (dropdown && dropdown.classList.contains('show')) {
            dropdown.classList.remove('show');
        }
    });
</script>