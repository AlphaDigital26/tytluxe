<?php

namespace App\Filament\Resources\Destinations\Pages;

use App\Filament\Resources\Destinations\DestinationResource;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditDestination extends EditRecord
{
    protected static string $resource = DestinationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->before(function (DeleteAction $action) {
                    $destination = $this->getRecord();

                    $counts = [
                        'hotel' => $destination->hotels()->count(),
                        'cruise' => $destination->cruises()->count(),
                        'package' => $destination->packages()->count(),
                    ];
                    $counts = array_filter($counts);

                    if ($counts === []) {
                        return;
                    }

                    $summary = collect($counts)
                        ->map(fn ($count, $label) => $count.' '.\Illuminate\Support\Str::plural($label, $count))
                        ->implode(', ');

                    Notification::make()
                        ->title('Cannot delete destination')
                        ->body("This destination still has {$summary} attached to it. Remove or reassign them first.")
                        ->danger()
                        ->send();

                    $action->halt();
                }),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
