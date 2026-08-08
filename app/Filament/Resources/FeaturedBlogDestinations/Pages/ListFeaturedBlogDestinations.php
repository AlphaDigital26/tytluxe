<?php

namespace App\Filament\Resources\FeaturedBlogDestinations\Pages;

use App\Filament\Resources\FeaturedBlogDestinations\FeaturedBlogDestinationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListFeaturedBlogDestinations extends ListRecords
{
    protected static string $resource = FeaturedBlogDestinationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
