{{-- One PAN on the hotel guest-details page: name, number, and the guests it covers. --}}
@php $chosen = (array) ($pan['guests'] ?? []); @endphp
<div class="br-pan-card" data-k="{{ $k }}">
  <div class="br-pan-fields">
    <div class="br-field">
      <label for="brPanName{{ $k }}">Name as per PAN <span class="br-pan-whose"></span></label>
      <input type="text" id="brPanName{{ $k }}" name="pans[{{ $k }}][name]" value="{{ $pan['name'] ?? '' }}" placeholder="e.g. Rahul Sharma" class="br-pan-name" required>
    </div>
    <div class="br-field">
      <label for="brPanNo{{ $k }}">PAN</label>
      <input type="text" id="brPanNo{{ $k }}" name="pans[{{ $k }}][number]" value="{{ $pan['number'] ?? '' }}" placeholder="ABCPE1234F" maxlength="10"
        pattern="[A-Za-z]{3}[ABCFGHJLPTKEabcfghjlptke][A-Za-z][0-9]{4}[A-Za-z]" title="Enter a valid PAN, e.g. ABCPE1234F" style="text-transform:uppercase;" required>
    </div>
    <button type="button" class="br-pan-remove" aria-label="Remove this PAN">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 6h18M8 6V4h8v2M19 6l-1 14H6L5 6M10 11v6M14 11v6"/></svg>
    </button>
  </div>
  {{-- Each guest on this PAN, one per line: who they are, their name, the PAN. --}}
  <ul class="br-pan-holders" aria-label="Guests on this PAN"></ul>
  <details class="br-pan-guests">
    <summary><span class="br-pan-guests-label">Guests on this PAN:</span> <span class="br-pan-guests-text">None chosen</span></summary>
    <div class="br-pan-guests-panel">
      @foreach($panGuests as $ri => $guests)
        <p class="br-pan-room">Room {{ $ri + 1 }}</p>
        @foreach($guests as $g)
          <label class="br-pan-guest">
            <input type="checkbox" name="pans[{{ $k }}][guests][]" value="{{ $g['key'] }}" @checked(in_array($g['key'], $chosen, true))>
            <span class="br-pan-who">{{ \Illuminate\Support\Str::after($g['who'], ' · ') }}@if($g['lead']) <small>(lead guest)</small>@endif</span>
            <span class="br-pan-guest-name" data-guest="{{ $g['key'] }}" data-who="{{ $g['who'] }}"></span>
          </label>
        @endforeach
      @endforeach
    </div>
  </details>
</div>
