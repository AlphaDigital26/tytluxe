@extends('layouts.frontend')

@section('meta_title', 'Flight Bookings — Best Deals on Domestic & International Flights | TYT Luxe')
@section('meta_description', 'Book affordable domestic and international flights with TYT Luxe. Our travel experts find the best fares for Indian travellers. Call or WhatsApp +91 98750 73788 for instant assistance.')

@push('styles')
<style>
*{box-sizing:border-box;}
.tyt-flights{font-family:'Poppins',sans-serif;width:100%;background:#0a0a0a}


/* SECTIONS */
.tyt-dark{background:#111111;padding:64px 48px}
.tyt-darker{background:#0a0a0a;padding:64px 48px}
.tyt-label{font-family:'Poppins',sans-serif;font-size:10px;font-weight:600;letter-spacing:3px;text-transform:uppercase;color:#C9A84C;margin-bottom:10px}
.tyt-head-row{display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:36px;gap:16px;flex-wrap:wrap}
.tyt-title-light{font-family:'Playfair Display',serif;font-size:36px;font-weight:800;color:#ffffff;line-height:1.15}
.tyt-outline-btn{
  display:inline-block;padding:13px 26px;
  font-family:'Poppins',sans-serif;font-size:10px;font-weight:600;
  letter-spacing:2.5px;text-transform:uppercase;
  border:1.5px solid rgba(201,168,76,0.5);
  color:#C9A84C;background:transparent;
  cursor:pointer;white-space:nowrap;text-decoration:none;
  transition:background .2s,color .2s;align-self:center;
}
.tyt-outline-btn:hover{background:#C9A84C;color:#0a0a0a;border-color:#C9A84C}
.tyt-gold-btn{display:inline-block;padding:14px 32px;font-family:'Poppins',sans-serif;font-size:11px;font-weight:600;letter-spacing:2.5px;text-transform:uppercase;background:#C9A84C;color:#0a0a0a;border:none;cursor:pointer;transition:background .2s}
.tyt-gold-btn:hover{background:#b8935a}

/* FLIGHT CARDS */
.tyt-cards-6{display:grid;grid-template-columns:repeat(3,1fr);gap:14px}
.tyt-img-card{position:relative;border-radius:6px;overflow:hidden;height:300px;cursor:pointer}
.tyt-img-card img{width:100%;height:100%;object-fit:cover;display:block;transition:transform .4s ease}
.tyt-img-card:hover img{transform:scale(1.05)}
.tyt-card-overlay{position:absolute;inset:0;background:linear-gradient(to top,rgba(0,0,0,0.80) 0%,rgba(0,0,0,0) 55%)}
.tyt-card-label{position:absolute;bottom:20px;left:20px;font-family:'Playfair Display',serif;font-size:17px;font-weight:700;color:#fff}
.tyt-card-sub{font-family:'Poppins',sans-serif;font-size:11px;font-weight:400;color:rgba(255,255,255,0.75);margin-top:4px}

/* AIRLINE GRID */
.tyt-airline-grid{display:grid;grid-template-columns:repeat(5,1fr);gap:12px;margin-bottom:48px}
.tyt-airline-card{
  background:#1a1a1a;
  border:1px solid rgba(255,255,255,0.07);
  border-radius:8px;padding:20px 12px 14px;
  display:flex;flex-direction:column;align-items:center;justify-content:center;
  gap:10px;transition:transform .2s,border-color .2s;cursor:pointer;min-height:100px;
}
.tyt-airline-card:hover{transform:translateY(-3px);border-color:rgba(201,168,76,0.4)}
.tyt-airline-card img{width:110px;height:48px;object-fit:contain;display:block}
.tyt-airline-name{font-family:'Poppins',sans-serif;font-size:10px;font-weight:600;color:#888;letter-spacing:1px;text-transform:uppercase}

/* FORM */
.tyt-form-2{display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px}
.tyt-form-3{display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;margin-bottom:16px}
.tyt-flabel{display:block;font-size:10px;font-weight:600;letter-spacing:2px;text-transform:uppercase;color:#666;margin-bottom:7px;font-family:'Poppins',sans-serif}
.tyt-finput{width:100%;background:#0d0d0d;border:1px solid rgba(255,255,255,0.08);padding:12px 14px;color:#fff;font-family:'Poppins',sans-serif;font-size:13px;font-weight:300;outline:none;transition:border-color .2s;-webkit-appearance:none;border-radius:0}
.tyt-finput:focus{border-color:#C9A84C}
.tyt-finput option{background:#1a1a1a}
.tyt-finput::placeholder{color:#444}
input[type="date"].tyt-finput::-webkit-calendar-picker-indicator{filter:invert(0.4)}
.tyt-trip-toggle{display:flex;margin-bottom:24px}
.tyt-trip-btn{padding:10px 22px;font-size:10px;font-weight:600;letter-spacing:2px;text-transform:uppercase;font-family:'Poppins',sans-serif;background:transparent;color:#666;border:1px solid rgba(255,255,255,0.1);cursor:pointer;transition:all .2s;margin-right:-1px}
.tyt-trip-btn.active{background:#C9A84C;color:#0a0a0a;border-color:#C9A84C;z-index:1}
.tyt-class-row{display:grid;grid-template-columns:repeat(4,1fr);gap:8px;margin-bottom:24px}
.tyt-class-btn{padding:10px 6px;font-size:10px;font-weight:600;letter-spacing:1.5px;text-transform:uppercase;font-family:'Poppins',sans-serif;background:#0d0d0d;color:#555;border:1px solid rgba(255,255,255,0.08);cursor:pointer;text-align:center;transition:all .2s}
.tyt-class-btn.active{color:#C9A84C;border-color:#C9A84C}

/* FEATURES BAR */
.tyt-feat-row{display:grid;grid-template-columns:repeat(4,1fr);gap:0;background:#111;border-top:1px solid rgba(255,255,255,0.06)}
.tyt-feat{display:flex;align-items:flex-start;gap:14px;padding:36px 28px;border-right:1px solid rgba(255,255,255,0.06)}
.tyt-feat:last-child{border-right:none}
.tyt-feat-icon{color:#C9A84C;font-size:18px;flex-shrink:0;margin-top:2px}
.tyt-feat-title{font-family:'Poppins',sans-serif;font-size:13px;font-weight:600;color:#fff;margin-bottom:5px}
.tyt-feat-desc{font-size:12px;color:#666;font-weight:300;line-height:1.6}

.tyt-success{display:none;background:#0d1f0d;border:1px solid #2a5a2a;padding:20px;margin-top:20px;text-align:center;color:#7ec87e;font-family:'Poppins',sans-serif;font-size:13px;font-weight:300}
.tyt-ret{display:none}
.tyt-ret.show{display:block}

/* DIVIDER */
.tyt-section-divider{width:50px;height:2px;background:#C9A84C;margin-bottom:32px;opacity:0.5}

@media(max-width:768px){
  .tyt-dark,.tyt-darker{padding:40px 20px}
  .tyt-flights-hero{padding:120px 24px 60px;min-height:400px}
  .tyt-cards-6{grid-template-columns:1fr 1fr}
  .tyt-airline-grid{grid-template-columns:repeat(2,1fr)}
  .tyt-form-2,.tyt-form-3{grid-template-columns:1fr}
  .tyt-class-row{grid-template-columns:repeat(2,1fr)}
  .tyt-feat-row{grid-template-columns:1fr 1fr}
  .tyt-feat{border-bottom:1px solid rgba(255,255,255,0.06)}
  .tyt-title-light{font-size:26px}
}
</style>
@endpush

@section('content')

<div class="tyt-flights">

  <!-- HERO SLIDER -->
  <x-hero-carousel 
    :slides="[
      'https://images.unsplash.com/photo-1436491865332-7a61a109cc05?w=1600&q=80',
      'https://images.unsplash.com/photo-1540339832862-474599807836?w=1600&q=80',
      'https://images.unsplash.com/photo-1503221043305-f7498f8b7888?w=1600&q=80',
      'https://images.unsplash.com/photo-1464037866556-6812c9d1c72e?w=1600&q=80'
    ]"
    eyebrow="Flight Experiences"
    title="Fly the <em>Right Way</em>"
    subtitle="Domestic &amp; international flights, business class and beyond —<br>curated by real travel experts, not bots."
    ctaText="Book a Flight →"
    ctaLink="#tyt-book-flight"
  />

  <!-- FLIGHT CATEGORIES -->
  <div class="tyt-dark">
    <p class="tyt-label">Flight Experiences</p>
    <div class="tyt-head-row">
      <h2 class="tyt-title-light">Choose Your Journey</h2>
      <a class="tyt-outline-btn" href="#tyt-book-flight">Request a Flight</a>
    </div>
    <div class="tyt-cards-6">
      <div class="tyt-img-card">
        <img src="https://images.unsplash.com/photo-1436491865332-7a61a109cc05?w=700&q=80" alt="Domestic Flights" loading="lazy"/>
        <div class="tyt-card-overlay"></div>
        <div class="tyt-card-label">Domestic Flights<div class="tyt-card-sub">Pan-India routes, best fares</div></div>
      </div>
      <div class="tyt-img-card">
        <img src="https://images.unsplash.com/photo-1503221043305-f7498f8b7888?w=700&q=80" alt="International Flights" loading="lazy"/>
        <div class="tyt-card-overlay"></div>
        <div class="tyt-card-label">International<div class="tyt-card-sub">Global destinations covered</div></div>
      </div>
      <div class="tyt-img-card">
        <img src="https://images.unsplash.com/photo-1540339832862-474599807836?w=700&q=80" alt="Business Class" loading="lazy"/>
        <div class="tyt-card-overlay"></div>
        <div class="tyt-card-label">Business Class<div class="tyt-card-sub">Lie-flat beds, premium lounges</div></div>
      </div>
      <div class="tyt-img-card">
        <img src="https://images.unsplash.com/photo-1464037866556-6812c9d1c72e?w=700&q=80" alt="First Class" loading="lazy"/>
        <div class="tyt-card-overlay"></div>
        <div class="tyt-card-label">First Class<div class="tyt-card-sub">Suite experience, concierge</div></div>
      </div>
      <div class="tyt-img-card">
        <img src="https://images.unsplash.com/photo-1578474846511-04ba529f0b88?w=700&q=80" alt="Charter Flights" loading="lazy"/>
        <div class="tyt-card-overlay"></div>
        <div class="tyt-card-label">Charter Flights<div class="tyt-card-sub">Private &amp; group aircraft</div></div>
      </div>
      <div class="tyt-img-card">
        <img src="https://images.unsplash.com/photo-1569154941061-e231b4725ef1?w=700&q=80" alt="Multi-City" loading="lazy"/>
        <div class="tyt-card-overlay"></div>
        <div class="tyt-card-label">Multi-City<div class="tyt-card-sub">Complex itineraries, seamless</div></div>
      </div>
    </div>
  </div>

  <!-- AIRLINE PARTNERS -->
  <div class="tyt-darker">
    <p class="tyt-label">Our Airline Partners</p>
    <div class="tyt-head-row" style="margin-bottom:24px">
      <h2 class="tyt-title-light">All Major Airlines. One Call.</h2>
    </div>
    <div class="tyt-airline-grid">
      <div class="tyt-airline-card">
        <img src="{{ asset('assets/images/airlines/indigo.svg') }}" onerror="this.src='https://logo.clearbit.com/goindigo.in'" alt="IndiGo"/>
        <div class="tyt-airline-name">IndiGo</div>
      </div>
      <div class="tyt-airline-card">
        <img src="https://logo.clearbit.com/airindia.com" onerror="this.src='https://ui-avatars.com/api/?name=Air+India&background=1a1a1a&color=C9A84C&size=100'" alt="Air India"/>
        <div class="tyt-airline-name">Air India</div>
      </div>
      <div class="tyt-airline-card">
        <img src="https://logo.clearbit.com/spicejet.com" onerror="this.src='https://ui-avatars.com/api/?name=SpiceJet&background=1a1a1a&color=C9A84C&size=100'" alt="SpiceJet"/>
        <div class="tyt-airline-name">SpiceJet</div>
      </div>
      <div class="tyt-airline-card">
        <img src="{{ asset('assets/images/airlines/emirates.svg') }}" onerror="this.src='https://logo.clearbit.com/emirates.com'" alt="Emirates"/>
        <div class="tyt-airline-name">Emirates</div>
      </div>
      <div class="tyt-airline-card">
        <img src="{{ asset('assets/images/airlines/qatar.svg') }}" onerror="this.src='https://logo.clearbit.com/qatarairways.com'" alt="Qatar Airways"/>
        <div class="tyt-airline-name">Qatar Airways</div>
      </div>
      <div class="tyt-airline-card">
        <img src="{{ asset('assets/images/airlines/lufthansa.svg') }}" onerror="this.src='https://logo.clearbit.com/lufthansa.com'" alt="Lufthansa"/>
        <div class="tyt-airline-name">Lufthansa</div>
      </div>
      <div class="tyt-airline-card">
        <img src="{{ asset('assets/images/airlines/british_airways.svg') }}" onerror="this.src='https://logo.clearbit.com/britishairways.com'" alt="British Airways"/>
        <div class="tyt-airline-name">British Airways</div>
      </div>
      <div class="tyt-airline-card">
        <img src="https://logo.clearbit.com/etihad.com" onerror="this.src='https://ui-avatars.com/api/?name=Etihad&background=1a1a1a&color=C9A84C&size=100'" alt="Etihad Airways"/>
        <div class="tyt-airline-name">Etihad Airways</div>
      </div>
      <div class="tyt-airline-card">
        <img src="https://logo.clearbit.com/singaporeair.com" onerror="this.src='https://ui-avatars.com/api/?name=Singapore+Airlines&background=1a1a1a&color=C9A84C&size=100'" alt="Singapore Airlines"/>
        <div class="tyt-airline-name">Singapore Airlines</div>
      </div>
      <div class="tyt-airline-card">
        <img src="{{ asset('assets/images/airlines/cathay.svg') }}" onerror="this.src='https://logo.clearbit.com/cathaypacific.com'" alt="Cathay Pacific"/>
        <div class="tyt-airline-name">Cathay Pacific</div>
      </div>
    </div>
  </div>

  <!-- BOOKING FORM -->
  <div class="tyt-dark" id="tyt-book-flight">
    <p class="tyt-label">Book Your Flight</p>
    <div class="tyt-head-row" style="margin-bottom:28px">
      <h2 class="tyt-title-light">Request a Flight</h2>
      <span style="font-family:'Poppins',sans-serif;font-size:12px;color:#555;font-weight:300;align-self:center">We'll call you back with the best options within 2 hours</span>
    </div>
    <div class="tyt-trip-toggle">
      <button class="tyt-trip-btn active" onclick="tytSetTrip('one',this)">One Way</button>
      <button class="tyt-trip-btn" onclick="tytSetTrip('round',this)">Round Trip</button>
      <button class="tyt-trip-btn" onclick="tytSetTrip('multi',this)">Multi-City</button>
    </div>
    <div class="tyt-form-2">
      <div><label class="tyt-flabel">From</label><input class="tyt-finput" type="text" placeholder="City or Airport" id="tyt-from"/></div>
      <div><label class="tyt-flabel">To</label><input class="tyt-finput" type="text" placeholder="City or Airport" id="tyt-to"/></div>
    </div>
    <div class="tyt-form-3">
      <div><label class="tyt-flabel">Departure Date</label><input class="tyt-finput" type="date" id="tyt-dep" onclick="this.showPicker()"/></div>
      <div class="tyt-ret" id="tyt-ret"><label class="tyt-flabel">Return Date</label><input class="tyt-finput" type="date" id="tyt-retdate" onclick="this.showPicker()"/></div>
      <div><label class="tyt-flabel">Passengers</label><select class="tyt-finput" id="tyt-pax"><option>1 Adult</option><option>2 Adults</option><option>2 Adults + 1 Child</option><option>2 Adults + 2 Children</option><option>3+ Adults</option><option>Group Booking</option></select></div>
    </div>
    <p class="tyt-flabel" style="margin-bottom:10px">Travel Class</p>
    <div class="tyt-class-row">
      <div class="tyt-class-btn active" onclick="tytSelClass(this)">Economy</div>
      <div class="tyt-class-btn" onclick="tytSelClass(this)">Premium Eco</div>
      <div class="tyt-class-btn" onclick="tytSelClass(this)">Business</div>
      <div class="tyt-class-btn" onclick="tytSelClass(this)">First Class</div>
    </div>
    <div class="tyt-form-3">
      <div><label class="tyt-flabel">Full Name</label><input class="tyt-finput" type="text" placeholder="Your Name" id="tyt-name"/></div>
      <div><label class="tyt-flabel">Phone / WhatsApp</label><input class="tyt-finput" type="tel" placeholder="+91 98750 73788" id="tyt-phone"/></div>
      <div><label class="tyt-flabel">Email Address</label><input class="tyt-finput" type="email" placeholder="you@email.com" id="tyt-email"/></div>
    </div>
    <div style="margin-bottom:28px">
      <label class="tyt-flabel">Special Requests (optional)</label>
      <textarea class="tyt-finput" rows="3" placeholder="Airline preference, wheelchair assistance, meal requirements…" id="tyt-special" style="resize:vertical"></textarea>
    </div>
    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:16px">
      <p style="font-family:'Poppins',sans-serif;font-size:12px;color:#555;font-weight:300;line-height:1.7">Our travel expert will reach out on<br>WhatsApp &amp; Email with curated options.</p>
      <button class="tyt-gold-btn" onclick="tytSubmit()" id="tyt-sbtn">ENQUIRE NOW &rarr;</button>
    </div>
  </div>

  <!-- FEATURES BAR -->
  <div class="tyt-feat-row">
    <div class="tyt-feat"><span class="tyt-feat-icon">✦</span><div><p class="tyt-feat-title">Zero Hidden Fees</p><p class="tyt-feat-desc">Transparent pricing, no surprises at checkout</p></div></div>
    <div class="tyt-feat"><span class="tyt-feat-icon">✦</span><div><p class="tyt-feat-title">24/7 Support</p><p class="tyt-feat-desc">Call or WhatsApp us anytime, even mid-travel</p></div></div>
    <div class="tyt-feat"><span class="tyt-feat-icon">✦</span><div><p class="tyt-feat-title">Flexible Changes</p><p class="tyt-feat-desc">Hassle-free rescheduling and cancellation help</p></div></div>
    <div class="tyt-feat"><span class="tyt-feat-icon">✦</span><div><p class="tyt-feat-title">Best Fare Guarantee</p><p class="tyt-feat-desc">We find the best price across all airlines</p></div></div>
  </div>

</div>

@endsection

@push('scripts')
<script>

function tytSetTrip(t,b){
  document.querySelectorAll('.tyt-trip-btn').forEach(function(x){x.classList.remove('active')});
  b.classList.add('active');
  document.getElementById('tyt-ret').classList.toggle('show',t==='round');
}
function tytSelClass(el){
  document.querySelectorAll('.tyt-class-btn').forEach(function(x){x.classList.remove('active')});
  el.classList.add('active');
}
async function tytSubmit(){
  var n=document.getElementById('tyt-name').value.trim();
  var p=document.getElementById('tyt-phone').value.trim();
  var e=document.getElementById('tyt-email').value.trim();
  var f=document.getElementById('tyt-from').value.trim();
  var t=document.getElementById('tyt-to').value.trim();
  if(!n||!p||!f||!t){alert('Please fill in: From, To, Name and Phone.');return;}
  var btn=document.getElementById('tyt-sbtn');
  var originalText=btn.textContent;
  btn.textContent='Sending...';
  btn.disabled = true;

  var tripType = document.querySelector('.tyt-trip-btn.active').textContent;
  var depDate = document.getElementById('tyt-dep').value;
  var retDate = document.getElementById('tyt-retdate').value;
  var pax = document.getElementById('tyt-pax').value;
  var cls = document.querySelector('.tyt-class-btn.active').textContent;
  var msg = document.getElementById('tyt-special').value.trim();

  var message = `Trip Type: ${tripType}\nFrom: ${f}\nTo: ${t}\nDeparture: ${depDate}\nReturn: ${retDate}\nPassengers: ${pax}\nClass: ${cls}\n\nSpecial Requests: ${msg}`;

  try {
      let res = await fetch("{{ route('enquiries.store') }}", {
          method: 'POST',
          headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': '{{ csrf_token() }}'
          },
          body: JSON.stringify({
              vertical: 'flight',
              reference_id: 0,
              name: n,
              phone: p,
              email: e,
              message: message
          })
      });
      
      if (!res.ok) {
          let errData = await res.text();
          throw new Error('Server error: ' + errData);
      }
      
      btn.textContent='SUBMITTED \u2713';
      btn.style.background='#2a7a2a';
      showToast('Request Received', 'Our team will WhatsApp you shortly with the best flight options.');
  } catch (error) {
      console.error(error);
      btn.textContent = originalText;
      btn.disabled = false;
      showToast('Error', 'Something went wrong. Please try again.', 'error');
  }
}
</script>
@endpush
