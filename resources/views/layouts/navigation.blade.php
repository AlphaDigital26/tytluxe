<nav x-data="{ open: false }" class="admin-nav">
    <div class="admin-nav-inner">
        <div class="admin-nav-left">
            <a href="{{ route('home') }}" class="admin-logo">
                <x-application-logo />
            </a>
        </div>
        
        <div class="admin-nav-right">
            @auth
            <x-dropdown>
                <x-slot name="trigger">
                    <button class="dropdown-trigger">
                        {{ Auth::user()->name }}
                        <svg viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                    </button>
                </x-slot>

                <x-slot name="content">
                    <x-dropdown-link :href="route('profile.edit')">
                        Profile
                    </x-dropdown-link>

                    <!-- Authentication -->
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <x-dropdown-link :href="route('logout')"
                                onclick="event.preventDefault(); this.closest('form').submit();">
                            Log Out
                        </x-dropdown-link>
                    </form>
                </x-slot>
            </x-dropdown>
            @else
                <a href="{{ route('login') }}" class="nav-link">Log in</a>
                @if (Route::has('register'))
                    <a href="{{ route('register') }}" class="nav-link">Register</a>
                @endif
            @endauth
        </div>
    </div>
</nav>
