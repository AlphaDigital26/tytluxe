<footer>

    <div class="container">

        <div class="footer-grid">

            <!-- About -->

            <div class="footer-about">

                <div class="footer-logo">

                    <img src="{{ asset('assets/images/tyt-logo.png') }}"
                         alt="TYT Luxe">

                </div>

                <p>

                    Curated hotels and cruises for unforgettable journeys.
                    Travel better, travel smarter with TYT Luxe.

                </p>

                <div class="social-links">

                    <a href="#">
                        <i class="fa-brands fa-facebook-f"></i>
                    </a>

                    <a href="#">
                        <i class="fa-brands fa-instagram"></i>
                    </a>

                    <a href="#">
                        <i class="fa-brands fa-youtube"></i>
                    </a>

                    <a href="https://wa.me/919875073788">

                        <i class="fa-brands fa-whatsapp"></i>

                    </a>

                </div>

            </div>

            <!-- Company -->

            <div class="footer-col">

                <h5>COMPANY</h5>

                <ul>

                    <li>
                        <a href="{{ url('/about') }}">
                            About Us
                        </a>
                    </li>

                    <li>
                        <a href="{{ url('/contact') }}">
                            Contact Us
                        </a>
                    </li>

                    <li>
                        <a href="{{ route('terms') }}">
                            Terms & Conditions
                        </a>
                    </li>

                    <li>
                        <a href="{{ route('privacy') }}">
                            Privacy Policy
                        </a>
                    </li>

                </ul>

            </div>

            <!-- Quick Links -->

            <div class="footer-col">

                <h5>QUICK LINKS</h5>

                <ul>

                    <li>
                        <a href="{{ url('/hotels') }}">
                            Hotels
                        </a>
                    </li>

                    <li>
                        <a href="{{ url('/flights') }}">
                            Flights
                        </a>
                    </li>

                    <li>
                        <a href="{{ url('/cruises') }}">
                            Cruises
                        </a>
                    </li>

                    <li>
                        <a href="{{ url('/staycation') }}">
                            Staycation
                        </a>
                    </li>

                    <li>
                        <a href="{{ url('/packages') }}">
                            Packages
                        </a>
                    </li>

                    <li>
                        <a href="{{ url('/offers') }}">
                            Offers
                        </a>
                    </li>

                </ul>

            </div>

            <!-- Support -->

            <div class="footer-col">

                <h5>SUPPORT</h5>

                <ul>

                    <li><a href="{{ route('help') }}">Help Center</a></li>

                    <li><a href="{{ route('cancellation') }}">Cancellation Policy</a></li>

                    <li><a href="{{ route('travel-guide') }}">Travel Guide</a></li>

                    <li><a href="{{ route('faqs') }}">FAQs</a></li>

                </ul>

            </div>

            <!-- Newsletter -->

            <div class="footer-col">

                <h5>STAY CONNECTED</h5>

                <p>

                    Get exclusive travel deals and inspiration.

                </p>

                <form class="newsletter-form">

                    <input
                        type="email"
                        placeholder="Enter your email">

                    <button type="submit">

                        <i class="fa-solid fa-arrow-right"></i>

                    </button>

                </form>

            </div>

        </div>

        <div class="copyright">

            <p>

                © {{ date('Y') }} TYT Luxe.
                All Rights Reserved.

            </p>

        </div>

    </div>

</footer>