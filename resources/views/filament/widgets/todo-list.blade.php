<x-filament-widgets::widget>
    <div class="tyt-todo">
        <h3 class="tyt-dash-title">Needs your attention</h3>

        @if(empty($items))
            <div class="tyt-todo-clear">
                <span>✅</span>
                <div>
                    <strong>All caught up</strong>
                    <p>No new enquiries, stuck bookings or reviews waiting. New items will appear here automatically.</p>
                </div>
            </div>
        @else
            <div class="tyt-todo-grid">
                @foreach($items as $item)
                    <a href="{{ $item['url'] }}" class="tyt-todo-card tyt-tone-{{ $item['tone'] }}">
                        <div class="tyt-todo-top">
                            <span class="tyt-todo-icon">{{ $item['icon'] }}</span>
                            @if($item['count'] !== null)
                                <span class="tyt-todo-count">{{ $item['count'] }}</span>
                            @endif
                        </div>
                        <strong>{{ $item['title'] }}</strong>
                        <p>{{ $item['text'] }}</p>
                        <span class="tyt-todo-btn">{{ $item['button'] }} →</span>
                    </a>
                @endforeach
            </div>
        @endif
    </div>

    <style>
        .tyt-dash-title { margin: 0 0 12px; font-size: 16px; font-weight: 700; color: #1f2430; }
        .dark .tyt-dash-title { color: #eceef2; }
        .tyt-todo-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(230px, 1fr)); gap: 14px; }
        .tyt-todo-card { display: flex; flex-direction: column; gap: 6px; padding: 16px 18px; border-radius: 14px; text-decoration: none; color: #1f2430;
            background: #fff; border: 1px solid #ece8dc; border-top: 4px solid var(--tone); box-shadow: 0 1px 2px rgba(16,24,40,.04); transition: transform .15s, box-shadow .15s; }
        .tyt-todo-card:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(16,24,40,.08); }
        .dark .tyt-todo-card { background: #1f2430; border-color: #333a4d; border-top-color: var(--tone); color: #eceef2; }
        .tyt-tone-danger { --tone: #e11d48; } .tyt-tone-warning { --tone: #f59e0b; } .tyt-tone-info { --tone: #3b82f6; }
        .tyt-todo-top { display: flex; justify-content: space-between; align-items: center; }
        .tyt-todo-icon { font-size: 22px; }
        .tyt-todo-count { min-width: 34px; padding: 2px 10px; border-radius: 999px; text-align: center; font-size: 18px; font-weight: 800; color: #fff; background: var(--tone); }
        .tyt-todo-card strong { font-size: 14.5px; line-height: 1.3; }
        .tyt-todo-card p { margin: 0; font-size: 12.5px; color: #6b7280; line-height: 1.45; flex: 1; }
        .dark .tyt-todo-card p { color: #a3aab8; }
        .tyt-todo-btn { margin-top: 6px; font-size: 12.5px; font-weight: 700; color: var(--tone); }
        .tyt-todo-clear { display: flex; align-items: center; gap: 14px; padding: 18px 20px; border-radius: 14px; background: #ecfdf5; border: 1px solid #a7f3d0; }
        .dark .tyt-todo-clear { background: #0f2a20; border-color: #14532d; }
        .tyt-todo-clear span { font-size: 26px; }
        .tyt-todo-clear strong { font-size: 15px; color: #065f46; }
        .dark .tyt-todo-clear strong { color: #6ee7b7; }
        .tyt-todo-clear p { margin: 2px 0 0; font-size: 13px; color: #047857; }
        .dark .tyt-todo-clear p { color: #a7f3d0; }
    </style>
</x-filament-widgets::widget>
