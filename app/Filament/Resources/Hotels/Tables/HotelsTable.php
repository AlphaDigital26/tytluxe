<?php

namespace App\Filament\Resources\Hotels\Tables;

use App\Models\Hotel;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class HotelsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['destination', 'images' => Hotel::visibleImagesConstraint()]))
            ->columns([
                ImageColumn::make('thumbnail')
                    ->label('Photo')
                    ->state(function (Hotel $record): ?string {
                        $img = $record->images->first();
                        if (! $img || ! $img->path) {
                            return null;
                        }
                        if (str_starts_with($img->path, 'http')) {
                            return $img->path;
                        }

                        return asset('storage/' . $img->path);
                    })
                    ->circular(),

                TextColumn::make('title')
                    ->label('Hotel Name')
                    ->searchable(['title', 'chain_name', 'address'])
                    ->sortable()
                    ->weight('bold')
                    ->description(fn (Hotel $record): ?string => $record->chain_name ?: ($record->address ? Str::limit($record->address, 40) : null)),

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
                    ->formatStateUsing(fn ($state): string => $state ? str_repeat('⭐', (int) $state) : '—')
                    ->sortable(),

                TextColumn::make('source')
                    ->label('Source')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'tripjack' => 'TripJack 🔄',
                        'manual'   => 'Manual ✍️',
                        default    => ucfirst((string) $state),
                    })
                    ->color(fn (?string $state): string => match ($state) {
                        'tripjack' => 'info',
                        'manual'   => 'success',
                        default    => 'gray',
                    }),

                ToggleColumn::make('is_active')
                    ->label('Visible')
                    ->sortable()
                    ->tooltip('Turn on to show on website, off to hide'),

                ToggleColumn::make('is_featured')
                    ->label('Featured')
                    ->sortable()
                    ->tooltip('Highlight on homepage & destination showcases'),

                TextColumn::make('created_at')
                    ->label('Added On')
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('destination_id')
                    ->label('Destination')
                    ->relationship('destination', 'name', fn ($query) => $query->whereJsonContains('for', 'hotel')->orderBy('name'))
                    ->searchable()
                    ->preload(),

                SelectFilter::make('star_rating')
                    ->label('Star Rating')
                    ->options([
                        5 => '⭐⭐⭐⭐⭐ 5 Stars',
                        4 => '⭐⭐⭐⭐ 4 Stars',
                        3 => '⭐⭐⭐ 3 Stars',
                        2 => '⭐⭐ 2 Stars',
                        1 => '⭐ 1 Star',
                    ]),

                SelectFilter::make('category')
                    ->label('Category')
                    ->options([
                        'beach_resort'    => '🏖️ Beach Resort',
                        'city_luxury'     => '🏙️ City Luxury',
                        'honeymoon'       => '💑 Honeymoon',
                        'family_friendly' => '👨‍👩‍👧 Family Friendly',
                    ]),

                SelectFilter::make('source')
                    ->label('Source')
                    ->options([
                        'tripjack' => '🔄 Synced from TripJack',
                        'manual'   => '✍️ Manually Added',
                    ]),

                TernaryFilter::make('is_active')
                    ->label('Website Visibility')
                    ->placeholder('All hotels')
                    ->trueLabel('Visible on website only')
                    ->falseLabel('Hidden hotels only'),

                TernaryFilter::make('is_featured')
                    ->label('Featured Status')
                    ->placeholder('All hotels')
                    ->trueLabel('Featured only')
                    ->falseLabel('Not featured'),
            ])
            ->recordActions([
                Action::make('view_live')
                    ->label('View Live')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->color('gray')
                    ->url(fn (Hotel $record) => route('hotel.details', ['slug' => $record->slug]))
                    ->openUrlInNewTab()
                    ->tooltip('Open live hotel page on website in a new tab'),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->emptyStateHeading('No hotels found')
            ->emptyStateDescription('Try clearing your filters or click "+ New Hotel" to add one.')
            ->emptyStateIcon('heroicon-o-building-office-2');
    }
}
