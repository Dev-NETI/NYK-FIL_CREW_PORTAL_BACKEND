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
        
        $maritimeDegrees = [
            'Bachelor of Science in Marine Transportation',
            'Bachelor of Science in Marine Engineering',
            'Bachelor of Science in Naval Architecture',
            'Bachelor of Maritime Technology',
            'Associate in Marine Transportation',
            'Marine Engineering Technology'
        ];
        
        $otherDegrees = [
            'Bachelor of Science in Information Technology',
            'Bachelor of Science in Business Administration',
            'Bachelor of Arts in Communication',
            'Bachelor of Science in Psychology',
            'Bachelor of Science in Electrical Engineering',
            'Bachelor of Science in Mechanical Engineering'
        ];
        
        $certifications = [
            'STCW Basic Safety Training (BST)',
            'Standards of Training, Certification and Watchkeeping (STCW)',
            'Certificate of Competency (COC)',
            'Radio Operator Certificate',
            'Medical First Aid Certificate',
            'Fire Fighting and Fire Prevention',
            'Personal Survival Techniques',
            'Personal Safety and Social Responsibilities'
        ];
        
        foreach ($users as $user) {
            $isCrew = $user->is_crew;
            $educationLevel = $faker->randomElement(['high_school', 'vocational', 'bachelor', 'master']);
            
            UserEducation::create([
                'user_id' => $user->id,
                'graduated_school_id' => $faker->optional(0.9)->randomElement($universities), // 90% chance
                'date_graduated' => $faker->dateTimeBetween('-20 years', '-1 year')->format('Y-m-d'),
                'degree' => $educationLevel === 'bachelor' || $educationLevel === 'master' 
                    ? ($isCrew 
                        ? $faker->randomElement($maritimeDegrees) 
                        : $faker->randomElement($otherDegrees))
                    : null,
                'field_of_study' => $isCrew 
                    ? $faker->randomElement(['Maritime Studies', 'Marine Engineering', 'Navigation', 'Seamanship'])
                    : $faker->randomElement(['Business', 'Information Technology', 'Engineering', 'Liberal Arts']),
                'gpa' => $faker->optional(0.7)->randomFloat(2, 1.5, 4.0), // 70% chance, GPA 1.5-4.0
                'education_level' => $educationLevel,
                'certifications' => $isCrew 
                    ? implode(', ', $faker->randomElements($certifications, $faker->numberBetween(2, 5)))
                    : $faker->optional(0.4)->sentence(), // 40% chance for non-crew
                'additional_training' => $faker->optional(0.5)->sentence(10), // 50% chance
                'created_at' => $user->created_at,
                'updated_at' => now(),
            ]);
        }
        
        $this->command->info('User education records seeded successfully!');
    }
}
