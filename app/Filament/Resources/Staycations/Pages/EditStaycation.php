<?php

namespace App\Filament\Resources\Staycations\Pages;

use App\Filament\Resources\Staycations\StaycationResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditStaycation extends EditRecord
{
    protected static string $resource = StaycationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
