<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\Province;
use Illuminate\Database\Seeder;

class CitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $citiesData = [
            // Luzon
            ['province' => 'ILOCOS NORTE', 'city' => 'LAOAG CITY (ILOCOS NORTE)'],
            ['province' => 'LA UNION', 'city' => 'AGOO (LA UNION)'],
            ['province' => 'NUEVA VIZCAYA', 'city' => 'SANTA FE (NUEVA VIZCAYA)'],
            ['province' => 'NUEVA ECIJA', 'city' => 'SAN ISIDRO (NUEVA ECIJA)'],
            ['province' => 'BULACAN', 'city' => 'MALOLOS (BULACAN)'],
            ['province' => 'BULACAN', 'city' => 'MARILAO (BULACAN)'],
            ['province' => 'PAMPANGA', 'city' => 'ARAYAT (PAMPANGA)'],
            ['province' => 'PAMPANGA', 'city' => 'MINALIN (PAMPANGA)'],
            ['province' => 'ZAMBALES', 'city' => 'CASTILLEJOS'],
            ['province' => 'ZAMBALES', 'city' => 'IBA (ZAMBALES)'],
            ['province' => 'LAGUNA', 'city' => 'CABUYAO (LAGUNA)'],
            ['province' => 'CAVITE', 'city' => 'BACOOR'],
            ['province' => 'CAVITE', 'city' => 'GENERAL TRIAS'],
            ['province' => 'CAVITE', 'city' => 'DASMARIÑAS (CAVITE)'],
            ['province' => 'CAVITE', 'city' => 'IMUS'],
            ['province' => 'BATANGAS', 'city' => 'BATANGAS CITY (BATANGAS)'],
            ['province' => 'BATANGAS', 'city' => 'SAN PASCUAL (BATANGAS)'],
            ['province' => 'BATANGAS', 'city' => 'TINGLOY (BATANGAS)'],
            ['province' => 'BATANGAS', 'city' => 'SAN NICOLAS (BATANGAS)'],
            ['province' => 'RIZAL', 'city' => 'SAN MATEO (RIZAL)'],
            ['province' => 'RIZAL', 'city' => 'ANGONO (RIZAL)'],
            ['province' => 'QUEZON', 'city' => 'LUCBAN (QUEZON)'],
            ['province' => 'CAMARINES NORTE', 'city' => 'SAN VICENTE (CAM. NORTE)'],
            ['province' => 'IFUGAO', 'city' => 'BANAUE (IFUGAO)'],
            ['province' => 'BENGUET', 'city' => 'LA TRINIDAD (BENGUET)'],
            ['province' => 'METRO MANILA', 'city' => 'MARIKINA CITY'],
            ['province' => 'METRO MANILA', 'city' => 'CALOOCAN CITY'],
            ['province' => 'METRO MANILA', 'city' => 'MANDALUYONG CITY'],
            ['province' => 'METRO MANILA', 'city' => 'QUEZON CITY'],
            ['province' => 'METRO MANILA', 'city' => 'MUNTINLUPA CITY'],
            // Visayas
            ['province' => 'ILOILO', 'city' => 'SAN DIONISIO (ILOILO)'],
            ['province' => 'ILOILO', 'city' => 'ILOILO CITY'],
            ['province' => 'ILOILO', 'city' => 'CABATUAN (ILOILO)'],
            ['province' => 'ILOILO', 'city' => 'DUMANGAS (ILOILO)'],
            ['province' => 'ILOILO', 'city' => 'MIAGAO (ILOILO)'],
            ['province' => 'CAPIZ', 'city' => 'IVISAN (CAPIZ)'],
            ['province' => 'CAPIZ', 'city' => 'ROXAS CITY'],
            ['province' => 'NEGROS OCCIDENTAL', 'city' => 'BACOLOD (NEG. OCC.)'],
            ['province' => 'NEGROS OCCIDENTAL', 'city' => 'HINIGARAN (NEG. OCC.)'],
            ['province' => 'NEGROS OCCIDENTAL', 'city' => 'CADIZ CITY (NEG. OCC.)'],
            ['province' => 'GUIMARAS', 'city' => 'JORDAN (GUIMARAS)'],
            ['province' => 'AKLAN', 'city' => 'KALIBO (AKLAN)'],
            ['province' => 'ANTIQUE', 'city' => 'HAMTIC (ANTIQUE)'],
            ['province' => 'CEBU', 'city' => 'DANAO CITY (CEBU)'],
            ['province' => 'CEBU', 'city' => 'TALISAY CITY (CEBU)'],
            ['province' => 'BOHOL', 'city' => 'LOON (BOHOL)'],
            ['province' => 'BOHOL', 'city' => 'VALENCIA (BOHOL)'],
            ['province' => 'BOHOL', 'city' => 'LILA (BOHOL)'],
            ['province' => 'BOHOL', 'city' => 'TAGBILARAN CITY (BOHOL)'],
            ['province' => 'BOHOL', 'city' => 'DAUIS (BOHOL)'],
            ['province' => 'BOHOL', 'city' => 'PILAR (BOHOL)'],
            ['province' => 'SOUTHERN LEYTE', 'city' => 'MAASIN CITY (S. LEYTE)'],
            ['province' => 'SOUTHERN LEYTE', 'city' => 'MALITBOG (S. LEYTE)'],
            ['province' => 'E. SAMAR', 'city' => 'BORONGAN (E. SAMAR)'],
            ['province' => 'SAMAR', 'city' => 'CATBALOGAN (SAMAR)'],
            ['province' => 'NORTHERN SAMAR', 'city' => 'LAPINIG (N. SAMAR)'],
            // Mindanao
            ['province' => 'ZAMBOANGA DEL NORTE', 'city' => 'LABASON (ZAMBOANGA DEL NORTE)'],
            ['province' => 'LANAO DEL NORTE', 'city' => 'ILIGAN CITY'],
            ['province' => 'DAVAO DEL NORTE', 'city' => 'KAPALONG (DAVAO DEL NORTE)'],
            ['province' => 'DAVAO DEL SUR', 'city' => 'DAVAO CITY'],
            ['province' => 'DAVAO DEL SUR', 'city' => 'BANSALAN (DAVAO DEL SUR)'],
            ['province' => 'AGUSAN DEL NORTE', 'city' => 'BUTUAN CITY'],
        ];

        foreach ($citiesData as $data) {
            $province = Province::where('name', $data['province'])->first();

            if ($province) {
                City::firstOrCreate([
                    'name' => $data['city'],
                    'province_id' => $province->id,
                ]);
            }
        }
    }
}
