<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Services\CabBookingService;
use App\Services\Payment\PaymentOrderException;
use App\Services\TripJack\Exceptions\TripJackException;
use App\Services\TripJack\TripJackCabClient;
use App\Support\CabSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Cab pages: search → results → review (passenger) → book. Payment,
 * confirmation and cancellation are reached through the shared
 * /booking/{reference}/* routes — FrontendController hands cab bookings to
 * the methods at the bottom of this class.
 *
 * A search and its results are kept in the session (cab_search.{id}) for
 * QUOTE_MINUTES, so the review and book steps use exactly the cab and price
 * the guest saw.
 */
class CabController extends Controller
{
    /** How long a search's prices are used before the guest must search again. */
    public const QUOTE_MINUTES = 30;

    public function __construct(protected CabBookingService $cabs)
    {
    }

    public function index()
    {
        return view('pages.cabs', [
            'bookingEnabled' => CabSettings::bookingEnabled(),
            'disabledMessage' => CabSettings::disabledMessage(),
            'upcomingFlights' => auth()->check() ? $this->upcomingFlights(auth()->id()) : [],
        ]);
    }

    /**
     * The guest's confirmed flights still to come, each with an "Add Cab"
     * search: an airport pickup at the arrival airport, 30 minutes after landing.
     *
     * @return array<int, array{reference: string, name: string, route: string, arrives: Carbon, airport: string, cabQuery: array}>
     */
    protected function upcomingFlights(int $userId): array
    {
        return Booking::where('user_id', $userId)->where('vertical', 'flight')->where('status', 'confirmed')
            ->whereNull('cancellation_requested_at')->whereDate('flight_departure_date', '>=', today())
            ->orderBy('flight_departure_date')->limit(10)->get()
            ->map(function (Booking $b) {
                $segments = $b->flight_itinerary['segments'] ?? [];
                $dest = $b->flight_legs[0]['dest'] ?? null;
                $landing = collect($segments)->first(fn ($s) => ($s['to'] ?? null) === $dest) ?? collect($segments)->first();
                if (! $landing || empty($landing['arrives'])) {
                    return null;
                }
                $arrives = Carbon::parse($landing['arrives']);
                $pickup = $arrives->copy()->addMinutes(30)->ceilMinutes(15);
                if ($pickup->lt(now()->addMinutes(CabBookingService::MIN_LEAD_MINUTES))) {
                    return null; // too soon to book a cab online
                }
                // "Chhatrapati Shivaji Airport, Mumbai" — what the place search understands.
                $name = trim((string) (($landing['toAirport'] ?? '') ?: ($landing['toCity'] ?? $landing['to'])));
                $airport = (str_contains(strtolower($name), 'airport') ? $name : $name.' Airport')
                    .(filled($landing['toCity'] ?? null) ? ', '.$landing['toCity'] : '');

                return [
                    'reference' => $b->reference,
                    'name' => (string) $b->lead_guest_name,
                    'route' => str_replace('-', ' → ', (string) $b->flight_route),
                    'arrives' => $arrives,
                    'airport' => $airport,
                    'cabQuery' => [
                        'journey' => 'airport_transfer',
                        'trip' => 'oneway',
                        'from' => $airport,
                        'pickup' => $pickup->format('Y-m-d\TH:i'),
                        'passengers' => max(1, min(CabBookingService::MAX_PASSENGERS, (int) $b->pax_adults + (int) $b->pax_children)),
                    ],
                ];
            })->filter()->values()->all();
    }

    /** Place suggestions for the From / To boxes. */
    public function places(Request $request, TripJackCabClient $client)
    {
        $q = trim((string) $request->query('q', ''));
        if (mb_strlen($q) < 3) {
            return response()->json(['places' => []]);
        }

        // Same words typed again (by anyone) come back instantly: places don't change.
        $key = 'cab_places:'.md5(mb_strtolower(preg_replace('/\s+/', ' ', mb_substr($q, 0, 80))));
        $places = \Illuminate\Support\Facades\Cache::get($key);

        if ($places === null) {
            try {
                $places = collect($client->places(mb_substr($q, 0, 80)))
                    ->map(fn ($p) => ['id' => (string) ($p['id'] ?? $p['value'] ?? ''), 'label' => (string) ($p['displayLabel'] ?? $p['name'] ?? '')])
                    ->filter(fn ($p) => $p['id'] !== '' && $p['label'] !== '')
                    ->take(8)->values()->all();
            } catch (TripJackException $e) {
                return response()->json(['places' => [], 'message' => 'Place search is not available right now.'], 502);
            }
            if ($places) {
                \Illuminate\Support\Facades\Cache::put($key, $places, now()->addDay());
            }
        }

        return response()->json(['places' => $places])->header('Cache-Control', 'private, max-age=3600');
    }

