<?php

namespace App\Filament\Resources\Cruises\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CabinTypesRelationManager extends RelationManager
{
    protected static string $relationship = 'cabinTypes';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->placeholder('e.g. Chairman\'s Suite'),

                TextInput::make('tier_label')
                    ->placeholder('e.g. Most Luxurious')
                    ->default(null),

                Textarea::make('description')
                    ->default(null)
                    ->columnSpanFull(),

                TextInput::make('size_info')
                    ->placeholder('e.g. Cabin: 596 Sq. Ft | Balcony: 222 Sq. Ft')
                    ->default(null)
                    ->columnSpanFull(),

                TextInput::make('price_from')
                    ->numeric()
                    ->prefix('₹')
                    ->default(null),

                Section::make('Cabin Image')
                    ->description('Upload an image OR provide an external URL — or both. The uploaded file takes priority.')
                    ->columnSpanFull()
                    ->schema([
                        FileUpload::make('image_path')
->disk('public')
                            ->label('Upload Image')
                            ->image()
                            ->saveUploadedFileUsing(fn ($file) => app(\App\Services\ImageOptimizer::class)->optimizeAndSave($file, 'thumbnail', 'cruise-cabins'))
                            ->imagePreviewHeight('200')
                            ->maxSize(4096)
                            ->helperText('Upload a JPG/PNG/WebP (max 4MB). This takes priority over the URL below.'),

                        TextInput::make('image_url')
                            ->label('Or: External Image URL')
                            ->url()
                            ->placeholder('https://images.unsplash.com/...')
                            ->default(null)
                            ->helperText('Used only when no uploaded file is present.'),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('tier_label')
                    ->label('Tier')
                    ->searchable(),
                TextColumn::make('price_from')
                    ->label('Price From (₹)')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('size_info')
                    ->label('Size')
                    ->limit(40),
                TextColumn::make('created_at')
                    ->dateTime('M j, Y h:i A')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime('M j, Y h:i A')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
