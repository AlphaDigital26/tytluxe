@php
  // $expiresAt: unix timestamp when the reviewed fare's TripJack session
  // (Review conditions.st) ends. $resultsUrl: where "Search again" goes.
  $timerUrl = $resultsUrl ?? route('flights.search');
@endphp
@if(! empty($expiresAt))
<div class="flt-timer" id="flrSessionTimer" data-expires="{{ (int) $expiresAt }}" data-now="{{ now()->timestamp }}" role="status" aria-live="off">
  <span class="flt-timer-icon" aria-hidden="true">&#9719;</span>
  <span>Fare held for <b id="flrSessionLeft">--:--</b> &middot; complete your booking before it runs out</span>
</div>

<div class="flt-expired" id="flrSessionExpired" role="alertdialog" aria-modal="true" aria-labelledby="flrSessionExpiredTitle" hidden>
  <div class="flt-expired-box">
    <div class="flt-expired-icon" aria-hidden="true">&#9719;</div>
    <h2 id="flrSessionExpiredTitle">Session Expired</h2>
    <p>The airline only holds a reviewed fare for a limited time, and this one has run out. Prices and seats may have changed — please pick your flight again to see the latest fares.</p>
    <a href="{{ $timerUrl }}" class="flr-submit">Search Again</a>
  </div>
</div>

<style>
  .flt-timer { max-width: 1100px; margin: -14px auto 22px; padding: 0 24px; display: flex; align-items: center; gap: 10px; font-family: 'Jost', sans-serif; font-size: 12.5px; color: var(--white-60); box-sizing: border-box; }
  .flt-timer > span:last-child { display: inline-flex; align-items: center; gap: 4px; flex-wrap: wrap; }
  .flt-timer b { font-variant-numeric: tabular-nums; font-size: 14px; color: var(--gold-light); min-width: 44px; display: inline-block; }
  .flt-timer-icon { width: 26px; height: 26px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; background: rgba(201,168,76,0.12); color: var(--gold); font-size: 13px; flex-shrink: 0; }
  .flt-timer.urgent b, .flt-timer.urgent .flt-timer-icon { color: #f3a3a3; }
  .flt-timer.urgent .flt-timer-icon { background: rgba(243,163,163,0.12); animation: fltPulse 1s ease-in-out infinite; }
  @keyframes fltPulse { 50% { opacity: 0.45; } }
  @media (prefers-reduced-motion: reduce) { .flt-timer.urgent .flt-timer-icon { animation: none; } }
  .flt-expired { position: fixed; inset: 0; z-index: 3000; display: flex; align-items: center; justify-content: center; padding: 24px; background: rgba(0,0,0,0.78); backdrop-filter: blur(3px); }
  .flt-expired[hidden] { display: none; }
  .flt-expired-box { width: 100%; max-width: 440px; text-align: center; background: var(--dark-2); border: 1px solid rgba(201,168,76,0.35); border-radius: 20px; padding: 34px 30px 28px; box-shadow: 0 24px 60px rgba(0,0,0,0.6); font-family: 'Jost', sans-serif; }
  .flt-expired-icon { width: 56px; height: 56px; margin: 0 auto 14px; border-radius: 50%; display: flex; align-items: center; justify-content: center; background: rgba(201,168,76,0.12); color: var(--gold); font-size: 26px; }
  .flt-expired-box h2 { font-family: 'Cormorant Garamond', serif; font-size: 1.7rem; color: var(--gold-light); margin: 0 0 10px; }
  .flt-expired-box p { font-size: 13px; line-height: 1.65; color: var(--white-60); margin: 0 0 20px; }
</style>

<script>
(function () {
  var timer = document.getElementById('flrSessionTimer');
  if (!timer) return;
  var left = document.getElementById('flrSessionLeft');
  var expired = document.getElementById('flrSessionExpired');
  // Measured against the server's clock: the guest's device clock may be
  // off, so only the remaining duration is taken from the page.
  var remainingAtLoad = parseInt(timer.dataset.expires, 10) - parseInt(timer.dataset.now, 10);
  var loadedAt = Date.now();

  function tick() {
    var secs = Math.max(0, Math.round(remainingAtLoad - (Date.now() - loadedAt) / 1000));
    left.textContent = Math.floor(secs / 60) + ':' + ('0' + (secs % 60)).slice(-2);
    timer.classList.toggle('urgent', secs <= 120);
    if (secs <= 0) {
      expired.hidden = false;
      // Nothing behind the popup can be booked any more.
      document.querySelectorAll('form button[type="submit"], .flr-submit:not(.flt-expired .flr-submit)').forEach(function (b) {
        b.setAttribute('disabled', 'disabled');
        b.setAttribute('aria-disabled', 'true');
        if (b.tagName === 'A') b.style.pointerEvents = 'none';
      });
      return;
    }
    setTimeout(tick, 1000);
  }
  tick();
})();
</script>
@endif
