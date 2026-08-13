<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // M1 — Campagnes
        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->decimal('initial_stock_kg', 12, 2)->default(0);
            $table->decimal('executed_stock_kg', 12, 2)->default(0);
            $table->string('status')->default('active'); // active, closed, archived
            $table->timestamp('archived_at')->nullable();
            $table->timestamps();
        });

        // M1 — Plannings / volets
        Schema::create('plannings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained('campaigns')->onDelete('cascade');
            $table->string('name');
            $table->string('category'); // instructions_dg, vulnerables, religieux, spontanées, oal, etc.
            $table->decimal('planned_quota_kg', 12, 2)->default(0);
            $table->decimal('executed_quota_kg', 12, 2)->default(0);
            $table->decimal('alert_threshold_kg', 12, 2)->default(0);
            $table->foreignId('warehouse_id')->nullable()->constrained('warehouses')->onDelete('set null');
            $table->string('status')->default('active'); // active, full, closed
            $table->timestamps();
        });

        // Table d'affectation N,N entre plannings et utilisateurs (agents terrain)
        Schema::create('planning_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('planning_id')->constrained('plannings')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            $table->unique(['planning_id', 'user_id']);
        });

        // M2 — Bénéficiaires
        Schema::create('beneficiaires', function (Blueprint $table) {
            $table->id();
            $table->foreignId('planning_id')->constrained('plannings')->onDelete('cascade');
            $table->string('name');
            $table->string('phone')->nullable();
            $table->string('cni')->nullable();
            $table->text('address')->nullable();
            $table->string('category')->default('vulnerable');
            $table->boolean('vulnerable')->default(false);
            $table->boolean('religious')->default(false);
            $table->boolean('spontaneous')->default(false);
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->string('status')->default('active');
            $table->timestamps();
            $table->index('phone');
            $table->index('cni');
        });

        // M3 — Bons-matière
        Schema::create('bon_matieres', function (Blueprint $table) {
            $table->id();
            $table->foreignId('planning_id')->constrained('plannings')->onDelete('cascade');
            $table->foreignId('beneficiaire_id')->constrained('beneficiaires')->onDelete('cascade');
            $table->string('numero_bon')->unique();
            $table->decimal('quantite_kg', 10, 2);
            $table->string('categorie')->default('riz');
            $table->string('statut')->default('attribue'); // attribue, livre, annule, perdu
            $table->timestamp('attributed_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->foreignId('attributed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('delivered_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });

        // M3 — Tickets de retrait
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bon_matiere_id')->constrained('bon_matieres')->onDelete('cascade');
            $table->string('code')->unique();
            $table->text('qr_data')->nullable();
            $table->boolean('used')->default(false);
            $table->timestamp('used_at')->nullable();
            $table->foreignId('used_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('reissued_at')->nullable();
            $table->text('reissue_reason')->nullable();
            $table->foreignId('reissued_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });

        // M3 — Livraisons / transport
        Schema::create('livraisons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bon_matiere_id')->constrained('bon_matieres')->onDelete('cascade');
            $table->string('transporter')->nullable();
            $table->string('phone')->nullable();
            $table->date('delivery_date')->nullable();
            $table->string('gps_coordinates')->nullable();
            $table->string('status')->default('pending'); // pending, delivered
            $table->timestamps();
        });

        // M5 — Doublons détectés
        Schema::create('doublon_detectes', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // telephone, cni, bon, nom_proche
            $table->foreignId('entity_1_id')->constrained('beneficiaires')->onDelete('cascade');
            $table->foreignId('entity_2_id')->constrained('beneficiaires')->onDelete('cascade');
            $table->foreignId('planning_1_id')->nullable()->constrained('plannings')->onDelete('cascade');
            $table->foreignId('planning_2_id')->nullable()->constrained('plannings')->onDelete('cascade');
            $table->string('status')->default('a_verifier'); // a_verifier, confirme, faux_positif
            $table->text('justification')->nullable();
            $table->foreignId('resolved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
        });

        // M6 — Alertes
        Schema::create('alertes', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // stock, taux_execution, quota, doublon
            $table->foreignId('campaign_id')->nullable()->constrained('campaigns')->onDelete('cascade');
            $table->foreignId('planning_id')->nullable()->constrained('plannings')->onDelete('cascade');
            $table->string('controle')->nullable();
            $table->decimal('valeur', 12, 2)->nullable();
            $table->decimal('seuil', 12, 2)->nullable();
            $table->string('status')->default('active'); // active, resolved
            $table->timestamp('resolved_at')->nullable();
            $table->foreignId('resolved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alertes');
        Schema::dropIfExists('doublon_detectes');
        Schema::dropIfExists('livraisons');
        Schema::dropIfExists('tickets');
        Schema::dropIfExists('bon_matieres');
        Schema::dropIfExists('beneficiaires');
        Schema::dropIfExists('planning_user');
        Schema::dropIfExists('plannings');
        Schema::dropIfExists('campaigns');
    }
};
