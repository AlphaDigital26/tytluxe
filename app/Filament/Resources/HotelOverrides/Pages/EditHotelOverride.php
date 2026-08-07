<?php

namespace App\Filament\Resources\HotelOverrides\Pages;

use App\Filament\Resources\HotelOverrides\HotelOverrideResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditHotelOverride extends EditRecord
{
    protected static string $resource = HotelOverrideResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
