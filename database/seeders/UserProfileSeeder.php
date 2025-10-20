<?php

namespace Database\Seeders;

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
            'Ernesto'
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
            'Jennifer'
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
            'Vargas'
        ];

        foreach ($users as $user) {
            $gender = $faker->randomElement(['male', 'female']);
            $firstName = $gender === 'male'
                ? $faker->randomElement($maleFirstNames)
                : $faker->randomElement($femaleFirstNames);

            $age = $faker->numberBetween(22, 65);
            $birthDate = now()->subYears($age)->subDays($faker->numberBetween(0, 365));

            UserProfile::create([
                'crew_id' => $user->is_crew ? 'CR' . str_pad($user->id, 6, '0', STR_PAD_LEFT) : null,
                'first_name' => $firstName,
                'middle_name' => $faker->optional(0.8)->randomElement($maleFirstNames + $femaleFirstNames),
                'last_name' => $faker->randomElement($lastNames),
                'suffix' => $faker->optional(0.15)->randomElement(['Jr.', 'Sr.', 'III', 'IV', 'V']),
                'date_of_birth' => $birthDate->format('Y-m-d'),
                'age' => $age,
                'gender' => $gender,
                'created_at' => $user->created_at,
                'updated_at' => now(),
            ]);
        }

        $this->command->info('User profiles seeded successfully! Generated ' . $users->count() . ' profiles.');
    }
}
