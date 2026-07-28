import os

blade_path = r"e:\TYTluxe\tyt\tytluxe\resources\views\pages\hotels.blade.php"

with open(blade_path, "r", encoding="utf-8") as f:
    content = f.read()

start_marker = '<div class="htl-grid" id="htlGrid">'
end_marker = '    </div>\n\n</section>' # This is likely where it ends.

start_idx = content.find(start_marker)
if start_idx == -1:
    print("Could not find start marker")
    exit(1)

# Find the ending </div> that matches this grid. 
# It is just before the </section> tag.
end_section_idx = content.find('</section>', start_idx)
if end_section_idx == -1:
    print("Could not find </section> after start marker")
    exit(1)
    
# Find the </div> right before </section>
end_idx = content.rfind('</div>', start_idx, end_section_idx)

if end_idx == -1:
    print("Could not find </div>")
    exit(1)

blade_loop = """<div class="htl-grid" id="htlGrid">
      @foreach($hotels as $hotel)
      <div class="htl-card" data-category="{{ Str::slug($hotel->destination->name) }}"
        data-name="{{ $hotel->title }}"
        data-badge="{{ ucfirst(str_replace('_', ' ', $hotel->category)) }}"
        data-location="{{ $hotel->destination->name }}"
        data-img="placeholder.jpg"
        data-desc="{{ $hotel->description }}"
        data-checkin="2:00 PM" data-checkout="11:00 AM"
        data-features="{{ $hotel->amenities->pluck('name')->implode(',') }}"
        data-rooms="Standard Room; Deluxe Room; Executive Room; Suite. Exact availability and occupancy can be confirmed on enquiry."
        data-wa="I'm interested in {{ $hotel->title }}, {{ $hotel->destination->name }}. Please share availability and rates.">
        <div class="htl-card-img">
          <img src="placeholder.jpg" alt="{{ $hotel->title }}, {{ $hotel->destination->name }}" loading="lazy" />
          <span class="htl-badge">{{ ucfirst(str_replace('_', ' ', $hotel->category)) }}</span>
          <span class="htl-loc-badge">&#128205; {{ $hotel->destination->name }}</span>
        </div>
        <div class="htl-card-body">
          <h3 class="htl-card-name">{{ $hotel->title }}</h3>
          <p class="htl-card-desc">{{ Str::limit($hotel->description, 100) }}</p>
          <div class="htl-card-meta">
            @foreach($hotel->amenities->take(2) as $amenity)
              <span>{{ $amenity->name }}</span>
            @endforeach
          </div>
          <div class="htl-card-footer">
            <span class="htl-card-timing">Check-in 2:00 PM &middot; Check-out 11:00 AM</span>
            <button class="htl-book-btn">View Details <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 8h10M9 4l4 4-4 4"/></svg></button>
          </div>
        </div>
      </div>
      @endforeach
    </div>"""

new_content = content[:start_idx] + blade_loop + content[end_idx+6:]

with open(blade_path, "w", encoding="utf-8") as f:
    f.write(new_content)

print("Replaced hotels grid successfully via string search!")
