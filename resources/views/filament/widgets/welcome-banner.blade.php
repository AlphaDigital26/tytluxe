<x-filament-widgets::widget>
    <div class="tyt-hello">
        <div class="tyt-hello-text">
            <p class="tyt-hello-date">{{ $date }}</p>
            <h2>{{ $greeting }}, {{ $name }} 👋</h2>
            <p class="tyt-hello-sub">
                @if($todoCount === 0)
                    You're all caught up — nothing needs your attention right now.
                @elseif($todoCount === 1)
                    There is <strong>1 thing</strong> that needs your attention today. It's listed just below.
                @else
                    There are <strong>{{ $todoCount }} things</strong> that need your attention today. They're listed just below.
                @endif
            </p>
        </div>
        <div class="tyt-hello-links">
            @foreach($links as $link)
                <a href="{{ $link['url'] }}" @if($link['new_tab']) target="_blank" rel="noopener" @endif>
                    <span>{{ $link['icon'] }}</span>{{ $link['label'] }}
                </a>
            @endforeach
        </div>
    </div>

    <style>
        .tyt-hello { position: relative; overflow: hidden; display: flex; justify-content: space-between; align-items: center; gap: 20px; flex-wrap: wrap;
            padding: 26px 28px; border-radius: 16px; color: #fff;
            background: radial-gradient(circle at 85% 20%, rgba(201,168,76,.35), transparent 45%), linear-gradient(120deg, #14161c 0%, #23262f 60%, #2e2a1f 100%); }
        .tyt-hello::after { content: ''; position: absolute; right: -40px; bottom: -60px; width: 220px; height: 220px; border-radius: 50%; border: 1px solid rgba(201,168,76,.25); }
        .tyt-hello-text { position: relative; z-index: 1; max-width: 620px; }
        .tyt-hello-date { margin: 0 0 4px; font-size: 12px; letter-spacing: .12em; text-transform: uppercase; color: #c9a84c; font-weight: 600; }
        .tyt-hello h2 { margin: 0 0 6px; font-size: 26px; font-weight: 700; line-height: 1.2; }
        .tyt-hello-sub { margin: 0; font-size: 14.5px; color: rgba(255,255,255,.78); }
        .tyt-hello-sub strong { color: #f3d98b; }
        .tyt-hello-links { position: relative; z-index: 1; display: flex; flex-wrap: wrap; gap: 8px; }
        .tyt-hello-links a { display: inline-flex; align-items: center; gap: 6px; padding: 9px 14px; border-radius: 999px; font-size: 13px; font-weight: 600;
            color: #fff; text-decoration: none; background: rgba(255,255,255,.08); border: 1px solid rgba(255,255,255,.16); transition: background .15s; }
        .tyt-hello-links a:hover { background: rgba(201,168,76,.28); border-color: rgba(201,168,76,.6); }
        @media (max-width: 640px) { .tyt-hello { padding: 20px; } .tyt-hello h2 { font-size: 21px; } }
    </style>
</x-filament-widgets::widget>
