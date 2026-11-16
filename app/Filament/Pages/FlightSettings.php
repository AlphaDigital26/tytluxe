<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use App\Services\FlightBookingService;
use App\Services\FlightPricingService;
use App\Services\TripJack\TripJackFlightClient;
use App\Support\DashboardData;
use Filament\Actions\Action;
use App\Support\FlightSettings as Settings;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Support\Carbon;
use Illuminate\Support\HtmlString;

/**
 * Everything about flights the client can change without a developer —
 * written for a non-technical reader. Values are read back through
 * App\Support\FlightSettings by the website.
 */
class FlightSettings extends Page
{

    protected static string|\UnitEnum|null $navigationGroup = 'Flights';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Flight Settings';
    protected string $view = 'filament.pages.flight-settings';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-adjustments-horizontal';

    protected static ?string $title = 'Flight Settings';

    public array $data = [];

    /** Prices and on/off switches affect every guest — Super Admin only. */
    public static function canAccess(): bool
    {
        return auth('admin')->user()?->role === 'Super Admin';
    }

    public function mount(): void
    {
        $this->data = [
            'booking_enabled' => Settings::bookingEnabled(),
            'disabled_message' => Settings::disabledMessage(),
            'domestic_markup' => Settings::domesticMarkup(),
            'international_markup' => Settings::internationalMarkup(),
            'infant_markup' => Settings::infantMarkup(),
            'allow_guest_cancel' => Settings::allowGuestCancel(),
            'allow_guest_reschedule' => Settings::allowGuestReschedule(),
            'allow_guest_extras' => Settings::allowGuestExtras(),
            'low_balance_alert' => Settings::lowBalanceAlert(),
        ];
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Section::make('✈️ Online Flight Booking')
                    ->description('Switch online flight booking on or off for the whole website. Turning it off does NOT affect flights guests have already booked — their tickets, payments and cancellations keep working.')
                    ->schema([
                        Toggle::make('booking_enabled')
                            ->label('Guests can search and book flights on the website')
                            ->onColor('success')
                            ->offColor('danger')
                            ->live(),
                        Textarea::make('disabled_message')
                            ->label('Message guests see while flight booking is switched off')
                            ->rows(2)
                            ->maxLength(300)
                            ->required()
                            ->visible(fn (Get $get) => ! $get('booking_enabled'))
                            ->helperText('Shown on the Flights page in place of the search box.'),
                    ]),

                Section::make('💰 Your Earnings on Each Ticket')
                    ->description('The fixed amount you add for each passenger on each flight. A round trip is two flights, so a guest pays it twice. Infants (under 2) pay their own, smaller amount. The website then adds 5% GST on your earnings and the online payment (Razorpay) charge automatically — you don\'t need to include those. Changes apply to new searches straight away; bookings already made keep their price.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('domestic_markup')
                            ->label('Domestic flight (within India)')
                            ->prefix('₹')
                            ->suffix('per passenger, per flight')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(20000)
                            ->required()
                            ->live(debounce: 500),
                        TextInput::make('international_markup')
                            ->label('International flight')
                            ->prefix('₹')
                            ->suffix('per passenger, per flight')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(20000)
                            ->required()
                            ->live(debounce: 500),
                        TextInput::make('infant_markup')
                            ->label('Infant (under 2), domestic or international')
                            ->prefix('₹')
                            ->suffix('per infant, per flight')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(20000)
                            ->required()
                            ->columnSpanFull(),
                        Placeholder::make('domestic_example')
                            ->label('What this means for a ₹5,000 domestic ticket (1 adult, 1 flight)')
                            ->content(fn (Get $get) => $this->priceExample(5000.0, (float) $get('domestic_markup'))),
                        Placeholder::make('international_example')
                            ->label('What this means for a ₹25,000 international ticket (1 adult, 1 flight)')
                            ->content(fn (Get $get) => $this->priceExample(25000.0, (float) $get('international_markup'))),
                    ]),

                Section::make('🧳 What Guests Can Do Themselves')
                    ->description('Turn an option off if you\'d rather your team handles it. Guests will then see a "please contact our support team" message instead.')
                    ->columns(3)
                    ->schema([
                        Toggle::make('allow_guest_cancel')
                            ->label('Cancel online')
                            ->helperText('Guests can cancel their own ticket. The refund is worked out from the airline\'s charges and paid back automatically.'),
                        Toggle::make('allow_guest_reschedule')
                            ->label('Change travel date online')
                            ->helperText('Guests can move their flight to another date and pay any difference online.'),
                        Toggle::make('allow_guest_extras')
                            ->label('Add seats, meals and baggage after booking')
                            ->helperText('Guests can buy extras for a ticket they have already booked.'),
                    ]),

