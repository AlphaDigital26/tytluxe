def find_markers(filepath):
    with open(filepath, 'r', encoding='utf-8') as f:
        text = f.read()
    
    print(f"File: {filepath}")
    
    start_marker = '<div class="mn-section" id="itinerary">'
    if start_marker in text:
        print(f"Found itinerary start at index {text.index(start_marker)}")
    else:
        print("Itinerary start not found")
        
    left_col_close = '</div> <!-- close mn-left-col -->'
    if left_col_close in text:
        print(f"Found left col close at index {text.index(left_col_close)}")
    else:
        print("Left col close not found")
        
    sidebar_marker = '{{-- ===== SIDEBAR ===== --}}'
    if sidebar_marker in text:
        print(f"Found sidebar start at index {text.index(sidebar_marker)}")
    else:
        print("Sidebar marker not found")

find_markers('e:/TYTluxe/tyt/tytluxe/resources/views/pages/package-manali.blade.php')
