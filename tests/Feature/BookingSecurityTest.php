<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Destination;
use App\Models\Hotel;
use App\Models\Payment;
use App\Models\User;
use App\Services\Payment\RazorpayService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Doubles\FakeRazorpayService;
use Tests\TestCase;

class BookingSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->app->instance(RazorpayService::class, new FakeRazorpayService());
    }

    protected function makeHotel(string $slug, string $tjHotelId): Hotel
    {
        $destination = Destination::create([
            'name' => 'Dubai', 'slug' => 'dubai-sec-'.$tjHotelId, 'country' => 'UAE',
            'type' => 'city', 'for' => ['hotel'], 'is_active' => true,
        ]);

        return Hotel::create([
            'destination_id' => $destination->id,
            'title' => 'Security Test Hotel', 'slug' => $slug,
            'description' => 'x', 'category' => 'city_luxury', 'address' => 'Dubai',
            'star_rating' => 4, 'price_from' => 0, 'source' => 'tripjack',
            'tripjack_hotel_id' => $tjHotelId, 'is_active' => true,
        ]);
    }

    public function test_guest_details_browsing_stays_public(): void
    {
        Http::fake(['*/hotel/pricing' => Http::response(['options' => [], 'status' => ['success' => true]], 200)]);

        $hotel = $this->makeHotel('public-browse-hotel', '777000001');

        $this->get("/hotels/{$hotel->slug}?check_in=2026-10-20&check_out=2026-10-22&adults=2&rooms=1")
            ->assertStatus(200);
    }

    public function test_selecting_a_room_while_logged_out_redirects_to_login(): void
    {
        $hotel = $this->makeHotel('login-required-hotel', '777000002');

        $this->post("/hotels/{$hotel->slug}/review", [
            'option_id' => 'opt-x', 'check_in' => '2026-10-20', 'check_out' => '2026-10-22',
            'adults' => 2, 'children' => 0, 'rooms' => 1,
        ])->assertRedirect(route('login'));
    }

    public function test_viewing_review_page_while_logged_out_redirects_to_login(): void
    {
        $hotel = $this->makeHotel('login-required-review-hotel', '777000003');

        $this->get("/hotels/{$hotel->slug}/review")->assertRedirect(route('login'));
    }

    public function test_submitting_booking_while_logged_out_redirects_to_login(): void
    {
        $hotel = $this->makeHotel('login-required-book-hotel', '777000004');

        $this->post("/hotels/{$hotel->slug}/book", [
            'lead_name' => 'John Doe', 'lead_email' => 'john@example.com', 'lead_phone' => '9876543210',
        ])->assertRedirect(route('login'));
    }

    public function test_booking_is_linked_to_the_authenticated_user(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $hotel = $this->makeHotel('linked-user-hotel', '777000005');

        session(['tripjack_booking_draft' => [
            'hotel_id' => $hotel->id, 'hid' => '777000005', 'bookingId' => 'TGS-SEC-1',
            'option' => ['optionId' => 'opt-1', 'compliance' => ['panRequired' => false, 'passportRequired' => false], 'pricing' => ['totalPrice' => 1000, 'currency' => 'INR']],
            'correlationId' => 'cid-1', 'check_in' => '2026-10-20', 'check_out' => '2026-10-22',
            'adults' => 1, 'children' => 0, 'rooms' => 1,
        ]]);

        $this->post("/hotels/{$hotel->slug}/book", [
            'lead_name' => 'John Doe', 'lead_email' => 'john@example.com', 'lead_phone' => '9876543210',
            'rooms' => [0 => ['travelers' => [0 => ['title' => 'Mr', 'first_name' => 'John', 'last_name' => 'Doe']]]],
        ]);

        $booking = Booking::firstOrFail();
        $this->assertSame($user->id, $booking->user_id);
    }

    public function test_a_user_cannot_view_another_users_booking_confirmation(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $hotel = $this->makeHotel('confirmation-owner-hotel', '777000006');

        $booking = Booking::create([
            'reference' => 'TYTOWNERTEST', 'user_id' => $owner->id, 'vertical' => 'hotel',
            'hotel_id' => $hotel->id, 'lead_guest_name' => 'Owner', 'base_amount' => 1000,
            'total_amount' => 1000, 'status' => 'pending_payment',
        ]);

        $this->actingAs($intruder);
        $this->get("/booking/{$booking->reference}")->assertForbidden();
    }

    public function test_a_user_cannot_view_another_users_payment_page(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $hotel = $this->makeHotel('payment-owner-hotel', '777000007');

        $booking = Booking::create([
            'reference' => 'TYTOWNERTEST2', 'user_id' => $owner->id, 'vertical' => 'hotel',
            'hotel_id' => $hotel->id, 'lead_guest_name' => 'Owner', 'base_amount' => 1000,
            'total_amount' => 1000, 'status' => 'pending_payment',
        ]);
        Payment::create(['booking_id' => $booking->id, 'razorpay_order_id' => 'order_owner_1', 'amount' => 1000, 'status' => 'created']);

        $this->actingAs($intruder);
        $this->get("/booking/{$booking->reference}/pay")->assertForbidden();
    }

    #[DataProvider('invalidGuestDetailsProvider')]
    public function test_malformed_guest_details_are_rejected(string $field, string $value): void
    {
        $this->actingAs(User::factory()->create());
        $hotel = $this->makeHotel('validation-hotel-'.\Illuminate\Support\Str::slug($field), '777000008'.crc32($field.$value));

        session(['tripjack_booking_draft' => [
            'hotel_id' => $hotel->id, 'hid' => (string) $hotel->tripjack_hotel_id, 'bookingId' => 'TGS-VAL-1',
            'option' => ['optionId' => 'opt-1', 'compliance' => ['panRequired' => true, 'passportRequired' => false], 'pricing' => ['totalPrice' => 1000, 'currency' => 'INR']],
            'correlationId' => 'cid-1', 'check_in' => '2026-10-20', 'check_out' => '2026-10-22',
            'adults' => 1, 'children' => 0, 'rooms' => 1,
        ]]);

        $payload = [
            'lead_name' => 'John Doe', 'lead_email' => 'john@example.com', 'lead_phone' => '9876543210',
            'pan_number' => 'ABCDE1234F',
            'rooms' => [0 => ['travelers' => [0 => ['title' => 'Mr', 'first_name' => 'John', 'last_name' => 'Doe']]]],
        ];
        data_set($payload, $field, $value);

        $this->post("/hotels/{$hotel->slug}/book", $payload)->assertSessionHasErrors();
        $this->assertSame(0, Booking::count());
    }

    public static function invalidGuestDetailsProvider(): array
    {
        return [
            'PAN with wrong format' => ['pan_number', 'NOTAPAN123'],
            'phone with too few digits' => ['lead_phone', '12345'],
            'name containing digits' => ['lead_name', 'John123'],
            'traveler first name containing digits' => ['rooms.0.travelers.0.first_name', 'John1'],
        ];
    }

    public function test_missing_profile_phone_is_filled_from_booking(): void
    {
        $user = User::factory()->create(['phone' => null]);
        $this->actingAs($user);
        $hotel = $this->makeHotel('profile-phone-sync-hotel', '777000099');

        session(['tripjack_booking_draft' => [
            'hotel_id' => $hotel->id, 'hid' => '777000099', 'bookingId' => 'TGS-SYNC-1',
            'option' => ['optionId' => 'opt-1', 'compliance' => ['panRequired' => false, 'passportRequired' => false], 'pricing' => ['totalPrice' => 1000, 'currency' => 'INR']],
            'correlationId' => 'cid-1', 'check_in' => '2026-10-20', 'check_out' => '2026-10-22',
            'adults' => 1, 'children' => 0, 'rooms' => 1,
        ]]);

        $this->post("/hotels/{$hotel->slug}/book", [
            'lead_name' => 'John Doe', 'lead_email' => 'john@example.com', 'lead_phone' => '9876543210',
            'rooms' => [0 => ['travelers' => [0 => ['title' => 'Mr', 'first_name' => 'John', 'last_name' => 'Doe']]]],
        ]);

        $this->assertSame('9876543210', $user->fresh()->phone);
    }

    public function test_profile_phone_already_set_is_never_overwritten(): void
    {
        $user = User::factory()->create(['phone' => '9111111111']);
        $this->actingAs($user);
        $hotel = $this->makeHotel('profile-phone-keep-hotel', '777000098');

        session(['tripjack_booking_draft' => [
            'hotel_id' => $hotel->id, 'hid' => '777000098', 'bookingId' => 'TGS-SYNC-2',
            'option' => ['optionId' => 'opt-1', 'compliance' => ['panRequired' => false, 'passportRequired' => false], 'pricing' => ['totalPrice' => 1000, 'currency' => 'INR']],
            'correlationId' => 'cid-1', 'check_in' => '2026-10-20', 'check_out' => '2026-10-22',
            'adults' => 1, 'children' => 0, 'rooms' => 1,
        ]]);

        $this->post("/hotels/{$hotel->slug}/book", [
            'lead_name' => 'John Doe', 'lead_email' => 'john@example.com', 'lead_phone' => '9222222222',
            'rooms' => [0 => ['travelers' => [0 => ['title' => 'Mr', 'first_name' => 'John', 'last_name' => 'Doe']]]],
        ]);

        $this->assertSame('9111111111', $user->fresh()->phone);
    }
}
