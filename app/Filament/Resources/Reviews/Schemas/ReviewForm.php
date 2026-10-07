<?php

namespace App\Filament\Resources\Reviews\Schemas;

use App\Models\Cruise;
use App\Models\Hotel;
use App\Models\Package;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

/**
 * Review moderation in plain words: what the review is about is picked by
 * name (stored as vertical + reference_id), ratings are 1–5 star pickers,
 * and the guest's account link (user_id) is kept but never typed by hand.
 */
class ReviewForm
{
    protected static function stars(): array
    {
        return [5 => '★★★★★ Excellent', 4 => '★★★★ Very good', 3 => '★★★ Good', 2 => '★★ Fair', 1 => '★ Poor'];
    }

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('What is this review about?')
                    ->columns(2)
                    ->schema([
                        Select::make('vertical')
                            ->label('Review for')
                            ->options([
                                'general' => 'TYT Luxe in general',
                                'hotel' => 'A hotel',
                                'package' => 'A holiday package',
                                'cruise' => 'A cruise',
                                'staycation' => 'A staycation',
                            ])
                            ->default('general')
                            ->required()
                            ->live()
                            ->afterStateUpdated(fn (Set $set) => $set('reference_id', null)),
                        Select::make('reference_id')
                            ->label(fn (Get $get) => match ($get('vertical')) {
                                'hotel' => 'Which hotel?',
                                'package' => 'Which package?',
                                'cruise' => 'Which cruise?',
                                default => 'Which one?',
                            })
                            ->options(fn (Get $get) => match ($get('vertical')) {
                                'hotel' => Hotel::orderBy('title')->pluck('title', 'id')->all(),
                                'package' => Package::orderBy('title')->pluck('title', 'id')->all(),
                                'cruise' => Cruise::orderBy('title')->pluck('title', 'id')->all(),
                                default => [],
                            })
                            ->searchable()
                            ->visible(fn (Get $get) => in_array($get('vertical'), ['hotel', 'package', 'cruise'], true))
                            ->required(fn (Get $get) => in_array($get('vertical'), ['hotel', 'package', 'cruise'], true)),
                    ]),

                Section::make('The review')
                    ->columns(2)
                    ->schema([
                        TextInput::make('author_name')
                            ->label('Guest name')
                            ->required(),
                        TextInput::make('author_location')
                            ->label('Guest city / country')
                            ->placeholder('e.g. Mumbai'),
                        TextInput::make('title')
                            ->label('Headline')
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Select::make('rating')
                            ->label('Overall rating')
                            ->options(self::stars())
                            ->required(),
                        Textarea::make('body')
                            ->label('Review text')
                            ->rows(5)
                            ->required()
                            ->columnSpanFull(),
                        FileUpload::make('images')
                            ->label('Guest photos')
                            ->disk('public')
                            ->multiple()
                            ->image()
                            ->saveUploadedFileUsing(fn ($file) => app(\App\Services\ImageOptimizer::class)->optimizeAndSave($file, 'thumbnail', 'reviews'))
                            ->columnSpanFull(),
                    ]),

                Section::make('Detailed ratings (package reviews)')
                    ->description('Optional — only shown on holiday package reviews.')
                    ->columns(4)
                    ->collapsed()
                    ->visible(fn (Get $get) => $get('vertical') === 'package')
                    ->schema([
                        Select::make('rating_guide')->label('Tour guide')->options(self::stars()),
                        Select::make('rating_accommodation')->label('Stay')->options(self::stars()),
                        Select::make('rating_value')->label('Value for money')->options(self::stars()),
                        Select::make('rating_itinerary')->label('Itinerary')->options(self::stars()),
                    ]),

                Section::make('Your reply & visibility')
                    ->schema([
                        Textarea::make('admin_reply')
                            ->label('Your reply (shown under the review)')
                            ->rows(3),
                        Toggle::make('is_published')
                            ->label('Show this review on the website')
                            ->helperText('Reviews written by guests on the website wait here until you switch this on.')
                            ->default(true),
                        Toggle::make('is_featured')
                            ->label('Feature this review (shown in highlighted spots)')
                            ->default(false),
                    ]),
            ]);
    }
}
