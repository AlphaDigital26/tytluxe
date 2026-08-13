<style>
    /* Dark Theme Modal Styles */
    .co-traveller-modal-overlay {
        position: fixed;
        top: 0; left: 0; right: 0; bottom: 0;
        background: rgba(0, 0, 0, 0.8);
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        visibility: hidden;
        transition: opacity 0.3s ease;
        z-index: 9999;
    }
    .co-traveller-modal-overlay.show {
        opacity: 1;
        visibility: visible;
    }
    .co-traveller-modal-box {
        background: #1c1c1c;
        width: 100%;
        max-width: 800px;
        border-radius: 12px;
        max-height: 90vh;
        overflow-y: auto;
        box-shadow: 0 10px 40px rgba(0,0,0,0.5);
        font-family: 'Outfit', sans-serif;
        border: 1px solid #333;
        /* Hide scrollbar for Firefox, IE, Edge */
        -ms-overflow-style: none; 
        scrollbar-width: none;
    }
    .co-traveller-modal-box::-webkit-scrollbar {
        display: none; /* Hide scrollbar for Chrome, Safari, Opera */
    }
    
    /* Attempt to hide native select dropdown scrollbars (Limited browser support) */
    select.ct-input {
        -ms-overflow-style: none; 
        scrollbar-width: none;
    }
    select.ct-input::-webkit-scrollbar {
        display: none;
    }
    .co-traveller-modal-header {
        position: sticky;
        top: 0;
        background: #1c1c1c;
        z-index: 10;
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 20px 24px;
        border-bottom: 1px solid #333;
        border-radius: 12px 12px 0 0;
    }
    .co-traveller-modal-header h2 {
        margin: 0;
        font-size: 20px;
        font-weight: 600;
        color: #fff;
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .co-traveller-modal-header h2 i {
        color: var(--primary, #d4af37);
        cursor: pointer;
        transition: opacity 0.2s;
    }
    .co-traveller-modal-header h2 i:hover {
        opacity: 0.8;
    }
    .co-traveller-header-actions {
        display: flex;
        gap: 12px;
    }
    .co-traveller-btn-cancel {
        background: transparent;
        color: #fff;
        border: 1px solid #444;
        padding: 8px 20px;
        border-radius: 4px;
        font-weight: 500;
        cursor: pointer;
        font-size: 13px;
        text-transform: uppercase;
        transition: background 0.2s;
    }
    .co-traveller-btn-cancel:hover {
        background: #333;
    }
    .co-traveller-btn-save {
        background: var(--primary, #d4af37);
        color: #000;
        border: none;
        padding: 8px 24px;
        border-radius: 4px;
        font-weight: 600;
        cursor: pointer;
        font-size: 13px;
        text-transform: uppercase;
        transition: opacity 0.2s;
    }
    .co-traveller-btn-save:hover {
        opacity: 0.9;
    }
    .co-traveller-modal-body {
        padding: 24px;
    }
    .co-traveller-note {
        background: rgba(212, 175, 55, 0.1);
        border: 1px dashed var(--primary, #d4af37);
        padding: 16px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        gap: 15px;
        margin-bottom: 24px;
    }
    .co-traveller-note i {
        font-size: 20px;
        color: var(--primary, #d4af37);
    }
    .co-traveller-note p {
        margin: 0;
        color: #fff;
        font-size: 14px;
        font-weight: 400;
        line-height: 1.4;
    }
    .co-traveller-section-title {
        font-size: 16px;
        font-weight: 600;
        color: #fff;
        margin-bottom: 16px;
        margin-top: 24px;
        padding-bottom: 8px;
        border-bottom: 1px dashed #333;
    }
    .co-traveller-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
        margin-bottom: 16px;
    }
    .co-traveller-grid-3 {
        grid-template-columns: 1fr 1fr 1fr;
    }
    .ct-input-group {
        display: flex;
        flex-direction: column;
    }
    
    /* Using existing profile form classes for consistency where possible, 
       but overriding custom bits for the modal */
    .ct-input-group label {
        font-size: 12px;
        color: var(--text-muted, #999);
        margin-bottom: 8px;
        text-transform: uppercase;
        font-weight: 500;
        letter-spacing: 0.5px;
    }
    .ct-input {
        border: 1px solid #333;
        border-radius: 4px;
        padding: 12px 16px;
        font-size: 14px;
        color: #fff;
        outline: none;
        transition: all 0.2s;
        font-family: inherit;
        background: #111;
    }
    .ct-input:focus {
        border-color: var(--primary, #d4af37);
        box-shadow: 0 0 0 1px var(--primary, #d4af37);
    }
    select.ct-input {
        appearance: none;
        background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23999' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right 12px center;
        background-size: 14px;
        padding-right: 32px;
    }
    select.ct-input option {
        background: #1c1c1c;
        color: #fff;
    }
    
    /* Relationship Pills */
    .relationship-container {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-bottom: 8px;
    }
    .relationship-container input[type="radio"] {
        display: none;
    }
    .relationship-container label {
        padding: 6px 14px;
        border: 1px solid #333;
        border-radius: 20px;
        font-size: 13px;
        color: #aaa;
        cursor: pointer;
        transition: all 0.2s;
        background: #111;
        text-transform: none;
        font-weight: 400;
    }
    .relationship-container input[type="radio"]:checked + label {
        background: rgba(212, 175, 55, 0.1);
        border-color: var(--primary, #d4af37);
        color: var(--primary, #d4af37);
        font-weight: 500;
    }
    .ct-helper-text {
        font-size: 12px;
        color: #777;
        margin-bottom: 16px;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .phone-input-wrapper {
        display: flex;
    }
    .phone-input-wrapper select {
        width: 130px;
        border-right: none;
        border-radius: 4px 0 0 4px;
    }
    .phone-input-wrapper input {
        flex: 1;
        border-radius: 0 4px 4px 0;
    }
</style>

<!-- Traveller Modal Overlay -->
<div class="co-traveller-modal-overlay" id="travellerModalOverlay">
    <div class="co-traveller-modal-box">
        <form id="travellerForm" method="POST" action="{{ route('profile.traveller.store') }}">
            @csrf
            <input type="hidden" name="_method" id="travellerMethod" value="POST">
            
            <!-- Header -->
            <div class="co-traveller-modal-header">
                <h2><i class="fa-solid fa-arrow-left" onclick="closeTravellerModal()"></i> <span id="travellerModalTitle">Add New Co-Traveller</span></h2>
                <div class="co-traveller-header-actions">
                    <button type="button" class="co-traveller-btn-cancel" onclick="closeTravellerModal()">Cancel</button>
                    <button type="submit" class="co-traveller-btn-save">Save</button>
                </div>
            </div>

            <!-- Body -->
            <div class="co-traveller-modal-body">
                
                <div class="co-traveller-note">
                    <i class="fa-regular fa-address-card"></i>
                    <p>Please double check if your First and Last name, Gender & Date of Birth match your Govt. ID such as Aadhaar or Passport</p>
                </div>

                <div class="co-traveller-section-title" style="margin-top: 0;">General Information</div>
                
                <div class="co-traveller-grid">
                    <div class="ct-input-group">
                        <label>First & Middle Name</label>
                        <input type="text" name="first_name" id="travellerFirstName" class="ct-input" required>
                    </div>
                    <div class="ct-input-group">
                        <label>Last Name</label>
                        <input type="text" name="last_name" id="travellerLastName" class="ct-input" required>
                    </div>
                </div>

                <div class="co-traveller-grid co-traveller-grid-3">
                    <div class="ct-input-group">
                        <label>Gender</label>
                        <select name="gender" id="travellerGender" class="ct-input">
                            <option value="">Select</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                        </select>
                    </div>
                    <div class="ct-input-group">
                        <label>Date of Birth</label>
                        <input type="date" name="dob" id="travellerDob" class="ct-input">
                    </div>
                    <div class="ct-input-group">
                        <label>Nationality</label>
                        <select name="nationality" id="travellerNationality" class="ct-input">
                            <option value="">Select</option>
                            @php
                                $countries = ['Indian','American','British','Australian','Canadian','French','German','Japanese','Chinese','Singaporean','Emirati','South African','Brazilian','Mexican','Italian','Spanish','Russian','Korean','Dutch','Swedish','Swiss','Norwegian','Danish','Finnish','Belgian','Austrian','Portuguese','Greek','Polish','Czech','Hungarian','Romanian','Turkish','Egyptian','Kenyan','Nigerian','Ghanaian','Bangladeshi','Pakistani','Sri Lankan','Nepali','Bhutanese','Maldivian','Thai','Malaysian','Indonesian','Filipino','Vietnamese','Burmese','Cambodian','Laotian'];
                            @endphp
                            @foreach($countries as $country)
                                <option value="{{ $country }}">{{ $country }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="ct-input-group">
                    <label>Relationship with Traveller</label>
                    <div class="relationship-container" id="relationshipContainer">
                        @php $relations = ['Spouse', 'Child', 'Sibling', 'GrandParent', 'Friend', 'Parent', 'Colleague', 'Relative', 'Parent In law', 'Other']; @endphp
                        @foreach($relations as $index => $rel)
                            <input type="radio" name="relationship" id="rel_{{ $index }}" value="{{ $rel }}">
                            <label for="rel_{{ $index }}">{{ $rel }}</label>
                        @endforeach
                    </div>
                    <div class="ct-helper-text">This helps to give us personalised travel recommendations when travelling</div>
                </div>

                <div class="co-traveller-grid">
                    <div class="ct-input-group">
                        <label>Meal Preference</label>
                        <select name="meal_preference" id="travellerMeal" class="ct-input">
                            <option value="">Select</option>
                            <option value="Vegetarian">Vegetarian</option>
                            <option value="Non-Vegetarian">Non-Vegetarian</option>
                            <option value="Vegan">Vegan</option>
                            <option value="Jain Meal">Jain Meal</option>
                            <option value="Halal">Halal</option>
                            <option value="Kosher">Kosher</option>
                        </select>
                    </div>
                    <div class="ct-input-group">
                        <label>Train Berth Preference</label>
                        <select name="train_berth_preference" id="travellerBerth" class="ct-input">
                            <option value="">Select</option>
                            <option value="Lower">Lower</option>
                            <option value="Middle">Middle</option>
                            <option value="Upper">Upper</option>
                            <option value="Side Lower">Side Lower</option>
                            <option value="Side Upper">Side Upper</option>
                        </select>
                    </div>
                </div>

                <div class="co-traveller-section-title">Passport Details</div>
                
                <div class="co-traveller-grid">
                    <div class="ct-input-group">
                        <label>Passport No.</label>
                        <input type="text" name="passport_number" id="travellerPassportNumber" class="ct-input">
                    </div>
                    <div class="ct-input-group">
                        <label>Expiry Date</label>
                        <input type="date" name="passport_expiry" id="travellerPassportExpiry" class="ct-input">
                    </div>
                </div>
                
                <div class="co-traveller-grid">
                    <div class="ct-input-group">
                        <label>Issuing Country</label>
                        <select name="passport_issuing_country" id="travellerPassportCountry" class="ct-input">
                            <option value="">Select</option>
                            @foreach($countries as $country)
                                <option value="{{ $country }}">{{ $country }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="co-traveller-section-title">Add contact information to receive booking details & other alerts</div>

                <div class="co-traveller-grid">
                    <div class="ct-input-group">
                        <label>Mobile Number</label>
                        <div class="phone-input-wrapper">
                            @php
                                $countryCodes = [
                                    '+91' => 'India (+91)',
                                    '+1' => 'USA/Canada (+1)',
                                    '+44' => 'UK (+44)',
                                    '+61' => 'Australia (+61)',
                                    '+971' => 'UAE (+971)',
                                    '+65' => 'Singapore (+65)',
                                ];
                            @endphp
                            <!-- Assuming phone string might contain the code, or for simplicity we just prepend it on submit if needed. For now, treating phone as single string in DB, but UI separates it. Let's merge them via JS on submit, or simply rely on standard input -->
                            <select id="countryCodeSelect" class="ct-input">
                                @foreach($countryCodes as $code => $label)
                                    <option value="{{ $code }}">{{ $code }}</option>
                                @endforeach
                            </select>
                            <input type="text" id="mobileNumberInput" class="ct-input" placeholder="Mobile Number">
                            <input type="hidden" name="phone" id="travellerPhone">
                        </div>
                    </div>
                    <div class="ct-input-group">
                        <label>Email ID</label>
                        <input type="email" name="email" id="travellerEmail" class="ct-input" placeholder="Email ID">
                    </div>
                </div>


            </div>
        </form>
    </div>
</div>

<script>
    document.getElementById('travellerForm').addEventListener('submit', function(e) {
        // Merge country code and mobile number
        const code = document.getElementById('countryCodeSelect').value;
        const number = document.getElementById('mobileNumberInput').value;
        if(number) {
            document.getElementById('travellerPhone').value = code + ' ' + number;
        } else {
            document.getElementById('travellerPhone').value = '';
        }
    });

    function openTravellerModal() {
        document.getElementById('travellerModalOverlay').classList.add('show');
        document.body.style.overflow = 'hidden';
        
        // Reset form for "New"
        document.getElementById('travellerForm').reset();
        document.getElementById('travellerForm').action = "{{ route('profile.traveller.store') }}";
        document.getElementById('travellerMethod').value = 'POST';
        document.getElementById('travellerModalTitle').innerText = 'Add New Co-Traveller';
        
        // Uncheck radios
        document.querySelectorAll('input[name="relationship"]').forEach(r => r.checked = false);
    }

    function editTraveller(id, data) {
        document.getElementById('travellerModalOverlay').classList.add('show');
        document.body.style.overflow = 'hidden';
        
        document.getElementById('travellerForm').action = `/profile/traveller/${id}`;
        document.getElementById('travellerMethod').value = 'PATCH';
        document.getElementById('travellerModalTitle').innerText = 'Edit Co-Traveller';

        document.getElementById('travellerFirstName').value = data.first_name || '';
        document.getElementById('travellerLastName').value = data.last_name || '';
        document.getElementById('travellerDob').value = data.dob ? data.dob.split('T')[0] : '';
        document.getElementById('travellerGender').value = data.gender || '';
        document.getElementById('travellerNationality').value = data.nationality || '';
        
        // Set relationship radio
        if (data.relationship) {
            const radio = document.querySelector(`input[name="relationship"][value="${data.relationship}"]`);
            if (radio) radio.checked = true;
        } else {
            document.querySelectorAll('input[name="relationship"]').forEach(r => r.checked = false);
        }

        document.getElementById('travellerMeal').value = data.meal_preference || '';
        document.getElementById('travellerBerth').value = data.train_berth_preference || '';
        
        document.getElementById('travellerPassportNumber').value = data.passport_number || '';
        document.getElementById('travellerPassportExpiry').value = data.passport_expiry ? data.passport_expiry.split('T')[0] : '';
        document.getElementById('travellerPassportCountry').value = data.passport_issuing_country || '';

        // Handle phone splitting
        if (data.phone) {
            const parts = data.phone.split(' ');
            if (parts.length > 1) {
                document.getElementById('countryCodeSelect').value = parts[0];
                document.getElementById('mobileNumberInput').value = parts.slice(1).join(' ');
            } else {
                document.getElementById('mobileNumberInput').value = data.phone;
            }
        } else {
            document.getElementById('countryCodeSelect').value = '+91';
            document.getElementById('mobileNumberInput').value = '';
        }

        document.getElementById('travellerEmail').value = data.email || '';
    }

    function closeTravellerModal() {
        document.getElementById('travellerModalOverlay').classList.remove('show');
        document.body.style.overflow = '';
    }

    // Close when clicking outside box
    document.getElementById('travellerModalOverlay').addEventListener('mousedown', function(e) {
        if(e.target === this) {
            closeTravellerModal();
        }
    });
</script>
