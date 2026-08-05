@extends('layouts.frontend')

@push('styles')
<style>
  *, *::before, *::after { box-sizing: border-box; }

  :root {
    --black:      #0a0a0a;
    --dark:       #111111;
    --dark-card:  #161616;
    --dark-mid:   #1c1c1c;
    --border:     rgba(255,255,255,0.09);
    --gold:       #C9A84C;
    --gold-light: #E2C97E;
    --gold-dim:   rgba(201,168,76,0.15);
    --white:      #ffffff;
    --w80:        rgba(255,255,255,0.80);
    --w50:        rgba(255,255,255,0.50);
    --w30:        rgba(255,255,255,0.30);
  }

  body { font-family: 'Poppins', sans-serif; background: var(--black); color: var(--white); }

  /* SECTION HELPERS */
  .s-divider { display: flex; align-items: center; gap: 1rem; max-width: 1100px; margin: 0 auto; padding: 0 3rem; }
  .s-divider::before,.s-divider::after { content: ''; flex: 1; height: 1px; background: var(--border); }
  .s-divider span { font-size: 9px; color: var(--w30); letter-spacing: .18em; text-transform: uppercase; }

  /* RESORT SECTION */
  .resort-section { padding: 4rem 3rem; max-width: 1200px; margin: 0 auto; }

  .resort-header { margin-bottom: 2.5rem; }
  .resort-label { font-size: 10px; font-weight: 600; letter-spacing: .22em; text-transform: uppercase; color: var(--gold); margin-bottom: .4rem; }
  .resort-name { font-family: 'Playfair Display', serif; font-size: clamp(1.6rem,3vw,2.2rem); font-weight: 400; color: var(--white); line-height: 1.2; margin-bottom: .75rem; }
  .resort-name em { font-style: italic; color: var(--gold-light); }
  .resort-desc { font-size: 13.5px; color: var(--w50); font-weight: 300; line-height: 1.8; max-width: 780px; }

  /* ROOM CATEGORY LABEL */
  .rooms-label { font-size: 10px; font-weight: 600; letter-spacing: .22em; text-transform: uppercase; color: var(--w30); margin-bottom: 1.5rem; }

  /* ROOM CARDS GRID */
  .rooms-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px,1fr)); gap: 1.25rem; }

  .room-card {
    background: var(--dark-card); border: 1px solid var(--border);
    border-radius: 10px; overflow: hidden;
    transition: transform .25s, box-shadow .25s, border-color .25s;
    position: relative;
  }
  .room-card:hover { transform: translateY(-4px); box-shadow: 0 18px 45px rgba(0,0,0,.5); border-color: rgba(201,168,76,.3); }

  .room-img { width: 100%; height: 180px; object-fit: cover; display: block; transition: transform .4s; }
  .room-card:hover .room-img { transform: scale(1.04); }
  .room-img-wrap { overflow: hidden; position: relative; }
  .room-img-wrap::after {
    content: ''; position: absolute; inset: 0;
    background: linear-gradient(to bottom, rgba(0,0,0,0) 40%, rgba(0,0,0,.65) 100%);
  }

  .room-body { padding: 1.25rem; }
  .room-name { font-family: 'Playfair Display', serif; font-size: 1.05rem; font-weight: 400; color: var(--white); margin-bottom: .5rem; border-left: 3px solid var(--gold); padding-left: .7rem; line-height: 1.3; }
  .room-desc { font-size: 11.5px; color: var(--w50); font-weight: 300; line-height: 1.7; margin-bottom: 1rem; }

  /* Amenities */
  .amenities { display: flex; flex-wrap: wrap; gap: .5rem; margin-bottom: 1rem; }
  .amenity {
    display: flex; align-items: center; gap: 5px;
    font-size: 10px; font-weight: 500; letter-spacing: .06em; text-transform: uppercase;
    color: var(--w50); background: var(--dark-mid); border: 1px solid var(--border);
    border-radius: 100px; padding: 4px 10px;
  }

  .room-footer { display: flex; align-items: center; justify-content: space-between; padding-top: .75rem; border-top: 1px solid var(--border); }
  .room-cta {
    font-size: 10px; font-weight: 600; letter-spacing: .08em; text-transform: uppercase;
    color: var(--black); background: var(--gold); border: none; border-radius: 5px;
    padding: .4rem .9rem; cursor: pointer; text-decoration: none; transition: background .2s;
  }
  .room-cta:hover { background: var(--gold-light); }

  /* BOTTOM CTA */
  .bottom-section { max-width: 700px; margin: 1rem auto 5rem; padding: 0 2rem; text-align: center; }
  .bottom-card {
    background: var(--dark-card); border: 1px solid var(--border);
    border-radius: 12px; padding: 2.5rem 2rem;
    position: relative; overflow: hidden;
  }
  .bottom-card::before {
    content: ''; position: absolute; top: -60px; left: 50%; transform: translateX(-50%);
    width: 300px; height: 300px; border-radius: 50%;
    background: radial-gradient(circle,rgba(201,168,76,.08) 0%,transparent 70%);
  }
  .bc-tag { font-size: 10px; font-weight: 600; letter-spacing: .2em; text-transform: uppercase; color: var(--gold); display: block; margin-bottom: .75rem; }
  .bottom-card h3 { font-family: 'Playfair Display', serif; font-size: 1.6rem; font-weight: 400; color: var(--white); margin-bottom: .5rem; }
  .bottom-card h3 em { font-style: italic; color: var(--gold-light); }
  .bottom-card p { font-size: 13px; color: var(--w50); line-height: 1.8; margin-bottom: 1.5rem; }
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

  @media(max-width:768px) {
    .resort-section { padding: 3rem 1.25rem; }
    .rooms-grid { grid-template-columns: 1fr; }
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

@foreach($resorts as $index => $resort)

  @if($index > 0)
    <div class="s-divider"><span>*</span></div>
  @endif

  <!-- RESORT: {{ strip_tags($resort['name'] ?? '') }} -->
  <div class="resort-section">
    <div class="resort-header">
      @if(!empty($resort['label']))
        <div class="resort-label">{{ $resort['label'] }}</div>
      @endif
      @if(!empty($resort['name']))
        <div class="resort-name">{!! $resort['name'] !!}</div>
      @endif
      @if(!empty($resort['description']))
        <p class="resort-desc">{{ $resort['description'] }}</p>
      @endif
    </div>

    @if(!empty($resort['rooms']))
      <div class="rooms-label">Room Categories</div>
      <div class="rooms-grid">
        @foreach($resort['rooms'] as $room)
          <div class="room-card">
            @if(!empty($room['resolved_image']))
              <div class="room-img-wrap">
                <img class="room-img" src="{{ $room['resolved_image'] }}" alt="{{ $room['name'] ?? '' }}" loading="lazy">
              </div>
            @endif
            <div class="room-body">
              <div class="room-name">{{ $room['name'] ?? '' }}</div>
              @if(!empty($room['description']))
                <p class="room-desc">{{ $room['description'] }}</p>
              @endif
              @if(!empty($room['amenity_list']))
                <div class="amenities">
                  @foreach($room['amenity_list'] as $amenity)
                    @if(trim($amenity))
                      <span class="amenity">{{ $amenity }}</span>
                    @endif
                  @endforeach
                </div>
              @endif
              <div class="room-footer">
                <a href="{{ $ctaWhatsapp }}" class="room-cta">Enquire</a>
              </div>
            </div>
          </div>
        @endforeach
      </div>
    @endif
  </div>

@endforeach


<!-- BOTTOM CTA -->
<div class="bottom-section">
  <div class="bottom-card">
    @if(!empty($ctaTag))
      <span class="bc-tag">{{ $ctaTag }}</span>
    @endif
    @if(!empty($ctaHeading))
      <h3>{!! $ctaHeading !!}</h3>
    @endif
    @if(!empty($ctaBody))
      <p>{{ $ctaBody }}</p>
    @endif
    <div class="or-row"><span>CONTACT US</span></div>
    <a href="{{ $ctaWhatsapp }}" target="_blank" class="wa-btn">
      <svg viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
      WhatsApp Us
    </a>
  </div>
</div>

@endsection

@push('scripts')
<script></script>
@endpush
