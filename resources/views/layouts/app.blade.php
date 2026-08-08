<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>
        <link rel="icon" type="image/png" href="{{ asset('assets/images/favicon.png') }}">

        <link rel="stylesheet" href="{{ asset('assets/css/admin.css') }}">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body>
        <div class="admin-layout">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @isset($header)
                <header class="admin-header">
                    <div class="admin-header-title">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main class="admin-main">
                @yield('content', $slot ?? '')
            </main>
        </div>
    </body>
</html>
