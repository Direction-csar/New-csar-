<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('donations', function (Blueprint $table) {
            // Ajouter le champ payment_provider après payment_method
            $table->string('payment_provider')->nullable()->after('payment_method');
            
            // Index pour les recherches par provider
            $table->index(['payment_provider', 'payment_status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('donations', function (Blueprint $table) {
            $table->dropIndex(['payment_provider', 'payment_status']);
            $table->dropColumn('payment_provider');
        });
    }
};
