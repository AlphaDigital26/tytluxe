<?php

namespace App\Filament\Resources\Hotels\Widgets;

use Filament\Widgets\Widget;

class DraftHotelWidget extends Widget
{
    protected string $view = 'filament.resources.hotels.widgets.draft-hotel-widget';

    public ?array $draft = null;

    public function mount(): void
    {
        $this->draft = cache()->get('draft_hotel_' . auth()->id());
    }

    public function clearDraft(): void
    {
        cache()->forget('draft_hotel_' . auth()->id());
        $this->draft = null;
    }
}
