<?php

namespace Database\Seeders;

use App\Models\RankCategory;
use Illuminate\Database\Seeder;

class RankCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            'OFFICER',
            'RATING',
        ];

        foreach ($categories as $categoryName) {
            RankCategory::firstOrCreate([
                'name' => $categoryName,
            ]);
        }
    }
}
