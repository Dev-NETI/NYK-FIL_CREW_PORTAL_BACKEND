<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserContact;
use App\Models\Address;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class UserContactSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('en_PH'); // Philippine locale
        
        // Get all users who don't have contacts yet
        $users = User::whereDoesntHave('contacts')->get();
        $addresses = Address::pluck('id')->toArray();
        
        foreach ($users as $user) {
            UserContact::create([
                'user_id' => $user->id,
                'mobile_number' => '+639' . $faker->numerify('#########'), // Philippine mobile format
                'alternate_phone' => $faker->optional(0.5)->phoneNumber, // 50% chance
                'email_personal' => $faker->optional(0.7)->safeEmail, // 70% chance
                'permanent_address_id' => $faker->optional(0.8)->randomElement($addresses), // 80% chance
                'current_address_id' => $faker->optional(0.6)->randomElement($addresses), // 60% chance
                'emergency_contact_name' => $faker->name,
                'emergency_contact_phone' => '+639' . $faker->numerify('#########'),
                'emergency_contact_relationship' => $faker->randomElement([
                    'spouse', 'parent', 'sibling', 'child', 'relative', 'friend'
                ]),
                'created_at' => $user->created_at,
                'updated_at' => now(),
            ]);
        }
        
        $this->command->info('User contacts seeded successfully!');
    }
}
