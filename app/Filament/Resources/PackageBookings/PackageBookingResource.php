<?php

namespace App\Filament\Resources\PackageBookings;

use App\Filament\Actions\RecordManualRefundAction;
use App\Filament\Resources\Bookings\HotelBookingActions;
use App\Filament\Resources\PackageBookings\Pages\ListPackageBookings;
use App\Filament\Resources\PackageBookings\Pages\ViewPackageBooking;
use App\Models\Booking;
use App\Models\Payment;
use App\Services\PackagePricingService;
use App\Support\PackageBookingStatus;
use Filament\Actions\ActionGroup;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

/**
 * Group package bookings made and paid on the website, in plain words for
 * non-technical staff. No create/edit form — staff act through the buttons
 * in PackageBookingActions.
 */
class PackageBookingResource extends Resource
{
    protected static ?string $model = Booking::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Bookings & Leads';

    protected static ?int $navigationSort = 5;

    protected static ?string $navigationLabel = 'Package Bookings';

    protected static ?string $slug = 'package-bookings';

    protected static ?string $modelLabel = 'package booking';

    protected static ?string $pluralModelLabel = 'package bookings';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-map';

    protected static ?string $recordTitleAttribute = 'reference';

    /** Booking pages open by booking number: /package-bookings/TYTZFN3UM1L. */
    protected static ?string $recordRouteKeyName = 'reference';

    /** Old links by internal number (/package-bookings/38) still open the booking. */
    public static function resolveRecordRouteBinding(int|string $key, ?\Closure $modifyQuery = null): ?\Illuminate\Database\Eloquent\Model
    {
        return parent::resolveRecordRouteBinding($key, $modifyQuery)
            ?? (ctype_digit((string) $key) ? static::getEloquentQuery()->whereKey((int) $key)->first() : null);
    }

