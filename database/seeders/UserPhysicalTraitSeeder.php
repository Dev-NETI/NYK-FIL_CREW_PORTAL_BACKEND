<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserPhysicalTrait;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class UserPhysicalTraitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('en_PH');

        // Get all users who don't have physical traits yet
        $users = User::whereDoesntHave('physicalTraits')->get();

        // Blood type distribution in Philippines (realistic percentages)
        $bloodTypeDistribution = [
            'O+' => 0.36,    // 36%
            'A+' => 0.27,    // 27% 
            'B+' => 0.25,    // 25%
            'AB+' => 0.06,   // 6%
            'O-' => 0.03,    // 3%
            'A-' => 0.02,    // 2%
            'B-' => 0.01,    // 1%
            'AB-' => 0.001   // 0.1%
        ];

        // Physical characteristics common in Philippines
        $eyeColors = ['Dark Brown', 'Brown', 'Black', 'Hazel'];
        $hairColors = ['Black', 'Dark Brown', 'Brown', 'Gray', 'Salt and Pepper', 'White'];

        $distinguishingMarks = [
            'None',
            'Small scar on hand',
            'Mole on face',
            'Birthmark on shoulder',
            'Small tattoo on arm',
            'Scar on forehead',
            'Mole on neck',
            'Birthmark on back',
            'Small scar on finger',
            'Freckles on face',
            'Dimple on cheek',
            'Small mole on arm',
            'Scar from childhood accident'
        ];

        // Common medical conditions for maritime workers
        $medicalConditions = [
            'None',
            'Hypertension - controlled with medication',
            'Diabetes Type 2 - controlled',
            'Mild asthma',
            'Chronic back pain',
            'Vision correction required (glasses/contacts)',
            'Hearing loss - mild',
            'Allergic rhinitis',
            'Gastric acid reflux',
            'High cholesterol - controlled',
            'Migraine headaches - occasional',
            'Skin allergies',
            'Joint pain - knees',
            'Sleep apnea - mild'
        ];

        foreach ($users as $user) {
            // Get user profile for gender-based physical traits
            $userProfile = $user->profile;
            $gender = $userProfile?->gender ?? 'male';
            $age = $userProfile?->age ?? $faker->numberBetween(25, 55);

            // Gender and age-appropriate height (cm)
            if ($gender === 'female') {
                $height = $faker->randomFloat(2, 145, 170); // Filipino female average
            } else {
                $height = $faker->randomFloat(2, 155, 180); // Filipino male average  
            }

            // Calculate BMI-appropriate weight
            $bmi = $faker->randomFloat(2, 18.5, 28); // Normal to slightly overweight
            $weight = round(($bmi * pow($height / 100, 2)), 2);

            // Age-appropriate hair color
            $hairColor = 'Black'; // Default for younger people
            if ($age > 45) {
                $hairColor = $faker->randomElement(['Black', 'Dark Brown', 'Gray', 'Salt and Pepper']);
            } elseif ($age > 55) {
                $hairColor = $faker->randomElement(['Gray', 'Salt and Pepper', 'White', 'Dark Brown']);
            }

            // Weighted blood type selection
            $bloodType = $faker->randomElement(array_keys($bloodTypeDistribution));

            UserPhysicalTrait::create([
                'user_id' => $user->id,
                'height' => $height,
                'weight' => $weight,
                'blood_type' => $bloodType,
                'eye_color' => $faker->randomElement($eyeColors),
                'hair_color' => $hairColor,
                'created_at' => $user->created_at,
                'updated_at' => now(),
            ]);
        }

        $this->command->info('User physical traits seeded successfully!');
    }
}
