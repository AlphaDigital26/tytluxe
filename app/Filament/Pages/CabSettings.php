<?php

namespace App\Filament\Pages;

use App\Services\CabBookingService;
use App\Services\CabPricingService;
use App\Support\CabSettings as Settings;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

/**
 * Everything about cabs the client can change without a developer, in plain
 * words. Read back by the website through App\Support\CabSettings.
 */
class CabSettings extends Page
{
    protected static string|\UnitEnum|null $navigationGroup = 'Cabs';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Cab Settings';

    protected string $view = 'filament.pages.cab-settings';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-adjustments-horizontal';

    protected static ?string $title = 'Cab Settings';

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
            'markup' => Settings::markup(),
            'allow_guest_cancel' => Settings::allowGuestCancel(),
        ];
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Section::make('🚕 Online Cab Booking')
                    ->description('Switch online cab booking on or off for the whole website. Turning it off does NOT affect cabs guests have already booked.')
                    ->schema([
                        Placeholder::make('setup')
                            ->label('')
                            ->content(new HtmlString('<span style="color:#9c1c1c;font-weight:600;">Cab booking is not set up yet: your developer needs to add your TripJack user ID (TRIPJACK_CAB_AGENT_ID) to the website settings. Until then guests can search cabs but not book them.</span>'))
                            ->visible(fn () => ! CabBookingService::configured()),
                        Toggle::make('booking_enabled')
                            ->label('Guests can search and book cabs on the website')
                            ->onColor('success')
                            ->offColor('danger')
                            ->live(),
                        Textarea::make('disabled_message')
                            ->label('Message guests see while cab booking is switched off')
                            ->rows(2)
                            ->maxLength(300)
                            ->required()
                            ->visible(fn (Get $get) => ! $get('booking_enabled'))
                            ->helperText('Shown on the Cabs page in place of the search box.'),
                    ]),

                Section::make('💰 Your Earnings on Each Cab')
                    ->description('The fixed amount you add to each cab trip. A round trip is two trips, so a guest pays it twice. The website then adds 5% GST on your earnings and the online payment (Razorpay) charge automatically. Changes apply to new searches straight away; bookings already made keep their price.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('markup')
                            ->label('Your earning per cab trip')
                            ->prefix('₹')
                            ->suffix('per one-way trip')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(20000)
                            ->required()
                            ->live(debounce: 500),
                        Placeholder::make('example')
                            ->label('What this means for a ₹1,500 cab (one way)')
                            ->content(function (Get $get) {
                                $p = CabPricingService::price(1500.0, (float) $get('markup'));
                                $r = fn (float $v) => '₹'.number_format($v, round($v) == $v ? 0 : 2);

                                return new HtmlString('Guest pays <strong>'.$r($p['total']).'</strong> · cab '.$r($p['supplier'])
                                    .' · <strong style="color:#16a34a">you earn '.$r($p['markup']).'</strong> · GST on your earnings '.$r($p['gst'])
                                    .' · online payment charge '.$r($p['gateway']));
                            }),
                    ]),

                Section::make('🧳 What Guests Can Do Themselves')
                    ->schema([
                        Toggle::make('allow_guest_cancel')
                            ->label('Cancel online')
                            ->helperText('Guests can cancel their own cab before pickup. The refund follows the cab\'s cancellation policy and is paid back automatically.'),
                    ]),
            ]);
    }

    public function save(): void
    {
        $data = $this->form->getState();

        Settings::save([
            'booking_enabled' => (bool) $data['booking_enabled'],
            'disabled_message' => trim((string) ($data['disabled_message'] ?? '')) ?: Settings::DEFAULT_DISABLED_MESSAGE,
            'markup' => round((float) $data['markup'], 2),
            'allow_guest_cancel' => (bool) $data['allow_guest_cancel'],
        ]);

        Notification::make()
            ->title('Cab settings saved')
            ->body($data['booking_enabled']
                ? 'Cab booking is ON. New searches now add ₹'.number_format((float) $data['markup']).' to each one-way cab trip.'
                : 'Cab booking is now OFF on the website. Existing bookings are not affected.')
            ->success()
            ->send();
    }
}
