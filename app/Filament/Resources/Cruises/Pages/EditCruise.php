<?php

namespace App\Filament\Resources\Cruises\Pages;

use App\Filament\Resources\Cruises\CruiseResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCruise extends EditRecord
{
    protected static string $resource = CruiseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
