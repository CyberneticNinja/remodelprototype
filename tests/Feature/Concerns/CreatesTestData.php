<?php

namespace Tests\Feature\Concerns;

use App\Models\Project;
use App\Models\Room;
use App\Models\Signature;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

trait CreatesTestData
{
    protected function makeContractor(array $attrs = []): User
    {
        return User::factory()->create([
            'password' => Hash::make('password'),
            ...$attrs,
        ]);
    }

    protected function makeClient(User $contractor, array $attrs = [], bool $activated = true): User
    {
        return User::factory()->client()->create([
            'created_by_contractor_id' => $contractor->id,
            'password'                 => $activated ? Hash::make('password') : null,
            'activated_at'             => $activated ? now() : null,
            ...$attrs,
        ]);
    }

    protected function makeProject(User $contractor, User $client, array $attrs = []): Project
    {
        return Project::create([
            'contractor_id' => $contractor->id,
            'client_id'     => $client->id,
            'title'         => 'Test Project',
            'address'       => '1 Test St',
            ...$attrs,
        ]);
    }

    protected function makeRoom(Project $project, array $attrs = []): Room
    {
        return $project->rooms()->create([
            'name' => 'Kitchen',
            ...$attrs,
        ]);
    }

    // Fully sign off the work_agreed stage (contractor online, client in person)
    protected function signWorkAgreed(Room $room, User $contractor, User $client): void
    {
        Signature::create([
            'room_id' => $room->id, 'stage' => 'work_agreed', 'role' => 'contractor',
            'method' => 'online', 'signed_by_user_id' => $contractor->id,
            'signature_data' => 'data:image/png;base64,AAA', 'signed_at' => now(),
        ]);
        Signature::create([
            'room_id' => $room->id, 'stage' => 'work_agreed', 'role' => 'client',
            'method' => 'online', 'signed_by_user_id' => $client->id,
            'signature_data' => 'data:image/png;base64,BBB', 'signed_at' => now(),
        ]);
        $room->update(['estimate_locked_at' => now()]);
    }

    // Fully sign off the completed stage too (room becomes fully complete)
    protected function signCompleted(Room $room, User $contractor, User $client): void
    {
        Signature::create([
            'room_id' => $room->id, 'stage' => 'completed', 'role' => 'contractor',
            'method' => 'online', 'signed_by_user_id' => $contractor->id,
            'signature_data' => 'data:image/png;base64,CCC', 'signed_at' => now(),
        ]);
        Signature::create([
            'room_id' => $room->id, 'stage' => 'completed', 'role' => 'client',
            'method' => 'online', 'signed_by_user_id' => $client->id,
            'signature_data' => 'data:image/png;base64,DDD', 'signed_at' => now(),
        ]);
    }
}
