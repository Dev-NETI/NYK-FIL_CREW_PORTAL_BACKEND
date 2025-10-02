<?php

namespace Database\Seeders;

use App\Models\Allotee;
use App\Models\CrewAllotee;
use App\Models\User;
use Illuminate\Database\Seeder;

class CrewAlloteeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $relationships = [
            ['crew_id' => '219454', 'allotee_name' => 'CALSEÑA, APRIL ROSE D'],
            ['crew_id' => '219456', 'allotee_name' => 'DUPIT, JENEBER U'],
            ['crew_id' => '219465', 'allotee_name' => 'FLORENTINO, CHLARIZ T'],
            ['crew_id' => '219471', 'allotee_name' => 'TONDO, JISZA T'],
            ['crew_id' => '219480', 'allotee_name' => 'GARCIA, CHERIE V'],
            ['crew_id' => '219482', 'allotee_name' => 'PRIETO, JONALYN R'],
            ['crew_id' => '219484', 'allotee_name' => 'CILLACAY, ANA MARIE A'],
            ['crew_id' => '219486', 'allotee_name' => 'MORENO, LEOLYN  B'],
            ['crew_id' => '219491', 'allotee_name' => 'SOPSOP, MARY ANN R'],
            ['crew_id' => '219499', 'allotee_name' => 'CALAMPINAY, GRACELDA O'],
            ['crew_id' => '219501', 'allotee_name' => 'GARCERON, DANNE DAVIS   F'],
            ['crew_id' => '219503', 'allotee_name' => 'DELA TORRE, ARCELI P'],
            ['crew_id' => '219505', 'allotee_name' => 'CONDINO, SHIELA R'],
            ['crew_id' => '219515', 'allotee_name' => 'GRANADA, MARIA TERESA C'],
            ['crew_id' => '219518', 'allotee_name' => 'CANALES , DENNIS PAOLO M'],
            ['crew_id' => '219546', 'allotee_name' => 'ABAYAO, EUGINE T'],
            ['crew_id' => '219548', 'allotee_name' => 'ABCEDE, ANNALYN L'],
            ['crew_id' => '219560', 'allotee_name' => 'ABENION, JOHN RENATO M'],
            ['crew_id' => '219572', 'allotee_name' => 'ABREA, ROEL F'],
            ['crew_id' => '219575', 'allotee_name' => 'ABRENICA, FRANNEL A'],
            ['crew_id' => '219585', 'allotee_name' => 'ACACIO, EDLYNN V'],
            ['crew_id' => '219588', 'allotee_name' => 'ACALING, ROSE ANN MAY '],
            ['crew_id' => '219590', 'allotee_name' => 'ACAYLAR, MYLENE M'],
            ['crew_id' => '219592', 'allotee_name' => 'ACEVEDO, ROSELYN B'],
            ['crew_id' => '219596', 'allotee_name' => 'ACOSTA, MARIEL E'],
            ['crew_id' => '220603', 'allotee_name' => 'ACUPINPIN, ARLYN D'],
            ['crew_id' => '220604', 'allotee_name' => 'ACUYONG, ANNABELE M'],
            ['crew_id' => '219615', 'allotee_name' => 'ADEM, ROSEMARIE '],
            ['crew_id' => '219648', 'allotee_name' => 'AGNIR, DENNIS T'],
            ['crew_id' => '219650', 'allotee_name' => 'AGOS, ELENA P'],
            ['crew_id' => '219651', 'allotee_name' => 'AGPALZA, KAYCEE '],
            ['crew_id' => '219691', 'allotee_name' => 'ALAGO, NARIO O'],
            ['crew_id' => '219694', 'allotee_name' => 'ALAMEDA, JOY S'],
            ['crew_id' => '219741', 'allotee_name' => 'ALDOVINO, JAMES A'],
            ['crew_id' => '219744', 'allotee_name' => 'ALEGORIA, MARY GRACE M'],
            ['crew_id' => '219745', 'allotee_name' => 'ALEGRIA, THERESA J'],
            ['crew_id' => '219748', 'allotee_name' => 'ALEJANDRE, ANALIZA T'],
            ['crew_id' => '219751', 'allotee_name' => 'ALEJANDRO , SWANIE T'],
            ['crew_id' => '219752', 'allotee_name' => 'ALEJO, EFREN JR. M'],
            ['crew_id' => '219768', 'allotee_name' => 'ALISDAN, APRIL JOY E'],
            ['crew_id' => '219769', 'allotee_name' => 'ALLA, SHEILA E'],
            ['crew_id' => '219774', 'allotee_name' => 'ALLONAR, MARYDOL G'],
            ['crew_id' => '219782', 'allotee_name' => 'ALMAZAN, DANILO  R'],
            ['crew_id' => '219783', 'allotee_name' => 'ALMAZAN, JERRY P'],
            ['crew_id' => '219804', 'allotee_name' => 'ALUMIA, MARIBEL S'],
            ['crew_id' => '219811', 'allotee_name' => 'ALVAREZ, ELENA T'],
            ['crew_id' => '219822', 'allotee_name' => 'AMATORIO, AMELITA D'],
            ['crew_id' => '219828', 'allotee_name' => 'AMIHAN, HAZEL B'],
            ['crew_id' => '219831', 'allotee_name' => 'AMISTOSO, ROWENA S'],
            ['crew_id' => '219832', 'allotee_name' => 'AMLOS, CLIFFTON O'],
            ['crew_id' => '219836', 'allotee_name' => 'AMONSOT, MARIAN F'],
            ['crew_id' => '219843', 'allotee_name' => 'AMPIT, FELIX '],
            ['crew_id' => '219849', 'allotee_name' => 'ANA, JIMMY A'],
            ['crew_id' => '219852', 'allotee_name' => 'ANCHETA, MARIBETH G'],
            ['crew_id' => '219855', 'allotee_name' => 'ANCHETA, MA. LOURDES M'],
            ['crew_id' => '219863', 'allotee_name' => 'ANDIANO, RAYMUNDO OR, ANDIANO ALMARIE '],
            ['crew_id' => '221687', 'allotee_name' => 'DREU, MARIBETH D'],
            ['crew_id' => '221693', 'allotee_name' => 'DUAY, ELLEN  C'],
            ['crew_id' => '221699', 'allotee_name' => 'DUEÑAS, MELIZA P'],
            ['crew_id' => '221707', 'allotee_name' => 'DULALAS, SHERRY LYN S'],
            ['crew_id' => '221720', 'allotee_name' => 'DUMA, MARIE JOSEPHINE G'],
            ['crew_id' => '221721', 'allotee_name' => 'DUMAGAT, JOSELYN T'],
            ['crew_id' => '221731', 'allotee_name' => 'DUNGO, MARIA KATHERINE P'],
            ['crew_id' => '221734', 'allotee_name' => 'DURA, VILMA P'],
            ['crew_id' => '221748', 'allotee_name' => 'EBORDA, MARITES '],
            ['crew_id' => '221759', 'allotee_name' => 'ECHON, JOCELYN D'],
            ['crew_id' => '221768', 'allotee_name' => 'EDONG, ALONA B'],
            ['crew_id' => '221769', 'allotee_name' => 'EDRADA, YOLANDA  B'],
            ['crew_id' => '221808', 'allotee_name' => 'ENERLAN, LYNN R'],
            ['crew_id' => '221833', 'allotee_name' => 'ESCABILLAS, MA. TERESA J'],
        ];

        foreach ($relationships as $data) {
            // Since crew_id is in user_profiles table, query through the relationship
            $user = User::whereHas('profile', function($query) use ($data) {
                $query->where('crew_id', $data['crew_id']);
            })->first();
            $allotee = Allotee::where('name', $data['allotee_name'])->first();

            if ($user && $allotee) {
                // Check if it's a SELF relationship or primary relationship (WIFE/SON/DAUGHTER)
                $isPrimary = in_array($allotee->relationship, ['WIFE', 'SON', 'DAUGHTER']) ||
                    $allotee->relationship === 'SELF';

                CrewAllotee::firstOrCreate([
                    'user_id' => $user->id,
                    'allotee_id' => $allotee->id,
                ], [
                    'is_primary' => $isPrimary,
                    'is_emergency_contact' => true, // Assuming all are emergency contacts for now
                ]);

                // Update the user's employment primary_allotee_id if this is primary
                if ($isPrimary && $user->employment && !$user->employment->primary_allotee_id) {
                    $user->employment->update(['primary_allotee_id' => $allotee->id]);
                }
            }
        }
    }
}
