<?php

namespace App\Filament\Pages;

use App\Models\Booking;
use App\Services\TripJack\Exceptions\TripJackException;
use App\Services\TripJack\TripJackClient;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

/**
 * Surfaces TripJack's own Booking List API (POST /oms/v1/hotel/bookings) in
 * the admin panel — previously only reachable via the ListTripjackBookings
 * console command. Lets ops staff cross-check TripJack's source-of-truth
 * booking status/price against our local `bookings` table for a date range,
 * without needing shell access.
 */
class TripjackBookingReconciliation extends Page
{

    protected static string|\UnitEnum|null $navigationGroup = 'Bookings & Leads';

    protected static ?int $navigationSort = 6;

    protected static ?string $navigationLabel = 'Compare Hotel Bookings with Supplier';
    protected string $view = 'filament.pages.tripjack-booking-reconciliation';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?string $title = 'Compare Hotel Bookings with Supplier';

    public function getSubheading(): ?string
    {
        return "Use this when a guest says they booked a hotel but you can't find it, or its status looks wrong. Pick the dates, press \"Get supplier's list\", and every hotel booking TripJack (our hotel and flight supplier) has on record appears next to the one on your website. A row marked as different or missing is the one to look at: open it and press \"Check status with TripJack\", or call TripJack support with their booking ID. Flights are checked from each flight booking's own page.";
    }

    /** Compares bookings with TripJack — for the team that handles bookings and money. */
    public static function canAccess(): bool
    {
        return in_array(auth('admin')->user()?->role, ['Super Admin', 'Operations', 'Finance'], true);
    }

    public array $data = [];

    /** @var array<int, array<string, mixed>> */
    public array $results = [];

    public bool $searched = false;

    public function mount(): void
    {
        $this->data = [
            'start_date' => now('Asia/Kolkata')->subDays(6)->toDateString(),
            'end_date' => now('Asia/Kolkata')->toDateString(),
        ];
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Section::make('Which days?')
                    ->description('Pick the days the guests booked on (Indian time). TripJack only allows up to 7 days at a time, and nothing older than 15 days — for a booking made 10 days ago, pick from 12 days ago to 6 days ago.')
                    ->schema([
                        // The calendar only offers days TripJack will accept.
                        DatePicker::make('start_date')->label('Booked from')->required()->native(false)
                            ->minDate(now('Asia/Kolkata')->subDays(15))->maxDate(now('Asia/Kolkata')),
                        DatePicker::make('end_date')->label('Booked until')->required()->native(false)
                            ->minDate(now('Asia/Kolkata')->subDays(15))->maxDate(now('Asia/Kolkata')),
                    ])
                    ->columns(2),
            ]);
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('fetch')
                ->label("Get supplier's list")
                ->icon('heroicon-o-arrow-path')
                ->color('primary')
                ->action('fetch'),
        ];
    }

    public function fetch(): void
    {
        // Picked dates are IST calendar days (TripJack's timezone).
        $start = Carbon::parse($this->data['start_date'] ?? now()->subDays(6), 'Asia/Kolkata')->startOfDay();
        $end = Carbon::parse($this->data['end_date'] ?? now(), 'Asia/Kolkata')->endOfDay();

        try {
            [$startIst, $endIst] = TripJackClient::bookingListRange($start, $end);
        } catch (\InvalidArgumentException $e) {
            Notification::make()->title('Invalid range')->body($e->getMessage())->danger()->send();

            return;
        }

        try {
            $client = app(TripJackClient::class);
            $response = $client->bookingList($startIst, $endIst);
        } catch (TripJackException $e) {
            Notification::make()
                ->title('TripJack request failed')
                ->body($e->getMessage())
                ->danger()
                ->send();

            return;
        }

        $tjBookings = collect($response['bookings'] ?? []);
        $localByTjId = Booking::whereIn('tripjack_booking_id', $tjBookings->pluck('bookingId')->filter())
            ->get()
            ->keyBy('tripjack_booking_id');

        $rows = $tjBookings->map(function (array $tj) use ($localByTjId) {
            $local = $localByTjId->get($tj['bookingId'] ?? null);
            $acceptable = $this->acceptableLocalStatuses($tj['status'] ?? null);
            $tjPrice = isset($tj['totalPrice']) ? (float) $tj['totalPrice'] : null;
            $localPrice = $local?->tripjack_total_price !== null ? (float) $local->tripjack_total_price : null;
            $priceMismatch = $local && $tjPrice !== null && $localPrice !== null && abs($tjPrice - $localPrice) > 1.0;

            $issue = match (true) {
                ! $local => 'Not in our database',
                ! in_array($local->status, $acceptable, true) => 'Status differs',
                $priceMismatch => 'Price differs',
                default => null,
            };

            return [
                'bookingId' => $tj['bookingId'] ?? '—',
                'tjStatus' => $tj['status'] ?? '—',
                'tjTotalPrice' => $tjPrice,
                'localReference' => $local?->reference,
                'localStatus' => $local?->status,
                'localTotalPrice' => $localPrice,
                'issue' => $issue,
                'mismatch' => $issue !== null,
            ];
        });

        // Reverse check: our hotel bookings sent to TripJack in this window
        // that TripJack's list doesn't contain at all. created_at is UTC;
        // the window is IST.
        $missingOnTripjack = Booking::where('vertical', 'hotel')
            ->whereNotNull('tripjack_booking_id')
            ->whereBetween('created_at', [Carbon::parse($startIst, 'Asia/Kolkata')->utc(), Carbon::parse($endIst, 'Asia/Kolkata')->utc()])
            ->whereNotIn('tripjack_booking_id', $tjBookings->pluck('bookingId')->filter()->all())
            ->get()
            ->map(fn (Booking $local) => [
                'bookingId' => $local->tripjack_booking_id,
                'tjStatus' => '—',
                'tjTotalPrice' => null,
                'localReference' => $local->reference,
                'localStatus' => $local->status,
                'localTotalPrice' => $local->tripjack_total_price !== null ? (float) $local->tripjack_total_price : null,
                'issue' => 'Missing on TripJack',
                'mismatch' => true,
            ]);

        $this->results = $rows->concat($missingOnTripjack)->values()->all();

        $this->searched = true;

        Notification::make()
            ->title(count($this->results).' booking(s) returned by TripJack')
            ->body(collect($this->results)->where('mismatch', true)->count().' flagged for review.')
            ->success()
            ->send();
    }

    /**
     * Local statuses that are consistent with a TripJack status (mirrors
     * FrontendController::mapTripjackBookingStatus()). A failed TripJack
     * booking is fine locally as either refunded or under review; a
     * CANCELLATION_PENDING one is still confirmed locally until it lands.
     *
     * @return string[]
     */
    protected function acceptableLocalStatuses(?string $tripjackStatus): array
    {
        return match ($tripjackStatus) {
            'SUCCESS' => ['confirmed'],
            'CANCELLED' => ['cancelled'],
            'CANCELLATION_PENDING' => ['confirmed'],
            'ABORTED', 'FAILED' => ['failed_needs_review', 'refunded'],
            default => ['pending_confirmation', 'confirmed'], // PENDING / IN_PROGRESS / ON_HOLD
        };
    }
}
