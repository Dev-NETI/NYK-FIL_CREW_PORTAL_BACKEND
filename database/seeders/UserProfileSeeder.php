<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Fleet;
use App\Models\Rank;
use App\Models\User;
use App\Models\UserProfile;
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

        // Pre-load IDs for rank, fleet, company lookups
        $rankIds    = Rank::pluck('id')->toArray();
        $fleetIds   = Fleet::pluck('id')->toArray();
        $companyIds = Company::pluck('id')->toArray();

        // Philippine common names for more realistic data
        $maleFirstNames = [
            'Jose',
            'Juan',
            'Antonio',
            'Pedro',
            'Manuel',
            'Ricardo',
            'Roberto',
            'Rafael',
            'Carlos',
            'Daniel',
            'Miguel',
            'Francisco',
            'Luis',
            'Mario',
            'Fernando',
            'Alejandro',
            'Eduardo',
            'Sergio',
            'Rodolfo',
            'Alberto',
            'Romeo',
            'Dennis',
            'Richard',
            'Reynaldo',
            'Armando',
            'Edgar',
            'Arturo',
            'Ernesto',
        ];

        $femaleFirstNames = [
            'Maria',
            'Ana',
            'Carmen',
            'Rosa',
            'Elena',
            'Luz',
            'Gloria',
            'Teresa',
            'Patricia',
            'Esperanza',
            'Cristina',
            'Sandra',
            'Leticia',
            'Margarita',
            'Rosario',
            'Victoria',
            'Angeles',
            'Dolores',
            'Guadalupe',
            'Isabel',
            'Josefina',
            'Pilar',
            'Concepcion',
            'Remedios',
            'Elizabeth',
            'Jennifer',
        ];

        $lastNames = [
            'Santos',
            'Reyes',
            'Cruz',
            'Bautista',
            'Ocampo',
            'Garcia',
            'Mendoza',
            'Torres',
            'Tomas',
            'Andres',
            'Marquez',
            'Robles',
            'Gutierrez',
            'Gonzales',
            'Ramos',
            'Flores',
            'Rivera',
            'Gomez',
            'Fernandez',
            'Perez',
            'Rosales',
            'Morales',
            'Jimenez',
            'Herrera',
            'Medina',
            'Aguilar',
            'Castillo',
            'Vargas',
        ];

        // Create full profiles for users who don't have one yet
        foreach ($users as $user) {
            $gender    = $faker->randomElement(['male', 'female']);
            $firstName = $gender === 'male'
                ? $faker->randomElement($maleFirstNames)
                : $faker->randomElement($femaleFirstNames);

            $age       = $faker->numberBetween(22, 65);
            $birthDate = now()->subYears($age)->subDays($faker->numberBetween(0, 365));

            UserProfile::create([
                'user_id'      => $user->id,
                'crew_id'      => $user->is_crew ? 'CR' . str_pad($user->id, 6, '0', STR_PAD_LEFT) : null,
                'first_name'   => $firstName,
                'middle_name'  => $faker->optional(0.8)->randomElement(
                    array_merge($maleFirstNames, $femaleFirstNames)
                ),
                'last_name'    => $faker->randomElement($lastNames),
                'suffix'       => $faker->optional(0.15)->randomElement(['Jr.', 'Sr.', 'III', 'IV', 'V']),
                'birth_date'   => $birthDate->format('Y-m-d'),
                'age'          => $age,
                'gender'       => ucfirst($gender),
                'nationality'  => 'Filipino',
                'civil_status' => $faker->randomElement(['Single', 'Married', 'Widowed']),
                'religion'     => $faker->randomElement([
                    'Roman Catholic',
                    'Protestant',
                    'Born Again Christian',
                    'Islam',
                    'Other',
                ]),
                'rank_id'     => $user->is_crew && !empty($rankIds) ? $faker->randomElement($rankIds) : null,
                'fleet_id'    => $user->is_crew && !empty($fleetIds) ? $faker->randomElement($fleetIds) : null,
                'company_id'  => $user->is_crew && !empty($companyIds) ? $faker->randomElement($companyIds) : null,
                'created_at'  => $user->created_at,
                'updated_at'  => now(),
            ]);
        }

        // Fill rank_id / fleet_id / company_id on existing crew profiles that are missing them
        // (UserSeeder creates profiles without these fields)
        $existingCrewProfiles = UserProfile::whereHas('user', fn ($q) => $q->where('is_crew', 1))
            ->where(function ($q) {
                $q->whereNull('rank_id')
                  ->orWhereNull('fleet_id')
                  ->orWhereNull('company_id');
            })
            ->get();

        foreach ($existingCrewProfiles as $profile) {
            $profile->update([
                'rank_id'    => $profile->rank_id    ?? (!empty($rankIds)    ? $faker->randomElement($rankIds)    : null),
                'fleet_id'   => $profile->fleet_id   ?? (!empty($fleetIds)   ? $faker->randomElement($fleetIds)   : null),
                'company_id' => $profile->company_id ?? (!empty($companyIds) ? $faker->randomElement($companyIds) : null),
            ]);
        }

        $this->command->info(
            'User profiles seeded successfully! Created ' . $users->count() .
            ', updated ' . $existingCrewProfiles->count() . ' existing profiles.'
        );
    }
}
