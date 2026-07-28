<?php

namespace App\Filament\Resources\Hotels\RelationManagers;

use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RoomTypesRelationManager extends RelationManager
{
    protected static string $relationship = 'roomTypes';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('occupancy_adults')
                    ->required()
                    ->numeric(),
                TextInput::make('occupancy_children')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('price_per_night')
                    ->required()
                    ->numeric(),
                Select::make('cancellation_policy')
                    ->options([
            'free_cancellation' => 'Free cancellation',
            'non_refundable' => 'Non refundable',
            'partial' => 'Partial',
        ])
                    ->required(),
                TextInput::make('tripjack_room_code')
                    ->default(null),
                Toggle::make('is_active')
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('occupancy_adults')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('occupancy_children')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('price_per_night')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('cancellation_policy')
                    ->badge(),
                TextColumn::make('tripjack_room_code')
                    ->searchable(),
                IconColumn::make('is_active')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(),
                AssociateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DissociateAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DissociateBulkAction::make(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
