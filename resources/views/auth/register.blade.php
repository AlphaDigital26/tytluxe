<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - TYT Luxe</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500;600;700&family=Inter:wght@300;400;500;600&family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400&display=swap" rel="stylesheet">
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
</head>
<body>

<div class="auth-page-wrapper">
    <!-- Right Side: Image/SVG -->
    <div class="auth-image-side">
        <div class="auth-illustration">
            <svg viewBox="0 0 500 500" width="100%" height="100%">
                <defs>
                    <!-- Glow Filter -->
                    <filter id="glow" x="-20%" y="-20%" width="140%" height="140%">
                        <feGaussianBlur stdDeviation="15" result="blur" />
                        <feComposite in="SourceGraphic" in2="blur" operator="over" />
                    </filter>
                    <linearGradient id="planetGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" stop-color="#2a2a2a" />
                        <stop offset="100%" stop-color="#111" />
                    </linearGradient>
                </defs>

                <!-- Background Orbital Rings to fill empty space -->
                <circle cx="250" cy="250" r="240" fill="none" stroke="#ffffff" stroke-width="1" opacity="0.03" stroke-dasharray="4 4" class="svg-pulse" />
                <circle cx="250" cy="250" r="210" fill="none" stroke="#c9a84c" stroke-width="1" opacity="0.05" />
                
                <!-- Crossing abstract flight paths in background -->
                <path d="M -50 150 Q 250 50 550 250" fill="none" stroke="#fff" stroke-width="1" opacity="0.05" />
                <path d="M -50 400 Q 250 450 550 300" fill="none" stroke="#c9a84c" stroke-width="1" opacity="0.08" />

                <!-- Main Background Circle (Planet) -->
                <circle cx="250" cy="250" r="160" fill="url(#planetGrad)" filter="url(#glow)" />
                <circle cx="250" cy="250" r="160" fill="none" stroke="#333" stroke-width="2" />
                
                <!-- Globe / Planet Lines -->
                <g opacity="0.8">
                    <circle cx="250" cy="250" r="160" fill="none" stroke="#c9a84c" stroke-width="1" opacity="0.3" />
                    <ellipse cx="250" cy="250" rx="160" ry="60" fill="none" stroke="#c9a84c" stroke-width="1.5" opacity="0.5" transform="rotate(35 250 250)" class="svg-float" style="transform-origin: center;" />
                    <ellipse cx="250" cy="250" rx="60" ry="160" fill="none" stroke="#c9a84c" stroke-width="1.5" opacity="0.5" transform="rotate(35 250 250)" class="svg-float" style="transform-origin: center;" />
                </g>
                
                <!-- Airplane Dashed Trail -->
                <path d="M 120 330 Q 50 220 180 120 T 360 120" fill="none" stroke="#fff" stroke-width="2.5" stroke-dasharray="6 6" opacity="0.5" />
                
                <!-- Floating Airplane -->
                <g class="svg-float-reverse" style="transform-origin: center;">
                    <g transform="translate(350, 110) rotate(55) scale(0.9)">
                        <!-- Wings -->
                        <path d="M 20 25 L -20 10 L 0 5 Z" fill="#eee" />
                        <path d="M 20 25 L 50 50 L 30 15 Z" fill="#c9a84c" />
                        <!-- Fuselage -->
                        <path d="M -30 25 Q 20 25 50 15 L 50 35 Q 20 35 -30 35 Q -40 30 -30 25 Z" fill="#fff" />
                        <!-- Tail -->
                        <path d="M -25 25 L -40 10 L -20 20 Z" fill="#eee" />
                    </g>
                </g>

                <!-- Floating Location Pin with glow -->
                <g class="svg-float-delayed" style="transform-origin: center;" filter="url(#glow)">
                    <g transform="translate(130, 80) scale(0.9)">
                        <path d="M15,0 C6.7,0 0,6.7 0,15 C0,26.2 15,40 15,40 C15,40 30,26.2 30,15 C30,6.7 23.3,0 15,0 Z" fill="#c9a84c" />
                        <circle cx="15" cy="14" r="6" fill="#fff" />
                    </g>
                </g>

                <!-- Floating Abstract Shapes & Constellations -->
                <circle cx="70" cy="130" r="14" fill="#333" class="svg-float" />
                <circle cx="430" cy="380" r="22" fill="#c9a84c" opacity="0.9" class="svg-float-delayed" filter="url(#glow)" />
                <circle cx="390" cy="90" r="8" fill="#fff" opacity="0.4" class="svg-pulse" />
                <circle cx="100" cy="400" r="6" fill="#c9a84c" class="svg-pulse" />
                <circle cx="450" cy="180" r="4" fill="#fff" opacity="0.2" />
                <circle cx="40" cy="280" r="5" fill="#c9a84c" opacity="0.3" />
                
                <!-- Constellation Lines -->
                <path d="M 390 90 L 450 180 L 430 380" fill="none" stroke="#fff" stroke-width="1" stroke-dasharray="2 4" opacity="0.15" />
                <path d="M 70 130 L 40 280 L 100 400" fill="none" stroke="#fff" stroke-width="1" stroke-dasharray="2 4" opacity="0.15" />

                <!-- Chat / Idea Bubble -->
                <g class="svg-float" style="transform-origin: center;">
                    <g transform="translate(330, 310) scale(1)">
                        <rect x="0" y="0" width="70" height="45" rx="10" fill="#222" stroke="#c9a84c" stroke-width="1" />
                        <polygon points="15,45 25,45 15,55" fill="#222" />
                        <polygon points="15,45 25,45 15,55" fill="none" stroke="#c9a84c" stroke-width="1" />
                        <!-- Line to cover the border where the triangle meets the rect -->
                        <line x1="16" y1="45" x2="24" y2="45" stroke="#222" stroke-width="2" />
                        
                        <circle cx="20" cy="22.5" r="4" fill="#c9a84c" />
                        <circle cx="35" cy="22.5" r="4" fill="#c9a84c" />
                        <circle cx="50" cy="22.5" r="4" fill="#c9a84c" />
                    </g>
                </g>
                
                <!-- Sparkles -->
                <path d="M 80 290 Q 90 290 90 280 Q 90 290 100 290 Q 90 290 90 300 Q 90 290 80 290 Z" fill="#fff" opacity="0.6" class="svg-pulse" />
                <path d="M 400 220 Q 405 220 405 215 Q 405 220 410 220 Q 405 220 405 225 Q 405 220 400 220 Z" fill="#c9a84c" opacity="0.9" class="svg-pulse" />

            </svg>
        </div>

        <div class="auth-image-content">
            <h2>Experience Travel the Right Way.</h2>
            <p>Handpicked Hotels & Cruises Curated for Comfort, Luxury & Value.</p>
        </div>
    </div>

    <!-- Right Side: Form -->
    <div class="auth-form-side">
        <a href="{{ url('/') }}" class="auth-back-btn"><i class="fa-solid fa-arrow-left"></i> Back to Home</a>
        
        <div class="auth-form-container">
            <img src="{{ asset('assets/images/tyt-logo.png') }}" alt="TYT Luxe" class="auth-logo-center">
            
            <h2>Join the Elite</h2>
            <p class="auth-modal-subtitle">Create your account</p>

            <form method="POST" action="{{ route('register') }}">
                @csrf
                <div class="auth-form-group">
                    <label>FULL NAME</label>
                    <div class="auth-input-wrapper">
                        <input type="text" name="name" placeholder="John Doe" required value="{{ old('name') }}">
                    </div>
                    @error('name')
                        <span class="auth-error-msg">{{ $message }}</span>
                    @enderror
                </div>

                <div class="auth-form-group">
                    <label>EMAIL</label>
                    <div class="auth-input-wrapper">
                        <input type="email" name="email" placeholder="mail@site.com" required value="{{ old('email') }}">
                    </div>
                    @error('email')
                        <span class="auth-error-msg">{{ $message }}</span>
                    @enderror
                </div>

                <div class="auth-form-group">
                    <label>PASSWORD</label>
                    <div class="auth-input-wrapper" style="position: relative;">
                        <input type="password" name="password" placeholder="••••••••" required>
                        <i class="fa-regular fa-eye" style="position: absolute; right: 0; top: 50%; transform: translateY(-50%); cursor: pointer;"></i>
                    </div>
                    @error('password')
                        <span class="auth-error-msg">{{ $message }}</span>
                    @enderror
                </div>

                <div class="auth-form-group">
                    <label>CONFIRM PASSWORD</label>
                    <div class="auth-input-wrapper" style="position: relative;">
                        <input type="password" name="password_confirmation" placeholder="••••••••" required>
                        <i class="fa-regular fa-eye" style="position: absolute; right: 0; top: 50%; transform: translateY(-50%); cursor: pointer;"></i>
                    </div>
                </div>

                <div class="auth-form-group-header" style="margin-bottom: 25px;">
                    <label class="terms-label" style="text-transform: none; font-weight: normal; font-size: 13px; color: #666;">
                        <input type="checkbox" name="terms" required style="width: auto;"> I agree to the <a href="#" style="color:#111;">Terms</a> & <a href="#" style="color:#111;">Privacy Policy</a>.
                    </label>
                </div>

                <button type="submit" class="auth-submit-btn">Register</button>
            </form>

            <div class="auth-social-divider">Or continue with</div>

            <div class="auth-social-buttons">
                <a href="#" class="auth-social-btn">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/5/53/Google_%22G%22_Logo.svg" alt="Google"> Google
                </a>
                <a href="#" class="auth-social-btn">
                    <i class="fa-brands fa-facebook" style="color: #1877F2; font-size: 18px;"></i> Facebook
                </a>
            </div>

            <div class="auth-switch">
                Already Registered? <a href="{{ route('login') }}" style="color: #111;">Sign In</a>
            </div>
        </div>
    </div>
</div>

</body>
</html>
