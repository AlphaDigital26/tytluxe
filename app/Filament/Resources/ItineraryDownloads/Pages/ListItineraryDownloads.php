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
            \pxlrbt\FilamentExcel\Actions\ExportAction::make()
                ->label('Download as Excel')
                ->exports([
                    \pxlrbt\FilamentExcel\Exports\ExcelExport::make()->fromTable(),
                ]),
        ];
    }

    public function getSubheading(): ?string
    {
        return 'People who downloaded a package itinerary — good leads to call. Each person is shown once (untick the filter to see every download). Use "Turn into enquiry" to add them to Enquiries → New.';
    }
}
