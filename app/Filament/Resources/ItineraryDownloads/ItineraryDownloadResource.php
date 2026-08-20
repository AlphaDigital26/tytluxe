<?php

namespace App\Filament\Resources\ItineraryDownloads;

use App\Filament\Resources\ItineraryDownloads\Pages\CreateItineraryDownload;
use App\Filament\Resources\ItineraryDownloads\Pages\EditItineraryDownload;
use App\Filament\Resources\ItineraryDownloads\Pages\ListItineraryDownloads;
use App\Filament\Resources\ItineraryDownloads\Pages\ViewItineraryDownload;
use App\Filament\Resources\ItineraryDownloads\Schemas\ItineraryDownloadForm;
use App\Filament\Resources\ItineraryDownloads\Schemas\ItineraryDownloadInfolist;
use App\Filament\Resources\ItineraryDownloads\Tables\ItineraryDownloadsTable;
use App\Models\ItineraryDownload;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ItineraryDownloadResource extends Resource
{
    protected static ?string $model = ItineraryDownload::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-arrow-down-tray';

    protected static ?int $navigationSort = 11;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return ItineraryDownloadForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ItineraryDownloadInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ItineraryDownloadsTable::configure($table);
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
            'index' => ListItineraryDownloads::route('/'),
        ];
    }
}
