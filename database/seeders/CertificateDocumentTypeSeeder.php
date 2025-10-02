<?php

namespace Database\Seeders;

use App\Models\CertificateDocumentType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CertificateDocumentTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            'STCW Certificates',
            'Government Required',
            'NMC Training Certificate',
            'TESDA Certificate',
            'Other Training Certificate',
        ];

        foreach ($types as $type) {
            CertificateDocumentType::create([
                'name' => $type,
            ]);
        }
    }
}
