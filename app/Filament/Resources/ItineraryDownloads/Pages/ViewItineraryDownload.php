<?php

namespace App\Filament\Resources\ItineraryDownloads\Pages;

use App\Filament\Resources\ItineraryDownloads\ItineraryDownloadResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewItineraryDownload extends ViewRecord
{
    protected static string $resource = ItineraryDownloadResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //
        ];
    }
}
