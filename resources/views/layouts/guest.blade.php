<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>
        <link rel="icon" type="image/png" href="{{ asset('assets/images/favicon.png') }}">

        <link rel="stylesheet" href="{{ asset('assets/css/admin.css') }}">
        
        <!-- FontAwesome -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body>
        <div class="auth-wrapper">
            <div class="auth-logo">
                <a href="/">
                    <img src="{{ asset('assets/images/tyt-logo.png') }}" alt="TYT Luxe" style="max-width: 150px;">
                </a>
            </div>

            <div class="auth-card">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
