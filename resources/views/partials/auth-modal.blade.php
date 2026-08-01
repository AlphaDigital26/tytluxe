<!-- Auth Modal Overlay -->
<div class="auth-modal-overlay" id="authModalOverlay">
    <div class="auth-modal-box">
        <button class="auth-modal-close" onclick="closeAuthModal()">
            <i class="fa-solid fa-xmark"></i>
        </button>

        <div class="auth-modal-content" id="loginFormContent">
            <h2 class="auth-modal-title">Welcome Back</h2>
            <p class="auth-modal-subtitle">Unlock a world of unparalleled luxury.</p>

            <form method="POST" action="{{ route('login') }}">
                @csrf
                <div class="auth-form-group">
                    <label>EMAIL ADDRESS</label>
                    <div class="auth-input-wrapper">
                        <i class="fa-regular fa-envelope"></i>
                        <input type="email" name="email" placeholder="" required value="{{ old('email') }}">
                    </div>
                    @error('email')
                        <span class="auth-error-msg">{{ $message }}</span>
                    @enderror
                </div>

                <div class="auth-form-group">
                    <div class="auth-form-group-header">
                        <label>PASSWORD</label>
                    </div>
                    <div class="auth-input-wrapper">
                        <i class="fa-solid fa-lock"></i>
                        <input type="password" name="password" placeholder="" required>
                    </div>
                    @error('password')
                        <span class="auth-error-msg">{{ $message }}</span>
                    @enderror
                </div>

                <button type="submit" class="auth-submit-btn">SIGN IN <i class="fa-solid fa-arrow-right"></i></button>
            </form>

            <div class="auth-switch">
                New to TYT Luxe? <a href="javascript:void(0)" onclick="switchAuthView('register')">Create an Account</a>
            </div>
        </div>

        <div class="auth-modal-content" id="registerFormContent" style="display: none;">
            <h2 class="auth-modal-title">Join the Elite</h2>
            <p class="auth-modal-subtitle">Unlock a world of unparalleled luxury.</p>

            <form method="POST" action="{{ route('register') }}">
                @csrf
                <div class="auth-form-group">
                    <label>FULL NAME</label>
                    <div class="auth-input-wrapper">
                        <i class="fa-regular fa-user"></i>
                        <input type="text" name="name" placeholder="" required value="{{ old('name') }}">
                    </div>
                    @error('name')
                        <span class="auth-error-msg">{{ $message }}</span>
                    @enderror
                </div>

                <div class="auth-form-group">
                    <label>EMAIL ADDRESS</label>
                    <div class="auth-input-wrapper">
                        <i class="fa-regular fa-envelope"></i>
                        <input type="email" name="email" placeholder="" required value="{{ old('email') }}">
                    </div>
                    @error('email', 'register')
                        <span class="auth-error-msg">{{ $message }}</span>
                    @enderror
                </div>

                <div class="auth-form-group">
                    <label>PASSWORD</label>
                    <div class="auth-input-wrapper">
                        <i class="fa-solid fa-lock"></i>
                        <input type="password" name="password" placeholder="" required>
                    </div>
                    @error('password', 'register')
                        <span class="auth-error-msg">{{ $message }}</span>
                    @enderror
                </div>

                <div class="auth-form-group">
                    <label>CONFIRM PASSWORD</label>
                    <div class="auth-input-wrapper">
                        <i class="fa-solid fa-arrow-rotate-left"></i>
                        <input type="password" name="password_confirmation" placeholder="" required>
                    </div>
                </div>
                
                <div class="auth-terms">
                    <label class="terms-label">
                        <input type="checkbox" required>
                        <span class="terms-text">I agree to the <a href="#">Terms & Conditions</a> and <a href="#">Privacy Policy</a>.</span>
                    </label>
                </div>

                <button type="submit" class="auth-submit-btn">SIGN UP <i class="fa-solid fa-arrow-right"></i></button>
            </form>

            <div class="auth-switch">
                Already have an account? <a href="javascript:void(0)" onclick="switchAuthView('login')">Sign In</a>
            </div>
        </div>
    </div>
</div>

<script>
    function openAuthModal(view = 'login') {
        document.getElementById('authModalOverlay').classList.add('show');
        switchAuthView(view);
        document.body.style.overflow = 'hidden';
    }

    function closeAuthModal() {
        document.getElementById('authModalOverlay').classList.remove('show');
        document.body.style.overflow = '';
    }

    function switchAuthView(view) {
        if(view === 'login') {
            document.getElementById('loginFormContent').style.display = 'block';
            document.getElementById('registerFormContent').style.display = 'none';
        } else {
            document.getElementById('loginFormContent').style.display = 'none';
            document.getElementById('registerFormContent').style.display = 'block';
        }
    }

    // Close on outside click (mousedown to prevent closing when dragging to select text)
    document.getElementById('authModalOverlay').addEventListener('mousedown', function(e) {
        if(e.target === this) {
            closeAuthModal();
        }
    });

    @if($errors->any())
        // Auto open modal if there are validation errors
        document.addEventListener('DOMContentLoaded', function() {
            @if($errors->has('name') || old('name'))
                openAuthModal('register');
            @else
                openAuthModal('login');
            @endif
        });
    @else
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const authParam = urlParams.get('auth');
            if (authParam === 'login') {
                openAuthModal('login');
            } else if (authParam === 'register') {
                openAuthModal('register');
            }
        });
    @endif
</script>
