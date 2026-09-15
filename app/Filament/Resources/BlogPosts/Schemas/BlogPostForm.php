<?php

namespace App\Filament\Resources\BlogPosts\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class BlogPostForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Content')
                    ->icon('heroicon-o-document-text')
                    ->columns(2)
                    ->schema([
                        TextInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, $set) {
                                $set('slug', Str::slug($state));
                            })
                            ->columnSpanFull(),

                        TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->columnSpanFull(),

                        Textarea::make('excerpt')
                            ->helperText('Short description shown on the blog card.')
                            ->rows(3)
                            ->maxLength(500)
                            ->columnSpanFull(),

                        Textarea::make('body')
                            ->helperText('Full article body (HTML or plain text).')
                            ->rows(10)
                            ->columnSpanFull(),
                    ]),

                Section::make('Media')
                    ->icon('heroicon-o-photo')
                    ->schema([
                        FileUpload::make('cover_image_path')
                            ->label('Upload Cover Image')
                            ->disk('public')
                            ->image()
                            ->imagePreviewHeight('160')
                            ->maxSize(8192)
                            ->saveUploadedFileUsing(fn ($file) => app(\App\Services\ImageOptimizer::class)->optimizeAndSave($file, 'hero', 'blog-covers'))
                            ->helperText('Upload from your computer — takes priority over the URL below.')
                            ->columnSpanFull(),

                        TextInput::make('cover_image_url')
                            ->label('Or: External Image URL')
                            ->url()
                            ->maxLength(1000)
                            ->helperText('Paste an Unsplash or any public image URL. Used only if no image is uploaded above.')
                            ->columnSpanFull(),
                    ]),

                Section::make('Settings')
                    ->icon('heroicon-o-cog-6-tooth')
                    ->columns(2)
                    ->schema([
                        Select::make('blog_category_id')
                            ->label('Category')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),

                        TextInput::make('read_time_minutes')
                            ->label('Read Time (minutes)')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(120)
                            ->default(5),

                        DateTimePicker::make('published_at')
                            ->label('Publish Date')
                            ->nullable(),

                        TextInput::make('sort_order')
                            ->label('Sort Order')
                            ->numeric()
                            ->default(0)
                            ->helperText('Lower numbers appear first.'),

                        Toggle::make('is_trending')
                            ->label('Trending (show in hero carousel)')
                            ->helperText('Only trending posts appear in the homepage hero slider.')
                            ->default(false),

                        Toggle::make('is_active')
                            ->label('Active (visible on site)')
                            ->default(true),
                    ]),
            ]);
    }
}
