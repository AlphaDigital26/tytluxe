<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;

class StaycationPageSettings extends Page
{
    protected string $view = 'filament.pages.staycation-page-settings';

    protected static string|\UnitEnum|null $navigationGroup = null;

    protected static ?string $navigationLabel = 'Staycation Page Settings';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-home-modern';

    protected static ?string $title = 'Staycation Page Settings';

    protected static ?int $navigationSort = 3;

    // ── Form State ────────────────────────────────────────────────────────
    public array $data = [];

    public function mount(): void
    {
        $this->data = [
            // Hero
            'hero_eyebrow'  => Setting::get('staycation_page.hero_eyebrow',  'Curated Staycations'),
            'hero_title'    => Setting::get('staycation_page.hero_title',    'Escape the Ordinary. <em>Stay Extraordinary.</em>'),
            'hero_subtitle' => Setting::get('staycation_page.hero_subtitle', 'Handpicked resort stays near Mumbai & Pune - perfect for weekends, honeymoons & family getaways.'),

            // Hero carousel images
            'hero_images'   => Setting::getJson('staycation_page.hero_images', [
                ['image_url' => 'https://meritashotels.com/wp-content/uploads/2023/06/Deluxe-Room.jpg',                         'image_path' => null],
                ['image_url' => 'https://meritashotels.com/wp-content/uploads/2023/03/DSC_9452-HDR-copy.jpg',                   'image_path' => null],
                ['image_url' => 'https://meritashotels.com/wp-content/uploads/2023/03/Standard-Room-with-Sit-Out3.png',         'image_path' => null],
                ['image_url' => 'https://meritashotels.com/wp-content/uploads/2023/03/Suite-Bed-Room-%40-Picaddle.jpg',         'image_path' => null],
                ['image_url' => 'https://meritashotels.com/wp-content/uploads/2023/03/DSC_9476-HDR-copy.jpg',                   'image_path' => null],
            ]),

            // Resorts
            'resorts' => Setting::getJson('staycation_page.resorts', []),

            // Bottom CTA
            'cta_tag'        => Setting::get('staycation_page.cta_tag',        'Book Your Staycation'),
            'cta_heading'    => Setting::get('staycation_page.cta_heading',    'Ready for Your <em>Perfect Escape?</em>'),
            'cta_body'       => Setting::get('staycation_page.cta_body',       "WhatsApp us with your dates and preferences - we'll get you the best rates on all Meritas properties instantly."),
            'cta_whatsapp'   => Setting::get('staycation_page.cta_whatsapp',   'https://wa.me/919875073788'),
        ];
    }

