<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\Region;
use App\Models\Province;
use Illuminate\Database\Seeder;

class CitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $jsonPath = public_path('refcitymun_202510171334.json');

        if (!file_exists($jsonPath)) {
            $this->command->error('City JSON file not found at: ' . $jsonPath);
            return;
        }

        $jsonContent = file_get_contents($jsonPath);
        $data = json_decode($jsonContent, true);

        if (!$data || !isset($data['refcitymun'])) {
            $this->command->error('Invalid JSON format in city file');
            return;
        }

        $cities = $data['refcitymun'];
        $total = count($cities);
        $this->command->info("Processing {$total} cities/municipalities...");

        $processed = 0;
        $errors = 0;

        foreach ($cities as $cityData) {
            try {
                $region = Region::where('reg_code', $cityData['regDesc'])->first();
                $province = Province::where('prov_code', $cityData['provCode'])->first();

                if ($region && $province) {
                    City::create([
                        'psgc_code' => $cityData['psgcCode'],
                        'citymun_desc' => $cityData['citymunDesc'],
                        'reg_code' => $cityData['regDesc'],
                        'prov_code' => $cityData['provCode'],
                        'citymun_code' => $cityData['citymunCode'],
                    ]);
                    $processed++;
                } else {
                    $errors++;
                    $this->command->warn("Missing parent record for city: {$cityData['citymunDesc']} (Code: {$cityData['citymunCode']})");
                }
            } catch (\Exception $e) {
                $errors++;
                $this->command->error("Error processing city {$cityData['citymunDesc']}: " . $e->getMessage());
            }

            if (($processed + $errors) % 100 == 0) {
                $this->command->info("Processed: {$processed}, Errors: {$errors}");
            }
        }

        $this->command->info("City seeding completed. Processed: {$processed}, Errors: {$errors}");
    }
}
