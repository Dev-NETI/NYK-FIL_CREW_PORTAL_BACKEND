<?php

namespace Database\Seeders;

use App\Models\CertificateType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CertificateTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $certificateTypes = [
            'STCW Certificates',
            'Government Required',
            'NMC Training Certificate',
            'TESDA Certificate',
            'Other Training Certificate',
        ];

        foreach ($certificateTypes as $type) {
            CertificateType::create([
                'name' => $type,
            ]);
        }
    }
}
