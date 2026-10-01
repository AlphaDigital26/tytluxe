@php
  // $current: 1 = Flight Itinerary, 2 = Passenger Details, 3 = Payment, 4 = Confirmation.
  // $stepOneUrl: when set, the completed Step 1 links back to it.
  $steps = ['Flight Itinerary', 'Passenger Details', 'Payment', 'Confirmation'];
  $ordinals = ['First Step', 'Second Step', 'Third Step', 'Final Step'];
@endphp
<nav class="flr-steps" aria-label="Booking progress">
  @foreach($steps as $idx => $label)
    @php $n = $idx + 1; $state = $n < $current ? 'done' : ($n === $current ? 'active' : ''); @endphp
    @if($idx > 0)<span class="flr-step-line {{ $n <= $current ? 'done' : '' }}"></span>@endif
    @if($n === 1 && $state === 'done' && ! empty($stepOneUrl))
      <a href="{{ $stepOneUrl }}" class="flr-step done">
    @else
      <div class="flr-step {{ $state }}" @if($state === 'active') aria-current="step" @endif>
    @endif
      <span class="flr-step-dot">{!! $state === 'done' ? '&#10003;' : $n !!}</span>
      <span class="flr-step-text"><span class="flr-step-k">{{ $ordinals[$idx] }}</span><br><span class="flr-step-v">{{ $label }}</span></span>
    @if($n === 1 && $state === 'done' && ! empty($stepOneUrl))
      </a>
    @else
      </div>
    @endif
  @endforeach
</nav>