                Section::make('🔔 TripJack Wallet Alert')
                    ->description('Flight tickets are paid for from your TripJack wallet. If the wallet runs low, new bookings will fail — so we email all active admins when it drops below this amount.')
                    ->schema([
                        TextInput::make('low_balance_alert')
                            ->label('Alert me when the wallet balance drops below')
                            ->prefix('₹')
                            ->numeric()
                            ->minValue(0)
                            ->required(),
                        Placeholder::make('current_balance')
                            ->label('Wallet balance at last check')
                            ->content(fn () => $this->lastBalanceText()),
                    ]),
            ]);
    }

    public function save(): void
    {
        $data = $this->form->getState();

        Settings::save([
            'booking_enabled' => (bool) $data['booking_enabled'],
            'disabled_message' => trim((string) ($data['disabled_message'] ?? '')) ?: Settings::DEFAULT_DISABLED_MESSAGE,
            'domestic_markup' => round((float) $data['domestic_markup'], 2),
            'international_markup' => round((float) $data['international_markup'], 2),
            'infant_markup' => round((float) $data['infant_markup'], 2),
            'allow_guest_cancel' => (bool) $data['allow_guest_cancel'],
            'allow_guest_reschedule' => (bool) $data['allow_guest_reschedule'],
            'allow_guest_extras' => (bool) $data['allow_guest_extras'],
            'low_balance_alert' => round((float) $data['low_balance_alert'], 2),
        ]);

        Notification::make()
            ->title('Flight settings saved')
            ->body($data['booking_enabled']
                ? 'Flight booking is ON. New searches now add ₹'.number_format((float) $data['domestic_markup']).' per passenger on each domestic flight and ₹'.number_format((float) $data['international_markup']).' on each international flight (₹'.number_format((float) $data['infant_markup']).' per infant).'
                : 'Flight booking is now OFF on the website. Existing bookings are not affected.')
            ->success()
            ->send();
    }

    /**
     * FlightPricingService::price() at the ₹ markup typed into the form (not
     * yet saved), so the example updates live.
     */
    protected function priceExample(float $airline, float $markup): HtmlString
    {
        $p = FlightPricingService::price($airline, $markup);
        $earning = $p['margin_amount'];
        $gst = $p['gst_on_margin'];
        $paymentCharge = $p['razorpay_recovery'];
        $guestPays = $p['customer_price'];
        $f = FlightPricingService::formula();

        $rupees = fn (float $v) => '₹'.number_format($v, round($v) == $v ? 0 : 2);

        return new HtmlString(
            'Guest pays <strong>'.$rupees($guestPays).'</strong> · '
            .'airline ticket '.$rupees($airline).' · '
            .'<strong style="color:#16a34a">you earn '.$rupees($earning).'</strong> · '
            .'GST on your earnings '.$rupees($gst).' · '
            .'management fee '.$rupees($f['managementFee']).' + GST '.$rupees($f['managementFeeGst']).' · '
            .'online payment charge '.$rupees($paymentCharge)
        );
    }

    protected function lastBalanceText(): string
    {
        $last = Setting::getJson('tripjack_alert.last_balance', []);
        if (! isset($last['amount'])) {
            return 'Not checked yet. Click "Check wallet now" at the top. It should also be checked automatically every 15 minutes — if it never is, the server\'s scheduled tasks (cron) are not set up; tell your developer.';
        }

        $at = Carbon::parse($last['checked_at']);

        return '₹'.number_format((float) $last['amount'], 2).' (checked '.$at->diffForHumans().')'
            .($at->lt(now()->subMinutes(DashboardData::WALLET_STALE_MINUTES))
                ? ' — automatic checks seem to have stopped; the server\'s scheduled tasks (cron) may not be running. Tell your developer.'
                : '');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('checkWallet')
                ->label('Check wallet now')
                ->icon('heroicon-o-arrow-path')
                ->color('gray')
                ->action(function (): void {
                    try {
                        $amount = FlightBookingService::tripJackBalance(app(TripJackFlightClient::class)->userDetail());
                    } catch (\Throwable $e) {
                        $amount = null;
                    }

                    if ($amount === null) {
                        Notification::make()->title('Couldn\'t read the wallet')->body('TripJack did not answer. Please try again in a few minutes.')->danger()->send();

                        return;
                    }

                    Setting::setJson('tripjack_alert.last_balance', ['amount' => $amount, 'checked_at' => now()->toIso8601String()]);
                    Notification::make()->title('TripJack wallet: ₹'.number_format($amount, 2))->success()->send();
                }),
        ];
    }
}
