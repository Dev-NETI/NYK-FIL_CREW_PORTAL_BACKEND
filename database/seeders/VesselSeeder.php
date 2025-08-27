<?php

namespace Database\Seeders;

use App\Models\Vessel;
use App\Models\VesselType;
use Illuminate\Database\Seeder;

class VesselSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $vesselsData = [
            ['vessel_id' => 1, 'name' => 'LAUREL PRIME', 'type' => 'TANKER - LPG'],
            ['vessel_id' => 2, 'name' => 'ONE COLUMBA', 'type' => 'CONTAINER'],
            ['vessel_id' => 4, 'name' => 'GAS AMETHYST', 'type' => 'TANKER - LPG'],
            ['vessel_id' => 5, 'name' => 'LNG JUPITER', 'type' => 'TANKER - LNG'],
            ['vessel_id' => 7, 'name' => 'LIBRA LEADER', 'type' => 'PCTC'],
            ['vessel_id' => 8, 'name' => 'PACIFIC ENLIGHTEN', 'type' => 'TANKER - LNG'],
            ['vessel_id' => 9, 'name' => 'AL KHOR', 'type' => 'TANKER - LNG'],
            ['vessel_id' => 10, 'name' => 'LNG JAMAL', 'type' => 'TANKER - LNG'],
            ['vessel_id' => 11, 'name' => 'ONE WREN', 'type' => 'CONTAINER'],
            ['vessel_id' => 13, 'name' => 'DV CHIKYU', 'type' => 'DEEP SEA DRILLING VESSEL'],
            ['vessel_id' => 14, 'name' => 'SUBARU', 'type' => 'CABLE SHIP'],
            ['vessel_id' => 15, 'name' => 'GRACE ACACIA', 'type' => 'TANKER - LNG'],
            ['vessel_id' => 16, 'name' => 'PARABURDOO', 'type' => 'BULK - VLOC'],
            ['vessel_id' => 17, 'name' => 'CETUS LEADER', 'type' => 'PCTC'],
            ['vessel_id' => 20, 'name' => 'CASSIOPEIA LEADER', 'type' => 'PCC'],
            ['vessel_id' => 21, 'name' => 'DOHA', 'type' => 'TANKER - LNG'],
            ['vessel_id' => 23, 'name' => 'SHINSHU MARU', 'type' => 'TANKER - LNG'],
            ['vessel_id' => 24, 'name' => 'WHITE PRINCESS', 'type' => 'BULK - WCC'],
            ['vessel_id' => 25, 'name' => 'TANSA', 'type' => 'SEISMIC VESSEL'],
            ['vessel_id' => 26, 'name' => 'TENRYU', 'type' => 'TANKER - VLCC'],
            ['vessel_id' => 27, 'name' => 'VELA LEADER', 'type' => 'PCTC'],
            ['vessel_id' => 28, 'name' => 'PACIFIC NOTUS', 'type' => 'TANKER - LNG'],
            ['vessel_id' => 29, 'name' => 'RISING SUN', 'type' => 'BULK - OVERPNMX'],
            ['vessel_id' => 30, 'name' => 'NBA MILLET', 'type' => 'BULK - OVERPNMX'],
            ['vessel_id' => 31, 'name' => 'PAVO LEADER', 'type' => 'PCTC'],
            ['vessel_id' => 32, 'name' => 'BUSHU MARU', 'type' => 'TANKER - LNG'],
            ['vessel_id' => 36, 'name' => 'SAKURA LEADER', 'type' => 'PCC'],
            ['vessel_id' => 37, 'name' => 'CS VEGA II', 'type' => 'CABLE SHIP'],
            ['vessel_id' => 38, 'name' => 'PACIFIC MIMOSA', 'type' => 'TANKER - LNG'],
            ['vessel_id' => 39, 'name' => 'ENERGY GLORY', 'type' => 'TANKER - LNG'],
            ['vessel_id' => 41, 'name' => 'TAHAROA PROVIDENCE', 'type' => 'BULK - CAPE'],
            ['vessel_id' => 42, 'name' => 'ANDROMEDA LEADER', 'type' => 'PCC'],
            ['vessel_id' => 43, 'name' => 'ASAHI MARU', 'type' => 'BULK - OVERPNMX'],
            ['vessel_id' => 44, 'name' => 'EMERALD HORIZON', 'type' => 'BULK - CAPE'],
            ['vessel_id' => 45, 'name' => 'IKIGAI', 'type' => 'TANKER - OIL/CHEMICAL'],
            ['vessel_id' => 46, 'name' => 'PACIFIC ARCADIA', 'type' => 'TANKER - LNG'],
            ['vessel_id' => 47, 'name' => 'TANGO', 'type' => 'TANKER - VLCC'],
            ['vessel_id' => 49, 'name' => 'TAITAR NO. 3', 'type' => 'TANKER - LNG'],
            ['vessel_id' => 50, 'name' => 'MARVEL FALCON', 'type' => 'TANKER - LNG'],
            ['vessel_id' => 52, 'name' => 'QUEST KIRISHIMA', 'type' => 'TANKER - LNG'],
            ['vessel_id' => 53, 'name' => 'NEW KEY', 'type' => 'BULK - HANDY'],
            ['vessel_id' => 54, 'name' => 'SHOHAKU', 'type' => 'BULK - OVERPNMX'],
            ['vessel_id' => 55, 'name' => 'SOL EXPLORER', 'type' => 'BULK - PANAMAX'],
            ['vessel_id' => 57, 'name' => 'DIAMOND GAS ORCHID', 'type' => 'TANKER - LNG'],
            ['vessel_id' => 58, 'name' => 'TOWA MARU', 'type' => 'TANKER - VLCC'],
            ['vessel_id' => 59, 'name' => 'ONE APUS', 'type' => 'CONTAINER'],
            ['vessel_id' => 60, 'name' => 'LNG OGUN', 'type' => 'TANKER - LNG'],
            ['vessel_id' => 62, 'name' => 'KING QUEST', 'type' => 'PCTC'],
            ['vessel_id' => 63, 'name' => 'TSUGARU', 'type' => 'TANKER - VLCC'],
            ['vessel_id' => 64, 'name' => 'MOONLIGHT DOLPHIN', 'type' => 'BULK - CAPE'],
            ['vessel_id' => 65, 'name' => 'ORE NOUMEA', 'type' => 'BULK - VLOC'],
            ['vessel_id' => 70, 'name' => 'LNG ADVENTURE', 'type' => 'TANKER - LNG'],
            ['vessel_id' => 105, 'name' => 'TATESHINA', 'type' => 'TANKER - VLCC'],
            ['vessel_id' => 126, 'name' => 'NEW LEGACY', 'type' => 'BULK - HANDY'],
            ['vessel_id' => 160, 'name' => 'FRONTIER DISCOVERY', 'type' => 'BULK - CAPE'],
            ['vessel_id' => 161, 'name' => 'GRACE EMILIA', 'type' => 'TANKER - LNG'],
            ['vessel_id' => 162, 'name' => 'NEW LEADER', 'type' => 'BULK - HANDY'],
            ['vessel_id' => 174, 'name' => 'SHOYO', 'type' => 'BULK - PANAMAX'],
        ];

        foreach ($vesselsData as $data) {
            $vesselType = VesselType::where('name', $data['type'])->first();

            if ($vesselType) {
                Vessel::firstOrCreate([
                    'vessel_id' => $data['vessel_id'],
                    'name' => $data['name'],
                    'vessel_type_id' => $vesselType->id,
                ]);
            }
        }
    }
}
