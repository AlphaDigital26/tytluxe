{{-- Copy of Filament's chart-widget view; only the period filter is changed to a styled dropdown. --}}
@php
    use Filament\Support\Facades\FilamentAsset;
    use Filament\Support\View\ComponentAttributeBag as FilamentComponentAttributeBag;
    use Filament\Widgets\View\Components\ChartWidgetComponent;
    use Illuminate\Contracts\Support\Htmlable;

    $color = $this->getColor();
    $heading = $this->getHeading();
    $description = $this->getDescription();
    $filters = $this->getFilters();
    $isCollapsible = $this->isCollapsible();
    $type = $this->getType();
    $maxHeight = $this->getMaxHeight();
    $hasMaxHeight = filled($maxHeight) && $maxHeight !== '100%';
    $isEmpty = $this->isEmpty();
    $chartAssistiveContent = $isEmpty ? null : $this->getChartAssistiveContent();
    $hasChartAssistiveContent = is_string($chartAssistiveContent)
        ? filled($chartAssistiveContent)
        : ($chartAssistiveContent !== null);

    // The chart paints onto a `<canvas>`, which exposes no internal structure, so build an accessible
    // name from the widget's heading and description for the surrounding `role="img"` element.
    $chartAccessibleLabel = trim(implode('. ', array_filter([
        $heading instanceof Htmlable ? strip_tags($heading->toHtml()) : $heading,
        $description instanceof Htmlable ? strip_tags($description->toHtml()) : $description,
    ], fn ($value): bool => filled($value))));
@endphp

<x-filament-widgets::widget class="fi-wi-chart">
    <x-filament::section
        :description="$description"
        :heading="$heading"
        :collapsible="$isCollapsible"
    >
        @if ($filters || method_exists($this, 'getFiltersSchema'))
            <x-slot name="afterHeader">
                @if ($filters)
                    {{-- Styled period dropdown (TYT Luxe), in place of the browser's plain select. --}}
                    <div class="tyt-pd" x-data="{ open: false }" x-on:keydown.escape.window="open = false" x-on:click.outside="open = false">
                        <button type="button" class="tyt-pd-btn" x-on:click="open = ! open" x-bind:aria-expanded="open" aria-haspopup="listbox" aria-label="Sales period">
                            <svg class="tyt-pd-ico" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M5.75 2a.75.75 0 01.75.75V4h7V2.75a.75.75 0 011.5 0V4h.25A2.75 2.75 0 0118 6.75v8.5A2.75 2.75 0 0115.25 18H4.75A2.75 2.75 0 012 15.25v-8.5A2.75 2.75 0 014.75 4H5V2.75A.75.75 0 015.75 2zm-1 5.5c-.69 0-1.25.56-1.25 1.25v6.5c0 .69.56 1.25 1.25 1.25h10.5c.69 0 1.25-.56 1.25-1.25v-6.5c0-.69-.56-1.25-1.25-1.25H4.75z"/></svg>
                            <span>{{ $filters[$this->filter] ?? reset($filters) }}</span>
                            <svg class="tyt-pd-chev" x-bind:class="open && 'is-open'" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.17l3.71-3.94a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd"/></svg>
                        </button>
                        <div class="tyt-pd-menu" x-show="open" x-transition.opacity.scale.origin.top.right x-cloak role="listbox" aria-label="Sales period">
                            @foreach ($filters as $value => $label)
                                <button
                                    type="button"
                                    role="option"
                                    aria-selected="{{ $this->filter === $value ? 'true' : 'false' }}"
                                    wire:click="$set('filter', '{{ $value }}')"
                                    x-on:click="open = false"
                                    @class(['tyt-pd-opt', 'is-on' => $this->filter === $value])
                                >
                                    <span>{{ $label }}</span>
                                    @if ($this->filter === $value)
                                        <svg viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M16.7 5.3a1 1 0 010 1.4l-8 8a1 1 0 01-1.4 0l-4-4a1 1 0 111.4-1.4L8 12.6l7.3-7.3a1 1 0 011.4 0z" clip-rule="evenodd"/></svg>
                                    @endif
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if (method_exists($this, 'getFiltersSchema'))
                    <x-filament::dropdown
                        :menu="false"
                        placement="bottom-end"
                        shift
                        width="xs"
                        class="fi-wi-chart-filter"
                    >
                        <x-slot name="trigger">
                            {{ $this->getFiltersTriggerAction() }}
                        </x-slot>

                        <div class="fi-wi-chart-filter-content">
                            {{ $this->getFiltersSchema() }}

                            @if (method_exists($this, 'hasDeferredFilters') && $this->hasDeferredFilters())
                                <div
                                    class="fi-wi-chart-filter-content-actions-ctn"
                                >
                                    {{ $this->getFiltersApplyAction() }}

                                    {{ $this->getFiltersResetAction() }}
                                </div>
                            @endif
                        </div>
                    </x-filament::dropdown>
                @endif
            </x-slot>
        @endif

        <div
            @if ($pollingInterval = $this->getPollingInterval())
                wire:poll.{{ $pollingInterval }}="updateChartData"
            @endif
            @if ($isEmpty)
                style="display: none"
            @endif
        >
            <div
                class="fi-wi-chart-image"
                @if (filled($chartAccessibleLabel))
                    role="img"
                    aria-label="{{ $chartAccessibleLabel }}"
                @endif
            >
                <div
                    x-load
                    x-load-src="{{ FilamentAsset::getAlpineComponentSrc('chart', 'filament/widgets') }}"
                    wire:ignore
                    data-chart-type="{{ $type }}"
                    x-data="chart({
                                cachedData: @js($this->getCachedData()),
                                options: @js($this->getOptions()),
                                type: @js($type),
                            })"
                    {{
                        (new FilamentComponentAttributeBag)
                            ->color(ChartWidgetComponent::class, $color)
                            ->class([
                                'fi-wi-chart-frame',
                                'fi-wi-chart-canvas-ctn',
                                'fi-wi-chart-frame-no-aspect-ratio' => $hasMaxHeight,
                            ])
                    }}
                >
                    <canvas
                        x-ref="canvas"
                        aria-hidden="true"
                        @style([
                            'width: 100%',
                            'height: 100%; max-height: 100%' => ! $hasMaxHeight,
                            ('max-height: ' . e($maxHeight)) => $hasMaxHeight,
                        ])
                    ></canvas>

                    {{--
                        Chart.js paints the chart onto the canvas, where a stylesheet cannot reach it. These empty
                        elements carry the colors it should use, so that a theme can set them with an ordinary
                        `color` declaration and they follow light and dark mode like any other element.
                    --}}
                    <span
                        aria-hidden="true"
                        x-ref="backgroundColorElement"
                        class="fi-wi-chart-bg-color"
                    ></span>

                    <span
                        aria-hidden="true"
                        x-ref="borderColorElement"
                        class="fi-wi-chart-border-color"
                    ></span>

                    <span
                        aria-hidden="true"
                        x-ref="gridColorElement"
                        class="fi-wi-chart-grid-color"
                    ></span>

                    <span
                        aria-hidden="true"
                        x-ref="textColorElement"
                        class="fi-wi-chart-text-color"
                    ></span>

                    <span
                        aria-hidden="true"
                        x-ref="tooltipBackgroundColorElement"
                        class="fi-wi-chart-tooltip-bg-color"
                    ></span>

                    <span
                        aria-hidden="true"
                        x-ref="tooltipTextColorElement"
                        class="fi-wi-chart-tooltip-text-color"
                    ></span>

                    <span
                        aria-hidden="true"
                        x-ref="tooltipBorderColorElement"
                        class="fi-wi-chart-tooltip-border-color"
                    ></span>
                </div>
            </div>

            @if ($hasChartAssistiveContent)
                <div class="fi-wi-chart-assistive-content fi-sr-only">
                    @if (is_string($chartAssistiveContent))
                        <p>{{ $chartAssistiveContent }}</p>
                    @else
                        {{ $chartAssistiveContent }}
                    @endif
                </div>
            @endif
        </div>

        @if ($isEmpty)
            @if ($emptyState = $this->getEmptyState())
                {{ $emptyState }}
            @else
                <div
                    @class([
                        'fi-wi-chart-frame',
                        'fi-wi-chart-frame-no-aspect-ratio' => $hasMaxHeight,
                    ])
                    @style([
                        ('min-height: ' . e($maxHeight)) => $hasMaxHeight,
                    ])
                >
                    <x-filament::empty-state
                        :contained="false"
                        :description="$this->getEmptyStateDescription()"
                        :heading="$this->getEmptyStateHeading()"
                        :icon="$this->getEmptyStateIcon()"
                        icon-color="gray"
                    >
                        @if ($emptyStateActions = $this->getEmptyStateActions())
                            <x-slot name="footer">
                                <x-filament::actions
                                    :actions="$emptyStateActions"
                                    alignment="center"
                                />
                            </x-slot>
                        @endif
                    </x-filament::empty-state>
                </div>
            @endif
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
