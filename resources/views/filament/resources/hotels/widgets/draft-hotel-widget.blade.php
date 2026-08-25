<x-filament-widgets::widget>
    @if ($draft)
        <x-filament::section icon="heroicon-o-document-text" icon-color="primary">
            <x-slot name="heading">
                Unsaved Draft Hotel
            </x-slot>
            
            <x-slot name="description">
                You have an unsaved hotel draft in progress. You can resume editing it or discard the draft.
            </x-slot>

            <div class="flex items-center gap-x-4 mt-4">
                <x-filament::button
                    tag="a"
                    color="primary"
                    href="{{ \App\Filament\Resources\Hotels\HotelResource::getUrl('create') }}"
                >
                    Resume Draft
                </x-filament::button>

                <x-filament::button
                    color="danger"
                    wire:click="clearDraft"
                    wire:confirm="Are you sure you want to discard this draft?"
                >
                    Discard Draft
                </x-filament::button>
            </div>
        </x-filament::section>
    @endif
</x-filament-widgets::widget>
