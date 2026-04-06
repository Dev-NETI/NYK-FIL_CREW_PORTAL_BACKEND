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
            'Industrial Fleet Operations' => [
                'FLEET A',
                'FLEET B1',
                'FLEET B2',
                'FLEET C1',
                'FLEET C2',
                'FLEET D1',
                'FLEET D2',
                'FLEET E1',
                'FLEET E2',
                'NTMA FLEET',
                'NA'
            ],
            'Crew Development' => ['Shore-Based Work', 'Promotion'],
            'Crew Morale and Welfare Department' => ['Crew Morale', 'NA'],
            'Finance' => ['Payroll', 'SLAF', 'Disbursement', 'SGA', 'Receivables'],
            'Legal and Crew Claims Department' => ['Claims', 'Legal', 'NA'],
            'Recruitment' => ['Recruitment', 'NA'],
            'Assessment' => ['Assessment', 'Engine', 'NA'],
            'Marine Crew Development Department' => ['SSRP - NAS', 'Deck', 'Engine', 'Galley', 'NA'],
            'Receivables' => ['Receivables'],
            'Crew Management Department' => ['Dry Vessels', 'Liquid Vessels', 'NA'],
            'Cruise' => ['Cruise', 'NA'],
            'Cruise Operation Department' => ['Cruise Operation Department', 'NA'],
            'Licensing and Liaison' => ['Liaison', 'Visa Processing', 'Licensing', 'NA'],
            'Office of the President' => ['Office of the President', 'NA'],
            'NA' => ['NA'],
            'Crew Certification' => ['Crew Certification']
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
