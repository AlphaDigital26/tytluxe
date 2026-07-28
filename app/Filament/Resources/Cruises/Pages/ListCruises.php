<?php

namespace App\Filament\Resources\Cruises\Pages;

use App\Filament\Resources\Cruises\CruiseResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCruises extends ListRecords
{
    protected static string $resource = CruiseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
