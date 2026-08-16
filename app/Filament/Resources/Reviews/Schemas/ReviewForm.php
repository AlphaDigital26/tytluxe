<?php

namespace App\Filament\Resources\Reviews\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ReviewForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('user_id')
                    ->numeric()
                    ->default(null),
                Select::make('vertical')
                    ->options([
            'hotel' => 'Hotel',
            'cruise' => 'Cruise',
            'staycation' => 'Staycation',
            'package' => 'Package',
            'general' => 'General',
        ])
                    ->default('general')
                    ->required(),
                TextInput::make('reference_id')
                    ->numeric()
                    ->default(null),
                TextInput::make('author_name')
                    ->required(),
                TextInput::make('author_location')
                    ->default(null),
                TextInput::make('avatar_path')
                    ->default(null),
                TextInput::make('title')
                    ->maxLength(255)
                    ->default(null),
                TextInput::make('rating')
                    ->numeric()
                    ->default(null),
                TextInput::make('rating_guide')->numeric()->default(null),
                TextInput::make('rating_accommodation')->numeric()->default(null),
                TextInput::make('rating_value')->numeric()->default(null),
                TextInput::make('rating_itinerary')->numeric()->default(null),
                Textarea::make('body')
                    ->required()
                    ->columnSpanFull(),
                \Filament\Forms\Components\FileUpload::make('images')
                    ->multiple()
                    ->image()
                    ->directory('reviews')
                    ->columnSpanFull(),
                Textarea::make('admin_reply')
                    ->columnSpanFull(),
                Toggle::make('is_published')
                    ->required(),
                Toggle::make('is_featured')
                    ->default(false),
            ]);
    }
}
