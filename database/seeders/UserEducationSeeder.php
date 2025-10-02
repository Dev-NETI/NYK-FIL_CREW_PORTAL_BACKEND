<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserEducation;
use App\Models\University;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class UserEducationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('en_PH');
        
        // Get all users who don't have education records yet
        $users = User::whereDoesntHave('education')->get();
        $universities = University::pluck('id')->toArray();
        
        // Maritime degrees with different levels
        $maritimeDegrees = [
            'bachelor' => [
                'Bachelor of Science in Marine Transportation',
                'Bachelor of Science in Marine Engineering', 
                'Bachelor of Science in Naval Architecture and Marine Engineering',
                'Bachelor of Maritime Technology',
                'Bachelor of Science in Marine Transportation (Deck)',
                'Bachelor of Science in Marine Engineering (Engine)',
                'Bachelor of Science in Maritime Business and Management'
            ],
            'associate' => [
                'Associate in Marine Transportation',
                'Associate in Marine Engineering',
                'Marine Engineering Technology',
                'Maritime Technology',
                'Ship Management Technology'
            ],
            'vocational' => [
                'Maritime Technology Course',
                'Basic Seamanship Course',
                'Marine Engineering Course',
                'Navigation Technology',
                'Ship Operations Certificate'
            ]
        ];
        
        $otherDegrees = [
            'bachelor' => [
                'Bachelor of Science in Information Technology',
                'Bachelor of Science in Business Administration',
                'Bachelor of Arts in Communication',
                'Bachelor of Science in Psychology',
                'Bachelor of Science in Electrical Engineering',
                'Bachelor of Science in Mechanical Engineering',
                'Bachelor of Science in Civil Engineering',
                'Bachelor of Science in Computer Engineering'
            ],
            'associate' => [
                'Associate in Computer Technology',
                'Associate in Business Management',
                'Associate in Electronics Technology'
            ]
        ];
        
        // Comprehensive STCW and maritime certifications
        $stcwCertifications = [
            'STCW Basic Safety Training (BST)',
            'Standards of Training, Certification and Watchkeeping (STCW)',
            'Certificate of Competency (COC) - Officer of the Watch',
            'Certificate of Competency (COC) - Chief Mate',
            'Certificate of Competency (COC) - Master Mariner',
            'Certificate of Competency (COC) - Engineer Officer',
            'Certificate of Competency (COC) - Chief Engineer',
            'Certificate of Proficiency in Survival Craft and Rescue Boats',
            'Advanced Fire Fighting Certificate',
            'Medical First Aid Certificate',
            'Medical Care Certificate',
            'Ship Security Officer (SSO) Certificate',
            'Dangerous Goods Certificate',
            'ARPA (Automatic Radar Plotting Aids) Certificate',
            'GMDSS (Global Maritime Distress and Safety System) Certificate',
            'Dynamic Positioning (DP) Certificate',
            'Tanker Safety Certificate (Oil/Chemical/Gas)',
            'Bridge Resource Management (BRM)',
            'Engine Room Resource Management (ERM)',
            'Electronic Chart Display and Information System (ECDIS)'
        ];
        
        $additionalCertifications = [
            'ISO 9001 Quality Management',
            'ISM Code Certificate',
            'ISPS Code Certificate',
            'Crane Operation Certificate',
            'Welding Certificate (SMAW/GTAW)',
            'Refrigeration and Air Conditioning',
            'Computer Literacy Certificate',
            'English Proficiency Certificate'
        ];
        
        foreach ($users as $user) {
            $isCrew = $user->is_crew;
            
            // More realistic education level distribution for maritime industry
            if ($isCrew) {
                $educationLevel = $faker->randomElement([
                    'high_school' => 0.15,      // 15% high school only
                    'vocational' => 0.35,       // 35% vocational/technical
                    'associate' => 0.25,        // 25% associate degree
                    'bachelor' => 0.23,         // 23% bachelor's degree
                    'master' => 0.02            // 2% master's degree
                ]);
            } else {
                $educationLevel = $faker->randomElement([
                    'high_school' => 0.10,
                    'vocational' => 0.20,
                    'associate' => 0.20,
                    'bachelor' => 0.45,
                    'master' => 0.05
                ]);
            }
            
            // Select appropriate degree based on education level and crew status
            $degree = null;
            if (in_array($educationLevel, ['bachelor', 'master'])) {
                if ($isCrew) {
                    $degree = $faker->randomElement($maritimeDegrees['bachelor']);
                } else {
                    $degree = $faker->randomElement($otherDegrees['bachelor']);
                }
            } elseif ($educationLevel === 'associate') {
                if ($isCrew) {
                    $degree = $faker->randomElement($maritimeDegrees['associate']);
                } else {
                    $degree = $faker->randomElement($otherDegrees['associate']);
                }
            } elseif ($educationLevel === 'vocational' && $isCrew) {
                $degree = $faker->randomElement($maritimeDegrees['vocational']);
            }
            
            // Generate appropriate field of study
            $fieldOfStudy = null;
            if ($isCrew) {
                $fieldOfStudy = $faker->randomElement([
                    'Maritime Studies', 'Marine Engineering', 'Navigation Technology', 
                    'Seamanship', 'Marine Transportation', 'Ship Operations',
                    'Maritime Technology', 'Naval Architecture', 'Port Management'
                ]);
            } else {
                $fieldOfStudy = $faker->randomElement([
                    'Business Administration', 'Information Technology', 'Engineering', 
                    'Liberal Arts', 'Management', 'Finance', 'Human Resources',
                    'Communications', 'Marketing', 'Operations Management'
                ]);
            }
            
            // Generate certifications
            $certifications = '';
            if ($isCrew) {
                // Always include basic STCW for crew
                $requiredCerts = ['STCW Basic Safety Training (BST)'];
                $additionalCerts = $faker->randomElements($stcwCertifications, $faker->numberBetween(2, 6));
                $otherCerts = $faker->randomElements($additionalCertifications, $faker->numberBetween(0, 3));
                $allCerts = array_merge($requiredCerts, $additionalCerts, $otherCerts);
                $certifications = implode(', ', array_unique($allCerts));
            } else {
                // Non-crew may have some professional certifications
                if ($faker->boolean(60)) { // 60% chance
                    $nonCrewCerts = $faker->randomElements($additionalCertifications, $faker->numberBetween(1, 3));
                    $certifications = implode(', ', $nonCrewCerts);
                }
            }
            
            // Generate realistic graduation date based on user's age
            $userProfile = $user->profile;
            if ($userProfile && $userProfile->age) {
                $estimatedAge = $userProfile->age;
                $graduationAge = $educationLevel === 'master' ? 24 : 
                                ($educationLevel === 'bachelor' ? 22 :
                                ($educationLevel === 'associate' ? 20 : 18));
                $yearsAgo = max(1, $estimatedAge - $graduationAge);
                $graduationDate = $faker->dateTimeBetween("-{$yearsAgo} years", '-1 year');
            } else {
                $graduationDate = $faker->dateTimeBetween('-25 years', '-1 year');
            }
            
            UserEducation::create([
                'user_id' => $user->id,
                'graduated_school_id' => $faker->optional(0.92)->randomElement($universities), // 92% chance
                'date_graduated' => $graduationDate->format('Y-m-d'),
                'degree' => $degree,
                'field_of_study' => $fieldOfStudy,
                'gpa' => $faker->optional(0.75)->randomFloat(2, 1.75, 4.0), // 75% chance, realistic GPA range
                'education_level' => $educationLevel,
                'certifications' => $certifications ?: null,
                'additional_training' => $faker->optional(0.6)->sentence(12), // 60% chance
                'created_at' => $user->created_at,
                'updated_at' => now(),
            ]);
        }
        
        $this->command->info('User education records seeded successfully!');
    }
}
