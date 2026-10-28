<?php

namespace App\Filament\Resources\FeaturedBlogDestinations\Schemas;

use Filament\Forms\Components\FileUpload;
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
                    // Only on create: changing a live page's slug breaks its old links.
                    ->afterStateUpdated(function ($state, $set, string $operation) {
                        if ($operation === 'create') {
                            $set('slug', Str::slug($state));
                        }
                    }),

                TextInput::make('slug')
                    ->label('Web address')
                    ->helperText('The last part of the page link, e.g. tytluxe.in/…/goa-beaches. Use hyphens instead of spaces.')
                    ->required()
                    ->maxLength(100)
                    ->unique(ignoreRecord: true),

                FileUpload::make('image_path')
                    ->label('Upload Image')
                    ->disk('public')
                    ->directory('destination-cards')
                    ->image()
                    ->imagePreviewHeight('160')
                    ->maxSize(8192)
                    ->helperText('Upload from your computer — takes priority over the URL below.'),

                TextInput::make('image_url')
                    ->label('Or: link to a photo on another website')
                    ->url()
                    ->maxLength(1000)
                    ->helperText('Paste an Unsplash or any public image URL. Used only if no image is uploaded above.'),

                TextInput::make('story_count')
                    ->label('Story Count')
                    ->numeric()
                    ->default(0)
                    ->helperText('Number shown below the destination name (e.g. "8 stories").'),

                TextInput::make('sort_order')
                    ->label('Position in the list')
                    ->numeric()
                    ->default(0)
                    ->helperText('Lower numbers appear first.'),

                Toggle::make('is_featured')
                    ->label('Featured (show on blog page)')
                    ->default(true),
            ]);
    }
}
