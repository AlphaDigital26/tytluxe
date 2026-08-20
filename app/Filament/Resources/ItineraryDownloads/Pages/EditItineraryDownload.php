<?php

namespace App\Filament\Resources\ItineraryDownloads\Pages;

use App\Filament\Resources\ItineraryDownloads\ItineraryDownloadResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditItineraryDownload extends EditRecord
{
    protected static string $resource = ItineraryDownloadResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
