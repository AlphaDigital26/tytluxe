@extends('layouts.frontend')

@section('meta_title', 'Confirm & Book — ' . $hotel->title . ' | TYT Luxe')

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com" />
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,500;0,600&family=Jost:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
<style>
  :root {
    --gold: #c9a84c; --gold-light: #e8c96b; --gold-dim: rgba(201,168,76,0.18);
    --dark: #0d0d0d; --dark-2: #141414; --dark-3: #1c1c1c;
    --white-80: rgba(255,255,255,0.80); --white-60: rgba(255,255,255,0.60); --white-30: rgba(255,255,255,0.30);
    --green: #4ade80; --transition: 0.32s cubic-bezier(0.25,0.46,0.45,0.94);
  }
  body { background: var(--dark); }

  .br-hero { padding: 48px 24px 0; max-width: 1080px; margin: 0 auto; }
  .br-back { display: inline-flex; align-items: center; gap: 6px; font-family: 'Jost', sans-serif; font-size: 12.5px; color: var(--white-60); text-decoration: none; margin-bottom: 20px; transition: color var(--transition); }
  .br-back:hover { color: var(--gold); }
  .br-eyebrow { font-family: 'Jost', sans-serif; font-size: 10.5px; font-weight: 600; letter-spacing: 0.22em; text-transform: uppercase; color: var(--gold); margin-bottom: 10px; }
  .br-title { font-family: 'Cormorant Garamond', serif; font-size: clamp(2rem, 4vw, 2.6rem); font-weight: 500; color: #fff; margin-bottom: 8px; }
  .br-sub { font-family: 'Jost', sans-serif; font-size: 13.5px; color: var(--white-60); margin-bottom: 36px; font-weight: 300; }

  .br-wrap { max-width: 1080px; margin: 0 auto; padding: 0 24px 90px; display: grid; grid-template-columns: 1fr 360px; gap: 36px; align-items: start; }
  @media (max-width: 900px) { .br-wrap { grid-template-columns: 1fr; } .br-summary { position: static; order: -1; } }

  .br-section {
    background: var(--dark-2); border: 1px solid rgba(255,255,255,0.08); border-radius: 18px;
    padding: 28px 30px; margin-bottom: 22px; transition: border-color var(--transition);
  }
  .br-section h2 {
    font-family: 'Cormorant Garamond', serif; font-size: 1.45rem; color: var(--gold); margin-bottom: 20px;
    display: flex; align-items: center; gap: 12px;
  }
  .br-section h2::after { content: ''; flex: 1; height: 1px; background: var(--gold-dim); }

  .br-field { display: flex; flex-direction: column; gap: 7px; margin-bottom: 16px; }
  .br-field label { font-family: 'Jost', sans-serif; font-size: 10.5px; font-weight: 600; letter-spacing: 0.1em; text-transform: uppercase; color: var(--gold); }
  .br-field input, .br-field select {
    background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.12); border-radius: 10px;
    padding: 13px 15px; color: #fff; font-family: 'Jost', sans-serif; font-size: 13.5px; outline: none;
    transition: border-color var(--transition), background var(--transition); width: 100%; box-sizing: border-box;
    -webkit-appearance: none; appearance: none;
  }
  .br-field select {
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23c9a84c' d='M6 8L1 3h10z'/%3E%3C/svg%3E");
    background-repeat: no-repeat; background-position: right 14px center; padding-right: 36px; cursor: pointer;
  }
  .br-field select option { background: var(--dark-3); color: #fff; }
  .br-field input:focus, .br-field select:focus { border-color: var(--gold); background: rgba(201,168,76,0.05); }
  .br-field input::placeholder { color: rgba(255,255,255,0.25); }

  .br-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
  .br-row-3 { display: grid; grid-template-columns: 110px 1fr 1fr; gap: 12px; }
  @media (max-width: 560px) { .br-row, .br-row-3 { grid-template-columns: 1fr; } }

  .br-traveler { border-top: 1px dashed rgba(255,255,255,0.1); padding-top: 18px; margin-top: 18px; }
  .br-traveler:first-child { border-top: none; padding-top: 0; margin-top: 0; }
  .br-traveler-label {
    font-family: 'Jost', sans-serif; font-size: 11px; font-weight: 600; letter-spacing: 0.08em; text-transform: uppercase;
    color: var(--white-60); margin-bottom: 12px; display: flex; align-items: center; gap: 8px;
  }
  .br-traveler-label .dot { width: 6px; height: 6px; border-radius: 50%; background: var(--gold); flex-shrink: 0; }

  .br-submit-row { margin-top: 8px; }
  .br-submit {
    width: 100%; padding: 17px; border: none; border-radius: 100px;
    background: linear-gradient(90deg, #c9a84c, #e8c96b); color: var(--dark);
    font-family: 'Jost', sans-serif; font-size: 13px; font-weight: 800; letter-spacing: 0.1em; text-transform: uppercase;
    cursor: pointer; transition: all var(--transition); box-shadow: 0 4px 16px rgba(201,168,76,0.15);
    display: flex; align-items: center; justify-content: center; gap: 10px;
  }
  .br-submit:hover:not(:disabled) { background: linear-gradient(90deg, #e8c96b, #f5e4a8); box-shadow: 0 8px 24px rgba(201,168,76,0.3); transform: translateY(-1px); }
  .br-submit:disabled { opacity: 0.7; cursor: wait; }
  .br-spinner { width: 15px; height: 15px; border: 2px solid rgba(13,13,13,0.25); border-top-color: var(--dark); border-radius: 50%; animation: brSpin 0.7s linear infinite; display: none; }
  .br-submit.loading .br-spinner { display: inline-block; }
  .br-submit.loading .br-submit-label { display: none; }
  @keyframes brSpin { to { transform: rotate(360deg); } }
  .br-submit-note { font-family: 'Jost', sans-serif; font-size: 11px; color: var(--white-30); text-align: center; margin-top: 12px; }

  .br-summary { position: sticky; top: 100px; background: var(--dark-2); border: 1px solid rgba(201,168,76,0.25); border-radius: 20px; padding: 28px; }
  .br-summary-hotel { display: flex; align-items: center; gap: 6px; font-family: 'Jost', sans-serif; font-size: 11px; color: var(--gold); letter-spacing: 0.08em; text-transform: uppercase; margin-bottom: 6px; }
  .br-summary h3 { font-family: 'Cormorant Garamond', serif; font-size: 1.4rem; color: #fff; margin-bottom: 20px; line-height: 1.2; }
  .br-line { display: flex; justify-content: space-between; gap: 10px; font-family: 'Jost', sans-serif; font-size: 13px; color: var(--white-80); padding: 8px 0; border-bottom: 1px dashed rgba(255,255,255,0.06); }
  .br-line:last-of-type { border-bottom: none; }
  .br-line span:first-child { color: var(--white-60); }
  .br-line.total { border-top: 1px solid rgba(255,255,255,0.12); border-bottom: none; margin-top: 6px; padding-top: 16px; font-weight: 700; font-size: 17px; color: #fff; }
  .br-refund { display: flex; align-items: center; gap: 8px; font-family: 'Jost', sans-serif; font-size: 12.5px; color: var(--green); margin: 16px 0 4px; padding: 10px 14px; background: rgba(74,222,128,0.06); border: 1px solid rgba(74,222,128,0.2); border-radius: 10px; }
  .br-note { font-family: 'Jost', sans-serif; font-size: 11.5px; color: var(--white-30); margin-top: 16px; line-height: 1.6; }

  .br-error {
    margin-bottom: 24px; padding: 14px 18px; border-radius: 12px;
    background: rgba(220,80,80,0.08); border: 1px solid rgba(220,80,80,0.3);
    color: #f3a3a3; font-family: 'Jost', sans-serif; font-size: 13.5px;
    display: flex; align-items: center; gap: 10px;
  }
</style>
@endpush

@php
  $pricing = $option['pricing'] ?? [];
  $roomNames = collect($option['roomInfo'] ?? [])->pluck('name')->unique()->implode(' + ');
  $cancellation = $option['cancellation'] ?? [];
  $isRefundable = $cancellation['isRefundable'] ?? false;
@endphp

@php
  $backUrl = route('hotel.details', array_filter([
    'slug'      => $hotel->slug,
    'check_in'  => $draft['check_in'] ?? null,
    'check_out' => $draft['check_out'] ?? null,
    'adults'    => $draft['adults'] ?? null,
    'children'  => $draft['children'] ?? null,
    'rooms'     => $draft['rooms'] ?? null,
  ]));
@endphp

@section('content')
<div class="br-hero">
  <a href="{{ $backUrl }}" class="br-back">&larr; Back to hotel</a>

  {{-- Step progress bar -- replaces the orphaned "Step 2 of 3" text --}}
  <div style="display:flex; align-items:center; gap:0; margin-bottom:28px; font-family:'Jost',sans-serif; font-size:11px; font-weight:600; letter-spacing:0.08em; text-transform:uppercase;">
    <div style="display:flex; align-items:center; gap:8px; color:rgba(255,255,255,0.35);">
      <span style="width:22px; height:22px; border-radius:50%; background:rgba(255,255,255,0.1); border:1px solid rgba(255,255,255,0.2); display:flex; align-items:center; justify-content:center; font-size:10px; flex-shrink:0;">✓</span>
      Choose Room
    </div>
    <div style="flex:1; height:1px; background:rgba(201,168,76,0.3); margin:0 12px; min-width:20px;"></div>
    <div style="display:flex; align-items:center; gap:8px; color:#c9a84c;">
      <span style="width:22px; height:22px; border-radius:50%; background:rgba(201,168,76,0.15); border:1px solid #c9a84c; display:flex; align-items:center; justify-content:center; font-size:10px; font-weight:800; flex-shrink:0;">2</span>
      Guest Details
    </div>
    <div style="flex:1; height:1px; background:rgba(255,255,255,0.1); margin:0 12px; min-width:20px;"></div>
    <div style="display:flex; align-items:center; gap:8px; color:rgba(255,255,255,0.25);">
      <span style="width:22px; height:22px; border-radius:50%; background:rgba(255,255,255,0.04); border:1px solid rgba(255,255,255,0.12); display:flex; align-items:center; justify-content:center; font-size:10px; flex-shrink:0;">3</span>
      Confirmed
    </div>
  </div>

  <h1 class="br-title">Confirm &amp; Book</h1>
  <p class="br-sub">{{ $hotel->title }} — price re-confirmed just now, ready to hold.</p>
</div>

<div class="br-wrap">
  <div>
    @if(session('booking_error'))
    <div class="br-error">⚠️ {{ session('booking_error') }}</div>
    @endif
    @if($errors->any())
    <div class="br-error">⚠️ {{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('hotel.book', $hotel->slug) }}" id="brBookForm">
      @csrf

      <div class="br-section">
        <h2>Lead Guest</h2>
        <div class="br-row">
          <div class="br-field">
            <label>Full Name</label>
            <input type="text" name="lead_name" value="{{ old('lead_name') }}" placeholder="e.g. Rahul Sharma" required>
          </div>
          <div class="br-field">
            <label>Email</label>
            <input type="email" name="lead_email" value="{{ old('lead_email') }}" placeholder="you@email.com" required>
          </div>
        </div>
        <div class="br-row">
          <div class="br-field">
            <label>Phone / WhatsApp</label>
            <input type="tel" name="lead_phone" value="{{ old('lead_phone') }}" placeholder="98765 43210" required>
          </div>
          @if($panRequired)
          <div class="br-field">
            <label>PAN Number (required for this rate)</label>
            <input type="text" name="pan_number" value="{{ old('pan_number') }}" placeholder="ABCDE1234F" required>
          </div>
          @endif
        </div>
      </div>

      <div class="br-section">
        <h2>Traveler Details</h2>
        @foreach($roomSlots as $ri => $slot)
          @php $count = $slot['adults'] + ($slot['children'] ?? 0); @endphp
          <p class="br-traveler-label"><span class="dot"></span>Room {{ $ri + 1 }}</p>
          @for($ti = 0; $ti < $count; $ti++)
            @php $isChild = $ti >= $slot['adults']; @endphp
            <div class="br-traveler">
              <p class="br-traveler-label">{{ $isChild ? 'Child' : 'Adult' }} {{ $ti + 1 }}</p>
              <div class="br-row-3">
                <div class="br-field">
                  <label>Title</label>
                  <select name="rooms[{{ $ri }}][travelers][{{ $ti }}][title]" required>
                    @if($isChild)
                      <option value="Master">Master</option>
                      <option value="Miss">Miss</option>
                    @else
                      <option value="Mr">Mr</option>
                      <option value="Mrs">Mrs</option>
                      <option value="Ms">Ms</option>
                    @endif
                  </select>
                </div>
                <div class="br-field">
                  <label>First Name</label>
                  <input type="text" name="rooms[{{ $ri }}][travelers][{{ $ti }}][first_name]" required>
                </div>
                <div class="br-field">
                  <label>Last Name</label>
                  <input type="text" name="rooms[{{ $ri }}][travelers][{{ $ti }}][last_name]" required>
                </div>
              </div>
              @if($passportRequired)
              <div class="br-field">
                <label>Passport Number</label>
                <input type="text" name="rooms[{{ $ri }}][travelers][{{ $ti }}][passport_number]" required>
              </div>
              @endif
            </div>
          @endfor
        @endforeach
      </div>

      <div class="br-submit-row">
        <button type="submit" class="br-submit" id="brSubmitBtn">
          <span class="br-spinner"></span>
          <span class="br-submit-label">Hold This Room</span>
        </button>
        <p class="br-submit-note">This may take a few seconds while we confirm with the hotel.</p>
      </div>
    </form>
  </div>

  <div class="br-summary">
    <p class="br-summary-hotel">{{ $hotel->destination?->name }}</p>
    <h3>{{ $hotel->title }}</h3>

    <div class="br-line"><span>Room</span><span>{{ $roomNames ?: 'Room' }}</span></div>
    <div class="br-line"><span>Meal Plan</span><span>{{ $option['mealBasis'] ?? 'Room Only' }}</span></div>
    <div class="br-line"><span>Base Price</span><span>{{ $pricing['currency'] ?? 'INR' }} {{ number_format($pricing['basePrice'] ?? 0) }}</span></div>
    <div class="br-line"><span>Taxes &amp; Fees</span><span>{{ $pricing['currency'] ?? 'INR' }} {{ number_format(($pricing['taxes'] ?? 0) + ($pricing['mf'] ?? 0) + ($pricing['mft'] ?? 0)) }}</span></div>
    <div class="br-line total"><span>Total</span><span>{{ $pricing['currency'] ?? 'INR' }} {{ number_format($pricing['totalPrice'] ?? 0) }}</span></div>

    @if($isRefundable)
    <div class="br-refund">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M20 6L9 17l-5-5"/></svg>
      Free cancellation available
    </div>
    @endif

    <p class="br-note">This holds the room at today's confirmed price. Payment is collected separately before the hold expires{{ isset($option['deadlineDateTime']) ? ' (by '.\Illuminate\Support\Carbon::parse($option['deadlineDateTime'])->format('d M, h:i A').')' : '' }} — our team will contact you to complete payment.</p>
  </div>
</div>
@endsection

@push('scripts')
<script>
  (function () {
    var form = document.getElementById('brBookForm');
    var btn = document.getElementById('brSubmitBtn');
    if (!form || !btn) return;
    form.addEventListener('submit', function () {
      if (!form.checkValidity()) return;
      btn.classList.add('loading');
      btn.disabled = true;
    });
  })();
</script>
@endpush
