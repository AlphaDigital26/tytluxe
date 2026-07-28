import os
import re

models_dir = r"e:\TYTluxe\tyt\tytluxe\app\Models"

relations_map = {
    "Destination": """
    public function hotels() { return $this->hasMany(Hotel::class); }
    public function cruises() { return $this->hasMany(Cruise::class); }
    public function staycations() { return $this->hasMany(Staycation::class); }
    public function packages() { return $this->hasMany(Package::class); }
""",
    "Amenity": """
    public function hotels() { return $this->belongsToMany(Hotel::class, 'hotel_amenity'); }
    public function staycations() { return $this->belongsToMany(Staycation::class, 'staycation_amenity'); }
""",
    "Hotel": """
    public function destination() { return $this->belongsTo(Destination::class); }
    public function roomTypes() { return $this->hasMany(HotelRoomType::class); }
    public function images() { return $this->hasMany(HotelImage::class); }
    public function amenities() { return $this->belongsToMany(Amenity::class, 'hotel_amenity'); }
""",
    "HotelRoomType": """
    public function hotel() { return $this->belongsTo(Hotel::class); }
""",
    "HotelImage": """
    public function hotel() { return $this->belongsTo(Hotel::class); }
""",
    "Cruise": """
    public function destination() { return $this->belongsTo(Destination::class); }
    public function itineraryDays() { return $this->hasMany(CruiseItineraryDay::class); }
    public function cabinTypes() { return $this->hasMany(CruiseCabinType::class); }
    public function images() { return $this->hasMany(CruiseImage::class); }
""",
    "CruiseItineraryDay": """
    public function cruise() { return $this->belongsTo(Cruise::class); }
""",
    "CruiseCabinType": """
    public function cruise() { return $this->belongsTo(Cruise::class); }
""",
    "CruiseImage": """
    public function cruise() { return $this->belongsTo(Cruise::class); }
""",
    "Staycation": """
    public function destination() { return $this->belongsTo(Destination::class); }
    public function images() { return $this->hasMany(StaycationImage::class); }
    public function amenities() { return $this->belongsToMany(Amenity::class, 'staycation_amenity'); }
""",
    "StaycationImage": """
    public function staycation() { return $this->belongsTo(Staycation::class); }
""",
    "Package": """
    public function destination() { return $this->belongsTo(Destination::class); }
    public function inclusions() { return $this->hasMany(PackageInclusion::class); }
    public function images() { return $this->hasMany(PackageImage::class); }
""",
    "PackageInclusion": """
    public function packageModel() { return $this->belongsTo(Package::class, 'package_id'); }
""",
    "PackageImage": """
    public function packageModel() { return $this->belongsTo(Package::class, 'package_id'); }
""",
    "Enquiry": """
    public function user() { return $this->belongsTo(User::class); }
    public function agent() { return $this->belongsTo(User::class, 'agent_id'); }
    public function offers() { return $this->hasMany(Offer::class); }
""",
    "Offer": """
    public function enquiry() { return $this->belongsTo(Enquiry::class); }
    public function agent() { return $this->belongsTo(User::class, 'agent_id'); }
""",
    "Booking": """
    public function user() { return $this->belongsTo(User::class); }
    public function agent() { return $this->belongsTo(User::class, 'agent_id'); }
    public function travelers() { return $this->hasMany(BookingTraveler::class); }
    public function payments() { return $this->hasMany(Payment::class); }
""",
    "BookingTraveler": """
    public function booking() { return $this->belongsTo(Booking::class); }
""",
    "Payment": """
    public function booking() { return $this->belongsTo(Booking::class); }
""",
    "Review": """
    public function user() { return $this->belongsTo(User::class); }
""",
    "User": """
    public function enquiries() { return $this->hasMany(Enquiry::class); }
    public function assignedEnquiries() { return $this->hasMany(Enquiry::class, 'agent_id'); }
    public function offers() { return $this->hasMany(Offer::class, 'agent_id'); }
    public function bookings() { return $this->hasMany(Booking::class); }
    public function assignedBookings() { return $this->hasMany(Booking::class, 'agent_id'); }
    public function reviews() { return $this->hasMany(Review::class); }
"""
}

for model, relations in relations_map.items():
    file_path = os.path.join(models_dir, f"{model}.php")
    if os.path.exists(file_path):
        with open(file_path, 'r', encoding='utf-8') as f:
            content = f.read()
        
        # Don't inject if already injected
        if "public function" not in content:
            # Inject just before the last closing brace
            last_brace_index = content.rfind('}')
            if last_brace_index != -1:
                content = content[:last_brace_index] + relations + content[last_brace_index:]
                with open(file_path, 'w', encoding='utf-8') as f:
                    f.write(content)
                print(f"Updated {model}")
            else:
                print(f"Failed to find closing brace in {model}")
        else:
            print(f"{model} already has functions")
    else:
        print(f"File not found: {model}.php")
