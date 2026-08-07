def fully_restore_and_update_manali(filepath):
    with open(filepath, 'r', encoding='utf-8') as f:
        text = f.read()
        
    # 1. Update font sizes and weights (Manali specific)
    text = text.replace("font-size: 15.5px; line-height: 1.9;\n  color: var(--white-60); font-weight: 300;", "font-size: 17px; line-height: 1.9;\n  color: var(--white-60); font-weight: 400;")
    text = text.replace("font-size: 12.5px; color: var(--white-60); font-weight: 300; line-height: 1.6;", "font-size: 14.5px; color: var(--white-60); font-weight: 400; line-height: 1.6;")
    text = text.replace("font-size: 12.5px; color: var(--white-60);\n  font-weight: 300;", "font-size: 14.5px; color: var(--white-60);\n  font-weight: 400;")
    text = text.replace("font-size: 14.5px; color: var(--white-60); line-height: 1.85; font-weight: 300;", "font-size: 16.5px; color: var(--white-60); line-height: 1.85; font-weight: 400;")
    text = text.replace("font-size: 13.5px; color: var(--white-60); font-weight: 300; line-height: 1.5;", "font-size: 15.5px; color: var(--white-60); font-weight: 400; line-height: 1.5;")
    text = text.replace("font-size: 13.5px; color: var(--white-60); font-weight: 300;\n  padding: 16px", "font-size: 15px; color: var(--white-60); font-weight: 400;\n  padding: 16px")
    text = text.replace("color: #fff; font-weight: 500; font-size: 13.5px;", "color: #fff; font-weight: 500; font-size: 15px;")
    text = text.replace("font-size: 13px; color: var(--white-60); margin-bottom: 12px;", "font-size: 15.5px; font-weight: 400; color: var(--white-60); margin-bottom: 12px;")
    text = text.replace("font-size: 13.5px;\n  color: var(--white-60); line-height: 1.7; font-weight: 300;", "font-size: 15.5px;\n  color: var(--white-60); line-height: 1.7; font-weight: 400;")
    
    # 2. Fix the price font to Jost (since it's an old file)
    text = text.replace("font-family: 'Cormorant Garamond', serif; font-size: 3.2rem; font-weight: 500; color: #fff; line-height: 1; margin-bottom: 4px;", "font-family: 'Jost', sans-serif; font-size: 2.8rem; font-weight: 200; color: #fff; line-height: 1.2; margin-bottom: 4px; white-space: nowrap;")
    text = text.replace("font-size: 1.8rem; color: var(--gold); margin-right: 2px;", "font-size: 1.6rem; color: var(--gold); vertical-align: middle; margin-right: 4px;")
    
    # 3. Replace TYTLuxe
    text = text.replace("PackandExplore", "TYTLuxe")
    text = text.replace("contact@packandexplore.in", "takeyourtrip7@gmail.com")
    text = text.replace("www.packandexplore.in", "www.tytluxe.in")
    text = text.replace("+91 79992 68526", "+91 9875073788")
    text = text.replace("https://wa.me/917999268526", "https://wa.me/919875073788")

    # 4. Remove Bank Details
    bank_text = '''          <div class="mn-note" style="margin-top:24px;">
            <strong>Bank Details:</strong> Name: PACKTURE EXPLORIFY LLP &nbsp;|&nbsp; A/C: 259074445353 &nbsp;|&nbsp; Bank: IndusInd Bank &nbsp;|&nbsp; IFSC: INDB0002082
          </div>'''
    text = text.replace(bank_text, "")

    # 5. Remove sticky from sidebar
    text = text.replace("position: sticky; top: 80px; ", "")
    
    # 6. Restructure layout (Sidebar up, Itinerary & Rest down)
    sidebar_start = "{{-- ===== SIDEBAR ===== --}}"
    sidebar_end = "{{-- /sidebar --}}"
    
    if sidebar_start in text and sidebar_end in text:
        s_idx = text.index(sidebar_start)
        e_idx = text.index(sidebar_end) + len(sidebar_end)
        sidebar_block = text[s_idx:e_idx]
        # remove it
        text = text[:s_idx] + text[e_idx:]
        
        # We need to insert it BEFORE itinerary. No wait! 
        # We need to close mn-left-col BEFORE itinerary, insert sidebar, then close mn-layout.
        # THEN open full width col and insert itinerary.
        
        itinerary_start = '<div class="mn-section" id="itinerary">'
        if itinerary_start in text:
            i_idx = text.index(itinerary_start)
            
            new_structure = f'''      </div> <!-- /mn-left-col -->
      {sidebar_block}
    </div> <!-- /mn-layout -->
    
    <div class="mn-full-width-col" style="max-width: 900px; margin: 0 auto;">
        {itinerary_start}'''
            
            text = text.replace(itinerary_start, new_structure)
            
            # Remove trailing divs
            closing_tags = '''      </div>
    </div>
  </div>
</section>'''
            text = text.replace(closing_tags, '''    </div>
  </div>
</section>''')
            
            with open(filepath, 'w', encoding='utf-8') as f:
                f.write(text)
            print("Successfully updated everything for Manali!")
        else:
            print("Itinerary start not found")
    else:
        print("Sidebar not found")

fully_restore_and_update_manali('e:/TYTluxe/tyt/tytluxe/resources/views/pages/package-manali.blade.php')
