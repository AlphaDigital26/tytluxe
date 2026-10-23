<?php

namespace Tests\Feature\Auth;

use App\Mail\OtpVerificationMail;
use App\Models\EmailOtp;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

/**
 * Security acceptance test of login and registration (21 Oct 2026):
 * SQL injection through every field, account takeover paths, enumeration.
 * Rate limits are switched off so every payload actually reaches the code.
 */
class AuthSecurityUatTest extends TestCase
{
    use RefreshDatabase;

    private const PAYLOADS = [
        "' OR '1'='1", "' OR 1=1-- -", "admin'-- -", '" OR ""="', "') OR ('1'='1",
        "' UNION SELECT 1,2,3-- -", "1' AND SLEEP(5)-- -", "'; DROP TABLE users;-- -",
        "guest@tyt.test' OR '1'='1", "guest@tyt.test'-- -", '%27%20OR%201%3D1', "\\' OR 1=1 #",
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware([ThrottleRequests::class, \App\Http\Middleware\ThrottlePerRoute::class]);
        Mail::fake();
        User::factory()->create(['email' => 'guest@tyt.test', 'password' => 'Right@12345']);
    }

    private function assertUsersIntact(): void
    {
        $this->assertSame(1, User::count());
        $this->assertGuest();
    }

    public function test_login_resists_sql_injection_in_email_and_password(): void
    {
        foreach (self::PAYLOADS as $p) {
            RateLimiter::clear('');
            $this->post('/login', ['email' => $p, 'password' => $p])->assertStatus(302);
            $this->post('/login', ['email' => 'guest@tyt.test', 'password' => $p])->assertStatus(302);
            $this->assertUsersIntact();
            RateLimiter::clear('guest@tyt.test|127.0.0.1');
        }
    }

    public function test_register_resists_sql_injection_and_stores_names_literally(): void
    {
        foreach (self::PAYLOADS as $i => $p) {
            $this->post('/register', ['name' => $p, 'email' => $p, 'password' => $p, 'password_confirmation' => $p])
                ->assertStatus(302)->assertSessionHasErrors('email');
            $this->assertUsersIntact();

            // A hostile name with a valid email goes through to OTP and is kept as plain text.
            RateLimiter::clear('otp-ip:127.0.0.1');
            $this->post('/register', ['name' => $p, 'email' => "new{$i}@tyt.test", 'password' => 'Strong@123', 'password_confirmation' => 'Strong@123'])
                ->assertRedirect(route('otp.verify'));
            $this->assertSame($p, session('pending_registration.name'));
        }
        $this->assertSame(1, User::count());
    }

    public function test_otp_cannot_be_bypassed_with_injection(): void
    {
        $this->post('/register', ['name' => 'A', 'email' => 'new@tyt.test', 'password' => 'Strong@123', 'password_confirmation' => 'Strong@123']);
        foreach (["' OR '1'='1", '1 OR 1', '000000 OR 1=1', "12345'"] as $p) {
            $this->post('/verify-otp', ['otp' => $p])->assertSessionHasErrors('otp');
        }
        $this->assertSame(1, User::count());
        $this->assertNotNull(EmailOtp::where('email', 'new@tyt.test')->first());
    }

    public function test_otp_wrong_guesses_lock_after_five(): void
    {
        $this->post('/register', ['name' => 'A', 'email' => 'new@tyt.test', 'password' => 'Strong@123', 'password_confirmation' => 'Strong@123']);
        $code = null;
        Mail::assertSent(OtpVerificationMail::class, function ($m) use (&$code) { $code = $m->otp; return true; });

        $wrong = $code === '111111' ? '222222' : '111111';
        for ($i = 0; $i < 5; $i++) {
            $this->post('/verify-otp', ['otp' => $wrong]);
        }
        $this->post('/verify-otp', ['otp' => $code])->assertRedirect(route('register'));
        $this->assertSame(1, User::count());
    }

    public function test_forgot_and_reset_password_resist_sql_injection(): void
    {
        foreach (self::PAYLOADS as $p) {
            $this->post('/forgot-password', ['email' => $p])->assertStatus(302);
            $this->post('/reset-password', ['token' => $p, 'email' => $p, 'password' => $p, 'password_confirmation' => $p])->assertStatus(302);
            $this->post('/reset-password', ['token' => $p, 'email' => 'guest@tyt.test', 'password' => 'New@123456', 'password_confirmation' => 'New@123456'])
                ->assertSessionHasErrors('email');
        }
        $this->assertUsersIntact();
        $this->post('/login', ['email' => 'guest@tyt.test', 'password' => 'Right@12345']);
        $this->assertAuthenticated();
    }

