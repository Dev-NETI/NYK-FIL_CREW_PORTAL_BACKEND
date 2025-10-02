<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserEmployment;
use App\Models\Fleet;
use App\Models\Rank;
use App\Models\Allotee;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class UserEmploymentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('en_PH');
        
        // Get all crew users who don't have employment records yet
        $crewUsers = User::where('is_crew', true)
            ->whereDoesntHave('employment')
            ->get();
            
        $fleets = Fleet::pluck('id')->toArray();
        $ranks = Rank::pluck('id')->toArray();
        $allotees = Allotee::pluck('id')->toArray();
        
        foreach ($crewUsers as $user) {
            UserEmployment::create([
                'user_id' => $user->id,
                'fleet_id' => $faker->randomElement($fleets),
                'rank_id' => $faker->randomElement($ranks),
                'crew_status' => $faker->randomElement(['on_board', 'on_vacation', 'standby']),
                'hire_status' => $faker->randomElement(['new_hire', 're_hire', 'promoted']),
                'hire_date' => $faker->dateTimeBetween('-10 years', '-1 month')->format('Y-m-d'),
                'passport_number' => $faker->numerify('P########'), // Philippine passport format
                'passport_expiry' => $faker->dateTimeBetween('+1 year', '+10 years')->format('Y-m-d'),
                'seaman_book_number' => 'SB' . $faker->numerify('########'),
                'seaman_book_expiry' => $faker->dateTimeBetween('+6 months', '+5 years')->format('Y-m-d'),
                'primary_allotee_id' => $faker->optional(0.8)->randomElement($allotees), // 80% chance
                'basic_salary' => $faker->numberBetween(800, 4000), // USD monthly salary range
                'employment_notes' => $faker->optional(0.3)->sentence(), // 30% chance of notes
                'created_at' => $user->created_at,
                'updated_at' => now(),
            ]);
        }
        
        $this->command->info('User employment records seeded successfully!');
    }
}
