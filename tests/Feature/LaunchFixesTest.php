<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\User;
use App\Rules\BookableEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

/** Fixes from the guest-side acceptance test (15 Oct 2026). */
class LaunchFixesTest extends TestCase
{
    use RefreshDatabase;

    public function test_booking_email_rule_refuses_addresses_suppliers_reject(): void
    {
        $ok = fn (string $email) => Validator::make(['e' => $email], ['e' => [new BookableEmail]])->passes();

        foreach (['name@gmail.com', 'ravi.k@company.co.in', 'o.haddad@outlook.ae'] as $email) {
            $this->assertTrue($ok($email), $email);
        }
        foreach (['x@abc.test', 'a@b', 'a@localhost', 'user@example.com', 'u@mail.example', 'bad@@x.com', 't@site.c', 'me@home.local'] as $email) {
            $this->assertFalse($ok($email), $email);
        }
    }

    public function test_hotel_gst_slab_is_per_room_per_night(): void
    {
        $price = fn (float $total, int $roomNights) => \App\Services\HotelPricingService::price($total, $roomNights)['gst_slab'];

        $this->assertSame(6, \App\Services\HotelPricingService::roomNights('2026-11-01', '2026-11-04', 2));
        $this->assertSame(1, \App\Services\HotelPricingService::roomNights(null, null, 0));

        $this->assertSame(0.05, $price(9000, 3));   // 3 nights × ₹3,000 → 5%
        $this->assertSame(0.18, $price(9000, 1));   // 1 night at ₹9,000 → 18%
        $this->assertSame(0.18, $price(16000, 2));  // 2 rooms × ₹8,000 → 18%
        $this->assertSame(0.05, $price(14999, 2));  // ₹7,499.50 per room-night → 5%
        $this->assertSame(0.18, $price(15000, 2));  // exactly ₹7,500 → 18%
    }

    public function test_pages_send_security_headers(): void
    {
        $this->get('/')->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    }

    public function test_forgot_password_gives_the_same_answer_for_unknown_emails(): void
    {
        \Illuminate\Support\Facades\Notification::fake();
        $user = User::factory()->create(['email' => 'known@gmail.com']);

        $this->post('/forgot-password', ['email' => 'Known@Gmail.com'])->assertSessionHasNoErrors();
        $known = session('status');
        \Illuminate\Support\Facades\Notification::assertSentTo($user, \Illuminate\Auth\Notifications\ResetPassword::class);

        $this->post('/forgot-password', ['email' => 'nobody@gmail.com'])->assertSessionHasNoErrors();
        $this->assertSame($known, session('status'));
    }

    public function test_unpublished_package_is_hidden_from_guests_but_admins_can_preview(): void
    {
        $p = \App\Models\Package::create(['title' => 'Hidden Trip', 'slug' => 'hidden-trip', 'description' => 'x', 'tour_type' => 'group', 'duration_nights' => 2, 'price_from' => 1000, 'is_active' => false]);
        $this->get('/packages/hidden-trip')->assertNotFound();

        $admin = \App\Models\Admin::create(['name' => 'O', 'email' => 'o@x.com', 'password' => 'secret-pass', 'role' => 'Super Admin', 'status' => 'Active']);
        $this->actingAs($admin, 'admin')->get('/packages/hidden-trip')->assertOk();
    }

    public function test_data_export_names_package_and_flight_bookings(): void
    {
        $user = User::factory()->create();
        $p = \App\Models\Package::create(['title' => 'Spiti Escape', 'slug' => 'spiti', 'description' => 'x', 'tour_type' => 'group', 'duration_nights' => 2, 'price_from' => 1000, 'is_active' => true]);
        Booking::create(['reference' => 'TYTEXP00001', 'user_id' => $user->id, 'vertical' => 'package', 'package_id' => $p->id, 'pax_adults' => 2, 'lead_guest_name' => 'G', 'base_amount' => 1, 'total_amount' => 1, 'status' => 'confirmed']);
        Booking::create(['reference' => 'TYTEXP00002', 'user_id' => $user->id, 'vertical' => 'flight', 'flight_route' => 'DEL-BOM', 'pax_adults' => 1, 'lead_guest_name' => 'G', 'base_amount' => 1, 'total_amount' => 1, 'status' => 'confirmed']);

        $json = json_decode($this->actingAs($user)->get('/profile/export-data')->streamedContent(), true);
        $booked = array_column($json['bookings'], 'booked', 'reference');
        $this->assertSame('Spiti Escape', $booked['TYTEXP00001']);
        $this->assertSame('Flight DEL → BOM', $booked['TYTEXP00002']);
    }

    public function test_package_cancel_request_emails_the_guest(): void
    {
        \Illuminate\Support\Facades\Mail::fake();
        $b = Booking::create(['reference' => 'TYTCAN00001', 'user_id' => User::factory()->create()->id, 'guest_email' => 'g@gmail.com', 'vertical' => 'package', 'pax_adults' => 1, 'lead_guest_name' => 'G', 'base_amount' => 1, 'total_amount' => 1, 'status' => 'confirmed', 'check_in' => today()->addDays(20)->toDateString()]);

        $this->assertTrue(app(\App\Services\PackageBookingService::class)->requestCancellation($b, 'Plans changed'));

        \Illuminate\Support\Facades\Mail::assertQueued(\App\Mail\BookingUpdateMail::class, fn ($m) => $m->kind === \App\Mail\BookingUpdateMail::CANCEL_REQUESTED);
    }

