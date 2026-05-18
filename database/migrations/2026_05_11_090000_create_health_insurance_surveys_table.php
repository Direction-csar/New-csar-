<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('health_insurance_surveys', function (Blueprint $table) {
            $table->id();

            // Identité (optionnelle - anonyme possible)
            $table->string('agent_nom')->nullable();
            $table->string('agent_prenom')->nullable();
            $table->string('agent_direction')->nullable();
            $table->string('agent_region')->nullable();
            $table->boolean('is_anonymous')->default(false);

            // I. Compréhension et accessibilité
            $table->enum('q1_info_level', ['totalement', 'partiellement', 'non'])->nullable();
            $table->enum('q2_documents_clarity', ['tres_clairs', 'moyennement', 'peu_clairs'])->nullable();
            $table->enum('q3_difficulty', ['jamais', 'parfois', 'souvent'])->nullable();

            // II. Qualité des prestations
            $table->enum('q4_soins_response', ['largement', 'avec_limites', 'non'])->nullable();
            $table->enum('q5_panier_soins', ['tres_suffisant', 'assez_suffisant', 'insuffisant'])->nullable();
            $table->enum('q6_delais_remboursement', ['rapides', 'acceptables', 'longs'])->nullable();
            $table->enum('q7_service_client', ['oui', 'non'])->nullable();
            $table->text('q8_probleme_recent')->nullable();

            // III. Satisfaction et accessibilité
            $table->enum('q9_coassurance', ['tres_satisfait', 'satisfait', 'pas_satisfait', 'autre'])->nullable();
            $table->string('q9_autre')->nullable();
            $table->enum('q10_reseau_soins', ['tres_accessible', 'accessible', 'pas_accessible', 'autre'])->nullable();
            $table->string('q10_autre')->nullable();

            // IV. Suggestions
            $table->json('q11_aspects')->nullable();
            $table->string('q11_autre')->nullable();
            $table->text('q12_propositions')->nullable();

            // V. Évaluation finale
            $table->tinyInteger('q13_note')->nullable()->comment('1-5');

            $table->ipAddress('ip_address')->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->timestamp('submitted_at')->useCurrent();
            $table->timestamps();

            $table->index('q13_note');
            $table->index('agent_direction');
            $table->index('submitted_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('health_insurance_surveys');
    }
};
