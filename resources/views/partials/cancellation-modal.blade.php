<!-- Cancellation Policy Modal -->
<div id="tytCancellationModalBackdrop" class="tyt-cancellation-modal-backdrop" aria-hidden="true">
  <div class="tyt-cancellation-modal-card" role="dialog" aria-labelledby="tytCancelModalTitle">
    
    <!-- Modal Header -->
    <div class="tyt-cancellation-modal-header">
      <div>
        <h2 id="tytCancelModalTitle" class="tyt-cancellation-modal-title">Room with Cancellation Policy</h2>
        <div id="tytCancelModalSubtitle" class="tyt-cancellation-modal-sub"></div>
      </div>
      <button type="button" class="tyt-cancellation-modal-close" id="tytCancelModalCloseBtn" aria-label="Close cancellation policy modal">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <line x1="18" y1="6" x2="6" y2="18"></line>
          <line x1="6" y1="6" x2="18" y2="18"></line>
        </svg>
      </button>
    </div>

    <!-- Highlights Section -->
    <div class="tyt-cancellation-policy-head">Cancellation Policy :</div>
    <div class="tyt-cancellation-policy-bullets" id="tytCancelPolicyBullets">
      <!-- Injected via JavaScript -->
    </div>

    <!-- Visual Timeline Bar -->
    <div class="tyt-cancellation-timeline-wrap" id="tytCancelTimelineWrap">
      <div class="tyt-cancellation-bar-container" id="tytCancelBarContainer">
        <!-- Injected via JavaScript -->
      </div>
      <div class="tyt-cancellation-timeline-ticks" id="tytCancelTimelineTicks">
        <!-- Injected via JavaScript -->
      </div>
    </div>

    <!-- Explanatory Bullet -->
    <div class="tyt-cancellation-expl-note" id="tytCancelExplNote">
      <!-- Injected via JavaScript -->
    </div>

    <!-- Table Section -->
    <div class="tyt-cancellation-table-heading" id="tytCancelTableHeading">Cancellation post that will be subject to a fees as follows</div>
    <div class="tyt-cancellation-table-card">
      <div style="overflow-x: auto;">
        <table class="tyt-cancellation-table">
          <thead>
            <tr>
              <th>Cancellation On or After</th>
              <th>Cancellation On or Before</th>
              <th style="text-align: right;">Cancellation Charges/Comments</th>
            </tr>
          </thead>
          <tbody id="tytCancelTableBody">
            <!-- Injected via JavaScript -->
          </tbody>
        </table>
      </div>
      <div class="tyt-cancellation-table-notes">
        <div>• No Show will attract full cancellation charge unless otherwise specified.</div>
        <div>• Early check out will attract full cancellation charge unless otherwise specified.</div>
      </div>
    </div>

  </div>
</div>

