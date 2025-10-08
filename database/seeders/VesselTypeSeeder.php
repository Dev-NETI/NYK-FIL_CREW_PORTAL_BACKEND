<?php

namespace Database\Seeders;

use App\Models\VesselType;
use Illuminate\Database\Seeder;

class VesselTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $vesselTypes = [
            'RESEARCH',
            'DRILL',
            'BULK CAPE',
            'BULK OVER PANAMAX',
            'BULK HANDY',
            'BULK VLOC',
            'PCTC',
            'BULK PANAMAX',
            'BULK WCC',
            'CABLE SHIP',
            'VLOC',
            'LNG',
            'TANKER VLCC',
            'OIL/CHEMICAL TANKER',
            'LPG',
            'CONTAINER',
            'PCC'
        ];

        foreach ($vesselTypes as $type) {
            VesselType::firstOrCreate([
                'name' => $type,
            ]);
        }
    }
}
