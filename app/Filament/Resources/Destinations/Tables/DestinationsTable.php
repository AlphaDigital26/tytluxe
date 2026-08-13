<?php

namespace App\Filament\Resources\Destinations\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DestinationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Destination')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('country')
                    ->label('Country')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('gray'),

                TextColumn::make('type')
                    ->label('Type')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'city'   => '🏙️ City',
                        'region' => '🗺️ Region',
                        'island' => '🏝️ Island',
                        default  => '📍 Other',
                    })
                    ->badge()
                    ->color('info'),

                TextColumn::make('for')
                    ->label('Used For')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'hotel'   => '🏨 Hotels',
                        'cruise'  => '🚢 Cruises',
                        'package' => '📦 Packages',
                        default   => ucfirst($state),
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'hotel'   => 'info',
                        'cruise'  => 'success',
                        'package' => 'warning',
                        default   => 'gray',
                    }),

                IconColumn::make('is_active')
                    ->label('Visible')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('lat')
                    ->label('Latitude')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('lng')
                    ->label('Longitude')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Added On')
                    ->date('d M Y')
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
            ])
            ->defaultSort('name', 'asc')
            ->striped()
            ->emptyStateHeading('No destinations yet')
            ->emptyStateDescription('Click "Add Destination" to add one.')
            ->emptyStateIcon('heroicon-o-map-pin');
    }
}
