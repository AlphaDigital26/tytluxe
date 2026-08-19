<?php

namespace App\Filament\Resources\Offers\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class OffersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Offer')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('discount_value')
                    ->label('Discount')
                    ->formatStateUsing(fn ($record) => $record->discount_type === 'percentage'
                        ? $record->discount_value . '%'
                        : '₹' . number_format($record->discount_value, 0))
                    ->badge()
                    ->color('success'),

                TextColumn::make('promo_code')
                    ->label('Promo Code')
                    ->default('—')
                    ->badge()
                    ->color('gray')
                    ->copyable()
                    ->copyMessage('Copied!')
                    ->searchable(),

                TextColumn::make('applies_to_vertical')
                    ->label('Applies To')
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'all'        => '🌍 All',
                        'hotel'      => '🏨 Hotels',
                        'flight'     => '✈️ Flights',
                        'cruise'     => '🚢 Cruises',
                        'package'    => '📦 Packages',
                        'staycation' => '🏡 Staycations',
                        default      => ucfirst($state),
                    })
                    ->badge()
                    ->color('info'),

                TextColumn::make('valid_from')
                    ->label('From')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('valid_to')
                    ->label('Until')
                    ->date('d M Y')
                    ->sortable()
                    ->color(fn ($record) => now()->gt($record->valid_to) ? 'danger' : 'success'),

                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('applies_to_vertical')
                    ->label('Category')
                    ->options([
                        'all'        => 'All',
                        'hotel'      => 'Hotels',
                        'flight'     => 'Flights',
                        'cruise'     => 'Cruises',
                        'package'    => 'Packages',
                        'staycation' => 'Staycations',
                    ]),

                TernaryFilter::make('is_active')
                    ->label('Status')
                    ->placeholder('All offers')
                    ->trueLabel('Active only')
                    ->falseLabel('Inactive only'),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('valid_from', 'desc')
            ->striped()
            ->emptyStateHeading('No offers yet')
            ->emptyStateDescription('Click "+ New Offer" to create your first offer.')
            ->emptyStateIcon('heroicon-o-tag');
    }
}
