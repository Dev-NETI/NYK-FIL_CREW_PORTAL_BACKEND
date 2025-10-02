<?php

namespace Database\Seeders;

use App\Models\CertificateDocument;
use App\Models\CertificateDocumentType;
use App\Models\UserProfile;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CertificateDocumentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get sample crew profiles and document types
        $crewProfiles = UserProfile::take(5)->get();
        $documentTypes = CertificateDocumentType::all();

        if ($crewProfiles->isEmpty() || $documentTypes->isEmpty()) {
            return; // Skip seeding if no crew profiles or document types exist
        }

        $certificates = [
            'Basic Safety Training (BST)',
            'Advanced Fire Fighting',
            'Medical First Aid',
            'Proficiency in Survival Craft',
            'Ship Security Officer',
            'GMDSS General Operator',
        ];

        $issuingAuthorities = [
            'Maritime Training Center Manila',
            'Philippine Merchant Marine Academy',
            'MARINA',
            'TESDA',
            'NYK-TDG Maritime Academy',
        ];

        foreach ($crewProfiles as $crew) {
            // Create 2-4 random certificates for each crew member
            $numberOfCerts = fake()->numberBetween(2, 4);

            for ($i = 0; $i < $numberOfCerts; $i++) {
                CertificateDocument::create([
                    'crew_id' => $crew->id,
                    'certificate_document_type_id' => $documentTypes->random()->id,
                    'certificate' => fake()->randomElement($certificates),
                    'certificate_no' => fake()->bothify('CERT-####-???-####'),
                    'issuing_authority' => fake()->randomElement($issuingAuthorities),
                    'date_issued' => fake()->dateTimeBetween('-5 years', '-6 months'),
                    'expiry_date' => fake()->dateTimeBetween('+6 months', '+5 years'),
                    'modified_by' => 'System Seeder',
                ]);
            }
        }
    }
}
