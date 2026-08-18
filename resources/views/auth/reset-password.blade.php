<x-guest-layout>
    <style>
        .password-checklist {
            margin-top: 10px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
            font-size: 13px;
            color: var(--text-muted, #888);
        }
        .checklist-item {
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .checklist-item i {
            font-size: 14px;
            color: #555;
        }
        .checklist-item.valid {
            color: #2ecc71;
        }
        .checklist-item.valid i {
            color: #2ecc71;
        }
    </style>

    <form method="POST" action="{{ route('password.store') }}" id="resetPasswordForm">
        @csrf

        <!-- Password Reset Token -->
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <!-- Email Address -->
        <div class="form-group" style="margin-bottom: 20px;">
            <label style="display: block; font-size: 12px; letter-spacing: 1px; color: #aaa; margin-bottom: 8px;">EMAIL</label>
            <input type="email" name="email" value="{{ old('email', $request->email) }}" required autofocus autocomplete="username" class="form-input" style="width: 100%; padding: 12px 15px; background: transparent; border: 1px solid #444; border-radius: 6px; color: #fff; outline: none; transition: border-color 0.3s;" onfocus="this.style.borderColor='#d4af37'" onblur="this.style.borderColor='#444'">
            <x-input-error :messages="$errors->get('email')" style="color: #e74c3c; font-size: 12px; margin-top: 5px;" />
        </div>

        <!-- Password -->
        <div class="form-group" style="margin-bottom: 20px;">
            <label style="display: block; font-size: 12px; letter-spacing: 1px; color: #aaa; margin-bottom: 8px;">PASSWORD</label>
            <div style="position: relative;">
                <input id="password" type="password" name="password" required autocomplete="new-password" class="form-input" style="width: 100%; padding: 12px 40px 12px 15px; background: transparent; border: 1px solid #444; border-radius: 6px; color: #fff; outline: none; transition: border-color 0.3s;" onfocus="this.style.borderColor='#d4af37'" onblur="this.style.borderColor='#444'">
                <i class="fa-regular fa-eye" style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); cursor: pointer; color: #888; font-size: 16px; z-index: 10;" onclick="const input = document.getElementById('password'); if(input.type === 'password') { input.type = 'text'; this.classList.replace('fa-eye', 'fa-eye-slash'); } else { input.type = 'password'; this.classList.replace('fa-eye-slash', 'fa-eye'); }"></i>
            </div>
            
            <div class="password-checklist">
                <div class="checklist-item" id="req-length"><i class="fa-solid fa-xmark" style="color: #e74c3c;"></i> Min 8 characters</div>
                <div class="checklist-item" id="req-uppercase"><i class="fa-solid fa-xmark" style="color: #e74c3c;"></i> Uppercase letter</div>
                <div class="checklist-item" id="req-lowercase"><i class="fa-solid fa-xmark" style="color: #e74c3c;"></i> Lowercase letter</div>
                <div class="checklist-item" id="req-number"><i class="fa-solid fa-xmark" style="color: #e74c3c;"></i> Numeric digit</div>
                <div class="checklist-item" id="req-special"><i class="fa-solid fa-xmark" style="color: #e74c3c;"></i> Special character</div>
                <div class="checklist-item valid" id="req-spaces"><i class="fa-solid fa-check"></i> No spaces</div>
            </div>

            <x-input-error :messages="$errors->get('password')" style="color: #e74c3c; font-size: 12px; margin-top: 5px;" />
        </div>

        <!-- Confirm Password -->
        <div class="form-group" style="margin-bottom: 30px;">
            <label style="display: block; font-size: 12px; letter-spacing: 1px; color: #aaa; margin-bottom: 8px;">CONFIRM PASSWORD</label>
            <div style="position: relative;">
                <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" class="form-input" style="width: 100%; padding: 12px 40px 12px 15px; background: transparent; border: 1px solid #444; border-radius: 6px; color: #fff; outline: none; transition: border-color 0.3s;" onfocus="this.style.borderColor='#d4af37'" onblur="this.style.borderColor='#444'">
                <i class="fa-regular fa-eye" style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); cursor: pointer; color: #888; font-size: 16px; z-index: 10;" onclick="const input = document.getElementById('password_confirmation'); if(input.type === 'password') { input.type = 'text'; this.classList.replace('fa-eye', 'fa-eye-slash'); } else { input.type = 'password'; this.classList.replace('fa-eye-slash', 'fa-eye'); }"></i>
            </div>
            <x-input-error :messages="$errors->get('password_confirmation')" style="color: #e74c3c; font-size: 12px; margin-top: 5px;" />
        </div>

        <div class="flex items-center justify-end" style="text-align: right;">
            <button type="submit" id="submitBtn" style="background: #d4af37; color: #000; font-weight: 600; padding: 12px 24px; border: none; border-radius: 4px; cursor: pointer; text-transform: uppercase; font-size: 14px; letter-spacing: 1px;">
                Reset Password
            </button>
        </div>
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const passwordInput = document.getElementById('password');
            const submitBtn = document.getElementById('submitBtn');
            
            const reqLength = document.getElementById('req-length');
            const reqUppercase = document.getElementById('req-uppercase');
            const reqLowercase = document.getElementById('req-lowercase');
            const reqNumber = document.getElementById('req-number');
            const reqSpecial = document.getElementById('req-special');
            const reqSpaces = document.getElementById('req-spaces');

            function validatePassword() {
                const val = passwordInput.value;
                let allValid = true;

                // Length >= 8
                if (val.length >= 8) {
                    reqLength.classList.add('valid');
                    reqLength.querySelector('i').className = 'fa-solid fa-check';
                    reqLength.querySelector('i').style.color = '#2ecc71';
                } else {
                    reqLength.classList.remove('valid');
                    reqLength.querySelector('i').className = 'fa-solid fa-xmark';
                    reqLength.querySelector('i').style.color = '#e74c3c';
                    allValid = false;
                }

                // Uppercase
                if (/[A-Z]/.test(val)) {
                    reqUppercase.classList.add('valid');
                    reqUppercase.querySelector('i').className = 'fa-solid fa-check';
                    reqUppercase.querySelector('i').style.color = '#2ecc71';
                } else {
                    reqUppercase.classList.remove('valid');
                    reqUppercase.querySelector('i').className = 'fa-solid fa-xmark';
                    reqUppercase.querySelector('i').style.color = '#e74c3c';
                    allValid = false;
                }

                // Lowercase
                if (/[a-z]/.test(val)) {
                    reqLowercase.classList.add('valid');
                    reqLowercase.querySelector('i').className = 'fa-solid fa-check';
                    reqLowercase.querySelector('i').style.color = '#2ecc71';
                } else {
                    reqLowercase.classList.remove('valid');
                    reqLowercase.querySelector('i').className = 'fa-solid fa-xmark';
                    reqLowercase.querySelector('i').style.color = '#e74c3c';
                    allValid = false;
                }

                // Number
                if (/[0-9]/.test(val)) {
                    reqNumber.classList.add('valid');
                    reqNumber.querySelector('i').className = 'fa-solid fa-check';
                    reqNumber.querySelector('i').style.color = '#2ecc71';
                } else {
                    reqNumber.classList.remove('valid');
                    reqNumber.querySelector('i').className = 'fa-solid fa-xmark';
                    reqNumber.querySelector('i').style.color = '#e74c3c';
                    allValid = false;
                }

                // Special Character
                if (/[!@#$%^&*(),.?":{}|<>\-_\+=\/\[\]\\;'`~]/.test(val)) {
                    reqSpecial.classList.add('valid');
                    reqSpecial.querySelector('i').className = 'fa-solid fa-check';
                    reqSpecial.querySelector('i').style.color = '#2ecc71';
                } else {
                    reqSpecial.classList.remove('valid');
                    reqSpecial.querySelector('i').className = 'fa-solid fa-xmark';
                    reqSpecial.querySelector('i').style.color = '#e74c3c';
                    allValid = false;
                }

                // No Spaces
                if (val.length > 0 && !/\s/.test(val)) {
                    reqSpaces.classList.add('valid');
                    reqSpaces.querySelector('i').className = 'fa-solid fa-check';
                    reqSpaces.querySelector('i').style.color = '#2ecc71';
                } else if (val.length === 0) {
                    reqSpaces.classList.add('valid'); // Empty is technically no spaces, but overall invalid
                    reqSpaces.querySelector('i').className = 'fa-solid fa-check';
                    reqSpaces.querySelector('i').style.color = '#2ecc71';
                } else {
                    reqSpaces.classList.remove('valid');
                    reqSpaces.querySelector('i').className = 'fa-solid fa-xmark';
                    reqSpaces.querySelector('i').style.color = '#e74c3c';
                    allValid = false;
                }
            }

            passwordInput.addEventListener('input', validatePassword);
            // Run once on load in case of auto-fill
            validatePassword();
        });
    </script>
</x-guest-layout>
