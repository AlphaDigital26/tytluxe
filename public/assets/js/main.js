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
            header.style.background = 'rgba(18, 18, 18, 0.95)';
            header.style.padding = '10px 0';
        } else {
            header.style.background = 'linear-gradient(to bottom, rgba(0,0,0,0.7) 0%, transparent 100%)';
            header.style.padding = '20px 0';
        }
    });

    /* ===== SHARED HERO SLIDER ===== */
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