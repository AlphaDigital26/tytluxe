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
        if (!e.target.closest('.header-inner')) {
            navMenu.classList.remove('active');
            mobileToggle.classList.remove('active');
        }
    });

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
});