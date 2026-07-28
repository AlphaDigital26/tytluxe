import os
import re

base_path = r"e:\TYTluxe\tyt\tytluxe\app\Filament\Resources"

updates = {
    "Hotels/HotelResource.php": [
        "RelationManagers\\RoomTypesRelationManager::class",
        "RelationManagers\\ImagesRelationManager::class"
    ],
    "Cruises/CruiseResource.php": [
        "RelationManagers\\ItineraryDaysRelationManager::class",
        "RelationManagers\\CabinTypesRelationManager::class",
        "RelationManagers\\ImagesRelationManager::class"
    ],
    "Staycations/StaycationResource.php": [
        "RelationManagers\\ImagesRelationManager::class"
    ],
    "Packages/PackageResource.php": [
        "RelationManagers\\InclusionsRelationManager::class",
        "RelationManagers\\ImagesRelationManager::class"
    ],
    "Bookings/BookingResource.php": [
        "RelationManagers\\TravelersRelationManager::class",
        "RelationManagers\\PaymentsRelationManager::class"
    ]
}

for file_rel, rel_classes in updates.items():
    file_path = os.path.join(base_path, file_rel.replace('/', '\\'))
    if os.path.exists(file_path):
        with open(file_path, 'r', encoding='utf-8') as f:
            content = f.read()
        
        # Build the relations array
        rels_str = ",\n            ".join(rel_classes)
        
        # Escape backslashes for re.sub replacement string
        repl_str = f'public static function getRelations(): array\n    {{\n        return [\n            {rels_str},\n        ];\n    }}'
        repl_str = repl_str.replace('\\', '\\\\')
        
        # Replace return [ ]; with the new relations
        content = re.sub(
            r'public static function getRelations\(\): array\s*\{\s*return\s*\[\s*\];\s*\}',
            repl_str,
            content
        )
        
        with open(file_path, 'w', encoding='utf-8') as f:
            f.write(content)
        print(f"Updated {file_rel}")
    else:
        print(f"File not found: {file_rel}")
