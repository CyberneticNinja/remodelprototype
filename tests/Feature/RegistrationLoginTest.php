<?php

namespace Tests\Feature;

use App\Mail\LoginLinkMail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Tests\Feature\Concerns\CreatesTestData;
use Tests\TestCase;

class RegistrationLoginTest extends TestCase
{
    use RefreshDatabase, CreatesTestData;

    protected function setUp(): void
    {
        parent::setUp();
        RateLimiter::clear('login-link:c@example.com');
        RateLimiter::clear('login-link:mike@example.com');
    }

    // Capture the login URL a mailable was sent with, without knowing its internals.
    protected function capturedLoginUrl(string $mailableClass, string $to): string
    {
        $url = null;
        Mail::assertSent($mailableClass, function ($mail) use (&$url, $to) {
            if (!$mail->hasTo($to)) {
                return false;
            }
            $url = $mail->url;
            return true;
        });

        return $url;
    }

    public function test_registering_does_not_log_the_contractor_in_immediately(): void
    {
        Mail::fake();

        $response = $this->post('/register', [
            'first_name' => 'Mike', 'last_name' => 'Torres',
            'email' => 'mike@example.com', 'phone' => '555-0100',
            'company_name' => 'Torres Co', 'company_address' => '1 Main St',
            'company_phone' => '555-0101',
        ]);

        $response->assertRedirect(route('login'));
        $this->assertGuest();
        $this->assertDatabaseHas('users', ['email' => 'mike@example.com', 'type' => 'contractor']);
        Mail::assertSent(LoginLinkMail::class);
    }

    public function test_requesting_a_login_link_for_a_known_email_sends_one(): void
    {
        Mail::fake();
        $this->makeContractor(['email' => 'c@example.com']);

        $response = $this->post('/login', ['email' => 'c@example.com']);

        $response->assertRedirect(route('login'));
        Mail::assertSent(LoginLinkMail::class, fn ($mail) => $mail->hasTo('c@example.com'));
    }

    public function test_requesting_a_login_link_for_an_unknown_email_gives_the_same_response(): void
    {
        Mail::fake();

        $response = $this->post('/login', ['email' => 'nobody@example.com']);

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('status');
        Mail::assertNothingSent();
    }

    public function test_clicking_a_login_link_shows_a_confirm_page_without_logging_in(): void
    {
        Mail::fake();
        $contractor = $this->makeContractor(['email' => 'c@example.com']);
        $this->post('/login', ['email' => 'c@example.com']);
        $url = $this->capturedLoginUrl(LoginLinkMail::class, 'c@example.com');

        $response = $this->get($url);

        $response->assertOk();
        $this->assertGuest();
    }

    public function test_confirming_a_login_link_logs_the_user_in(): void
    {
        Mail::fake();
        $contractor = $this->makeContractor(['email' => 'c@example.com']);
        $this->post('/login', ['email' => 'c@example.com']);
        $url = $this->capturedLoginUrl(LoginLinkMail::class, 'c@example.com');

        $response = $this->post($url);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($contractor);
    }

    public function test_a_login_link_cannot_be_used_twice(): void
    {
        Mail::fake();
        $contractor = $this->makeContractor(['email' => 'c@example.com']);
        $this->post('/login', ['email' => 'c@example.com']);
        $url = $this->capturedLoginUrl(LoginLinkMail::class, 'c@example.com');

        $this->post($url);
        $this->post('/logout');
        $response = $this->post($url);

        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_an_expired_login_link_is_rejected(): void
    {
        Mail::fake();
        $contractor = $this->makeContractor(['email' => 'c@example.com']);
        $this->post('/login', ['email' => 'c@example.com']);
        $url = $this->capturedLoginUrl(LoginLinkMail::class, 'c@example.com');

        $this->travel(16)->minutes();
        $response = $this->post($url);

        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_an_invalid_login_link_token_is_rejected(): void
    {
        $response = $this->post('/login/not-a-real-token-at-all');

        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_requesting_a_new_login_link_invalidates_the_previous_one(): void
    {
        Mail::fake();
        $contractor = $this->makeContractor(['email' => 'c@example.com']);
        $this->post('/login', ['email' => 'c@example.com']);
        $firstUrl = $this->capturedLoginUrl(LoginLinkMail::class, 'c@example.com');

        $this->post('/login', ['email' => 'c@example.com']);

        $response = $this->post($firstUrl);
        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_login_link_requests_are_rate_limited(): void
    {
        Mail::fake();
        $this->makeContractor(['email' => 'c@example.com']);

        for ($i = 0; $i < 6; $i++) {
            $this->post('/login', ['email' => 'c@example.com']);
        }
        $response = $this->post('/login', ['email' => 'c@example.com']);

        $response->assertStatus(429);
    }
}
