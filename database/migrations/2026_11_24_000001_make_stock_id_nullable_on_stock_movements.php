<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Les mouvements de stock sont désormais enregistrés au niveau magasin
     * (produit/format dans le libellé) et ne référencent plus obligatoirement
     * une fiche `stocks`. La vérification inspecteur reste obligatoire avant
     * toute intégration au stock officiel.
     */
    public function up(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->unsignedBigInteger('stock_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->unsignedBigInteger('stock_id')->nullable(false)->change();
        });
    }
};
