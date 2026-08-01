<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="auth-status" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email Address -->
        <div class="form-group">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" />
        </div>

        <!-- Password -->
        <div class="form-group">
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input id="password"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('password')" />
        </div>

        <!-- Remember Me -->
        <div class="form-group">
            <label for="remember_me" class="flex items-center" style="gap:0.5rem">
                <input id="remember_me" type="checkbox" class="form-checkbox" name="remember">
                <span class="text-sm" style="color:var(--white-60)">{{ __('Remember me') }}</span>
            </label>
        </div>

        <div class="flex items-center justify-between mt-6">
            <div style="display: flex; gap: 1rem;">
                @if (Route::has('register'))
                    <a class="btn-text" href="{{ route('register') }}" style="font-size:14px;">
                        {{ __('Sign up') }}
                    </a>
                @endif
                @if (Route::has('password.request'))
                    <a class="btn-text" href="{{ route('password.request') }}" style="font-size:14px;">
                        {{ __('Forgot password?') }}
                    </a>
                @endif
            </div>

            <x-primary-button>
                {{ __('Log in') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
