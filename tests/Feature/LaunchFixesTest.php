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
