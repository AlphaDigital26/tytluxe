@extends('layouts.frontend')

@section('meta_title', 'Exclusive Travel Offers & Deals — Limited Time Discounts | TYT Luxe')
@section('meta_description', 'Explore exclusive limited-time travel deals and offers from TYT Luxe. Discover discounted luxury hotel packages, cruise deals and special promotions tailored for Indian travellers.')

@push('styles')
<style>
  *, *::before, *::after { box-sizing: border-box; }

  :root {
    --black:     #0a0a0a;
    --dark:      #111111;
    --dark-card: #161616;
    --dark-mid:  #1c1c1c;
    --border:    rgba(255,255,255,0.09);
    --gold:      #C9A84C;
    --gold-light:#E2C97E;
    --gold-dim:  rgba(201,168,76,0.15);
    --white:     #ffffff;
    --w80:       rgba(255,255,255,0.80);
    --w50:       rgba(255,255,255,0.50);
    --w30:       rgba(255,255,255,0.30);
  }

  body { font-family: 'Poppins', sans-serif; background: var(--black); color: var(--white); }

  /* ── FILTER TABS ── */
  .filter-bar { display: flex; align-items: center; justify-content: center; gap: .6rem; padding: 2.5rem 2rem 0; flex-wrap: wrap; }
  .filter-btn {
    padding: .45rem 1.2rem; border-radius: 100px; border: 1px solid var(--border);
    background: transparent; color: var(--w50); font-family: 'Poppins', sans-serif;
    font-size: 11px; font-weight: 500; letter-spacing: .08em; text-transform: uppercase;
    cursor: pointer; transition: all .2s;
  }
  .filter-btn:hover { border-color: var(--gold); color: var(--gold); }
  .filter-btn.active { background: var(--gold); border-color: var(--gold); color: var(--black); }

  /* ── SLIDER SECTION ── */
  .slider-section { padding: 3.5rem 0 0; }

  .slider-header { display: flex; align-items: flex-end; justify-content: space-between; padding: 0 3rem; margin-bottom: 1.75rem; }
  .slider-label { font-size: 10px; font-weight: 600; letter-spacing: .22em; text-transform: uppercase; color: var(--gold); margin-bottom: .3rem; }
  .slider-title { font-family: 'Playfair Display', serif; font-size: 1.75rem; font-weight: 400; color: var(--white); line-height: 1.2; }
  .slider-title em { font-style: italic; color: var(--gold-light); }
  .slider-arrows { display: flex; gap: .5rem; }
  .arrow-btn {
    width: 38px; height: 38px; border-radius: 50%; border: 1px solid var(--border);
    background: var(--dark-card); color: var(--w50); font-size: 16px; cursor: pointer;
    display: flex; align-items: center; justify-content: center; transition: all .2s;
  }
  .arrow-btn:hover { border-color: var(--gold); color: var(--gold); background: var(--gold-dim); }

  /* TRACK */
  .slider-track {
    display: flex; gap: 1.25rem; padding: 0 3rem 2rem;
    overflow-x: auto; scroll-behavior: smooth;
    scrollbar-width: none; -ms-overflow-style: none;
    scroll-snap-type: x mandatory;
  }
  .slider-track::-webkit-scrollbar { display: none; }

  /* ── OFFER CARD ── */
  .offer-card {
    flex: 0 0 290px; scroll-snap-align: start;
    border-radius: 12px; overflow: hidden;
    position: relative; cursor: pointer;
    transition: transform .25s, box-shadow .25s;
    border: 1px solid var(--border);
  }
  .offer-card:hover { transform: translateY(-5px); box-shadow: 0 20px 50px rgba(0,0,0,.5); border-color: rgba(201,168,76,.3); }

  .card-img {
    width: 100%; height: 200px; object-fit: cover; display: block;
    transition: transform .4s;
  }
  .offer-card:hover .card-img { transform: scale(1.04); }

  .card-img-wrap { position: relative; overflow: hidden; }
  .card-img-wrap::after {
    content: ''; position: absolute; inset: 0;
    background: linear-gradient(to bottom, rgba(0,0,0,0) 40%, rgba(0,0,0,.7) 100%);
  }
  .card-badge {
    position: absolute; top: 12px; left: 12px; z-index: 2;
    font-size: 9px; font-weight: 700; letter-spacing: .15em; text-transform: uppercase;
    padding: 4px 10px; border-radius: 100px;
  }
  .badge-gold { background: var(--gold); color: var(--black); }
  .badge-hot  { background: #e74c3c; color: #fff; }
  .badge-new  { background: #27ae60; color: #fff; }

  .card-body { background: var(--dark-card); padding: 1.1rem 1.25rem 1.25rem; }
  .card-name { font-family: 'Playfair Display', serif; font-size: 1.05rem; font-weight: 400; color: var(--white); margin-bottom: .3rem; }
  .card-sub  { font-size: 11px; color: var(--w50); line-height: 1.6; margin-bottom: .9rem; }
  .card-footer { display: flex; align-items: center; justify-content: space-between; }
  .card-price { font-size: 13px; color: var(--gold); font-weight: 500; }
  .card-cta {
    font-size: 10px; font-weight: 600; letter-spacing: .08em; text-transform: uppercase;
    color: var(--black); background: var(--gold); border: none; border-radius: 5px;
    padding: .4rem .9rem; cursor: pointer; text-decoration: none; transition: background .2s;
  }
  .card-cta:hover { background: var(--gold-light); }

  /* DOTS */
  .slider-dots { display: flex; justify-content: center; gap: 6px; padding: .5rem 0 2rem; }
  .dot { width: 6px; height: 6px; border-radius: 50%; background: var(--border); border: 1px solid rgba(255,255,255,.15); cursor: pointer; transition: all .25s; }
  .dot.active { background: var(--gold); border-color: var(--gold); width: 20px; border-radius: 3px; }

  /* SECTION DIVIDER */
  .s-divider { display: flex; align-items: center; gap: 1rem; max-width: 1100px; margin: 0 auto; padding: 0 3rem; }
  .s-divider::before,.s-divider::after { content: ''; flex: 1; height: 1px; background: var(--border); }
  .s-divider span { font-size: 9px; color: var(--w30); letter-spacing: .18em; text-transform: uppercase; white-space: nowrap; }

  /* ── COMING SOON RIBBON ── */
  .soon-ribbon {
    position: absolute; bottom: 0; left: 0; right: 0; z-index: 3;
    background: rgba(10,10,10,.82); backdrop-filter: blur(4px);
    text-align: center; padding: .5rem;
    font-size: 10px; font-weight: 600; letter-spacing: .15em; text-transform: uppercase;
    color: var(--gold);
  }

  /* ── NOTIFY + WA ── */
  .bottom-section { max-width: 700px; margin: 3rem auto 5rem; padding: 0 2rem; text-align: center; }
  .bottom-card {
    background: var(--dark-card); border: 1px solid var(--border);
    border-radius: 12px; padding: 2.5rem 2rem;
    position: relative; overflow: hidden;
  }
  .bottom-card::before {
    content: ''; position: absolute; top: -60px; left: 50%; transform: translateX(-50%);
    width: 300px; height: 300px; border-radius: 50%;
    background: radial-gradient(circle, rgba(201,168,76,.08) 0%, transparent 70%);
  }
  .bc-tag { font-size: 10px; font-weight: 600; letter-spacing: .2em; text-transform: uppercase; color: var(--gold); display: block; margin-bottom: .75rem; }
  .bottom-card h3 { font-family: 'Playfair Display', serif; font-size: 1.6rem; font-weight: 400; color: var(--white); margin-bottom: .5rem; }
  .bottom-card h3 em { font-style: italic; color: var(--gold-light); }
  .bottom-card p { font-size: 13px; color: var(--w50); line-height: 1.8; margin-bottom: 1.5rem; }
  .notify-form { display: flex; gap: .75rem; max-width: 400px; margin: 0 auto .75rem; }
  .notify-form input {
    flex: 1; background: var(--dark-mid); border: 1px solid var(--border); border-radius: 6px;
    padding: .7rem 1rem; font-family: 'Poppins', sans-serif; font-size: 13px; color: var(--white); outline: none; transition: border-color .2s;
  }
  .notify-form input::placeholder { color: var(--w30); }
  .notify-form input:focus { border-color: var(--gold); }
  .notify-form button {
    background: var(--gold); border: none; border-radius: 6px; padding: .7rem 1.1rem;
    font-family: 'Poppins', sans-serif; font-size: 10px; font-weight: 600; letter-spacing: .1em;
    text-transform: uppercase; color: var(--black); cursor: pointer; white-space: nowrap; transition: background .2s;
  }
  .notify-form button:hover { background: var(--gold-light); }
  .notify-note { font-size: 11px; color: var(--w30); margin-bottom: 1.5rem; }
  .notify-success { display: none; font-size: 13px; color: #25D366; margin: .5rem 0 1.25rem; }
  .or-row { display: flex; align-items: center; gap: .75rem; margin: 1.25rem 0; }
  .or-row::before,.or-row::after { content: ''; flex: 1; height: 1px; background: var(--border); }
  .or-row span { font-size: 10px; color: var(--w30); letter-spacing: .1em; }
  .wa-btn {
    display: inline-flex; align-items: center; gap: 10px; background: #25D366; color: #fff;
    text-decoration: none; border-radius: 6px; padding: .85rem 1.75rem;
    font-size: 12px; font-weight: 600; letter-spacing: .08em; text-transform: uppercase;
    transition: opacity .2s, transform .15s;
  }
  .wa-btn:hover { opacity: .9; transform: translateY(-1px); }
  .wa-btn svg { width: 18px; height: 18px; fill: #fff; flex-shrink: 0; }

  /* ── VOUCHER CARD ── */
  .voucher-grid { display: flex; gap: 1.5rem; padding: 0 3rem 2rem; overflow-x: auto; scrollbar-width: none; -ms-overflow-style: none; scroll-snap-type: x mandatory; }
  .voucher-grid::-webkit-scrollbar { display: none; }
  .voucher-card {
    flex: 0 0 340px; scroll-snap-align: start;
    display: flex; background: var(--dark-card); border-radius: 12px;
    border: 1px dashed var(--gold); position: relative; overflow: hidden;
  }
  .voucher-left {
    padding: 1.5rem; flex: 1; border-right: 1px dashed var(--border);
    display: flex; flex-direction: column; justify-content: center;
  }
  .voucher-right {
    padding: 1.5rem; width: 120px; display: flex; flex-direction: column;
    align-items: center; justify-content: center; background: var(--gold-dim);
  }
  .voucher-amount { font-family: 'Playfair Display', serif; font-size: 2rem; color: var(--gold); line-height: 1; margin-bottom: .25rem; }
  .voucher-type { font-size: 10px; text-transform: uppercase; letter-spacing: .1em; color: var(--w50); margin-bottom: .75rem; }
  .voucher-desc { font-size: 13px; color: var(--white); line-height: 1.4; }
  .voucher-code-wrap { text-align: center; margin-bottom: .75rem; }
  .voucher-code-label { font-size: 9px; text-transform: uppercase; color: var(--w50); letter-spacing: .05em; }
  .voucher-code { font-family: monospace; font-size: 16px; font-weight: 700; color: var(--white); }
  .voucher-copy-btn {
    background: var(--gold); color: var(--black); border: none; padding: .5rem .75rem;
    border-radius: 4px; font-size: 10px; font-weight: 600; text-transform: uppercase;
    cursor: pointer; transition: background .2s; width: 100%;
  }
  .voucher-copy-btn:hover { background: var(--gold-light); }

  /* ── VOUCHER MODAL ── */
  .voucher-modal { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.8); z-index: 9999; align-items: center; justify-content: center; padding: 1.5rem; }
  .voucher-modal-content { background: var(--dark-card); border: 1px solid var(--border); border-radius: 12px; padding: 2.5rem; max-width: 500px; width: 100%; position: relative; }
  .voucher-modal-close { position: absolute; top: 1rem; right: 1.5rem; font-size: 1.5rem; color: var(--w50); cursor: pointer; transition: color .2s; }
  .voucher-modal-close:hover { color: var(--white); }
  .vm-amount { font-family: 'Playfair Display', serif; font-size: 2.5rem; color: var(--gold); margin-bottom: .25rem; }
  .vm-type { font-size: 11px; text-transform: uppercase; letter-spacing: .1em; color: var(--w50); margin-bottom: 1.5rem; }
  .vm-code { display: inline-block; background: var(--gold-dim); color: var(--gold); padding: .5rem 1rem; border-radius: 4px; font-family: monospace; font-size: 1.25rem; font-weight: 700; margin-bottom: 1.5rem; border: 1px dashed var(--gold); }
  .vm-desc { font-size: 14px; color: var(--white); line-height: 1.6; margin-bottom: 2rem; padding-bottom: 1.5rem; border-bottom: 1px solid var(--border); }
  .vm-terms-title { font-size: 11px; text-transform: uppercase; letter-spacing: .1em; color: var(--w50); margin-bottom: 1rem; }
  .vm-terms { font-size: 12px; color: var(--w50); line-height: 1.8; margin-bottom: 0; }

  @media(max-width:768px){
    .slider-header { padding: 0 1.25rem; }
    .slider-track { padding: 0 1.25rem 1.5rem; }
    .notify-form { flex-direction: column; }
    .voucher-grid { padding: 0 1.25rem 1.5rem; }
  }
</style>
@endpush

@section('content')

<!-- HERO -->
<x-hero-carousel
  :slides="$heroImages"
  eyebrow="{{ $heroEyebrow }}"
  :title="$heroTitle"
  subtitle="{{ $heroSubtitle }}"
  ctaText=""
  ctaLink=""
/>

<!-- FILTER TABS -->
<div class="filter-bar">
  @foreach($filterTabs as $tab)
    <button
      class="filter-btn {{ $loop->first ? 'active' : '' }}"
      onclick="filterOffers('{{ $tab['key'] }}', this)"
    >{{ $tab['label'] }}</button>
  @endforeach
</div>

<!-- FLIGHT VOUCHERS -->
<div class="slider-section" data-category="flights">
  <div class="slider-header">
    <div>
      <div class="slider-label">Flight Deals</div>
      <div class="slider-title">Exclusive <em>Flight Vouchers</em></div>
    </div>
  </div>
  <div class="voucher-grid">
    {{-- Voucher 1 --}}
    <div class="voucher-card" onclick="openVoucherModal('FLYTYT50', '$50 OFF', 'International Flights', 'Valid on all round-trip international bookings over $500.', '1. Valid until Dec 31, 2026.<br>2. Minimum booking value $500.<br>3. Cannot be combined with other offers.<br>4. Applicable only on international routes.')" style="cursor: pointer;">
      <div class="voucher-left">
        <div class="voucher-amount">$50 OFF</div>
        <div class="voucher-type">International Flights</div>
        <div class="voucher-desc">Valid on all round-trip international bookings over $500.</div>
      </div>
      <div class="voucher-right">
        <div class="voucher-code-wrap">
          <div class="voucher-code-label">Use Code</div>
          <div class="voucher-code">FLYTYT50</div>
        </div>
        <button class="voucher-copy-btn" onclick="copyVoucher(event, this, 'FLYTYT50')">Copy Code</button>
      </div>
    </div>

    {{-- Voucher 2 --}}
    <div class="voucher-card" onclick="openVoucherModal('SUMMER20', '20% OFF', 'Domestic Flights', 'Get up to $100 off on any domestic flight booking.', '1. Valid until Aug 31, 2026.<br>2. Maximum discount is $100.<br>3. Valid for domestic flights only.<br>4. One-time use per customer.')" style="cursor: pointer;">
      <div class="voucher-left">
        <div class="voucher-amount">20% OFF</div>
        <div class="voucher-type">Domestic Flights</div>
        <div class="voucher-desc">Get up to $100 off on any domestic flight booking.</div>
      </div>
      <div class="voucher-right">
        <div class="voucher-code-wrap">
          <div class="voucher-code-label">Use Code</div>
          <div class="voucher-code">SUMMER20</div>
        </div>
        <button class="voucher-copy-btn" onclick="copyVoucher(event, this, 'SUMMER20')">Copy Code</button>
      </div>
    </div>
  </div>
</div>

@foreach($categories as $catIndex => $category)
  @php $trackId = $category['category_key'] . '-track'; $dotsId = $category['category_key'] . '-dots'; @endphp

  @if($catIndex > 0)
    <div class="s-divider"><span>✦</span></div>
  @endif

  <div class="slider-section" data-category="{{ $category['category_key'] }}">
    <div class="slider-header">
      <div>
        @if(!empty($category['slider_label']))
          <div class="slider-label">{{ $category['slider_label'] }}</div>
        @endif
        @if(!empty($category['slider_title']))
          <div class="slider-title">{!! $category['slider_title'] !!}</div>
        @endif
      </div>
      <div class="slider-arrows">
        <button class="arrow-btn" onclick="slide('{{ $trackId }}',-1)">&#8592;</button>
        <button class="arrow-btn" onclick="slide('{{ $trackId }}', 1)">&#8594;</button>
      </div>
    </div>

    <div class="slider-track" id="{{ $trackId }}">
      @foreach($category['cards'] as $card)
        <div class="offer-card">
          <div class="card-img-wrap">
            @if(!empty($card['resolved_image']))
              <img class="card-img" src="{{ $card['resolved_image'] }}" alt="{{ $card['name'] ?? '' }}" loading="lazy">
            @endif
            @if(!empty($card['badge_label']))
              <span class="card-badge {{ $card['badge_type'] ?? 'badge-gold' }}">{{ $card['badge_label'] }}</span>
            @endif
            @if(!empty($card['coming_soon']))
              <div class="soon-ribbon">Deal Coming Soon</div>
            @endif
          </div>
          <div class="card-body">
            <div class="card-name">{{ $card['name'] ?? '' }}</div>
            @if(!empty($card['subtitle']))
              <div class="card-sub">{{ $card['subtitle'] }}</div>
            @endif
            <div class="card-footer">
              <span class="card-price">{{ $card['price'] ?? 'Contact for Price' }}</span>
              <a href="{{ $card['enquire_link'] ?? $ctaWhatsapp }}" class="card-cta">Enquire</a>
            </div>
          </div>
        </div>
      @endforeach
    </div>

    @if(!empty($category['cards']))
      <div class="slider-dots" id="{{ $dotsId }}">
        @foreach($category['cards'] as $di => $__)
          <div class="dot {{ $di === 0 ? 'active' : '' }}" onclick="scrollToDot('{{ $trackId }}', {{ $di }})"></div>
        @endforeach
      </div>
    @endif
  </div>
@endforeach

<!-- BOTTOM CTA -->
<div class="bottom-section">
  <div class="bottom-card">
    @if(!empty($ctaTag))<span class="bc-tag">{{ $ctaTag }}</span>@endif
    @if(!empty($ctaHeading))<h3>{!! $ctaHeading !!}</h3>@endif
    @if(!empty($ctaBody))<p>{{ $ctaBody }}</p>@endif
    <form class="notify-form" onsubmit="handleNotify(event)">
      <input type="tel" id="notifyPhone" placeholder="+91 XXXXX XXXXX" required>
      <button type="submit">Notify Me</button>
    </form>
    @if(!empty($ctaNotifyNote))<p class="notify-note">{{ $ctaNotifyNote }}</p>@endif
    <div class="or-row"><span>OR</span></div>
    <a href="{{ $ctaWhatsapp }}" target="_blank" class="wa-btn">
      <svg viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
      {{ $ctaWaLabel }}
    </a>
  </div>
</div>

<!-- VOUCHER MODAL -->
<div id="voucherModal" class="voucher-modal" onclick="closeVoucherModal(event)">
  <div class="voucher-modal-content">
    <span class="voucher-modal-close" onclick="closeVoucherModal(event)">&times;</span>
    <h3 class="vm-amount" id="vmAmount"></h3>
    <div class="vm-type" id="vmType"></div>
    <div class="vm-code">Code: <span id="vmCode"></span></div>
    <p class="vm-desc" id="vmDesc"></p>
    
    <div class="vm-terms-title">Terms & Conditions</div>
    <div class="vm-terms" id="vmTerms"></div>
  </div>
</div>

@endsection

@push('scripts')
<script>
  function slide(id, dir) {
    const t = document.getElementById(id);
    const card = t.querySelector('.offer-card');
    if (!card) return;
    const w = card.offsetWidth + 20;
    t.scrollBy({ left: dir * w, behavior: 'smooth' });
    setTimeout(() => updateDots(id), 350);
  }
  function scrollToDot(id, idx) {
    const t = document.getElementById(id);
    const card = t.querySelector('.offer-card');
    if (!card) return;
    const w = card.offsetWidth + 20;
    t.scrollTo({ left: idx * w, behavior: 'smooth' });
    setTimeout(() => updateDots(id), 350);
  }
  function updateDots(id) {
    const t = document.getElementById(id);
    const card = t.querySelector('.offer-card');
    if (!card) return;
    const w = card.offsetWidth + 20;
    const idx = Math.round(t.scrollLeft / w);
    const dotsId = id.replace('-track', '-dots');
    const dotsEl = document.getElementById(dotsId);
    if (dotsEl) dotsEl.querySelectorAll('.dot').forEach((d, i) => d.classList.toggle('active', i === idx));
  }

  document.querySelectorAll('.slider-track').forEach(t => {
    t.addEventListener('scroll', () => updateDots(t.id));
  });

  function filterOffers(cat, btn) {
    document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    document.querySelectorAll('.slider-section').forEach(s => {
      s.style.display = (cat === 'all' || s.dataset.category === cat) ? '' : 'none';
    });
    document.querySelectorAll('.s-divider').forEach(d => d.style.display = cat === 'all' ? '' : 'none');
  }

  async function handleNotify(e) {
    e.preventDefault();
    const btn = e.target.querySelector('button');
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
                vertical: 'general',
                reference_id: 0,
                name: 'Guest (Notify Me)',
                phone: document.getElementById('notifyPhone').value,
                message: 'Requested to be notified about latest deals via WhatsApp.'
            })
        });
        document.querySelector('.notify-form').reset();
        btn.textContent = originalText;
        btn.disabled = false;
        showToast('Subscribed', 'Done! We\'ll WhatsApp you as soon as a deal drops.');
    } catch (error) {
        console.error(error);
        btn.textContent = originalText;
        btn.disabled = false;
        showToast('Error', 'Something went wrong. Please try again.', 'error');
    }
  }

  function openVoucherModal(code, amount, type, desc, terms) {
    document.getElementById('vmAmount').textContent = amount;
    document.getElementById('vmType').textContent = type;
    document.getElementById('vmCode').textContent = code;
    document.getElementById('vmDesc').textContent = desc;
    document.getElementById('vmTerms').innerHTML = terms;
    document.getElementById('voucherModal').style.display = 'flex';
  }

  function closeVoucherModal(e) {
    if (e.target.id === 'voucherModal' || e.target.classList.contains('voucher-modal-close')) {
      document.getElementById('voucherModal').style.display = 'none';
    }
  }

  function copyVoucher(e, btn, code) {
    e.stopPropagation();
    navigator.clipboard.writeText(code).then(() => {
      const originalText = btn.textContent;
      btn.textContent = 'Copied!';
      btn.style.background = '#27ae60';
      btn.style.color = '#fff';
      setTimeout(() => {
        btn.textContent = originalText;
        btn.style.background = 'var(--gold)';
        btn.style.color = 'var(--black)';
      }, 2000);
    });
  }
</script>
@endpush
