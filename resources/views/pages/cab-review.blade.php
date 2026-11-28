@extends('layouts.frontend')

@section('meta_title', 'Review Your Cab | TYT Luxe')

@php
  $price = $option['price'];
  $p = $option['policies'] ?? [];
  $pickup = \Illuminate\Support\Carbon::parse($search['pickupAt']);
  $return = $search['returnAt'] ? \Illuminate\Support\Carbon::parse($search['returnAt']) : null;
  $from = $search['origin']['displayAddress'];
  $to = $search['destination']['displayAddress'];
  $free = collect($p['cancellationPolicy'] ?? [])->where('refundPercentage', '>=', 100)->sortBy('minHours')->first();
  $freeUntil = $free ? $pickup->copy()->subHours((int) $free['minHours']) : null;
  $isAirport = $search['journeyType'] === 'airport_transfer';
  // Profiles often hold the full name in "name": don't repeat the surname.
  $lastName = trim((string) $user->last_name);
  $firstName = trim((string) $user->name);
  if ($lastName !== '' && \Illuminate\Support\Str::endsWith(mb_strtolower($firstName), ' '.mb_strtolower($lastName))) {
      $firstName = trim(mb_substr($firstName, 0, -mb_strlen($lastName)));
  }
@endphp

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600&family=Jost:wght@300;400;500;600;700;800&display=swap" rel="stylesheet" />
<style>
  :root { --gold:#c9a84c; --gold-light:#e8c96b; --gold-dim:rgba(201,168,76,0.15); --dark:#0d0d0d; --dark-2:#141414; --white-80:rgba(255,255,255,0.8); --white-60:rgba(255,255,255,0.6); --white-30:rgba(255,255,255,0.3); --green:#4ade80; }
  body { background: var(--dark); }
  .br-hero { padding: 105px 24px 0; max-width: 1100px; margin: 0 auto; font-family: 'Jost', sans-serif; }
  .br-back { display: inline-flex; gap: 6px; font-size: 12.5px; color: var(--white-60); text-decoration: none; margin-bottom: 28px; }
  .br-back:hover { color: var(--gold); }
  .br-eyebrow { font-size: 10.5px; font-weight: 600; letter-spacing: 0.22em; text-transform: uppercase; color: var(--gold); margin-bottom: 10px; }
  /* cab-heading-fix: the site's global h1–h3 colour is dark */
  .br-hero h1.br-title, .br-summary h3 { color: #fff; }
  .br-lists h3 { font-family: 'Jost', sans-serif; }
  .br-title { font-family: 'Cormorant Garamond', serif; font-size: clamp(2rem, 4vw, 2.6rem); font-weight: 500; color: #fff; margin: 0 0 10px; }
  .br-sub { font-size: 14px; color: var(--white-60); margin: 0 0 40px; font-weight: 300; }
  .br-wrap { max-width: 1100px; margin: 0 auto; padding: 0 24px 100px; display: grid; grid-template-columns: 1fr 380px; gap: 44px; align-items: start; font-family: 'Jost', sans-serif; }
  .br-wrap > * { min-width: 0; }
  .br-section { background: var(--dark-2); border: 1px solid rgba(255,255,255,0.08); border-radius: 20px; padding: 34px 36px; margin-bottom: 26px; color: #fff; }
  .br-wrap .br-section h2 { font-family: 'Cormorant Garamond', serif; font-size: 1.5rem; font-weight: 600; color: var(--gold); margin: 0 0 26px; display: flex; align-items: center; gap: 14px; }
  .br-section h2::after { content: ''; flex: 1; height: 1px; background: var(--gold-dim); }
  .br-trip { display: grid; grid-template-columns: 1fr 1fr; gap: 22px; font-size: 14px; }
  .br-k { font-size: 10.5px; font-weight: 600; letter-spacing: 0.1em; text-transform: uppercase; color: var(--gold); margin-bottom: 6px; }
  .br-v-sub { color: var(--white-60); font-size: 12.5px; margin-top: 2px; }
  .br-field { display: flex; flex-direction: column; gap: 8px; margin-bottom: 20px; }
  .br-field label { font-size: 10.5px; font-weight: 600; letter-spacing: 0.1em; text-transform: uppercase; color: var(--gold); }
  .br-field input, .br-field textarea, .br-field select { background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.12); border-radius: 10px; padding: 14px 16px; color: #fff; font-family: 'Jost', sans-serif; font-size: 13.5px; outline: none; width: 100%; box-sizing: border-box; }
  .br-field select option { background: #1a1a1a; }
  .br-field input:focus, .br-field textarea:focus, .br-field select:focus { border-color: var(--gold); background: rgba(201,168,76,0.05); }
  .br-field input::placeholder, .br-field textarea::placeholder { color: rgba(255,255,255,0.35); }
  .br-field-hint { font-size: 11.5px; color: var(--white-60); }
  .br-field-error { font-size: 11.5px; color: #f3a3a3; }
  .br-row { display: grid; grid-template-columns: 1fr 1fr; gap: 18px; }
  .br-phone { display: grid; grid-template-columns: 90px 1fr; gap: 10px; }
  .br-lists { display: grid; grid-template-columns: 1fr 1fr; gap: 22px; font-size: 13.5px; color: var(--white-80); line-height: 1.75; }
  .br-lists h3 { font-size: 13px; margin: 0 0 6px; }
  .br-lists ul { margin: 0; padding-left: 18px; }
  .br-policy { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 20px; }
  .br-policy span { padding: 8px 12px; border-radius: 10px; font-size: 12.5px; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); }
  .br-summary { position: sticky; top: 100px; background: var(--dark-2); border: 1px solid rgba(201,168,76,0.25); border-radius: 22px; padding: 32px; color: #fff; }
  .br-summary-img { width: 100%; height: 150px; border-radius: 14px; margin-bottom: 18px; background: #f6f1e7; display: flex; align-items: center; justify-content: center; overflow: hidden; }
  .br-summary-img img { max-width: 100%; max-height: 100%; object-fit: contain; }
  .br-summary-tag { font-size: 11px; color: var(--gold); letter-spacing: 0.08em; text-transform: uppercase; margin-bottom: 8px; }
  .br-summary h3 { font-family: 'Cormorant Garamond', serif; font-size: 1.5rem; margin: 0 0 22px; }
  .br-line { display: flex; justify-content: space-between; gap: 10px; font-size: 13px; color: var(--white-80); padding: 10px 0; border-bottom: 1px dashed rgba(255,255,255,0.08); }
  .br-line span:first-child { color: var(--white-60); }
  .br-line.total { border-top: 1px solid rgba(255,255,255,0.12); border-bottom: none; margin-top: 6px; padding-top: 16px; font-weight: 700; font-size: 17px; color: #fff; }
  .br-line.total span:first-child { color: #fff; }
  .br-refund { font-size: 12.5px; color: var(--green); margin: 16px 0; padding: 10px 14px; background: rgba(74,222,128,0.06); border: 1px solid rgba(74,222,128,0.2); border-radius: 10px; }
  .br-accept { display: flex; gap: 10px; align-items: flex-start; font-size: 12.5px; color: var(--white-80); margin: 6px 0 18px; line-height: 1.5; }
  .br-accept input { accent-color: var(--gold); width: 18px; height: 18px; margin-top: 1px; flex-shrink: 0; }
  .br-submit { width: 100%; padding: 18px; border: none; border-radius: 100px; background: linear-gradient(90deg, #c9a84c, #e8c96b); color: var(--dark); font-family: 'Jost', sans-serif; font-size: 13px; font-weight: 800; letter-spacing: 0.1em; text-transform: uppercase; cursor: pointer; }
  .br-submit:disabled { opacity: .7; cursor: wait; }
  .br-note { font-size: 11.5px; color: var(--white-60); margin-top: 14px; line-height: 1.6; text-align: center; }
  .br-alert { max-width: 1052px; margin: 0 auto 20px; padding: 14px 18px; border-radius: 12px; background: rgba(220,80,80,0.08); border: 1px solid rgba(220,80,80,0.3); color: #f3a3a3; font-family: 'Jost', sans-serif; font-size: 13.5px; }
  /* TripJack-style checkout blocks */
  .ck-trip-head { font-size: 15px; font-weight: 600; color: #fff; margin-bottom: 18px; display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
  .ck-trip-head span[aria-hidden] { color: rgba(255,255,255,0.35); }
  .ck-type { margin-left: auto; font-size: 10px; font-weight: 600; letter-spacing: .1em; text-transform: uppercase; color: var(--gold-light); border: 1px solid rgba(201,168,76,0.4); padding: 3px 10px; border-radius: 100px; }
  .ck-route { display: grid; grid-template-columns: 1fr 80px 1fr; gap: 16px; align-items: center; font-size: 13.5px; color: #fff; }
  .ck-place { line-height: 1.5; }
  .ck-line { position: relative; height: 2px; border-top: 2px dashed rgba(201,168,76,0.45); }
  .ck-line::before, .ck-line::after { content: ''; position: absolute; top: -6px; width: 9px; height: 9px; border-radius: 50%; }
  .ck-line::before { left: -4px; border: 2px solid var(--gold); background: var(--dark-2); }
  .ck-line::after { right: -4px; background: var(--gold); }
  .ck-dates { display: flex; flex-wrap: wrap; gap: 8px 22px; margin-top: 18px; padding-top: 16px; border-top: 1px solid rgba(255,255,255,0.08); font-size: 13px; color: var(--white-80); }
  .ck-dates i { color: var(--gold); margin-right: 6px; }
  .ck-cab { display: grid; grid-template-columns: 150px 1fr auto; gap: 22px; align-items: start; }
  .ck-cab-img { position: relative; height: 112px; border-radius: 14px; background: #f6f1e7; display: flex; align-items: center; justify-content: center; overflow: hidden; }
  .ck-cab-img img { max-width: 92%; max-height: 80%; object-fit: contain; }
  .ck-cab-type { position: absolute; top: 8px; left: 8px; font-size: 9.5px; font-weight: 700; letter-spacing: .06em; color: #333; background: #fff; border: 1px solid #ddd; padding: 2px 8px; border-radius: 100px; }
  .br-wrap .ck-cab-name { font-family: 'Jost', sans-serif; font-size: 19px; font-weight: 600; color: #fff; margin: 0 0 2px; }
  .br-wrap .ck-cab-name::after { display: none; }
  .ck-chips { display: flex; flex-wrap: wrap; gap: 8px; margin: 12px 0 10px; }
  .ck-chips span { font-size: 12px; color: var(--white-80); border: 1px solid rgba(255,255,255,0.14); border-radius: 8px; padding: 5px 10px; }
  .ck-chips i { color: var(--gold); margin-right: 5px; }
  .ck-policy-btn { background: none; border: none; padding: 0; cursor: pointer; color: var(--gold-light); font-family: 'Jost', sans-serif; font-size: 13px; font-weight: 500; text-decoration: underline; text-underline-offset: 3px; }
  .ck-policy-btn:hover { color: #fff; }
  /* Policies pop-up */
  .ck-dialog { position: fixed; inset: 0; margin: auto; width: min(760px, 94vw); height: min(520px, 86vh); max-height: 86vh; padding: 0; border: 1px solid rgba(201,168,76,0.35); border-radius: 20px; background: #141414; color: #fff; box-shadow: 0 30px 80px rgba(0,0,0,0.6); font-family: 'Jost', sans-serif; }
  .ck-dialog::backdrop { background: rgba(0,0,0,0.7); backdrop-filter: blur(3px); }
  .ck-dialog[open] { display: flex; flex-direction: column; animation: ckPop .2s ease-out; }
  @keyframes ckPop { from { opacity: 0; transform: translateY(12px) scale(.98); } to { opacity: 1; transform: none; } }
  .ck-dialog-head { display: flex; justify-content: space-between; align-items: center; gap: 12px; padding: 20px 24px; border-bottom: 1px solid rgba(255,255,255,0.08); }
  .ck-dialog .ck-dialog-head h2 { font-family: 'Cormorant Garamond', serif; font-size: 1.5rem; font-weight: 600; color: var(--gold); margin: 0; }
  .ck-dialog-x { width: 34px; height: 34px; border-radius: 50%; background: #0d0d0d; border: 1px solid rgba(255,255,255,0.15); color: var(--gold); font-size: 20px; line-height: 1; cursor: pointer; flex-shrink: 0; }
  .ck-dialog-x:hover { border-color: var(--gold); }
  .ck-dialog-body { flex: 1; min-height: 0; display: grid; grid-template-columns: 200px 1fr; }
  .ck-tabs { display: flex; flex-direction: column; gap: 4px; padding: 16px 12px; border-right: 1px solid rgba(255,255,255,0.08); background: #101010; overflow-y: auto; }
  .ck-tab { display: flex; align-items: center; gap: 10px; width: 100%; text-align: left; background: none; border: 1px solid transparent; border-radius: 10px; padding: 11px 12px; color: var(--white-60); font-family: 'Jost', sans-serif; font-size: 13.5px; cursor: pointer; transition: background .15s, color .15s; }
  .ck-tab i { width: 16px; color: var(--gold); opacity: .7; }
  .ck-tab:hover { background: rgba(255,255,255,0.04); color: #fff; }
  .ck-tab.on { background: rgba(201,168,76,0.12); border-color: rgba(201,168,76,0.35); color: #fff; font-weight: 600; }
  .ck-tab.on i { opacity: 1; }
  .ck-panes { padding: 22px 26px; overflow-y: auto; scrollbar-width: thin; scrollbar-color: rgba(201,168,76,0.45) transparent; }
  .ck-panes::-webkit-scrollbar { width: 6px; }
  .ck-panes::-webkit-scrollbar-thumb { background: rgba(201,168,76,0.45); border-radius: 3px; }
  .ck-dialog .ck-pane h3 { font-family: 'Jost', sans-serif; font-size: 16px; font-weight: 600; color: var(--gold); margin: 0 0 14px; }
  .ck-cancel-rows { display: flex; flex-direction: column; gap: 10px; }
  .ck-cancel-row { display: flex; justify-content: space-between; gap: 14px; padding: 12px 14px; border-radius: 10px; background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.08); font-size: 13.5px; color: var(--white-80); }
  .ck-cancel-row b { white-space: nowrap; color: #fff; }
  .ck-cancel-row b.ok { color: var(--green); }
  .ck-cancel-row b.no { color: #f3a3a3; }
  .ck-pane-list { list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; gap: 10px; font-size: 13.5px; color: var(--white-80); line-height: 1.6; }
  .ck-pane-list li { position: relative; padding-left: 24px; }
  .ck-pane-list li::before { position: absolute; left: 0; top: 0; font-family: 'Font Awesome 6 Free'; font-weight: 900; font-size: 12px; content: '\f111'; transform: scale(.45); color: var(--gold); }
  .ck-pane-list.ck-inc li::before { content: '\f00c'; transform: none; color: var(--green); }
  .ck-pane-list.ck-exc li::before { content: '\f00d'; transform: none; color: #f3a3a3; }
  @media (max-width: 620px) {
    .ck-dialog { height: 86vh; }
    .ck-dialog-body { grid-template-columns: 1fr; grid-template-rows: auto 1fr; }
    .ck-tabs { flex-direction: row; overflow-x: auto; border-right: none; border-bottom: 1px solid rgba(255,255,255,0.08); padding: 10px; }
    .ck-tab { width: auto; white-space: nowrap; }
  }
  .ck-dialog-foot { padding: 16px 24px; border-top: 1px solid rgba(255,255,255,0.08); display: flex; justify-content: flex-end; }
  .ck-dialog-ok { width: auto; padding: 12px 34px; }
  .ck-cab-price { text-align: right; }
  .ck-cab-price strong { display: block; font-size: 22px; color: #fff; }
  .ck-cab-price span { font-size: 11.5px; color: var(--white-60); }
  .ck-info { display: flex; gap: 10px; align-items: flex-start; font-size: 12.5px; color: var(--white-80); background: rgba(201,168,76,0.07); border: 1px solid rgba(201,168,76,0.2); border-radius: 10px; padding: 11px 14px; margin: -6px 0 22px; }
  .ck-info i { color: var(--gold); margin-top: 2px; }
  .ck-saved { max-width: 50%; }
  .ck-req { color: #f3a3a3; }
  .ck-opt { color: var(--white-60); font-weight: 400; text-transform: none; letter-spacing: 0; }
  .ck-save { margin: 0; }
  .ck-steps ol { list-style: none; margin: 0; padding: 0; display: grid; grid-template-columns: repeat(3, 1fr); gap: 18px; position: relative; }
  .ck-steps li { position: relative; }
  .ck-step { display: inline-block; font-size: 11px; font-weight: 600; color: var(--gold-light); background: rgba(201,168,76,0.1); border: 1px solid rgba(201,168,76,0.35); border-radius: 6px; padding: 4px 10px; margin-bottom: 14px; position: relative; z-index: 1; }
  .ck-steps li:not(:last-child)::after { content: ''; position: absolute; top: 13px; left: 70px; right: -18px; height: 1px; background: rgba(201,168,76,0.3); }
  .br-wrap .ck-steps h3 { font-family: 'Jost', sans-serif; font-size: 15px; font-weight: 600; color: #fff; margin: 0 0 6px; }
  .ck-steps p { font-size: 12.5px; color: var(--white-60); line-height: 1.6; margin: 0; }
  .ck-confirm { margin: 0 0 18px; }
  .ck-pay-row { display: flex; justify-content: flex-end; }
  .ck-pay { width: auto; padding: 16px 44px; }
  .br-summary h3.ck-sum-title { font-family: 'Jost', sans-serif; font-size: 18px; font-weight: 600; margin: 0 0 14px; color: #fff; }
  .br-line.ck-strong span { color: #fff !important; font-weight: 600; }
  .br-line.ck-sub { padding: 6px 0 6px 12px; font-size: 12.5px; border-bottom: none; }
  @media (max-width: 700px) {
    .ck-cab { grid-template-columns: 1fr; }
    .ck-cab-price { text-align: left; }
    .ck-route { grid-template-columns: 1fr; }
    .ck-line { display: none; }
    .ck-steps ol { grid-template-columns: 1fr; }
    .ck-steps li::after { display: none; }
    .ck-saved { max-width: none; }
    .ck-pay { width: 100%; }
  }
  @media (max-width: 900px) { .br-wrap { grid-template-columns: 1fr; gap: 24px; } .br-summary { position: static; order: -1; } }
  @media (max-width: 560px) {
    .br-hero { padding-top: 90px; }
    .br-section { padding: 22px 18px; border-radius: 16px; }
    .br-trip, .br-row, .br-lists { grid-template-columns: 1fr; }
    .br-summary { padding: 22px; }
  }
</style>
@endpush

@section('content')
<div class="br-hero">
  <a href="{{ url()->previous() !== url()->current() ? url()->previous() : route('cabs') }}" class="br-back">&larr; Back to cabs</a>
  <p class="br-eyebrow">Review your cab</p>
  <h1 class="br-title">{{ $option['label'] }} · {{ \App\Services\CabBookingService::JOURNEY_TYPES[$search['journeyType']] ?? 'Cab' }}</h1>
  <p class="br-sub">{{ \App\Services\CabBookingService::shortPlace($from) }} → {{ \App\Services\CabBookingService::shortPlace($to) }} · {{ $pickup->format('D, j M, g:i A') }} · {{ $search['passengers'] }} {{ $search['passengers'] == 1 ? 'passenger' : 'passengers' }}</p>
</div>

@if(session('booking_error'))
  <div class="br-alert" role="alert">{{ session('booking_error') }}</div>
@endif
@if(! $configured)
  <div class="br-alert" role="alert">{{ \App\Support\CabSettings::disabledMessage() }}</div>
@endif

<form method="POST" action="{{ route('cabs.book') }}" class="br-wrap" id="cabBookForm" novalidate>
  @csrf
  <input type="hidden" name="search" value="{{ $searchId }}">
  <input type="hidden" name="cab" value="{{ $option['key'] }}">

  <div>
    {{-- Trip: pickup ●┄┄● drop, with the date --}}
    <section class="br-section ck-trip">
      <div class="ck-trip-head">Cab Booking <span aria-hidden="true">|</span> {{ $return ? 'Round Trip' : 'One Way' }} <span class="ck-type">{{ \App\Services\CabBookingService::JOURNEY_TYPES[$search['journeyType']] ?? 'Cab' }}</span></div>
      <div class="ck-route">
        <div class="ck-end"><div class="br-k">Pickup</div><div class="ck-place">{{ $from }}</div></div>
        <div class="ck-line" aria-hidden="true"><span></span></div>
        <div class="ck-end"><div class="br-k">Drop</div><div class="ck-place">{{ $to }}</div></div>
      </div>
      <div class="ck-dates">
        <span><i class="fa-regular fa-calendar" aria-hidden="true"></i> {{ $pickup->format('j M Y | g:i A') }}</span>
        @if($return)<span><i class="fa-solid fa-rotate-left" aria-hidden="true"></i> Return {{ $return->format('j M Y | g:i A') }}</span>@endif
        @if(!empty($search['passengers']))<span><i class="fa-solid fa-user" aria-hidden="true"></i> {{ $search['passengers'] }} {{ $search['passengers'] == 1 ? 'passenger' : 'passengers' }}</span>@endif
      </div>
    </section>

    {{-- The chosen cab --}}
    <section class="br-section ck-cab">
      <div class="ck-cab-img">
        @if($option['vehicleType'])<span class="ck-cab-type">{{ strtoupper($option['vehicleType']) }}</span>@endif
        @if($option['image'])<img src="{{ $option['image'] }}" alt="{{ $option['label'] }}" referrerpolicy="no-referrer">@endif
      </div>
      <div class="ck-cab-info">
        <h2 class="ck-cab-name">{{ $option['label'] }}</h2>
        @if($option['similar'])<div class="br-v-sub">{{ $option['similar'] }}</div>@endif
        <div class="ck-chips">
          <span><i class="fa-solid fa-user" aria-hidden="true"></i> {{ $option['seats'] }} Seats</span>
          <span><i class="fa-solid fa-suitcase-rolling" aria-hidden="true"></i> {{ $option['bags'] }} Bags</span>
          @if(!empty($p['waitingTime']))<span><i class="fa-regular fa-clock" aria-hidden="true"></i> {{ $p['waitingTime'] }}</span>@endif
        </div>
        @if($p)
          <button type="button" class="ck-policy-btn" data-open-policies aria-haspopup="dialog">View policies</button>
          <dialog class="ck-dialog" id="ckPolicies" aria-labelledby="ckPoliciesTitle">
            <div class="ck-dialog-head">
              <h2 id="ckPoliciesTitle">{{ $option['label'] }} · Policies</h2>
              <button type="button" class="ck-dialog-x" data-close-policies aria-label="Close">&times;</button>
            </div>
            @php
              $policyTabs = array_filter([
                  'cancel' => ! empty($p['cancellationPolicy']) ? ['Cancellation', 'fa-rotate-left'] : null,
                  'inc' => ! empty($p['inclusions']) ? ['Included', 'fa-circle-check'] : null,
                  'exc' => ! empty($p['exclusions']) ? ['Not included', 'fa-circle-xmark'] : null,
                  'bag' => ! empty($p['baggagePolicy']) ? ['Luggage', 'fa-suitcase-rolling'] : null,
                  'terms' => ! empty($p['termsAndPolicies']) ? ['Terms', 'fa-file-lines'] : null,
              ]);
              $firstTab = array_key_first($policyTabs);
            @endphp
            <div class="ck-dialog-body">
              <nav class="ck-tabs" role="tablist" aria-orientation="vertical" aria-label="Policy sections">
                @foreach($policyTabs as $key => [$label, $icon])
                  <button type="button" role="tab" id="ckTab-{{ $key }}" aria-controls="ckPane-{{ $key }}" aria-selected="{{ $key === $firstTab ? 'true' : 'false' }}" class="ck-tab {{ $key === $firstTab ? 'on' : '' }}" data-tab="{{ $key }}">
                    <i class="fa-solid {{ $icon }}" aria-hidden="true"></i> {{ $label }}
                  </button>
                @endforeach
              </nav>
              <div class="ck-panes">
                @if(isset($policyTabs['cancel']))
                  <div class="ck-pane" id="ckPane-cancel" role="tabpanel" aria-labelledby="ckTab-cancel" @if($firstTab !== 'cancel') hidden @endif>
                    <h3>Cancellation</h3>
                    <div class="ck-cancel-rows">
                      @foreach(collect($p['cancellationPolicy'])->sortByDesc('minHours') as $c)
                        @php $pct = (float) ($c['refundPercentage'] ?? 0); @endphp
                        <div class="ck-cancel-row"><span>{{ $c['description'] ?? '' }}</span><b class="{{ $pct >= 100 ? 'ok' : ($pct <= 0 ? 'no' : '') }}">{{ rtrim(rtrim(number_format($pct, 2), '0'), '.') }}% refund</b></div>
                      @endforeach
                    </div>
                  </div>
                @endif
                @foreach(['inc' => 'inclusions', 'exc' => 'exclusions', 'bag' => 'baggagePolicy', 'terms' => 'termsAndPolicies'] as $key => $field)
                  @if(isset($policyTabs[$key]))
                    <div class="ck-pane" id="ckPane-{{ $key }}" role="tabpanel" aria-labelledby="ckTab-{{ $key }}" @if($firstTab !== $key) hidden @endif>
                      <h3>{{ $policyTabs[$key][0] }}</h3>
                      <ul class="ck-pane-list ck-{{ $key }}">@foreach($p[$field] as $x)<li>{{ $x }}</li>@endforeach</ul>
                    </div>
                  @endif
                @endforeach
              </div>
            </div>
            <div class="ck-dialog-foot"><button type="button" class="br-submit ck-dialog-ok" data-close-policies>Got it</button></div>
          </dialog>
        @endif
      </div>
      <div class="ck-cab-price">
        <strong>&#8377;{{ number_format($price['total']) }}</strong>
        <span>All-inclusive</span>
      </div>
    </section>

    {{-- Primary traveller --}}
    <section class="br-section">
      <h2>Primary Traveller Details</h2>
      <p class="ck-info"><i class="fa-solid fa-circle-info" aria-hidden="true"></i> We will use your email and phone number to share the cab and driver details before your trip.</p>

      @if($travellers->isNotEmpty())
        <div class="br-field ck-saved">
          <label for="cbSaved">Choose from your travellers list</label>
          <select id="cbSaved">
            <option value="">Search for travellers list…</option>
            @foreach($travellers as $t)
              <option value="{{ $t->id }}" data-first="{{ $t->first_name }}" data-last="{{ $t->last_name }}" data-email="{{ $t->email }}" data-phone="{{ $t->phone }}">{{ trim($t->first_name.' '.$t->last_name) }}</option>
            @endforeach
          </select>
        </div>
      @endif

      <div class="br-row">
        <div class="br-field">
          <label for="cbFirst">First name <span class="ck-req">*</span></label>
          <input id="cbFirst" name="first_name" value="{{ old('first_name', $firstName) }}" autocomplete="given-name" required maxlength="50">
          @error('first_name')<span class="br-field-error">{{ $message }}</span>@enderror
        </div>
        <div class="br-field">
          <label for="cbLast">Last name <span class="ck-req">*</span></label>
          <input id="cbLast" name="last_name" value="{{ old('last_name', $user->last_name) }}" autocomplete="family-name" required maxlength="50">
          @error('last_name')<span class="br-field-error">{{ $message }}</span>@enderror
        </div>
        <div class="br-field">
          <label for="cbEmail">Email address <span class="ck-req">*</span></label>
          <input id="cbEmail" type="email" name="email" value="{{ old('email', $user->email) }}" autocomplete="email" required>
          @error('email')<span class="br-field-error">{{ $message }}</span>@enderror
        </div>
        <div class="br-field">
          <label for="cbPhone">Phone number <span class="ck-req">*</span></label>
          <div class="br-phone">
            <select name="phone_code" aria-label="Country code">
              @foreach(['91' => '🇮🇳 +91', '971' => '🇦🇪 +971', '1' => '🇺🇸 +1', '44' => '🇬🇧 +44', '65' => '🇸🇬 +65', '61' => '🇦🇺 +61', '966' => '🇸🇦 +966', '974' => '🇶🇦 +974'] as $code => $label)
                <option value="{{ $code }}" @selected(old('phone_code', '91') == $code)>{{ $label }}</option>
              @endforeach
            </select>
            <input id="cbPhone" type="tel" name="phone" value="{{ old('phone', $user->phone) }}" autocomplete="tel-national" inputmode="tel" required>
          </div>
          @error('phone')<span class="br-field-error">{{ $message }}</span>@enderror
        </div>
        @if($isAirport)
          <div class="br-field">
            <label for="cbFlight">Flight number <span class="ck-req">*</span></label>
            <input id="cbFlight" name="flight_number" value="{{ old('flight_number') }}" placeholder="Enter flight number, e.g. 6E 2134" maxlength="10" required>
            @error('flight_number')<span class="br-field-error">{{ $message }}</span>@enderror
          </div>
        @endif
        <div class="br-field">
          <label for="cbRequest">Special service request <span class="ck-opt">(optional)</span></label>
          <input id="cbRequest" name="request" value="{{ old('request') }}" placeholder="Enter request, e.g. child seat" maxlength="300">
        </div>
      </div>
      <label class="br-accept ck-save">
        <input type="checkbox" name="save_traveller" value="1" @checked(old('save_traveller'))>
        <span>Add this to my travellers list <span class="ck-opt">(for faster bookings next time)</span></span>
      </label>
    </section>

    {{-- What happens next --}}
    <section class="br-section ck-steps" aria-label="What happens next">
      <ol>
        <li><span class="ck-step">Step 1</span><h3>Booking Confirmed</h3><p>You'll receive your booking confirmation right after the payment.</p></li>
        <li><span class="ck-step">Step 2</span><h3>Cab &amp; Driver Details</h3><p>We'll share your cab and driver details before your trip.</p></li>
        <li><span class="ck-step">Step 3</span><h3>On-Time Pickup</h3><p>Your cab will arrive at the pickup point at the scheduled time. Enjoy your trip!</p></li>
      </ol>
    </section>

    <label class="br-accept ck-confirm">
      <input type="checkbox" name="accept" value="1" @checked(old('accept')) required>
      <span>I confirm that the booking details are accurate, the traveller has agreed to this booking, the cab's policies above and the <a href="{{ route('terms') }}" target="_blank" style="color:var(--gold-light)">Terms &amp; Conditions</a>, and I authorise TYT Luxe to share the traveller's email and phone number with the cab operator for cab and driver details.</span>
    </label>
    @error('accept')<p class="br-field-error" style="margin:-8px 0 12px;">{{ $message }}</p>@enderror
    <div class="ck-pay-row">
      <button type="submit" class="br-submit ck-pay" @disabled(! $configured)>Pay Now &rarr;</button>
    </div>
  </div>

  <aside class="br-summary">
    <h3 class="ck-sum-title">Price Breakup</h3>
    @php $fare = $price['supplier'] + $price['markup']; @endphp
    <div class="br-line ck-strong"><span>Cab fare</span><span>&#8377;{{ number_format($fare, 2) }}</span></div>
    @if($return)
      <div class="br-line ck-sub"><span>Forward trip</span><span>&#8377;{{ number_format($fare / 2, 2) }}</span></div>
      <div class="br-line ck-sub"><span>Return trip</span><span>&#8377;{{ number_format($fare / 2, 2) }}</span></div>
    @else
      <div class="br-line ck-sub"><span>Forward trip</span><span>&#8377;{{ number_format($fare, 2) }}</span></div>
    @endif
    <div class="br-line ck-strong"><span>Other fees</span><span>&#8377;{{ number_format($price['gst'] + $price['gateway'], 2) }}</span></div>
    <div class="br-line ck-sub"><span>GST</span><span>&#8377;{{ number_format($price['gst'], 2) }}</span></div>
    <div class="br-line ck-sub"><span>Online payment charge</span><span>&#8377;{{ number_format($price['gateway'], 2) }}</span></div>
    <div class="br-line total"><span>Total amount</span><span>&#8377;{{ number_format($price['total'], 2) }}</span></div>
    @if($freeUntil && $freeUntil->isFuture())
      <div class="br-refund">✓ Free cancellation until {{ $freeUntil->format('D, j M, g:i A') }}</div>
    @endif
    <p class="br-note">Secure payment by Razorpay. If the cab can't be confirmed after you pay, you get a full refund automatically.</p>
  </aside>
</form>
@endsection

@push('scripts')
<script>
  // Policies pop-up (closes on ×, "Got it", Esc or a click outside).
  var policies = document.getElementById('ckPolicies');
  if (policies) {
    document.querySelectorAll('[data-open-policies]').forEach(function (b) { b.addEventListener('click', function () { policies.showModal(); }); });
    policies.querySelectorAll('[data-close-policies]').forEach(function (b) { b.addEventListener('click', function () { policies.close(); }); });
    policies.addEventListener('click', function (e) { if (e.target === policies) policies.close(); });
    // Sidebar: one section at a time.
    policies.querySelectorAll('.ck-tab').forEach(function (tab) {
      tab.addEventListener('click', function () {
        policies.querySelectorAll('.ck-tab').forEach(function (t) {
          var on = t === tab;
          t.classList.toggle('on', on);
          t.setAttribute('aria-selected', on ? 'true' : 'false');
          document.getElementById('ckPane-' + t.dataset.tab).hidden = !on;
        });
      });
    });
  }

  // Pick a saved traveller: fills the name, email and phone.
  var saved = document.getElementById('cbSaved');
  if (saved) saved.addEventListener('change', function () {
    var o = saved.selectedOptions[0];
    if (!o || !o.value) return;
    document.getElementById('cbFirst').value = o.dataset.first || '';
    document.getElementById('cbLast').value = o.dataset.last || '';
    if (o.dataset.email) document.getElementById('cbEmail').value = o.dataset.email;
    var phone = (o.dataset.phone || '').replace(/[^\d+]/g, '');
    if (phone) {
      var codeSel = document.querySelector('select[name="phone_code"]');
      var match = Array.prototype.find.call(codeSel.options, function (opt) { return phone.indexOf('+' + opt.value) === 0; });
      if (match) { codeSel.value = match.value; phone = phone.slice(match.value.length + 1); }
      document.getElementById('cbPhone').value = phone.replace(/^\+/, '');
    }
  });

  document.getElementById('cabBookForm').addEventListener('submit', function () {
    var b = this.querySelector('.br-submit');
    setTimeout(function () { b.disabled = true; b.textContent = 'Please wait…'; }, 0);
  });
</script>
@endpush
