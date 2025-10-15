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
            // Master Data Management
            ['name' => 'Vessel Management'],
            ['name' => 'Fleet Management'],
            ['name' => 'Rank Management'],
            ['name' => 'Location Management'], // Islands, Regions, Provinces, Cities
            ['name' => 'Nationality Management'],
            ['name' => 'University Management'],
            ['name' => 'Department Management'],
            ['name' => 'Program Management'],

            // Crew Management
            ['name' => 'Crew Management'], // Full CRUD on crew users
            ['name' => 'Contract Management'],
            ['name' => 'Allotee Management'],
            ['name' => 'Address Management'],
            ['name' => 'Employment Records Management'],

            // Document Management
            ['name' => 'Employment Document Management'],
            ['name' => 'Travel Document Management'],
            ['name' => 'Certificate Document Management'],
            ['name' => 'Document Type Management'],

            // Admin Users
            ['name' => 'Admin User Management'],

            // System Roles
            ['name' => 'Super Admin'], // Full system access
            ['name' => 'Viewer'], // Read-only access
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role['name']], $role);
        }
    }
}
