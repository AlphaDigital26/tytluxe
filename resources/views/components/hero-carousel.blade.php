@props([
    'slides' => [],
    'eyebrow' => '',
    'title' => '',
    'subtitle' => '',
    'ctaText' => '',
    'ctaLink' => '#',
    'pills' => []
])

<section class="shared-hero">
  @if(count($slides) > 0)
    @foreach($slides as $index => $slide)
      <div class="shared-slide {{ $index === 0 ? 'active' : '' }}" style="background-image: url('{{ $slide }}');"></div>
    @endforeach
  @else
    <div class="shared-slide active" style="background-color: var(--bg-dark);"></div>
  @endif

  <div class="shared-hero-overlay"></div>
  <div class="shared-hero-content">
    @if($eyebrow)
      <span class="shared-hero-label">{{ $eyebrow }}</span>
    @endif
    <h1 class="shared-hero-title">{!! $title !!}</h1>
    @if($subtitle)
      <p class="shared-hero-sub">{!! $subtitle !!}</p>
    @endif
    @if(count($pills) > 0)
      <div class="shared-hero-pills">
        @foreach($pills as $pill)
          <span>{{ $pill }}</span>
        @endforeach
      </div>
    @endif
    @if($ctaText)
      <a href="{{ $ctaLink }}" class="shared-hero-cta">{{ $ctaText }}</a>
    @endif
  </div>

  @if(count($slides) > 1)
    <button class="shared-arrow shared-arrow-prev"><i class="fa-solid fa-chevron-left"></i></button>
    <button class="shared-arrow shared-arrow-next"><i class="fa-solid fa-chevron-right"></i></button>
    <div class="shared-slider-dots">
      @foreach($slides as $index => $slide)
        <button class="shared-dot {{ $index === 0 ? 'active' : '' }}" data-slide="{{ $index }}"></button>
      @endforeach
    </div>
  @endif
</section>
