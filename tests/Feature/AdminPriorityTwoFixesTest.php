<?php

namespace Tests\Feature;

use App\Filament\Actions\RecordManualRefundAction;
use App\Filament\Resources\Bookings\BookingResource;
use App\Filament\Resources\Enquiries\EnquiryResource;
use App\Filament\Resources\FlightBookings\FlightBookingResource;
use App\Filament\Resources\Users\UserResource;
use App\Models\Admin;
use App\Models\Amenity;
use App\Models\Booking;
use App\Models\Cruise;
use App\Models\Enquiry;
use App\Models\Hotel;
use App\Models\Offer;
use App\Models\Setting;
use App\Models\User;
use App\Support\DashboardData;
use App\Support\FlightBookingStatus;
use App\Support\HotelBookingStatus;
use Filament\Auth\Notifications\ResetPassword;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/** Priority-2 fixes from the admin review (19 Oct 2026). */
class AdminPriorityTwoFixesTest extends TestCase
{
    use RefreshDatabase;

    protected function admin(string $role = 'Super Admin'): Admin
    {
        return Admin::create(['name' => $role, 'email' => uniqid('staff').'@example.com', 'password' => 'secret-pass', 'role' => $role, 'status' => 'Active']);
    }

    protected function booking(array $attributes = []): Booking
    {
        return Booking::create($attributes + [
            'reference' => uniqid('TYT'), 'user_id' => User::factory()->create()->id, 'lead_guest_name' => 'Meera Iyer',
            'guest_email' => 'meera@gmail.com', 'guest_phone' => '9812345670', 'vertical' => 'hotel', 'pax_adults' => 2,
            'check_in' => today()->addDays(10), 'base_amount' => 9000, 'tax_amount' => 0, 'total_amount' => 9500,
            'currency' => 'INR', 'status' => 'confirmed',
        ]);
    }

    public function test_top_search_finds_bookings_customers_and_enquiries_by_name_phone_and_email(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $this->actingAs($this->admin(), 'admin');
        $hotel = $this->booking();
        $flight = $this->booking(['vertical' => 'flight', 'lead_guest_name' => 'Arjun Mehta', 'guest_phone' => '9900011122',
            'flight_route' => 'DEL-BOM', 'tripjack_flight_pnr' => ['QX7Z2P']]);
        User::factory()->create(['name' => 'Kavya', 'email' => 'kavya.r@gmail.com', 'phone' => '9988776655']);
        Enquiry::factory()->create(['name' => 'Rohan Das', 'phone' => '9123456780']);

        $found = fn (string $resource, string $search) => $resource::getGlobalSearchResults($search)->pluck('url')->all();

        $this->assertSame([BookingResource::getUrl('view', ['record' => $hotel])], $found(BookingResource::class, '9812345670'));
        $this->assertCount(1, $found(BookingResource::class, 'meera@gmail'));
        $this->assertCount(1, $found(BookingResource::class, 'Meera'));
        $this->assertCount(0, $found(BookingResource::class, 'Arjun'), 'flights are not listed as hotel bookings');
        $this->assertSame([FlightBookingResource::getUrl('view', ['record' => $flight])], $found(FlightBookingResource::class, 'QX7Z2P'));
        $this->assertCount(1, $found(FlightBookingResource::class, '9900011122'));
        $this->assertCount(1, $found(UserResource::class, '9988776655'));
        $this->assertCount(1, $found(EnquiryResource::class, '9123456780'));
    }

    public function test_dashboard_sales_include_packages(): void
    {
        Http::fake();
        $this->actingAs($this->admin(), 'admin');
        $this->booking(['total_amount' => 10000, 'margin_amount' => 800]);
        $this->booking(['vertical' => 'package', 'total_amount' => 25000]);

        $this->get('/tyt-console')->assertOk()
            ->assertSee('₹35,000')
            ->assertSee('packages ₹25,000')
            ->assertSee('Your earnings (hotels &amp; flights)', false);
    }

    public function test_manual_refund_shows_only_where_a_refund_can_be_owed(): void
    {
        $confirmed = $this->booking();
        $flagged = $this->booking(['manual_refund_due_at' => now()]);
        $cancelled = $this->booking(['status' => 'cancelled']);

        foreach ([$confirmed, $flagged, $cancelled] as $b) {
            $b->payments()->create(['razorpay_order_id' => uniqid('order_'), 'razorpay_payment_id' => uniqid('pay_'), 'amount' => 9500, 'currency' => 'INR', 'status' => 'captured']);
        }
        $this->actingAs($this->admin('Finance'), 'admin');
        $visible = fn (Booking $b) => RecordManualRefundAction::make()->record($b->fresh())->isVisible();

        $this->assertFalse($visible($confirmed), 'not on a normal confirmed booking');
        $this->assertTrue($visible($flagged));
        $this->assertTrue($visible($cancelled), 'a cancelled booking can still get money back');
    }

    public function test_guests_who_left_the_payment_page_are_labelled(): void
    {
        $now = $this->booking(['status' => 'pending_payment']);
        $old = $this->booking(['status' => 'pending_payment']);
        $old->forceFill(['created_at' => now()->subHours(3)])->save();

        $this->assertSame('Waiting for payment', HotelBookingStatus::for($now)['label']);
        $this->assertSame('Left without paying', HotelBookingStatus::for($old->fresh())['label']);
        $this->assertSame('Left without paying', FlightBookingStatus::for($old->fresh()->forceFill(['vertical' => 'flight']))['label']);
    }

