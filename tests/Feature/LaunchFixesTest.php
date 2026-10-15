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
