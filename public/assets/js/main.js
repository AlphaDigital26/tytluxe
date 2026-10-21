/**
 * Sends an enquiry and fails loudly: asks for a JSON reply (so validation
 * errors come back as 422 instead of a silent redirect) and throws an Error
 * carrying the server's own message, e.g. "Please enter a valid phone number".
 * Same arguments as fetch().
 */
window.postEnquiry = async function (url, options) {
    options = options || {};
    options.headers = Object.assign({ 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, options.headers || {});
    const res = await fetch(url, options);
    if (res.ok) return res;
    let message = 'Something went wrong. Please try again.';
    if (res.status === 429) message = 'Too many attempts. Please wait a minute and try again.';
    if (res.status === 419) message = 'This page has expired. Please refresh and try again.';
    try {
        const data = await res.json();
        const errors = data && data.errors ? Object.values(data.errors).flat() : [];
        if (errors.length) message = errors[0];
    } catch (e) { /* not JSON */ }
    throw new Error(message);
};

document.addEventListener('DOMContentLoaded', function() {
    const mobileToggle = document.querySelector('.mobile-toggle');
    const navMenu = document.querySelector('.nav-menu');

    if (mobileToggle) {
        mobileToggle.addEventListener('click', function(e) {
            e.preventDefault();
            navMenu.classList.toggle('active');
            mobileToggle.classList.toggle('active');
        });
    }

    // Close mobile menu when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.header-inner') && navMenu.classList.contains('active')) {
            navMenu.classList.remove('active');
            mobileToggle.classList.remove('active');
        }
    });

    const menuClose = document.querySelector('.menu-close');
    if (menuClose) {
        menuClose.addEventListener('click', function(e) {
            e.preventDefault();
            navMenu.classList.remove('active');
            mobileToggle.classList.remove('active');
        });
    }

    // Sticky header logic
    const header = document.querySelector('header');
    window.addEventListener('scroll', function() {
        if (window.scrollY > 50) {
            header.style.background = '#121212';
            header.style.padding = '10px 0';
        } else {
            header.style.background = 'linear-gradient(to bottom, rgba(0,0,0,0.7) 0%, transparent 100%)';
            header.style.padding = '20px 0';
        }
    });

    /* ===== SHARED HERO SLIDER ===== */
    // These slides hotlink to images.unsplash.com; if that host is
    // unreachable (network hiccup, blocked, timed out) the slide is left
    // showing nothing. Detect the failure and fall back to a local image
    // instead of a blank/broken background.
    document.querySelectorAll('.shared-slide').forEach(function (slide) {
        const match = slide.style.backgroundImage.match(/url\(["']?(.*?)["']?\)/);
        if (!match || !match[1]) return;
        const probe = new Image();
        probe.onerror = function () {
            slide.style.backgroundImage = "url('/assets/images/hero-bg.png')";
        };
        probe.src = match[1];
    });

    const sharedSlides = document.querySelectorAll('.shared-slide');
    if (sharedSlides.length > 1) {
        const sharedDots   = document.querySelectorAll('.shared-dot');
        let sharedCurrent  = 0, sharedTimer;

        function goSharedTo(n) {
            sharedSlides[sharedCurrent].classList.remove('active');
            if (sharedDots.length) sharedDots[sharedCurrent].classList.remove('active');
            sharedCurrent = (n + sharedSlides.length) % sharedSlides.length;
            sharedSlides[sharedCurrent].classList.add('active');
            if (sharedDots.length) sharedDots[sharedCurrent].classList.add('active');
        }

        function startSharedAuto() { 
            clearInterval(sharedTimer); 
            sharedTimer = setInterval(() => goSharedTo(sharedCurrent + 1), 5000); 
        }

        if (sharedDots.length) {
            sharedDots.forEach(dot => dot.addEventListener('click', () => { 
                goSharedTo(+dot.dataset.slide); 
                startSharedAuto(); 
            }));
        }

        const prevBtn = document.querySelector('.shared-arrow-prev');
        const nextBtn = document.querySelector('.shared-arrow-next');
        if (prevBtn) prevBtn.addEventListener('click', () => { goSharedTo(sharedCurrent - 1); startSharedAuto(); });
        if (nextBtn) nextBtn.addEventListener('click', () => { goSharedTo(sharedCurrent + 1); startSharedAuto(); });

        startSharedAuto();
    }
});
