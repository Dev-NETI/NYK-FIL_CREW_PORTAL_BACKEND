<?php

namespace Database\Seeders;

use App\Models\Contract;
use App\Models\User;
use App\Models\Vessel;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ContractSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $contractsData = [
            ['crew_id' => '219454', 'departure_date' => '2025-02-16', 'arrival_date' => '2025-10-13', 'vessel_id' => 1, 'duration' => 8],
            ['crew_id' => '219456', 'departure_date' => '2025-01-01', 'arrival_date' => '2025-07-27', 'vessel_id' => 2, 'duration' => 9],
            ['crew_id' => '219465', 'departure_date' => '2025-04-18', 'arrival_date' => '2025-12-13', 'vessel_id' => 4, 'duration' => 8],
            ['crew_id' => '219471', 'departure_date' => '2024-11-09', 'arrival_date' => '2025-08-05', 'vessel_id' => 5, 'duration' => 6],
            ['crew_id' => '219480', 'departure_date' => '2025-03-09', 'arrival_date' => '2025-12-03', 'vessel_id' => 7, 'duration' => 9],
            ['crew_id' => '219482', 'departure_date' => '2025-03-06', 'arrival_date' => '2025-09-01', 'vessel_id' => 8, 'duration' => 6],
            ['crew_id' => '219484', 'departure_date' => '2025-01-15', 'arrival_date' => '2025-08-12', 'vessel_id' => 9, 'duration' => 6],
            ['crew_id' => '219486', 'departure_date' => '2024-12-14', 'arrival_date' => '2025-04-12', 'vessel_id' => 10, 'duration' => 3],
            ['crew_id' => '219491', 'departure_date' => '2024-12-28', 'arrival_date' => '2025-09-23', 'vessel_id' => 11, 'duration' => 9],
            ['crew_id' => '219498', 'departure_date' => '2025-07-29', 'arrival_date' => '2025-08-27', 'vessel_id' => 13, 'duration' => 1],
            ['crew_id' => '219499', 'departure_date' => '2025-08-10', 'arrival_date' => '2025-09-08', 'vessel_id' => 14, 'duration' => 1],
            ['crew_id' => '219501', 'departure_date' => '2025-02-22', 'arrival_date' => '2025-08-20', 'vessel_id' => 15, 'duration' => 6],
            ['crew_id' => '219503', 'departure_date' => '2025-08-02', 'arrival_date' => '2026-04-28', 'vessel_id' => 16, 'duration' => 9],
            ['crew_id' => '219505', 'departure_date' => '2025-02-03', 'arrival_date' => '2025-10-30', 'vessel_id' => 17, 'duration' => 9],
            ['crew_id' => '219515', 'departure_date' => '2024-10-08', 'arrival_date' => '2025-08-03', 'vessel_id' => 20, 'duration' => 9],
            ['crew_id' => '219518', 'departure_date' => '2025-06-17', 'arrival_date' => '2025-12-13', 'vessel_id' => 21, 'duration' => 6],
            ['crew_id' => '219546', 'departure_date' => '2025-04-07', 'arrival_date' => '2025-10-03', 'vessel_id' => 23, 'duration' => 6],
            ['crew_id' => '219548', 'departure_date' => '2025-01-26', 'arrival_date' => '2025-10-22', 'vessel_id' => 24, 'duration' => 9],
            ['crew_id' => '219551', 'departure_date' => '2025-08-12', 'arrival_date' => '2025-09-10', 'vessel_id' => 25, 'duration' => 1],
            ['crew_id' => '219560', 'departure_date' => '2024-11-11', 'arrival_date' => '2025-07-08', 'vessel_id' => 26, 'duration' => 8],
            ['crew_id' => '219572', 'departure_date' => '2025-08-14', 'arrival_date' => '2026-05-10', 'vessel_id' => 20, 'duration' => 9],
            ['crew_id' => '219573', 'departure_date' => '2025-08-12', 'arrival_date' => '2025-09-10', 'vessel_id' => 13, 'duration' => 1],
            ['crew_id' => '219575', 'departure_date' => '2025-06-04', 'arrival_date' => '2025-11-30', 'vessel_id' => 27, 'duration' => 6],
            ['crew_id' => '219585', 'departure_date' => '2025-03-01', 'arrival_date' => '2025-11-25', 'vessel_id' => 27, 'duration' => 9],
            ['crew_id' => '219588', 'departure_date' => '2025-01-08', 'arrival_date' => '2025-09-04', 'vessel_id' => 28, 'duration' => 6],
            ['crew_id' => '219590', 'departure_date' => '2025-04-12', 'arrival_date' => '2025-07-30', 'vessel_id' => 29, 'duration' => 0],
            ['crew_id' => '219592', 'departure_date' => '2025-04-28', 'arrival_date' => '2025-08-25', 'vessel_id' => 30, 'duration' => 3],
            ['crew_id' => '219596', 'departure_date' => '2025-05-06', 'arrival_date' => '2026-01-30', 'vessel_id' => 29, 'duration' => 9],
            ['crew_id' => '220603', 'departure_date' => '2025-01-01', 'arrival_date' => '2025-08-03', 'vessel_id' => 31, 'duration' => 6],
            ['crew_id' => '220604', 'departure_date' => '2025-06-03', 'arrival_date' => '2025-11-29', 'vessel_id' => 32, 'duration' => 6],
            ['crew_id' => '219615', 'departure_date' => '2024-12-05', 'arrival_date' => '2025-06-02', 'vessel_id' => 23, 'duration' => 6],
            ['crew_id' => '219648', 'departure_date' => '2025-07-29', 'arrival_date' => '2026-01-24', 'vessel_id' => 36, 'duration' => 6],
            ['crew_id' => '219650', 'departure_date' => '2025-06-17', 'arrival_date' => '2025-09-14', 'vessel_id' => 37, 'duration' => 3],
            ['crew_id' => '219651', 'departure_date' => '2025-01-14', 'arrival_date' => '2025-08-11', 'vessel_id' => 38, 'duration' => 6],
            ['crew_id' => '219691', 'departure_date' => '2025-04-28', 'arrival_date' => '2026-01-22', 'vessel_id' => 2, 'duration' => 9],
            ['crew_id' => '219694', 'departure_date' => '2025-04-24', 'arrival_date' => '2025-10-20', 'vessel_id' => 39, 'duration' => 6],
            ['crew_id' => '219741', 'departure_date' => '2024-11-18', 'arrival_date' => '2025-08-14', 'vessel_id' => 24, 'duration' => 6],
            ['crew_id' => '219744', 'departure_date' => '2025-06-12', 'arrival_date' => '2025-12-08', 'vessel_id' => 41, 'duration' => 6],
            ['crew_id' => '219745', 'departure_date' => '2025-02-10', 'arrival_date' => '2025-08-28', 'vessel_id' => 42, 'duration' => 0],
            ['crew_id' => '219748', 'departure_date' => '2024-10-20', 'arrival_date' => '2025-07-16', 'vessel_id' => 43, 'duration' => 9],
            ['crew_id' => '219751', 'departure_date' => '2025-05-27', 'arrival_date' => '2025-11-22', 'vessel_id' => 5, 'duration' => 6],
            ['crew_id' => '219752', 'departure_date' => '2025-07-01', 'arrival_date' => '2025-09-07', 'vessel_id' => 44, 'duration' => 6],
            ['crew_id' => '219768', 'departure_date' => '2025-07-23', 'arrival_date' => '2025-11-19', 'vessel_id' => 45, 'duration' => 4],
            ['crew_id' => '219769', 'departure_date' => '2025-06-30', 'arrival_date' => '2025-09-27', 'vessel_id' => 46, 'duration' => 3],
            ['crew_id' => '219774', 'departure_date' => '2025-05-27', 'arrival_date' => '2026-02-20', 'vessel_id' => 47, 'duration' => 9],
            ['crew_id' => '219782', 'departure_date' => '2025-06-17', 'arrival_date' => '2025-12-13', 'vessel_id' => 49, 'duration' => 6],
            ['crew_id' => '219783', 'departure_date' => '2025-06-07', 'arrival_date' => '2025-12-03', 'vessel_id' => 50, 'duration' => 6],
            ['crew_id' => '219804', 'departure_date' => '2025-05-22', 'arrival_date' => '2025-11-17', 'vessel_id' => 52, 'duration' => 6],
            ['crew_id' => '219811', 'departure_date' => '2025-04-12', 'arrival_date' => '2025-12-07', 'vessel_id' => 53, 'duration' => 8],
            ['crew_id' => '219822', 'departure_date' => '2025-05-24', 'arrival_date' => '2026-02-17', 'vessel_id' => 54, 'duration' => 9],
            ['crew_id' => '219828', 'departure_date' => '2025-05-28', 'arrival_date' => '2026-02-21', 'vessel_id' => 55, 'duration' => 9],
            ['crew_id' => '219831', 'departure_date' => '2025-02-02', 'arrival_date' => '2025-07-31', 'vessel_id' => 57, 'duration' => 6],
            ['crew_id' => '219832', 'departure_date' => '2025-01-01', 'arrival_date' => '2025-08-20', 'vessel_id' => 16, 'duration' => 9],
            ['crew_id' => '219836', 'departure_date' => '2025-06-01', 'arrival_date' => '2025-10-30', 'vessel_id' => 58, 'duration' => 6],
            ['crew_id' => '219843', 'departure_date' => '2024-11-18', 'arrival_date' => '2025-08-10', 'vessel_id' => 59, 'duration' => 9],
            ['crew_id' => '219849', 'departure_date' => '2025-03-24', 'arrival_date' => '2025-09-19', 'vessel_id' => 60, 'duration' => 6],
            ['crew_id' => '219852', 'departure_date' => '2025-07-09', 'arrival_date' => '2026-04-04', 'vessel_id' => 62, 'duration' => 9],
            ['crew_id' => '219855', 'departure_date' => '2025-03-02', 'arrival_date' => '2025-10-27', 'vessel_id' => 63, 'duration' => 8],
            ['crew_id' => '219863', 'departure_date' => '2025-01-16', 'arrival_date' => '2025-11-11', 'vessel_id' => 64, 'duration' => 6],
            ['crew_id' => '221687', 'departure_date' => '2025-04-18', 'arrival_date' => '2026-01-12', 'vessel_id' => 174, 'duration' => 9],
            ['crew_id' => '221693', 'departure_date' => '2025-04-10', 'arrival_date' => '2025-10-06', 'vessel_id' => 70, 'duration' => 6],
            ['crew_id' => '221699', 'departure_date' => '2025-02-25', 'arrival_date' => '2025-09-22', 'vessel_id' => 49, 'duration' => 6],
            ['crew_id' => '221707', 'departure_date' => '2025-08-16', 'arrival_date' => '2026-01-12', 'vessel_id' => 27, 'duration' => 0],
            ['crew_id' => '221720', 'departure_date' => '2025-01-01', 'arrival_date' => '2025-05-22', 'vessel_id' => 16, 'duration' => 6],
            ['crew_id' => '221721', 'departure_date' => '2024-11-18', 'arrival_date' => '2025-08-14', 'vessel_id' => 160, 'duration' => 9],
            ['crew_id' => '221731', 'departure_date' => '2025-02-22', 'arrival_date' => '2025-09-19', 'vessel_id' => 65, 'duration' => 6],
            ['crew_id' => '221734', 'departure_date' => '2025-08-11', 'arrival_date' => '2026-02-06', 'vessel_id' => 46, 'duration' => 6],
            ['crew_id' => '221748', 'departure_date' => '2025-03-02', 'arrival_date' => '2025-10-27', 'vessel_id' => 63, 'duration' => 8],
            ['crew_id' => '221759', 'departure_date' => '2025-04-07', 'arrival_date' => '2025-12-02', 'vessel_id' => 161, 'duration' => 8],
            ['crew_id' => '221768', 'departure_date' => '2025-01-01', 'arrival_date' => '2025-05-26', 'vessel_id' => 126, 'duration' => 6],
            ['crew_id' => '221769', 'departure_date' => '2025-01-01', 'arrival_date' => '2025-10-02', 'vessel_id' => 162, 'duration' => 6],
            ['crew_id' => '221808', 'departure_date' => '2025-02-06', 'arrival_date' => '2025-08-04', 'vessel_id' => 21, 'duration' => 6],
            ['crew_id' => '221810', 'departure_date' => '2025-07-29', 'arrival_date' => '2025-08-27', 'vessel_id' => 13, 'duration' => 1],
            ['crew_id' => '221833', 'departure_date' => '2025-08-06', 'arrival_date' => '2026-05-02', 'vessel_id' => 105, 'duration' => 9],
        ];

        foreach ($contractsData as $data) {
            $user = User::where('crew_id', $data['crew_id'])->first();
            $vessel = Vessel::where('vessel_id', $data['vessel_id'])->first();

            if ($user && $vessel) {
                $departureDate = Carbon::parse($data['departure_date']);
                $arrivalDate = Carbon::parse($data['arrival_date']);

                Contract::firstOrCreate([
                    'user_id' => $user->id,
                    'vessel_id' => $vessel->id,
                    'departure_date' => $departureDate,
                ], [
                    'contract_number' => 'CNT-' . $user->crew_id . '-' . $departureDate->format('Y'),
                    'arrival_date' => $arrivalDate,
                    'duration_months' => $data['duration'],
                    'contract_start_date' => $departureDate,
                    'contract_end_date' => $arrivalDate,
                ]);
            }
        }
    }
}
