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
    .profile-fields-grid { grid-template-columns: 1fr !important; }
}

/* ── Profile Section Cards ─────────────────────────────── */
.profile-section-card {
    border: 1px solid #2a2a2a;
    border-radius: 8px;
    overflow: hidden;
    background: #0d0d0d;
}
.profile-section-header {
    display: flex;
    align-items: flex-start;
    gap: 16px;
    padding: 22px 28px;
    border-bottom: 1px solid #222;
    background: rgba(194,154,98,0.04);
}
.profile-section-header > i {
    font-size: 22px;
    color: var(--primary);
    margin-top: 2px;
    flex-shrink: 0;
}
.profile-section-title {
    font-family: 'Cormorant Garamond', serif;
    font-size: 20px;
    font-weight: 600;
    color: #fff;
    margin: 0 0 4px;
}
.profile-section-sub {
    font-size: 12px;
    color: var(--text-muted);
    margin: 0;
}

/* ── Profile Fields Grid ───────────────────────────────── */
.profile-fields-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0;
}
.pf-field {
    padding: 20px 28px;
    border-right: 1px solid #1e1e1e;
    border-bottom: 1px solid #1e1e1e;
    transition: background 0.2s;
}
.pf-field:nth-child(even) { border-right: none; }
.pf-field:hover { background: rgba(255,255,255,0.02); }
.pf-col-full { grid-column: 1 / -1; border-right: none; }
.pf-label {
    display: block;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 1.2px;
    text-transform: uppercase;
    color: var(--text-muted);
    margin-bottom: 8px;
}
.pf-input {
    width: 100%;
    background: transparent;
    border: none;
    border-bottom: 1px solid #333;
    border-radius: 0;
    color: #fff;
    font-family: 'Outfit', sans-serif;
    font-size: 14px;
    padding: 6px 0 8px;
    outline: none;
    transition: border-color 0.2s;
    box-sizing: border-box;
}
.pf-input:focus { border-bottom-color: var(--primary); }
/* Fix for browser autofill background */
.pf-input:-webkit-autofill,
.pf-input:-webkit-autofill:hover, 
.pf-input:-webkit-autofill:focus, 
.pf-input:-webkit-autofill:active{
    -webkit-box-shadow: 0 0 0 30px transparent inset !important;
    -webkit-text-fill-color: #fff !important;
    transition: background-color 5000s ease-in-out 0s;
}
.pf-select { cursor: pointer; }
.pf-select option { background: #111; color: #fff; }
.pf-error { font-size: 11px; color: #dc3545; margin-top: 4px; display: block; }
.pf-field--note { display: flex; align-items: center; padding: 12px 28px; }

/* ── Contact Section ───────────────────────────────────── */
.pf-field--contact { padding: 22px 28px; }
.pf-contact-verified {
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: rgba(255,255,255,0.04);
    border: 1px solid #2a2a2a;
    border-radius: 6px;
    padding: 14px 18px;
    margin-bottom: 10px;
}
.pf-contact-value {
    font-size: 14px;
    color: #e8e8e8;
    font-weight: 500;
}
.pf-verified-badge {
    font-size: 11px;
    color: #22c55e;
    font-weight: 600;
    letter-spacing: 0.3px;
    display: flex;
    align-items: center;
    gap: 4px;
}
.pf-add-link {
    background: none;
    border: none;
    color: var(--primary);
    font-family: 'Outfit', sans-serif;
    font-size: 12px;
    font-weight: 700;
    letter-spacing: 0.6px;
    cursor: pointer;
    padding: 0;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    text-transform: uppercase;
    transition: opacity 0.2s;
}
.pf-add-link:hover { opacity: 0.75; }
.pf-hidden { display: none !important; }

/* ── Save Button ───────────────────────────────────────── */
.pf-save-btn {
    padding: 14px 40px;
    font-size: 14px;
    font-weight: 600;
    letter-spacing: 0.5px;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: transform 0.2s, box-shadow 0.2s;
}
.pf-save-btn:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(194,154,98,0.3); }

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
        <div class="profile-badge" style="display: flex; flex-direction: column; gap: 8px; align-items: center; justify-content: center; font-size: 15px; letter-spacing: 0.5px; border: none; background: transparent; padding: 0; box-shadow: none;">
            <div style="display: flex; align-items: center; gap: 8px; color: #fff;">
                <i class="fa-solid fa-phone" style="font-size: 16px;"></i> 
                @if(auth()->user()->phone)
                    {{ auth()->user()->phone }}
                @else
                    <a href="#contact-details-section" onclick="switchTab('personal'); setTimeout(() => { document.getElementById('phone-input-row').classList.remove('pf-hidden'); document.getElementById('profilePhoneNumberAdd').focus(); }, 100);" style="color: var(--primary); text-decoration: underline; text-underline-offset: 4px; cursor: pointer;">Add Mobile Number</a>
                @endif
            </div>
            <div style="display: flex; align-items: center; gap: 8px; color: #fff; text-transform: lowercase;">
                <i class="fa-regular fa-envelope" style="font-size: 16px;"></i> {{ auth()->user()->email }}
            </div>
        </div>
    </div>
</section>

<!-- Profile Content Section -->
<section class="profile-content-section">
    <div class="profile-dashboard">
        
        <!-- SIDEBAR -->
        <div class="profile-sidebar">
            <button class="profile-tab-btn active" onclick="switchTab('personal')"><i class="fa-regular fa-user"></i> Personal Info</button>
            <button class="profile-tab-btn" onclick="switchTab('travellers')"><i class="fa-solid fa-users"></i> Co-Travellers</button>
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
                <h2 class="tab-title">My Profile</h2>

                <form method="POST" action="{{ route('profile.update') }}" id="profile-main-form">
                    @csrf @method('patch')

                    {{-- ─── GENERAL INFORMATION ─── --}}
                    <div class="profile-section-card">
                        <div class="profile-section-header">
                            <i class="fa-regular fa-id-card"></i>
                            <div>
                                <h3 class="profile-section-title">General Information</h3>
                                <p class="profile-section-sub">Manage your name, date of birth and other general details.</p>
                            </div>
                        </div>

                        <div class="profile-fields-grid">

                            {{-- Row 1: Full Name --}}
                            <div class="pf-field pf-col-full">
                                <label class="pf-label">Full Name (As per Govt. ID)</label>
                                <input type="text" name="name" class="pf-input" placeholder="First & Last Name as per Aadhaar or Passport"
                                    value="{{ old('name', auth()->user()->name) }}" required>
                                @error('name') <span class="pf-error">{{ $message }}</span> @enderror
                            </div>

                            {{-- Row 2: Gender | Date of Birth | Nationality --}}
                            <div class="pf-field">
                                <label class="pf-label">Gender</label>  
                                <select name="gender" class="pf-input pf-select">
                                    <option value="">Select Gender</option>
                                    <option value="Male"   {{ old('gender', auth()->user()->gender) == 'Male'   ? 'selected' : '' }}>Male</option>
                                    <option value="Female" {{ old('gender', auth()->user()->gender) == 'Female' ? 'selected' : '' }}>Female</option>
                                    <option value="Other"  {{ old('gender', auth()->user()->gender) == 'Other'  ? 'selected' : '' }}>Other</option>
                                </select>
                                @error('gender') <span class="pf-error">{{ $message }}</span> @enderror
                            </div>
                            <div class="pf-field">
                                <label class="pf-label">Date of Birth</label>
                                <input type="date" name="dob" class="pf-input"
                                    value="{{ old('dob', auth()->user()->dob ? auth()->user()->dob->format('Y-m-d') : '') }}">
                                @error('dob') <span class="pf-error">{{ $message }}</span> @enderror
                            </div>
                            <div class="pf-field">
                                <label class="pf-label">Nationality</label>
                                <select name="nationality" class="pf-input pf-select">
                                    <option value="">Select Nationality</option>
                                    @php
                                        $countries = ['Indian','American','British','Australian','Canadian','French','German','Japanese','Chinese','Singaporean','Emirati','South African','Brazilian','Mexican','Italian','Spanish','Russian','Korean','Dutch','Swedish','Swiss','Norwegian','Danish','Finnish','Belgian','Austrian','Portuguese','Greek','Polish','Czech','Hungarian','Romanian','Turkish','Egyptian','Kenyan','Nigerian','Ghanaian','Bangladeshi','Pakistani','Sri Lankan','Nepali','Bhutanese','Maldivian','Thai','Malaysian','Indonesian','Filipino','Vietnamese','Burmese','Cambodian','Laotian'];
                                        $userNat = old('nationality', auth()->user()->nationality);
                                    @endphp
                                    @foreach($countries as $country)
                                        <option value="{{ $country }}" {{ $userNat == $country ? 'selected' : '' }}>{{ $country }}</option>
                                    @endforeach
                                </select>
                                @error('nationality') <span class="pf-error">{{ $message }}</span> @enderror
                            </div>

                            {{-- Row 3: Marital Status | Anniversary --}}
                            <div class="pf-field">
                                <label class="pf-label">Marital Status</label>
                                <select name="marital_status" class="pf-input pf-select" id="marital-status-select">
                                    <option value="">Select Marital Status</option>
                                    <option value="Single"   {{ old('marital_status', auth()->user()->marital_status) == 'Single'   ? 'selected' : '' }}>Single</option>
                                    <option value="Married"  {{ old('marital_status', auth()->user()->marital_status) == 'Married'  ? 'selected' : '' }}>Married</option></select>
                                @error('marital_status') <span class="pf-error">{{ $message }}</span> @enderror
                            </div>
                            <div class="pf-field" id="anniversary-field" style="{{ old('marital_status', auth()->user()->marital_status) == 'Married' ? '' : 'opacity:0.4; pointer-events:none;' }}">
                                <label class="pf-label">Anniversary</label>
                                <input type="date" name="anniversary" class="pf-input"
                                    value="{{ old('anniversary', auth()->user()->anniversary ? auth()->user()->anniversary->format('Y-m-d') : '') }}">
                                @error('anniversary') <span class="pf-error">{{ $message }}</span> @enderror
                            </div>

                            {{-- City and State moved to Address Details --}}

                        </div>{{-- .profile-fields-grid --}}
                    </div>{{-- .profile-section-card --}}

                    {{-- ─── CONTACT DETAILS ─── --}}
                    <div class="profile-section-card" id="contact-details-section" style="margin-top: 30px;">
                        <div class="profile-section-header">
                            <i class="fa-regular fa-address-book"></i>
                            <div>
                                <h3 class="profile-section-title">Contact Details</h3>
                                <p class="profile-section-sub">Add contact information to receive booking details &amp; other alerts.</p>
                            </div>
                        </div>

                        <div class="profile-fields-grid">

                            {{-- Mobile Number --}}
                            <div class="pf-field pf-field--contact">
                                <label class="pf-label">Mobile Number</label>
                                @if(auth()->user()->phone)
                                    <div class="pf-contact-verified">
                                        <span class="pf-contact-value">
                                            <i class="fa-solid fa-phone" style="margin-right:8px; color:var(--primary);"></i>
                                            {{ auth()->user()->phone }}
                                        </span>
                                    </div>
                                    <input type="hidden" name="phone" id="profilePhoneHiddenEdit" value="{{ auth()->user()->phone }}">
                                    <button type="button" class="pf-add-link" onclick="document.getElementById('phone-edit-row').classList.toggle('pf-hidden')">
                                        <i class="fa-solid fa-pen-to-square"></i> Change Number
                                    </button>
                                    <div id="phone-edit-row" class="pf-hidden" style="margin-top:10px;">
                                        <div style="display: flex;">
                                            <select id="profilePhoneCodeEdit" class="pf-input pf-select" style="width: 140px; border-right: none; border-radius: 4px 0 0 4px; padding: 15px;" onchange="mergePhone('Edit')">
                                                <option value="+91" selected>+91 (India)</option>
                                                <option value="+1">+1 (USA/Canada)</option>
                                                <option value="+44">+44 (UK)</option>
                                                <option value="+61">+61 (Australia)</option>
                                                <option value="+971">+971 (UAE)</option>
                                            </select>
                                            <input type="text" id="profilePhoneNumberEdit" class="pf-input" placeholder="Mobile Number" style="border-radius: 0 4px 4px 0; flex: 1; border-left: 1px solid #333; padding-left: 15px;" oninput="mergePhone('Edit')">
                                        </div>
                                        @error('phone') <span class="pf-error">{{ $message }}</span> @enderror
                                    </div>
                                @else
                                    <input type="hidden" name="phone" id="profilePhoneHiddenAdd" value="">
                                    <button type="button" class="pf-add-link" onclick="document.getElementById('phone-input-row').classList.toggle('pf-hidden')">
                                        <i class="fa-solid fa-plus"></i> Add Mobile Number
                                    </button>
                                    <div id="phone-input-row" class="pf-hidden" style="margin-top:10px;">
                                        <div style="display: flex;">
                                            <select id="profilePhoneCodeAdd" class="pf-input pf-select" style="width: 140px; border-right: none; border-radius: 4px 0 0 4px; padding: 15px;" onchange="mergePhone('Add')">
                                                <option value="+91" selected>+91 (India)</option>
                                                <option value="+1">+1 (USA/Canada)</option>
                                                <option value="+44">+44 (UK)</option>
                                                <option value="+61">+61 (Australia)</option>
                                                <option value="+971">+971 (UAE)</option>
                                            </select>
                                            <input type="text" id="profilePhoneNumberAdd" class="pf-input" placeholder="Mobile Number" style="border-radius: 0 4px 4px 0; flex: 1; border-left: 1px solid #333; padding-left: 15px;" oninput="mergePhone('Add')">
                                        </div>
                                        @error('phone') <span class="pf-error">{{ $message }}</span> @enderror
                                    </div>
                                @endif
                            </div>

                            {{-- Email ID --}}
                            <div class="pf-field pf-field--contact">
                                <label class="pf-label">Email ID</label>
                                <div class="pf-contact-verified">
                                    <span class="pf-contact-value">
                                        <i class="fa-regular fa-envelope" style="margin-right:8px; color:var(--primary);"></i>
                                        {{ auth()->user()->email }}
                                    </span>
                                </div>
                                <input type="hidden" name="email" value="{{ auth()->user()->email }}">
                                <button type="button" class="pf-add-link" onclick="document.getElementById('email-edit-row').classList.toggle('pf-hidden')">
                                    <i class="fa-solid fa-pen-to-square"></i> Change Email
                                </button>
                                <div id="email-edit-row" class="pf-hidden" style="margin-top:10px;">
                                    <input type="email" name="email" class="pf-input" placeholder="your@email.com"
                                        value="{{ auth()->user()->email }}">
                                    @error('email') <span class="pf-error">{{ $message }}</span> @enderror
                                </div>
                            </div>

                        </div>{{-- .profile-fields-grid --}}
                    </div>{{-- .profile-section-card --}}

                    {{-- ─── DOCUMENTS DETAILS ─── --}}
                    <div class="profile-section-card" style="margin-top: 30px;">
                        <div class="profile-section-header">
                            <i class="fa-regular fa-folder-open"></i>
                            <div>
                                <h3 class="profile-section-title">Documents Details</h3>
                                <p class="profile-section-sub">Manage your travel and identification documents.</p>
                            </div>
                        </div>

                        <div class="profile-fields-grid">
                            <div class="pf-field">
                                <label class="pf-label">PASSPORT NO.</label>
                                <input type="text" name="passport_no" class="pf-input" placeholder="Passport No." value="{{ old('passport_no', auth()->user()->passport_no) }}">
                                @error('passport_no') <span class="pf-error">{{ $message }}</span> @enderror
                            </div>
                            <div class="pf-field">
                                <label class="pf-label">EXPIRY DATE</label>
                                <input type="date" name="passport_expiry" class="pf-input" value="{{ old('passport_expiry', auth()->user()->passport_expiry ? auth()->user()->passport_expiry->format('Y-m-d') : '') }}">
                                @error('passport_expiry') <span class="pf-error">{{ $message }}</span> @enderror
                            </div>
                            <div class="pf-field">
                                <label class="pf-label">ISSUING COUNTRY</label>
                                <select name="passport_issuing_country" class="pf-input pf-select">
                                    <option value="">Select Country</option>
                                    @php
                                        $userPPCountry = old('passport_issuing_country', auth()->user()->passport_issuing_country);
                                    @endphp
                                    @foreach($countries as $country)
                                        <option value="{{ $country }}" {{ $userPPCountry == $country ? 'selected' : '' }}>{{ $country }}</option>
                                    @endforeach
                                </select>
                                @error('passport_issuing_country') <span class="pf-error">{{ $message }}</span> @enderror
                            </div>
                            <div class="pf-field pf-col-full" style="margin-top: 15px; border-top: 1px dashed #333; padding-top: 20px;">
                                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 15px;">
                                    <label class="pf-label" style="margin-bottom:0;">GOVT. IDs</label>
                                    <button type="button" class="pf-add-link" onclick="addGovtIdRow()">
                                        <i class="fa-solid fa-plus"></i> ADD ID
                                    </button>
                                </div>
                                <div id="govt-ids-container">
                                    @php
                                        $govtIds = old('govt_ids', auth()->user()->govt_ids ?? []);
                                        if (empty($govtIds)) {
                                            $govtIds = [['type' => '', 'number' => '']];
                                        }
                                    @endphp
                                    @foreach($govtIds as $index => $id)
                                        <div class="govt-id-row" data-index="{{ $index }}" style="display:flex; gap:15px; align-items:flex-end; margin-bottom: 15px;">
                                            <div style="flex:1;">
                                                <select name="govt_ids[{{ $index }}][type]" class="pf-input pf-select govt-id-type" onchange="updateGovtIdValidation(this)">
                                                    <option value="">Select ID Type</option>
                                                    <option value="Aadhaar Card" {{ ($id['type'] ?? '') == 'Aadhaar Card' ? 'selected' : '' }}>Aadhaar Card</option>
                                                    <option value="PAN Card" {{ ($id['type'] ?? '') == 'PAN Card' ? 'selected' : '' }}>PAN Card</option>
                                                    <option value="Driving License" {{ ($id['type'] ?? '') == 'Driving License' ? 'selected' : '' }}>Driving License</option>
                                                </select>
                                                @error("govt_ids.{$index}.type") <span class="pf-error">{{ $message }}</span> @enderror
                                            </div>
                                            <div style="flex:2;">
                                                <input type="text" name="govt_ids[{{ $index }}][number]" class="pf-input govt-id-number" placeholder="ID Number" value="{{ $id['number'] ?? '' }}" oninput="this.value = this.value.toUpperCase()">
                                                @error("govt_ids.{$index}.number") <span class="pf-error">{{ $message }}</span> @enderror
                                            </div>
                                            <button type="button" class="btn btn-sm btn-outline-danger btn-remove-id" style="padding: 6px 12px; border-radius: 4px; background: transparent; border: 1px solid #dc3545; color: #dc3545; display: {{ count($govtIds) > 1 ? 'block' : 'none' }};" onclick="removeGovtIdRow(this)">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            
                            <div class="pf-field pf-field--note pf-col-full">
                                <span style="font-size:12px; color:var(--text-muted);"><strong style="color:#eab308;">NOTE:</strong> Your PAN No. will only be used for international bookings as per RBI Guidelines</span>
                            </div>
                        </div>
                    </div>

                    {{-- ─── ADDRESS DETAILS ─── --}}
                    <div class="profile-section-card" style="margin-top: 30px;">
                        <div class="profile-section-header">
                            <i class="fa-solid fa-map-location-dot"></i>
                            <div>
                                <h3 class="profile-section-title">Address Details</h3>
                                <p class="profile-section-sub">Manage your billing and residential address.</p>
                            </div>
                        </div>

                        <div class="profile-fields-grid">
                            <div class="pf-field pf-col-full">
                                <label class="pf-label">Street Address</label>
                                <input type="text" name="address[street]" class="pf-input" placeholder="Street Address" value="{{ old('address.street', auth()->user()->address['street'] ?? '') }}">
                            </div>
                            <div class="pf-field">
                                <label class="pf-label">Country</label>
                                <input type="text" name="address[country]" class="pf-input" placeholder="Country" value="{{ old('address.country', auth()->user()->address['country'] ?? '') }}">
                            </div>
                            <div class="pf-field">
                                <label class="pf-label">ZIP / Postal Code</label>
                                <input type="text" name="address[pin]" class="pf-input" placeholder="ZIP / Postal Code" value="{{ old('address.pin', auth()->user()->address['pin'] ?? '') }}">
                            </div>
                            <div class="pf-field">
                                <label class="pf-label">City of Residence</label>
                                <input type="text" name="address[city]" class="pf-input" placeholder="City of Residence" value="{{ old('address.city', auth()->user()->address['city'] ?? '') }}">
                            </div>
                            <div class="pf-field">
                                <label class="pf-label">State</label>
                                <select name="address[state]" class="pf-input pf-select">
                                    <option value="">Select State</option>
                                    @php
                                        $states = ['Andhra Pradesh','Arunachal Pradesh','Assam','Bihar','Chhattisgarh','Goa','Gujarat','Haryana','Himachal Pradesh','Jharkhand','Karnataka','Kerala','Madhya Pradesh','Maharashtra','Manipur','Meghalaya','Mizoram','Nagaland','Odisha','Punjab','Rajasthan','Sikkim','Tamil Nadu','Telangana','Tripura','Uttar Pradesh','Uttarakhand','West Bengal','Andaman and Nicobar Islands','Chandigarh','Dadra and Nagar Haveli and Daman and Diu','Delhi','Jammu and Kashmir','Ladakh','Lakshadweep','Puducherry'];
                                        $userState = old('address.state', auth()->user()->address['state'] ?? '');
                                    @endphp
                                    @foreach($states as $state)
                                        <option value="{{ $state }}" {{ $userState == $state ? 'selected' : '' }}>{{ $state }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @if(auth()->user()->address['state'] ?? false)
                            <div class="pf-field pf-field--note pf-col-full">
                                <i class="fa-solid fa-circle-info" style="color:var(--primary); margin-right:6px;"></i>
                                <span style="font-size:12px; color:var(--text-muted);">Required for GST purpose on your tax invoice.</span>
                            </div>
                            @endif
                        </div>
                    </div>

                    <div style="margin-top: 32px; display:flex; justify-content: flex-end;">
                        <button type="submit" class="btn btn-primary pf-save-btn">
                            <i class="fa-solid fa-floppy-disk"></i> Save Changes
                        </button>
                    </div>

                </form>
            </div>


            <!-- TRAVELLERS -->
            <div id="tab-travellers" class="profile-tab-pane">
                <h2 class="tab-title">Co-Travellers</h2>
                <p class="tab-subtitle">Manage saved co-travellers to quickly fill passenger details during booking.</p>
                
                <button class="btn btn-outline-dark mb-4" onclick="openTravellerModal('new')"><i class="fa-solid fa-plus"></i> Add New Traveller</button>
                
                <div class="mt-4">
                    @forelse(auth()->user()->savedTravellers as $traveller)
                    <div class="traveller-card">
                        <div class="traveller-info">
                            <h4>{{ trim($traveller->first_name . ' ' . $traveller->last_name) }} <span style="font-size:11px; background:#222; padding:2px 8px; border-radius:12px; text-transform:uppercase; margin-left:8px; font-family:'Outfit', sans-serif;">{{ $traveller->relationship ?? 'Co-Traveller' }}</span></h4>
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
                            <input type="text" name="preferences[flight]" placeholder="e.g. Window seat, Extra legroom" value="{{ old('preferences.flight', $prefs['flight'] ?? '') }}">
                        </div>
                        <div class="form-group">
                            <label>Meal Preference</label>
                            <select name="preferences[meal]">
                                <option value="">Select Meal Preference</option>
                                <option value="Vegetarian" {{ (old('preferences.meal', $prefs['meal'] ?? '') == 'Vegetarian') ? 'selected' : '' }}>Vegetarian</option>
                                <option value="Non-Vegetarian" {{ (old('preferences.meal', $prefs['meal'] ?? '') == 'Non-Vegetarian') ? 'selected' : '' }}>Non-Vegetarian</option>
                                <option value="Vegan" {{ (old('preferences.meal', $prefs['meal'] ?? '') == 'Vegan') ? 'selected' : '' }}>Vegan</option>
                                <option value="Jain Meal" {{ (old('preferences.meal', $prefs['meal'] ?? '') == 'Jain Meal') ? 'selected' : '' }}>Jain Meal</option>
                                <option value="Halal" {{ (old('preferences.meal', $prefs['meal'] ?? '') == 'Halal') ? 'selected' : '' }}>Halal</option>
                                <option value="Kosher" {{ (old('preferences.meal', $prefs['meal'] ?? '') == 'Kosher') ? 'selected' : '' }}>Kosher</option>
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary" style="margin-top: 32px;">Save Preferences</button>
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

        // Anniversary field toggle based on marital status
        const maritalSelect = document.getElementById('marital-status-select');
        const anniversaryField = document.getElementById('anniversary-field');
        if (maritalSelect && anniversaryField) {
            maritalSelect.addEventListener('change', function() {
                if (this.value === 'Married') {
                    anniversaryField.style.opacity = '1';
                    anniversaryField.style.pointerEvents = 'auto';
                } else {
                    anniversaryField.style.opacity = '0.4';
                    anniversaryField.style.pointerEvents = 'none';
                    const annInput = anniversaryField.querySelector('input');
                    if (annInput) annInput.value = '';
                }
            });
        }
    });

    let govtIdIndex = {{ count($govtIds ?? []) ?: 1 }};
    
    function addGovtIdRow() {
        const container = document.getElementById('govt-ids-container');
        const row = document.createElement('div');
        row.className = 'govt-id-row';
        row.style.cssText = 'display:flex; gap:15px; align-items:flex-end; margin-bottom: 15px;';
        row.innerHTML = `
            <div style="flex:1;">
                <select name="govt_ids[${govtIdIndex}][type]" class="pf-input pf-select govt-id-type" onchange="updateGovtIdValidation(this)">
                    <option value="">Select ID Type</option>
                    <option value="Aadhaar Card">Aadhaar Card</option>
                    <option value="PAN Card">PAN Card</option>
                    <option value="Driving License">Driving License</option>
                </select>
            </div>
            <div style="flex:2;">
                <input type="text" name="govt_ids[${govtIdIndex}][number]" class="pf-input govt-id-number" placeholder="ID Number" oninput="this.value = this.value.toUpperCase()">
            </div>
            <button type="button" class="btn btn-sm btn-outline-danger btn-remove-id" style="padding: 6px 12px; border-radius: 4px; background: transparent; border: 1px solid #dc3545; color: #dc3545;" onclick="removeGovtIdRow(this)">
                <i class="fa-solid fa-trash"></i>
            </button>
        `;
        container.appendChild(row);
        govtIdIndex++;
        updateRemoveButtons();
    }

    function removeGovtIdRow(btn) {
        const row = btn.closest('.govt-id-row');
        row.remove();
        updateRemoveButtons();
    }

    function updateRemoveButtons() {
        const rows = document.querySelectorAll('.govt-id-row');
        const buttons = document.querySelectorAll('.btn-remove-id');
        if (rows.length <= 1) {
            buttons.forEach(btn => btn.style.display = 'none');
        } else {
            buttons.forEach(btn => btn.style.display = 'block');
        }
    }

    function updateGovtIdValidation(selectEl) {
        const row = selectEl.closest('.govt-id-row');
        const inputEl = row.querySelector('.govt-id-number');
        const type = selectEl.value;
        
        inputEl.value = ''; // clear value on change
        
        if (type === 'Aadhaar Card') {
            inputEl.setAttribute('maxlength', '12');
            inputEl.setAttribute('placeholder', '12 Digit Aadhaar Number');
            inputEl.oninput = function() {
                this.value = this.value.replace(/[^0-9]/g, '');
            };
        } else if (type === 'PAN Card') {
            inputEl.setAttribute('maxlength', '10');
            inputEl.setAttribute('placeholder', '10 Character PAN');
            inputEl.oninput = function() {
                this.value = this.value.replace(/[^A-Za-z0-9]/g, '').toUpperCase();
            };
        } else if (type === 'Driving License') {
            inputEl.setAttribute('maxlength', '16');
            inputEl.setAttribute('placeholder', 'e.g. MH04 2010 0034761');
            inputEl.oninput = function() {
                this.value = this.value.toUpperCase();
            };
        } else {
            inputEl.removeAttribute('maxlength');
            inputEl.setAttribute('placeholder', 'ID Number');
            inputEl.oninput = function() {
                this.value = this.value.toUpperCase();
            };
        }
    }

    // Initialize formatting for existing rows
    document.addEventListener("DOMContentLoaded", function() {
        document.querySelectorAll('.govt-id-type').forEach(select => {
            if (select.value) {
                const row = select.closest('.govt-id-row');
                const inputEl = row.querySelector('.govt-id-number');
                const type = select.value;
                if (type === 'Aadhaar Card') {
                    inputEl.setAttribute('maxlength', '12');
                    inputEl.setAttribute('placeholder', '12 Digit Aadhaar Number');
                    inputEl.oninput = function() { this.value = this.value.replace(/[^0-9]/g, ''); };
                } else if (type === 'PAN Card') {
                    inputEl.setAttribute('maxlength', '10');
                    inputEl.setAttribute('placeholder', '10 Character PAN');
                    inputEl.oninput = function() { this.value = this.value.replace(/[^A-Za-z0-9]/g, '').toUpperCase(); };
                } else if (type === 'Driving License') {
                    inputEl.setAttribute('maxlength', '16');
                    inputEl.setAttribute('placeholder', 'e.g. MH04 2010 0034761');
                    inputEl.oninput = function() { this.value = this.value.toUpperCase(); };
                }
            }
        });
        
        // Initialize existing phone numbers in edit mode
        const existingPhone = document.getElementById('profilePhoneHiddenEdit')?.value;
        if (existingPhone) {
            const parts = existingPhone.split(' ');
            if (parts.length > 1) {
                document.getElementById('profilePhoneCodeEdit').value = parts[0];
                document.getElementById('profilePhoneNumberEdit').value = parts.slice(1).join(' ');
            } else {
                document.getElementById('profilePhoneNumberEdit').value = existingPhone;
            }
        }
    });

    function mergePhone(type) {
        let code = document.getElementById('profilePhoneCode' + type).value;
        let number = document.getElementById('profilePhoneNumber' + type).value;
        
        // Restrict to digits only for formatting
        number = number.replace(/[^0-9]/g, '');
        document.getElementById('profilePhoneNumber' + type).value = number;
        
        if (number) {
            document.getElementById('profilePhoneHidden' + type).value = code + ' ' + number;
        } else {
            document.getElementById('profilePhoneHidden' + type).value = '';
        }
    }

</script>
@endpush
