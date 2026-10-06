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
    protected string $view = 'filament.pages.tripjack-booking-reconciliation';

    protected static string|\UnitEnum|null $navigationGroup = 'Hotels';

    protected static ?string $navigationLabel = 'TripJack Reconciliation';

    protected static ?int $navigationSort = 42;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?string $title = 'TripJack Booking Reconciliation';

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
                Section::make('Date Range')
                    ->description('TripJack returns bookings created within this window (IST dates). TripJack allows at most 7 days per request, starting no more than 15 days ago.')
                    ->schema([
                        DatePicker::make('start_date')->required()->native(false),
                        DatePicker::make('end_date')->required()->native(false),
                    ])
                    ->columns(2),
            ]);
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('fetch')
                ->label('Fetch from TripJack')
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
