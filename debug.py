def debug_manali(filepath):
    with open(filepath, 'r', encoding='utf-8') as f:
        text = f.read()
    
    print("Checking itinerary start marker...")
    if '<div class="mn-section" id="itinerary">' in text:
        print("Found it!")
    else:
        print("Not found.")
        
    print("Checking left col close marker...")
    if '</div> <!-- close mn-left-col -->' in text:
        print("Found it!")
    else:
        print("Not found.")

debug_manali('e:/TYTluxe/tyt/tytluxe/resources/views/pages/package-manali.blade.php')
