import { gsap } from "gsap";

/**
 * Horizontal 3D Stacked Card Carousel
 * ------------------------------------
 * - Pure GSAP – no Swiper / no CSS classes per-position
 * - Only manipulates: translateX, scale, rotateY, z-index, opacity
 * - Zero translateY movement
 * - Autoplay with pause-on-hover
 * - Previous / next controls
 * - Touch / mouse drag (horizontal only)
 */

export function initCardCarousel(container) {
  if (!container) return;

  const track = container.querySelector(".carousel-track");
  const cards = Array.from(container.querySelectorAll(".carousel-card"));
  const prevBtn = container.querySelector(".carousel-btn-prev");
  const nextBtn = container.querySelector(".carousel-btn-next");
  const dots = Array.from(container.querySelectorAll(".carousel-dot"));
  const AUTOPLAY_DELAY = 3000; // ms between advances

  if (!cards.length) return;

  const total = cards.length;
  let activeIndex = 0;
  let isAnimating = false;
  let autoplayTimer = null;
  let isDragging = false;
  let dragStartX = 0;
  let dragDeltaX = 0;

  /* ── Position model ───────────────────────────── */
  // Returns the transform parameters for a given distance from the active card.
  // position 0 = active (center), ±1, ±2, ±3 …
  function getPositionParams(position) {
    const absPos = Math.abs(position);
    const sign = position < 0 ? -1 : 1;

    // Desktop defaults (scaled down on mobile inside updateAll)
    const xStep = getXStep();
    const scaleStep = 0.06;
    const rotateStep = 4; // subtle deg per step
    const opacityMin = 0.0;

    // How many cards we show each side
    const maxVisible = getMaxVisible();

    if (absPos > maxVisible) {
      // Fully hidden: park just outside the visible range, no opacity
      return {
        x: sign * xStep * (maxVisible + 1),
        scale: 0.55,
        rotateY: sign * rotateStep * maxVisible,
        zIndex: 0,
        opacity: 0,
        display: "none",
      };
    }

    const x = sign * xStep * absPos;
    const scale = Math.max(0.55, 1 - scaleStep * absPos);
    const rotateY = sign * rotateStep * absPos;
    const zIndex = 100 - absPos * 10;
    // Fade out gradually toward edges
    const opacity = absPos === 0 ? 1 : Math.max(opacityMin, 1 - absPos * 0.15);

    return { x, scale, rotateY, zIndex, opacity, display: "block" };
  }

  function getXStep() {
    const w = window.innerWidth;
    if (w <= 575) return 90;
    if (w <= 991) return 110;
    return 130; // tighter overlap for a proper "stack" look
  }

  function getMaxVisible() {
    const w = window.innerWidth;
    if (w <= 575) return 2;   // 5 cards
    if (w <= 991) return 3;   // 7 cards
    return 4;                 // 9 cards
  }

  /* ── Core: animate a single card to its position ─ */
  function setCardPosition(card, position, instant = false) {
    const params = getPositionParams(position);
    const dur = instant ? 0 : 0.9;
    const ease = "power3.inOut";

    // Remove/add active class for caption visibility
    if (position === 0) {
      card.classList.add("is-active");
    } else {
      card.classList.remove("is-active");
    }

    if (params.display === "none") {
      gsap.set(card, { display: "none", opacity: 0 });
      return;
    }

    gsap.to(card, {
      display: "block",
      x: params.x,
      y: 0,           // ALWAYS force y=0 – no vertical movement ever
      scale: params.scale,
      rotateY: params.rotateY,
      zIndex: params.zIndex,
      opacity: params.opacity,
      duration: dur,
      ease: ease,
      overwrite: "auto",
      transformPerspective: 1400,
    });
  }

  /* ── Update all cards relative to the new activeIndex ─ */
  function updateAll(instant = false) {
    cards.forEach((card, i) => {
      // Compute position relative to active (with wrapping)
      let pos = i - activeIndex;
      // Wrap to shortest path around the loop
      if (pos > total / 2) pos -= total;
      if (pos < -total / 2) pos += total;

      setCardPosition(card, pos, instant);
    });

    // Update dots
    dots.forEach((dot, i) => {
      dot.classList.toggle("is-active", i === activeIndex);
    });
  }

  /* ── Navigate ────────────────────────────────────── */
  function advance(direction) {
    if (isAnimating) return;
    isAnimating = true;

    activeIndex = (activeIndex + direction + total) % total;
    updateAll();

    setTimeout(() => {
      isAnimating = false;
    }, 950);
  }

  /* ── Autoplay ─────────────────────────────────── */
  function startAutoplay() {
    stopAutoplay();
    autoplayTimer = setInterval(() => {
      if (!isDragging) advance(1);
    }, AUTOPLAY_DELAY);
  }

  function stopAutoplay() {
    if (autoplayTimer) {
      clearInterval(autoplayTimer);
      autoplayTimer = null;
    }
  }

  /* ── Controls ────────────────────────────────── */
  if (prevBtn) prevBtn.addEventListener("click", () => { advance(-1); restartAutoplay(); });
  if (nextBtn) nextBtn.addEventListener("click", () => { advance(1);  restartAutoplay(); });

  dots.forEach((dot, i) => {
    dot.addEventListener("click", () => {
      if (i === activeIndex || isAnimating) return;
      isAnimating = true;
      activeIndex = i;
      updateAll();
      setTimeout(() => { isAnimating = false; }, 950);
      restartAutoplay();
    });
  });

  function restartAutoplay() {
    stopAutoplay();
    startAutoplay();
  }

  /* ── Pause on hover ───────────────────────────── */
  container.addEventListener("mouseenter", stopAutoplay);
  container.addEventListener("mouseleave", startAutoplay);

  /* ── Touch / Mouse drag (horizontal only) ─────── */
  function onDragStart(clientX) {
    isDragging = true;
    dragStartX = clientX;
    dragDeltaX = 0;
    stopAutoplay();
  }

  function onDragMove(clientX) {
    if (!isDragging) return;
    dragDeltaX = clientX - dragStartX;
  }

  function onDragEnd() {
    if (!isDragging) return;
    isDragging = false;
    const threshold = 60;
    if (Math.abs(dragDeltaX) > threshold) {
      advance(dragDeltaX < 0 ? 1 : -1);
    }
    startAutoplay();
    dragDeltaX = 0;
  }

  // Mouse
  track.addEventListener("mousedown",  (e) => onDragStart(e.clientX));
  window.addEventListener("mousemove", (e) => { if (isDragging) onDragMove(e.clientX); });
  window.addEventListener("mouseup",   () => onDragEnd());

  // Touch
  track.addEventListener("touchstart", (e) => onDragStart(e.touches[0].clientX), { passive: true });
  track.addEventListener("touchmove",  (e) => onDragMove(e.touches[0].clientX),  { passive: true });
  track.addEventListener("touchend",   () => onDragEnd());

  // Click individual card to centre it
  cards.forEach((card, i) => {
    card.addEventListener("click", () => {
      if (Math.abs(dragDeltaX) > 5) return; // was a drag, not a click
      if (i === activeIndex || isAnimating) return;
      isAnimating = true;
      activeIndex = i;
      updateAll();
      setTimeout(() => { isAnimating = false; }, 950);
      restartAutoplay();
    });
  });

  /* ── Keyboard ────────────────────────────────── */
  document.addEventListener("keydown", (e) => {
    if (!document.contains(container)) return;
    if (e.key === "ArrowLeft")  { advance(-1); restartAutoplay(); }
    if (e.key === "ArrowRight") { advance(1);  restartAutoplay(); }
  });

  /* ── Resize handler ───────────────────────────── */
  let resizeTimer;
  window.addEventListener("resize", () => {
    clearTimeout(resizeTimer);
    resizeTimer = setTimeout(() => updateAll(true), 200);
  });

  /* ── Initialise ──────────────────────────────── */
  // Force all cards visible and positioned instantly
  gsap.set(cards, { display: "block", y: 0 });
  updateAll(true);
  startAutoplay();
}

/* ── Auto-initialise every carousel on the page ─── */
document.addEventListener("DOMContentLoaded", () => {
  document.querySelectorAll(".carousel-section[data-carousel]").forEach((section) => {
    initCardCarousel(section);
  });
});
