<?php

namespace App\Filament\Resources\Enquiries\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Filters\SelectFilter;

class EnquiriesTable
{
    public const STATUSES = [
        'new' => 'New — not contacted yet',
        'contacted' => 'Contacted',
        'quoted' => 'Quote sent',
        'converted' => 'Booked',
        'closed' => 'Closed',
    ];

    public const CATEGORIES = [
        'hotel' => 'Hotel',
        'flight' => 'Flight',
        'package' => 'Holiday package',
        'cruise' => 'Cruise',
        'staycation' => 'Staycation',
        'general' => 'General',
    ];

    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label('Received On')
                    ->dateTime('M d, Y h:i A')
                    ->sortable()
                    ->toggleable(),
                    
                TextColumn::make('name')
                    ->label('Guest Details')
                    ->searchable()
                    ->sortable()
                    ->description(fn ($record) => trim("{$record->phone} " . ($record->email ? "| {$record->email}" : "")))
                    ->weight('semibold'),
                    
                TextColumn::make('vertical')
                    ->label('Category')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'hotel' => 'primary',
                        'flight' => 'info',
                        'package' => 'success',
                        'cruise' => 'warning',
                        'general' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => self::CATEGORIES[$state] ?? ucfirst($state))
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Stage')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'new' => 'danger',
                        'contacted', 'quoted' => 'warning',
                        'converted' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => self::STATUSES[$state] ?? ucfirst($state))
                    ->sortable(),
                    
                // --- More detail (switch on from the columns menu) ---

                TextColumn::make('phone')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('email')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('travel_date_from')
                    ->label('Travel from')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('travel_date_to')
                    ->label('Travel until')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('pax_adults')
                    ->label('Adults')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('pax_children')
                    ->label('Children')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('notes')
                    ->label('Guest request')
                    ->wrap()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('assignedAgent.name')
                    ->label('Resolved By')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('source')
                    ->label('Came in via')
                    ->badge()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Last updated')
                    ->dateTime('M j, Y h:i A')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('admin_notes')
                    ->label('Resolution Notes')
                    ->wrap()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('resolved_at')
                    ->label('Resolved on')
                    ->dateTime('M j, Y h:i A')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')->label('Stage')->options(self::STATUSES),
                SelectFilter::make('vertical')->label('Looking for')->options(self::CATEGORIES),
                SelectFilter::make('source')->label('Came in via')->options(['web' => 'Website', 'whatsapp' => 'WhatsApp', 'phone' => 'Phone call']),
            ])
            ->recordActions([
                ViewAction::make(),
                Action::make('updateStage')
                    ->label('Update stage')
                    ->icon('heroicon-o-arrow-right-circle')
                    ->color('gray')
                    ->hidden(fn ($record) => $record->status === 'closed')
                    ->schema([
                        Select::make('status')
                            ->label('Stage')
                            ->options(array_diff_key(self::STATUSES, ['closed' => true]))
                            ->default(fn ($record) => $record->status)
                            ->required(),
                    ])
                    ->action(function (array $data, $record): void {
                        $record->update([
                            'status' => $data['status'],
                            'assigned_agent_id' => $record->assigned_agent_id ?? auth('admin')->id(),
                        ]);
                    }),
                Action::make('resolve')
                    ->label('Close')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->hidden(fn ($record) => $record->status === 'closed')
                    ->form([
                        Textarea::make('admin_notes')
                            ->label('Resolution Comments')
                            ->required()
                            ->maxLength(1000)
                            ->helperText('How was this enquiry handled? e.g. "Booked 3 nights at Taj, paid" or "Guest not interested".'),
                    ])
                    ->action(function (array $data, $record): void {
                        $record->update([
                            'status' => 'closed',
                            'admin_notes' => $data['admin_notes'],
                            'resolved_at' => now(),
                            'assigned_agent_id' => auth('admin')->id(),
                        ]);
                    }),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    \pxlrbt\FilamentExcel\Actions\ExportBulkAction::make()
                        ->exports([
                            \pxlrbt\FilamentExcel\Exports\ExcelExport::make()->fromModel(),
                        ]),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
