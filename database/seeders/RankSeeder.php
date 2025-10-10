<?php

namespace Database\Seeders;

use App\Models\Rank;
use Illuminate\Database\Seeder;

class RankSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $ranks = [
            ['name' => 'Master', 'code' => 'MSTR', 'rank_department_id' => 1, 'rank_type_id' => 1],
            ['name' => 'Chief Mate', 'code' => 'CM', 'rank_department_id' => 1, 'rank_type_id' => 1],
            ['name' => 'Junior Chief Mate', 'code' => 'CM-2', 'rank_department_id' => 1, 'rank_type_id' => 1],
            ['name' => 'Second Mate', 'code' => '2M', 'rank_department_id' => 1, 'rank_type_id' => 1],
            ['name' => 'Third Mate', 'code' => '3M', 'rank_department_id' => 1, 'rank_type_id' => 1],
            ['name' => 'Chief Engineer', 'code' => 'CE', 'rank_department_id' => 2, 'rank_type_id' => 1],
            ['name' => 'First Assistant Engineer', 'code' => '1AE', 'rank_department_id' => 2, 'rank_type_id' => 1],
            ['name' => 'Junior First Assistant Engineer', 'code' => '1AE-2', 'rank_department_id' => 2, 'rank_type_id' => 1],
            ['name' => 'Second Assistant Engineer', 'code' => '2AE', 'rank_department_id' => 2, 'rank_type_id' => 1],
            ['name' => 'Third Assistant Engineer', 'code' => '3AE', 'rank_department_id' => 2, 'rank_type_id' => 1],
            ['name' => 'Electrical Engineer', 'code' => 'E/E', 'rank_department_id' => 2, 'rank_type_id' => 1],
            ['name' => 'Boatswain', 'code' => 'BSN', 'rank_department_id' => 1, 'rank_type_id' => 2],
            ['name' => 'Pumpman', 'code' => 'PMN', 'rank_department_id' => 1, 'rank_type_id' => 2],
            ['name' => 'Able Bodies Seaman', 'code' => 'AB', 'rank_department_id' => 1, 'rank_type_id' => 2],
            ['name' => 'Ordinary Seaman', 'code' => 'OS', 'rank_department_id' => 1, 'rank_type_id' => 2],
            ['name' => 'Deck Boy', 'code' => 'DBOY', 'rank_department_id' => 1, 'rank_type_id' => 2],
            ['name' => 'Fitter', 'code' => 'FTR', 'rank_department_id' => 2, 'rank_type_id' => 2],
            ['name' => 'Oiler', 'code' => 'OLR', 'rank_department_id' => 2, 'rank_type_id' => 2],
            ['name' => 'Wiper', 'code' => 'WPR', 'rank_department_id' => 2, 'rank_type_id' => 2],
            ['name' => 'Engine Boy', 'code' => 'EBOY', 'rank_department_id' => 2, 'rank_type_id' => 2],
            ['name' => 'Electrician', 'code' => 'ELECT', 'rank_department_id' => 2, 'rank_type_id' => 2],
            ['name' => 'Chief Cook', 'code' => 'CCK', 'rank_department_id' => 3, 'rank_type_id' => 2],
            ['name' => 'Second Cook', 'code' => '2CK', 'rank_department_id' => 3, 'rank_type_id' => 2],
            ['name' => 'Messman', 'code' => 'MSM', 'rank_department_id' => 3, 'rank_type_id' => 2],
            ['name' => 'Catering Boy', 'code' => 'CBOY', 'rank_department_id' => 3, 'rank_type_id' => 2],
            ['name' => 'Junior Third Mate', 'code' => 'JR3M', 'rank_department_id' => 1, 'rank_type_id' => 3],
            ['name' => 'Deck Cadet', 'code' => 'D/CDT', 'rank_department_id' => 1, 'rank_type_id' => 3],
            ['name' => 'Deck Maintenance Assistant', 'code' => 'DMA', 'rank_department_id' => 1, 'rank_type_id' => 3],
            ['name' => 'Junior Third Assistant Engineer', 'code' => 'JR3AE', 'rank_department_id' => 2, 'rank_type_id' => 3],
            ['name' => 'Engine Cadet', 'code' => 'E/CDT', 'rank_department_id' => 2, 'rank_type_id' => 3],
            ['name' => 'Assistant Electrician', 'code' => 'A/ELECT', 'rank_department_id' => 2, 'rank_type_id' => 3],
            ['name' => 'Helper Electrician', 'code' => 'H/E', 'rank_department_id' => 2, 'rank_type_id' => 3],
            ['name' => 'Engine Maintenance Assistant', 'code' => 'EMA', 'rank_department_id' => 2, 'rank_type_id' => 3],
            ['name' => 'Fitter Maintenance Assistant', 'code' => 'FMA', 'rank_department_id' => 2, 'rank_type_id' => 3],
        ];

        foreach ($ranks as $rank) {
            Rank::create($rank);
        }
    }
}
