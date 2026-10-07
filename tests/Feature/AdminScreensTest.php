<?php

namespace Tests\Feature;

use App\Filament\Resources\Enquiries\Pages\ListEnquiries;
use App\Filament\Resources\Hotels\Pages\EditHotel;
use App\Models\Admin;
use App\Models\Booking;
use App\Models\Destination;
use App\Models\Enquiry;
use App\Models\Hotel;
use App\Models\Review;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminScreensTest extends TestCase
{
    use RefreshDatabase;

    protected function admin(string $role = 'Super Admin'): Admin
    {
        return Admin::create(['name' => $role.' person', 'email' => str_replace(' ', '', strtolower($role)).'@example.com', 'password' => 'secret-pass', 'role' => $role, 'status' => 'Active']);
    }

    protected function hotel(): Hotel
    {
        $destination = Destination::create(['name' => 'Goa', 'slug' => 'goa', 'country' => 'India']);

        return Hotel::create([
            'destination_id' => $destination->id, 'title' => 'Sea View', 'slug' => 'sea-view-1', 'description' => 'x',
            'category' => 'city_luxury', 'address' => 'Goa', 'star_rating' => 4, 'price_from' => 0,
            'source' => 'tripjack', 'tripjack_hotel_id' => '100001', 'is_active' => true,
        ]);
    }

    public function test_every_menu_screen_opens_for_super_admin(): void
    {
        $hotel = $this->hotel();
        Review::create(['vertical' => 'hotel', 'reference_id' => $hotel->id, 'author_name' => 'Asha', 'rating' => 5, 'body' => 'Lovely', 'is_published' => false]);
        $enquiry = Enquiry::create(['vertical' => 'hotel', 'name' => 'Ravi', 'phone' => '9876543210', 'email' => 'r@example.com', 'pax_adults' => 2, 'pax_children' => 0, 'status' => 'new', 'source' => 'web']);

        $this->actingAs($this->admin(), 'admin');

        foreach ([
            '/tyt-console', '/tyt-console/enquiries', '/tyt-console/enquiries/create',
            '/tyt-console/itinerary-downloads', '/tyt-console/bookings', '/tyt-console/flight-bookings',
            '/tyt-console/tripjack-booking-reconciliation', '/tyt-console/hotels', '/tyt-console/amenities',
            '/tyt-console/hotel-listing-settings', '/tyt-console/flight-settings', '/tyt-console/packages',
            '/tyt-console/offers', '/tyt-console/cruises', '/tyt-console/cruise-page-settings', '/tyt-console/destinations',
            '/tyt-console/reviews', '/tyt-console/reviews/create', '/tyt-console/blog-posts', '/tyt-console/blog-categories',
            '/tyt-console/featured-blog-destinations', '/tyt-console/users', '/tyt-console/admins', '/tyt-console/trip-jack-notifications',
        ] as $url) {
            $status = $this->get($url)->status();
            $this->assertSame(200, $status, "$url returned $status");
        }

        $this->get('/tyt-console/reviews')->assertSee('Hotel: Sea View')->assertSee('★★★★★');
        $this->get('/tyt-console')->assertSee('Bookings &amp; Leads', false)->assertSee('Check Bookings with TripJack')->assertDontSee('System Notification');
    }

    public function test_raw_settings_screen_is_switched_off(): void
    {
        $this->actingAs($this->admin(), 'admin');

        $this->assertContains($this->get('/tyt-console/settings')->status(), [403, 404]);
    }

    public function test_roles_limit_content_screens(): void
    {
        $this->actingAs($this->admin('Analyst'), 'admin');
        $this->get('/tyt-console/destinations')->assertOk();
        $this->get('/tyt-console/destinations/create')->assertForbidden();
        $this->get('/tyt-console/blog-posts')->assertOk();
        $this->get('/tyt-console/blog-posts/create')->assertForbidden();
        $this->get('/tyt-console/tripjack-booking-reconciliation')->assertForbidden();
    }

    public function test_content_role_runs_the_travel_journal_only(): void
    {
        $this->actingAs($this->admin('Content'), 'admin');
        $this->get('/tyt-console/blog-posts/create')->assertOk();
        $this->get('/tyt-console/itinerary-downloads')->assertForbidden();
    }

    public function test_a_booked_hotel_cannot_be_deleted(): void
    {
        $hotel = $this->hotel();
        Booking::create([
            'reference' => 'TYT-H1', 'user_id' => User::factory()->create()->id, 'guest_email' => 'g@example.com', 'guest_phone' => '9876543210',
            'lead_guest_name' => 'Ravi', 'vertical' => 'hotel', 'hotel_id' => $hotel->id, 'pax_adults' => 2, 'pax_children' => 0,
            'base_amount' => 9000, 'tax_amount' => 0, 'total_amount' => 10000, 'currency' => 'INR', 'status' => 'confirmed',
        ]);
        $this->actingAs($this->admin(), 'admin');

        Livewire::test(EditHotel::class, ['record' => $hotel->id])
            ->callAction(DeleteAction::class)
            ->assertNotified('This hotel can\'t be deleted');

        $this->assertModelExists($hotel);
    }

    public function test_closing_an_enquiry_records_the_admin_who_closed_it(): void
    {
        $admin = $this->admin();
        $enquiry = Enquiry::create(['vertical' => 'hotel', 'name' => 'Ravi', 'phone' => '9876543210', 'email' => 'r@example.com', 'pax_adults' => 2, 'pax_children' => 0, 'status' => 'new', 'source' => 'web']);
        $this->actingAs($admin, 'admin');

        Livewire::test(ListEnquiries::class)
            ->callTableAction('resolve', $enquiry, ['admin_notes' => 'Booked 3 nights.']);

        $enquiry->refresh();
        $this->assertSame('closed', $enquiry->status);
        $this->assertSame($admin->id, $enquiry->assigned_agent_id);
    }
}
