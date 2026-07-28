<?php

namespace App\Filament\Resources\Staycations\Pages;

use App\Filament\Resources\Staycations\StaycationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListStaycations extends ListRecords
{
    protected static string $resource = StaycationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
