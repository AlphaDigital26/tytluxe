<?php

namespace App\Filament\Resources\Cruises\Pages;

use App\Filament\Resources\Cruises\CruiseResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCruise extends CreateRecord
{
    protected static string $resource = CruiseResource::class;

    // One clear Save button — no "Create & create another".
    protected static bool $canCreateAnother = false;
}
