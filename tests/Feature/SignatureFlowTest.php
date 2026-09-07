<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesTestData;
use Tests\TestCase;

class SignatureFlowTest extends TestCase
{
    use RefreshDatabase, CreatesTestData;

    public function test_completed_stage_cannot_be_signed_before_work_agreed_is_complete(): void
    {
        $contractor = $this->makeContractor();
        $client = $this->makeClient($contractor);
        $project = $this->makeProject($contractor, $client);
        $room = $this->makeRoom($project);

        // Only the contractor has signed work_agreed so far — not complete yet.
        $this->actingAs($contractor)->post(
            route('signatures.store', [$project, $room, 'work_agreed', 'contractor']),
            ['signature_data' => 'data:image/png;base64,AAA']
        );
        $this->assertFalse($room->fresh()->work_agreed_complete);

        // Contractor tries to sign "completed" — must be blocked.
        $this->actingAs($contractor)
            ->post(route('signatures.store', [$project, $room, 'completed', 'contractor']),
                ['signature_data' => 'data:image/png;base64,ZZZ'])
            ->assertForbidden();

        // Contractor tries the in-person client completed signature — also blocked.
        $this->actingAs($contractor)
            ->post(route('signatures.person.store', [$project, $room, 'completed']),
                ['signature_data' => 'data:image/png;base64,ZZZ', 'signer_name_confirmation' => $client->full_name])
            ->assertForbidden();

        $this->assertDatabaseMissing('signatures', ['room_id' => $room->id, 'stage' => 'completed']);
    }

    public function test_completed_stage_becomes_signable_once_work_agreed_is_fully_signed(): void
    {
        $contractor = $this->makeContractor();
        $client = $this->makeClient($contractor);
        $project = $this->makeProject($contractor, $client);
        $room = $this->makeRoom($project);

        $this->signWorkAgreed($room, $contractor, $client);
        $this->assertTrue($room->fresh()->work_agreed_complete);

        $this->actingAs($contractor)
            ->post(route('signatures.store', [$project, $room, 'completed', 'contractor']),
                ['signature_data' => 'data:image/png;base64,CCC'])
            ->assertRedirect(route('rooms.show', [$project, $room]));

        $this->actingAs($client)
            ->post(route('signatures.store', [$project, $room, 'completed', 'client']),
                ['signature_data' => 'data:image/png;base64,DDD'])
            ->assertRedirect(route('rooms.show', [$project, $room]));

        $this->assertTrue($room->fresh()->is_complete);
    }

    public function test_in_person_signature_rejects_a_wrong_typed_name(): void
    {
        $contractor = $this->makeContractor();
        $client = $this->makeClient($contractor, ['first_name' => 'Ben', 'last_name' => 'Ortiz']);
        $project = $this->makeProject($contractor, $client);
        $room = $this->makeRoom($project);

        $response = $this->actingAs($contractor)->post(
            route('signatures.person.store', [$project, $room, 'work_agreed']),
            ['signature_data' => 'data:image/png;base64,BBB', 'signer_name_confirmation' => 'Wrong Name']
        );

        $response->assertSessionHasErrors('signer_name_confirmation');
        $this->assertDatabaseMissing('signatures', ['room_id' => $room->id, 'role' => 'client']);
    }

    public function test_in_person_signature_accepts_the_correct_typed_name(): void
    {
        $contractor = $this->makeContractor();
        $client = $this->makeClient($contractor, ['first_name' => 'Ben', 'last_name' => 'Ortiz']);
        $project = $this->makeProject($contractor, $client);
        $room = $this->makeRoom($project);

        $response = $this->actingAs($contractor)->post(
            route('signatures.person.store', [$project, $room, 'work_agreed']),
            ['signature_data' => 'data:image/png;base64,BBB', 'signer_name_confirmation' => 'Ben Ortiz']
        );

        $response->assertRedirect(route('rooms.show', [$project, $room]));
        $this->assertDatabaseHas('signatures', [
            'room_id' => $room->id, 'role' => 'client', 'method' => 'in_person',
            'signed_by_user_id' => $client->id,
        ]);
    }

    public function test_client_cannot_sign_the_contractor_role(): void
    {
        $contractor = $this->makeContractor();
        $client = $this->makeClient($contractor);
        $project = $this->makeProject($contractor, $client);
        $room = $this->makeRoom($project);

        $this->actingAs($client)
            ->post(route('signatures.store', [$project, $room, 'work_agreed', 'contractor']),
                ['signature_data' => 'data:image/png;base64,AAA'])
            ->assertForbidden();
    }

    public function test_another_contractor_cannot_sign_a_room_they_dont_own(): void
    {
        $contractor = $this->makeContractor();
        $client = $this->makeClient($contractor);
        $project = $this->makeProject($contractor, $client);
        $room = $this->makeRoom($project);

        $otherContractor = $this->makeContractor(['email' => 'other@example.com']);

        $this->actingAs($otherContractor)
            ->post(route('signatures.store', [$project, $room, 'work_agreed', 'contractor']),
                ['signature_data' => 'data:image/png;base64,AAA'])
            ->assertForbidden();
    }
}