    public function test_reviews_open_after_the_trip_and_once_per_trip(): void
    {
        $user = User::factory()->create();
        $p = \App\Models\Package::create(['title' => 'Spiti', 'slug' => 'spiti-r', 'description' => 'x', 'tour_type' => 'group', 'duration_nights' => 2, 'price_from' => 1000, 'is_active' => true]);
        $b = Booking::create(['reference' => 'TYTREV00001', 'user_id' => $user->id, 'vertical' => 'package', 'package_id' => $p->id, 'pax_adults' => 1, 'lead_guest_name' => 'G', 'base_amount' => 1, 'total_amount' => 1, 'status' => 'confirmed', 'package_balance_amount' => 0,
            'check_in' => today()->addDays(5)->toDateString(), 'check_out' => today()->addDays(8)->toDateString()]);
        $review = ['rating' => 5, 'body' => 'Lovely trip.'];

        $this->actingAs($user)->post(route('package.reviews.store', $p->slug), $review)->assertSessionHas('error');

        $b->update(['check_in' => today()->subDays(8)->toDateString(), 'check_out' => today()->subDays(5)->toDateString()]);
        $this->post(route('package.reviews.store', $p->slug), $review)->assertSessionHas('success');
        $this->post(route('package.reviews.store', $p->slug), $review)->assertSessionHas('error');
        $this->assertSame(1, \App\Models\Review::count());
    }

    public function test_emails_typed_with_capitals_work_for_login(): void
    {
        User::factory()->create(['email' => 'ananya@gmail.com', 'password' => bcrypt('Str0ng!Pass2026')]);

        $this->post('/login', ['email' => 'Ananya@Gmail.com', 'password' => 'Str0ng!Pass2026']);
        $this->assertAuthenticated();
    }

    public function test_profile_and_saved_travellers_refuse_impossible_values(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->patch('/profile', ['name' => 'Z', 'email' => $user->email, 'phone' => 'abc-not-a-phone'])->assertSessionHasErrors('phone');
        $this->patch('/profile', ['name' => 'Z', 'email' => $user->email, 'dob' => now()->addYear()->toDateString()])->assertSessionHasErrors('dob');
        $this->patch('/profile', ['name' => 'Z', 'email' => $user->email, 'phone' => '+91 98765-43210'])->assertSessionHasNoErrors();
        $this->assertSame('9876543210', $user->fresh()->phone);

        $this->post('/profile/traveller', ['first_name' => 'A', 'last_name' => 'K', 'dob' => now()->addMonth()->toDateString()])->assertSessionHasErrors('dob');
        $this->post('/profile/traveller', ['first_name' => 'A', 'last_name' => 'K', 'passport_expiry' => now()->subDay()->toDateString()])->assertSessionHasErrors('passport_expiry');
    }

    public function test_enquiries_need_a_real_phone_and_a_known_type(): void
    {
        $ok = ['vertical' => 'general', 'reference_id' => 0, 'name' => 'Pooja', 'phone' => '+91 98765 43210', 'message' => 'Hi'];
        $this->postJson('/enquiries', $ok)->assertOk();
        $this->postJson('/enquiries', ['phone' => 'call me maybe'] + $ok)->assertStatus(422)->assertJsonValidationErrors('phone');
        $this->postJson('/enquiries', ['vertical' => 'spaceship'] + $ok)->assertStatus(422)->assertJsonValidationErrors('vertical');
    }

    public function test_wishlist_list_is_capped(): void
    {
        $this->postJson('/wishlist/lookup', ['slugs' => array_map(fn ($i) => 's'.$i, range(1, 101))])->assertStatus(422);
        $this->postJson('/wishlist/lookup', ['slugs' => ['a', 'b']])->assertOk();
    }

    public function test_blocked_booking_shows_the_branded_page(): void
    {
        $owner = User::factory()->create();
        Booking::create(['reference' => 'TYTOWN00001', 'user_id' => $owner->id, 'vertical' => 'package', 'pax_adults' => 1, 'lead_guest_name' => 'G', 'base_amount' => 1, 'total_amount' => 1, 'status' => 'confirmed']);

        $this->actingAs(User::factory()->create())->get('/booking/TYTOWN00001')
            ->assertForbidden()->assertSee('belongs to a different account')->assertSee('Go to My Trips');
    }

    public function test_account_with_an_upcoming_trip_cannot_be_deleted(): void
    {
        $user = User::factory()->create(['password' => bcrypt('Secret!2026')]);
        $booking = Booking::create(['reference' => 'TYTDEL00001', 'user_id' => $user->id, 'vertical' => 'package', 'pax_adults' => 1,
            'lead_guest_name' => 'G', 'base_amount' => 1, 'total_amount' => 1, 'status' => 'confirmed', 'check_in' => today()->addDays(10)->toDateString()]);

        $this->actingAs($user)->delete('/profile', ['password' => 'Secret!2026'])
            ->assertSessionHasErrorsIn('userDeletion', 'password');
        $this->assertFalse($user->fresh()->trashed());

        // After the trip, the account can go.
        $booking->update(['check_in' => today()->subDays(3)->toDateString()]);
        $this->actingAs($user)->delete('/profile', ['password' => 'Secret!2026'])->assertRedirect('/');
        $this->assertTrue($user->fresh()->trashed());
    }
}
