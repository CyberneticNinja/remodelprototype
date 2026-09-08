<?php

namespace Tests\Feature;

use App\Console\Commands\ResetDemoData;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesTestData;
use Tests\TestCase;

class DemoModeTest extends TestCase
{
    use RefreshDatabase, CreatesTestData;

    public function test_login_page_shows_a_demo_option(): void
    {
        $response = $this->get('/login');

        $response->assertOk();
        $response->assertSee(route('demo.login'), false);
    }

    public function test_demo_login_creates_the_demo_contractor_if_missing(): void
    {
        $this->assertDatabaseMissing('users', ['is_demo' => true]);

        $response = $this->post('/demo');

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();
        $this->assertTrue(auth()->user()->isDemoAccount());
        $this->assertDatabaseHas('users', ['is_demo' => true, 'type' => 'contractor']);
    }

    public function test_demo_login_reuses_the_same_demo_contractor_on_repeat_visits(): void
    {
        $this->post('/demo');
        $this->post('/logout');

        $this->post('/demo');

        $this->assertSame(1, User::where('is_demo', true)->count());
    }

    public function test_an_already_logged_in_user_cannot_be_switched_into_the_demo_via_the_button(): void
    {
        $contractor = $this->makeContractor();

        $response = $this->actingAs($contractor)->post('/demo');

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($contractor);
    }

    public function test_demo_login_is_rate_limited(): void
    {
        for ($i = 0; $i < 10; $i++) {
            $this->post('/demo');
            $this->post('/logout');
        }
        $response = $this->post('/demo');

        $response->assertStatus(429);
    }

    public function test_reset_command_creates_demo_contractor_when_none_exists(): void
    {
        $this->artisan('demo:reset')->assertSuccessful();

        $this->assertDatabaseHas('users', ['is_demo' => true, 'type' => 'contractor']);
        $demo = User::where('is_demo', true)->first();
        $this->assertSame(1, Project::where('contractor_id', $demo->id)->count());
    }

    public function test_reset_command_wipes_prior_demo_data_and_recreates_it(): void
    {
        $this->artisan('demo:reset')->assertSuccessful();
        $demo = User::where('is_demo', true)->first();

        // Simulate a visitor messing with the demo: add an extra client/project.
        $extraClient = $this->makeClient($demo, ['email' => 'visitor-added@example.com']);
        $this->makeProject($demo, $extraClient, ['title' => 'Visitor Junk Project']);
        $this->assertDatabaseHas('users', ['email' => 'visitor-added@example.com']);

        $this->artisan('demo:reset')->assertSuccessful();

        $this->assertDatabaseMissing('users', ['email' => 'visitor-added@example.com']);
        $this->assertDatabaseMissing('projects', ['title' => 'Visitor Junk Project']);
        // Still exactly one demo contractor and one canonical demo project.
        $this->assertSame(1, User::where('is_demo', true)->count());
        $this->assertSame(1, Project::where('contractor_id', $demo->id)->count());
    }

    public function test_reset_command_never_touches_a_real_contractors_data(): void
    {
        $realContractor = $this->makeContractor();
        $realClient = $this->makeClient($realContractor);
        $realProject = $this->makeProject($realContractor, $realClient, ['title' => 'Real Customer Project']);

        $this->artisan('demo:reset')->assertSuccessful();

        $this->assertDatabaseHas('projects', ['id' => $realProject->id, 'title' => 'Real Customer Project']);
        $this->assertDatabaseHas('users', ['id' => $realClient->id]);
        $this->assertDatabaseHas('users', ['id' => $realContractor->id]);
    }
}
