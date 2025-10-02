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
        
        // Maritime industry salary ranges by rank category (USD per month)
        $salaryRanges = [
            'Master' => [4500, 6000],
            'Chief Officer' => [3500, 4500], 
            'Chief Engineer' => [3500, 4500],
            'Second Officer' => [2800, 3500],
            'Second Engineer' => [2800, 3500],
            'Third Officer' => [2200, 2800],
            'Third Engineer' => [2200, 2800],
            'Bosun' => [1800, 2400],
            'Able Seaman' => [1200, 1800],
            'Ordinary Seaman' => [900, 1400],
            'Oiler' => [1400, 1900],
            'Fitter' => [1500, 2000],
            'Cook' => [1300, 1800],
            'Electrical Engineer' => [2500, 3200],
            'Engine Assistant' => [1000, 1500],
        ];
        
        // Crew status distribution (realistic for maritime industry)
        $crewStatusDistribution = [
            'on_board' => 0.4,      // 40% currently on ships
            'on_vacation' => 0.35,   // 35% on vacation/shore leave
            'standby' => 0.20,       // 20% on standby
            'medical_leave' => 0.03, // 3% on medical leave
            'training' => 0.02,      // 2% in training
        ];
        
        // Hire status distribution
        $hireStatusDistribution = [
            're_hire' => 0.70,       // 70% are re-hires (experienced)
            'new_hire' => 0.20,      // 20% are new hires
            'promoted' => 0.10,      // 10% are promoted
        ];
        
        foreach ($crewUsers as $user) {
            // Determine rank-based salary
            $rank = $ranks ? Rank::find($faker->randomElement($ranks)) : null;
            $baseSalary = 1500; // Default
            
            if ($rank) {
                foreach ($salaryRanges as $rankType => $range) {
                    if (str_contains(strtolower($rank->name ?? ''), strtolower($rankType))) {
                        $baseSalary = $faker->numberBetween($range[0], $range[1]);
                        break;
                    }
                }
            }
            
            // Generate realistic dates
            $hireDate = $faker->dateTimeBetween('-15 years', '-3 months');
            $contractLength = $faker->numberBetween(6, 12); // months
            
            // Generate passport and seaman book with realistic formats
            $passportNumber = 'P' . $faker->numerify('########') . $faker->randomLetter();
            $seamanBookNumber = 'SB-' . $faker->numerify('####-####-####');
            
            UserEmployment::create([
                'user_id' => $user->id,
                'fleet_id' => $faker->randomElement($fleets),
                'rank_id' => $rank?->id,
                'crew_status' => $faker->randomElement(array_keys($crewStatusDistribution)),
                'hire_status' => $faker->randomElement(array_keys($hireStatusDistribution)),
                'hire_date' => $hireDate->format('Y-m-d'),
                'contract_start' => $faker->optional(0.8)->dateTimeBetween('-2 years', 'now')?->format('Y-m-d'),
                'contract_end' => $faker->optional(0.8)->dateTimeBetween('now', '+1 year')?->format('Y-m-d'),
                'passport_number' => $passportNumber,
                'passport_expiry' => $faker->dateTimeBetween('+6 months', '+10 years')->format('Y-m-d'),
                'seaman_book_number' => $seamanBookNumber,
                'seaman_book_expiry' => $faker->dateTimeBetween('+1 year', '+5 years')->format('Y-m-d'),
                'primary_allotee_id' => $faker->optional(0.75)->randomElement($allotees), // 75% have allotees
                'basic_salary' => $baseSalary,
                'overtime_rate' => $faker->optional(0.9)->randomFloat(2, 8, 25), // USD per hour
                'leave_pay' => $faker->optional(0.8)->randomFloat(2, 200, 800), // Monthly leave pay
                'employment_notes' => $faker->optional(0.25)->sentence(8), // 25% chance of notes
                'created_at' => $user->created_at,
                'updated_at' => now(),
            ]);
        }
        
        $this->command->info('User employment records seeded successfully! Generated ' . $crewUsers->count() . ' employment records.');
    }
}
