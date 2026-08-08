<?php

namespace App\Filament\Resources\FeaturedBlogDestinations\Pages;

use App\Filament\Resources\FeaturedBlogDestinations\FeaturedBlogDestinationResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditFeaturedBlogDestination extends EditRecord
{
    protected static string $resource = FeaturedBlogDestinationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
