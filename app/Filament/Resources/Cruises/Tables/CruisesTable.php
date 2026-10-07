<?php

namespace App\Filament\Resources\Cruises\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CruisesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Cruise')
                    ->description(fn ($record) => $record->cruise_line)
                    ->searchable(['title', 'cruise_line'])
                    ->weight('semibold'),
                TextColumn::make('category')
                    ->label('Type')
                    ->formatStateUsing(fn ($state) => ucwords(str_replace('_', ' ', (string) $state)))
                    ->badge(),
                TextColumn::make('duration_nights')
                    ->label('Nights')
                    ->sortable(),
                TextColumn::make('price_from')
                    ->label('Price from')
                    ->money('INR')
                    ->sortable(),
                ToggleColumn::make('is_active')
                    ->label('On website')
                    ->disabled(fn ($record) => ! auth('admin')->user()?->can('update', $record)),
                TextColumn::make('updated_at')
                    ->label('Last updated')
                    ->dateTime('j M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make()->label('Edit'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
