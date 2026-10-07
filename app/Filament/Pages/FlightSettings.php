<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use App\Services\FlightPricingService;
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
            'markup_percent' => Settings::markupPercent(),
            'allow_hold' => Settings::allowHold(),
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
                    ->description('The percentage you add on top of the airline\'s ticket price. The website then adds the GST on your earnings and the online payment (Razorpay) charge automatically — you don\'t need to include those.')
                    ->schema([
                        TextInput::make('markup_percent')
                            ->label('Your markup')
                            ->suffix('%')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(50)
                            ->step(0.5)
                            ->required()
                            ->live(debounce: 500)
                            ->helperText('Example: 10 means you earn 10% of the ticket price. Changes apply to new searches straight away; bookings already made keep their price.'),
                        Placeholder::make('markup_example')
                            ->label('What this means for a ₹5,000 ticket')
                            ->content(fn (Get $get) => $this->priceExample((float) $get('markup_percent'))),
                    ]),

                Section::make('🧳 What Guests Can Do Themselves')
                    ->description('Turn an option off if you\'d rather your team handles it. Guests will then see a "please contact our support team" message instead.')
                    ->columns(2)
                    ->schema([
                        Toggle::make('allow_hold')
                            ->label('Reserve now, pay later')
                            ->helperText('Lets guests block a seat without paying, when the airline allows it. The reservation is released automatically if they don\'t pay in time.'),
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
            'markup_percent' => round((float) $data['markup_percent'], 2),
            'allow_hold' => (bool) $data['allow_hold'],
            'allow_guest_cancel' => (bool) $data['allow_guest_cancel'],
            'allow_guest_reschedule' => (bool) $data['allow_guest_reschedule'],
            'allow_guest_extras' => (bool) $data['allow_guest_extras'],
            'low_balance_alert' => round((float) $data['low_balance_alert'], 2),
        ]);

        Notification::make()
            ->title('Flight settings saved')
            ->body($data['booking_enabled']
                ? 'Flight booking is ON. New searches now use your '.rtrim(rtrim(number_format((float) $data['markup_percent'], 2), '0'), '.').'% markup.'
                : 'Flight booking is now OFF on the website. Existing bookings are not affected.')
            ->success()
            ->send();
    }

    /**
     * The same steps as FlightPricingService::price(), but at the markup
     * typed into the form (not yet saved), so the example updates live.
     */
    protected function priceExample(float $markupPercent): HtmlString
    {
        $f = FlightPricingService::formula();
        $airline = 5000.0;
        $earning = $airline * max(0, $markupPercent) / 100;
        $gst = $earning * ($airline < $f['gstThreshold'] ? $f['gstLow'] : $f['gstHigh']);
        $guestPays = ($airline + $earning + $gst) / (1 - $f['razorpayRate']);
        $paymentCharge = $guestPays - ($airline + $earning + $gst);

        $rupees = fn (float $v) => '₹'.number_format($v, 0);

        return new HtmlString(
            'Guest pays <strong>'.$rupees($guestPays).'</strong> · '
            .'airline ticket '.$rupees($airline).' · '
            .'<strong style="color:#16a34a">you earn '.$rupees($earning).'</strong> · '
            .'GST on your earnings '.$rupees($gst).' · '
            .'online payment charge '.$rupees($paymentCharge)
        );
    }

    protected function lastBalanceText(): string
    {
        $last = Setting::getJson('tripjack_alert.last_balance', []);
        if (! isset($last['amount'])) {
            return 'Not checked yet — it is checked automatically every 15 minutes.';
        }

        return '₹'.number_format((float) $last['amount'], 2)
            .' (checked '.Carbon::parse($last['checked_at'])->diffForHumans().')';
    }
}
