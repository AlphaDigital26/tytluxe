<x-filament-widgets::widget>
    <div class="tyt-mc">
        <div class="tyt-mc-head">
            <h3><span class="tyt-mc-badge">{{ $icon }}</span>{{ $title }}</h3>
            @if($link)
                <a href="{{ $link['url'] }}">{{ $link['label'] }} →</a>
            @endif
        </div>

        <div class="tyt-mc-grid" style="--tyt-mc-min: {{ $minWidth }}px;">
            @foreach($cards as $card)
                @php $tag = ! empty($card['url']) ? 'a' : 'div'; @endphp
                <{{ $tag }} @if($tag === 'a') href="{{ $card['url'] }}" @endif class="tyt-mc-card tyt-mc-{{ $card['tone'] ?? 'gold' }}">
                    <span class="tyt-mc-icon">{{ $card['icon'] }}</span>
                    <span class="tyt-mc-label">{{ $card['label'] }}</span>
                    <strong class="tyt-mc-value">{{ $card['value'] }}</strong>
                    @if(! empty($card['hint']))
                        <span class="tyt-mc-hint">{{ $card['hint'] }}</span>
                    @endif
                    @if($tag === 'a')
                        <span class="tyt-mc-arrow">→</span>
                    @endif
                </{{ $tag }}>
            @endforeach
        </div>
    </div>

    <style>
        .tyt-mc-head { display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; }
        .tyt-mc-head h3 { display: flex; align-items: center; gap: 10px; margin: 0; font-size: 16px; font-weight: 700; color: #1f2430; }
        .dark .tyt-mc-head h3 { color: #eceef2; }
        .tyt-mc-badge { display: inline-flex; align-items: center; justify-content: center; width: 32px; height: 32px; border-radius: 10px; font-size: 16px;
            background: linear-gradient(135deg, #f6ecd0, #e9d59a); box-shadow: inset 0 0 0 1px rgba(201,168,76,.35); }
        .dark .tyt-mc-badge { background: linear-gradient(135deg, #3a3220, #57492a); }
        .tyt-mc-head a { font-size: 13px; font-weight: 600; color: #a8862f; text-decoration: none; }
        .tyt-mc-head a:hover { text-decoration: underline; }

        .tyt-mc-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(var(--tyt-mc-min), 1fr)); gap: 14px; }
        .tyt-mc-card { position: relative; overflow: hidden; display: flex; flex-direction: column; gap: 4px; padding: 18px 18px 16px; border-radius: 16px;
            text-decoration: none; color: #1f2430; background: #fff; border: 1px solid #eee9db;
            box-shadow: 0 1px 2px rgba(16,24,40,.04); transition: transform .15s ease, box-shadow .15s ease, border-color .15s ease; }
        .tyt-mc-card::before { content: ''; position: absolute; inset: 0 auto 0 0; width: 4px; background: var(--c); }
        .tyt-mc-card::after { content: ''; position: absolute; right: -30px; top: -30px; width: 110px; height: 110px; border-radius: 50%; background: var(--soft); opacity: .55; }
        a.tyt-mc-card:hover { transform: translateY(-3px); box-shadow: 0 10px 24px rgba(16,24,40,.09); border-color: var(--c); }
        .dark .tyt-mc-card { background: #1f2430; border-color: #333a4d; color: #eceef2; }
        .dark .tyt-mc-card::after { opacity: .18; }

        .tyt-mc-icon { position: relative; z-index: 1; display: inline-flex; align-items: center; justify-content: center; width: 40px; height: 40px;
            margin-bottom: 8px; border-radius: 12px; font-size: 19px; background: var(--soft); }
        .tyt-mc-label { position: relative; z-index: 1; font-size: 12.5px; font-weight: 600; color: #6b7280; letter-spacing: .01em; }
        .dark .tyt-mc-label { color: #a3aab8; }
        .tyt-mc-value { position: relative; z-index: 1; font-size: 28px; font-weight: 800; line-height: 1.15; letter-spacing: -.01em; }
        .tyt-mc-hint { position: relative; z-index: 1; font-size: 12.5px; font-weight: 600; color: var(--c); }
        .tyt-mc-arrow { position: absolute; z-index: 1; right: 16px; bottom: 14px; font-size: 16px; font-weight: 700; color: var(--c); opacity: 0; transition: opacity .15s, transform .15s; }
        a.tyt-mc-card:hover .tyt-mc-arrow { opacity: 1; transform: translateX(2px); }

        .tyt-mc-gold   { --c: #b8912e; --soft: #f7eed3; }
        .tyt-mc-green  { --c: #059669; --soft: #d9f5ea; }
        .tyt-mc-red    { --c: #e11d48; --soft: #fde4ea; }
        .tyt-mc-blue   { --c: #2563eb; --soft: #dfe9fe; }
        .tyt-mc-amber  { --c: #d97706; --soft: #fdeccf; }
        .tyt-mc-violet { --c: #7c3aed; --soft: #ece3fe; }
        .tyt-mc-gray   { --c: #64748b; --soft: #eceff3; }
        .dark .tyt-mc-card { --soft: rgba(255,255,255,.08); }
    </style>
</x-filament-widgets::widget>
