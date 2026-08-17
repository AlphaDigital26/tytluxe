<?php

namespace App\Filament\Resources\Packages;

use App\Filament\Resources\Packages\Pages\CreatePackage;
use App\Filament\Resources\Packages\Pages\EditPackage;
use App\Filament\Resources\Packages\Pages\ListPackages;
use App\Filament\Resources\Packages\Schemas\PackageForm;
use App\Filament\Resources\Packages\Tables\PackagesTable;
use App\Models\Package;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class PackageResource extends Resource
{
    protected static ?string $model = Package::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-gift';

    protected static ?int $navigationSort = 30;

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?string $navigationLabel = 'Packages';

    public static function form(Schema $schema): Schema
    {
        return PackageForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PackagesTable::configure($table);
    }

    public static function getRelations(): array
    {
        // All relations (Highlights, Itinerary Days, Inclusions, Exclusions, Images)
        // are now managed inline within the main form using Repeater components.
        // No separate relation manager tabs needed.
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListPackages::route('/'),
            'create' => CreatePackage::route('/create'),
            'edit'   => EditPackage::route('/{record}/edit'),
        ];
    }
}
