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
            'FLEET A',
            'FLEET B1',
            'FLEET B2',
            'FLEET C1',
            'FLEET C2',
            'FLEET D1',
            'FLEET D2',
            'FLEET E1',
            'FLEET E2',
            'NTMA FLEET',
        ];

        foreach ($fleets as $fleetName) {
            Fleet::firstOrCreate([
                'name' => $fleetName,
            ]);
        }
    }
}
