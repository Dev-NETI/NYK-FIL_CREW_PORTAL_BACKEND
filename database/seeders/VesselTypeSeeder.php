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
            'TANKER - LPG',
            'CONTAINER',
            'TANKER - LNG',
            'PCTC',
            'CABLE SHIP',
            'BULK - VLOC',
            'DEEP SEA DRILLING VESSEL',
            'PCC',
            'BULK - WCC',
            'BULK - OVERPNMX',
            'BULK - CAPE',
            'TANKER - VLCC',
            'BULK - HANDY',
            'TANKER - OIL/CHEMICAL',
            'BULK - PANAMAX',
            'SEISMIC VESSEL',
        ];

        foreach ($vesselTypes as $type) {
            VesselType::firstOrCreate([
                'name' => $type,
            ]);
        }
    }
}
