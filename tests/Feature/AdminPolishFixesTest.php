<?php

namespace Tests\Feature;

use App\Filament\Pages\TripJackNotifications;
use App\Filament\Resources\ItineraryDownloads\Pages\ListItineraryDownloads;
use App\Filament\Resources\Offers\Pages\CreateOffer;
use App\Filament\Resources\Users\Pages\CreateUser;
use App\Models\Admin;
use App\Models\Enquiry;
use App\Models\ItineraryDownload;
use App\Models\Package;
use App\Models\User;
use App\Support\FlightAddonNames;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tests\TestCase;

/** Priority-3 polish from the admin review (19 Oct 2026). */
class AdminPolishFixesTest extends TestCase
{
    use RefreshDatabase;

    protected function admin(string $role = 'Super Admin'): Admin
    {
        return Admin::create(['name' => $role, 'email' => uniqid('staff').'@example.com', 'password' => 'secret-pass', 'role' => $role, 'status' => 'Active']);
    }

    public function test_error_messages_name_the_box_as_it_reads(): void
    {
        $this->actingAs($this->admin(), 'admin');

        $errors = Livewire::test(CreateOffer::class)->set('data.title', '')->call('create')->errors();

        $this->assertSame('The "Headline" field is required.', $errors->first('data.title'));
        $this->assertSame('The "Tab on the Offers page" field is required.', $errors->first('data.category_key'));
    }

    public function test_meal_codes_read_as_meals(): void
    {
        $this->assertSame('Seafood meal (SFML)', FlightAddonNames::name('SFML'));
        $this->assertSame('HCCB', FlightAddonNames::name('HCCB'), 'unknown airline code stays as it is');

        $saved = FlightAddonNames::fromReview(['tripInfos' => [['sI' => [['ssrInfo' => [
            'MEAL' => [['code' => 'HCCB', 'desc' => 'Chicken sandwich', 'amount' => 400]],
            'BAGGAGE' => [['code' => 'XB05', 'desc' => '5 Kg', 'amount' => 1800]],
        ]]]]]]);
        $this->assertSame('Chicken sandwich (HCCB)', FlightAddonNames::name('HCCB', $saved));
        $this->assertSame('5 Kg (XB05)', FlightAddonNames::name('XB05', $saved));
    }

    public function test_alerts_sit_with_bookings(): void
    {
        $this->assertSame('Bookings & Leads', TripJackNotifications::getNavigationGroup());
    }

    public function test_itinerary_downloads_show_each_person_once_and_turn_into_an_enquiry(): void
    {
        $this->actingAs($this->admin(), 'admin');
        $package = Package::create(['title' => 'Spiti Tour', 'slug' => 'spiti', 'description' => 'x', 'tour_type' => 'group', 'duration_nights' => 6, 'price_from' => 20000, 'is_active' => true]);
        $first = ItineraryDownload::create(['package_id' => $package->id, 'name' => 'Neha', 'phone' => '9811111111', 'email' => 'neha@gmail.com']);
        $again = ItineraryDownload::create(['package_id' => $package->id, 'name' => 'Neha', 'phone' => '9811111111', 'email' => 'neha@gmail.com']);
        $other = ItineraryDownload::create(['package_id' => $package->id, 'name' => 'Vikram', 'phone' => '9822222222', 'email' => 'vik@gmail.com']);

        Livewire::test(ListItineraryDownloads::class)
            ->assertCanSeeTableRecords([$again, $other])
            ->assertCanNotSeeTableRecords([$first])
            ->assertTableColumnStateSet('downloads_by_person', 2, $again)
            ->callTableAction('makeEnquiry', $again)
            ->assertHasNoTableActionErrors();

        $enquiry = Enquiry::sole();
        $this->assertSame(['package', $package->id, 'Neha', '9811111111', 'new'], [$enquiry->vertical, (int) $enquiry->reference_id, $enquiry->name, $enquiry->phone, $enquiry->status]);
        $this->assertStringContainsString('Spiti Tour', $enquiry->notes);

        Livewire::test(ListItineraryDownloads::class)
            ->assertTableActionHidden('makeEnquiry', $again)
            ->assertTableActionVisible('openEnquiry', $again)
            ->assertTableActionVisible('makeEnquiry', $other);
    }

    public function test_a_customer_added_by_staff_can_get_in_and_is_not_cleaned_up(): void
    {
        Notification::fake();
        $this->actingAs($this->admin(), 'admin');

        Livewire::test(CreateUser::class)
            ->set('data.name', 'Kabir Malhotra')
            ->set('data.email', 'kabir@gmail.com')
            ->set('data.status', 'Active')
            ->call('create')
            ->assertHasNoFormErrors();

        $customer = User::where('email', 'kabir@gmail.com')->sole();
        // Verified, so the hourly clean-up of unverified sign-ups (routes/console.php) leaves it alone.
        $this->assertNotNull($customer->email_verified_at);
        Notification::assertSentTo($customer, ResetPassword::class);
    }
}
