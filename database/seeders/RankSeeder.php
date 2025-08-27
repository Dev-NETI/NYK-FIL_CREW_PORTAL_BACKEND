<?php

namespace Database\Seeders;

use App\Models\Rank;
use App\Models\RankGroup;
use Illuminate\Database\Seeder;

class RankSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $ranksData = [
            // Masters
            ['group' => 'MASTER', 'rank' => 'MSTR(10)'],
            ['group' => 'MASTER', 'rank' => 'MSTR'],
            ['group' => 'MASTER', 'rank' => 'MSTR(9)'],
            ['group' => 'MASTER', 'rank' => 'MSTR(2)'],
            ['group' => 'MASTER', 'rank' => 'MSTR(5)'],
            // Chief Mates
            ['group' => 'CHIEF MATE', 'rank' => 'C/M(4)'],
            ['group' => 'CHIEF MATE', 'rank' => 'C/M(6)'],
            ['group' => 'CHIEF MATE', 'rank' => 'C/M(1)'],
            // Junior Chief Mates
            ['group' => 'JUNIOR CHIEF MATE', 'rank' => 'C/M-2(4)'],
            ['group' => 'JUNIOR CHIEF MATE', 'rank' => 'C/M-2(3)'],
            // Second Mates
            ['group' => 'SECOND MATE', 'rank' => '2/M(4)'],
            // Chief Engineers
            ['group' => 'CHIEF ENGINEER', 'rank' => 'C/E(10)'],
            ['group' => 'CHIEF ENGINEER', 'rank' => 'C/E(1)'],
            ['group' => 'CHIEF ENGINEER', 'rank' => 'C/E(2)'],
            ['group' => 'CHIEF ENGINEER', 'rank' => 'C/E(4)'],
            ['group' => 'CHIEF ENGINEER', 'rank' => 'C/E(8)'],
            // First Assistant Engineers
            ['group' => 'FIRST ASSISTANT ENGINEER', 'rank' => '1/AE(4)'],
            ['group' => 'FIRST ASSISTANT ENGINEER', 'rank' => '1/AE(2)'],
            // Junior First Assistant Engineers
            ['group' => 'JUNIOR FIRST ASSISTANT ENGINEER', 'rank' => '1/AE-2(4)'],
            // Second Assistant Engineers
            ['group' => 'SECOND ASSISTANT ENGINEER', 'rank' => '2/AE(5)'],
            ['group' => 'SECOND ASSISTANT ENGINEER', 'rank' => '2/AE(2)'],
            ['group' => 'SECOND ASSISTANT ENGINEER', 'rank' => '2/E(4)'],
            // Electrical Engineers
            ['group' => 'ELECTRICAL ENGINEER', 'rank' => 'ELECT ENGR. 10'],
            // Boatswains
            ['group' => 'BOATSWAIN', 'rank' => 'BSN(2)'],
            ['group' => 'BOATSWAIN', 'rank' => 'BSN(1)'],
            // Able Seamen
            ['group' => 'ABLE-SEAMAN', 'rank' => 'AB(2)'],
            ['group' => 'ABLE-SEAMAN', 'rank' => 'AB'],
            // No. 1 Oilers
            ['group' => 'NO. 1 OILER', 'rank' => 'OLR NO. 1(2)'],
            // Oilers
            ['group' => 'OILER', 'rank' => 'OLR(2)'],
            ['group' => 'OILER', 'rank' => 'OLR'],
            // Chief Cooks
            ['group' => 'CHIEF COOK', 'rank' => 'C/CK(2)'],
            ['group' => 'CHIEF COOK', 'rank' => 'C/CK'],
            // Fitters
            ['group' => 'FITTER', 'rank' => 'FTR(2)'],
            // Pumpmen
            ['group' => 'PUMPMAN', 'rank' => 'P/MAN(2)'],
            // Roustabouts
            ['group' => 'ROUSTABOUT', 'rank' => 'RABOUT'],
            // Roustabout - Pushers
            ['group' => 'ROUSTABOUT - PUSHER', 'rank' => 'RABOUT-PHR'],
            // Engine Assistants
            ['group' => 'ENGINE ASSISTANT', 'rank' => 'ENG ASST'],
        ];

        foreach ($ranksData as $data) {
            $group = RankGroup::where('name', $data['group'])->first();

            if ($group) {
                Rank::firstOrCreate([
                    'name' => $data['rank'],
                    'rank_group_id' => $group->id,
                ]);
            }
        }
    }
}
