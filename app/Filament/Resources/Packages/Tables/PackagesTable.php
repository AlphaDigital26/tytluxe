<?php

namespace App\Filament\Resources\Packages\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PackagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('destination.name')
                    ->label('Destination')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('region_type')
                    ->label('Region')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'domestic'      => 'success',
                        'international' => 'info',
                        'spiritual'     => 'purple',
                        default         => 'gray',
                    }),
                TextColumn::make('tour_type')
                    ->label('Type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'group'  => 'warning',
                        'custom' => 'primary',
                        default  => 'gray',
                    }),
                TextColumn::make('duration_nights')
                    ->label('Nights')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('price_from')
                    ->label('Price From')
                    ->money('INR')
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->dateTime('M j, Y h:i A')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('region_type')
                    ->label('Region')
                    ->options(['domestic' => 'Domestic', 'international' => 'International', 'spiritual' => 'Spiritual']),
                SelectFilter::make('tour_type')
                    ->label('Tour Type')
                    ->options(['group' => 'Group', 'custom' => 'Custom']),
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
