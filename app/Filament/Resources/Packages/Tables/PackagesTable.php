<?php

namespace App\Filament\Resources\Packages\Tables;

use App\Filament\Resources\Packages\PackageBinActions;
use App\Models\Package;
use Filament\Actions\BulkActionGroup;
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
                    ->dateTime('j M Y, g:i A')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                // Bin only: when it was deleted and when it is erased.
                TextColumn::make('deleted_at')
                    ->label('Moved to Bin')
                    ->dateTime('j M Y, g:i A')
                    ->sortable()
                    ->visible(fn ($livewire) => ($livewire->activeTab ?? null) === 'bin'),
                TextColumn::make('erases_on')
                    ->label('Deleted forever on')
                    ->state(fn (Package $record) => $record->canBeErased() ? $record->erasesOn()?->format('j M Y') : 'Never — has bookings')
                    ->description(fn (Package $record) => $record->canBeErased() && $record->erasesOn() ? max(0, (int) ceil(now()->diffInDays($record->erasesOn(), false))).' days left' : null)
                    ->color(fn (Package $record) => $record->canBeErased() ? 'danger' : 'gray')
                    ->visible(fn ($livewire) => ($livewire->activeTab ?? null) === 'bin'),
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
                EditAction::make()->hidden(fn (Package $record) => $record->trashed()),
                PackageBinActions::moveToBin(),
                PackageBinActions::restore(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    PackageBinActions::moveToBinBulk(),
                    PackageBinActions::restoreBulk(),
                ]),
            ])
            ->emptyStateHeading(fn ($livewire) => ($livewire->activeTab ?? null) === 'bin' ? 'The Bin is empty' : 'No packages yet')
            ->emptyStateDescription(fn ($livewire) => ($livewire->activeTab ?? null) === 'bin'
                ? 'Deleted packages appear here for '.Package::BIN_DAYS.' days, so you can restore them.'
                : null);
    }
}
