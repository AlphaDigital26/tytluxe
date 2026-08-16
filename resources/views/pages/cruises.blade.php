@extends('layouts.frontend')

@push('styles')
<style>
@import url('https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400&family=Poppins:wght@300;400;500;600&display=swap');

/* ── ROOT ── */
.tyt-cruises {
  font-family:'Poppins',sans-serif;
  background:#f5f0e8;
  color:#1a1108;
  overflow:hidden;
}
.tyt-cruises *, .tyt-cruises *::before, .tyt-cruises *::after { box-sizing:border-box; }



/* ── UTIL ── */
.tc-section {
  width:100%;
  max-width:none;
  padding:88px 60px;
  margin:0;
}
.tc-section-full{
  width:100%;
  padding:88px 0;
}
.tc-label { font-size:.72rem; font-weight:600; letter-spacing:.3em; text-transform:uppercase; color:#b8935a; margin-bottom:12px; }
.tc-h2 { font-family:'Playfair Display',serif; font-size:clamp(2rem,4vw,3rem); font-weight:700; line-height:1.15; color:#1a1108; margin-bottom:16px; }
.tc-h2-light { color:#f5f0e8; }
.tc-divider { width:52px; height:2px; background:#b8935a; margin-bottom:52px; }
.tc-divider-center { margin:0 auto 52px; }
.tc-dark { background:#0f0c08; }
.tc-cream { background:#f5f0e8; }
.tc-inner {
  width:100%;
  max-width:none;
  margin:0;
  padding:0 60px;
}

/* ── DESTINATIONS ROW ── */
.tc-dest-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:3px; }
.tc-dest-card { position:relative; overflow:hidden; aspect-ratio:4/5; cursor:pointer; }
.tc-dest-card img { width:100%; height:100%; object-fit:cover; transition:transform .6s ease; }
.tc-dest-card:hover img { transform:scale(1.06); }
.tc-dest-overlay { position:absolute; inset:0; background:linear-gradient(to top,rgba(8,6,4,.82) 0%,rgba(8,6,4,.1) 55%,transparent 100%); }
.tc-dest-info { position:absolute; bottom:28px; left:28px; right:28px; }
.tc-dest-city { font-family:'Playfair Display',serif; font-size:1.7rem; font-weight:700; color:#f5f0e8; display:block; }
.tc-dest-tag { font-size:.7rem; letter-spacing:.16em; text-transform:uppercase; color:#b8935a; margin-top:4px; display:block; }

/* ── SHIP STATS ── */
.tc-ship-band { background:#1a1510; padding:48px 24px; }
.tc-ship-stats { display:flex; justify-content:center; flex-wrap:wrap; gap:0; max-width:900px; margin:0 auto; }
.tc-ship-stat { flex:1; min-width:140px; text-align:center; padding:28px 16px; border-right:1px solid rgba(184,147,90,.15); }
.tc-ship-stat:last-child { border-right:none; }
.tc-ship-stat-num { font-family:'Playfair Display',serif; font-size:2.2rem; font-weight:700; color:#b8935a; display:block; }
.tc-ship-stat-lbl { font-size:.7rem; letter-spacing:.14em; text-transform:uppercase; color:rgba(245,240,232,.55); margin-top:4px; display:block; }

/* ── EXPERIENCE TABS ── */
.tc-exp-tabs { display:flex; gap:0; flex-wrap:wrap; margin-bottom:48px; border-bottom:1px solid rgba(184,147,90,.2); }
.tc-exp-tab { font-family:'Poppins',sans-serif; font-size:.78rem; font-weight:600; letter-spacing:.12em; text-transform:uppercase; padding:14px 28px; background:none; border:none; border-bottom:2px solid transparent; cursor:pointer; color:#999; transition:all .2s; margin-bottom:-1px; }
.tc-exp-tab.active { color:#b8935a; border-bottom-color:#b8935a; }
.tc-exp-panel { display:none; animation:tcFadeUp .4s ease both; }
.tc-exp-panel.active { display:block; }

/* Experience cards */
.tc-exp-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(240px,1fr)); gap:24px; }
.tc-exp-card { background:#fff; border:1px solid rgba(184,147,90,.15); padding:32px 28px; transition:transform .22s,box-shadow .22s; }
.tc-exp-card:hover { transform:translateY(-4px); box-shadow:0 12px 40px rgba(0,0,0,.09); }
.tc-exp-card-icon { font-size:1.8rem; margin-bottom:14px; }
.tc-exp-card h3 { font-family:'Playfair Display',serif; font-size:1.15rem; margin-bottom:10px; color:#1a1108; }
.tc-exp-card p { font-size:.84rem; color:#777; line-height:1.75; }

/* Dining specific */
.tc-dining-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(200px,1fr)); gap:20px; }
.tc-dining-card { background:#fff; border:1px solid rgba(184,147,90,.12); overflow:hidden; }
.tc-dining-card img { width:100%; height:160px; object-fit:cover; }
.tc-dining-card-body { padding:20px; }
.tc-dining-card h3 { font-family:'Playfair Display',serif; font-size:1.1rem; color:#1a1108; margin-bottom:8px; }
.tc-dining-card p { font-size:.82rem; color:#888; line-height:1.7; }

/* Cabin cards */
.tc-cabin-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); gap:24px; }
.tc-cabin-card { background:#fff; border:1px solid rgba(184,147,90,.15); overflow:hidden; transition:transform .22s,box-shadow .22s; }
.tc-cabin-card:hover { transform:translateY(-4px); box-shadow:0 12px 40px rgba(0,0,0,.09); }
.tc-cabin-card img { width:100%; height:170px; object-fit:cover; }
.tc-cabin-card-body { padding:22px; }
.tc-cabin-tier { font-size:.65rem; font-weight:600; letter-spacing:.2em; text-transform:uppercase; color:#b8935a; margin-bottom:8px; }
.tc-cabin-card h3 { font-family:'Playfair Display',serif; font-size:1.1rem; color:#1a1108; margin-bottom:8px; }
.tc-cabin-card p { font-size:.81rem; color:#888; line-height:1.7; margin-bottom:12px; }
.tc-cabin-size { font-size:.75rem; font-weight:600; color:#1a1108; background:#f5f0e8; padding:6px 12px; display:inline-block; }

/* ── BOOKING FORM ── */
.tc-form-wrap { background:#fff; border:1px solid rgba(184,147,90,.2); padding:52px 48px; }
.tc-form-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); gap:20px; }
.tc-fg { display:flex; flex-direction:column; gap:7px; }
.tc-fg label { font-size:.7rem; font-weight:600; letter-spacing:.14em; text-transform:uppercase; color:#aaa; }
.tc-fg input, .tc-fg select, .tc-fg textarea {
  padding:13px 16px; border:1px solid #e0d9ce; background:#faf8f5;
  font-family:'Poppins',sans-serif; font-size:.88rem; color:#1a1108;
  outline:none; transition:border-color .2s; border-radius:0; -webkit-appearance:none;
}
.tc-fg input:focus, .tc-fg select:focus, .tc-fg textarea:focus { border-color:#b8935a; }
.tc-fg textarea { resize:vertical; min-height:90px; }
.tc-form-divider { grid-column:1/-1; height:1px; background:#ece7de; margin:4px 0; }
.tc-form-sec { grid-column:1/-1; font-size:.68rem; font-weight:700; letter-spacing:.2em; text-transform:uppercase; color:#b8935a; }
.tc-form-submit-row { grid-column:1/-1; display:flex; justify-content:flex-end; margin-top:8px; }
.tc-submit-btn { background:#b8935a; color:#fff; border:none; font-family:'Poppins',sans-serif; font-size:.84rem; font-weight:600; letter-spacing:.14em; text-transform:uppercase; padding:17px 52px; cursor:pointer; transition:all .2s; }
.tc-submit-btn:hover { background:#9a7a47; transform:translateY(-1px); }

/* ── TRUST STRIP ── */
.tc-trust { background:#1a1510; padding:44px 24px; }
.tc-trust-inner { max-width:1000px; margin:0 auto; display:flex; flex-wrap:wrap; justify-content:center; gap:48px; text-align:center; }
.tc-trust-title { font-size:.78rem; font-weight:600; letter-spacing:.12em; text-transform:uppercase; color:#b8935a; margin-bottom:6px; }
.tc-trust-desc { font-size:.82rem; color:rgba(245,240,232,.55); line-height:1.6; max-width:160px; }
.tc-trust-icon { font-size:1.5rem; margin-bottom:12px; display:block; }



@media(max-width:768px) {
  .tc-dest-grid { grid-template-columns:1fr 1fr; }
  .tc-form-wrap { padding:32px 20px; }
  .tc-hero-stats { gap:24px; }
  .tc-ship-stats { flex-direction:column; align-items:center; }
  .tc-ship-stat { border-right:none; border-bottom:1px solid rgba(184,147,90,.15); width:100%; }
  .tc-exp-tab { padding:10px 16px; font-size:.72rem; }
  .tc-section, .tc-inner { padding-left:24px; padding-right:24px; }
}
@media(max-width:480px) {
  .tc-dest-grid { grid-template-columns:1fr; }
}
</style>
@endpush

@section('content')

<div class="tyt-cruises">

<!-- HERO with brochure images slideshow -->
<x-hero-carousel 
  :slides="$heroImages"
  eyebrow="{{ $heroEyebrow }}"
  :title="$heroTitle"
  subtitle="{{ $heroSubtitle }}"
  ctaText="{{ $heroCtaText }}"
  ctaLink="#tc-booking"
/>

<!-- SHIP STATS BAND -->
<div class="tc-ship-band">
  <div class="tc-ship-stats">
    @foreach($shipStats as $stat)
    <div class="tc-ship-stat">
      <span class="tc-ship-stat-num">{{ $stat['value'] }}</span>
      <span class="tc-ship-stat-lbl">{{ $stat['label'] }}</span>
    </div>
    @endforeach
  </div>
</div>

<!-- DESTINATIONS -->
<section class="tc-section-full tc-dark">
  <div class="tc-inner" style="padding-top:0;padding-bottom:0;">
    <div class="tc-section" style="padding-bottom:40px;">
      <p class="tc-label" style="color:#b8935a;">{{ $destinationsLabel }}</p>
      <h2 class="tc-h2 tc-h2-light">{{ $destinationsHeading }}</h2>
      <div class="tc-divider"></div>
    </div>
  </div>
  <div class="tc-dest-grid">
    @foreach($destinationCards as $card)
    <div class="tc-dest-card">
      @if(!empty($card['resolved_image']))
        <img src="{{ $card['resolved_image'] }}" alt="{{ $card['city'] }}" loading="lazy">
      @endif
      <div class="tc-dest-overlay"></div>
      <div class="tc-dest-info">
        <span class="tc-dest-city">{{ $card['city'] }}</span>
        <span class="tc-dest-tag">{{ $card['tag'] }}</span>
      </div>
    </div>
    @endforeach
  </div>
</section>

<!-- EXPERIENCE TABS -->
<section class="tc-section tc-cream">
  <p class="tc-label">Life Onboard</p>
  <h2 class="tc-h2">Everything You Could Ever Want</h2>
  <div class="tc-divider"></div>

  <div class="tc-exp-tabs">
    <button class="tc-exp-tab active" onclick="tcTab(this,'dining')">Dining</button>
    <button class="tc-exp-tab" onclick="tcTab(this,'entertainment')">Entertainment</button>
    <button class="tc-exp-tab" onclick="tcTab(this,'bars')">Bars &amp; Lounges</button>
    <button class="tc-exp-tab" onclick="tcTab(this,'indulgence')">Indulgence</button>
    <button class="tc-exp-tab" onclick="tcTab(this,'events')">Events</button>
  </div>

  <!-- DINING -->
  <div class="tc-exp-panel active" id="tc-panel-dining">
    @if($diningIntro)
    <p style="font-size:.9rem;color:#777;line-height:1.8;margin-bottom:36px;max-width:720px;">{{ $diningIntro }}</p>
    @endif
    <div class="tc-dining-grid">
      @foreach($diningItems as $item)
      <div class="tc-dining-card">
        @if(!empty($item['resolved_image']))
          <img src="{{ $item['resolved_image'] }}" alt="{{ $item['name'] }}" loading="lazy">
        @endif
        <div class="tc-dining-card-body"><h3>{{ $item['name'] }}</h3><p>{{ $item['description'] }}</p></div>
      </div>
      @endforeach
    </div>
  </div>

  <!-- ENTERTAINMENT -->
  <div class="tc-exp-panel" id="tc-panel-entertainment">
    @if($entertainmentIntro)
    <p style="font-size:.9rem;color:#777;line-height:1.8;margin-bottom:36px;max-width:720px;">{{ $entertainmentIntro }}</p>
    @endif
    <div class="tc-exp-grid">
      @foreach($entertainmentItems as $item)
      <div class="tc-exp-card">
        <div class="tc-exp-card-icon">{{ $item['icon'] ?? '' }}</div>
        <h3>{{ $item['name'] }}</h3>
        <p>{{ $item['description'] }}</p>
      </div>
      @endforeach
    </div>
  </div>

  <!-- BARS -->
  <div class="tc-exp-panel" id="tc-panel-bars">
    @if($barsIntro)
    <p style="font-size:.9rem;color:#777;line-height:1.8;margin-bottom:36px;max-width:720px;">{{ $barsIntro }}</p>
    @endif
    <div class="tc-exp-grid">
      @foreach($barsItems as $item)
      <div class="tc-exp-card">
        <div class="tc-exp-card-icon">{{ $item['icon'] ?? '' }}</div>
        <h3>{{ $item['name'] }}</h3>
        <p>{{ $item['description'] }}</p>
      </div>
      @endforeach
    </div>
  </div>

  <!-- INDULGENCE -->
  <div class="tc-exp-panel" id="tc-panel-indulgence">
    @if($indulgenceIntro)
    <p style="font-size:.9rem;color:#777;line-height:1.8;margin-bottom:36px;max-width:720px;">{{ $indulgenceIntro }}</p>
    @endif
    <div class="tc-exp-grid">
      @foreach($indulgenceItems as $item)
      <div class="tc-exp-card">
        <div class="tc-exp-card-icon">{{ $item['icon'] ?? '' }}</div>
        <h3>{{ $item['name'] }}</h3>
        <p>{{ $item['description'] }}</p>
      </div>
      @endforeach
    </div>
  </div>

  <!-- EVENTS -->
  <div class="tc-exp-panel" id="tc-panel-events">
    <div class="tc-exp-grid">
      @foreach($eventsItems as $item)
      <div class="tc-exp-card">
        <div class="tc-exp-card-icon">{{ $item['icon'] ?? '' }}</div>
        <h3>{{ $item['name'] }}</h3>
        <p>{{ $item['description'] }}</p>
      </div>
      @endforeach
    </div>
  </div>
</section>

<!-- ACCOMMODATION — pulled from DB cruise cabin types -->
@if($cabinTypes->isNotEmpty())
<section class="tc-section-full tc-dark" style="padding-top:88px;padding-bottom:88px;">
  <div class="tc-inner">
    <p class="tc-label" style="color:#b8935a;">Your Home at Sea</p>
    <h2 class="tc-h2 tc-h2-light">Choose Your Stateroom</h2>
    <div class="tc-divider"></div>
    <div class="tc-cabin-grid">
      @foreach($cabinTypes as $cabin)
      <div class="tc-cabin-card">
        @if($cabin->resolved_image)
          <img src="{{ $cabin->resolved_image }}" alt="{{ $cabin->name }}" loading="lazy">
        @endif
        <div class="tc-cabin-card-body">
          @if($cabin->tier_label)
            <div class="tc-cabin-tier">{{ $cabin->tier_label }}</div>
          @endif
          <h3>{{ $cabin->name }}</h3>
          @if($cabin->description)
            <p>{{ $cabin->description }}</p>
          @endif
          @if($cabin->size_info)
            <span class="tc-cabin-size">{{ $cabin->size_info }}</span>
          @endif
        </div>
      </div>
      @endforeach
    </div>
  </div>
</section>
@endif

<!-- BOOKING FORM -->
<section class="tc-section tc-cream" id="tc-booking">
  <p class="tc-label">Plan Your Voyage</p>
  <h2 class="tc-h2">Book Your Cruise</h2>
  <div class="tc-divider"></div>
  <div class="tc-form-wrap">
    <div class="tc-form-grid">

      <div class="tc-form-sec">Voyage Details</div>

      <div class="tc-fg">
        <label>Departure Port</label>
        <select>
          <option value="">Select Port</option>
          @foreach($bookingPorts as $port)
            <option>{{ $port }}</option>
          @endforeach
        </select>
      </div>
      <div class="tc-fg">
        <label>Destination</label>
        <select>
          <option value="">Select Destination</option>
          @foreach($bookingDestinations as $dest)
            <option>{{ $dest }}</option>
          @endforeach
        </select>
      </div>
      <div class="tc-fg">
        <label>Travel Date</label>
        <input type="date" onclick="this.showPicker()">
      </div>
      <div class="tc-fg">
        <label>Duration</label>
        <select>
          <option>2 Nights / 3 Days</option>
          <option>3 Nights / 4 Days</option>
          <option>4 Nights / 5 Days</option>
          <option>5 Nights / 6 Days</option>
          <option>7 Nights / 8 Days</option>
        </select>
      </div>
      @if($cabinTypes->isNotEmpty())
      <div class="tc-fg">
        <label>Cabin Type</label>
        <select>
          @foreach($cabinTypes as $cabin)
            <option>{{ $cabin->name }}</option>
          @endforeach
        </select>
      </div>
      @endif
      <div class="tc-fg">
        <label>No. of Guests</label>
        <select>
          <option>1 Guest</option>
          <option>2 Guests</option>
          <option>3 Guests</option>
          <option>4 Guests</option>
          <option>5+ Guests / Group</option>
        </select>
      </div>

      <div class="tc-form-divider"></div>
      <div class="tc-form-sec">Your Details</div>

      <div class="tc-fg">
        <label>Full Name</label>
        <input type="text" placeholder="Your full name">
      </div>
      <div class="tc-fg">
        <label>Phone / WhatsApp</label>
        <input type="tel" placeholder="+91 98750 73788">
      </div>
      <div class="tc-fg">
        <label>Email Address</label>
        <input type="email" placeholder="you@email.com">
      </div>
      <div class="tc-fg" style="grid-column:1/-1">
        <label>Special Requests (optional)</label>
        <textarea placeholder="Dietary requirements, anniversary celebration, wheelchair access, Jain / vegetarian meals, etc."></textarea>
      </div>

      <div class="tc-form-submit-row">
        <button class="tc-submit-btn" onclick="tcSubmit()">Request a Quote &rarr;</button>
      </div>
    </div>
  </div>
</section>

<!-- TRUST STRIP -->
@if(!empty($trustItems))
<div class="tc-trust">
  <div class="tc-trust-inner">
    @foreach($trustItems as $item)
    <div class="tc-trust-item">
      <span class="tc-trust-icon">{{ $item['icon'] ?? '' }}</span>
      <div class="tc-trust-title">{{ $item['title'] }}</div>
      <div class="tc-trust-desc">{{ $item['desc'] }}</div>
    </div>
    @endforeach
  </div>
</div>
@endif

</div><!-- end .tyt-cruises -->

@endsection

@push('scripts')
<script>
function tcTab(btn, panel) {
  document.querySelectorAll('.tc-exp-tab').forEach(b => b.classList.remove('active'));
  document.querySelectorAll('.tc-exp-panel').forEach(p => p.classList.remove('active'));
  btn.classList.add('active');
  document.getElementById('tc-panel-' + panel).classList.add('active');
}
async function tcSubmit() {
  const req = document.querySelectorAll('.tc-form-wrap input[type="text"], .tc-form-wrap input[type="email"], .tc-form-wrap input[type="tel"]');
  let ok = true;
  req.forEach(function(i){ 
      if(!i.value.trim()){i.style.borderColor='#c0392b';ok=false;}
      else{i.style.borderColor='';} 
  });
  
  if(!ok) {
      showToast('Validation Error', 'Please fill in your name, phone and email to continue.', 'error');
      return;
  }

  const inputs = document.querySelectorAll('.tc-form-wrap input, .tc-form-wrap textarea');
  const btn = document.querySelector('.tc-submit-btn');
  const originalText = btn.textContent;
  btn.textContent = 'Sending...';
  btn.disabled = true;
  
  try {
      await fetch("{{ route('enquiries.store') }}", {
          method: 'POST',
          headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': '{{ csrf_token() }}'
          },
          body: JSON.stringify({
              vertical: 'cruise',
              reference_id: 0,
              name: inputs[0].value,
              phone: inputs[1].value,
              email: inputs[2].value,
              message: inputs[3].value
          })
      });
      showToast('Enquiry Sent', 'Thank you! Our cruise specialist will WhatsApp you within 2 hours with the best options for your voyage.');
      inputs.forEach(i => i.value = '');
      btn.textContent = originalText;
      btn.disabled = false;
  } catch (error) {
      console.error(error);
      showToast('Error', 'Something went wrong. Please try again.', 'error');
      btn.textContent = originalText;
      btn.disabled = false;
  }
}
</script>
@endpush
