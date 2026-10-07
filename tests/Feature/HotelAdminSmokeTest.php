<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Booking;
use App\Models\Destination;
use App\Models\Hotel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class HotelAdminSmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_hotel_admin_screen_opens(): void
    {
        Http::fake(['*' => Http::response(['status' => ['success' => false]], 400)]);

        $admin = Admin::create(['name' => 'Owner', 'email' => 'o@example.com', 'password' => 'secret-pass', 'role' => 'Super Admin', 'status' => 'Active']);
        $destination = Destination::create(['name' => 'Goa', 'slug' => 'goa', 'country' => 'India']);
        $hotel = Hotel::create(['title' => 'Sea View', 'slug' => 'sea-view-1', 'destination_id' => $destination->id, 'star_rating' => 4, 'is_active' => true, 'description' => 'Nice', 'category' => 'city_luxury', 'address' => 'Goa', 'price_from' => 0, 'source' => 'tripjack', 'tripjack_hotel_id' => '100001']);
        $booking = Booking::create([
            'reference' => 'TYT-H1', 'user_id' => User::factory()->create()->id, 'guest_email' => 'g@example.com', 'guest_phone' => '9876543210', 'lead_guest_name' => 'Ravi Kumar',
            'vertical' => 'hotel', 'hotel_id' => $hotel->id, 'check_in' => now()->addDays(5), 'check_out' => now()->addDays(7),
            'pax_adults' => 2, 'pax_children' => 0, 'base_amount' => 9000, 'tax_amount' => 0, 'total_amount' => 10000,
            'currency' => 'INR', 'status' => 'confirmed', 'tripjack_booking_id' => 'TJH1',
        ]);

        $this->actingAs($admin, 'admin');

        $urls = [
            '/tyt-console/hotels',
            "/tyt-console/hotels/{$hotel->id}/edit",
            '/tyt-console/destinations',
            "/tyt-console/destinations/{$destination->id}/edit",
            '/tyt-console/amenities',
            '/tyt-console/bookings',
            "/tyt-console/bookings/{$booking->id}",
            "/tyt-console/bookings/{$booking->id}/edit",
            '/tyt-console/hotel-listing-settings',
            '/tyt-console/tripjack-booking-reconciliation',
        ];

        foreach ($urls as $url) {
            $status = $this->get($url)->status();
            $this->assertSame(200, $status, "$url returned $status");
        }

        // The booking page no longer calls TripJack when it opens.
        Http::assertNothingSent();
        $this->get("/tyt-console/bookings/{$booking->id}")->assertSee('Confirmed')->assertSee('Check status with TripJack');
        $this->get('/tyt-console/bookings')->assertSee('Sea View')->assertSee('Needs your attention');
        $this->get('/tyt-console/bookings/create')->assertNotFound();
    }

    public function test_hotel_settings_change_markup_and_switches(): void
    {
        $admin = Admin::create(['name' => 'Owner', 'email' => 'o@example.com', 'password' => 'secret-pass', 'role' => 'Super Admin', 'status' => 'Active']);
        $this->actingAs($admin, 'admin');
        $this->assertSame(1000.0, \App\Services\HotelPricingService::price(10000)['margin_amount']);

        \Livewire\Livewire::test(\App\Filament\Pages\HotelListingSettings::class)
            ->set('data.markup_percent', 12)
            ->set('data.booking_enabled', false)
            ->set('data.disabled_message', 'Call us for hotels.')
            ->set('data.allow_guest_cancel', false)
            ->call('save')
            ->assertHasNoErrors();

        app()->forgetInstance(\App\Support\HotelSettings::class);
        $this->assertSame(1200.0, \App\Services\HotelPricingService::price(10000)['margin_amount']);
        $this->assertFalse(\App\Support\HotelSettings::bookingEnabled());
        $this->assertFalse(\App\Support\HotelSettings::allowGuestCancel());
        $this->assertSame([1, 2, 3, 4, 5], \App\Models\Hotel::allowedStarRatings());

        $user = User::factory()->create();
        $this->actingAs($user)
            ->post(route('hotel.review', 'any-hotel'))
            ->assertRedirect(route('hotel.details', 'any-hotel'))
            ->assertSessionHas('booking_error', 'Call us for hotels.');
    }

    public function test_hotel_settings_page_is_super_admin_only(): void
    {
        $support = Admin::create(['name' => 'Desk', 'email' => 'd@example.com', 'password' => 'secret-pass', 'role' => 'Support', 'status' => 'Active']);

        $this->actingAs($support, 'admin')->get('/tyt-console/hotel-listing-settings')->assertForbidden();
    }

    public function test_edit_form_only_saves_contact_details_and_notes(): void
    {
        $admin = Admin::create(['name' => 'Owner', 'email' => 'o@example.com', 'password' => 'secret-pass', 'role' => 'Super Admin', 'status' => 'Active']);
        $booking = Booking::create([
            'reference' => 'TYT-H2', 'user_id' => User::factory()->create()->id, 'guest_email' => 'g@example.com', 'guest_phone' => '9876543210', 'lead_guest_name' => 'Ravi',
            'vertical' => 'hotel', 'pax_adults' => 2, 'pax_children' => 0, 'base_amount' => 9000, 'tax_amount' => 0, 'total_amount' => 10000,
            'currency' => 'INR', 'status' => 'confirmed',
        ]);
        $this->actingAs($admin, 'admin');

        \Livewire\Livewire::test(\App\Filament\Resources\Bookings\Pages\EditBooking::class, ['record' => $booking->id])
            ->fillForm(['guest_email' => 'fixed@example.com', 'admin_note' => 'Guest called.'])
            ->call('save')
            ->assertHasNoErrors();

        $booking->refresh();
        $this->assertSame('fixed@example.com', $booking->guest_email);
        $this->assertSame('Guest called.', $booking->admin_note);
        $this->assertSame('10000.00', number_format((float) $booking->total_amount, 2, '.', ''));
        $this->assertSame('confirmed', $booking->status);
    }
}
