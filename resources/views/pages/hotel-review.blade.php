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

  /* The site-wide stylesheet sets `overflow-x: hidden` on html/body (to stop
     horizontal scroll elsewhere). Per the CSS overflow spec, setting overflow-x
     without overflow-y forces overflow-y to `auto` too — so html/body silently
     become their own scroll containers instead of the plain viewport, which is
     exactly what breaks `position: sticky` on .br-summary below. Restoring the
     default (visible) here — scoped to just this page's own <style> block, not
     the shared stylesheet — fixes sticky without touching other pages. This
     page's own layout (max-width, centered, self-contained tables) doesn't rely
     on that clipping.
  */
  html, body { overflow-x: visible; overflow-y: visible; }

  .br-hero { padding: 105px 24px 0; max-width: 1100px; margin: 0 auto; }
  .br-back { display: inline-flex; align-items: center; gap: 6px; font-family: 'Jost', sans-serif; font-size: 12.5px; color: var(--white-60); text-decoration: none; margin-bottom: 28px; transition: color var(--transition); }
  .br-back:hover { color: var(--gold); }
  .br-eyebrow { font-family: 'Jost', sans-serif; font-size: 10.5px; font-weight: 600; letter-spacing: 0.22em; text-transform: uppercase; color: var(--gold); margin-bottom: 10px; }
  .br-title { font-family: 'Cormorant Garamond', serif; font-size: clamp(2rem, 4vw, 2.6rem); font-weight: 500; color: #fff; margin-bottom: 10px; }
  .br-sub { font-family: 'Jost', sans-serif; font-size: 14px; color: var(--white-60); margin-bottom: 44px; font-weight: 300; }

  .br-wrap { max-width: 1100px; margin: 0 auto; padding: 0 24px 100px; display: grid; grid-template-columns: 1fr 380px; gap: 44px; align-items: start; }
  .br-wrap > * { min-width: 0; }
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
  .br-field input, .br-field select, .br-field textarea {
    background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.12); border-radius: 10px;
    padding: 14px 16px; color: #fff; font-family: 'Jost', sans-serif; font-size: 13.5px; outline: none;
    transition: border-color var(--transition), background var(--transition); width: 100%; box-sizing: border-box;
    -webkit-appearance: none; appearance: none;
  }
  .br-field textarea { resize: vertical; min-height: 90px; font-family: 'Jost', sans-serif; }
  .br-field select {
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23c9a84c' d='M6 8L1 3h10z'/%3E%3C/svg%3E");
    background-repeat: no-repeat; background-position: right 14px center; padding-right: 36px; cursor: pointer;
  }
  .br-field select option { background: var(--dark-3); color: #fff; }
  .br-field input:focus, .br-field select:focus, .br-field textarea:focus { border-color: var(--gold); background: rgba(201,168,76,0.05); }
  .br-field input::placeholder, .br-field textarea::placeholder { color: rgba(255,255,255,0.25); }
  .br-field.error input, .br-field.error select { border-color: #f3a3a3; background: rgba(220,80,80,0.06); }
  .br-field-error-msg { font-family: 'Jost', sans-serif; font-size: 11px; color: #f3a3a3; margin-top: -2px; }

  .br-row { display: grid; grid-template-columns: 1fr 1fr; gap: 18px; }
  .br-row-3 { display: grid; grid-template-columns: 110px 1fr 1fr; gap: 14px; }
  @media (max-width: 560px) { .br-row, .br-row-3 { grid-template-columns: 1fr; } }

  @media (max-width: 480px) {
    .tyt-stepper { font-size: 9px; letter-spacing: 0.03em; }
    .tyt-stepper-label { display: none; }
    .tyt-stepper-connector { margin: 0 6px; min-width: 10px; }
  }

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
  .br-summary-img { width: 100%; height: 150px; object-fit: cover; border-radius: 14px; margin-bottom: 18px; display: block; }
  .br-summary-hotel { display: flex; align-items: center; gap: 6px; font-family: 'Jost', sans-serif; font-size: 11px; color: var(--gold); letter-spacing: 0.08em; text-transform: uppercase; margin-bottom: 8px; }
  .br-summary h3 { font-family: 'Cormorant Garamond', serif; font-size: 1.5rem; color: #fff; margin-bottom: 26px; line-height: 1.25; }
  .br-line { display: flex; justify-content: space-between; gap: 10px; font-family: 'Jost', sans-serif; font-size: 13px; color: var(--white-80); padding: 10px 0; border-bottom: 1px dashed rgba(255,255,255,0.08); }
  .br-line span:first-child { flex-shrink: 0; }
  .br-line span:last-child { min-width: 0; text-align: right; }
  .br-line:last-of-type { border-bottom: none; }
  .br-line span:first-child { color: var(--white-60); }
  .br-line.total { border-top: 1px solid rgba(255,255,255,0.12); border-bottom: none; margin-top: 6px; padding-top: 16px; font-weight: 700; font-size: 17px; color: #fff; }
  .br-refund { display: flex; align-items: center; flex-wrap: wrap; gap: 6px 8px; font-family: 'Jost', sans-serif; font-size: 12.5px; color: var(--green); margin: 16px 0 4px; padding: 10px 14px; background: rgba(74,222,128,0.06); border: 1px solid rgba(74,222,128,0.2); border-radius: 10px; cursor: pointer; transition: all 0.2s ease; }
  .br-refund:hover { background: rgba(74,222,128,0.12); border-color: rgba(74,222,128,0.4); }
  .br-refund span { min-width: 0; }
  .br-refund span:last-child { margin-left: auto; }
  @media (max-width: 360px) { .br-refund span:last-child { margin-left: 0; } }
  .br-note { font-family: 'Jost', sans-serif; font-size: 11.5px; color: var(--white-30); margin-top: 16px; line-height: 1.6; }

  .br-error {
    margin-bottom: 28px; padding: 15px 20px; border-radius: 12px;
    background: rgba(220,80,80,0.08); border: 1px solid rgba(220,80,80,0.3);
    color: #f3a3a3; font-family: 'Jost', sans-serif; font-size: 13.5px;
    display: flex; align-items: center; gap: 10px;
  }

  /* ===== Stay summary + room card ===== */
  .br-stay-dates { display: flex; align-items: center; gap: 12px; margin-bottom: 14px; }
  .br-stay-stat { display: flex; flex-direction: column; gap: 2px; }
  .br-stay-stat.right { align-items: flex-end; text-align: right; }
  .br-stay-duration { flex: 1; display: flex; flex-direction: column; align-items: center; gap: 4px; min-width: 56px; }
  .br-stay-duration span { font-family: 'Jost', sans-serif; font-size: 10.5px; font-weight: 600; letter-spacing: 0.04em; color: var(--gold-light); white-space: nowrap; }
  .br-stay-duration-line { position: relative; width: 100%; height: 1px; background: rgba(201,168,76,0.3); display: flex; align-items: center; justify-content: center; }
  .br-stay-duration-line svg { position: relative; background: var(--dark-2); color: var(--gold); padding: 0 4px; flex-shrink: 0; width: 12px; height: 12px; }
  @media (max-width: 480px) {
    .br-stay-dates { flex-direction: column; align-items: stretch; gap: 8px; }
    .br-stay-stat.right { align-items: flex-start; text-align: left; }
    .br-stay-duration { flex-direction: row; justify-content: center; }
    .br-stay-duration-line { display: none; }
  }

  .br-stay-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px 14px; padding-top: 12px; border-top: 1px dashed rgba(255,255,255,0.08); margin-bottom: 0; }
  .br-stay-item { display: flex; flex-direction: column; gap: 2px; }
  .br-stay-label { font-family: 'Jost', sans-serif; font-size: 9.5px; font-weight: 600; letter-spacing: 0.08em; text-transform: uppercase; color: var(--white-30); }
  .br-stay-value { font-family: 'Jost', sans-serif; font-size: 13px; font-weight: 600; color: #fff; }
  .br-stay-sub { font-family: 'Jost', sans-serif; font-size: 10.5px; color: var(--white-30); }

  .br-room-card { background: rgba(255,255,255,0.025); border: 1px solid rgba(255,255,255,0.07); border-radius: 12px; padding: 14px 16px; margin-top: 14px; }
  .br-room-name { font-family: 'Cormorant Garamond', serif; font-size: 1.1rem; color: #fff; margin-bottom: 6px; }
  .br-room-tags { display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 10px; }
  .br-room-tag { display: inline-flex; align-items: center; gap: 5px; font-family: 'Jost', sans-serif; font-size: 10.5px; font-weight: 600; letter-spacing: 0.04em; padding: 4px 10px; border-radius: 100px; }
  .br-room-tag.refundable { background: rgba(74,222,128,0.1); color: var(--green); border: 1px solid rgba(74,222,128,0.3); }
  .br-room-tag.non-refundable { background: rgba(220,80,80,0.1); color: #f3a3a3; border: 1px solid rgba(220,80,80,0.3); }
  .br-room-tag.meal { background: rgba(255,255,255,0.06); color: var(--white-80); border: 1px solid rgba(255,255,255,0.12); }

  .br-confirm-check { display: flex; align-items: flex-start; gap: 8px; margin-top: 10px; padding-top: 10px; border-top: 1px dashed rgba(255,255,255,0.08); font-family: 'Jost', sans-serif; font-size: 12px; color: var(--white-60); line-height: 1.45; }
  .br-confirm-check input { margin-top: 3px; flex-shrink: 0; accent-color: var(--gold); width: 15px; height: 15px; cursor: pointer; }
  .br-save-guest-check { display: flex; align-items: flex-start; gap: 10px; margin-top: 14px; font-family: 'Jost', sans-serif; font-size: 12px; color: var(--white-60); line-height: 1.5; cursor: pointer; }
  .br-save-guest-check input { margin-top: 3px; flex-shrink: 0; accent-color: var(--gold); width: 14px; height: 14px; }

  /* ===== Inline cancellation policy (page equivalent of the modal) ===== */
  .br-cancel-toggle { display: flex; align-items: center; justify-content: space-between; cursor: pointer; }
  .br-cancel-toggle .chev { transition: transform 0.25s ease; color: var(--white-30); }
  .br-cancel-toggle.collapsed .chev { transform: rotate(-90deg); }
  .br-cancel-body { overflow: hidden; }
  .br-cancel-body.collapsed { display: none; }
  .br-cancel-table-wrap { overflow-x: auto; margin-top: 4px; }

  /* ===== PAN Information ===== */
  .br-pan-verify-row { display: flex; align-items: center; gap: 14px; flex-wrap: wrap; margin-top: -6px; margin-bottom: 4px; }
  .br-pan-verify-btn {
    display: inline-flex; align-items: center; gap: 7px;
    padding: 10px 18px; border-radius: 10px; border: 1px solid var(--gold);
    background: transparent; color: var(--gold); font-family: 'Jost', sans-serif; font-size: 11.5px;
    font-weight: 700; letter-spacing: 0.06em; text-transform: uppercase; cursor: pointer; white-space: nowrap;
    transition: all var(--transition); flex-shrink: 0;
  }
  .br-pan-verify-btn:hover { background: rgba(201,168,76,0.1); }
  .br-pan-verify-btn.verified { border-color: var(--green); color: var(--green); cursor: default; }
  .br-pan-verify-note { font-family: 'Jost', sans-serif; font-size: 11.5px; color: var(--white-30); margin: 0; line-height: 1.5; }
  .br-pan-verify-note.valid { color: var(--green); }
  .br-pan-verify-note.invalid { color: #f3a3a3; }

  /* ===== Important information ===== */
  .br-info-block { margin-bottom: 22px; }
  .br-info-block:last-child { margin-bottom: 0; }
  .br-info-block h4 { font-family: 'Jost', sans-serif; font-size: 12px; font-weight: 700; letter-spacing: 0.06em; text-transform: uppercase; color: var(--gold-light); margin-bottom: 10px; }
  .br-info-block ul { margin: 0; padding-left: 18px; }
  .br-info-block li { font-family: 'Jost', sans-serif; font-size: 12.5px; color: var(--white-60); line-height: 1.7; margin-bottom: 4px; }
  .br-info-times { display: flex; gap: 28px; margin-bottom: 22px; flex-wrap: wrap; }
  .br-info-time-item { display: flex; flex-direction: column; gap: 2px; }

  @media (max-width: 700px) {
    .br-section { padding: 26px 20px; }
  }
</style>
@endpush

@php
  $pricing = $option['pricing'] ?? [];
  $roomNames = collect($option['roomInfo'] ?? [])->pluck('name')->unique()->implode(' + ');
  $cancellation = $option['cancellation'] ?? [];
  $isRefundable = $cancellation['isRefundable'] ?? false;

  $checkInDate = \Illuminate\Support\Carbon::parse($draft['check_in'] ?? null);
  $checkOutDate = \Illuminate\Support\Carbon::parse($draft['check_out'] ?? null);
  $nights = max(1, $checkInDate->diffInDays($checkOutDate));
  $totalRooms = count($roomSlots);
  $totalAdults = collect($roomSlots)->sum('adults');
  $totalChildren = collect($roomSlots)->sum('children');

  // Guest sees only the final all-inclusive price — no base/tax
  // breakdown line items on this page.
  $customerPrice = $pricing['customerPrice'] ?? ($pricing['totalPrice'] ?? 0);
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
  <div class="tyt-stepper" style="display:flex; align-items:center; gap:0; margin-bottom:28px; font-family:'Jost',sans-serif; font-size:11px; font-weight:600; letter-spacing:0.08em; text-transform:uppercase;">
    <div style="display:flex; align-items:center; gap:8px; color:rgba(255,255,255,0.35);">
      <span style="width:22px; height:22px; border-radius:50%; background:rgba(255,255,255,0.1); border:1px solid rgba(255,255,255,0.2); display:flex; align-items:center; justify-content:center; font-size:10px; flex-shrink:0;">✓</span>
      <span class="tyt-stepper-label">Choose Room</span>
    </div>
    <div class="tyt-stepper-connector" style="flex:1; height:1px; background:rgba(201,168,76,0.3); margin:0 12px; min-width:20px;"></div>
    <div style="display:flex; align-items:center; gap:8px; color:#c9a84c;">
      <span style="width:22px; height:22px; border-radius:50%; background:rgba(201,168,76,0.15); border:1px solid #c9a84c; display:flex; align-items:center; justify-content:center; font-size:10px; font-weight:800; flex-shrink:0;">2</span>
      <span class="tyt-stepper-label">Guest Details</span>
    </div>
    <div class="tyt-stepper-connector" style="flex:1; height:1px; background:rgba(255,255,255,0.1); margin:0 12px; min-width:20px;"></div>
    <div style="display:flex; align-items:center; gap:8px; color:rgba(255,255,255,0.25);">
      <span style="width:22px; height:22px; border-radius:50%; background:rgba(255,255,255,0.04); border:1px solid rgba(255,255,255,0.12); display:flex; align-items:center; justify-content:center; font-size:10px; flex-shrink:0;">3</span>
      <span class="tyt-stepper-label">Payment</span>
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

      @php
        // Prefill from the logged-in guest's own profile so returning
        // guests don't retype everything — old() (a resubmission after a
        // validation error) always wins over the profile default.
        $profilePan = collect(auth()->user()->govt_ids ?? [])->firstWhere('type', 'PAN Card')['number'] ?? null;
        $profilePanName = collect(auth()->user()->govt_ids ?? [])->firstWhere('type', 'PAN Card')['name'] ?? null;
      @endphp

      <div class="br-section">
        <h2>Stay &amp; Room</h2>

        <div class="br-stay-dates">
          <div class="br-stay-stat">
            <span class="br-stay-label">Check In</span>
            <span class="br-stay-value">{{ $checkInDate->format('d M Y') }}</span>
            @if($hotel->check_in_time)<span class="br-stay-sub">from {{ $hotel->check_in_time }}</span>@endif
          </div>
          <div class="br-stay-duration">
            <span>{{ $nights }} {{ Str::plural('Night', $nights) }}</span>
            <div class="br-stay-duration-line">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
            </div>
          </div>
          <div class="br-stay-stat right">
            <span class="br-stay-label">Check Out</span>
            <span class="br-stay-value">{{ $checkOutDate->format('d M Y') }}</span>
            @if($hotel->check_out_time)<span class="br-stay-sub">by {{ $hotel->check_out_time }}</span>@endif
          </div>
        </div>

        <div class="br-stay-grid">
          <div class="br-stay-item">
            <span class="br-stay-label">Rooms</span>
            <span class="br-stay-value">{{ $totalRooms }} {{ Str::plural('Room', $totalRooms) }}</span>
          </div>
          <div class="br-stay-item">
            <span class="br-stay-label">Guests</span>
            <span class="br-stay-value">{{ $totalAdults }} {{ Str::plural('Adult', $totalAdults) }}{{ $totalChildren ? ', '.$totalChildren.' '.Str::plural('Child', $totalChildren) : '' }}</span>
          </div>
        </div>

        <div class="br-room-card">
          <p class="br-room-name">{{ $roomNames ?: 'Room' }}</p>
          <div class="br-room-tags">
            <span class="br-room-tag {{ $isRefundable ? 'refundable' : 'non-refundable' }}">
              @if($isRefundable)
                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M20 6L9 17l-5-5"/></svg>
              @else
                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M18 6L6 18M6 6l12 12"/></svg>
              @endif
              {{ $isRefundable ? 'Refundable' : 'Non-Refundable' }}
            </span>
            <span class="br-room-tag meal">{{ $option['mealBasis'] ?? 'Room Only' }}</span>
          </div>
          <label class="br-confirm-check">
            <input type="checkbox" required>
            <span>I confirm that I have reviewed and agree to proceed with the above selected room category for booking.</span>
          </label>
        </div>
      </div>

      <div class="br-section">
        <div class="br-cancel-toggle" id="brCancelToggle">
          <h2 style="margin-bottom:0;">Cancellation Policy</h2>
          <svg class="chev" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
        </div>
        <div class="br-cancel-body" id="brCancelBody" style="margin-top:22px;">
          <div id="brCancelBullets" style="display:flex; flex-direction:column; gap:8px; margin-bottom:20px;"></div>
          <div style="margin-bottom:20px;">
            <div id="brCancelBar" style="display:flex; height:36px; border-radius:10px; overflow:hidden;"></div>
            <div id="brCancelTicks" style="display:flex; justify-content:space-between; margin-top:8px; font-family:'Jost',sans-serif; font-size:11px; color:var(--white-60); gap:8px;"></div>
          </div>
          <p id="brCancelExpl" style="font-family:'Jost',sans-serif; font-size:12.5px; color:var(--white-60); margin-bottom:18px; line-height:1.5;"></p>
          <div class="br-cancel-table-wrap">
            <table style="width:100%; border-collapse:collapse; font-family:'Jost',sans-serif; font-size:12.5px; min-width:420px;">
              <thead>
                <tr>
                  <th style="text-align:left; padding:10px 12px; color:var(--gold-light); border-bottom:1px solid rgba(255,255,255,0.1);">Cancel On or After</th>
                  <th style="text-align:left; padding:10px 12px; color:var(--gold-light); border-bottom:1px solid rgba(255,255,255,0.1);">Cancel On or Before</th>
                  <th style="text-align:right; padding:10px 12px; color:var(--gold-light); border-bottom:1px solid rgba(255,255,255,0.1);">Charges</th>
                </tr>
              </thead>
              <tbody id="brCancelTableBody"></tbody>
            </table>
          </div>
          <p style="font-family:'Jost',sans-serif; font-size:11px; color:var(--white-30); margin-top:14px; line-height:1.6;">
            • No-shows and early check-outs attract the full cancellation charge unless otherwise specified above.
          </p>
        </div>
      </div>

      <div class="br-section">
        <h2>Contact Details</h2>
        <p style="font-family:'Jost',sans-serif; font-size:12px; color:var(--white-60); margin:-14px 0 22px;">Where we'll send your booking confirmation and any updates.</p>
        <div class="br-row">
          <div class="br-field {{ $errors->has('contact_email') ? 'error' : '' }}">
            <label>Email</label>
            <input type="email" name="contact_email" value="{{ old('contact_email', auth()->user()->email) }}" placeholder="you@email.com" required>
          </div>
          <div class="br-field {{ $errors->has('contact_phone') ? 'error' : '' }}">
            <label>Phone / WhatsApp</label>
            <input type="tel" name="contact_phone" value="{{ old('contact_phone', auth()->user()->phone) }}" placeholder="98765 43210" required>
          </div>
        </div>
      </div>

      @if($panRequired)
      <div class="br-section">
        <h2>PAN Information</h2>
        <div class="br-row">
          <div class="br-field {{ $errors->has('pan_name') ? 'error' : '' }}">
            <label>Name as per PAN</label>
            <input type="text" name="pan_name" value="{{ old('pan_name', $profilePanName) }}" placeholder="e.g. Rahul Sharma" required>
          </div>
          <div class="br-field {{ $errors->has('pan_number') ? 'error' : '' }}">
            <label>PAN Number (required for this rate)</label>
            <input type="text" name="pan_number" id="brPanInput" value="{{ old('pan_number', $profilePan) }}" placeholder="ABCDE1234F" maxlength="10" style="text-transform:uppercase;" required>
          </div>
        </div>
        <div class="br-pan-verify-row">
          <button type="button" class="br-pan-verify-btn" id="brPanVerifyBtn">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M9 12l2 2 4-4"/><path d="M12 3l7 3v6c0 4.5-3 7.5-7 9-4-1.5-7-4.5-7-9V6l7-3z"/></svg>
            <span id="brPanVerifyBtnLabel">Verify Format</span>
          </button>
          <p class="br-pan-verify-note" id="brPanVerifyNote">We check the PAN format only — this is not a government verification.</p>
        </div>
      </div>
      @endif

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
              @if($savedTravellers->isNotEmpty())
              <div class="br-field" style="margin-bottom:14px;">
                <label>Fill From Saved Traveller</label>
                <select class="br-saved-picker" data-is-child="{{ $isChild ? '1' : '0' }}">
                  <option value="">— Select a saved co-traveller —</option>
                  @foreach($savedTravellers as $saved)
                  <option
                    value="{{ $saved->id }}"
                    data-first-name="{{ $saved->first_name }}"
                    data-last-name="{{ $saved->last_name }}"
                    data-gender="{{ $saved->gender }}"
                    data-passport="{{ $saved->passport_number }}"
                  >{{ $saved->first_name }} {{ $saved->last_name }}@if($saved->relationship) ({{ $saved->relationship }}) @endif</option>
                  @endforeach
                </select>
              </div>
              @endif
              <div class="br-row-3">
                <div class="br-field {{ $errors->has($titleField) ? 'error' : '' }}">
                  <label>Title</label>
                  <select name="rooms[{{ $ri }}][travelers][{{ $ti }}][title]" data-field="title" required>
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
                  <input type="text" name="rooms[{{ $ri }}][travelers][{{ $ti }}][first_name]" value="{{ old($firstNameField) }}" data-field="first_name" required>
                </div>
                <div class="br-field {{ $errors->has($lastNameField) ? 'error' : '' }}">
                  <label>Last Name</label>
                  <input type="text" name="rooms[{{ $ri }}][travelers][{{ $ti }}][last_name]" value="{{ old($lastNameField) }}" data-field="last_name" required>
                </div>
              </div>
              @if($passportRequired)
              <div class="br-field {{ $errors->has($passportField) ? 'error' : '' }}">
                <label>Passport Number</label>
                <input type="text" name="rooms[{{ $ri }}][travelers][{{ $ti }}][passport_number]" value="{{ old($passportField) }}" data-field="passport_number" required>
              </div>
              @endif
              <label class="br-save-guest-check">
                <input type="checkbox" name="rooms[{{ $ri }}][travelers][{{ $ti }}][save_to_list]" value="1" {{ old("rooms.{$ri}.travelers.{$ti}.save_to_list") ? 'checked' : '' }}>
                <span>Add this guest to my guest list, for faster booking next time</span>
              </label>
            </div>
          @endfor
        @endforeach
      </div>

      @if($hotel->check_in_time || $hotel->check_out_time || $hotel->know_before_you_go || $hotel->special_instructions || $hotel->mandatory_fees || !empty($hotel->house_rules))
      <div class="br-section">
        <h2>Important Information</h2>

        @if($hotel->check_in_time || $hotel->check_out_time)
        <div class="br-info-times">
          @if($hotel->check_in_time)
          <div class="br-info-time-item">
            <span class="br-stay-label">Check-In</span>
            <span class="br-stay-value">{{ $hotel->check_in_time }}</span>
          </div>
          @endif
          @if($hotel->check_out_time)
          <div class="br-info-time-item">
            <span class="br-stay-label">Check-Out</span>
            <span class="br-stay-value">{{ $hotel->check_out_time }}</span>
          </div>
          @endif
        </div>
        @endif

        @if($hotel->know_before_you_go)
        <div class="br-info-block">
          <h4>Know Before You Go</h4>
          {!! $hotel->know_before_you_go !!}
        </div>
        @endif

        @if($hotel->special_instructions)
        <div class="br-info-block">
          <h4>Policies &amp; Check-In Instructions</h4>
          {!! $hotel->special_instructions !!}
        </div>
        @endif

        @if($hotel->mandatory_fees)
        <div class="br-info-block">
          <h4>Fees</h4>
          {!! $hotel->mandatory_fees !!}
        </div>
        @endif

        @if(!empty($hotel->house_rules))
        <div class="br-info-block">
          <h4>House Rules</h4>
          <ul>
            @foreach($hotel->house_rules as $rule => $value)
              <li>{{ Str::title(str_replace('_', ' ', $rule)) }}: {{ is_bool($value) ? ($value ? 'Yes' : 'No') : $value }}</li>
            @endforeach
          </ul>
        </div>
        @endif
      </div>
      @endif

      <div class="br-section">
        <h2>Special Request</h2>
        <div class="br-field" style="margin-bottom:0;">
          <label>Anything the hotel should know? (Optional)</label>
          <textarea name="special_requests" rows="4" maxlength="500" placeholder="e.g. High floor, early check-in, airport transfer...">{{ old('special_requests') }}</textarea>
        </div>
      </div>
    </form>
  </div>

  <div class="br-summary">
    <p class="br-summary-hotel">{{ $hotel->destination?->name }}</p>
    <h3>{{ $hotel->title }}</h3>

    @if($roomImage)
    <img src="{{ $roomImage }}" alt="{{ $roomNames ?: 'Room' }}" class="br-summary-img" loading="lazy">
    @endif

    <div class="br-line"><span>Room</span><span>{{ $roomNames ?: 'Room' }}</span></div>
    <div class="br-line"><span>Meal Plan</span><span>{{ $option['mealBasis'] ?? 'Room Only' }}</span></div>
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
      <span style="font-size:11px; opacity:0.85; text-decoration:underline; text-underline-offset:2px;">View Policy ⓘ</span>
    </div>
    @endif

    <p class="br-note">Your booking is confirmed instantly once payment is complete.</p>

    <div class="br-submit-row">
      <button type="submit" form="brBookForm" class="br-submit" id="brSubmitBtn">
        <span class="br-spinner"></span>
        <span class="br-submit-label">Proceed to Pay</span>
      </button>
      <p class="br-submit-note">You'll be redirected to our secure payment partner to complete your booking.</p>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
  (function () {
    // Render the same cancellation-policy content used by the site-wide
    // modal (partials.cancellation-modal), but inline and always visible —
    // matching TripJack's own checkout page instead of hiding it behind a click.
    if (window.renderCancellationPolicyInto) {
      window.renderCancellationPolicyInto({
        cancellation: @json($cancellation),
        isRefundable: @json($isRefundable),
        checkIn: @json($draft['check_in'] ?? ''),
        price: @json($customerPrice ?? 0),
      }, {
        bullets: 'brCancelBullets',
        bar: 'brCancelBar',
        ticks: 'brCancelTicks',
        expl: 'brCancelExpl',
        tableBody: 'brCancelTableBody',
      });
    }

    var cancelToggle = document.getElementById('brCancelToggle');
    var cancelBody = document.getElementById('brCancelBody');
    if (cancelToggle && cancelBody) {
      cancelToggle.addEventListener('click', function () {
        cancelToggle.classList.toggle('collapsed');
        cancelBody.classList.toggle('collapsed');
      });
    }

    var panInput = document.getElementById('brPanInput');
    var panVerifyBtn = document.getElementById('brPanVerifyBtn');
    var panVerifyBtnLabel = document.getElementById('brPanVerifyBtnLabel');
    var panVerifyNote = document.getElementById('brPanVerifyNote');
    if (panInput && panVerifyBtn) {
      var panRegex = /^[A-Za-z]{5}[0-9]{4}[A-Za-z]{1}$/;
      panVerifyBtn.addEventListener('click', function () {
        var value = panInput.value.trim().toUpperCase();
        panInput.value = value;
        if (panRegex.test(value)) {
          panVerifyBtnLabel.textContent = 'Valid Format';
          panVerifyBtn.classList.add('verified');
          panVerifyNote.textContent = 'PAN format looks correct. This checks the format only, not a government registry.';
          panVerifyNote.classList.remove('invalid');
          panVerifyNote.classList.add('valid');
        } else {
          panVerifyBtnLabel.textContent = 'Verify Format';
          panVerifyBtn.classList.remove('verified');
          panVerifyNote.textContent = 'That doesn\'t match a valid PAN format (e.g. ABCDE1234F). Please check and try again.';
          panVerifyNote.classList.remove('valid');
          panVerifyNote.classList.add('invalid');
        }
      });
      panInput.addEventListener('input', function () {
        panVerifyBtnLabel.textContent = 'Verify Format';
        panVerifyBtn.classList.remove('verified');
        panVerifyNote.textContent = 'We check the PAN format only — this is not a government verification.';
        panVerifyNote.classList.remove('valid', 'invalid');
      });
    }

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

    document.querySelectorAll('.br-saved-picker').forEach(function (picker) {
      picker.addEventListener('change', function () {
        var traveler = picker.closest('.br-traveler');
        if (!traveler) return;
        var option = picker.options[picker.selectedIndex];
        if (!option || !option.value) return;

        var firstName = option.getAttribute('data-first-name') || '';
        var lastName = option.getAttribute('data-last-name') || '';
        var gender = option.getAttribute('data-gender') || '';
        var passport = option.getAttribute('data-passport') || '';
        var isChild = picker.getAttribute('data-is-child') === '1';

        var firstNameField = traveler.querySelector('[data-field="first_name"]');
        if (firstNameField) firstNameField.value = firstName;

        var lastNameField = traveler.querySelector('[data-field="last_name"]');
        if (lastNameField) lastNameField.value = lastName;

        var passportField = traveler.querySelector('[data-field="passport_number"]');
        if (passportField && passport) passportField.value = passport;

        var titleField = traveler.querySelector('[data-field="title"]');
        if (titleField && gender) {
          var title = '';
          if (gender === 'Male') title = isChild ? 'Master' : 'Mr';
          else if (gender === 'Female') title = isChild ? 'Miss' : 'Ms';
          if (title && Array.prototype.some.call(titleField.options, function (o) { return o.value === title; })) {
            titleField.value = title;
          }
        }
      });
    });
  })();
</script>
@endpush
