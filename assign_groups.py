import os
import re

base_path = r"e:\TYTluxe\tyt\tytluxe\app\Filament\Resources"

groups = {
    "Catalog": ["Destinations/DestinationResource.php", "Amenities/AmenityResource.php", "Hotels/HotelResource.php", "Cruises/CruiseResource.php", "Staycations/StaycationResource.php", "Packages/PackageResource.php"],
    "Operations": ["Enquiries/EnquiryResource.php", "Bookings/BookingResource.php", "Offers/OfferResource.php", "Reviews/ReviewResource.php"],
    "System": ["Users/UserResource.php", "Settings/SettingResource.php"]
}

for group, files in groups.items():
    for f_rel in files:
        file_path = os.path.join(base_path, f_rel.replace('/', '\\'))
        if os.path.exists(file_path):
            with open(file_path, 'r', encoding='utf-8') as f:
                content = f.read()
            
            if "$navigationGroup" not in content:
                content = re.sub(
                    r'(protected static \?string \$navigationIcon = \'[^\']+\';)',
                    f"\\1\n\n    protected static ?string $navigationGroup = '{group}';",
                    content
                )
                with open(file_path, 'w', encoding='utf-8') as f:
                    f.write(content)
                print(f"Updated {f_rel} with group {group}")
        else:
            print(f"Not found: {file_path}")