    // ── Schema ────────────────────────────────────────────────────────────
    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([

                // ── HERO ─────────────────────────────────────────────────
                Section::make('Hero Section')
                    ->description('Controls the hero banner at the top of the staycation page.')
                    ->collapsible()
                    ->schema([
                        TextInput::make('hero_eyebrow')
                            ->label('Eyebrow Text')
                            ->placeholder('Curated Staycations'),

                        TextInput::make('hero_title')
                            ->label('Hero Title (HTML allowed, use <em> for italic gold)')
                            ->placeholder('Escape the Ordinary. <em>Stay Extraordinary.</em>')
                            ->columnSpanFull(),

                        Textarea::make('hero_subtitle')
                            ->label('Hero Subtitle')
                            ->rows(2)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                // ── HERO CAROUSEL IMAGES ──────────────────────────────────
                Section::make('Hero Carousel Images')
                    ->description('Images shown in the hero slider. Upload a file OR paste an external URL — uploaded file takes priority.')
                    ->collapsible()
                    ->schema([
                        Repeater::make('hero_images')
                            ->label(false)
                            ->schema([
                                FileUpload::make('image_path')
                                    ->label('Upload Image')
                                    ->image()
                                    ->disk('public')
                                    ->directory('staycation/hero')
                                    ->imagePreviewHeight('100')
                                    ->nullable(),
                                TextInput::make('image_url')
                                    ->label('OR External Image URL')
                                    ->url()
                                    ->placeholder('https://...')
                                    ->nullable(),
                            ])
                            ->columns(2)
                            ->addActionLabel('+ Add Slide')
                            ->reorderable()
                            ->collapsible()
                            ->itemLabel(fn (array $state): string =>
                                (!empty($state['image_url']) ? $state['image_url'] : 'New Slide')
                            ),
                    ]),

                // ── RESORTS ───────────────────────────────────────────────
                Section::make('Resorts & Room Categories')
                    ->description('Each resort block shows on the page with its room cards.')
                    ->collapsible()
                    ->schema([
                        Repeater::make('resorts')
                            ->label(false)
                            ->schema([
                                TextInput::make('label')
                                    ->label('Resort Label (e.g. "Lonavala - Resort 01")')
                                    ->required(),
                                TextInput::make('name')
                                    ->label('Resort Name (HTML allowed, e.g. Meritas Picaddle Resort, <em>Lonavala</em>)')
                                    ->required()
                                    ->columnSpanFull(),
                                Textarea::make('description')
                                    ->label('Resort Description')
                                    ->rows(3)
                                    ->columnSpanFull(),

                                // Rooms repeater nested inside resort
                                Repeater::make('rooms')
                                    ->label('Room Categories')
                                    ->schema([
                                        TextInput::make('name')
                                            ->label('Room Name')
                                            ->required(),
                                        Textarea::make('description')
                                            ->label('Room Description')
                                            ->rows(2)
                                            ->columnSpanFull(),
                                        TextInput::make('amenities')
                                            ->label('Amenities (comma-separated, e.g. AC, Free WiFi, Minibar)')
                                            ->placeholder('Up to 3 Guests, Queen Bed, AC, Free WiFi')
                                            ->columnSpanFull(),
                                        FileUpload::make('image_path')
                                            ->label('Room Image (Upload)')
                                            ->image()
                                            ->disk('public')
                                            ->directory('staycation/rooms')
                                            ->imagePreviewHeight('80')
                                            ->nullable(),
                                        TextInput::make('image_url')
                                            ->label('Room Image (External URL)')
                                            ->url()
                                            ->placeholder('https://...')
                                            ->nullable(),
                                    ])
                                    ->columns(2)
                                    ->addActionLabel('+ Add Room')
                                    ->reorderable()
                                    ->collapsible()
                                    ->itemLabel(fn (array $state): string =>
                                        (!empty($state['name']) ? $state['name'] : 'New Room')
                                    )
                                    ->columnSpanFull(),
                            ])
                            ->columns(2)
                            ->addActionLabel('+ Add Resort')
                            ->reorderable()
                            ->collapsible()
                            ->itemLabel(fn (array $state): string =>
                                (!empty($state['name']) ? strip_tags($state['name']) : 'New Resort')
                            ),
                    ]),

                // ── BOTTOM CTA ────────────────────────────────────────────
                Section::make('Bottom CTA Card')
                    ->description('The booking call-to-action card at the bottom of the page.')
                    ->collapsible()
                    ->schema([
                        TextInput::make('cta_tag')
                            ->label('Small Tag Line')
                            ->placeholder('Book Your Staycation'),
                        TextInput::make('cta_whatsapp')
                            ->label('WhatsApp Link')
                            ->url()
                            ->placeholder('https://wa.me/919875073788'),
                        TextInput::make('cta_heading')
                            ->label('Heading (HTML allowed)')
                            ->placeholder('Ready for Your <em>Perfect Escape?</em>')
                            ->columnSpanFull(),
                        Textarea::make('cta_body')
                            ->label('Body Text')
                            ->rows(2)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

            ]);
    }

    // ── Save Action ───────────────────────────────────────────────────────
    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Save Staycation Page Settings')
                ->action('save'),
        ];
    }

    public function save(): void
    {
        $data = $this->data;

        // Simple string keys
        $simpleKeys = [
            'hero_eyebrow', 'hero_title', 'hero_subtitle',
            'cta_tag', 'cta_heading', 'cta_body', 'cta_whatsapp',
        ];
        foreach ($simpleKeys as $key) {
            Setting::set('staycation_page.' . $key, $data[$key] ?? '');
        }

        // JSON keys
        $jsonKeys = ['hero_images', 'resorts'];
        foreach ($jsonKeys as $key) {
            Setting::setJson('staycation_page.' . $key, $data[$key] ?? []);
        }

        Notification::make()
            ->title('Staycation Page Settings Saved!')
            ->success()
            ->send();
    }
}
