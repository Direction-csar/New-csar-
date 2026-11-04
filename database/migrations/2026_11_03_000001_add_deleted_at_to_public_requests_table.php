<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Suppression douce des demandes publiques : un dossier supprimé reste
 * en base (traçabilité institutionnelle) et disparaît des écrans.
 * Additive uniquement — aucune donnée existante n'est modifiée.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('public_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('public_requests', 'deleted_at')) {
                $table->softDeletes()->after('updated_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('public_requests', function (Blueprint $table) {
            if (Schema::hasColumn('public_requests', 'deleted_at')) {
                $table->dropSoftDeletes();
            }
        });
    }
};
