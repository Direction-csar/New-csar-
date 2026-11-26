<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Circuit opérationnel de gestion des stocks :
 * DG (instruction) → DTL (affectation inspecteur) → Inspecteur (consignes magasinier)
 * → Magasinier (exécution) → Inspecteur (vérification) → stock officiel.
 *
 * Les mouvements liés portent stock_operation_id et un statut
 * 'en_attente' : ils ne comptent dans le stock officiel qu'après
 * vérification ('verifie').
 */
return new class extends Migration
{
    public const STATUTS = [
        'soumise',              // créée par la DG, en attente de prise en charge DTL
        'affectee_inspecteur',  // DTL a désigné l'inspecteur
        'transmise_magasinier', // inspecteur a transmis les consignes opérationnelles
        'executee',             // magasinier a enregistré la/les opération(s) réelle(s)
        'verifiee',             // inspecteur a confirmé : stock officiel mis à jour
        'anomalie',             // inspecteur a signalé une anomalie
        'rejetee',              // inspecteur/DG a rejeté l'opération
        'annulee',              // annulée avant exécution
    ];

    public function up(): void
    {
        if (!Schema::hasTable('stock_operations')) {
            Schema::create('stock_operations', function (Blueprint $table) {
                $table->id();
                $table->string('reference')->unique();
                $table->string('type')->index(); // entry | exit | transfer | inventory
                $table->foreignId('warehouse_id')->constrained('warehouses');
                $table->foreignId('destination_warehouse_id')->nullable()->constrained('warehouses');

                // Produit / conditionnement demandé
                $table->string('produit')->nullable();
                $table->string('unite')->nullable();       // kg | L
                $table->unsignedSmallInteger('format')->nullable(); // contenant : 25, 50...
                $table->decimal('quantity_requested', 14, 2);        // contenants demandés
                $table->decimal('quantity_requested_kg', 14, 2)->nullable();

                // Réellement exécuté (renseigné par le magasinier)
                $table->decimal('quantity_executed', 14, 2)->nullable();
                $table->decimal('quantity_executed_kg', 14, 2)->nullable();

                $table->text('instructions')->nullable();   // consigne DG
                $table->text('dtl_comment')->nullable();
                $table->text('inspector_comment')->nullable(); // consignes opérationnelles
                $table->text('anomaly_report')->nullable();
                $table->text('rejection_reason')->nullable();
                $table->json('attachments')->nullable();    // photos / justificatifs

                $table->string('status')->default('soumise')->index();

                // Circuit
                $table->foreignId('created_by')->constrained('users');                 // DG
                $table->foreignId('dtl_assigned_by')->nullable()->constrained('users');
                $table->timestamp('dtl_assigned_at')->nullable();
                $table->foreignId('assigned_inspector_id')->nullable()->constrained('users');
                $table->timestamp('inspector_transmitted_at')->nullable();
                $table->foreignId('assigned_magasinier_id')->nullable()->constrained('users');
                $table->foreignId('executed_by')->nullable()->constrained('users');    // magasinier
                $table->timestamp('executed_at')->nullable();
                $table->foreignId('verified_by')->nullable()->constrained('users');    // inspecteur
                $table->timestamp('verified_at')->nullable();

                $table->timestamps();
                $table->softDeletes();

                $table->index(['warehouse_id', 'status']);
                $table->index(['assigned_inspector_id', 'status']);
                $table->index(['assigned_magasinier_id', 'status']);
            });
        }

        if (Schema::hasTable('stock_movements')) {
            Schema::table('stock_movements', function (Blueprint $table) {
                if (!Schema::hasColumn('stock_movements', 'stock_operation_id')) {
                    $table->foreignId('stock_operation_id')
                        ->nullable()
                        ->after('request_mission_id')
                        ->constrained('stock_operations')
                        ->nullOnDelete();
                }
                if (!Schema::hasColumn('stock_movements', 'verified_by')) {
                    // verified_by/validated_by existent déjà dans certains schémas ;
                    // on ne touche qu'à ce qui manque.
                }
            });

            // Suppression logique : aucune opération ne disparaît sans trace.
            if (!Schema::hasColumn('stock_movements', 'deleted_at')) {
                Schema::table('stock_movements', function (Blueprint $table) {
                    $table->softDeletes();
                });
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('stock_movements') && Schema::hasColumn('stock_movements', 'stock_operation_id')) {
            Schema::table('stock_movements', function (Blueprint $table) {
                $table->dropConstrainedForeignId('stock_operation_id');
            });
        }
        if (Schema::hasTable('stock_movements') && Schema::hasColumn('stock_movements', 'deleted_at')) {
            Schema::table('stock_movements', function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }
        Schema::dropIfExists('stock_operations');
    }
};
