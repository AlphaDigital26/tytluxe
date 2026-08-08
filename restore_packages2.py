def restore_packages2():
    filepath = 'e:/TYTluxe/tyt/tytluxe/app/Http/Controllers/FrontendController.php'
    with open(filepath, 'r', encoding='utf-8') as f:
        text = f.read()
        
    start_str = '    public function packages()'
    end_str = "        return view('pages.package-details', compact('package'));\n    }"
    
    if start_str in text and end_str in text:
        start_idx = text.index(start_str)
        end_idx = text.index(end_str) + len(end_str)
        
        new_text = '''    public function packages()
    {
         = Package::with(['destination', 'images'])->where('is_active', true)->get();
        return view('pages.packages', compact('packages'));
    }

    public function packageDetails()
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
        
        text = text[:start_idx] + new_text + text[end_idx:]
        with open(filepath, 'w', encoding='utf-8') as f:
            f.write(text)
        print("Done replacing.")
    else:
        print("Could not find markers.")
        
restore_packages2()
