<?php

namespace App\Filament\Resources\Hotels\Pages;

use App\Filament\Resources\Hotels\HotelResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Filament\Actions\Action;

class CreateHotel extends CreateRecord
{
    protected static string $resource = HotelResource::class;

    // After saving the hotel, take the admin directly to the Edit page
    // where the Room Types tab is visible — so they can add rooms immediately.
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('edit', ['record' => $this->getRecord()]);
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Hotel Created! 🏨')
            ->body('Now scroll down to the \'Room Types\' section below to add the rooms for this hotel.');
    }

    public function mount(): void
    {
        parent::mount();

        $draft = cache()->get('draft_hotel_' . auth()->id());

        if ($draft && is_array($draft)) {
            $this->form->fill($draft);

            // Relationship fields get wiped by the form->fill() on Create pages.
            // We must manually re-hydrate them by setting their state explicitly.
            $fields = $this->form->getFlatFields();
            foreach (['images', 'amenities'] as $key) {
                if (isset($draft[$key]) && isset($fields[$key])) {
                    $fields[$key]->state($draft[$key]);
                }
            }
        }
    }

    private function getSerializableData(array $data): array
    {
        $result = [];
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $result[$key] = $this->getSerializableData($value);
            } elseif (!is_object($value)) {
                $result[$key] = $value;
            }
        }
        return $result;
    }

    public function updated($property): void
    {
        if (str_starts_with($property, 'data.')) {
            try {
                cache()->put(
                    'draft_hotel_' . auth()->id(),
                    $this->getSerializableData($this->data),
                    now()->addHours(24)
                );
            } catch (\Throwable $e) {
                // Silently ignore if it still fails to serialize
            }
        }
    }

    protected function afterCreate(): void
    {
        cache()->forget('draft_hotel_' . auth()->id());
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('clear_draft')
                ->label('Discard Draft')
                ->color('danger')
                ->requiresConfirmation()
                ->action(function () {
                    cache()->forget('draft_hotel_' . auth()->id());
                    $this->form->fill();
                })
                ->visible(fn () => cache()->has('draft_hotel_' . auth()->id())),
        ];
    }
}

