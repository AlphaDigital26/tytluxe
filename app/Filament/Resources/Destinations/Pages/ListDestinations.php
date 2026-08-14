<?php

namespace App\Filament\Resources\Destinations\Pages;

use App\Filament\Resources\Destinations\DestinationResource;
use App\Models\Destination;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListDestinations extends ListRecords
{
    protected static string $resource = DestinationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Add Destination'),
        ];
    }

    public function getTabs(): array
    {
        return [
            'hotel' => Tab::make('🏨  Hotels')
                ->badge(Destination::whereJsonContains('for', 'hotel')->count())
                ->badgeColor('info')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereJsonContains('for', 'hotel')),

            'cruise' => Tab::make('🚢  Cruises')
                ->badge(Destination::whereJsonContains('for', 'cruise')->count())
                ->badgeColor('success')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereJsonContains('for', 'cruise')),

            'package' => Tab::make('📦  Packages')
                ->badge(Destination::whereJsonContains('for', 'package')->count())
                ->badgeColor('warning')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereJsonContains('for', 'package')),
        ];
    }
}
