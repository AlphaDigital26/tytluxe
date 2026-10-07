<?php

namespace App\Filament\Widgets;

use App\Support\DashboardData;
use Filament\Widgets\Widget;

/** "Needs your attention today" — one card per thing to act on, each with a button. */
class TodoList extends Widget
{
    protected string $view = 'filament.widgets.todo-list';

    protected static ?int $sort = 1;

    /** Load with the page (one request) instead of one request per widget. */
    protected static bool $isLazy = false;

    protected int|string|array $columnSpan = 'full';

    protected function getViewData(): array
    {
        return ['items' => DashboardData::todo()];
    }
}
