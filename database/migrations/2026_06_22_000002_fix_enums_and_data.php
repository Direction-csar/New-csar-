<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Nettoyer les valeurs invalides avant de modifier les enums
        $tables = [
            'q1_info_level'          => ['totalement','partiellement','non','pas_concerne'],
            'q2_documents_clarity'   => ['tres_clairs','moyennement','peu_clairs','pas_concerne'],
            'q3_difficulty'          => ['jamais','parfois','souvent','pas_concerne'],
            'q4_soins_response'      => ['largement','avec_limites','non','pas_concerne'],
            'q5_panier_soins'        => ['tres_suffisant','assez_suffisant','insuffisant','pas_concerne'],
            'q6_delais_remboursement'=> ['rapides','acceptables','longs','pas_concerne'],
            'q7_service_client'      => ['oui','non','pas_concerne'],
            'q9_coassurance'         => ['tres_satisfait','satisfait','pas_satisfait','autre','pas_concerne'],
            'q10_reseau_soins'       => ['tres_accessible','accessible','pas_accessible','autre','pas_concerne'],
        ];

        foreach ($tables as $col => $valid) {
            $validStr = implode("','", $valid);
            DB::statement("UPDATE health_insurance_surveys SET {$col} = NULL WHERE {$col} NOT IN ('{$validStr}') OR {$col} = ''");
        }

        // Modifier les enums
        DB::statement("ALTER TABLE health_insurance_surveys MODIFY q1_info_level ENUM('totalement','partiellement','non','pas_concerne') NULL");
        DB::statement("ALTER TABLE health_insurance_surveys MODIFY q2_documents_clarity ENUM('tres_clairs','moyennement','peu_clairs','pas_concerne') NULL");
        DB::statement("ALTER TABLE health_insurance_surveys MODIFY q3_difficulty ENUM('jamais','parfois','souvent','pas_concerne') NULL");
        DB::statement("ALTER TABLE health_insurance_surveys MODIFY q4_soins_response ENUM('largement','avec_limites','non','pas_concerne') NULL");
        DB::statement("ALTER TABLE health_insurance_surveys MODIFY q5_panier_soins ENUM('tres_suffisant','assez_suffisant','insuffisant','pas_concerne') NULL");
        DB::statement("ALTER TABLE health_insurance_surveys MODIFY q6_delais_remboursement ENUM('rapides','acceptables','longs','pas_concerne') NULL");
        DB::statement("ALTER TABLE health_insurance_surveys MODIFY q7_service_client ENUM('oui','non','pas_concerne') NULL");
        DB::statement("ALTER TABLE health_insurance_surveys MODIFY q9_coassurance ENUM('tres_satisfait','satisfait','pas_satisfait','autre','pas_concerne') NULL");
        DB::statement("ALTER TABLE health_insurance_surveys MODIFY q10_reseau_soins ENUM('tres_accessible','accessible','pas_accessible','autre','pas_concerne') NULL");
    }

    public function down(): void
    {
        // Pas de rollback
    }
};
