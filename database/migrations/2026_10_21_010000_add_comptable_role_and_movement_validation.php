<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Comptable des matières : valide les mouvements de stock
     * (entrées/sorties/justificatifs rattachés aux missions DTL).
     * Colonnes ajoutées conditionnellement : le schéma stock_movements
     * a plusieurs définitions historiques selon les environnements.
     */
    public function up(): void
    {
        if (Schema::hasTable('roles') && !DB::table('roles')->where('name', 'comptable')->exists()) {
            DB::table('roles')->insert([
                'name'         => 'comptable',
                'display_name' => 'Comptable des matières',
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);
        }

        if (Schema::hasTable('stock_movements')) {
            Schema::table('stock_movements', function (Blueprint $table) {
                if (!Schema::hasColumn('stock_movements', 'status')) {
                    $table->string('status')->default('draft')->index();
                }
                if (!Schema::hasColumn('stock_movements', 'validated_by')) {
                    $table->unsignedBigInteger('validated_by')->nullable();
                }
                if (!Schema::hasColumn('stock_movements', 'validated_at')) {
                    $table->timestamp('validated_at')->nullable();
                }
                if (!Schema::hasColumn('stock_movements', 'rejection_reason')) {
                    $table->text('rejection_reason')->nullable();
                }
            });
        }
    }

    public function down(): void
    {
        // Rollback limité aux colonnes introduites par CETTE migration.
        // validated_by/validated_at existaient déjà (cf. migration
        // 2025_06_30_100001_create_stock_movements_table) : ne pas les
        // supprimer, au risque de perdre des données de validation
        // antérieures ou de casser d'autres fonctionnalités.
        if (Schema::hasTable('stock_movements')) {
            Schema::table('stock_movements', function (Blueprint $table) {
                foreach (['status', 'rejection_reason'] as $col) {
                    if (Schema::hasColumn('stock_movements', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }

        if (Schema::hasTable('roles')) {
            DB::table('roles')->where('name', 'comptable')->delete();
        }
    }
};
