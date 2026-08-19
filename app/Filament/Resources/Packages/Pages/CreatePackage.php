<?php

namespace App\Filament\Resources\Packages\Pages;

use App\Filament\Resources\Packages\PackageResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Actions\Action;

class CreatePackage extends CreateRecord
{
    protected static string $resource = PackageResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    public function mount(): void
    {
        parent::mount();

        $draft = cache()->get('draft_package_' . auth()->id());

        if ($draft && is_array($draft)) {
            $this->form->fill($draft);
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
                    'draft_package_' . auth()->id(),
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
        cache()->forget('draft_package_' . auth()->id());
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('clear_draft')
                ->label('Discard Draft')
                ->color('danger')
                ->requiresConfirmation()
                ->action(function () {
                    cache()->forget('draft_package_' . auth()->id());
                    $this->form->fill();
                })
                ->visible(fn () => cache()->has('draft_package_' . auth()->id())),
        ];
    }
}
