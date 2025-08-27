<?php

namespace Database\Seeders;

use App\Models\Allotee;
use App\Models\User;
use Illuminate\Database\Seeder;

class AlloteeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $alloteeData = [
            ['crew_id' => '219454', 'name' => 'CALSEÑA, APRIL ROSE D', 'relationship' => 'WIFE', 'mobile' => '09665333181', 'address' => 'BRGY. DUGMAN SAN DIONISIO ILOILO / MISI CAMBUNAO ILOILO'],
            ['crew_id' => '219456', 'name' => 'DUPIT, JENEBER U', 'relationship' => 'WIFE', 'mobile' => '09209598554', 'address' => 'SITIO ALMON MALOCLOC SUR IVISAN, CAPIZ'],
            ['crew_id' => '219465', 'name' => 'FLORENTINO, CHLARIZ T', 'relationship' => 'WIFE', 'mobile' => '09087955343', 'address' => 'LOT 3 BLOCK 1 PHASE 1 SAN LORENZO SUBDIVISION BRGY TANGUB BACOLOD CITY NEGROS OCCIDENTAL'],
            ['crew_id' => '219471', 'name' => 'TONDO, JISZA T', 'relationship' => 'WIFE', 'mobile' => '09162604922', 'address' => 'BLK8, L3, NEDF VILL. HANDUMANAN BACOLOD CITY NEGROS OCCIDENTAL'],
            ['crew_id' => '219480', 'name' => 'GARCIA, CHERIE V', 'relationship' => 'WIFE', 'mobile' => '9497165596', 'address' => '090 CECILLO ST. POBLACION SAN ISIDRO NUEVA ECIJA'],
            ['crew_id' => '219482', 'name' => 'PRIETO, JONALYN R', 'relationship' => 'WIFE', 'mobile' => '09197007258', 'address' => 'BLK 112 LOT 30 MABUHAY CITY MAMATID CABUYAO LAGUNA'],
            ['crew_id' => '219484', 'name' => 'CILLACAY, ANA MARIE A', 'relationship' => 'WIFE', 'mobile' => '9482338335', 'address' => 'PUROK- 8 GABUYAN KAPALONG DAVAO DEL NORTE'],
            ['crew_id' => '219486', 'name' => 'MORENO, LEOLYN  B', 'relationship' => 'WIFE', 'mobile' => '09489001317', 'address' => 'NABAGATNAN, POBLACION, JORDAN GUIMARAS'],
            ['crew_id' => '219491', 'name' => 'SOPSOP, MARY ANN R', 'relationship' => 'WIFE', 'mobile' => '0966-849-0505', 'address' => 'P.G. ALMENDRAS ST. DANAO CITY, CEBU'],
            ['crew_id' => '219499', 'name' => 'CALAMPINAY, GRACELDA O', 'relationship' => 'WIFE', 'mobile' => '0999-964-3578', 'address' => 'BLOCK 2 LOT 45 GREENTOWN VILL. 1 MAMBOG 2 BACOOR CAVITE'],
            ['crew_id' => '219501', 'name' => 'GARCERON, DANNE DAVIS   F', 'relationship' => 'SELF', 'mobile' => '09177183893', 'address' => 'LOT 19, BLOCK 1, IPIL STREET, MARIKINA HEIGHTS (CONCEPCION), MARIKINA CITY'],
            ['crew_id' => '219503', 'name' => 'DELA TORRE, ARCELI P', 'relationship' => 'WIFE', 'mobile' => '09995995159', 'address' => 'TICUGAN, LOON, BOHOL'],
            ['crew_id' => '219505', 'name' => 'CONDINO, SHIELA R', 'relationship' => 'WIFE', 'mobile' => '0998-905-1378', 'address' => '02093, ESPINA EXTENSION, BARANGAY TAFT, SURIGAO CITY, SURIGAO DEL NORTE'],
            ['crew_id' => '219515', 'name' => 'GRANADA, MARIA TERESA C', 'relationship' => 'WIFE', 'mobile' => '09064908137', 'address' => 'BLK. 7 LOT.31 CAMACHILE SUBD., PASONG CAMACHILE, GEN. TRIAS CAVITE.'],
            ['crew_id' => '219518', 'name' => 'CANALES , DENNIS PAOLO M', 'relationship' => 'SELF', 'mobile' => '09778376062', 'address' => 'SMERDAN NORTH TOWER, SHERIDAN ST, BRGY. HIGHWAY HILLS MANDALUYONG CITY'],
            ['crew_id' => '219546', 'name' => 'ABAYAO, EUGINE T', 'relationship' => 'SELF', 'mobile' => '09950578531', 'address' => 'IBAYONG, BANAUE, IFUGAO'],
            ['crew_id' => '219548', 'name' => 'ABCEDE, ANNALYN L', 'relationship' => 'WIFE', 'mobile' => '09194495766', 'address' => '48 A. MABINI ST. LUCBAN,QUEZON'],
            ['crew_id' => '219560', 'name' => 'ABENION, JOHN RENATO M', 'relationship' => 'SON', 'mobile' => '09353640803', 'address' => 'PUROK SAN PEDRO, BARANGAY MARIA CLARA, MAASIN CITY, SOUTHERN LEYTE'],
            ['crew_id' => '219572', 'name' => 'ABREA, ROEL F', 'relationship' => 'SELF', 'mobile' => '0977-318-7670', 'address' => 'BARANGAY CANDATAG, MALITBOG, SOUTHERN LEYTE'],
            ['crew_id' => '219575', 'name' => 'ABRENICA, FRANNEL A', 'relationship' => 'SELF', 'mobile' => '09192962419', 'address' => 'CUTA LOOBAN BATANGAS CITY, 4200'],
            ['crew_id' => '219585', 'name' => 'ACACIO, EDLYNN V', 'relationship' => 'WIFE', 'mobile' => '09162317358', 'address' => '#178 LIBIA ST., GREENHEIGHTS SUBD., NANGKA, MARIKINA CITY'],
            ['crew_id' => '219588', 'name' => 'ACALING, ROSE ANN MAY ', 'relationship' => 'WIFE', 'mobile' => '09178018067', 'address' => 'L7 B7 VILLA ESTEFANIA, BACOLOD CITY, NEGROS OCCIDENTAL'],
            ['crew_id' => '219590', 'name' => 'ACAYLAR, MYLENE M', 'relationship' => 'WIFE', 'mobile' => '0917-686-1799', 'address' => '#28 RIZAL AVE. EXT., CATBALOGAN, WESTERN SAMAR'],
            ['crew_id' => '219592', 'name' => 'ACEVEDO, ROSELYN B', 'relationship' => 'WIFE', 'mobile' => '0956-071-1937', 'address' => '2324, LEGASPI STREET, ILAYA, BARANGAY II, ROXAS CITY, CAPIZ'],
            ['crew_id' => '219596', 'name' => 'ACOSTA, MARIEL E', 'relationship' => 'WIFE', 'mobile' => '4340588/ 09292263100', 'address' => 'B-2 EMPLOYEES VILL UP DILIMAN QUEZON CITY'],
            ['crew_id' => '220603', 'name' => 'ACUPINPIN, ARLYN D', 'relationship' => 'WIFE', 'mobile' => '0333332300', 'address' => '28TH ST. WESTWOODS SUBD. MAND. ILOILO CITY'],
            ['crew_id' => '220604', 'name' => 'ACUYONG, ANNABELE M', 'relationship' => 'WIFE', 'mobile' => '09283111500', 'address' => 'LOT 4 I-10 FARMVILLE SUBD. PUROK 1 TIKAY MALOLOS CITY BULACAN'],
            ['crew_id' => '219615', 'name' => 'ADEM, ROSEMARIE ', 'relationship' => 'WIFE', 'mobile' => '09655781240', 'address' => 'POBLACION SEASIDE VALENCIA BOHOL'],
            ['crew_id' => '219648', 'name' => 'AGNIR, DENNIS T', 'relationship' => 'SELF', 'mobile' => '09454596557', 'address' => 'BRGY. 55-B, SALET-BULANGON, LAOAG CITY, ILOCOS NORTE'],
            ['crew_id' => '219650', 'name' => 'AGOS, ELENA P', 'relationship' => 'WIFE', 'mobile' => '9052206758', 'address' => 'LOT 1 B4 P5 CITTA ITALIA, BUHAY NA TUBIG RD, IMUS CAVITE'],
            ['crew_id' => '219651', 'name' => 'AGPALZA, KAYCEE ', 'relationship' => 'DAUGHTER', 'mobile' => '09751155717', 'address' => 'B16 L9 CHESTER PLACE BUROL MAIN DASMARINAS CAVITE'],
            ['crew_id' => '219691', 'name' => 'ALAGO, NARIO O', 'relationship' => 'SELF', 'mobile' => '-', 'address' => 'TIGUIS, LILA, BOHOL'],
            ['crew_id' => '219694', 'name' => 'ALAMEDA, JOY S', 'relationship' => 'WIFE', 'mobile' => '09493661213', 'address' => 'BLK 3 LOT 2 VICTORS SUBD., COMMUNAL, BUHANGIN, DAVAO CITY'],
            ['crew_id' => '219741', 'name' => 'ALDOVINO, JAMES A', 'relationship' => 'SELF', 'mobile' => '09178255513', 'address' => 'BARANGAY STO. TOMAS, TINGLOY, BATANGAS'],
            ['crew_id' => '219744', 'name' => 'ALEGORIA, MARY GRACE M', 'relationship' => 'WIFE', 'mobile' => '0915-635-3294, (044)322-2265', 'address' => 'BLOCK 6, LOT 20, PHASE 1, TULIP STREET, GOLDEN HILLS SUBDIVISION, LOMA DE GATO, MARILAO, BULACAN'],
            ['crew_id' => '219745', 'name' => 'ALEGRIA, THERESA J', 'relationship' => 'WIFE', 'mobile' => '0905-424-2196', 'address' => 'TIRING, CABATUAN, ILOILO'],
            ['crew_id' => '219748', 'name' => 'ALEJANDRE, ANALIZA T', 'relationship' => 'WIFE', 'mobile' => '09153265236', 'address' => '#840 ROSE STREET, POBLACION 2, BANSALAN, DAVAO DEL SUR'],
            ['crew_id' => '219751', 'name' => 'ALEJANDRO , SWANIE T', 'relationship' => 'WIFE', 'mobile' => '09166533930', 'address' => 'CAROMANGAY, HAMTIC, ANTIQUE'],
            ['crew_id' => '219752', 'name' => 'ALEJO, EFREN JR. M', 'relationship' => 'SELF', 'mobile' => '9179420191', 'address' => 'QUEZON ST., MIAGAO ILOILO'],
            ['crew_id' => '219768', 'name' => 'ALISDAN, APRIL JOY E', 'relationship' => 'WIFE', 'mobile' => '0908-148-1583', 'address' => 'ED205-F BUYAGAN LA TRINIDAD, BENGUET'],
            ['crew_id' => '219769', 'name' => 'ALLA, SHEILA E', 'relationship' => 'WIFE', 'mobile' => '9498315987', 'address' => 'BLK 1 LOT 8 KATHLEEN PLACE 3 SUBD. E. GARCIA ST. BRGY. PINAGKAISAHAN CUBAO, QUEZON CITY'],
            ['crew_id' => '219774', 'name' => 'ALLONAR, MARYDOL G', 'relationship' => 'WIFE', 'mobile' => '0919-867-7809', 'address' => 'BLOCK 3 LOT 23, BACOLOD HOMES ROYALE, BARANGAY ALIJIS, BACOLOD CITY, NEGROS OCCIDENTAL'],
            ['crew_id' => '219782', 'name' => 'ALMAZAN, DANILO  R', 'relationship' => 'SELF', 'mobile' => '09554538674', 'address' => '18 YAKAL ST. CAREBI SAN VICENTE ANGONO RIZAL'],
            ['crew_id' => '219783', 'name' => 'ALMAZAN, JERRY P', 'relationship' => 'SELF/WIFE', 'mobile' => '0928-389-4217', 'address' => '110, SIXTO M. CACHO STREET, BARANGAY STA. MARIA, CASTILLEJOS, ZAMBALES'],
            ['crew_id' => '219804', 'name' => 'ALUMIA, MARIBEL S', 'relationship' => 'WIFE', 'mobile' => '09454727229 / 09167591600', 'address' => 'PROJ. 5, SOOC, AREVALO ILOILO'],
            ['crew_id' => '219811', 'name' => 'ALVAREZ, ELENA T', 'relationship' => 'WIFE', 'mobile' => '0917-110-4219', 'address' => 'DULCE 6, DOÑA SEGUNDINA TOWNHOMES, NATIONAL HIGHWAY, PUTATAN, MUNTINLUPA CITY'],
            ['crew_id' => '219822', 'name' => 'AMATORIO, AMELITA D', 'relationship' => 'WIFE', 'mobile' => '0921-500-4815', 'address' => 'BLK. 18 L-2 BAMBOO ORCHARD, BANAYBANAY, CABUYAO, LAGUNA'],
            ['crew_id' => '219828', 'name' => 'AMIHAN, HAZEL B', 'relationship' => 'WIFE', 'mobile' => '09693052409', 'address' => 'BRGY PAGDUGUE, DUMANGAS, ILOILO CITY'],
            ['crew_id' => '219831', 'name' => 'AMISTOSO, ROWENA S', 'relationship' => 'WIFE', 'mobile' => '09277641598', 'address' => 'IMELDA LABASON, ZAMBOANGA DEL NORTE'],
            ['crew_id' => '219832', 'name' => 'AMLOS, CLIFFTON O', 'relationship' => 'SELF', 'mobile' => '09752835371', 'address' => 'BRGY. TACTAC, STA. FE NUEVA VIZCAYA'],
            ['crew_id' => '219836', 'name' => 'AMONSOT, MARIAN F', 'relationship' => 'WIFE', 'mobile' => '09395638810', 'address' => '15 LIGONES STREET, BOOL DISTRICT, TAGBILARAN CITY, BOHOL'],
            ['crew_id' => '219843', 'name' => 'AMPIT, FELIX ', 'relationship' => 'SELF', 'mobile' => '09260772572', 'address' => 'LUMBAY, PILAR, BOHOL'],
            ['crew_id' => '219849', 'name' => 'ANA, JIMMY A', 'relationship' => 'SELF', 'mobile' => '9364140700', 'address' => 'I. ABENIS ST. BORONGAN CITY, E. SAMAR'],
            ['crew_id' => '219852', 'name' => 'ANCHETA, MARIBETH G', 'relationship' => 'WIFE', 'mobile' => '0928-310-1333', 'address' => '161, BARANGAY SAN VICENTE SUR, AGOO, LA UNION'],
            ['crew_id' => '219855', 'name' => 'ANCHETA, MA. LOURDES M', 'relationship' => 'WIFE', 'mobile' => '043-7271255', 'address' => 'B3 LOT 8 VILLA MARIA SUBD. SAMBAT SAN PASCUAL,  BATANGAS'],
            ['crew_id' => '219863', 'name' => 'ANDIANO, RAYMUNDO OR, ANDIANO ALMARIE ', 'relationship' => 'SELF/WIFE', 'mobile' => '09989763863', 'address' => 'BLOCK 18, LOT 6, PHASE 9, DECCA EXECUTIVE HOMES, BARANGAY TACUNAN, DAVAO CITY, DAVAO DEL SUR'],
            ['crew_id' => '221687', 'name' => 'DREU, MARIBETH D', 'relationship' => 'WIFE', 'mobile' => '09618308987', 'address' => '#33 STA. CRUZ CANDATING ARAYAT PAMPANGA'],
            ['crew_id' => '221693', 'name' => 'DUAY, ELLEN  C', 'relationship' => 'WIFE', 'mobile' => '09299744027', 'address' => '88 BANABA WEST BATANGAS CITY'],
            ['crew_id' => '221699', 'name' => 'DUEÑAS, MELIZA P', 'relationship' => 'WIFE', 'mobile' => '09284819615', 'address' => 'BLOCK 7, LOT 1, JEM SUBDIVISION, BARANGAY GUZMAN-JESENA, ILOILO CITY, ILOILO 5000'],
            ['crew_id' => '221707', 'name' => 'DULALAS, SHERRY LYN S', 'relationship' => 'WIFE', 'mobile' => '09397654620', 'address' => 'PUROK 2, CATARMAN, DAUIS, BOHOL'],
            ['crew_id' => '221720', 'name' => 'DUMA, MARIE JOSEPHINE G', 'relationship' => 'WIFE', 'mobile' => '9178940281', 'address' => 'TAMBLOT ST. TAGBILARAN CITY, BOHOL'],
            ['crew_id' => '221721', 'name' => 'DUMAGAT, JOSELYN T', 'relationship' => 'WIFE', 'mobile' => '09264173359', 'address' => '971B GOLDEN RIBBON, BUTUAN CITY'],
            ['crew_id' => '221731', 'name' => 'DUNGO, MARIA KATHERINE P', 'relationship' => 'WIFE', 'mobile' => '9157141544', 'address' => '#810 BRGY. STO. DOMINGO, MINALIN, PAMPANGA'],
            ['crew_id' => '221734', 'name' => 'DURA, VILMA P', 'relationship' => 'WIFE', 'mobile' => '09611672747', 'address' => '215 CALANGAY SAN NICOLAS BATANGAS'],
            ['crew_id' => '221748', 'name' => 'EBORDA, MARITES ', 'relationship' => 'WIFE', 'mobile' => '0917-620-9102', 'address' => 'BLK 7 LOT 6 3-D CAMELLA HOMES LAWA-AN TALISAY CITY CEBU'],
            ['crew_id' => '221759', 'name' => 'ECHON, JOCELYN D', 'relationship' => 'WIFE', 'mobile' => '09389272763 / 09982582725', 'address' => 'E-159 P-1 DIRITA, BALOGUTEN - IBA, ZAMBALES'],
            ['crew_id' => '221768', 'name' => 'EDONG, ALONA B', 'relationship' => 'WIFE', 'mobile' => '09088100367', 'address' => 'BLK-7 LOT 50 CHINA ST. BARCELONA 4 B.N.T. IMUS, CAVITE'],
            ['crew_id' => '221769', 'name' => 'EDRADA, YOLANDA  B', 'relationship' => 'WIFE', 'mobile' => '09674237094', 'address' => 'SOUTH SAN JOSE ST., MOLO, ILOILO CITY'],
            ['crew_id' => '221808', 'name' => 'ENERLAN, LYNN R', 'relationship' => 'WIFE', 'mobile' => '09063815097', 'address' => 'LERIO, ACMAC, ILIGAN CITY.'],
            ['crew_id' => '221833', 'name' => 'ESCABILLAS, MA. TERESA J', 'relationship' => 'WIFE', 'mobile' => '0928-979-8956', 'address' => '11-A JEREOS EXT. LAPAZ ILOILO CITY'],
        ];

        foreach ($alloteeData as $data) {
            $user = User::where('crew_id', $data['crew_id'])->first();

            if ($user) {
                // Clean and truncate mobile number to fit database constraints
                $mobileNumber = $this->cleanMobileNumber($data['mobile']);

                Allotee::firstOrCreate([
                    'name' => $data['name'],
                    'relationship' => $data['relationship'],
                    'mobile_number' => $mobileNumber,
                    'address' => $data['address'],
                ]);
            }
        }
    }

    /**
     * Clean and format mobile number
     */
    private function cleanMobileNumber(string $mobile): string
    {
        // Remove extra spaces and clean
        $mobile = trim($mobile);

        // If there are multiple numbers separated by comma or slash, take the first one
        if (strpos($mobile, ',') !== false) {
            $mobile = trim(explode(',', $mobile)[0]);
        }

        if (strpos($mobile, '/') !== false) {
            $mobile = trim(explode('/', $mobile)[0]);
        }

        // Limit to 20 characters to fit database constraints
        return substr($mobile, 0, 20);
    }
}
