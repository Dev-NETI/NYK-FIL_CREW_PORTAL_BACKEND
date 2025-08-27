<?php

namespace Database\Seeders;

use App\Models\Island;
use App\Models\Region;
use Illuminate\Database\Seeder;

class RegionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $regionsData = [
            // Luzon
            ['island' => 'LUZON', 'region' => 'REGION I'],
            ['island' => 'LUZON', 'region' => 'REGION II'],
            ['island' => 'LUZON', 'region' => 'REGION III'],
            ['island' => 'LUZON', 'region' => 'REGION IV-A'],
            ['island' => 'LUZON', 'region' => 'REGION V'],
            ['island' => 'LUZON', 'region' => 'CAR'],
            ['island' => 'LUZON', 'region' => 'NCR'],
            // Visayas
            ['island' => 'VISAYAS', 'region' => 'REGION VI'],
            ['island' => 'VISAYAS', 'region' => 'REGION VII'],
            ['island' => 'VISAYAS', 'region' => 'REGION VIII'],
            // Mindanao
            ['island' => 'MINDANAO', 'region' => 'REGION IX'],
            ['island' => 'MINDANAO', 'region' => 'REGION X'],
            ['island' => 'MINDANAO', 'region' => 'REGION XI'],
            ['island' => 'MINDANAO', 'region' => 'REGION XIII'],
        ];

        foreach ($regionsData as $data) {
            $island = Island::where('name', $data['island'])->first();

            if ($island) {
                Region::firstOrCreate([
                    'name' => $data['region'],
                    'island_id' => $island->id,
                ]);
            }
        }
    }
}
