<?php

namespace Database\Seeders;

use App\Models\RankCategory;
use App\Models\RankGroup;
use Illuminate\Database\Seeder;

class RankGroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rankGroupsData = [
            // Officer Groups
            ['category' => 'OFFICER', 'group' => 'MASTER'],
            ['category' => 'OFFICER', 'group' => 'CHIEF MATE'],
            ['category' => 'OFFICER', 'group' => 'JUNIOR CHIEF MATE'],
            ['category' => 'OFFICER', 'group' => 'SECOND MATE'],
            ['category' => 'OFFICER', 'group' => 'CHIEF ENGINEER'],
            ['category' => 'OFFICER', 'group' => 'FIRST ASSISTANT ENGINEER'],
            ['category' => 'OFFICER', 'group' => 'JUNIOR FIRST ASSISTANT ENGINEER'],
            ['category' => 'OFFICER', 'group' => 'SECOND ASSISTANT ENGINEER'],
            ['category' => 'OFFICER', 'group' => 'ELECTRICAL ENGINEER'],
            // Rating Groups
            ['category' => 'RATING', 'group' => 'BOATSWAIN'],
            ['category' => 'RATING', 'group' => 'ABLE-SEAMAN'],
            ['category' => 'RATING', 'group' => 'NO. 1 OILER'],
            ['category' => 'RATING', 'group' => 'OILER'],
            ['category' => 'RATING', 'group' => 'CHIEF COOK'],
            ['category' => 'RATING', 'group' => 'FITTER'],
            ['category' => 'RATING', 'group' => 'PUMPMAN'],
            ['category' => 'RATING', 'group' => 'ROUSTABOUT'],
            ['category' => 'RATING', 'group' => 'ROUSTABOUT - PUSHER'],
            ['category' => 'RATING', 'group' => 'ENGINE ASSISTANT'],
        ];

        foreach ($rankGroupsData as $data) {
            $category = RankCategory::where('name', $data['category'])->first();

            if ($category) {
                RankGroup::firstOrCreate([
                    'name' => $data['group'],
                    'rank_category_id' => $category->id,
                ]);
            }
        }
    }
}
