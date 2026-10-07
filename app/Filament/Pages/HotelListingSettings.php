<?php

namespace App\Filament\Pages;

use App\Models\Hotel;
use App\Models\Setting;
use App\Services\HotelPricingService;
use App\Support\HotelSettings;
use Filament\Actions\Action;
use Filament\Forms\Components\CheckboxList;
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

class HotelListingSettings extends Page
{

    protected static string|\UnitEnum|null $navigationGroup = 'Hotels';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Hotel Settings';
    protected string $view = 'filament.pages.hotel-listing-settings';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-adjustments-horizontal';

    protected static ?string $title = 'Hotel Settings';

    // -----------------------------------------------------------------
    // Form State
    // -----------------------------------------------------------------
    public array $data = [];

    /** Prices and on/off switches affect every guest — Super Admin only. */
    public static function canAccess(): bool
    {
        return auth('admin')->user()?->role === 'Super Admin';
    }

    public function mount(): void
    {
        $this->data = [
            'booking_enabled' => HotelSettings::bookingEnabled(),
            'disabled_message' => HotelSettings::disabledMessage(),
            'markup_percent' => HotelSettings::markupPercent(),
            'allow_guest_cancel' => HotelSettings::allowGuestCancel(),
            'allowed_star_ratings' => Hotel::allowedStarRatings(),
        ];
    }

    // -----------------------------------------------------------------
    // Form
    // -----------------------------------------------------------------
    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Section::make('🏨 Online Hotel Booking')
                    ->description('Switch online hotel booking on or off for the whole website. Guests can still browse hotels while it is off, and bookings already made are not affected.')
                    ->schema([
                        Toggle::make('booking_enabled')
                            ->label('Guests can book hotels on the website')
                            ->onColor('success')
                            ->offColor('danger')
                            ->live(),
                        Textarea::make('disabled_message')
                            ->label('Message guests see if they try to book while it is switched off')
                            ->rows(2)
                            ->maxLength(300)
                            ->required()
                            ->visible(fn (Get $get) => ! $get('booking_enabled')),
                        Toggle::make('allow_guest_cancel')
                            ->label('Guests can cancel their hotel booking online')
                            ->helperText('When on, guests cancel from their booking page and are refunded automatically according to the hotel\'s cancellation policy. Turn off if you\'d rather your team handles cancellations — guests will see "please contact our support team".'),
                    ]),

                Section::make('💰 Your Earnings on Each Booking')
                    ->description('The percentage you add on top of the hotel\'s price. The website then adds the GST on your earnings and the online payment (Razorpay) charge automatically — you don\'t need to include those.')
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
                            ->helperText('Example: 10 means you earn 10% of the hotel price. Changes apply to new searches straight away; bookings already made keep their price.'),
                        Placeholder::make('markup_example')
                            ->label('What this means for a ₹10,000 stay')
                            ->content(fn (Get $get) => $this->priceExample((float) $get('markup_percent'))),
                    ]),

                Section::make('⭐ Star Rating Filter')
                    ->description('Control which hotel star ratings are allowed to appear anywhere on the public website — search results, hotel detail pages, wishlist, and booking. Hotels with an unchecked star rating stay in the CMS untouched and remain fully manageable here, they just will not show to visitors.')
                    ->schema([
                        CheckboxList::make('allowed_star_ratings')
                            ->label('Star Ratings Visible on the Website')
                            ->options([
                                1 => '⭐ 1 Star',
                                2 => '⭐⭐ 2 Stars',
                                3 => '⭐⭐⭐ 3 Stars',
                                4 => '⭐⭐⭐⭐ 4 Stars',
                                5 => '⭐⭐⭐⭐⭐ 5 Stars',
                            ])
                            ->required()
                            ->columns(5)
                            ->helperText('e.g. tick only 4 and 5 Stars to show visitors just your 4-star and 5-star hotels. Untick a rating to instantly hide every hotel at that rating from the site.'),
                    ]),
            ]);
    }

    // -----------------------------------------------------------------
    // Save Action
    // -----------------------------------------------------------------
    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Save Hotel Settings')
                ->icon('heroicon-o-check-circle')
                ->color('primary')
                ->action('save'),
        ];
    }

    public function save(): void
    {
        $ratings = array_values(array_map('intval', $this->data['allowed_star_ratings'] ?? []));

        if (empty($ratings)) {
            Notification::make()
                ->title('At least one star rating must stay checked')
                ->body('Otherwise every hotel on the site would be hidden from visitors.')
                ->danger()
                ->send();

            return;
        }

        $data = $this->form->getState();

        Setting::setJson('hotel_listing.allowed_star_ratings', $ratings);
        HotelSettings::save([
            'booking_enabled' => (bool) $data['booking_enabled'],
            'disabled_message' => trim((string) ($data['disabled_message'] ?? '')) ?: HotelSettings::DEFAULT_DISABLED_MESSAGE,
            'markup_percent' => round((float) $data['markup_percent'], 2),
            'allow_guest_cancel' => (bool) $data['allow_guest_cancel'],
        ]);

        Notification::make()
            ->title('Hotel settings saved!')
            ->body(($data['booking_enabled'] ? 'Hotel booking is ON' : 'Hotel booking is OFF')
                .' · markup '.rtrim(rtrim(number_format((float) $data['markup_percent'], 2), '0'), '.').'%'
                .' · showing hotels rated: '.implode(', ', array_map(fn ($r) => $r.'★', $ratings)).'.')
            ->success()
            ->send();
    }

    /**
     * The same steps as HotelPricingService::price(), at the markup typed
     * into the form (not yet saved), so the example updates live.
     */
    protected function priceExample(float $markupPercent): HtmlString
    {
        $f = HotelPricingService::formula();
        $hotel = 10000.0;
        $earning = $hotel * max(0, $markupPercent) / 100;
        $gst = $earning * ($hotel < $f['gstThreshold'] ? $f['gstLow'] : $f['gstHigh']);
        $guestPays = ($hotel + $earning + $gst) / (1 - $f['razorpayRate']);
        $paymentCharge = $guestPays - ($hotel + $earning + $gst);

        $rupees = fn (float $v) => '₹'.number_format($v, 0);

        return new HtmlString(
            'Guest pays <strong>'.$rupees($guestPays).'</strong> · '
            .'hotel price '.$rupees($hotel).' · '
            .'<strong style="color:#16a34a">you earn '.$rupees($earning).'</strong> · '
            .'GST on your earnings '.$rupees($gst).' · '
            .'online payment charge '.$rupees($paymentCharge)
        );
    }
}
