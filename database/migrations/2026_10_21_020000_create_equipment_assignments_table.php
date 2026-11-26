<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Affectations de matériel gérées par le Comptable des matières.
 *
 * Justification de la table dédiée (plutôt que stock_movements.metadata) :
 * l'état d'une affectation (affectee / retournee / perdue) est interrogé
 * en permanence — inventaire, relances, historique par agent — et doit
 * être indexé, ce qu'un blob JSON ne permet pas proprement.
 *
 * Le carburant ne requiert PAS de table : il est suivi par les lignes
 * stock_movements où produit/unite dénotent le carburant (unite = 'L'
 * ou produit contenant carburant/gasoil/essence), avec numero_bon,
 * fournisseur et destinataire déjà présents dans le schéma existant.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('equipment_assignments')) {
            Schema::create('equipment_assignments', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('warehouse_id')->nullable()
                    ->comment('Magasin d\'où sort le matériel');
                $table->string('produit')->comment('Désignation du matériel');
                $table->string('unite', 20)->nullable()->default('pièce');
                $table->decimal('quantity', 10, 2)->default(1);
                $table->string('agent_name')->comment('Agent bénéficiaire');
                $table->unsignedBigInteger('assigned_to')->nullable()
                    ->comment('Utilisateur bénéficiaire si compte existant');
                $table->string('numero_bon')->nullable()
                    ->comment('Référence du bon d\'affectation / justificatif');
                $table->string('status')->default('affectee')
                    ->comment('affectee | retournee | perdue');
                $table->unsignedBigInteger('assigned_by')
                    ->comment('Comptable qui a enregistré l\'affectation');
                $table->timestamp('assigned_at');
                $table->timestamp('returned_at')->nullable();
                $table->unsignedBigInteger('returned_by')->nullable();
                $table->text('return_note')->nullable()
                    ->comment('État du matériel au retour / motif de perte');
                $table->timestamps();

                $table->index('status');
                $table->index('agent_name');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('equipment_assignments');
    }
};
