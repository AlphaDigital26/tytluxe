<?php

namespace App\Filament\Resources\Hotels\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class HotelsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Hotel Name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                
                TextColumn::make('destination.name')
                    ->label('Destination')
                    ->sortable()
                    ->searchable()
                    ->badge()
                    ->color('info'),

                TextColumn::make('category')
                    ->label('Category')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'beach_resort'    => '🏖️ Beach Resort',
                        'city_luxury'     => '🏙️ City Luxury',
                        'honeymoon'       => '💑 Honeymoon',
                        'family_friendly' => '👨‍👩‍👧 Family Friendly',
                        default           => ucfirst(str_replace('_', ' ', $state)),
                    })
                    ->color('gray'),

                TextColumn::make('star_rating')
                    ->label('Rating')
                    ->formatStateUsing(fn (string $state): string => str_repeat('⭐', (int)$state))
                    ->sortable(),

                TextColumn::make('price_from')
                    ->label('Starting Price')
                    ->money('INR')
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label('Visible')
                    ->boolean()
                    ->sortable(),

                IconColumn::make('is_featured')
                    ->label('Featured')
                    ->boolean()
                    ->sortable(),

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
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->emptyStateHeading('No hotels yet')
            ->emptyStateDescription('Click "+ New Hotel" to add the first one.')
            ->emptyStateIcon('heroicon-o-building-office-2');
    }
}
