/*
 * Keeps the page behind any open popup from scrolling, on every page.
 *
 * style.css gives both <html> and <body> overflow-x: hidden, which makes
 * <html> the element that scrolls — so the popups that lock the page with
 * body.style.overflow = 'hidden' were locking the wrong element and the
 * background still scrolled. This locks <html> (class tyt-scroll-locked,
 * see style.css) whenever a popup is open:
 *   - a popup that already set body.style.overflow = 'hidden'
 *   - a native <dialog> opened with showModal()
 *   - any fixed overlay covering the screen (backdrops, compare / fare
 *     alert popups, lightboxes), so new popups are covered too.
 * It re-checks after any change on the page, at most once per frame.
 */
(function () {
  var root = document.documentElement;
  var CANDIDATES = '[aria-modal="true"], [role="dialog"], [class*="modal"], [class*="overlay"], [class*="backdrop"], [class*="lightbox"], [class*="popup"]';

  function coversScreen(el) {
    var cs = getComputedStyle(el);
    if (cs.position !== 'fixed' || cs.display === 'none' || cs.visibility === 'hidden' || parseFloat(cs.opacity) === 0) return false;
    var r = el.getBoundingClientRect();
    var w = window.innerWidth, h = window.innerHeight;
    return r.left <= w * 0.1 && r.top <= h * 0.1 && r.right >= w * 0.9 && r.bottom >= h * 0.9;
  }

  function popupOpen() {
    if (document.body.style.overflow === 'hidden') return true;
    if (document.querySelector('dialog[open]')) return true;
    var els = document.querySelectorAll(CANDIDATES);
    for (var i = 0; i < els.length; i++) {
      if (coversScreen(els[i])) return true;
    }
    return false;
  }

  var queued = false;
  function check() {
    queued = false;
    root.classList.toggle('tyt-scroll-locked', popupOpen());
  }
  function queue() {
    if (!queued) { queued = true; requestAnimationFrame(check); }
  }

  new MutationObserver(queue).observe(document.body, {
    subtree: true, childList: true, attributes: true,
    attributeFilter: ['class', 'style', 'open', 'hidden', 'aria-hidden'],
  });
  // Opening/closing animations finish after the attribute change.
  document.addEventListener('transitionend', queue, true);
  window.addEventListener('resize', queue);
  check();
})();
