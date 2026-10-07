<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

/**
 * A titled row of dashboard cards: icon badge, label, big number, one-line
 * hint, optional link. Shared look for the Hotels, Flights and Website rows
 * — subclasses only say what the cards contain.
 */
abstract class MetricCardsWidget extends Widget
{
    protected string $view = 'filament.widgets.metric-cards';

    /** Load with the page (one request) instead of one request per widget. */
    protected static bool $isLazy = false;

    protected int|string|array $columnSpan = 'full';

    /** Section title and its emoji badge. */
    abstract protected function heading(): array;

    /**
     * @return array<int, array{icon: string, label: string, value: string, hint?: string, tone?: string, url?: ?string}>
     *         tone: gold | green | red | blue | amber | violet | gray
     */
    abstract protected function cards(): array;

    /** Optional "View all" link for the section header. */
    protected function link(): ?array
    {
        return null;
    }

    /** Cards per row on wide screens (auto-fits on smaller ones). */
    protected function minCardWidth(): int
    {
        return 200;
    }

    protected function getViewData(): array
    {
        [$title, $icon] = $this->heading();

        return [
            'title' => $title,
            'icon' => $icon,
            'link' => $this->link(),
            'cards' => $this->cards(),
            'minWidth' => $this->minCardWidth(),
        ];
    }
}
