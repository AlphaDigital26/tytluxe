<?php

namespace App\Filament\Resources\BlogCategories\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class BlogCategoryForm
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
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255)
                    ->helperText('Used for filtering on the blog page.'),

                TextInput::make('sort_order')
                    ->label('Position in the list')
                    ->numeric()
                    ->default(0)
                    ->helperText('Lower numbers appear first in the filter bar.'),

                Toggle::make('is_active')
                    ->label('Active (visible in filter bar)')
                    ->default(true),
            ]);
    }
}
