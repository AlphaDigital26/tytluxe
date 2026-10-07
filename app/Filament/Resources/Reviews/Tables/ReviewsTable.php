<?php

namespace App\Filament\Resources\Reviews\Tables;

use App\Models\Review;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Collection;

class ReviewsTable
{
    public const VERTICALS = [
        'general' => 'TYT Luxe',
        'hotel' => 'Hotel',
        'package' => 'Package',
        'cruise' => 'Cruise',
        'staycation' => 'Staycation',
    ];

    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('author_name')
                    ->label('Guest')
                    ->description(fn (Review $record) => $record->author_location)
                    ->searchable(),
                TextColumn::make('title')
                    ->label('Review')
                    ->description(fn (Review $record) => \Illuminate\Support\Str::limit((string) $record->body, 80))
                    ->wrap()
                    ->searchable(['title', 'body']),
                TextColumn::make('rating')
                    ->label('Rating')
                    ->formatStateUsing(fn ($state) => $state ? str_repeat('★', (int) $state).str_repeat('☆', 5 - (int) $state) : '—')
                    ->color('warning')
                    ->sortable(),
                TextColumn::make('vertical')
                    ->label('About')
                    ->badge()
                    ->formatStateUsing(function (Review $record) {
                        $label = self::VERTICALS[$record->vertical] ?? ucfirst((string) $record->vertical);
                        $title = $record->reference_id ? $record->verticalModel()?->first()?->title : null;

                        return $title ? $label.': '.$title : $label;
                    }),
                ToggleColumn::make('is_published')
                    ->label('On website')
                    ->disabled(fn (Review $record) => ! auth('admin')->user()?->can('update', $record)),
                ToggleColumn::make('is_featured')
                    ->label('Featured')
                    ->disabled(fn (Review $record) => ! auth('admin')->user()?->can('update', $record)),
                TextColumn::make('created_at')
                    ->label('Received')
                    ->dateTime('j M Y')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                TernaryFilter::make('is_published')
                    ->label('Shown on website')
                    ->trueLabel('Shown only')
                    ->falseLabel('Waiting for approval'),
                SelectFilter::make('vertical')
                    ->label('About')
                    ->options(self::VERTICALS),
                SelectFilter::make('rating')
                    ->label('Rating')
                    ->options([5 => '5 stars', 4 => '4 stars', 3 => '3 stars', 2 => '2 stars', 1 => '1 star']),
            ])
            ->recordActions([
                EditAction::make()->label('Open'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('publish')
                        ->label('Show on website')
                        ->icon('heroicon-o-eye')
                        ->action(fn (Collection $records) => $records->each->update(['is_published' => true])),
                    BulkAction::make('unpublish')
                        ->label('Hide from website')
                        ->icon('heroicon-o-eye-slash')
                        ->action(fn (Collection $records) => $records->each->update(['is_published' => false])),
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('No reviews yet');
    }
}
