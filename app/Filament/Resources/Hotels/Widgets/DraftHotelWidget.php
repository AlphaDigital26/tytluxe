<?php

namespace App\Filament\Resources\Hotels\Widgets;

use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Widgets\Widget;

class DraftHotelWidget extends Widget implements HasActions, HasSchemas
{
    use InteractsWithActions;
    use InteractsWithSchemas;

    protected string $view = 'filament.resources.hotels.widgets.draft-hotel-widget';

    public ?array $draft = null;

    public function mount(): void
    {
        $this->draft = cache()->get('draft_hotel_' . auth()->id());
    }

    public function discardDraftAction(): Action
    {
        return Action::make('discardDraft')
            ->label('Discard Draft')
            ->color('danger')
            ->icon('heroicon-o-trash')
            ->requiresConfirmation()
            ->modalHeading('Discard Hotel Draft?')
            ->modalDescription('Are you sure you want to discard this draft? All unsaved data will be permanently lost.')
            ->modalSubmitActionLabel('Yes, Discard Draft')
            ->modalIcon('heroicon-o-exclamation-triangle')
            ->modalIconColor('danger')
            ->action(function () {
                cache()->forget('draft_hotel_' . auth()->id());
                $this->draft = null;
            });
    }
}
