<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Affectation persistante inspecteur ↔ magasin(s).
     * Historique complet : une affectation révoquée reste en base
     * (is_active=false, revoked_at/revoked_by) — jamais de suppression.
     */
    public function up(): void
    {
        Schema::create('inspector_warehouse_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inspector_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('warehouse_id')->constrained('warehouses')->cascadeOnDelete();
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('note')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('revoked_at')->nullable();
            $table->foreignId('revoked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['inspector_id', 'warehouse_id']);
            $table->index(['warehouse_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inspector_warehouse_assignments');
    }
};
