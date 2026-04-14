<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sim_mobile_collections', function (Blueprint $table) {
            $table->string('provenance', 100)->nullable()->after('product_id');
            $table->decimal('quantity_collected', 10, 2)->nullable()->after('provenance');
            $table->decimal('producer_price', 10, 2)->nullable()->after('quantity_collected');
        });

        // Rename price → retail_price is already there; add producer_price alias
        // price field becomes "Prix Producteur" in the form
    }

    public function down(): void
    {
        Schema::table('sim_mobile_collections', function (Blueprint $table) {
            $table->dropColumn(['provenance', 'quantity_collected', 'producer_price']);
        });
    }
};
