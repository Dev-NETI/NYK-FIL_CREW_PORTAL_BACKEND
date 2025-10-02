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
        
        $bloodTypes = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
        $eyeColors = ['Brown', 'Black', 'Hazel', 'Green', 'Blue'];
        $hairColors = ['Black', 'Brown', 'Dark Brown', 'Light Brown', 'Gray', 'White'];
        
        $distinguishingMarks = [
            'Small scar on left hand',
            'Mole on right cheek',
            'Birthmark on shoulder',
            'Tattoo on arm',
            'Scar on forehead',
            'None'
        ];
        
        $medicalConditions = [
            'None',
            'Hypertension - controlled',
            'Diabetes - Type 2',
            'Asthma - mild',
            'Back pain - chronic',
            'Vision correction required',
            'Hearing aid required'
        ];
        
        foreach ($users as $user) {
            UserPhysicalTrait::create([
                'user_id' => $user->id,
                'height' => $faker->randomFloat(2, 150, 190), // Height in cm
                'weight' => $faker->randomFloat(2, 50, 120), // Weight in kg
                'blood_type' => $faker->randomElement($bloodTypes),
                'eye_color' => $faker->randomElement($eyeColors),
                'hair_color' => $faker->randomElement($hairColors),
                'distinguishing_marks' => $faker->randomElement($distinguishingMarks),
                'medical_conditions' => $faker->randomElement($medicalConditions),
                'created_at' => $user->created_at,
                'updated_at' => now(),
            ]);
        }
        
        $this->command->info('User physical traits seeded successfully!');
    }
}
