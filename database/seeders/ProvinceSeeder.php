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
        $provinces = [
            [
                'id' => 1,
                'psgc_code' => '012800000',
                'prov_desc' => 'ILOCOS NORTE',
                'reg_code' => '01',
                'prov_code' => '0128'
            ],
            [
                'id' => 2,
                'psgc_code' => '012900000',
                'prov_desc' => 'ILOCOS SUR',
                'reg_code' => '01',
                'prov_code' => '0129'
            ],
            [
                'id' => 3,
                'psgc_code' => '013300000',
                'prov_desc' => 'LA UNION',
                'reg_code' => '01',
                'prov_code' => '0133'
            ],
            [
                'id' => 4,
                'psgc_code' => '015500000',
                'prov_desc' => 'PANGASINAN',
                'reg_code' => '01',
                'prov_code' => '0155'
            ],
            [
                'id' => 5,
                'psgc_code' => '020900000',
                'prov_desc' => 'BATANES',
                'reg_code' => '02',
                'prov_code' => '0209'
            ],
            [
                'id' => 6,
                'psgc_code' => '021500000',
                'prov_desc' => 'CAGAYAN',
                'reg_code' => '02',
                'prov_code' => '0215'
            ],
            [
                'id' => 7,
                'psgc_code' => '023100000',
                'prov_desc' => 'ISABELA',
                'reg_code' => '02',
                'prov_code' => '0231'
            ],
            [
                'id' => 8,
                'psgc_code' => '025000000',
                'prov_desc' => 'NUEVA VIZCAYA',
                'reg_code' => '02',
                'prov_code' => '0250'
            ],
            [
                'id' => 9,
                'psgc_code' => '025700000',
                'prov_desc' => 'QUIRINO',
                'reg_code' => '02',
                'prov_code' => '0257'
            ],
            [
                'id' => 10,
                'psgc_code' => '030800000',
                'prov_desc' => 'BATAAN',
                'reg_code' => '03',
                'prov_code' => '0308'
            ],
            [
                'id' => 11,
                'psgc_code' => '031400000',
                'prov_desc' => 'BULACAN',
                'reg_code' => '03',
                'prov_code' => '0314'
            ],
            [
                'id' => 12,
                'psgc_code' => '034900000',
                'prov_desc' => 'NUEVA ECIJA',
                'reg_code' => '03',
                'prov_code' => '0349'
            ],
            [
                'id' => 13,
                'psgc_code' => '035400000',
                'prov_desc' => 'PAMPANGA',
                'reg_code' => '03',
                'prov_code' => '0354'
            ],
            [
                'id' => 14,
                'psgc_code' => '036900000',
                'prov_desc' => 'TARLAC',
                'reg_code' => '03',
                'prov_code' => '0369'
            ],
            [
                'id' => 15,
                'psgc_code' => '037100000',
                'prov_desc' => 'ZAMBALES',
                'reg_code' => '03',
                'prov_code' => '0371'
            ],
            [
                'id' => 16,
                'psgc_code' => '037700000',
                'prov_desc' => 'AURORA',
                'reg_code' => '03',
                'prov_code' => '0377'
            ],
            [
                'id' => 17,
                'psgc_code' => '041000000',
                'prov_desc' => 'BATANGAS',
                'reg_code' => '04',
                'prov_code' => '0410'
            ],
            [
                'id' => 18,
                'psgc_code' => '042100000',
                'prov_desc' => 'CAVITE',
                'reg_code' => '04',
                'prov_code' => '0421'
            ],
            [
                'id' => 19,
                'psgc_code' => '043400000',
                'prov_desc' => 'LAGUNA',
                'reg_code' => '04',
                'prov_code' => '0434'
            ],
            [
                'id' => 20,
                'psgc_code' => '045600000',
                'prov_desc' => 'QUEZON',
                'reg_code' => '04',
                'prov_code' => '0456'
            ],
            [
                'id' => 21,
                'psgc_code' => '045800000',
                'prov_desc' => 'RIZAL',
                'reg_code' => '04',
                'prov_code' => '0458'
            ],
            [
                'id' => 22,
                'psgc_code' => '174000000',
                'prov_desc' => 'MARINDUQUE',
                'reg_code' => '17',
                'prov_code' => '1740'
            ],
            [
                'id' => 23,
                'psgc_code' => '175100000',
                'prov_desc' => 'OCCIDENTAL MINDORO',
                'reg_code' => '17',
                'prov_code' => '1751'
            ],
            [
                'id' => 24,
                'psgc_code' => '175200000',
                'prov_desc' => 'ORIENTAL MINDORO',
                'reg_code' => '17',
                'prov_code' => '1752'
            ],
            [
                'id' => 25,
                'psgc_code' => '175300000',
                'prov_desc' => 'PALAWAN',
                'reg_code' => '17',
                'prov_code' => '1753'
            ],
            [
                'id' => 26,
                'psgc_code' => '175900000',
                'prov_desc' => 'ROMBLON',
                'reg_code' => '17',
                'prov_code' => '1759'
            ],
            [
                'id' => 27,
                'psgc_code' => '050500000',
                'prov_desc' => 'ALBAY',
                'reg_code' => '05',
                'prov_code' => '0505'
            ],
            [
                'id' => 28,
                'psgc_code' => '051600000',
                'prov_desc' => 'CAMARINES NORTE',
                'reg_code' => '05',
                'prov_code' => '0516'
            ],
            [
                'id' => 29,
                'psgc_code' => '051700000',
                'prov_desc' => 'CAMARINES SUR',
                'reg_code' => '05',
                'prov_code' => '0517'
            ],
            [
                'id' => 30,
                'psgc_code' => '052000000',
                'prov_desc' => 'CATANDUANES',
                'reg_code' => '05',
                'prov_code' => '0520'
            ],
            [
                'id' => 31,
                'psgc_code' => '054100000',
                'prov_desc' => 'MASBATE',
                'reg_code' => '05',
                'prov_code' => '0541'
            ],
            [
                'id' => 32,
                'psgc_code' => '056200000',
                'prov_desc' => 'SORSOGON',
                'reg_code' => '05',
                'prov_code' => '0562'
            ],
            [
                'id' => 33,
                'psgc_code' => '060400000',
                'prov_desc' => 'AKLAN',
                'reg_code' => '06',
                'prov_code' => '0604'
            ],
            [
                'id' => 34,
                'psgc_code' => '060600000',
                'prov_desc' => 'ANTIQUE',
                'reg_code' => '06',
                'prov_code' => '0606'
            ],
            [
                'id' => 35,
                'psgc_code' => '061900000',
                'prov_desc' => 'CAPIZ',
                'reg_code' => '06',
                'prov_code' => '0619'
            ],
            [
                'id' => 36,
                'psgc_code' => '063000000',
                'prov_desc' => 'ILOILO',
                'reg_code' => '06',
                'prov_code' => '0630'
            ],
            [
                'id' => 37,
                'psgc_code' => '064500000',
                'prov_desc' => 'NEGROS OCCIDENTAL',
                'reg_code' => '06',
                'prov_code' => '0645'
            ],
            [
                'id' => 38,
                'psgc_code' => '067900000',
                'prov_desc' => 'GUIMARAS',
                'reg_code' => '06',
                'prov_code' => '0679'
            ],
            [
                'id' => 39,
                'psgc_code' => '071200000',
                'prov_desc' => 'BOHOL',
                'reg_code' => '07',
                'prov_code' => '0712'
            ],
            [
                'id' => 40,
                'psgc_code' => '072200000',
                'prov_desc' => 'CEBU',
                'reg_code' => '07',
                'prov_code' => '0722'
            ],
            [
                'id' => 41,
                'psgc_code' => '074600000',
                'prov_desc' => 'NEGROS ORIENTAL',
                'reg_code' => '07',
                'prov_code' => '0746'
            ],
            [
                'id' => 42,
                'psgc_code' => '076100000',
                'prov_desc' => 'SIQUIJOR',
                'reg_code' => '07',
                'prov_code' => '0761'
            ],
            [
                'id' => 43,
                'psgc_code' => '082600000',
                'prov_desc' => 'EASTERN SAMAR',
                'reg_code' => '08',
                'prov_code' => '0826'
            ],
            [
                'id' => 44,
                'psgc_code' => '083700000',
                'prov_desc' => 'LEYTE',
                'reg_code' => '08',
                'prov_code' => '0837'
            ],
            [
                'id' => 45,
                'psgc_code' => '084800000',
                'prov_desc' => 'NORTHERN SAMAR',
                'reg_code' => '08',
                'prov_code' => '0848'
            ],
            [
                'id' => 46,
                'psgc_code' => '086000000',
                'prov_desc' => 'SAMAR (WESTERN SAMAR)',
                'reg_code' => '08',
                'prov_code' => '0860'
            ],
            [
                'id' => 47,
                'psgc_code' => '086400000',
                'prov_desc' => 'SOUTHERN LEYTE',
                'reg_code' => '08',
                'prov_code' => '0864'
            ],
            [
                'id' => 48,
                'psgc_code' => '087800000',
                'prov_desc' => 'BILIRAN',
                'reg_code' => '08',
                'prov_code' => '0878'
            ],
            [
                'id' => 49,
                'psgc_code' => '097200000',
                'prov_desc' => 'ZAMBOANGA DEL NORTE',
                'reg_code' => '09',
                'prov_code' => '0972'
            ],
            [
                'id' => 50,
                'psgc_code' => '097300000',
                'prov_desc' => 'ZAMBOANGA DEL SUR',
                'reg_code' => '09',
                'prov_code' => '0973'
            ],
            [
                'id' => 51,
                'psgc_code' => '098300000',
                'prov_desc' => 'ZAMBOANGA SIBUGAY',
                'reg_code' => '09',
                'prov_code' => '0983'
            ],
            [
                'id' => 52,
                'psgc_code' => '099700000',
                'prov_desc' => 'CITY OF ISABELA',
                'reg_code' => '09',
                'prov_code' => '0997'
            ],
            [
                'id' => 53,
                'psgc_code' => '101300000',
                'prov_desc' => 'BUKIDNON',
                'reg_code' => '10',
                'prov_code' => '1013'
            ],
            [
                'id' => 54,
                'psgc_code' => '101800000',
                'prov_desc' => 'CAMIGUIN',
                'reg_code' => '10',
                'prov_code' => '1018'
            ],
            [
                'id' => 55,
                'psgc_code' => '103500000',
                'prov_desc' => 'LANAO DEL NORTE',
                'reg_code' => '10',
                'prov_code' => '1035'
            ],
            [
                'id' => 56,
                'psgc_code' => '104200000',
                'prov_desc' => 'MISAMIS OCCIDENTAL',
                'reg_code' => '10',
                'prov_code' => '1042'
            ],
            [
                'id' => 57,
                'psgc_code' => '104300000',
                'prov_desc' => 'MISAMIS ORIENTAL',
                'reg_code' => '10',
                'prov_code' => '1043'
            ],
            [
                'id' => 58,
                'psgc_code' => '112300000',
                'prov_desc' => 'DAVAO DEL NORTE',
                'reg_code' => '11',
                'prov_code' => '1123'
            ],
            [
                'id' => 59,
                'psgc_code' => '112400000',
                'prov_desc' => 'DAVAO DEL SUR',
                'reg_code' => '11',
                'prov_code' => '1124'
            ],
            [
                'id' => 60,
                'psgc_code' => '112500000',
                'prov_desc' => 'DAVAO ORIENTAL',
                'reg_code' => '11',
                'prov_code' => '1125'
            ],
            [
                'id' => 61,
                'psgc_code' => '118200000',
                'prov_desc' => 'COMPOSTELA VALLEY',
                'reg_code' => '11',
                'prov_code' => '1182'
            ],
            [
                'id' => 62,
                'psgc_code' => '118600000',
                'prov_desc' => 'DAVAO OCCIDENTAL',
                'reg_code' => '11',
                'prov_code' => '1186'
            ],
            [
                'id' => 63,
                'psgc_code' => '124700000',
                'prov_desc' => 'COTABATO (NORTH COTABATO)',
                'reg_code' => '12',
                'prov_code' => '1247'
            ],
            [
                'id' => 64,
                'psgc_code' => '126300000',
                'prov_desc' => 'SOUTH COTABATO',
                'reg_code' => '12',
                'prov_code' => '1263'
            ],
            [
                'id' => 65,
                'psgc_code' => '126500000',
                'prov_desc' => 'SULTAN KUDARAT',
                'reg_code' => '12',
                'prov_code' => '1265'
            ],
            [
                'id' => 66,
                'psgc_code' => '128000000',
                'prov_desc' => 'SARANGANI',
                'reg_code' => '12',
                'prov_code' => '1280'
            ],
            [
                'id' => 67,
                'psgc_code' => '129800000',
                'prov_desc' => 'COTABATO CITY',
                'reg_code' => '12',
                'prov_code' => '1298'
            ],
            [
                'id' => 68,
                'psgc_code' => '133900000',
                'prov_desc' => 'NCR, CITY OF MANILA, FIRST DISTRICT',
                'reg_code' => '13',
                'prov_code' => '1339'
            ],
            [
                'id' => 69,
                'psgc_code' => '133900000',
                'prov_desc' => 'CITY OF MANILA',
                'reg_code' => '13',
                'prov_code' => '1339'
            ],
            [
                'id' => 70,
                'psgc_code' => '137400000',
                'prov_desc' => 'NCR, SECOND DISTRICT',
                'reg_code' => '13',
                'prov_code' => '1374'
            ],
            [
                'id' => 71,
                'psgc_code' => '137500000',
                'prov_desc' => 'NCR, THIRD DISTRICT',
                'reg_code' => '13',
                'prov_code' => '1375'
            ],
            [
                'id' => 72,
                'psgc_code' => '137600000',
                'prov_desc' => 'NCR, FOURTH DISTRICT',
                'reg_code' => '13',
                'prov_code' => '1376'
            ],
            [
                'id' => 73,
                'psgc_code' => '140100000',
                'prov_desc' => 'ABRA',
                'reg_code' => '14',
                'prov_code' => '1401'
            ],
            [
                'id' => 74,
                'psgc_code' => '141100000',
                'prov_desc' => 'BENGUET',
                'reg_code' => '14',
                'prov_code' => '1411'
            ],
            [
                'id' => 75,
                'psgc_code' => '142700000',
                'prov_desc' => 'IFUGAO',
                'reg_code' => '14',
                'prov_code' => '1427'
            ],
            [
                'id' => 76,
                'psgc_code' => '143200000',
                'prov_desc' => 'KALINGA',
                'reg_code' => '14',
                'prov_code' => '1432'
            ],
            [
                'id' => 77,
                'psgc_code' => '144400000',
                'prov_desc' => 'MOUNTAIN PROVINCE',
                'reg_code' => '14',
                'prov_code' => '1444'
            ],
            [
                'id' => 78,
                'psgc_code' => '148100000',
                'prov_desc' => 'APAYAO',
                'reg_code' => '14',
                'prov_code' => '1481'
            ],
            [
                'id' => 79,
                'psgc_code' => '150700000',
                'prov_desc' => 'BASILAN',
                'reg_code' => '15',
                'prov_code' => '1507'
            ],
            [
                'id' => 80,
                'psgc_code' => '153600000',
                'prov_desc' => 'LANAO DEL SUR',
                'reg_code' => '15',
                'prov_code' => '1536'
            ],
            [
                'id' => 81,
                'psgc_code' => '153800000',
                'prov_desc' => 'MAGUINDANAO',
                'reg_code' => '15',
                'prov_code' => '1538'
            ],
            [
                'id' => 82,
                'psgc_code' => '156600000',
                'prov_desc' => 'SULU',
                'reg_code' => '15',
                'prov_code' => '1566'
            ],
            [
                'id' => 83,
                'psgc_code' => '157000000',
                'prov_desc' => 'TAWI-TAWI',
                'reg_code' => '15',
                'prov_code' => '1570'
            ],
            [
                'id' => 84,
                'psgc_code' => '160200000',
                'prov_desc' => 'AGUSAN DEL NORTE',
                'reg_code' => '16',
                'prov_code' => '1602'
            ],
            [
                'id' => 85,
                'psgc_code' => '160300000',
                'prov_desc' => 'AGUSAN DEL SUR',
                'reg_code' => '16',
                'prov_code' => '1603'
            ],
            [
                'id' => 86,
                'psgc_code' => '166700000',
                'prov_desc' => 'SURIGAO DEL NORTE',
                'reg_code' => '16',
                'prov_code' => '1667'
            ],
            [
                'id' => 87,
                'psgc_code' => '166800000',
                'prov_desc' => 'SURIGAO DEL SUR',
                'reg_code' => '16',
                'prov_code' => '1668'
            ],
            [
                'id' => 88,
                'psgc_code' => '168500000',
                'prov_desc' => 'DINAGAT ISLANDS',
                'reg_code' => '16',
                'prov_code' => '1685'
            ]
        ];

        foreach ($provinces as $provinceData) {
            $region = Region::where('reg_code', $provinceData['reg_code'])->first();
            
            if ($region) {
                Province::create([
                    'id' => $provinceData['id'],
                    'psgc_code' => $provinceData['psgc_code'],
                    'prov_desc' => $provinceData['prov_desc'],
                    'reg_code' => $provinceData['reg_code'],
                    'prov_code' => $provinceData['prov_code'],
                    'region_id' => $region->id,
                ]);
            }
        }
    }
}