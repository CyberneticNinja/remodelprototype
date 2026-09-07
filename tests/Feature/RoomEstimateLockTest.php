<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesTestData;
use Tests\TestCase;

class RoomEstimateLockTest extends TestCase
{
    use RefreshDatabase, CreatesTestData;

    public function test_contractor_can_edit_estimate_while_unlocked(): void
    {
        $contractor = $this->makeContractor();
        $client = $this->makeClient($contractor);
        $project = $this->makeProject($contractor, $client);
        $room = $this->makeRoom($project);

        $response = $this->actingAs($contractor)->put(route('rooms.update', [$project, $room]), [
            'notes' => 'Updated notes',
            'scope_description' => 'New scope',
            'estimated_cost' => 1500,
            'estimated_duration_days' => 3,
        ]);

        $response->assertRedirect(route('rooms.show', [$project, $room]));
        $this->assertEquals('New scope', $room->fresh()->scope_description);
    }

    public function test_estimate_locks_after_first_work_agreed_signature(): void
    {
        $contractor = $this->makeContractor();
        $client = $this->makeClient($contractor);
        $project = $this->makeProject($contractor, $client);
        $room = $this->makeRoom($project);

        $this->assertFalse($room->isEstimateLocked());

        $this->actingAs($contractor)->post(
            route('signatures.store', [$project, $room, 'work_agreed', 'contractor']),
            ['signature_data' => 'data:image/png;base64,AAA']
        );

        $this->assertTrue($room->fresh()->isEstimateLocked());
    }

    public function test_editing_a_locked_estimate_is_forbidden(): void
    {
        $contractor = $this->makeContractor();
        $client = $this->makeClient($contractor);
        $project = $this->makeProject($contractor, $client);
        $room = $this->makeRoom($project, ['estimate_locked_at' => now()]);

        $this->actingAs($contractor)
            ->get(route('rooms.edit', [$project, $room]))
            ->assertForbidden();

        $this->actingAs($contractor)
            ->put(route('rooms.update', [$project, $room]), ['estimated_cost' => 999])
            ->assertForbidden();
    }
}
