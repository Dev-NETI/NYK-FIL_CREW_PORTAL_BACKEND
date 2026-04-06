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
            'Industrial Fleet Operations',
            'Crew Morale and Welfare Department',
            'Recruitment',
            'Assessment',
            'Crew Development',
            'Crew Certification',
            'Finance',
            'Office of the President',
            'Legal and Crew Claims Department',
            'Marine Crew Development Department',
            'Receivables',
            'Crew Management Department',
            'Cruise',
            'Cruise Operation Department',
            'Licensing and Liaison',
            'NA'
        ];

        foreach ($categories as $category) {
            DepartmentCategory::create([
                'name' => $category,
            ]);
        }
    }
}
