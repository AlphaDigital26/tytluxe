<?php

namespace App\Filament\Resources\Packages\Pages;

use App\Filament\Resources\Packages\PackageResource;
use App\Models\Package;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListPackages extends ListRecords
{
    protected static string $resource = PackageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    /** Packages, and the Bin of deleted ones (restorable for Package::BIN_DAYS days). */
    public function getTabs(): array
    {
        $inBin = Package::onlyTrashed()->count();

        return [
            'all' => Tab::make('Packages')->icon('heroicon-m-gift'),
            'bin' => Tab::make('Bin')
                ->icon('heroicon-m-trash')
                ->badge($inBin ?: null)
                ->badgeColor('gray')
                ->modifyQueryUsing(fn (Builder $query) => $query->onlyTrashed()->reorder('deleted_at', 'desc')),
        ];
    }

    public function getSubheading(): ?string
    {
        return $this->activeTab === 'bin'
            ? 'Deleted packages stay here for '.Package::BIN_DAYS.' days. Restore one to bring it back; after '.Package::BIN_DAYS.' days it is deleted for good. Packages with bookings are never deleted for good.'
            : null;
    }

    protected function getFooterWidgets(): array
    {
        return [
            \App\Filament\Resources\Packages\Widgets\DraftPackageWidget::class,
        ];
    }
}
