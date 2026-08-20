<?php

namespace App\Filament\Resources\Offers\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
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

                ImageColumn::make('image_path')
                    ->label('')
                    ->disk('public')
                    ->width(56)
                    ->height(42)
                    ->defaultImageUrl(fn ($record) => $record->image_url ?: null)
                    ->extraImgAttributes(['style' => 'object-fit:cover;border-radius:6px;'])
                    ->toggleable(),

                TextColumn::make('title')
                    ->label('Offer')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->description(fn ($record) => collect([$record->destination, $record->duration, $record->subtitle])->filter()->implode(' · ')),

                TextColumn::make('category_key')
                    ->label('Category')
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'flights'  => '✈️  Flights',
                        'hotels'   => '🏨  Hotels',
                        'cruises'  => '🚢  Cruises',
                        'packages' => '📦  Packages',
                        default    => ucfirst($state),
                    })
                    ->badge()
                    ->color('info')
                    ->sortable(),

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

                TextColumn::make('valid_from')
                    ->label('Starts')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('valid_to')
                    ->label('Expires')
                    ->date('d M Y')
                    ->sortable()
                    ->color(fn ($record) => now()->gt($record->valid_to) ? 'danger' : 'success'),

                IconColumn::make('coming_soon')
                    ->label('Coming Soon')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),

                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('sort_order')
                    ->label('Order')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('category_key')
                    ->label('Category')
                    ->options([
                        'flights'  => '✈️  Flights',
                        'hotels'   => '🏨  Hotels',
                        'cruises'  => '🚢  Cruises',
                        'packages' => '📦  Packages',
                    ]),

                TernaryFilter::make('is_active')
                    ->label('Status')
                    ->placeholder('All offers')
                    ->trueLabel('Active only')
                    ->falseLabel('Inactive only'),

                TernaryFilter::make('coming_soon')
                    ->label('Coming Soon')
                    ->placeholder('All')
                    ->trueLabel('Coming soon only')
                    ->falseLabel('Live offers only'),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('sort_order', 'asc')
            ->striped()
            ->emptyStateHeading('No offers yet')
            ->emptyStateDescription('Click "+ New Offer" to add your first offer card.')
            ->emptyStateIcon('heroicon-o-tag');
    }
}
