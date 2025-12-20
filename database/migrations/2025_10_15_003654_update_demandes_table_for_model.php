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
            // Ajouter les champs manquants pour le modèle Demande
            if (!Schema::hasColumn('demandes', 'code_suivi')) $table->string('code_suivi')->nullable();
            if (!Schema::hasColumn('demandes', 'nom_demandeur')) $table->string('nom_demandeur')->nullable();
            if (!Schema::hasColumn('demandes', 'statut')) $table->string('statut')->default('en_attente');
            if (!Schema::hasColumn('demandes', 'region')) $table->string('region')->nullable();
            if (!Schema::hasColumn('demandes', 'commune')) $table->string('commune')->nullable();
            if (!Schema::hasColumn('demandes', 'departement')) $table->string('departement')->nullable();
            if (!Schema::hasColumn('demandes', 'adresse')) $table->text('adresse')->nullable();
            if (!Schema::hasColumn('demandes', 'date_demande')) $table->date('date_demande')->nullable();
            if (!Schema::hasColumn('demandes', 'date_traitement')) $table->date('date_traitement')->nullable();
            if (!Schema::hasColumn('demandes', 'commentaire_admin')) $table->text('commentaire_admin')->nullable();
            if (!Schema::hasColumn('demandes', 'assignee_id')) $table->unsignedBigInteger('assignee_id')->nullable();
            if (!Schema::hasColumn('demandes', 'priorite')) $table->string('priorite')->default('moyenne');
            if (!Schema::hasColumn('demandes', 'latitude')) $table->decimal('latitude', 10, 8)->nullable();
            if (!Schema::hasColumn('demandes', 'longitude')) $table->decimal('longitude', 11, 8)->nullable();
            if (!Schema::hasColumn('demandes', 'sms_envoye')) $table->boolean('sms_envoye')->default(false);
            if (!Schema::hasColumn('demandes', 'sms_message_id')) $table->string('sms_message_id')->nullable();
            if (!Schema::hasColumn('demandes', 'sms_envoye_at')) $table->timestamp('sms_envoye_at')->nullable();
        });
        
        // Ajouter les index - skip if exist
        try {
            Schema::table('demandes', function (Blueprint $table) {
                $table->index('code_suivi');
                $table->index('statut');
                $table->index('region');
                $table->index('assignee_id');
                $table->index('created_at');
            });
        } catch (\Exception $e) {
            // Indexes may already exist
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('demandes', function (Blueprint $table) {
            // Supprimer les champs ajoutés
            $table->dropColumn([
                'code_suivi', 'nom_demandeur', 'statut', 'region', 'commune', 
                'departement', 'adresse', 'date_demande', 'date_traitement', 
                'commentaire_admin', 'assignee_id', 'priorite', 'latitude', 
                'longitude', 'sms_envoye', 'sms_message_id', 'sms_envoye_at'
            ]);
        });
    }
};
