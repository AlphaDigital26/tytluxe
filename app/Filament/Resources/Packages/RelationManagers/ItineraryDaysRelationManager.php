<?php

namespace App\Filament\Resources\Packages\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TagsInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ItineraryDaysRelationManager extends RelationManager
{
    protected static string $relationship = 'itineraryDays';

    protected static ?string $title = 'Itinerary Days';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('day_number')
                ->label('Day Number')
                ->required()
                ->numeric()
                ->default(1),
            TextInput::make('title')
                ->required()
                ->columnSpanFull(),
            Textarea::make('description')
                ->rows(5)
                ->columnSpanFull(),
            TagsInput::make('chips')
                ->label('Activity Chips (e.g. Jibhi Waterfall, Jalori Pass)')
                ->placeholder('Add activity and press Enter')
                ->columnSpanFull(),
            TextInput::make('sort_order')
                ->label('Sort Order')
                ->numeric()
                ->default(0),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->defaultSort('sort_order')
            ->columns([
                TextColumn::make('day_number')->label('Day')->sortable(),
                TextColumn::make('title')->searchable()->limit(50),
                TextColumn::make('sort_order')->sortable(),
            ])
            ->headerActions([CreateAction::make()])
            ->recordActions([EditAction::make(), DeleteAction::make()])
            ->toolbarActions([\Filament\Actions\BulkActionGroup::make([DeleteBulkAction::make()])]);
    }
}
