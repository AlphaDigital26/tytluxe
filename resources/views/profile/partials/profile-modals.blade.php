<!-- Traveller Modal Overlay -->
<div class="auth-modal-overlay" id="travellerModalOverlay">
    <div class="auth-modal-box" style="max-width: 600px;">
        <button class="auth-modal-close" onclick="closeTravellerModal()">
            <i class="fa-solid fa-xmark"></i>
        </button>

        <div class="auth-modal-content">
            <h2 class="auth-modal-title" id="travellerModalTitle">Add Traveller</h2>
            <p class="auth-modal-subtitle">Enter traveller details to save for quick bookings.</p>

            <form id="travellerForm" method="POST" action="{{ route('profile.traveller.store') }}">
                @csrf
                <input type="hidden" name="_method" id="travellerMethod" value="POST">
                
                <div class="form-grid">
                    <div class="form-group full-width">
                        <label>Relation / Type</label>
                        <select name="type" id="travellerType" required>
                            <option value="self">Self</option>
                            <option value="spouse">Spouse</option>
                            <option value="child">Child</option>
                            <option value="parent">Parent</option>
                            <option value="other">Other</option>
                        </select>
                    </div>

                    <div class="form-group full-width">
                        <label>Full Name</label>
                        <input type="text" name="name" id="travellerName" required>
                    </div>

                    <div class="form-group">
                        <label>Date of Birth</label>
                        <input type="date" name="dob" id="travellerDob">
                    </div>

                    <div class="form-group">
                        <label>Gender</label>
                        <select name="gender" id="travellerGender">
                            <option value="">Select Gender</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Nationality</label>
                        <input type="text" name="nationality" id="travellerNationality">
                    </div>

                    <div class="form-group">
                        <label>Passport Number</label>
                        <input type="text" name="passport_number" id="travellerPassportNumber">
                    </div>

                    <div class="form-group">
                        <label>Passport Expiry</label>
                        <input type="date" name="passport_expiry" id="travellerPassportExpiry">
                    </div>

                    <div class="form-group">
                        <label>Issuing Country</label>
                        <input type="text" name="passport_issuing_country" id="travellerPassportCountry">
                    </div>
                </div>

                <button type="submit" class="auth-submit-btn mt-4">Save Traveller</button>
            </form>
        </div>
    </div>
</div>

<script>
    function openTravellerModal() {
        document.getElementById('travellerModalOverlay').classList.add('show');
        document.body.style.overflow = 'hidden';
        
        // Reset form for "New"
        document.getElementById('travellerForm').reset();
        document.getElementById('travellerForm').action = "{{ route('profile.traveller.store') }}";
        document.getElementById('travellerMethod').value = 'POST';
        document.getElementById('travellerModalTitle').innerText = 'Add Traveller';
    }

    function editTraveller(id, data) {
        document.getElementById('travellerModalOverlay').classList.add('show');
        document.body.style.overflow = 'hidden';
        
        document.getElementById('travellerForm').action = `/profile/traveller/${id}`;
        document.getElementById('travellerMethod').value = 'PATCH';
        document.getElementById('travellerModalTitle').innerText = 'Edit Traveller';

        document.getElementById('travellerType').value = data.type || 'self';
        document.getElementById('travellerName').value = data.name || '';
        document.getElementById('travellerDob').value = data.dob ? data.dob.split('T')[0] : '';
        document.getElementById('travellerGender').value = data.gender || '';
        document.getElementById('travellerNationality').value = data.nationality || '';
        document.getElementById('travellerPassportNumber').value = data.passport_number || '';
        document.getElementById('travellerPassportExpiry').value = data.passport_expiry ? data.passport_expiry.split('T')[0] : '';
        document.getElementById('travellerPassportCountry').value = data.passport_issuing_country || '';
    }

    function closeTravellerModal() {
        document.getElementById('travellerModalOverlay').classList.remove('show');
        document.body.style.overflow = '';
    }

    document.getElementById('travellerModalOverlay').addEventListener('mousedown', function(e) {
        if(e.target === this) {
            closeTravellerModal();
        }
    });
</script>
