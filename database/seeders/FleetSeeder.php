<?php

namespace Database\Seeders;

use App\Models\Fleet;
use Illuminate\Database\Seeder;

class FleetSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $fleets = [
            ['name' => 'FLEET A', 'department_id' => 1],
            ['name' => 'FLEET B1', 'department_id' => 2],
            ['name' => 'FLEET B2', 'department_id' => 3],
            ['name' => 'FLEET C1', 'department_id' => 4],
            ['name' => 'FLEET C2', 'department_id' => 5],
            ['name' => 'FLEET D1', 'department_id' => 6],
            ['name' => 'FLEET D2', 'department_id' => 7],
            ['name' => 'FLEET E1', 'department_id' => 8],
            ['name' => 'FLEET E2', 'department_id' => 9],
            ['name' => 'NTMA FLEET', 'department_id' => 10],
        ];

        foreach ($fleets as $fleet) {
            Fleet::firstOrCreate(
                ['name' => $fleet['name']],
                ['department_id' => $fleet['department_id']]
            );
        }
    }
}
