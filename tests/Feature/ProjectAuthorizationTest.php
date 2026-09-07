<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesTestData;
use Tests\TestCase;

class ProjectAuthorizationTest extends TestCase
{
    use RefreshDatabase, CreatesTestData;

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/')->assertRedirect('/login');
    }

    public function test_contractor_cannot_view_another_contractors_project(): void
    {
        $contractor = $this->makeContractor();
        $client = $this->makeClient($contractor);
        $project = $this->makeProject($contractor, $client);

        $otherContractor = $this->makeContractor(['email' => 'other@example.com']);

        $this->actingAs($otherContractor)
            ->get(route('projects.show', $project))
            ->assertForbidden();
    }

    public function test_client_can_view_their_own_project_read_only(): void
    {
        $contractor = $this->makeContractor();
        $client = $this->makeClient($contractor);
        $project = $this->makeProject($contractor, $client);

        $response = $this->actingAs($client)->get(route('projects.show', $project));

        $response->assertOk();
        $response->assertDontSee('Add Room');
    }

    public function test_client_cannot_view_a_project_they_are_not_party_to(): void
    {
        $contractor = $this->makeContractor();
        $client = $this->makeClient($contractor);
        $project = $this->makeProject($contractor, $client);

        $otherClient = $this->makeClient($contractor, ['email' => 'other-client@example.com']);

        $this->actingAs($otherClient)
            ->get(route('projects.show', $project))
            ->assertForbidden();
    }

    public function test_client_cannot_create_a_project(): void
    {
        $contractor = $this->makeContractor();
        $client = $this->makeClient($contractor);

        $this->actingAs($client)->get(route('projects.create'))->assertForbidden();
    }
}
