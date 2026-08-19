<?php

namespace App\Filament\Resources\Hotels\RelationManagers;

use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TagsInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Table;

class RoomTypesRelationManager extends RelationManager
{
    protected static string $relationship = 'roomTypes';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Room Details')->schema([
                    TextInput::make('name')
                        ->required()
                        ->maxLength(255)
                        ->columnSpanFull(),
                    FileUpload::make('image_path')
                        ->label('Main Thumbnail')
                        ->image()
                        ->saveUploadedFileUsing(fn ($file) => app(\App\Services\ImageOptimizer::class)->optimizeAndSave($file, 'thumbnail', 'room-images')),
                    FileUpload::make('images')
                        ->label('Gallery Images (Multiple)')
                        ->multiple()
                        ->image()
                        ->reorderable()
                        ->saveUploadedFileUsing(fn ($file) => app(\App\Services\ImageOptimizer::class)->optimizeAndSave($file, 'thumbnail', 'room-images')),
                    Textarea::make('description')
                        ->rows(3)
                        ->columnSpanFull(),
                ])->columns(2),

                Section::make('Specifications & Occupancy')->schema([
                    TextInput::make('room_size')
                        ->placeholder('e.g. 300 sq.ft'),
                    TextInput::make('bed_type')
                        ->placeholder('e.g. 1 King Bed'),
                    TextInput::make('occupancy_adults')
                        ->required()
                        ->numeric()
                        ->default(2),
                    TextInput::make('occupancy_children')
                        ->required()
                        ->numeric()
                        ->default(0),
                ])->columns(2),

                Section::make('Pricing & Settings')->schema([
                    TextInput::make('price_per_night')
                        ->required()
                        ->numeric()
                        ->prefix('₹'),
                    Select::make('cancellation_policy')
                        ->options([
                            'free_cancellation' => 'Free cancellation',
                            'non_refundable' => 'Non refundable',
                            'partial' => 'Partial',
                        ])
                        ->required(),
                    TagsInput::make('inclusions')
                        ->placeholder('Add inclusions (e.g. Free Breakfast, Free WiFi)')
                        ->columnSpanFull(),
                    Toggle::make('is_active')
                        ->label('Room is active (Visible on website)')
                        ->default(true)
                        ->required()
                        ->columnSpanFull(),
                ])->columns(2),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                ImageColumn::make('image_path')->circular(),
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('price_per_night')
                    ->money('inr')
                    ->sortable(),
                TextColumn::make('occupancy_adults')
                    ->label('Adults')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('occupancy_children')
                    ->label('Children')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('cancellation_policy')
                    ->badge(),
                IconColumn::make('is_active')
                    ->boolean(),
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
