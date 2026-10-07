<?php

namespace App\Support;

use App\Filament\Pages\FlightSettings as FlightSettingsPage;
use App\Filament\Pages\HotelListingSettings;
use App\Filament\Pages\TripJackNotifications;
use App\Filament\Resources\Bookings\BookingResource;
use App\Filament\Resources\Enquiries\EnquiryResource;
use App\Filament\Resources\FlightBookings\FlightBookingResource;
use App\Filament\Resources\Reviews\ReviewResource;
use App\Models\Booking;
use App\Models\Enquiry;
use App\Models\Review;
use App\Models\Setting;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

/**
 * The numbers behind the admin dashboard, in one place so every card counts
 * the same way. Database only — never calls TripJack, so the dashboard
 * always opens quickly.
 */
class DashboardData
{
    /** Booking statuses that count as a sale (money kept, trip going ahead). */
    public const SOLD = ['confirmed', 'pending_confirmation'];

    public static function sales(): Builder
    {
        return Booking::whereIn('status', self::SOLD);
    }

    /**
     * Things a person should act on, most urgent first. Only items with
     * something to do, and only ones this admin is allowed to open.
     *
     * @return array<int, array{icon: string, tone: string, count: ?int, title: string, text: string, url: string, button: string}>
     */
    public static function todo(): array
    {
        $items = [];

        if (EnquiryResource::canViewAny() && ($n = Enquiry::where('status', 'new')->count())) {
            $items[] = ['icon' => '📞', 'tone' => 'danger', 'count' => $n,
                'title' => $n === 1 ? 'New enquiry waiting for a call' : 'New enquiries waiting for a call',
                'text' => 'Guests asked about a trip and nobody has contacted them yet.',
                'url' => EnquiryResource::getUrl('index').'?activeTab=new', 'button' => 'Call them'];
        }

        if (BookingResource::canViewAny() && ($n = HotelBookingStatus::applyNeedsAttention(BookingResource::getEloquentQuery())->count())) {
            $items[] = ['icon' => '🏨', 'tone' => 'danger', 'count' => $n,
                'title' => $n === 1 ? 'Hotel booking needs a look' : 'Hotel bookings need a look',
                'text' => 'Something is stuck or a refund could not be paid automatically.',
                'url' => BookingResource::getUrl('index').'?activeTab=attention', 'button' => 'See bookings'];
        }

        if (FlightBookingResource::canViewAny() && ($n = FlightBookingStatus::applyNeedsAttention(FlightBookingResource::getEloquentQuery())->count())) {
            $items[] = ['icon' => '✈️', 'tone' => 'danger', 'count' => $n,
                'title' => $n === 1 ? 'Flight booking needs a look' : 'Flight bookings need a look',
                'text' => 'A ticket, cancellation or refund did not finish by itself.',
                'url' => FlightBookingResource::getUrl('index').'?activeTab=attention', 'button' => 'See bookings'];
        }

        $balance = self::walletBalance();
        if ($balance !== null && $balance['low']) {
            $items[] = ['icon' => '💳', 'tone' => 'danger', 'count' => null,
                'title' => 'TripJack wallet is running low',
                'text' => 'Only ₹'.number_format($balance['amount'], 0).' left. Top it up, or new hotel and flight bookings will fail.',
                'url' => FlightSettingsPage::canAccess() ? FlightSettingsPage::getUrl() : '#', 'button' => 'Details'];
        }

        if (ReviewResource::canViewAny() && ($n = Review::where('is_published', false)->count())) {
            $items[] = ['icon' => '⭐', 'tone' => 'warning', 'count' => $n,
                'title' => $n === 1 ? 'Guest review waiting for approval' : 'Guest reviews waiting for approval',
                'text' => 'Read them and switch on the ones you want shown on the website.',
                'url' => ReviewResource::getUrl('index').'?tableFilters[is_published][value]=0', 'button' => 'Review them'];
        }

        if (! HotelSettings::bookingEnabled() && HotelListingSettings::canAccess()) {
            $items[] = ['icon' => '⏸️', 'tone' => 'warning', 'count' => null,
                'title' => 'Online hotel booking is switched OFF',
                'text' => 'Guests can browse hotels but cannot book. Switch it back on when you are ready.',
                'url' => HotelListingSettings::getUrl(), 'button' => 'Hotel settings'];
        }

        if (! FlightSettings::bookingEnabled() && FlightSettingsPage::canAccess()) {
            $items[] = ['icon' => '⏸️', 'tone' => 'warning', 'count' => null,
                'title' => 'Online flight booking is switched OFF',
                'text' => 'Guests cannot search or book flights right now.',
                'url' => FlightSettingsPage::getUrl(), 'button' => 'Flight settings'];
        }

        if ($n = (int) auth('admin')->user()?->unreadNotifications()->count()) {
            $items[] = ['icon' => '🔔', 'tone' => 'info', 'count' => $n,
                'title' => $n === 1 ? 'Unread alert' : 'Unread alerts',
                'text' => 'Automatic warnings from the website, such as TripJack problems.',
                'url' => TripJackNotifications::getUrl(), 'button' => 'Read'];
        }

        return $items;
    }

