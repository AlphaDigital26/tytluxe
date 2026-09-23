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
            'start_date' => now()->subDays(7)->toDateString(),
            'end_date' => now()->toDateString(),
        ];
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Section::make('Date Range')
                    ->description('TripJack returns bookings created within this window. Range is capped to 31 days to keep the request fast.')
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
        $start = Carbon::parse($this->data['start_date'] ?? now()->subDays(7))->startOfDay();
        $end = Carbon::parse($this->data['end_date'] ?? now())->endOfDay();

        if ($start->diffInDays($end) > 31) {
            Notification::make()
                ->title('Range too wide')
                ->body('Please keep the range to 31 days or less.')
                ->danger()
                ->send();

            return;
        }

        try {
            $client = app(TripJackClient::class);
            $response = $client->bookingList(
                $start->format('Y-m-d\TH:i:s'),
                $end->format('Y-m-d\TH:i:s'),
            );
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

        $this->results = $tjBookings->map(function (array $tj) use ($localByTjId) {
            $local = $localByTjId->get($tj['bookingId'] ?? null);
            $expectedLocalStatus = $this->mapTripjackStatus($tj['status'] ?? null);

            return [
                'bookingId' => $tj['bookingId'] ?? '—',
                'tjStatus' => $tj['status'] ?? '—',
                'tjTotalPrice' => $tj['totalPrice'] ?? null,
                'localReference' => $local?->reference,
                'localStatus' => $local?->status,
                'localTotalPrice' => $local?->tripjack_total_price,
                // Flag anything that doesn't have a matching local row at
                // all, or whose local status doesn't match what TripJack's
                // status *should* map to (same mapping submitBooking()'s
                // confirmation flow uses) — the actual thing an ops person
                // needs to notice, not a raw string comparison (our local
                // statuses use a different vocabulary than TripJack's).
                'mismatch' => ! $local || $local->status !== $expectedLocalStatus,
            ];
        })->values()->all();

        $this->searched = true;

        Notification::make()
            ->title(count($this->results).' booking(s) returned by TripJack')
            ->body(collect($this->results)->where('mismatch', true)->count().' flagged for review.')
            ->success()
            ->send();
    }

    /**
     * Mirrors FrontendController::mapTripjackBookingStatus() — kept as a
     * separate copy rather than reusing that protected controller method,
     * since this is a read-only comparison, not something that should be
     * able to accidentally inherit controller-specific side effects.
     */
    protected function mapTripjackStatus(?string $tripjackStatus): string
    {
        return match ($tripjackStatus) {
            'CANCELLED' => 'cancelled',
            'ABORTED', 'FAILED' => 'failed_needs_review',
            default => 'confirmed',
        };
    }
}
