<?php

namespace Database\Seeders;

use App\Models\Program;
use Illuminate\Database\Seeder;

class ProgramSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $programs = [
            'NTMA Cadetship',
            'NYK-PANAMA Cadetship',
            'OJT Program',
            'Maritime Ratings Program (MRP)',
            'ETO Development Program (EDP)'
        ];

        foreach ($programs as $programName) {
            Program::updateOrCreate(
                ['name' => $programName],
                ['name' => $programName]
            );
        }
    }
}