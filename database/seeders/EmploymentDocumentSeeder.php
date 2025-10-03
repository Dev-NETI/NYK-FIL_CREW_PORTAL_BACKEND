<?php

namespace Database\Seeders;

use App\Models\EmploymentDocument;
use App\Models\EmploymentDocumentType;
use App\Models\UserProfile;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EmploymentDocumentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get sample crew profiles and document types
        $crewProfiles = UserProfile::take(10)->get();
        $documentTypes = EmploymentDocumentType::all();

        if ($crewProfiles->isEmpty() || $documentTypes->isEmpty()) {
            return; // Skip seeding if no crew profiles or document types exist
        }

        foreach ($crewProfiles as $crew) {
            // Create TIN for each crew member
            $tinType = $documentTypes->where('name', 'TIN')->first();
            if ($tinType) {
                EmploymentDocument::create([
                    'crew_id' => $crew->crew_id,
                    'employment_document_type_id' => $tinType->id,
                    'document_number' => fake()->numerify('###-###-###-###'),
                    'modified_by' => 'System Seeder',
                ]);
            }

            // Create SSS for each crew member
            $sssType = $documentTypes->where('name', 'SSS')->first();
            if ($sssType) {
                EmploymentDocument::create([
                    'crew_id' => $crew->crew_id,
                    'employment_document_type_id' => $sssType->id,
                    'document_number' => fake()->numerify('##-#######-#'),
                    'modified_by' => 'System Seeder',
                ]);
            }

            // Create PAG-IBIG for 80% of crew members
            if (fake()->boolean(80)) {
                $pagibigType = $documentTypes->where('name', 'PAG-IBIG')->first();
                if ($pagibigType) {
                    EmploymentDocument::create([
                        'crew_id' => $crew->crew_id,
                        'employment_document_type_id' => $pagibigType->id,
                        'document_number' => fake()->numerify('####-####-####'),
                        'modified_by' => 'System Seeder',
                    ]);
                }
            }

            // Create PHILHEALTH for 85% of crew members
            if (fake()->boolean(85)) {
                $philhealthType = $documentTypes->where('name', 'PHILHEALTH')->first();
                if ($philhealthType) {
                    EmploymentDocument::create([
                        'crew_id' => $crew->crew_id,
                        'employment_document_type_id' => $philhealthType->id,
                        'document_number' => fake()->numerify('##-#########-#'),
                        'modified_by' => 'System Seeder',
                    ]);
                }
            }

            // Create SRN for 60% of crew members
            if (fake()->boolean(60)) {
                $srnType = $documentTypes->where('name', 'SRN')->first();
                if ($srnType) {
                    EmploymentDocument::create([
                        'crew_id' => $crew->crew_id,
                        'employment_document_type_id' => $srnType->id,
                        'document_number' => 'SRN-' . fake()->numerify('##########'),
                        'modified_by' => 'System Seeder',
                    ]);
                }
            }
        }
    }
}
