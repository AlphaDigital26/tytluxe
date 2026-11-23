{{--
  Country picker (Nationality / Country of Residence) inside the hotel
  page's Rooms & Guests popover. 106 = India is the default.
  Expects: $selectId, $name, $label, $value, $nationalities (countryId/countryName).
--}}
@once
<style>
  .htl-guest-nat { margin: 4px 0 10px; padding-top: 10px; border-top: 1px solid rgba(255,255,255,0.08); }
  .htl-guest-nat label { display: block; font-family: 'Jost', sans-serif; font-size: 11px; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; color: rgba(255,255,255,0.6); margin-bottom: 6px; }
  .htl-guest-nat select {
    width: 100%; background: #111; color: #fff; border: 1px solid rgba(201,168,76,0.35); border-radius: 8px;
    padding: 8px 10px; font-family: 'Jost', sans-serif; font-size: 13px; cursor: pointer;
  }
  .htl-guest-nat select:focus { outline: none; border-color: var(--gold); }
</style>
@endonce
<div class="htl-guest-nat">
  <label for="{{ $selectId }}">{{ $label ?? 'Nationality' }}</label>
  <select id="{{ $selectId }}" name="{{ $name ?? 'nationality' }}">
    @foreach($nationalities ?? [] as $n)
      <option value="{{ $n['countryId'] }}" @selected((string) $n['countryId'] === (string) ($value ?? '106'))>{{ $n['countryName'] }}</option>
    @endforeach
  </select>
</div>
