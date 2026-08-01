<!DOCTYPE html>
<html lang="en">
    @include('partials.head')
<body>

    @include('partials.header')

    @yield('content')

    @include('partials.footer')
    
    @include('partials.auth-modal')
    
    @include('partials.scripts')
    @stack('scripts')
</body>
</html>
