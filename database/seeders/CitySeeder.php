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
        $cities = [
            // JSON data for Philippine cities/municipalities should be added here
            // Each city should have: id, psgcCode, citymunDesc, regDesc, provCode, citymunCode
        ];

        foreach ($cities as $cityData) {
            $region = Region::where('reg_code', $cityData['reg_code'])->first();
            $province = Province::where('prov_code', $cityData['prov_code'])->first();

            if ($region && $province) {
                City::create([
                    'psgc_code' => $cityData['psgc_code'],
                    'citymun_desc' => $cityData['citymun_desc'],
                    'reg_code' => $cityData['reg_code'],
                    'prov_code' => $cityData['prov_code'],
                    'citymun_code' => $cityData['citymun_code'],
                    'region_id' => $region->id,
                    'province_id' => $province->id,
                ]);
            }
        }
    }
}
