@extends('layouts.frontend')

@section('meta_title', 'Flight Search Results | TYT Luxe')

@php
  $airlineNames = [
    '6E' => 'IndiGo', 'AI' => 'Air India', 'SG' => 'SpiceJet', 'UK' => 'Vistara',
    'G8' => 'Go First', 'I5' => 'AirAsia India', 'IX' => 'Air India Express',
    'EK' => 'Emirates', 'QR' => 'Qatar Airways', 'LH' => 'Lufthansa', 'BA' => 'British Airways',
    'EY' => 'Etihad Airways', 'SQ' => 'Singapore Airlines', 'CX' => 'Cathay Pacific',
  ];

  // Flatten both trip groups into a simple card-shaped array — the card
  // markup below doesn't care which group an option came from, only the
  // hidden review form needs the group key back (see price_ids[...]).
  //
  // Confirmed against a live sandbox response: tripInfos.ONWARD/RETURN is a
  // plain array of itinerary objects, each with its OWN `sI` segment list
  // and its OWN `totalPriceList` of fares for that itinerary — not a single
  // object holding one shared `sI`/`totalPriceList` across every fare, as
  // earlier (unverified) code here had assumed.
  $formatTime = function ($iso) {
      if (! $iso) {
          return '';
      }
      try {
          return \Carbon\Carbon::parse($iso)->format('H:i');
      } catch (\Throwable $e) {
          return '';
      }
  };

  // Fare Type filter groups. TripJack returns ~20 raw fare names (PUBLISHED,
  // SME, NDC_Xpress Value, STRETCH, ECO FLEX… — confirmed live), so they're
  // bucketed the way TripJack's own filter presents them. STRETCH is
  // IndiGo's business-class product sold in economy searches.
  $fareGroupOf = function (string $fareId, ?string $cabin) {
      $u = strtoupper($fareId);
      if (str_starts_with($u, 'NDC')) {
          return 'NDC';
      }
      if (str_contains($u, 'SME') || str_contains($u, 'CORPORATE')) {
          return 'Corporate / SME';
      }
      if (str_starts_with($u, 'STRETCH') || in_array(strtoupper((string) $cabin), ['BUSINESS', 'FIRST'], true)) {
          return 'Business';
      }

      return 'Standard';
  };
  // "HH:MM" of a TripJack local timestamp ("2026-10-20T08:15") as minutes
  // since midnight, for the time-of-day filters.
  $minuteOfDay = fn ($iso) => $iso && strlen($iso) >= 16 ? ((int) substr($iso, 11, 2)) * 60 + (int) substr($iso, 14, 2) : null;

  // Group metadata: oneway/return use TripJack's own ONWARD/RETURN keys;
  // Multi-City's tripInfos is keyed 0..5 (confirmed live), one per leg the
  // guest entered in searchParams['legs'].
  // International Return / Multi-City come back as one "COMBO" list of
  // whole-journey fares instead (confirmed live), each itinerary holding
  // every leg; one priceId books the lot.
  $isCombo = isset($results['tripInfos']['COMBO']);
  if ($isCombo) {
      $comboLegs = $searchParams['tripType'] === 'multi'
          ? $searchParams['legs']
          : [['from' => $searchParams['from'], 'to' => $searchParams['to']], ['from' => $searchParams['to'], 'to' => $searchParams['from']]];
      $groupMeta = ['COMBO' => [
          'label' => $searchParams['tripType'] === 'multi' ? 'Multi-City Itineraries' : 'Round-Trip Flights',
          'from' => $comboLegs[0]['from'],
          'to' => $comboLegs[0]['to'],
          'date' => $searchParams['departDate'],
          'legLabels' => $searchParams['tripType'] === 'multi'
              ? array_map(fn ($i) => 'Flight '.($i + 1), array_keys($comboLegs))
              : ['Onward', 'Return'],
      ]];
  } elseif ($searchParams['tripType'] === 'multi') {
      $groupMeta = [];
      foreach ($searchParams['legs'] as $i => $leg) {
          $groupMeta[$i] = ['label' => 'Flight '.($i + 1), 'from' => $leg['from'], 'to' => $leg['to'], 'date' => $leg['date']];
      }
  } else {
      $groupMeta = [
          'ONWARD' => ['label' => 'Onward Flight', 'from' => $searchParams['from'], 'to' => $searchParams['to'], 'date' => $searchParams['departDate']],
          'RETURN' => ['label' => 'Return Flight', 'from' => $searchParams['to'], 'to' => $searchParams['from'], 'date' => $searchParams['returnDate']],
      ];
  }

  // Grouped by FLIGHT (one row per itinerary, like TripJack's own results
  // list), not by fare — each flight can have several selectable fare
  // options (Published/Lite/Flex/etc.) shown together in a compact list
  // within its row, matching the reference layout.
  $cardGroups = [];
  if ($results) {
      foreach ($groupMeta as $key => $meta) {
          $itineraries = $results['tripInfos'][$key] ?? null;
          if (! $itineraries || ! is_array($itineraries)) {
              continue;
          }

          $flights = [];
          foreach ($itineraries as $itinerary) {
              $seg = $itinerary['sI'][0] ?? null;
              if (! $seg) {
                  continue;
              }
              // A connecting itinerary is several `sI` segments (confirmed
              // live) — departure comes from the first, arrival from the
              // last, and the journey time is every flight plus every
              // layover (`cT`, in minutes, on the segment before it).
              $segments = $itinerary['sI'];
              // A COMBO itinerary holds several legs (outbound + return, or
              // each Multi-City flight); everything else is a single leg.
              // The card's headline times come from the first leg; stops,
              // duration and layovers cover the whole journey.
              $legSegs = \App\Services\TripJack\TripJackFlightClient::itineraryLegs($segments);
              $legSummaries = array_map(function ($leg) use ($formatTime) {
                  $first = $leg[0];
                  $last = end($leg);
                  return [
                      'from' => $first['da']['code'] ?? '',
                      'to' => $last['aa']['code'] ?? '',
                      'depTime' => $formatTime($first['dt'] ?? null),
                      'arrTime' => $formatTime($last['at'] ?? null),
                      'depDate' => isset($first['dt']) ? \Carbon\Carbon::parse($first['dt'])->format('D, M j') : '',
                      'nextDay' => isset($first['dt'], $last['at']) && substr($first['dt'], 0, 10) !== substr($last['at'], 0, 10),
                      'duration' => collect($leg)->sum(fn ($s) => (int) ($s['duration'] ?? 0) + (int) ($s['cT'] ?? 0)),
                      'stops' => (count($leg) - 1) + collect($leg)->sum(fn ($s) => (int) ($s['stops'] ?? 0)),
                      'via' => collect(array_slice($leg, 1))->map(fn ($s) => $s['da']['code'] ?? null)->filter()->values()->all(),
                      'airlineCode' => $first['fD']['aI']['code'] ?? '',
                      'airlineName' => $first['fD']['aI']['name'] ?? '',
                      'flightNo' => $first['fD']['fN'] ?? '',
                      'fromCity' => $first['da']['city'] ?? null,
                      'toCity' => $last['aa']['city'] ?? null,
                      'fromCountry' => $first['da']['country'] ?? null,
                      'toCountry' => $last['aa']['country'] ?? null,
                      'fromAirportName' => $first['da']['name'] ?? null,
                      'toAirportName' => $last['aa']['name'] ?? null,
                      'fromTerminal' => $first['da']['terminal'] ?? null,
                      'toTerminal' => $last['aa']['terminal'] ?? null,
                      'depRaw' => $first['dt'] ?? null,
                      'arrRaw' => $last['at'] ?? null,
                  ];
              }, $legSegs);
              $lastSeg = end($legSegs[0]);
              $journeyMinutes = array_sum(array_column($legSummaries, 'duration'));
              $stopCount = max(array_column($legSummaries, 'stops'));
              $viaCodes = collect($legSummaries)->flatMap(fn ($l) => $l['via'])->unique()->values()->all();

              $options = [];
              foreach (($itinerary['totalPriceList'] ?? []) as $option) {
                  $fdAll = $option['fd'] ?? $option['fD'] ?? [];
                  $fd = $fdAll['ADULT'] ?? [];
                  $fc = $fd['fC'] ?? [];
                  $tf = (float) ($fc['TF'] ?? 0);
                  $bf = (float) ($fc['BF'] ?? 0);
                  $taf = (float) ($fc['TAF'] ?? max(0, $tf - $bf));

                  $options[] = [
                      'id' => $option['id'],
                      'price' => $tf,
                      'baseFare' => $bf,
                      'taxes' => $taf,
                      'refundable' => ($fd['rT'] ?? 0) >= 1,
                      // Search doc: rT 0 = Non-Refundable, 1 = Refundable,
                      // 2 = Partially Refundable — kept distinct so a partial
                      // fare is never labelled (or filtered) as fully refundable.
                      'refundType' => in_array((int) ($fd['rT'] ?? 0), [0, 1, 2], true) ? (int) ($fd['rT'] ?? 0) : 0,
                      'fareIdentifier' => $option['fareIdentifier'] ?? 'PUBLISHED',
                      'fareGroup' => $fareGroupOf($option['fareIdentifier'] ?? 'PUBLISHED', $fd['cc'] ?? null),
                      // Doc: "When fareIdentifier is SPECIAL_RETURN, both legs
                      // must be SPECIAL_RETURN" — matched via sri/msri, but
                      // those fields came back null/empty even on confirmed
                      // SPECIAL_RETURN fares in live testing, so we enforce
                      // only the unambiguous half (fareIdentifier consistency
                      // across legs) client-side and leave exact pairing to
                      // TripJack's own Review revalidation.
                      'isSpecialReturn' => ($option['fareIdentifier'] ?? '') === 'SPECIAL_RETURN',
                      // Special Return pairing (confirmed live, BOM⇄DEL): Air
                      // India / H1 fares carry sri + msri, where msri lists the
                      // exact fare(s) on the other leg it pairs with (e.g. onward
                      // sri 2422AI6694 ↔ return msri ["2422AI6694"]) — any other
                      // pair is rejected at Review. IndiGo sends neither, and
                      // pairs with any same-airline Special Return fare.
                      'sri' => (string) ($option['sri'] ?? ''),
                      'msri' => array_values(array_filter((array) ($option['msri'] ?? []), 'is_string')),
                      // TJ_FLEX: zero-cancellation-fee domestic direct fare
                      // (doc) — flex charges (FTC) are themselves non-
                      // refundable, so this isn't "free cancellation", just
                      // no separate cancellation fee on top of the FTC.
                      'isFlex' => ($option['fareIdentifier'] ?? '') === 'TJ_FLEX',
                      'seatsLeft' => $fd['sR'] ?? null,
                      'baggageCheckin' => $fd['bI']['iB'] ?? null,
                      'baggageCabin' => $fd['bI']['cB'] ?? null,
                      'bookingClass' => $fd['cB'] ?? null,
                      'cabinClass' => $fd['cc'] ?? null,
                      // Per-pax-type baggage table (Adult/Child/Infant) —
                      // fdAll is keyed by pax type exactly like the top-level
                      // ADULT breakdown used above.
                      'baggageByPaxType' => collect($fdAll)->map(fn ($paxFd) => [
                          'checkin' => $paxFd['bI']['iB'] ?? null,
                          'cabin' => $paxFd['bI']['cB'] ?? null,
                      ])->all(),
                  ];
              }

              if (! $options) {
                  continue;
              }

              usort($options, fn ($a, $b) => $a['price'] <=> $b['price']);
              $airlineCode = $seg['fD']['aI']['code'] ?? '';

              $flights[] = [
                  'segId' => $seg['id'] ?? null,
                  'airlineCode' => $airlineCode,
                  'airlineName' => $seg['fD']['aI']['name'] ?? ($airlineNames[$airlineCode] ?? ($airlineCode ?: 'Airline')),
                  'flightNo' => $seg['fD']['fN'] ?? '',
                  'aircraftType' => $seg['fD']['eT'] ?? null,
                  'depTime' => $formatTime($seg['dt'] ?? null),
                  'arrTime' => $formatTime($lastSeg['at'] ?? null),
                  'depDateTimeRaw' => $seg['dt'] ?? null,
                  'arrDateTimeRaw' => $lastSeg['at'] ?? null,
                  'fromTerminal' => $seg['da']['terminal'] ?? null,
                  'toTerminal' => $lastSeg['aa']['terminal'] ?? null,
                  'fromCity' => $seg['da']['city'] ?? null,
                  'toCity' => $lastSeg['aa']['city'] ?? null,
                  'fromCountry' => $seg['da']['country'] ?? null,
                  'toCountry' => $lastSeg['aa']['country'] ?? null,
                  'fromAirportName' => $seg['da']['name'] ?? null,
                  'toAirportName' => $lastSeg['aa']['name'] ?? null,
                  'duration' => $journeyMinutes ?: null,
                  'stops' => $stopCount,
                  'via' => $viaCodes,
                  // Sort keys for the Departure / Arrival sort buttons —
                  // arrival uses the full timestamp so a next-day arrival
                  // sorts after a same-day one.
                  'depSort' => $seg['dt'] ?? '',
                  'arrSort' => $lastSeg['at'] ?? '',
                  // Filter keys (see the sidebar filters below).
                  'depMinute' => $minuteOfDay($seg['dt'] ?? null),
                  'arrMinute' => $minuteOfDay($lastSeg['at'] ?? null),
                  'depAirport' => $seg['da']['code'] ?? '',
                  'arrAirport' => $lastSeg['aa']['code'] ?? '',
                  'depAirportName' => $seg['da']['name'] ?? ($seg['da']['code'] ?? ''),
                  'arrAirportName' => $lastSeg['aa']['name'] ?? ($lastSeg['aa']['code'] ?? ''),
                  'layoverMax' => (int) collect($segments)->max(fn ($s) => (int) ($s['cT'] ?? 0)),
                  'flightNos' => collect($segments)->map(fn ($s) => ($s['fD']['aI']['code'] ?? '').'-'.($s['fD']['fN'] ?? ''))->all(),
                  'legs' => $legSummaries,
                  'options' => $options,
                  'minPrice' => $options[0]['price'],
              ];
          }

          if ($flights) {
              usort($flights, fn ($a, $b) => $a['minPrice'] <=> $b['minPrice']);
              $cardGroups[$key] = ['meta' => $meta, 'flights' => $flights];
          }
      }
  }

  $allPrices = collect($cardGroups)->flatMap(fn ($g) => collect($g['flights'])->pluck('minPrice'));
  $minPrice = $allPrices->isNotEmpty() ? (int) floor($allPrices->min()) : 0;
  $maxPrice = $allPrices->isNotEmpty() ? (int) ceil($allPrices->max()) : 0;

  $fmtDuration = fn ($m) => $m ? intdiv($m, 60).'h '.($m % 60).'m' : '—';

  // A search was actually submitted (as opposed to landing on the bare
  // results URL): drives the summary bar, even when the search failed.
  $hasSearched = $searchParams['tripType'] === 'multi'
      ? ! empty($searchParams['legs'])
      : ($searchParams['from'] !== '' && $searchParams['to'] !== '' && $searchParams['departDate'] !== '');

  // Flights shown per "page" of each list; more are revealed on scroll.
  $pageSize = 20;

  // Everything the "View Details" panel and the Compare popup need, as one
  // compact JSON block keyed by row id. The panels used to be rendered
  // server-side for every flight (~1 MB of HTML for 236 flights) even though
  // guests open only a handful — now a panel is built in the browser the
  // first time it's opened. Values are pre-formatted here so the browser
  // only has to slot them in.
  $fmtStamp = fn ($iso, $fmt) => $iso ? \Carbon\Carbon::parse($iso)->format($fmt) : '';
  $titleCase = fn ($s) => ucwords(strtolower(str_replace('_', ' ', (string) $s)));
  $flightData = [];
  foreach ($cardGroups as $key => $group) {
      foreach ($group['flights'] as $fIdx => $f) {
          $rep = $f['options'][0];
          $bags = collect($rep['baggageByPaxType'])->map(fn ($b, $pax) => [ucfirst(strtolower($pax)), $b['checkin'] ?? '—', $b['cabin'] ?? '—'])->values()->all()
              ?: [['Adult', $rep['baggageCheckin'] ?? '—', $rep['baggageCabin'] ?? '—']];
          $multiLeg = count($f['legs']) > 1;
          $flightData['frxRow_'.$key.'_'.$fIdx] = [
              'sub' => implode(' · ', array_filter([
                  $multiLeg ? null : $f['airlineCode'].'-'.$f['flightNo'].($f['aircraftType'] ? '-'.$f['aircraftType'] : ''),
                  $titleCase($rep['cabinClass'] ?? 'ECONOMY'),
                  $rep['bookingClass'] ? 'CB:'.$rep['bookingClass'] : null,
                  $rep['seatsLeft'] ? $rep['seatsLeft'].' seat(s) left' : null,
              ])),
              // One entry per leg (a single entry unless this is a COMBO
              // international return / multi-city itinerary):
              // [title, depPoint, arrPoint, stops, duration], where a point is
              // [time, city, airport, terminal].
              'legs' => array_map(fn ($l, $lIdx) => [
                  ($multiLeg ? ($group['meta']['legLabels'][$lIdx] ?? 'Flight '.($lIdx + 1)).': ' : '')
                      .$l['from'].($l['fromCity'] ? ' ('.$l['fromCity'].')' : '').' → '.$l['to'].($l['toCity'] ? ' ('.$l['toCity'].')' : '').' · '.$fmtStamp($l['depRaw'], 'D, M j Y')
                      .($multiLeg ? ' · '.$l['airlineCode'].'-'.$l['flightNo'] : ''),
                  [$fmtStamp($l['depRaw'], 'M j, D, H:i') ?: $l['depTime'], ($l['fromCity'] ?: $l['from']).($l['fromCountry'] ? ', '.$l['fromCountry'] : ''), $l['fromAirportName'] ?: $l['from'], $l['fromTerminal']],
                  [$fmtStamp($l['arrRaw'], 'M j, D, H:i') ?: $l['arrTime'], ($l['toCity'] ?: $l['to']).($l['toCountry'] ? ', '.$l['toCountry'] : ''), $l['toAirportName'] ?: $l['to'], $l['toTerminal']],
                  $l['stops'] === 0 ? 'Non-Stop' : $l['stops'].' Stop(s)'.($l['via'] ? ' via '.implode(', ', $l['via']) : ''),
                  $fmtDuration($l['duration']),
              ], $f['legs'], array_keys($f['legs'])),
              'bags' => $bags,
              'rulesId' => $rep['id'],
              // [label, base, taxes, total, id, fareIdentifier, price, refundable, checkin, cabin]
              'fares' => array_map(fn ($o) => [
                  $titleCase($o['fareIdentifier']).(isset($o['seatsLeft']) ? ' ('.$o['seatsLeft'].' left)' : ''),
                  number_format($o['baseFare']), number_format($o['taxes']), number_format($o['price']),
                  $o['id'], $o['fareIdentifier'], $o['price'], $o['refundable'], $o['baggageCheckin'], $o['baggageCabin'],
              ], $f['options']),
          ];
      }
  }

  // Sidebar filter options + counts, built from every flight on the page.
  $allFlights = collect($cardGroups)->flatMap(fn ($g) => $g['flights']);
  $allOptions = $allFlights->flatMap(fn ($f) => $f['options']);
  $airlineFacets = $allFlights->groupBy('airlineCode')->map(fn ($fs, $code) => [
      'code' => $code,
      'name' => $airlineNames[$code] ?? ($fs->first()['airlineName'] ?: $code),
      'count' => $fs->count(),
      'minPrice' => $fs->min('minPrice'),
  ])->sortBy('minPrice')->values();
  $fareGroupFacets = collect(['Standard', 'Corporate / SME', 'NDC', 'Business'])
      ->mapWithKeys(fn ($g) => [$g => $allOptions->where('fareGroup', $g)->count()])
      ->filter();
  $refundFacets = [
      '0' => $allOptions->where('refundType', 0)->count(),
      '2' => $allOptions->where('refundType', 2)->count(),
      '1' => $allOptions->where('refundType', 1)->count(),
  ];
  $terminalFacet = function ($airportKey, $terminalKey) use ($allFlights) {
      return $allFlights->filter(fn ($f) => $f[$terminalKey])
          ->groupBy(fn ($f) => $f[$airportKey].'|'.$f[$terminalKey])
          ->map(fn ($fs, $k) => ['value' => $k, 'label' => str_replace('|', ' · ', $k), 'count' => $fs->count()])
          ->sortBy('label')->values();
  };
  $depTerminalFacets = $terminalFacet('depAirport', 'fromTerminal');
  $arrTerminalFacets = $terminalFacet('arrAirport', 'toTerminal');
  $airportFacet = fn ($codeKey, $nameKey) => $allFlights->groupBy($codeKey)
      ->map(fn ($fs, $code) => ['code' => $code, 'name' => $fs->first()[$nameKey], 'count' => $fs->count()])
      ->sortByDesc('count')->values();
  $depAirportFacets = $airportFacet('depAirport', 'depAirportName');
  $arrAirportFacets = $airportFacet('arrAirport', 'arrAirportName');
  $layoverAirportFacets = $allFlights->flatMap(fn ($f) => $f['via'])->countBy()->sortDesc();
  $durations = $allFlights->pluck('duration')->filter();
  $durationMin = (int) floor(($durations->min() ?? 0) / 60) * 60;
  $durationMax = (int) ceil(($durations->max() ?? 0) / 60) * 60;
  $layoverCeil = (int) ceil(($allFlights->max('layoverMax') ?? 0) / 60) * 60;
  // Flights that don't use the searched airports (TripJack includes
  // nearby ones — e.g. NMI for a BOM search, confirmed live).
  $nearbyCount = collect($cardGroups)->sum(fn ($g) => collect($g['flights'])->filter(fn ($f) => $f['depAirport'] !== $g['meta']['from'] || $f['arrAirport'] !== $g['meta']['to'])->count());
  $popularAirlines = $airlineFacets->sortByDesc('count')->take(3)->values();
  $timeBuckets = [
      ['from' => 0, 'to' => 360, 'label' => '00-06', 'icon' => '&#9788;'],
      ['from' => 360, 'to' => 720, 'label' => '06-12', 'icon' => '&#9728;'],
      ['from' => 720, 'to' => 1080, 'label' => '12-18', 'icon' => '&#9925;'],
      ['from' => 1080, 'to' => 1440, 'label' => '18-24', 'icon' => '&#9790;'],
  ];

  // Cheapest / Fastest quick-sort cards — one line per leg, so a return
  // trip shows its onward and return picks separately instead of mixing
  // the two legs' fares together.
  $quickPicks = ['cheapest' => [], 'fastest' => []];
  foreach ($cardGroups as $key => $group) {
      $flights = collect($group['flights']);
      $cheapest = $flights->sortBy('minPrice')->first();
      $fastest = $flights->filter(fn ($f) => $f['duration'])->sortBy([['duration', 'asc'], ['minPrice', 'asc']])->first() ?? $cheapest;
      $label = count($cardGroups) > 1 ? $group['meta']['label'] : null;
      $quickPicks['cheapest'][] = ['label' => $label, 'price' => $cheapest['minPrice'], 'duration' => $cheapest['duration']];
      $quickPicks['fastest'][] = ['label' => $label, 'price' => $fastest['minPrice'], 'duration' => $fastest['duration']];
  }

  // Date strip (oneway/return only — a Multi-City search has one date per
  // leg, so there is no single date to shift). The current date's fare is
  // already known from these results; the others are fetched on demand.
  $showDateStrip = $cardGroups && $searchParams['tripType'] !== 'multi' && $searchParams['departDate'];
  $currentDayFare = $quickPicks['cheapest'] ? (int) round(collect($quickPicks['cheapest'])->sum('price')) : null;
  $stripQuery = collect(request()->query())->except(['depart_date', 'page'])->all();
