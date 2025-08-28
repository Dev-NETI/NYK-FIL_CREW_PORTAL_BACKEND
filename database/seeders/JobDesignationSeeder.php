<?php

namespace Database\Seeders;

use App\Models\JobDesignation;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class JobDesignationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $designations = [
            ['name' => 'Fleet'],
            ['name' => 'Executive Assistant'],
            ['name' => 'Office of the Vice President'],
            ['name' => 'Fleet Manager'],
            ['name' => 'Chief Liason'],
            ['name' => 'Claims Officer'],
            ['name' => 'Liason Officer'],
            ['name' => 'Office of the General Manager'],
            ['name' => 'Assistant General Manager'],
            ['name' => 'Manning Executive Assistant'],
            ['name' => 'SGA Accountant'],
            ['name' => 'SGA Supervisor'],
            ['name' => 'SGA Manager'],
            ['name' => 'Chief Accountant'],
            ['name' => 'Payroll Supervisor'],
            ['name' => 'Manager'],
            ['name' => 'Payroll Accountant'],
            ['name' => 'Accounting Staff'],
            ['name' => 'Disbursement Supervisor'],
            ['name' => 'Comptroller'],
            ['name' => 'Chief Finance Officer'],
            ['name' => 'President'],
            ['name' => 'Vice President'],
        ];

        foreach ($designations as $designation) {
            JobDesignation::create($designation);
        }
    }
}
