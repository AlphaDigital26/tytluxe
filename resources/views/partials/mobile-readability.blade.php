{{--
  Phone readability (from the mobile check, 29 Oct 2026). Phones only: the
  computer layout is untouched. Loaded after every page's own styles, and
  marked !important because some pages add their styles inside the body.
    1. Labels and captions that were 9–10px are at least 11px.
    2. Buttons and links that were under ~30px get a comfortable tap area.
--}}
<style>
@media (max-width: 600px) {
  /* 1. Small labels → 11px */
  .frx-opt-fareid, .frx-view-details, .frx-modify-btn, .frx-rt-pick-label, .frx-rt-total-label,
  .tyt-airline-name, .tyt-sb-flabel, .tyt-label,
  .dest-card-country, .pf-label, .ta-team-role,
  .pd-section-label, .pd-day-tag, .pd-stay-type, .pd-info-label, .pd-book-label, .pd-dep-mon, .pd-price-label,
  .pkg-drawer-section-title, .pb-lead,
  .bd-stat-label, .info-label, .hd-qf-label, .hd-tag-highlight, .hd-tag-feature,
  .row-divider span, .hd-rate-price-per-night, .hd-mform-group label, .rx-field label,
  /* sizes written directly on the element */
  [style*="font-size: 9px"], [style*="font-size:9px"], [style*="font-size: 10px"], [style*="font-size:10px"] {
    font-size: 11px !important;
  }

  /* 2. Bigger tap areas */
  .mobile-toggle { min-width: 40px !important; min-height: 40px !important; }
  .htl-thumb-gallery-btn { min-width: 36px !important; min-height: 36px !important; }
  .tyt-sb-tab { min-height: 36px !important; }
  .frx-modify-btn, .frx-more-fares, .frx-details-toggle, .frx-leg-sort-btn, .bd-chip-more { min-height: 34px !important; }
  .frx-date-fare { min-height: 30px !important; }
  .pf-add-link, .pd-inc-stay-link, .bd-map-link, .pc-back {
    display: inline-flex !important; align-items: center; min-height: 32px !important;
  }
  /* Carousel dots keep their look; the touchable area around them grows to 32px. */
  .shared-slider-dots .shared-dot, .shared-dot, .blog-dot {
    box-sizing: content-box !important; padding: 12px !important; background-clip: content-box !important;
  }
}
</style>
