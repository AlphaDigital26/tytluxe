<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/profile');

        $response->assertOk();
    }

    public function test_profile_information_can_be_updated(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => $user->email,
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $this->assertSame('Test User', $user->refresh()->name);
    }

    public function test_a_new_email_only_takes_effect_after_the_code_sent_to_it_is_entered(): void
    {
        \Illuminate\Support\Facades\Mail::fake();
        $user = User::factory()->create(['email' => 'old@example.com']);

        $this->actingAs($user)->patch('/profile', ['name' => 'Test User', 'email' => 'new@example.com'])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('profile.email.verify'));

        // Other fields save straight away; the email waits for the code.
        $user->refresh();
        $this->assertSame('Test User', $user->name);
        $this->assertSame('old@example.com', $user->email);
        $this->assertNotNull($user->email_verified_at);
        \Illuminate\Support\Facades\Mail::assertSent(\App\Mail\OtpVerificationMail::class, fn ($m) => $m->hasTo('new@example.com') && $m->purpose === 'email_change');

        $this->get(route('profile.email.verify'))->assertOk()->assertSee('new@example.com');

        // A wrong code changes nothing.
        $code = $this->sentCode('new@example.com');
        $wrong = $code === '000000' ? '111111' : '000000';
        $this->post(route('profile.email.verify.submit'), ['otp' => $wrong])->assertSessionHasErrors('otp');
        $this->assertSame('old@example.com', $user->refresh()->email);

        $this->post(route('profile.email.verify.submit'), ['otp' => $code])->assertRedirect(route('profile.edit'));
        $user->refresh();
        $this->assertSame('new@example.com', $user->email);
        $this->assertNotNull($user->email_verified_at);
        $this->assertNull(session('pending_email_change'));
    }

    public function test_an_expired_email_change_code_does_not_change_the_email(): void
    {
        \Illuminate\Support\Facades\Mail::fake();
        $user = User::factory()->create(['email' => 'old@example.com']);

        $this->actingAs($user)->patch('/profile', ['name' => 'Test User', 'email' => 'new@example.com']);
        $code = $this->sentCode('new@example.com');

        $this->travel(11)->minutes();
        $this->post(route('profile.email.verify.submit'), ['otp' => $code])
            ->assertSessionHasErrors(['otp' => \App\Http\Controllers\Auth\OtpController::CODE_EXPIRED_MESSAGE]);
        $this->assertSame('old@example.com', $user->refresh()->email);
    }

    public function test_email_change_can_be_cancelled_and_a_taken_address_is_refused(): void
    {
        \Illuminate\Support\Facades\Mail::fake();
        $user = User::factory()->create(['email' => 'old@example.com']);

        $this->actingAs($user)->patch('/profile', ['name' => 'Test User', 'email' => 'new@example.com']);
        $this->post(route('profile.email.cancel'))->assertRedirect(route('profile.edit'));
        $this->get(route('profile.email.verify'))->assertRedirect(route('profile.edit'));
        $this->assertSame('old@example.com', $user->refresh()->email);

        // Someone registers the address while the code is pending.
        $this->patch('/profile', ['name' => 'Test User', 'email' => 'wanted@example.com']);
        User::factory()->create(['email' => 'wanted@example.com']);
        $code = $this->sentCode('wanted@example.com');
        $this->post(route('profile.email.verify.submit'), ['otp' => $code])->assertSessionHasErrors('email');
        $this->assertSame('old@example.com', $user->refresh()->email);
    }

    public function test_changing_email_does_not_get_the_account_deleted_by_the_cleanup(): void
    {
        $this->travelTo(now()->subDays(10));
        $user = User::factory()->create();
        $this->travelBack();

        $this->actingAs($user)->patch('/profile', ['name' => 'Test User', 'email' => 'new-address@example.com'])
            ->assertSessionHasNoErrors();

        $this->runScheduledCleanup();

        $this->assertNotNull(User::find($user->id));
    }

    public function test_cleanup_removes_old_unverified_sign_ups_but_never_accounts_with_bookings(): void
    {
        $this->travelTo(now()->subDays(3));
        $abandoned = User::factory()->unverified()->create();
        $customer = User::factory()->unverified()->create();
        $this->travelBack();
        \App\Models\Booking::forceCreate(['reference' => 'TYTCLEAN01', 'user_id' => $customer->id, 'vertical' => 'hotel', 'status' => 'confirmed', 'base_amount' => 1000, 'total_amount' => 1000, 'lead_guest_name' => 'Old Customer', 'guest_email' => 'old@example.com']);

        $this->runScheduledCleanup();

        $this->assertNull(User::find($abandoned->id));
        $this->assertNotNull(User::find($customer->id));
    }

    /** Runs the hourly unverified-account cleanup from routes/console.php. */
    protected function runScheduledCleanup(): void
    {
        $event = collect(app(\Illuminate\Console\Scheduling\Schedule::class)->events())
            ->first(fn ($e) => $e instanceof \Illuminate\Console\Scheduling\CallbackEvent && $e->expression === '0 * * * *' && $e->command === null);
        $this->assertNotNull($event, 'The hourly cleanup is scheduled');
        $event->run($this->app);
    }

    public function test_email_verification_status_is_unchanged_when_the_email_address_is_unchanged(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => $user->email,
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $this->assertNotNull($user->refresh()->email_verified_at);
    }

    public function test_user_can_delete_their_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->delete('/profile', [
                'password' => 'password',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/');

        $this->assertGuest();
        // Accounts are soft-deleted so past bookings keep their customer.
        $this->assertSoftDeleted($user);
    }

    public function test_correct_password_must_be_provided_to_delete_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from('/profile')
            ->delete('/profile', [
                'password' => 'wrong-password',
            ]);

        $response
            ->assertSessionHasErrorsIn('userDeletion', 'password')
            ->assertRedirect('/profile');

        $this->assertNotNull($user->fresh());
    }

    /** The last 6-digit code emailed to $email (the database only keeps a hash). */
    protected function sentCode(string $email): ?string
    {
        $code = null;
        \Illuminate\Support\Facades\Mail::assertSent(\App\Mail\OtpVerificationMail::class, function ($m) use (&$code, $email) {
            if ($m->hasTo($email)) {
                $code = $m->otp;
            }

            return true;
        });

        return $code;
    }
}