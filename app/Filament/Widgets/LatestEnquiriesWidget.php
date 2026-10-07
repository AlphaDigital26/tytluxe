<?php

namespace App\Filament\Widgets;

use App\Models\Enquiry;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestEnquiriesWidget extends BaseWidget
{
    protected static ?int $sort = 9;

    /** Load with the page (one request) instead of one request per widget. */
    protected static bool $isLazy = false;
    protected static ?string $heading = 'Latest enquiries';
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(Enquiry::query()->latest()->limit(5))
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Guest Name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('phone')
                    ->label('Contact')
                    ->searchable(),
                Tables\Columns\TextColumn::make('vertical')
                    ->label('Looking for')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'flight' => 'info',
                        'hotel' => 'success',
                        'package' => 'warning',
                        'cruise' => 'primary',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => \App\Filament\Resources\Enquiries\Tables\EnquiriesTable::CATEGORIES[$state] ?? ucfirst($state)),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'new' => 'danger',
                        'contacted' => 'warning',
                        'quoted' => 'info',
                        'converted' => 'success',
                        default => 'gray',
                    })
                    ->label('Stage')
                    ->formatStateUsing(fn (string $state): string => \App\Filament\Resources\Enquiries\Tables\EnquiriesTable::STATUSES[$state] ?? ucfirst($state)),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Received')
                    ->dateTime('M j, Y h:i A')
                    ->sortable(),
            ])
            ->actions([
                \Filament\Actions\Action::make('view')
                    ->label('Open')
                    ->url(fn (Enquiry $record): string => \App\Filament\Resources\Enquiries\EnquiryResource::getUrl('index') . '?tableFilters[name][value]=' . urlencode($record->name))
                    ->icon('heroicon-m-eye')
            ])
            ->paginated(false);
    }
}
