<?php

namespace App\Filament\Resources\Offers\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class OfferForm
{
    // Category options shared between form and table
    public static array $categories = [
        'hotels'    => '🏨 Hotels',
        'cruises'   => '🚢 Cruises',
        'flights'   => '✈️ Flights',
        'packages'  => '📦 Packages',
        'honeymoon' => '💑 Honeymoon',
        'family'    => '👨‍👩‍👧 Family',
    ];

    public static function configure(Schema $schema): Schema
    {
        return $schema->components([

            Tabs::make('Offer')
                ->tabs([

                    // ── TAB 1: Card Display ─────────────────────────────
                    Tab::make('Card Display')
                        ->icon('heroicon-o-photo')
                        ->schema([

                            Grid::make(2)->schema([

                                Select::make('category_key')
                                    ->label('Category / Section')
                                    ->helperText('Which slider section does this offer appear in?')
                                    ->options(self::$categories)
                                    ->required()
                                    ->native(false),

                                TextInput::make('sort_order')
                                    ->label('Sort Order')
                                    ->helperText('Lower numbers appear first. Use 0, 10, 20…')
                                    ->numeric()
                                    ->default(0)
                                    ->required(),

                                TextInput::make('title')
                                    ->label('Card Title')
                                    ->helperText('Shown as the main heading on the offer card (e.g. "Maldives Luxury Escape")')
                                    ->required()
                                    ->placeholder('Maldives Luxury Escape')
                                    ->columnSpan(2),

                                TextInput::make('subtitle')
                                    ->label('Subtitle / Tagline')
                                    ->helperText('Short line below title (e.g. "5N/6D · Breakfast included")')
                                    ->placeholder('5 Nights · Breakfast Included')
                                    ->nullable()
                                    ->columnSpan(2),

                                TextInput::make('display_price')
                                    ->label('Price Display')
                                    ->helperText('Shown on the card footer. Leave blank to show "Contact for Price".')
                                    ->placeholder('₹45,000 / person')
                                    ->nullable(),

                                TextInput::make('enquire_link')
                                    ->label('Enquire Button URL')
                                    ->helperText('Leave blank to use the default WhatsApp link from settings.')
                                    ->url()
                                    ->placeholder('https://wa.me/91XXXXXXXXXX')
                                    ->nullable(),
                            ]),

                            // ── Badge ────────────────────────────────────
                            Section::make('Badge (optional)')
                                ->description('Small coloured label on the card image corner.')
                                ->collapsible()
                                ->schema([
                                    Grid::make(2)->schema([
                                        TextInput::make('badge_label')
                                            ->label('Badge Text')
                                            ->placeholder('HOT DEAL')
                                            ->nullable(),

                                        Select::make('badge_type')
                                            ->label('Badge Colour')
                                            ->options([
                                                'badge-gold' => '🟡 Gold',
                                                'badge-hot'  => '🔴 Hot (Red)',
                                                'badge-new'  => '🟢 New (Green)',
                                            ])
                                            ->default('badge-gold')
                                            ->native(false),
                                    ]),
                                ]),

                            // ── Coming Soon ──────────────────────────────
                            Toggle::make('coming_soon')
                                ->label('"Deal Coming Soon" ribbon')
                                ->helperText('Shows a "Deal Coming Soon" ribbon over the card instead of the enquire button.')
                                ->default(false),

                            // ── Card Image ───────────────────────────────
                            Section::make('Card Image')
                                ->description('Upload an image OR paste an external URL. Uploaded image takes priority.')
                                ->schema([
                                    FileUpload::make('image_path')
                                        ->label('Upload Image')
                                        ->image()
                                        ->saveUploadedFileUsing(fn ($file) => app(\App\Services\ImageOptimizer::class)->optimizeAndSave($file, 'thumbnail', 'offer-cards'))
                                        ->imagePreviewHeight('200')
                                        ->maxSize(5120)
                                        ->columnSpanFull(),

                                    TextInput::make('image_url')
                                        ->label('Or: External Image URL')
                                        ->url()
                                        ->placeholder('https://images.unsplash.com/...')
                                        ->nullable()
                                        ->columnSpanFull(),
                                ]),
                        ]),

                    // ── TAB 2: Slider Section Heading ───────────────────
                    Tab::make('Slider Heading')
                        ->icon('heroicon-o-bars-3-bottom-left')
                        ->schema([
                            Section::make()
                                ->description('These values are used as the section heading for ALL offers in this category. You only need to fill them once — just make sure they match on every offer within the same category.')
                                ->schema([
                                    TextInput::make('slider_label')
                                        ->label('Section Label (small caps)')
                                        ->helperText('e.g. "Hotel Deals" — appears above the main heading in gold small caps.')
                                        ->placeholder('Hotel Deals')
                                        ->nullable()
                                        ->columnSpanFull(),

                                    TextInput::make('slider_title')
                                        ->label('Section Title (HTML allowed)')
                                        ->helperText('e.g. "Exclusive <em>Hotel Packages</em>" — use <em> for italic gold text.')
                                        ->placeholder('Exclusive <em>Hotel Packages</em>')
                                        ->nullable()
                                        ->columnSpanFull(),
                                ]),
                        ]),

                    // ── TAB 3: Discount & Promo ─────────────────────────
                    Tab::make('Discount & Promo Code')
                        ->icon('heroicon-o-tag')
                        ->schema([
                            Section::make()
                                ->description('Optional discount rules. Used internally to validate and apply coupon codes at checkout.')
                                ->schema([
                                    Grid::make(2)->schema([
                                        Select::make('discount_type')
                                            ->label('Discount Type')
                                            ->options([
                                                'percentage' => '% Percentage off',
                                                'fixed'      => '₹ Fixed amount off',
                                            ])
                                            ->required()
                                            ->native(false),

                                        TextInput::make('discount_value')
                                            ->label('Discount Value')
                                            ->numeric()
                                            ->required()
                                            ->minValue(0)
                                            ->placeholder('20'),

                                        TextInput::make('promo_code')
                                            ->label('Promo Code')
                                            ->helperText('Leave blank if no code is needed.')
                                            ->placeholder('SUMMER20')
                                            ->nullable()
                                            ->unique(ignoreRecord: true)
                                            ->columnSpan(2),

                                        Select::make('applies_to_vertical')
                                            ->label('Discount Applies To')
                                            ->options([
                                                'all'        => '🌍 All Categories',
                                                'hotel'      => '🏨 Hotels',
                                                'flight'     => '✈️ Flights',
                                                'cruise'     => '🚢 Cruises',
                                                'package'    => '📦 Packages',
                                                'staycation' => '🏡 Staycations',
                                            ])
                                            ->required()
                                            ->native(false),

                                        TextInput::make('applies_to_reference_id')
                                            ->label('Specific Record ID (optional)')
                                            ->numeric()
                                            ->helperText('Leave blank to apply to all records in the category above.')
                                            ->nullable(),
                                    ]),
                                ]),
                        ]),

                    // ── TAB 4: Validity & Status ────────────────────────
                    Tab::make('Validity & Status')
                        ->icon('heroicon-o-calendar-days')
                        ->schema([
                            Grid::make(2)->schema([

                                DatePicker::make('valid_from')
                                    ->label('Offer Starts On')
                                    ->helperText('The offer card becomes visible from this date.')
                                    ->required()
                                    ->native(false),

                                DatePicker::make('valid_to')
                                    ->label('Offer Expires On')
                                    ->helperText('The card is hidden automatically after this date.')
                                    ->required()
                                    ->native(false)
                                    ->afterOrEqual('valid_from'),
                            ]),

                            Toggle::make('is_active')
                                ->label('Active — visible on the Offers page')
                                ->helperText('Turn off to hide this offer from the website without deleting it.')
                                ->default(true)
                                ->columnSpanFull(),
                        ]),

                ])->columnSpanFull(),

        ]);
    }
}
