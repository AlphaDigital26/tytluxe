<?php

namespace App\Filament\Resources\Amenities\Tables;

use App\Models\Amenity;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AmenitiesTable
{
    public static function configure(Table $table): Table
    {
        return $table

            // ── Columns ──────────────────────────────────────────────────
            ->columns([

                TextColumn::make('icon')
                    ->label('Icon')
                    ->default('—')
                    ->width('60px'),

                TextColumn::make('name')
                    ->label('Amenity Name')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),

                TextColumn::make('type')
                    ->label('Section')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'hotel'   => '🏨  Hotels',
                        'cruise'  => '🚢  Cruises',
                        'package' => '📦  Packages',
                        default   => ucfirst($state),
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'hotel'   => 'info',
                        'cruise'  => 'success',
                        'package' => 'warning',
                        default   => 'gray',
                    })
                    ->sortable(),

                // Shown by default: tells staff which entries matter before
                // they tidy the list.
                TextColumn::make('hotels_count')
                    ->label('Used by hotels')
                    ->counts('hotels')
                    ->badge()
                    ->color(fn ($state) => $state ? 'gray' : 'warning')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('Added on')
                    ->date('j M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

            ])

            ->filters([
                Filter::make('unused')
                    ->label('Not used by any hotel, cruise or package')
                    ->query(fn (Builder $query) => $query->doesntHave('hotels')->doesntHave('cruises')->doesntHave('packages')),
            ])

            // ── Row actions ───────────────────────────────────────────────
            ->recordActions([
                EditAction::make()
                    ->label('Edit'),
                DeleteAction::make()
                    ->label('Delete'),
            ])

            // ── Bulk actions ──────────────────────────────────────────────
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])

            ->defaultSort('name')
            ->striped()
            ->emptyStateHeading('No amenities yet')
            ->emptyStateDescription('Click "+ Add Amenity" to create your first one for this section.')
            ->emptyStateIcon('heroicon-o-check-badge');
    }
}
