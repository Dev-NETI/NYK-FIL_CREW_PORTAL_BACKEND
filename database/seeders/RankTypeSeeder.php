<?php

namespace Database\Seeders;

use App\Models\RankType;
use Illuminate\Database\Seeder;

class RankTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            ['id' => 1, 'name' => 'Officer'],
            ['id' => 2, 'name' => 'Ratings'],
            ['id' => 3, 'name' => 'Supernumerary Positions'],
        ];

        foreach ($types as $type) {
            RankType::create($type);
        }
    }
}
