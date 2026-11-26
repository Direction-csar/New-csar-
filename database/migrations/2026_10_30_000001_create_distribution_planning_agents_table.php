<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Affectations multiples : un planning peut avoir plusieurs distributeurs ET
 * plusieurs scanners, les deux types d'affectation etant independants.
 *
 * Additive et idempotente : la colonne historique distribution_plannings.assigned_to
 * est conservee (jamais supprimee) et chaque valeur non nulle est reprise comme
 * affectation « distributor ».
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('distribution_planning_agents')) {
            Schema::create('distribution_planning_agents', function (Blueprint $table) {
                $table->id();
                $table->foreignId('planning_id')->constrained('distribution_plannings')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->string('type', 20); // distributor | scanner
                $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->unique(['planning_id', 'user_id', 'type'], 'dpa_planning_user_type_unique');
                $table->index(['user_id', 'type'], 'dpa_user_type_index');
            });
        }

        $now = now();
        DB::table('distribution_plannings')
            ->whereNotNull('assigned_to')
            ->orderBy('id')
            ->chunkById(200, function ($plannings) use ($now) {
                DB::table('distribution_planning_agents')->insertOrIgnore(
                    $plannings->map(fn ($p) => [
                        'planning_id' => $p->id,
                        'user_id' => $p->assigned_to,
                        'type' => 'distributor',
                        'assigned_by' => null,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ])->all()
                );
            });
    }

    public function down(): void
    {
        // assigned_to n'a jamais ete modifie : seule la table des affectations multiples disparait.
        Schema::dropIfExists('distribution_planning_agents');
    }
};
