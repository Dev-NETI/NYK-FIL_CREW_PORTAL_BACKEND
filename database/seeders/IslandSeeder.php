<?php

namespace Database\Seeders;

use App\Models\Island;
use Illuminate\Database\Seeder;

class IslandSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $islands = [
            'LUZON',
            'VISAYAS',
            'MINDANAO',
        ];

        foreach ($islands as $islandName) {
            Island::firstOrCreate([
                'name' => $islandName,
            ]);
        }
    }
}
