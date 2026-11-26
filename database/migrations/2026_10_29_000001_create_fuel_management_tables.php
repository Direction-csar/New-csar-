<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Module « Gestion du carburant » — Comptable des matières.
 *
 * Trois flux métier strictement distincts :
 *  - fuel_entries        : approvisionnements (entrées de stock en litres)
 *  - fuel_exits          : sorties / dotations aux chauffeurs et véhicules
 *  - fuel_reimbursements : remboursements de carburant avancé par un agent
 *                          (SANS mouvement de stock physique)
 *
 * fuel_attachments  : pièces justificatives polymorphes (entrées, sorties,
 *                     remboursements).
 * fuel_status_logs  : historique non modifiable des transitions de statut
 *                     (qui, quand, action, motif).
 *
 * Le stock physique reste dans stock_movements : une ligne unite='L'
 * statut 'validated' est écrite à la validation d'une entrée ou sortie,
 * reliée par metadata.fuel_entry_id / metadata.fuel_exit_id.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('fuel_entries')) {
            Schema::create('fuel_entries', function (Blueprint $table) {
                $table->id();
                $table->string('reference')->unique()
                    ->comment('Référence unique générée : ENT-CARB-YYYY-NNNNN');
                $table->date('operation_date')->index();
                $table->unsignedBigInteger('warehouse_id')->index()
                    ->comment('Magasin / cuve destinataire');
                $table->string('supplier')->comment('Fournisseur');
                $table->string('fuel_type', 40)->comment('essence | gasoil | petrole | autre');
                $table->string('fuel_type_label')->nullable()
                    ->comment('Libellé si fuel_type = autre');
                $table->decimal('quantity', 12, 2)->comment('Litres');
                $table->decimal('unit_price', 12, 2)->comment('FCFA / litre');
                $table->decimal('total_amount', 14, 2)->comment('quantity × unit_price');
                $table->string('invoice_number')->nullable();
                $table->date('invoice_date')->nullable();
                $table->string('delivery_note_ref')->nullable()
                    ->comment('Référence du bon de livraison');
                $table->string('funding_source')->nullable();
                $table->text('notes')->nullable();
                $table->string('status')->default('draft')->index()
                    ->comment('draft | validated | rejected | cancelled');
                $table->unsignedBigInteger('created_by')->index();
                $table->unsignedBigInteger('validated_by')->nullable();
                $table->timestamp('validated_at')->nullable();
                $table->text('rejection_reason')->nullable();
                $table->unsignedBigInteger('stock_movement_id')->nullable()
                    ->comment('Ligne stock_movements écrite à la validation');
                $table->string('idempotency_key')->nullable()->unique()
                    ->comment('Protection anti double-soumission');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('fuel_exits')) {
            Schema::create('fuel_exits', function (Blueprint $table) {
                $table->id();
                $table->string('exit_number')->unique()
                    ->comment('Bon de sortie : SOR-CARB-YYYY-NNNNN');
                $table->date('operation_date')->index();
                $table->unsignedBigInteger('warehouse_id')->index();
                $table->string('fuel_type', 40);
                $table->string('fuel_type_label')->nullable();
                $table->string('beneficiary_name')->comment('Chauffeur / bénéficiaire');
                $table->unsignedBigInteger('beneficiary_id')->nullable()
                    ->comment('Agent du personnel, si connu');
                $table->string('vehicle_label')->nullable()
                    ->comment('Véhicule (libellé libre)');
                $table->string('vehicle_plate')->nullable()->index()
                    ->comment('Immatriculation');
                $table->string('destination')->nullable();
                $table->string('purpose')->comment('Motif de la sortie');
                $table->string('mission_number')->nullable();
                $table->date('mission_start_date')->nullable();
                $table->date('mission_end_date')->nullable();
                $table->string('authorization_ref')->nullable()
                    ->comment('Ordre de mission / autorisation');
                $table->decimal('quantity_requested', 12, 2);
                $table->decimal('quantity_authorized', 12, 2)->nullable();
                $table->decimal('quantity_delivered', 12, 2)->nullable()
                    ->comment('Litres réellement délivrés — alimente le stock');
                $table->unsignedInteger('odometer_start')->nullable();
                $table->unsignedInteger('odometer_end')->nullable();
                $table->string('delivered_by_name')->nullable()
                    ->comment('Agent qui délivre physiquement');
                $table->text('notes')->nullable();
                $table->string('status')->default('draft')->index()
                    ->comment('draft | authorized | validated | rejected | cancelled');
                $table->unsignedBigInteger('created_by')->index();
                $table->unsignedBigInteger('authorized_by')->nullable();
                $table->timestamp('authorized_at')->nullable();
                $table->unsignedBigInteger('validated_by')->nullable();
                $table->timestamp('validated_at')->nullable();
                $table->text('rejection_reason')->nullable();
                $table->unsignedBigInteger('stock_movement_id')->nullable();
                $table->string('idempotency_key')->nullable()->unique();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('fuel_reimbursements')) {
            Schema::create('fuel_reimbursements', function (Blueprint $table) {
                $table->id();
                $table->string('reference')->unique()
                    ->comment('REM-CARB-YYYY-NNNNN');
                $table->string('agent_name')->index()->comment('Agent demandeur');
                $table->unsignedBigInteger('agent_id')->nullable()
                    ->comment('Compte utilisateur / personnel, si connu');
                $table->string('service')->nullable()->comment('Service / direction');
                $table->date('expense_date');
                $table->string('purpose')->comment('Motif de la dépense');
                $table->string('activity_ref')->nullable()
                    ->comment('Mission / atelier / activité (référence)');
                $table->string('activity_type', 30)->nullable()
                    ->comment('mission | atelier | activite');
                $table->string('vehicle_label')->nullable();
                $table->string('vehicle_plate')->nullable();
                $table->decimal('amount_claimed', 14, 2)
                    ->comment('Montant avancé déclaré par l\'agent');
                $table->decimal('quantity_liters', 12, 2)->nullable();
                $table->string('receipt_ref')->nullable()
                    ->comment('N° facture / reçu de la station');
                $table->decimal('amount_requested', 14, 2);
                $table->decimal('amount_approved', 14, 2)->nullable();
                $table->decimal('amount_paid', 14, 2)->nullable();
                $table->decimal('advance_amount', 14, 2)->nullable()
                    ->comment('Avance consentie à l\'agent');
                $table->decimal('justified_amount', 14, 2)->nullable()
                    ->comment('Justificatifs acceptés');
                $table->decimal('complement_amount', 14, 2)->nullable()
                    ->comment('Complément payé ou reliquat restitué');
                $table->string('payment_ref')->nullable();
                $table->date('payment_date')->nullable();
                $table->string('status')->default('draft')->index()
                    ->comment('draft | submitted | in_review | approved | rejected | paid | closed');
                $table->unsignedBigInteger('created_by')->index();
                $table->unsignedBigInteger('reviewed_by')->nullable();
                $table->unsignedBigInteger('approved_by')->nullable();
                $table->timestamp('approved_at')->nullable();
                $table->unsignedBigInteger('paid_by')->nullable();
                $table->timestamp('paid_at')->nullable();
                $table->text('review_comment')->nullable();
                $table->text('rejection_reason')->nullable();
                $table->string('duplicate_of_ref')->nullable()
                    ->comment('Référence du doublon suspecté (avertissement, non bloquant)');
                $table->string('idempotency_key')->nullable()->unique();
                $table->timestamps();

                $table->index(['agent_name', 'expense_date']);
                $table->index('receipt_ref');
            });
        }

        if (!Schema::hasTable('fuel_attachments')) {
            Schema::create('fuel_attachments', function (Blueprint $table) {
                $table->id();
                $table->string('attachable_type');
                $table->unsignedBigInteger('attachable_id');
                $table->string('original_name');
                $table->string('stored_path')->comment('Chemin dans le disque sécurisé');
                $table->string('mime_type', 100);
                $table->unsignedBigInteger('size')->comment('Octets');
                $table->string('sha256', 64)->comment('Empreinte : intégrité + dédoublonnage');
                $table->string('kind', 40)->default('justificatif')
                    ->comment('facture | bon_livraison | recu | ordre_mission | justificatif');
                $table->unsignedBigInteger('uploaded_by');
                $table->timestamps();

                $table->index(['attachable_type', 'attachable_id']);
                $table->index('sha256');
            });
        }

        if (!Schema::hasTable('fuel_status_logs')) {
            Schema::create('fuel_status_logs', function (Blueprint $table) {
                $table->id();
                $table->string('subject_type');
                $table->unsignedBigInteger('subject_id');
                $table->string('action', 60)->comment('created|submitted|validated|rejected|paid|cancelled|...');
                $table->string('from_status')->nullable();
                $table->string('to_status');
                $table->text('reason')->nullable()->comment('Motif (obligatoire rejet/correction)');
                $table->unsignedBigInteger('actor_id');
                $table->timestamps();

                $table->index(['subject_type', 'subject_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('fuel_status_logs');
        Schema::dropIfExists('fuel_attachments');
        Schema::dropIfExists('fuel_reimbursements');
        Schema::dropIfExists('fuel_exits');
        Schema::dropIfExists('fuel_entries');
    }
};
