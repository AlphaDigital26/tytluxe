<?php

namespace App\Filament\Resources\Hotels\Pages;

use App\Filament\Resources\Hotels\HotelResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditHotel extends EditRecord
{
    protected static string $resource = HotelResource::class;

    // After saving hotel details (and rooms), go back to the hotels list.
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Hotel Saved! ✅')
            ->body('All hotel details and room types have been saved successfully.');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('view_live')
                ->label('View on Website')
                ->icon('heroicon-o-arrow-top-right-on-square')
                ->color('gray')
                ->url(fn () => route('hotel.details', ['slug' => $this->record->slug]))
                ->openUrlInNewTab()
                ->visible(fn () => ! empty($this->record?->slug)),
            DeleteAction::make()
                // Bookings keep a link to their hotel (and the database
                // refuses to delete it), so a booked hotel is hidden, not deleted.
                ->before(function (DeleteAction $action) {
                    $count = $this->getRecord()->bookings()->count();
                    if ($count === 0) {
                        return;
                    }

                    Notification::make()
                        ->title('This hotel can\'t be deleted')
                        ->body("It has {$count} ".\Illuminate\Support\Str::plural('booking', $count).'. To take it off the website, switch off "Visible on Website" instead.')
                        ->danger()
                        ->send();

                    $action->halt();
                }),
        ];
    }
}
