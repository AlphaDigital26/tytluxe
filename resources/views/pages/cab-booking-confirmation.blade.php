@extends('layouts.frontend')

@section('meta_title', 'Cab Booking '.$booking->reference.' | TYT Luxe')

@php
  $d = $booking->cab_details ?? [];
  $o = $d['option'] ?? [];
  $live = $d['live'] ?? [];
  $type = \App\Services\CabBookingService::JOURNEY_TYPES[$d['journeyType'] ?? ''] ?? 'Cab';
  $cancelling = $booking->status === 'confirmed' && $booking->cancellation_requested_at !== null;
  $ride = strtoupper((string) ($live['rideStatus'] ?? ''));
  $completed = in_array($ride, ['COMPLETED', 'ENDED', 'FINISHED'], true);
  $onTrip = in_array($ride, ['STARTED', 'ONGOING', 'IN_PROGRESS', 'ARRIVED', 'DRIVER_ASSIGNED', 'ON_THE_WAY'], true);
  [$eyebrow, $title, $sub, $tone] = match (true) {
      $cancelling => ['Cancellation in progress', 'We\'re cancelling your cab', $booking->guestCancellationMessage(), 'gold'],
      $booking->status === 'confirmed' => ['Booking confirmed', 'Your cab is booked', 'Confirmation sent to '.$booking->guest_email.'. Your driver\'s details are shared before pickup.', 'green'],
      $booking->status === 'failed_needs_review' => ['Payment received', 'We\'re confirming your cab', 'Your payment went through and our team is confirming your cab with the operator. We\'ll email you shortly — there\'s no need to pay again.', 'gold'],
      $booking->status === 'refunded' => ['Not booked — refunded', 'We couldn\'t book this cab', 'The cab could not be confirmed after your payment, so we refunded you in full. It can take 5–7 business days to show in your account.', 'red'],
      $booking->status === 'cancelled' => ['Cancelled', 'This booking is cancelled', $booking->guestCancellationMessage(), 'red'],
      default => ['Awaiting payment', 'Complete your payment', 'Your cab isn\'t booked yet. Complete the payment to confirm it.', 'gold'],
  };
  $steps = [
      ['Paid', $booking->hasInvoice()],
      ['Confirmed', $booking->status === 'confirmed'],
      ['Driver on the way', $onTrip || $completed],
      ['Trip completed', $completed],
  ];
