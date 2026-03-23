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
            ['name' => 'NYK-Fil Ship Management, Inc.'],
            ['name' => 'NYK-Fil Maritime E-Training, Inc.'],
            ['name' => 'NYK Bulk & Projects Carriers Ltd.'],
            ['name' => 'NYK Line (Asia) Pte. Ltd.'],
            ['name' => 'NYK Shipmanagement Pte. Ltd.'],
            ['name' => 'Yusen Logistics Co., Ltd.'],
            ['name' => 'MTI Co., Ltd.'],
            ['name' => 'NYK Cool AB'],
        ];

        foreach ($companies as $company) {
            Company::firstOrCreate(['name' => $company['name']], $company);
        }

        $this->command->info('Companies seeded successfully!');
    }
}