<style>
  /* ===== Cancellation Policy Modal Styles ===== */
  .tyt-cancellation-modal-backdrop {
    position: fixed;
    inset: 0;
    z-index: 999999;
    background: rgba(0, 0, 0, 0.82);
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.28s ease;
  }
  .tyt-cancellation-modal-backdrop.open {
    opacity: 1;
    pointer-events: auto;
  }
  .tyt-cancellation-modal-card {
    background: #171717;
    border: 1px solid rgba(201, 168, 76, 0.28);
    border-radius: 20px;
    max-width: 660px;
    width: 100%;
    max-height: 90vh;
    overflow-y: auto;
    padding: 30px 34px;
    position: relative;
    box-shadow: 0 24px 70px rgba(0, 0, 0, 0.9), 0 0 35px rgba(201, 168, 76, 0.12);
    transform: translateY(20px) scale(0.97);
    transition: transform 0.32s cubic-bezier(0.25, 0.46, 0.45, 0.94);
    scrollbar-width: thin;
    scrollbar-color: rgba(201, 168, 76, 0.3) transparent;
  }
  .tyt-cancellation-modal-backdrop.open .tyt-cancellation-modal-card {
    transform: translateY(0) scale(1);
  }

  .tyt-cancellation-modal-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 16px;
    margin-bottom: 20px;
    padding-bottom: 16px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.08);
  }
  .tyt-cancellation-modal-title {
    font-family: 'Cormorant Garamond', serif;
    font-size: 23px;
    font-weight: 600;
    color: #ffffff;
    line-height: 1.25;
    margin: 0;
  }
  .tyt-cancellation-modal-sub {
    font-family: 'Jost', sans-serif;
    font-size: 13px;
    color: rgba(255, 255, 255, 0.55);
    margin-top: 4px;
  }
  .tyt-cancellation-modal-close {
    background: rgba(255, 255, 255, 0.06);
    border: 1px solid rgba(255, 255, 255, 0.14);
    color: rgba(255, 255, 255, 0.7);
    width: 34px;
    height: 34px;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s ease;
    flex-shrink: 0;
  }
  .tyt-cancellation-modal-close:hover {
    background: rgba(201, 168, 76, 0.22);
    border-color: #c9a84c;
    color: #ffffff;
    transform: rotate(90deg);
  }

  /* Policy Bullets */
  .tyt-cancellation-policy-head {
    font-family: 'Jost', sans-serif;
    font-size: 14px;
    font-weight: 600;
    color: #ffffff;
    margin-bottom: 10px;
    letter-spacing: 0.02em;
  }
  .tyt-cancellation-policy-bullets {
    display: flex;
    flex-direction: column;
    gap: 8px;
    margin-bottom: 22px;
  }
  .tyt-cancel-bullet-item {
    display: flex;
    align-items: center;
    gap: 10px;
    font-family: 'Jost', sans-serif;
    font-size: 13.5px;
    color: rgba(255, 255, 255, 0.85);
  }
  .tyt-cancel-bullet-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 19px;
    height: 19px;
    border-radius: 50%;
    font-size: 11px;
    font-weight: 700;
    flex-shrink: 0;
  }
  .tyt-cancel-bullet-icon.success {
    background: rgba(74, 222, 128, 0.16);
    color: #4ade80;
    border: 1px solid rgba(74, 222, 128, 0.38);
  }
  .tyt-cancel-bullet-icon.warning {
    background: rgba(245, 158, 11, 0.16);
    color: #fbbf24;
    border: 1px solid rgba(245, 158, 11, 0.38);
  }
  .tyt-cancel-bullet-icon.danger {
    background: rgba(248, 113, 113, 0.16);
    color: #f87171;
    border: 1px solid rgba(248, 113, 113, 0.38);
  }

  /* Progress Bar */
  .tyt-cancellation-timeline-wrap {
    margin-bottom: 20px;
  }
  .tyt-cancellation-bar-container {
    display: flex;
    height: 38px;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 4px 14px rgba(0, 0, 0, 0.35);
  }
  .tyt-cancellation-bar-seg {
    display: flex;
    align-items: center;
    justify-content: center;
    font-family: 'Jost', sans-serif;
    font-size: 13px;
    font-weight: 600;
    letter-spacing: 0.02em;
    padding: 0 8px;
    text-align: center;
    white-space: nowrap;
    transition: all 0.2s ease;
  }
  .tyt-cancellation-bar-seg.seg-full {
    background: #3b9186;
    color: #ffffff;
  }
  .tyt-cancellation-bar-seg.seg-partial {
    background: #fae4b5;
    color: #6b5016;
  }
  .tyt-cancellation-bar-seg.seg-none {
    background: #dc5652;
    color: #ffffff;
  }

  /* Timeline Ticks */
  .tyt-cancellation-timeline-ticks {
    display: flex;
    justify-content: space-between;
    margin-top: 8px;
    font-family: 'Jost', sans-serif;
    font-size: 11px;
    color: rgba(255, 255, 255, 0.62);
    gap: 8px;
  }
  .tyt-cancel-tick {
    line-height: 1.25;
  }
  .tyt-cancel-tick.start { text-align: left; }
  .tyt-cancel-tick.mid { text-align: center; }
  .tyt-cancel-tick.end { text-align: right; }
  .tyt-cancel-tick-sub {
    display: block;
    font-weight: 600;
    color: #dfc276;
    font-size: 10.5px;
    margin-top: 1px;
  }

  /* Explanatory Note */
  .tyt-cancellation-expl-note {
    font-family: 'Jost', sans-serif;
    font-size: 12.5px;
    color: rgba(255, 255, 255, 0.65);
    margin-bottom: 22px;
    line-height: 1.5;
  }

  /* Table */
  .tyt-cancellation-table-heading {
    font-family: 'Jost', sans-serif;
    font-size: 13.5px;
    font-weight: 600;
    color: #ffffff;
    margin-bottom: 12px;
    letter-spacing: 0.02em;
  }
  .tyt-cancellation-table-card {
    border: 1px solid rgba(255, 255, 255, 0.12);
    border-radius: 12px;
    overflow: hidden;
    background: rgba(255, 255, 255, 0.02);
  }
  .tyt-cancellation-table {
    width: 100%;
    border-collapse: collapse;
    font-family: 'Jost', sans-serif;
    font-size: 12.5px;
    min-width: 480px;
  }
  .tyt-cancellation-table th {
    background: rgba(255, 255, 255, 0.05);
    padding: 12px 14px;
    font-weight: 600;
    color: #dfc276;
    text-align: left;
    border-bottom: 1px solid rgba(255, 255, 255, 0.09);
    letter-spacing: 0.02em;
  }
  .tyt-cancellation-table td {
    padding: 11px 14px;
    color: rgba(255, 255, 255, 0.8);
    border-bottom: 1px solid rgba(255, 255, 255, 0.06);
  }
  .tyt-cancellation-table-notes {
    padding: 12px 16px;
    font-family: 'Jost', sans-serif;
    font-size: 11.5px;
    color: rgba(255, 255, 255, 0.52);
    line-height: 1.6;
    border-top: 1px solid rgba(255, 255, 255, 0.08);
    background: rgba(0, 0, 0, 0.25);
  }

  @media (max-width: 600px) {
    .tyt-cancellation-modal-card { padding: 22px 18px; }
    .tyt-cancellation-modal-title { font-size: 20px; }
    .tyt-cancellation-timeline-ticks { font-size: 10px; }
  }
