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
                TextColumn::make('pax_adults')->label('Travellers'),
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
                ViewAction::make()->label('Open'),
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

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Status')
                ->schema([
                    TextEntry::make('status_label')
                        ->label('')
                        ->state(fn (Booking $record) => PackageBookingStatus::for($record)['label'])
                        ->badge()
                        ->color(fn (Booking $record) => PackageBookingStatus::for($record)['color'])
                        ->helperText(fn (Booking $record) => PackageBookingStatus::for($record)['help']),
                    TextEntry::make('cancellation_reason')->label('Cancellation')->visible(fn (Booking $record) => filled($record->cancellation_reason)),
                ])
                ->columnSpanFull(),
            Section::make('Trip')
                ->columns(3)
                ->schema([
                    TextEntry::make('package.title')->label('Package'),
                    TextEntry::make('check_in')->label('Departure')->formatStateUsing(fn ($state) => $state ? Carbon::parse($state)->format('D, j M Y') : '—'),
                    TextEntry::make('check_out')->label('Return')->formatStateUsing(fn ($state) => $state ? Carbon::parse($state)->format('D, j M Y') : '—'),
                    TextEntry::make('package_option')->label('Room sharing')->placeholder('—'),
                    TextEntry::make('pax_adults')->label('Travellers'),
                    TextEntry::make('package_price_per_person')->label('Price per person (before GST)')->money('INR'),
                ]),
            Section::make('Money')
                ->columns(3)
                ->schema([
                    TextEntry::make('trip_total')->label('Trip total (incl. 5% GST)')->state(fn (Booking $record) => (float) $record->base_amount + (float) $record->tax_amount)->money('INR'),
                    TextEntry::make('paid')->label('Paid so far')->state(fn (Booking $record) => self::paidSoFar($record))->money('INR'),
                    TextEntry::make('refunded')->label('Refunded')->state(fn (Booking $record) => (float) $record->payments->sum('refund_amount'))->money('INR'),
                    TextEntry::make('package_payment_plan')->label('How they chose to pay')->formatStateUsing(fn ($state) => $state === 'part' ? 'Booking amount now, balance later' : 'In full'),
                    TextEntry::make('balance')->label('Balance due (incl. GST)')->state(fn (Booking $record) => self::balanceWithGst($record))->money('INR')->placeholder('Nothing due'),
                    TextEntry::make('package_balance_due_on')->label('Balance due by')->date('D, j M Y')->visible(fn (Booking $record) => $record->packageBalanceDue()),
                    TextEntry::make('razorpay_recovery')->label('Online payment charges collected')->money('INR'),
                ]),
            Section::make('Guest')
                ->columns(3)
                ->schema([
                    TextEntry::make('lead_guest_name')->label('Lead traveller'),
                    TextEntry::make('guest_email')->label('Email')->copyable(),
                    TextEntry::make('guest_phone')->label('Phone')->formatStateUsing(fn ($state, Booking $record) => '+'.($record->guest_phone_code ?: '91').' '.$state)->copyable(),
                    TextEntry::make('special_requests')->label('Special requests')->placeholder('None')->columnSpanFull(),
                ]),
            Section::make('Travellers')
                ->schema([
                    RepeatableEntry::make('travelers')
                        ->label('')
                        ->columns(3)
                        ->schema([
                            TextEntry::make('full_name')->label('Name'),
                            TextEntry::make('age')->label('Age')->placeholder('—'),
                            TextEntry::make('gender')->label('Gender')->formatStateUsing(fn ($state) => ucfirst((string) $state)),
                        ]),
                ]),
            Section::make('Payments')
                ->schema([
                    RepeatableEntry::make('payments')
                        ->label('')
                        ->columns(4)
                        ->schema([
                            TextEntry::make('purpose')->label('For')->formatStateUsing(fn ($state, Payment $record) => $state === 'package_balance' ? 'Balance' : ($record->booking?->package_payment_plan === 'part' ? 'Booking amount' : 'Full payment')),
                            TextEntry::make('amount')->label('Amount')->money('INR'),
                            TextEntry::make('status')->label('Status')->formatStateUsing(fn ($state) => match ($state) {
                                'captured' => 'Paid',
                                'refunded' => 'Refunded in full',
                                'partially_refunded' => 'Partly refunded',
                                'failed' => 'Failed',
                                default => 'Not completed',
                            }),
                            TextEntry::make('created_at')->label('When')->dateTime('j M Y, g:i A'),
                        ]),
                ]),
            Section::make('Notes for your team')
                ->schema([
                    TextEntry::make('admin_note')->label('')->placeholder('No notes yet.')->formatStateUsing(fn ($state) => nl2br(e($state)))->html(),
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
