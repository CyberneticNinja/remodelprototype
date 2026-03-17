<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Client;
use App\Models\Contractor;
use App\Models\Project;
use App\Models\Room;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        echo "Seeding database :-)...\n";
    // ── Contractor ────────────────────────────────────────────────────────
        $contractor = Contractor::create([
            'first_name'      => 'Mike',
            'last_name'       => 'Torres',
            'email'           => 'mike@torresremodeling.com',
            'password'        => Hash::make('password'),
            'phone'           => '915-555-0101',
            'company_name'    => 'Torres Remodeling LLC',
            'company_address' => '100 Industrial Blvd, El Paso, TX 79901',
            'company_phone'   => '915-555-0100',
        ]);
 
        // ── Client ────────────────────────────────────────────────────────────
        $client = Client::create([
            'contractor_id' => $contractor->id,
            'first_name'    => 'John',
            'last_name'     => 'Smith',
            'phone'         => '915-555-0202',
            'address'       => '234 Maple Lane, El Paso, TX 79902',
        ]);
 
        // ── Project ───────────────────────────────────────────────────────────
        $project = Project::create([
            'contractor_id' => $contractor->id,
            'client_id'     => $client->id,
            'title'         => 'Smith Home Full Remodel',
            'address'       => '234 Maple Lane, El Paso, TX 79902',
        ]);
 
        // ── Rooms ─────────────────────────────────────────────────────────────
        $rooms = [
            ['name' => 'Master Bedroom', 'notes' => 'Repaint walls, replace flooring, install new ceiling fan.'],
            ['name' => 'Kitchen',        'notes' => 'New countertops, repaint cabinets, install backsplash.'],
            ['name' => 'Bathroom',       'notes' => 'Replace vanity, retile shower, install new fixtures.'],
        ];
 
        foreach ($rooms as $room) {
            Room::create([
                'project_id' => $project->id,
                'name'       => $room['name'],
                'notes'      => $room['notes'],
            ]);
        }
    }
}
