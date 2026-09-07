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
        $this->assertNull($client->password);
        $this->assertFalse($client->hasActivatedAccount());
        Mail::assertSent(ClientInviteMail::class);
    }

    public function test_client_can_activate_via_signed_link_and_is_logged_in(): void
    {
        $contractor = $this->makeContractor();
        $client = $this->makeClient($contractor, [], activated: false);

        $url = \Illuminate\Support\Facades\URL::temporarySignedRoute(
            'clients.activate', now()->addDays(7), ['user' => $client->id]
        );

        $response = $this->post($url, [
            'password' => 'newpassword123', 'password_confirmation' => 'newpassword123',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($client);
        $this->assertTrue($client->fresh()->hasActivatedAccount());
    }

    public function test_activation_link_without_valid_signature_is_rejected(): void
    {
        $contractor = $this->makeContractor();
        $client = $this->makeClient($contractor, [], activated: false);

        $response = $this->get("/clients/{$client->id}/activate?expires=9999999999&signature=bogus");

        $response->assertForbidden();
    }

    public function test_resend_invite_fails_once_client_has_activated(): void
    {
        $contractor = $this->makeContractor();
        $client = $this->makeClient($contractor, [], activated: true);

        $response = $this->actingAs($contractor)->post("/clients/{$client->id}/resend-invite");

        $response->assertStatus(400);
    }
}
