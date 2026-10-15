<?php

namespace Tests\Feature\Auth;

use App\Mail\OtpVerificationMail;
use App\Models\EmailOtp;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    /**
     * Registration is two steps: the form emails a 6-digit code, and the
     * account is only created (and logged in) once that code is entered.
     */
    public function test_new_users_can_register(): void
    {
        Mail::fake();

        $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertRedirect(route('otp.verify'));

        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['email' => 'test@example.com']);
        Mail::assertSent(OtpVerificationMail::class);

        $otp = $this->sentCode('test@example.com');

        $this->post(route('otp.verify.submit'), ['otp' => $otp])
            ->assertRedirect(route('home'));

        $this->assertAuthenticated();
        $this->assertNotNull(User::where('email', 'test@example.com')->value('email_verified_at'));
    }

    public function test_mail_server_failure_shows_a_friendly_message_instead_of_an_error_page(): void
    {
        Mail::shouldReceive('to')->andThrow(new \Symfony\Component\Mailer\Exception\TransportException('Expected response code "220" but got empty code.'));

        $this->from('/register')->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])
            ->assertRedirect('/register')
            ->assertSessionHasErrors(['email' => \App\Http\Controllers\Auth\OtpController::EMAIL_FAILED_MESSAGE]);

        $this->assertDatabaseMissing('email_otps', ['email' => 'test@example.com']);
        $this->assertGuest();
    }

    public function test_wrong_code_does_not_create_the_account(): void
    {
        Mail::fake();

        $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $wrong = $this->sentCode('test@example.com') === '000000' ? '111111' : '000000';

        $this->post(route('otp.verify.submit'), ['otp' => $wrong])->assertSessionHasErrors('otp');

        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['email' => 'test@example.com']);
    }

    public function test_the_database_never_holds_the_code_itself(): void
    {
        Mail::fake();
        $this->post('/register', ['name' => 'Test User', 'email' => 'test@example.com', 'password' => 'password', 'password_confirmation' => 'password']);

        $stored = EmailOtp::where('email', 'test@example.com')->value('otp');
        $this->assertNotSame($this->sentCode('test@example.com'), $stored);
        $this->assertSame(64, strlen($stored));
    }

    public function test_an_email_gets_at_most_5_codes_an_hour(): void
    {
        Mail::fake();
        $this->post('/register', ['name' => 'Test User', 'email' => 'test@example.com', 'password' => 'password', 'password_confirmation' => 'password']);
        for ($i = 0; $i < 4; $i++) {
            $this->travel(2)->minutes(); // past the per-minute route throttle
            $this->post(route('otp.resend'))->assertSessionHasNoErrors();
        }

        $this->travel(2)->minutes();
        $this->post(route('otp.resend'))->assertSessionHasErrors(['otp' => \App\Http\Controllers\Auth\OtpController::TOO_MANY_CODES_MESSAGE]);
        Mail::assertSentCount(5);

        // An hour later it works again.
        $this->travel(61)->minutes();
        $this->post(route('otp.resend'))->assertSessionHasNoErrors();
    }

    public function test_code_expires_after_10_minutes_and_a_resent_code_works(): void
    {
        Mail::fake();

        $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);
        $otp = $this->sentCode('test@example.com');

        $this->travel(11)->minutes();
        $this->post(route('otp.verify.submit'), ['otp' => $otp])
            ->assertSessionHasErrors(['otp' => \App\Http\Controllers\Auth\OtpController::CODE_EXPIRED_MESSAGE]);
        $this->assertGuest();

        $this->post(route('otp.resend'));
        $fresh = $this->sentCode('test@example.com');
        $this->post(route('otp.verify.submit'), ['otp' => $fresh])->assertRedirect(route('home'));
        $this->assertAuthenticated();
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