    /** @return array{amount: float, low: bool, checked_at: ?string}|null */
    public static function walletBalance(): ?array
    {
        $last = Setting::getJson('tripjack_alert.last_balance', []);
        if (! isset($last['amount'])) {
            return null;
        }

        return [
            'amount' => (float) $last['amount'],
            'low' => (float) $last['amount'] < FlightSettings::lowBalanceAlert(),
            'checked_at' => $last['checked_at'] ?? null,
        ];
    }

    /**
     * Check-ins and flight departures in the next $days days, soonest first.
     *
     * @return array<int, array{date: Carbon, icon: string, guest: string, what: string, url: string, status: array}>
     */
    public static function upcomingTrips(int $days = 7, int $limit = 8): array
    {
        $trips = [];

        if (BookingResource::canViewAny()) {
            BookingResource::getEloquentQuery()->with('hotel')
                ->whereIn('status', self::SOLD)->whereNull('cancellation_requested_at')
                ->whereBetween('check_in', [today(), today()->addDays($days)])
                ->orderBy('check_in')->limit($limit)->get()
                ->each(function (Booking $b) use (&$trips) {
                    $trips[] = ['date' => Carbon::parse($b->check_in), 'icon' => '🏨', 'guest' => (string) $b->lead_guest_name,
                        'what' => ($b->hotel?->title ?? 'Hotel').($b->check_out ? ' · '.Carbon::parse($b->check_in)->diffInDays(Carbon::parse($b->check_out)).' nights' : ''),
                        'url' => BookingResource::getUrl('view', ['record' => $b]), 'status' => HotelBookingStatus::for($b)];
                });
        }

        if (FlightBookingResource::canViewAny()) {
            FlightBookingResource::getEloquentQuery()
                ->where('status', 'confirmed')->whereNull('cancellation_requested_at')
                ->whereBetween('flight_departure_date', [today(), today()->addDays($days)])
                ->orderBy('flight_departure_date')->limit($limit)->get()
                ->each(function (Booking $b) use (&$trips) {
                    $trips[] = ['date' => Carbon::parse($b->flight_departure_date), 'icon' => '✈️', 'guest' => (string) $b->lead_guest_name,
                        'what' => str_replace('-', ' → ', (string) $b->flight_route).($b->flight_journey_type === 'RETURN' ? ' (round trip)' : ''),
                        'url' => FlightBookingResource::getUrl('view', ['record' => $b]), 'status' => FlightBookingStatus::for($b)];
                });
        }

        usort($trips, fn ($a, $b) => $a['date'] <=> $b['date']);

        return array_slice($trips, 0, $limit);
    }

    /**
     * Per-month totals for the last $months months (oldest first).
     *
     * @return array{labels: array<int, string>, hotel: array<int, float>, flight: array<int, float>, earning: array<int, float>, bookings: array<int, int>, enquiries: array<int, int>}
     */
    public static function monthly(int $months = 6): array
    {
        $out = ['labels' => [], 'hotel' => [], 'flight' => [], 'earning' => [], 'bookings' => [], 'enquiries' => []];

        for ($i = $months - 1; $i >= 0; $i--) {
            $start = now()->startOfMonth()->subMonths($i);
            $end = $start->copy()->endOfMonth();
            $month = fn () => self::sales()->whereBetween('created_at', [$start, $end]);

            $out['labels'][] = $start->format('M');
            $out['hotel'][] = round((float) $month()->where('vertical', '!=', 'flight')->sum('total_amount'), 2);
            $out['flight'][] = round((float) $month()->where('vertical', 'flight')->sum('total_amount'), 2);
            $out['earning'][] = round((float) $month()->sum('margin_amount'), 2);
            $out['bookings'][] = $month()->count();
            $out['enquiries'][] = Enquiry::whereBetween('created_at', [$start, $end])->count();
        }

        return $out;
    }
}
