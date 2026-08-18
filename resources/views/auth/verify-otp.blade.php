<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Email - TYT Luxe</title>
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
        /* Animated bg grid (same as login/register) */
        .auth-grid-scroll {
            position: absolute; top: -50%; left: -10%;
            width: 120%; height: 200%;
            display: flex; gap: 20px;
            transform: rotate(-15deg);
            z-index: 1; pointer-events: none; opacity: 0.6;
        }
        .grid-column { display: flex; flex-direction: column; gap: 20px; width: 50%; }
        .grid-column img { width: 100%; border-radius: 15px; object-fit: cover; box-shadow: 0 10px 30px rgba(0,0,0,0.5); height: 300px; }
        .col-up  { animation: scrollUp   35s linear infinite; }
        .col-down { animation: scrollDown 35s linear infinite; }
        @keyframes scrollUp   { 0% { transform: translateY(0); }    100% { transform: translateY(-50%); } }
        @keyframes scrollDown { 0% { transform: translateY(-50%); } 100% { transform: translateY(0); } }
        .auth-bg-overlay {
            position: absolute; top: 0; left: 0; width: 100%; height: 100%;
            background: radial-gradient(circle at center, rgba(10,10,10,0.5) 0%, rgba(10,10,10,0.95) 100%);
            z-index: 2;
        }

        /* OTP digit inputs */
        .otp-inputs {
            display: flex; gap: 10px; justify-content: center; margin: 28px 0;
        }
        .otp-digit {
            width: 52px; height: 60px;
            border: 1.5px solid #ddd; border-radius: 10px;
            font-size: 26px; font-weight: 600; text-align: center;
            color: #111; background: #fafafa;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
            outline: none;
            font-family: 'Inter', sans-serif;
        }
        .otp-digit:focus {
            border-color: #c9a227;
            box-shadow: 0 0 0 3px rgba(201,162,39,0.15);
            background: #fff;
        }
        .otp-digit.filled { border-color: #c9a227; background: #fff; }
        .otp-digit.error  { border-color: #e74c3c; box-shadow: 0 0 0 3px rgba(231,76,60,0.12); }

        /* Countdown */
        .otp-countdown {
            text-align: center; font-size: 13px; color: #999; margin-bottom: 6px;
        }
        .otp-countdown span { color: #c9a227; font-weight: 600; }
        .otp-countdown.expired span { color: #e74c3c; }

        /* Resend */
        .otp-resend-row { text-align: center; font-size: 13px; color: #888; margin-top: 4px; }
        .otp-resend-btn {
            background: none; border: none; cursor: pointer;
            font-size: 13px; color: #c9a227; font-weight: 600;
            text-decoration: underline; padding: 0;
            transition: color 0.2s;
        }
        .otp-resend-btn:hover { color: #a0811d; }
        .otp-resend-btn:disabled { color: #ccc; cursor: not-allowed; text-decoration: none; }

        /* Email hint */
        .email-hint {
            display: flex; align-items: center; gap: 8px;
            background: #fdf9ee; border: 1px solid #ece4c0;
            border-radius: 8px; padding: 11px 14px;
            font-size: 13px; color: #666; margin-bottom: 6px;
        }
        .email-hint i { color: #c9a227; font-size: 15px; }
        .email-hint strong { color: #333; }

        /* Alert messages */
        .otp-alert {
            padding: 10px 14px; border-radius: 8px;
            font-size: 13px; margin-bottom: 14px;
            display: flex; align-items: center; gap: 8px;
        }
        .otp-alert.success { background: #edfaf4; color: #1a7a4a; border: 1px solid #b8ead2; }
        .otp-alert.error   { background: #fef2f2; color: #c0392b; border: 1px solid #fad0d0; }
        .otp-alert.info    { background: #fffbeb; color: #92680a; border: 1px solid #fde68a; }

        /* Hidden OTP input for form submission */
        #otpHiddenInput { display: none; }
    </style>
</head>
<body>

<div class="auth-page-wrapper">
    <!-- Left Side: Animated Grid -->
    <div class="auth-image-side" style="padding: 0; position: relative; background: #000; overflow: hidden;">
        <div class="auth-grid-scroll">
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
        <div class="auth-bg-overlay"></div>
        <div class="auth-image-content" style="position: relative; z-index: 10; padding: 40px; text-align: center;">
            <img src="{{ asset('assets/images/tyt-logo.png') }}" alt="TYT Luxe" style="max-width: 150px; margin-bottom: 20px; display: inline-block;">
            <h2>Elevate Your Journey.</h2>
            <p>Experience world-class flights, luxury cruises, and exclusive handpicked hotels tailored for you.</p>
        </div>
    </div>

    <!-- Right Side: OTP Form -->
    <div class="auth-form-side">
        <a href="{{ route('register') }}" class="auth-back-btn"><i class="fa-solid fa-arrow-left"></i> Back to Register</a>

        <div class="auth-form-container">

            <!-- Icon -->
            <div style="text-align: center; margin-bottom: 18px;">
                <div style="display: inline-flex; align-items: center; justify-content: center; width: 60px; height: 60px; background: linear-gradient(135deg, #0a0a0a, #2a2a2a); border-radius: 50%; margin-bottom: 10px;">
                    <i class="fa-solid fa-envelope-circle-check" style="font-size: 24px; color: #d4af37;"></i>
                </div>
            </div>

            <h2 style="text-align: center;">Verify Your Email</h2>
            <p class="auth-modal-subtitle" style="text-align: center;">Enter the 6-digit code we sent to your email</p>

            {{-- Session alerts --}}
            @if(session('success'))
                <div class="otp-alert success"><i class="fa-solid fa-circle-check"></i> {{ session('success') }}</div>
            @endif
            @if(session('info'))
                <div class="otp-alert info"><i class="fa-solid fa-circle-info"></i> {{ session('info') }}</div>
            @endif
            @if($errors->has('otp'))
                <div class="otp-alert error"><i class="fa-solid fa-circle-exclamation"></i> {{ $errors->first('otp') }}</div>
            @endif
            @if($errors->has('email'))
                <div class="otp-alert error"><i class="fa-solid fa-circle-exclamation"></i> {{ $errors->first('email') }}</div>
            @endif

            {{-- Email hint --}}
            @php $pending = session('pending_registration'); @endphp
            @if($pending)
                <div class="email-hint">
                    <i class="fa-regular fa-envelope"></i>
                    <span>Code sent to <strong>{{ $pending['email'] }}</strong></span>
                </div>
            @endif

            {{-- OTP Verification Form --}}
            <form method="POST" action="{{ route('otp.verify.submit') }}" id="otpForm">
                @csrf
                {{-- Hidden input that gets populated by JS --}}
                <input type="hidden" name="otp" id="otpHiddenInput">

                {{-- 6 digit boxes --}}
                <div class="otp-inputs" id="otpInputs">
                    <input class="otp-digit" type="text" maxlength="1" inputmode="numeric" autocomplete="one-time-code" id="d1">
                    <input class="otp-digit" type="text" maxlength="1" inputmode="numeric" id="d2">
                    <input class="otp-digit" type="text" maxlength="1" inputmode="numeric" id="d3">
                    <input class="otp-digit" type="text" maxlength="1" inputmode="numeric" id="d4">
                    <input class="otp-digit" type="text" maxlength="1" inputmode="numeric" id="d5">
                    <input class="otp-digit" type="text" maxlength="1" inputmode="numeric" id="d6">
                </div>

                {{-- Countdown timer --}}
                <div class="otp-countdown" id="otpCountdown">
                    Resend code in <span id="timerDisplay">01:00</span>
                </div>

                <button type="submit" class="auth-submit-btn" id="verifyBtn" style="margin-top: 20px;" disabled>
                    Verify Email
                </button>
            </form>

            {{-- Resend form --}}
            <div class="otp-resend-row" style="margin-top: 16px;">
                Didn't receive the code?
                <form method="POST" action="{{ route('otp.resend') }}" style="display: inline;" id="resendForm">
                    @csrf
                    <button type="submit" class="otp-resend-btn" id="resendBtn" disabled>
                        Resend Code
                    </button>
                </form>
            </div>

        </div>
    </div>
</div>

<script>
(function () {
    // ─── Digit Input Logic ────────────────────────────────────────────
    const digits    = Array.from(document.querySelectorAll('.otp-digit'));
    const hidden    = document.getElementById('otpHiddenInput');
    const verifyBtn = document.getElementById('verifyBtn');

    function getOtpValue() {
        return digits.map(d => d.value).join('');
    }

    function updateHidden() {
        const val = getOtpValue();
        hidden.value = val;
        // Enable submit only when all 6 digits filled
        verifyBtn.disabled = val.length < 6;
    }

    digits.forEach((input, idx) => {
        input.addEventListener('input', function (e) {
            // Allow only digits
            this.value = this.value.replace(/[^0-9]/g, '').slice(-1);
            this.classList.toggle('filled', this.value !== '');

            updateHidden();

            // Auto-advance
            if (this.value && idx < digits.length - 1) {
                digits[idx + 1].focus();
            }
        });

        input.addEventListener('keydown', function (e) {
            // Backspace: clear & go back
            if (e.key === 'Backspace' && !this.value && idx > 0) {
                digits[idx - 1].focus();
                digits[idx - 1].value = '';
                digits[idx - 1].classList.remove('filled');
                updateHidden();
            }
            // Arrow keys
            if (e.key === 'ArrowLeft'  && idx > 0)               digits[idx - 1].focus();
            if (e.key === 'ArrowRight' && idx < digits.length - 1) digits[idx + 1].focus();
        });

        // Handle paste (e.g. from SMS autofill)
        input.addEventListener('paste', function (e) {
            e.preventDefault();
            const text = (e.clipboardData || window.clipboardData).getData('text').replace(/[^0-9]/g, '');
            text.split('').slice(0, 6).forEach((ch, i) => {
                if (digits[i]) {
                    digits[i].value = ch;
                    digits[i].classList.add('filled');
                }
            });
            updateHidden();
            // Focus last filled or last
            const next = Math.min(text.length, digits.length - 1);
            digits[next].focus();
        });
    });

    // Auto-focus first box
    digits[0].focus();

    // ─── Countdown Timer ─────────────────────────────────────────────
    const DURATION_SECONDS = 60; // 1 minute
    const resendBtn        = document.getElementById('resendBtn');
    const countdownEl      = document.getElementById('otpCountdown');
    const timerDisplay     = document.getElementById('timerDisplay');

    let secondsLeft = DURATION_SECONDS;

    function formatTime(s) {
        const m = Math.floor(s / 60);
        const sec = s % 60;
        return String(m).padStart(2, '0') + ':' + String(sec).padStart(2, '0');
    }

    timerDisplay.textContent = formatTime(secondsLeft);

    const timer = setInterval(function () {
        secondsLeft--;

        if (secondsLeft <= 0) {
            clearInterval(timer);
            countdownEl.innerHTML = '<span>You can now resend the code</span>';
            resendBtn.disabled = false;
        } else {
            timerDisplay.textContent = formatTime(secondsLeft);
        }
    }, 1000);

    // ─── Mark digits as error if validation failed ────────────────────
    @if($errors->has('otp'))
        digits.forEach(d => d.classList.add('error'));
    @endif
})();
</script>

</body>
</html>