    public function test_array_and_oversized_inputs_do_not_crash(): void
    {
        $this->post('/login', ['email' => ['a' => 'b'], 'password' => ['x']])->assertStatus(302);
        $this->post('/register', ['name' => str_repeat('A', 5000), 'email' => str_repeat('a', 300).'@x.com', 'password' => 'x', 'password_confirmation' => 'x'])->assertStatus(302);
        $this->post('/forgot-password', ['email' => ['x']])->assertStatus(302);
        $this->post('/verify-otp', ['otp' => ['1']])->assertStatus(302);
        $this->assertUsersIntact();
    }

    public function test_forgot_password_reply_is_the_same_for_unknown_emails(): void
    {
        $known = $this->post('/forgot-password', ['email' => 'guest@tyt.test'])->getSession()->get('status');
        $unknown = $this->post('/forgot-password', ['email' => 'nobody@tyt.test'])->getSession()->get('status');
        $this->assertSame($known, $unknown);
    }

    public function test_suspended_user_cannot_log_in(): void
    {
        User::first()->update(['status' => 'Suspended']);
        $this->post('/login', ['email' => 'guest@tyt.test', 'password' => 'Right@12345']);
        $this->assertGuest();
    }

    public function test_weak_passwords_are_refused_at_sign_up(): void
    {
        foreach (['12345678', 'password', 'Password1', 'abcdefg!'] as $weak) {
            $this->post('/register', ['name' => 'A', 'email' => 'new@tyt.test', 'password' => $weak, 'password_confirmation' => $weak])
                ->assertSessionHasErrors('password');
        }
        Mail::assertNothingSent();
    }

    public function test_google_login_failure_hides_server_details_and_new_google_users_are_verified(): void
    {
        $provider = \Mockery::mock(\Laravel\Socialite\Contracts\Provider::class);
        $provider->shouldReceive('user')->once()->andThrow(new \RuntimeException('cURL error 28: secret-internal-host'));
        \Laravel\Socialite\Facades\Socialite::shouldReceive('driver')->with('google')->andReturn($provider);
        $this->get('/auth/google/callback')->assertRedirect(route('login'));
        $this->assertStringNotContainsString('secret-internal-host', session('errors')->first('email'));

        $google = \Mockery::mock(\Laravel\Socialite\Two\User::class);
        $google->shouldReceive('getEmail')->andReturn('New.Person@Gmail.com');
        $google->shouldReceive('getName')->andReturn('New Person');
        $google->shouldReceive('getId')->andReturn('g-1');
        $provider->shouldReceive('user')->andReturn($google);
        $this->get('/auth/google/callback');
        $this->assertNotNull(User::where('email', 'new.person@gmail.com')->value('email_verified_at'));
    }

    public function test_user_cannot_set_own_status_or_admin_fields_while_registering(): void
    {
        $this->post('/register', ['name' => 'A', 'email' => 'new@tyt.test', 'password' => 'Strong@123', 'password_confirmation' => 'Strong@123',
            'status' => 'Active', 'role' => 'admin', 'is_admin' => 1, 'email_verified_at' => now()]);
        $this->assertSame(['name', 'email', 'password', 'subscribe_newsletter', 'existing'], array_keys(session('pending_registration')));
    }

    public function test_sign_up_does_not_reveal_which_emails_have_an_account(): void
    {
        $form = fn ($email) => ['name' => 'A', 'email' => $email, 'password' => 'Strong@123', 'password_confirmation' => 'Strong@123'];

        $newInfo = $this->post('/register', $form('new@tyt.test'))->getSession()->get('info');
        $this->flushSession();
        $taken = $this->post('/register', $form('Guest@TYT.test'));

        $taken->assertRedirect(route('otp.verify'))->assertSessionHasNoErrors();
        $this->assertSame($newInfo, str_replace('guest@tyt.test', 'new@tyt.test', $taken->getSession()->get('info')));

        // The owner is told by email; no usable code goes out.
        Mail::assertSent(OtpVerificationMail::class, fn ($m) => $m->hasTo('guest@tyt.test') && $m->purpose === 'account_exists' && $m->otp === '');

        // Even the stored code (never sent) cannot create a second account or log in.
        $stored = EmailOtp::where('email', 'guest@tyt.test')->first();
        $this->assertNotNull($stored);
        $this->post('/verify-otp', ['otp' => '123456'])->assertSessionHasErrors('otp');
        $this->assertSame(1, User::count());
        $this->assertGuest();
    }

    public function test_pages_send_a_content_security_policy_but_the_admin_console_does_not(): void
    {
        $csp = $this->get('/login')->headers->get('Content-Security-Policy');
        $this->assertStringContainsString("object-src 'none'", $csp);
        $this->assertStringContainsString("frame-ancestors 'self'", $csp);
        $this->assertStringContainsString('https://*.razorpay.com', $csp);

        $this->assertNull($this->get('/tyt-console/login')->headers->get('Content-Security-Policy'));
    }
}
