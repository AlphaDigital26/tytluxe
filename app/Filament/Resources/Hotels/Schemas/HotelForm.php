<?php

namespace App\Filament\Resources\Hotels\Schemas;

use App\Models\Hotel;
use App\Models\HotelImage;
use App\Models\Review;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

/**
 * Everything here is read-only display except: the two Photos repeaters
 * (hotel gallery) and the Visible/Featured toggles. All hotel content
 * (name, description, amenities, rooms, policies, ratings, location, etc.)
 * comes from TripJack sync or a one-time manual entry and is only meant to
 * be corrected there — this form exists so non-technical staff can see
 * everything the public hotel page shows, in one place, without being able
 * to accidentally break synced data.
 *
 * `title`/`destination_id`/`category`/`slug` still render as real inputs
 * (not Placeholders) ONLY while creating a brand-new manual hotel, since
 * that's the one time there is no TripJack sync to populate them from —
 * see the `disabled()`/`dehydrated()` closures below.
 */
class HotelForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Property Status & Overview')
                    ->description('Manage website visibility and view hotel information. All property details below are read-only to preserve sync consistency.')
                    ->schema([
                        Placeholder::make('header_banner')
                            ->hiddenLabel()
                            ->content(function (?Hotel $record) {
                                if (! $record) {
                                    return new HtmlString(
                                        '<div style="background:rgba(201,168,76,0.08); border:1px solid rgba(201,168,76,0.3); border-radius:8px; padding:14px 18px; font-size:13.5px; line-height:1.6; color:#e8c96b;">'
                                        .'<strong>🏨 New Hotel:</strong> Enter the initial details below to create the hotel record. Once created, property details are read-only and you can manage the photos gallery.'
                                        .'</div>'
                                    );
                                }

                                $sourceLabel = $record->source === 'tripjack' ? '🔄 TripJack Partner API' : '✍️ Manual Entry';
                                $syncId = $record->tripjack_hotel_id ? ' • Property ID: '.e($record->tripjack_hotel_id) : '';

                                return new HtmlString(
                                    '<div style="background:rgba(128,128,128,0.06); border:1px solid rgba(128,128,128,0.25); border-radius:10px; padding:14px 18px;">'
                                    .'<div style="display:flex; align-items:center; gap:10px; margin-bottom:4px;">'
                                    .'<span style="font-size:16px; font-weight:700;">'.e($record->title).'</span>'
                                    .'<span style="background:rgba(201,168,76,0.12); border:1px solid rgba(201,168,76,0.3); color:#e8c96b; border-radius:999px; padding:2px 10px; font-size:11.5px; font-weight:600;">'.$sourceLabel.$syncId.'</span>'
                                    .'</div>'
                                    .'<p style="margin:0; font-size:12.5px; opacity:0.75;">Property details are in read-only display. You can manage photo visibility and upload new marketing photos in the Photos tab below.</p>'
                                    .'</div>'
                                );
                            })
                            ->columnSpanFull(),

                        Toggle::make('is_active')
                            ->label('Visible on Website')
                            ->helperText('Turn ON to display this hotel to website visitors')
                            ->default(true)
                            ->required(),

                        Toggle::make('is_featured')
                            ->label('Featured Hotel')
                            ->helperText('Highlight this hotel on the homepage and destination showcases')
                            ->default(false)
                            ->required(),
                    ])->columns(2)->columnSpanFull(),

                Tabs::make('Hotel Details')
                    ->tabs([
                        Tab::make('Photos')
                            ->label('📸 Hotel Photos')
                            ->icon('heroicon-o-photo')
                            ->schema([
                                Repeater::make('tripjack_images')
                                    ->label('Synced Photos (TripJack)')
                                    ->helperText('Turn a photo off to hide it from visitors without losing it — this survives future TripJack resyncs.')
                                    ->visible(fn ($record) => $record && $record->images()->where('path', 'like', 'http%')->exists())
                                    ->relationship('images', modifyQueryUsing: fn ($query) => $query->where('path', 'like', 'http%')->orderBy('sort_order'))
                                    ->schema([
                                        Placeholder::make('preview')
                                            ->hiddenLabel()
                                            ->content(fn (?HotelImage $record) => $record
                                                ? new HtmlString('<img src="'.e($record->path).'" loading="lazy" style="width:100%; height:110px; object-fit:cover; border-radius:8px; display:block;">')
                                                : null)
                                            ->columnSpanFull(),
                                        Toggle::make('is_hidden')
                                            ->label('Hide from visitors')
                                            ->default(false),
                                    ])
                                    ->grid(4)
                                    ->columnSpanFull()
                                    ->addable(false)
                                    ->reorderable(false)
                                    ->deletable(false),
                                Repeater::make('images')
                                    ->label('Manually Added Photos')
                                    ->helperText('Upload additional high-resolution marketing photos of the hotel. Drag and drop to reorder.')
                                    ->relationship('images', modifyQueryUsing: fn ($query) => $query->where('path', 'not like', 'http%'))
                                    ->schema([
                                        FileUpload::make('path')
                                            ->disk('public')
                                            ->label('Photo')
                                            ->image()
                                            ->saveUploadedFileUsing(fn ($file) => app(\App\Services\ImageOptimizer::class)->optimizeAndSave($file, 'hero', 'hotels'))
                                            ->required(),
                                        TextInput::make('alt_text')
                                            ->label('Photo Caption (optional)')
                                            ->nullable(),
                                    ])
                                    ->grid(2)
                                    ->columnSpanFull()
                                    ->defaultItems(0)
                                    ->reorderableWithDragAndDrop(true)
                                    ->addActionLabel('Add Photo'),
                            ]),

                        Tab::make('Overview')
                            ->label('🏨 Overview & Location')
                            ->icon('heroicon-o-information-circle')
                            ->schema([
                                Grid::make(2)->schema([
                                    Select::make('destination_id')
                                        ->label('Destination')
                                        ->helperText('City or region where this hotel is located')
                                        ->relationship('destination', 'name', fn ($query) => $query->whereJsonContains('for', 'hotel')->where('is_active', true)->orderBy('name'))
                                        ->required()
                                        ->searchable()
                                        ->preload()
                                        ->visible(fn (?Hotel $record) => $record === null),
                                    Placeholder::make('destination_view')
                                        ->label('Destination')
                                        ->content(fn (?Hotel $record) => $record?->destination?->name ?? '—')
                                        ->visible(fn (?Hotel $record) => $record !== null),

                                    Select::make('category')
                                        ->label('Hotel Category')
                                        ->options(static::categoryOptions())
                                        ->required()
                                        ->native(false)
                                        ->visible(fn (?Hotel $record) => $record === null),
                                    Placeholder::make('category_view')
                                        ->label('Hotel Category')
                                        ->content(fn (?Hotel $record) => $record ? (static::categoryOptions()[$record->category] ?? $record->category) : '—')
                                        ->visible(fn (?Hotel $record) => $record !== null),

                                    TextInput::make('title')
                                        ->label('Hotel Name')
                                        ->required()
                                        ->live(onBlur: true)
                                        ->afterStateUpdated(fn ($set, ?string $state) => $set('slug', Str::slug($state)))
                                        ->columnSpan(2)
                                        ->visible(fn (?Hotel $record) => $record === null),
                                    Placeholder::make('title_view')
                                        ->label('Hotel Name')
                                        ->content(fn (?Hotel $record) => $record?->title ?? '—')
                                        ->columnSpan(2)
                                        ->visible(fn (?Hotel $record) => $record !== null),

                                    Placeholder::make('chain_name_view')
                                        ->label('Hotel Chain / Brand')
                                        ->content(fn (?Hotel $record) => $record?->chain_name ?: '—'),

                                    Select::make('star_rating')
                                        ->label('Hotel Star Rating')
                                        ->options([
                                            1 => '⭐ 1 Star',
                                            2 => '⭐⭐ 2 Stars',
                                            3 => '⭐⭐⭐ 3 Stars',
                                            4 => '⭐⭐⭐⭐ 4 Stars',
                                            5 => '⭐⭐⭐⭐⭐ 5 Stars',
                                        ])
                                        ->required()
                                        ->native(false)
                                        ->visible(fn (?Hotel $record) => $record === null),
                                    Placeholder::make('star_rating_view')
                                        ->label('Hotel Star Rating')
                                        ->content(fn (?Hotel $record) => $record?->star_rating ? str_repeat('⭐', (int) $record->star_rating) : '—')
                                        ->visible(fn (?Hotel $record) => $record !== null),

                                    TextInput::make('address')
                                        ->label('Full Address')
                                        ->required()
                                        ->columnSpan(2)
                                        ->visible(fn (?Hotel $record) => $record === null),
                                    Placeholder::make('address_view')
                                        ->label('Full Address')
                                        ->content(fn (?Hotel $record) => $record?->address ?: '—')
                                        ->columnSpan(2)
                                        ->visible(fn (?Hotel $record) => $record !== null),

                                    Placeholder::make('check_in_time_view')
                                        ->label('Check-in Time')
                                        ->content(fn (?Hotel $record) => $record?->check_in_time ?: '—'),

                                    Placeholder::make('check_out_time_view')
                                        ->label('Check-out Time')
                                        ->content(fn (?Hotel $record) => $record?->check_out_time ?: '—'),

                                    Placeholder::make('map_view')
                                        ->label('Map Location')
                                        ->content(fn (?Hotel $record) => ($record?->lat && $record?->lng)
                                            ? new HtmlString('<a href="https://maps.google.com/?q='.$record->lat.','.$record->lng.'" target="_blank" rel="noopener" style="text-decoration:underline; color:#e8c96b;">'.$record->lat.', '.$record->lng.' — open in Google Maps</a>')
                                            : '—')
                                        ->columnSpan(2),

                                    TextInput::make('slug')
                                        ->label('Web Address (URL Slug)')
                                        ->helperText('Auto-generated web address for this hotel.')
                                        ->required()
                                        ->unique(ignoreRecord: true)
                                        ->columnSpan(2)
                                        ->visible(fn (?Hotel $record) => $record === null),
                                    Placeholder::make('slug_view')
                                        ->label('Web Address (URL Slug)')
                                        ->content(fn (?Hotel $record) => $record?->slug ? '/hotels/'.$record->slug : '—')
                                        ->columnSpan(2)
                                        ->visible(fn (?Hotel $record) => $record !== null),
                                ]),
                            ]),

                        Tab::make('About')
                            ->label('📝 About & Description')
                            ->icon('heroicon-o-document-text')
                            ->schema([
                                RichEditor::make('description')
                                    ->label('Description')
                                    ->helperText('A brief description that appears on the hotel detail page.')
                                    ->required()
                                    ->columnSpanFull()
                                    ->visible(fn (?Hotel $record) => $record === null)
                                    ->toolbarButtons(['bold', 'italic', 'bulletList', 'orderedList', 'h2', 'h3', 'link', 'redo', 'undo']),
                                Placeholder::make('description_view')
                                    ->label('Hotel Description')
                                    ->helperText('This is what customers see on the hotel page.')
                                    ->content(fn (?Hotel $record) => new HtmlString(
                                        '<div style="font-size:13.5px; line-height:1.7;">'.($record?->description ?: '<span style="opacity:.5;">—</span>').'</div>'
                                    ))
                                    ->columnSpanFull()
                                    ->visible(fn (?Hotel $record) => $record !== null),

                                Placeholder::make('description_sections_preview')
                                    ->label('About Highlights & Sub-Sections')
                                    ->helperText('Detailed panels (e.g. Rooms, Business Amenities) shown on the hotel page.')
                                    ->content(fn (?Hotel $record) => static::renderKeyedSections($record?->description_sections))
                                    ->columnSpanFull(),

                                Placeholder::make('bottom_sections_preview')
                                    ->label('Additional Notices & Attractions')
                                    ->helperText('Additional notices rendered near the bottom of the hotel page.')
                                    ->content(fn (?Hotel $record) => static::renderKeyedSections($record?->bottom_sections))
                                    ->columnSpanFull(),
                            ]),

                        Tab::make('Amenities & Nearby')
                            ->label('✨ Amenities & Neighborhood')
                            ->icon('heroicon-o-sparkles')
                            ->schema([
                                Select::make('amenities')
                                    ->label('Hotel Amenities')
                                    ->relationship('amenities', 'name', fn ($query) => $query->where('type', 'hotel')->orderBy('name'))
                                    ->multiple()
                                    ->preload()
                                    ->searchable()
                                    ->columnSpanFull()
                                    ->visible(fn (?Hotel $record) => $record === null),
                                Placeholder::make('amenities_view')
                                    ->label('Hotel Amenities')
                                    ->helperText('Amenities offered by this hotel, as shown to customers.')
                                    ->content(fn (?Hotel $record) => static::renderBadges($record?->amenities->pluck('name')->all() ?? []))
                                    ->columnSpanFull()
                                    ->visible(fn (?Hotel $record) => $record !== null),

                                Textarea::make('room_categories')
                                    ->label('Room Types (fallback list)')
                                    ->rows(3)
                                    ->columnSpanFull()
                                    ->visible(fn (?Hotel $record) => $record === null),
                                Placeholder::make('room_categories_view')
                                    ->label('Room Types (fallback list)')
                                    ->helperText('Only shown on the website when no individual Room Types (see the Room Types section) exist.')
                                    ->content(fn (?Hotel $record) => static::renderLines($record?->room_categories))
                                    ->columnSpanFull()
                                    ->visible(fn (?Hotel $record) => $record !== null),

                                Textarea::make('nearby_attractions')
                                    ->label('Nearby Attractions')
                                    ->rows(3)
                                    ->columnSpanFull()
                                    ->visible(fn (?Hotel $record) => $record === null),
                                Placeholder::make('nearby_attractions_view')
                                    ->label('Nearby Attractions')
                                    ->content(fn (?Hotel $record) => static::renderLines($record?->nearby_attractions))
                                    ->columnSpanFull()
                                    ->visible(fn (?Hotel $record) => $record !== null),

                                Textarea::make('restaurants_cafes')
                                    ->label('Restaurants & Cafés Nearby')
                                    ->rows(3)
                                    ->columnSpanFull()
                                    ->visible(fn (?Hotel $record) => $record === null),
                                Placeholder::make('restaurants_cafes_view')
                                    ->label('Restaurants & Cafés Nearby')
                                    ->content(fn (?Hotel $record) => static::renderLines($record?->restaurants_cafes))
                                    ->columnSpanFull()
                                    ->visible(fn (?Hotel $record) => $record !== null),

                                Textarea::make('top_attractions')
                                    ->label('Top Attractions Nearby')
                                    ->rows(3)
                                    ->columnSpanFull()
                                    ->visible(fn (?Hotel $record) => $record === null),
                                Placeholder::make('top_attractions_view')
                                    ->label('Top Attractions Nearby')
                                    ->content(fn (?Hotel $record) => static::renderLines($record?->top_attractions))
                                    ->columnSpanFull()
                                    ->visible(fn (?Hotel $record) => $record !== null),
                            ]),

                        Tab::make('Policies & Rules')
                            ->label('📜 Policies & Rules')
                            ->icon('heroicon-o-shield-check')
                            ->schema([
                                RichEditor::make('mandatory_fees')
                                    ->label('Mandatory Fees')
                                    ->columnSpanFull()
                                    ->toolbarButtons(['bold', 'italic', 'bulletList', 'orderedList', 'link', 'redo', 'undo'])
                                    ->visible(fn (?Hotel $record) => $record === null),
                                Placeholder::make('mandatory_fees_view')
                                    ->label('Mandatory Fees')
                                    ->content(fn (?Hotel $record) => new HtmlString('<div style="font-size:13.5px; line-height:1.7;">'.($record?->mandatory_fees ?: '<span style="opacity:.5;">—</span>').'</div>'))
                                    ->columnSpanFull()
                                    ->visible(fn (?Hotel $record) => $record !== null),

                                RichEditor::make('special_instructions')
                                    ->label('Special Instructions')
                                    ->columnSpanFull()
                                    ->toolbarButtons(['bold', 'italic', 'bulletList', 'orderedList', 'link', 'redo', 'undo'])
                                    ->visible(fn (?Hotel $record) => $record === null),
                                Placeholder::make('special_instructions_view')
                                    ->label('Special Instructions')
                                    ->content(fn (?Hotel $record) => new HtmlString('<div style="font-size:13.5px; line-height:1.7;">'.($record?->special_instructions ?: '<span style="opacity:.5;">—</span>').'</div>'))
                                    ->columnSpanFull()
                                    ->visible(fn (?Hotel $record) => $record !== null),

                                RichEditor::make('know_before_you_go')
                                    ->label('Know Before You Go')
                                    ->columnSpanFull()
                                    ->toolbarButtons(['bold', 'italic', 'bulletList', 'orderedList', 'link', 'redo', 'undo'])
                                    ->visible(fn (?Hotel $record) => $record === null),
                                Placeholder::make('know_before_you_go_view')
                                    ->label('Know Before You Go')
                                    ->content(fn (?Hotel $record) => new HtmlString('<div style="font-size:13.5px; line-height:1.7;">'.($record?->know_before_you_go ?: '<span style="opacity:.5;">—</span>').'</div>'))
                                    ->columnSpanFull()
                                    ->visible(fn (?Hotel $record) => $record !== null),

                                Placeholder::make('house_rules_preview')
                                    ->label('House Rules')
                                    ->content(fn (?Hotel $record) => static::renderKeyedSections($record?->house_rules))
                                    ->columnSpanFull(),
                            ]),

                        Tab::make('Rating & Reviews')
                            ->label('⭐ Ratings & Reviews')
                            ->icon('heroicon-o-star')
                            ->schema([
                                Grid::make(2)->schema([
                                    Placeholder::make('rating_score_view')
                                        ->label('Guest Review Score')
                                        ->content(fn (?Hotel $record) => $record?->rating_score ? number_format($record->rating_score, 1).' / 5 ('.($record->review_count ?? 0).' reviews)' : '—'),

                                    Placeholder::make('rating_tagline_view')
                                        ->label('Accolade / Tagline')
                                        ->content(fn (?Hotel $record) => $record?->rating_tagline ?: '—'),
                                ]),

                                Placeholder::make('reviews_preview')
                                    ->label('Guest Reviews')
                                    ->content(fn (?Hotel $record) => static::renderReviews($record))
                                    ->columnSpanFull(),
                            ]),
                    ])->columnSpanFull(),
            ]);
    }

    protected static function categoryOptions(): array
    {
        return [
            'beach_resort'    => '🏖️  Beach Resort',
            'city_luxury'     => '🏙️  City Luxury',
            'honeymoon'       => '💑  Honeymoon',
            'family_friendly' => '👨‍👩‍👧  Family Friendly',
        ];
    }

    /**
     * Renders a newline-separated text field (nearby_attractions,
     * restaurants_cafes, top_attractions, room_categories) as a bullet list.
     */
    protected static function renderLines(?string $text): HtmlString
    {
        $lines = collect(preg_split('/\r\n|\r|\n/', (string) $text))
            ->map(fn ($line) => trim($line))
            ->filter();

        if ($lines->isEmpty()) {
            return new HtmlString('<p style="opacity:0.6; font-size:13px;">None set.</p>');
        }

        return new HtmlString(
            '<ul style="margin:0; padding-left:18px; font-size:13.5px; line-height:1.8;">'
            .$lines->map(fn ($line) => '<li>'.e($line).'</li>')->implode('')
            .'</ul>'
        );
    }

    /**
     * Renders a list of strings (e.g. amenity names) as pill badges.
     */
    protected static function renderBadges(array $items): HtmlString
    {
        if (empty($items)) {
            return new HtmlString('<p style="opacity:0.6; font-size:13px;">None selected.</p>');
        }

        $html = '<div style="display:flex; flex-wrap:wrap; gap:8px;">';
        foreach ($items as $item) {
            $html .= '<span style="background:rgba(201,168,76,0.12); border:1px solid rgba(201,168,76,0.3); border-radius:999px; padding:4px 12px; font-size:12.5px;">'.e($item).'</span>';
        }
        $html .= '</div>';

        return new HtmlString($html);
    }

    /**
     * Renders a JSON key => value|list section (description_sections,
     * bottom_sections, house_rules) as read-only HTML. Values may be a
     * plain string (often HTML from the frontend's own rendering) or a
     * list of strings.
     */
    protected static function renderKeyedSections(?array $sections): HtmlString
    {
        if (empty($sections)) {
            return new HtmlString('<p style="opacity:0.6; font-size:13px;">None set.</p>');
        }

        $html = '<div style="display:flex; flex-direction:column; gap:14px;">';
        foreach ($sections as $heading => $value) {
            $body = is_array($value)
                ? '<ul style="margin:4px 0 0; padding-left:18px;">'.collect($value)->map(fn ($line) => '<li>'.e((string) $line).'</li>')->implode('')
                .'</ul>'
                : '<div>'.$value.'</div>';

            $html .= '<div style="border:1px solid rgba(128,128,128,0.25); border-radius:8px; padding:10px 14px;">'
                .'<div style="font-weight:600; font-size:13px; margin-bottom:4px;">'.e((string) $heading).'</div>'
                .'<div style="font-size:13px; opacity:0.85;">'.$body.'</div>'
                .'</div>';
        }
        $html .= '</div>';

        return new HtmlString($html);
    }

    protected static function renderReviews(?Hotel $record): HtmlString
    {
        if (! $record) {
            return new HtmlString('<p style="opacity:0.6; font-size:13px;">Save the hotel first to see reviews.</p>');
        }

        $reviews = Review::query()
            ->where('vertical', 'hotel')
            ->where('reference_id', $record->id)
            ->latest()
            ->get();

        if ($reviews->isEmpty()) {
            return new HtmlString('<p style="opacity:0.6; font-size:13px;">No reviews yet.</p>');
        }

        $html = '<div style="display:flex; flex-direction:column; gap:10px;">';
        foreach ($reviews as $review) {
            $stars = $review->rating ? str_repeat('⭐', (int) $review->rating) : '—';
            $status = $review->is_published
                ? '<span style="color:#4ade80;">Published</span>'
                : '<span style="color:#facc15;">Unpublished</span>';

            $html .= '<div style="border:1px solid rgba(128,128,128,0.25); border-radius:8px; padding:10px 14px;">'
                .'<div style="display:flex; justify-content:space-between; font-size:13px; margin-bottom:4px;">'
                .'<strong>'.e($review->author_name).'</strong>'
                .'<span>'.$stars.' &middot; '.$status.'</span>'
                .'</div>'
                .($review->title ? '<div style="font-weight:600; font-size:13px;">'.e($review->title).'</div>' : '')
                .'<div style="font-size:13px; opacity:0.85;">'.e($review->body).'</div>'
                .'</div>';
        }
        $html .= '</div>';

        return new HtmlString($html);
    }
}
