@php
  // One leg: its fare and duration. Several legs (Multi-City): the total.
  $single = count($picks) === 1 ? $picks[0] : null;
  $total = collect($picks)->sum('price');
  $minutes = collect($picks)->sum('duration');
  $fmt = fn ($m) => intdiv((int) $m, 60).'h'.((int) $m % 60 ? ' '.((int) $m % 60).'m' : '');
@endphp
@if($single)
<b>&#8377;{{ number_format($single['price']) }}</b> &middot; {{ $fmt($single['duration']) }}
@elseif($kind === 'cheapest')
Total from <b>&#8377;{{ number_format($total) }}</b>
@else
<b>{{ $fmt($minutes) }}</b> total flying time
@endif
