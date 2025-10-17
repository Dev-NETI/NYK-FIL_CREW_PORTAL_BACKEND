<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\City;
use App\Models\Island;
use App\Models\Province;
use App\Models\Region;
use Illuminate\Database\Seeder;

class AddressSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $addressesData = [
            ['street' => 'DUGMAN', 'city' => 'SAN DIONISIO (ILOILO)', 'province' => 'ILOILO', 'region' => 'REGION VI', 'island' => 'VISAYAS'],
            ['street' => 'MALOCLOC SUR', 'city' => 'IVISAN (CAPIZ)', 'province' => 'CAPIZ', 'region' => 'REGION VI', 'island' => 'VISAYAS'],
            ['street' => 'LOT 3, BLOCK 1, PHASE 1 SAN LORENZO HOMES SUBDIVISION, BARANGAY TANGUB', 'city' => 'BACOLOD (NEG. OCC.)', 'province' => 'NEGROS OCCIDENTAL', 'region' => 'REGION VI', 'island' => 'VISAYAS'],
            ['street' => 'B8 L3 NEDF VILLAGE HANDUMANAN', 'city' => 'BACOLOD (NEG. OCC.)', 'province' => 'NEGROS OCCIDENTAL', 'region' => 'REGION VI', 'island' => 'VISAYAS'],
            ['street' => 'PUROK 7 RIZAL STREET  BRGY ALUA ', 'city' => 'SAN ISIDRO (NUEVA ECIJA)', 'province' => 'NUEVA ECIJA', 'region' => 'REGION III', 'island' => 'LUZON'],
            ['street' => 'B-112 L-30 MABUHAY CITY MAMATID', 'city' => 'CABUYAO (LAGUNA)', 'province' => 'LAGUNA', 'region' => 'REGION IV-A', 'island' => 'LUZON'],
            ['street' => 'PUROK 2A GABUYAN', 'city' => 'KAPALONG (DAVAO DEL NORTE)', 'province' => 'DAVAO DEL NORTE', 'region' => 'REGION XI', 'island' => 'MINDANAO'],
            ['street' => 'NABAGATNAN, POBLACION', 'city' => 'JORDAN (GUIMARAS)', 'province' => 'GUIMARAS', 'region' => 'REGION VI', 'island' => 'VISAYAS'],
            ['street' => 'CM ENRIQUEZ STREET, SUBA', 'city' => 'DANAO CITY (CEBU)', 'province' => 'CEBU', 'region' => 'REGION VII', 'island' => 'VISAYAS'],
            ['street' => 'REFULGENTE STREET, BARANGAY ESTANCIA', 'city' => 'KALIBO (AKLAN)', 'province' => 'AKLAN', 'region' => 'REGION VI', 'island' => 'VISAYAS'],
            ['street' => 'BLOCK 2 LOT 45 GREENTOWN VILL. 1 MAMBOG 2', 'city' => 'BACOOR', 'province' => 'CAVITE', 'region' => 'REGION IV-A', 'island' => 'LUZON'],
            ['street' => 'LOT 19, BLOCK 1, IPIL STREET, MARIKINA HEIGHTS (CONCEPCION)', 'city' => 'MARIKINA CITY', 'province' => 'METRO MANILA', 'region' => 'NCR', 'island' => 'LUZON'],
            ['street' => 'PUROK 7 MOTO SUR', 'city' => 'LOON (BOHOL)', 'province' => 'BOHOL', 'region' => 'REGION VII', 'island' => 'VISAYAS'],
            ['street' => 'PHASE 3D  AREA 3 BLK 29 LOT 50 DAGAT-DAGATANA', 'city' => 'CALOOCAN CITY', 'province' => 'METRO MANILA', 'region' => 'NCR', 'island' => 'LUZON'],
            ['street' => 'BLK. 7 LOT.31 CAMACHILE SUBD., PASONG', 'city' => 'GENERAL TRIAS', 'province' => 'CAVITE', 'region' => 'REGION IV-A', 'island' => 'LUZON'],
            ['street' => 'SHERIDAN TOWERS NORTH TOWER, SHERIDAN STREET, BARANGAY HIGHWAY HILLS', 'city' => 'MANDALUYONG CITY', 'province' => 'METRO MANILA', 'region' => 'NCR', 'island' => 'LUZON'],
            ['street' => 'IBAYONG, BOCOS', 'city' => 'BANAUE (IFUGAO)', 'province' => 'IFUGAO', 'region' => 'CAR', 'island' => 'LUZON'],
            ['street' => '02 MADERAL ST.', 'city' => 'LUCBAN (QUEZON)', 'province' => 'QUEZON', 'region' => 'REGION IV-A', 'island' => 'LUZON'],
            ['street' => 'JP RIZAL ST., POBLACION DISTRICT I', 'city' => 'SAN VICENTE (CAM. NORTE)', 'province' => 'CAMARINES NORTE', 'region' => 'REGION V', 'island' => 'LUZON'],
            ['street' => 'PUROK SAN PEDRO, BARANGAY MARIA CLARA', 'city' => 'MAASIN CITY (S. LEYTE)', 'province' => 'SOUTHERN LEYTE', 'region' => 'REGION VIII', 'island' => 'VISAYAS'],
            ['street' => 'BARANGAY CANDATAG', 'city' => 'MALITBOG (S. LEYTE)', 'province' => 'SOUTHERN LEYTE', 'region' => 'REGION VIII', 'island' => 'VISAYAS'],
            ['street' => 'PUROK BOUGAINVILLA BRGY. BANQUEROHAN', 'city' => 'CADIZ CITY (NEG. OCC.)', 'province' => 'NEGROS OCCIDENTAL', 'region' => 'REGION VI', 'island' => 'VISAYAS'],
            ['street' => 'J. TAGLE ST., CUTA LOOBAN', 'city' => 'BATANGAS CITY (BATANGAS)', 'province' => 'BATANGAS', 'region' => 'REGION IV-A', 'island' => 'LUZON'],
            ['street' => 'BLK 20, LOT 17, IBIZA HOMES, BRGY. GUINAYANG, SAN MATEO, RIZAL', 'city' => 'SAN MATEO (RIZAL)', 'province' => 'RIZAL', 'region' => 'REGION IV-A', 'island' => 'LUZON'],
            ['street' => 'LOT 7 BLK 7 VILLA ESTIFANIA', 'city' => 'BACOLOD (NEG. OCC.)', 'province' => 'NEGROS OCCIDENTAL', 'region' => 'REGION VI', 'island' => 'VISAYAS'],
            ['street' => '#28 RIZAL AVE. EXT', 'city' => 'CATBALOGAN (SAMAR)', 'province' => 'SAMAR', 'region' => 'REGION VIII', 'island' => 'VISAYAS'],
            ['street' => '2324, LEGASPI STREET, ILAYA, BARANGAY II', 'city' => 'ROXAS CITY', 'province' => 'CAPIZ', 'region' => 'REGION VI', 'island' => 'VISAYAS'],
            ['street' => 'B-2 EMPLOYEES VILL UP DILIMAN', 'city' => 'QUEZON CITY', 'province' => 'METRO MANILA', 'region' => 'NCR', 'island' => 'LUZON'],
            ['street' => 'B32 L8 WESTWOOD SUBD. MANDURRIAO', 'city' => 'ILOILO CITY', 'province' => 'ILOILO', 'region' => 'REGION VI', 'island' => 'VISAYAS'],
            ['street' => 'LOT4-I-10 FARMVILLE SUBD., PUROK-1, TIKAY', 'city' => 'MALOLOS (BULACAN)', 'province' => 'BULACAN', 'region' => 'REGION III', 'island' => 'LUZON'],
            ['street' => 'POB. SEASIDE,', 'city' => 'VALENCIA (BOHOL)', 'province' => 'BOHOL', 'region' => 'REGION VII', 'island' => 'VISAYAS'],
            ['street' => 'BRGY. 55-B SALET-BULANGON', 'city' => 'LAOAG CITY (ILOCOS NORTE)', 'province' => 'ILOCOS NORTE', 'region' => 'REGION I', 'island' => 'LUZON'],
            ['street' => 'LOT1 BLOCK4 PHASE 5 CITA ITALIA SUBDIVISION, BUHAY NA TUBIG', 'city' => 'IMUS', 'province' => 'CAVITE', 'region' => 'REGION IV-A', 'island' => 'LUZON'],
            ['street' => 'B16 L9 CHESTER PLACE BUROL MAIN', 'city' => 'DASMARIÑAS (CAVITE)', 'province' => 'CAVITE', 'region' => 'REGION IV-A', 'island' => 'LUZON'],
            ['street' => 'PUROK 3, TIGUIS', 'city' => 'LILA (BOHOL)', 'province' => 'BOHOL', 'region' => 'REGION VII', 'island' => 'VISAYAS'],
            ['street' => 'BLK 3 LOT 2 VICTORS SUBD., COMMUNAL, BUHANGIN', 'city' => 'DAVAO CITY', 'province' => 'DAVAO DEL SUR', 'region' => 'REGION XI', 'island' => 'MINDANAO'],
            ['street' => '"BRGY. STO. TOMAS\n\n"', 'city' => 'TINGLOY (BATANGAS)', 'province' => 'BATANGAS', 'region' => 'REGION IV-A', 'island' => 'LUZON'],
            ['street' => 'B6, L20, PH1, TULIP STREET, GOLDEN HILLS SUBDIVISION, LOMA DE GATO', 'city' => 'MARILAO (BULACAN)', 'province' => 'BULACAN', 'region' => 'REGION III', 'island' => 'LUZON'],
            ['street' => 'BRGY TIRING', 'city' => 'CABATUAN (ILOILO)', 'province' => 'ILOILO', 'region' => 'REGION VI', 'island' => 'VISAYAS'],
            ['street' => '#840 ROSE STREET, POBLACION 2', 'city' => 'BANSALAN (DAVAO DEL SUR)', 'province' => 'DAVAO DEL SUR', 'region' => 'REGION XI', 'island' => 'MINDANAO'],
            ['street' => 'CAROMANGAY, HAMTIC, ANTIQUE', 'city' => 'HAMTIC (ANTIQUE)', 'province' => 'ANTIQUE', 'region' => 'REGION VI', 'island' => 'VISAYAS'],
            ['street' => 'QUEZON ST, BRGY. MAT-Y', 'city' => 'MIAGAO (ILOILO)', 'province' => 'ILOILO', 'region' => 'REGION VI', 'island' => 'VISAYAS'],
            ['street' => 'AD-205-F POBLACION', 'city' => 'LA TRINIDAD (BENGUET)', 'province' => 'BENGUET', 'region' => 'CAR', 'island' => 'LUZON'],
            ['street' => 'BLK 1 LOT 8 KATHLEEN PLACE III SUBD., BRGY., PINAGKAISAHAN E. GARCIA ST., CUBAO, ', 'city' => 'QUEZON CITY', 'province' => 'METRO MANILA', 'region' => 'NCR', 'island' => 'LUZON'],
            ['street' => 'SITIO LINAO, BRGY BATO', 'city' => 'HINIGARAN (NEG. OCC.)', 'province' => 'NEGROS OCCIDENTAL', 'region' => 'REGION VI', 'island' => 'VISAYAS'],
            ['street' => '18 YAKAL ST. CAREBI SUBD.', 'city' => 'ANGONO (RIZAL)', 'province' => 'RIZAL', 'region' => 'REGION IV-A', 'island' => 'LUZON'],
            ['street' => '110, SIXTO M. CACHO STREET, BARANGAY STA. MARIA', 'city' => 'CASTILLEJOS', 'province' => 'ZAMBALES', 'region' => 'REGION III', 'island' => 'LUZON'],
            ['street' => 'BRGY SOOC, AREVALO', 'city' => 'ILOILO CITY', 'province' => 'ILOILO', 'region' => 'REGION VI', 'island' => 'VISAYAS'],
            ['street' => 'DULCE 6 DONA SEGUNDINA TOWN HOMES NATIONAL HI-WAY PUTATAN', 'city' => 'MUNTINLUPA CITY', 'province' => 'METRO MANILA', 'region' => 'NCR', 'island' => 'LUZON'],
            ['street' => 'BLK. 18 L-2 BAMBOO ORCHARD, BANAYBANAY', 'city' => 'CABUYAO (LAGUNA)', 'province' => 'LAGUNA', 'region' => 'REGION IV-A', 'island' => 'LUZON'],
            ['street' => 'BRGY. PAGDUGUE', 'city' => 'DUMANGAS (ILOILO)', 'province' => 'ILOILO', 'region' => 'REGION VI', 'island' => 'VISAYAS'],
            ['street' => 'IMELDA', 'city' => 'LABASON (ZAMBOANGA DEL NORTE)', 'province' => 'ZAMBOANGA DEL NORTE', 'region' => 'REGION IX', 'island' => 'MINDANAO'],
            ['street' => '#42, BARANGAY TACTAC', 'city' => 'SANTA FE (NUEVA VIZCAYA)', 'province' => 'NUEVA VIZCAYA', 'region' => 'REGION II', 'island' => 'LUZON'],
            ['street' => '15 LIGONES STREET, BOOL DISTRICT', 'city' => 'TAGBILARAN CITY (BOHOL)', 'province' => 'BOHOL', 'region' => 'REGION VII', 'island' => 'VISAYAS'],
            ['street' => 'PUROK 3, LUMBAY', 'city' => 'PILAR (BOHOL)', 'province' => 'BOHOL', 'region' => 'REGION VII', 'island' => 'VISAYAS'],
            ['street' => 'I-ABENIS ST', 'city' => 'BORONGAN (E. SAMAR)', 'province' => 'E. SAMAR', 'region' => 'REGION VIII', 'island' => 'VISAYAS'],
            ['street' => '161, BARANGAY SAN VICENTE SUR', 'city' => 'AGOO (LA UNION)', 'province' => 'LA UNION', 'region' => 'REGION I', 'island' => 'LUZON'],
            ['street' => 'B3 LOT 8 VILLA MARIA SUBD. SAMBAT', 'city' => 'SAN PASCUAL (BATANGAS)', 'province' => 'BATANGAS', 'region' => 'REGION IV-A', 'island' => 'LUZON'],
            ['street' => 'BLOCK 18, LOT 6, PHASE 9, DECCA EXECUTIVE HOMES, BARANGAY TACUNAN', 'city' => 'DAVAO CITY', 'province' => 'DAVAO DEL SUR', 'region' => 'REGION XI', 'island' => 'MINDANAO'],
            ['street' => '#33 STA. CRUZ, CANDATING', 'city' => 'ARAYAT (PAMPANGA)', 'province' => 'PAMPANGA', 'region' => 'REGION III', 'island' => 'LUZON'],
            ['street' => '87 BANABA WEST ', 'city' => 'BATANGAS CITY (BATANGAS)', 'province' => 'BATANGAS', 'region' => 'REGION IV-A', 'island' => 'LUZON'],
            ['street' => 'BLOCK 7, LOT 1, JEM SUBDIVISION, BARANGAY GUZMAN-JESENA', 'city' => 'ILOILO CITY', 'province' => 'ILOILO', 'region' => 'REGION VI', 'island' => 'VISAYAS'],
            ['street' => 'PUROK 2, CATARMAN', 'city' => 'DAUIS (BOHOL)', 'province' => 'BOHOL', 'region' => 'REGION VII', 'island' => 'VISAYAS'],
            ['street' => '335 TAMBLOT STREET', 'city' => 'TAGBILARAN CITY (BOHOL)', 'province' => 'BOHOL', 'region' => 'REGION VII', 'island' => 'VISAYAS'],
            ['street' => '971B GOLDEN RIBBON,', 'city' => 'BUTUAN CITY', 'province' => 'AGUSAN DEL NORTE', 'region' => 'REGION XIII', 'island' => 'MINDANAO'],
            ['street' => '#810 BRGY. STO. DOMINGO', 'city' => 'MINALIN (PAMPANGA)', 'province' => 'PAMPANGA', 'region' => 'REGION III', 'island' => 'LUZON'],
            ['street' => '215 CALANGAY', 'city' => 'SAN NICOLAS (BATANGAS)', 'province' => 'BATANGAS', 'region' => 'REGION IV-A', 'island' => 'LUZON'],
            ['street' => 'BLK 7 LOT 6 TIERRA GRANDE 3-D CAMELLA HOMES LAWA-AN', 'city' => 'TALISAY CITY (CEBU)', 'province' => 'CEBU', 'region' => 'REGION VII', 'island' => 'VISAYAS'],
            ['street' => 'E-159 DIRITA-BALOGUEN', 'city' => 'IBA (ZAMBALES)', 'province' => 'ZAMBALES', 'region' => 'REGION III', 'island' => 'LUZON'],
            ['street' => 'BLK-7 LOT 50 CHINA ST. BARCELONA 4 BUHAY NA TUBIG', 'city' => 'IMUS', 'province' => 'CAVITE', 'region' => 'REGION IV-A', 'island' => 'LUZON'],
            ['street' => 'SOUTH SAN JOSE ST., MOLO', 'city' => 'ILOILO CITY', 'province' => 'ILOILO', 'region' => 'REGION VI', 'island' => 'VISAYAS'],
            ['street' => 'ADELFA, ACMAC', 'city' => 'ILIGAN CITY', 'province' => 'LANAO DEL NORTE', 'region' => 'REGION X', 'island' => 'MINDANAO'],
            ['street' => 'POTONG,', 'city' => 'LAPINIG (N. SAMAR)', 'province' => 'NORTHERN SAMAR', 'region' => 'REGION VIII', 'island' => 'VISAYAS'],
            ['street' => '11-A JEREOS EXTENSION, LAPAZ, LOPEZ JAENA SUR', 'city' => 'ILOILO CITY', 'province' => 'ILOILO', 'region' => 'REGION VI', 'island' => 'VISAYAS'],
        ];

        foreach ($addressesData as $data) {
            // $island = Island::where('name', $data['island'])->first();
            // $region = Region::where('name', $data['region'])->first();
            // $province = Province::where('name', $data['province'])->first();
            // $city = City::where('name', $data['city'])->first();

            // if ($island && $region && $province && $city) {
            //     Address::firstOrCreate([
            //         'street_address' => $data['street'],
            //         'city_id' => $city->id,
            //         'province_id' => $province->id,
            //         'region_id' => $region->id,
            //         'island_id' => $island->id,
            //     ]);
            // }
        }
    }
}
