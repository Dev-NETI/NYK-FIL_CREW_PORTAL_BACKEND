<?php

namespace Database\Seeders;

use App\Models\AdminRole;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AdminRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get admin users (is_crew = 0)
        $adminUsers = User::where('is_crew', 0)->limit(25)->get();

        // Get all available roles
        $roles = Role::all();

        if ($adminUsers->isEmpty()) {
            $this->command->warn('No admin users found. Please run UserSeeder first or create admin users.');
            return;
        }

        if ($roles->isEmpty()) {
            $this->command->warn('No roles found. Please run RoleSeeder first.');
            return;
        }

        // Define role assignment patterns
        $assignmentPatterns = [
            // Pattern 1: Super Admin gets all permissions
            ['roles_count' => 1, 'specific_roles' => ['Super Admin']],

            // Pattern 2: Department heads get department + crew management
            ['roles_count' => 3, 'role_types' => ['management']],

            // Pattern 3: Document managers
            ['roles_count' => 2, 'role_types' => ['document']],

            // Pattern 4: Master data managers
            ['roles_count' => 4, 'role_types' => ['master_data']],

            // Pattern 5: Viewers only
            ['roles_count' => 1, 'specific_roles' => ['Viewer']],

            // Pattern 6: Mixed permissions
            ['roles_count' => rand(2, 5), 'role_types' => ['mixed']],
        ];

        $createdCount = 0;

        // Special handling: Assign ALL roles to NOC admin (noc@neti.com.ph)
        $nocAdmin = User::where('email', 'noc@neti.com.ph')->first();
        if ($nocAdmin) {
            $nocAssignedCount = 0;
            foreach ($roles as $role) {
                $existing = AdminRole::where('user_id', $nocAdmin->id)
                    ->where('role_id', $role->id)
                    ->first();

                if (!$existing) {
                    AdminRole::create([
                        'user_id' => $nocAdmin->id,
                        'role_id' => $role->id,
                        'modified_by' => $nocAdmin->id,
                    ]);
                    $nocAssignedCount++;
                    $this->command->info("✅ Assigned role '{$role->name}' to NOC admin (noc@neti.com.ph)");
                }
            }

            if ($nocAssignedCount > 0) {
                $this->command->info("✅ Total roles assigned to NOC admin: {$nocAssignedCount}");
            } else {
                $this->command->info("ℹ️  NOC admin already has all roles assigned");
            }
        }

        foreach ($adminUsers as $index => $user) {
            // Skip NOC admin as it already has all roles
            if ($user->email === 'noc@neti.com.ph') {
                continue;
            }
            // Select pattern based on user index
            $patternIndex = $index % count($assignmentPatterns);
            $pattern = $assignmentPatterns[$patternIndex];

            $rolesToAssign = [];

            // Handle specific roles
            if (isset($pattern['specific_roles'])) {
                foreach ($pattern['specific_roles'] as $roleName) {
                    $role = $roles->firstWhere('name', $roleName);
                    if ($role) {
                        $rolesToAssign[] = $role;
                    }
                }
            }

            // Handle role types
            if (isset($pattern['role_types'])) {
                $roleType = $pattern['role_types'][0];

                switch ($roleType) {
                    case 'management':
                        $managementRoles = $roles->filter(function ($role) {
                            return str_contains($role->name, 'Crew') ||
                                str_contains($role->name, 'Department') ||
                                str_contains($role->name, 'Admin User');
                        });
                        $rolesToAssign = $managementRoles->random(min($pattern['roles_count'], $managementRoles->count()))->all();
                        break;

                    case 'document':
                        $documentRoles = $roles->filter(function ($role) {
                            return str_contains($role->name, 'Document');
                        });
                        $rolesToAssign = $documentRoles->random(min($pattern['roles_count'], $documentRoles->count()))->all();
                        break;

                    case 'master_data':
                        $masterDataRoles = $roles->filter(function ($role) {
                            return str_contains($role->name, 'Vessel') ||
                                str_contains($role->name, 'Fleet') ||
                                str_contains($role->name, 'Rank') ||
                                str_contains($role->name, 'Location') ||
                                str_contains($role->name, 'Nationality') ||
                                str_contains($role->name, 'University');
                        });
                        $rolesToAssign = $masterDataRoles->random(min($pattern['roles_count'], $masterDataRoles->count()))->all();
                        break;

                    case 'mixed':
                        $rolesToAssign = $roles->random(min($pattern['roles_count'], $roles->count()))->all();
                        break;
                }
            }

            // Create admin role assignments
            foreach ($rolesToAssign as $role) {
                $existing = AdminRole::where('user_id', $user->id)
                    ->where('role_id', $role->id)
                    ->first();

                if (!$existing) {
                    AdminRole::create([
                        'user_id' => $user->id,
                        'role_id' => $role->id,
                    ]);
                    $createdCount++;
                    $this->command->info("Assigned role '{$role->name}' to user {$user->email}");
                }
            }
        }

        $this->command->info("AdminRoleSeeder completed! Created {$createdCount} role assignments.");
    }
}
