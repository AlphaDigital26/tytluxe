<?php

namespace App\Filament\Resources\Enquiries\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class EnquiriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('vertical')
                    ->badge(),
                TextColumn::make('reference_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('phone')
                    ->searchable(),
                TextColumn::make('email')
                    ->label('Email address')
                    ->searchable(),
                TextColumn::make('travel_date_from')
                    ->date()
                    ->sortable(),
                TextColumn::make('travel_date_to')
                    ->date()
                    ->sortable(),
                TextColumn::make('pax_adults')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('pax_children')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('notes')
                    ->searchable(),
                TextColumn::make('status')
                    ->badge(),
                TextColumn::make('assigned_agent_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('source')
                    ->badge(),
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
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
