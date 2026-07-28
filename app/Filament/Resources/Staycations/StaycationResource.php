<?php

namespace App\Filament\Resources\Staycations;

use App\Filament\Resources\Staycations\Pages\CreateStaycation;
use App\Filament\Resources\Staycations\Pages\EditStaycation;
use App\Filament\Resources\Staycations\Pages\ListStaycations;
use App\Filament\Resources\Staycations\Schemas\StaycationForm;
use App\Filament\Resources\Staycations\Tables\StaycationsTable;
use App\Models\Staycation;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class StaycationResource extends Resource
{
    protected static ?string $model = Staycation::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return StaycationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return StaycationsTable::configure($table);
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
            'index' => ListStaycations::route('/'),
            'create' => CreateStaycation::route('/create'),
            'edit' => EditStaycation::route('/{record}/edit'),
        ];
    }
}
