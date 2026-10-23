<?php

namespace App\Filament\Resources\Packages\Schemas;

use App\Models\Amenity;
use App\Models\Destination;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;
use Illuminate\Support\HtmlString;

class PackageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([

            Tabs::make('Package Details')
                ->persistTabInQueryString()
                ->tabs([

                    // ──────────────────────────────────────────────────────────
                    // TAB 1 — Basic Information
                    // ──────────────────────────────────────────────────────────
                    Tab::make('Basic Info')
                        ->icon('heroicon-o-document-text')
                        ->schema([

                            TextInput::make('title')
                                ->label('Package Title')
                                ->placeholder('e.g.  Jibhi & Tirthan Valley — 2 Nights 3 Days')
                                ->helperText('The full name of this trip, exactly as it appears on the brochure or PDF.')
                                ->required()
                                ->live(onBlur: true)
                                ->afterStateUpdated(fn (?string $state, $set, string $operation) => $operation === 'create' ? $set('slug', Str::slug((string) $state)) : null) // only on create: a new slug breaks old links
                                ->columnSpanFull(),

                            TextInput::make('slug')
                                ->label('Web address')
                                ->helperText(fn (string $operation) => $operation === 'create' ? 'Auto-generated from the title. You can edit it, but use hyphens instead of spaces.' : 'This is the web address of the page. Changing it breaks links people already have (Google, WhatsApp shares) — only change it if you must.')
                                ->required()
                                ->unique(ignoreRecord: true),

                            TextInput::make('hero_eyebrow')
                                ->label('Location Tag')
                                ->placeholder('e.g.  Himachal Pradesh, India')
                                ->helperText('Short location line shown above the title on the website.'),

                            Textarea::make('description')
                                ->label('Package Description')
                                ->placeholder('Write 2–4 sentences describing the essence of this trip...')
                                ->helperText('Copy the main overview paragraph from the PDF. Appears under the title on the website.')
                                ->rows(5)
                                ->required()
                                ->columnSpanFull(),

                            Select::make('region_type')
                                ->label('Region')
                                ->options([
                                    'domestic'      => 'Domestic  (within India)',
                                    'international' => 'International  (outside India)',
                                    'spiritual'     => 'Spiritual',
                                ])
                                ->helperText('Is this trip within India, abroad, or a spiritual journey?')
                                ->required(),

                            Select::make('tour_type')
                                ->label('Tour Type')
                                ->options([
                                    'group'  => 'Group Tour  (fixed departure dates)',
                                    'custom' => 'Custom / Private Tour  (tailor-made)',
                                ])
                                ->helperText('Group tours go on scheduled dates. Custom tours are arranged per group.')
                                ->required()
                                ->live(),

                            Select::make('destination_id')
                                ->label('Destination')
                                ->relationship('destination', 'name', fn ($query) => $query->whereJsonContains('for', 'package')->where('is_active', true)->orderBy('name'))
                                ->searchable()
                                ->preload()
                                ->nullable()
                                ->helperText('Choose the main destination for this package.'),

                        ])->columns(2),

                    // ──────────────────────────────────────────────────────────
                    // TAB 2 — Pricing & Logistics
                    // ──────────────────────────────────────────────────────────
                    Tab::make('Pricing')
                        ->icon('heroicon-o-banknotes')
                        ->schema([

                            TextInput::make('price_from')
                                ->label('Starting Price  (₹)')
                                ->placeholder('e.g.  6999')
                                ->helperText(fn ($get) => $get('tour_type') === 'custom'
                                    ? 'Optional for a custom tour — leave blank if it\'s priced per group after enquiry.'
                                    : 'The lowest per-person price shown on the PDF.')
                                ->required(fn ($get) => $get('tour_type') !== 'custom')
                                ->numeric()
                                ->minValue(0)
                                ->prefix('₹'),

                            TextInput::make('duration_nights')
                                ->label('Duration  (number of nights)')
                                ->placeholder('e.g.  2')
                                ->helperText(fn ($get) => $get('tour_type') === 'custom'
                                    ? 'Optional for a custom tour — leave blank if it varies per group.'
                                    : 'A "2 Night 3 Day" trip = 2 nights.')
                                ->required(fn ($get) => $get('tour_type') !== 'custom')
                                ->numeric()
                                ->minValue(0)
                                ->maxValue(65535)
                                ->live(onBlur: true)
                                ->suffix('nights'),

                            TextInput::make('booking_amount')
                                ->label('Advance Booking Amount per person  (₹)')
                                ->placeholder('e.g.  2000')
                                ->helperText('Guests can pay this per person to reserve their seat and pay the rest later (up to '.\App\Services\PackageBookingService::BALANCE_DUE_DAYS_BEFORE.' days before departure). Leave empty to take full payment only. 5% GST is added on top.')
                                ->numeric()
                                ->minValue(0)
                                ->prefix('₹')
                                ->nullable(),

                            Toggle::make('online_booking')
                                ->label('Guests can book and pay online')
                                ->helperText('Shows a "Book Now" box with dates and seats on the website. Needs a price, and departure dates in the Departures tab. Only for group tours.')
                                ->default(true)
                                ->visible(fn ($get) => $get('tour_type') !== 'custom')
                                ->columnSpanFull(),

                            Repeater::make('sharing_prices')
                                ->label('Room sharing prices  (optional)')
                                ->helperText('Only if the price depends on room sharing — e.g. Quad ₹17,999, Triple ₹18,999, Double ₹21,999 per person. Guests pick one when booking. Leave empty to use the Starting Price for everyone. Prices are before 5% GST.')
                                ->schema([
                                    TextInput::make('label')
                                        ->label('Sharing')
                                        ->placeholder('e.g.  Triple sharing')
                                        ->required()
                                        ->maxLength(60),
                                    TextInput::make('price')
                                        ->label('Price per person')
                                        ->numeric()
                                        ->minValue(1)
                                        ->prefix('₹')
                                        ->required(),
                                ])
                                ->columns(2)
                                ->defaultItems(0)
                                ->addActionLabel('+ Add a sharing price')
                                ->reorderable()
                                ->itemLabel(fn (array $state): string => filled($state['label'] ?? null) ? $state['label'].(filled($state['price'] ?? null) ? '  —  ₹'.number_format((float) $state['price']) : '') : 'New sharing price')
                                ->visible(fn ($get) => $get('tour_type') !== 'custom')
                                ->columnSpanFull(),

                            TagsInput::make('departure_from')
                                ->label('Departure Cities')
                                ->placeholder('Type a city and press Enter  —  e.g.  Delhi')
                                ->helperText('The cities from which the trip begins. Press Enter after each city.'),

                            TagsInput::make('arrival_cities')
                                ->label('Arrival Cities')
                                ->placeholder('Type a city and press Enter  —  e.g.  Manali')
                                ->helperText('The cities where the trip ends. Press Enter after each city.'),

                            TextInput::make('meals_info')
                                ->label('Meals Included')
                                ->placeholder('e.g.  2 Breakfasts + 2 Dinners  (MAP AI)')
                                ->helperText('Copy directly from the PDF meals section.'),

                            TextInput::make('transport_info')
                                ->label('Transport')
                                ->placeholder('e.g.  Volvo Bus / Tempo Traveller')
                                ->helperText('The type of transport used during the trip.'),

                            TextInput::make('stay_info')
                                ->label('Accommodation / Stay')
                                ->placeholder('e.g.  Cosy riverside homestay in Jibhi')
                                ->helperText('Describe where guests will be staying.'),

                        ])->columns(2),

                    // ──────────────────────────────────────────────────────────
                    // TAB 3 — Trip Highlights
                    // ──────────────────────────────────────────────────────────
                    Tab::make('Highlights')
                        ->icon('heroicon-o-star')
                        ->schema([

                            Repeater::make('highlights')
                                ->relationship('highlights')
                                ->label('Trip Highlights')
                                ->helperText('Add the key selling points — usually the bold bullets or feature cards at the top of the PDF. Drag rows to reorder.')
                                ->schema([

                                    Select::make('icon')
                                        ->label('Icon')
                                        ->options(static::highlightIcons())
                                        ->helperText('Pick the closest matching icon.')
                                        ->required()
                                        ->default('fa-solid fa-star')
                                        ->searchable(),

                                    TextInput::make('title')
                                        ->label('Highlight Title')
                                        ->placeholder('e.g.  Jalori Pass Trek')
                                        ->helperText('A short, punchy title for this highlight.')
                                        ->required(),

                                    Textarea::make('description')
                                        ->label('Short Description  (optional)')
                                        ->placeholder('e.g.  Trek to 10,280 ft with panoramic Himalayan views')
                                        ->helperText('One sentence expanding on this highlight.')
                                        ->rows(2)
                                        ->columnSpanFull(),

                                ])
                                ->columns(2)
                                ->addActionLabel('+ Add a Highlight')
                                ->reorderable('sort_order')
                                ->orderColumn('sort_order')
                                ->collapsible()
                                ->collapsed(false)
                                ->itemLabel(fn (array $state): string => $state['title'] ?? 'New Highlight')
                                ->columnSpanFull(),

                        ]),

                    // ──────────────────────────────────────────────────────────
                    // TAB 4 — Day-by-Day Itinerary
                    // ──────────────────────────────────────────────────────────
                    Tab::make('Itinerary')
                        ->icon('heroicon-o-calendar-days')
                        ->schema([

                            Repeater::make('itineraryDays')
                                ->relationship('itineraryDays')
                                ->label('Day-by-Day Itinerary')
                                ->helperText('Copy each day directly from the "Itinerary" section of the PDF. Use the drag handle (⠿) to reorder days.')
                                ->schema([

                                    TextInput::make('day_number')
                                        ->label('Day No.')
                                        ->helperText('e.g. 0 for Day 0 or 1 for Day 1')
                                        ->numeric()
                                        ->default(1)
                                        ->required()
                                        ->minValue(0)
                                        ->columnSpan(1),

                                    TextInput::make('title')
                                        ->label('Day Title')
                                        ->placeholder('e.g.  Arrival in Jibhi | Waterfall Walk')
                                        ->helperText('A short heading for this day — copy from the PDF.')
                                        ->required()
                                        ->columnSpan(3),

                                    Textarea::make('description')
                                        ->label('Day Description')
                                        ->placeholder('Describe what guests do and experience on this day...')
                                        ->helperText('Copy the full paragraph for this day from the PDF.')
                                        ->rows(4)
                                        ->columnSpanFull(),

                                    FileUpload::make('image')
->disk('public')
                                        ->label('Day Photo  (optional)')
                                        ->helperText('An optional image for this specific day.')
                                        ->image()
                                        ->imagePreviewHeight('150')
                                        ->saveUploadedFileUsing(fn ($file) => app(\App\Services\ImageOptimizer::class)->optimizeAndSave($file, 'thumbnail', 'packages/itinerary'))
                                        ->columnSpanFull(),

                                    TagsInput::make('chips')
                                        ->label('Activity Tags  (optional)')
                                        ->placeholder('Type an activity and press Enter  —  e.g.  Jibhi Waterfall')
                                        ->helperText('Quick-glance activity tags shown under the day title on the website.')
                                        ->columnSpanFull(),

                                ])
                                ->columns(4)
                                ->addActionLabel('+ Add a Day')
                                ->reorderable('sort_order')
                                ->orderColumn('sort_order')
                                ->collapsible()
                                ->collapsed(false)
                                ->itemLabel(fn (array $state): string =>
                                    (isset($state['day_number']) && isset($state['title']) && $state['title'] !== '')
                                        ? 'Day ' . $state['day_number'] . '  —  ' . $state['title']
                                        : 'New Day'
                                )
                                ->columnSpanFull(),

                        ]),

                    // ──────────────────────────────────────────────────────────
                    // TAB — Hotels & Stays ("Where You'll Stay" on the website)
                    // ──────────────────────────────────────────────────────────
                    Tab::make('Hotels & Stays')
                        ->icon('heroicon-o-building-office-2')
                        ->schema([

                            Repeater::make('stays')
                                ->relationship('stays')
                                ->label('Where guests stay')
                                ->helperText(function ($get) {
                                    $text = 'Add one row per hotel, camp or homestay, in trip order. Pick a hotel from your hotel list and its photo, stars and page link come with it — or just type the name for a camp or homestay. Shown as "Where You\'ll Stay" on the package page.';
                                    $limit = (int) $get('duration_nights');
                                    if ($limit < 1) {
                                        return $text.' Fill in "Duration (number of nights)" in the Pricing tab and the nights here are checked against it.';
                                    }
                                    $used = static::stayNights($get('stays'));
                                    $line = 'Nights used: '.$used.' of '.$limit.' ('.$limit.'-night package).';

                                    return new HtmlString(e($text).'<br><strong style="color:'.($used > $limit ? '#dc2626' : '#16a34a').';">'.e($line)
                                        .($used > $limit ? ' That is '.($used - $limit).' too many — lower the nights before saving.' : '').'</strong>');
                                })
                                // All the stays' nights together can't be more than the
                                // package's "Duration (number of nights)" in the Pricing
                                // tab — the same nights the website advertises.
                                ->rule(fn ($get) => function (string $attribute, $value, \Closure $fail) use ($get) {
                                    $limit = (int) $get('duration_nights');
                                    $used = static::stayNights($value);
                                    if ($limit > 0 && $used > $limit) {
                                        $fail('The stays add up to '.$used.' nights, but this is a '.$limit.'-night package (Duration in the Pricing tab). Lower the nights here, or change the duration.');
                                    }
                                    $lastDay = collect(\App\Models\PackageStay::dayPlan(is_array($value) ? $value : []))->max(fn ($d) => $d['out'] ?? $d['in']);
                                    // A stay can't start before the one above it has checked out.
                                    $plan = array_values(\App\Models\PackageStay::dayPlan(is_array($value) ? $value : []));
                                    $names = array_values(array_map(fn ($row) => (string) ($row['name'] ?? '') ?: 'the next stay', is_array($value) ? $value : []));
                                    foreach ($plan as $i => $days) {
                                        $before = $plan[$i - 1] ?? null;
                                        if ($before && $before['out'] !== null && $days['in'] < $before['out']) {
                                            $fail('"'.$names[$i].'" checks in on Day '.$days['in'].', but the stay above it checks out on Day '.$before['out'].'. Two stays cannot overlap — change the days.');

                                            return;
                                        }
                                    }
                                    if ($limit > 0 && $used <= $limit && $lastDay > $limit + 1) {
                                        $fail('A stay ends on Day '.$lastDay.', but this '.$limit.'-night trip ends on Day '.($limit + 1).'. Check the "Check-in on day" boxes.');
                                    }
                                })
                                ->schema([

                                    // One or the other, never both: pick a hotel from the
                                    // list — its name, place, stars and type fill in by
                                    // themselves and are locked, so nobody can overwrite
                                    // them — OR type the details in by hand (typing a name
                                    // locks the hotel list). The locked copies are saved
                                    // empty: the website always reads the hotel itself.
                                    Select::make('hotel_id')
                                        ->label('Choose from your hotel list')
                                        ->relationship('hotel', 'title')
                                        ->getOptionLabelFromRecordUsing(fn (\App\Models\Hotel $record) => $record->title.($record->destination?->name ? ' — '.$record->destination->name : ''))
                                        ->searchable()
                                        ->live()
                                        ->disabled(fn ($get) => blank($get('hotel_id')) && filled($get('name')))
                                        ->afterStateUpdated(function ($state, $set) {
                                            $hotel = filled($state) ? \App\Models\Hotel::with('destination')->find($state) : null;
                                            $set('name', $hotel?->title);
                                            $set('location', $hotel?->destination?->name);
                                            $set('star_rating', $hotel?->star_rating ?: null);
                                            $set('check_in_time', static::timeForPicker($hotel?->check_in_time));
                                            $set('check_out_time', static::timeForPicker($hotel?->check_out_time));
                                            if ($hotel) {
                                                $set('stay_type', static::stayTypeFor($hotel->title));
                                            }
                                        })
                                        ->helperText(fn ($get) => blank($get('hotel_id')) && filled($get('name'))
                                            ? 'Locked because a name is typed in below. Clear the name to choose a hotel from the list instead.'
                                            : 'Its name, place, stars and photo fill in by themselves. Leave empty for a camp, homestay or any place not in your list.')
                                        ->columnSpan(2),

                                    TextInput::make('name')
                                        ->label('OR type the name')
                                        ->placeholder('e.g.  Riverside Camps, Kasol')
                                        ->live(debounce: 500)
                                        ->disabled(fn ($get) => filled($get('hotel_id')))
                                        ->afterStateHydrated(fn ($component, $get) => filled($get('hotel_id')) ? $component->state(\App\Models\Hotel::find($get('hotel_id'))?->title) : null)
                                        ->dehydrated()
                                        ->dehydrateStateUsing(fn ($state, $get) => filled($get('hotel_id')) ? null : $state)
                                        ->helperText(fn ($get) => filled($get('hotel_id'))
                                            ? 'Filled in from the hotel and locked. Clear the hotel to type a name instead.'
                                            : 'For a camp, homestay or any place not in your hotel list.')
                                        ->required(fn ($get) => blank($get('hotel_id')))
                                        ->maxLength(255)
                                        ->columnSpan(2),

                                    Select::make('stay_type')
                                        ->label('Type')
                                        ->options(\App\Models\PackageStay::TYPES)
                                        ->default('hotel')
                                        ->required(),

                                    TextInput::make('location')
                                        ->label('Place')
                                        ->placeholder('e.g.  Manali')
                                        ->disabled(fn ($get) => filled($get('hotel_id')))
                                        ->afterStateHydrated(fn ($component, $get) => filled($get('hotel_id')) ? $component->state(\App\Models\Hotel::with('destination')->find($get('hotel_id'))?->destination?->name) : null)
                                        ->dehydrated()
                                        ->dehydrateStateUsing(fn ($state, $get) => filled($get('hotel_id')) ? null : $state)
                                        ->maxLength(255),

                                    // Worked out from the days below (check-out − check-in),
                                    // never typed — so the days and nights can't disagree.
                                    TextInput::make('nights')
                                        ->label('Nights here')
                                        ->numeric()
                                        ->default(1)
                                        ->minValue(1)
                                        ->maxValue(60)
                                        ->disabled()
                                        ->dehydrated()
                                        ->helperText('Worked out from the check-in and check-out days below.'),

                                    Select::make('star_rating')
                                        ->label('Stars  (optional)')
                                        ->options([1 => '1 star', 2 => '2 stars', 3 => '3 stars', 4 => '4 stars', 5 => '5 stars'])
                                        ->placeholder(fn ($get) => filled($get('hotel_id')) ? 'The hotel has no star rating' : 'Not shown')
                                        ->disabled(fn ($get) => filled($get('hotel_id')))
                                        ->afterStateHydrated(fn ($component, $get) => filled($get('hotel_id')) ? $component->state(\App\Models\Hotel::find($get('hotel_id'))?->star_rating ?: null) : null)
                                        ->dehydrated()
                                        ->dehydrateStateUsing(fn ($state, $get) => filled($get('hotel_id')) ? null : $state),

                                    TextInput::make('room_type')
                                        ->label('Room  (optional)')
                                        ->placeholder('e.g.  Deluxe room, triple sharing')
                                        // Suggestions from the chosen hotel's own room types.
                                        ->datalist(fn ($get) => filled($get('hotel_id'))
                                            ? \App\Models\RoomType::where('hotel_id', $get('hotel_id'))->orderBy('name')->limit(50)->pluck('name')->filter()->unique()->values()->all()
                                            : [])
                                        ->helperText(fn ($get) => filled($get('hotel_id')) ? 'Start typing to pick one of this hotel\'s rooms, or write your own.' : null)
                                        ->maxLength(255)
                                        ->columnSpan(2),

                                    TextInput::make('meal_plan')
                                        ->label('Meals here  (optional)')
                                        ->placeholder('e.g.  Breakfast & dinner')
                                        ->maxLength(255)
                                        ->columnSpan(2),

                                    // Which trip days: left empty, a stay starts on the day
                                    // the one before it ends (the first on Day 1). Set it
                                    // only when the trip isn't back-to-back, e.g. a night
                                    // on the bus before this stay.
                                    TextInput::make('check_in_day')
                                        ->label('Check-in on day')
                                        ->numeric()
                                        ->minValue(0)
                                        // The latest day a stay can start and still check out by
                                        // the trip's last day (a 2-night trip ends on Day 3, so
                                        // Day 2 at the latest).
                                        ->maxValue(fn ($get) => static::latestCheckInDay($get('../../duration_nights'), 1))
                                        ->validationMessages(['max' => 'This stay would end after the trip. The latest it can start is Day :max.'])
                                        ->prefix('Day')
                                        ->live(onBlur: true)
                                        // A new check-in day: check-out becomes the next day (1
                                        // night) — change the check-out day for a longer stay.
                                        ->afterStateUpdated(function ($get, $set) {
                                            $set('nights', 1);
                                            static::refreshCheckOutDays($get, $set);
                                        })
                                        ->placeholder(fn ($component, $get) => (string) (static::stayDays($component, $get)['in'] ?? 1))
                                        ->helperText(fn ($get) => (int) $get('../../duration_nights') > 0
                                            ? 'Empty = right after the stay before. Latest: Day '.static::latestCheckInDay($get('../../duration_nights'), 1).'.'
                                            : 'Empty = right after the stay before.'),

                                    // Editable, and kept in step with "Nights here": typing a
                                    // check-out day sets the nights (check-out − check-in),
                                    // changing the nights moves the check-out day. Not saved
                                    // itself — the nights are.
                                    TextInput::make('check_out_day')
                                        ->label('Check-out on day')
                                        ->numeric()
                                        ->prefix('Day')
                                        ->dehydrated(false)
                                        ->live(onBlur: true)
                                        ->afterStateHydrated(fn ($component, $get) => $component->state(static::stayDays($component, $get)['out']))
                                        ->afterStateUpdated(function ($state, $component, $get, $set) {
                                            $in = static::stayDays($component, $get)['in'];
                                            if (filled($state) && (int) $state > $in) {
                                                $set('nights', (int) $state - $in);
                                                static::refreshCheckOutDays($get, $set);
                                            }
                                            // On/before check-in: left as typed, so the error below shows.
                                        })
                                        ->minValue(fn ($component, $get) => static::stayDays($component, $get)['in'] + 1)
                                        ->maxValue(fn ($get) => (int) $get('../../duration_nights') > 0 ? (int) $get('../../duration_nights') + 1 : 61)
                                        ->validationMessages([
                                            'min' => 'Check-out must be after the check-in day (Day :min at the earliest).',
                                            'max' => 'The trip ends on Day :max — check-out can\'t be later.',
                                        ])
                                        ->helperText(function ($component, $get) {
                                            $days = static::stayDays($component, $get);
                                            $lastDay = (int) $get('../../duration_nights') + 1;
                                            $nights = (int) $get('nights');
                                            // Say what the check-out day means, so a changed
                                            // "Nights here" is never a surprise.
                                            $meaning = $days['out'] !== null
                                                ? $nights.' '.($nights === 1 ? 'night' : 'nights').' here (Day '.$days['in'].' → Day '.$days['out'].').'
                                                : 'Type the day the guests check out.';
                                            if ($days['out'] !== null && $lastDay > 1 && $days['out'] > $lastDay) {
                                                return new HtmlString('<span style="color:#dc2626;font-weight:600;">'.e($meaning).' The trip ends on Day '.$lastDay.' — pick an earlier check-out day.</span>');
                                            }

                                            return $meaning.' Fills in as the next day — change it for a longer stay.';
                                        }),

                                    // Typed in for a camp/homestay; a hotel from the list
                                    // uses its own times (filled in and locked).
                                    ...collect(['check_in_time' => 'Check-in time', 'check_out_time' => 'Check-out time'])
                                        ->map(fn (string $label, string $field) => TimePicker::make($field)
                                            ->label($label.'  (optional)')
                                            // The browser's own time picker: hour, minute and
                                            // AM/PM columns to scroll and tap.
                                            ->seconds(false)
                                            ->native()
                                            ->format('H:i')
                                            // Open the hour/minute/AM-PM columns on a click anywhere
                                            // in the box, not only on the small clock icon.
                                            ->extraInputAttributes(['onclick' => 'try { this.showPicker(); } catch (e) {}', 'style' => 'cursor: pointer;'], merge: true)
                                            ->disabled(fn ($get) => filled($get('hotel_id')))
                                            ->afterStateHydrated(fn ($component, $get) => filled($get('hotel_id'))
                                                ? $component->state(static::timeForPicker(\App\Models\Hotel::find($get('hotel_id'))?->{$field}))
                                                : null)
                                            ->helperText(fn ($get) => filled($get('hotel_id')) ? 'The hotel\'s own time, locked.' : null)
                                            ->dehydrated()
                                            ->dehydrateStateUsing(fn ($state, $get) => filled($get('hotel_id')) ? null : $state)
                                            ->columnSpan(1))
                                        ->values()->all(),

                                    // The photo guests will see when nothing is uploaded below.
                                    \Filament\Forms\Components\Placeholder::make('hotel_photo')
                                        ->label('Hotel photo shown to guests')
                                        ->visible(fn ($get) => filled($get('hotel_id')))
                                        ->content(function ($get) {
                                            $path = \App\Models\HotelImage::where('hotel_id', $get('hotel_id'))->where('is_hidden', false)->orderBy('sort_order')->value('path');
                                            if (! $path) {
                                                return new HtmlString('<span style="color:#d97706;">This hotel has no photos in your hotel list. Upload one in "Photo" below, or add photos to the hotel.</span>');
                                            }
                                            $url = Str::startsWith($path, ['http://', 'https://']) ? $path : \Illuminate\Support\Facades\Storage::disk('public')->url($path);

                                            return new HtmlString('<img src="'.e($url).'" alt="" style="height:110px;border-radius:8px;object-fit:cover;">');
                                        })
                                        ->columnSpan(2),

                                    FileUpload::make('image')
                                        ->disk('public')
                                        ->label('Photo  (optional)')
                                        ->helperText(fn ($get) => filled($get('hotel_id')) ? 'Only if you want a different photo from the hotel\'s own.' : 'A photo of the place.')
                                        ->image()
                                        ->imagePreviewHeight('120')
                                        ->saveUploadedFileUsing(fn ($file) => app(\App\Services\ImageOptimizer::class)->optimizeAndSave($file, 'thumbnail', 'packages/stays'))
                                        ->columnSpan(2),

                                    Toggle::make('or_similar')
                                        ->label('Show "Subject to availability"')
                                        ->helperText('Tells guests this hotel depends on availability on their dates. Turn off when the hotel is confirmed.')
                                        ->default(true)
                                        ->columnSpan(2),

                                    Textarea::make('note')
                                        ->label('Short note  (optional)')
                                        ->placeholder('e.g.  River-facing tents with attached washrooms')
                                        ->rows(2)
                                        ->maxLength(500)
                                        ->columnSpanFull(),

                                ])
                                ->columns(4)
                                ->addActionLabel('+ Add a Stay')
                                ->reorderable('sort_order')
                                ->orderColumn('sort_order')
                                ->collapsible()
                                ->collapsed(false)
                                ->itemLabel(fn (array $state): string => trim(
                                    (filled($state['nights'] ?? null) ? $state['nights'].'N  ·  ' : '')
                                    .($state['name'] ?? null ?: (filled($state['hotel_id'] ?? null) ? (\App\Models\Hotel::find($state['hotel_id'])?->title ?? 'Hotel') : 'New Stay'))
                                    .(filled($state['location'] ?? null) ? '  —  '.$state['location'] : '')
                                ))
                                ->columnSpanFull(),

                        ]),

                    // ──────────────────────────────────────────────────────────
                    // TAB 5 — Departures
                    // ──────────────────────────────────────────────────────────
                    Tab::make('Departures')
                        ->icon('heroicon-o-calendar')
                        ->schema([

                            Repeater::make('departures')
                                ->relationship('departures')
                                ->label('Travel Dates / Batches')
                                ->helperText('Add fixed departure batches for this tour. They will be automatically grouped by month on the website.')
                                ->schema([
                                    // Pick the start date only: the end date follows from the
                                    // package's number of nights (Pricing tab), e.g. 3 nights
                                    // from 10 Nov → 13 Nov. It can still be changed by hand.
                                    DatePicker::make('start_date')
                                        ->label('Start Date')
                                        ->required()
                                        ->native(false)
                                        ->displayFormat('j M Y')
                                        ->closeOnDateSelection()
                                        ->live()
                                        ->afterStateUpdated(function ($state, $get, $set) {
                                            $nights = (int) $get('../../duration_nights');
                                            if ($state && $nights > 0) {
                                                $set('end_date', \Carbon\Carbon::parse($state)->addDays($nights)->toDateString());
                                            }
                                        })
                                        ->helperText(fn ($get) => (int) $get('../../duration_nights') > 0
                                            ? 'The end date fills in by itself ('.(int) $get('../../duration_nights').' nights later).'
                                            : 'Tip: set "Duration (number of nights)" in the Pricing tab and the end date fills in by itself.'),

                                    DatePicker::make('end_date')
                                        ->label('End Date')
                                        ->required()
                                        ->native(false)
                                        ->displayFormat('j M Y')
                                        ->closeOnDateSelection()
                                        ->live()
                                        ->minDate(fn ($get) => $get('start_date'))
                                        ->afterOrEqual('start_date')
                                        ->helperText(function ($get) {
                                            $start = $get('start_date');
                                            $end = $get('end_date');
                                            if (! $start || ! $end) {
                                                return null;
                                            }
                                            $n = \Carbon\Carbon::parse($start)->diffInDays(\Carbon\Carbon::parse($end));
                                            $expected = (int) $get('../../duration_nights');
                                            $text = $n.' night'.($n === 1 ? '' : 's').' / '.($n + 1).' day'.($n === 0 ? '' : 's');

                                            return $expected > 0 && $n !== $expected
                                                ? new HtmlString('<span style="color:#d97706;">'.$text.' — this package is '.$expected.' nights. Check the dates.</span>')
                                                : $text;
                                        }),

                                    TextInput::make('total_seats')
                                        ->label('Total seats  (optional)')
                                        ->helperText(function ($record) {
                                            if (! $record) {
                                                return 'Leave empty for no limit. The website shows "seats left" and stops bookings when full.';
                                            }
                                            $taken = $record->seatsTaken();

                                            return "Booked so far: {$taken}. Leave empty for no limit.";
                                        })
                                        ->numeric()
                                        ->minValue(0)
                                        ->maxValue(999)
                                        ->nullable(),

                                    TextInput::make('extra_price')
                                        ->label('Peak-date extra per person  (optional)')
                                        ->helperText('Added to the price for this date only — e.g. ₹2,000 on a long weekend.')
                                        ->numeric()
                                        ->minValue(0)
                                        ->prefix('₹')
                                        ->default(0),
                                ])
                                ->columns(2)
                                ->addActionLabel('+ Add a Batch')
                                ->collapsible()
                                ->collapsed(false)
                                ->itemLabel(fn (array $state): string =>
                                    (isset($state['start_date']) && isset($state['end_date']))
                                        ? \Carbon\Carbon::parse($state['start_date'])->format('D, j M Y') . ' — ' . \Carbon\Carbon::parse($state['end_date'])->format('D, j M Y')
                                            . '  ·  ' . ($n = \Carbon\Carbon::parse($state['start_date'])->diffInDays(\Carbon\Carbon::parse($state['end_date']))) . 'N/' . ($n + 1) . 'D'
                                            . (\Carbon\Carbon::parse($state['start_date'])->lt(today()) ? '  ·  past' : '')
                                            . (filled($state['total_seats'] ?? null) ? '  ·  ' . $state['total_seats'] . ' seats' : '')
                                        : 'New Batch'
                                )
                                ->columnSpanFull(),

                        ]),

                    // ──────────────────────────────────────────────────────────
                    // TAB 6 — Inclusions
                    // ──────────────────────────────────────────────────────────
                    Tab::make('Inclusions')
                        ->icon('heroicon-o-check-circle')
                        ->schema([

                            Repeater::make('inclusions')
                                ->relationship('inclusions')
                                ->label('What\'s Included')
                                ->helperText('List everything covered in the package price — from the "Inclusions" section of the PDF. Add one item per row.')
                                ->schema([

                                    Select::make('category')
                                        ->label('Category')
                                        ->options([
                                            'flight'   => '✈️  Flight',
                                            'hotel'    => '🏨  Hotel / Stay',
                                            'meal'     => '🍽️  Meal',
                                            'activity' => '🎯  Activity / Sightseeing',
                                            'transfer' => '🚌  Transport / Transfer',
                                            'other'    => '📌  Other',
                                        ])
                                        ->helperText('Choose the best category.')
                                        ->required()
                                        ->default('other'),

                                    TextInput::make('label')
                                        ->label('What\'s included?')
                                        ->placeholder('e.g.  Breakfast and dinner at the homestay')
                                        ->helperText('Copy the inclusion text from the PDF.')
                                        ->required(),

                                ])
                                ->columns(2)
                                ->addActionLabel('+ Add an Inclusion')
                                ->reorderable('sort_order')
                                ->orderColumn('sort_order')
                                ->collapsible()
                                ->collapsed(false)
                                ->itemLabel(fn (array $state): string => $state['label'] ?? 'New Inclusion')
                                ->columnSpanFull(),

                        ]),

                    // ──────────────────────────────────────────────────────────
                    // TAB 6 — Exclusions
                    // ──────────────────────────────────────────────────────────
                    Tab::make('Exclusions')
                        ->icon('heroicon-o-x-circle')
                        ->schema([

                            Repeater::make('exclusions')
                                ->relationship('exclusions')
                                ->label('What\'s NOT Included')
                                ->helperText('List things guests need to arrange or pay for themselves — from the "Exclusions" section of the PDF. Add one item per row.')
                                ->schema([

                                    TextInput::make('name')
                                        ->label('Not included item')
                                        ->placeholder('e.g.  Personal travel insurance')
                                        ->helperText('Copy each exclusion line from the PDF.')
                                        ->required()
                                        ->columnSpanFull(),

                                ])
                                ->addActionLabel('+ Add an Exclusion')
                                ->reorderable('sort_order')
                                ->orderColumn('sort_order')
                                ->collapsible()
                                ->collapsed(false)
                                ->itemLabel(fn (array $state): string => $state['name'] ?? 'New Exclusion')
                                ->columnSpanFull(),

                        ]),

                    // ──────────────────────────────────────────────────────────
                    // TAB 7 — Photos & Notes
                    // ──────────────────────────────────────────────────────────
                    Tab::make('Photos & Notes')
                        ->icon('heroicon-o-photo')
                        ->schema([

                            FileUpload::make('hero_bg_image')
->disk('public')
                                ->label('Hero Banner Image')
                                ->helperText('The large background image at the top of the package page. Best size: 1920 × 1080 px (landscape/wide photo).')
                                ->image()
                                ->imagePreviewHeight('200')
                                ->saveUploadedFileUsing(fn ($file) => app(\App\Services\ImageOptimizer::class)->optimizeAndSave($file, 'hero', 'packages/heroes'))
                                ->columnSpanFull(),

                            Repeater::make('images')
                                ->relationship('images')
                                ->label('Gallery Photos')
                                ->helperText('Upload all the trip photos. Drag rows to reorder them — the first photo appears first in the gallery.')
                                ->schema([

                                    FileUpload::make('path')
->disk('public')
                                        ->label('Photo')
                                        ->helperText('Upload a photo from your computer.')
                                        ->image()
                                        ->imagePreviewHeight('130')
                                        ->saveUploadedFileUsing(fn ($file) => app(\App\Services\ImageOptimizer::class)->optimizeAndSave($file, 'thumbnail', 'packages/gallery'))
                                        ->required(),

                                    TextInput::make('alt_text')
                                        ->label('Photo Caption  (optional)')
                                        ->placeholder('e.g.  View from Jalori Pass at sunrise')
                                        ->helperText('A short description of this photo.'),

                                ])
                                ->columns(2)
                                ->addActionLabel('+ Add a Photo')
                                ->reorderable('sort_order')
                                ->orderColumn('sort_order')
                                ->collapsible()
                                ->collapsed(false)
                                        ->itemLabel(fn (array $state): string =>
                                    ! empty($state['alt_text']) ? $state['alt_text'] : 'Photo'
                                )
                                ->columnSpanFull(),

                            TagsInput::make('notes')
                                ->label('Important Notes')
                                ->helperText('Add any important notes for the itinerary PDF (e.g. "Trekking routes on Day 3 are subject to weather"). Press enter to add multiple notes.')
                                ->placeholder('Add a note...')
                                ->columnSpanFull(),

                        ]),

                    // ──────────────────────────────────────────────────────────
                    // TAB 8 — Amenities
                    // ──────────────────────────────────────────────────────────
                    Tab::make('Amenities')
                        ->icon('heroicon-o-check-badge')
                        ->schema([

                            Select::make('amenities')
                                ->label('Package Amenities / What\'s Included')
                                ->helperText('Pick all amenities that apply to this package. To add a new amenity, go to Content → Amenities in the sidebar.')
                                ->relationship('amenities', 'name', fn ($query) => $query->where('type', 'package')->orderBy('name'))
                                ->multiple()
                                ->preload()
                                ->searchable()
                                ->columnSpanFull(),

                        ]),

                    // ──────────────────────────────────────────────────────────
                    // TAB 9 — Publishing
                    // ──────────────────────────────────────────────────────────
                    Tab::make('Publish')
                        ->icon('heroicon-o-rocket-launch')
                        ->schema([

                            Toggle::make('is_active')
                                ->label('Published & Visible on Website')
                                ->helperText('Turn this ON when the package is ready for customers to see. Turn it OFF to hide it temporarily.')
                                ->required()
                                ->default(true),

                        ]),

                ])
                ->columnSpanFull(),

        ]);
    }

    /**
     * Re-works out every stay's "Check-out on day" box after a nights or day
     * change, so the rows below follow on. Called from a field inside a stay.
     */
    public static function refreshCheckOutDays($get, $set): void
    {
        $rows = $get('../../stays');
        if (! is_array($rows)) {
            return;
        }
        foreach (\App\Models\PackageStay::dayPlan($rows) as $key => $days) {
            $set('../../stays.'.$key.'.check_out_day', $days['out']);
        }
    }

    /** Last day a stay of $nights can start and still check out by the trip's last day (nights + 1). */
    public static function latestCheckInDay($packageNights, $nights): int
    {
        $packageNights = (int) $packageNights;
        if ($packageNights < 1) {
            return 60; // duration not set yet: nothing to check against
        }

        return max(1, $packageNights + 1 - max(1, (int) $nights));
    }

    /**
     * This stay's trip days ({in, out}) from the whole list as the form holds
     * it — the row's key is the second-last part of the field's state path.
     */
    public static function stayDays($component, $get): array
    {
        $rows = $get('../../stays') ?? [];
        $parts = explode('.', (string) $component->getStatePath());
        $key = $parts[count($parts) - 2] ?? null;

        return \App\Models\PackageStay::dayPlan(is_array($rows) ? $rows : [])[$key] ?? ['in' => 1, 'out' => null];
    }

    /** A hotel's "2:00 PM" as the time picker holds it ("14:00"); null if unreadable. */
    public static function timeForPicker(?string $time): ?string
    {
        if (blank($time)) {
            return null;
        }
        try {
            return \Illuminate\Support\Carbon::parse($time)->format('H:i');
        } catch (\Throwable) {
            return null;
        }
    }

    /** All the stays' nights added up, as the form holds them. */
    public static function stayNights($stays): int
    {
        return (int) collect(is_array($stays) ? $stays : [])->sum(fn ($s) => max(0, (int) ($s['nights'] ?? 0)));
    }

    /** A stay's type from the hotel's name: "… Resort" → Resort, "… Camps" → Camp; else Hotel (staff can change it). */
    public static function stayTypeFor(?string $title): string
    {
        $title = strtolower((string) $title);

        return match (true) {
            str_contains($title, 'resort') => 'resort',
            str_contains($title, 'camp') => 'camp',
            str_contains($title, 'homestay') || str_contains($title, 'home stay') => 'homestay',
            str_contains($title, 'cottage') => 'cottage',
            str_contains($title, 'houseboat') => 'houseboat',
            default => 'hotel',
        };
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Curated icon options for Trip Highlights
    // ─────────────────────────────────────────────────────────────────────────
    private static function highlightIcons(): array
    {
        return [
            'fa-solid fa-mountain'        => '🏔️   Mountain / Trek',
            'fa-solid fa-person-hiking'   => '🥾   Trekking / Hiking',
            'fa-solid fa-water'           => '💧   Waterfall / River',
            'fa-solid fa-tree'            => '🌲   Forest / Nature Walk',
            'fa-solid fa-sun'             => '☀️   Sunrise / Sunset View',
            'fa-solid fa-snowflake'       => '❄️   Snow / Winter Experience',
            'fa-solid fa-campground'      => '⛺   Camping / Bonfire Night',
            'fa-solid fa-fish'            => '🎣   Fishing / Angling',
            'fa-solid fa-camera'          => '📷   Photography Spots',
            'fa-solid fa-binoculars'      => '🔭   Wildlife / Bird Watching',
            'fa-solid fa-spa'             => '🧘   Relaxation / Wellness',
            'fa-solid fa-utensils'        => '🍽️   Local Food / Cuisine',
            'fa-solid fa-hotel'           => '🏨   Luxury / Premium Stay',
            'fa-solid fa-house'           => '🏡   Homestay / Village Life',
            'fa-solid fa-car-side'        => '🚗   Scenic Road Trip',
            'fa-solid fa-bus'             => '🚌   Group Transport',
            'fa-solid fa-plane'           => '✈️   Flight Included',
            'fa-solid fa-umbrella-beach'  => '🏖️   Beach / Coastal',
            'fa-solid fa-landmark'        => '🏛️   Heritage / Culture / Temple',
            'fa-solid fa-om'              => '🕉️   Spiritual',
            'fa-solid fa-map-marked-alt'  => '🗺️   Guided Sightseeing',
            'fa-solid fa-mountain-sun'    => '⛰️   Hill Station',
            'fa-solid fa-star'            => '⭐   General Highlight',
        ];
    }
}
