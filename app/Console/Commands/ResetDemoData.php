<?php

namespace App\Console\Commands;

use App\Models\Project;
use App\Models\Signature;
use App\Models\User;
use Illuminate\Console\Command;

class ResetDemoData extends Command
{
    protected $signature = 'demo:reset';

    protected $description = 'Wipe and reseed the public demo contractor account, so no visitor\'s changes stick around for the next one';

    public function handle(): int
    {
        $demo = User::demoContractor();

        // Deleting the demo contractor's projects cascades away their
        // rooms, room_photos, and signatures automatically (see those
        // migrations' cascadeOnDelete()).
        Project::where('contractor_id', $demo->id)->delete();

        // Any client a visitor added only ever existed to serve one of
        // those projects — safe to remove outright, not just orphan.
        User::where('created_by_contractor_id', $demo->id)->delete();

        $client = User::create([
            'type'                     => 'client',
            'created_by_contractor_id' => $demo->id,
            'first_name'               => 'Jamie',
            'last_name'                => 'Rivera',
            'email'                    => 'jamie.demo@remodelpro.test',
            'phone'                    => '555-0199',
            'address'                  => '789 Demo Ct, Sample City, TX',
            'activated_at'             => now(),
        ]);

        $project = Project::create([
            'contractor_id' => $demo->id,
            'client_id'     => $client->id,
            'title'         => 'Rivera Home Remodel (Demo)',
            'address'       => '789 Demo Ct, Sample City, TX',
        ]);

        // One room at each stage of the lifecycle, so a visitor sees the
        // whole story at a glance instead of having to build it themselves.
        $project->rooms()->create([
            'name'                     => 'Guest Bathroom',
            'notes'                    => 'Outdated tile, old fixtures.',
            'scope_description'        => 'Retile shower, replace vanity and fixtures.',
            'estimated_cost'           => 4200,
            'estimated_duration_days'  => 5,
        ]);

        $kitchen = $project->rooms()->create([
            'name'                     => 'Kitchen',
            'notes'                    => 'Cabinets need repainting, counters are dated.',
            'scope_description'        => 'Repaint cabinets, replace countertops, new backsplash.',
            'estimated_cost'           => 9500,
            'estimated_duration_days'  => 8,
            'estimate_locked_at'       => now(),
        ]);
        $this->signBothRoles($kitchen->id, 'work_agreed', $demo->id, $client->id);

        $bedroom = $project->rooms()->create([
            'name'                     => 'Primary Bedroom',
            'notes'                    => 'Repaint, new flooring.',
            'scope_description'        => 'Full repaint, laminate flooring install.',
            'estimated_cost'           => 3100,
            'estimated_duration_days'  => 3,
            'estimate_locked_at'       => now(),
        ]);
        $this->signBothRoles($bedroom->id, 'work_agreed', $demo->id, $client->id);
        $this->signBothRoles($bedroom->id, 'completed', $demo->id, $client->id);

        $this->info('Demo data reset: 1 contractor, 1 client, 1 project, 3 rooms.');

        return self::SUCCESS;
    }

    private function signBothRoles(int $roomId, string $stage, int $contractorId, int $clientId): void
    {
        Signature::create([
            'room_id' => $roomId, 'stage' => $stage, 'role' => 'contractor',
            'method' => 'online', 'signed_by_user_id' => $contractorId,
            'signature_data' => 'data:image/png;base64,DEMO', 'signed_at' => now(),
        ]);
        Signature::create([
            'room_id' => $roomId, 'stage' => $stage, 'role' => 'client',
            'method' => 'online', 'signed_by_user_id' => $clientId,
            'signature_data' => 'data:image/png;base64,DEMO', 'signed_at' => now(),
        ]);
    }
}
