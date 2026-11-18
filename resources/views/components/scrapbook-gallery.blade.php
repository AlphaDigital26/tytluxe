{{--
  scrapbook-gallery.blade.php
  -------------------------
  Reusable Blade component for a scattered, pinboard-style photo gallery.

  Props:
    $items   – Collection of objects with:
                  ->image_url  (string, full URL)
                  ->title      (string)
                  ->caption    (string, optional)
    $label   – Section eyebrow label (default: "Memories")
    $heading – Section heading text (default: "Gallery")
    $em      – Part of heading to italicise/gold (optional)
--}}

@props([
  'items'   => collect(),
  'label'   => 'Memories',
  'heading' => 'Gallery',
  'em'      => '',
])

<section class="scrapbook-section">

  {{-- ── Header ─────────────────────────────────── --}}
  <div class="scrapbook-section-label">{{ $label }}</div>
  <h2 class="scrapbook-section-title">
    {{ $heading }}
    @if($em)
      <em>{{ $em }}</em>
    @endif
  </h2>

  {{-- ── Board: one row, scrolled sideways (swipe, trackpad or the arrows) ── --}}
  <div class="scrapbook-scroller">
  <button type="button" class="scrapbook-arrow scrapbook-arrow-prev" aria-label="Previous photos" hidden>&#8249;</button>
  <button type="button" class="scrapbook-arrow scrapbook-arrow-next" aria-label="Next photos" hidden>&#8250;</button>
  <div class="scrapbook-board">

    @foreach ($items as $i => $item)
      @php
        // Alternate decorations for the scrapbook feel
        $decor = ($i % 2 === 0) ? 'tape' : 'pin';
      @endphp
      <article class="scrapbook-photo" data-decor="{{ $decor }}">
        <img
          src="{{ $item['image_url'] }}"
          alt="{{ $item['title'] }}"
          loading="lazy"
          draggable="false"
        >
      </article>
    @endforeach

  </div>
  </div>

</section>

@once
<script>
document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('.scrapbook-scroller').forEach(function (wrap) {
    var board = wrap.querySelector('.scrapbook-board');
    var prev = wrap.querySelector('.scrapbook-arrow-prev');
    var next = wrap.querySelector('.scrapbook-arrow-next');
    function update() {
      var max = board.scrollWidth - board.clientWidth;
      prev.hidden = board.scrollLeft <= 4;
      next.hidden = board.scrollLeft >= max - 4;
      // Fade out any card not fully inside the board, so none shows half cut.
      var r = board.getBoundingClientRect();
      var left = r.left + parseFloat(getComputedStyle(board).paddingLeft) - 30;
      var right = r.right - parseFloat(getComputedStyle(board).paddingRight) + 30;
      Array.prototype.forEach.call(board.children, function (c) {
        var box = c.offsetLeft - board.scrollLeft + r.left;
        c.classList.toggle('is-out', box < left || box + c.offsetWidth > right);
      });
    }
    // One card per click: the distance from one card to the next.
    function step() {
      var cards = board.children;
      return cards.length > 1 ? cards[1].offsetLeft - cards[0].offsetLeft : board.clientWidth;
    }
    prev.addEventListener('click', function () { board.scrollBy({ left: -step(), behavior: 'smooth' }); });
    next.addEventListener('click', function () { board.scrollBy({ left: step(), behavior: 'smooth' }); });
    // Show only whole cards: narrow the board to the number of cards that
    // fully fit (never a card cut in half at the edge).
    function fit() {
      var cards = board.children;
      board.style.width = '';
      board.style.removeProperty('--card-w');
      if (cards.length < 2) return;
      var cs = getComputedStyle(board);
      var pad = parseFloat(cs.paddingLeft) + parseFloat(cs.paddingRight);
      var room = wrap.clientWidth - parseFloat(getComputedStyle(wrap).paddingLeft) - parseFloat(getComputedStyle(wrap).paddingRight) - pad;
      // Desktop: 4 whole cards at a time, shrinking the cards a little if needed.
      if (window.innerWidth > 1024 && cards.length > 4) {
        var space = parseFloat(cs.columnGap) || 0;
        board.style.setProperty('--card-w', Math.min(280, Math.floor((room - 3 * space) / 4)) + 'px');
      }
      var card = cards[0].offsetWidth, gap = step();
      var n = Math.max(1, Math.min(cards.length, Math.floor((room - card) / gap) + 1));
      if (n < cards.length) board.style.width = ((n - 1) * gap + card + pad) + 'px';
    }
    board.addEventListener('scroll', update, { passive: true });
    window.addEventListener('resize', function () { fit(); update(); });
    fit();
    update();
  });
});
</script>
@endonce
