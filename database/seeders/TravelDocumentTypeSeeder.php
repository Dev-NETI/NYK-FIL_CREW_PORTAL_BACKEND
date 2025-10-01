<?php

namespace Database\Seeders;

use App\Models\TravelDocumentType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TravelDocumentTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $documentTypes = [
            'Passport',
            'Seafarer\'s Identification and Record Book (SIRB)',
            'Seafarer\'s Identity Document (SID)',
        ];

        foreach ($documentTypes as $type) {
            TravelDocumentType::create([
                'name' => $type,
            ]);
        }
    }
}
