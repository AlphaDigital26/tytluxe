def restructure_itinerary(filepath, prefix):
    with open(filepath, 'r', encoding='utf-8') as f:
        text = f.read()

    itinerary_start_marker = f'<div class="{prefix}-section" id="itinerary">'
    # I need to find the end of the itinerary section.
    # It ends right before the closing of the left column, which was:
    # </div> <!-- close {prefix}-left-col -->
    left_col_close = f'</div> <!-- close {prefix}-left-col -->'
    
    if itinerary_start_marker in text and left_col_close in text:
        start_idx = text.index(itinerary_start_marker)
        end_idx = text.index(left_col_close)
        
        itinerary_block = text[start_idx:end_idx]
        
        # Remove from current location
        text = text[:start_idx] + text[end_idx:]
        
        # Where to insert? 
        # I want to insert it right inside the full-width column.
        full_width_marker = f'<div class="{prefix}-full-width-col" style="max-width: 900px; margin: 0 auto;">'
        if full_width_marker in text:
            insert_idx = text.index(full_width_marker) + len(full_width_marker)
            text = text[:insert_idx] + '\n\n' + itinerary_block + text[insert_idx:]
            
            with open(filepath, 'w', encoding='utf-8') as f:
                f.write(text)
            print(f"Successfully moved itinerary in {filepath}")
        else:
            print(f"Could not find full-width marker in {filepath}")
    else:
        print(f"Could not find itinerary start or left-col close in {filepath}")

restructure_itinerary('e:/TYTluxe/tyt/tytluxe/resources/views/pages/package-jibhi.blade.php', 'jb')
restructure_itinerary('e:/TYTluxe/tyt/tytluxe/resources/views/pages/package-manali.blade.php', 'mn')
