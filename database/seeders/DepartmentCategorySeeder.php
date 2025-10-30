<?php

namespace Database\Seeders;

use App\Models\DepartmentCategory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DepartmentCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            'Fleet Operations',
            'Technical Operations',
            'Recruitment',
            'Assessment',
            'Crew Development',
            'Crew Certification',
            'Crew Allied Services',
            'Finance',
            'OP'
        ];

        foreach ($categories as $category) {
            DepartmentCategory::create([
                'name' => $category,
            ]);
        }
    }
}
