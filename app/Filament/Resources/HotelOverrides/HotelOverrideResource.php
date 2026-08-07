<?php

namespace App\Filament\Resources\HotelOverrides;

use App\Filament\Resources\HotelOverrides\Pages\CreateHotelOverride;
use App\Filament\Resources\HotelOverrides\Pages\EditHotelOverride;
use App\Filament\Resources\HotelOverrides\Pages\ListHotelOverrides;
use App\Filament\Resources\HotelOverrides\Schemas\HotelOverrideForm;
use App\Filament\Resources\HotelOverrides\Tables\HotelOverridesTable;
use App\Models\HotelOverride;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class HotelOverrideResource extends Resource
{
    protected static ?string $model = HotelOverride::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return HotelOverrideForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return HotelOverridesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListHotelOverrides::route('/'),
            'create' => CreateHotelOverride::route('/create'),
            'edit' => EditHotelOverride::route('/{record}/edit'),
        ];
    }
}
