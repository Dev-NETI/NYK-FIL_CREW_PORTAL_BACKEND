<?php

namespace Database\Seeders;

use App\Models\Barangay;
use App\Models\Region;
use App\Models\Province;
use App\Models\City;
use Illuminate\Database\Seeder;

class BarangaySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $jsonPath = public_path('refbrgy_202510171334.json');

        if (!file_exists($jsonPath)) {
            $this->command->error('Barangay JSON file not found at: ' . $jsonPath);
            return;
        }

        $jsonContent = file_get_contents($jsonPath);
        $data = json_decode($jsonContent, true);

        if (!$data || !isset($data['refbrgy'])) {
            $this->command->error('Invalid JSON format in barangay file');
            return;
        }

        $barangays = $data['refbrgy'];
        $total = count($barangays);
        $this->command->info("Processing {$total} barangays...");

        $processed = 0;
        $errors = 0;

        foreach ($barangays as $barangayData) {
            try {
                $region = Region::where('reg_code', $barangayData['regCode'])->first();
                $province = Province::where('prov_code', $barangayData['provCode'])->first();
                $city = City::where('citymun_code', $barangayData['citymunCode'])->first();

                if ($region && $province && $city) {
                    Barangay::create([
                        'brgy_code' => $barangayData['brgyCode'],
                        'brgy_desc' => $barangayData['brgyDesc'],
                        'reg_code' => $barangayData['regCode'],
                        'prov_code' => $barangayData['provCode'],
                        'citymun_code' => $barangayData['citymunCode'],
                    ]);
                    $processed++;
                } else {
                    $errors++;
                    $this->command->warn("Missing parent record for barangay: {$barangayData['brgyDesc']} (Code: {$barangayData['brgyCode']})");
                }
            } catch (\Exception $e) {
                $errors++;
                $this->command->error("Error processing barangay {$barangayData['brgyDesc']}: " . $e->getMessage());
            }

            if (($processed + $errors) % 1000 == 0) {
                $this->command->info("Processed: {$processed}, Errors: {$errors}");
            }
        }

        $this->command->info("Barangay seeding completed. Processed: {$processed}, Errors: {$errors}");
    }
}
