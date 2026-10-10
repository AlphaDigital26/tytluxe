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

        $otp = EmailOtp::where('email', 'test@example.com')->value('otp');

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

        $wrong = EmailOtp::where('email', 'test@example.com')->value('otp') === '000000' ? '111111' : '000000';

        $this->post(route('otp.verify.submit'), ['otp' => $wrong])->assertSessionHasErrors('otp');

        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['email' => 'test@example.com']);
    }
}
