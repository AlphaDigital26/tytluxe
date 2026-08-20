<?php

namespace App\Filament\Resources\Offers\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\RichEditor;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class OfferForm
{
    /**
     * The 4 canonical offer categories for a travel agency.
     * These match the filter tabs on the frontend offers page.
     */
    public static array $categories = [
        'flights'  => '✈️  Flights',
        'hotels'   => '🏨  Hotels',
        'cruises'  => '🚢  Cruises',
        'packages' => '📦  Packages',
    ];

    public static function configure(Schema $schema): Schema
    {
        return $schema->components([

            Tabs::make('Offer')
                ->tabs([

                    // ── TAB 1: Deal Details ─────────────────────────────
                    Tab::make('Deal Details')
                        ->icon('heroicon-o-tag')
                        ->schema([

                            Grid::make(2)->schema([

                                Select::make('category_key')
                                    ->label('Category')
                                    ->helperText('Which section does this offer belong to?')
                                    ->options(self::$categories)
                                    ->required()
                                    ->native(false),

                                TextInput::make('sort_order')
                                    ->label('Sort Order')
                                    ->helperText('Lower = first. Use 0, 10, 20…')
                                    ->numeric()
                                    ->default(0)
                                    ->required(),

                                TextInput::make('title')
                                    ->label('Offer Title')
                                    ->helperText('Main heading on the card. E.g. "Maldives Luxury Escape" or "Mumbai → Dubai"')
                                    ->required()
                                    ->placeholder('Maldives Luxury Escape')
                                    ->columnSpan(2),

                                TextInput::make('destination')
                                    ->label('Destination')
                                    ->helperText('Short destination label. E.g. "Maldives", "Dubai", "Goa → Sri Lanka"')
                                    ->placeholder('Maldives')
                                    ->nullable(),

                                TextInput::make('duration')
                                    ->label('Duration')
                                    ->helperText('E.g. "5 Nights / 6 Days", "3 Hours", "7 Nights"')
                                    ->placeholder('5 Nights / 6 Days')
                                    ->nullable(),

                                TextInput::make('subtitle')
                                    ->label('Subtitle / Inclusions')
                                    ->helperText('Short line shown below title. E.g. "Breakfast · Seaplane · Sunset Dinner"')
                                    ->placeholder('Breakfast · Airport Transfer · Sunset Dinner')
                                    ->nullable()
                                    ->columnSpan(2),

                                TextInput::make('display_price')
                                    ->label('Price (display)')
                                    ->helperText('E.g. "₹45,000 / person" or "From ₹3,50,000 / couple". Leave blank to show "Contact for Price".')
                                    ->placeholder('₹45,000 / person')
                                    ->nullable(),

                                TextInput::make('enquire_link')
                                    ->label('Enquire Button URL')
                                    ->helperText('WhatsApp or page link. Leave blank to use the default WhatsApp from settings.')
                                    ->url()
                                    ->placeholder('https://wa.me/91XXXXXXXXXX')
                                    ->nullable(),

                            ]),

                            // ── Badge ────────────────────────────────────
                            Section::make('Deal Badge')
                                ->description('Optional coloured label on the card image.')
                                ->collapsible()
                                ->collapsed()
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

                            // ── Promo Code ───────────────────────────────
                            Section::make('Promo Code (optional)')
                                ->description('Add a promo/coupon code to display on this offer card.')
                                ->collapsible()
                                ->collapsed()
                                ->schema([
                                    Grid::make(3)->schema([
                                        Select::make('discount_type')
                                            ->label('Discount Type')
                                            ->options([
                                                'percentage' => '% Percentage',
                                                'fixed'      => '₹ Fixed Amount',
                                            ])
                                            ->default('percentage')
                                            ->native(false),

                                        TextInput::make('discount_value')
                                            ->label('Discount Value')
                                            ->numeric()
                                            ->minValue(0)
                                            ->placeholder('20'),

                                        TextInput::make('promo_code')
                                            ->label('Promo Code')
                                            ->placeholder('SUMMER20')
                                            ->nullable()
                                            ->unique(ignoreRecord: true),
                                    ]),
                                ]),

                            Toggle::make('coming_soon')
                                ->label('"Deal Coming Soon" — hides the price and enquire button')
                                ->helperText('Use for upcoming deals you want to tease on the website.')
                                ->default(false),

                            RichEditor::make('terms_and_conditions')
                                ->label('Terms & Conditions')
                                ->toolbarButtons([
                                    'bold', 'italic', 'underline', 'bulletList', 'orderedList', 'link', 'redo', 'undo',
                                ])
                                ->columnSpanFull()
                                ->nullable(),

                        ]),

                    // ── TAB 2: Card Image ───────────────────────────────
                    Tab::make('Card Image')
                        ->icon('heroicon-o-photo')
                        ->schema([
                            Section::make()
                                ->description('Upload a photo OR paste an external URL. Uploaded image takes priority.')
                                ->schema([
                                    FileUpload::make('image_path')
                                        ->label('Upload Image')
                                        ->image()
                                        ->saveUploadedFileUsing(fn ($file) => app(\App\Services\ImageOptimizer::class)->optimizeAndSave($file, 'thumbnail', 'offer-cards'))
                                        ->imagePreviewHeight('220')
                                        ->maxSize(5120)
                                        ->columnSpanFull(),

                                    TextInput::make('image_url')
                                        ->label('External Image URL')
                                        ->url()
                                        ->placeholder('https://images.unsplash.com/photo-...')
                                        ->nullable()
                                        ->columnSpanFull(),
                                ]),
                        ]),

                    // ── TAB 3: Validity & Status ────────────────────────
                    Tab::make('Validity & Status')
                        ->icon('heroicon-o-calendar-days')
                        ->schema([
                            Grid::make(2)->schema([

                                DatePicker::make('valid_from')
                                    ->label('Offer Starts On')
                                    ->helperText('The offer is published from this date. Leave it as today if you want it live immediately.')
                                    ->required()
                                    ->native(false)
                                    ->default(now()),

                                DatePicker::make('valid_to')
                                    ->label('Offer Expires On')
                                    ->helperText('The offer is automatically hidden after this date.')
                                    ->required()
                                    ->native(false)
                                    ->afterOrEqual('valid_from'),
                            ]),

                            Toggle::make('is_active')
                                ->label('Active — show on the Offers page')
                                ->helperText('Turn off to temporarily hide this offer without deleting it.')
                                ->default(true)
                                ->columnSpanFull(),
                        ]),

                ])->columnSpanFull(),

        ]);
    }
}
