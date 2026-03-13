<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\DepartmentCategory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $departmentData = [
            'Fleet Operations' => [
                'FLEET A',
                'FLEET B1',
                'FLEET B2',
                'FLEET C1',
                'FLEET C2',
                'FLEET D1',
                'FLEET D2',
                'FLEET E1',
                'FLEET E2',
                'NTMA FLEET'
            ],
            'Technical Operations' => ['Dry', 'Liquid'],
            'Crew Development' => ['Shore-Based Work', 'Promotion'],
            'Crew Allied Services' => ['VISA', 'JISS', 'Crew Morale', 'Cruise'],
            'Finance' => ['Payroll', 'SLAF', 'Disbursement', 'SGA'],
            'OP' => ['QAD', 'Claims'],
            'Recruitment' => ['Recruitment'],
            'Assessment' => ['Assessment'],
        ];

        foreach ($departmentData as $categoryName => $departments) {
            $category = DepartmentCategory::where('name', $categoryName)->first();

            if ($category) {
                foreach ($departments as $departmentName) {
                    Department::create([
                        'department_category_id' => $category->id,
                        'name' => $departmentName,
                    ]);
                }
            }
        }
    }
}
