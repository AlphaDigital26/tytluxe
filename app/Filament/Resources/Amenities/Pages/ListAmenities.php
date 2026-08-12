<?php

namespace App\Filament\Resources\Amenities\Pages;

use App\Filament\Resources\Amenities\AmenityResource;
use App\Models\Amenity;
use Filament\Actions\CreateAction;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListAmenities extends ListRecords
{
    protected static string $resource = AmenityResource::class;

    // ── Header action: "New Amenity" button ──────────────────────────────
    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('+ Add Amenity'),
        ];
    }

    // ── Tabs ─────────────────────────────────────────────────────────────
    public function getTabs(): array
    {
        return [

            'all' => Tab::make('All')
                ->badge(Amenity::count()),

            'hotel' => Tab::make('🏨  Hotels')
                ->badge(Amenity::where('type', 'hotel')->count())
                ->badgeColor('info')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'hotel')),

            'cruise' => Tab::make('🚢  Cruises')
                ->badge(Amenity::where('type', 'cruise')->count())
                ->badgeColor('success')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'cruise')),

            'package' => Tab::make('📦  Packages')
                ->badge(Amenity::where('type', 'package')->count())
                ->badgeColor('warning')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'package')),

        ];
    }
}
