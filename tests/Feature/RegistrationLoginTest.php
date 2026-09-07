<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesTestData;
use Tests\TestCase;

class RegistrationLoginTest extends TestCase
{
    use RefreshDatabase, CreatesTestData;

    public function test_contractor_can_register_and_is_logged_in(): void
    {
        $response = $this->post('/register', [
            'first_name' => 'Mike', 'last_name' => 'Torres',
            'email' => 'mike@example.com', 'password' => 'password123',
            'password_confirmation' => 'password123', 'phone' => '555-0100',
            'company_name' => 'Torres Co', 'company_address' => '1 Main St',
            'company_phone' => '555-0101',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', ['email' => 'mike@example.com', 'type' => 'contractor']);
    }

    public function test_contractor_can_login(): void
    {
        $contractor = $this->makeContractor(['email' => 'c@example.com']);

        $response = $this->post('/login', ['email' => 'c@example.com', 'password' => 'password']);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($contractor);
    }

    public function test_unactivated_client_cannot_login(): void
    {
        $contractor = $this->makeContractor();
        $client = $this->makeClient($contractor, ['email' => 'client@example.com'], activated: false);

        $response = $this->post('/login', ['email' => 'client@example.com', 'password' => 'whatever']);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_activated_client_can_login(): void
    {
        $contractor = $this->makeContractor();
        $client = $this->makeClient($contractor, ['email' => 'client@example.com'], activated: true);

        $response = $this->post('/login', ['email' => 'client@example.com', 'password' => 'password']);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($client);
    }
}
