<?php

namespace Database\Seeders;

use App\Models\Region;
use Illuminate\Database\Seeder;

class RegionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $regions = [
            [
                'id' => 1,
                'psgc_code' => '010000000',
                'reg_desc' => 'REGION I (ILOCOS REGION)',
                'reg_code' => '01'
            ],
            [
                'id' => 2,
                'psgc_code' => '020000000',
                'reg_desc' => 'REGION II (CAGAYAN VALLEY)',
                'reg_code' => '02'
            ],
            [
                'id' => 3,
                'psgc_code' => '030000000',
                'reg_desc' => 'REGION III (CENTRAL LUZON)',
                'reg_code' => '03'
            ],
            [
                'id' => 4,
                'psgc_code' => '040000000',
                'reg_desc' => 'REGION IV-A (CALABARZON)',
                'reg_code' => '04'
            ],
            [
                'id' => 5,
                'psgc_code' => '170000000',
                'reg_desc' => 'REGION IV-B (MIMAROPA)',
                'reg_code' => '17'
            ],
            [
                'id' => 6,
                'psgc_code' => '050000000',
                'reg_desc' => 'REGION V (BICOL REGION)',
                'reg_code' => '05'
            ],
            [
                'id' => 7,
                'psgc_code' => '060000000',
                'reg_desc' => 'REGION VI (WESTERN VISAYAS)',
                'reg_code' => '06'
            ],
            [
                'id' => 8,
                'psgc_code' => '070000000',
                'reg_desc' => 'REGION VII (CENTRAL VISAYAS)',
                'reg_code' => '07'
            ],
            [
                'id' => 9,
                'psgc_code' => '080000000',
                'reg_desc' => 'REGION VIII (EASTERN VISAYAS)',
                'reg_code' => '08'
            ],
            [
                'id' => 10,
                'psgc_code' => '090000000',
                'reg_desc' => 'REGION IX (ZAMBOANGA PENINSULA)',
                'reg_code' => '09'
            ],
            [
                'id' => 11,
                'psgc_code' => '100000000',
                'reg_desc' => 'REGION X (NORTHERN MINDANAO)',
                'reg_code' => '10'
            ],
            [
                'id' => 12,
                'psgc_code' => '110000000',
                'reg_desc' => 'REGION XI (DAVAO REGION)',
                'reg_code' => '11'
            ],
            [
                'id' => 13,
                'psgc_code' => '120000000',
                'reg_desc' => 'REGION XII (SOCCSKSARGEN)',
                'reg_code' => '12'
            ],
            [
                'id' => 14,
                'psgc_code' => '130000000',
                'reg_desc' => 'NATIONAL CAPITAL REGION (NCR)',
                'reg_code' => '13'
            ],
            [
                'id' => 15,
                'psgc_code' => '140000000',
                'reg_desc' => 'CORDILLERA ADMINISTRATIVE REGION (CAR)',
                'reg_code' => '14'
            ],
            [
                'id' => 16,
                'psgc_code' => '150000000',
                'reg_desc' => 'AUTONOMOUS REGION IN MUSLIM MINDANAO (ARMM)',
                'reg_code' => '15'
            ],
            [
                'id' => 17,
                'psgc_code' => '160000000',
                'reg_desc' => 'REGION XIII (Caraga)',
                'reg_code' => '16'
            ]
        ];

        foreach ($regions as $region) {
            Region::create($region);
        }
    }
}
