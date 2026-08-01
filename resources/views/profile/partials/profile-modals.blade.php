<!-- Profile Modal Overlay -->
<div class="auth-modal-overlay" id="profileModalOverlay">
    <div class="auth-modal-box">
        <button class="auth-modal-close" onclick="closeProfileModal()">
            <i class="fa-solid fa-xmark"></i>
        </button>

        <!-- Edit Details Form -->
        <div class="auth-modal-content" id="profileDetailsFormContent" style="display: none;">
            <h2 class="auth-modal-title">Edit Details</h2>
            <p class="auth-modal-subtitle">Update your personal contact information.</p>

            <form method="POST" action="{{ route('profile.update') }}">
                @csrf
                @method('patch')
                
                <div class="auth-form-group">
                    <label>FULL NAME</label>
                    <input type="text" name="name" placeholder="Enter your full name" required value="{{ old('name', auth()->user()->name) }}">
                    @error('name')
                        <span class="auth-error-msg">{{ $message }}</span>
                    @enderror
                </div>

                <div class="auth-form-group">
                    <label>EMAIL ADDRESS</label>
                    <input type="email" name="email" placeholder="Enter your email" required value="{{ old('email', auth()->user()->email) }}">
                    @error('email')
                        <span class="auth-error-msg">{{ $message }}</span>
                    @enderror
                </div>

                <div class="auth-form-group">
                    <label>PHONE NUMBER</label>
                    <input type="text" name="phone" placeholder="Enter your phone number" value="{{ old('phone', auth()->user()->phone) }}">
                    @error('phone')
                        <span class="auth-error-msg">{{ $message }}</span>
                    @enderror
                </div>

                <button type="submit" class="auth-submit-btn">SAVE CHANGES</button>
            </form>
        </div>

        <!-- Update Password Form -->
        <div class="auth-modal-content" id="profilePasswordFormContent" style="display: none;">
            <h2 class="auth-modal-title">Update Password</h2>
            <p class="auth-modal-subtitle">Ensure your account is using a long, random password to stay secure.</p>

            <form method="POST" action="{{ route('password.update') }}">
                @csrf
                @method('put')
                
                <div class="auth-form-group">
                    <label>CURRENT PASSWORD</label>
                    <input type="password" name="current_password" placeholder="Enter current password" required>
                    @error('current_password', 'updatePassword')
                        <span class="auth-error-msg">{{ $message }}</span>
                    @enderror
                </div>

                <div class="auth-form-group">
                    <label>NEW PASSWORD</label>
                    <input type="password" name="password" placeholder="Enter new password" required>
                    @error('password', 'updatePassword')
                        <span class="auth-error-msg">{{ $message }}</span>
                    @enderror
                </div>

                <div class="auth-form-group">
                    <label>CONFIRM NEW PASSWORD</label>
                    <input type="password" name="password_confirmation" placeholder="Confirm new password" required>
                </div>

                <button type="submit" class="auth-submit-btn">UPDATE PASSWORD</button>
            </form>
        </div>
    </div>
</div>

<script>
    function openProfileModal(view) {
        document.getElementById('profileModalOverlay').classList.add('show');
        
        if (view === 'details') {
            document.getElementById('profileDetailsFormContent').style.display = 'block';
            document.getElementById('profilePasswordFormContent').style.display = 'none';
        } else {
            document.getElementById('profileDetailsFormContent').style.display = 'none';
            document.getElementById('profilePasswordFormContent').style.display = 'block';
        }
        
        document.body.style.overflow = 'hidden';
    }

    function closeProfileModal() {
        document.getElementById('profileModalOverlay').classList.remove('show');
        document.body.style.overflow = '';
    }

    // Close on outside click (mousedown to prevent closing when dragging to select text)
    document.getElementById('profileModalOverlay').addEventListener('mousedown', function(e) {
        if(e.target === this) {
            closeProfileModal();
        }
    });

    // Auto open modal if there are validation errors
    document.addEventListener('DOMContentLoaded', function() {
        @if($errors->updatePassword->any())
            openProfileModal('password');
        @elseif($errors->any() && !request()->routeIs('login') && !request()->routeIs('register'))
            // Only open details modal if we are on the profile page and there are generic errors
            openProfileModal('details');
        @endif
        
        @if (session('status') === 'profile-updated' || session('status') === 'password-updated')
            // Optional: You could show a toast notification here
            // alert('Update successful');
        @endif
    });
</script>
