<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AppointmentType;
use App\Models\Department;
use App\Models\User;

class AppointmentTypeSeeder extends Seeder
{
    public function run(): void
    {
        // Pick a system admin user as creator
        $admin = User::where('is_crew', 0)->first();

        if (!$admin) {
            $this->command->warn('No admin user found. Skipping appointment type seeding.');
            return;
        }

        $typesByDepartment = [
            'Recruitment' => [
                'Initial Interview',
                'Final Interview',
                'Document Evaluation',
            ],
            'Medical' => [
                'Medical Check-up',
                'Medical Clearance',
            ],
            'VISA' => [
                'Visa Application',
                'Visa Renewal',
            ],
            'Payroll' => [
                'Salary Concern',
                'Disbursement Inquiry',
            ],
            'Crew Morale' => [
                'Counseling',
                'Grievance Meeting',
            ],
        ];

        foreach ($typesByDepartment as $departmentName => $types) {
            $department = Department::where('name', $departmentName)->first();

            if (!$department) {
                $this->command->warn("Department '{$departmentName}' not found. Skipping.");
                continue;
            }

            foreach ($types as $typeName) {
                AppointmentType::firstOrCreate(
                    [
                        'department_id' => $department->id,
                        'name' => $typeName,
                    ],
                    [
                        'description' => null,
                        'is_active' => true,
                        'created_by' => $admin->id,
                    ]
                );
            }
        }

        $this->command->info('Appointment types seeded successfully.');
    }
}
