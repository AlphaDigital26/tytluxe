<?php

namespace App\Filament\Resources\Packages\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class HighlightsRelationManager extends RelationManager
{
    protected static string $relationship = 'highlights';

    protected static ?string $title = 'Highlights';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('icon')
                ->label('FontAwesome Icon Class')
                ->placeholder('e.g. fa-solid fa-water')
                ->required(),
            TextInput::make('title')
                ->required(),
            Textarea::make('description')
                ->rows(3)
                ->columnSpanFull(),
            TextInput::make('sort_order')
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
                TextColumn::make('icon')->label('Icon Class'),
                TextColumn::make('title')->searchable(),
                TextColumn::make('sort_order')->sortable(),
            ])
            ->headerActions([CreateAction::make()])
            ->recordActions([EditAction::make(), DeleteAction::make()])
            ->toolbarActions([\Filament\Actions\BulkActionGroup::make([DeleteBulkAction::make()])]);
    }
}
