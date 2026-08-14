<?php

namespace App\Filament\Resources\FeaturedBlogDestinations\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class FeaturedBlogDestinationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(100)
                    ->live(onBlur: true)
                    ->afterStateUpdated(function ($state, $set) {
                        $set('slug', Str::slug($state));
                    }),

                TextInput::make('slug')
                    ->required()
                    ->maxLength(100)
                    ->unique(ignoreRecord: true),

                TextInput::make('image_url')
                    ->label('Image URL')
                    ->url()
                    ->maxLength(1000)
                    ->helperText('Paste an Unsplash or any public image URL for the destination card.'),

                TextInput::make('story_count')
                    ->label('Story Count')
                    ->numeric()
                    ->default(0)
                    ->helperText('Number shown below the destination name (e.g. "8 stories").'),

                TextInput::make('sort_order')
                    ->label('Sort Order')
                    ->numeric()
                    ->default(0)
                    ->helperText('Lower numbers appear first.'),

                Toggle::make('is_featured')
                    ->label('Featured (show on blog page)')
                    ->default(true),
            ]);
    }
}
