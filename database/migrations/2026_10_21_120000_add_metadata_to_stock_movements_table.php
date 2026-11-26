<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * La colonne metadata est écrite par WarehouseKeeperController (transferts,
 * ajustements — transfer_type, device info, etc.) mais aucune migration ne la
 * crée : les écritures échouent ou sont perdues selon le schéma déployé.
 * Migration idempotente : ne touche à rien si la colonne existe déjà.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('stock_movements') && !Schema::hasColumn('stock_movements', 'metadata')) {
            Schema::table('stock_movements', function (Blueprint $table) {
                $table->json('metadata')->nullable()->after('reason');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('stock_movements') && Schema::hasColumn('stock_movements', 'metadata')) {
            Schema::table('stock_movements', function (Blueprint $table) {
                $table->dropColumn('metadata');
            });
        }
    }
};
