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

  .br-hero { padding: 105px 24px 0; max-width: 1100px; margin: 0 auto; }
  .br-back { display: inline-flex; align-items: center; gap: 6px; font-family: 'Jost', sans-serif; font-size: 12.5px; color: var(--white-60); text-decoration: none; margin-bottom: 28px; transition: color var(--transition); }
  .br-back:hover { color: var(--gold); }
  .br-eyebrow { font-family: 'Jost', sans-serif; font-size: 10.5px; font-weight: 600; letter-spacing: 0.22em; text-transform: uppercase; color: var(--gold); margin-bottom: 10px; }
  .br-title { font-family: 'Cormorant Garamond', serif; font-size: clamp(2rem, 4vw, 2.6rem); font-weight: 500; color: #fff; margin-bottom: 10px; }
  .br-sub { font-family: 'Jost', sans-serif; font-size: 14px; color: var(--white-60); margin-bottom: 44px; font-weight: 300; }

  .br-wrap { max-width: 1100px; margin: 0 auto; padding: 0 24px 100px; display: grid; grid-template-columns: 1fr 380px; gap: 44px; align-items: start; }
  @media (max-width: 900px) { .br-wrap { grid-template-columns: 1fr; gap: 28px; } .br-summary { position: static; order: -1; } }

  .br-section {
    background: var(--dark-2); border: 1px solid rgba(255,255,255,0.08); border-radius: 20px;
    padding: 34px 36px; margin-bottom: 26px; transition: border-color var(--transition);
  }
  .br-section h2 {
    font-family: 'Cormorant Garamond', serif; font-size: 1.5rem; color: var(--gold); margin-bottom: 26px;
    display: flex; align-items: center; gap: 14px;
  }
  .br-section h2::after { content: ''; flex: 1; height: 1px; background: var(--gold-dim); }

  .br-field { display: flex; flex-direction: column; gap: 8px; margin-bottom: 20px; }
  .br-field label { font-family: 'Jost', sans-serif; font-size: 10.5px; font-weight: 600; letter-spacing: 0.1em; text-transform: uppercase; color: var(--gold); }
  .br-field input, .br-field select {
    background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.12); border-radius: 10px;
    padding: 14px 16px; color: #fff; font-family: 'Jost', sans-serif; font-size: 13.5px; outline: none;
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
  .br-field.error input, .br-field.error select { border-color: #f3a3a3; background: rgba(220,80,80,0.06); }
  .br-field-error-msg { font-family: 'Jost', sans-serif; font-size: 11px; color: #f3a3a3; margin-top: -2px; }

  .br-row { display: grid; grid-template-columns: 1fr 1fr; gap: 18px; }
  .br-row-3 { display: grid; grid-template-columns: 110px 1fr 1fr; gap: 14px; }
  @media (max-width: 560px) { .br-row, .br-row-3 { grid-template-columns: 1fr; } }

  .br-traveler { border-top: 1px dashed rgba(255,255,255,0.1); padding-top: 22px; margin-top: 22px; }
  .br-traveler:first-child { border-top: none; padding-top: 0; margin-top: 0; }
  .br-traveler-label {
    font-family: 'Jost', sans-serif; font-size: 11px; font-weight: 600; letter-spacing: 0.08em; text-transform: uppercase;
    color: var(--white-60); margin-bottom: 16px; display: flex; align-items: center; gap: 8px;
  }
  .br-traveler-label .dot { width: 6px; height: 6px; border-radius: 50%; background: var(--gold); flex-shrink: 0; }

  .br-submit-row { margin-top: 12px; }
  .br-submit {
    width: 100%; padding: 18px; border: none; border-radius: 100px;
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
  .br-submit-note { font-family: 'Jost', sans-serif; font-size: 11px; color: var(--white-30); text-align: center; margin-top: 14px; line-height: 1.6; }

  .br-summary { position: sticky; top: 100px; background: var(--dark-2); border: 1px solid rgba(201,168,76,0.25); border-radius: 22px; padding: 32px; }
  .br-summary-hotel { display: flex; align-items: center; gap: 6px; font-family: 'Jost', sans-serif; font-size: 11px; color: var(--gold); letter-spacing: 0.08em; text-transform: uppercase; margin-bottom: 8px; }
  .br-summary h3 { font-family: 'Cormorant Garamond', serif; font-size: 1.5rem; color: #fff; margin-bottom: 26px; line-height: 1.25; }
  .br-line { display: flex; justify-content: space-between; gap: 10px; font-family: 'Jost', sans-serif; font-size: 13px; color: var(--white-80); padding: 10px 0; border-bottom: 1px dashed rgba(255,255,255,0.08); }
  .br-line:last-of-type { border-bottom: none; }
  .br-line span:first-child { color: var(--white-60); }
  .br-line.total { border-top: 1px solid rgba(255,255,255,0.12); border-bottom: none; margin-top: 6px; padding-top: 16px; font-weight: 700; font-size: 17px; color: #fff; }
  .br-refund { display: flex; align-items: center; gap: 8px; font-family: 'Jost', sans-serif; font-size: 12.5px; color: var(--green); margin: 16px 0 4px; padding: 10px 14px; background: rgba(74,222,128,0.06); border: 1px solid rgba(74,222,128,0.2); border-radius: 10px; cursor: pointer; transition: all 0.2s ease; }
  .br-refund:hover { background: rgba(74,222,128,0.12); border-color: rgba(74,222,128,0.4); }
  .br-note { font-family: 'Jost', sans-serif; font-size: 11.5px; color: var(--white-30); margin-top: 16px; line-height: 1.6; }

  .br-error {
    margin-bottom: 28px; padding: 15px 20px; border-radius: 12px;
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
      Payment
    </div>
  </div>

  <h1 class="br-title">Confirm &amp; Book</h1>
  <p class="br-sub">{{ $hotel->title }} — price re-confirmed just now. Enter your details to proceed to payment.</p>
</div>

<div class="br-wrap">
  <div>
    @if(session('booking_error'))
    <div class="br-error">⚠️ {{ session('booking_error') }}</div>
    @endif
    @if($errors->any())
    <div class="br-error" style="flex-direction:column; align-items:flex-start; gap:6px;">
      @foreach($errors->all() as $error)
        <div style="display:flex; gap:8px;"><span>⚠️</span><span>{{ $error }}</span></div>
      @endforeach
    </div>
    @endif

    <form method="POST" action="{{ route('hotel.book', $hotel->slug) }}" id="brBookForm">
      @csrf

      <div class="br-section">
        <h2>Lead Guest</h2>
        <div class="br-row">
          <div class="br-field {{ $errors->has('lead_name') ? 'error' : '' }}">
            <label>Full Name</label>
            <input type="text" name="lead_name" value="{{ old('lead_name') }}" placeholder="e.g. Rahul Sharma" required>
          </div>
          <div class="br-field {{ $errors->has('lead_email') ? 'error' : '' }}">
            <label>Email</label>
            <input type="email" name="lead_email" value="{{ old('lead_email') }}" placeholder="you@email.com" required>
          </div>
        </div>
        <div class="br-row">
          <div class="br-field {{ $errors->has('lead_phone') ? 'error' : '' }}">
            <label>Phone / WhatsApp</label>
            <input type="tel" name="lead_phone" value="{{ old('lead_phone') }}" placeholder="98765 43210" required>
          </div>
          @if($panRequired)
          <div class="br-field {{ $errors->has('pan_number') ? 'error' : '' }}">
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
              @php
                $titleField = "rooms.{$ri}.travelers.{$ti}.title";
                $firstNameField = "rooms.{$ri}.travelers.{$ti}.first_name";
                $lastNameField = "rooms.{$ri}.travelers.{$ti}.last_name";
                $passportField = "rooms.{$ri}.travelers.{$ti}.passport_number";
                $oldTitle = old($titleField);
              @endphp
              <div class="br-row-3">
                <div class="br-field {{ $errors->has($titleField) ? 'error' : '' }}">
                  <label>Title</label>
                  <select name="rooms[{{ $ri }}][travelers][{{ $ti }}][title]" required>
                    @if($isChild)
                      <option value="Master" {{ $oldTitle === 'Master' ? 'selected' : '' }}>Master</option>
                      <option value="Miss" {{ $oldTitle === 'Miss' ? 'selected' : '' }}>Miss</option>
                    @else
                      <option value="Mr" {{ $oldTitle === 'Mr' ? 'selected' : '' }}>Mr</option>
                      <option value="Mrs" {{ $oldTitle === 'Mrs' ? 'selected' : '' }}>Mrs</option>
                      <option value="Ms" {{ $oldTitle === 'Ms' ? 'selected' : '' }}>Ms</option>
                    @endif
                  </select>
                </div>
                <div class="br-field {{ $errors->has($firstNameField) ? 'error' : '' }}">
                  <label>First Name</label>
                  <input type="text" name="rooms[{{ $ri }}][travelers][{{ $ti }}][first_name]" value="{{ old($firstNameField) }}" required>
                </div>
                <div class="br-field {{ $errors->has($lastNameField) ? 'error' : '' }}">
                  <label>Last Name</label>
                  <input type="text" name="rooms[{{ $ri }}][travelers][{{ $ti }}][last_name]" value="{{ old($lastNameField) }}" required>
                </div>
              </div>
              @if($passportRequired)
              <div class="br-field {{ $errors->has($passportField) ? 'error' : '' }}">
                <label>Passport Number</label>
                <input type="text" name="rooms[{{ $ri }}][travelers][{{ $ti }}][passport_number]" value="{{ old($passportField) }}" required>
              </div>
              @endif
            </div>
          @endfor
        @endforeach
      </div>

      <div class="br-submit-row">
        <button type="submit" class="br-submit" id="brSubmitBtn">
          <span class="br-spinner"></span>
          <span class="br-submit-label">Proceed to Pay</span>
        </button>
        <p class="br-submit-note">You'll be redirected to our secure payment partner to complete your booking.</p>
      </div>
    </form>
  </div>

  <div class="br-summary">
    <p class="br-summary-hotel">{{ $hotel->destination?->name }}</p>
    <h3>{{ $hotel->title }}</h3>

    <div class="br-line"><span>Room</span><span>{{ $roomNames ?: 'Room' }}</span></div>
    <div class="br-line"><span>Meal Plan</span><span>{{ $option['mealBasis'] ?? 'Room Only' }}</span></div>
    @php
      // Guest sees only the final all-inclusive price — no base/tax
      // breakdown line items on this page.
      $customerPrice = $pricing['customerPrice'] ?? ($pricing['totalPrice'] ?? 0);
    @endphp
    <div class="br-line total"><span>Total</span><span>{{ $pricing['currency'] ?? 'INR' }} {{ number_format($customerPrice) }}</span></div>

    @if($isRefundable)
    <div class="br-refund htl-cancel-policy-trigger"
         data-cancellation='@json($cancellation)'
         data-refundable="true"
         data-room-name="{{ $roomNames }}"
         data-hotel-title="{{ $hotel->title }}"
         data-checkin="{{ $draft['check_in'] ?? '' }}"
         data-checkout="{{ $draft['check_out'] ?? '' }}"
         data-price="{{ $customerPrice ?? 0 }}"
         title="Click to view Cancellation Policy">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M20 6L9 17l-5-5"/></svg>
      <span>Free cancellation available</span>
      <span style="margin-left:auto; font-size:11px; opacity:0.85; text-decoration:underline; text-underline-offset:2px;">View Policy ⓘ</span>
    </div>
    @endif

    <p class="br-note">Your booking is confirmed instantly once payment is complete.</p>
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

    // A bfcache restore (e.g. browser Back after this form redirected
    // elsewhere) would otherwise bring back the disabled/spinning button
    // with no way to submit again.
    window.addEventListener('pageshow', function () {
      btn.classList.remove('loading');
      btn.disabled = false;
    });
  })();
</script>
@endpush
