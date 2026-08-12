{{--
  scrapbook-gallery.blade.php
  -------------------------
  Reusable Blade component for a scattered, pinboard-style photo gallery.

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

<section class="scrapbook-section">

  {{-- ── Header ─────────────────────────────────── --}}
  <div class="scrapbook-section-label">{{ $label }}</div>
  <h2 class="scrapbook-section-title">
    {{ $heading }}
    @if($em)
      <em>{{ $em }}</em>
    @endif
  </h2>

  {{-- ── Board ──────────────────────────────────── --}}
  <div class="scrapbook-board">

    @foreach ($items as $i => $item)
      @php
        // Alternate decorations for the scrapbook feel
        $decor = ($i % 2 === 0) ? 'tape' : 'pin';
      @endphp
      <article class="scrapbook-photo" data-decor="{{ $decor }}">
        <img
          src="{{ $item['image_url'] }}"
          alt="{{ $item['title'] }}"
          loading="lazy"
          draggable="false"
        >
      </article>
    @endforeach

  </div>

</section>
