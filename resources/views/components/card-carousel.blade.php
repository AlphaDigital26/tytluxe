{{--
  card-carousel.blade.php
  -------------------------
  Reusable Blade component for the 3D stacked card carousel.

  Props:
    $items   – Collection of objects with:
                  ->image_url  (string, full URL)
                  ->title      (string)
                  ->caption    (string, optional)
    $label   – Section eyebrow label (default: "Memories")
    $heading – Section heading text (default: "Gallery")
    $em      – Part of heading to italicise/gold (optional)
--}}

@props([
  'items'   => collect(),
  'label'   => 'Memories',
  'heading' => 'Gallery',
  'em'      => '',
])

<section class="carousel-section" data-carousel>

  {{-- ── Header ─────────────────────────────────── --}}
  <div class="carousel-section-label">{{ $label }}</div>
  <h2 class="carousel-section-title">
    {{ $heading }}
    @if($em)
      <em>{{ $em }}</em>
    @endif
  </h2>

  {{-- ── Stage ──────────────────────────────────── --}}
  <div class="carousel-stage">
    <div class="carousel-track">
      @foreach ($items as $item)
        <article class="carousel-card">
          <img
            src="{{ $item['image_url'] }}"
            alt="{{ $item['title'] }}"
            loading="lazy"
            draggable="false"
          >
          <div class="carousel-card-caption">{{ $item['caption'] ?? $item['title'] }}</div>
        </article>
      @endforeach
    </div>
  </div>

  {{-- ── Controls ────────────────────────────────── --}}
  <div class="carousel-nav">
    <button class="carousel-btn carousel-btn-prev" aria-label="Previous">
      <i class="fa-solid fa-chevron-left"></i>
    </button>

    <div class="carousel-dots">
      @foreach ($items as $i => $item)
        <span class="carousel-dot {{ $i === 0 ? 'is-active' : '' }}" role="button" aria-label="Go to slide {{ $i + 1 }}"></span>
      @endforeach
    </div>

    <button class="carousel-btn carousel-btn-next" aria-label="Next">
      <i class="fa-solid fa-chevron-right"></i>
    </button>
  </div>

</section>
