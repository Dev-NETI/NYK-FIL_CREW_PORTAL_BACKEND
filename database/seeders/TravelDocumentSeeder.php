<?php

namespace Database\Seeders;

use App\Models\TravelDocument;
use App\Models\TravelDocumentType;
use App\Models\UserProfile;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TravelDocumentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get sample crew profiles and document types
        $crewProfiles = UserProfile::take(5)->get();
        $documentTypes = TravelDocumentType::all();

        if ($crewProfiles->isEmpty() || $documentTypes->isEmpty()) {
            return; // Skip seeding if no crew profiles or document types exist
        }

        foreach ($crewProfiles as $crew) {
            // Create a passport for each crew member
            $passportType = $documentTypes->where('name', 'Passport')->first();
            if ($passportType) {
                TravelDocument::create([
                    'crew_id' => $crew->id,
                    'id_no' => 'P' . fake()->numerify('########'),
                    'travel_document_type_id' => $passportType->id,
                    'place_of_issue' => fake()->city() . ', Philippines',
                    'date_of_issue' => fake()->dateTimeBetween('-5 years', '-1 year'),
                    'expiration_date' => fake()->dateTimeBetween('+1 year', '+5 years'),
                    'remaining_pages' => fake()->numberBetween(5, 30),
                    'modified_by' => 'System Seeder',
                ]);
            }

            // Randomly create SIRB or SID for some crew members
            if (fake()->boolean(70)) {
                $sirbType = $documentTypes->where('name', 'like', '%SIRB%')->first();
                if ($sirbType) {
                    TravelDocument::create([
                        'crew_id' => $crew->id,
                        'id_no' => 'SIRB' . fake()->numerify('######'),
                        'travel_document_type_id' => $sirbType->id,
                        'place_of_issue' => 'Manila, Philippines',
                        'date_of_issue' => fake()->dateTimeBetween('-3 years', '-1 month'),
                        'expiration_date' => fake()->dateTimeBetween('+1 year', '+3 years'),
                        'remaining_pages' => null,
                        'modified_by' => 'System Seeder',
                    ]);
                }
            }
        }
    }
}