    public function test_content_team_can_add_offers_and_cruises_and_is_not_asked_to_call_guests(): void
    {
        $content = $this->admin('Content');
        $this->assertTrue($content->can('create', Offer::class));
        $this->assertTrue($content->can('create', Cruise::class));
        $this->assertFalse($this->admin('Analyst')->can('create', Offer::class));

        Enquiry::factory()->create(['status' => 'new']);
        $this->actingAs($content, 'admin');
        $this->assertNull(collect(DashboardData::todo())->firstWhere('icon', '📞'));

        $this->actingAs($this->admin('Support'), 'admin');
        $this->assertNotNull(collect(DashboardData::todo())->firstWhere('icon', '📞'));
    }

    public function test_only_super_admin_operations_and_content_edit_hotels_packages_and_cruises(): void
    {
        $shimla = \App\Models\Destination::create(['name' => 'Shimla', 'slug' => 'shimla-2', 'country' => 'India']);
        $hotel = Hotel::create(['destination_id' => $shimla->id, 'title' => 'Snow Valley', 'slug' => 'snow-valley', 'description' => 'x', 'category' => 'city_luxury', 'address' => 'Shimla', 'star_rating' => 4, 'price_from' => 0, 'is_active' => true]);
        $package = \App\Models\Package::create(['title' => 'Spiti Tour', 'slug' => 'spiti', 'description' => 'x', 'tour_type' => 'group', 'duration_nights' => 6, 'price_from' => 20000, 'is_active' => true]);

        foreach (['Super Admin' => true, 'Operations' => true, 'Content' => true, 'Support' => false, 'Finance' => false, 'Analyst' => false] as $role => $allowed) {
            $staff = $this->admin($role);
            foreach ([[Hotel::class, $hotel], [\App\Models\Package::class, $package], [Cruise::class, new Cruise]] as [$class, $record]) {
                $this->assertSame($allowed, $staff->can('create', $class), "$role create $class");
                $this->assertSame($allowed, $staff->can('update', $record), "$role update $class");
                $this->assertTrue($staff->can('view', $record), "$role can still look at $class");
            }
        }

        $this->actingAs($this->admin('Analyst'), 'admin')->get('/tyt-console/hotels/'.$hotel->id.'/edit')->assertForbidden();
        $this->get('/tyt-console/hotels')->assertOk();
    }

    public function test_staff_can_reset_a_forgotten_password(): void
    {
        Notification::fake();
        $staff = $this->admin('Operations');

        $this->get('/tyt-console/login')->assertOk()->assertSee('Forgot password');
        $this->get('/tyt-console/password-reset/request')->assertOk();

        \Livewire\Livewire::test(\Filament\Auth\Pages\PasswordReset\RequestPasswordReset::class)
            ->set('data.email', $staff->email)
            ->call('request');

        Notification::assertSentTo($staff, ResetPassword::class);
        $this->assertDatabaseHas('admin_password_reset_tokens', ['email' => $staff->email]);
        $this->assertDatabaseMissing('password_reset_tokens', ['email' => $staff->email]);
    }

    public function test_wallet_explains_when_automatic_checks_are_not_running(): void
    {
        $this->actingAs($this->admin(), 'admin');

        $this->assertTrue(DashboardData::walletStatus()['problem']);
        $this->assertStringContainsString('cron', DashboardData::walletStatus()['hint']);
        $this->assertNotNull(collect(DashboardData::todo())->firstWhere('icon', '⚙️'));

        Setting::setJson('tripjack_alert.last_balance', ['amount' => 90000, 'checked_at' => now()->subHours(5)->toIso8601String()]);
        $this->assertStringContainsString('stopped', DashboardData::walletStatus()['hint']);

        Setting::setJson('tripjack_alert.last_balance', ['amount' => 90000, 'checked_at' => now()->toIso8601String()]);
        $this->assertFalse(DashboardData::walletStatus()['problem']);
        $this->assertNull(collect(DashboardData::todo())->firstWhere('icon', '⚙️'));

        $this->actingAs($this->admin('Support'), 'admin');
        Setting::setJson('tripjack_alert.last_balance', []);
        $this->assertNull(collect(DashboardData::todo())->firstWhere('icon', '⚙️'), 'only the Super Admin, who can fix it');
    }

    public function test_hotel_names_are_not_amenities(): void
    {
        $shimla = \App\Models\Destination::create(['name' => 'Shimla', 'slug' => 'shimla', 'country' => 'India']);
        Hotel::create(['destination_id' => $shimla->id, 'title' => 'Shimla Mountain Resort', 'slug' => 'smr', 'description' => 'x', 'category' => 'city_luxury', 'address' => 'Shimla', 'star_rating' => 4, 'price_from' => 0, 'is_active' => true]);
        $junk = Amenity::create(['name' => 'Shimla Mountain Resort', 'type' => 'hotel']);
        $wifi = Amenity::create(['name' => 'Free WiFi', 'type' => 'hotel']);
        Amenity::forgetHotelTitles();

        $this->assertTrue(Amenity::looksLikeHotelName('shimla mountain resort'));
        $this->assertTrue(Amenity::looksLikeHotelName('Larisa Collection'));
        $this->assertTrue(Amenity::looksLikeHotelName('AM Hotel Kollection'));
        $this->assertTrue(Amenity::looksLikeHotelName('Official Hotel'));
        $this->assertTrue(Amenity::looksLikeHotelName('8Fold by Larisa', '8Fold by Larisa'));
        $this->assertFalse(Amenity::looksLikeHotelName('Free WiFi'));
        $this->assertFalse(Amenity::looksLikeHotelName('Hotel Room Service App'));
        $this->assertFalse(Amenity::looksLikeHotelName('Jungle Retreat'));

        $this->artisan('app:clean-amenities')->assertSuccessful();
        $this->assertModelExists($junk); // only listed

        $this->artisan('app:clean-amenities --delete')->assertSuccessful();
        $this->assertModelMissing($junk);
        $this->assertModelExists($wifi);
    }
}
