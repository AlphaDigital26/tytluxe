<footer role="contentinfo">

    <div class="container">

        <div class="footer-grid">

            <!-- About -->
            <div class="footer-about">

                <div class="footer-logo">
                    <a href="{{ url('/') }}" aria-label="TYT Luxe — Go to homepage">
                        <img src="{{ asset('assets/images/tyt-logo.png') }}"
                             alt="TYT Luxe logo"
                             width="140"
                             height="48"
                             loading="lazy">
                    </a>
                </div>

                <p>
                    Curated hotels and cruises for unforgettable journeys.
                    Travel better, travel smarter with TYT Luxe.
                </p>

                <nav aria-label="Social media links">
                    <div class="social-links">

                        <a href="https://www.facebook.com/profile.php?id=61589456826795" target="_blank" aria-label="Follow TYT Luxe on Facebook" rel="noopener noreferrer">
                            <i class="fa-brands fa-facebook-f" aria-hidden="true"></i>
                        </a>

                        <a href="https://www.instagram.com/tytluxe_/" target="_blank" aria-label="Follow TYT Luxe on Instagram" rel="noopener noreferrer">
                            <i class="fa-brands fa-instagram" aria-hidden="true"></i>
                        </a>

                        <a href="https://wa.me/919875073788"
                           aria-label="Chat with TYT Luxe on WhatsApp"
                           target="_blank"
                           rel="noopener noreferrer">
                            <i class="fa-brands fa-whatsapp" aria-hidden="true"></i>
                        </a>

                    </div>
                </nav>

            </div>

            <!-- Company -->
            <div class="footer-col">

                <h5>COMPANY</h5>

                <ul role="list">
                    <li><a href="{{ url('/about') }}">About Us</a></li>
                    <li><a href="{{ url('/contact') }}">Contact Us</a></li>
                    <li><a href="{{ route('terms') }}">Terms &amp; Conditions</a></li>
                    <li><a href="{{ route('privacy') }}">Privacy Policy</a></li>
                </ul>

            </div>

            <!-- Quick Links -->
            <div class="footer-col">

                <h5>QUICK LINKS</h5>

                <ul role="list">
                    <li><a href="{{ url('/hotels') }}">Hotels</a></li>
                    <li><a href="{{ url('/flights') }}">Flights</a></li>
                    <li><a href="{{ url('/cruises') }}">Cruises</a></li>
                    <li><a href="{{ url('/packages') }}">Packages</a></li>
                    <li><a href="{{ url('/offers') }}">Offers</a></li>
                </ul>

            </div>

            <!-- Support -->
            <div class="footer-col">

                <h5>SUPPORT</h5>

                <ul role="list">
                    <li><a href="{{ route('help') }}">Help Center</a></li>
                    <li><a href="{{ route('cancellation') }}">Cancellation Policy</a></li>
                    <li><a href="{{ route('blog') }}">Travel Journal</a></li>
                    <li><a href="{{ route('faqs') }}">FAQs</a></li>
                </ul>

            </div>

            <!-- Newsletter -->
            <div class="footer-col">

                <h5>STAY CONNECTED</h5>

                <p>Get exclusive travel deals and inspiration.</p>

                <form class="newsletter-form" aria-label="Newsletter subscription">
                    <label for="footer-newsletter-email" class="sr-only">Your email address</label>
                    <input
                        type="email"
                        id="footer-newsletter-email"
                        name="email"
                        placeholder="Enter your email"
                        autocomplete="email"
                        required>
                    <button type="submit" aria-label="Subscribe to newsletter">
                        <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                    </button>
                </form>

            </div>

        </div>

        <div class="copyright">
            <p>
                &copy; {{ date('Y') }} TYT Luxe. All Rights Reserved.
                &nbsp;|&nbsp; Designed for the Indian Traveller.
            </p>
        </div>

    </div>

</footer>