    public function search(Request $request)
    {
        $data = $request->validate([
            'journey' => 'required|in:'.implode(',', array_keys(CabBookingService::JOURNEY_TYPES)),
            'trip' => 'required|in:oneway,roundtrip',
            'from_id' => 'required|string|max:300',
            'from' => 'required|string|max:300',
            'to_id' => 'required|string|max:300',
            'to' => 'required|string|max:300',
            'pickup' => 'required|date',
            'return' => 'nullable|required_if:trip,roundtrip|date',
            'passengers' => 'required|integer|min:1|max:'.CabBookingService::MAX_PASSENGERS,
            'bags' => 'nullable|integer|min:0|max:20',
        ], [
            'from_id.required' => 'Please pick your pickup place from the suggestions.',
            'to_id.required' => 'Please pick your drop place from the suggestions.',
            'return.required_if' => 'Please choose a return date and time for a round trip.',
        ]);

        $pickup = Carbon::parse($data['pickup']);
        $roundTrip = $data['trip'] === 'roundtrip';
        $return = $roundTrip ? Carbon::parse($data['return']) : null;
        $error = match (true) {
            $pickup->lt(now()->addMinutes(CabBookingService::MIN_LEAD_MINUTES)) => 'Pickup must be at least '.(CabBookingService::MIN_LEAD_MINUTES / 60).' hours from now.',
            $pickup->gt(now()->addMonths(6)) => 'Please choose a pickup within the next 6 months.',
            $roundTrip && $return->lt($pickup->copy()->addMinutes(30)) => 'The return must be at least 30 minutes after pickup.',
            $data['from_id'] === $data['to_id'] => 'Pickup and drop must be different places.',
            default => null,
        };
        if ($error) {
            return redirect()->route('cabs', $request->query())->with('booking_error', $error);
        }

        $search = [
            'journeyType' => $data['journey'],
            'roundTrip' => $roundTrip,
            'pickupAt' => $pickup->toIso8601String(),
            'returnAt' => $return?->toIso8601String(),
            'passengers' => (int) $data['passengers'],
        ];

        try {
            $search['origin'] = $this->cabs->location($data['from_id'], $data['from']);
            $search['destination'] = $this->cabs->location($data['to_id'], $data['to']);
            $result = $this->cabs->search($search);
            // Only cabs with room for everyone and their bags.
            $bags = (int) ($data['bags'] ?? 0);
            $result['options'] = array_values(array_filter($result['options'], fn ($o) => ($o['seats'] <= 0 || $o['seats'] >= $search['passengers']) && ($o['bags'] <= 0 || $o['bags'] >= $bags)));
        } catch (TripJackException $e) {
            Log::channel('tripjack')->warning('cab_search_failed', ['message' => $e->getMessage()]);

            return view('pages.cab-results', ['search' => $search + ['from' => $data['from'], 'to' => $data['to']], 'result' => null, 'searchId' => null, 'query' => $request->query(),
                'error' => 'We couldn\'t fetch cabs for this trip right now. Please try again, or try a different time.']);
        }

        $searchId = Str::random(12);
        $this->forgetOldSearches();
        session()->put("cab_search.$searchId", ['search' => $search, 'result' => $result, 'at' => now()->timestamp]);

        return view('pages.cab-results', [
            'search' => $search,
            'result' => $result,
            'searchId' => $searchId,
            'query' => $request->query(),
            'error' => null,
        ]);
    }

    public function review(Request $request)
    {
        [$saved, $option] = $this->chosen((string) $request->query('search'), (string) $request->query('cab'));
        if (! $option) {
            return redirect()->route('cabs')->with('booking_error', 'Prices for that search have expired. Please search again.');
        }

        return view('pages.cab-review', [
            'searchId' => $request->query('search'),
            'search' => $saved['search'],
            'option' => $option,
            'user' => $request->user(),
            'travellers' => $request->user()->savedTravellers()->orderBy('first_name')->get(['id', 'first_name', 'last_name', 'phone', 'email']),
            'configured' => CabBookingService::configured(),
        ]);
    }

