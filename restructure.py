def restructure(filepath, prefix):
    with open(filepath, 'r', encoding='utf-8') as f:
        text = f.read()

    # Update contacts
    text = text.replace('+91 79992 68526', '+91 9875073788')
    text = text.replace('contact@tytluxe.in', 'takeyourtrip7@gmail.com')
    text = text.replace('www.tytluxe.com', 'www.tytluxe.in')
    text = text.replace('https://wa.me/917999268526', 'https://wa.me/919875073788')

    # Find the sidebar block
    sidebar_start = "{{-- ===== SIDEBAR ===== --}}"
    sidebar_end = "{{-- /sidebar --}}"
    
    if sidebar_start in text and sidebar_end in text:
        start_idx = text.index(sidebar_start)
        end_idx = text.index(sidebar_end) + len(sidebar_end)
        sidebar_block = text[start_idx:end_idx]
        
        # Remove sidebar from bottom
        text = text[:start_idx] + text[end_idx:]
        
        # Find where to insert it: before INCLUSIONS
        if prefix == 'jb':
            inclusions_marker = "{{-- INCLUSIONS --}}"
        else:
            inclusions_marker = "{{-- INCLUSIONS / EXCLUSIONS --}}"
            
        new_layout = f'''      </div> <!-- close {prefix}-left-col -->
      {sidebar_block}
    </div> <!-- close {prefix}-layout -->
    
    <div class="{prefix}-full-width-col" style="max-width: 900px; margin: 0 auto;">
        {inclusions_marker}'''
        
        text = text.replace(inclusions_marker, new_layout)
        
        # Remove the extra closing tags at the bottom.
        # We need to remove two </div> tags before the <script>
        closing_tags_before_script = f'''    </div>
  </div>
</div>

<script>'''
        closing_tags_after_script = f'''    </div>
</div>

<script>'''
        if closing_tags_before_script in text:
            text = text.replace(closing_tags_before_script, closing_tags_after_script)
        else:
            print(f"Could not find closing tags to replace in {filepath}")

        with open(filepath, 'w', encoding='utf-8') as f:
            f.write(text)
        print(f"Successfully restructured {filepath}")
    else:
        print(f"Sidebar not found in {filepath}")

restructure('e:/TYTluxe/tyt/tytluxe/resources/views/pages/package-jibhi.blade.php', 'jb')
restructure('e:/TYTluxe/tyt/tytluxe/resources/views/pages/package-manali.blade.php', 'mn')
