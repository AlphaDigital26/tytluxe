@extends('layouts.frontend')

@section('content')

<!-- Profile Hero Section -->
<section class="profile-hero" style="background-image: linear-gradient(to bottom, rgba(0,0,0,0.4), rgba(0,0,0,1)), url('{{ asset('assets/images/Carousel.jpeg') }}');">
    <div class="profile-hero-inner">
        <div class="profile-avatar-container">
            <img src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name) }}&color=c9a84c&background=111&size=120" alt="{{ auth()->user()->name }}" class="profile-avatar">
        </div>
        <h1 class="profile-name">{{ auth()->user()->name }}</h1>
    </div>
</section>

<!-- Profile Content Section -->
<section class="profile-content-section">

    <div class="history-container">
        <div class="history-grid">
            
            <!-- LEFT COLUMN -->
            <div class="history-main">
                
                <!-- Upcoming Journeys -->
                <div class="history-section-title">
                    <h2>Upcoming Journeys</h2>
                </div>
                
                @forelse($upcomingBookings as $booking)
                <div class="journey-card">
                    @php
                        $imageUrl = 'https://images.unsplash.com/photo-1512453979798-5ea266f8880c?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80';
                        $title = 'Booking ' . $booking->reference;
                        $location = '';
                        if ($booking->vertical === 'hotel' && $booking->hotel) {
                            $title = $booking->hotel->title;
                            $location = $booking->hotel->destination ? $booking->hotel->destination->name : '';
                            if ($booking->hotel->images->count() > 0) {
                                $imageUrl = str_starts_with($booking->hotel->images->first()->image_path, 'http') ? $booking->hotel->images->first()->image_path : Storage::disk('public')->url($booking->hotel->images->first()->image_path);
                            }
                        } elseif ($booking->vertical === 'package' && $booking->package) {
                            $title = $booking->package->name;
                            $location = $booking->package->destination ? $booking->package->destination->name : '';
                            $imageUrl = $booking->package->hero_image_url;
                        } elseif ($booking->vertical === 'flight') {
                            $title = 'Flight: ' . $booking->flight_route;
                        }

                        $guestsLabel = $booking->pax_adults . ' Adult' . ($booking->pax_adults > 1 ? 's' : '');
                        if ($booking->pax_children) {
                            $guestsLabel .= ', ' . $booking->pax_children . ' Children';
                        }

                        $modalData = [
                            'reference' => $booking->reference,
                            'title' => $title,
                            'location' => $location,
                            'imageUrl' => $imageUrl,
                            'status' => ucfirst(str_replace('_', ' ', $booking->status)),
                            'checkIn' => $booking->check_in ? \Carbon\Carbon::parse($booking->check_in)->format('d M Y') : null,
                            'checkOut' => $booking->check_out ? \Carbon\Carbon::parse($booking->check_out)->format('d M Y') : null,
                            'guests' => $guestsLabel,
                            'roomType' => $booking->roomType->name ?? null,
                            'total' => $booking->total_amount ? number_format($booking->total_amount, 2) : null,
                            'currency' => $booking->currency,
                            'detailsUrl' => route('hotel.booking.confirmation', $booking->reference),
                        ];
                    @endphp
                    <img src="{{ $imageUrl }}" alt="{{ $title }}" class="journey-img">
                    <div class="journey-content">
                        <div class="journey-header">
                            <span class="status-badge">{{ ucfirst(str_replace('_', ' ', $booking->status)) }}</span>
                            <span>
                                @if($booking->check_in && $booking->check_out)
                                    {{ \Carbon\Carbon::parse($booking->check_in)->format('M d') }} - {{ \Carbon\Carbon::parse($booking->check_out)->format('M d, Y') }}
                                @elseif($booking->check_in)
                                    {{ \Carbon\Carbon::parse($booking->check_in)->format('M d, Y') }}
                                @endif
                            </span>
                        </div>
                        <h3>{{ $title }}</h3>
                        <div class="journey-location">
                            @if($location)
                                <svg viewBox="0 0 24 24"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/></svg>
                                {{ $location }}
                            @endif
                        </div>
                        <button type="button" class="btn btn-primary journey-btn booking-details-trigger" data-booking='{{ json_encode($modalData, JSON_HEX_APOS | JSON_HEX_QUOT) }}'>View Details</button>
                    </div>
                </div>
                @empty
                    <p style="padding: 10px; color: #666;">You have no upcoming journeys.</p>
                @endforelse

                <!-- Enquiries -->
                <div class="history-section-title">
                    <h2>Your Enquiries</h2>
                </div>

                <div class="concierge-list">
                    @forelse(auth()->user()->enquiries as $enquiry)
                        <div class="concierge-item">
                            <div class="concierge-icon">
                                <i class="fa-solid fa-clipboard-list" style="font-size: 24px; color: var(--primary);"></i>
                            </div>
                            <div class="concierge-info">
                                <div class="concierge-title">Enquiry for {{ ucfirst($enquiry->vertical) }}</div>
                                <div class="concierge-desc">
                                    Status: {{ ucfirst($enquiry->status) }} 
                                    @if($enquiry->travel_date_from)
                                    &bull; Travel Date: {{ \Carbon\Carbon::parse($enquiry->travel_date_from)->format('M d, Y') }}
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <p style="padding: 10px; color: #666;">You have no enquiries yet.</p>
                    @endforelse
                </div>

                <!-- Past Journeys -->
                <div class="history-section-title">
                    <h2>Past Journeys</h2>
                </div>
                
                <div class="past-journeys-grid">
                    @forelse($pastBookings as $booking)
                        @php
                            $imageUrl = 'https://images.unsplash.com/photo-1570077188670-e3a8d69ac5f1?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80';
                            $title = 'Booking ' . $booking->reference;
                            $location = '';
                            if ($booking->vertical === 'hotel' && $booking->hotel) {
                                $title = $booking->hotel->title;
                                $location = $booking->hotel->destination ? $booking->hotel->destination->name : '';
                                if ($booking->hotel->images->count() > 0) {
                                    $imageUrl = str_starts_with($booking->hotel->images->first()->image_path, 'http') ? $booking->hotel->images->first()->image_path : Storage::disk('public')->url($booking->hotel->images->first()->image_path);
                                }
                            } elseif ($booking->vertical === 'package' && $booking->package) {
                                $title = $booking->package->name;
                                $location = $booking->package->destination ? $booking->package->destination->name : '';
                                $imageUrl = $booking->package->hero_image_url;
                            } elseif ($booking->vertical === 'flight') {
                                $title = 'Flight: ' . $booking->flight_route;
                            }

                            $guestsLabel = $booking->pax_adults . ' Adult' . ($booking->pax_adults > 1 ? 's' : '');
                            if ($booking->pax_children) {
                                $guestsLabel .= ', ' . $booking->pax_children . ' Children';
                            }

                            $modalData = [
                                'reference' => $booking->reference,
                                'title' => $title,
                                'location' => $location,
                                'imageUrl' => $imageUrl,
                                'status' => ucfirst(str_replace('_', ' ', $booking->status)),
                                'checkIn' => $booking->check_in ? \Carbon\Carbon::parse($booking->check_in)->format('d M Y') : null,
                                'checkOut' => $booking->check_out ? \Carbon\Carbon::parse($booking->check_out)->format('d M Y') : null,
                                'guests' => $guestsLabel,
                                'roomType' => $booking->roomType->name ?? null,
                                'total' => $booking->total_amount ? number_format($booking->total_amount, 2) : null,
                                'currency' => $booking->currency,
                                'detailsUrl' => route('hotel.booking.confirmation', $booking->reference),
                            ];
                        @endphp
                        <div class="past-card">
                            <img src="{{ $imageUrl }}" alt="{{ $title }}" class="past-img">
                            <div class="past-content">
                                <div class="journey-header">
                                    <span class="status-badge" style="background: var(--white-10); color: var(--white-60); border: 1px solid var(--border-color);">{{ ucfirst(str_replace('_', ' ', $booking->status)) }}</span>
                                    <span>
                                        @if($booking->check_in)
                                            {{ \Carbon\Carbon::parse($booking->check_in)->format('M Y') }}
                                        @endif
                                    </span>
                                </div>
                                <h3 style="font-size: 20px;">{{ $title }}</h3>
                                <div class="journey-location" style="margin-bottom: 0;">
                                    @if($location)
                                        <svg viewBox="0 0 24 24"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/></svg>
                                        {{ $location }}
                                    @endif
                                </div>
                                <button type="button" class="btn btn-primary journey-btn booking-details-trigger" style="margin-top: 14px;" data-booking='{{ json_encode($modalData, JSON_HEX_APOS | JSON_HEX_QUOT) }}'>View Details</button>
                            </div>
                        </div>
                    @empty
                        <p style="padding: 10px; color: #666; grid-column: 1 / -1;">You have no past journeys.</p>
                    @endforelse
                </div>

            </div>
            
            <!-- RIGHT COLUMN -->
            <div class="history-sidebar">
                
                <!-- Saved Inspiration -->
                <div class="history-section-title">
                    <h2>Saved Inspiration</h2>
                </div>
                <div class="inspiration-grid">
                    <img src="https://images.unsplash.com/photo-1499793983690-e29da59ef1c2?ixlib=rb-4.0.3&w=300&q=80" alt="Insp" class="insp-img">
                    <img src="https://images.unsplash.com/photo-1540541338287-41700207dee6?ixlib=rb-4.0.3&w=300&q=80" alt="Insp" class="insp-img">
                    <div class="insp-img large">
                        <img src="https://images.unsplash.com/photo-1439066615861-d1af74d74000?ixlib=rb-4.0.3&w=600&q=80" alt="Villas" style="width:100%; height:100%; object-fit:cover; border-radius:inherit;">
                        <div class="insp-overlay">
                            <span>Overwater Villas</span>
                        </div>
                    </div>
                </div>


            </div>
        </div>



    </div>
