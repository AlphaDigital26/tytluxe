{{--
  Phone-only bottom tab bar, so the site feels like an app (chosen design:
  "Option 1 – Bottom tab bar"). Hidden on computers, and on booking steps
  (flight results, guest details, payment, checkout) so those screens keep
  their full height and their own buttons.
--}}
@php
  $tabbarHidden = request()->is(
      'flights/search', 'flights/review*', 'hotels/*/review', 'packages/*/book',
      'booking/*/pay', 'booking/*/package/balance', 'booking/*/flight/*', 'booking/*/cancel'
  );
  $tabs = [
      ['label' => 'Home',     'url' => route('home'),     'icon' => 'fa-house',         'active' => request()->is('/')],
      ['label' => 'Hotels',   'url' => route('hotels'),   'icon' => 'fa-hotel',         'active' => request()->is('hotels*')],
      ['label' => 'Flights',  'url' => route('flights'),  'icon' => 'fa-plane',         'active' => request()->is('flights*')],
      ['label' => 'Packages', 'url' => route('packages'), 'icon' => 'fa-suitcase-rolling', 'active' => request()->is('packages*')],
      ['label' => 'My Trips', 'url' => auth()->check() ? route('history') : route('login'), 'icon' => 'fa-user', 'active' => request()->is('history', 'profile*', 'booking/*', 'wishlist')],
  ];
@endphp
@unless($tabbarHidden)
<nav class="tyt-tabbar" aria-label="Main">
  @foreach($tabs as $tab)
    <a href="{{ $tab['url'] }}" class="tyt-tab{{ $tab['active'] ? ' is-active' : '' }}" @if($tab['active']) aria-current="page" @endif>
      <i class="fa-solid {{ $tab['icon'] }}" aria-hidden="true"></i>
      <span>{{ $tab['label'] }}</span>
    </a>
  @endforeach
</nav>
<style>
.tyt-tabbar { display: none; }
@media (max-width: 768px) {
  .tyt-tabbar {
    display: grid; grid-template-columns: repeat(5, minmax(0, 1fr));
    position: fixed; left: 0; right: 0; bottom: 0; z-index: 9000;
    background: rgba(18, 18, 18, 0.97); border-top: 1px solid rgba(201, 168, 76, 0.18);
    padding-bottom: env(safe-area-inset-bottom, 0px);
    -webkit-backdrop-filter: blur(12px); backdrop-filter: blur(12px);
  }
  .tyt-tab {
    min-height: 60px; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 4px;
    color: #a39b8b; text-decoration: none; font-size: 11px; letter-spacing: 0.02em;
    -webkit-tap-highlight-color: transparent;
  }
  .tyt-tab i { font-size: 19px; }
  .tyt-tab.is-active { color: var(--gold, #c9a84c); font-weight: 600; }
  .tyt-tab:active { transform: scale(0.94); }
  /* Room so the last of the page (footer, buttons) is never hidden behind the bar. */
  body { padding-bottom: calc(60px + env(safe-area-inset-bottom, 0px)); }
  .tyt-toast { bottom: calc(76px + env(safe-area-inset-bottom, 0px)) !important; }
}
</style>
@endunless
