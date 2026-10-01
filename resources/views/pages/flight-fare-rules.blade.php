@extends('layouts.frontend')

@section('meta_title', 'Fare Rules — ' . $booking->flight_route . ' | TYT Luxe')

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com" />
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,500;0,600&family=Jost:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
<style>
  :root {
    --gold: #c9a84c; --gold-light: #e8c96b;
    --dark: #0d0d0d; --dark-2: #141414;
    --white-80: rgba(255,255,255,0.80); --white-60: rgba(255,255,255,0.60); --white-30: rgba(255,255,255,0.30); --red: #f3a3a3;
  }
  body { background: var(--dark); }
  .fr2-wrap { max-width: 680px; margin: 0 auto; padding: 105px 24px 90px; font-family: 'Jost', sans-serif; }
  @media (max-width: 560px) { .fr2-wrap { padding: 90px 18px 60px; } }
  .fr2-eyebrow { font-size: 10.5px; font-weight: 600; letter-spacing: 0.22em; text-transform: uppercase; color: var(--gold); margin-bottom: 10px; }
  .fr2-title { font-family: 'Cormorant Garamond', serif; font-size: clamp(1.8rem, 4vw, 2.3rem); color: #fff; margin-bottom: 8px; }
  .fr2-sub { font-size: 13.5px; color: var(--white-60); margin-bottom: 28px; }
  .fr2-error { padding: 14px 18px; border-radius: 12px; background: rgba(220,80,80,0.08); border: 1px solid rgba(220,80,80,0.3); color: var(--red); font-size: 13px; }
  .fr2-section { background: var(--dark-2); border: 1px solid rgba(255,255,255,0.08); border-radius: 16px; padding: 24px 26px; margin-bottom: 18px; }
  .fr2-segment-label { font-size: 12px; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; color: var(--gold-light); margin: 0 0 14px; }
  .fr2-policy-label { font-size: 12.5px; font-weight: 700; color: var(--gold-light); letter-spacing: 0.06em; text-transform: uppercase; margin-bottom: 8px; }
  .fr2-band { display: flex; justify-content: space-between; gap: 10px; font-size: 13px; color: var(--white-80); padding: 5px 0; }
  .fr2-back { display: inline-block; margin-top: 8px; color: var(--white-60); font-size: 12.5px; text-decoration: none; }
  .fr2-back:hover { color: #fff; }
</style>
@endpush

@section('content')
<div class="fr2-wrap">
  <p class="fr2-eyebrow">Cancellation &amp; Change Policy</p>
  <h1 class="fr2-title">Fare Rules</h1>
  <p class="fr2-sub">{{ $booking->flight_route }} &middot; Booking {{ $booking->reference }}</p>

  @if($fareRuleError)
    <div class="fr2-error">⚠️ {{ $fareRuleError }}</div>
  @elseif($fareRules)
    @foreach($fareRules as $segmentKey => $rule)
      <div class="fr2-section">
        <p class="fr2-segment-label">{{ $segmentKey }}</p>
        @forelse(($rule['tfr'] ?? []) as $policyType => $bands)
          <div style="margin-bottom:16px;">
            <div class="fr2-policy-label">{{ ucwords(strtolower(str_replace('_',' ', $policyType))) }}</div>
            @foreach($bands as $band)
              <div class="fr2-band">
                <span>
                  @if(isset($band['st']) && isset($band['et']))
                    {{ $band['st'] }}&ndash;{{ $band['et'] }} hrs before departure
                  @else
                    {{ ['BEFORE_DEPARTURE' => 'Before departure', 'AFTER_DEPARTURE' => 'After departure', 'DEFAULT' => 'Any time'][$band['pp'] ?? ''] ?? ucfirst(strtolower(str_replace('_', ' ', $band['pp'] ?? ''))) }}
                  @endif
                  @if(!empty($band['policyInfo'])) &middot; {{ trim(preg_replace('/\s+/', ' ', str_replace('__nls__', ' ', $band['policyInfo']))) }} @endif
                </span>
                <span>
                  @if(isset($band['amount']))
                    INR {{ number_format($band['amount'] + ($band['additionalFee'] ?? 0)) }}
                  @endif
                </span>
              </div>
            @endforeach
          </div>
        @empty
          {{-- Doc: with no mini-rule from the airline, a plain-text
               ("Cat 16") rule comes back under miscInfo instead. --}}
          @php $miscLines = collect(is_array($rule['miscInfo'] ?? null) ? $rule['miscInfo'] : [$rule['miscInfo'] ?? null])->filter(fn ($l) => is_string($l) && trim($l) !== ''); @endphp
          @forelse($miscLines as $line)
            <p class="fr2-sub" style="margin:0 0 8px;">{{ trim(preg_replace('/\s+/', ' ', str_replace('__nls__', ' ', $line))) }}</p>
          @empty
            <p class="fr2-sub" style="margin:0;">No time-banded policy available for this segment.</p>
          @endforelse
        @endforelse
      </div>
    @endforeach
    <p class="fr2-sub">Charges shown are the airline/TripJack's own fee for that action — exact amounts are confirmed at the time you actually cancel/change.</p>
  @else
    <p class="fr2-sub">No fare rules available for this booking.</p>
  @endif

  <a href="{{ route('hotel.booking.confirmation', $booking->reference) }}" class="fr2-back">&larr; Back to Booking</a>
</div>
@endsection
