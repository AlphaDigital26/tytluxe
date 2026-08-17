<?php

namespace App\Filament\Resources\FeaturedBlogDestinations;

use App\Filament\Resources\FeaturedBlogDestinations\Pages\CreateFeaturedBlogDestination;
use App\Filament\Resources\FeaturedBlogDestinations\Pages\EditFeaturedBlogDestination;
use App\Filament\Resources\FeaturedBlogDestinations\Pages\ListFeaturedBlogDestinations;
use App\Filament\Resources\FeaturedBlogDestinations\Schemas\FeaturedBlogDestinationForm;
use App\Filament\Resources\FeaturedBlogDestinations\Tables\FeaturedBlogDestinationsTable;
use App\Models\FeaturedBlogDestination;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class FeaturedBlogDestinationResource extends Resource
{
    protected static ?string $model = FeaturedBlogDestination::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-map-pin';

    protected static string|\UnitEnum|null $navigationGroup = 'Travel Journal';

    protected static ?string $navigationLabel = 'Featured Destinations';

    protected static ?int $navigationSort = 140;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return FeaturedBlogDestinationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FeaturedBlogDestinationsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListFeaturedBlogDestinations::route('/'),
            'create' => CreateFeaturedBlogDestination::route('/create'),
            'edit'   => EditFeaturedBlogDestination::route('/{record}/edit'),
        ];
    }
}
