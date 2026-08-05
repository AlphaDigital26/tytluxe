@extends('layouts.frontend')

@push('styles')
<style>
/* Dark Theme Profile Settings Adjustments */
.profile-dashboard {
    max-width: var(--container-max);
    margin: 0 auto;
    padding: 0 20px 40px;
    display: flex;
    gap: 30px;
    align-items: flex-start;
}
.profile-sidebar {
    width: 300px;
    background: #111111;
    border: 1px solid #222;
    border-radius: 8px;
    padding: 20px 0;
    position: sticky;
    top: 100px;
    flex-shrink: 0;
}
.profile-tab-btn {
    display: flex;
    align-items: center;
    width: 100%;
    text-align: left;
    padding: 16px 30px;
    background: none;
    border: none;
    font-family: 'Outfit', sans-serif;
    font-size: 14px;
    color: var(--text-muted);
    cursor: pointer;
    transition: var(--transition);
    border-left: 3px solid transparent;
}
.profile-tab-btn:hover, .profile-tab-btn.active {
    background: rgba(194, 154, 98, 0.05);
    color: var(--primary);
    border-left-color: var(--primary);
    font-weight: 500;
}
.profile-tab-btn i { width: 24px; margin-right: 15px; font-size: 18px; text-align: center; }

.profile-content {
    flex: 1;
    background: #111111;
    border: 1px solid #222;
    border-radius: 8px;
    padding: 50px;
    min-width: 0;
}
.profile-tab-pane { display: none; }
.profile-tab-pane.active { display: block; animation: fadeIn 0.4s ease forwards; }

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.tab-title { font-family: 'Cormorant Garamond', serif; font-size: 32px; color: #fff; margin-bottom: 10px; margin-top: 0; font-weight: 500; }
.tab-subtitle { color: var(--text-muted); font-size: 14px; margin-bottom: 40px; border-bottom: 1px solid #222; padding-bottom: 20px; }

.form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 24px 30px; }
.form-group { margin-bottom: 5px; }
.form-group.full-width { grid-column: 1 / -1; }
.form-group label { display: block; font-size: 11px; font-weight: 600; letter-spacing: 1px; color: var(--text-muted); text-transform: uppercase; margin-bottom: 8px; }
.form-group input, .form-group select { width: 100%; padding: 15px 20px; border: 1px solid #333; border-radius: 4px; font-family: 'Outfit', sans-serif; font-size: 14px; background: #0a0a0a; transition: var(--transition); color: #fff; }
.form-group input:focus, .form-group select:focus { outline: none; border-color: var(--primary); background: #000; box-shadow: 0 0 0 1px var(--primary); }
.form-group input[type="checkbox"] { width: auto; margin-right: 12px; transform: scale(1.2); cursor: pointer; }
.checkbox-label { display: flex; align-items: center; font-size: 14px; color: var(--text-muted); margin-bottom: 16px; cursor: pointer; }

/* Custom Buttons for Dark Theme */
.btn-outline-dark { background: transparent; color: #fff; border: 1px solid #555; }
.btn-outline-dark:hover { background: #fff; color: #000; transform: translateY(-2px); border-color: #fff; }
.btn-danger { background-color: #dc3545; color: #fff; border: 1px solid #dc3545; }
.btn-danger:hover { background-color: #c82333; border-color: #bd2130; color: #fff; transform: translateY(-2px); }

.traveller-card { border: 1px solid #222; border-radius: 6px; padding: 25px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; transition: var(--transition); background: #0a0a0a; }
.traveller-card:hover { border-color: var(--primary); transform: translateY(-2px); }
.traveller-info h4 { margin: 0 0 8px; font-size: 20px; font-family: 'Cormorant Garamond', serif; color: #fff; display: flex; align-items: center;}
.traveller-info p { margin: 0; color: var(--text-muted); font-size: 14px; }
.traveller-actions button { background: none; border: none; cursor: pointer; color: var(--text-muted); margin-left: 15px; font-size: 16px; transition: var(--transition); padding: 5px;}
.traveller-actions button:hover { color: var(--primary); transform: scale(1.1); }

.alert-success { background: rgba(212, 237, 218, 0.1); color: #28a745; padding: 16px 20px; border-radius: 4px; margin-bottom: 30px; border-left: 4px solid #28a745; font-size: 14px; }
.alert-error { background: rgba(248, 215, 218, 0.1); color: #dc3545; padding: 16px 20px; border-radius: 4px; margin-bottom: 30px; border-left: 4px solid #dc3545; font-size: 14px; }

@media(max-width: 991px) {
    .profile-dashboard { flex-direction: column; }
    .profile-sidebar { width: 100%; position: static; }
    .profile-content { padding: 30px; }
}
@media(max-width: 768px) {
    .form-grid { grid-template-columns: 1fr; gap: 15px; }
}
</style>
@endpush

@section('content')

<!-- Profile Hero Section -->
<section class="profile-hero" style="background-image: linear-gradient(to bottom, rgba(0,0,0,0.4), rgba(0,0,0,1)), url('{{ asset('assets/images/Carousel.jpeg') }}');">
    <div class="profile-hero-inner">
        <div class="profile-avatar-container">
            <img src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name) }}&color=c9a84c&background=111&size=120" alt="{{ auth()->user()->name }}" class="profile-avatar">
        </div>
        <h1 class="profile-name">{{ auth()->user()->name }}</h1>
        <div class="profile-badge">
            <i class="fa-regular fa-star"></i> Platinum Member
        </div>
    </div>
</section>

<!-- Profile Content Section -->
<section class="profile-content-section">
    <div class="profile-dashboard">
        
        <!-- SIDEBAR -->
        <div class="profile-sidebar">
            <button class="profile-tab-btn active" onclick="switchTab('personal')"><i class="fa-regular fa-user"></i> Personal Info</button>
            <button class="profile-tab-btn" onclick="switchTab('address')"><i class="fa-solid fa-map-location-dot"></i> Address</button>
            <button class="profile-tab-btn" onclick="switchTab('travellers')"><i class="fa-solid fa-users"></i> Travellers & Docs</button>
            <button class="profile-tab-btn" onclick="switchTab('preferences')"><i class="fa-solid fa-sliders"></i> Preferences</button>
            <button class="profile-tab-btn" onclick="switchTab('notifications')"><i class="fa-regular fa-bell"></i> Notifications</button>
            <button class="profile-tab-btn" onclick="switchTab('security')"><i class="fa-solid fa-shield-halved"></i> Security</button>
            <button class="profile-tab-btn" onclick="switchTab('privacy')"><i class="fa-solid fa-lock"></i> Privacy & Account</button>
        </div>

        <!-- CONTENT -->
        <div class="profile-content">
            
            @if(session('status'))
                <div class="alert-success">Successfully updated!</div>
            @endif
            @if($errors->any())
                <div class="alert-error">There were some errors with your submission. Please check the fields below.</div>
            @endif

            <!-- PERSONAL INFO -->
            <div id="tab-personal" class="profile-tab-pane active">
                <h2 class="tab-title">Personal Information</h2>
                <p class="tab-subtitle">Update your basic profile details.</p>

                <form method="POST" action="{{ route('profile.update') }}">
                    @csrf @method('patch')
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Full Name</label>
                            <input type="text" name="name" value="{{ old('name', auth()->user()->name) }}" required>
                            @error('name') <span style="color:#dc3545; font-size:13px;">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <label>Email Address</label>
                            <input type="email" name="email" value="{{ old('email', auth()->user()->email) }}" required>
                            @error('email') <span style="color:#dc3545; font-size:13px;">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <label>Phone Number</label>
                            <input type="text" name="phone" value="{{ old('phone', auth()->user()->phone) }}">
                            @error('phone') <span style="color:#dc3545; font-size:13px;">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <label>Date of Birth</label>
                            <input type="date" name="dob" value="{{ old('dob', auth()->user()->dob ? auth()->user()->dob->format('Y-m-d') : '') }}">
                            @error('dob') <span style="color:#dc3545; font-size:13px;">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <label>Gender</label>
                            <select name="gender">
                                <option value="">Select Gender</option>
                                <option value="Male" {{ old('gender', auth()->user()->gender) == 'Male' ? 'selected' : '' }}>Male</option>
                                <option value="Female" {{ old('gender', auth()->user()->gender) == 'Female' ? 'selected' : '' }}>Female</option>
                                <option value="Other" {{ old('gender', auth()->user()->gender) == 'Other' ? 'selected' : '' }}>Other</option>
                            </select>
                            @error('gender') <span style="color:#dc3545; font-size:13px;">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary mt-4">Save Changes</button>
                </form>
            </div>

            <!-- ADDRESS -->
            <div id="tab-address" class="profile-tab-pane">
                <h2 class="tab-title">Address</h2>
                <p class="tab-subtitle">Manage your billing and residential address.</p>
                @php $addr = auth()->user()->address ?? []; @endphp
                <form method="POST" action="{{ route('profile.update') }}">
                    @csrf @method('patch')
                    <div class="form-grid">
                        <div class="form-group full-width">
                            <label>Street Address</label>
                            <input type="text" name="address[street]" value="{{ old('address.street', $addr['street'] ?? '') }}">
                        </div>
                        <div class="form-group">
                            <label>Country</label>
                            <input type="text" name="address[country]" value="{{ old('address.country', $addr['country'] ?? '') }}">
                        </div>
                        <div class="form-group">
                            <label>State / Province</label>
                            <input type="text" name="address[state]" value="{{ old('address.state', $addr['state'] ?? '') }}">
                        </div>
                        <div class="form-group">
                            <label>City</label>
                            <input type="text" name="address[city]" value="{{ old('address.city', $addr['city'] ?? '') }}">
                        </div>
                        <div class="form-group">
                            <label>ZIP / Postal Code</label>
                            <input type="text" name="address[pin]" value="{{ old('address.pin', $addr['pin'] ?? '') }}">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary mt-4">Save Address</button>
                </form>
            </div>

            <!-- TRAVELLERS -->
            <div id="tab-travellers" class="profile-tab-pane">
                <h2 class="tab-title">Travellers & Documents</h2>
                <p class="tab-subtitle">Manage saved travellers to quickly fill passenger details during booking.</p>
                
                <button class="btn btn-outline-dark mb-4" onclick="openTravellerModal('new')"><i class="fa-solid fa-plus"></i> Add New Traveller</button>
                
                <div class="mt-4">
                    @forelse(auth()->user()->savedTravellers as $traveller)
                    <div class="traveller-card">
                        <div class="traveller-info">
                            <h4>{{ $traveller->name }} <span style="font-size:11px; background:#222; padding:2px 8px; border-radius:12px; text-transform:uppercase; margin-left:8px; font-family:'Outfit', sans-serif;">{{ $traveller->type }}</span></h4>
                            <p>DOB: {{ $traveller->dob ? $traveller->dob->format('d M Y') : 'N/A' }} | Passport: {{ $traveller->passport_number ?: 'N/A' }}</p>
                        </div>
                        <div class="traveller-actions">
                            <button onclick="editTraveller({{ $traveller->id }}, {{ json_encode($traveller) }})"><i class="fa-solid fa-pen"></i></button>
                            <form method="POST" action="{{ route('profile.traveller.destroy', $traveller) }}" style="display:inline;" onsubmit="return confirm('Are you sure?');">
                                @csrf @method('delete')
                                <button type="submit"><i class="fa-solid fa-trash"></i></button>
                            </form>
                        </div>
                    </div>
                    @empty
                    <p style="color:var(--text-muted); text-align:center; padding: 40px; border: 1px dashed #333; border-radius:4px;">No saved travellers yet.</p>
                    @endforelse
                </div>
            </div>

            <!-- PREFERENCES -->
            <div id="tab-preferences" class="profile-tab-pane">
                <h2 class="tab-title">Preferences</h2>
                <p class="tab-subtitle">Tailor your experience on TYT Luxe.</p>
                @php $prefs = auth()->user()->preferences ?? []; @endphp
                <form method="POST" action="{{ route('profile.update') }}">
                    @csrf @method('patch')
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Preferred Currency</label>
                            <select name="preferences[currency]">
                                <option value="INR" {{ (old('preferences.currency', $prefs['currency'] ?? '') == 'INR') ? 'selected' : '' }}>INR (₹)</option>
                                <option value="USD" {{ (old('preferences.currency', $prefs['currency'] ?? '') == 'USD') ? 'selected' : '' }}>USD ($)</option>
                                <option value="EUR" {{ (old('preferences.currency', $prefs['currency'] ?? '') == 'EUR') ? 'selected' : '' }}>EUR (€)</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Preferred Language</label>
                            <select name="preferences[language]">
                                <option value="English" {{ (old('preferences.language', $prefs['language'] ?? '') == 'English') ? 'selected' : '' }}>English</option>
                                <option value="Hindi" {{ (old('preferences.language', $prefs['language'] ?? '') == 'Hindi') ? 'selected' : '' }}>Hindi</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Hotel Room Preference</label>
                            <input type="text" name="preferences[hotel_room]" placeholder="e.g. High floor, Non-smoking" value="{{ old('preferences.hotel_room', $prefs['hotel_room'] ?? '') }}">
                        </div>
                        <div class="form-group">
                            <label>Flight Preference</label>
                            <input type="text" name="preferences[flight]" placeholder="e.g. Window seat, Veg meal" value="{{ old('preferences.flight', $prefs['flight'] ?? '') }}">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary mt-4">Save Preferences</button>
                </form>
            </div>

            <!-- NOTIFICATIONS -->
            <div id="tab-notifications" class="profile-tab-pane">
                <h2 class="tab-title">Notifications</h2>
                <p class="tab-subtitle">Control how and when we contact you.</p>
                @php $notifs = auth()->user()->notifications ?? []; @endphp
                <form method="POST" action="{{ route('profile.update') }}">
                    @csrf @method('patch')
                    
                    <h4 style="margin-top:24px; margin-bottom:16px; color:#fff;">Booking Updates</h4>
                    <label class="checkbox-label">
                        <input type="hidden" name="notifications[booking]" value="0">
                        <input type="checkbox" name="notifications[booking]" value="1" {{ (old('notifications.booking', $notifs['booking'] ?? 1) == 1) ? 'checked' : '' }}>
                        Receive emails for booking confirmations and updates.
                    </label>
                    <label class="checkbox-label">
                        <input type="hidden" name="notifications[cancellation]" value="0">
                        <input type="checkbox" name="notifications[cancellation]" value="1" {{ (old('notifications.cancellation', $notifs['cancellation'] ?? 1) == 1) ? 'checked' : '' }}>
                        Receive emails for cancellations and refund updates.
                    </label>
                    
                    <h4 style="margin-top:32px; margin-bottom:16px; color:#fff;">Promotional</h4>
                    <label class="checkbox-label">
                        <input type="hidden" name="notifications[promo_email]" value="0">
                        <input type="checkbox" name="notifications[promo_email]" value="1" {{ (old('notifications.promo_email', $notifs['promo_email'] ?? 0) == 1) ? 'checked' : '' }}>
                        Send me exclusive travel deals via Email.
                    </label>
                    <label class="checkbox-label">
                        <input type="hidden" name="notifications[promo_sms]" value="0">
                        <input type="checkbox" name="notifications[promo_sms]" value="1" {{ (old('notifications.promo_sms', $notifs['promo_sms'] ?? 0) == 1) ? 'checked' : '' }}>
                        Send me exclusive travel deals via SMS.
                    </label>
                    <label class="checkbox-label">
                        <input type="hidden" name="notifications[promo_whatsapp]" value="0">
                        <input type="checkbox" name="notifications[promo_whatsapp]" value="1" {{ (old('notifications.promo_whatsapp', $notifs['promo_whatsapp'] ?? 0) == 1) ? 'checked' : '' }}>
                        Send me exclusive travel deals via WhatsApp.
                    </label>

                    <button type="submit" class="btn btn-primary" style="margin-top: 32px;">Save Notifications</button>
                </form>
            </div>

            <!-- SECURITY -->
            <div id="tab-security" class="profile-tab-pane">
                <h2 class="tab-title">Security</h2>
                <p class="tab-subtitle">Manage your password and active sessions.</p>

                <h4 style="margin-bottom:16px; color:#fff;">Change Password</h4>
                <form method="POST" action="{{ route('password.update') }}">
                    @csrf @method('put')
                    <div class="form-grid">
                        <div class="form-group full-width">
                            <label>Current Password</label>
                            <input type="password" name="current_password" required>
                            @error('current_password', 'updatePassword') <span style="color:#dc3545; font-size:13px;">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <label>New Password</label>
                            <input type="password" name="password" required>
                            @error('password', 'updatePassword') <span style="color:#dc3545; font-size:13px;">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <label>Confirm New Password</label>
                            <input type="password" name="password_confirmation" required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary mt-2 mb-5">Update Password</button>
                </form>

                <hr style="border:0; border-top:1px solid #222; margin:40px 0;">

                <h4 style="margin-bottom:16px; color:#fff;">Active Sessions</h4>
                <p style="font-size:14px; color:var(--text-muted); margin-bottom:24px;">If necessary, you may log out of all of your other browser sessions across all of your devices. Some of your recent sessions are listed below; however, this list may not be exhaustive. If you feel your account has been compromised, you should also update your password.</p>
                
                <form method="POST" action="{{ route('profile.logout-other-devices') }}">
                    @csrf
                    <div class="form-group" style="max-width: 400px;">
                        <label>Current Password to confirm</label>
                        <input type="password" name="password" required>
                        @error('password') <span style="color:#dc3545; font-size:13px;">{{ $message }}</span> @enderror
                    </div>
                    <button type="submit" class="btn btn-outline-dark mt-2">Logout Other Browser Sessions</button>
                </form>
            </div>

            <!-- PRIVACY -->
            <div id="tab-privacy" class="profile-tab-pane">
                <h2 class="tab-title">Privacy & Account</h2>
                <p class="tab-subtitle">Manage your personal data and account status.</p>
                
                <h4 style="margin-bottom:16px; color:#fff;">Request Personal Data</h4>
                <p style="font-size:14px; color:var(--text-muted); margin-bottom:24px;">You can request a copy of all the personal data we hold about you.</p>
                <button class="btn btn-outline-dark mb-5" onclick="alert('Data request has been initiated. You will receive an email shortly.')">Download My Data</button>

                <hr style="border:0; border-top:1px solid #222; margin:40px 0;">

                <h4 style="margin-bottom:16px; color:#dc3545;">Delete Account</h4>
                <p style="font-size:14px; color:var(--text-muted); margin-bottom:24px;">Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain.</p>
                <form method="POST" action="{{ route('profile.destroy') }}" onsubmit="return confirm('Are you sure you want to permanently delete your account?');">
                    @csrf @method('delete')
                    <div class="form-group" style="max-width: 400px;">
                        <label>Current Password to confirm deletion</label>
                        <input type="password" name="password" required>
                        @error('password', 'userDeletion') <span style="color:#dc3545; font-size:13px;">{{ $message }}</span> @enderror
                    </div>
                    <button type="submit" class="btn btn-danger mt-2">Delete Account</button>
                </form>
            </div>

        </div>
    </div>
</section>

<!-- Modals -->
@include('profile.partials.profile-modals')

@endsection

@push('scripts')
<script>
    function switchTab(tabId) {
        // Update active class on buttons
        document.querySelectorAll('.profile-tab-btn').forEach(btn => {
            btn.classList.remove('active');
        });
        event.currentTarget.classList.add('active');

        // Update active class on panes
        document.querySelectorAll('.profile-tab-pane').forEach(pane => {
            pane.classList.remove('active');
        });
        document.getElementById('tab-' + tabId).classList.add('active');
    }

    // Check URL hash to open specific tab on load
    document.addEventListener("DOMContentLoaded", function() {
        if(window.location.hash) {
            let tab = window.location.hash.substring(1);
            let btn = document.querySelector(`.profile-tab-btn[onclick="switchTab('${tab}')"]`);
            if(btn) btn.click();
        }
    });
</script>
@endpush
