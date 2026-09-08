<?php

namespace Tests\Feature;

use App\Mail\ClientInviteMail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\Feature\Concerns\CreatesTestData;
use Tests\TestCase;

class ClientInviteTest extends TestCase
{
    use RefreshDatabase, CreatesTestData;

    protected function capturedInviteUrl(User $client): string
    {
        $url = null;
        Mail::assertSent(ClientInviteMail::class, function ($mail) use (&$url, $client) {
            if (!$mail->hasTo($client->email)) {
                return false;
            }
            $url = $mail->url;
            return true;
        });

        return $url;
    }

    public function test_contractor_can_invite_a_client(): void
    {
        Mail::fake();
        $contractor = $this->makeContractor();

        $response = $this->actingAs($contractor)->post('/clients', [
            'first_name' => 'Ben', 'last_name' => 'Ortiz',
            'email' => 'ben@example.com', 'phone' => '555-3333', 'address' => '2 Oak Ave',
        ]);

        $response->assertRedirect(route('clients.index'));
        $this->assertDatabaseHas('users', [
            'email' => 'ben@example.com', 'type' => 'client',
            'created_by_contractor_id' => $contractor->id,
        ]);
        $client = User::where('email', 'ben@example.com')->first();
        $this->assertFalse($client->hasActivatedAccount());
        Mail::assertSent(ClientInviteMail::class);
    }

    public function test_client_can_use_invite_link_to_activate_and_log_in(): void
    {
        Mail::fake();
        $contractor = $this->makeContractor();
        $this->actingAs($contractor)->post('/clients', [
            'first_name' => 'Ivy', 'last_name' => 'Nguyen',
            'email' => 'invitee@example.com', 'phone' => '555-4444', 'address' => '9 Elm St',
        ]);
        $client = User::where('email', 'invitee@example.com')->first();
        $url = $this->capturedInviteUrl($client);
        $this->post('/logout'); // the client opens this link on their own device, not the contractor's

        $confirmResponse = $this->get($url);
        $confirmResponse->assertOk();
        $this->assertGuest();

        $response = $this->post($url);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($client);
        $this->assertTrue($client->fresh()->hasActivatedAccount());
    }

    public function test_an_invalid_invite_link_is_rejected(): void
    {
        $response = $this->post('/login/not-a-real-invite-token');

        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_resend_invite_fails_once_client_has_activated(): void
    {
        $contractor = $this->makeContractor();
        $client = $this->makeClient($contractor, [], activated: true);

        $response = $this->actingAs($contractor)->post("/clients/{$client->id}/resend-invite");

        $response->assertStatus(400);
    }

    public function test_resend_invite_invalidates_the_earlier_link(): void
    {
        Mail::fake();
        $contractor = $this->makeContractor();
        $this->actingAs($contractor)->post('/clients', [
            'first_name' => 'Lee', 'last_name' => 'Park',
            'email' => 'lee@example.com', 'phone' => '555-5555', 'address' => '5 Pine St',
        ]);
        $lee = User::where('email', 'lee@example.com')->first();
        $firstUrl = $this->capturedInviteUrl($lee);

        $this->actingAs($contractor)->post("/clients/{$lee->id}/resend-invite");
        $this->post('/logout');

        $response = $this->post($firstUrl);
        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }

    // The public demo lets a stranger invite a "client" with any email they
    // type in — we must never actually email a real stranger from the demo.
    public function test_demo_contractor_invite_does_not_send_a_real_email(): void
    {
        Mail::fake();
        $demo = User::factory()->create(['is_demo' => true]);

        $response = $this->actingAs($demo)->post('/clients', [
            'first_name' => 'Some', 'last_name' => 'Stranger',
            'email' => 'a-real-persons-inbox@example.com', 'phone' => '555-9999', 'address' => '1 Real St',
        ]);

        $response->assertRedirect(route('clients.index'));
        $response->assertSessionHas('demo_invite_url');
        Mail::assertNothingSent();
    }
}
