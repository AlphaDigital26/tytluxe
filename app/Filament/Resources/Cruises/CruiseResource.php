<?php

namespace App\Filament\Resources\Cruises;

use App\Filament\Resources\Cruises\Pages\CreateCruise;
use App\Filament\Resources\Cruises\Pages\EditCruise;
use App\Filament\Resources\Cruises\Pages\ListCruises;
use App\Filament\Resources\Cruises\RelationManagers\CabinTypesRelationManager;
use App\Filament\Resources\Cruises\RelationManagers\ImagesRelationManager;
use App\Filament\Resources\Cruises\RelationManagers\ItineraryDaysRelationManager;
use App\Filament\Resources\Cruises\Schemas\CruiseForm;
use App\Filament\Resources\Cruises\Tables\CruisesTable;
use App\Models\Cruise;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class CruiseResource extends Resource
{
    protected static ?string $model = Cruise::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Cruise Catalog';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-paper-airplane';

    protected static ?string $navigationLabel = 'Cruises';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return CruiseForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CruisesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            ImagesRelationManager::class,
            CabinTypesRelationManager::class,
            ItineraryDaysRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCruises::route('/'),
            'create' => CreateCruise::route('/create'),
            'edit' => EditCruise::route('/{record}/edit'),
        ];
    }
}

