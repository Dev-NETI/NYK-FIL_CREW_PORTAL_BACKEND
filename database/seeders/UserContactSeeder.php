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
        
        // Philippine mobile prefixes for more realistic numbers
        $mobileNetworks = [
            '0905', '0906', '0915', '0916', '0917', '0926', '0927', '0935', '0936', '0937', '0938', '0939',
            '0908', '0918', '0919', '0920', '0921', '0928', '0929', '0930', '0938', '0939',
            '0907', '0909', '0910', '0912', '0930', '0946', '0947', '0948', '0949', '0950',
            '0813', '0817', '0905', '0906', '0915', '0916', '0917', '0926', '0927'
        ];
        
        // Common emergency contact relationships in Philippines
        $relationships = [
            'spouse', 'parent', 'mother', 'father', 'sibling', 'brother', 'sister',
            'child', 'son', 'daughter', 'relative', 'cousin', 'uncle', 'aunt',
            'friend', 'neighbor', 'colleague'
        ];
        
        foreach ($users as $user) {
            // Generate realistic Philippine mobile number
            $mobilePrefix = $faker->randomElement($mobileNetworks);
            $mobileNumber = '+63' . substr($mobilePrefix, 1) . $faker->numerify('#######');
            
            // Generate alternate phone (landline or another mobile)
            $alternatePhone = null;
            if ($faker->boolean(60)) { // 60% chance of having alternate phone
                if ($faker->boolean(70)) { // 70% chance it's a landline
                    $areaCode = $faker->randomElement(['02', '032', '033', '034', '035', '038', '043', '044', '045', '046', '047', '048', '049']);
                    $alternatePhone = '(' . $areaCode . ') ' . $faker->numerify('###-####');
                } else { // 30% chance it's another mobile
                    $altPrefix = $faker->randomElement($mobileNetworks);
                    $alternatePhone = '+63' . substr($altPrefix, 1) . $faker->numerify('#######');
                }
            }
            
            // Generate emergency contact
            $emergencyName = $faker->name;
            $emergencyPrefix = $faker->randomElement($mobileNetworks);
            $emergencyPhone = '+63' . substr($emergencyPrefix, 1) . $faker->numerify('#######');
            
            UserContact::create([
                'user_id' => $user->id,
                'mobile_number' => $mobileNumber,
                'alternate_phone' => $alternatePhone,
                'email_personal' => $faker->optional(0.75)->safeEmail, // 75% chance of personal email
                'permanent_address_id' => $faker->optional(0.85)->randomElement($addresses), // 85% chance
                'current_address_id' => $faker->optional(0.70)->randomElement($addresses), // 70% chance (many crew live away)
                'emergency_contact_name' => $emergencyName,
                'emergency_contact_phone' => $emergencyPhone,
                'emergency_contact_relationship' => $faker->randomElement($relationships),
                'created_at' => $user->created_at,
                'updated_at' => now(),
            ]);
        }
        
        $this->command->info('User contacts seeded successfully! Generated ' . $users->count() . ' contact records.');
    }
}
