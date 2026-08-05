<?php

namespace App\Filament\Resources\Cruises\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ImagesRelationManager extends RelationManager
{
    protected static string $relationship = 'images';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Image Source')
                    ->description('Upload an image file OR paste an external URL — or both. The uploaded file takes priority on the frontend.')
                    ->columnSpanFull()
                    ->schema([
                        FileUpload::make('path')
                            ->label('Upload Image')
                            ->image()
                            ->disk('public')
                            ->directory('cruise-hero')
                            ->imagePreviewHeight('220')
                            ->maxSize(8192)
                            ->helperText('Upload a high-resolution image (max 8MB, JPG/PNG/WebP). Recommended: 1800×900px or wider.'),

                        TextInput::make('image_url')
                            ->label('Or: External Image URL')
                            ->url()
                            ->placeholder('https://images.unsplash.com/...')
                            ->default(null)
                            ->helperText('Used as a fallback when no uploaded file is present.'),
                    ]),

                TextInput::make('alt_text')
                    ->label('Alt Text')
                    ->placeholder('Brief description of the image (e.g. Ocean sunset from deck)')
                    ->default(null),

                TextInput::make('sort_order')
                    ->label('Sort Order')
                    ->numeric()
                    ->default(0)
                    ->helperText('Lower numbers appear first in the carousel.'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->defaultSort('sort_order')
            ->columns([
                TextColumn::make('sort_order')
                    ->label('#')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('path')
                    ->label('Uploaded File')
                    ->limit(50)
                    ->searchable(),
                TextColumn::make('image_url')
                    ->label('External URL')
                    ->limit(50),
                TextColumn::make('alt_text')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
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