@endphp

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com" />
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,500;0,600&family=Jost:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
<style>
  :root {
    --gold: #c9a84c; --gold-light: #e8c96b; --gold-dim: rgba(201,168,76,0.18);
    --dark: #0d0d0d; --dark-2: #141414; --dark-3: #1c1c1c;
    --white-80: rgba(255,255,255,0.80); --white-60: rgba(255,255,255,0.60); --white-30: rgba(255,255,255,0.30);
    --green: #4ade80; --tr: 0.25s ease;
  }
  body { background: var(--dark); }
  * { box-sizing: border-box; }

  .frx-wrap { max-width: 1320px; margin: 0 auto; padding: 100px 24px 90px; font-family: 'Jost', sans-serif; }

  /* ── Top summary bar ─────────────────────────────────────────── */
  .frx-topbar { background: var(--dark-2); border: 1px solid rgba(255,255,255,0.08); border-radius: 12px; padding: 7px 18px; display: flex; align-items: center; flex-wrap: wrap; gap: 20px; margin-bottom: 24px; }
  .frx-route { display: flex; align-items: center; gap: 10px; }
  .frx-route-city { font-size: 14px; font-weight: 600; color: #fff; }
  .frx-route-sub { font-size: 10.5px; color: var(--white-60); max-width: 110px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
  .frx-tb-sep { color: var(--white-30); margin: 0 3px; }
  .frx-modify-btn { display: inline-flex; align-items: center; gap: 6px; }
  .frx-modify-chev { display: inline-block; font-size: 13px; line-height: 1; margin-top: -5px; transition: transform var(--tr); }
  .frx-modify-btn[aria-expanded="true"] { background: rgba(201,168,76,0.12); }
  .frx-modify-btn[aria-expanded="true"] .frx-modify-chev { transform: rotate(180deg); margin-top: 5px; }
  .frx-route-arrow { color: var(--gold); font-size: 15px; }
  .frx-tb-divider { width: 1px; align-self: stretch; background: rgba(255,255,255,0.08); }
  .frx-tb-label { font-size: 9.5px; font-weight: 600; letter-spacing: 0.08em; text-transform: uppercase; color: var(--gold); margin-bottom: 3px; }
  .frx-tb-value { font-size: 12.5px; color: #ddd; }
  .frx-tb-spacer { flex: 1; }
  .frx-modify-btn { padding: 7px 16px; border: 1px solid rgba(201,168,76,0.4); border-radius: 100px; background: transparent; color: var(--gold-light); font-family: 'Jost', sans-serif; font-size: 11.5px; font-weight: 700; letter-spacing: 0.06em; text-transform: uppercase; cursor: pointer; transition: background var(--tr); }
  .frx-modify-btn:hover { background: rgba(201,168,76,0.1); }

  /* On this page the site header scrolls away with the page, and the search
     summary + date strip pin to the top instead (--frx-sticky-h is kept in
     step with the pinned block's real height by the script below). */
  /* main.js restyles the header inline on scroll (padding 20px→10px, new
     background, with a 0.3s `all` transition) — made for the fixed header on
     other pages. Here the header scrolls away, so that restyle would make it
     visibly shrink and flash as it leaves; hold it steady instead. */
  header[role="banner"] { position: absolute; padding: 20px 0 !important; background: linear-gradient(to bottom, rgba(0,0,0,0.7) 0%, rgba(0,0,0,0.3) 70%, transparent 100%) !important; transition: none !important; }
  /* style.css sets `overflow-x: hidden` on both html and body, which turns
     body into a scroll container and silently disables position: sticky.
     `clip` still prevents sideways scrolling without that side effect. */
  html, body { overflow-x: clip; }
  /* The pinned block must never change size when it sticks — a sticky
     element that shrinks or grows at that moment shifts everything below it
     mid-scroll, which reads as a jerk. So "stuck" only adds a shadow (paint
     only, no layout), and the border is always present, just transparent. */
  .frx-sticky { position: sticky; top: 0; z-index: 900; background: var(--dark); margin: 0 -24px 14px; padding: 6px 24px; border-bottom: 1px solid transparent; }
  .frx-sticky .frx-topbar { margin-bottom: 0; }
  .frx-sticky > :last-child { margin-bottom: 0; }
  .frx-sticky .frx-head-row { margin-bottom: 0; }
  .frx-sticky { transition: box-shadow 0.2s ease, border-color 0.2s ease; }
  .frx-sticky.stuck { border-bottom-color: rgba(201,168,76,0.18); box-shadow: 0 14px 24px -10px rgba(0,0,0,0.75); }
  .frx-sticky-sentinel { height: 1px; margin-bottom: -1px; }
  /* Modify Search: no scroll box — a scrolling container clipped the airport
     / calendar / passenger dropdowns at its bottom edge. Instead, while the
     form is open the bar stops pinning, so a tall form (Multi-City, phones)
     scrolls with the page like normal content. */
  .frx-sticky .frx-modify-panel { margin: 10px 0 0; }
  .frx-sticky.modify-open { position: relative; }
  .frx-group-title { scroll-margin-top: calc(var(--frx-sticky-h, 90px) + var(--frx-sort-h, 50px) + 16px); }
  @media (max-width: 720px) {
    .frx-sticky { padding: 5px 16px; margin: 0 -16px 12px; }
    /* One compact layout on phones, pinned or not: route + Modify Search on
       the first line, date and passengers as a small second line. */
    .frx-topbar { gap: 4px 14px; padding: 7px 12px; }
    .frx-topbar .frx-tb-divider, .frx-topbar .frx-tb-spacer, .frx-topbar .frx-tb-label, .frx-topbar .frx-route-sub { display: none; }
    .frx-topbar .frx-modify-btn { order: 1; margin-left: auto; padding: 6px 12px; font-size: 10px; }
    /* Labels are hidden on phones, so a bare "None" would mean nothing. */
    .frx-topbar .frx-tb-pref-none { display: none; }
    .frx-topbar > div:not(.frx-route) { order: 2; }
    /* Forced line break after Modify Search, so the date never squeezes
       onto the first line when the button is narrow. */
    .frx-topbar::after { content: ''; order: 1; flex-basis: 100%; height: 0; }
    .frx-topbar .frx-tb-value { font-size: 11.5px; color: var(--white-60); }
  }

  /* The search form is already its own bordered card, so the panel adds no
     second box around it — just the trip-type tabs above the card. */
  .frx-modify-panel { display: none; padding: 4px 0 2px; }
  .frx-modify-panel.open { display: block; animation: frxModifyIn 0.18s ease-out; }
  @keyframes frxModifyIn { from { opacity: 0; transform: translateY(-4px); } to { opacity: 1; transform: none; } }
  .frx-modify-panel .tyt-sb-tabs { padding-left: 4px; }
  .frx-modify-panel .tyt-searchbar { box-shadow: 0 16px 40px rgba(0,0,0,0.55); }
  .fr-field label { display: block; font-family: 'Jost', sans-serif; font-size: 10px; font-weight: 600; letter-spacing: 0.08em; text-transform: uppercase; color: var(--gold); margin-bottom: 6px; }
  .fr-field input, .fr-field select { width: 100%; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.12); border-radius: 8px; padding: 10px 12px; color: #fff; font-family: 'Jost', sans-serif; font-size: 13px; outline: none; }

  .frx-error { margin-bottom: 24px; padding: 14px 18px; border-radius: 12px; background: rgba(220,80,80,0.08); border: 1px solid rgba(220,80,80,0.3); color: #f3a3a3; font-size: 13px; }
  .frx-empty { text-align: center; padding: 60px 20px; color: var(--white-60); }

  /* ── Body: sidebar + list ─────────────────────────────────────── */
  .frx-body { display: grid; grid-template-columns: 268px minmax(0, 1fr); gap: 24px; align-items: start; }
  .frx-main { min-width: 0; }
  @media (max-width: 900px) { .frx-body { grid-template-columns: minmax(0, 1fr); } .frx-sidebar { position: static; } }

  .frx-sidebar { background: var(--dark-2); border: 1px solid rgba(255,255,255,0.08); border-radius: 14px; padding: 20px; position: sticky; top: calc(var(--frx-sticky-h, 150px) + 12px); max-height: calc(100vh - var(--frx-sticky-h, 150px) - 24px); overflow-y: auto; scrollbar-width: thin; scrollbar-color: rgba(201,168,76,0.4) transparent; }
  .frx-side-title { font-size: 12px; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; color: var(--gold-light); margin: 0 0 14px; }
  .frx-side-block { margin-bottom: 24px; padding-bottom: 24px; border-bottom: 1px solid rgba(255,255,255,0.07); }
  .frx-side-block:last-child { border-bottom: none; margin-bottom: 0; padding-bottom: 0; }

  .frx-price-row { display: flex; gap: 10px; align-items: end; margin-bottom: 10px; }
  .frx-price-row .fr-field { flex: 1; }
  .frx-price-apply { padding: 9px 14px; border: 1px solid rgba(201,168,76,0.4); border-radius: 8px; background: transparent; color: var(--gold-light); font-family: 'Jost', sans-serif; font-size: 11px; font-weight: 700; text-transform: uppercase; cursor: pointer; white-space: nowrap; }
  .frx-price-hint { font-size: 11px; color: var(--white-30); }

  .frx-chip-row { display: flex; flex-wrap: wrap; gap: 8px; }
  .frx-chip { display: inline-flex; align-items: center; gap: 6px; padding: 7px 13px; border: 1px solid rgba(255,255,255,0.14); border-radius: 100px; font-size: 11.5px; color: var(--white-80); cursor: pointer; user-select: none; transition: all var(--tr); }
  .frx-chip:hover { border-color: rgba(201,168,76,0.4); }
  .frx-chip.active { background: var(--gold); border-color: var(--gold); color: var(--dark); font-weight: 600; }

  .frx-airline-list { display: flex; flex-direction: column; gap: 10px; }
  .frx-airline-opt { display: flex; align-items: center; justify-content: space-between; gap: 8px; font-size: 12.5px; color: var(--white-80); cursor: pointer; }
  .frx-airline-opt input { accent-color: var(--gold); width: 15px; height: 15px; cursor: pointer; }
  .frx-airline-opt-label { display: flex; align-items: center; gap: 8px; flex: 1; min-width: 0; }
  .frx-airline-count { color: var(--white-30); font-size: 11px; }

  .frx-reset-btn { display: inline-block; margin-top: 4px; font-size: 11px; color: var(--gold); cursor: pointer; text-decoration: underline; text-underline-offset: 2px; }

  /* Extra filter controls */
  .frx-side-block-tight { padding-bottom: 16px; margin-bottom: 18px; }
  .frx-side-sub { font-size: 10px; color: var(--white-30); letter-spacing: 0.04em; }
  .frx-seg { display: grid; grid-auto-flow: column; grid-auto-columns: 1fr; border: 1px solid rgba(255,255,255,0.14); border-radius: 10px; overflow: hidden; }
  .frx-seg-btn { background: transparent; border: none; border-left: 1px solid rgba(255,255,255,0.1); color: var(--white-80); font-family: 'Jost', sans-serif; font-size: 12px; font-weight: 600; padding: 8px 2px; cursor: pointer; display: flex; flex-direction: column; align-items: center; gap: 3px; transition: background var(--tr), color var(--tr); }
  .frx-seg-btn:first-child { border-left: none; }
  .frx-seg-btn:hover { background: rgba(201,168,76,0.08); }
  .frx-seg-btn.active { background: var(--gold); color: var(--dark); }
  .frx-seg-time .frx-seg-btn { font-size: 10.5px; }
  .frx-seg-icon { font-size: 15px; line-height: 1; color: var(--gold-light); }
  .frx-seg-btn.active .frx-seg-icon { color: var(--dark); }
  .frx-timeframe-btn { display: block; width: 100%; margin-top: 8px; padding: 6px; background: transparent; border: 1px solid rgba(255,255,255,0.12); border-radius: 8px; color: var(--white-60); font-family: 'Jost', sans-serif; font-size: 11px; font-weight: 600; cursor: pointer; }
  .frx-timeframe-btn:hover, .frx-timeframe-btn.active { border-color: rgba(201,168,76,0.5); color: var(--gold-light); }
  .frx-timeframe { display: flex; flex-wrap: wrap; align-items: center; gap: 6px; margin-top: 8px; font-size: 11px; color: var(--white-60); }
  .frx-timeframe[hidden] { display: none; }
  .frx-timeframe select { flex: 1; min-width: 0; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.12); border-radius: 6px; color: #fff; font-family: 'Jost', sans-serif; font-size: 12px; padding: 6px; }
  .frx-timeframe select option { background: var(--dark-2); }
  .frx-link-btn { background: none; border: none; padding: 0 0 0 6px; color: #f08a8a; font-family: 'Jost', sans-serif; font-size: 10px; font-weight: 700; letter-spacing: 0.06em; text-transform: uppercase; cursor: pointer; }
  .frx-link-btn[hidden] { display: none; }
  .frx-check-list { display: flex; flex-direction: column; gap: 10px; }
  .frx-check { display: flex; align-items: center; gap: 9px; font-size: 12.5px; color: var(--white-80); cursor: pointer; }
  .frx-check input { accent-color: var(--gold); width: 15px; height: 15px; flex-shrink: 0; cursor: pointer; margin: 0; }
  .frx-check span:first-of-type { flex: 1; min-width: 0; }
  .frx-check-count { color: var(--white-30); font-size: 11px; }
  .frx-check-heading { margin: 4px 0 -2px; font-size: 10px; font-weight: 700; letter-spacing: 0.1em; text-transform: uppercase; color: var(--white-30); }
  .frx-inline-add { display: flex; gap: 8px; }
  .frx-inline-add input, .frx-search-input input { flex: 1; min-width: 0; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.12); border-radius: 8px; padding: 9px 11px; color: #fff; font-family: 'Jost', sans-serif; font-size: 12.5px; outline: none; }
  .frx-inline-add input:focus, .frx-search-input input:focus { border-color: rgba(201,168,76,0.6); }
  .frx-inline-add button { width: 38px; flex-shrink: 0; border: 1px solid rgba(201,168,76,0.4); border-radius: 8px; background: transparent; color: var(--gold-light); font-size: 18px; cursor: pointer; }
  .frx-tag-row { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 8px; }
  .frx-tag-row:empty { display: none; }
  .frx-tag { display: inline-flex; align-items: center; gap: 6px; padding: 4px 6px 4px 10px; border-radius: 100px; background: rgba(201,168,76,0.14); color: var(--gold-light); font-size: 11.5px; font-weight: 600; }
  .frx-tag button { background: none; border: none; color: inherit; font-size: 14px; line-height: 1; cursor: pointer; padding: 0 2px; }
  .frx-search-input { position: relative; display: flex; margin-bottom: 12px; }
  .frx-search-input span { position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: var(--white-30); font-size: 13px; pointer-events: none; }
  .frx-search-input input { padding-left: 30px; }
  .frx-airline-price { color: var(--white-60); font-size: 11.5px; white-space: nowrap; }
  .frx-single-range { width: 100%; accent-color: var(--gold); margin: 6px 0 8px; }
  .frx-range-labels b { color: var(--gold-light); }
  .frx-opt-bag { display: none; font-size: 10.5px; color: var(--white-60); white-space: nowrap; }
  .frx-show-bag .frx-opt-bag { display: inline; }
  .frx-opt-row.frx-opt-hidden { display: none; }
  /* Special Return pairing */
  .frx-sr-badge:empty { display: none; }
  .frx-sr-badge { padding: 2px 8px; border-radius: 100px; background: rgba(74,222,128,0.12); color: var(--green); font-size: 10px; font-weight: 700; letter-spacing: 0.03em; white-space: nowrap; }
  .frx-opt-row.frx-sr-partner { border-color: rgba(74,222,128,0.45); }
  .frx-opt-row.frx-sr-blocked { opacity: 0.4; }
  .frx-opt-row.frx-sr-blocked:hover { opacity: 0.7; }

  /* Collapsible sidebar + collapsible filter blocks */
  .frx-body.side-collapsed { grid-template-columns: minmax(0, 1fr); }
  .frx-body.side-collapsed .frx-sidebar { display: none; }
  .frx-side-toggle { flex-shrink: 0; width: 34px; height: 34px; border-radius: 8px; border: 1px solid rgba(201,168,76,0.4); background: var(--dark-2); color: var(--gold-light); font-size: 16px; line-height: 1; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; transition: background var(--tr); }
  .frx-side-toggle:hover { background: rgba(201,168,76,0.1); }
  .frx-side-head { display: flex; align-items: center; justify-content: space-between; gap: 10px; margin-bottom: 14px; }
  .frx-side-head .frx-side-title { margin: 0; }
  .frx-block-toggle { background: none; border: none; color: var(--gold-light); font-size: 18px; line-height: 1; width: 22px; height: 22px; cursor: pointer; padding: 0; }
  .frx-side-block.collapsed .frx-side-body { display: none; }
  .frx-side-block.collapsed .frx-side-head { margin-bottom: 0; }

  /* Dual-handle price slider */
  .frx-range { position: relative; height: 30px; margin: 4px 4px 12px; }
  .frx-range-track { position: absolute; left: 0; right: 0; top: 50%; height: 6px; margin-top: -3px; border-radius: 6px; background: rgba(255,255,255,0.1); }
  .frx-range-fill { position: absolute; top: 0; bottom: 0; border-radius: 6px; background: linear-gradient(90deg, #c9a84c, #e8c96b); }
  .frx-range input[type=range] { position: absolute; left: -4px; right: -4px; width: calc(100% + 8px); top: 0; height: 30px; margin: 0; background: none; pointer-events: none; -webkit-appearance: none; appearance: none; }
  .frx-range input[type=range]::-webkit-slider-thumb { -webkit-appearance: none; pointer-events: auto; width: 18px; height: 18px; border-radius: 4px; background: #fff; border: 2px solid var(--gold); cursor: grab; box-shadow: 0 2px 6px rgba(0,0,0,0.5); }
  .frx-range input[type=range]::-moz-range-thumb { pointer-events: auto; width: 14px; height: 14px; border-radius: 4px; background: #fff; border: 2px solid var(--gold); cursor: grab; }
  .frx-range input[type=range]::-moz-range-track { background: none; }
  .frx-range input[type=range]:focus-visible::-webkit-slider-thumb { outline: 2px solid var(--gold-light); outline-offset: 2px; }
  .frx-range-labels { display: flex; justify-content: space-between; font-size: 11.5px; font-weight: 600; color: var(--gold-light); margin-bottom: 12px; }
  .frx-price-input { position: relative; }
  .frx-price-input span { position: absolute; left: 10px; top: 50%; transform: translateY(-50%); font-size: 12px; color: var(--white-60); pointer-events: none; }
  .frx-price-input input { padding-left: 22px !important; }
  .frx-price-dash { color: var(--white-30); padding-bottom: 10px; }
  .frx-price-row { flex-wrap: wrap; }
  .frx-price-row .fr-field { flex: 1 1 0; min-width: 0; }
  .frx-price-row .frx-price-apply { flex: 1 0 100%; padding: 10px 14px; }
  .frx-price-input input { padding-right: 6px !important; -moz-appearance: textfield; }
  .frx-price-input input::-webkit-outer-spin-button, .frx-price-input input::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }

  /* ── Main column head: date strip, quick picks, share, sort ─────── */
  .frx-main-head { margin-bottom: 18px; }
  .frx-head-row { display: flex; align-items: center; gap: 12px; margin-bottom: 14px; }

  .frx-dates { flex: 1; min-width: 0; display: flex; align-items: stretch; background: var(--dark-2); border: 1px solid rgba(255,255,255,0.08); border-radius: 12px; overflow: hidden; }
  .frx-dates-nav { flex-shrink: 0; width: 38px; border: none; background: transparent; color: var(--gold-light); font-size: 20px; cursor: pointer; transition: background var(--tr); }
  .frx-dates-nav:hover:not(:disabled) { background: rgba(201,168,76,0.1); }
  .frx-dates-nav:disabled { color: var(--white-30); cursor: not-allowed; }
  .frx-dates-track { flex: 1; min-width: 0; display: grid; grid-template-columns: repeat(7, minmax(0, 1fr)); }
  .frx-date { position: relative; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 3px; padding: 10px 4px; border-left: 1px solid rgba(255,255,255,0.06); text-decoration: none; color: var(--white-80); transition: background var(--tr); min-width: 0; }
  .frx-date:hover { background: rgba(255,255,255,0.03); }
  .frx-date-day { font-size: 12px; font-weight: 600; white-space: nowrap; }
  .frx-date-fare { font-size: 12px; font-weight: 600; color: var(--white-60); background: none; border: none; padding: 0; font-family: inherit; cursor: pointer; white-space: nowrap; }
  .frx-date-fare.fetch { color: var(--gold); text-decoration: underline dotted; text-underline-offset: 3px; }
  .frx-date-fare.fetch:hover { color: var(--gold-light); }
  .frx-date-fare.priced { color: #fff; }
  .frx-date-fare.muted { color: var(--white-30); cursor: default; font-weight: 500; }
  .frx-date.active { background: rgba(201,168,76,0.1); color: var(--gold-light); }
  .frx-date.active::after { content: ''; position: absolute; left: 0; right: 0; bottom: 0; height: 2px; background: var(--gold); }
  .frx-date.active .frx-date-fare { color: var(--gold-light); }
  .frx-date.disabled { pointer-events: none; opacity: 0.35; }
  .frx-date.cheapest-day .frx-date-fare.priced { color: var(--green); }
  @media (max-width: 720px) {
    .frx-dates-track { grid-template-columns: repeat(7, 92px); overflow-x: auto; scrollbar-width: none; }
    .frx-dates-track::-webkit-scrollbar { display: none; }
  }

  .frx-picks-row { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; margin-bottom: 14px; }
  .frx-picks { display: flex; gap: 12px; flex-wrap: wrap; }
  .frx-pick { display: flex; align-items: center; gap: 12px; min-width: 200px; padding: 11px 16px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.1); background: var(--dark-2); cursor: pointer; text-align: left; font-family: 'Jost', sans-serif; color: var(--white-80); transition: all var(--tr); }
  .frx-pick:hover { border-color: rgba(201,168,76,0.4); }
  .frx-pick.active { border-color: var(--gold); background: rgba(201,168,76,0.08); box-shadow: inset 0 -2px 0 var(--gold); }
  .frx-pick-icon { width: 30px; height: 30px; flex-shrink: 0; border-radius: 50%; display: flex; align-items: center; justify-content: center; background: rgba(201,168,76,0.14); color: var(--gold-light); font-size: 15px; font-weight: 700; }
  .frx-pick-title { font-size: 13px; font-weight: 700; color: #fff; }
  .frx-pick-line { font-size: 11px; color: var(--white-60); }
  .frx-pick-line b { color: var(--gold-light); font-weight: 600; }

  @media (max-width: 560px) { .frx-picks { width: 100%; } .frx-pick { flex: 1 1 100%; min-width: 0; } }
  .frx-share { display: flex; align-items: center; gap: 8px; font-size: 11.5px; color: var(--white-60); }
  .frx-share-btn { width: 32px; height: 32px; border-radius: 50%; border: 1px solid rgba(255,255,255,0.12); background: var(--dark-2); display: inline-flex; align-items: center; justify-content: center; cursor: pointer; text-decoration: none; color: var(--white-80); transition: all var(--tr); }
  .frx-share-btn:hover { border-color: var(--gold); color: var(--gold-light); }
  .frx-share-btn svg { width: 15px; height: 15px; }
  .frx-share-copied { font-size: 11px; color: var(--green); }

  /* Pins directly under the pinned search bar. Like that bar it never
     changes size when it sticks — "stuck" only adds a shadow. */
  .frx-sortbar { position: sticky; top: var(--frx-sticky-h, 0px); z-index: 800; margin-bottom: 18px; display: grid; grid-template-columns: 150px minmax(0, 1fr); align-items: center; gap: 18px; padding: 5px 20px; border-radius: 10px; background: var(--dark-2); border: 1px solid rgba(255,255,255,0.08); transition: box-shadow 0.2s ease, border-color 0.2s ease; }
  .frx-sortbar.stuck { border-color: rgba(201,168,76,0.3); box-shadow: 0 16px 26px -8px rgba(0,0,0,0.8); }
  .frx-sortbar-sentinel { height: 1px; margin-bottom: -1px; }
  .frx-sortbar-label { font-size: 11px; font-weight: 600; letter-spacing: 0.08em; text-transform: uppercase; color: var(--white-30); }
  .frx-sort-keys { display: grid; grid-template-columns: repeat(4, auto); justify-content: space-between; gap: 10px; }
  .frx-sort-btn { background: none; border: none; padding: 4px 2px; font-family: 'Jost', sans-serif; font-size: 12px; font-weight: 600; letter-spacing: 0.04em; text-transform: uppercase; color: var(--white-60); cursor: pointer; display: inline-flex; align-items: center; gap: 5px; white-space: nowrap; }
  .frx-sort-btn:hover { color: var(--gold-light); }
  .frx-sort-btn .frx-sort-arrow { font-size: 10px; opacity: 0; transition: transform var(--tr); }
  .frx-sort-btn.active { color: var(--gold-light); }
  .frx-sort-btn.active .frx-sort-arrow { opacity: 1; }
  .frx-sort-btn.desc .frx-sort-arrow { transform: rotate(180deg); }
  /* Scroll loader skeleton (next page of flights) */
  .frx-load-more[hidden] { display: none; }
  .frx-skel { background: var(--dark-2); border: 1px solid rgba(255,255,255,0.06); border-radius: 14px; padding: 18px 20px; margin-bottom: 12px; }
  .frx-skel-row { display: flex; align-items: center; gap: 16px; }
  .frx-skel-row + .frx-skel-row { margin-top: 16px; }
  .frx-skel-row span { display: block; height: 14px; border-radius: 6px; background: linear-gradient(90deg, rgba(255,255,255,0.05) 0%, rgba(255,255,255,0.11) 50%, rgba(255,255,255,0.05) 100%); background-size: 200% 100%; animation: frxShimmer 1.2s ease-in-out infinite; }
  .frx-skel-row .w-logo { width: 34px; height: 34px; border-radius: 8px; }
  .frx-skel-row .w-time { width: 60px; height: 20px; }
  .frx-skel-row .w-line { flex: 1; height: 2px; }
  .frx-skel-row .w-fare { width: 110px; }
  .frx-skel-row .w-btn { width: 96px; height: 30px; border-radius: 8px; }
  .frx-skel-row .w-opt { width: 170px; height: 34px; border-radius: 10px; }
  @keyframes frxShimmer { 0% { background-position: 100% 0; } 100% { background-position: -100% 0; } }
  @media (prefers-reduced-motion: reduce) { .frx-skel-row span { animation: none; } }
  @media (max-width: 720px) {
    /* Just the four sort keys on a phone, so the pinned area stays short. */
    .frx-sortbar { grid-template-columns: 1fr; gap: 8px; padding: 4px 12px; }
    .frx-sortbar-label { display: none; }
    .frx-sort-keys { grid-template-columns: repeat(4, auto); }
  }

  /* ── Result rows (one per flight, several fare options each) ──────── */
  .frx-group-title { font-size: 13px; font-weight: 700; letter-spacing: 0.1em; text-transform: uppercase; color: var(--gold-light); margin: 0 0 14px; }
  .frx-group-title:not(:first-child) { margin-top: 30px; }

  .frx-flight { background: var(--dark-2); border: 1px solid rgba(255,255,255,0.08); border-radius: 14px; margin-bottom: 12px; overflow: hidden; transition: border-color var(--tr); }
  .frx-flight:hover { border-color: rgba(201,168,76,0.25); }
  .frx-flight.has-selected { border-color: var(--gold); background: rgba(201,168,76,0.04); }

  /* TripJack-style card: airline | journey | fare list | actions */
  .frx-flight-main { display: grid; grid-template-columns: 190px minmax(250px, 1fr) minmax(260px, 1.15fr) 132px; align-items: start; gap: 20px; padding: 18px 20px; }

  .frx-col-airline { display: flex; flex-direction: column; align-items: flex-start; gap: 14px; min-width: 0; }
  .frx-card-airline { display: flex; align-items: center; gap: 10px; min-width: 0; }
  .frx-card-airline img { width: 34px; height: 34px; flex-shrink: 0; object-fit: contain; border-radius: 6px; background: #fff; padding: 3px; }
  .frx-card-airline-name { font-size: 14px; font-weight: 600; color: #fff; line-height: 1.25; }
  .frx-card-flightno { font-size: 11px; color: var(--white-30); margin-top: 2px; }
  .frx-details-toggle.frx-view-details { background: linear-gradient(90deg, #c9a84c, #e8c96b); border: none; color: var(--dark); font-weight: 700; padding: 9px 16px; }
  .frx-details-toggle.frx-view-details:hover { color: var(--dark); box-shadow: 0 6px 16px rgba(201,168,76,0.3); }
  .frx-details-toggle.frx-view-details.open { background: transparent; border: 1px solid rgba(201,168,76,0.5); color: var(--gold-light); }
  .frx-seats-left { font-size: 12px; font-weight: 600; color: #f0c47a; }

  .frx-col-journey { min-width: 0; padding-top: 2px; }
  .frx-card-times { display: flex; align-items: flex-start; gap: 14px; }
  .frx-card-time { flex-shrink: 0; min-width: 58px; }
  .frx-card-time:last-child { text-align: right; }
  .frx-card-time-sub { font-size: 11px; font-weight: 600; color: var(--white-60); text-transform: uppercase; letter-spacing: 0.06em; }
  .frx-card-time-val { font-size: 21px; font-weight: 600; color: #fff; line-height: 1.25; margin-top: 2px; }
  .frx-card-time-date { font-size: 11.5px; color: var(--white-30); margin-top: 2px; }
  .frx-card-path { flex: 1; text-align: center; min-width: 70px; padding-top: 4px; }
  .frx-card-stops { font-size: 11px; color: var(--white-60); }
  .frx-card-path-line { position: relative; height: 1px; background: rgba(255,255,255,0.18); margin: 8px 0 6px; }
  .frx-card-path-line::after { content: '✈'; position: absolute; top: 50%; right: -2px; transform: translateY(-52%); font-size: 11px; color: var(--gold); background: var(--dark-2); padding-left: 3px; }
  .frx-card-duration { font-size: 13px; font-weight: 600; color: var(--white-80); }
  .frx-card-via { font-size: 10.5px; font-weight: 400; color: var(--white-30); }
  .frx-nearby-chip { display: inline-block; margin-top: 8px; padding: 3px 10px; border-radius: 100px; background: rgba(201,168,76,0.12); color: var(--gold-light); font-size: 10.5px; font-weight: 600; }
  .frx-arrives-note { margin-top: 12px; font-size: 12px; color: var(--white-60); }
  .frx-arrives-note span { color: var(--gold); margin-right: 4px; }
  .frx-next-day { font-size: 9.5px; font-weight: 700; color: var(--gold); margin-left: 2px; vertical-align: super; }

  .frx-col-actions { display: flex; flex-direction: column; gap: 10px; }
  .frx-col-actions .frx-row-continue-btn.frx-book-btn { width: 100%; padding: 13px 10px; border-radius: 8px; font-size: 13px; letter-spacing: 0.12em; }
  .frx-col-actions .frx-compare-btn { width: 100%; padding: 10px; border-radius: 8px; text-align: center; }

  @media (max-width: 1180px) {
    .frx-flight-main { grid-template-columns: 170px minmax(0, 1fr); }
    .frx-col-fares { grid-column: 1 / -1; }
    .frx-col-actions { grid-column: 1 / -1; flex-direction: row; justify-content: flex-end; }
    .frx-col-actions .frx-row-continue-btn.frx-book-btn, .frx-col-actions .frx-compare-btn { width: auto; min-width: 140px; }
  }
  @media (max-width: 600px) {
    .frx-flight-main { grid-template-columns: minmax(0, 1fr); gap: 16px; padding: 16px; }
    .frx-col-airline { flex-direction: row; flex-wrap: wrap; align-items: center; justify-content: space-between; }
    .frx-col-actions .frx-row-continue-btn.frx-book-btn, .frx-col-actions .frx-compare-btn { flex: 1; min-width: 0; }
  }
  /* Multi-leg (COMBO) cards: one compact row per leg */
  .frx-card-legs { display: flex; flex-direction: column; gap: 10px; }
  .frx-card-leg { display: flex; align-items: center; gap: 14px; }
  .frx-card-leg + .frx-card-leg { padding-top: 10px; border-top: 1px dashed rgba(255,255,255,0.08); }
  .frx-card-leg-label { width: 116px; flex-shrink: 0; }
  .frx-card-leg-label span { display: block; font-size: 10px; font-weight: 700; letter-spacing: 0.1em; text-transform: uppercase; color: var(--gold-light); }
  .frx-card-leg-label small { display: block; margin-top: 2px; font-size: 10px; color: var(--white-30); }
  .frx-card-leg .frx-card-times { min-width: 0; }

  .frx-details-toggle { flex-shrink: 0; background: transparent; border: 1px solid rgba(255,255,255,0.15); color: var(--white-60); font-family: 'Jost', sans-serif; font-size: 11px; font-weight: 600; letter-spacing: 0.04em; text-transform: uppercase; padding: 8px 14px; border-radius: 8px; cursor: pointer; transition: all var(--tr); }
  .frx-details-toggle:hover { border-color: rgba(201,168,76,0.4); color: var(--gold-light); }
  .frx-details-toggle .frx-toggle-icon { display: inline-block; margin-left: 4px; transition: transform var(--tr); }
  .frx-details-toggle.open .frx-toggle-icon { transform: rotate(45deg); }
  .frx-compare-btn { border-color: rgba(201,168,76,0.35); color: var(--gold-light); }
  .frx-compare-btn:hover { background: rgba(201,168,76,0.1); }

  /* ── Compare Fares modal ──────────────────────────────────────── */
  /* "Prices may have changed" (fares older than TripJack's 15-minute window) */
  .frx-stale { position: fixed; inset: 0; z-index: 2500; display: flex; align-items: center; justify-content: center; padding: 24px; background: rgba(0,0,0,0.75); backdrop-filter: blur(3px); }
  .frx-stale[hidden] { display: none; }
  .frx-stale-box { width: 100%; max-width: 430px; text-align: center; background: var(--dark-2); border: 1px solid rgba(201,168,76,0.35); border-radius: 20px; padding: 32px 30px 24px; box-shadow: 0 24px 60px rgba(0,0,0,0.6); }
  .frx-stale-icon { width: 54px; height: 54px; margin: 0 auto 14px; border-radius: 50%; display: flex; align-items: center; justify-content: center; background: rgba(201,168,76,0.12); color: var(--gold); font-size: 26px; }
  .frx-stale-box h2 { font-family: 'Cormorant Garamond', serif; font-size: 1.6rem; color: var(--gold-light); margin: 0 0 10px; }
  .frx-stale-box p { font-size: 13px; line-height: 1.65; color: var(--white-60); margin: 0 0 20px; }
  .frx-stale-refresh { display: block; width: 100%; padding: 14px; border: none; border-radius: 100px; background: linear-gradient(90deg, #c9a84c, #e8c96b); color: var(--dark); font-family: 'Jost', sans-serif; font-size: 12.5px; font-weight: 800; letter-spacing: 0.1em; text-transform: uppercase; cursor: pointer; }
  .frx-stale-dismiss { margin-top: 12px; background: none; border: none; color: var(--white-60); font-family: 'Jost', sans-serif; font-size: 12px; text-decoration: underline; text-underline-offset: 3px; cursor: pointer; }
  .frx-compare-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.72); backdrop-filter: blur(2px); z-index: 2000; align-items: center; justify-content: center; padding: 24px; }
  .frx-compare-overlay.open { display: flex; }
  .frx-compare-modal { background: var(--dark-2); border: 1px solid rgba(201,168,76,0.3); border-radius: 16px; box-shadow: 0 20px 60px rgba(0,0,0,0.5); max-width: 1080px; width: 100%; max-height: 85vh; display: flex; flex-direction: column; overflow: hidden; }
  .frx-compare-head { display: flex; align-items: center; justify-content: space-between; padding: 18px 24px; border-bottom: 1px solid rgba(255,255,255,0.08); flex-shrink: 0; }
  .frx-compare-title { font-size: 15px; font-weight: 700; color: #fff; letter-spacing: 0.01em; }
  .frx-compare-sub { font-size: 11px; color: var(--white-30); margin-top: 2px; }
  .frx-compare-close { background: rgba(255,255,255,0.06); border: none; color: var(--white-60); width: 30px; height: 30px; border-radius: 50%; font-size: 18px; line-height: 1; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: background 0.15s, color 0.15s; }
  .frx-compare-close:hover { color: #fff; background: rgba(255,255,255,0.14); }
  .frx-compare-scroll { overflow-x: hidden; overflow-y: auto; padding: 20px 24px 24px; scrollbar-width: thin; scrollbar-color: rgba(201,168,76,0.7) transparent; }
  .frx-compare-scroll::-webkit-scrollbar { width: 8px; }
  .frx-compare-scroll::-webkit-scrollbar-track { background: transparent; margin: 10px 0; }
  .frx-compare-scroll::-webkit-scrollbar-thumb { background: rgba(201,168,76,0.55); border-radius: 100px; border: 2px solid var(--dark-2); }
  .frx-compare-scroll::-webkit-scrollbar-thumb:hover { background: var(--gold); }
  .frx-compare-scroll::-webkit-scrollbar-button { display: none; height: 0; }
  .frx-compare-scroll-track::-webkit-scrollbar-button { display: none; width: 0; }
  .frx-compare-track-wrap { position: relative; }
  .frx-compare-scroll-track { overflow-x: auto; overflow-y: hidden; padding-bottom: 10px; scrollbar-width: thin; scrollbar-color: var(--gold) rgba(255,255,255,0.08); }
  /* The browser's default scrollbar is too thin/subtle on dark backgrounds
     to register as "there's more to see here" — style it explicitly gold
     so it reads as an affordance rather than disappearing into the modal. */
  .frx-compare-scroll-track::-webkit-scrollbar { height: 9px; }
  .frx-compare-scroll-track::-webkit-scrollbar-track { background: rgba(255,255,255,0.06); border-radius: 100px; }
  .frx-compare-scroll-track::-webkit-scrollbar-thumb { background: var(--gold); border-radius: 100px; }
  .frx-compare-scroll-track::-webkit-scrollbar-thumb:hover { background: var(--gold-light); }
  .frx-compare-grid { display: grid; gap: 1px; background: rgba(255,255,255,0.07); border: 1px solid rgba(255,255,255,0.07); border-radius: 12px; overflow: clip; min-width: 560px; width: 100%; }
  .frx-compare-fade { position: absolute; top: 0; bottom: 10px; right: 0; width: 48px; pointer-events: none; background: linear-gradient(90deg, transparent, var(--dark-2) 85%); display: flex; align-items: center; justify-content: flex-end; opacity: 0; transition: opacity 0.2s; }
  .frx-compare-fade.visible { opacity: 1; }
  .frx-compare-fade-arrow { width: 26px; height: 26px; border-radius: 50%; background: rgba(201,168,76,0.9); color: var(--dark); display: flex; align-items: center; justify-content: center; font-size: 13px; font-weight: 800; animation: frxFadeArrow 1.4s ease-in-out infinite; }
  @keyframes frxFadeArrow { 0%, 100% { transform: translateX(0); } 50% { transform: translateX(3px); } }
  .frx-compare-hint { font-size: 10.5px; color: var(--white-30); margin: -4px 0 10px; }
  .frx-compare-notes { list-style: none; margin: 18px 0 0; padding: 14px 18px; background: rgba(201,168,76,0.06); border: 1px solid rgba(201,168,76,0.18); border-left: 3px solid var(--gold); border-radius: 10px; display: flex; flex-direction: column; gap: 7px; }
  .frx-compare-notes li { position: relative; padding-left: 16px; font-size: 11.5px; line-height: 1.55; color: var(--white-60); }
  .frx-compare-notes li::before { content: ''; position: absolute; left: 0; top: 7px; width: 5px; height: 5px; border-radius: 50%; background: var(--gold); }
  .frx-cmp-cell { background: var(--dark-2); padding: 13px 16px; font-size: 12px; color: var(--white-80); line-height: 1.5; display: flex; flex-direction: column; justify-content: center; }
  .frx-cmp-label { position: sticky; left: 0; z-index: 2; background: linear-gradient(rgba(255,255,255,0.035), rgba(255,255,255,0.035)), var(--dark-2); box-shadow: 6px 0 12px -6px rgba(0,0,0,0.6); color: var(--gold); font-size: 10.5px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; }
  .frx-cmp-label small { display: block; margin-top: 2px; color: var(--white-30); font-size: 9.5px; text-transform: none; letter-spacing: 0; font-weight: 400; }
  .frx-cmp-head { align-items: center; text-align: center; background: rgba(255,255,255,0.035); }
  .frx-cmp-head.best { background: rgba(201,168,76,0.14); }
  .frx-cmp-fareid { font-size: 12.5px; font-weight: 700; color: #fff; text-transform: uppercase; letter-spacing: 0.04em; }
  .frx-cmp-badge { display: inline-block; margin-top: 5px; padding: 2px 9px; border-radius: 100px; background: rgba(201,168,76,0.9); color: var(--dark); font-size: 9px; font-weight: 800; letter-spacing: 0.05em; text-transform: uppercase; }
  .frx-cmp-value { text-align: center; }
  .frx-cmp-price { font-size: 17px; font-weight: 700; color: var(--gold-light); }
  .frx-cmp-book-cell { align-items: center; padding-top: 14px; padding-bottom: 14px; }
  .frx-compare-book { padding: 9px 22px; border: none; border-radius: 100px; background: linear-gradient(90deg, #c9a84c, #e8c96b); color: var(--dark); font-family: 'Jost', sans-serif; font-size: 10.5px; font-weight: 800; letter-spacing: 0.06em; text-transform: uppercase; cursor: pointer; white-space: nowrap; transition: transform 0.15s, box-shadow 0.15s; }
  .frx-compare-book:hover { transform: translateY(-1px); box-shadow: 0 6px 16px rgba(201,168,76,0.35); }
  .frx-compare-loading { color: var(--white-30); font-size: 11px; }
  .frx-cmp-policy { display: block; max-width: 190px; margin: 0 auto; color: var(--white-80); font-size: 11.5px; line-height: 1.55; }
  .frx-cmp-note { display: block; max-width: 190px; margin: 8px auto 0; padding-top: 8px; border-top: 1px dashed rgba(255,255,255,0.1); color: var(--white-30); font-size: 10.5px; line-height: 1.55; }
  .frx-cmp-note[hidden] { display: none; }
  .frx-cmp-subnote { display: block; max-width: 190px; margin: 4px auto 0; color: var(--white-30); font-size: 10.5px; line-height: 1.5; }
  .frx-cmp-more { display: inline-block; margin-top: 8px; align-self: center; background: none; border: none; padding: 0; color: var(--gold-light); font-size: 10px; font-weight: 600; letter-spacing: 0.02em; cursor: pointer; text-decoration: underline; text-underline-offset: 2px; }

  /* Fare list — one fare per line: radio · price, then fare type + cabin/refundability */
  .frx-col-fares { display: flex; flex-direction: column; min-width: 0; }
  .frx-opt-row { display: grid; grid-template-columns: 20px auto minmax(0, 1fr); column-gap: 10px; row-gap: 4px; align-items: center; padding: 10px 6px; border-bottom: 1px solid rgba(255,255,255,0.07); border-radius: 6px; cursor: pointer; transition: background var(--tr); }
  .frx-opt-row > input[type="radio"] { grid-row: 1 / span 2; align-self: start; margin: 4px 0 0; accent-color: var(--gold); width: 17px; height: 17px; cursor: pointer; }
  .frx-opt-row > .frx-opt-price { grid-column: 2; }
  .frx-opt-row > .frx-sr-badge { grid-column: 3; justify-self: start; }
  .frx-opt-row > .frx-opt-meta { grid-column: 2 / -1; }
  .frx-opt-row:hover { background: rgba(255,255,255,0.03); }
  .frx-opt-row.selected { background: rgba(201,168,76,0.08); }
  .frx-opt-price { font-size: 18px; font-weight: 700; color: #fff; white-space: nowrap; }
  .frx-opt-row.selected .frx-opt-price { color: var(--gold-light); }
  .frx-opt-meta { display: flex; flex-wrap: wrap; align-items: center; gap: 6px 10px; }
  .frx-opt-fareid { display: inline-block; padding: 2px 8px; border-radius: 4px; background: rgba(201,168,76,0.16); color: var(--gold-light); font-size: 10.5px; font-weight: 700; letter-spacing: 0.02em; }
  .frx-opt-desc { font-size: 12.5px; color: var(--white-60); }
  .frx-refund-ok { color: var(--green); }
  .frx-refund-part { color: #f0c47a; }
  .frx-refund-non { color: #f3a3a3; }
  .frx-opt-flex { font-size: 10.5px; font-weight: 600; color: var(--green); }
  /* "+N more fares" — rows past the 4th visible one are tucked away
     (a selected fare always stays visible). */
  .frx-flight:not(.frx-fares-open) .frx-opt-row.frx-opt-extra:not(.selected) { display: none; }
  .frx-more-fares { align-self: flex-end; margin-top: 10px; padding: 5px 14px; border-radius: 100px; border: 1px solid rgba(201,168,76,0.35); background: rgba(201,168,76,0.08); color: var(--gold-light); font-family: 'Jost', sans-serif; font-size: 11px; font-weight: 600; cursor: pointer; transition: background var(--tr); }
  .frx-more-fares:hover { background: rgba(201,168,76,0.16); }
  .frx-more-fares[hidden] { display: none; }

  .frx-row-continue-btn { padding: 10px 24px; border: none; border-radius: 100px; background: linear-gradient(90deg, #c9a84c, #e8c96b); color: var(--dark); font-family: 'Jost', sans-serif; font-size: 11px; font-weight: 800; letter-spacing: 0.07em; text-transform: uppercase; cursor: pointer; white-space: nowrap; transition: transform 0.15s, box-shadow 0.15s; }
  .frx-row-continue-btn:hover { transform: translateY(-1px); box-shadow: 0 6px 16px rgba(201,168,76,0.35); }
  .frx-group-title.frx-group-title-nudge { animation: frxNudge 1.1s ease; }
  @keyframes frxNudge { 0%, 100% { color: var(--gold); } 30% { color: #fff; text-shadow: 0 0 14px rgba(201,168,76,0.6); } }

  /* ── Expandable details panel: Flight / Fare / Fare Rules tabs ────── */
  .frx-flight-details { display: none; border-top: 1px solid rgba(255,255,255,0.08); background: rgba(0,0,0,0.15); padding: 18px 20px; }
  .frx-flight-details.open { display: block; }
  .frx-tabs { display: flex; gap: 4px; margin-bottom: 16px; border-bottom: 1px solid rgba(255,255,255,0.08); }
  .frx-tab { background: transparent; border: none; color: var(--white-60); font-family: 'Jost', sans-serif; font-size: 12px; font-weight: 600; letter-spacing: 0.04em; text-transform: uppercase; padding: 8px 14px; cursor: pointer; border-bottom: 2px solid transparent; margin-bottom: -1px; }
  .frx-tab.active { color: var(--gold-light); border-color: var(--gold); }
  .frx-tab-panel { display: none; }
  .frx-tab-panel.active { display: block; }

  .frx-detail-line { display: flex; justify-content: space-between; gap: 12px; font-size: 12.5px; color: var(--white-80); padding: 6px 0; border-bottom: 1px dashed rgba(255,255,255,0.06); }
  .frx-detail-line:last-child { border-bottom: none; }
  .frx-detail-line span:first-child { color: var(--white-30); }

  .frx-fare-table { width: 100%; border-collapse: collapse; font-size: 12.5px; }
  .frx-fare-table th { text-align: left; color: var(--gold); font-size: 10px; text-transform: uppercase; letter-spacing: 0.06em; padding: 6px 10px; border-bottom: 1px solid rgba(255,255,255,0.1); }
  .frx-fare-table td { padding: 8px 10px; color: var(--white-80); border-bottom: 1px dashed rgba(255,255,255,0.06); }
  .frx-fare-table tr:last-child td { border-bottom: none; }
  .frx-fare-table .total-row td { font-weight: 700; color: #fff; }

  /* ── Flight Details tab ────────────────────────────────────────── */
  .frx-fd-title { font-size: 13.5px; font-weight: 700; color: #fff; margin-bottom: 4px; }
  .frx-fd-sub { font-size: 11.5px; color: var(--white-60); margin-bottom: 18px; }
  .frx-fd-segment { display: flex; align-items: flex-start; gap: 16px; margin-bottom: 20px; padding-bottom: 18px; border-bottom: 1px solid rgba(255,255,255,0.07); }
  .frx-fd-point { flex: 1; min-width: 0; }
  .frx-fd-point-right { text-align: right; }
  .frx-fd-time { font-size: 14px; font-weight: 700; color: #fff; margin-bottom: 4px; }
  .frx-fd-city { font-size: 12.5px; color: var(--white-80); }
  .frx-fd-airport { font-size: 11px; color: var(--white-30); margin-top: 2px; }
  .frx-fd-terminal { font-size: 10.5px; color: var(--gold-light); margin-top: 2px; }
  .frx-fd-mid { flex: 0 0 140px; text-align: center; padding-top: 2px; }
  .frx-fd-duration { font-size: 11px; color: var(--white-60); margin-top: 4px; }
  .frx-fd-baggage-title { font-size: 11px; font-weight: 700; color: var(--gold-light); text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 10px; }
  @media (max-width: 560px) {
    .frx-fd-segment { flex-direction: column; }
    .frx-fd-point-right { text-align: left; }
    .frx-fd-mid { text-align: left; padding-left: 0; }
  }

  .frx-detail-line { display: flex; justify-content: space-between; gap: 12px; font-size: 12.5px; color: var(--white-80); padding: 6px 0; border-bottom: 1px dashed rgba(255,255,255,0.06); }
  .frx-detail-line:last-child { border-bottom: none; }
  .frx-detail-line span:first-child { color: var(--white-30); }

  /* ── Fare Rules tab ─────────────────────────────────────────────── */
  .frx-rules-loading, .frx-rules-empty { color: var(--white-30); font-size: 12.5px; }
  .frx-rules-misc { margin: 8px 0 14px; padding: 12px 14px; border-radius: 10px; border: 1px solid rgba(255,255,255,0.08); background: rgba(255,255,255,0.02); font-size: 12px; line-height: 1.6; color: var(--white-80); }
  .frx-rules-misc p { margin: 0 0 6px; }
  .frx-rules-misc p:last-child { margin-bottom: 0; }
  .frx-rules-segment { font-size: 12px; font-weight: 700; color: var(--gold-light); letter-spacing: 0.04em; margin-bottom: 4px; }
  .frx-rules-hint { font-size: 11px; color: var(--white-30); margin-bottom: 12px; }
  .frx-rules-table { width: 100%; border-collapse: collapse; font-size: 12px; margin-bottom: 12px; }
  .frx-rules-table th { text-align: left; color: var(--gold); font-size: 9.5px; text-transform: uppercase; letter-spacing: 0.04em; padding: 8px 10px; border-bottom: 1px solid rgba(255,255,255,0.1); white-space: nowrap; }
  .frx-rules-table td { padding: 10px; color: var(--white-80); border-bottom: 1px dashed rgba(255,255,255,0.06); vertical-align: top; }
  .frx-rules-table tr:last-child td { border-bottom: none; }
  .frx-rules-notes { font-size: 10.5px; color: var(--white-30); line-height: 1.8; }
  .frx-rules-notes p { margin: 0; }

  .frx-no-match { text-align: center; padding: 40px 20px; color: var(--white-30); font-size: 13px; }


</style>
@endpush

@section('content')
<div class="frx-wrap">

  <div class="frx-sticky-sentinel" id="frxStickySentinel"></div>
  <div class="frx-sticky" id="frxSticky">
  {{-- Shown for any search that was actually run — including one that failed
       or found nothing, so the guest can still Modify Search from here. --}}
  @if($hasSearched)
    <div class="frx-topbar">
      @if($searchParams['tripType'] === 'multi')
        <div class="frx-route">
          <div>
            <div class="frx-route-city">{{ collect($searchParams['legs'])->pluck('from')->implode(' → ') }} → {{ end($searchParams['legs'])['to'] }}</div>
            <div class="frx-route-sub">Multi-City &middot; {{ count($searchParams['legs']) }} Flights</div>
          </div>
        </div>
      @else
        @php
          // "Bengaluru, India" under each code, taken from a flight that
          // actually uses the searched airport (not a nearby one).
          $onward = $cardGroups ? collect(reset($cardGroups)['flights']) : collect();
          $fromFlight = $onward->firstWhere('depAirport', $searchParams['from']) ?? $onward->first();
          $toFlight = $onward->firstWhere('arrAirport', $searchParams['to']) ?? $onward->first();
          // Without flights (a failed or empty search), fall back to the
          // airport list the search box uses (public/data/airports.json).
          $airportPlaces = null;
          $placeOf = function ($code) use (&$airportPlaces) {
              if ($airportPlaces === null) {
                  $file = public_path('data/airports.json');
                  $airportPlaces = is_file($file)
                      ? \Illuminate\Support\Facades\Cache::rememberForever('airport_places:'.filemtime($file), fn () => collect(json_decode(file_get_contents($file), true) ?: [])
                          ->mapWithKeys(fn ($a) => [$a[0] => $a[1].', '.$a[3]])->all())
                      : [];
              }

              return $airportPlaces[$code] ?? null;
          };
          $fromPlace = implode(', ', array_filter([$fromFlight['fromCity'] ?? null, $fromFlight['fromCountry'] ?? null])) ?: ($placeOf($searchParams['from']) ?? 'Origin');
          $toPlace = implode(', ', array_filter([$toFlight['toCity'] ?? null, $toFlight['toCountry'] ?? null])) ?: ($placeOf($searchParams['to']) ?? 'Destination');
        @endphp
        <div class="frx-route">
          <div>
            <div class="frx-route-city">{{ $searchParams['from'] }}</div>
            <div class="frx-route-sub" title="{{ $fromPlace }}">{{ $fromPlace }}</div>
          </div>
          <span class="frx-route-arrow">&#9992;</span>
          <div>
            <div class="frx-route-city">{{ $searchParams['to'] }}</div>
            <div class="frx-route-sub" title="{{ $toPlace }}">{{ $toPlace }}</div>
          </div>
        </div>
        <div class="frx-tb-divider"></div>
        <div>
          <div class="frx-tb-label">Departure Date</div>
          <div class="frx-tb-value">{{ \Carbon\Carbon::parse($searchParams['departDate'])->format('D, M jS Y') }}</div>
        </div>
        @if($searchParams['tripType'] === 'return' && $searchParams['returnDate'])
          <div class="frx-tb-divider"></div>
          <div>
            <div class="frx-tb-label">Return Date</div>
            <div class="frx-tb-value">{{ \Carbon\Carbon::parse($searchParams['returnDate'])->format('D, M jS Y') }}</div>
          </div>
        @endif
      @endif
      @php
        $paxParts = array_filter([
            $searchParams['adults'] ? $searchParams['adults'].' Adult'.($searchParams['adults'] > 1 ? 's' : '') : null,
            $searchParams['children'] ? $searchParams['children'].' Child'.($searchParams['children'] > 1 ? 'ren' : '') : null,
            $searchParams['infants'] ? $searchParams['infants'].' Infant'.($searchParams['infants'] > 1 ? 's' : '') : null,
        ]);
        $prefCode = $searchParams['preferredAirline'] ?? '';
        $prefName = $prefCode === '' ? 'None' : ($airlineNames[$prefCode] ?? optional($airlineFacets->firstWhere('code', $prefCode))['name'] ?? $prefCode);
      @endphp
      <div class="frx-tb-divider"></div>
      <div>
        <div class="frx-tb-label">Passengers &amp; Class</div>
        <div class="frx-tb-value">{{ implode(', ', $paxParts) }} <span class="frx-tb-sep">|</span> {{ strtoupper(str_replace('_', ' ', $searchParams['cabinClass'])) }}</div>
      </div>
      <div class="frx-tb-divider"></div>
      <div class="{{ $prefCode === '' ? 'frx-tb-pref-none' : '' }}">
        <div class="frx-tb-label">Preferred Airline</div>
        <div class="frx-tb-value">{{ $prefName }}</div>
      </div>
      <div class="frx-tb-spacer"></div>
      <button type="button" class="frx-modify-btn" id="frxModifyToggle" aria-expanded="{{ $cardGroups ? 'false' : 'true' }}" aria-controls="frxModifyPanel">Modify Search <span class="frx-modify-chev" aria-hidden="true">&#8964;</span></button>
    </div>
  @endif

  {{-- Starts open when a search found nothing, so the guest can adjust it
       straight away. --}}
  <div class="frx-modify-panel {{ $hasSearched && ! $cardGroups ? 'open' : '' }}" id="frxModifyPanel">
    @include('partials.flight-search-widget', [
      'action' => route('flights.search'),
      'from' => $searchParams['from'],
      'to' => $searchParams['to'],
      'departDate' => $searchParams['departDate'],
      'returnDate' => $searchParams['returnDate'],
      'adults' => $searchParams['adults'],
      'children' => $searchParams['children'],
      'infants' => $searchParams['infants'],
      'cabinClass' => $searchParams['cabinClass'],
      'tripType' => $searchParams['tripType'],
      'preferredAirline' => $searchParams['preferredAirline'] ?? '',
      'fareType' => $searchParams['fareType'] ?? 'REGULAR',
      'directFlightOnly' => $searchParams['directFlightOnly'] ?? false,
    ])
  </div>
  </div>

  @php
    // $searchError only ever carries an error from THIS request's own
    // search attempt — a redirect from review() (e.g. "session expired",
    // "select a flight for every leg", a TripJack Review failure) flashes
    // its message to session('booking_error') instead, which this page was
    // previously never reading, so those redirects landed on a bare
    // /flights/search with the error silently dropped and just the generic
    // empty-state message shown instead.
    // review() also puts a fixed ?notice= code on the URL — it survives
    // even if a background request from another tab consumed the flash.
    $noticeMessages = [
        'unavailable' => 'Sorry, that fare just sold out with the airline. Here are the latest fares — please choose another flight.',
        'review_failed' => 'We couldn’t confirm that fare with the airline. Please choose another flight.',
        'special_return' => 'Special Return fares can only be booked as a matched pair. Please pick the return fare marked “Pairs with your onward fare”, or choose regular fares on both flights.',
        'legs' => 'Please select a flight for every leg of your trip.',
        'expired' => 'Your search session has expired, so the fares were refreshed. Please choose your flight again.',
    ];
    $noticeCode = (string) request()->query('notice');
    $notice = $noticeMessages[$noticeCode] ?? null;
    // "Sold out" always uses our own wording; other codes prefer the fuller
    // flashed message when it's still there. Shown as a toast (the site's
    // global one, layouts.frontend) — the results stay the focus. Only a
    // failed search of THIS request stays inline, as it explains the empty
    // list below it.
    $toastMessage = $noticeCode === 'unavailable' ? $notice : (session('booking_error') ?: $notice);
    $displayError = $searchError;
  @endphp

  @if($displayError)
    <div class="frx-error">⚠️ {{ $displayError }}</div>
  @endif

  @if($toastMessage)
    <script>
      window.addEventListener('load', function () {
        if (typeof showToast === 'function') {
          showToast(@json($noticeCode === 'unavailable' ? 'Fare sold out' : 'Please choose again'), @json($toastMessage), 'error');
        }
      });
    </script>
  @endif

  @if($results === null && ! $displayError)
    <div class="frx-empty">Enter your route and travel date above to see live fares.</div>
  @elseif(! $cardGroups)
    <div class="frx-empty">No flights found for this route and date. Try adjusting your search.</div>
  @else
    <div class="frx-body" id="frxBody">
      <aside class="frx-sidebar" id="frxSidebar">
        <div class="frx-side-block">
          <div class="frx-side-head">
            <p class="frx-side-title">Price</p>
            <button type="button" class="frx-block-toggle" aria-expanded="true" aria-label="Collapse price filter">&minus;</button>
          </div>
          <div class="frx-side-body">
            <div class="frx-range" id="frxPriceRange">
              <div class="frx-range-track"><div class="frx-range-fill" id="frxRangeFill"></div></div>
              <input type="range" id="frxRangeMin" min="{{ $minPrice }}" max="{{ $maxPrice }}" value="{{ $minPrice }}" step="1" aria-label="Minimum price">
              <input type="range" id="frxRangeMax" min="{{ $minPrice }}" max="{{ $maxPrice }}" value="{{ $maxPrice }}" step="1" aria-label="Maximum price">
            </div>
            <div class="frx-range-labels"><span id="frxRangeMinLabel">&#8377;{{ number_format($minPrice) }}</span><span id="frxRangeMaxLabel">&#8377;{{ number_format($maxPrice) }}</span></div>
            <div class="frx-price-row">
              <div class="fr-field"><label for="frxPriceMin">Min</label><div class="frx-price-input"><span>&#8377;</span><input type="number" id="frxPriceMin" value="{{ $minPrice }}" min="0"></div></div>
              <span class="frx-price-dash">&ndash;</span>
              <div class="fr-field"><label for="frxPriceMax">Max</label><div class="frx-price-input"><span>&#8377;</span><input type="number" id="frxPriceMax" value="{{ $maxPrice }}" min="0"></div></div>
              <button type="button" class="frx-price-apply" id="frxPriceApply">Apply</button>
            </div>
          </div>
        </div>

        @if($nearbyCount > 0)
          <div class="frx-side-block frx-side-block-tight">
            <label class="frx-check">
              <input type="checkbox" id="frxHideNearby">
              <span>Hide Nearby Airports</span>
              <span class="frx-check-count">{{ $nearbyCount }}</span>
            </label>
          </div>
        @endif

        <div class="frx-side-block">
          <div class="frx-side-head"><p class="frx-side-title">Popular Filters</p></div>
          <div class="frx-side-body">
            <div class="frx-chip-row" id="frxPopular">
              <span class="frx-chip" data-popular="stops:0">Non Stop</span>
              <span class="frx-chip" data-popular="stops:1">1 Stop</span>
              <span class="frx-chip" data-popular="dep:720">Departure: 12-18</span>
              <span class="frx-chip" data-popular="dep:1080">Departure: 18-00</span>
              @foreach($popularAirlines as $a)
                <span class="frx-chip" data-popular="airline:{{ $a['code'] }}">{{ $a['name'] }}</span>
              @endforeach
            </div>
          </div>
        </div>

        <div class="frx-side-block">
          <div class="frx-side-head">
            <p class="frx-side-title">Stops</p>
            <button type="button" class="frx-block-toggle" aria-expanded="true" aria-label="Collapse stops filter">&minus;</button>
          </div>
          <div class="frx-side-body">
            <div class="frx-seg" id="frxStopsFilter" role="group" aria-label="Stops">
              <button type="button" class="frx-seg-btn" data-stops="0">0</button>
              <button type="button" class="frx-seg-btn" data-stops="1">1</button>
              <button type="button" class="frx-seg-btn" data-stops="2">2</button>
              <button type="button" class="frx-seg-btn" data-stops="3">3+</button>
            </div>
          </div>
        </div>

        {{-- Departure / Arrival time of day, per leg (a return trip's two legs
             leave from different cities, so each gets its own buckets). --}}
        @foreach($cardGroups as $gKey => $g)
          @foreach([['dep', 'Departure From', $g['meta']['from']], ['arr', 'Arrival At', $g['meta']['to']]] as [$kind, $title, $code])
            @php $cityName = collect($g['flights'])->first()[$kind === 'dep' ? 'fromCity' : 'toCity'] ?: $code; @endphp
            <div class="frx-side-block">
              <div class="frx-side-head">
                <p class="frx-side-title">{{ $title }} {{ $cityName }}@if(count($cardGroups) > 1) <span class="frx-side-sub">({{ $g['meta']['label'] }})</span>@endif</p>
                <button type="button" class="frx-block-toggle" aria-expanded="true" aria-label="Collapse filter">&minus;</button>
              </div>
              <div class="frx-side-body">
                <div class="frx-seg frx-seg-time" role="group" aria-label="{{ $title }} {{ $cityName }}">
                  @foreach($timeBuckets as $b)
                    <button type="button" class="frx-seg-btn" data-time="{{ $kind }}" data-group="{{ $gKey }}" data-from="{{ $b['from'] }}" data-to="{{ $b['to'] }}">
                      <span class="frx-seg-icon">{!! $b['icon'] !!}</span>{{ $b['label'] }}
                    </button>
                  @endforeach
                </div>
                <button type="button" class="frx-timeframe-btn" data-timeframe-toggle>Select Specific Timeframe &#9719;</button>
                <div class="frx-timeframe" hidden data-timeframe="{{ $kind }}" data-group="{{ $gKey }}">
                  <select aria-label="From" data-tf="from">
                    @for($h = 0; $h < 24; $h++)<option value="{{ $h * 60 }}">{{ sprintf('%02d:00', $h) }}</option>@endfor
                  </select>
                  <span>to</span>
                  <select aria-label="To" data-tf="to">
                    @for($h = 1; $h <= 24; $h++)<option value="{{ $h * 60 }}" @selected($h === 24)>{{ $h === 24 ? '23:59' : sprintf('%02d:00', $h) }}</option>@endfor
                  </select>
                  <button type="button" class="frx-price-apply" data-tf-apply>Apply</button>
                  <button type="button" class="frx-link-btn" data-tf-clear>Clear</button>
                </div>
              </div>
            </div>
          @endforeach
        @endforeach

        <div class="frx-side-block">
          <div class="frx-side-head">
            <p class="frx-side-title">Baggage</p>
            <button type="button" class="frx-block-toggle" aria-expanded="true" aria-label="Collapse baggage filter">&minus;</button>
          </div>
          <div class="frx-side-body">
            <label class="frx-check"><input type="checkbox" id="frxShowCheckin"><span>Show Check-in Baggage</span></label>
          </div>
        </div>

        @if($fareGroupFacets->isNotEmpty())
          <div class="frx-side-block">
            <div class="frx-side-head">
              <p class="frx-side-title">Fare Type</p>
              <button type="button" class="frx-block-toggle" aria-expanded="true" aria-label="Collapse fare type filter">&minus;</button>
            </div>
            <div class="frx-side-body frx-check-list">
              @foreach($fareGroupFacets as $group => $count)
                <label class="frx-check"><input type="checkbox" data-filter="fareGroup" value="{{ $group }}"><span>{{ $group }}</span><span class="frx-check-count">{{ $count }}</span></label>
              @endforeach
            </div>
          </div>
        @endif

        <div class="frx-side-block">
          <div class="frx-side-head">
            <p class="frx-side-title">Flight Number <button type="button" class="frx-link-btn" id="frxFlightNoClear" hidden>Clear</button></p>
            <button type="button" class="frx-block-toggle" aria-expanded="true" aria-label="Collapse flight number filter">&minus;</button>
          </div>
          <div class="frx-side-body">
            <div class="frx-inline-add">
              <input type="text" id="frxFlightNoInput" placeholder="Eg. 123 or 6E-123" maxlength="10" autocomplete="off">
              <button type="button" id="frxFlightNoAdd" aria-label="Add flight number">+</button>
            </div>
            <div class="frx-tag-row" id="frxFlightNoTags"></div>
          </div>
        </div>

        <div class="frx-side-block">
          <div class="frx-side-head">
            <p class="frx-side-title">Airlines <button type="button" class="frx-link-btn" id="frxAirlineClear" hidden>Clear</button></p>
            <button type="button" class="frx-block-toggle" aria-expanded="true" aria-label="Collapse airlines filter">&minus;</button>
          </div>
          <div class="frx-side-body">
            <div class="frx-search-input">
              <span aria-hidden="true">&#9906;</span>
              <input type="search" id="frxAirlineSearch" placeholder="Search Airline Name" autocomplete="off">
            </div>
            <div class="frx-airline-list" id="frxAirlineFilter">
              @foreach($airlineFacets as $a)
                <label class="frx-airline-opt" data-name="{{ strtolower($a['name'].' '.$a['code']) }}">
                  <span class="frx-airline-opt-label">
                    <input type="checkbox" data-filter="airline" value="{{ $a['code'] }}">
                    {{ $a['name'] }} <span class="frx-airline-count">{{ $a['count'] }}</span>
                  </span>
                  <span class="frx-airline-price">&#8377;{{ number_format($a['minPrice']) }}</span>
                </label>
              @endforeach
            </div>
          </div>
        </div>

        <div class="frx-side-block">
          <div class="frx-side-head">
            <p class="frx-side-title">Cancellation Type</p>
            <button type="button" class="frx-block-toggle" aria-expanded="true" aria-label="Collapse cancellation filter">&minus;</button>
          </div>
          <div class="frx-side-body frx-check-list">
            <label class="frx-check"><input type="checkbox" data-filter="refundable" value="0"><span>Non Refundable</span><span class="frx-check-count">{{ $refundFacets['0'] }}</span></label>
            @if($refundFacets['2'] > 0)
              <label class="frx-check"><input type="checkbox" data-filter="refundable" value="2"><span>Partially Refundable</span><span class="frx-check-count">{{ $refundFacets['2'] }}</span></label>
            @endif
            <label class="frx-check"><input type="checkbox" data-filter="refundable" value="1"><span>Refundable</span><span class="frx-check-count">{{ $refundFacets['1'] }}</span></label>
          </div>
        </div>

        {{-- The rest start collapsed, like TripJack's "+" sections. --}}
        @if($depTerminalFacets->isNotEmpty() || $arrTerminalFacets->isNotEmpty())
          <div class="frx-side-block collapsed">
            <div class="frx-side-head">
              <p class="frx-side-title">Terminal</p>
              <button type="button" class="frx-block-toggle" aria-expanded="false" aria-label="Expand terminal filter">+</button>
            </div>
            <div class="frx-side-body frx-check-list">
              @if($depTerminalFacets->isNotEmpty())<p class="frx-check-heading">Departure</p>@endif
              @foreach($depTerminalFacets as $t)
                <label class="frx-check"><input type="checkbox" data-filter="depTerminal" value="{{ $t['value'] }}"><span>{{ $t['label'] }}</span><span class="frx-check-count">{{ $t['count'] }}</span></label>
              @endforeach
              @if($arrTerminalFacets->isNotEmpty())<p class="frx-check-heading">Arrival</p>@endif
              @foreach($arrTerminalFacets as $t)
                <label class="frx-check"><input type="checkbox" data-filter="arrTerminal" value="{{ $t['value'] }}"><span>{{ $t['label'] }}</span><span class="frx-check-count">{{ $t['count'] }}</span></label>
              @endforeach
            </div>
          </div>
        @endif

        <div class="frx-side-block collapsed">
          <div class="frx-side-head">
            <p class="frx-side-title">Airport</p>
            <button type="button" class="frx-block-toggle" aria-expanded="false" aria-label="Expand airport filter">+</button>
          </div>
          <div class="frx-side-body frx-check-list">
            <p class="frx-check-heading">Departure</p>
            @foreach($depAirportFacets as $ap)
              <label class="frx-check"><input type="checkbox" data-filter="depAirport" value="{{ $ap['code'] }}"><span>{{ $ap['code'] }} &middot; {{ $ap['name'] }}</span><span class="frx-check-count">{{ $ap['count'] }}</span></label>
            @endforeach
            <p class="frx-check-heading">Arrival</p>
            @foreach($arrAirportFacets as $ap)
              <label class="frx-check"><input type="checkbox" data-filter="arrAirport" value="{{ $ap['code'] }}"><span>{{ $ap['code'] }} &middot; {{ $ap['name'] }}</span><span class="frx-check-count">{{ $ap['count'] }}</span></label>
            @endforeach
          </div>
        </div>

        @if($layoverAirportFacets->isNotEmpty())
          <div class="frx-side-block collapsed">
            <div class="frx-side-head">
              <p class="frx-side-title">Layover Airport</p>
              <button type="button" class="frx-block-toggle" aria-expanded="false" aria-label="Expand layover airport filter">+</button>
            </div>
            <div class="frx-side-body frx-check-list">
              @foreach($layoverAirportFacets as $code => $count)
                <label class="frx-check"><input type="checkbox" data-filter="via" value="{{ $code }}"><span>{{ $code }}</span><span class="frx-check-count">{{ $count }}</span></label>
              @endforeach
            </div>
          </div>
        @endif

        @if($durationMax > $durationMin)
          <div class="frx-side-block collapsed">
            <div class="frx-side-head">
              <p class="frx-side-title">Duration</p>
              <button type="button" class="frx-block-toggle" aria-expanded="false" aria-label="Expand duration filter">+</button>
            </div>
            <div class="frx-side-body">
              <input type="range" class="frx-single-range" id="frxDurationMax" min="{{ $durationMin }}" max="{{ $durationMax }}" step="30" value="{{ $durationMax }}" aria-label="Maximum journey duration">
              <div class="frx-range-labels"><span>{{ $fmtDuration($durationMin) }}</span><span>Up to <b id="frxDurationLabel">{{ $fmtDuration($durationMax) }}</b></span></div>
            </div>
          </div>
        @endif

        @if($layoverCeil > 0)
          <div class="frx-side-block collapsed">
            <div class="frx-side-head">
              <p class="frx-side-title">Layover Duration</p>
              <button type="button" class="frx-block-toggle" aria-expanded="false" aria-label="Expand layover duration filter">+</button>
            </div>
            <div class="frx-side-body">
              <input type="range" class="frx-single-range" id="frxLayoverMax" min="0" max="{{ $layoverCeil }}" step="30" value="{{ $layoverCeil }}" aria-label="Maximum layover">
              <div class="frx-range-labels"><span>0h</span><span>Longest layover up to <b id="frxLayoverLabel">{{ $fmtDuration($layoverCeil) }}</b></span></div>
              <p class="frx-price-hint">Non-stop flights are always included.</p>
            </div>
          </div>
        @endif

        <span class="frx-reset-btn" id="frxResetFilters">Reset all filters</span>
      </aside>

      <main class="frx-main">
        <div class="frx-main-head">
          <div class="frx-head-row">
            <button type="button" class="frx-side-toggle" id="frxSideToggle" aria-controls="frxSidebar" aria-expanded="true" title="Hide filters">&laquo;</button>
            @if($showDateStrip)
              <div class="frx-dates" id="frxDates"
                data-current="{{ $searchParams['departDate'] }}"
                data-current-fare="{{ $currentDayFare }}"
                data-return="{{ $searchParams['tripType'] === 'return' ? $searchParams['returnDate'] : '' }}"
                data-today="{{ now()->toDateString() }}">
                <button type="button" class="frx-dates-nav" id="frxDatesPrev" aria-label="Earlier dates">&lsaquo;</button>
                <div class="frx-dates-track" id="frxDatesTrack"></div>
                <button type="button" class="frx-dates-nav" id="frxDatesNext" aria-label="Later dates">&rsaquo;</button>
              </div>
            @endif
          </div>

          <div class="frx-picks-row">
            <div class="frx-picks">
              <button type="button" class="frx-pick active" data-sort="price" data-dir="asc">
                <span class="frx-pick-icon">&#8377;</span>
                <span>
                  <span class="frx-pick-title">Cheapest</span>
                  @foreach($quickPicks['cheapest'] as $pick)
                    <span class="frx-pick-line" style="display:block;">{{ $pick['label'] ? $pick['label'].': ' : '' }}<b>&#8377;{{ number_format($pick['price']) }}</b> &middot; Duration: {{ $fmtDuration($pick['duration']) }}</span>
                  @endforeach
                </span>
              </button>
              <button type="button" class="frx-pick" data-sort="duration" data-dir="asc">
                <span class="frx-pick-icon">&#9889;&#xFE0E;</span>
                <span>
                  <span class="frx-pick-title">Fastest</span>
                  @foreach($quickPicks['fastest'] as $pick)
                    <span class="frx-pick-line" style="display:block;">{{ $pick['label'] ? $pick['label'].': ' : '' }}<b>&#8377;{{ number_format($pick['price']) }}</b> &middot; Duration: {{ $fmtDuration($pick['duration']) }}</span>
                  @endforeach
                </span>
              </button>
            </div>
            <div class="frx-share">
              <span>Share by:</span>
              <a class="frx-share-btn" id="frxShareWa" href="#" target="_blank" rel="noopener" title="Share on WhatsApp" aria-label="Share on WhatsApp">
                <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2a10 10 0 0 0-8.6 15.1L2 22l5-1.3A10 10 0 1 0 12 2zm0 18.2a8.2 8.2 0 0 1-4.2-1.1l-.3-.2-3 .8.8-2.9-.2-.3A8.2 8.2 0 1 1 12 20.2zm4.5-6.1c-.2-.1-1.5-.7-1.7-.8s-.4-.1-.6.1-.7.8-.8 1-.3.2-.5.1a6.7 6.7 0 0 1-3.3-2.9c-.3-.4.3-.4.7-1.3.1-.2 0-.3 0-.4l-.8-1.8c-.2-.5-.4-.4-.6-.4h-.5a1 1 0 0 0-.7.3 3 3 0 0 0-.9 2.2 5.2 5.2 0 0 0 1.1 2.8 11.9 11.9 0 0 0 4.6 4c1.7.7 2.3.8 3.2.6a2.7 2.7 0 0 0 1.8-1.3 2.2 2.2 0 0 0 .1-1.3c0-.1-.2-.2-.4-.3z"/></svg>
              </a>
              <a class="frx-share-btn" id="frxShareMail" href="#" title="Share by email" aria-label="Share by email">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg>
              </a>
              <button type="button" class="frx-share-btn" id="frxShareCopy" title="Copy link" aria-label="Copy link">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M10 14a5 5 0 0 0 7 0l3-3a5 5 0 0 0-7-7l-1 1"/><path d="M14 10a5 5 0 0 0-7 0l-3 3a5 5 0 0 0 7 7l1-1"/></svg>
              </button>
              <span class="frx-share-copied" id="frxShareCopied" hidden>Link copied</span>
            </div>
          </div>
        </div>

        {{-- A direct child of .frx-main (not of .frx-main-head) so it can stay
             pinned for the whole length of the flight list. --}}
        <div class="frx-sortbar-sentinel" id="frxSortSentinel"></div>
        <div class="frx-sortbar" id="frxSortbar">
            <span class="frx-sortbar-label">Sort by</span>
            <div class="frx-sort-keys">
              <button type="button" class="frx-sort-btn" data-sort="departure">Departure <span class="frx-sort-arrow">&#9650;</span></button>
              <button type="button" class="frx-sort-btn" data-sort="duration">Duration <span class="frx-sort-arrow">&#9650;</span></button>
              <button type="button" class="frx-sort-btn" data-sort="arrival">Arrival <span class="frx-sort-arrow">&#9650;</span></button>
              <button type="button" class="frx-sort-btn active" data-sort="price">Price <span class="frx-sort-arrow">&#9650;</span></button>
            </div>        </div>

        <form method="POST" action="{{ route('flights.review') }}" id="frReviewForm" target="_blank">
          @csrf

          @foreach($cardGroups as $key => $group)
            <p class="frx-group-title" id="frxGroupTitle_{{ $key }}">{{ $group['meta']['label'] }} &middot; {{ $group['meta']['from'] }} &rarr; {{ $group['meta']['to'] }}</p>

            <div class="frx-group-list" data-group="{{ $key }}">
            @foreach($group['flights'] as $fIdx => $flight)
              @php $rowId = 'frxRow_'.$key.'_'.$fIdx; @endphp
              <div class="frx-flight"
                data-stops="{{ min(3, $flight['stops']) }}"
                data-group="{{ $key }}"
                data-dep-minute="{{ $flight['depMinute'] }}"
                data-arr-minute="{{ $flight['arrMinute'] }}"
                data-dep-airport="{{ $flight['depAirport'] }}"
                data-arr-airport="{{ $flight['arrAirport'] }}"
                data-dep-terminal="{{ $flight['fromTerminal'] ? $flight['depAirport'].'|'.$flight['fromTerminal'] : '' }}"
                data-arr-terminal="{{ $flight['toTerminal'] ? $flight['arrAirport'].'|'.$flight['toTerminal'] : '' }}"
                data-nearby="{{ $flight['depAirport'] !== $group['meta']['from'] || $flight['arrAirport'] !== $group['meta']['to'] ? '1' : '0' }}"
                data-via="{{ implode(',', $flight['via']) }}"
                data-layover="{{ $flight['layoverMax'] }}"
                data-flight-nos="{{ implode(',', $flight['flightNos']) }}"
                data-airline="{{ $flight['airlineCode'] }}"
                data-price="{{ (int) $flight['minPrice'] }}"
                data-duration="{{ (int) $flight['duration'] }}"
                data-departure="{{ $flight['depSort'] }}"
                data-arrival="{{ $flight['arrSort'] }}"
                data-order="{{ $fIdx }}"
                data-match="1"
                {{-- Only the first page of each list is laid out on load; the
                     rest are revealed 20 at a time as the guest scrolls. --}}
                @if($fIdx >= $pageSize) style="display:none" @endif>

                @php
                  // TripJack-style card: airline | journey | fare list | actions.
                  $cardDate = fn ($raw) => $raw ? \Carbon\Carbon::parse($raw)->format('M d') : '';
                  $seatsLeft = collect($flight['options'])->pluck('seatsLeft')->filter(fn ($s) => is_numeric($s) && (int) $s > 0)->min();
                  $isNearby = $flight['depAirport'] !== $group['meta']['from'] || $flight['arrAirport'] !== $group['meta']['to'];
                  $arrivesNextDay = count($flight['legs']) === 1 && ($flight['legs'][0]['nextDay'] ?? false);
                  // "NDC_XPRESS_PROMO" → "NDC Xpress Promo" (acronyms stay upper case, like TripJack's own labels)
                  $fareLabel = fn ($id) => collect(explode('_', strtoupper((string) $id)))
                      ->map(fn ($w) => in_array($w, ['SME', 'NDC', 'TJ', 'LCC', 'GDS', 'SOTO', 'SOTI'], true) ? $w : ucfirst(strtolower($w)))
                      ->implode(' ');
                @endphp
                <div class="frx-flight-main">
                  <div class="frx-col-airline">
                    <div class="frx-card-airline">
                      <img src="https://images.kiwi.com/airlines/64/{{ $flight['airlineCode'] }}.png" loading="lazy" decoding="async" width="34" height="34" onerror="this.onerror=null;this.src='https://ui-avatars.com/api/?name={{ urlencode($flight['airlineName']) }}&background=1a1a1a&color=C9A84C&size=64'" alt="{{ $flight['airlineName'] }}">
                      <div>
                        <div class="frx-card-airline-name">{{ $flight['airlineName'] }}</div>
                        <div class="frx-card-flightno">{{ implode(', ', $flight['flightNos']) ?: $flight['airlineCode'].' '.$flight['flightNo'] }}</div>
                      </div>
                    </div>
                    <button type="button" class="frx-details-toggle frx-view-details" data-target="{{ $rowId }}">View Details <span class="frx-toggle-icon">+</span></button>
                    @if($seatsLeft)
                      <div class="frx-seats-left">Seats left: {{ $seatsLeft }}</div>
                    @endif
                  </div>

                  <div class="frx-col-journey">
                  @if(count($flight['legs']) > 1)
                    {{-- COMBO itinerary: one line per leg (Onward / Return, or each Multi-City flight). --}}
                    <div class="frx-card-legs">
                      @foreach($flight['legs'] as $lIdx => $leg)
                        <div class="frx-card-leg">
                          <div class="frx-card-leg-label">
                            <span>{{ $group['meta']['legLabels'][$lIdx] ?? 'Flight '.($lIdx + 1) }}</span>
                            <small>{{ $leg['depDate'] }} &middot; {{ $leg['airlineCode'] }} {{ $leg['flightNo'] }}</small>
                          </div>
                          <div class="frx-card-times">
                            <div class="frx-card-time">
                              <div class="frx-card-time-val">{{ $leg['depTime'] }}</div>
                              <div class="frx-card-time-sub">{{ $leg['from'] }}</div>
                            </div>
                            <div class="frx-card-path">
                              <div class="frx-card-path-line"></div>
                              <div class="frx-card-stops">{{ $leg['stops'] === 0 ? 'Non-stop' : $leg['stops'].' stop(s)'.($leg['via'] ? ' via '.implode(', ', $leg['via']) : '') }}{{ $leg['duration'] ? ' · '.$fmtDuration($leg['duration']) : '' }}</div>
                            </div>
                            <div class="frx-card-time">
                              <div class="frx-card-time-val">{{ $leg['arrTime'] }}@if($leg['nextDay'])<sup class="frx-next-day">+1</sup>@endif</div>
                              <div class="frx-card-time-sub">{{ $leg['to'] }}</div>
                            </div>
                          </div>
                        </div>
                      @endforeach
                    </div>
                  @else
                    <div class="frx-card-times">
                      <div class="frx-card-time">
                        <div class="frx-card-time-sub">{{ $flight['depAirport'] ?: $group['meta']['from'] }}</div>
                        <div class="frx-card-time-val">{{ $flight['depTime'] }}</div>
                        <div class="frx-card-time-date">{{ $cardDate($flight['depDateTimeRaw']) }}</div>
                      </div>
                      <div class="frx-card-path">
                        <div class="frx-card-stops" @if($flight['via']) title="via {{ implode(', ', $flight['via']) }}" @endif>{{ $flight['stops'] === 0 ? 'Non-Stop' : $flight['stops'].' Stop(s)' }}</div>
                        <div class="frx-card-path-line"></div>
                        <div class="frx-card-duration">{{ $flight['duration'] ? $fmtDuration($flight['duration']) : '' }}@if($flight['via'])<span class="frx-card-via"> &middot; via {{ implode(', ', $flight['via']) }}</span>@endif</div>
                        @if($isNearby)<span class="frx-nearby-chip">Nearby Airport</span>@endif
                      </div>
                      <div class="frx-card-time">
                        <div class="frx-card-time-sub">{{ $flight['arrAirport'] ?: $group['meta']['to'] }}</div>
                        <div class="frx-card-time-val">{{ $flight['arrTime'] }}@if($arrivesNextDay)<sup class="frx-next-day">+1</sup>@endif</div>
                        <div class="frx-card-time-date">{{ $cardDate($flight['arrDateTimeRaw']) }}</div>
                      </div>
                    </div>
                    @if($arrivesNextDay)
                      <div class="frx-arrives-note"><span aria-hidden="true">&#9992;</span> Flight arrives after 1 day</div>
                    @endif
                  @endif
                  </div>

                  {{-- Fare list: each option on its own line (price, fare type,
                       cabin & refundability). The first 4 show; the rest sit
                       behind "+N more fares" (see frxSyncMoreFares). --}}
                  <div class="frx-col-fares frx-flight-options">
                    @foreach($flight['options'] as $oIdx => $opt)
                      @php
                        $refundLabel = ['Non-Refundable', 'Refundable', 'Partially Refundable'][$opt['refundType']];
                        $refundClass = ['non', 'ok', 'part'][$opt['refundType']];
                        $cabin = $titleCase($opt['cabinClass'] ?? $searchParams['cabinClass']);
                      @endphp
                      <label class="frx-opt-row" data-special-return="{{ $opt['isSpecialReturn'] ? '1' : '0' }}" data-fare-group="{{ $opt['fareGroup'] }}" data-refundable="{{ $opt['refundType'] }}">
                        <input type="radio" name="price_ids[{{ $key }}]" value="{{ $opt['id'] }}" required class="frx-radio" data-price="{{ (int) $opt['price'] }}" data-special-return="{{ $opt['isSpecialReturn'] ? '1' : '0' }}" @if($opt['isSpecialReturn']) data-sri="{{ $opt['sri'] }}" data-msri="{{ implode(',', $opt['msri']) }}" data-airline="{{ $flight['airlineCode'] }}" @endif>
                        {{-- Must stay a direct child of the label: the Special
                             Return script inserts its badge right before it. --}}
                        <span class="frx-opt-price">&#8377;{{ number_format($opt['price'], 2) }}</span>
                        <span class="frx-opt-meta">
                          <span class="frx-opt-fareid">{{ $fareLabel($opt['fareIdentifier']) }}</span>
                          <span class="frx-opt-desc">{{ $cabin }}, <span class="frx-refund-{{ $refundClass }}">{{ $refundLabel }}</span></span>
                          @if($opt['isFlex'])
                            {{-- Search doc: TJ_FLEX has no cancellation fee only if cancelled
                                 at least 24 hours before departure, and the flex charge
                                 itself is non-refundable — say so on the tag. --}}
                            <span class="frx-opt-flex" title="No airline cancellation fee if you cancel at least 24 hours before departure. The flex charge included in this fare is non-refundable.">Zero Cancellation Fee &middot; 24h+</span>
                          @endif
                          @if($opt['baggageCheckin'])<span class="frx-opt-bag" title="Check-in baggage">&#129523;&#xFE0E; {{ $opt['baggageCheckin'] }}</span>@endif
                        </span>
                      </label>
                    @endforeach
                    <button type="button" class="frx-more-fares" hidden></button>
                  </div>

                  <div class="frx-col-actions">
                    <button type="button" class="frx-row-continue-btn frx-book-btn" data-group="{{ $key }}">Book</button>
                    @if(count($flight['options']) > 1)
                      <button type="button" class="frx-details-toggle frx-compare-btn" data-row="{{ $rowId }}" data-group="{{ $key }}">Compare <span aria-hidden="true">&#9662;</span></button>
                    @endif
                  </div>
                </div>

                {{-- Filled in by the browser on first "View Details" (see frxFlightData). --}}
                <div class="frx-flight-details" id="{{ $rowId }}"></div>
              </div>
            @endforeach
            </div>
            {{-- Scroll loader: when this comes near the viewport the next
                 page of this list is revealed. The skeleton is what shows if
                 the guest outruns it. --}}
            <div class="frx-load-more" data-group="{{ $key }}" @if(count($group['flights']) <= $pageSize) hidden @endif aria-hidden="true">
              @for($s = 0; $s < 2; $s++)
                <div class="frx-skel">
                  <div class="frx-skel-row"><span class="w-logo"></span><span class="w-time"></span><span class="w-line"></span><span class="w-time"></span><span class="w-fare"></span><span class="w-btn"></span></div>
                  <div class="frx-skel-row"><span class="w-opt"></span><span class="w-opt"></span><span class="w-opt"></span></div>
                </div>
              @endfor
            </div>
          @endforeach

          <p class="frx-no-match" id="frxNoMatch" style="display:none;">No fares match the selected filters.</p>
          <p class="frx-no-match" id="frxSpecialReturnWarning" style="display:none;color:#f3a3a3;">⚠️ Special Return fares can only be booked as a matched pair — on the other flight, pick the fare marked “Pairs with your … fare” (shown at the top of that list), or choose regular fares on both flights instead.</p>
        </form>
      </main>
    </div>

    {{-- Search doc: priceIds are valid for 15 minutes. After that, Review
         would reject them, so the guest is asked to refresh instead. --}}
    <div class="frx-stale" id="frxStale" role="alertdialog" aria-modal="true" aria-labelledby="frxStaleTitle" hidden>
      <div class="frx-stale-box">
        <div class="frx-stale-icon" aria-hidden="true">&#8635;</div>
        <h2 id="frxStaleTitle">Prices May Have Changed</h2>
        <p>These fares were fetched over 15 minutes ago, and airlines update prices and seat availability constantly. Refresh to see the latest fares before booking.</p>
        <button type="button" class="frx-stale-refresh" id="frxStaleRefresh">Refresh Results</button>
        <button type="button" class="frx-stale-dismiss" id="frxStaleDismiss">Keep browsing</button>
      </div>
    </div>

    <div class="frx-compare-overlay" id="frxCompareOverlay">
      <div class="frx-compare-modal">
        <div class="frx-compare-head">
          <div>
            <p class="frx-compare-title">Compare Fares</p>
            <p class="frx-compare-sub" id="frxCompareSub"></p>
          </div>
          <button type="button" class="frx-compare-close" id="frxCompareClose">&times;</button>
        </div>
        <div class="frx-compare-scroll">
          <p class="frx-compare-hint" id="frxCompareHint" style="display:none;">&larr; Scroll sideways to see all fare options &rarr;</p>
          <div class="frx-compare-track-wrap">
            <div class="frx-compare-scroll-track" id="frxCompareScrollTrack">
              <div class="frx-compare-grid" id="frxCompareTable"></div>
            </div>
            <div class="frx-compare-fade" id="frxCompareFade"><span class="frx-compare-fade-arrow">&rsaquo;</span></div>
          </div>
          <ul class="frx-compare-notes">
            <li>The airline fee is indicative, which will depend upon the time of cancellation / re-issue as per the airline fare rules.</li>
            <li>Mentioned fees are Per Pax Per Sector.</li>
            <li>Apart from airline charges, GST + RAF + applicable charges if any, will be charged.</li>
            <li>For more clarity, please check the Fare Rules tab under View Details.</li>
          </ul>
        </div>
      </div>
    </div>
  @endif
</div>
@endsection

@push('scripts')
{{-- JSON_HEX_TAG turns "<" into <, so no value can close this tag. --}}
<script type="application/json" id="frxFlightData">{!! json_encode($flightData, JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) !!}</script>
<script>
  // The ?notice= message has been shown — drop it from the address bar so a
  // refresh or a shared link doesn't repeat it.
  (function () {
    try {
      var u = new URL(window.location.href);
      if (u.searchParams.has('notice')) { u.searchParams.delete('notice'); history.replaceState(null, '', u.toString()); }
    } catch (e) {}
  })();

  // "+N more fares": a card lists its first 4 visible fares (after the fare
  // filters) and tucks the rest behind this button. Re-run whenever filters
  // or the selected fare change — a selected fare is never tucked away.
  var FRX_FARES_SHOWN = 4;
  window.frxSyncMoreFares = function (row) {
    var btn = row.querySelector('.frx-more-fares');
    if (!btn) return;
    var visible = Array.prototype.filter.call(row.querySelectorAll('.frx-opt-row'), function (opt) {
      return !opt.classList.contains('frx-opt-hidden');
    });
    var tucked = 0;
    visible.forEach(function (opt, i) {
      var extra = i >= FRX_FARES_SHOWN;
      opt.classList.toggle('frx-opt-extra', extra);
      if (extra && !opt.classList.contains('selected')) tucked++;
    });
    var open = row.classList.contains('frx-fares-open');
    var extras = Math.max(0, visible.length - FRX_FARES_SHOWN);
    btn.hidden = open ? extras === 0 : tucked === 0;
    btn.innerHTML = open ? 'Show fewer fares &#9652;' : '+' + tucked + ' more fare' + (tucked === 1 ? '' : 's') + ' &#9662;';
  };
  document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.frx-flight').forEach(window.frxSyncMoreFares);
    document.addEventListener('click', function (e) {
      var btn = e.target.closest('.frx-more-fares');
      if (!btn) return;
      var row = btn.closest('.frx-flight');
      row.classList.toggle('frx-fares-open');
      window.frxSyncMoreFares(row);
    });
  });
</script>
<script>
(function () {
  var modifyBtn = document.getElementById('frxModifyToggle');
  var modifyPanel = document.getElementById('frxModifyPanel');
  if (modifyBtn) {
    var stickyBar = document.getElementById('frxSticky');
    var setModifyOpen = function (open) {
      modifyPanel.classList.toggle('open', open);
      modifyBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
      if (stickyBar) {
        // Opening from further down the page: the bar un-pins and sits back
        // at the top, so bring the page up to it.
        var wasPinned = stickyBar.getBoundingClientRect().top <= 1 && window.scrollY > 0;
        stickyBar.classList.toggle('modify-open', open);
        if (open && wasPinned) stickyBar.scrollIntoView({ block: 'start' });
      }
    };
    modifyBtn.addEventListener('click', function () { setModifyOpen(!modifyPanel.classList.contains('open')); });
    if (modifyPanel.classList.contains('open') && stickyBar) stickyBar.classList.add('modify-open');
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && modifyPanel.classList.contains('open') && !document.querySelector('.tyt-sb-autocomplete.open, .tyt-sb-calendar.open, .tyt-sb-pax-panel.open')) setModifyOpen(false);
    });
  }

  // Pinned search bar: publish its height (the filter sidebar and scroll
  // targets sit just below it) and mark when it's actually pinned.
  // Two pinned layers: the search bar at the very top, and the Sort By bar
  // directly beneath it. Each has a 1px marker just above it; once a marker
  // has scrolled past the line its bar pins to, that bar is "stuck". The
  // browser reports this off the main thread, so no work runs per scroll
  // frame. Only the lower of the two stuck bars draws a shadow.
  var sticky = document.getElementById('frxSticky');
  var sortbar = document.getElementById('frxSortbar');
  if (sticky) {
    var topStuck = false, sortStuck = false, sortObserver = null;
    var paintShadows = function () {
      sticky.classList.toggle('stuck', topStuck && !sortStuck);
      if (sortbar) sortbar.classList.toggle('stuck', sortStuck);
    };
    var watch = function (marker, offset, onChange) {
      return new IntersectionObserver(function (entries) {
        onChange(!entries[0].isIntersecting && entries[0].boundingClientRect.top < offset);
      }, { rootMargin: (-offset) + 'px 0px 0px 0px' });
    };
    var sortSentinel = document.getElementById('frxSortSentinel');
    var setStickyH = function () {
      var h = sticky.offsetHeight;
      document.documentElement.style.setProperty('--frx-sticky-h', h + 'px');
      if (sortbar) document.documentElement.style.setProperty('--frx-sort-h', sortbar.offsetHeight + 'px');
      // The Sort By bar pins at the search bar's height, so re-arm its
      // watcher whenever that height changes (e.g. Modify Search opened).
      if (window.IntersectionObserver && sortSentinel) {
        if (sortObserver) sortObserver.disconnect();
        sortObserver = watch(sortSentinel, h, function (s) { sortStuck = s; paintShadows(); });
        sortObserver.observe(sortSentinel);
      }
    };
    setStickyH();
    if (window.ResizeObserver) new ResizeObserver(setStickyH).observe(sticky);
    else window.addEventListener('resize', setStickyH);

    var sentinel = document.getElementById('frxStickySentinel');
    if (window.IntersectionObserver && sentinel) {
      watch(sentinel, 0, function (s) { topStuck = s; paintShadows(); }).observe(sentinel);
    }
  }

  var flightRows = Array.prototype.slice.call(document.querySelectorAll('.frx-flight'));
  if (!flightRows.length) return;

  // ── Filters ────────────────────────────────────────────────────────
  // Every filter is an AND across sections and an OR within a section (two
  // airlines ticked = either airline). Fare-level filters (Fare Type,
  // Cancellation Type) hide individual fares inside a card; a card with no
  // fares left is hidden, and the price filter uses its cheapest remaining
  // fare.
  var activeStops = new Set();
  var priceMin = null, priceMax = null;
  var hideNearby = false;
  var maxDuration = null, maxLayover = null;
  var flightNos = [];
  // time[kind][group] = list of [from, to) minute ranges.
  var time = { dep: {}, arr: {} };

  function checkedSet(name) {
    return new Set(Array.prototype.map.call(document.querySelectorAll('input[data-filter="' + name + '"]:checked'), function (cb) { return cb.value; }));
  }

  function inRanges(minute, ranges) {
    if (!ranges || !ranges.length) return true;
    if (isNaN(minute)) return false;
    return ranges.some(function (r) { return minute >= r[0] && minute < r[1]; });
  }

  function matchesFlightNo(row) {
    if (!flightNos.length) return true;
    var own = (row.dataset.flightNos || '').toUpperCase().split(',');
    return flightNos.some(function (q) {
      var qp = q.split('-');
      return own.some(function (fn) {
        // "6E-123" must match airline and number; a bare "123" matches any
        // airline's 123. Numbers compare as numbers, so "0123" = "123".
        var fp = fn.split('-');
        var sameNo = parseInt(fp[1], 10) === parseInt(qp[qp.length - 1], 10);
        return qp.length === 2 ? sameNo && fp[0] === qp[0] : sameNo;
      });
    });
  }

  function applyFilters() {
    var airlines = checkedSet('airline');
    var fareGroups = checkedSet('fareGroup');
    var refundable = checkedSet('refundable');
    var depTerminals = checkedSet('depTerminal'), arrTerminals = checkedSet('arrTerminal');
    var depAirports = checkedSet('depAirport'), arrAirports = checkedSet('arrAirport');
    var vias = checkedSet('via');
    var visibleCount = 0, unchecked = false;

    flightRows.forEach(function (row) {
      var d = row.dataset;
      var ok = true;
      if (activeStops.size && !activeStops.has(parseInt(d.stops, 10))) ok = false;
      if (ok && airlines.size && !airlines.has(d.airline)) ok = false;
      if (ok && hideNearby && d.nearby === '1') ok = false;
      if (ok && !inRanges(parseInt(d.depMinute, 10), time.dep[d.group])) ok = false;
      if (ok && !inRanges(parseInt(d.arrMinute, 10), time.arr[d.group])) ok = false;
      if (ok && depTerminals.size && !depTerminals.has(d.depTerminal)) ok = false;
      if (ok && arrTerminals.size && !arrTerminals.has(d.arrTerminal)) ok = false;
      if (ok && depAirports.size && !depAirports.has(d.depAirport)) ok = false;
      if (ok && arrAirports.size && !arrAirports.has(d.arrAirport)) ok = false;
      if (ok && vias.size && !(d.via || '').split(',').some(function (v) { return vias.has(v); })) ok = false;
      if (ok && maxDuration !== null && parseInt(d.duration, 10) > maxDuration) ok = false;
      if (ok && maxLayover !== null && parseInt(d.layover, 10) > maxLayover) ok = false;
      if (ok && !matchesFlightNo(row)) ok = false;

      // Fare-level filters, then the price filter on what's left.
      var cheapest = Infinity;
      row.querySelectorAll('.frx-opt-row').forEach(function (opt) {
        var show = (!fareGroups.size || fareGroups.has(opt.dataset.fareGroup))
          && (!refundable.size || refundable.has(opt.dataset.refundable));
        opt.classList.toggle('frx-opt-hidden', !show);
        var radio = opt.querySelector('.frx-radio');
        if (!show && radio.checked) { radio.checked = false; unchecked = true; }
        if (show) cheapest = Math.min(cheapest, parseInt(radio.dataset.price, 10));
      });
      window.frxSyncMoreFares(row);
      if (cheapest === Infinity) ok = false;
      if (ok && priceMin !== null && cheapest < priceMin) ok = false;
      if (ok && priceMax !== null && cheapest > priceMax) ok = false;

      // Filters only decide which flights match; renderWindow() decides
      // how many of those are actually on screen.
      row.dataset.match = ok ? '1' : '0';
      if (ok) visibleCount++;
    });

    if (unchecked && typeof updateSelection === 'function' && typeof groupKeys !== 'undefined') updateSelection();
    document.getElementById('frxNoMatch').style.display = visibleCount ? 'none' : 'block';
    resetWindow();
    syncFilterUi();
  }

  // ── Load more on scroll ──────────────────────────────────────────
  // Every flight is already on the page (one TripJack search returns them
  // all), but only the first PAGE_SIZE matches of each list are displayed;
  // the rest stay display:none, so the browser doesn't lay out or paint
  // hundreds of cards up front. Reaching the end of a list reveals the next
  // PAGE_SIZE instantly — no server round-trip — and the loader starts
  // well before the guest actually hits the bottom so there's no wait.
  var PAGE_SIZE = {{ $pageSize }};
  var limits = {};
  var loaders = Array.prototype.slice.call(document.querySelectorAll('.frx-load-more'));

  function renderWindow() {
    document.querySelectorAll('.frx-group-list').forEach(function (list) {
      var group = list.dataset.group, limit = limits[group] || PAGE_SIZE, shown = 0, remaining = 0;
      Array.prototype.forEach.call(list.children, function (row) {
        if (row.dataset.match === '0') { row.style.display = 'none'; return; }
        if (shown < limit) { row.style.display = ''; shown++; } else { row.style.display = 'none'; remaining++; }
      });
      var loader = document.querySelector('.frx-load-more[data-group="' + group + '"]');
      if (loader) loader.hidden = remaining === 0;
    });
  }

  function resetWindow() { limits = {}; renderWindow(); }

  var LOAD_AHEAD = 800; // px below the viewport at which the next page loads
  function loadMore(loader) {
    var group = loader.dataset.group;
    limits[group] = (limits[group] || PAGE_SIZE) + PAGE_SIZE;
    renderWindow();
    // On a tall screen the loader can still be in range after a page is
    // added; keep going until it's pushed out of range or runs out.
    requestAnimationFrame(function () {
      if (!loader.hidden && loader.getBoundingClientRect().top < window.innerHeight + LOAD_AHEAD) loadMore(loader);
    });
  }

  if (window.IntersectionObserver) {
    var loadObserver = new IntersectionObserver(function (entries) {
      entries.forEach(function (e) { if (e.isIntersecting && !e.target.hidden) loadMore(e.target); });
    }, { rootMargin: '0px 0px ' + LOAD_AHEAD + 'px 0px' });
    loaders.forEach(function (l) { loadObserver.observe(l); });
  } else {
    // Very old browsers: just show everything.
    PAGE_SIZE = Infinity;
  }

  // Reflect filter state back onto the controls that mirror each other
  // (Popular Filters chips, the two "Clear" links).
  function syncFilterUi() {
    document.querySelectorAll('#frxPopular .frx-chip').forEach(function (chip) {
      var p = chip.dataset.popular.split(':'), on = false;
      if (p[0] === 'stops') on = activeStops.has(parseInt(p[1], 10));
      if (p[0] === 'airline') { var cb = document.querySelector('input[data-filter="airline"][value="' + p[1] + '"]'); on = !!(cb && cb.checked); }
      if (p[0] === 'dep') {
        on = Array.prototype.every.call(document.querySelectorAll('.frx-seg-btn[data-time="dep"][data-from="' + p[1] + '"]'), function (b) { return b.classList.contains('active'); });
      }
      chip.classList.toggle('active', on);
    });
    document.getElementById('frxAirlineClear').hidden = !document.querySelector('input[data-filter="airline"]:checked');
    document.getElementById('frxFlightNoClear').hidden = !flightNos.length;
  }

  // Stops: 0 / 1 / 2 / 3+
  document.querySelectorAll('#frxStopsFilter .frx-seg-btn').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var val = parseInt(btn.dataset.stops, 10);
      if (activeStops.has(val)) activeStops.delete(val); else activeStops.add(val);
      btn.classList.toggle('active', activeStops.has(val));
      applyFilters();
    });
  });

  // Every checkbox facet (airline, fare type, cancellation, terminal,
  // airport, layover airport) just re-runs the filter.
  document.querySelectorAll('input[data-filter]').forEach(function (cb) {
    cb.addEventListener('change', applyFilters);
  });

  var hideNearbyEl = document.getElementById('frxHideNearby');
  if (hideNearbyEl) hideNearbyEl.addEventListener('change', function () { hideNearby = hideNearbyEl.checked; applyFilters(); });

  // "Show Check-in Baggage" is a display toggle: every fare on this route
  // includes some check-in allowance (confirmed live), so it reveals the
  // allowance on each fare rather than hiding anything.
  document.getElementById('frxShowCheckin').addEventListener('change', function (e) {
    document.getElementById('frReviewForm').classList.toggle('frx-show-bag', e.target.checked);
  });

  // Departure / Arrival time of day — the four buckets, or one custom
  // "Select Specific Timeframe" range instead of them.
  function rebuildTime(kind, group) {
    var panel = document.querySelector('.frx-timeframe[data-timeframe="' + kind + '"][data-group="' + group + '"]');
    var custom = panel && panel.dataset.active === '1'
      ? [[parseInt(panel.querySelector('[data-tf="from"]').value, 10), parseInt(panel.querySelector('[data-tf="to"]').value, 10)]]
      : null;
    time[kind][group] = custom || Array.prototype.filter.call(
      document.querySelectorAll('.frx-seg-btn[data-time="' + kind + '"][data-group="' + group + '"]'),
      function (b) { return b.classList.contains('active'); }
    ).map(function (b) { return [parseInt(b.dataset.from, 10), parseInt(b.dataset.to, 10)]; });
  }

  function setCustomTimeframe(panel, on) {
    panel.dataset.active = on ? '1' : '';
    panel.closest('.frx-side-body').querySelector('[data-timeframe-toggle]').classList.toggle('active', on);
  }

  document.querySelectorAll('.frx-seg-btn[data-time]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      btn.classList.toggle('active');
      // Picking a bucket replaces any custom range for that section.
      var panel = btn.closest('.frx-side-body').querySelector('.frx-timeframe');
      if (panel) setCustomTimeframe(panel, false);
      rebuildTime(btn.dataset.time, btn.dataset.group);
      applyFilters();
    });
  });

  document.querySelectorAll('[data-timeframe-toggle]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var panel = btn.nextElementSibling;
      panel.hidden = !panel.hidden;
    });
  });

  document.querySelectorAll('.frx-timeframe').forEach(function (panel) {
    var kind = panel.dataset.timeframe, group = panel.dataset.group;
    panel.querySelector('[data-tf-apply]').addEventListener('click', function () {
      var from = parseInt(panel.querySelector('[data-tf="from"]').value, 10);
      var toEl = panel.querySelector('[data-tf="to"]');
      if (parseInt(toEl.value, 10) <= from) toEl.value = Math.min(1440, from + 60);
      panel.closest('.frx-side-body').querySelectorAll('.frx-seg-btn.active').forEach(function (b) { b.classList.remove('active'); });
      setCustomTimeframe(panel, true);
      rebuildTime(kind, group);
      applyFilters();
    });
    panel.querySelector('[data-tf-clear]').addEventListener('click', function () {
      setCustomTimeframe(panel, false);
      panel.hidden = true;
      rebuildTime(kind, group);
      applyFilters();
    });
  });

  // Popular Filters are shortcuts that flip the matching control below.
  document.querySelectorAll('#frxPopular .frx-chip').forEach(function (chip) {
    chip.addEventListener('click', function () {
      var p = chip.dataset.popular.split(':');
      var turnOn = !chip.classList.contains('active');
      if (p[0] === 'stops') {
        var sb = document.querySelector('#frxStopsFilter .frx-seg-btn[data-stops="' + p[1] + '"]');
        if (activeStops.has(parseInt(p[1], 10)) !== turnOn) sb.click();
      } else if (p[0] === 'airline') {
        var cb = document.querySelector('input[data-filter="airline"][value="' + p[1] + '"]');
        if (cb && cb.checked !== turnOn) { cb.checked = turnOn; applyFilters(); }
      } else if (p[0] === 'dep') {
        // Applies to every leg's departure.
        document.querySelectorAll('.frx-seg-btn[data-time="dep"][data-from="' + p[1] + '"]').forEach(function (b) {
          if (b.classList.contains('active') !== turnOn) b.click();
        });
      }
    });
  });

  // Flight number: add one or more, e.g. "6E-123" or just "123".
  var flightNoInput = document.getElementById('frxFlightNoInput');
  var flightNoTags = document.getElementById('frxFlightNoTags');
  function renderFlightNos() {
    flightNoTags.innerHTML = '';
    flightNos.forEach(function (q) {
      var tag = document.createElement('span');
      tag.className = 'frx-tag';
      tag.textContent = q;
      var x = document.createElement('button');
      x.type = 'button';
      x.setAttribute('aria-label', 'Remove ' + q);
      x.innerHTML = '&times;';
      x.addEventListener('click', function () { flightNos = flightNos.filter(function (f) { return f !== q; }); renderFlightNos(); applyFilters(); });
      tag.appendChild(x);
      flightNoTags.appendChild(tag);
    });
  }
  function addFlightNo() {
    // "123" stays a bare number (any airline); "6E123" / "6e-123" becomes
    // "6E-123". Anything else is ignored.
    var raw = flightNoInput.value.trim().toUpperCase().replace(/\s+/g, '');
    var m = raw.match(/^([A-Z0-9]{2})-?(\d{1,5})$/);
    var q = /^\d{1,5}$/.test(raw) ? String(parseInt(raw, 10)) : (m ? m[1] + '-' + parseInt(m[2], 10) : null);
    if (q && flightNos.indexOf(q) === -1) flightNos.push(q);
    flightNoInput.value = '';
    renderFlightNos();
    applyFilters();
  }
  document.getElementById('frxFlightNoAdd').addEventListener('click', addFlightNo);
  flightNoInput.addEventListener('keydown', function (e) { if (e.key === 'Enter') { e.preventDefault(); addFlightNo(); } });
  document.getElementById('frxFlightNoClear').addEventListener('click', function () { flightNos = []; renderFlightNos(); applyFilters(); });

  // Airlines: search the list, and Clear.
  document.getElementById('frxAirlineSearch').addEventListener('input', function (e) {
    var q = e.target.value.trim().toLowerCase();
    document.querySelectorAll('#frxAirlineFilter .frx-airline-opt').forEach(function (opt) {
      opt.style.display = !q || opt.dataset.name.indexOf(q) > -1 ? '' : 'none';
    });
  });
  document.getElementById('frxAirlineClear').addEventListener('click', function () {
    document.querySelectorAll('input[data-filter="airline"]:checked').forEach(function (cb) { cb.checked = false; });
    applyFilters();
  });

  // Duration / Layover Duration: "up to" sliders.
  function fmtMinutes(m) { return Math.floor(m / 60) + 'h ' + (m % 60) + 'm'; }
  [['frxDurationMax', 'frxDurationLabel', function (v) { maxDuration = v; }], ['frxLayoverMax', 'frxLayoverLabel', function (v) { maxLayover = v; }]].forEach(function (cfg) {
    var el = document.getElementById(cfg[0]);
    if (!el) return;
    el.addEventListener('input', function () { document.getElementById(cfg[1]).textContent = fmtMinutes(parseInt(el.value, 10)); });
    el.addEventListener('change', function () {
      var v = parseInt(el.value, 10);
      cfg[2](v >= parseInt(el.max, 10) ? null : v);
      applyFilters();
    });
  });

  // Price: a two-handle slider kept in step with the Min/Max boxes. Dragging
  // a handle filters as soon as it's released; typed values wait for Apply.
  var PRICE_FLOOR = {{ $minPrice }}, PRICE_CEIL = {{ $maxPrice }};
  var rangeMin = document.getElementById('frxRangeMin');
  var rangeMax = document.getElementById('frxRangeMax');
  var rangeFill = document.getElementById('frxRangeFill');
  var priceMinEl = document.getElementById('frxPriceMin');
  var priceMaxEl = document.getElementById('frxPriceMax');

  function inr(n) { return '₹' + Math.round(n).toLocaleString('en-IN'); }

  function paintRange() {
    var lo = parseInt(rangeMin.value, 10), hi = parseInt(rangeMax.value, 10);
    var span = (PRICE_CEIL - PRICE_FLOOR) || 1;
    rangeFill.style.left = ((lo - PRICE_FLOOR) / span * 100) + '%';
    rangeFill.style.right = (100 - (hi - PRICE_FLOOR) / span * 100) + '%';
    document.getElementById('frxRangeMinLabel').textContent = inr(lo);
    document.getElementById('frxRangeMaxLabel').textContent = inr(hi);
    // Keep the lower handle reachable when both sit at the top end.
    rangeMin.style.zIndex = lo >= PRICE_CEIL - (span * 0.02) ? 3 : 2;
  }

  function applyPrice(lo, hi) {
    priceMin = lo > PRICE_FLOOR ? lo : null;
    priceMax = hi < PRICE_CEIL ? hi : null;
    applyFilters();
  }

  rangeMin.addEventListener('input', function () {
    if (parseInt(rangeMin.value, 10) > parseInt(rangeMax.value, 10)) rangeMin.value = rangeMax.value;
    priceMinEl.value = rangeMin.value;
    paintRange();
  });
  rangeMax.addEventListener('input', function () {
    if (parseInt(rangeMax.value, 10) < parseInt(rangeMin.value, 10)) rangeMax.value = rangeMin.value;
    priceMaxEl.value = rangeMax.value;
    paintRange();
  });
  [rangeMin, rangeMax].forEach(function (r) {
    r.addEventListener('change', function () { applyPrice(parseInt(rangeMin.value, 10), parseInt(rangeMax.value, 10)); });
  });

  function applyTypedPrice() {
    var lo = priceMinEl.value !== '' ? parseInt(priceMinEl.value, 10) : PRICE_FLOOR;
    var hi = priceMaxEl.value !== '' ? parseInt(priceMaxEl.value, 10) : PRICE_CEIL;
    if (lo > hi) { var t = lo; lo = hi; hi = t; }
    rangeMin.value = Math.max(PRICE_FLOOR, Math.min(PRICE_CEIL, lo));
    rangeMax.value = Math.max(PRICE_FLOOR, Math.min(PRICE_CEIL, hi));
    priceMinEl.value = lo;
    priceMaxEl.value = hi;
    paintRange();
    // Typed bounds are applied as entered, even outside the slider's range.
    priceMin = lo;
    priceMax = hi;
    applyFilters();
  }
  document.getElementById('frxPriceApply').addEventListener('click', applyTypedPrice);
  [priceMinEl, priceMaxEl].forEach(function (el) {
    el.addEventListener('keydown', function (e) { if (e.key === 'Enter') { e.preventDefault(); applyTypedPrice(); } });
  });
  paintRange();

  document.getElementById('frxResetFilters').addEventListener('click', function () {
    activeStops.clear();
    priceMin = null;
    priceMax = null;
    hideNearby = false;
    maxDuration = maxLayover = null;
    flightNos = [];
    time = { dep: {}, arr: {} };
    renderFlightNos();
    document.querySelectorAll('.frx-seg-btn.active').forEach(function (c) { c.classList.remove('active'); });
    document.querySelectorAll('input[data-filter]:checked').forEach(function (c) { c.checked = false; });
    if (hideNearbyEl) hideNearbyEl.checked = false;
    document.querySelectorAll('.frx-timeframe').forEach(function (p) { setCustomTimeframe(p, false); p.hidden = true; });
    [['frxDurationMax', 'frxDurationLabel'], ['frxLayoverMax', 'frxLayoverLabel']].forEach(function (cfg) {
      var el = document.getElementById(cfg[0]);
      if (el) { el.value = el.max; document.getElementById(cfg[1]).textContent = fmtMinutes(parseInt(el.max, 10)); }
    });
    priceMinEl.value = rangeMin.value = PRICE_FLOOR;
    priceMaxEl.value = rangeMax.value = PRICE_CEIL;
    paintRange();
    applyFilters();
  });

  // Collapsible filter blocks (the "−" beside each filter title) and the
  // whole filter sidebar (the « button beside the date strip).
  document.querySelectorAll('.frx-block-toggle').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var block = btn.closest('.frx-side-block');
      var collapsed = block.classList.toggle('collapsed');
      btn.innerHTML = collapsed ? '+' : '&minus;';
      btn.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
    });
  });

  var sideToggle = document.getElementById('frxSideToggle');
  var frxBody = document.getElementById('frxBody');
  function setSidebar(open) {
    frxBody.classList.toggle('side-collapsed', !open);
    sideToggle.innerHTML = open ? '&laquo;' : '&raquo;';
    sideToggle.title = open ? 'Hide filters' : 'Show filters';
    sideToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
  }
  sideToggle.addEventListener('click', function () { setSidebar(frxBody.classList.contains('side-collapsed')); });
  // On a phone the filters would fill the first screen, so start collapsed.
  if (window.matchMedia('(max-width: 900px)').matches) setSidebar(false);

  // Sorting — reorders the rows inside each leg's list, so Onward and
  // Return flights stay under their own headings. Clicking the active key
  // again flips the direction; the Cheapest / Fastest cards are shortcuts
  // for Price ↑ and Duration ↑.
  var sortState = { key: 'price', dir: 'asc' };
  var sortBtns = document.querySelectorAll('.frx-sort-btn');
  var pickBtns = document.querySelectorAll('.frx-pick');

  function sortValue(row, key) {
    if (key === 'price' || key === 'duration') {
      var n = parseInt(row.dataset[key], 10);
      return isNaN(n) || n === 0 ? Infinity : n;
    }
    return row.dataset[key] || '￿';
  }

  function applySort(key, dir) {
    sortState = { key: key, dir: dir };
    var sign = dir === 'desc' ? -1 : 1;
    document.querySelectorAll('.frx-group-list').forEach(function (list) {
      var rows = Array.prototype.slice.call(list.children);
      rows.sort(function (a, b) {
        var va = sortValue(a, key), vb = sortValue(b, key);
        if (va < vb) return -sign;
        if (va > vb) return sign;
        // Ties: cheaper first, then the original (price) order.
        var pa = parseInt(a.dataset.price, 10), pb = parseInt(b.dataset.price, 10);
        if (pa !== pb) return pa - pb;
        return parseInt(a.dataset.order, 10) - parseInt(b.dataset.order, 10);
      });
      rows.forEach(function (r) { list.appendChild(r); });
    });
    // A new order starts again from the top of the list.
    resetWindow();

    sortBtns.forEach(function (b) {
      var on = b.dataset.sort === key;
      b.classList.toggle('active', on);
      b.classList.toggle('desc', on && dir === 'desc');
      b.setAttribute('aria-pressed', on ? 'true' : 'false');
    });
    pickBtns.forEach(function (p) {
      p.classList.toggle('active', p.dataset.sort === key && p.dataset.dir === dir);
    });
  }

  sortBtns.forEach(function (b) {
    b.addEventListener('click', function () {
      var dir = sortState.key === b.dataset.sort && sortState.dir === 'asc' ? 'desc' : 'asc';
      applySort(b.dataset.sort, dir);
    });
  });
  pickBtns.forEach(function (p) {
    p.addEventListener('click', function () { applySort(p.dataset.sort, p.dataset.dir); });
  });
  applySort('price', 'asc');
  applyFilters();

  // Share this search — the results URL already carries every search
  // parameter, so the link reopens the same search for whoever receives it.
  (function () {
    var url = window.location.href;
    var titleEl = document.querySelector('.frx-route-city');
    var text = 'Flight options on TYT Luxe' + (titleEl ? ' — ' + Array.prototype.map.call(document.querySelectorAll('.frx-route .frx-route-city'), function (e) { return e.textContent.trim(); }).join(' to ') : '');
    document.getElementById('frxShareWa').href = 'https://wa.me/?text=' + encodeURIComponent(text + '\n' + url);
    document.getElementById('frxShareMail').href = 'mailto:?subject=' + encodeURIComponent(text) + '&body=' + encodeURIComponent(text + '\n\n' + url);
    var copied = document.getElementById('frxShareCopied');
    document.getElementById('frxShareCopy').addEventListener('click', function () {
      function done() { copied.hidden = false; setTimeout(function () { copied.hidden = true; }, 1800); }
      if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(url).then(done, function () { window.prompt('Copy this link:', url); });
      } else {
        window.prompt('Copy this link:', url);
      }
    });
  })();

  // Date strip — seven days at a time. Clicking a day runs the same search
  // for that date; "Fetch Fare" looks up that day's cheapest fare in place
  // (one TripJack search per click, cached server-side for 15 minutes).
  (function () {
    var strip = document.getElementById('frxDates');
    if (!strip) return;
    var track = document.getElementById('frxDatesTrack');
    var prevBtn = document.getElementById('frxDatesPrev');
    var nextBtn = document.getElementById('frxDatesNext');
    var baseQuery = @json($stripQuery);
    var fareUrl = @json(route('flights.fare-calendar.ajax'));
    var searchUrl = @json(route('flights.search'));
    var current = strip.dataset.current;
    var returnDate = strip.dataset.return;
    var today = strip.dataset.today;
    var fares = {};
    fares[current] = parseInt(strip.dataset.currentFare, 10) || null;
    var DAYS = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
    var MONTHS = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

    function parse(iso) { var p = iso.split('-'); return new Date(Date.UTC(+p[0], +p[1] - 1, +p[2])); }
    function iso(d) { return d.toISOString().slice(0, 10); }
    function addDays(isoDate, n) { var d = parse(isoDate); d.setUTCDate(d.getUTCDate() + n); return iso(d); }
    function label(isoDate) { var d = parse(isoDate); return DAYS[d.getUTCDay()] + ', ' + MONTHS[d.getUTCMonth()] + ' ' + d.getUTCDate(); }
    function query(extra) {
      var params = new URLSearchParams();
      Object.keys(baseQuery).forEach(function (k) {
        var v = baseQuery[k];
        if (v !== null && typeof v === 'object') return;
        params.set(k, v);
      });
      Object.keys(extra).forEach(function (k) { params.set(k, extra[k]); });
      if (!returnDate) params.delete('return_date');
      return params.toString();
    }
    // A day is unavailable before today, or after the return date on a
    // return trip (the outbound can't leave after the guest comes back).
    function unavailable(isoDate) { return isoDate < today || (returnDate && isoDate > returnDate); }

    // Start the window three days before the searched date where possible,
    // so the guest sees both earlier and later options.
    var windowStart = addDays(current, -3);
    if (windowStart < today) windowStart = today;

    function cheapestShown() {
      var vals = Object.keys(fares).map(function (k) { return fares[k]; }).filter(function (v) { return v; });
      return vals.length > 1 ? Math.min.apply(null, vals) : null;
    }

    function render() {
      var lowest = cheapestShown();
      track.innerHTML = '';
      for (var i = 0; i < 7; i++) {
        var day = addDays(windowStart, i);
        var cell = document.createElement('a');
        cell.className = 'frx-date' + (day === current ? ' active' : '') + (unavailable(day) ? ' disabled' : '');
        cell.href = day === current ? '#' : searchUrl + '?' + query({ depart_date: day });
        if (day === current) cell.setAttribute('aria-current', 'date');
        if (fares[day] && fares[day] === lowest) cell.classList.add('cheapest-day');

        var dayEl = document.createElement('span');
        dayEl.className = 'frx-date-day';
        dayEl.textContent = label(day);
        cell.appendChild(dayEl);

        var fareEl = document.createElement('button');
        fareEl.type = 'button';
        fareEl.dataset.day = day;
        if (fares[day]) {
          fareEl.className = 'frx-date-fare priced';
          fareEl.textContent = inr(fares[day]);
        } else if (fares[day] === 0) {
          fareEl.className = 'frx-date-fare muted';
          fareEl.textContent = 'No flights';
        } else if (unavailable(day)) {
          fareEl.className = 'frx-date-fare muted';
          fareEl.textContent = '—';
        } else {
          fareEl.className = 'frx-date-fare fetch';
          fareEl.textContent = 'Fetch Fare';
        }
        cell.appendChild(fareEl);
        track.appendChild(cell);
      }
      prevBtn.disabled = windowStart <= today;
      // On a phone only ~3 days fit; keep the searched day in view.
      var active = track.querySelector('.frx-date.active');
      if (active && track.scrollWidth > track.clientWidth) {
        track.scrollLeft = active.offsetLeft - (track.clientWidth - active.offsetWidth) / 2;
      }
    }

    track.addEventListener('click', function (e) {
      var fareBtn = e.target.closest('.frx-date-fare');
      var cell = e.target.closest('.frx-date');
      if (cell && cell.classList.contains('active')) { e.preventDefault(); return; }
      if (!fareBtn || !fareBtn.classList.contains('fetch')) return;
      // "Fetch Fare" prices the day without leaving the page.
      e.preventDefault();
      var day = fareBtn.dataset.day;
      fareBtn.classList.remove('fetch');
      fareBtn.classList.add('muted');
      fareBtn.textContent = 'Fetching…';
      fetch(fareUrl + '?' + query({ depart_date: day }), { headers: { 'Accept': 'application/json' } })
        .then(function (r) { return r.ok ? r.json() : { success: false }; })
        .then(function (data) {
          if (data.success) fares[day] = data.minPrice;
          else if (data.message === 'No flights') fares[day] = 0;
          render();
          if (!data.success && data.message !== 'No flights') {
            var again = track.querySelector('.frx-date-fare[data-day="' + day + '"]');
            if (again) again.textContent = 'Retry';
          }
        })
        .catch(function () {
          var again = track.querySelector('.frx-date-fare[data-day="' + day + '"]');
          if (again) { again.className = 'frx-date-fare fetch'; again.textContent = 'Retry'; }
        });
    });

    prevBtn.addEventListener('click', function () {
      windowStart = addDays(windowStart, -7);
      if (windowStart < today) windowStart = today;
      render();
    });
    nextBtn.addEventListener('click', function () {
      windowStart = addDays(windowStart, 7);
      render();
    });

    render();
  })();

  // Selection state — the per-row "Select & Continue" buttons submit the
  // form once every leg has a fare picked.
  var allRadios = Array.prototype.slice.call(document.querySelectorAll('.frx-radio'));
  var groupKeys = Array.from(new Set(allRadios.map(function (r) { return r.name; })));
  var frReviewForm = document.getElementById('frReviewForm');

  // ── Fare freshness (Search doc: priceIds valid for 15 minutes) ──────
  // After that, Review rejects the priceId — which a guest would otherwise
  // only discover as a misleading "sold out" error. Timed from when this
  // page's fares arrived; re-checked on a timer, whenever the tab becomes
  // visible again (background tabs pause timers), and on every attempt to
  // continue to Review.
  var PRICE_TTL_MS = 15 * 60 * 1000;
  window.frxPriceClock = { fetchedAt: Date.now() };
  var staleModal = document.getElementById('frxStale');
  var staleDismissed = false;
  function pricesStale() { return Date.now() - window.frxPriceClock.fetchedAt >= PRICE_TTL_MS; }
  function showStale() { if (staleModal) staleModal.hidden = false; }
  function checkStale() { if (!staleDismissed && pricesStale()) showStale(); }
  if (staleModal) {
    setInterval(checkStale, 30 * 1000);
    document.addEventListener('visibilitychange', function () { if (!document.hidden) checkStale(); });
    window.addEventListener('pageshow', checkStale);
    document.getElementById('frxStaleRefresh').addEventListener('click', function () { window.location.reload(); });
    document.getElementById('frxStaleDismiss').addEventListener('click', function () { staleDismissed = true; staleModal.hidden = true; });
    // Browsing on is fine; continuing to Review with an expired price isn't.
    frReviewForm.addEventListener('submit', function (e) {
      if (pricesStale()) { e.preventDefault(); showStale(); }
    });
  }
  var canSubmit = false;

  var specialReturnWarning = document.getElementById('frxSpecialReturnWarning');

  function updateSelection() {
    var specialFlags = [];
    var allSelected = groupKeys.every(function (name) {
      var checked = document.querySelector('input[name="' + name + '"]:checked');
      if (checked) specialFlags.push(checked.dataset.specialReturn === '1');
      return !!checked;
    });

    allRadios.forEach(function (r) {
      r.closest('.frx-opt-row').classList.toggle('selected', r.checked);
    });
    flightRows.forEach(function (row) {
      row.classList.toggle('has-selected', !!row.querySelector('.frx-radio:checked'));
      window.frxSyncMoreFares(row);
    });

    // Doc: "When fareIdentifier is SPECIAL_RETURN, both legs must be
    // SPECIAL_RETURN" — a mismatch (one leg Special Return, the other not)
    // is blocked here rather than left for TripJack's Review call to reject,
    // so the guest gets an explanation instead of a generic booking error.
    var mismatch = allSelected && specialFlags.length > 1 && specialFlags.some(function (f) { return f; }) && !specialFlags.every(function (f) { return f; });
    // Both legs Special Return but not a valid pair (normally prevented by
    // applySpecialReturnPairing below — this is the safety net).
    if (!mismatch && allSelected && groupKeys.length === 2 && specialFlags.every(function (f) { return f; })) {
      var picks = groupKeys.map(function (name) { return document.querySelector('input[name="' + name + '"]:checked'); });
      mismatch = !srPairs(picks[0], picks[1]);
    }
    specialReturnWarning.style.display = mismatch ? '' : 'none';

    canSubmit = allSelected && !mismatch;
  }

  // ── Special Return pairing (domestic return, two legs) ─────────────
  // Confirmed live (BOM⇄DEL): Air India / H1 Special Return fares name
  // their one partner fare on the other leg — sri on one side appears in
  // the other's msri — and TripJack's Review rejects any other pair
  // ("All Segments Must be selected if Special Return fare"). IndiGo's
  // carry no ids and pair with any same-airline Special Return fare.
  function srIds(r) { return { sri: r.dataset.sri || '', msri: (r.dataset.msri || '').split(',').filter(Boolean) }; }
  function srPairs(a, b) {
    if (!a || !b) return false;
    var x = srIds(a), y = srIds(b);
    if (x.sri || y.sri) {
      return (!!x.sri && y.msri.indexOf(x.sri) > -1) || (!!y.sri && x.msri.indexOf(y.sri) > -1);
    }
    return !!a.dataset.airline && a.dataset.airline === b.dataset.airline;
  }

  var srLegs = groupKeys.length === 2 ? groupKeys : [];
  var srLegLabel = {};
  srLegs.forEach(function (name) {
    var key = name.replace(/^price_ids\[/, '').replace(/\]$/, '');
    srLegLabel[name] = key === 'RETURN' ? 'return' : 'onward';
  });

  // Restrict leg `name`'s Special Return fares to partners of `anchor` (a
  // checked Special Return radio on the other leg), or lift the restriction.
  function restrictLeg(name, anchor) {
    var partners = [];
    document.querySelectorAll('input.frx-radio[name="' + name + '"][data-special-return="1"]').forEach(function (r) {
      var row = r.closest('.frx-opt-row');
      var ok = !anchor || srPairs(anchor, r);
      var badge = row.querySelector('.frx-sr-badge');
      if (!badge) {
        badge = document.createElement('span');
        badge.className = 'frx-sr-badge';
        row.insertBefore(badge, row.querySelector('.frx-opt-price'));
      }
      // Dimmed, not disabled: the guest can still pick it, which then
      // clears the other leg's (no longer matching) pick instead.
      row.classList.toggle('frx-sr-blocked', !ok);
      row.classList.toggle('frx-sr-partner', !!anchor && ok);
      row.title = ok ? '' : 'Doesn’t pair with the Special Return fare selected on your ' + srLegLabel[anchor.name] + ' flight';
      badge.textContent = anchor && ok ? 'Pairs with your ' + srLegLabel[anchor.name] + ' fare' : '';
      if (!ok && r.checked) r.checked = false;
      if (anchor && ok) partners.push(r.closest('.frx-flight'));
    });
    // Bring the partner flight(s) to the top of that leg's list, so the
    // guest doesn't have to hunt through hundreds of flights for them.
    if (anchor && partners.length) {
      var list = partners[0].parentElement;
      partners.slice().reverse().forEach(function (row) { if (row.parentElement === list) list.insertBefore(row, list.firstChild); });
      if (typeof renderWindow === 'function') renderWindow();
    }
  }

  function applySpecialReturnPairing(changed) {
    if (srLegs.length !== 2 || !changed || srLegs.indexOf(changed.name) === -1) return;
    var other = srLegs[0] === changed.name ? srLegs[1] : srLegs[0];
    var anchor = changed.checked && changed.dataset.specialReturn === '1' ? changed : null;
    // The latest pick leads: the other leg is narrowed to its partners (an
    // existing pick there that doesn't pair is cleared), and the leg just
    // changed is left free so the guest can always change their mind.
    restrictLeg(other, anchor);
    restrictLeg(changed.name, null);
  }

  allRadios.forEach(function (r) {
    r.addEventListener('change', function () {
      applySpecialReturnPairing(r);
      updateSelection();
    });
  });
  updateSelection();

  // Per-row "Select & Continue" — books the flight the guest is already
  // looking at instead of forcing a scroll to the shared bar at the very
  // bottom of a long results list. Oneway searches (the common case, one
  // group) submit immediately on click; Return/Multi-City searches (2+
  // groups) select this leg then smooth-scroll straight to the next leg
  // still needing a pick, so the guest never has to hunt for it manually.
  document.querySelectorAll('.frx-row-continue-btn').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var flightRow = btn.closest('.frx-flight');
      var rowRadios = Array.prototype.slice.call(flightRow.querySelectorAll('.frx-radio'));
      var checked = rowRadios.find(function (r) { return r.checked; });
      if (!checked) {
        checked = rowRadios[0];
        checked.checked = true;
        checked.dispatchEvent(new Event('change'));
      }

      var nextGroup = groupKeys.find(function (name) {
        return name !== btn.dataset.group && !document.querySelector('input[name="' + name + '"]:checked');
      });

      if (!nextGroup) {
        if (canSubmit) {
          frReviewForm.requestSubmit();
          return;
        }
        // An unmatched Special Return pair: take the guest to the other
        // flight's list, where the matching fare now sits at the top.
        nextGroup = groupKeys.find(function (name) { return name !== btn.dataset.group; });
        if (!nextGroup) {
          specialReturnWarning.scrollIntoView({ behavior: 'smooth', block: 'center' });
          return;
        }
      }

      var key = nextGroup.replace(/^price_ids\[/, '').replace(/\]$/, '');
      var target = document.getElementById('frxGroupTitle_' + key);
      if (target) {
        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        target.classList.remove('frx-group-title-nudge');
        void target.offsetWidth;
        target.classList.add('frx-group-title-nudge');
      }
    });
  });

  // Expandable "View Details" panel (Flight Details / Fare Details / Fare
  // Rules tabs) — Fare Rules is lazy-loaded per row on first open, not
  // pre-fetched for every result (see FlightController::fareRuleAjax()).
  var flightData = {};
  try { flightData = JSON.parse(document.getElementById('frxFlightData').textContent) || {}; } catch (e) {}

  function esc(s) {
    return String(s === null || s === undefined ? '' : s).replace(/[&<>"']/g, function (c) {
      return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
    });
  }

  // Builds a flight's "View Details" panel (Flight Details / Fare Details /
  // Fare Rules tabs) from frxFlightData the first time it's opened.
  function buildDetails(details) {
    var d = flightData[details.id];
    if (!d) return;
    function point(p, right) {
      return '<div class="frx-fd-point' + (right ? ' frx-fd-point-right' : '') + '">'
        + '<div class="frx-fd-time">' + esc(p[0]) + '</div>'
        + '<div class="frx-fd-city">' + esc(p[1]) + '</div>'
        + '<div class="frx-fd-airport">' + esc(p[2]) + '</div>'
        + (p[3] ? '<div class="frx-fd-terminal">' + esc(p[3]) + '</div>' : '')
        + '</div>';
    }
    details.innerHTML =
      '<div class="frx-tabs">'
      + '<button type="button" class="frx-tab active" data-tab="flight">Flight Details</button>'
      + '<button type="button" class="frx-tab" data-tab="fare">Fare Details</button>'
      + '<button type="button" class="frx-tab" data-tab="rules" data-price-id="' + esc(d.rulesId) + '">Fare Rules</button>'
      + '</div>'
      + '<div class="frx-tab-panel active" data-panel="flight">'
      // legs[i] = [title, depPoint, arrPoint, stops, duration]
      + d.legs.map(function (l, i) {
        return '<div class="frx-fd-title"' + (i ? ' style="margin-top:18px;"' : '') + '>' + esc(l[0]) + '</div>'
          + (i === 0 ? '<div class="frx-fd-sub">' + esc(d.sub) + '</div>' : '')
          + '<div class="frx-fd-segment">' + point(l[1])
          + '<div class="frx-fd-mid"><div class="frx-card-stops">' + esc(l[3]) + '</div><div class="frx-card-path-line"></div><div class="frx-fd-duration">' + esc(l[4]) + '</div></div>'
          + point(l[2], true) + '</div>';
      }).join('')
      + '<p class="frx-fd-baggage-title">Baggage Information</p>'
      + '<table class="frx-fare-table"><thead><tr><th>Pax Type</th><th>Check In</th><th>Cabin</th></tr></thead><tbody>'
      + d.bags.map(function (b) { return '<tr><td>' + esc(b[0]) + '</td><td>' + esc(b[1]) + '</td><td>' + esc(b[2]) + '</td></tr>'; }).join('')
      + '</tbody></table></div>'
      + '<div class="frx-tab-panel" data-panel="fare">'
      + '<table class="frx-fare-table"><thead><tr><th>Fare Type</th><th>Base Fare</th><th>Taxes &amp; Fees</th><th>Total</th></tr></thead><tbody>'
      + d.fares.map(function (f) { return '<tr class="total-row"><td>' + esc(f[0]) + '</td><td>&#8377;' + esc(f[1]) + '</td><td>&#8377;' + esc(f[2]) + '</td><td>&#8377;' + esc(f[3]) + '</td></tr>'; }).join('')
      + '</tbody></table>'
      + '<p class="frx-detail-line" style="border:none;margin-top:8px;"><span></span><span style="color:var(--white-30);font-size:11px;">Per adult. GST/RAF and any applicable charges are included in Total.</span></p>'
      + '</div>'
      + '<div class="frx-tab-panel" data-panel="rules"><div class="frx-rules-content"><p class="frx-rules-loading">Click to load cancellation &amp; date-change fees…</p></div></div>';
    bindDetailTabs(details);
  }

  // [data-target] only — the Compare button shares this class but isn't a
  // details toggle.
  document.querySelectorAll('.frx-details-toggle[data-target]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var panel = document.getElementById(btn.dataset.target);
      if (!panel.firstChild) buildDetails(panel);
      var open = panel.classList.toggle('open');
      btn.classList.toggle('open', open);
      btn.firstChild.textContent = open ? 'Hide Details ' : 'View Details ';
    });
  });

  function bindDetailTabs(details) {
    var tabs = details.querySelectorAll('.frx-tab');
    var panels = details.querySelectorAll('.frx-tab-panel');

    tabs.forEach(function (tab) {
      tab.addEventListener('click', function () {
        tabs.forEach(function (t) { t.classList.remove('active'); });
        panels.forEach(function (p) { p.classList.remove('active'); });
        tab.classList.add('active');
        details.querySelector('.frx-tab-panel[data-panel="' + tab.dataset.tab + '"]').classList.add('active');

        if (tab.dataset.tab === 'rules' && !tab.dataset.loaded) {
          tab.dataset.loaded = '1';
          var contentEl = details.querySelector('.frx-tab-panel[data-panel="rules"] .frx-rules-content');
          fetch('{{ route('flights.fare-rule.ajax') }}?price_id=' + encodeURIComponent(tab.dataset.priceId))
            .then(function (r) { return r.json(); })
            .then(function (data) {
              if (!data.success || !data.fareRule) {
                contentEl.innerHTML = '<p class="frx-rules-empty">' + (data.message || 'No fare rules available for this fare.') + '</p>';
                return;
              }

              // One column per policy type (fixed order matching TripJack's
              // own reference layout), one row per unique time band found
              // across those policies — a policy with no band covering a
              // given row shows "—" rather than a blank cell.
              var POLICY_COLUMNS = [
                { key: 'CANCELLATION', label: 'Cancellation Fee' },
                { key: 'DATECHANGE', label: 'Date Change Fee' },
                { key: 'NO_SHOW', label: 'No Show Fee (Post Departure)' },
                { key: 'SEAT_CHARGEABLE', label: 'Seat Chargeable Fee' },
              ];

              function fmtBound(h) {
                h = parseInt(h, 10);
                if (isNaN(h)) return '';
                return h < 24 ? (h + ' hrs') : (Math.round(h / 24) + ' days');
              }

              // TripJack's raw policyInfo text uses a literal "__nls__" token
              // as a line-break placeholder — never render it as-is.
              function cleanPolicyInfo(text) {
                return (text || '').replace(/__nls__/g, ' ').replace(/\s+/g, ' ').trim();
              }

              function cellContent(band) {
                if (!band) return '<span style="color:var(--white-30)">—</span>';
                if (band.amount !== undefined) {
                  var amt = Math.round((band.amount || 0) + (band.additionalFee || 0));
                  var html = 'INR ' + amt.toLocaleString('en-IN');
                  var info = cleanPolicyInfo(band.policyInfo);
                  if (info) html += '<br><span style="color:var(--white-30);font-size:10.5px;">' + info + '</span>';
                  return html;
                }
                return cleanPolicyInfo(band.policyInfo) || '<span style="color:var(--white-30)">—</span>';
              }

              var PP_LABELS = { BEFORE_DEPARTURE: 'Before departure', AFTER_DEPARTURE: 'After departure', DEFAULT: 'Any time' };
              var PP_ORDER = { BEFORE_DEPARTURE: 0, AFTER_DEPARTURE: 1, DEFAULT: 2 };
              function bandKey(b) {
                if (!b) return null;
                if (b.st !== undefined && b.et !== undefined) return 't:' + b.st + '-' + b.et;
                if (b.pp) return 'p:' + b.pp;
                return null;
              }
              function bandRank(b) {
                return b.st !== undefined ? parseInt(b.st, 10) : 100000 + (PP_ORDER[b.pp] !== undefined ? PP_ORDER[b.pp] : 9);
              }
              function bandLabel(b) {
                return b.st !== undefined ? fmtBound(b.st) + ' to ' + fmtBound(b.et) : (PP_LABELS[b.pp] || String(b.pp).replace(/_/g, ' ').toLowerCase());
              }
              function escHtml(s) { return String(s).replace(/[&<>"']/g, function (ch) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[ch]; }); }
              function miscText(m) {
                if (!m) return '';
                var parts = typeof m === 'string' ? [m] : (Array.isArray(m) ? m : Object.keys(m).map(function (k) { return m[k]; }));
                return parts.filter(function (p) { return typeof p === 'string' && p.trim(); })
                  .map(function (p) { return '<p>' + escHtml(cleanPolicyInfo(p)) + '</p>'; }).join('');
              }

              var html = '';
              Object.keys(data.fareRule).forEach(function (segKey) {
                html += '<div class="frx-rules-segment">' + segKey + '</div>';
                html += '<p class="frx-rules-hint">* Charges shown per time frame from first scheduled flight departure.</p>';

                var rule = data.fareRule[segKey] || {};
                var tfr = rule.tfr || {};
                var presentCols = POLICY_COLUMNS.filter(function (c) { return tfr[c.key] && tfr[c.key].length; });
                if (!presentCols.length) {
                  // Doc: with no mini-rule from the airline, a plain-text
                  // ("Cat 16") rule comes back under miscInfo instead.
                  var misc = miscText(rule.miscInfo);
                  html += misc
                    ? '<div class="frx-rules-misc">' + misc + '</div>'
                    : '<p class="frx-rules-empty">No time-banded policy available for this segment.</p>';
                  return;
                }

                // A band is either time-based (st/et, hours before
                // departure) or a policy period (pp) — never both, per the doc.
                var bandKeys = [];
                var bandMap = {};
                presentCols.forEach(function (c) {
                  tfr[c.key].forEach(function (b) {
                    var k = bandKey(b);
                    if (k && !bandMap[k]) { bandMap[k] = b; bandKeys.push(k); }
                  });
                });
                bandKeys.sort(function (a, b) { return bandRank(bandMap[a]) - bandRank(bandMap[b]); });

                html += '<table class="frx-rules-table"><thead><tr><th>Time Frame</th>';
                presentCols.forEach(function (c) { html += '<th>' + c.label + '</th>'; });
                html += '</tr></thead><tbody>';
                bandKeys.forEach(function (k) {
                  html += '<tr><td>' + bandLabel(bandMap[k]) + '</td>';
                  presentCols.forEach(function (c) {
                    var match = (tfr[c.key] || []).filter(function (x) { return bandKey(x) === k; })[0];
                    html += '<td>' + cellContent(match) + '</td>';
                  });
                  html += '</tr>';
                });
                html += '</tbody></table>';
              });

              html += '<div class="frx-rules-notes">'
                + '<p>The airline fee is indicative, which will depend upon the time of cancellation / re-issue as per the airline fare rules.</p>'
                + '<p>Mentioned fees are Per Pax Per Sector.</p>'
                + '<p>Apart from airline charges, GST + RAF + applicable charges if any, will be charged.</p>'
                + '</div>';

              contentEl.innerHTML = html || '<p class="frx-rules-empty">No fare rules available.</p>';
            })
            .catch(function () {
              contentEl.innerHTML = '<p class="frx-rules-empty">Couldn\'t load fare rules right now.</p>';
            });
        }
      });
    });
  }

  // Compare Fares modal — built entirely from data already on the page
  // (data-compare JSON on the button) except Cancellation/Date Change fees,
  // which are lazily fetched per option (same on-demand Fare Rule endpoint
  // as the "Fare Rules" tab) only when a guest actually opens Compare for
  // that specific flight, not pre-fetched for every option of every flight.
  var compareOverlay = document.getElementById('frxCompareOverlay');
  var compareTable = document.getElementById('frxCompareTable');
  var compareScrollTrack = document.getElementById('frxCompareScrollTrack');
  var compareFade = document.getElementById('frxCompareFade');
  var compareHint = document.getElementById('frxCompareHint');

  // Surfaces that there ARE more fare columns off-screen (an animated arrow
  // + hint text), since the browser's native scrollbar alone is too easy to
  // miss — hides itself once the guest has scrolled to the last column.
  function updateCompareScrollHint() {
    var hasOverflow = compareScrollTrack.scrollWidth > compareScrollTrack.clientWidth + 4;
    compareHint.style.display = hasOverflow ? '' : 'none';
    var atEnd = compareScrollTrack.scrollLeft + compareScrollTrack.clientWidth >= compareScrollTrack.scrollWidth - 4;
    compareFade.classList.toggle('visible', hasOverflow && !atEnd);
  }
  compareScrollTrack.addEventListener('scroll', updateCompareScrollHint);
  window.addEventListener('resize', updateCompareScrollHint);

  function closeCompare() { compareOverlay.classList.remove('open'); }
  document.getElementById('frxCompareClose').addEventListener('click', closeCompare);
  compareOverlay.addEventListener('click', function (e) { if (e.target === compareOverlay) closeCompare(); });

  // Cell content is built as a flat left-to-right, top-to-bottom sequence —
  // CSS Grid auto-places it into a matrix from `grid-template-columns`, so
  // there's no table/colspan bookkeeping to get out of sync, and no column
  // can blow out in width just because one cell's text is long (each cell
  // wraps/clamps independently instead of forcing nowrap on the whole row).
  function cmpLabelCell(label, sub) {
    return '<div class="frx-cmp-cell frx-cmp-label">' + label + (sub ? '<small>' + sub + '</small>' : '') + '</div>';
  }

  document.querySelectorAll('.frx-compare-btn').forEach(function (btn) {
    btn.addEventListener('click', function () {
      // Rebuilt from the same frxFlightData block as the details panel:
      // fares[i] = [label, base, taxes, total, id, fareIdentifier, price, refundable, checkin, cabin].
      var options = ((flightData[btn.dataset.row] || {}).fares || []).map(function (f) {
        return { id: f[4], fareIdentifier: f[5], price: f[6], refundable: f[7], baggageCheckin: f[8], baggageCabin: f[9] };
      });
      if (!options.length) return;
      var groupName = btn.dataset.group;
      var cheapest = Math.min.apply(null, options.map(function (o) { return o.price; }));
      var n = options.length;

      document.getElementById('frxCompareSub').textContent = n + ' fare option' + (n === 1 ? '' : 's') + ' available for this flight';
      // Fill the modal when there are few fares, but never let a column grow
      // past its share just because it holds a long policy sentence; with
      // many fares, the min-width forces a sideways scroll instead of cramming.
      compareTable.style.gridTemplateColumns = '150px repeat(' + n + ', minmax(0, 1fr))';
      compareTable.style.minWidth = (150 + n * 190) + 'px';

      var cells = [];
      cells.push(cmpLabelCell('Fares'));
      options.forEach(function (opt) {
        var isBest = opt.price === cheapest;
        cells.push(
          '<div class="frx-cmp-cell frx-cmp-head' + (isBest ? ' best' : '') + '">'
          + '<div class="frx-cmp-fareid">' + opt.fareIdentifier.replace(/_/g, ' ') + '</div>'
          + (isBest ? '<span class="frx-cmp-badge">Cheapest</span>' : '')
          + '</div>'
        );
      });

      cells.push(cmpLabelCell('Price'));
      options.forEach(function (opt) {
        cells.push('<div class="frx-cmp-cell frx-cmp-value"><span class="frx-cmp-price">&#8377;' + Math.round(opt.price).toLocaleString('en-IN') + '</span></div>');
      });

      cells.push(cmpLabelCell('Baggage', 'Check-in / Cabin'));
      options.forEach(function (opt) {
        cells.push('<div class="frx-cmp-cell frx-cmp-value">' + (opt.baggageCheckin || '—') + ' / ' + (opt.baggageCabin || '—') + '</div>');
      });

      cells.push(cmpLabelCell('Cancellation Fee'));
      options.forEach(function (opt, i) {
        cells.push('<div class="frx-cmp-cell frx-cmp-value" id="frxCmpCancel_' + i + '"><span class="frx-compare-loading">Loading…</span></div>');
      });

      cells.push(cmpLabelCell('Date Change Fee'));
      options.forEach(function (opt, i) {
        cells.push('<div class="frx-cmp-cell frx-cmp-value" id="frxCmpDate_' + i + '"><span class="frx-compare-loading">Loading…</span></div>');
      });

      cells.push(cmpLabelCell('Seat Charge'));
      options.forEach(function () {
        cells.push('<div class="frx-cmp-cell frx-cmp-value">Chargeable</div>');
      });

      cells.push(cmpLabelCell('Meals'));
      options.forEach(function () {
        cells.push('<div class="frx-cmp-cell frx-cmp-value">Available at booking</div>');
      });

      cells.push(cmpLabelCell(''));
      options.forEach(function (opt) {
        cells.push('<div class="frx-cmp-cell frx-cmp-value frx-cmp-book-cell"><button type="button" class="frx-compare-book" data-group="' + groupName + '" data-price-id="' + opt.id + '">Select This Fare</button></div>');
      });

      compareTable.innerHTML = cells.join('');

      compareTable.querySelectorAll('.frx-compare-book').forEach(function (bookBtn) {
        bookBtn.addEventListener('click', function () {
          var radio = document.querySelector('input[name="price_ids[' + bookBtn.dataset.group + ']"][value="' + bookBtn.dataset.priceId + '"]');
          if (radio) {
            radio.checked = true;
            radio.dispatchEvent(new Event('change'));
            radio.closest('.frx-flight').scrollIntoView({ behavior: 'smooth', block: 'center' });
          }
          closeCompare();
        });
      });

      compareOverlay.classList.add('open');
      updateCompareScrollHint();

      options.forEach(function (opt, i) {
        fetch('{{ route('flights.fare-rule.ajax') }}?price_id=' + encodeURIComponent(opt.id))
          .then(function (r) { return r.json(); })
          .then(function (data) {
            var cancelCell = document.getElementById('frxCmpCancel_' + i);
            var dateCell = document.getElementById('frxCmpDate_' + i);
            if (!data.success || !data.fareRule) {
              if (cancelCell) cancelCell.innerHTML = '—';
              if (dateCell) dateCell.innerHTML = '—';
              return;
            }
            var segKey = Object.keys(data.fareRule)[0];
            var tfr = (segKey && data.fareRule[segKey].tfr) || {};

            // TripJack's raw policyInfo text uses a literal "__nls__" token as
            // a line-break placeholder — clean it up before ever showing it.
            function cleanPolicyText(text) {
              return (text || '').replace(/__nls__/g, ' ').replace(/\s+/g, ' ').trim();
            }

            function firstBandCell(policyKey, cellId) {
              var bands = tfr[policyKey];
              if (!bands || !bands.length) return '—';
              var b = bands[0];
              if (b.amount === undefined) {
                var note = cleanPolicyText(b.policyInfo);
                if (!note) return '—';
                // TripJack appends a generic "Please Note: …" disclaimer to the
                // actual policy — show the policy itself as the headline and
                // tuck the disclaimer behind "See more".
                var split = note.search(/please note/i);
                var main = split > 0 ? note.slice(0, split).trim() : note;
                var extra = split > 0 ? note.slice(split).trim() : '';
                var html = '<span class="frx-cmp-policy">' + main + '</span>';
                if (extra) {
                  html += '<span class="frx-cmp-note" hidden>' + extra + '</span>'
                    + '<button type="button" class="frx-cmp-more">See more</button>';
                }
                return html;
              }
              var amt = Math.round((b.amount || 0) + (b.additionalFee || 0));
              var html = 'INR ' + amt.toLocaleString('en-IN') + (bands.length > 1 ? ' onwards' : '');
              if (b.policyInfo) html += '<span class="frx-cmp-subnote">' + cleanPolicyText(b.policyInfo) + '</span>';
              return html;
            }

            if (cancelCell) cancelCell.innerHTML = firstBandCell('CANCELLATION', 'frxCmpCancel_' + i);
            if (dateCell) dateCell.innerHTML = firstBandCell('DATECHANGE', 'frxCmpDate_' + i);
            [cancelCell, dateCell].forEach(function (cell) {
              if (!cell) return;
              var moreBtn = cell.querySelector('.frx-cmp-more');
              if (moreBtn) {
                moreBtn.addEventListener('click', function () {
                  var note = cell.querySelector('.frx-cmp-note');
                  note.hidden = !note.hidden;
                  moreBtn.textContent = note.hidden ? 'See more' : 'See less';
                });
              }
            });
          })
          .catch(function () {
            var cancelCell = document.getElementById('frxCmpCancel_' + i);
            var dateCell = document.getElementById('frxCmpDate_' + i);
            if (cancelCell) cancelCell.innerHTML = '—';
            if (dateCell) dateCell.innerHTML = '—';
          });
      });
    });
  });
})();
</script>
@endpush
