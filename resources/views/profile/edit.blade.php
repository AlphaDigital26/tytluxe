<x-app-layout>
    <x-slot name="header">
        <h2 style="font-family:'Cormorant Garamond', serif; font-size: 28px; color:#fff;">
            {{ __('Profile') }}
        </h2>
    </x-slot>

    <div>
        <div class="panel">
            <div style="max-width: 600px;">
                @include('profile.partials.update-profile-information-form')
            </div>
        </div>

        <div class="panel">
            <div style="max-width: 600px;">
                @include('profile.partials.update-password-form')
            </div>
        </div>

        <div class="panel">
            <div style="max-width: 600px;">
                @include('profile.partials.delete-user-form')
            </div>
        </div>
    </div>
</x-app-layout>
