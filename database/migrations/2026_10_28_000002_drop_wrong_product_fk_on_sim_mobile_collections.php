<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * sim_mobile_collections.product_id référençait la table `products`
     * (stock magasins) alors que l'API SIM et le modèle utilisent
     * `sim_products`. Seule la contrainte erronée est supprimée ; l'intégrité
     * reste assurée par la validation `exists:sim_products,id` de l'API.
     * Aucune donnée n'est modifiée.
     */
    public function up(): void
    {
        if (!Schema::hasTable('sim_mobile_collections')) {
            return;
        }

        $wrongFk = collect(Schema::getForeignKeys('sim_mobile_collections'))
            ->first(fn ($fk) => $fk['columns'] === ['product_id'] && $fk['foreign_table'] === 'products');

        if ($wrongFk === null) {
            return;
        }

        Schema::table('sim_mobile_collections', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
        });
    }

    public function down(): void
    {
        // Volontairement vide : ne pas réintroduire une contrainte erronée.
    }
};
