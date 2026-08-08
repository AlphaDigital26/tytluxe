def restore_packages():
    filepath = 'e:/TYTluxe/tyt/tytluxe/app/Http/Controllers/FrontendController.php'
    with open(filepath, 'r', encoding='utf-8') as f:
        text = f.read()
        
    # Replace packages() method
    packages_start = '    public function packages()\n    {\n        // Dummy data for presentation\n         = collect(['
    packages_end = "        return view('pages.packages', compact('packages'));\n    }"
    
    if packages_start in text:
        start_idx = text.index(packages_start)
        # Find the end by searching for packages_end from start_idx
        end_idx = text.index(packages_end, start_idx) + len(packages_end)
        
        new_packages = '''    public function packages()
    {
         = Package::with(['destination', 'images'])->where('is_active', true)->get();
        return view('pages.packages', compact('packages'));
    }'''
        
        text = text[:start_idx] + new_packages + text[end_idx:]
        print("Restored packages()")

    # Replace packageDetails() method
    details_start = '    public function packageDetails()\n    {\n        // ── Jibhi Tirthan Valley 2N3D ──────────────────────────────────────────'
    details_end = "        return view('pages.package-details', compact('package'));\n    }"
    
    if details_start in text:
        start_idx = text.index(details_start)
        end_idx = text.index(details_end, start_idx) + len(details_end)
        
        new_details = '''    public function packageDetails()
    {
        if ( == 7) {
            return view('pages.package-jibhi');
        }

        if ( == 8) {
            return view('pages.package-manali');
        }

         = Package::with(['destination', 'images', 'inclusions', 'itinerary'])->findOrFail();
        
        return view('pages.package-details', compact('package'));
    }'''
        
        text = text[:start_idx] + new_details + text[end_idx:]
        print("Restored packageDetails()")

    with open(filepath, 'w', encoding='utf-8') as f:
        f.write(text)

restore_packages()
