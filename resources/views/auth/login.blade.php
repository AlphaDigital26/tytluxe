<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - TYT Luxe</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/images/favicon.png') }}">
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500;600;700&family=Inter:wght@300;400;500;600&family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400&display=swap" rel="stylesheet">
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    <style>
        .auth-grid-scroll {
            position: absolute;
            top: -50%;
            left: -10%;
            width: 120%;
            height: 200%;
            display: flex;
            gap: 20px;
            transform: rotate(-15deg);
            z-index: 1;
            pointer-events: none;
            opacity: 0.6;
        }

        .grid-column {
            display: flex;
            flex-direction: column;
            gap: 20px;
            width: 50%;
        }

        .grid-column img {
            width: 100%;
            border-radius: 15px;
            object-fit: cover;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
            height: 300px;
        }

        .col-up {
            animation: scrollUp 35s linear infinite;
        }

        .col-down {
            animation: scrollDown 35s linear infinite;
        }

        @keyframes scrollUp {
            0% { transform: translateY(0); }
            100% { transform: translateY(-50%); }
        }

        @keyframes scrollDown {
            0% { transform: translateY(-50%); }
            100% { transform: translateY(0); }
        }

        .auth-bg-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle at center, rgba(10,10,10,0.5) 0%, rgba(10,10,10,0.95) 100%);
            z-index: 2;
        }
    </style>
</head>
<body>

<div class="auth-page-wrapper">
    <!-- Right Side: Animated Grid Scroll -->
    <div class="auth-image-side" style="padding: 0; position: relative; background: #000; overflow: hidden;">
        <!-- Scrolling image grid -->
        <div class="auth-grid-scroll">
            <!-- Column 1 (Scrolls Up) -->
            <div class="grid-column col-up">
                <img src="{{ asset('assets/images/Hotel Collections 1.webp') }}" alt="Hotel">
                <img src="{{ asset('assets/images/Cruise Experiences 1.webp') }}" alt="Cruise">
                <img src="{{ asset('assets/images/Hotel Collections 3.jpg') }}" alt="Hotel">
                <img src="{{ asset('assets/images/29788-15-hotel_carousel_large.jpg') }}" alt="Flight">
                <img src="{{ asset('assets/images/Cruise Experiences 2.png') }}" alt="Cruise">
                <!-- Duplicate for infinite scroll -->
                <img src="{{ asset('assets/images/Hotel Collections 1.webp') }}" alt="Hotel">
                <img src="{{ asset('assets/images/Cruise Experiences 1.webp') }}" alt="Cruise">
                <img src="{{ asset('assets/images/Hotel Collections 3.jpg') }}" alt="Hotel">
                <img src="{{ asset('assets/images/29788-15-hotel_carousel_large.jpg') }}" alt="Flight">
                <img src="{{ asset('assets/images/Cruise Experiences 2.png') }}" alt="Cruise">
            </div>
            <!-- Column 2 (Scrolls Down) -->
            <div class="grid-column col-down">
                <img src="{{ asset('assets/images/Hotel Collections 2.jpg') }}" alt="Hotel">
                <img src="{{ asset('assets/images/Carousel.jpeg') }}" alt="Carousel">
                <img src="{{ asset('assets/images/Cruise Experiences 3.webp') }}" alt="Cruise">
                <img src="{{ asset('assets/images/Hotel Collections 4.png') }}" alt="Hotel">
                <img src="{{ asset('assets/images/cruise-ship-is-docked-night-with-word-cruise-side_916191-10755.jpg') }}" alt="Cruise">
                <!-- Duplicate for infinite scroll -->
                <img src="{{ asset('assets/images/Hotel Collections 2.jpg') }}" alt="Hotel">
                <img src="{{ asset('assets/images/Carousel.jpeg') }}" alt="Carousel">
                <img src="{{ asset('assets/images/Cruise Experiences 3.webp') }}" alt="Cruise">
                <img src="{{ asset('assets/images/Hotel Collections 4.png') }}" alt="Hotel">
                <img src="{{ asset('assets/images/cruise-ship-is-docked-night-with-word-cruise-side_916191-10755.jpg') }}" alt="Cruise">
            </div>
        </div>
        
        <!-- Gradient Overlay -->
        <div class="auth-bg-overlay"></div>

        <div class="auth-image-content" style="position: relative; z-index: 10; padding: 40px; text-align: center;">
            <img src="{{ asset('assets/images/tyt-logo.png') }}" alt="TYT Luxe" style="max-width: 150px; margin-bottom: 20px; display: inline-block;">
            <h2>Elevate Your Journey.</h2>
            <p>Experience world-class flights, luxury cruises, and exclusive handpicked hotels tailored for you.</p>
        </div>
    </div>

    <!-- Right Side: Form -->
    <div class="auth-form-side">
        <a href="{{ url('/') }}" class="auth-back-btn"><i class="fa-solid fa-arrow-left"></i> Back to Home</a>
        
        <div class="auth-form-container">
            
            <h2>Welcome To TYT Luxe</h2>
            <p class="auth-modal-subtitle">Login to your account</p>

            <form method="POST" action="{{ route('login') }}">
                @csrf
                <div class="auth-form-group">
                    <label>EMAIL</label>
                    <div class="auth-input-wrapper">
                        <input type="email" name="email" placeholder="abc@gmail.com" required value="{{ old('email') }}">
                    </div>
                    @error('email')
                        <span class="auth-error-msg">{{ $message }}</span>
                    @enderror
                </div>

                <div class="auth-form-group">
                    <div class="auth-form-group-header">
                        <label>PASSWORD</label>
                    </div>
                    <div class="auth-input-wrapper" style="position: relative;">
                        <input type="password" name="password" placeholder="••••••••" required id="passwordInput">
                        <i class="fa-regular fa-eye" style="position: absolute; right: 0; top: 50%; transform: translateY(-50%); cursor: pointer;" onclick="const input = document.getElementById('passwordInput'); if(input.type === 'password') { input.type = 'text'; this.classList.replace('fa-eye', 'fa-eye-slash'); } else { input.type = 'password'; this.classList.replace('fa-eye-slash', 'fa-eye'); }"></i>
                    </div>
                    @error('password')
                        <span class="auth-error-msg">{{ $message }}</span>
                    @enderror
                </div>

                <div class="auth-form-group-header" style="margin-bottom: 25px;">
                    <label class="terms-label" style="text-transform: none; font-weight: normal; font-size: 13px; color: #666;">
                        <input type="checkbox" name="remember" style="width: auto;"> Remember Me
                    </label>
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" style="font-size: 13px; color: #666; text-decoration: none;">Forgot Password?</a>
                    @endif
                </div>

                <button type="submit" class="auth-submit-btn">Login</button>
            </form>

            <div class="auth-social-divider">Or continue with</div>

            <div class="auth-social-buttons" style="justify-content: center;">
                <a href="{{ route('social.google.redirect') }}" class="auth-social-btn" style="width: 100%; justify-content: center;">
                    <img src="{{ asset('assets/images/google-icon.svg') }}" alt="Google" style="width: 20px; height: 20px;"> Google
                </a>
            </div>

            <div class="auth-switch">
                Not Registered Yet? <a href="{{ route('register') }}" style="color: #111;">Create an account</a>
            </div>
        </div>
    </div>
</div>

</body>
</html>
