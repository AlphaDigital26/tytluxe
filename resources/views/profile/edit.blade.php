@extends('layouts.frontend')

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
    <div class="profile-container">
        <h2 class="profile-section-title">Account Settings</h2>
        
        <div class="profile-grid">
            
            <!-- Contact Information Card -->
            <div class="profile-card">
                <div class="profile-card-header">
                    <i class="fa-regular fa-address-card"></i> CONTACT INFORMATION
                </div>
                <div class="profile-card-body">
                    <div class="profile-field">
                        <label>FULL NAME</label>
                        <div class="profile-value">{{ auth()->user()->name }}</div>
                    </div>
                    <div class="profile-field">
                        <label>EMAIL ADDRESS</label>
                        <div class="profile-value">{{ auth()->user()->email }}</div>
                    </div>
                    <div class="profile-field">
                        <label>PHONE NUMBER</label>
                        <div class="profile-value">{{ auth()->user()->phone ?? '+1 234 567 890' }}</div>
                    </div>
                </div>
                <div class="profile-card-footer">
                    <button class="profile-action-btn" onclick="openProfileModal('details')">
                        <i class="fa-solid fa-pen"></i> EDIT DETAILS
                    </button>
                </div>
            </div>

            <!-- Security Card -->
            <div class="profile-card">
                <div class="profile-card-header">
                    <i class="fa-solid fa-shield-halved"></i> SECURITY
                </div>
                <div class="profile-card-body">
                    <div class="profile-field profile-field-row">
                        <div class="profile-field-col">
                            <label>PASSWORD</label>
                            <div class="profile-value">••••••••••••••</div>
                        </div>
                        <div class="profile-field-col">
                            <button class="profile-update-btn" onclick="openProfileModal('password')">UPDATE</button>
                        </div>
                    </div>
                    
                    <div class="profile-divider"></div>
                    
                    <div class="profile-field profile-field-row">
                        <div class="profile-field-col">
                            <div class="profile-value-bold">Two-Factor Authentication</div>
                            <div class="profile-value-sub">Enhanced security for your account</div>
                        </div>
                        <div class="profile-field-col">
                            <label class="profile-switch">
                                <input type="checkbox">
                                <span class="profile-slider round"></span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- Include Modals -->
@include('profile.partials.profile-modals')

@endsection
