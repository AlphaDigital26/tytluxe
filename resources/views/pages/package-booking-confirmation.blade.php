@extends('layouts.frontend')

@section('meta_title', 'Booking ' . $booking->reference . ' — ' . $booking->package->title . ' | TYT Luxe')

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com" />
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,500;0,600;1,500&family=Jost:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
<style>
  :root {
    --gold: #c9a84c; --gold-light: #e8c96b; --gold-dim: rgba(201,168,76,0.14);
    --dark: #0d0d0d; --dark-2: #141414; --dark-3: #1c1c1c;
    --white-80: rgba(255,255,255,0.80); --white-60: rgba(255,255,255,0.60); --white-40: rgba(255,255,255,0.40); --white-10: rgba(255,255,255,0.10);
    --ok: #6fdc9b; --bad: #f3a3a3;
  }
  body { background: var(--dark); }
  .pb { max-width: 960px; margin: 0 auto; padding: 100px 24px 90px; font-family: 'Jost', sans-serif; color: var(--white-80); }

  /* Hero: package photo with the status on top */
  .pb-hero { position: relative; border-radius: 24px; overflow: hidden; min-height: 300px; display: flex; align-items: flex-end; background: var(--dark-3) center/cover no-repeat; }
  .pb-hero::after { content: ''; position: absolute; inset: 0; background: linear-gradient(180deg, rgba(0,0,0,0.15) 0%, rgba(0,0,0,0.55) 45%, rgba(10,10,10,0.96) 100%); }
  .pb-hero-body { position: relative; z-index: 1; padding: 32px 34px; width: 100%; box-sizing: border-box; }
  .pb-status { display: inline-flex; align-items: center; gap: 8px; padding: 6px 14px; border-radius: 100px; font-size: 12px; font-weight: 600; letter-spacing: 0.04em; backdrop-filter: blur(6px); }
  .pb-status.ok { background: rgba(111,220,155,0.16); color: var(--ok); border: 1px solid rgba(111,220,155,0.35); }
  .pb-status.warn { background: rgba(201,168,76,0.18); color: var(--gold-light); border: 1px solid rgba(201,168,76,0.4); }
  .pb-status.bad { background: rgba(243,163,163,0.14); color: var(--bad); border: 1px solid rgba(243,163,163,0.35); }
  .pb-title { font-family: 'Cormorant Garamond', serif; font-size: 2.6rem; color: #fff; margin: 14px 0 6px; line-height: 1.1; }
  .pb-subtitle { font-size: 14.5px; color: var(--white-80); margin: 0; }
  .pb-meta { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 18px; }
  .pb-chip { display: inline-flex; align-items: center; gap: 7px; padding: 7px 13px; border-radius: 100px; background: rgba(255,255,255,0.08); border: 1px solid var(--white-10); font-size: 12.5px; color: #fff; }
  .pb-chip i { color: var(--gold); font-size: 12px; }

  .pb-alert { padding: 14px 18px; border-radius: 14px; font-size: 13.5px; margin-bottom: 18px; line-height: 1.6; }
  .pb-alert.ok { background: rgba(111,220,155,0.08); border: 1px solid rgba(111,220,155,0.3); color: #a6e9bf; }
  .pb-alert.bad { background: rgba(243,163,163,0.08); border: 1px solid rgba(243,163,163,0.3); color: var(--bad); }

  .pb-grid { display: grid; grid-template-columns: minmax(0, 1.35fr) minmax(0, 1fr); gap: 20px; margin-top: 20px; }
  .pb-card { background: var(--dark-2); border: 1px solid var(--white-10); border-radius: 20px; padding: 24px 26px; }
  .pb-card + .pb-card { margin-top: 20px; }
  .pb-card-label { font-size: 10.5px; font-weight: 700; letter-spacing: 0.18em; text-transform: uppercase; color: var(--gold); margin: 0 0 14px; }

  /* Payment progress */
  .pb-pay-head { display: flex; justify-content: space-between; align-items: baseline; gap: 12px; flex-wrap: wrap; }
  .pb-big { font-family: 'Cormorant Garamond', serif; font-size: 2.4rem; color: var(--gold-light); line-height: 1; }
  .pb-of { font-size: 13px; color: var(--white-60); }
  .pb-bar { height: 10px; border-radius: 100px; background: var(--dark-3); overflow: hidden; margin: 16px 0 10px; }
  .pb-bar span { display: block; height: 100%; border-radius: 100px; background: linear-gradient(90deg, #c9a84c, #e8c96b); }
  .pb-bar-legend { display: flex; justify-content: space-between; font-size: 12.5px; color: var(--white-60); }
  .pb-bar-legend strong { color: #fff; font-weight: 600; }
  .pb-due { margin-top: 18px; padding: 16px 18px; border-radius: 14px; background: var(--gold-dim); border: 1px solid rgba(201,168,76,0.35); display: flex; align-items: center; justify-content: space-between; gap: 16px; flex-wrap: wrap; }
  .pb-due-text { font-size: 13.5px; color: #fff; line-height: 1.5; }
  .pb-due-text small { display: block; color: var(--white-60); font-size: 12px; margin-top: 2px; }
  .pb-btn { display: inline-block; padding: 13px 26px; border-radius: 100px; background: linear-gradient(90deg, #c9a84c, #e8c96b); color: var(--dark); font-size: 12.5px; font-weight: 800; letter-spacing: 0.08em; text-transform: uppercase; text-decoration: none; white-space: nowrap; transition: transform .2s, box-shadow .2s; }
  .pb-btn:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(201,168,76,0.25); }
  .pb-paid-full { display: flex; align-items: center; gap: 10px; margin-top: 16px; font-size: 13.5px; color: var(--ok); }

  /* Timeline */
  .pb-steps { list-style: none; margin: 0; padding: 0; }
  .pb-step { position: relative; padding: 0 0 20px 34px; }
  .pb-step:last-child { padding-bottom: 0; }
  .pb-step::before { content: ''; position: absolute; left: 10px; top: 22px; bottom: 0; width: 2px; background: var(--white-10); }
  .pb-step:last-child::before { display: none; }
  .pb-dot { position: absolute; left: 0; top: 0; width: 22px; height: 22px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 10px; background: var(--dark-3); border: 2px solid var(--white-10); color: var(--white-40); }
  .pb-step.done .pb-dot { background: var(--gold); border-color: var(--gold); color: var(--dark); }
  .pb-step.now .pb-dot { border-color: var(--gold); color: var(--gold); box-shadow: 0 0 0 4px var(--gold-dim); }
  .pb-step-title { font-size: 14px; color: #fff; font-weight: 500; }
  .pb-step-sub { font-size: 12.5px; color: var(--white-60); margin-top: 2px; }

  /* Countdown */
  .pb-countdown { text-align: center; }
  .pb-countdown-num { font-family: 'Cormorant Garamond', serif; font-size: 3.4rem; color: #fff; line-height: 1; }
  .pb-countdown-txt { font-size: 12.5px; color: var(--white-60); margin-top: 4px; }

  /* Travellers */
  .pb-people { display: grid; grid-template-columns: repeat(auto-fill, minmax(190px, 1fr)); gap: 12px; }
  .pb-person { display: flex; align-items: center; gap: 12px; padding: 12px 14px; border-radius: 14px; background: var(--dark-3); }
  .pb-avatar { flex: none; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 14px; color: var(--dark); background: linear-gradient(135deg, #c9a84c, #e8c96b); }
  .pb-person-name { font-size: 14px; color: #fff; font-weight: 500; }
  .pb-person-sub { font-size: 12px; color: var(--white-60); }
  .pb-lead { display: inline-block; margin-left: 6px; padding: 1px 7px; border-radius: 100px; font-size: 10px; background: var(--gold-dim); color: var(--gold); vertical-align: 2px; }
  .pb-contact { display: flex; flex-wrap: wrap; gap: 8px 20px; margin-top: 16px; font-size: 13px; color: var(--white-60); }
  .pb-contact i { color: var(--gold); margin-right: 6px; }

  /* Actions */
  .pb-actions { display: grid; grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)); gap: 12px; margin-top: 20px; }
  .pb-action { display: flex; align-items: center; gap: 12px; padding: 16px 18px; border-radius: 16px; background: var(--dark-2); border: 1px solid var(--white-10); color: #fff; text-decoration: none; font-size: 13.5px; font-weight: 500; transition: border-color .2s, transform .2s; }
  .pb-action:hover { border-color: var(--gold); transform: translateY(-2px); }
  .pb-action i { width: 34px; height: 34px; border-radius: 10px; display: flex; align-items: center; justify-content: center; background: var(--gold-dim); color: var(--gold); }
  .pb-action small { display: block; font-size: 11.5px; color: var(--white-60); font-weight: 400; }
  .pb-action.danger i { background: rgba(243,163,163,0.12); color: var(--bad); }
  .pb-help { text-align: center; font-size: 12.5px; color: var(--white-40); margin-top: 22px; }
  .pb-help a { color: var(--gold); text-decoration: none; }

  @media (max-width: 820px) {
    .pb-grid { grid-template-columns: 1fr; }
  }
  @media (max-width: 560px) {
    .pb { padding: 88px 16px 60px; }
    .pb-hero { min-height: 260px; border-radius: 18px; }
    .pb-hero-body { padding: 22px 20px; }
    .pb-title { font-size: 2rem; }
    .pb-card { padding: 20px 18px; }
    .pb-big { font-size: 2rem; }
    .pb-due { flex-direction: column; align-items: stretch; text-align: left; }
    .pb-btn { text-align: center; }
  }
</style>
@endpush

@section('content')
@php
  $start = \Carbon\Carbon::parse($booking->check_in);
  $end = $booking->check_out ? \Carbon\Carbon::parse($booking->check_out) : null;
  $paid = (float) $booking->payments->whereIn('status', \App\Models\Booking::PAID_PAYMENT_STATUSES)->sum('amount');
  $refunded = (float) $booking->payments->sum('refund_amount');
  $balanceTotal = $balanceCharge['total'] ?? 0;
  $fullAmount = $paid + $balanceTotal;
  $percent = $fullAmount > 0 ? max(4, min(100, round($paid / $fullAmount * 100))) : 0;
  $cancelling = $booking->status === 'confirmed' && $booking->cancellation_requested_at !== null;
  $confirmed = $booking->status === 'confirmed';
  $daysToGo = $confirmed ? (int) today()->diffInDays($start, false) : null;
  $nights = $end ? $start->diffInDays($end) : null;
  $initials = fn ($name) => collect(preg_split('/\s+/', trim($name)))->filter()->take(2)->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->implode('');

  [$statusClass, $statusIcon, $statusText, $headline] = match (true) {
      $cancelling => ['warn', 'fa-hourglass-half', 'Cancellation requested', 'We\'re reviewing your cancellation'],
      $confirmed && $balanceCharge !== null => ['ok', 'fa-circle-check', 'Seats booked', 'Your seats are booked!'],
      $confirmed => ['ok', 'fa-circle-check', 'Fully paid', 'Your trip is confirmed!'],
      $booking->status === 'cancelled' => ['bad', 'fa-circle-xmark', 'Cancelled', 'This booking is cancelled'],
      $booking->status === 'refunded' => ['bad', 'fa-rotate-left', 'Refunded', 'Seats were not available'],
      $booking->status === 'failed_needs_review' => ['warn', 'fa-hourglass-half', 'Under review', 'We\'re checking your booking'],
      default => ['warn', 'fa-credit-card', 'Payment pending', 'Complete your payment'],
  };
  $subtitle = match (true) {
      $booking->status === 'refunded' => 'The seats were taken before your payment went through, so we refunded you in full. It can take 5–7 business days to reach your account.',
      $booking->status === 'failed_needs_review' => 'Your payment was received and our team is giving it a quick check. We\'ll contact you shortly.',
      in_array($booking->status, ['pending_payment', 'payment_failed'], true) => 'Your seats are not booked until payment is complete.',
      default => $booking->guestCancellationMessage(),
  };
@endphp

<div class="pb">
  @if(session('booking_success'))<div class="pb-alert ok" role="status">{{ session('booking_success') }}</div>@endif
  @if(session('booking_error'))<div class="pb-alert bad" role="alert">{{ session('booking_error') }}</div>@endif

  {{-- Hero --}}
  <section class="pb-hero" style="background-image: url('{{ $booking->package->hero_image_url }}');">
    <div class="pb-hero-body">
      <span class="pb-status {{ $statusClass }}"><i class="fa-solid {{ $statusIcon }}"></i> {{ $statusText }}</span>
      <h1 class="pb-title">{{ $headline }}</h1>
      <p class="pb-subtitle">{{ $subtitle ?: $booking->package->title }}</p>
      <div class="pb-meta">
        <span class="pb-chip"><i class="fa-solid fa-suitcase-rolling"></i> {{ $booking->package->title }}</span>
        <span class="pb-chip"><i class="fa-regular fa-calendar"></i> {{ $start->format('d M') }}{{ $end ? ' – '.$end->format('d M Y') : ' '.$start->format('Y') }}</span>
        <span class="pb-chip"><i class="fa-solid fa-user-group"></i> {{ $booking->packageTravellersLabel() }}</span>
        @if($booking->package_option && $booking->package_option !== 'Per person')
          <span class="pb-chip"><i class="fa-solid fa-bed"></i> {{ $booking->package_option }}</span>
        @endif
        <span class="pb-chip"><i class="fa-solid fa-hashtag"></i> {{ $booking->reference }}</span>
      </div>
    </div>
  </section>

  <div class="pb-grid">
    <div>
      {{-- Payment --}}
      <div class="pb-card">
        <p class="pb-card-label">Payment</p>
        @if(in_array($booking->status, ['pending_payment', 'payment_failed'], true))
          <div class="pb-due" style="margin-top:0;">
            <div class="pb-due-text">Finish paying to book your seats.<small>Seats are held for a short time only.</small></div>
            <a href="{{ route('hotel.payment.show', $booking->reference) }}" class="pb-btn">Complete Payment</a>
          </div>
        @else
          <div class="pb-pay-head">
            <div>
              <div class="pb-big">&#8377;{{ number_format($paid, 0) }}</div>
              <div class="pb-of">paid{{ $balanceCharge ? ' of ₹'.number_format($fullAmount, 0) : '' }}</div>
            </div>
            @if($refunded > 0)
              <div class="pb-of" style="text-align:right;">&#8377;{{ number_format($refunded, 0) }} refunded</div>
            @endif
          </div>
          @if($balanceCharge)
            <div class="pb-bar" role="progressbar" aria-valuenow="{{ $percent }}" aria-valuemin="0" aria-valuemax="100"><span style="width: {{ $percent }}%"></span></div>
            <div class="pb-bar-legend"><span>Paid <strong>{{ $percent }}%</strong></span><span>Remaining <strong>&#8377;{{ number_format($balanceTotal, 0) }}</strong></span></div>
            <div class="pb-due">
              <div class="pb-due-text">
                Pay &#8377;{{ number_format($balanceTotal, 2) }} by <strong>{{ $booking->package_balance_due_on?->format('D, d M') }}</strong>
                <small>Unpaid by departure? The booking is cancelled and the booking amount is not refunded.</small>
              </div>
              <a href="{{ route('package.balance', $booking->reference) }}" class="pb-btn">Pay Balance Now</a>
            </div>
          @elseif($confirmed && ! $cancelling)
            <div class="pb-paid-full"><i class="fa-solid fa-circle-check"></i> Fully paid — nothing more to pay.</div>
          @endif
        @endif
      </div>

      {{-- Travellers --}}
      <div class="pb-card">
        <p class="pb-card-label">Who's going</p>
        <div class="pb-people">
          @foreach($booking->travelers as $i => $t)
            <div class="pb-person">
              <div class="pb-avatar">{{ $initials($t->full_name) }}</div>
              <div>
                <div class="pb-person-name">{{ $t->full_name }}@if($i === 0)<span class="pb-lead">Lead</span>@endif</div>
                <div class="pb-person-sub">{{ $t->age ? $t->age.' yrs' : '' }}{{ $t->age && $t->gender ? ' · ' : '' }}{{ $t->gender ? ucfirst($t->gender) : '' }}{{ $t->traveler_type === 'infant' ? ' · Infant (free)' : '' }}</div>
              </div>
            </div>
          @endforeach
        </div>
        <div class="pb-contact">
          <span><i class="fa-regular fa-envelope"></i>{{ $booking->guest_email }}</span>
          <span><i class="fa-solid fa-phone"></i>+{{ $booking->guest_phone_code }} {{ $booking->guest_phone }}</span>
        </div>
        @if($booking->special_requests)
          <p class="pb-step-sub" style="margin-top:12px;"><i class="fa-regular fa-comment" style="color:var(--gold);margin-right:6px;"></i>{{ $booking->special_requests }}</p>
        @endif
      </div>
    </div>

    <div>
      {{-- Countdown --}}
      @if($confirmed && ! $cancelling && $daysToGo !== null && $daysToGo >= 0)
        <div class="pb-card pb-countdown">
          <p class="pb-card-label">Your trip starts in</p>
          <div class="pb-countdown-num">{{ $daysToGo === 0 ? 'Today' : $daysToGo }}</div>
          <div class="pb-countdown-txt">{{ $daysToGo === 0 ? 'Have a wonderful trip!' : ($daysToGo === 1 ? 'day' : 'days').' · '.$start->format('l, d M') }}</div>
        </div>
      @endif

      {{-- What happens next --}}
      @if($confirmed)
        <div class="pb-card">
          <p class="pb-card-label">What happens next</p>
          <ol class="pb-steps">
            <li class="pb-step done">
              <span class="pb-dot"><i class="fa-solid fa-check"></i></span>
              <div class="pb-step-title">Seats booked</div>
              <div class="pb-step-sub">{{ $booking->created_at->format('d M Y') }}</div>
            </li>
            @if($booking->package_payment_plan === 'part')
              <li class="pb-step {{ $balanceCharge ? 'now' : 'done' }}">
                <span class="pb-dot">@if($balanceCharge) 2 @else <i class="fa-solid fa-check"></i> @endif</span>
                <div class="pb-step-title">{{ $balanceCharge ? 'Pay the balance' : 'Balance paid' }}</div>
                <div class="pb-step-sub">{{ $balanceCharge ? 'By '.$booking->package_balance_due_on?->format('d M Y') : 'All settled' }}</div>
              </li>
            @endif
            <li class="pb-step {{ ! $balanceCharge && ! $cancelling ? 'now' : '' }}">
              <span class="pb-dot"><i class="fa-solid fa-paper-plane"></i></span>
              <div class="pb-step-title">Trip details from our team</div>
              <div class="pb-step-sub">A few days before departure</div>
            </li>
            <li class="pb-step">
              <span class="pb-dot"><i class="fa-solid fa-mountain-sun"></i></span>
              <div class="pb-step-title">Your trip begins</div>
              <div class="pb-step-sub">{{ $start->format('D, d M Y') }}{{ $nights ? ' · '.$nights.' night'.($nights > 1 ? 's' : '') : '' }}</div>
            </li>
          </ol>
        </div>
      @endif
    </div>
  </div>

  {{-- Actions --}}
  <div class="pb-actions">
    @if($booking->hasInvoice())
      <a href="{{ route('hotel.booking.invoice', $booking->reference) }}" class="pb-action"><i class="fa-solid fa-file-invoice"></i><span>Download Invoice<small>PDF for your records</small></span></a>
    @endif
    <a href="{{ route('package.details', $booking->package->slug) }}" class="pb-action"><i class="fa-solid fa-route"></i><span>View Itinerary<small>Day-by-day plan</small></span></a>
    <a href="{{ route('history') }}" class="pb-action"><i class="fa-solid fa-suitcase"></i><span>My Trips<small>All your bookings</small></span></a>
    @if($canCancel)
      <a href="{{ route('hotel.booking.cancel.show', $booking->reference) }}" class="pb-action danger"><i class="fa-solid fa-xmark"></i><span>Cancel Booking<small>Until {{ $cancelDeadline->format('d M Y') }}</small></span></a>
    @endif
  </div>

  <p class="pb-help">
    Need help? Call or WhatsApp <a href="https://wa.me/919875073788" target="_blank">+91 98750 73788</a> and quote <strong style="color:#fff;">{{ $booking->reference }}</strong>.
    @if($confirmed && ! $canCancel && ! $cancelling)
      <br>Online cancellation has closed ({{ \App\Services\PackageBookingService::CANCEL_DAYS_BEFORE }} days before departure).
    @endif
  </p>
</div>
@endsection
