<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;

class OffersPageSettings extends Page
{
    protected string $view = 'filament.pages.offers-page-settings';

    protected static string|\UnitEnum|null $navigationGroup = null;

    protected static ?string $navigationLabel = 'Offers Page Settings';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-receipt-percent';

    protected static ?string $title = 'Offers Page Settings';

    protected static ?int $navigationSort = 4;

    public array $data = [];

    public function mount(): void
    {
        $this->data = [
            // Hero
            'hero_eyebrow'  => Setting::get('offers_page.hero_eyebrow',  'Limited Time Deals'),
            'hero_title'    => Setting::get('offers_page.hero_title',    'Exclusive Deals. <em>Unforgettable</em> Experiences.'),
            'hero_subtitle' => Setting::get('offers_page.hero_subtitle', 'Handpicked offers on hotels, cruises & flights — updated regularly'),

            // Hero images
            'hero_images' => Setting::getJson('offers_page.hero_images', [
                ['image_url' => 'https://images.unsplash.com/photo-1542314831-068cd1dbfeeb?w=1400&q=85', 'image_path' => null],
                ['image_url' => 'https://images.unsplash.com/photo-1548574505-5e239809ee19?w=1400&q=85', 'image_path' => null],
                ['image_url' => 'https://images.unsplash.com/photo-1436491865332-7a61a109cc05?w=1400&q=85', 'image_path' => null],
                ['image_url' => 'https://images.unsplash.com/photo-1512343879784-a960bf40e7f2?w=1400&q=85', 'image_path' => null],
                ['image_url' => 'https://images.unsplash.com/photo-1590490360182-c33d57733427?w=1400&q=85', 'image_path' => null],
            ]),

            // Filter tab labels
            'filter_tabs' => Setting::getJson('offers_page.filter_tabs', [
                ['key' => 'all',       'label' => 'All Offers'],
                ['key' => 'hotels',    'label' => 'Hotels'],
                ['key' => 'cruises',   'label' => 'Cruises'],
                ['key' => 'flights',   'label' => 'Flights'],
                ['key' => 'honeymoon', 'label' => 'Honeymoon'],
                ['key' => 'family',    'label' => 'Family'],
            ]),

            // Offer categories (each has a slider of cards)
            'categories' => Setting::getJson('offers_page.categories', []),

            // Bottom CTA
            'cta_tag'       => Setting::get('offers_page.cta_tag',      'Stay Ahead'),
            'cta_heading'   => Setting::get('offers_page.cta_heading',  'Be the First to <em>Know</em>'),
            'cta_body'      => Setting::get('offers_page.cta_body',     "Drop your WhatsApp number and we'll notify you the moment a new deal goes live — no spam, ever."),
            'cta_notify_note' => Setting::get('offers_page.cta_notify_note', "WhatsApp only. We won't call unless you ask."),
            'cta_whatsapp'  => Setting::get('offers_page.cta_whatsapp', 'https://wa.me/9875073788'),
            'cta_wa_label'  => Setting::get('offers_page.cta_wa_label', 'Ask for Latest Deals on WhatsApp'),
        ];
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([

                // ── HERO ────────────────────────────────────────────────
                Section::make('Hero Section')
                    ->collapsible()
                    ->schema([
                        TextInput::make('hero_eyebrow')->label('Eyebrow Text'),
                        TextInput::make('hero_title')
                            ->label('Title (HTML allowed — use <em> for italic gold)')
                            ->columnSpanFull(),
                        Textarea::make('hero_subtitle')->label('Subtitle')->rows(2)->columnSpanFull(),
                    ])
                    ->columns(2),

                // ── HERO CAROUSEL IMAGES ─────────────────────────────────
                Section::make('Hero Carousel Images')
                    ->description('Upload a file OR paste an external URL — uploaded file takes priority.')
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        Repeater::make('hero_images')
                            ->label(false)
                            ->schema([
                                FileUpload::make('image_path')
                                    ->label('Upload Image')->image()->disk('public')
                                    ->directory('offers/hero')->imagePreviewHeight('80')->nullable(),
                                TextInput::make('image_url')
                                    ->label('OR External URL')->url()->placeholder('https://...')->nullable(),
                            ])
                            ->columns(2)->addActionLabel('+ Add Slide')->reorderable()->collapsible()
                            ->itemLabel(fn (array $state): string => $state['image_url'] ?? 'New Slide'),
                    ]),

                // ── FILTER TABS ──────────────────────────────────────────
                Section::make('Filter Tabs')
                    ->description('The category filter buttons shown above the sliders.')
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        Repeater::make('filter_tabs')
                            ->label(false)
                            ->schema([
                                TextInput::make('key')
                                    ->label('Category Key (used for JS filtering, lowercase, no spaces)')
                                    ->placeholder('hotels'),
                                TextInput::make('label')->label('Button Label')->placeholder('Hotels'),
                            ])
                            ->columns(2)->addActionLabel('+ Add Filter Tab')->reorderable()
                            ->itemLabel(fn (array $state): string => $state['label'] ?? 'New Tab'),
                    ]),

                // ── OFFER CATEGORIES (SLIDERS) ──────────────────────────
                Section::make('Offer Categories & Cards')
                    ->description('Each category is a slider section on the page. Add, reorder, or remove categories and their offer cards here.')
                    ->collapsible()
                    ->schema([
                        Repeater::make('categories')
                            ->label(false)
                            ->schema([
                                TextInput::make('category_key')
                                    ->label('Category Key (must match a filter tab key above)')
                                    ->placeholder('hotels')->required(),
                                TextInput::make('slider_label')
                                    ->label('Section Label (small caps above title)')
                                    ->placeholder('Hotel Offers'),
                                TextInput::make('slider_title')
                                    ->label('Section Title (HTML allowed)')
                                    ->placeholder('Handpicked <em>Stays</em>')
                                    ->columnSpanFull(),

                                // Cards nested repeater
                                Repeater::make('cards')
                                    ->label('Offer Cards')
                                    ->schema([
                                        TextInput::make('name')->label('Card Title')->required(),
                                        TextInput::make('subtitle')->label('Subtitle / Short Description')->columnSpanFull(),
                                        TextInput::make('price')->label('Price Text')->placeholder('Contact for Price'),
                                        Select::make('badge_type')
                                            ->label('Badge Style')
                                            ->options([
                                                'badge-gold' => 'Gold',
                                                'badge-hot'  => 'Hot (Red)',
                                                'badge-new'  => 'New (Green)',
                                            ])
                                            ->default('badge-gold'),
                                        TextInput::make('badge_label')->label('Badge Label')->placeholder('Hot Deal'),
                                        Toggle::make('coming_soon')
                                            ->label('Show "Deal Coming Soon" ribbon')
                                            ->default(true),
                                        TextInput::make('enquire_link')
                                            ->label('Enquire Link (WhatsApp or URL)')
                                            ->placeholder('https://wa.me/9875073788'),
                                        FileUpload::make('image_path')
                                            ->label('Card Image (Upload)')->image()->disk('public')
                                            ->directory('offers/cards')->imagePreviewHeight('80')->nullable(),
                                        TextInput::make('image_url')
                                            ->label('Card Image (External URL)')->url()->placeholder('https://...')->nullable(),
                                    ])
                                    ->columns(2)
                                    ->addActionLabel('+ Add Card')
                                    ->reorderable()->collapsible()
                                    ->itemLabel(fn (array $state): string => $state['name'] ?? 'New Card')
                                    ->columnSpanFull(),
                            ])
                            ->columns(2)
                            ->addActionLabel('+ Add Category Section')
                            ->reorderable()->collapsible()
                            ->itemLabel(fn (array $state): string =>
                                (!empty($state['slider_label']) ? $state['slider_label'] : ($state['category_key'] ?? 'New Category'))
                            ),
                    ]),

                // ── BOTTOM CTA ───────────────────────────────────────────
                Section::make('Bottom CTA Card')
                    ->collapsible()
                    ->schema([
                        TextInput::make('cta_tag')->label('Small Tag Line'),
                        TextInput::make('cta_whatsapp')->label('WhatsApp Link')->url(),
                        TextInput::make('cta_heading')->label('Heading (HTML allowed)')->columnSpanFull(),
                        Textarea::make('cta_body')->label('Body Text')->rows(2)->columnSpanFull(),
                        TextInput::make('cta_notify_note')->label('Note under phone form')->columnSpanFull(),
                        TextInput::make('cta_wa_label')->label('WhatsApp Button Label')->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Save Offers Page Settings')
                ->action('save'),
        ];
    }

    public function save(): void
    {
        $data = $this->data;

        foreach (['hero_eyebrow', 'hero_title', 'hero_subtitle', 'cta_tag', 'cta_heading', 'cta_body', 'cta_notify_note', 'cta_whatsapp', 'cta_wa_label'] as $key) {
            Setting::set('offers_page.' . $key, $data[$key] ?? '');
        }

        foreach (['hero_images', 'filter_tabs', 'categories'] as $key) {
            Setting::setJson('offers_page.' . $key, $data[$key] ?? []);
        }

        Notification::make()->title('Offers Page Settings Saved!')->success()->send();
    }
}
