<!DOCTYPE html>
<html lang="en">

@include('partials.head')

{{-- Page-level <style> blocks pushed from individual views --}}
@stack('styles')

<body>

@include('partials.header')

@yield('content')

@include('partials.footer')

@include('partials.scripts')

{{-- Page-level <script> blocks pushed from individual views --}}
@stack('scripts')

</body>
</html>