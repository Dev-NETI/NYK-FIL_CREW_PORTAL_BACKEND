<?php

namespace Database\Seeders;

use App\Models\AdminProfile;
use App\Models\AdminRole;
use App\Models\Department;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class AdminProfileSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminData = [
            [
                'email' => 'fleet@nykfil.com',
                'firstname' => 'John',
                'middlename' => 'Doe',
                'lastname' => 'Smith',
                'department_name' => 'A',
            ],
            [
                'email' => 'technical.dry@nykfil.com',
                'firstname' => 'Jane',
                'middlename' => 'Marie',
                'lastname' => 'Johnson',
                'department_name' => 'Dry',
            ],
            [
                'email' => 'recruitment@nykfil.com',
                'firstname' => 'Robert',
                'middlename' => 'Lee',
                'lastname' => 'Williams',
                'department_name' => 'Payroll',
            ],
            [
                'email' => 'assessment@nykfil.com',
                'firstname' => 'Michael',
                'middlename' => 'James',
                'lastname' => 'Brown',
                'department_name' => 'B1',
            ],
            [
                'email' => 'crewdevelopment@nykfil.com',
                'firstname' => 'Sarah',
                'middlename' => 'Ann',
                'lastname' => 'Davis',
                'department_name' => 'B2',
            ],
            [
                'email' => 'crewcertification@nykfil.com',
                'firstname' => 'David',
                'middlename' => 'Paul',
                'lastname' => 'Miller',
                'department_name' => 'C1',
            ],
            [
                'email' => 'crewalliedservices@nykfil.com',
                'firstname' => 'Emily',
                'middlename' => 'Rose',
                'lastname' => 'Wilson',
                'department_name' => 'C2',
            ],
            [
                'email' => 'finance.payroll@nykfil.com',
                'firstname' => 'Thomas',
                'middlename' => 'Edward',
                'lastname' => 'Moore',
                'department_name' => 'Dry',
            ],
            [
                'email' => 'finance.SLAF@nykfil.com',
                'firstname' => 'Jennifer',
                'middlename' => 'Lynn',
                'lastname' => 'Taylor',
                'department_name' => 'Liquid',
            ],
            [
                'email' => 'finance.disbursement@nykfil.com',
                'firstname' => 'Christopher',
                'middlename' => 'Allen',
                'lastname' => 'Anderson',
                'department_name' => 'Shore-Based Work',
            ],
            [
                'email' => 'finance.sga@nykfil.com',
                'firstname' => 'Amanda',
                'middlename' => 'Grace',
                'lastname' => 'Thomas',
                'department_name' => 'Promotion',
            ],
            [
                'email' => 'op.qad@nykfil.com',
                'firstname' => 'Matthew',
                'middlename' => 'Scott',
                'lastname' => 'Jackson',
                'department_name' => 'VISA',
            ],
            [
                'email' => 'op.claims@nykfil.com',
                'firstname' => 'Jessica',
                'middlename' => 'Marie',
                'lastname' => 'White',
                'department_name' => 'JISS',
            ],
            [
                'email' => 'morale.officer@nykfil.com',
                'firstname' => 'Daniel',
                'middlename' => 'Ray',
                'lastname' => 'Harris',
                'department_name' => 'Crew Morale',
            ],
            [
                'email' => 'cruise.coordinator@nykfil.com',
                'firstname' => 'Ashley',
                'middlename' => 'Nicole',
                'lastname' => 'Martin',
                'department_name' => 'Cruise',
            ],
            [
                'email' => 'payroll.admin@nykfil.com',
                'firstname' => 'Joshua',
                'middlename' => 'Aaron',
                'lastname' => 'Thompson',
                'department_name' => 'Payroll',
            ],
            [
                'email' => 'slaf.officer@nykfil.com',
                'firstname' => 'Melissa',
                'middlename' => 'Jane',
                'lastname' => 'Garcia',
                'department_name' => 'SLAF',
            ],
            [
                'email' => 'qad.specialist@nykfil.com',
                'firstname' => 'Ryan',
                'middlename' => 'Patrick',
                'lastname' => 'Martinez',
                'department_name' => 'Liquid',
            ],
            [
                'email' => 'claims.officer@nykfil.com',
                'firstname' => 'Lauren',
                'middlename' => 'Elizabeth',
                'lastname' => 'Rodriguez',
                'department_name' => 'NTMA',
            ],
            [
                'email' => 'fleet.ntma@nykfil.com',
                'firstname' => 'Brandon',
                'middlename' => 'Lee',
                'lastname' => 'Lopez',
                'department_name' => 'NTMA',
            ],
            [
                'email' => 'noc@neti.com.ph',
                'firstname' => 'Network Operations',
                'middlename' => 'Center',
                'lastname' => 'Administrator',
                'department_name' => 'A',
                'assign_all_roles' => true, // Special flag for NOC admin
            ],
        ];

        foreach ($adminData as $data) {
            // Find department by name
            $department = Department::where('name', $data['department_name'])->first();

            if (!$department) {
                $this->command->warn("Department '{$data['department_name']}' not found, skipping {$data['email']}");
                continue;
            }

            // Find or create admin user
            $user = User::where('email', $data['email'])->first();

            if (!$user) {
                $user = User::create([
                    'email' => $data['email'],
                    'is_crew' => 0,
                    'department_id' => $department->id,
                    'email_verified_at' => now()->toDateTimeString(),
                ]);

                $this->command->info("Created admin user: {$data['email']} with department: {$data['department_name']}");
            } else {
                // Update department_id if user exists but doesn't have one
                if (!$user->department_id) {
                    $user->update(['department_id' => $department->id]);
                    $this->command->info("Updated department for existing user: {$data['email']}");
                }
            }

            // Create admin profile if it doesn't exist
            $profile = AdminProfile::where('user_id', $user->id)->first();

            if (!$profile) {
                AdminProfile::create([
                    'user_id' => $user->id,
                    'firstname' => $data['firstname'],
                    'middlename' => $data['middlename'],
                    'lastname' => $data['lastname'],
                    'modified_by' => $user->id,
                ]);

                $this->command->info("Created admin profile for: {$data['email']}");
            } else {
                $this->command->warn("Admin profile already exists for: {$data['email']}");
            }

            // Assign all roles if specified (for NOC admin)
            if (isset($data['assign_all_roles']) && $data['assign_all_roles']) {
                $allRoles = Role::all();
                $assignedCount = 0;

                foreach ($allRoles as $role) {
                    // Check if role is already assigned
                    $existingRole = AdminRole::where('user_id', $user->id)
                        ->where('role_id', $role->id)
                        ->first();

                    if (!$existingRole) {
                        AdminRole::create([
                            'user_id' => $user->id,
                            'role_id' => $role->id,
                            'modified_by' => $user->id,
                        ]);
                        $assignedCount++;
                    }
                }

                if ($assignedCount > 0) {
                    $this->command->info("Assigned {$assignedCount} roles to: {$data['email']}");
                } else {
                    $this->command->warn("All roles already assigned to: {$data['email']}");
                }
            }
        }

        $this->command->info('AdminProfileSeeder completed successfully!');
    }
}
