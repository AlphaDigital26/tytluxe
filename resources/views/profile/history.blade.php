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
                            'statusRaw' => $booking->status,
                            'checkIn' => $booking->check_in ? \Carbon\Carbon::parse($booking->check_in)->format('d M Y') : null,
                            'checkOut' => $booking->check_out ? \Carbon\Carbon::parse($booking->check_out)->format('d M Y') : null,
                            'guests' => $guestsLabel,
                            'roomType' => $booking->roomType->name ?? null,
                            'total' => $booking->total_amount ? number_format($booking->total_amount, 2) : null,
                            'baseAmount' => $booking->base_amount ? number_format($booking->base_amount, 2) : null,
                            'taxAmount' => $booking->tax_amount ? number_format($booking->tax_amount, 2) : null,
                            'discountAmount' => $booking->discount_amount ? number_format($booking->discount_amount, 2) : null,
                            'currency' => $booking->currency,
                            'leadGuestName' => $booking->lead_guest_name,
                            'guestEmail' => $booking->guest_email,
                            'guestPhone' => $booking->guest_phone,
                            'specialRequests' => $booking->special_requests,
                            'cancellationReason' => $booking->cancellation_reason,
                            'invoiceUrl' => ! in_array($booking->status, ['pending_payment', 'payment_failed'], true) ? route('hotel.booking.invoice', $booking->reference) : null,
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
                                'statusRaw' => $booking->status,
                                'checkIn' => $booking->check_in ? \Carbon\Carbon::parse($booking->check_in)->format('d M Y') : null,
                                'checkOut' => $booking->check_out ? \Carbon\Carbon::parse($booking->check_out)->format('d M Y') : null,
                                'guests' => $guestsLabel,
                                'roomType' => $booking->roomType->name ?? null,
                                'total' => $booking->total_amount ? number_format($booking->total_amount, 2) : null,
                                'baseAmount' => $booking->base_amount ? number_format($booking->base_amount, 2) : null,
                                'taxAmount' => $booking->tax_amount ? number_format($booking->tax_amount, 2) : null,
                                'discountAmount' => $booking->discount_amount ? number_format($booking->discount_amount, 2) : null,
                                'currency' => $booking->currency,
                                'leadGuestName' => $booking->lead_guest_name,
                                'guestEmail' => $booking->guest_email,
                                'guestPhone' => $booking->guest_phone,
                                'specialRequests' => $booking->special_requests,
                                'cancellationReason' => $booking->cancellation_reason,
                                'invoiceUrl' => ! in_array($booking->status, ['pending_payment', 'payment_failed'], true) ? route('hotel.booking.invoice', $booking->reference) : null,
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
        <div class="bd-modal-stat">
          <div class="bd-modal-stat-icon">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-6.5 8-12.5A8 8 0 004 9.5C4 15.5 12 22 12 22z"/><circle cx="12" cy="9.5" r="2.5"/></svg>
          </div>
          <div>
            <div class="bd-modal-label">Check-in</div>
            <div class="bd-modal-value" id="bdModalCheckin">—</div>
          </div>
        </div>
        <div class="bd-modal-stat">
          <div class="bd-modal-stat-icon">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-6.5 8-12.5A8 8 0 004 9.5C4 15.5 12 22 12 22z"/><circle cx="12" cy="9.5" r="2.5"/></svg>
          </div>
          <div>
            <div class="bd-modal-label">Check-out</div>
            <div class="bd-modal-value" id="bdModalCheckout">—</div>
          </div>
        </div>
        <div class="bd-modal-stat">
          <div class="bd-modal-stat-icon">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg>
          </div>
          <div>
            <div class="bd-modal-label">Guests</div>
            <div class="bd-modal-value" id="bdModalGuests">—</div>
          </div>
        </div>
        <div class="bd-modal-stat" id="bdModalRoomWrap" hidden>
          <div class="bd-modal-stat-icon">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 7v11m0-11a2 2 0 012-2h4a2 2 0 012 2m-8 0h18M9 7v11m9-11v11m0-11a2 2 0 012 2v9"/></svg>
          </div>
          <div>
            <div class="bd-modal-label">Room Type</div>
            <div class="bd-modal-value" id="bdModalRoom">—</div>
          </div>
        </div>
      </div>

      <div class="bd-modal-section" id="bdModalGuestSection" hidden>
        <div class="bd-modal-section-title">Booking Contact</div>
        <div class="bd-modal-value" id="bdModalGuestName">—</div>
        <div class="bd-modal-subvalue" id="bdModalGuestContact"></div>
      </div>

      <div class="bd-modal-section" id="bdModalRequestSection" hidden>
        <div class="bd-modal-section-title">Special Requests</div>
        <div class="bd-modal-subvalue" id="bdModalRequestText"></div>
      </div>

      <div class="bd-modal-section" id="bdModalCancelSection" hidden>
        <div class="bd-modal-section-title">Cancellation Note</div>
        <div class="bd-modal-subvalue" id="bdModalCancelText"></div>
      </div>

      <div class="bd-modal-section" id="bdModalPriceSection" hidden>
        <div class="bd-modal-section-title">Price Breakdown</div>
        <div class="bd-modal-price-row" id="bdModalBaseRow" hidden>
          <span>Room Charges</span><span id="bdModalBaseValue">—</span>
        </div>
        <div class="bd-modal-price-row" id="bdModalTaxRow" hidden>
          <span>Taxes &amp; Fees</span><span id="bdModalTaxValue">—</span>
        </div>
        <div class="bd-modal-price-row bd-modal-price-discount" id="bdModalDiscountRow" hidden>
          <span>Discount</span><span id="bdModalDiscountValue">—</span>
        </div>
      </div>

      <div class="bd-modal-footer">
        <div id="bdModalTotalWrap" hidden>
          <div class="bd-modal-label" id="bdModalTotalLabel">Total Paid</div>
          <div class="bd-modal-total" id="bdModalTotal">—</div>
        </div>
        <a href="#" id="bdModalInvoiceLink" class="bd-modal-btn" hidden>
          Download Invoice
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 3v12m0 0l-4-4m4 4l4-4M4 19h16"/></svg>
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
    scrollbar-width: thin; scrollbar-color: rgba(201,168,76,0.4) transparent;
  }
  .bd-modal-backdrop.open .bd-modal-card { transform: translateY(0) scale(1); }
  .bd-modal-card::-webkit-scrollbar { width: 6px; }
  .bd-modal-card::-webkit-scrollbar-track { background: transparent; margin: 20px 0; }
  .bd-modal-card::-webkit-scrollbar-thumb { background: rgba(201,168,76,0.35); border-radius: 100px; }
  .bd-modal-card::-webkit-scrollbar-thumb:hover { background: rgba(201,168,76,0.6); }

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
    margin-bottom: 8px; border: 1px solid rgba(74,222,128,0.3);
  }
  .bd-modal-status--bad { background: rgba(248,113,113,0.15); color: #f87171; border-color: rgba(248,113,113,0.3); }
  .bd-modal-status--neutral { background: rgba(232,201,107,0.15); color: #e8c96b; border-color: rgba(232,201,107,0.3); }
  .bd-modal-img-overlay h3 { font-family: 'Cormorant Garamond', serif; color: #fff; font-size: 22px; margin: 0 0 2px; }
  .bd-modal-img-overlay p { font-family: 'Jost', sans-serif; font-size: 12.5px; color: rgba(255,255,255,0.65); margin: 0; }

  .bd-modal-body { padding: 22px; }
  .bd-modal-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 20px; }
  .bd-modal-stat {
    display: flex; align-items: flex-start; gap: 10px; padding: 12px; border-radius: 12px;
    background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.06);
  }
  .bd-modal-stat-icon {
    flex-shrink: 0; width: 28px; height: 28px; border-radius: 8px; display: flex; align-items: center; justify-content: center;
    background: rgba(201,168,76,0.12); color: #c9a84c; margin-top: 1px;
  }
  .bd-modal-label { font-family: 'Jost', sans-serif; font-size: 10.5px; color: rgba(255,255,255,0.35); text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 4px; }
  .bd-modal-value { font-family: 'Jost', sans-serif; font-size: 13.5px; color: #fff; font-weight: 500; line-height: 1.3; }
  .bd-modal-subvalue { font-family: 'Jost', sans-serif; font-size: 12.5px; color: rgba(255,255,255,0.55); line-height: 1.5; margin-top: 3px; }

  .bd-modal-section { margin-bottom: 16px; padding-top: 14px; border-top: 1px solid rgba(255,255,255,0.07); }
  .bd-modal-section-title { font-family: 'Jost', sans-serif; font-size: 10.5px; color: #c9a84c; text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 6px; }
  .bd-modal-price-row {
    display: flex; justify-content: space-between; font-family: 'Jost', sans-serif; font-size: 13px;
    color: rgba(255,255,255,0.75); padding: 4px 0;
  }
  .bd-modal-price-discount { color: #4ade80; }

  .bd-modal-footer {
    display: flex; align-items: center; justify-content: space-between; gap: 12px;
    padding-top: 18px; border-top: 1px dashed rgba(255,255,255,0.1);
  }
  .bd-modal-total { font-family: 'Jost', sans-serif; font-size: 19px; font-weight: 700; color: #e8c96b; }
  .bd-modal-btn {
    display: inline-flex; align-items: center; gap: 6px; padding: 11px 20px; border-radius: 100px;
    background: linear-gradient(90deg, #c9a84c, #e8c96b); color: #0d0d0d;
    font-family: 'Jost', sans-serif; font-size: 12px; font-weight: 800; letter-spacing: 0.06em;
    text-transform: uppercase; text-decoration: none; flex-shrink: 0; transition: opacity 0.2s ease;
  }
  .bd-modal-btn:hover { opacity: 0.88; }
  .bd-modal-ref { margin-top: 14px; font-family: 'Jost', sans-serif; font-size: 11px; color: rgba(255,255,255,0.3); text-align: center; }

  @media (max-width: 480px) {
    .bd-modal-grid { grid-template-columns: 1fr; }
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
    const statusEl = document.getElementById('bdModalStatus');
    statusEl.textContent = data.status || '';
    statusEl.classList.remove('bd-modal-status--bad', 'bd-modal-status--neutral');
    const badStatuses = ['cancelled', 'refunded', 'failed_needs_review', 'payment_failed'];
    const neutralStatuses = ['pending_payment', 'cancellation_pending'];
    if (badStatuses.includes(data.statusRaw)) {
      statusEl.classList.add('bd-modal-status--bad');
    } else if (neutralStatuses.includes(data.statusRaw)) {
      statusEl.classList.add('bd-modal-status--neutral');
    }

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

    const guestSection = document.getElementById('bdModalGuestSection');
    if (data.leadGuestName) {
      guestSection.hidden = false;
      document.getElementById('bdModalGuestName').textContent = data.leadGuestName;
      const contactParts = [data.guestEmail, data.guestPhone].filter(Boolean);
      document.getElementById('bdModalGuestContact').textContent = contactParts.join(' • ');
    } else {
      guestSection.hidden = true;
    }

    const requestSection = document.getElementById('bdModalRequestSection');
    if (data.specialRequests) {
      requestSection.hidden = false;
      document.getElementById('bdModalRequestText').textContent = data.specialRequests;
    } else {
      requestSection.hidden = true;
    }

    const cancelSection = document.getElementById('bdModalCancelSection');
    if (data.cancellationReason) {
      cancelSection.hidden = false;
      document.getElementById('bdModalCancelText').textContent = data.cancellationReason;
    } else {
      cancelSection.hidden = true;
    }

    const priceSection = document.getElementById('bdModalPriceSection');
    const baseRow = document.getElementById('bdModalBaseRow');
    const taxRow = document.getElementById('bdModalTaxRow');
    const discountRow = document.getElementById('bdModalDiscountRow');
    let hasPriceRow = false;

    if (data.baseAmount) {
      baseRow.hidden = false;
      document.getElementById('bdModalBaseValue').textContent = (data.currency || 'INR') + ' ' + data.baseAmount;
      hasPriceRow = true;
    } else {
      baseRow.hidden = true;
    }
    if (data.taxAmount) {
      taxRow.hidden = false;
      document.getElementById('bdModalTaxValue').textContent = (data.currency || 'INR') + ' ' + data.taxAmount;
      hasPriceRow = true;
    } else {
      taxRow.hidden = true;
    }
    if (data.discountAmount) {
      discountRow.hidden = false;
      document.getElementById('bdModalDiscountValue').textContent = '− ' + (data.currency || 'INR') + ' ' + data.discountAmount;
      hasPriceRow = true;
    } else {
      discountRow.hidden = true;
    }
    priceSection.hidden = !hasPriceRow;

    const totalWrap = document.getElementById('bdModalTotalWrap');
    if (data.total) {
      totalWrap.hidden = false;
      document.getElementById('bdModalTotalLabel').textContent = data.statusRaw === 'refunded' ? 'Total Refunded' : 'Total Paid';
      document.getElementById('bdModalTotal').textContent = (data.currency || 'INR') + ' ' + data.total;
    } else {
      totalWrap.hidden = true;
    }

    const invoiceLink = document.getElementById('bdModalInvoiceLink');
    if (data.invoiceUrl) {
      invoiceLink.hidden = false;
      invoiceLink.href = data.invoiceUrl;
    } else {
      invoiceLink.hidden = true;
    }

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
