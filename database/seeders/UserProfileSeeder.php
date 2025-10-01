<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class UserProfileSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('en_PH'); // Philippine locale
        
        // Get all users who don't have profiles yet
        $users = User::whereDoesntHave('profile')->get();
        
        foreach ($users as $user) {
            UserProfile::create([
                'user_id' => $user->id,
                'crew_id' => $user->is_crew ? 'CR' . str_pad($user->id, 6, '0', STR_PAD_LEFT) : null,
                'first_name' => $faker->firstName,
                'middle_name' => $faker->optional(0.7)->firstName, // 70% chance of having middle name
                'last_name' => $faker->lastName,
                'suffix' => $faker->optional(0.1)->randomElement(['Jr.', 'Sr.', 'III', 'IV']), // 10% chance
                'date_of_birth' => $faker->dateTimeBetween('-65 years', '-18 years')->format('Y-m-d'),
                'gender' => $faker->randomElement(['male', 'female']),
                'created_at' => $user->created_at,
                'updated_at' => now(),
            ]);
        }
        
        $this->command->info('User profiles seeded successfully!');
    }
}
