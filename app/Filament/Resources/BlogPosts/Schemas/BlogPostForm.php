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
                            // Only on create: changing a live post's slug breaks its old links.
                            ->afterStateUpdated(function ($state, $set, string $operation) {
                                if ($operation === 'create') {
                                    $set('slug', Str::slug($state));
                                }
                            })
                            ->columnSpanFull(),

                        TextInput::make('slug')
                            ->label('Web address')
                            ->helperText('The last part of the page link, e.g. tytluxe.in/…/goa-beaches. Use hyphens instead of spaces.')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->columnSpanFull(),

                        Textarea::make('excerpt')
                            ->label('Short summary')
                            ->helperText('Two or three lines shown on the blog card, under the title.')
                            ->rows(3)
                            ->maxLength(500)
                            ->columnSpanFull(),

                        Textarea::make('body')
                            ->label('Article')
                            ->helperText('The full article. Plain text is fine; leave a blank line between paragraphs.')
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
                            ->label('Or: link to a photo on another website')
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
                            ->required()
                            ->helperText('Pick one, or press + to add a new category without leaving this post.')
                            // Staff used to hit an empty list here and had to leave the post
                            // half-written to make a category under Blog Categories.
                            ->createOptionForm([
                                TextInput::make('name')->label('Category name')->required()->maxLength(100),
                            ])
                            ->createOptionUsing(function (array $data): int {
                                $slug = \Illuminate\Support\Str::slug($data['name']);
                                $base = $slug;
                                for ($n = 2; \App\Models\BlogCategory::where('slug', $slug)->exists(); $n++) {
                                    $slug = $base.'-'.$n;
                                }

                                return \App\Models\BlogCategory::create(['name' => $data['name'], 'slug' => $slug, 'is_active' => true])->getKey();
                            }),

                        TextInput::make('read_time_minutes')
                            ->label('Read Time (minutes)')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(120)
                            ->default(5),

                        DateTimePicker::make('published_at')
                            ->label('Publish Date')
                            ->default(now())
                            ->helperText('The post appears on the website from this date and time. Pick a later date to schedule it.')
                            ->nullable(),

                        TextInput::make('sort_order')
                            ->label('Position in the list')
                            ->numeric()
                            ->default(0)
                            ->helperText('Leave at 0 and the newest posts show first. Give a post a lower number (e.g. -1) to pin it above the rest.'),

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
