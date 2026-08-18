@props([
    'slides' => [],
    'eyebrow' => '',
    'title' => '',
    'subtitle' => '',
    'ctaText' => '',
    'ctaLink' => '#',
    'pills' => []
])

<section
    class="shared-hero"
    role="region"
    aria-label="Hero banner"
    aria-roledescription="carousel"
>
  @if(count($slides) > 0)
    @foreach($slides as $index => $slide)
      <div
          class="shared-slide {{ $index === 0 ? 'active' : '' }}"
          style="background-image: url('{{ $slide }}');"
          role="img"
          aria-label="Slide {{ $index + 1 }} of {{ count($slides) }}"
      ></div>
    @endforeach
  @else
    <div class="shared-slide active" style="background-color: var(--bg-dark);" role="img" aria-label="Hero background"></div>
  @endif

  <div class="shared-hero-overlay" aria-hidden="true"></div>
  <div class="shared-hero-content">
    @if($eyebrow)
      <span class="shared-hero-label">{{ $eyebrow }}</span>
    @endif
    <h1 class="shared-hero-title">{!! $title !!}</h1>
    @if($subtitle)
      <p class="shared-hero-sub">{!! $subtitle !!}</p>
    @endif
    @if(count($pills) > 0)
      <div class="shared-hero-pills" role="list">
        @foreach($pills as $pill)
          <span role="listitem">{{ $pill }}</span>
        @endforeach
      </div>
    @endif
    @if($ctaText)
      <a href="{{ $ctaLink }}" class="shared-hero-cta">{{ $ctaText }}</a>
    @endif
  </div>

  @if(count($slides) > 1)
    <button
        class="shared-arrow shared-arrow-prev"
        aria-label="Previous slide"
        type="button"
    >
        <i class="fa-solid fa-chevron-left" aria-hidden="true"></i>
    </button>
    <button
        class="shared-arrow shared-arrow-next"
        aria-label="Next slide"
        type="button"
    >
        <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
    </button>
    <div class="shared-slider-dots" role="tablist" aria-label="Carousel navigation">
      @foreach($slides as $index => $slide)
        <button
            class="shared-dot {{ $index === 0 ? 'active' : '' }}"
            data-slide="{{ $index }}"
            type="button"
            role="tab"
            aria-label="Go to slide {{ $index + 1 }}"
            aria-selected="{{ $index === 0 ? 'true' : 'false' }}"
        ></button>
      @endforeach
    </div>
  @endif
</section>
