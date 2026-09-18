<?php

namespace App\Filament\Resources\Destinations\Tables;

use App\Jobs\SyncDestinationHotels;
use App\Models\Destination;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DestinationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->withCount(['hotels', 'hotelsOnWebsite']))
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

                TextColumn::make('hotel_sync_status')
                    ->label('Hotels')
                    ->formatStateUsing(fn (?string $state, ?Destination $record): string => match ($state) {
                        'pending' => '⏳ Queued...',
                        'syncing' => '🔄 Syncing...',
                        'done'    => "✅ ".($record->hotel_sync_count ?? 0)." synced",
                        'failed'  => '❌ Failed',
                        default   => '—',
                    })
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'pending' => 'gray',
                        'syncing' => 'warning',
                        'done'    => 'success',
                        'failed'  => 'danger',
                        default   => 'gray',
                    }),

                TextColumn::make('hotels_on_website_count')
                    ->label('On Website')
                    ->formatStateUsing(function (?int $state, ?Destination $record): string {
                        if (! $record || ! in_array('hotel', (array) $record->for, true)) {
                            return '—';
                        }
                        $total = $record->hotel_sync_count ?? 0;
                        $live  = $state ?? 0;
                        return "🌏 {$live} / {$total}";
                    })
                    ->badge()
                    ->color(fn (?int $state, ?Destination $record): string => match (true) {
                        $state > 0  => 'success',
                        ($record?->hotel_sync_count ?? 0) > 0 => 'warning',
                        default => 'gray',
                    })
                    ->tooltip('Hotels visible on website ÷ Total synced from API'),

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
                Action::make('syncHotels')
                    ->label('Sync Hotels')
                    ->icon('heroicon-o-arrow-path')
                    ->color('gray')
                    ->visible(fn (?Destination $record): bool => $record && in_array('hotel', (array) $record->for, true))
                    ->action(function (Destination $record): void {
                        $record->update(['hotel_sync_status' => 'pending']);
                        SyncDestinationHotels::dispatch($record->id);

                        Notification::make()
                            ->title('Hotel sync started')
                            ->body("We're fetching hotels for {$record->name}. This page will update automatically once it's done.")
                            ->success()
                            ->send();
                    }),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->before(function (DeleteBulkAction $action, \Illuminate\Support\Collection $records) {
                            $blocked = $records->filter(
                                fn (Destination $destination) => $destination->hotels()->exists()
                                    || $destination->cruises()->exists()
                                    || $destination->packages()->exists()
                            );

                            if ($blocked->isEmpty()) {
                                return;
                            }

                            $names = $blocked->pluck('name')->implode(', ');

                            Notification::make()
                                ->title('Cannot delete some destinations')
                                ->body("{$names} still have hotels, cruises, or packages attached. Remove or reassign them first.")
                                ->danger()
                                ->send();

                            $action->halt();
                        }),
                ]),
            ])
            ->poll('5s')
            ->defaultSort('name', 'asc')
            ->striped()
            ->emptyStateHeading('No destinations yet')
            ->emptyStateDescription('Click "Add Destination" to add one.')
            ->emptyStateIcon('heroicon-o-map-pin');
    }
}
