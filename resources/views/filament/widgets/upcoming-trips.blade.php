<x-filament-widgets::widget>
    <x-filament::section heading="Coming up in the next 7 days" description="Guests checking in or flying soon.">
        @if(empty($trips))
            <div class="tyt-trip-empty">
                <span>🧳</span>
                <strong>A quiet week ahead</strong>
                <p>No guests are checking in or flying in the next 7 days. New trips will show up here automatically.</p>
            </div>
        @else
            <div class="tyt-trips">
                @foreach($trips as $trip)
                    @php
                        $when = $trip['date']->isToday() ? 'Today' : ($trip['date']->isTomorrow() ? 'Tomorrow' : $trip['date']->format('D'));
                    @endphp
                    <a href="{{ $trip['url'] }}" class="tyt-trip">
                        <div class="tyt-trip-date {{ $trip['date']->isToday() ? 'is-today' : '' }}">
                            <span>{{ $trip['date']->format('M') }}</span>
                            <strong>{{ $trip['date']->format('j') }}</strong>
                        </div>
                        <div class="tyt-trip-body">
                            <strong>{{ $trip['icon'] }} {{ $trip['guest'] ?: 'Guest' }}</strong>
                            <span>{{ $trip['what'] }}</span>
                        </div>
                        <div class="tyt-trip-when">
                            <span>{{ $when }}</span>
                            @if($trip['status']['color'] !== 'success')
                                <small title="{{ $trip['status']['help'] }}">{{ $trip['status']['label'] }}</small>
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </x-filament::section>

    <style>
        .tyt-trip-empty { display: flex; flex-direction: column; align-items: center; text-align: center; gap: 6px; padding: 34px 16px;
            border-radius: 14px; background: linear-gradient(180deg, #fbf7ec, transparent); }
        .dark .tyt-trip-empty { background: linear-gradient(180deg, #262c3a, transparent); }
        .tyt-trip-empty span { display: inline-flex; align-items: center; justify-content: center; width: 58px; height: 58px; border-radius: 50%;
            font-size: 28px; background: #f6ecd0; margin-bottom: 4px; }
        .tyt-trip-empty strong { font-size: 15px; }
        .tyt-trip-empty p { margin: 0; max-width: 300px; font-size: 13px; color: #6b7280; }
        .tyt-trips { display: flex; flex-direction: column; }
        .tyt-trip { display: flex; align-items: center; gap: 14px; padding: 10px 4px; border-bottom: 1px dashed #ece8dc; text-decoration: none; color: inherit; border-radius: 8px; }
        .tyt-trip:last-child { border-bottom: none; }
        .tyt-trip:hover { background: rgba(201,168,76,.08); }
        .dark .tyt-trip { border-color: #333a4d; }
        .tyt-trip-date { width: 46px; flex-shrink: 0; text-align: center; border-radius: 10px; padding: 4px 0; background: #faf6ea; border: 1px solid #ece2c2; }
        .dark .tyt-trip-date { background: #262c3a; border-color: #3a4256; }
        .tyt-trip-date.is-today { background: #c9a84c; border-color: #c9a84c; color: #fff; }
        .tyt-trip-date span { display: block; font-size: 10px; text-transform: uppercase; letter-spacing: .08em; }
        .tyt-trip-date strong { display: block; font-size: 18px; line-height: 1.1; }
        .tyt-trip-body { flex: 1; min-width: 0; }
        .tyt-trip-body strong { display: block; font-size: 13.5px; }
        .tyt-trip-body span { display: block; font-size: 12.5px; color: #6b7280; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .tyt-trip-when { text-align: right; font-size: 12px; font-weight: 600; color: #8a7a4a; }
        .tyt-trip-when small { display: block; font-weight: 600; color: #b45309; }
    </style>
</x-filament-widgets::widget>
