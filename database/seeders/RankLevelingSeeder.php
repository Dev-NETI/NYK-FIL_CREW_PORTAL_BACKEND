<?php

namespace Database\Seeders;

use App\Models\RankLeveling;
use Illuminate\Database\Seeder;

class RankLevelingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rankLevels = [
            1 => 4,   // Master: 1 to 4
            2 => 4,   // Chief Mate: 1 to 4
            3 => 4,   // Junior Chief Mate: 1 to 4
            4 => 4,   // Second Mate: 1 to 4
            5 => 4,   // Third Mate: 1 to 4
            6 => 4,   // Chief Engineer: 1 to 4
            7 => 4,   // First Assistant Engineer: 1 to 4
            8 => 4,   // Junior First Assistant Engineer: 1 to 4
            9 => 5,   // Second Assistant Engineer: 1 to 5
            10 => 4,  // Third Assistant Engineer: 1 to 4
            11 => 10, // Electrical Engineer: 1 to 10
            12 => 2,  // Boatswain: 1 to 2
            13 => 2,  // Pumpman: 1 to 2
            14 => 2,  // Able Bodies Seaman: 1 to 2
            15 => 2,  // Ordinary Seaman: 1 to 2
            16 => 1,  // Deck Boy: 1
            17 => 2,  // Fitter: 1 to 2
            18 => 2,  // Oiler: 1 to 2
            19 => 2,  // Wiper: 1 to 2
            20 => 1,  // Engine Boy: 1
            21 => 4,  // Electrician: 1 to 4
            22 => 2,  // Chief Cook: 1 to 2
            23 => 2,  // Second Cook: 1 to 2
            24 => 2,  // Messman: 1 to 2
            25 => 1,  // Catering Boy: 1
            26 => 1,  // Junior Third Mate: 1
            27 => 1,  // Deck Cadet: 1
            28 => 1,  // Deck Maintenance Assistant: 1
            29 => 1,  // Junior Third Assistant Engineer: 1
            30 => 1,  // Engine Cadet: 1
            31 => 1,  // Assistant Electrician: 1
            32 => 1,  // Helper Electrician: 1
            33 => 1,  // Engine Maintenance Assistant: 1
            34 => 1,  // Fitter Maintenance Assistant: 1
        ];

        foreach ($rankLevels as $rankId => $maxLevel) {
            for ($level = 1; $level <= $maxLevel; $level++) {
                RankLeveling::create([
                    'rank_id' => $rankId,
                    'level' => $level,
                ]);
            }
        }
    }
}
