<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Booking;
use App\Models\Destination;
use App\Models\Enquiry;
use App\Models\Hotel;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function admin(string $role = 'Super Admin'): Admin
    {
        return Admin::create(['name' => 'Priya Shah', 'email' => uniqid('a').'@example.com', 'password' => 'secret-pass', 'role' => $role, 'status' => 'Active']);
    }

    protected function seedActivity(): void
    {
        $destination = Destination::create(['name' => 'Goa', 'slug' => 'goa', 'country' => 'India']);
        $hotel = Hotel::create([
            'destination_id' => $destination->id, 'title' => 'Sea View Resort', 'slug' => 'sea-view-1', 'description' => 'x',
            'category' => 'city_luxury', 'address' => 'Goa', 'star_rating' => 4, 'price_from' => 0,
            'source' => 'tripjack', 'tripjack_hotel_id' => '100001', 'is_active' => true,
        ]);
        $base = ['user_id' => User::factory()->create()->id, 'guest_email' => 'g@example.com', 'guest_phone' => '9876543210',
            'pax_adults' => 2, 'pax_children' => 0, 'base_amount' => 9000, 'tax_amount' => 0, 'currency' => 'INR', 'status' => 'confirmed'];

        Booking::create($base + ['reference' => 'TYT-H1', 'lead_guest_name' => 'Ravi Kumar', 'vertical' => 'hotel', 'hotel_id' => $hotel->id,
            'check_in' => today()->addDays(2), 'check_out' => today()->addDays(5), 'total_amount' => 12000, 'margin_amount' => 1000]);
        Booking::create($base + ['reference' => 'TYT-F1', 'lead_guest_name' => 'Asha Rao', 'vertical' => 'flight', 'flight_route' => 'BOM-DEL',
            'flight_departure_date' => today()->addDay(), 'total_amount' => 6000, 'margin_amount' => 500, 'tripjack_booking_id' => 'TJS1']);
        Booking::create(['reference' => 'TYT-H2', 'lead_guest_name' => 'Stuck Guest', 'vertical' => 'hotel', 'hotel_id' => $hotel->id,
            'status' => 'failed_needs_review', 'total_amount' => 5000] + $base);

        Enquiry::factory()->count(2)->create(['status' => 'new']);
        Setting::setJson('tripjack_alert.last_balance', ['amount' => 1500, 'checked_at' => now()->toIso8601String()]);
    }

    public function test_home_screen_shows_greeting_todo_numbers_and_upcoming_trips(): void
    {
        Http::fake();
        $this->seedActivity();
        $this->actingAs($this->admin(), 'admin');

        $this->get('/tyt-console')
            ->assertOk()
            ->assertSee('Priya')
            ->assertSee('Needs your attention')
            ->assertSee('New enquiries waiting for a call')
            ->assertSee('Hotel booking needs a look')
            ->assertSee('TripJack wallet is running low')
            ->assertSee('This month at a glance')
            ->assertSee('₹18,000')        // sales: 12,000 hotel + 6,000 flight
            ->assertSee('₹1,500')         // earnings
            ->assertSee('Coming up in the next 7 days')
            ->assertSee('Ravi Kumar')
            ->assertSee('Sea View Resort')
            ->assertSee('BOM → DEL')
            ->assertSee('Hotel sales this month')
            ->assertSee('₹12,000')
            ->assertSee('Flight sales this month')
            ->assertSee('Flights in the next 7 days');

        Http::assertNothingSent();
    }

    public function test_all_caught_up_when_nothing_to_do(): void
    {
        $this->actingAs($this->admin(), 'admin');

        $this->get('/tyt-console')->assertOk()->assertSee('All caught up')->assertSee('nothing needs your attention');
    }

    public function test_money_is_hidden_from_content_team(): void
    {
        $this->seedActivity();
        $this->actingAs($this->admin('Content'), 'admin');

        $this->get('/tyt-console')
            ->assertOk()
            ->assertDontSee('This month at a glance')
            ->assertDontSee('Sales over the last 6 months');
    }
}
