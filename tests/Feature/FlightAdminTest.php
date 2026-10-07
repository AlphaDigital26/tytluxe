<?php

namespace Tests\Feature;

use App\Filament\Pages\FlightSettings as FlightSettingsPage;
use App\Filament\Resources\FlightBookings\FlightBookingResource;
use App\Filament\Resources\FlightBookings\Pages\ListFlightBookings;
use App\Models\Admin;
use App\Models\Booking;
use App\Models\User;
use App\Services\FlightBookingService;
use App\Services\FlightPricingService;
use App\Support\FlightBookingStatus;
use App\Support\FlightSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class FlightAdminTest extends TestCase
{
    use RefreshDatabase;

    protected Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = Admin::create([
            'name' => 'Owner', 'email' => 'owner@example.com', 'password' => 'secret-pass',
            'role' => 'Super Admin', 'status' => 'Active',
        ]);
    }

    protected function flightBooking(array $overrides = []): Booking
    {
        $user = User::factory()->create();

        return Booking::create(array_merge([
            'reference' => 'TYT-F'.random_int(10000, 99999),
            'user_id' => $user->id,
            'guest_email' => 'guest@example.com',
            'guest_phone' => '+919876543210',
            'lead_guest_name' => 'Asha Rao',
            'vertical' => 'flight',
            'flight_route' => 'BOM-DEL',
            'flight_journey_type' => 'ONEWAY',
            'flight_departure_date' => now()->addDays(3)->toDateString(),
            'flight_legs' => [['src' => 'BOM', 'dest' => 'DEL', 'departureDate' => now()->addDays(3)->toDateString()]],
            'flight_segments_payload' => ['travellerInfo' => [['ti' => 'Ms', 'pt' => 'ADULT', 'fN' => 'Asha', 'lN' => 'Rao']]],
            'pax_adults' => 1,
            'pax_children' => 0,
            'pax_infants' => 0,
            'base_amount' => 5000,
            'tax_amount' => 0,
            'total_amount' => 5700,
            'tripjack_total_price' => 5000,
            'margin_amount' => 500,
            'currency' => 'INR',
            'status' => 'confirmed',
            'tripjack_booking_id' => 'TJS'.random_int(100000000, 999999999),
            'tripjack_flight_pnr' => ['BOM-DEL' => 'ABC123'],
        ], $overrides));
    }

    public function test_admin_pages_open_for_flight_bookings(): void
    {
        $booking = $this->flightBooking();
        $this->flightBooking(['status' => 'failed_needs_review', 'admin_note' => 'TripJack did not answer.']);

        $this->actingAs($this->admin, 'admin');

        $this->get(FlightBookingResource::getUrl('index'))->assertOk()->assertSee('Asha Rao')->assertSee('Needs your attention');
        $this->get(FlightBookingResource::getUrl('view', ['record' => $booking]))
            ->assertOk()
            ->assertSee('ABC123')
            ->assertSee('Your earning')
            ->assertSee('BOM');
        $this->get(FlightSettingsPage::getUrl())->assertOk()->assertSee('Your Earnings on Each Ticket');
        $this->get('/tyt-console')->assertOk();
    }

    public function test_needs_attention_tab_lists_only_problem_bookings(): void
    {
        $this->flightBooking(['reference' => 'TYT-OK']);
        $this->flightBooking(['reference' => 'TYT-BAD', 'status' => 'failed_needs_review']);
        $this->flightBooking(['reference' => 'TYT-STUCK', 'cancellation_requested_at' => now()->subHours(5)]);
        $this->flightBooking(['reference' => 'TYT-REFUND', 'status' => 'cancelled', 'cancellation_reason' => 'Cancelled. No automatic refund applied — needs manual review.']);

        $this->actingAs($this->admin, 'admin');

        Livewire::test(ListFlightBookings::class, ['activeTab' => 'attention'])
            ->assertCanSeeTableRecords(Booking::whereIn('reference', ['TYT-BAD', 'TYT-STUCK', 'TYT-REFUND'])->get())
            ->assertCanNotSeeTableRecords(Booking::where('reference', 'TYT-OK')->get());

        $this->assertSame('Confirmed', FlightBookingStatus::for(Booking::where('reference', 'TYT-OK')->first())['label']);
    }

    public function test_hotel_bookings_screen_no_longer_lists_flights(): void
    {
        $this->assertSame(0, \App\Filament\Resources\Bookings\BookingResource::getEloquentQuery()->where('vertical', 'flight')->count());
    }

    public function test_saving_settings_changes_markup_and_switches(): void
    {
        $this->actingAs($this->admin, 'admin');
        $this->assertSame(500.0, FlightPricingService::price(5000)['margin_amount']);

        Livewire::test(FlightSettingsPage::class)
            ->set('data.markup_percent', 8)
            ->set('data.allow_hold', false)
            ->set('data.allow_guest_cancel', false)
            ->call('save')
            ->assertHasNoErrors();

        app()->forgetInstance(FlightSettings::class);
        $this->assertSame(400.0, FlightPricingService::price(5000)['margin_amount']);
        $this->assertSame(0.08, FlightPricingService::formula()['marginRate']);
        $this->assertFalse(FlightSettings::allowHold());
        $this->assertFalse(FlightSettings::allowGuestCancel());
        $this->assertTrue(FlightSettings::allowGuestReschedule());
    }

    public function test_settings_page_is_super_admin_only(): void
    {
        $support = Admin::create([
            'name' => 'Desk', 'email' => 'desk@example.com', 'password' => 'secret-pass',
            'role' => 'Support', 'status' => 'Active',
        ]);

        $this->actingAs($support, 'admin')->get(FlightSettingsPage::getUrl())->assertForbidden();
    }

    public function test_switching_flight_booking_off_blocks_search_but_not_existing_bookings(): void
    {
        FlightSettings::save(['booking_enabled' => false, 'disabled_message' => 'Call us to book flights.']);

        $this->get('/flights')->assertOk()->assertSee('Call us to book flights.');
        $this->get(route('flights.search', ['from' => 'BOM', 'to' => 'DEL', 'depart_date' => now()->addDays(5)->toDateString()]))
            ->assertRedirect(route('flights'));
        $this->getJson(route('flights.fare-calendar.ajax'))->assertStatus(503);

        // Post-booking routes (payment, confirmation, cancellation) are never behind the switch.
        $this->assertNotContains('flight.enabled:booking', app('router')->getRoutes()->getByName('hotel.booking.confirmation')->gatherMiddleware());
        $this->assertNotContains('flight.enabled:booking', app('router')->getRoutes()->getByName('payment.razorpay.callback')->gatherMiddleware());
    }

    public function test_guest_cancel_and_extras_switches_send_guest_to_support(): void
    {
        FlightSettings::save(['allow_guest_cancel' => false, 'allow_guest_extras' => false]);
        $booking = $this->flightBooking();

        $this->actingAs($booking->user)
            ->get(route('hotel.booking.cancel.show', $booking->reference))
            ->assertRedirect(route('hotel.booking.confirmation', $booking->reference))
            ->assertSessionHas('booking_error', FlightSettings::contactUsMessage());

        $this->actingAs($booking->user)
            ->get(route('flights.extras.show', $booking->reference))
            ->assertRedirect(route('hotel.booking.confirmation', $booking->reference));
    }

    public function test_itinerary_and_every_travellers_tickets_are_saved_from_booking_details(): void
    {
        $booking = $this->flightBooking(['tripjack_flight_pnr' => null]);
        $details = [
            'order' => ['status' => 'SUCCESS'],
            'itemInfos' => ['AIR' => [
                'tripInfos' => [['sI' => [[
                    'fD' => ['aI' => ['code' => '6E', 'name' => 'IndiGo'], 'fN' => '2134'],
                    'da' => ['code' => 'BOM', 'city' => 'Mumbai'], 'aa' => ['code' => 'DEL', 'city' => 'Delhi'],
                    'dt' => '2026-10-10T06:00', 'at' => '2026-10-10T08:10',
                ]]]],
                'travellerInfos' => [
                    ['fN' => 'Asha', 'lN' => 'Rao', 'pnrDetails' => ['BOM-DEL' => 'XYZ789'], 'ticketNumberDetails' => ['BOM-DEL' => '1234567890']],
                    ['fN' => 'Ravi', 'lN' => 'Rao', 'pnrDetails' => ['BOM-DEL' => 'XYZ789'], 'ticketNumberDetails' => ['BOM-DEL' => '1234567891']],
                ],
            ]],
        ];

        app(FlightBookingService::class)->applyBookingDetails($booking, $details, app(\App\Services\Payment\RazorpayService::class));
        $booking->refresh();

        $this->assertSame('IndiGo', $booking->flight_itinerary['segments'][0]['airline']);
        $this->assertSame(['BOM-DEL' => '1234567891'], $booking->flight_itinerary['tickets']['RAVI RAO']);
        $this->assertSame(['BOM-DEL' => 'XYZ789'], $booking->tripjack_flight_pnr);
    }
}
