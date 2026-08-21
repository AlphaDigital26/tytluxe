<?php

namespace App\Filament\Resources\Offers\Pages;

use App\Filament\Resources\Offers\OfferResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListOffers extends ListRecords
{
    protected static string $resource = OfferResource::class;

    protected function getHeaderActions(): array
    {
        $isVisible = \App\Models\Setting::get('offers_page.is_visible', '1') === '1';

        return [
            \Filament\Actions\Action::make('toggle_visibility')
                ->label($isVisible ? 'Hide Offers Page' : 'Show Offers Page')
                ->icon($isVisible ? 'heroicon-o-eye-slash' : 'heroicon-o-eye')
                ->color($isVisible ? 'danger' : 'success')
                ->action(function () use ($isVisible) {
                    \App\Models\Setting::set('offers_page.is_visible', $isVisible ? '0' : '1');
                    \Filament\Notifications\Notification::make()
                        ->title($isVisible ? 'Offers Page Hidden' : 'Offers Page Visible')
                        ->success()
                        ->send();
                    
                    return redirect(request()->header('Referer'));
                }),
            CreateAction::make(),
        ];
    }
}