    public function book(Request $request)
    {
        $data = $request->validate([
            'search' => 'required|string',
            'cab' => 'required|string',
            'first_name' => ['required', 'string', 'max:50', 'regex:/^[\pL .\'-]+$/u'],
            'last_name' => ['required', 'string', 'max:50', 'regex:/^[\pL .\'-]+$/u'],
            'email' => 'required|email:rfc|max:120',
            'phone_code' => 'nullable|string|max:5',
            'phone' => 'required|string|min:7|max:20',
            // Required for airport transfers, as on TripJack's own checkout:
            // the driver follows the flight for delays.
            'flight_number' => [Rule::requiredIf(fn () => (session("cab_search.{$request->input('search')}.search.journeyType")) === 'airport_transfer'), 'nullable', 'string', 'max:10', 'regex:/^[A-Za-z0-9 ]+$/'],
            'request' => 'nullable|string|max:300',
            'save_traveller' => 'nullable|boolean',
            'accept' => 'accepted',
        ], [
            'flight_number.required' => 'Please enter the flight number, so the driver can follow your flight.',
            'first_name.regex' => 'Please enter the name using letters only.',
            'last_name.regex' => 'Please enter the name using letters only.',
            'accept.accepted' => 'Please accept the cab terms and cancellation policy.',
        ]);

        [$saved, $option] = $this->chosen($data['search'], $data['cab']);
        if (! $option) {
            return redirect()->route('cabs')->with('booking_error', 'Prices for that search have expired. Please search again.');
        }
        if (Carbon::parse($saved['search']['pickupAt'])->lt(now()->addMinutes(120))) {
            return redirect()->route('cabs')->with('booking_error', 'Pickup is now less than 2 hours away, which is too soon to book online. Please call us, or choose a later time.');
        }
        if (! CabBookingService::configured()) {
            Log::channel('tripjack')->critical('cab_agent_not_configured');

            return back()->withInput()->with('booking_error', CabSettings::disabledMessage());
        }

        $booking = $this->cabs->createBooking($request->user()->id, $saved['search'], $saved['result'], $option, [
            'firstName' => trim($data['first_name']),
            'lastName' => trim($data['last_name']),
            'email' => $data['email'],
            'phone' => preg_replace('/[^\d+]/', '', $data['phone']),
            'phoneCode' => ltrim((string) ($data['phone_code'] ?? '91'), '+') ?: '91',
            'flightNumber' => $data['flight_number'] ?? null,
            'request' => $data['request'] ?? null,
        ]);
        session()->forget("cab_search.{$data['search']}");

        // "Add this to my travellers list" — once per name.
        if ($request->boolean('save_traveller')) {
            $request->user()->savedTravellers()->firstOrCreate(
                ['first_name' => trim($data['first_name']), 'last_name' => trim($data['last_name'])],
                ['phone' => '+'.(ltrim((string) ($data['phone_code'] ?? '91'), '+') ?: '91').preg_replace('/\D+/', '', $data['phone']), 'email' => $data['email']],
            );
        }

        return redirect()->route('hotel.payment.show', $booking->reference);
    }

    // ── Shared /booking/{reference}/* pages, for cab bookings ─────────────

    public function payment(Booking $booking)
    {
        if (! in_array($booking->status, ['pending_payment', 'payment_failed'], true)) {
            return redirect()->route('hotel.booking.confirmation', $booking->reference);
        }

        // Too close to pickup now (the guest left the payment page open).
        $tooLate = $booking->cab_pickup_at?->lt(now()->addMinutes(120));
        $payment = null;
        if (! $tooLate) {
            try {
                $payment = $this->cabs->paymentFor($booking);
            } catch (PaymentOrderException $e) {
                return redirect()->route('cabs')->with('booking_error', $e->getMessage());
            }
        }

        return view('pages.cab-payment', [
            'booking' => $booking,
            'payment' => $payment,
            'razorpayKeyId' => config('services.razorpay.key_id'),
        ]);
    }

    public function confirmation(Booking $booking)
    {
        $live = $booking->cab_details['live'] ?? null;
        $stale = ! $live || Carbon::parse($live['checkedAt'] ?? '2000-01-01')->lt(now()->subMinutes(2));
        if ($stale && in_array($booking->status, ['confirmed', 'failed_needs_review'], true) && $booking->tripjack_booking_id) {
            $this->cabs->refreshStatus($booking);
            $booking->refresh();
        }

        return view('pages.cab-booking-confirmation', [
            'booking' => $booking,
            'canCancel' => CabBookingService::guestCanCancel($booking),
        ]);
    }

    public function showCancellation(Booking $booking)
    {
        if (! CabBookingService::guestCanCancel($booking)) {
            return redirect()->route('hotel.booking.confirmation', $booking->reference)
                ->with('booking_error', $booking->status === 'confirmed' && $booking->cancellation_requested_at === null ? CabSettings::contactUsMessage() : null);
        }

        return view('pages.cab-booking-cancel', [
            'booking' => $booking,
            'quote' => $this->cabs->cancellationQuote($booking),
        ]);
    }

    public function submitCancellation(Booking $booking, Request $request)
    {
        if (! CabBookingService::guestCanCancel($booking)) {
            return redirect()->route('hotel.booking.confirmation', $booking->reference);
        }
        $request->validate(['confirm' => 'accepted'], ['confirm.accepted' => 'Please tick the box to confirm you want to cancel.']);

        $result = $this->cabs->cancel($booking, 'by guest');

        return redirect()->route('hotel.booking.confirmation', $booking->reference)
            ->with($result['ok'] ? 'booking_success' : 'booking_error', $result['message']);
    }

    /** @return array{0: array|null, 1: array|null} the saved search and the chosen cab, if still valid */
    protected function chosen(string $searchId, string $key): array
    {
        $saved = session("cab_search.$searchId");
        if (! $saved || ($saved['at'] ?? 0) < now()->subMinutes(self::QUOTE_MINUTES)->timestamp) {
            return [null, null];
        }
        $option = collect($saved['result']['options'] ?? [])->firstWhere('key', $key);

        return [$saved, $option];
    }

    protected function forgetOldSearches(): void
    {
        foreach (array_keys((array) session('cab_search', [])) as $id) {
            if ((session("cab_search.$id.at") ?? 0) < now()->subMinutes(self::QUOTE_MINUTES)->timestamp) {
                session()->forget("cab_search.$id");
            }
        }
    }
}
