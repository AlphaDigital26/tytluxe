@php
  // $current: 1 = Flight Itinerary, 2 = Passenger Details, 3 = Review, 4 = Payment, 5 = Confirmation.
  // $stepUrls: [stepNumber => url] — a completed step with a URL links back to it.
  // ($stepOneUrl is still accepted for step 1.)
  $steps = ['Flight Itinerary', 'Passenger Details', 'Review', 'Payment', 'Confirmation'];
  $ordinals = ['First Step', 'Second Step', 'Third Step', 'Fourth Step', 'Final Step'];
  $stepUrls = ($stepUrls ?? []) + (! empty($stepOneUrl) ? [1 => $stepOneUrl] : []);
@endphp
<nav class="flr-steps" aria-label="Booking progress">
  @foreach($steps as $idx => $label)
    @php
      $n = $idx + 1;
      $state = $n < $current ? 'done' : ($n === $current ? 'active' : '');
      $link = $state === 'done' ? ($stepUrls[$n] ?? null) : null;
    @endphp
    @if($idx > 0)<span class="flr-step-line {{ $n <= $current ? 'done' : '' }}"></span>@endif
    @if($link)
      <a href="{{ $link }}" class="flr-step done">
    @else
      <div class="flr-step {{ $state }}" @if($state === 'active') aria-current="step" @endif>
    @endif
      <span class="flr-step-dot">{!! $state === 'done' ? '&#10003;' : $n !!}</span>
      <span class="flr-step-text"><span class="flr-step-k">{{ $ordinals[$idx] }}</span><br><span class="flr-step-v">{{ $label }}</span></span>
    @if($link)
      </a>
    @else
      </div>
    @endif
  @endforeach
</nav>