    /** Link to a booking's page by its booking number. */
    public static function bookingUrl(Booking $record): string
    {
        return static::getUrl('view', ['record' => $record->reference]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('vertical', 'package')->with(['package', 'payments']);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getNavigationBadge(): ?string
    {
        $count = PackageBookingStatus::applyNeedsAttention(static::getEloquentQuery())->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Bookings that need your attention';
    }

    /** What the guest has paid so far (all payments, before refunds). */
    public static function paidSoFar(Booking $record): float
    {
        return round((float) $record->payments->whereIn('status', Booking::PAID_PAYMENT_STATUSES)->sum('amount'), 2);
    }

    /** Balance still to pay incl. GST (before the online payment charge), or null. */
    public static function balanceWithGst(Booking $record): ?float
    {
        if (! $record->packageBalanceDue()) {
            return null;
        }
        $c = PackagePricingService::charge((float) $record->package_balance_amount);

        return round($c['base'] + $c['gst'], 2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordUrl(fn (Booking $record) => self::bookingUrl($record))
            ->columns([
                TextColumn::make('reference')->label('Booking No.')->searchable()->copyable()->weight('bold'),
                TextColumn::make('lead_guest_name')
                    ->label('Guest')
                    ->description(fn (Booking $record) => $record->guest_phone)
                    ->searchable(['lead_guest_name', 'guest_email', 'guest_phone']),
                TextColumn::make('package.title')
                    ->label('Package')
                    ->description(fn (Booking $record) => $record->package_option && $record->package_option !== 'Per person' ? $record->package_option : null)
                    ->wrap()
                    ->searchable(),
                TextColumn::make('check_in')
                    ->label('Departure')
                    ->formatStateUsing(fn ($state) => $state ? Carbon::parse($state)->format('D, j M Y') : '—')
                    ->sortable(),
                TextColumn::make('pax_adults')->label('Travellers')->formatStateUsing(fn ($state, Booking $record) => $state.($record->pax_infants ? ' + '.$record->pax_infants.' infant' : '')),
                TextColumn::make('paid')
                    ->label('Paid')
                    ->state(fn (Booking $record) => self::paidSoFar($record))
                    ->money('INR'),
                TextColumn::make('balance')
                    ->label('Balance due')
                    ->state(fn (Booking $record) => self::balanceWithGst($record))
                    ->money('INR')
                    ->placeholder('—')
                    ->description(fn (Booking $record) => $record->packageBalanceDue() ? 'by '.$record->package_balance_due_on?->format('j M') : null),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (Booking $record) => PackageBookingStatus::for($record)['label'])
                    ->color(fn (Booking $record) => PackageBookingStatus::for($record)['color'])
                    ->tooltip(fn (Booking $record) => PackageBookingStatus::for($record)['help']),
                TextColumn::make('created_at')->label('Booked on')->dateTime('j M Y, g:i A')->sortable(),
                TextColumn::make('guest_email')->label('Email')->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->searchPlaceholder('Search by booking no., guest, phone, email or package')
            ->filters([
                SelectFilter::make('status')->label('Status')->options(PackageBookingStatus::filterOptions()),
                SelectFilter::make('package_id')->label('Package')->relationship('package', 'title')->searchable(),
                Filter::make('check_in')
                    ->label('Departure date')
                    ->schema([
                        DatePicker::make('from')->label('Departing from'),
                        DatePicker::make('until')->label('Departing until'),
                    ])
                    ->query(fn (Builder $query, array $data) => $query
                        ->when($data['from'] ?? null, fn (Builder $q, $date) => $q->whereDate('check_in', '>=', $date))
                        ->when($data['until'] ?? null, fn (Builder $q, $date) => $q->whereDate('check_in', '<=', $date))),
            ])
            ->recordActions([
                ViewAction::make()->label('Open')->url(fn (Booking $record) => self::bookingUrl($record)),
                ActionGroup::make([
                    PackageBookingActions::markBalanceReceived(),
                    HotelBookingActions::downloadInvoice(),
                    HotelBookingActions::addNote(),
                    RecordManualRefundAction::make(onlyWhenDue: true),
                    PackageBookingActions::cancelAndRefund(),
                ])->label('More')->button()->color('gray'),
            ])
            ->emptyStateHeading('No package bookings yet')
            ->emptyStateDescription('Group packages booked and paid on the website will appear here. Turn on "Guests can book and pay online" in a package\'s Pricing tab.');
    }

    /** ₹ amount for the plain-English blocks below. */
    protected static function rupees(float $v): string
    {
        return '₹'.number_format($v, 2);
    }

    /**
     * The booking page, written for non-technical staff: what state it is
     * in and what to do, the money in plain words, then trip, people,
     * payments and notes.
     */
    public static function infolist(Schema $schema): Schema
    {
        $cell = fn (string $label, string $value, string $extra = '') => '<div style="padding:4px 0;"><div style="font-size:12px;color:#6b7280;">'.e($label).'</div><div style="font-size:20px;font-weight:700;'.$extra.'">'.$value.'</div></div>';

        return $schema->components([
            // 1. At a glance
            Section::make('At a glance')
                ->icon('heroicon-o-eye')
                ->columns(4)
                ->columnSpanFull()
                ->schema([
                    TextEntry::make('status_label')
                        ->label('Status')
                        ->state(fn (Booking $record) => PackageBookingStatus::for($record)['label'])
                        ->badge()
                        ->size('lg')
                        ->color(fn (Booking $record) => PackageBookingStatus::for($record)['color'])
                        ->helperText(fn (Booking $record) => 'What to do: '.PackageBookingStatus::for($record)['help'])
                        ->columnSpan(2),
                    TextEntry::make('glance_paid')
                        ->hiddenLabel()
                        ->state(fn (Booking $record) => $cell('Guest has paid', self::rupees(self::paidSoFar($record) - (float) $record->payments->sum('refund_amount')), 'color:#16a34a;'))
                        ->html(),
                    TextEntry::make('glance_due')
                        ->hiddenLabel()
                        ->state(function (Booking $record) use ($cell) {
                            $due = self::balanceWithGst($record);

                            return $due
                                ? $cell('Still to pay (by '.$record->package_balance_due_on?->format('j M').')', self::rupees($due), 'color:#d97706;')
                                : $cell('Still to pay', 'Nothing', 'color:#6b7280;');
                        })
                        ->html(),
                    TextEntry::make('cancellation_reason')
                        ->label('Cancellation')
                        ->columnSpanFull()
                        ->visible(fn (Booking $record) => filled($record->cancellation_reason)),
                ]),

            // 2. Trip
            Section::make('Trip')
                ->icon('heroicon-o-map')
                ->columns(2)
                ->schema([
                    TextEntry::make('package.title')->label('Package')->weight('bold'),
                    TextEntry::make('trip_dates')
                        ->label('Travel dates')
                        ->state(function (Booking $record) {
                            $start = Carbon::parse($record->check_in);
                            $end = $record->check_out ? Carbon::parse($record->check_out) : null;
                            $nights = $end ? $start->diffInDays($end) : null;

                            return $start->format('D, j M Y').($end ? ' → '.$end->format('D, j M Y') : '')
                                .($nights ? " ({$nights} nights)" : '');
                        }),
                    TextEntry::make('package_option')->label('Room sharing')->placeholder('—'),
                    TextEntry::make('pax_adults')->label('Number of travellers')->formatStateUsing(fn ($state, Booking $record) => $record->packageTravellersLabel()),
                ]),

            // 3. Guest & travellers
            Section::make('Guest & travellers')
                ->icon('heroicon-o-user-group')
                ->columns(2)
                ->schema([
                    TextEntry::make('contact')
                        ->label('Contact person')
                        ->state(function (Booking $record) {
                            $phone = '+'.($record->guest_phone_code ?: '91').' '.$record->guest_phone;
                            $wa = 'https://wa.me/'.preg_replace('/\D/', '', ($record->guest_phone_code ?: '91').$record->guest_phone);

                            return '<strong>'.e($record->lead_guest_name).'</strong><br>'
                                .'📞 <a href="tel:'.e(preg_replace('/\s/', '', $phone)).'" style="color:#b8932b;">'.e($phone).'</a>'
                                .' · <a href="'.e($wa).'" target="_blank" style="color:#16a34a;">WhatsApp</a><br>'
                                .'✉️ <a href="mailto:'.e($record->guest_email).'" style="color:#b8932b;">'.e($record->guest_email).'</a>';
                        })
                        ->html(),
                    TextEntry::make('traveller_list')
                        ->label('Travellers')
                        ->state(fn (Booking $record) => $record->travelers->values()->map(fn ($t, $i) => ($i + 1).'. <strong>'.e($t->full_name).'</strong>'
                            .' — '.($t->age ? e($t->age).' yrs' : 'age not given').($t->gender ? ', '.e(ucfirst($t->gender)) : ''))
                            ->implode('<br>'))
                        ->html(),
                    TextEntry::make('special_requests')->label('Special requests from the guest')->placeholder('None')->columnSpanFull(),
                ]),

            // 4. Money, line by line
            Section::make('Money')
                ->icon('heroicon-o-banknotes')
                ->description('How the amount the guest paid is made up.')
                ->schema([
                    TextEntry::make('money_table')
                        ->hiddenLabel()
                        ->state(function (Booking $record) {
                            $row = fn (string $label, string $value, string $note = '', bool $bold = false) => '<tr><td style="padding:6px 12px 6px 0;'.($bold ? 'font-weight:700;' : '').'">'.e($label)
                                .($note ? '<div style="font-size:12px;color:#6b7280;font-weight:400;">'.e($note).'</div>' : '').'</td>'
                                .'<td style="padding:6px 0;text-align:right;white-space:nowrap;'.($bold ? 'font-weight:700;' : '').'">'.$value.'</td></tr>';
                            $line = '<tr><td colspan="2" style="border-top:1px solid #e5e7eb;"></td></tr>';
                            $paid = self::paidSoFar($record);
                            $refunded = (float) $record->payments->sum('refund_amount');
                            $due = self::balanceWithGst($record);

                            return '<table style="width:100%;max-width:560px;font-size:14px;">'
                                .$row('Package price', self::rupees((float) $record->base_amount), $record->pax_adults.' × '.self::rupees((float) $record->package_price_per_person).' per person')
                                .$row('GST (5%)', self::rupees((float) $record->tax_amount))
                                .$row('Trip price', self::rupees((float) $record->base_amount + (float) $record->tax_amount), '', true)
                                .$line
                                .$row('Online payment charges', self::rupees((float) $record->razorpay_recovery), 'Added to each online payment to cover the Razorpay fee')
                                .$row('Total paid by guest', self::rupees($paid), '', true)
                                .($refunded > 0 ? $row('Refunded to guest', '− '.self::rupees($refunded)) : '')
                                .($due ? $row('Still to pay', self::rupees($due), 'Due by '.$record->package_balance_due_on?->format('D, j M Y').' (online payment charges are added when paid)', true) : '')
                                .'</table>'
                                .'<p style="margin-top:8px;font-size:12px;color:#6b7280;">Payment plan: '.($record->package_payment_plan === 'part' ? 'booking amount first, balance later' : 'paid in full at booking').'.</p>';
                        })
                        ->html(),
                ]),

            // 5. Payment history
            Section::make('Payment history')
                ->icon('heroicon-o-clock')
                ->schema([
                    TextEntry::make('payment_list')
                        ->hiddenLabel()
                        ->state(fn (Booking $record) => $record->payments->sortBy('id')->map(function (Payment $p) use ($record) {
                            $what = $p->purpose === 'package_balance' ? 'Balance' : ($record->package_payment_plan === 'part' ? 'Booking amount' : 'Full payment');
                            [$status, $color] = match ($p->status) {
                                'captured' => ['Paid', '#16a34a'],
                                'refunded' => ['Refunded in full', '#6b7280'],
                                'partially_refunded' => ['Partly refunded ('.self::rupees((float) $p->refund_amount).')', '#d97706'],
                                'failed' => ['Failed — no money taken', '#dc2626'],
                                default => ['Not completed — no money taken', '#6b7280'],
                            };

                            return '<div style="padding:6px 0;border-bottom:1px solid #f3f4f6;">'
                                .'<span style="color:#6b7280;">'.$p->created_at->format('j M Y, g:i A').'</span> · '
                                .'<strong>'.$what.'</strong> · '.self::rupees((float) $p->amount)
                                .' · <span style="color:'.$color.';font-weight:600;">'.$status.'</span></div>';
                        })->implode('') ?: 'No payments yet.')
                        ->html(),
                ]),

            // 6. Notes
            Section::make('Notes for your team')
                ->icon('heroicon-o-pencil-square')
                ->description('Only your team sees these. Use "Add note" at the top to write one.')
                ->schema([
                    TextEntry::make('admin_note')
                        ->hiddenLabel()
                        ->placeholder('No notes yet.')
                        ->formatStateUsing(fn ($state) => collect(preg_split('/\r?\n/', (string) $state))->filter()
                            ->map(fn ($line) => '<div style="padding:5px 0;border-bottom:1px solid #f3f4f6;">'.e($line).'</div>')->implode(''))
                        ->html(),
                ]),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPackageBookings::route('/'),
            'view' => ViewPackageBooking::route('/{record}'),
        ];
    }
}
