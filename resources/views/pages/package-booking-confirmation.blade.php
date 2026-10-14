@extends('layouts.frontend')

@section('meta_title', 'Booking ' . $booking->reference . ' — ' . $booking->package->title . ' | TYT Luxe')

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com" />
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,500;0,600&family=Jost:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
<style>
  :root {
    --gold: #c9a84c; --gold-light: #e8c96b; --gold-dim: rgba(201,168,76,0.12);
    --dark: #0d0d0d; --dark-2: #141414; --dark-3: #1b1b1b;
    --white-80: rgba(255,255,255,0.80); --white-60: rgba(255,255,255,0.60); --white-10: rgba(255,255,255,0.10);
  }
  body { background: var(--dark); }
  .pb-wrap { max-width: 820px; margin: 0 auto; padding: 110px 24px 90px; font-family: 'Jost', sans-serif; color: var(--white-80); }
  .pb-hero { text-align: center; margin-bottom: 30px; }
  .pb-icon { width: 64px; height: 64px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 26px; margin-bottom: 16px; }
  .pb-icon.ok { background: rgba(80,200,120,0.12); color: #7ee2a2; }
  .pb-icon.warn { background: var(--gold-dim); color: var(--gold); }
  .pb-icon.bad { background: rgba(220,80,80,0.12); color: #f3a3a3; }
  .pb-title { font-family: 'Cormorant Garamond', serif; font-size: 2.2rem; color: #fff; margin: 0 0 8px; }
  .pb-sub { font-size: 14px; color: var(--white-60); margin: 0; line-height: 1.6; }
  .pb-ref { display: inline-block; margin-top: 12px; padding: 4px 14px; border-radius: 100px; background: var(--dark-3); font-size: 12px; letter-spacing: 0.08em; color: var(--gold); }
  .pb-card { background: var(--dark-2); border: 1px solid var(--white-10); border-radius: 18px; padding: 24px 26px; margin-bottom: 20px; }
  .pb-card h2 { font-family: 'Cormorant Garamond', serif; font-size: 1.45rem; color: #fff; margin: 0 0 14px; }
  .pb-row { display: flex; justify-content: space-between; gap: 16px; font-size: 13.5px; padding: 7px 0; border-bottom: 1px solid var(--white-10); }
  .pb-row:last-child { border-bottom: none; }
  .pb-row span { color: var(--white-60); }
  .pb-row strong { color: #fff; font-weight: 500; text-align: right; }
  .pb-balance { border-color: rgba(201,168,76,0.45); background: linear-gradient(135deg, rgba(201,168,76,0.10), var(--dark-2)); }
  .pb-balance .pb-amount { font-family: 'Cormorant Garamond', serif; font-size: 2rem; color: var(--gold-light); }
  .pb-btn { display: inline-block; padding: 14px 28px; border-radius: 100px; background: linear-gradient(90deg, #c9a84c, #e8c96b); color: var(--dark); font-size: 12.5px; font-weight: 800; letter-spacing: 0.1em; text-transform: uppercase; text-decoration: none; margin-top: 14px; }
  .pb-btn-outline { display: inline-block; padding: 12px 22px; border-radius: 100px; border: 1px solid rgba(255,255,255,0.25); color: #fff; font-size: 12.5px; font-weight: 600; text-decoration: none; margin: 6px 8px 0 0; }
  .pb-btn-outline:hover { border-color: var(--gold); color: var(--gold); }
  .pb-btn-danger { color: #f3a3a3; border-color: rgba(220,80,80,0.4); }
  .pb-alert { padding: 14px 18px; border-radius: 12px; font-size: 13.5px; margin-bottom: 20px; line-height: 1.6; }
  .pb-alert.ok { background: rgba(80,200,120,0.08); border: 1px solid rgba(80,200,120,0.3); color: #a6e9bf; }
  .pb-alert.bad { background: rgba(220,80,80,0.08); border: 1px solid rgba(220,80,80,0.3); color: #f3a3a3; }
  .pb-alert.info { background: var(--gold-dim); border: 1px solid rgba(201,168,76,0.3); color: var(--gold-light); }
  .pb-note { font-size: 12.5px; color: var(--white-60); line-height: 1.6; margin: 10px 0 0; }
  @media (max-width: 560px) {
    .pb-wrap { padding: 92px 16px 60px; }
    .pb-title { font-size: 1.8rem; }
    .pb-card { padding: 20px 16px; }
  }
</style>
@endpush

@section('content')
@php
  $dep = $booking->packageDeparture;
  $start = \Carbon\Carbon::parse($booking->check_in);
  $end = $booking->check_out ? \Carbon\Carbon::parse($booking->check_out) : null;
  $paidPayments = $booking->payments->whereIn('status', \App\Models\Booking::PAID_PAYMENT_STATUSES)->sortBy('id');
  $paidTotal = (float) $paidPayments->sum('amount');
  $refunded = (float) $paidPayments->sum('refund_amount');
  $cancelling = $booking->status === 'confirmed' && $booking->cancellation_requested_at !== null;
  [$icon, $iconClass, $heading, $sub] = match (true) {
      $cancelling => ['fa-hourglass-half', 'warn', 'Cancellation requested', ''],
      $booking->status === 'confirmed' && $balanceCharge !== null => ['fa-check', 'ok', 'Your seats are booked!', 'Booking amount received. Please pay the balance by '.$booking->package_balance_due_on?->format('d M Y').'.'],
      $booking->status === 'confirmed' => ['fa-check', 'ok', 'Your trip is confirmed!', 'Paid in full. Our team will be in touch with your trip details before departure.'],
      $booking->status === 'cancelled' => ['fa-xmark', 'bad', 'Booking cancelled', ''],
      $booking->status === 'refunded' => ['fa-rotate-left', 'bad', 'Seats not available — refunded', 'The seats were taken before your payment went through, so we refunded you in full. It can take 5–7 business days to show in your account.'],
      $booking->status === 'failed_needs_review' => ['fa-hourglass-half', 'warn', 'We are looking into your booking', 'Your payment was received but needs a quick check by our team. We will contact you shortly.'],
      default => ['fa-credit-card', 'warn', 'Payment not completed', 'Your seats are not booked yet.'],
  };
@endphp

<div class="pb-wrap">
  @if(session('booking_success'))<div class="pb-alert ok" role="status">{{ session('booking_success') }}</div>@endif
  @if(session('booking_error'))<div class="pb-alert bad" role="alert">{{ session('booking_error') }}</div>@endif

  <div class="pb-hero">
    <div class="pb-icon {{ $iconClass }}"><i class="fa-solid {{ $icon }}"></i></div>
    <h1 class="pb-title">{{ $heading }}</h1>
    @if($sub)<p class="pb-sub">{{ $sub }}</p>@endif
    @if($msg = $booking->guestCancellationMessage())<p class="pb-sub" style="margin-top:8px;">{{ $msg }}</p>@endif
    <span class="pb-ref">BOOKING NO. {{ $booking->reference }}</span>
  </div>

  @if(in_array($booking->status, ['pending_payment', 'payment_failed'], true))
    <div class="pb-card" style="text-align:center;">
      <p class="pb-sub">Complete your payment to book the seats.</p>
      <a href="{{ route('hotel.payment.show', $booking->reference) }}" class="pb-btn">Complete Payment</a>
    </div>
  @endif

  @if($balanceCharge)
    <div class="pb-card pb-balance">
      <h2>Balance to pay</h2>
      <div class="pb-amount">&#8377;{{ number_format($balanceCharge['total'], 2) }}</div>
      <p class="pb-note">
        Package price &#8377;{{ number_format($balanceCharge['base'], 2) }} + GST &#8377;{{ number_format($balanceCharge['gst'], 2) }} + online payment charge &#8377;{{ number_format($balanceCharge['gateway'], 2) }}.<br>
        <strong style="color:#fff;">Due by {{ $booking->package_balance_due_on?->format('l, d M Y') }}.</strong>
        If the balance is not paid before departure, the booking is cancelled and the booking amount is not refundable.
      </p>
      <a href="{{ route('package.balance', $booking->reference) }}" class="pb-btn">Pay Balance Now</a>
    </div>
  @endif

  <div class="pb-card">
    <h2>{{ $booking->package->title }}</h2>
    <div class="pb-row"><span>Travel dates</span><strong>{{ $start->format('D, d M Y') }}{{ $end ? ' – '.$end->format('D, d M Y') : '' }}</strong></div>
    @if($booking->package?->destination)<div class="pb-row"><span>Destination</span><strong>{{ $booking->package->destination->name }}</strong></div>@endif
    @if($booking->package_option && $booking->package_option !== 'Per person')<div class="pb-row"><span>Room sharing</span><strong>{{ $booking->package_option }}</strong></div>@endif
    <div class="pb-row"><span>Travellers</span><strong>{{ $booking->pax_adults }}</strong></div>
    <div class="pb-row"><span>Price per person</span><strong>&#8377;{{ number_format((float) $booking->package_price_per_person, 2) }} + GST</strong></div>
    <div class="pb-row"><span>Trip total (incl. 5% GST)</span><strong>&#8377;{{ number_format((float) $booking->base_amount + (float) $booking->tax_amount, 2) }}</strong></div>
    <div class="pb-row"><span>Paid so far</span><strong>&#8377;{{ number_format($paidTotal, 2) }}</strong></div>
    @if($refunded > 0)<div class="pb-row"><span>Refunded</span><strong>&#8377;{{ number_format($refunded, 2) }}</strong></div>@endif
  </div>

  <div class="pb-card">
    <h2>Travellers</h2>
    @foreach($booking->travelers as $i => $t)
      <div class="pb-row"><span>{{ $i === 0 ? 'Lead traveller' : 'Traveller '.($i + 1) }}</span><strong>{{ $t->full_name }}{{ $t->age ? ', '.$t->age : '' }}{{ $t->gender ? ' · '.ucfirst($t->gender) : '' }}</strong></div>
    @endforeach
    <div class="pb-row"><span>Contact</span><strong>{{ $booking->guest_email }} · +{{ $booking->guest_phone_code }} {{ $booking->guest_phone }}</strong></div>
    @if($booking->special_requests)<div class="pb-row"><span>Special requests</span><strong>{{ $booking->special_requests }}</strong></div>@endif
  </div>

  <div style="text-align:center;">
    @if($booking->hasInvoice())
      <a href="{{ route('hotel.booking.invoice', $booking->reference) }}" class="pb-btn-outline"><i class="fa-solid fa-file-invoice"></i> Download Invoice</a>
    @endif
    <a href="{{ route('history') }}" class="pb-btn-outline">My Trips</a>
    @if($canCancel)
      <a href="{{ route('hotel.booking.cancel.show', $booking->reference) }}" class="pb-btn-outline pb-btn-danger">Cancel Booking</a>
      <p class="pb-note">Online cancellation is open until {{ $cancelDeadline->format('d M Y') }}.</p>
    @elseif($booking->status === 'confirmed' && ! $cancelling)
      <p class="pb-note">Online cancellation has closed ({{ \App\Services\PackageBookingService::CANCEL_DAYS_BEFORE }} days before departure). Please contact our team on +91 9875073788 for any changes.</p>
    @endif
  </div>
</div>
@endsection