</style>

<script>
(function() {
  function formatOrdinalDate(dateInput) {
    if (!dateInput) return '';
    const d = new Date(dateInput);
    if (isNaN(d.getTime())) return String(dateInput);
    const day = d.getDate();
    const suffix = (day === 1 || day === 21 || day === 31) ? 'st' :
                   (day === 2 || day === 22) ? 'nd' :
                   (day === 3 || day === 23) ? 'rd' : 'th';
    const months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
    const month = months[d.getMonth()];
    const year = d.getFullYear();
    let hours = d.getHours();
    const minutes = d.getMinutes().toString().padStart(2, '0');
    const ampm = hours >= 12 ? 'PM' : 'AM';
    hours = hours % 12;
    hours = hours ? hours : 12;
    return `${day}${suffix} ${month}, ${year}, ${hours}:${minutes} ${ampm}`;
  }

  function formatShortDate(dateInput) {
    if (!dateInput) return '';
    const d = new Date(dateInput);
    if (isNaN(d.getTime())) return String(dateInput);
    const day = d.getDate();
    const suffix = (day === 1 || day === 21 || day === 31) ? 'st' :
                   (day === 2 || day === 22) ? 'nd' :
                   (day === 3 || day === 23) ? 'rd' : 'th';
    const months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
    const month = months[d.getMonth()];
    let hours = d.getHours();
    const minutes = d.getMinutes().toString().padStart(2, '0');
    const ampm = hours >= 12 ? 'PM' : 'AM';
    hours = hours % 12;
    hours = hours ? hours : 12;
    return `${day}${suffix} ${month} ${hours}:${minutes} ${ampm}`;
  }

  function formatTableDate(dateInput) {
    if (!dateInput) return '—';
    const d = new Date(dateInput);
    if (isNaN(d.getTime())) return String(dateInput);
    const day = d.getDate().toString().padStart(2, '0');
    const month = (d.getMonth() + 1).toString().padStart(2, '0');
    const year = d.getFullYear();
    return `${day}-${month}-${year}`;
  }

  function formatCurrency(amount) {
    const num = Number(amount);
    if (isNaN(num) || num === 0) return '₹0.00';
    return '₹' + num.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  }

  window.openCancellationPolicyModal = function(data) {
    const backdrop = document.getElementById('tytCancellationModalBackdrop');
    if (!backdrop) return;

    let cancellation = data.cancellation;
    if (typeof cancellation === 'string') {
      try { cancellation = JSON.parse(cancellation); } catch(e) { cancellation = null; }
    }
    cancellation = cancellation || {};

    const roomName = data.roomName || data.room_name || '';
    const hotelTitle = data.hotelTitle || data.hotel_title || '';
    const checkIn = data.checkIn || data.checkin || '';
    const checkOut = data.checkOut || data.checkout || '';
    const customerPrice = data.price || data.customerPrice || 0;

    // Subtitle
    const subtitleEl = document.getElementById('tytCancelModalSubtitle');
    const parts = [];
    if (roomName) parts.push(roomName);
    if (hotelTitle) parts.push(hotelTitle);
    subtitleEl.textContent = parts.join(' · ');

    const isRefundable = cancellation.isRefundable ?? (data.isRefundable ?? true);
    const penalties = Array.isArray(cancellation.penalties) ? cancellation.penalties : [];

    const bulletsContainer = document.getElementById('tytCancelPolicyBullets');
    const barContainer = document.getElementById('tytCancelBarContainer');
    const ticksContainer = document.getElementById('tytCancelTimelineTicks');
    const explNoteEl = document.getElementById('tytCancelExplNote');
    const tableBody = document.getElementById('tytCancelTableBody');

    // Find 0 penalty (free cancellation window)
    const freeTier = penalties.find(p => Number(p.amount) === 0);
    // Find last penalty (non-refundable tier)
    const lastPenalty = penalties.length > 0 ? penalties[penalties.length - 1] : null;

    if (!isRefundable || (penalties.length > 0 && !freeTier)) {
      // Non-Refundable
      bulletsContainer.innerHTML = `
        <div class="tyt-cancel-bullet-item">
          <span class="tyt-cancel-bullet-icon danger">✕</span>
          <span><strong>Non-Refundable Rate</strong>: This booking cannot be cancelled or modified for free.</span>
        </div>
      `;

      barContainer.innerHTML = `
        <div class="tyt-cancellation-bar-seg seg-none" style="flex: 1;">Non Refundable</div>
      `;

      ticksContainer.innerHTML = `
        <div class="tyt-cancel-tick start">Now</div>
        <div class="tyt-cancel-tick end">
          ${checkIn ? formatShortDate(checkIn) : 'Check-In'}
          <span class="tyt-cancel-tick-sub">Check-In</span>
        </div>
      `;

      explNoteEl.innerHTML = `• 100% cancellation charges apply if you cancel, amend, or fail to arrive.`;

      tableBody.innerHTML = `
        <tr>
          <td>Booking Date</td>
          <td>${checkIn ? formatTableDate(checkIn) : 'Check-In Date'}</td>
          <td style="text-align:right; font-weight:600; color:#f87171;">${customerPrice ? formatCurrency(customerPrice) : '100% Charge'}</td>
        </tr>
      `;
    } else {
      // Refundable with free cancellation
      const freeUntilStr = freeTier ? formatOrdinalDate(freeTier.to) : '';
      const noRefundStr = lastPenalty ? formatOrdinalDate(lastPenalty.from) : '';

      bulletsContainer.innerHTML = `
        ${freeUntilStr ? `
        <div class="tyt-cancel-bullet-item">
          <span class="tyt-cancel-bullet-icon success">✓</span>
          <span>Free Cancellation till <strong>${freeUntilStr}</strong></span>
        </div>` : ''}
        ${noRefundStr && noRefundStr !== freeUntilStr ? `
        <div class="tyt-cancel-bullet-item">
          <span class="tyt-cancel-bullet-icon warning">✓</span>
          <span>No Refund if canceled after <strong>${noRefundStr}</strong></span>
        </div>` : ''}
      `;

      // Build Segments & Ticks
      if (penalties.length >= 3) {
        // 3 segments: 100% refund, partial, non-refundable
        barContainer.innerHTML = `
          <div class="tyt-cancellation-bar-seg seg-full" style="flex: 1.2;">100% Refund</div>
          <div class="tyt-cancellation-bar-seg seg-partial" style="flex: 1;"></div>
          <div class="tyt-cancellation-bar-seg seg-none" style="flex: 1.2;">Non Refundable</div>
        `;

        ticksContainer.innerHTML = `
          <div class="tyt-cancel-tick start">Now</div>
          <div class="tyt-cancel-tick mid">${formatShortDate(penalties[0].to)}</div>
          <div class="tyt-cancel-tick mid">${formatShortDate(penalties[1].to)}</div>
          <div class="tyt-cancel-tick end">
            ${penalties[2].to ? formatShortDate(penalties[2].to) : (checkIn ? formatShortDate(checkIn) : 'Check-In')}
            <span class="tyt-cancel-tick-sub">Check-In</span>
          </div>
        `;
      } else if (penalties.length === 2) {
        // 2 segments: 100% refund, non-refundable
        barContainer.innerHTML = `
          <div class="tyt-cancellation-bar-seg seg-full" style="flex: 1;">100% Refund</div>
          <div class="tyt-cancellation-bar-seg seg-none" style="flex: 1;">Non Refundable</div>
        `;

        ticksContainer.innerHTML = `
          <div class="tyt-cancel-tick start">Now</div>
          <div class="tyt-cancel-tick mid">${formatShortDate(penalties[0].to)}</div>
          <div class="tyt-cancel-tick end">
            ${penalties[1].to ? formatShortDate(penalties[1].to) : (checkIn ? formatShortDate(checkIn) : 'Check-In')}
            <span class="tyt-cancel-tick-sub">Check-In</span>
          </div>
        `;
      } else {
        // 1 segment: 100% refund
        barContainer.innerHTML = `
          <div class="tyt-cancellation-bar-seg seg-full" style="flex: 1;">100% Refund</div>
        `;

        ticksContainer.innerHTML = `
          <div class="tyt-cancel-tick start">Now</div>
          <div class="tyt-cancel-tick end">
            ${freeTier && freeTier.to ? formatShortDate(freeTier.to) : (checkIn ? formatShortDate(checkIn) : 'Check-In')}
            <span class="tyt-cancel-tick-sub">Free Until</span>
          </div>
        `;
      }

      explNoteEl.innerHTML = freeUntilStr 
        ? `• Free Cancellation (100% refund) if you cancel this booking before ${freeUntilStr}`
        : `• Free Cancellation (100% refund) is available for this room option.`;

      // Table rows
      if (penalties.length > 0) {
        tableBody.innerHTML = penalties.map(p => `
          <tr>
            <td>${formatTableDate(p.from)}</td>
            <td>${formatTableDate(p.to)}</td>
            <td style="text-align: right; font-weight: 600; color: ${Number(p.amount) === 0 ? '#4ade80' : '#ffffff'};">
              ${formatCurrency(p.amount)}
            </td>
          </tr>
        `).join('');
      } else {
        tableBody.innerHTML = `
          <tr>
            <td>Now</td>
            <td>48 hours before check-in</td>
            <td style="text-align: right; font-weight: 600; color: #4ade80;">₹0.00 (100% Refund)</td>
          </tr>
          <tr>
            <td>48 hours before check-in</td>
            <td>Check-In</td>
            <td style="text-align: right; font-weight: 600; color: #f87171;">100% Charge</td>
          </tr>
        `;
      }
    }

    backdrop.classList.add('open');
    document.body.style.overflow = 'hidden';
  };

  window.closeCancellationPolicyModal = function() {
    const backdrop = document.getElementById('tytCancellationModalBackdrop');
    if (!backdrop) return;
    backdrop.classList.remove('open');
    document.body.style.overflow = '';
  };

  // Close triggers
  document.addEventListener('click', function(e) {
    if (e.target && (e.target.id === 'tytCancelModalCloseBtn' || e.target.closest('#tytCancelModalCloseBtn'))) {
      e.preventDefault();
      closeCancellationPolicyModal();
      return;
    }
    if (e.target && e.target.id === 'tytCancellationModalBackdrop') {
      closeCancellationPolicyModal();
      return;
    }

    // Auto trigger listener on any element with .htl-cancel-policy-trigger or data-open-cancel-modal
    const trigger = e.target.closest('.htl-cancel-policy-trigger, [data-open-cancel-modal]');
    if (trigger) {
      e.preventDefault();
      e.stopPropagation();

      let cancellationData = trigger.getAttribute('data-cancellation');
      if (cancellationData) {
        try {
          cancellationData = JSON.parse(cancellationData);
        } catch(err) {
          console.warn('Could not parse data-cancellation JSON', err);
        }
      }

      window.openCancellationPolicyModal({
        cancellation: cancellationData,
        isRefundable: trigger.getAttribute('data-refundable') !== 'false',
        roomName: trigger.getAttribute('data-room-name') || '',
        hotelTitle: trigger.getAttribute('data-hotel-title') || '',
        checkIn: trigger.getAttribute('data-checkin') || '',
        checkOut: trigger.getAttribute('data-checkout') || '',
        price: trigger.getAttribute('data-price') || 0
      });
    }
  });

  // Close on ESC
  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
      closeCancellationPolicyModal();
    }
  });
})();
</script>
