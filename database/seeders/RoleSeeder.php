<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            ['name' => 'Dashboard'],
            ['name' => 'Crew Management'],
            ['name' => 'Manage Crew Basic Info'],
            ['name' => 'Manage Crew Physical Info'],
            ['name' => 'Manage Crew Contact Info'],
            ['name' => 'Manage Crew Employment Info'],
            ['name' => 'Manage Crew Education'],
            ['name' => 'Document Approval'],
            ['name' => 'Employment Document Approval'],
            ['name' => 'Travel Document Approval'],
            ['name' => 'Inquiries'],
            ['name' => 'Reports'],
            ['name' => 'User Management'],
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role['name']], $role);
        }
    }
}
