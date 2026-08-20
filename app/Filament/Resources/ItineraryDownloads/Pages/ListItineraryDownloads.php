<?php

namespace App\Filament\Resources\ItineraryDownloads\Pages;

use App\Filament\Resources\ItineraryDownloads\ItineraryDownloadResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListItineraryDownloads extends ListRecords
{
    protected static string $resource = ItineraryDownloadResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //
        ];
    }
}