</section>

<!-- Booking Details Popup -->
<div id="bdModalBackdrop" class="bd-modal-backdrop" aria-hidden="true">
  <div class="bd-modal-card" role="dialog" aria-labelledby="bdModalTitle">
    <button type="button" class="bd-modal-close" id="bdModalCloseBtn" aria-label="Close">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
    </button>
    <div class="bd-modal-img-wrap">
      <img id="bdModalImg" src="" alt="">
      <div class="bd-modal-img-overlay">
        <span class="bd-modal-status" id="bdModalStatus"></span>
        <h3 id="bdModalTitle"></h3>
        <p id="bdModalLocation"></p>
      </div>
    </div>
    <div class="bd-modal-body">
      <div class="bd-modal-grid">
        <div>
          <div class="bd-modal-label">Check-in</div>
          <div class="bd-modal-value" id="bdModalCheckin">—</div>
        </div>
        <div>
          <div class="bd-modal-label">Check-out</div>
          <div class="bd-modal-value" id="bdModalCheckout">—</div>
        </div>
        <div>
          <div class="bd-modal-label">Guests</div>
          <div class="bd-modal-value" id="bdModalGuests">—</div>
        </div>
        <div id="bdModalRoomWrap" hidden>
          <div class="bd-modal-label">Room Type</div>
          <div class="bd-modal-value" id="bdModalRoom">—</div>
        </div>
      </div>
      <div class="bd-modal-footer">
        <div id="bdModalTotalWrap" hidden>
          <div class="bd-modal-label">Total Paid</div>
          <div class="bd-modal-total" id="bdModalTotal">—</div>
        </div>
        <a href="#" id="bdModalFullLink" class="bd-modal-btn">
          View Full Details
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
        </a>
      </div>
      <div class="bd-modal-ref" id="bdModalRef"></div>
    </div>
  </div>
