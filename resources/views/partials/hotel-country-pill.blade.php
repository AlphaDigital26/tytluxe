{{--
  "More Options" pill with a searchable country list, for the hotel search
  bar (Nationality, Country of Residence). Submits as a hidden input.
  Expects: $pillId, $name, $label, $value, $nationalities (countryId/countryName).
--}}
@php
  $selectedName = collect($nationalities ?? [])->first(fn ($n) => (string) $n['countryId'] === (string) $value)['countryName'] ?? 'India';
@endphp
@once
<style>
  .htl-cc-wrap .htl-mo-dropdown { min-width: 240px; padding: 8px; }
  .htl-cc-search {
    width: 100%; box-sizing: border-box; background: #111; color: #fff; border: 1px solid rgba(201,168,76,0.3);
    border-radius: 8px; padding: 8px 10px; margin-bottom: 6px; font-family: 'Jost', sans-serif; font-size: 13px; outline: none;
  }
  .htl-cc-search:focus { border-color: var(--gold); }
  .htl-cc-list { max-height: 260px; overflow-y: auto; display: flex; flex-direction: column; gap: 2px; }
  .htl-cc-empty { padding: 8px 12px; color: rgba(255,255,255,0.5); font-family: 'Jost', sans-serif; font-size: 13px; }
</style>
@endonce
<div class="htl-mo-dropdown-wrap htl-cc-wrap" id="{{ $pillId }}">
  <button type="button" class="htl-mo-pill htl-cc-trigger" aria-haspopup="listbox" aria-expanded="false">
    <span class="htl-mo-pill-tag">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
      {{ $label }}:
    </span>
    <span class="htl-mo-current-val htl-cc-current">{{ $selectedName }}</span>
    <svg class="htl-mo-chevron" viewBox="0 0 12 12"><path fill="currentColor" d="M6 8L1 3h10z"/></svg>
  </button>

  <input type="hidden" name="{{ $name }}" class="htl-cc-input" id="{{ $pillId }}Input" value="{{ $value }}">

  <div class="htl-mo-dropdown" role="listbox" aria-label="{{ $label }}" onclick="event.stopPropagation()">
    <input type="text" class="htl-cc-search" placeholder="Search country" autocomplete="off" aria-label="Search {{ $label }}">
    <div class="htl-cc-list">
      @foreach($nationalities ?? [] as $n)
        <div class="htl-cc-opt {{ (string) $n['countryId'] === (string) $value ? 'selected' : '' }}"
             data-value="{{ $n['countryId'] }}" data-name="{{ $n['countryName'] }}" role="option">
          <span>{{ $n['countryName'] }}</span>
        </div>
      @endforeach
      <div class="htl-cc-empty" hidden>No countries found</div>
    </div>
  </div>
</div>
@once
<style>
  .htl-cc-opt { padding: 8px 12px; border-radius: 8px; color: #fff; font-family: 'Jost', sans-serif; font-size: 13px; cursor: pointer; }
  .htl-cc-opt:hover { background: rgba(201,168,76,0.12); color: var(--gold-light); }
  .htl-cc-opt.selected { background: rgba(201,168,76,0.16); color: var(--gold-light); font-weight: 600; }
</style>
<script>
  document.addEventListener('DOMContentLoaded', function () {
    const wraps = document.querySelectorAll('.htl-cc-wrap');
    const closeAll = (except) => wraps.forEach(w => {
      if (w !== except) { w.classList.remove('open'); w.querySelector('.htl-cc-trigger').setAttribute('aria-expanded', 'false'); }
    });
    wraps.forEach(function (wrap) {
      const trigger = wrap.querySelector('.htl-cc-trigger');
      const input = wrap.querySelector('.htl-cc-input');
      const current = wrap.querySelector('.htl-cc-current');
      const search = wrap.querySelector('.htl-cc-search');
      const opts = wrap.querySelectorAll('.htl-cc-opt');
      const empty = wrap.querySelector('.htl-cc-empty');

      trigger.addEventListener('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        closeAll(wrap);
        const open = wrap.classList.toggle('open');
        trigger.setAttribute('aria-expanded', open ? 'true' : 'false');
        if (open) {
          search.value = '';
          search.dispatchEvent(new Event('input'));
          search.focus();
          const sel = wrap.querySelector('.htl-cc-opt.selected');
          if (sel) sel.scrollIntoView({ block: 'nearest' });
        }
      });
      search.addEventListener('input', function () {
        const q = search.value.trim().toLowerCase();
        let shown = 0;
        opts.forEach(o => { const hit = o.dataset.name.toLowerCase().includes(q); o.hidden = !hit; if (hit) shown++; });
        empty.hidden = shown > 0;
      });
      search.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') {
          e.preventDefault(); // never submit the search form from here
          const first = Array.from(opts).find(o => !o.hidden);
          if (first) first.click();
        } else if (e.key === 'Escape') {
          wrap.classList.remove('open');
          trigger.setAttribute('aria-expanded', 'false');
          trigger.focus();
        }
      });
      opts.forEach(function (opt) {
        opt.addEventListener('click', function () {
          opts.forEach(o => o.classList.remove('selected'));
          opt.classList.add('selected');
          input.value = opt.dataset.value;
          current.textContent = opt.dataset.name;
          wrap.classList.remove('open');
          trigger.setAttribute('aria-expanded', 'false');
        });
      });
    });
    document.addEventListener('click', function (e) {
      wraps.forEach(w => { if (!w.contains(e.target)) { w.classList.remove('open'); w.querySelector('.htl-cc-trigger').setAttribute('aria-expanded', 'false'); } });
    });
  });
</script>
@endonce
