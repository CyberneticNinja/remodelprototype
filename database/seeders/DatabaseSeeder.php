<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\Room;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // ── Contractor ────────────────────────────────────────────────────
        // No password to seed — sign in from /login with either email below
        // and grab the link from the log driver (storage/logs/laravel.log).
        $contractor = User::create([
            'type'            => 'contractor',
            'first_name'      => 'Mike',
            'last_name'       => 'Torres',
            'email'           => 'mike@torresremodeling.com',
            'phone'           => '915-555-0101',
            'company_name'    => 'Torres Remodeling LLC',
            'company_address' => '100 Industrial Blvd, El Paso, TX 79901',
            'company_phone'   => '915-555-0100',
        ]);

        // ── Client — activated, so the login flow can be tried both ways ──
        $client = User::create([
            'type'                     => 'client',
            'created_by_contractor_id' => $contractor->id,
            'first_name'               => 'John',
            'last_name'                => 'Smith',
            'email'                    => 'john@example.com',
            'activated_at'             => now(),
            'phone'                    => '915-555-0202',
            'address'                  => '234 Maple Lane, El Paso, TX 79902',
        ]);

        // ── Project ─────────────────────────────────────────────────────
        $project = Project::create([
            'contractor_id' => $contractor->id,
            'client_id'     => $client->id,
            'title'         => 'Smith Home Full Remodel',
            'address'       => '234 Maple Lane, El Paso, TX 79902',
        ]);

        // ── Rooms ───────────────────────────────────────────────────────
        $rooms = [
            ['name' => 'Master Bedroom', 'notes' => 'Repaint walls, replace flooring, install new ceiling fan.', 'scope_description' => 'Full repaint, new laminate flooring, ceiling fan install.', 'estimated_cost' => 3200, 'estimated_duration_days' => 4],
            ['name' => 'Kitchen',        'notes' => 'New countertops, repaint cabinets, install backsplash.', 'scope_description' => 'Quartz countertops, cabinet repaint, tile backsplash.', 'estimated_cost' => 8500, 'estimated_duration_days' => 10],
            ['name' => 'Bathroom',       'notes' => 'Replace vanity, retile shower, install new fixtures.', 'scope_description' => 'New vanity, shower retile, fixture replacement.', 'estimated_cost' => 5400, 'estimated_duration_days' => 7],
        ];

        foreach ($rooms as $room) {
            Room::create([
                'project_id' => $project->id,
                ...$room,
            ]);
        }
    }
}
