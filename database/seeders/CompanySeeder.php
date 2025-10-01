<?php

namespace Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $companies = [
            ['name' => 'NYK-Fil Maritime E-Training, Inc.'],
            ['name' => 'NYK-Fil Ship Management Inc.'],
        ];

        foreach ($companies as $company) {
            Company::create($company);
        }
    }
}
