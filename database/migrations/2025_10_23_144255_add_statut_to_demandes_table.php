<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('demandes', function (Blueprint $table) {
            if (!Schema::hasColumn('demandes', 'statut')) {
                $table->enum('statut', ['en_attente', 'en_cours', 'traitee', 'rejetee'])->default('en_attente');
            }
            if (!Schema::hasColumn('demandes', 'reponse')) {
                $table->text('reponse')->nullable();
            }
            if (!Schema::hasColumn('demandes', 'date_traitement')) {
                $table->timestamp('date_traitement')->nullable();
            }
            if (!Schema::hasColumn('demandes', 'traite_par')) {
                $table->unsignedBigInteger('traite_par')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('demandes', function (Blueprint $table) {
            $table->dropColumn(['statut', 'reponse', 'date_traitement', 'traite_par']);
        });
    }
};
