<x-guest-layout>
    <x-slot:title>Forgot Password</x-slot:title>
    <x-slot:description>Reset your TYT Luxe account password — we will email you a secure link.</x-slot:description>

    <h1 style="font-size:1.4rem;margin:0 0 10px;">Forgot your password?</h1>
    <div class="mb-4 text-sm" style="color:var(--white-60)">
        {{ __('No problem. Enter the email address of your account and we will email you a link to choose a new password.') }}
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="auth-status" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <!-- Email Address -->
        <div class="form-group">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" type="email" name="email" :value="old('email')" required autofocus />
            <x-input-error :messages="$errors->get('email')" />
        </div>

        <div class="flex items-center justify-end mt-6">
            <x-primary-button>
                {{ __('Email Password Reset Link') }}
            </x-primary-button>
        </div>
    </form>
    <p style="text-align:center;margin-top:18px;font-size:14px;"><a href="{{ route('login') }}" style="color:var(--gold, #c9a84c);">&larr; Back to login</a></p>
</x-guest-layout>
