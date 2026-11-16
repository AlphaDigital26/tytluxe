{{-- Theme JavaScript: handles mobile menu toggle and sticky header behaviour --}}
<script src="{{ asset('assets/js/main.js') }}"></script>
<script>
  // Read by wishlist.js to block adding items when the visitor isn't logged in.
  window.TYT_AUTH = @json(auth()->check());
  window.TYT_LOGIN_URL = "{{ route('login') }}";
</script>
<script src="{{ asset('assets/js/wishlist.js') }}"></script>
{{-- Stops the page behind any open popup from scrolling. --}}
<script src="{{ asset('assets/js/scroll-lock.js') }}?v={{ @filemtime(public_path('assets/js/scroll-lock.js')) }}"></script>
