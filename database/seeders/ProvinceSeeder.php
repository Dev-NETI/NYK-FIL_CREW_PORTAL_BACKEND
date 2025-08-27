<?php

namespace Database\Seeders;

use App\Models\Province;
use App\Models\Region;
use Illuminate\Database\Seeder;

class ProvinceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $provincesData = [
            // Luzon
            ['region' => 'REGION I', 'province' => 'ILOCOS NORTE'],
            ['region' => 'REGION I', 'province' => 'LA UNION'],
            ['region' => 'REGION II', 'province' => 'NUEVA VIZCAYA'],
            ['region' => 'REGION III', 'province' => 'NUEVA ECIJA'],
            ['region' => 'REGION III', 'province' => 'BULACAN'],
            ['region' => 'REGION III', 'province' => 'PAMPANGA'],
            ['region' => 'REGION III', 'province' => 'ZAMBALES'],
            ['region' => 'REGION IV-A', 'province' => 'LAGUNA'],
            ['region' => 'REGION IV-A', 'province' => 'CAVITE'],
            ['region' => 'REGION IV-A', 'province' => 'BATANGAS'],
            ['region' => 'REGION IV-A', 'province' => 'RIZAL'],
            ['region' => 'REGION IV-A', 'province' => 'QUEZON'],
            ['region' => 'REGION V', 'province' => 'CAMARINES NORTE'],
            ['region' => 'CAR', 'province' => 'IFUGAO'],
            ['region' => 'CAR', 'province' => 'BENGUET'],
            ['region' => 'NCR', 'province' => 'METRO MANILA'],
            // Visayas
            ['region' => 'REGION VI', 'province' => 'ILOILO'],
            ['region' => 'REGION VI', 'province' => 'CAPIZ'],
            ['region' => 'REGION VI', 'province' => 'NEGROS OCCIDENTAL'],
            ['region' => 'REGION VI', 'province' => 'GUIMARAS'],
            ['region' => 'REGION VI', 'province' => 'AKLAN'],
            ['region' => 'REGION VI', 'province' => 'ANTIQUE'],
            ['region' => 'REGION VII', 'province' => 'CEBU'],
            ['region' => 'REGION VII', 'province' => 'BOHOL'],
            ['region' => 'REGION VIII', 'province' => 'SOUTHERN LEYTE'],
            ['region' => 'REGION VIII', 'province' => 'E. SAMAR'],
            ['region' => 'REGION VIII', 'province' => 'SAMAR'],
            ['region' => 'REGION VIII', 'province' => 'NORTHERN SAMAR'],
            // Mindanao
            ['region' => 'REGION IX', 'province' => 'ZAMBOANGA DEL NORTE'],
            ['region' => 'REGION X', 'province' => 'LANAO DEL NORTE'],
            ['region' => 'REGION XI', 'province' => 'DAVAO DEL NORTE'],
            ['region' => 'REGION XI', 'province' => 'DAVAO DEL SUR'],
            ['region' => 'REGION XIII', 'province' => 'AGUSAN DEL NORTE'],
        ];

        foreach ($provincesData as $data) {
            $region = Region::where('name', $data['region'])->first();

            if ($region) {
                Province::firstOrCreate([
                    'name' => $data['province'],
                    'region_id' => $region->id,
                ]);
            }
        }
    }
}
