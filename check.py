def reorganize_file(filepath, prefix):
    with open(filepath, 'r', encoding='utf-8') as f:
        lines = f.readlines()
        
    inclusions_idx = -1
    left_col_close_idx = -1
    sidebar_start_idx = -1
    sidebar_end_idx = -1
    
    for i, line in enumerate(lines):
        if "{{-- INCLUSIONS --}}" in line or "{{-- INCLUSIONS / EXCLUSIONS --}}" in line:
            if inclusions_idx == -1: inclusions_idx = i
        if "{{-- ===== SIDEBAR ===== --}}" in line:
            sidebar_start_idx = i
    
    if sidebar_start_idx != -1:
        # find the end of the sidebar by finding the </section> closing tag
        for i in range(sidebar_start_idx, len(lines)):
            if "</section>" in lines[i]:
                # The sidebar divs and layout/container divs close right before this.
                sidebar_end_idx = i
                break
                
    if inclusions_idx == -1 or sidebar_start_idx == -1 or sidebar_end_idx == -1:
        print(f"Error parsing {filepath}")
        return
        
    print(f"{filepath}: Inclusions at {inclusions_idx}, Sidebar starts at {sidebar_start_idx}, ends around {sidebar_end_idx}")
    
    # 1. Update contact info directly
    for i in range(len(lines)):
        lines[i] = lines[i].replace('+91 79992 68526', '+91 9875073788')
        lines[i] = lines[i].replace('contact@tytluxe.in', 'takeyourtrip7@gmail.com')
        lines[i] = lines[i].replace('www.tytluxe.com', 'www.tytluxe.in') # User said www.tytluxe.in
    
    # Extract sidebar block
    # The sidebar is at the end. It's wrapped in <div><div class="xx-sidebar-wrap">...</div></div>
    # But wait, there are also the closing tags for xx-left-col and xx-layout before the sidebar?
    # Let's just print the lines around sidebar_start_idx to understand what's there.
    for i in range(sidebar_start_idx - 5, sidebar_start_idx + 5):
        print(f"{i}: {lines[i].strip()}")

reorganize_file('e:/TYTluxe/tyt/tytluxe/resources/views/pages/package-jibhi.blade.php', 'jb')
reorganize_file('e:/TYTluxe/tyt/tytluxe/resources/views/pages/package-manali.blade.php', 'mn')