@endphp

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600&family=Jost:wght@300;400;500;600;700;800&display=swap" rel="stylesheet" />
<style>
  body { background: #0d0d0d; }
  .cb-wrap { max-width: 900px; margin: 0 auto; padding: 110px 24px 80px; font-family: 'Jost', sans-serif; color: #fff; display: flex; flex-direction: column; gap: 24px; }
  .cb-head { text-align: center; display: flex; flex-direction: column; align-items: center; gap: 10px; }
  .cb-icon { width: 64px; height: 64px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 26px; }
  .cb-icon.green { border: 1px solid rgba(74,222,128,0.4); background: rgba(74,222,128,0.08); color: #4ade80; }
  .cb-icon.gold { border: 1px solid rgba(201,168,76,0.45); background: rgba(201,168,76,0.08); color: #e8c96b; }
  .cb-icon.red { border: 1px solid rgba(243,163,163,0.4); background: rgba(220,80,80,0.08); color: #f3a3a3; }
  .cb-eyebrow { font-size: 10.5px; font-weight: 600; letter-spacing: 0.22em; text-transform: uppercase; color: #c9a84c; margin: 0; }
  /* cab-heading-fix: the site's global h1–h3 colour is dark */
  .cb-wrap h1, .cb-wrap h2 { line-height: 1.2; }
  .cb-wrap h1.cb-title { color: #fff; }
  .cb-title { margin: 0; font-family: 'Cormorant Garamond', serif; font-size: clamp(2rem, 4vw, 2.6rem); font-weight: 500; }
  .cb-sub { margin: 0; max-width: 620px; font-size: 14px; font-weight: 300; color: rgba(255,255,255,0.7); line-height: 1.6; }
  .cb-ref { font-size: 13px; color: rgba(255,255,255,0.6); }
  .cb-ref strong { color: #fff; }
  .cb-flash { padding: 14px 18px; border-radius: 12px; font-size: 13.5px; }
  .cb-flash.ok { background: rgba(74,222,128,0.06); border: 1px solid rgba(74,222,128,0.25); color: #86efac; }
  .cb-flash.err { background: rgba(220,80,80,0.08); border: 1px solid rgba(220,80,80,0.3); color: #f3a3a3; }
  .cb-section { background: #141414; border: 1px solid rgba(255,255,255,0.08); border-radius: 20px; padding: 30px 34px; }
  .cb-wrap .cb-section h2 { margin: 0 0 22px; font-family: 'Cormorant Garamond', serif; font-size: 1.5rem; font-weight: 600; color: #c9a84c; display: flex; align-items: center; gap: 14px; }
  .cb-section h2::after { content: ''; flex: 1; height: 1px; background: rgba(201,168,76,0.15); }
  .cb-steps { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 10px; font-size: 13px; list-style: none; margin: 0; padding: 0; }
  .cb-steps li { display: flex; flex-direction: column; gap: 8px; color: rgba(255,255,255,0.6); }
  .cb-steps li::before { content: ''; height: 4px; border-radius: 2px; background: rgba(255,255,255,0.12); }
  .cb-steps li.done { color: #fff; font-weight: 500; }
  .cb-steps li.done::before { background: #c9a84c; }
  .cb-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 22px; font-size: 14px; }
  .cb-k { font-size: 10.5px; font-weight: 600; letter-spacing: 0.1em; text-transform: uppercase; color: #c9a84c; margin-bottom: 6px; }
  .cb-v-sub { color: rgba(255,255,255,0.6); font-size: 12.5px; margin-top: 2px; }
  .cb-note { grid-column: 1 / -1; padding: 14px 16px; border-radius: 12px; background: rgba(201,168,76,0.07); border: 1px solid rgba(201,168,76,0.22); font-size: 13px; color: rgba(255,255,255,0.8); }
  .cb-actions { display: flex; gap: 12px; flex-wrap: wrap; justify-content: center; }
  .cb-btn { padding: 14px 28px; border-radius: 100px; font-size: 12px; font-weight: 700; letter-spacing: 0.1em; text-transform: uppercase; text-decoration: none; }
  .cb-btn.gold { background: linear-gradient(90deg, #c9a84c, #e8c96b); color: #0d0d0d; font-weight: 800; }
  .cb-btn.line { border: 1.5px solid rgba(201,168,76,0.5); color: #c9a84c; }
  .cb-btn.red { border: 1.5px solid rgba(243,163,163,0.5); color: #f3a3a3; }
  .cb-foot { text-align: center; font-size: 12px; color: rgba(255,255,255,0.6); margin: 0; }
  @media (max-width: 600px) {
    .cb-wrap { padding-top: 90px; }
    .cb-section { padding: 22px 18px; }
    .cb-grid { grid-template-columns: 1fr; }
    .cb-steps { grid-template-columns: repeat(2, minmax(0, 1fr)); }
  }
</style>
@endpush

@section('content')
<div class="cb-wrap">
  @if(session('booking_success'))<div class="cb-flash ok" role="status">{{ session('booking_success') }}</div>@endif
  @if(session('booking_error'))<div class="cb-flash err" role="alert">{{ session('booking_error') }}</div>@endif

  <div class="cb-head">
    <div class="cb-icon {{ $tone }}" aria-hidden="true">{{ $tone === 'green' ? '✓' : ($tone === 'red' ? '!' : '…') }}</div>
    <p class="cb-eyebrow">{{ $eyebrow }}</p>
    <h1 class="cb-title">{{ $title }}</h1>
    @if($sub)<p class="cb-sub">{{ $sub }}</p>@endif
    <div class="cb-ref">Booking ID <strong>{{ $booking->reference }}</strong></div>
  </div>

  @if($booking->status === 'confirmed' && ! $cancelling)
    <section class="cb-section">
      <h2>Status</h2>
      <ol class="cb-steps">
        @foreach($steps as [$label, $done])
          <li class="{{ $done ? 'done' : '' }}">{{ $label }}</li>
        @endforeach
      </ol>
    </section>
  @endif

  <section class="cb-section">
    <h2>Trip Details</h2>
    <div class="cb-grid">
      <div><div class="cb-k">Pickup</div>{{ $d['from'] ?? '' }}<div class="cb-v-sub">{{ $booking->cab_pickup_at?->format('D, j M Y, g:i A') }}@if(!empty($d['passenger']['flightNumber'])) · Flight {{ strtoupper($d['passenger']['flightNumber']) }}@endif</div></div>
      <div><div class="cb-k">Drop</div>{{ $d['to'] ?? '' }}<div class="cb-v-sub">@if(!empty($d['returnAt']))Return {{ \Illuminate\Support\Carbon::parse($d['returnAt'])->format('D, j M Y, g:i A') }}@else{{ $type }} · One way @endif</div></div>
      <div><div class="cb-k">Vehicle</div>{{ $o['label'] ?? 'Cab' }}<div class="cb-v-sub">{{ $o['similar'] ?? '' }}</div></div>
      <div><div class="cb-k">Passenger</div>{{ $booking->lead_guest_name }} · {{ $booking->pax_adults }} {{ $booking->pax_adults == 1 ? 'traveller' : 'travellers' }}<div class="cb-v-sub">Amount paid &#8377;{{ number_format((float) $booking->total_amount, 2) }}</div></div>
      @if($booking->status === 'confirmed' && ! $cancelling)
        <div class="cb-note">
          @if(($d['journeyType'] ?? '') === 'airport_transfer')Your driver will meet you at the arrivals gate with a name placard. @endif
          Need help? Call or WhatsApp us on +91 98750 73788{{ !empty($live['helpline']) ? ', or the operator\'s 24/7 helpline in your confirmation email' : '' }}.
        </div>
      @endif
    </div>
  </section>

  <div class="cb-actions">
    @if(in_array($booking->status, ['pending_payment', 'payment_failed'], true))
      <a href="{{ route('hotel.payment.show', $booking->reference) }}" class="cb-btn gold">Complete Payment</a>
    @endif
    @if($booking->status === 'confirmed' && ! $cancelling && !empty($live['trackingLink']))
      <a href="{{ $live['trackingLink'] }}" class="cb-btn gold" target="_blank" rel="noopener noreferrer">Track Driver</a>
    @endif
    @if($booking->hasInvoice())
      <a href="{{ route('hotel.booking.invoice', $booking->reference) }}" class="cb-btn line">Download Invoice</a>
    @endif
    @if($canCancel)
      <a href="{{ route('hotel.booking.cancel.show', $booking->reference) }}" class="cb-btn red">Cancel Booking</a>
    @endif
    <a href="{{ route('history') }}" class="cb-btn line">My Trips</a>
  </div>
</div>
@endsection
