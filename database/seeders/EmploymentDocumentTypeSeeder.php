<?php

namespace Database\Seeders;

use App\Models\EmploymentDocumentType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EmploymentDocumentTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $documentTypes = [
            'TIN',
            'SSS',
            'PAG-IBIG',
            'PHILHEALTH',
            'SRN',
        ];

        foreach ($documentTypes as $type) {
            EmploymentDocumentType::create([
                'name' => $type,
            ]);
        }
    }
}