</div>

<style>
  .bd-modal-backdrop {
    position: fixed; inset: 0; z-index: 999999;
    background: rgba(0,0,0,0.82); backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px);
    display: flex; align-items: center; justify-content: center; padding: 20px;
    opacity: 0; pointer-events: none; transition: opacity 0.28s ease;
  }
  .bd-modal-backdrop.open { opacity: 1; pointer-events: auto; }
  .bd-modal-card {
    background: #171717; border: 1px solid rgba(201,168,76,0.28); border-radius: 20px;
    max-width: 480px; width: 100%; max-height: 90vh; overflow-y: auto; position: relative;
    box-shadow: 0 24px 70px rgba(0,0,0,0.9), 0 0 35px rgba(201,168,76,0.12);
    transform: translateY(20px) scale(0.97); transition: transform 0.32s cubic-bezier(0.25,0.46,0.45,0.94);
  }
  .bd-modal-backdrop.open .bd-modal-card { transform: translateY(0) scale(1); }

  .bd-modal-close {
    position: absolute; top: 14px; right: 14px; z-index: 2;
    background: rgba(0,0,0,0.45); border: 1px solid rgba(255,255,255,0.2); color: #fff;
    width: 32px; height: 32px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center;
    cursor: pointer; transition: all 0.2s ease;
  }
  .bd-modal-close:hover { background: rgba(201,168,76,0.3); border-color: #c9a84c; transform: rotate(90deg); }

  .bd-modal-img-wrap { position: relative; width: 100%; height: 190px; }
  .bd-modal-img-wrap img { width: 100%; height: 100%; object-fit: cover; }
  .bd-modal-img-overlay {
    position: absolute; inset: 0; display: flex; flex-direction: column; justify-content: flex-end;
    padding: 18px 22px; background: linear-gradient(to top, rgba(0,0,0,0.92), rgba(0,0,0,0.1) 65%);
  }
  .bd-modal-status {
    align-self: flex-start; padding: 3px 12px; border-radius: 100px; font-size: 11px; font-weight: 700;
    letter-spacing: 0.04em; font-family: 'Jost', sans-serif; background: rgba(74,222,128,0.15); color: #4ade80;
    margin-bottom: 8px;
  }
  .bd-modal-img-overlay h3 { font-family: 'Cormorant Garamond', serif; color: #fff; font-size: 22px; margin: 0 0 2px; }
  .bd-modal-img-overlay p { font-family: 'Jost', sans-serif; font-size: 12.5px; color: rgba(255,255,255,0.65); margin: 0; }

  .bd-modal-body { padding: 22px; }
  .bd-modal-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px 14px; margin-bottom: 20px; }
  .bd-modal-label { font-family: 'Jost', sans-serif; font-size: 10.5px; color: rgba(255,255,255,0.35); text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 5px; }
  .bd-modal-value { font-family: 'Jost', sans-serif; font-size: 14px; color: #fff; font-weight: 500; }

  .bd-modal-footer {
    display: flex; align-items: center; justify-content: space-between; gap: 12px;
    padding-top: 16px; border-top: 1px dashed rgba(255,255,255,0.1);
  }
  .bd-modal-total { font-family: 'Jost', sans-serif; font-size: 18px; font-weight: 700; color: #e8c96b; }
  .bd-modal-btn {
    display: inline-flex; align-items: center; gap: 6px; padding: 11px 20px; border-radius: 100px;
    background: linear-gradient(90deg, #c9a84c, #e8c96b); color: #0d0d0d;
    font-family: 'Jost', sans-serif; font-size: 12px; font-weight: 800; letter-spacing: 0.06em;
    text-transform: uppercase; text-decoration: none; flex-shrink: 0; transition: opacity 0.2s ease;
  }
  .bd-modal-btn:hover { opacity: 0.88; }
  .bd-modal-ref { margin-top: 14px; font-family: 'Jost', sans-serif; font-size: 11px; color: rgba(255,255,255,0.3); text-align: center; }

  @media (max-width: 480px) {
    .bd-modal-footer { flex-direction: column; align-items: stretch; }
    .bd-modal-btn { justify-content: center; }
  }
</style>

<script>
(function() {
  const backdrop = document.getElementById('bdModalBackdrop');
  if (!backdrop) return;

  function openModal(data) {
    document.getElementById('bdModalImg').src = data.imageUrl || '';
    document.getElementById('bdModalImg').alt = data.title || '';
    document.getElementById('bdModalTitle').textContent = data.title || '';
    document.getElementById('bdModalLocation').textContent = data.location || '';
    document.getElementById('bdModalStatus').textContent = data.status || '';
    document.getElementById('bdModalCheckin').textContent = data.checkIn || '—';
    document.getElementById('bdModalCheckout').textContent = data.checkOut || '—';
    document.getElementById('bdModalGuests').textContent = data.guests || '—';

    const roomWrap = document.getElementById('bdModalRoomWrap');
    if (data.roomType) {
      roomWrap.hidden = false;
      document.getElementById('bdModalRoom').textContent = data.roomType;
    } else {
      roomWrap.hidden = true;
    }

    const totalWrap = document.getElementById('bdModalTotalWrap');
    if (data.total) {
      totalWrap.hidden = false;
      document.getElementById('bdModalTotal').textContent = (data.currency || 'INR') + ' ' + data.total;
    } else {
      totalWrap.hidden = true;
    }

    document.getElementById('bdModalFullLink').href = data.detailsUrl || '#';
    document.getElementById('bdModalRef').textContent = data.reference ? ('Booking Reference: ' + data.reference) : '';

    backdrop.classList.add('open');
    document.body.style.overflow = 'hidden';
  }

  function closeModal() {
    backdrop.classList.remove('open');
    document.body.style.overflow = '';
  }

  document.addEventListener('click', function(e) {
    const trigger = e.target.closest('.booking-details-trigger');
    if (trigger) {
      e.preventDefault();
      let data = {};
      try { data = JSON.parse(trigger.getAttribute('data-booking')); } catch (err) { /* leave empty */ }
      openModal(data);
      return;
    }
    if (e.target.id === 'bdModalCloseBtn' || e.target.closest('#bdModalCloseBtn') || e.target.id === 'bdModalBackdrop') {
      closeModal();
    }
  });

  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeModal();
  });
})();
</script>
@endsection
