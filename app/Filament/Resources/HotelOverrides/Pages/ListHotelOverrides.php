<?php

namespace App\Filament\Resources\HotelOverrides\Pages;

use App\Filament\Resources\HotelOverrides\HotelOverrideResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListHotelOverrides extends ListRecords
{
    protected static string $resource = HotelOverrideResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
