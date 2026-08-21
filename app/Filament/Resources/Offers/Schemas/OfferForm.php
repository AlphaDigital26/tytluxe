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

            Section::make('1. Basic Offer Details')
                ->description('What is this offer and where does it belong?')
                ->columnSpan('full')
                ->schema([
                    Grid::make(2)->schema([
                        Select::make('category_key')
                            ->label('Which Tab Should This Go In?')
                            ->options(self::$categories)
                            ->required()
                            ->native(false),

                        TextInput::make('sort_order')
                            ->label('Display Priority')
                            ->helperText('Use 1 to show first, 2 for second, etc.')
                            ->numeric()
                            ->default(10)
                            ->required(),

                        TextInput::make('title')
                            ->label('Main Big Golden Text')
                            ->helperText('E.g. "$50 OFF" or "Luxury Maldives Escape"')
                            ->required()
                            ->columnSpan(2),

                        TextInput::make('subtitle')
                            ->label('Small Subtitle')
                            ->helperText('E.g. "INTERNATIONAL FLIGHTS" or "Limited Time Deal"')
                            ->columnSpan(2),
                    ]),
                ]),

            Section::make('2. Discount Details')
                ->description('Configure how the discount is calculated.')
                ->columnSpan('full')
                ->schema([
                    Grid::make(3)->schema([
                        Select::make('discount_type')
                            ->label('Discount Type')
                            ->options([
                                'percentage' => 'Percentage (%)',
                                'fixed'      => 'Fixed Amount (₹)',
                            ])
                            ->default('percentage')
                            ->required()
                            ->native(false),

                        TextInput::make('discount_value')
                            ->label('Discount Value')
                            ->helperText('E.g. 50 (for 50%) or 1000 (for ₹1000)')
                            ->numeric()
                            ->required(),

                        TextInput::make('min_order_value')
                            ->label('Minimum Order Value (Optional)')
                            ->helperText('E.g. 50000 for flights above 50,000rs')
                            ->numeric()
                            ->nullable(),
                    ]),

                    Grid::make(2)->schema([
                        Toggle::make('is_upto')
                            ->label('Is this an "Up to" offer?')
                            ->helperText('Will display as "Up to X% OFF" instead of flat "X% OFF".')
                            ->live(), // To toggle visibility of options

                        \Filament\Forms\Components\TagsInput::make('upto_options')
                            ->label('Randomized Discount Options')
                            ->helperText('Type values and press Enter (e.g. 5, 10, 15, 20). Only used if "Up to" is enabled.')
                            ->visible(fn ($get) => $get('is_upto')),
                    ]),
                ]),

            Section::make('3. Description & Terms')
                ->description('Provide the details of the offer.')
                ->columnSpan('full')
                ->schema([
                    Grid::make(3)->schema([
                        TextInput::make('destination')
                            ->label('Destination (Optional)')
                            ->placeholder('E.g. Maldives'),

                        TextInput::make('duration')
                            ->label('Duration (Optional)')
                            ->placeholder('E.g. 5 Nights / 6 Days'),

                        TextInput::make('display_price')
                            ->label('Price (Optional)')
                            ->placeholder('E.g. ₹45,000 / person'),
                    ]),

                    Textarea::make('description')
                        ->label('Short Description')
                        ->helperText('Keep it brief to fit on the coupon card.')
                        ->rows(3)
                        ->nullable(),

                    RichEditor::make('terms_and_conditions')
                        ->label('Terms & Conditions (Optional)')
                        ->helperText('If you add terms, a clickable "*T&Cs apply" link will appear.')
                        ->toolbarButtons(['bold', 'italic', 'bulletList', 'orderedList', 'link'])
                        ->nullable(),
                ]),

            Section::make('4. Promo Code & Call-to-Action')
                ->description('How do customers claim this offer?')
                ->columnSpan('full')
                ->schema([
                    Grid::make(2)->schema([
                        TextInput::make('promo_code')
                            ->label('Promo Code')
                            ->helperText('E.g. "SUMMER20". If left blank, it will show an "ENQUIRE" button instead.')
                            ->nullable()
                            ->unique(ignoreRecord: true),

                        TextInput::make('enquire_link')
                            ->label('Custom Enquire URL (Optional)')
                            ->helperText('Leave blank to use the default WhatsApp number.')
                            ->url()
                            ->nullable(),
                    ]),
                ]),

            Section::make('5. Status & Expiry')
                ->description('When should this offer be shown?')
                ->columnSpan('full')
                ->schema([
                    Grid::make(2)->schema([
                        DatePicker::make('valid_to')
                            ->label('Expiry Date')
                            ->helperText('After this date, the offer will automatically disappear.')
                            ->native(false)
                            ->required(),

                        DatePicker::make('valid_from')
                            ->label('Start Date (Optional)')
                            ->native(false)
                            ->default(now()),
                    ]),

                    Grid::make(2)->schema([
                        Toggle::make('coming_soon')
                            ->label('Mark as "Coming Soon"')
                            ->helperText('Shows a "Dropping Soon" label instead of the code.'),

                        Toggle::make('is_active')
                            ->label('Offer is Active')
                            ->helperText('Turn off to hide this offer immediately.')
                            ->default(true),
                    ]),
                ]),

        ]);
    }
}
