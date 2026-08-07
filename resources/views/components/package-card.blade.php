<a href="{{ route('package.details', ['id' => $package->id ?? 1]) }}" class="pkg-card">
  <div class="pkg-card-img">
    @if($package->images && $package->images->count() > 0)
      @if(Str::startsWith($package->images->first()->image_path, 'http'))
        <img src="{{ $package->images->first()->image_path }}" alt="{{ $package->title }}" loading="lazy">
      @else
        <img src="{{ asset('storage/' . $package->images->first()->image_path) }}" alt="{{ $package->title }}" loading="lazy">
      @endif
    @else
      <img src="https://images.unsplash.com/photo-1540202404-b71180fb78d1?w=700&q=80" alt="{{ $package->title }}" loading="lazy">
    @endif
    <div class="pkg-badge">Featured</div>
    <div class="pkg-nights">
      <i class="fa-solid fa-moon"></i> {{ $package->duration_nights }} Nights
    </div>
  </div>
  
  <div class="pkg-card-body">
    @if($package->destination)
    <div class="pkg-location">
      <i class="fa-solid fa-location-dot"></i> {{ $package->destination->name }}
    </div>
    @endif
    
    <h3 class="pkg-name">{{ $package->title }}</h3>
    
    @if($package->inclusions && $package->inclusions->count() > 0)
    <div class="pkg-inclusions">
      @foreach($package->inclusions->take(3) as $inclusion)
        <div class="pkg-inclusion-badge">
          <i class="fa-solid fa-check"></i> {{ $inclusion->name ?? $inclusion->title ?? 'Included' }}
        </div>
      @endforeach
    </div>
    @endif
    
    <div class="pkg-footer">
      <div class="pkg-price">
        <div class="pkg-price-lbl">Starting From</div>
        <div class="pkg-price-val"><span class="pkg-price-curr">₹</span>{{ number_format($package->price_from, 0) }}</div>
      </div>
      <div class="pkg-btn-sm">View Details</div>
    </div>
  </div>
</a>
