<!DOCTYPE html>
<html lang="en">
    @include('partials.head')
<body>

    {{-- ══════════════════════════════════════
         SKIP TO MAIN CONTENT (Accessibility)
    ══════════════════════════════════════ --}}
    <a href="#main-content" class="skip-link">Skip to main content</a>

    @include('partials.header')

    <main id="main-content" tabindex="-1">
        @yield('content')
    </main>

    @include('partials.footer')
    
    <!-- TOAST HTML -->
    <div
        id="tyt-global-toast"
        class="tyt-toast"
        role="alert"
        aria-live="polite"
        aria-atomic="true"
    >
        <div class="tyt-toast-icon"><i class="fa-solid fa-check-circle" aria-hidden="true"></i></div>
        <div class="tyt-toast-content">
            <div class="tyt-toast-title">Success</div>
            <div class="tyt-toast-message">Request received! Our team will WhatsApp you shortly.</div>
        </div>
        <button class="tyt-toast-close" onclick="hideToast()" aria-label="Close notification">&times;</button>
    </div>

    @include('partials.scripts')
    
    <script>
      function showToast(title = 'Success', message = 'Request received! Our team will WhatsApp you shortly.', type = 'success') {
          const toast = document.getElementById('tyt-global-toast');
          if(!toast) return;
          toast.querySelector('.tyt-toast-title').textContent = title;
          toast.querySelector('.tyt-toast-message').textContent = message;
          
          if(type === 'error') {
              toast.querySelector('.tyt-toast-icon i').className = 'fa-solid fa-circle-exclamation';
              toast.style.borderLeftColor = '#e74c3c';
              toast.querySelector('.tyt-toast-icon').style.color = '#e74c3c';
          } else {
              toast.querySelector('.tyt-toast-icon i').className = 'fa-solid fa-check-circle';
              toast.style.borderLeftColor = '#27ae60';
              toast.querySelector('.tyt-toast-icon').style.color = '#27ae60';
          }
      
          toast.classList.add('show');
          setTimeout(hideToast, 5000);
      }
      function hideToast() {
          const toast = document.getElementById('tyt-global-toast');
          if(toast) toast.classList.remove('show');
      }
    </script>
    
    @stack('scripts')
</body>
</html>
